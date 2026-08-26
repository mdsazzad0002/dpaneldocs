<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiGatewayClient
{
    /**
     * Send a chat completion request to the dPanel AI gateway and return the
     * assistant's text content. Routing/model selection is always "auto" —
     * the gateway itself picks the provider.
     */
    public function chat(array $messages, array $options = []): string
    {
        $apiKey = config('services.ai_gateway.api_key');

        if (! $apiKey) {
            throw new RuntimeException('AI_GATEWAY_API_KEY is not configured.');
        }

        $response = Http::withToken($apiKey)
            ->timeout(120)
            ->retry(2, 1000)
            ->post(rtrim(config('services.ai_gateway.base_url'), '/').'/chat/completions', [
                'model' => 'auto',
                'messages' => $messages,
                ...$options,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('AI gateway request failed: '.$response->status().' '.$response->body());
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw new RuntimeException('AI gateway returned an empty response.');
        }

        return $content;
    }
}
