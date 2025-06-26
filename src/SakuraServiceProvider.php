<?php

namespace Genericmilk\Robin;

use Illuminate\Support\ServiceProvider;
use Genericmilk\Robin\Console\Commands\GenerateTestsCommand;
use Genericmilk\Robin\Services\CodeAnalyzer;
use Genericmilk\Robin\Services\TestGenerator;
use Genericmilk\Robin\Services\AIProviderFactory;
use Genericmilk\Robin\Services\AIProviderInterface;
use Genericmilk\Robin\Services\OpenAIProvider;
use Genericmilk\Robin\Services\ClaudeProvider;
use Genericmilk\Robin\Services\GeminiProvider;
use Genericmilk\Robin\Services\OllamaProvider;

class RobinServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/robin.php', 'robin');

        $this->app->singleton(CodeAnalyzer::class);
        $this->app->singleton(TestGenerator::class);
        
        // Register AI providers
        $this->app->singleton(OpenAIProvider::class);
        $this->app->singleton(ClaudeProvider::class);
        $this->app->singleton(GeminiProvider::class);
        $this->app->singleton(OllamaProvider::class);
        
        // Bind the AI provider interface to the factory
        $this->app->bind(AIProviderInterface::class, function ($app) {
            return AIProviderFactory::create();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateTestsCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/robin.php' => config_path('robin.php'),
            ], 'robin-config');

            // Create .robin directory on install
            $this->createRobinDirectory();
        }
    }

    private function createRobinDirectory(): void
    {
        $robinPath = base_path('.robin');
        
        if (!file_exists($robinPath)) {
            mkdir($robinPath, 0755, true);
            
            // Create initial code tree file
            $codeTreePath = $robinPath . '/code-tree.json';
            if (!file_exists($codeTreePath)) {
                file_put_contents($codeTreePath, json_encode([
                    'version' => '1.0.0',
                    'last_updated' => now()->toISOString(),
                    'classes' => [],
                    'functions' => []
                ], JSON_PRETTY_PRINT));
            }
        }
    }
} 