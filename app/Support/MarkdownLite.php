<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Parses the lightweight markup used for documentation content: "# "/"## "/"### "
 * headings, "- " bullet list items, and blank-line-separated paragraphs. Mirrors
 * the parser used client-side for AI-authored content so posts render identically
 * whether viewed via the public Blade pages or (previously) the Vue reader.
 */
class MarkdownLite
{
    /**
     * @return array<int, array{type: string, text?: string, id?: string, items?: array<int, string>}>
     */
    public static function parse(?string $content): array
    {
        $blocks = [];
        $paragraph = [];
        $list = [];

        $flushParagraph = function () use (&$paragraph, &$blocks) {
            if ($paragraph !== []) {
                $blocks[] = ['type' => 'p', 'text' => implode(' ', $paragraph)];
                $paragraph = [];
            }
        };
        $flushList = function () use (&$list, &$blocks) {
            if ($list !== []) {
                $blocks[] = ['type' => 'ul', 'items' => $list];
                $list = [];
            }
        };

        foreach (explode("\n", (string) $content) as $rawLine) {
            $line = rtrim($rawLine);

            if (preg_match('/^(#{1,3})\s+(.*)$/', $line, $m)) {
                $flushParagraph();
                $flushList();
                $text = trim($m[2]);
                $blocks[] = ['type' => 'h'.strlen($m[1]), 'text' => $text, 'id' => Str::slug($text)];
            } elseif (preg_match('/^[-*]\s+(.*)$/', $line, $m)) {
                $flushParagraph();
                $list[] = $m[1];
            } elseif (trim($line) === '') {
                $flushParagraph();
                $flushList();
            } else {
                $flushList();
                $paragraph[] = trim($line);
            }
        }
        $flushParagraph();
        $flushList();

        return $blocks;
    }

    /**
     * @param  array<int, array{type: string, text?: string, id?: string}>  $blocks
     * @return array<int, array{type: string, text: string, id: string}>
     */
    public static function tableOfContents(array $blocks): array
    {
        return array_values(array_filter(
            $blocks,
            fn (array $b) => in_array($b['type'], ['h1', 'h2', 'h3'], true)
        ));
    }
}
