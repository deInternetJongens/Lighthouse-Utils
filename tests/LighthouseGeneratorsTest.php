<?php

namespace DeInternetJongens\LighthouseUtils\Tests;

use DeInternetJongens\LighthouseUtils\Facades\LighthouseUtils;
use DeInternetJongens\LighthouseUtils\ServiceProvider;
use Orchestra\Testbench\TestCase;

class LighthouseUtilsTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'lighthouse-utils' => LighthouseUtils::class,
        ];
    }

    public function testExample()
    {
        $this->assertEquals(1, 1);
    }
}
