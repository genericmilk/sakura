<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which AI provider to use for test generation.
    |
    */
    'provider' => env('SAKURA_AI_PROVIDER', 'openai'), // openai, claude, gemini, ollama

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your OpenAI API settings for test generation.
    |
    */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('SAKURA_OPENAI_MODEL', 'gpt-4o-mini'),
        'max_tokens' => env('SAKURA_MAX_TOKENS', 4000),
        'temperature' => env('SAKURA_TEMPERATURE', 0.3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Claude Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Anthropic Claude API settings for test generation.
    |
    */
    'claude' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('SAKURA_CLAUDE_MODEL', 'claude-3-5-sonnet-20241022'),
        'max_tokens' => env('SAKURA_CLAUDE_MAX_TOKENS', 4000),
        'temperature' => env('SAKURA_CLAUDE_TEMPERATURE', 0.3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Gemini Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Google Gemini API settings for test generation.
    |
    */
    'gemini' => [
        'api_key' => env('GOOGLE_AI_API_KEY'),
        'model' => env('SAKURA_GEMINI_MODEL', 'gemini-1.5-pro'),
        'max_tokens' => env('SAKURA_GEMINI_MAX_TOKENS', 4000),
        'temperature' => env('SAKURA_GEMINI_TEMPERATURE', 0.3),
        'timeout' => env('SAKURA_GEMINI_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ollama Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Ollama settings for local test generation.
    |
    */
    'ollama' => [
        'base_url' => env('SAKURA_OLLAMA_BASE_URL', 'http://localhost:11434'),
        'model' => env('SAKURA_OLLAMA_MODEL', 'codellama'),
        'max_tokens' => env('SAKURA_OLLAMA_MAX_TOKENS', 4000),
        'temperature' => env('SAKURA_OLLAMA_TEMPERATURE', 0.3),
        'timeout' => env('SAKURA_OLLAMA_TIMEOUT', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Code Analysis Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which directories and file types to analyze.
    |
    */
    'analysis' => [
        'directories' => [
            'app/Http/Controllers',
            'app/Services',
            'app/Actions',
            'app/Enums',
            'app/Console/Commands',
            'app/Models',
            'app/Repositories',
            'app/Jobs',
            'app/Listeners',
            'app/Notifications',
        ],
        'file_extensions' => ['php'],
        'exclude_patterns' => [
            '*Test.php',
            '*Tests.php',
            'TestCase.php',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Generation Configuration
    |--------------------------------------------------------------------------
    |
    | Configure test generation settings.
    |
    */
    'testing' => [
        'framework' => env('SAKURA_TEST_FRAMEWORK', 'auto'), // auto, pest, phpunit
        'test_directory' => env('SAKURA_TEST_DIRECTORY', 'tests'),
        'generate_feature_tests' => env('SAKURA_GENERATE_FEATURE_TESTS', true),
        'generate_unit_tests' => env('SAKURA_GENERATE_UNIT_TESTS', true),
        'max_tests_per_class' => env('SAKURA_MAX_TESTS_PER_CLASS', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configure where Sakura stores its data.
    |
    */
    'storage' => [
        'directory' => '.sakura',
        'code_tree_file' => 'code-tree.json',
        'cache_file' => 'cache.json',
    ],
]; 