<?php

namespace Genericmilk\Sakura\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TestGenerator
{
    private AIProviderInterface $aiProvider;

    public function __construct(AIProviderInterface $aiProvider)
    {
        $this->aiProvider = $aiProvider;
    }

    public function generateTestsForClass(array $class): array
    {
        $testFramework = $this->detectTestFramework();
        $testType = $this->determineTestType($class);
        
        $prompt = $this->buildClassPrompt($class, $testFramework, $testType);
        $response = $this->aiProvider->generateTest($prompt);
        
        if (isset($response['error'])) {
            return $response;
        }
        
        $testContent = $response['content'];
        $filePath = $this->generateTestFilePath($class, $testType);
        
        return [
            'content' => $testContent,
            'file_path' => $filePath,
            'class_name' => $class['name'],
            'test_type' => $testType,
            'framework' => $testFramework,
        ];
    }

    public function generateTestsForFunction(array $function): array
    {
        $testFramework = $this->detectTestFramework();
        
        $prompt = $this->buildFunctionPrompt($function, $testFramework);
        $response = $this->aiProvider->generateTest($prompt);
        
        if (isset($response['error'])) {
            return $response;
        }
        
        $testContent = $response['content'];
        $filePath = $this->generateFunctionTestFilePath($function);
        
        return [
            'content' => $testContent,
            'file_path' => $filePath,
            'function_name' => $function['name'],
            'test_type' => 'unit',
            'framework' => $testFramework,
        ];
    }

    public function saveTestFile(array $testData): bool
    {
        $directory = dirname($testData['file_path']);
        
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
        
        return File::put($testData['file_path'], $testData['content']) !== false;
    }

    private function detectTestFramework(): string
    {
        $framework = config('sakura.testing.framework');
        
        if ($framework !== 'auto') {
            return $framework;
        }
        
        // Auto-detect based on existing test files
        $testDirectory = base_path(config('sakura.testing.test_directory'));
        
        if (File::exists($testDirectory . '/TestCase.php')) {
            return 'phpunit';
        }
        
        if (File::exists($testDirectory . '/Pest.php')) {
            return 'pest';
        }
        
        // Check for Pest in composer.json
        $composerPath = base_path('composer.json');
        if (File::exists($composerPath)) {
            $composer = json_decode(File::get($composerPath), true);
            if (isset($composer['require-dev']['pestphp/pest'])) {
                return 'pest';
            }
        }
        
        return 'phpunit'; // Default fallback
    }

    private function determineTestType(array $class): string
    {
        $className = strtolower($class['name']);
        $filePath = strtolower($class['file_path']);
        
        // Controllers and HTTP-related classes should be feature tests
        if (Str::contains($className, 'controller') || 
            Str::contains($filePath, 'http/controllers') ||
            Str::contains($filePath, 'middleware') ||
            Str::contains($filePath, 'requests')) {
            return 'feature';
        }
        
        // Models, Services, Actions, etc. should be unit tests
        return 'unit';
    }

    private function buildClassPrompt(array $class, string $framework, string $testType): string
    {
        $frameworkInfo = $this->getFrameworkInfo($framework);
        $testTypeInfo = $this->getTestTypeInfo($testType);
        
        return "Generate comprehensive {$testTypeInfo['name']} tests for the following PHP class using {$frameworkInfo['name']}:

Class Information:
- Name: {$class['name']}
- Full Name: {$class['full_name']}
- File: {$class['file_path']}
- Type: {$testTypeInfo['description']}

Class Code:
```php
{$class['content']}
```

Requirements:
{$frameworkInfo['requirements']}
{$testTypeInfo['requirements']}

- Generate tests for all public methods
- Include edge cases and error scenarios
- Follow Laravel best practices
- Use proper assertions and mocking where appropriate
- Ensure good test coverage

Return only the complete test file content, no explanations.";
    }

    private function buildFunctionPrompt(array $function, string $framework): string
    {
        $frameworkInfo = $this->getFrameworkInfo($framework);
        
        return "Generate comprehensive unit tests for the following PHP function using {$frameworkInfo['name']}:

Function Information:
- Name: {$function['name']}
- Full Name: {$function['full_name']}
- File: {$function['file_path']}

Function Code:
```php
{$function['content']}
```

Requirements:
{$frameworkInfo['requirements']}

- Test all possible input scenarios
- Include edge cases and error handling
- Follow Laravel best practices
- Use proper assertions

Return only the complete test file content, no explanations.";
    }

    private function generateTestFilePath(array $class, string $testType): string
    {
        $testDirectory = base_path(config('sakura.testing.test_directory'));
        $className = $class['name'] . 'Test';
        
        // Convert namespace to directory structure
        $namespace = $this->extractNamespace($class['full_name']);
        $relativePath = $this->convertNamespaceToPath($namespace);
        
        $typeDirectory = $testType === 'feature' ? 'Feature' : 'Unit';
        
        return $testDirectory . '/' . $typeDirectory . '/' . $relativePath . '/' . $className . '.php';
    }

    private function generateFunctionTestFilePath(array $function): string
    {
        $testDirectory = base_path(config('sakura.testing.test_directory'));
        $functionName = $function['name'] . 'Test';
        
        // Convert namespace to directory structure
        $namespace = $this->extractNamespace($function['full_name']);
        $relativePath = $this->convertNamespaceToPath($namespace);
        
        return $testDirectory . '/Unit/' . $relativePath . '/' . $functionName . '.php';
    }

    private function extractNamespace(string $fullName): string
    {
        $parts = explode('\\', $fullName);
        array_pop($parts); // Remove the class/function name
        return implode('\\', $parts);
    }

    private function convertNamespaceToPath(string $namespace): string
    {
        return str_replace('\\', '/', $namespace);
    }

    private function getFrameworkInfo(string $framework): array
    {
        return match ($framework) {
            'pest' => [
                'name' => 'Pest',
                'requirements' => '- Use Pest syntax with test() function
- Use expect() for assertions
- Use beforeEach() for setup
- Use describe() for grouping tests',
            ],
            'phpunit' => [
                'name' => 'PHPUnit',
                'requirements' => '- Use PHPUnit class extending TestCase
- Use $this->assert* methods for assertions
- Use setUp() method for test setup
- Use proper test method naming (test*)
- Use data providers where appropriate',
            ],
            default => [
                'name' => 'PHPUnit',
                'requirements' => '- Use PHPUnit class extending TestCase
- Use $this->assert* methods for assertions
- Use setUp() method for test setup',
            ],
        };
    }

    private function getTestTypeInfo(string $testType): array
    {
        return match ($testType) {
            'feature' => [
                'name' => 'Feature',
                'description' => 'HTTP/Integration test',
                'requirements' => '- Test HTTP requests and responses
- Use $this->get(), $this->post(), etc. for HTTP calls
- Test routes and middleware
- Use $this->actingAs() for authentication
- Test JSON responses and redirects',
            ],
            'unit' => [
                'name' => 'Unit',
                'description' => 'Unit test',
                'requirements' => '- Test individual methods in isolation
- Use mocking for dependencies
- Test business logic and calculations
- Use factories for test data
- Test edge cases and error conditions',
            ],
            default => [
                'name' => 'Unit',
                'description' => 'Unit test',
                'requirements' => '- Test individual methods in isolation
- Use mocking for dependencies',
            ],
        };
    }
} 