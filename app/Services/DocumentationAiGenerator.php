<?php

namespace App\Services;

use RuntimeException;

class DocumentationAiGenerator
{
    public function __construct(private readonly AiGatewayClient $client) {}

    /**
     * Generate a full documentation post for the given topic.
     *
     * @return array{title: string, excerpt: string, content: string, category: string}
     */
    public function generate(string $topic): array
    {
        $content = $this->client->chat([
            [
                'role' => 'system',
                'content' => 'You write clear, accurate product documentation for dPanel, an source-available '
                    .'server and website control panel. Respond with ONLY a single JSON object — no markdown '
                    .'fences, no commentary — matching exactly this shape: '
                    .'{"title": string, "category": string, "excerpt": string, "content": string}. '
                    .'"category" is a short 1-3 word topic name (e.g. "Deployment", "Database", "Security"). '
                    .'"excerpt" is a single plain-text sentence (max 160 characters) summarizing the post, '
                    .'suitable as an SEO meta description. '
                    .'"content" is the article body written in this lightweight markup: lines starting with '
                    .'"# " for a top-level heading, "## " for a subheading, "### " for a sub-subheading, "- " '
                    .'for a bullet list item, and blank lines separating plain paragraphs. Do not use any other '
                    .'markdown syntax (no bold, links, or code fences). Include at least one "# " heading, two '
                    .'or more "## " sections, and a short bullet list where relevant.',
            ],
            [
                'role' => 'user',
                'content' => "Write a documentation post about: {$topic}",
            ],
        ]);

        $data = json_decode($this->extractJson($content), true);

        if (! is_array($data) || ! isset($data['title'], $data['content'], $data['category'])) {
            throw new RuntimeException('AI response was not in the expected format.');
        }

        return [
            'title' => trim((string) $data['title']),
            'category' => trim((string) $data['category']),
            'excerpt' => trim((string) ($data['excerpt'] ?? '')),
            'content' => trim((string) $data['content']),
        ];
    }

    /**
     * Strip accidental ```json fences models sometimes add despite instructions.
     */
    private function extractJson(string $raw): string
    {
        $trimmed = trim($raw);

        if (str_starts_with($trimmed, '```')) {
            $trimmed = preg_replace('/^```[a-z]*\n|```$/i', '', $trimmed);
        }

        return trim($trimmed);
    }
}
