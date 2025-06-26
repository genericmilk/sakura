<?php

namespace Genericmilk\Sakura\Services;

use Gemini;

class GeminiProvider implements AIProviderInterface
{
    private array $config;
    private $client;

    public function __construct()
    {
        $this->config = config('sakura.gemini');
        $this->client = Gemini::client($this->config['api_key']);
    }

    public function generateTest(string $prompt): array
    {
        try {
            $result = $this->client->generativeModel(model: $this->config['model'])->generateContent($this->buildPrompt($prompt));
            
            $content = $result->text();

            return [
                'content' => $content,
                'error' => null,
            ];

        } catch (\Exception $e) {
            return [
                'content' => null,
                'error' => 'Gemini API Error: ' . $e->getMessage(),
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
        return 'Gemini';
    }

    /**
     * Get available Gemini models for validation
     */
    public function getAvailableModels(): array
    {
        return [
            'gemini-1.5-pro',
            'gemini-1.5-flash',
            'gemini-1.0-pro',
            'gemini-2.0-flash',
            'gemini-2.5-flash',
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
            $result = $this->client->generativeModel(model: $this->config['model'])->generateContent('Hello, this is a test message.');
            return !empty($result->text());
        } catch (\Exception $e) {
            return false;
        }
    }
} 