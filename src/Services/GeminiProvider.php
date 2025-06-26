<?php

namespace Genericmilk\Sakura\Services;

use Gemini;

class GeminiProvider implements AIProviderInterface
{
    private array $config;
    private $client;

    public function __construct()
    {
        // Remove config caching and client initialization from constructor
    }

    private function getConfig(): array
    {
        return config('sakura.gemini') ?? [];
    }

    private function getClient()
    {
        $config = $this->getConfig();
        if (empty($config['api_key'])) {
            return null;
        }
        
        // Initialize client on demand
        return Gemini::client($config['api_key']);
    }

    public function generateTest(string $prompt): array
    {
        $client = $this->getClient();
        if (!$client) {
            return [
                'content' => null,
                'error' => 'Gemini API client not initialized. Please check your API key configuration.',
            ];
        }

        try {
            $config = $this->getConfig();
            $result = $client->generativeModel(model: $config['model'])->generateContent($this->buildPrompt($prompt));
            
            $content = $result->text();
            
            // Strip markdown code blocks
            $content = $this->stripMarkdownCodeBlocks($content);

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
        $config = $this->getConfig();
        return !empty($config['api_key']);
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
        $config = $this->getConfig();
        if (empty($config['model'])) {
            return false;
        }
        
        $availableModels = $this->getAvailableModels();
        return in_array($config['model'], $availableModels);
    }

    /**
     * Test the API connection
     */
    public function testConnection(): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            $config = $this->getConfig();
            $result = $client->generativeModel(model: $config['model'])->generateContent('Hello, this is a test message.');
            return !empty($result->text());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Strip markdown code blocks from the response
     */
    private function stripMarkdownCodeBlocks(string $content): string
    {
        // Remove opening code blocks (```php, ```, etc.)
        $content = preg_replace('/^```[a-zA-Z]*\n?/m', '', $content);
        
        // Remove closing code blocks
        $content = preg_replace('/\n?```$/m', '', $content);
        
        // Clean up any remaining backticks at start/end
        $content = trim($content, '`');
        
        // Clean up extra whitespace
        $content = trim($content);
        
        return $content;
    }
} 