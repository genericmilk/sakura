<?php

namespace Genericmilk\Robin\Tests\Unit;

use Genericmilk\Robin\Tests\TestCase;
use Genericmilk\Robin\Services\AIProviderFactory;
use Genericmilk\Robin\Services\AIProviderInterface;
use Genericmilk\Robin\Services\OpenAIProvider;
use Genericmilk\Robin\Services\ClaudeProvider;
use Genericmilk\Robin\Services\GeminiProvider;
use Genericmilk\Robin\Services\OllamaProvider;

class AIProviderFactoryTest extends TestCase
{
    public function test_it_creates_openai_provider_by_default()
    {
        config(['robin.provider' => 'openai']);
        
        $provider = AIProviderFactory::create();
        
        $this->assertInstanceOf(OpenAIProvider::class, $provider);
        $this->assertInstanceOf(AIProviderInterface::class, $provider);
    }

    public function test_it_creates_claude_provider_when_configured()
    {
        config(['robin.provider' => 'claude']);
        
        $provider = AIProviderFactory::create();
        
        $this->assertInstanceOf(ClaudeProvider::class, $provider);
        $this->assertInstanceOf(AIProviderInterface::class, $provider);
    }

    public function test_it_creates_gemini_provider_when_configured()
    {
        config(['robin.provider' => 'gemini']);
        
        $provider = AIProviderFactory::create();
        
        $this->assertInstanceOf(GeminiProvider::class, $provider);
        $this->assertInstanceOf(AIProviderInterface::class, $provider);
    }

    public function test_it_creates_ollama_provider_when_configured()
    {
        config(['robin.provider' => 'ollama']);
        
        $provider = AIProviderFactory::create();
        
        $this->assertInstanceOf(OllamaProvider::class, $provider);
        $this->assertInstanceOf(AIProviderInterface::class, $provider);
    }

    public function test_it_throws_exception_for_invalid_provider()
    {
        config(['robin.provider' => 'invalid']);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported AI provider: invalid');
        
        AIProviderFactory::create();
    }

    public function test_openai_provider_has_correct_name()
    {
        config(['robin.provider' => 'openai']);
        
        $provider = AIProviderFactory::create();
        
        $this->assertEquals('OpenAI', $provider->getName());
    }

    public function test_claude_provider_has_correct_name()
    {
        config(['robin.provider' => 'claude']);
        
        $provider = AIProviderFactory::create();
        
        $this->assertEquals('Claude', $provider->getName());
    }

    public function test_gemini_provider_has_correct_name()
    {
        config(['robin.provider' => 'gemini']);
        
        $provider = AIProviderFactory::create();
        
        $this->assertEquals('Gemini', $provider->getName());
    }

    public function test_ollama_provider_has_correct_name()
    {
        config(['robin.provider' => 'ollama']);
        
        $provider = AIProviderFactory::create();
        
        $this->assertEquals('Ollama', $provider->getName());
    }
} 