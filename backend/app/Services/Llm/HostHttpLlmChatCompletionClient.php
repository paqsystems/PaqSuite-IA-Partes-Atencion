<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use PaqSuite\LaravelCore\ChatAssistant\Contracts\LlmChatCompletionClient;
use PaqSuite\LaravelCore\Llm\LlmCredentialContext;
use PaqSuite\LaravelCore\Llm\LlmProviderCatalog;

/**
 * Cliente HTTP host para proveedores GEN-16 (OpenAI-compatible + Anthropic + Gemini).
 * Timeout desde config producto (TR-008).
 */
final class HostHttpLlmChatCompletionClient implements LlmChatCompletionClient
{
    public function complete(
        LlmCredentialContext $credential,
        array $messages,
        array $images = []
    ): string {
        $timeout = max(5, (int) config('paqsuite.chatAssistant.llmTimeoutSeconds', 60));
        $provider = LlmProviderCatalog::normalize($credential->provider);

        return match ($provider) {
            LlmProviderCatalog::ANTHROPIC => $this->completeAnthropic($credential, $messages, $timeout),
            LlmProviderCatalog::GOOGLE_GEMINI => $this->completeGemini(
                $credential,
                $messages,
                $timeout
            ),
            default => $this->completeOpenAiCompatible($credential, $messages, $images, $timeout),
        };
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @param  list<array{fileName: string, mimeType: string, contentBase64: string}>  $images
     */
    private function completeOpenAiCompatible(
        LlmCredentialContext $credential,
        array $messages,
        array $images,
        int $timeout
    ): string {
        $provider = LlmProviderCatalog::normalize($credential->provider);
        $fallbackBase = LlmProviderCatalog::defaultBaseUrl($provider) ?? 'https://api.openai.com/v1';
        $baseUrl = rtrim($credential->baseUrl ?: $fallbackBase, '/');
        $payloadMessages = $messages;
        if ($images !== [] && $credential->supportsVision) {
            $payloadMessages = $this->attachOpenAiImages($messages, $images);
        }

        $headers = [];
        if ($provider === LlmProviderCatalog::OPEN_ROUTER) {
            $headers['HTTP-Referer'] = (string) config('app.url', 'https://paqsuite.local');
            $headers['X-Title'] = (string) config('app.name', 'PaqSuite');
        }

        $response = Http::timeout($timeout)
            ->withToken($credential->secret())
            ->withHeaders($headers)
            ->acceptJson()
            ->post($baseUrl.'/chat/completions', [
                'model' => $credential->model,
                'messages' => $payloadMessages,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('LLM provider request failed: HTTP '.$response->status());
        }

        $content = data_get($response->json(), 'choices.0.message.content');
        if (! is_string($content) || trim($content) === '') {
            throw new \RuntimeException('LLM provider returned an empty reply.');
        }

        return $content;
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     */
    private function completeAnthropic(
        LlmCredentialContext $credential,
        array $messages,
        int $timeout
    ): string {
        $system = '';
        $anthropicMessages = [];
        foreach ($messages as $message) {
            if (($message['role'] ?? '') === 'system') {
                $system = (string) ($message['content'] ?? '');
                continue;
            }
            $anthropicMessages[] = [
                'role' => ($message['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user',
                'content' => (string) ($message['content'] ?? ''),
            ];
        }

        $baseUrl = rtrim($credential->baseUrl ?: 'https://api.anthropic.com', '/');
        $body = [
            'model' => $credential->model,
            'max_tokens' => 1024,
            'messages' => $anthropicMessages,
        ];
        if ($system !== '') {
            $body['system'] = $system;
        }

        $response = Http::timeout($timeout)
            ->withHeaders([
                'x-api-key' => $credential->secret(),
                'anthropic-version' => '2023-06-01',
            ])
            ->acceptJson()
            ->post($baseUrl.'/v1/messages', $body);

        if (! $response->successful()) {
            throw new \RuntimeException('LLM provider request failed: HTTP '.$response->status());
        }

        $content = data_get($response->json(), 'content.0.text');
        if (! is_string($content) || trim($content) === '') {
            throw new \RuntimeException('LLM provider returned an empty reply.');
        }

        return $content;
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     */
    private function completeGemini(
        LlmCredentialContext $credential,
        array $messages,
        int $timeout
    ): string {
        $parts = [];
        foreach ($messages as $message) {
            $role = (string) ($message['role'] ?? 'user');
            $prefix = $role === 'system' ? 'System: ' : ($role === 'assistant' ? 'Assistant: ' : 'User: ');
            $parts[] = ['text' => $prefix.(string) ($message['content'] ?? '')];
        }

        $baseUrl = rtrim(
            $credential->baseUrl ?: 'https://generativelanguage.googleapis.com/v1beta',
            '/'
        );
        $url = sprintf(
            '%s/models/%s:generateContent?key=%s',
            $baseUrl,
            rawurlencode($credential->model),
            rawurlencode($credential->secret())
        );

        $response = Http::timeout($timeout)
            ->acceptJson()
            ->post($url, [
                'contents' => [
                    ['role' => 'user', 'parts' => $parts],
                ],
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('LLM provider request failed: HTTP '.$response->status());
        }

        $content = data_get($response->json(), 'candidates.0.content.parts.0.text');
        if (! is_string($content) || trim($content) === '') {
            throw new \RuntimeException('LLM provider returned an empty reply.');
        }

        return $content;
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @param  list<array{fileName: string, mimeType: string, contentBase64: string}>  $images
     * @return list<array<string, mixed>>
     */
    private function attachOpenAiImages(array $messages, array $images): array
    {
        $result = [];
        foreach ($messages as $message) {
            if (($message['role'] ?? '') !== 'user') {
                $result[] = $message;
                continue;
            }

            $content = [
                ['type' => 'text', 'text' => (string) ($message['content'] ?? '')],
            ];
            foreach ($images as $image) {
                $mime = (string) ($image['mimeType'] ?? 'image/png');
                $b64 = (string) ($image['contentBase64'] ?? '');
                $content[] = [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => 'data:'.$mime.';base64,'.$b64,
                    ],
                ];
            }
            $result[] = ['role' => 'user', 'content' => $content];
        }

        return $result;
    }
}
