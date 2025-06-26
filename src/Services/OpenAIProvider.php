<?php

namespace Genericmilk\Sakura\Services;

use OpenAI;

class OpenAIProvider implements AIProviderInterface
{
    private array $config;
    private $client;

    public function __construct()
    {
        $this->config = config('sakura.openai');
        $this->client = OpenAI::client($this->config['api_key']);
    }

    public function generateTest(string $prompt): array
    {
        try {
            $response = $this->client->chat()->create([
                'model' => $this->config['model'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert PHP developer and testing specialist. Generate comprehensive, well-structured tests for the provided PHP code. Return only the test code, no explanations. Focus on creating high-quality, maintainable tests that follow Laravel best practices and PHP testing conventions.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => $this->config['max_tokens'],
                'temperature' => $this->config['temperature'],
            ]);

            $content = $response->choices[0]->message->content;
            
            // Strip markdown code blocks
            $content = $this->stripMarkdownCodeBlocks($content);

            return [
                'content' => $content,
                'error' => null,
            ];

        } catch (\Exception $e) {
            return [
                'content' => null,
                'error' => 'OpenAI API Error: ' . $e->getMessage(),
            ];
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

    public function isConfigured(): bool
    {
        return !empty($this->config['api_key']);
    }

    public function getName(): string
    {
        return 'OpenAI';
    }
} 