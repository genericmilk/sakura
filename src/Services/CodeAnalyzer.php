<?php

namespace Genericmilk\Sakura\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CodeAnalyzer
{
    private string $storageDirectory;
    private string $codeTreeFile;
    private string $cacheFile;

    public function __construct()
    {
        $this->storageDirectory = base_path(config('sakura.storage.directory'));
        $this->codeTreeFile = $this->storageDirectory . '/' . config('sakura.storage.code_tree_file');
        $this->cacheFile = $this->storageDirectory . '/' . config('sakura.storage.cache_file');
        
        $this->ensureStorageDirectoryExists();
    }

    public function analyzeCodebase(): array
    {
        $directories = config('sakura.analysis.directories');
        $fileExtensions = config('sakura.analysis.file_extensions');
        $excludePatterns = config('sakura.analysis.exclude_patterns');

        $classes = [];
        $functions = [];
        $totalFilesProcessed = 0;
        $skippedFiles = [];
        $processedFiles = [];

        foreach ($directories as $directory) {
            $fullPath = base_path($directory);
            
            if (!File::exists($fullPath)) {
                // Skip non-existent directories silently to avoid noise
                continue;
            }

            $files = File::allFiles($fullPath);
            
            foreach ($files as $file) {
                if (!in_array($file->getExtension(), $fileExtensions)) {
                    $skippedFiles[] = $file->getRelativePathname() . ' (extension: ' . $file->getExtension() . ')';
                    continue;
                }

                $fileName = $file->getFilename();
                $shouldExclude = false;
                
                foreach ($excludePatterns as $pattern) {
                    if (Str::is($pattern, $fileName)) {
                        $shouldExclude = true;
                        $skippedFiles[] = $file->getRelativePathname() . ' (excluded by pattern: ' . $pattern . ')';
                        break;
                    }
                }

                if ($shouldExclude) {
                    continue;
                }

                $content = File::get($file->getPathname());
                $processedFiles[] = $file->getRelativePathname();
                $totalFilesProcessed++;
                $this->extractClassesAndFunctions($content, $file->getRelativePathname(), $classes, $functions);
            }
        }

        // Debug output (only when running via console)
        if (app()->runningInConsole()) {
            $debugInfo = [
                'totalFilesProcessed' => $totalFilesProcessed,
                'classesFound' => count($classes),
                'functionsFound' => count($functions),
                'processedFiles' => $processedFiles,
                'skippedFiles' => array_slice($skippedFiles, 0, 10),
                'directories' => $directories,
            ];
            
            // Write debug info to a temporary file
            File::put(storage_path('sakura_debug.json'), json_encode($debugInfo, JSON_PRETTY_PRINT));
        }

        return [
            'version' => '1.0.0',
            'last_updated' => now()->toISOString(),
            'classes' => $classes,
            'functions' => $functions,
        ];
    }

    public function getChangedItems(): array
    {
        $currentTree = $this->analyzeCodebase();
        $previousTree = $this->loadCodeTree();

        $changedClasses = [];
        $newClasses = [];
        $changedFunctions = [];
        $newFunctions = [];

        // Check for changed/new classes
        foreach ($currentTree['classes'] as $currentClass) {
            $previousClass = $this->findClassByName($previousTree['classes'], $currentClass['name']);
            
            if (!$previousClass) {
                $newClasses[] = $currentClass;
            } elseif ($this->hasClassChanged($currentClass, $previousClass)) {
                $changedClasses[] = $currentClass;
            }
        }

        // Check for changed/new functions
        foreach ($currentTree['functions'] as $currentFunction) {
            $previousFunction = $this->findFunctionByName($previousTree['functions'], $currentFunction['name']);
            
            if (!$previousFunction) {
                $newFunctions[] = $currentFunction;
            } elseif ($this->hasFunctionChanged($currentFunction, $previousFunction)) {
                $changedFunctions[] = $currentFunction;
            }
        }

        return [
            'changed_classes' => $changedClasses,
            'new_classes' => $newClasses,
            'changed_functions' => $changedFunctions,
            'new_functions' => $newFunctions,
        ];
    }

    public function saveCodeTree(array $codeTree): void
    {
        File::put($this->codeTreeFile, json_encode($codeTree, JSON_PRETTY_PRINT));
    }

    public function getCodeTree(): array
    {
        return $this->loadCodeTree();
    }

    private function loadCodeTree(): array
    {
        if (!File::exists($this->codeTreeFile)) {
            return [
                'version' => '1.0.0',
                'last_updated' => now()->toISOString(),
                'classes' => [],
                'functions' => [],
            ];
        }

        $content = File::get($this->codeTreeFile);
        return json_decode($content, true) ?: [
            'version' => '1.0.0',
            'last_updated' => now()->toISOString(),
            'classes' => [],
            'functions' => [],
        ];
    }

    private function ensureStorageDirectoryExists(): void
    {
        if (!File::exists($this->storageDirectory)) {
            File::makeDirectory($this->storageDirectory, 0755, true);
        }
    }

