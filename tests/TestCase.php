<?php

namespace Genericmilk\Sakura\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Genericmilk\Sakura\SakuraServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            SakuraServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Set OpenAI API key for testing
        $app['config']->set('sakura.openai.api_key', 'test-key');
    }
} 