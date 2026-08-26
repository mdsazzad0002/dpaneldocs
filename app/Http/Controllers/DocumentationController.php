<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Documentation;
use App\Models\DocumentationVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class DocumentationController extends Controller
{
    public function index(Request $request): Response
    {
        $actor = $request->user();
        $canManage = (bool) $actor->is_admin;

        $documentation = Documentation::query()
            ->when(! $canManage, fn ($query) => $query->where('submitted_by', $actor->id))
            ->with(['submittedBy:id,name,email', 'category', 'versions'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Documentation $doc): array => [
                'id' => $doc->id,
                'title' => $doc->title,
                'slug' => $doc->slug,
                'category' => $doc->category?->name,
                'status' => $doc->status,
                'views' => $doc->views,
                'rejection_reason' => $doc->rejection_reason,
                'submitted_by' => $doc->submittedBy?->name,
                'version_count' => $doc->versions->count(),
                'created_at' => $doc->created_at?->diffForHumans(),
                'can_edit' => $canManage || $doc->submitted_by === $actor->id,
            ])
            ->all();

        return Inertia::render('Documentation/List', [
            'documentation' => $documentation,
            'canManage' => $canManage,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Documentation/Create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $actor = $request->user();
        $canManage = (bool) $actor->is_admin;

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'category_id' => ['nullable', 'string', 'exists:categories,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
        ]);

        $slug = $this->uniqueSlug($validated['title']);

        Documentation::create([
            'id' => (string) Str::uuid(),
            'title' => $validated['title'],
            'slug' => $slug,
            'category_id' => $validated['category_id'] ?? null,
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'status' => $canManage ? 'published' : 'pending',
            'submitted_by' => $actor->id,
        ]);

        return redirect()->route('documentation.index')->with(
            'success',
            $canManage ? "Post '{$validated['title']}' published successfully." : "Post '{$validated['title']}' submitted for review."
        );
    }

    public function edit(string $id): Response
    {
        $doc = Documentation::with('versions')->findOrFail($id);
        $this->authorizeAccess($doc);

        return Inertia::render('Documentation/Edit', [
            'documentation' => [
                'id' => $doc->id,
                'title' => $doc->title,
                'slug' => $doc->slug,
                'category_id' => $doc->category_id,
                'excerpt' => $doc->excerpt,
                'content' => $doc->content,
                'status' => $doc->status,
                'rejection_reason' => $doc->rejection_reason,
                'versions' => $doc->versions->map(fn (DocumentationVersion $v): array => [
                    'id' => $v->id,
                    'version' => $v->version,
                    'changelog' => $v->changelog,
                    'file_name' => $v->file_name,
                    'file_size' => $v->file_size,
                    'downloads' => $v->downloads,
                    'created_at' => $v->created_at?->diffForHumans(),
                ]),
            ],
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'canManage' => (bool) request()->user()->is_admin,
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $doc = Documentation::findOrFail($id);
        $this->authorizeAccess($doc);
        $canManage = (bool) $request->user()->is_admin;

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'category_id' => ['nullable', 'string', 'exists:categories,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
        ]);

        $doc->fill($validated);

        if ($doc->isDirty('title')) {
            $doc->slug = $this->uniqueSlug($validated['title'], $doc->id);
        }

        // Non-managers editing their own post send it back for re-review.
        if (! $canManage) {
            $doc->status = 'pending';
            $doc->rejection_reason = null;
        }

        $doc->save();

        return redirect()->route('documentation.index')->with('success', "Post '{$doc->title}' updated successfully.");
    }

    public function destroy(string $id): RedirectResponse
    {
        $doc = Documentation::with('versions')->findOrFail($id);
        $this->authorizeAccess($doc);

        foreach ($doc->versions as $version) {
            Storage::disk('local')->delete($version->file_path);
        }

        $doc->delete();

        return redirect()->route('documentation.index')->with('success', "Post '{$doc->title}' deleted.");
    }

    public function approve(string $id): RedirectResponse
    {
        $doc = Documentation::findOrFail($id);
        $doc->update([
            'status' => 'published',
            'reviewed_by' => request()->user()->id,
            'rejection_reason' => null,
        ]);

        return redirect()->back()->with('success', "Post '{$doc->title}' published.");
    }

    public function reject(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $doc = Documentation::findOrFail($id);
        $doc->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return redirect()->back()->with('success', "Post '{$doc->title}' rejected.");
    }

    public function storeVersion(Request $request, string $id): RedirectResponse
    {
        $doc = Documentation::findOrFail($id);
        $this->authorizeAccess($doc);

        $validated = $request->validate([
            'version' => [
                'required', 'string', 'max:32',
                Rule::unique('documentation_versions', 'version')->where('documentation_id', $doc->id),
            ],
            'changelog' => ['nullable', 'string', 'max:2000'],
            'archive' => ['required', 'file', 'mimes:zip', 'max:102400'], // 100 MB
        ]);

        $file = $request->file('archive');
        $filename = Str::slug($doc->title).'-'.$validated['version'].'.zip';
        $path = $file->storeAs('documentation/'.$doc->id, $filename, 'local');

        DocumentationVersion::create([
            'id' => (string) Str::uuid(),
            'documentation_id' => $doc->id,
            'version' => $validated['version'],
            'changelog' => $validated['changelog'] ?? null,
            'file_path' => $path,
            'file_name' => $filename,
            'file_size' => $file->getSize(),
        ]);

        return redirect()->back()->with('success', "Version {$validated['version']} uploaded.");
    }

    public function destroyVersion(string $id, string $versionId): RedirectResponse
    {
        $doc = Documentation::findOrFail($id);
        $this->authorizeAccess($doc);

        $version = DocumentationVersion::where('documentation_id', $doc->id)->findOrFail($versionId);
        Storage::disk('local')->delete($version->file_path);
        $version->delete();

        return redirect()->back()->with('success', "Version {$version->version} removed.");
    }

    private function authorizeAccess(Documentation $doc): void
    {
        $actor = request()->user();

        if (! $actor->is_admin && $doc->submitted_by !== $actor->id) {
            abort(BaseResponse::HTTP_FORBIDDEN, 'You do not have access to this documentation post.');
        }
    }

    private function uniqueSlug(string $title, ?string $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (
            Documentation::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
