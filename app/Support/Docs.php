<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalink;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\StringContainerInterface;

/**
 * Static documentation pages. Each page is a Markdown file in resources/docs,
 * listed (and ordered) in config/site.php. Pages are rendered once and cached
 * until the file changes, so a deploy is all it takes to publish an edit.
 */
class Docs
{
    public static function directory(): string
    {
        return resource_path('docs');
    }

    /**
     * Sidebar sections: ['Getting started' => [['slug' => ..., 'title' => ..., 'description' => ...], ...]].
     *
     * @return array<string, array<int, array{slug: string, title: string, description: string, section: string}>>
     */
    public static function sections(): array
    {
        $sections = [];

        foreach (config('site.docs', []) as $section => $pages) {
            foreach ($pages as $slug => $page) {
                if (is_file(self::path($slug))) {
                    $sections[$section][] = ['slug' => $slug, 'section' => $section] + $page;
                }
            }
        }

        return $sections;
    }

    /**
     * Every published page in reading order.
     *
     * @return array<int, array{slug: string, title: string, description: string, section: string}>
     */
    public static function all(): array
    {
        return array_merge([], ...array_values(self::sections()));
    }

    public static function exists(string $slug): bool
    {
        return collect(self::all())->contains('slug', $slug);
    }

    public static function path(string $slug): string
    {
        return self::directory().DIRECTORY_SEPARATOR.$slug.'.md';
    }

    public static function lastModified(string $slug): int
    {
        return (int) @filemtime(self::path($slug));
    }

    /**
     * @return array{slug: string, title: string, description: string, section: string, html: string, toc: array<int, array{level: int, text: string, id: string}>, text: string, updated_at: int, previous: ?array, next: ?array}|null
     */
    public static function find(string $slug): ?array
    {
        $pages = self::all();
        $index = collect($pages)->search(fn ($page) => $page['slug'] === $slug);

        if ($index === false) {
            return null;
        }

        $rendered = Cache::rememberForever(
            'docs:'.$slug.':'.self::lastModified($slug),
            fn () => self::render((string) file_get_contents(self::path($slug)))
        );

        return $pages[$index] + $rendered + [
            'updated_at' => self::lastModified($slug),
            'previous' => $pages[$index - 1] ?? null,
            'next' => $pages[$index + 1] ?? null,
        ];
    }

    /**
     * Full-text search over page titles, descriptions, headings, and body text.
     *
     * @return array<int, array{slug: string, title: string, section: string, excerpt: string, anchor: ?string}>
     */
    public static function search(string $query, int $limit = 20): array
    {
        $terms = array_values(array_filter(preg_split('/\s+/', Str::lower(trim($query))) ?: []));

        if ($terms === []) {
            return [];
        }

        $results = [];

        foreach (self::all() as $page) {
            $doc = self::find($page['slug']);
            $haystack = Str::lower($doc['title'].' '.$doc['description'].' '.$doc['text']);

            if (collect($terms)->contains(fn ($term) => ! str_contains($haystack, $term))) {
                continue;
            }

            $score = 0;
            foreach ($terms as $term) {
                $score += str_contains(Str::lower($doc['title']), $term) ? 20 : 0;
                $score += collect($doc['toc'])->filter(fn ($h) => str_contains(Str::lower($h['text']), $term))->count() * 5;
                $score += min(substr_count($haystack, $term), 10);
            }

            $heading = collect($doc['toc'])->first(fn ($h) => str_contains(Str::lower($h['text']), $terms[0]));

            $results[] = [
                'slug' => $doc['slug'],
                'title' => $doc['title'],
                'section' => $doc['section'],
                'excerpt' => Str::excerpt($doc['text'], $terms[0], ['radius' => 110]) ?: $doc['description'],
                'anchor' => $heading['id'] ?? null,
                'heading' => $heading['text'] ?? null,
                'score' => $score,
            ];
        }

        usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($results, 0, $limit);
    }

