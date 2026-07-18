<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OllamaEmbeddingService
{
    /**
     * Embed one or more texts via local Ollama /api/embed.
     *
     * @param  string|array<int, string>  $input
     * @return array<int, array<int, float>>|null
     */
    public function embed(string|array $input): ?array
    {
        if (! $this->shouldUseAi()) {
            return null;
        }

        $texts = $this->normalizeInput($input);
        if ($texts === null) {
            return null;
        }

        $baseUrl = rtrim((string) config('ai.base_url'), '/');
        $timeout = max(1, (int) config('ai.embedding_timeout', 45));
        $model = (string) config('ai.embedding_model', 'nomic-embed-text');
        $url = "{$baseUrl}/api/embed";

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->post($url, [
                    'model' => $model,
                    'input' => count($texts) === 1 ? $texts[0] : $texts,
                ]);
        } catch (ConnectionException $exception) {
            $this->logFailure('connection', $exception->getMessage());

            return null;
        } catch (Throwable $exception) {
            $this->logFailure('request', $exception->getMessage());

            return null;
        }

        if (! $response->successful()) {
            $this->logFailure('http_error', 'status '.$response->status());

            return null;
        }

        $embeddings = data_get($response->json(), 'embeddings');

        return $this->validateEmbeddings($embeddings, count($texts));
    }

    public function shouldUseAi(): bool
    {
        if (! filter_var(config('ai.enabled'), FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        return strtolower(trim((string) config('ai.provider'))) === 'ollama';
    }

    /**
     * @param  string|array<int, string>  $input
     * @return array<int, string>|null
     */
    private function normalizeInput(string|array $input): ?array
    {
        if (is_string($input)) {
            $text = trim($input);

            return $text === '' ? null : [$text];
        }

        $texts = [];
        foreach ($input as $item) {
            if (! is_string($item)) {
                return null;
            }

            $text = trim($item);
            if ($text === '') {
                return null;
            }

            $texts[] = $text;
        }

        return $texts === [] ? null : array_values($texts);
    }

    /**
     * @return array<int, array<int, float>>|null
     */
    private function validateEmbeddings(mixed $embeddings, int $expectedCount): ?array
    {
        if (! is_array($embeddings) || count($embeddings) !== $expectedCount) {
            $this->logFailure('malformed', 'embeddings count mismatch');

            return null;
        }

        $validated = [];

        foreach ($embeddings as $vector) {
            if (! is_array($vector) || $vector === []) {
                $this->logFailure('malformed', 'empty or non-array vector');

                return null;
            }

            $floats = [];
            foreach ($vector as $value) {
                if (! is_numeric($value)) {
                    $this->logFailure('malformed', 'non-numeric embedding value');

                    return null;
                }

                $floats[] = (float) $value;
            }

            $validated[] = $floats;
        }

        return $validated;
    }

    private function logFailure(string $reason, string $detail): void
    {
        Log::warning('AI embedding service failure', [
            'reason' => $reason,
            'detail' => $detail,
            'base_url' => config('ai.base_url'),
            'model' => config('ai.embedding_model'),
            'timeout' => config('ai.embedding_timeout'),
        ]);
    }
}
