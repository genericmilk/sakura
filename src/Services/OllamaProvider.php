<?php

namespace Genericmilk\Sakura\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class OllamaProvider implements AIProviderInterface
{
    private array $config;
    private Client $client;

    public function __construct()
    {
        $this->config = config('sakura.ollama');
        $this->client = new Client([
            'base_uri' => $this->config['base_url'],
            'timeout' => $this->config['timeout'],
        ]);
    }

    public function generateTest(string $prompt): array
    {
        try {
            $response = $this->client->post('/api/generate', [
                'json' => [
                    'model' => $this->config['model'],
                    'prompt' => $this->buildPrompt($prompt),
                    'stream' => false,
                    'options' => [
                        'temperature' => $this->config['temperature'],
                        'num_predict' => $this->config['max_tokens'],
                    ],
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['response'])) {
                return [
                    'content' => null,
                    'error' => 'Invalid response from Ollama API',
                ];
            }

            return [
                'content' => $data['response'],
                'error' => null,
            ];

        } catch (RequestException $e) {
            return [
                'content' => null,
                'error' => 'Ollama API Error: ' . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'content' => null,
                'error' => 'Ollama Error: ' . $e->getMessage(),
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
        return !empty($this->config['base_url']) && !empty($this->config['model']);
    }

    public function getName(): string
    {
        return 'Ollama';
    }

    /**
     * Check if the specified model is available
     */
    public function isModelAvailable(): bool
    {
        try {
            $response = $this->client->get('/api/tags');
            $data = json_decode($response->getBody()->getContents(), true);
            
            if (!isset($data['models'])) {
                return false;
            }
            
            foreach ($data['models'] as $model) {
                if ($model['name'] === $this->config['model']) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Test the API connection
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->client->get('/api/tags');
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }
} 