<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\DocSync;
use App\Models\PageFeedback;
use App\Support\Docs;
use App\Support\DocsSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocsController extends Controller
{
    public function index(): Response
    {
        $feedback = PageFeedback::query()
            ->selectRaw('page, count(*) as total, sum(case when helpful then 1 else 0 end) as helpful')
            ->groupBy('page')
            ->get()
            ->keyBy('page');

        $comments = Comment::approved()->whereNull('parent_id')->selectRaw('page, count(*) as total')->groupBy('page')->pluck('total', 'page');

        $pages = collect(DocsSync::files())->map(function (string $file, string $slug) use ($feedback, $comments) {
            $meta = collect(Docs::all())->firstWhere('slug', $slug);
            $votes = $feedback[$slug] ?? null;

            return [
                'slug' => $slug,
                'title' => $meta['title'] ?? $slug,
                'section' => $meta['section'] ?? null,
                'source' => $file,
                'source_url' => Docs::sourceUrl($slug),
                'url' => $meta ? route('docs.show', $slug) : null,
                'exists' => is_file(Docs::path($slug)),
                'updated_at' => Docs::lastModified($slug) ?: null,
                'size' => is_file(Docs::path($slug)) ? filesize(Docs::path($slug)) : 0,
                'helpful' => $votes ? (int) round($votes->helpful / max($votes->total, 1) * 100) : null,
                'votes' => (int) ($votes->total ?? 0),
                'comments' => (int) ($comments[$slug] ?? 0),
            ];
        })->values();

        return Inertia::render('Backend/Docs/Index', [
            'pages' => $pages,
            'syncs' => DocSync::with('user:id,name')->latest()->limit(15)->get(),
            'repository' => config('site.repositories.panel'),
            'branch' => config('site.docs_branch', 'main'),
        ]);
    }

    public function sync(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'branch' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9._\/-]+$/'],
        ]);

        $sync = DocsSync::fromGitHub($request->user(), $data['branch'] ?? null);

        $message = match ($sync->status) {
            'success' => count($sync->changed)
                ? count($sync->changed).' page(s) updated from GitHub.'
                : 'Documentation is already up to date.',
            'partial' => count($sync->changed).' page(s) updated; '.count($sync->skipped).' could not be downloaded: '.implode(', ', $sync->skipped).'.',
            default => 'Sync failed: '.$sync->message,
        };

        return back()->with('status', $message);
    }
}