    private function extractClassesAndFunctions(string $content, string $filePath, array &$classes, array &$functions): void
    {
        $tokens = token_get_all($content);
        $namespace = '';
        $currentClass = null;
        $currentMethod = null;
        $inClass = false;
        $classStartLine = 0;
        $methodStartLine = 0;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                [$id, $text, $line] = $token;

                switch ($id) {
                    case T_NAMESPACE:
                        $namespace = $this->extractNamespace($tokens, $token);
                        break;

                    case T_CLASS:
                        $inClass = true;
                        $classStartLine = $line;
                        $currentClass = [
                            'name' => '',
                            'full_name' => '',
                            'file_path' => $filePath,
                            'start_line' => $line,
                            'end_line' => 0,
                            'methods' => [],
                            'content' => '',
                            'hash' => '',
                        ];
                        break;

                    case T_STRING:
                        if ($inClass && $currentClass && empty($currentClass['name'])) {
                            $currentClass['name'] = $text;
                            $currentClass['full_name'] = $namespace ? $namespace . '\\' . $text : $text;
                        } elseif ($currentMethod && empty($currentMethod['name'])) {
                            $currentMethod['name'] = $text;
                        }
                        break;

                    case T_FUNCTION:
                        if ($inClass) {
                            $currentMethod = [
                                'name' => '',
                                'start_line' => $line,
                                'end_line' => 0,
                                'content' => '',
                                'hash' => '',
                            ];
                            $methodStartLine = $line;
                        } else {
                            // Standalone function
                            $functionName = $this->extractFunctionName($tokens, $token);
                            if ($functionName) {
                                $functions[] = [
                                    'name' => $functionName,
                                    'full_name' => $namespace ? $namespace . '\\' . $functionName : $functionName,
                                    'file_path' => $filePath,
                                    'start_line' => $line,
                                    'end_line' => $this->findFunctionEndLine($tokens, $token),
                                    'content' => $this->extractFunctionContent($content, $line),
                                    'hash' => '',
                                ];
                            }
                        }
                        break;

                    case '}':
                        if ($currentMethod) {
                            $currentMethod['end_line'] = $line;
                            $currentMethod['content'] = $this->extractMethodContent($content, $methodStartLine, $line);
                            $currentMethod['hash'] = md5($currentMethod['content']);
                            
                            if ($currentClass) {
                                $currentClass['methods'][] = $currentMethod;
                            }
                            $currentMethod = null;
                        } elseif ($inClass && $currentClass) {
                            $currentClass['end_line'] = $line;
                            $currentClass['content'] = $this->extractClassContent($content, $classStartLine, $line);
                            $currentClass['hash'] = md5($currentClass['content']);
                            
                            $classes[] = $currentClass;
                            $currentClass = null;
                            $inClass = false;
                        }
                        break;
                }
            }
        }
    }

    private function extractNamespace(array $tokens, array $namespaceToken): string
    {
        $namespace = '';
        $foundNamespace = false;
        
        foreach ($tokens as $token) {
            if ($token === $namespaceToken) {
                $foundNamespace = true;
                continue;
            }
            
            if ($foundNamespace && is_array($token) && $token[0] === T_STRING) {
                $namespace .= $token[1];
            } elseif ($foundNamespace && is_array($token) && $token[0] === T_NS_SEPARATOR) {
                $namespace .= '\\';
            } elseif ($foundNamespace && $token === ';') {
                break;
            }
        }
        
        return $namespace;
    }

    private function extractFunctionName(array $tokens, array $functionToken): ?string
    {
        $foundFunction = false;
        
        foreach ($tokens as $token) {
            if ($token === $functionToken) {
                $foundFunction = true;
                continue;
            }
            
            if ($foundFunction && is_array($token) && $token[0] === T_STRING) {
                return $token[1];
            } elseif ($foundFunction && $token === '(') {
                break;
            }
        }
        
        return null;
    }

    private function findFunctionEndLine(array $tokens, array $functionToken): int
    {
        $foundFunction = false;
        $braceCount = 0;
        $started = false;
        
        foreach ($tokens as $token) {
            if ($token === $functionToken) {
                $foundFunction = true;
                continue;
            }
            
            if ($foundFunction && $token === '{') {
                $braceCount++;
                $started = true;
            } elseif ($started && $token === '}') {
                $braceCount--;
                if ($braceCount === 0) {
                    return is_array($token) ? $token[2] : 0;
                }
            }
        }
        
        return 0;
    }

    private function extractFunctionContent(string $content, int $startLine): string
    {
        $lines = explode("\n", $content);
        $startIndex = $startLine - 1;
        
        // Find the end of the function
        $braceCount = 0;
        $endIndex = $startIndex;
        
        for ($i = $startIndex; $i < count($lines); $i++) {
            $line = $lines[$i];
            $braceCount += substr_count($line, '{') - substr_count($line, '}');
            
            if ($braceCount === 0 && $i > $startIndex) {
                $endIndex = $i;
                break;
            }
        }
        
        return implode("\n", array_slice($lines, $startIndex, $endIndex - $startIndex + 1));
    }

    private function extractMethodContent(string $content, int $startLine, int $endLine): string
    {
        $lines = explode("\n", $content);
        return implode("\n", array_slice($lines, $startLine - 1, $endLine - $startLine + 1));
    }

    private function extractClassContent(string $content, int $startLine, int $endLine): string
    {
        $lines = explode("\n", $content);
        return implode("\n", array_slice($lines, $startLine - 1, $endLine - $startLine + 1));
    }

    private function findClassByName(array $classes, string $name): ?array
    {
        foreach ($classes as $class) {
            if ($class['name'] === $name) {
                return $class;
            }
        }
        return null;
    }

    private function findFunctionByName(array $functions, string $name): ?array
    {
        foreach ($functions as $function) {
            if ($function['name'] === $name) {
                return $function;
            }
        }
        return null;
    }

    private function hasClassChanged(array $current, array $previous): bool
    {
        return $current['hash'] !== $previous['hash'];
    }

    private function hasFunctionChanged(array $current, array $previous): bool
    {
        return $current['hash'] !== $previous['hash'];
    }
} 