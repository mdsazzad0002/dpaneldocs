<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Support\Docs;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $siteUpdated = collect(Docs::all())->map(fn ($page) => Docs::lastModified($page['slug']))->max() ?: time();
        $reviewsUpdated = Review::approved()->max('approved_at');

        $urls = collect([
            ['loc' => route('home'), 'lastmod' => $siteUpdated, 'priority' => '1.0'],
            ['loc' => route('docs.index'), 'lastmod' => $siteUpdated, 'priority' => '0.9'],
            ['loc' => route('support.index'), 'lastmod' => null, 'priority' => '0.7'],
            ['loc' => route('reviews.index'), 'lastmod' => $reviewsUpdated ? strtotime($reviewsUpdated) : null, 'priority' => '0.6'],
            ['loc' => route('privacy'), 'lastmod' => null, 'priority' => '0.2'],
            ['loc' => route('terms'), 'lastmod' => null, 'priority' => '0.2'],
        ]);

        foreach (Docs::all() as $page) {
            $urls->push([
                'loc' => route('docs.show', $page['slug']),
                'lastmod' => Docs::lastModified($page['slug']) ?: null,
                'priority' => '0.8',
            ]);
        }

        return response(view('public.sitemap', ['urls' => $urls])->render(), 200)
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /support/tickets/',
            'Disallow: /login',
            'Disallow: /forgot-password',
            'Disallow: /reset-password',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200)->header('Content-Type', 'text/plain');
    }
}
