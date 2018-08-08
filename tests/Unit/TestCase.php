<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit;

use DeInternetJongens\LighthouseUtils\Facades\LighthouseUtils;
use DeInternetJongens\LighthouseUtils\ServiceProvider;
use Nuwave\Lighthouse\Providers\LighthouseServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

    protected function setUp()
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    /**
     * @inheritdoc
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('lighthouse.schema.register', __DIR__ . '/Generators/files/schema.graphql');

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
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

    /**
     * Set up the database.
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        include_once __DIR__ . '/../../database/migrations/create_graphql_schema_table.php.stub';
        (new \CreateGraphQLSchemaTable())->up();
    }
}
