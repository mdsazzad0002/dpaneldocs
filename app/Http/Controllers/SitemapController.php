<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Documentation;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('docs.public.index'), 'lastmod' => now()->toAtomString()],
        ]);

        Category::query()
            ->whereHas('documentation', fn ($q) => $q->published())
            ->get(['slug'])
            ->each(fn (Category $category) => $urls->push([
                'loc' => route('docs.public.category', ['slug' => $category->slug]),
                'lastmod' => now()->toAtomString(),
            ]));

        Documentation::published()
            ->get(['slug', 'updated_at'])
            ->each(fn (Documentation $doc) => $urls->push([
                'loc' => route('docs.public.show', ['slug' => $doc->slug]),
                'lastmod' => $doc->updated_at?->toAtomString() ?? now()->toAtomString(),
            ]));

        $xml = view('public.sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
