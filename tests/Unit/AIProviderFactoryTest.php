<?php

namespace Genericmilk\Sakura\Tests\Unit;

use Genericmilk\Sakura\Tests\TestCase;
use Genericmilk\Sakura\Services\AIProviderFactory;
use Genericmilk\Sakura\Services\AIProviderInterface;
use Genericmilk\Sakura\Services\OpenAIProvider;
use Genericmilk\Sakura\Services\ClaudeProvider;
use Genericmilk\Sakura\Services\GeminiProvider;
use Genericmilk\Sakura\Services\OllamaProvider;

class AIProviderFactoryTest extends TestCase
{
    public function test_it_creates_openai_provider_by_default()
    {
        config(['sakura.provider' => 'openai']);
        
        $provider = AIProviderFactory::create();
        
        $this->assertInstanceOf(OpenAIProvider::class, $provider);
        $this->assertInstanceOf(AIProviderInterface::class, $provider);
    }

    public function test_it_creates_claude_provider_when_configured()
    {
        config(['sakura.provider' => 'claude']);
        
        $provider = AIProviderFactory::create();
        
        $this->assertInstanceOf(ClaudeProvider::class, $provider);
        $this->assertInstanceOf(AIProviderInterface::class, $provider);
    }

    public function test_it_creates_gemini_provider_when_configured()
    {
        config(['sakura.provider' => 'gemini']);
        
        $provider = AIProviderFactory::create();
        
        $this->assertInstanceOf(GeminiProvider::class, $provider);
        $this->assertInstanceOf(AIProviderInterface::class, $provider);
    }

    public function test_it_creates_ollama_provider_when_configured()
    {
        config(['sakura.provider' => 'ollama']);
        
        $provider = AIProviderFactory::create();
        
        $this->assertInstanceOf(OllamaProvider::class, $provider);
        $this->assertInstanceOf(AIProviderInterface::class, $provider);
    }

    public function test_it_throws_exception_for_invalid_provider()
    {
        config(['sakura.provider' => 'invalid']);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported AI provider: invalid');
        
        AIProviderFactory::create();
    }

    public function test_openai_provider_has_correct_name()
    {
        config(['sakura.provider' => 'openai']);
        
        $provider = AIProviderFactory::create();
        
        $this->assertEquals('OpenAI', $provider->getName());
    }

    public function test_claude_provider_has_correct_name()
    {
        config(['sakura.provider' => 'claude']);
        
        $provider = AIProviderFactory::create();
        
        $this->assertEquals('Claude', $provider->getName());
    }

    public function test_gemini_provider_has_correct_name()
    {
        config(['sakura.provider' => 'gemini']);
        
        $provider = AIProviderFactory::create();
        
        $this->assertEquals('Gemini', $provider->getName());
    }

    public function test_ollama_provider_has_correct_name()
    {
        config(['sakura.provider' => 'ollama']);
        
        $provider = AIProviderFactory::create();
        
        $this->assertEquals('Ollama', $provider->getName());
    }
} 