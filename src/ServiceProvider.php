<?php

namespace deinternetjongens\LighthouseGenerators;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/lighthouse-generators.php';

    public function boot()
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('lighthouse-generators.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'lighthouse-generators'
        );

        $this->app->bind('lighthouse-generators', function () {
            return new LighthouseGenerators();
        });
    }
}
