<?php

namespace deinternetjongens\LighthouseGenerators\Facades;

use Illuminate\Support\Facades\Facade;

class LighthouseGenerators extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'lighthouse-generators';
    }
}
