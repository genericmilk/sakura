<?php

namespace Genericmilk\Sakura\Tests\Unit;

use Genericmilk\Sakura\Tests\TestCase;
use Genericmilk\Sakura\Services\ClaudeProvider;

class ClaudeProviderTest extends TestCase
{
    private ClaudeProvider $claudeProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->claudeProvider = new ClaudeProvider();
    }

    public function test_it_has_correct_name()
    {
        $this->assertEquals('Claude', $this->claudeProvider->getName());
    }

    public function test_it_returns_available_models()
    {
        $models = $this->claudeProvider->getAvailableModels();
        
        $this->assertIsArray($models);
        $this->assertContains('claude-3-5-sonnet-20241022', $models);
        $this->assertContains('claude-3-5-haiku-20241022', $models);
        $this->assertContains('claude-3-opus-20240229', $models);
    }

    public function test_it_validates_model_availability()
    {
        config(['sakura.claude.model' => 'claude-3-5-sonnet-20241022']);
        
        $this->assertTrue($this->claudeProvider->isModelAvailable());
    }

    public function test_it_rejects_invalid_model()
    {
        config(['sakura.claude.model' => 'invalid-model']);
        
        $this->assertFalse($this->claudeProvider->isModelAvailable());
    }

    public function test_it_requires_api_key_for_configuration()
    {
        config(['sakura.claude.api_key' => '']);
        
        $this->assertFalse($this->claudeProvider->isConfigured());
    }

    public function test_it_is_configured_with_api_key()
    {
        config(['sakura.claude.api_key' => 'test-key']);
        
        $this->assertTrue($this->claudeProvider->isConfigured());
    }
} 