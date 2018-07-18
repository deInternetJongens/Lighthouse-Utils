<?php

namespace DeInternetJongens\LighthouseUtils;

use DeInternetJongens\LighthouseUtils\Console\GenerateSchemaCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/lighthouse-utils.php';
    const DIRECTIVE_PATH = __DIR__.'/Directives';

    /** @var string */
    private $directiveAppPath;

    public function __construct(\Illuminate\Contracts\Foundation\Application $app)
    {
        parent::__construct($app);

        // The path where our directives will be published to.
        $this->directiveAppPath = app_path('DijLighthouse/Directives');
    }

    public function boot()
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('lighthouse-utils.php'),
        ], 'config');

        $this->publishes([
            self::DIRECTIVE_PATH => $this->directiveAppPath,
        ]);
    }

    public function register()
    {

        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'lighthouse-utils'
        );

        // Merging config doesn't seem to work on arrays, this is our work-around.
        config()->set('lighthouse.directives', array_merge(config('lighthouse.directives', []), [$this->directiveAppPath]));

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
