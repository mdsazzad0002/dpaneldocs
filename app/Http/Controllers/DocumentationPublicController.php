<?php

namespace App\Http\Controllers;

use App\Models\Documentation;
use App\Models\DocumentationVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentationPublicController extends Controller
{
    public function index(): Response
    {
        $posts = Documentation::published()
            ->with('category:id,name')
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'slug', 'category_id', 'excerpt', 'views', 'created_at'])
            ->map(fn (Documentation $doc): array => [
                'title' => $doc->title,
                'slug' => $doc->slug,
                'category' => $doc->category?->name,
                'excerpt' => $doc->excerpt,
                'views' => $doc->views,
                'created_at' => $doc->created_at?->format('M j, Y'),
            ]);

        return Inertia::render('Documentation/PublicIndex', [
            'posts' => $posts,
            'categories' => $posts->pluck('category')->filter()->unique()->values(),
        ]);
    }

    public function show(string $slug): Response
    {
        $doc = Documentation::published()->with('category:id,name')->where('slug', $slug)->firstOrFail();
        $doc->increment('views');

        return Inertia::render('Documentation/PublicShow', [
            'post' => [
                'title' => $doc->title,
                'slug' => $doc->slug,
                'category' => $doc->category?->name,
                'excerpt' => $doc->excerpt,
                'content' => $doc->content,
                'views' => $doc->views,
                'created_at' => $doc->created_at?->format('M j, Y'),
                'versions' => $doc->versions->map(fn (DocumentationVersion $v): array => [
                    'id' => $v->id,
                    'version' => $v->version,
                    'changelog' => $v->changelog,
                    'file_name' => $v->file_name,
                    'file_size' => $v->file_size,
                    'downloads' => $v->downloads,
                    'created_at' => $v->created_at?->format('M j, Y'),
                ]),
            ],
        ]);
    }

    public function download(string $slug, string $versionId): BinaryFileResponse
    {
        $doc = Documentation::published()->where('slug', $slug)->firstOrFail();
        $version = DocumentationVersion::where('documentation_id', $doc->id)->findOrFail($versionId);

        DB::table('documentation_versions')->where('id', $version->id)->increment('downloads');

        $path = Storage::disk('local')->path($version->file_path);

        return response()->download($path, $version->file_name);
    }

    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if ($query === '') {
            return response()->json(['results' => []]);
        }

        $results = Documentation::published()
            ->with('category:id,name')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->orderByDesc('views')
            ->limit(8)
            ->get(['id', 'title', 'slug', 'category_id', 'excerpt'])
            ->map(fn (Documentation $doc): array => [
                'title' => $doc->title,
                'slug' => $doc->slug,
                'category' => $doc->category?->name,
                'excerpt' => $doc->excerpt,
            ]);

        return response()->json(['results' => $results]);
    }
}
