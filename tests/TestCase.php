<?php

namespace deinternetjongens\LighthouseGenerators\Tests;

use deinternetjongens\LighthouseGenerators\Facades\LighthouseGenerators;
use deinternetjongens\LighthouseGenerators\ServiceProvider;
use Nuwave\Lighthouse\Providers\LighthouseServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @inheritdoc
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('lighthouse.schema.register', __DIR__ . '/files/schema');
    }

    /**
     * @inheritdoc
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
            LighthouseServiceProvider::class,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getPackageAliases($app)
    {
        return [
            'lighthouse-generators' => LighthouseGenerators::class,
        ];
    }
}