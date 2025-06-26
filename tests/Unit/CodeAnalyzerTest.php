<?php

namespace Genericmilk\Sakura\Tests\Unit;

use Genericmilk\Sakura\Tests\TestCase;
use Genericmilk\Sakura\Services\CodeAnalyzer;
use Illuminate\Support\Facades\File;

class CodeAnalyzerTest extends TestCase
{
    private CodeAnalyzer $codeAnalyzer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->codeAnalyzer = new CodeAnalyzer();
    }

    public function test_it_can_analyze_empty_codebase()
    {
        $result = $this->codeAnalyzer->analyzeCodebase();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('classes', $result);
        $this->assertArrayHasKey('functions', $result);
        $this->assertIsArray($result['classes']);
        $this->assertIsArray($result['functions']);
    }

    public function test_it_can_get_code_tree()
    {
        $tree = $this->codeAnalyzer->getCodeTree();
        
        $this->assertIsArray($tree);
        $this->assertArrayHasKey('version', $tree);
        $this->assertArrayHasKey('last_updated', $tree);
        $this->assertArrayHasKey('classes', $tree);
        $this->assertArrayHasKey('functions', $tree);
    }

    public function test_it_can_save_code_tree()
    {
        $testTree = [
            'version' => '1.0.0',
            'last_updated' => now()->toISOString(),
            'classes' => [],
            'functions' => []
        ];
        
        $this->codeAnalyzer->saveCodeTree($testTree);
        
        $savedTree = $this->codeAnalyzer->getCodeTree();
        $this->assertEquals($testTree['version'], $savedTree['version']);
    }

    public function test_it_can_get_changed_items()
    {
        $changedItems = $this->codeAnalyzer->getChangedItems();
        
        $this->assertIsArray($changedItems);
        $this->assertArrayHasKey('changed_classes', $changedItems);
        $this->assertArrayHasKey('changed_functions', $changedItems);
        $this->assertArrayHasKey('new_classes', $changedItems);
        $this->assertArrayHasKey('new_functions', $changedItems);
    }
} 