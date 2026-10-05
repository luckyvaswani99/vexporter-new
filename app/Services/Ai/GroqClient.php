<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Minimal client for Groq's OpenAI-compatible Chat Completions API.
 *
 * Two shapes: structured() returns a parsed JSON object (strict JSON-Schema
 * mode on gpt-oss models, JSON-object mode elsewhere) and text() returns the
 * raw reply. A rate limit (429/413) retries once on the fallback model.
 */
class GroqClient
{
    public function hasKey(): bool
    {
        return filled(config('services.groq.key'));
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    public function structured(array $messages, array $schema, string $name = 'result', int $maxTokens = 1800): array
    {
        $reply = $this->dispatch((string) config('services.groq.model'), $messages, $maxTokens, fn (string $m): array => $this->supportsSchema($m)
            ? ['type' => 'json_schema', 'json_schema' => ['name' => $name, 'strict' => true, 'schema' => $schema]]
            : ['type' => 'json_object']);

        $data = json_decode($reply, true);

        if (! is_array($data)) {
            throw new RuntimeException('The AI service returned an unreadable answer. Please try again.');
        }

        return $data;
    }

    /** @param  array<int, array{role: string, content: string}>  $messages */
    public function text(array $messages, int $maxTokens = 2500): string
    {
        return trim($this->dispatch((string) config('services.groq.model'), $messages, $maxTokens, fn () => null));
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  callable(string): (array<string, mixed>|null)  $format
     */
    private function dispatch(string $model, array $messages, int $maxTokens, callable $format): string
    {
        try {
            return $this->request($model, $messages, $maxTokens, $format($model));
        } catch (RequestException $e) {
            $fallback = (string) config('services.groq.fallback_model');

            if (! in_array($e->response->status(), [413, 429], true) || $fallback === '' || $fallback === $model) {
                throw new RuntimeException($this->friendly($e), previous: $e);
            }

            try {
                return $this->request($fallback, $messages, $maxTokens, $format($fallback));
            } catch (RequestException $second) {
                throw new RuntimeException($this->friendly($second), previous: $second);
            }
        }
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>|null  $format
     */
    private function request(string $model, array $messages, int $maxTokens, ?array $format): string
    {
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => (float) config('services.groq.temperature', 0.2),
            'max_completion_tokens' => $maxTokens,
        ];

        if ($format) {
            $payload['response_format'] = $format;
        }

        if ($this->supportsSchema($model)) {
            $payload['reasoning_effort'] = 'low';
        }

        $response = Http::withToken((string) config('services.groq.key'))
            ->timeout((int) config('services.groq.timeout', 60))
            ->acceptJson()
            ->post(rtrim((string) config('services.groq.base_url'), '/').'/chat/completions', $payload)
            ->throw();

        return (string) $response->json('choices.0.message.content', '');
    }

    private function supportsSchema(string $model): bool
    {
        return str_contains($model, 'gpt-oss');
    }

    private function friendly(RequestException $e): string
    {
        return match ($e->response->status()) {
            401, 403 => 'The AI key was rejected. Ask the site admin to check GROQ_API_KEY.',
            429, 413 => 'The AI service is busy right now. Wait a minute and try again.',
            default => 'The AI service could not be reached. Please try again.',
        };
    }
}
