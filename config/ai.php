<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Local AI assistant (Ollama)
    |--------------------------------------------------------------------------
    |
    | Advisory-only project proposal assistance. When disabled or unreachable,
    | the app returns a structured fallback suggestion. Suggestions are never
    | persisted and never submit or approve ideas.
    |
    */

    'enabled' => (bool) env('AI_ENABLED', false),

    'provider' => env('AI_PROVIDER', 'ollama'),

    'base_url' => env('AI_BASE_URL', 'http://localhost:11434'),

    'model' => env('AI_MODEL', 'llama3.2:3b'),

    // Cold start after embedding-model switches can exceed 60s on a laptop.
    'timeout' => (int) env('AI_TIMEOUT', 90),

    // Keep the chat model loaded in Ollama between requests (e.g. after similarity embeds).
    'keep_alive' => env('AI_KEEP_ALIVE', '10m'),

    'max_input_chars' => (int) env('AI_MAX_INPUT_CHARS', 2000),

    /*
    |--------------------------------------------------------------------------
    | Local semantic similarity (Ollama embeddings)
    |--------------------------------------------------------------------------
    |
    | Advisory-only project similarity checking. Embeddings are computed on
    | request and are never persisted. Results never block idea submission.
    |
    */

    'embedding_model' => env('AI_EMBEDDING_MODEL', 'nomic-embed-text'),

    'embedding_timeout' => (int) env('AI_EMBEDDING_TIMEOUT', 45),

    'similarity_min_score' => (float) env('AI_SIMILARITY_MIN_SCORE', 0.66),

    'similarity_high_score' => (float) env('AI_SIMILARITY_HIGH_SCORE', 0.78),

    'similarity_top_n' => (int) env('AI_SIMILARITY_TOP_N', 5),

];
