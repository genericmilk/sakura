<?php

namespace Genericmilk\Sakura\Console\Commands;

use Illuminate\Console\Command;
use Genericmilk\Sakura\Services\CodeAnalyzer;
use Genericmilk\Sakura\Services\TestGenerator;
use Genericmilk\Sakura\Services\AIProviderFactory;
use Illuminate\Support\Facades\File;

class GenerateTestsCommand extends Command
{
    protected $signature = 'sakura:generate-tests 
                            {--force : Force regeneration of all tests}
                            {--class= : Generate tests for a specific class only}
                            {--function= : Generate tests for a specific function only}
                            {--dry-run : Show what would be generated without creating files}
                            {--provider= : Override the AI provider (openai/claude/gemini/ollama)}';

    protected $description = 'Generate tests for PHP classes and functions using OpenAI, Claude, Gemini, or Ollama';

    private CodeAnalyzer $codeAnalyzer;
    private TestGenerator $testGenerator;

    public function __construct(CodeAnalyzer $codeAnalyzer, TestGenerator $testGenerator)
    {
        parent::__construct();
        $this->codeAnalyzer = $codeAnalyzer;
        $this->testGenerator = $testGenerator;
    }

    public function handle(): int
    {
        $this->info('🌸 Sakura - AI-Powered Test Generator');
        $this->newLine();

        // Check AI provider configuration
        if (!$this->validateAIProviderConfig()) {
            return 1;
        }

        // Handle specific class/function generation
        if ($specificClass = $this->option('class')) {
            return $this->generateTestsForSpecificClass($specificClass);
        }

        if ($specificFunction = $this->option('function')) {
            return $this->generateTestsForSpecificFunction($specificFunction);
        }

        // Analyze codebase
        $this->info('📊 Analyzing codebase...');
        $changedItems = $this->codeAnalyzer->getChangedItems();
        
        // Check if this is a fresh installation
        $codeTreeFile = base_path(config('sakura.storage.directory')) . '/' . config('sakura.storage.code_tree_file');
        $isFirstRun = !File::exists($codeTreeFile);
        
        if ($isFirstRun) {
            $this->info('🆕 First run detected - analyzing all existing code...');
            $currentTree = $this->codeAnalyzer->analyzeCodebase();
            
            // On first run, treat everything as new
            $changedItems = [
                'changed_classes' => [],
                'changed_functions' => [],
                'new_classes' => $currentTree['classes'],
                'new_functions' => $currentTree['functions'],
            ];
        }
        
        if ($this->option('force')) {
            $this->info('🔄 Force mode enabled - analyzing all code...');
            
            // Debug: Show what directories we're looking at
            $directories = config('sakura.analysis.directories');
            $this->line('Debug: Configured directories: ' . implode(', ', $directories));
            
            $existingDirs = [];
            $nonExistentDirs = [];
            foreach ($directories as $dir) {
                $fullPath = base_path($dir);
                if (File::exists($fullPath)) {
                    $existingDirs[] = $dir;
                } else {
                    $nonExistentDirs[] = $dir;
                }
            }
            
            if (!empty($existingDirs)) {
                $this->line('Debug: Existing directories: ' . implode(', ', $existingDirs));
            }
            if (!empty($nonExistentDirs)) {
                $this->line('Debug: Non-existent directories: ' . implode(', ', $nonExistentDirs));
            }
            
            $currentTree = $this->codeAnalyzer->analyzeCodebase();
            $this->line('Debug: Analysis complete - found ' . count($currentTree['classes']) . ' classes and ' . count($currentTree['functions']) . ' functions');
            
            $changedItems = [
                'changed_classes' => $currentTree['classes'],
                'changed_functions' => $currentTree['functions'],
                'new_classes' => [],
                'new_functions' => [],
            ];
        }

        $totalItems = count($changedItems['changed_classes']) + 
                     count($changedItems['changed_functions']) + 
                     count($changedItems['new_classes']) + 
                     count($changedItems['new_functions']);

        if ($totalItems === 0) {
            if ($isFirstRun) {
                $this->warn('⚠️  No PHP classes or functions found to generate tests for.');
                $this->line('');
                $this->line('This could mean:');
                $this->line('  • No configured directories exist in your project');
                $this->line('  • Configured directories contain no PHP files');
                $this->line('  • All PHP files are excluded by patterns');
                $this->line('');
                $this->line('💡 Try:');
                $this->line('  • Run: php artisan vendor:publish --tag=sakura-config');
                $this->line('  • Edit config/sakura.php to match your project structure');
                $this->line('  • Create some classes in app/Models or app/Http/Controllers');
                $this->line('  • Use --force flag to regenerate tests for existing code');
            } else {
                $this->info('✅ No changes detected. All tests are up to date!');
                $this->line('💡 To regenerate all tests, use: php artisan sakura:generate-tests --force');
            }
            return 0;
        }

        $this->info("Found {$totalItems} items to process:");
        $this->displayChangedItems($changedItems);

        if ($this->option('dry-run')) {
            $this->info('🔍 Dry run mode - no files will be created');
            return 0;
        }

        // Confirm before proceeding
        if (!$this->confirm('Do you want to generate tests for these items?')) {
            $this->info('❌ Test generation cancelled.');
            return 0;
        }

        // Generate tests
        $this->generateTests($changedItems);

        // Update code tree
        $this->info('💾 Updating code tree...');
        $currentTree = $this->codeAnalyzer->analyzeCodebase();
        $this->codeAnalyzer->saveCodeTree($currentTree);

        $this->info('✅ Test generation completed!');
        return 0;
    }

