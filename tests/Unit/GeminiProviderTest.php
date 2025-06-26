<?php

namespace Genericmilk\Robin\Tests\Unit;

use Genericmilk\Robin\Tests\TestCase;
use Genericmilk\Robin\Services\GeminiProvider;

class GeminiProviderTest extends TestCase
{
    private GeminiProvider $geminiProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->geminiProvider = new GeminiProvider();
    }

    public function test_it_has_correct_name()
    {
        $this->assertEquals('Gemini', $this->geminiProvider->getName());
    }

    public function test_it_returns_available_models()
    {
        $models = $this->geminiProvider->getAvailableModels();
        
        $this->assertIsArray($models);
        $this->assertContains('gemini-1.5-pro', $models);
        $this->assertContains('gemini-1.5-flash', $models);
        $this->assertContains('gemini-1.0-pro', $models);
    }

    public function test_it_validates_model_availability()
    {
        config(['robin.gemini.model' => 'gemini-1.5-pro']);
        
        $this->assertTrue($this->geminiProvider->isModelAvailable());
    }

    public function test_it_rejects_invalid_model()
    {
        config(['robin.gemini.model' => 'invalid-model']);
        
        $this->assertFalse($this->geminiProvider->isModelAvailable());
    }

    public function test_it_requires_api_key_for_configuration()
    {
        config(['robin.gemini.api_key' => '']);
        
        $this->assertFalse($this->geminiProvider->isConfigured());
    }

    public function test_it_is_configured_with_api_key()
    {
        config(['robin.gemini.api_key' => 'test-key']);
        
        $this->assertTrue($this->geminiProvider->isConfigured());
    }

    public function test_it_has_connection_test_method()
    {
        $this->assertTrue(method_exists($this->geminiProvider, 'testConnection'));
    }
} 