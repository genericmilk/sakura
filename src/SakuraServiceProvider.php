<?php

namespace Genericmilk\Sakura;

use Illuminate\Support\ServiceProvider;
use Genericmilk\Sakura\Console\Commands\GenerateTestsCommand;
use Genericmilk\Sakura\Services\CodeAnalyzer;
use Genericmilk\Sakura\Services\TestGenerator;
use Genericmilk\Sakura\Services\AIProviderFactory;
use Genericmilk\Sakura\Services\AIProviderInterface;
use Genericmilk\Sakura\Services\OpenAIProvider;
use Genericmilk\Sakura\Services\ClaudeProvider;
use Genericmilk\Sakura\Services\GeminiProvider;
use Genericmilk\Sakura\Services\OllamaProvider;

class sakuraServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/sakura.php', 'sakura');

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
                __DIR__.'/../config/sakura.php' => config_path('sakura.php'),
            ], 'sakura-config');

            // Create .sakura directory on install
            $this->createsakuraDirectory();
        }
    }

    private function createsakuraDirectory(): void
    {
        $sakuraPath = base_path('.sakura');
        
        if (!file_exists($sakuraPath)) {
            mkdir($sakuraPath, 0755, true);
            
            // Create initial code tree file
            $codeTreePath = $sakuraPath . '/code-tree.json';
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