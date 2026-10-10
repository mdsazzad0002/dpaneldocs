<?php

namespace App\Support;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\BadRequestException;
use Anthropic\Core\Exceptions\RateLimitException;
use App\Models\Setting;
use RuntimeException;

/**
 * Drafts replies for staff with Claude. Staff always review and edit the
 * draft before it is sent; nothing is sent automatically.
 */
class AiAssistant
{
    public const MODELS = [
        'claude-opus-5-5' => 'Claude Opus 5.5 (best quality)',
        'claude-sonnet-5-5' => 'Claude Sonnet 5.5 (fast, lower cost)',
        'claude-haiku-5-5' => 'Claude Haiku 5.5 (fastest, lowest cost)',
        'claude-fable-5-1' => 'Claude Fable 5.1 (most capable)',
    ];

    /**
     * Models that accept server-side refusal fallbacks ("default" mode).
     */
    private const FALLBACK_MODELS = ['claude-opus-5-5', 'claude-fable-5-1', 'claude-sonnet-5-5'];

    public function apiKey(): ?string
    {
        return Setting::get('ai.api_key') ?: config('services.anthropic.key');
    }

    public function model(): string
    {
        $model = Setting::get('ai.model', 'claude-opus-5-5');

        return array_key_exists($model, self::MODELS) ? $model : 'claude-opus-5-5';
    }

    public function isEnabled(): bool
    {
        return Setting::get('ai.enabled', '1') === '1' && filled($this->apiKey());
    }

    /**
     * Write a reply draft.
     *
     * @param  string  $kind  What is being answered, e.g. "support ticket" or "docs comment".
     * @param  string  $conversation  The message(s) to answer, oldest first.
     * @param  string  $guidance  Optional notes from the staff member ("say we fixed it in 1.4").
     * @param  string  $draft  Optional text the staff member already wrote, to improve.
     */
    public function draftReply(string $kind, string $conversation, string $guidance = '', string $draft = ''): string
    {
        $prompt = "Write the reply to this {$kind}.\n\n<conversation>\n{$conversation}\n</conversation>";

        if (trim($guidance) !== '') {
            $prompt .= "\n\n<staff_notes>\n{$guidance}\n</staff_notes>\nFollow the staff notes: they say what the reply must contain.";
        }

        if (trim($draft) !== '') {
            $prompt .= "\n\n<current_draft>\n{$draft}\n</current_draft>\nImprove this draft: keep its facts and intent, fix the wording, and fill gaps.";
        }

        return $this->complete($this->systemPrompt(), $prompt);
    }

    /**
     * Write a fresh email from a short description.
     */
    public function composeEmail(string $recipient, string $subject, string $guidance, string $draft = ''): string
    {
        $prompt = "Write an email to {$recipient}.\nSubject: {$subject}\n\n<what_to_say>\n{$guidance}\n</what_to_say>";

        if (trim($draft) !== '') {
            $prompt .= "\n\n<current_draft>\n{$draft}\n</current_draft>\nImprove this draft rather than starting over.";
        }

        return $this->complete($this->systemPrompt(), $prompt);
    }

    private function systemPrompt(): string
    {
        $site = config('site.name');
        $pages = collect(Docs::all())
            ->map(fn ($page) => "- {$page['title']}: ".route('docs.show', $page['slug']).' — '.$page['description'])
            ->implode("\n");

        $prompt = <<<PROMPT
        You write replies on behalf of the {$site} team. {$site} is a free, self-hosted web hosting control panel built on Laravel, Vue, and Rust (website: {$this->siteUrl()}).

        The reply you write is a draft: a staff member reads and edits it before sending it by email or posting it publicly. Write only the body of the reply: no subject line, no "Hi <name>" greeting (one is added automatically), and no signature. Use plain text, no Markdown headings or tables. Be friendly, direct, and specific. When a documentation page answers the question, link it by its full URL. Never invent features, commands, prices, or promises; if you are unsure, say what the team will check and ask for the details you need (dPanel version, server OS, the exact error).

        Reply in the language the person wrote in.

        Documentation pages:
        {$pages}
        PROMPT;

        $instructions = trim((string) Setting::get('ai.instructions', ''));

        if ($instructions !== '') {
            $prompt .= "\n\nAdditional instructions from the team:\n{$instructions}";
        }

        return $prompt;
    }

    private function siteUrl(): string
    {
        return rtrim((string) config('app.url'), '/');
    }

    /**
     * Call the Messages API and return the reply text.
     */
    protected function complete(string $system, string $prompt): string
    {
        $apiKey = $this->apiKey();

        if (blank($apiKey)) {
            throw new RuntimeException('The AI assistant is not set up. Add an Anthropic API key in Settings.');
        }

        $model = $this->model();
        $client = new Client(apiKey: $apiKey);
        $fallbacks = in_array($model, self::FALLBACK_MODELS, true);

        try {
            $message = $client->beta->messages->create(
                maxTokens: 16000,
                messages: [['role' => 'user', 'content' => $prompt]],
                model: $model,
                system: $system,
                outputConfig: ['effort' => 'medium'],
                fallbacks: $fallbacks ? 'default' : null,
                betas: $fallbacks ? ['server-side-fallback-2026-07-01'] : null,
            );
        } catch (AuthenticationException) {
            throw new RuntimeException('The Anthropic API key was rejected. Check it in Settings.');
        } catch (RateLimitException) {
            throw new RuntimeException('The AI assistant is busy (rate limited). Try again in a minute.');
        } catch (BadRequestException $e) {
            throw new RuntimeException('The AI request was rejected: '.$e->getMessage());
        } catch (APIStatusException|APIConnectionException $e) {
            report($e);
            throw new RuntimeException('Could not reach the AI service. Try again shortly.');
        }

        if ($message->stopReason === 'refusal') {
            throw new RuntimeException('The AI assistant declined to write this reply. Please write it yourself.');
        }

        $text = '';
        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $text .= $block->text;
            }
        }

        if (trim($text) === '') {
            throw new RuntimeException('The AI assistant returned an empty reply. Try again.');
        }

        return trim($text);
    }
}