    private function validateAIProviderConfig(): bool
    {
        $provider = $this->option('provider') ?? config('sakura.provider', 'openai');
        
        try {
            $aiProvider = AIProviderFactory::create();
            
            if (!$aiProvider->isConfigured()) {
                $this->error("❌ {$aiProvider->getName()} is not properly configured.");
                
                if ($aiProvider->getName() === 'OpenAI') {
                    $this->line('Please set OPENAI_API_KEY in your .env file.');
                } elseif ($aiProvider->getName() === 'Claude') {
                    $this->line('Please set ANTHROPIC_API_KEY in your .env file.');
                } elseif ($aiProvider->getName() === 'Gemini') {
                    $this->line('Please set GOOGLE_AI_API_KEY in your .env file.');
                } elseif ($aiProvider->getName() === 'Ollama') {
                    $this->line('Please ensure Ollama is running and accessible at: ' . config('sakura.ollama.base_url'));
                    $this->line('You can start Ollama with: ollama serve');
                }
                
                return false;
            }

            // Additional validation for Ollama
            if ($aiProvider->getName() === 'Ollama' && method_exists($aiProvider, 'isModelAvailable')) {
                if (!$aiProvider->isModelAvailable()) {
                    $this->error("❌ Ollama model '" . config('sakura.ollama.model') . "' is not available.");
                    $this->line('Available models can be listed with: ollama list');
                    $this->line('To pull the model: ollama pull ' . config('sakura.ollama.model'));
                    return false;
                }
            }

            // Additional validation for Claude
            if ($aiProvider->getName() === 'Claude' && method_exists($aiProvider, 'isModelAvailable')) {
                if (!$aiProvider->isModelAvailable()) {
                    $this->error("❌ Claude model '" . config('sakura.claude.model') . "' is not available.");
                    $this->line('Available models: ' . implode(', ', $aiProvider->getAvailableModels()));
                    return false;
                }
            }

            // Additional validation for Gemini
            if ($aiProvider->getName() === 'Gemini' && method_exists($aiProvider, 'isModelAvailable')) {
                if (!$aiProvider->isModelAvailable()) {
                    $this->error("❌ Gemini model '" . config('sakura.gemini.model') . "' is not available.");
                    $this->line('Available models: ' . implode(', ', $aiProvider->getAvailableModels()));
                    return false;
                }
            }

            $this->info("🤖 Using {$aiProvider->getName()} for test generation");
            return true;
            
        } catch (\InvalidArgumentException $e) {
            $this->error("❌ Invalid AI provider: {$provider}");
            $this->line('Supported providers: openai, claude, gemini, ollama');
            return false;
        }
    }

    private function generateTestsForSpecificClass(string $className): int
    {
        $this->info("🎯 Generating tests for class: {$className}");
        
        $currentTree = $this->codeAnalyzer->analyzeCodebase();
        $targetClass = null;
        
        foreach ($currentTree['classes'] as $class) {
            if ($class['name'] === $className || $class['full_name'] === $className) {
                $targetClass = $class;
                break;
            }
        }
        
        if (!$targetClass) {
            $this->error("❌ Class '{$className}' not found in the codebase.");
            return 1;
        }

        $this->generateTests(['changed_classes' => [$targetClass], 'changed_functions' => [], 'new_classes' => [], 'new_functions' => []]);
        
        return 0;
    }

