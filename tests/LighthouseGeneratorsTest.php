<?php

namespace deinternetjongens\LighthouseGenerators\Tests;

use deinternetjongens\LighthouseGenerators\Facades\LighthouseGenerators;
use deinternetjongens\LighthouseGenerators\ServiceProvider;
use Orchestra\Testbench\TestCase;

class LighthouseGeneratorsTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'lighthouse-generators' => LighthouseGenerators::class,
        ];
    }

    public function testExample()
    {
        $this->assertEquals(1, 1);
    }
}
