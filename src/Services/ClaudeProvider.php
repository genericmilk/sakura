<?php

namespace Genericmilk\Sakura\Services;

use WpAi\Anthropic\AnthropicAPI;

class ClaudeProvider implements AIProviderInterface
{
    private array $config;
    private AnthropicAPI $client;

    public function __construct()
    {
        $this->config = config('sakura.claude');
        $this->client = new AnthropicAPI($this->config['api_key']);
    }

    public function generateTest(string $prompt): array
    {
        try {
            $response = $this->client->messages()->create([
                'model' => $this->config['model'],
                'maxTokens' => $this->config['max_tokens'],
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $this->buildPrompt($prompt)
                    ]
                ]
            ]);

            $content = $response['content'][0]['text'] ?? '';

            return [
                'content' => $content,
                'error' => null,
            ];

        } catch (\Exception $e) {
            return [
                'content' => null,
                'error' => 'Claude API Error: ' . $e->getMessage(),
            ];
        }
    }

    private function buildPrompt(string $userPrompt): string
    {
        return "You are an expert PHP developer and testing specialist. Generate comprehensive, well-structured tests for the provided PHP code.

{$userPrompt}

Return only the test code, no explanations. Focus on creating high-quality, maintainable tests that follow Laravel best practices and PHP testing conventions.";
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['api_key']);
    }

    public function getName(): string
    {
        return 'Claude';
    }

    /**
     * Get available Claude models for validation
     */
    public function getAvailableModels(): array
    {
        return [
            'claude-3-5-sonnet-20241022',
            'claude-3-5-haiku-20241022',
            'claude-3-opus-20240229',
            'claude-3-sonnet-20240229',
            'claude-3-haiku-20240307',
        ];
    }

    /**
     * Check if the specified model is available
     */
    public function isModelAvailable(): bool
    {
        $availableModels = $this->getAvailableModels();
        return in_array($this->config['model'], $availableModels);
    }

    /**
     * Test the API connection
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->client->messages()->create([
                'model' => $this->config['model'],
                'maxTokens' => 10,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Hello, this is a test message.'
                    ]
                ]
            ]);
            
            return !empty($response['content']);
        } catch (\Exception $e) {
            return false;
        }
    }
} 