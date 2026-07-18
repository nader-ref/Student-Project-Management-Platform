<?php

use App\Services\Ai\CosineSimilarity;

it('returns approximately 1 for identical vectors', function () {
    $vector = [1.0, 0.0, 0.0];

    expect(CosineSimilarity::score($vector, $vector))->toEqualWithDelta(1.0, 0.0001);
});

it('returns approximately 0 for orthogonal vectors', function () {
    $a = [1.0, 0.0];
    $b = [0.0, 1.0];

    expect(CosineSimilarity::score($a, $b))->toEqualWithDelta(0.0, 0.0001);
});

it('returns null for dimension mismatch', function () {
    expect(CosineSimilarity::score([1.0, 0.0], [1.0, 0.0, 0.0]))->toBeNull();
});

it('returns null for zero vectors', function () {
    expect(CosineSimilarity::score([0.0, 0.0], [1.0, 0.0]))->toBeNull();
    expect(CosineSimilarity::score([1.0, 0.0], [0.0, 0.0]))->toBeNull();
});

it('clamps scores into the 0..1 range', function () {
    // Numerically noisy near-identical directions still clamp to 1.
    $a = [0.6, 0.8];
    $b = [0.6, 0.8];

    $score = CosineSimilarity::score($a, $b);

    expect($score)->not->toBeNull()
        ->and($score)->toBeGreaterThanOrEqual(0.0)
        ->and($score)->toBeLessThanOrEqual(1.0);
});
