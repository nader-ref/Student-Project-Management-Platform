<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ProjectProposalAssistantService
{
    public const DISCLAIMER = 'AI suggestions are advisory only. They do not submit your idea, notify supervisors, or guarantee acceptance. Review and edit before submitting.';

    public const FALLBACK_NOTICE = 'Ollama was unavailable, so a starter template was used.';

    private const SYSTEM_PROMPT = 'Return valid JSON only with title, problem_statement, objectives, scope, functional_requirements.';

    private ?string $lastDiagnostic = null;

    /**
     * @return array{ok: bool, mode: string, disclaimer: string, suggestion: array{title: string, problem_statement: string, objectives: array<int, string>, scope: string, functional_requirements: array<int, string>}, diagnostic?: string, notice?: string}
     */
    public function suggest(string $rawIdea): array
    {
        $this->lastDiagnostic = null;
        $rawIdea = trim($rawIdea);
        $maxChars = (int) config('ai.max_input_chars', 2000);

        if (mb_strlen($rawIdea) > $maxChars) {
            $rawIdea = mb_substr($rawIdea, 0, $maxChars);
        }

        if (! $this->shouldUseAi()) {
            $this->lastDiagnostic = 'disabled_or_unsupported_provider';
            $this->logOutcome('fallback', $this->lastDiagnostic);

            return $this->response('fallback', $this->buildFallbackSuggestion($rawIdea));
        }

        try {
            $suggestion = $this->callOllama($rawIdea);

            if ($suggestion === null) {
                $this->lastDiagnostic ??= 'normalization_error';
                $this->logOutcome('fallback', $this->lastDiagnostic);
                Log::warning('AI proposal assistant fallback without exception', [
                    'diagnostic' => $this->lastDiagnostic,
                    'fallback_line' => 'suggest:callOllama returned null',
                    'input_length' => mb_strlen($rawIdea),
                ]);

                return $this->response('fallback', $this->buildFallbackSuggestion($rawIdea));
            }

            $this->lastDiagnostic = 'ok';
            $this->logOutcome('ai', 'ok');

            return $this->response('ai', $suggestion);
        } catch (Throwable $exception) {
            $this->lastDiagnostic ??= $this->mapExceptionDiagnostic($exception);
            $this->logOutcome('fallback', $this->lastDiagnostic);
            Log::warning('AI proposal assistant unavailable', [
                'reason' => class_basename($exception),
                'exception_class' => $exception::class,
                'exception_message' => $exception->getMessage(),
                'diagnostic' => $this->lastDiagnostic,
                'fallback_line' => 'suggest:catch after callOllama',
                'input_length' => mb_strlen($rawIdea),
            ]);

            return $this->response('fallback', $this->buildFallbackSuggestion($rawIdea));
        }
    }

    /**
     * Local Ollama only. Other providers fall back without calling a remote API.
     */
    public function shouldUseAi(): bool
    {
        if (! filter_var(config('ai.enabled'), FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        return strtolower(trim((string) config('ai.provider'))) === 'ollama';
    }

    /**
     * @param  array{title: string, problem_statement: string, objectives: array<int, string>, scope: string, functional_requirements: array<int, string>}  $suggestion
     * @return array{ok: bool, mode: string, disclaimer: string, suggestion: array{title: string, problem_statement: string, objectives: array<int, string>, scope: string, functional_requirements: array<int, string>}, diagnostic?: string, notice?: string}
     */
    private function response(string $mode, array $suggestion): array
    {
        $payload = [
            'ok' => true,
            'mode' => $mode,
            'disclaimer' => self::DISCLAIMER,
            'suggestion' => $suggestion,
        ];

        if ($mode === 'fallback') {
            $payload['notice'] = self::FALLBACK_NOTICE;
        }

        if (app()->environment(['local', 'testing']) && $this->lastDiagnostic !== null) {
            $payload['diagnostic'] = $this->lastDiagnostic;
        }

        return $payload;
    }

    /**
     * Exposed for local diagnosis scripts only.
     */
    public function lastDiagnostic(): ?string
    {
        return $this->lastDiagnostic;
    }

    /**
     * @return array{title: string, problem_statement: string, objectives: array<int, string>, scope: string, functional_requirements: array<int, string>}|null
     */
    private function callOllama(string $rawIdea): ?array
    {
        $baseUrl = rtrim((string) config('ai.base_url'), '/');
        $timeout = max(1, (int) config('ai.timeout', 60));
        $url = "{$baseUrl}/api/chat";
        $startedAt = microtime(true);
        $response = null;

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->post($url, [
                    'model' => (string) config('ai.model'),
                    'stream' => false,
                    'format' => 'json',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => self::SYSTEM_PROMPT,
                        ],
                        [
                            'role' => 'user',
                            'content' => 'Raw graduation project idea: '.$rawIdea,
                        ],
                    ],
                ]);
        } catch (Throwable $exception) {
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $this->lastDiagnostic = $this->mapExceptionDiagnostic($exception);
            $this->logOllamaFailure(
                exception: $exception,
                durationMs: $durationMs,
                httpStatus: null,
                responseBodyPreview: null,
                fallbackLine: 'callOllama:catch(Http::post)',
            );

            throw $exception;
        }

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $bodyPreview = Str::limit((string) $response->body(), 500, '');

        if (! $response->successful()) {
            $this->lastDiagnostic = 'http_error';
            $httpException = new \RuntimeException('Ollama request failed with status '.$response->status());
            $this->logOllamaFailure(
                exception: $httpException,
                durationMs: $durationMs,
                httpStatus: $response->status(),
                responseBodyPreview: $bodyPreview,
                fallbackLine: 'callOllama:non_2xx_response',
            );

            throw $httpException;
        }

        Log::info('AI proposal assistant ollama http ok', [
            'duration_ms' => $durationMs,
            'http_status' => $response->status(),
            'body_preview' => $bodyPreview,
        ]);

        $content = data_get($response->json(), 'message.content');

        if (is_array($content)) {
            $normalized = $this->normalizeSuggestion($content);
            if ($normalized === null) {
                $this->lastDiagnostic = 'normalization_error';
                $this->logOllamaFailure(
                    exception: new \RuntimeException('normalizeSuggestion rejected array message.content'),
                    durationMs: $durationMs,
                    httpStatus: $response->status(),
                    responseBodyPreview: $bodyPreview,
                    fallbackLine: 'callOllama:normalizeSuggestion(array content) returned null',
                );
            }

            return $normalized;
        }

        if (! is_string($content) || trim($content) === '') {
            $this->lastDiagnostic = 'json_error';
            $this->logOllamaFailure(
                exception: new \RuntimeException('message.content missing or empty'),
                durationMs: $durationMs,
                httpStatus: $response->status(),
                responseBodyPreview: $bodyPreview,
                fallbackLine: 'callOllama:missing message.content',
            );

            return null;
        }

        $decoded = $this->decodeJsonContent($content);

        if ($decoded === null) {
            $this->lastDiagnostic = 'json_error';
            $this->logOllamaFailure(
                exception: new \RuntimeException('message.content json_decode failed'),
                durationMs: $durationMs,
                httpStatus: $response->status(),
                responseBodyPreview: Str::limit($content, 500, ''),
                fallbackLine: 'callOllama:decodeJsonContent returned null',
            );

            return null;
        }

        $normalized = $this->normalizeSuggestion($decoded);
        if ($normalized === null) {
            $this->lastDiagnostic = 'normalization_error';
            $this->logOllamaFailure(
                exception: new \RuntimeException('normalizeSuggestion rejected decoded payload'),
                durationMs: $durationMs,
                httpStatus: $response->status(),
                responseBodyPreview: Str::limit($content, 500, ''),
                fallbackLine: 'callOllama:normalizeSuggestion(decoded) returned null',
                extra: [
                    'decoded_keys' => array_keys($decoded),
                    'title_type' => gettype($decoded['title'] ?? null),
                    'problem_statement_type' => gettype($decoded['problem_statement'] ?? null),
                    'scope_type' => gettype($decoded['scope'] ?? null),
                    'objectives_type' => gettype($decoded['objectives'] ?? null),
                    'functional_requirements_type' => gettype($decoded['functional_requirements'] ?? null),
                ],
            );
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function logOllamaFailure(
        Throwable $exception,
        int $durationMs,
        ?int $httpStatus,
        ?string $responseBodyPreview,
        string $fallbackLine,
        array $extra = [],
    ): void {
        Log::warning('AI proposal assistant ollama failure detail', array_merge([
            'diagnostic' => $this->lastDiagnostic,
            'fallback_line' => $fallbackLine,
            'exception_class' => $exception::class,
            'exception_message' => $exception->getMessage(),
            'http_status' => $httpStatus,
            'response_body_preview' => $responseBodyPreview,
            'duration_ms' => $durationMs,
            'base_url' => config('ai.base_url'),
            'model' => config('ai.model'),
            'timeout' => config('ai.timeout'),
        ], $extra));
    }

    private function mapExceptionDiagnostic(Throwable $exception): string
    {
        $message = strtolower($exception->getMessage());

        if (str_contains($message, 'timed out')
            || str_contains($message, 'timeout')
            || str_contains($message, 'curl error 28')) {
            return 'timeout';
        }

        if (str_contains($message, 'connection refused')
            || str_contains($message, 'failed to connect')
            || str_contains($message, 'curl error 7')) {
            return 'connection_refused';
        }

        if (str_contains($message, 'could not resolve')
            || str_contains($message, 'name or service not known')
            || str_contains($message, 'curl error 6')) {
            return 'dns_error';
        }

        if (str_contains($message, 'ssl')
            || str_contains($message, 'certificate')
            || str_contains($message, 'curl error 35')
            || str_contains($message, 'curl error 60')) {
            return 'ssl_error';
        }

        if ($exception instanceof ConnectionException) {
            return 'connection_refused';
        }

        if ($exception instanceof RequestException) {
            return 'http_error';
        }

        return 'unknown';
    }

    /**
     * Decode Ollama message.content, stripping optional markdown fences.
     *
     * @return array<string, mixed>|null
     */
    private function decodeJsonContent(string $content): ?array
    {
        $content = trim($content);
        $content = $this->stripMarkdownFences($content);

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function stripMarkdownFences(string $content): string
    {
        $content = trim($content);

        if (preg_match('/^```(?:json)?\s*([\s\S]*?)\s*```$/i', $content, $matches)) {
            return trim($matches[1]);
        }

        return $content;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{title: string, problem_statement: string, objectives: array<int, string>, scope: string, functional_requirements: array<int, string>}|null
     */
    private function normalizeSuggestion(array $payload): ?array
    {
        $title = $this->asNonEmptyText($payload['title'] ?? null);
        $problemStatement = $this->asNonEmptyText($payload['problem_statement'] ?? null);
        $scope = $this->asNonEmptyText($payload['scope'] ?? null);
        $objectives = $this->asStringList($payload['objectives'] ?? null);
        $requirements = $this->asStringList($payload['functional_requirements'] ?? null);

        if ($title === null || $problemStatement === null || $scope === null) {
            return null;
        }

        if ($objectives === [] || $requirements === []) {
            return null;
        }

        return [
            'title' => $title,
            'problem_statement' => $problemStatement,
            'objectives' => $objectives,
            'scope' => $scope,
            'functional_requirements' => $requirements,
        ];
    }

    /**
     * @return array{title: string, problem_statement: string, objectives: array<int, string>, scope: string, functional_requirements: array<int, string>}
     */
    private function buildFallbackSuggestion(string $rawIdea): array
    {
        $title = Str::limit(Str::of($rawIdea)->replaceMatches('/\s+/', ' ')->trim()->toString(), 80, '');
        if ($title === '') {
            $title = 'Untitled project idea';
        }

        return [
            'title' => $title,
            'problem_statement' => 'Students and stakeholders currently lack a clear, structured solution for: '.$title.'. A graduation project can clarify the problem, propose a practical approach, and demonstrate a working prototype.',
            'objectives' => [
                'Clarify the core problem and intended users.',
                'Design a practical solution aligned with the stated idea.',
                'Implement and evaluate a working prototype against the objectives.',
            ],
            'scope' => 'In scope: core features needed to demonstrate the idea as a graduation project. Out of scope: unrelated modules, production-scale infrastructure, and features beyond the stated concept.',
            'functional_requirements' => [
                'Allow users to access the primary workflow described in the idea.',
                'Capture and display the key data needed for the proposed solution.',
                'Provide basic validation and clear feedback for main user actions.',
                'Support a simple role or access model appropriate to the project.',
            ],
        ];
    }

    /**
     * Accept string or list-of-strings fields from local models.
     */
    private function asNonEmptyText(mixed $value): ?string
    {
        if (is_string($value)) {
            $value = trim($value);

            return $value !== '' ? $value : null;
        }

        if (is_numeric($value)) {
            return trim((string) $value);
        }

        if (is_array($value)) {
            $parts = $this->asStringList($value);

            return $parts === [] ? null : implode(' ', $parts);
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function asStringList(mixed $value): array
    {
        if (is_string($value)) {
            $parts = preg_split('/\r\n|\r|\n|;/', $value) ?: [];
            $value = $parts;
        }

        if (! is_array($value)) {
            return [];
        }

        $items = [];

        foreach ($value as $item) {
            if (is_array($item)) {
                $nested = $this->asStringList($item);
                foreach ($nested as $nestedItem) {
                    $items[] = $nestedItem;
                }

                continue;
            }

            if (! is_string($item) && ! is_numeric($item)) {
                continue;
            }

            $text = trim((string) $item);
            if ($text !== '') {
                $items[] = $text;
            }
        }

        return array_values($items);
    }

    private function logOutcome(string $mode, string $reason): void
    {
        Log::info('AI proposal assistant outcome', [
            'mode' => $mode,
            'reason' => $reason,
        ]);
    }
}