    /**
     * @return array{html: string, toc: array<int, array{level: int, text: string, id: string}>, text: string}
     */
    public static function render(string $markdown): array
    {
        $markdown = self::strip($markdown);

        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'heading_permalink' => [
                'html_class' => 'heading-anchor',
                'id_prefix' => '',
                'apply_id_to_heading' => true,
                'fragment_prefix' => '',
                'insert' => 'after',
                'min_heading_level' => 2,
                'max_heading_level' => 4,
                'title' => 'Link to this section',
                'symbol' => '#',
                'aria_hidden' => true,
            ],
            'external_link' => [
                'internal_hosts' => [parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost'],
                'open_in_new_window' => true,
                'noopener' => 'external',
                'noreferrer' => 'external',
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new HeadingPermalinkExtension);
        $environment->addExtension(new ExternalLinkExtension);

        // Rewrite repository-relative links (installation.md#x, ../SECURITY.md)
        // to their page on this site, or to GitHub when there is no such page.
        $environment->addEventListener(DocumentParsedEvent::class, function (DocumentParsedEvent $event) {
            foreach ($event->getDocument()->iterator() as $node) {
                if ($node instanceof Link) {
                    $node->setUrl(self::rewriteUrl($node->getUrl()));
                }
            }
        }, 100);

        $rendered = (new MarkdownConverter($environment))->convert($markdown);

        $toc = [];
        foreach ($rendered->getDocument()->iterator() as $node) {
            if ($node instanceof Heading && in_array($node->getLevel(), [2, 3], true)) {
                $permalink = collect($node->children())->first(fn ($child) => $child instanceof HeadingPermalink);
                if ($permalink) {
                    $toc[] = ['level' => $node->getLevel(), 'text' => self::textOf($node), 'id' => $permalink->getSlug()];
                }
            }
        }

        $html = $rendered->getContent();

        return [
            'html' => $html,
            'toc' => $toc,
            'text' => trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(str_replace('<', ' <', $html)))) ?? ''),
        ];
    }

    /**
     * Drop the leading "# Title" (the page renders its own title) and the
     * hand-written "## Contents" list (the page renders its own table of
     * contents), and the "Part of the dPanel documentation" preface.
     */
    private static function strip(string $markdown): string
    {
        $markdown = str_replace("\r\n", "\n", $markdown);
        $markdown = preg_replace('/\A\s*#\s+[^\n]*\n/', '', $markdown) ?? $markdown;
        $markdown = preg_replace('/^##\s+Contents\s*\n.*?(?=^##\s)/ms', '', $markdown) ?? $markdown;
        $markdown = preg_replace('/\A\s*>\s*Part of the \[dPanel documentation\].*?\n\n/s', '', $markdown) ?? $markdown;

        return ltrim($markdown);
    }

    public static function rewriteUrl(string $url): string
    {
        if ($url === '' || str_starts_with($url, '#') || preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) {
            return $url;
        }

        [$path, $fragment] = array_pad(explode('#', $url, 2), 2, null);
        $file = Str::lower(pathinfo($path, PATHINFO_FILENAME));
        $anchor = $fragment ? '#'.$fragment : '';

        if (Str::contains(Str::lower($path), 'whmcs')) {
            $file = 'whmcs';
        } elseif ($file === 'readme') {
            return route('docs.index', absolute: false).$anchor;
        }

        if (self::exists($file)) {
            return route('docs.show', $file, false).$anchor;
        }

        $repoPath = ltrim(preg_replace('#^(\.\./)+#', '', $path) ?? $path, './');

        return rtrim(config('site.repositories.panel'), '/').'/blob/main/'.$repoPath.$anchor;
    }

    private static function textOf(Heading $heading): string
    {
        $text = '';

        foreach ($heading->iterator() as $node) {
            if ($node instanceof StringContainerInterface && ! $node->parent() instanceof HeadingPermalink) {
                $text .= $node->getLiteral();
            }
        }

        return trim($text);
    }

    /**
     * The file a page comes from, relative to the dpanel repository root.
     */
    public static function sourceFile(string $slug): string
    {
        return match ($slug) {
            'security' => 'SECURITY.md',
            'contributing' => 'CONTRIBUTING.md',
            'whmcs' => 'dpanel/integrations/whmcs/README.md',
            default => 'docs/'.$slug.'.md',
        };
    }

    /**
     * The GitHub URL of a page's source file, for "Edit this page" links.
     */
    public static function sourceUrl(string $slug): string
    {
        return rtrim(config('site.repositories.panel'), '/').'/blob/main/'.self::sourceFile($slug);
    }
}
