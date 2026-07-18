<?php

namespace App\Services\Ai;

/**
 * Pure PHP cosine similarity for embedding vectors.
 */
final class CosineSimilarity
{
    /**
     * Compute cosine similarity clamped to [0, 1] for advisory display.
     *
     * @param  array<int, float|int>  $a
     * @param  array<int, float|int>  $b
     */
    public static function score(array $a, array $b): ?float
    {
        $dimA = count($a);
        $dimB = count($b);

        if ($dimA === 0 || $dimB === 0 || $dimA !== $dimB) {
            return null;
        }

        $dot = 0.0;
        $magA = 0.0;
        $magB = 0.0;

        for ($i = 0; $i < $dimA; $i++) {
            if (! is_numeric($a[$i]) || ! is_numeric($b[$i])) {
                return null;
            }

            $va = (float) $a[$i];
            $vb = (float) $b[$i];
            $dot += $va * $vb;
            $magA += $va * $va;
            $magB += $vb * $vb;
        }

        if ($magA <= 0.0 || $magB <= 0.0) {
            return null;
        }

        $cosine = $dot / (sqrt($magA) * sqrt($magB));

        if (! is_finite($cosine)) {
            return null;
        }

        return max(0.0, min(1.0, $cosine));
    }
}
