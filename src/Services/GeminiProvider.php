<?php

namespace Genericmilk\Sakura\Services;

use Google\AI\GenerativeLanguage\V1beta\GenerativeServiceClient;
use Google\AI\GenerativeLanguage\V1beta\GenerateContentRequest;
use Google\AI\GenerativeLanguage\V1beta\Content;
use Google\AI\GenerativeLanguage\V1beta\Part;
use Google\AI\GenerativeLanguage\V1beta\GenerationConfig;
use Google\ApiCore\ApiException;

class GeminiProvider implements AIProviderInterface
{
    private array $config;
    private GenerativeServiceClient $client;

    public function __construct()
    {
        $this->config = config('sakura.gemini');
        $this->client = new GenerativeServiceClient([
            'credentials' => null,
            'apiKey' => $this->config['api_key'],
        ]);
    }

    public function generateTest(string $prompt): array
    {
        try {
            $generationConfig = new GenerationConfig([
                'temperature' => $this->config['temperature'],
                'max_output_tokens' => $this->config['max_tokens'],
            ]);

            $content = new Content([
                'parts' => [
                    new Part([
                        'text' => $this->buildPrompt($prompt)
                    ])
                ]
            ]);

            $request = new GenerateContentRequest([
                'model' => 'models/' . $this->config['model'],
                'contents' => [$content],
                'generation_config' => $generationConfig,
            ]);

            $response = $this->client->generateContent($request);
            $candidates = $response->getCandidates();

            if (empty($candidates)) {
                return [
                    'content' => null,
                    'error' => 'No response generated from Gemini API',
                ];
            }

            $firstCandidate = $candidates[0];
            $content = $firstCandidate->getContent();
            $parts = $content->getParts();

            if (empty($parts)) {
                return [
                    'content' => null,
                    'error' => 'No content parts in Gemini response',
                ];
            }

            $text = $parts[0]->getText();

            return [
                'content' => $text,
                'error' => null,
            ];

        } catch (ApiException $e) {
            return [
                'content' => null,
                'error' => 'Gemini API Error: ' . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'content' => null,
                'error' => 'Gemini Error: ' . $e->getMessage(),
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
            'gemini-1.0-pro-vision',
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
            $content = new Content([
                'parts' => [
                    new Part([
                        'text' => 'Hello, this is a test message.'
                    ])
                ]
            ]);

            $request = new GenerateContentRequest([
                'model' => 'models/' . $this->config['model'],
                'contents' => [$content],
            ]);

            $response = $this->client->generateContent($request);
            return !empty($response->getCandidates());
        } catch (\Exception $e) {
            return false;
        }
    }
} 