    private function generateTestsForSpecificFunction(string $functionName): int
    {
        $this->info("🎯 Generating tests for function: {$functionName}");
        
        $currentTree = $this->codeAnalyzer->analyzeCodebase();
        $targetFunction = null;
        
        foreach ($currentTree['functions'] as $function) {
            if ($function['name'] === $functionName || $function['full_name'] === $functionName) {
                $targetFunction = $function;
                break;
            }
        }
        
        if (!$targetFunction) {
            $this->error("❌ Function '{$functionName}' not found in the codebase.");
            return 1;
        }

        $this->generateTests(['changed_classes' => [], 'changed_functions' => [$targetFunction], 'new_classes' => [], 'new_functions' => []]);
        
        return 0;
    }

    private function displayChangedItems(array $changedItems): void
    {
        if (!empty($changedItems['changed_classes'])) {
            $this->line('📝 Changed Classes:');
            foreach ($changedItems['changed_classes'] as $class) {
                $this->line("  - {$class['full_name']} ({$class['file_path']})");
            }
        }

        if (!empty($changedItems['new_classes'])) {
            $this->line('🆕 New Classes:');
            foreach ($changedItems['new_classes'] as $class) {
                $this->line("  - {$class['full_name']} ({$class['file_path']})");
            }
        }

        if (!empty($changedItems['changed_functions'])) {
            $this->line('📝 Changed Functions:');
            foreach ($changedItems['changed_functions'] as $function) {
                $this->line("  - {$function['full_name']} ({$function['file_path']})");
            }
        }

        if (!empty($changedItems['new_functions'])) {
            $this->line('🆕 New Functions:');
            foreach ($changedItems['new_functions'] as $function) {
                $this->line("  - {$function['full_name']} ({$function['file_path']})");
            }
        }
    }

    private function generateTests(array $changedItems): void
    {
        $progressBar = $this->output->createProgressBar(
            count($changedItems['changed_classes']) + 
            count($changedItems['new_classes']) + 
            count($changedItems['changed_functions']) + 
            count($changedItems['new_functions'])
        );

        $progressBar->start();

        $successCount = 0;
        $errorCount = 0;

        // Process classes
        $allClasses = array_merge($changedItems['changed_classes'], $changedItems['new_classes']);
        foreach ($allClasses as $class) {
            $progressBar->setMessage("Generating tests for {$class['name']}...");
            
            try {
                $testData = $this->testGenerator->generateTestsForClass($class);
                
                if (isset($testData['error'])) {
                    $this->newLine();
                    $this->error("❌ Error generating tests for {$class['name']}: {$testData['error']}");
                    $errorCount++;
                } else {
                    if ($this->testGenerator->saveTestFile($testData)) {
                        $this->newLine();
                        $this->info("✅ Generated tests for {$class['name']} → {$testData['file_path']}");
                        $successCount++;
                    } else {
                        $this->newLine();
                        $this->error("❌ Failed to save tests for {$class['name']}");
                        $errorCount++;
                    }
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("❌ Exception generating tests for {$class['name']}: {$e->getMessage()}");
                $errorCount++;
            }
            
            $progressBar->advance();
        }

        // Process functions
        $allFunctions = array_merge($changedItems['changed_functions'], $changedItems['new_functions']);
        foreach ($allFunctions as $function) {
            $progressBar->setMessage("Generating tests for {$function['name']}...");
            
            try {
                $testData = $this->testGenerator->generateTestsForFunction($function);
                
                if (isset($testData['error'])) {
                    $this->newLine();
                    $this->error("❌ Error generating tests for {$function['name']}: {$testData['error']}");
                    $errorCount++;
                } else {
                    if ($this->testGenerator->saveTestFile($testData)) {
                        $this->newLine();
                        $this->info("✅ Generated tests for {$function['name']} → {$testData['file_path']}");
                        $successCount++;
                    } else {
                        $this->newLine();
                        $this->error("❌ Failed to save tests for {$function['name']}");
                        $errorCount++;
                    }
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("❌ Exception generating tests for {$function['name']}: {$e->getMessage()}");
                $errorCount++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("📊 Summary: {$successCount} successful, {$errorCount} failed");
    }
} 