<?php

namespace DeInternetJongens\LighthouseUtils;

use DeInternetJongens\LighthouseUtils\Console\GenerateSchemaCommand;
use ReflectionClass;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/lighthouse-utils.php';
    const DIRECTIVE_PATH = __DIR__.'/Directives';
    const DIRECTIVE_NAMESPACE = 'DeInternetJongens\\LighthouseUtils\\Directives';


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
            self::CONFIG_PATH => config_path('lightho   use-utils.php'),
        ], 'config');

        $this->registerDirectives();
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

    /**
     * @return void
     * @throws \ReflectionException
     */
    private function registerDirectives(): void
    {
        foreach (glob(self::DIRECTIVE_PATH . \DIRECTORY_SEPARATOR . '*.php') as $directiveFile) {
            // some/path/foo.bar -> some/path/foo
            $pathParts = explode('.', $directiveFile);
            $classNameWithPath = $pathParts[count($pathParts) - 2];

            // some/path/foo -> foo
            $classNameWithPathParts = explode(\DIRECTORY_SEPARATOR, $classNameWithPath);
            $className = end($classNameWithPathParts);

            // foo -> some\namespace\foo
            $namespace = self::DIRECTIVE_NAMESPACE;
            $class = $namespace . '\\' . $className;

            $reflectionClass = new ReflectionClass($class);

            // Things like abstract classes aren't instantiable, we don't register them.
            if ($reflectionClass->isInstantiable()) {
                \graphql()->directives()->register($reflectionClass->newInstance());
            }
        }
    }
}
