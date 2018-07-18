<?php

namespace DeInternetJongens\LighthouseUtils\Facades;

use Illuminate\Support\Facades\Facade;

class LighthouseUtils extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'lighthouse-utils';
    }
}
