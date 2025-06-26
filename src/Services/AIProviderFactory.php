<?php

namespace Genericmilk\Sakura\Services;

class AIProviderFactory
{
    public static function create(): AIProviderInterface
    {
        $provider = config('sakura.provider', 'openai');

        return match ($provider) {
            'openai' => new OpenAIProvider(),
            'claude' => new ClaudeProvider(),
            'gemini' => new GeminiProvider(),
            'ollama' => new OllamaProvider(),
            default => throw new \InvalidArgumentException("Unsupported AI provider: {$provider}"),
        };
    }
} 