<?php

use App\Services\Ai\OllamaEmbeddingService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class);

beforeEach(function () {
    Config::set('ai.enabled', true);
    Config::set('ai.provider', 'ollama');
    Config::set('ai.base_url', 'http://ollama.test');
    Config::set('ai.embedding_model', 'nomic-embed-text');
    Config::set('ai.embedding_timeout', 45);
});

it('embeds a single input successfully', function () {
    Http::fake([
        'http://ollama.test/api/embed' => Http::response([
            'embeddings' => [[0.1, 0.2, 0.3]],
        ], 200),
    ]);

    $service = app(OllamaEmbeddingService::class);
    $result = $service->embed('Title: Campus Finder');

    expect($result)->toBe([[0.1, 0.2, 0.3]]);

    Http::assertSent(function ($request) {
        return $request->url() === 'http://ollama.test/api/embed'
            && $request['model'] === 'nomic-embed-text'
            && $request['input'] === 'Title: Campus Finder';
    });
});

it('embeds a batch of inputs successfully', function () {
    Http::fake([
        'http://ollama.test/api/embed' => Http::response([
            'embeddings' => [
                [1.0, 0.0],
                [0.0, 1.0],
            ],
        ], 200),
    ]);

    $service = app(OllamaEmbeddingService::class);
    $result = $service->embed(['first text', 'second text']);

    expect($result)->toHaveCount(2)
        ->and($result[0])->toBe([1.0, 0.0])
        ->and($result[1])->toBe([0.0, 1.0]);

    Http::assertSent(function ($request) {
        return $request['input'] === ['first text', 'second text'];
    });
});

it('returns null on connection failure', function () {
    Http::fake(function () {
        throw new ConnectionException('Connection refused');
    });

    $service = app(OllamaEmbeddingService::class);

    expect($service->embed('some proposal text'))->toBeNull();
});

it('returns null on non-2xx responses', function () {
    Http::fake([
        'http://ollama.test/api/embed' => Http::response(['error' => 'model missing'], 404),
    ]);

    $service = app(OllamaEmbeddingService::class);

    expect($service->embed('some proposal text'))->toBeNull();
});

it('returns null on malformed embedding payloads', function () {
    Http::fake([
        'http://ollama.test/api/embed' => Http::response([
            'embeddings' => 'not-an-array',
        ], 200),
    ]);

    $service = app(OllamaEmbeddingService::class);

    expect($service->embed('some proposal text'))->toBeNull();
});

it('returns null when AI is disabled', function () {
    Config::set('ai.enabled', false);
    Http::fake();

    $service = app(OllamaEmbeddingService::class);

    expect($service->embed('some proposal text'))->toBeNull();
    Http::assertNothingSent();
});
