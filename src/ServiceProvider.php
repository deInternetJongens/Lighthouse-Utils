<?php

namespace DeInternetJongens\LighthouseUtils;

use DeInternetJongens\LighthouseUtils\Console\GenerateSchemaCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/lighthouse-utils.php';

    public function boot()
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('lighthouse-utils.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'lighthouse-utils'
        );

        $this->app->bind('lighthouse-utils', function () {
            return new LighthouseUtils();
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateSchemaCommand::class
            ]);
        }
    }
}
