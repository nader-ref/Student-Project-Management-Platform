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

    'timeout' => (int) env('AI_TIMEOUT', 60),

    'max_input_chars' => (int) env('AI_MAX_INPUT_CHARS', 2000),

];
