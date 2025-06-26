<?php

namespace Genericmilk\Sakura\Services;

interface AIProviderInterface
{
    /**
     * Generate test content using the AI provider
     *
     * @param string $prompt The prompt to send to the AI
     * @return array Response with 'content' and 'error' keys
     */
    public function generateTest(string $prompt): array;

    /**
     * Check if the provider is properly configured
     *
     * @return bool
     */
    public function isConfigured(): bool;

    /**
     * Get the provider name
     *
     * @return string
     */
    public function getName(): string;
} 