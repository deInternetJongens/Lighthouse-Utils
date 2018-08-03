<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit;

use DeInternetJongens\LighthouseUtils\Facades\LighthouseUtils;
use DeInternetJongens\LighthouseUtils\ServiceProvider;
use Nuwave\Lighthouse\Providers\LighthouseServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @inheritdoc
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('lighthouse.schema.register', __DIR__ . '/Generators/files/schema.graphql');
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
            'lighthouse-generators' => LighthouseUtils::class,
        ];
    }
}
