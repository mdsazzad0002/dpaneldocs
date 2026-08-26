<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Documentation;
use App\Models\DocumentationVersion;
use Illuminate\Http\JsonResponse;

/**
 * Public, read-only "is there an update?" API. Any external app (e.g. the
 * dPanel control panel itself) can poll this to see every real, published
 * version of a product and pick whichever one it needs — never a fake or
 * placeholder entry. If a product exists but has no versions uploaded yet,
 * "latest" is null and "versions" is an empty array, not a 404 or made-up data.
 */
class VersionController extends Controller
{
    public function index(string $slug): JsonResponse
    {
        $doc = Documentation::published()->where('slug', $slug)->first();

        if (! $doc) {
            return response()->json(['message' => 'No published documentation found for this slug.'], 404);
        }

        $versions = $doc->versions->map(fn (DocumentationVersion $v) => $this->formatVersion($doc, $v))->values();

        return response()->json([
            'product' => $doc->title,
            'slug' => $doc->slug,
            'latest' => $versions->first(),
            'versions' => $versions,
        ]);
    }

    public function latest(string $slug): JsonResponse
    {
        $doc = Documentation::published()->where('slug', $slug)->first();

        if (! $doc) {
            return response()->json(['message' => 'No published documentation found for this slug.'], 404);
        }

        $latest = $doc->versions->first();

        return response()->json([
            'product' => $doc->title,
            'slug' => $doc->slug,
            'latest' => $latest ? $this->formatVersion($doc, $latest) : null,
        ]);
    }

    private function formatVersion(Documentation $doc, DocumentationVersion $version): array
    {
        return [
            'version' => $version->version,
            'changelog' => $version->changelog,
            'install_guide' => $version->install_guide,
            'file_name' => $version->file_name,
            'file_size' => $version->file_size,
            'downloads' => $version->downloads,
            'released_at' => $version->created_at?->toIso8601String(),
            'download_url' => route('docs.public.download', ['slug' => $doc->slug, 'versionId' => $version->id]),
        ];
    }
}
