# Lighthouse Generators

This package can generate queries for the [Lighthouse GraphQL library](https://github.com/nuwave/lighthouse).
This is not a standalone package, so Lighthouse is listed as a dependency.
A GraphiQL interface is also included to test your GraphQL interface.

## Installation

Install via composer
```bash
composer require deinternetjongens/lighthouse-generators
```

### Register Service Provider

**Note! This and next step are optional if you use laravel>=5.5 with package
auto discovery feature.**

Add service provider to `config/app.php` in `providers` section
```php
deinternetjongens\LighthouseGenerators\ServiceProvider::class,
```

### Register Facade

Register package facade in `config/app.php` in `aliases` section
```php
deinternetjongens\LighthouseGenerators\Facades\LighthouseGenerators::class,
```

### Publish Configuration File

```bash
php artisan vendor:publish --provider="deinternetjongens\LighthouseGenerators\ServiceProvider" --tag="config"
```

## Contributing

Before committing, please run 
`./automate.sh`

This script will run all code style checks and phpunit tests. Fix all errors before opening a pull request.

## Usage

This package uses Laravel Auto Discovery to register itself with your application. 
It exposes a GraphQL interface interface on the `/graphql` route.

To get started, run the following command in your Laravel application:
`php artisan vendor:publish --provider="Nuwave\Lighthouse\Providers\LighthouseServiceProvider"`

A config file will be generated: `config/lighthouse.php`. A GraphQL Schema will also be generated in `routes/graphql/schema.graphql`.  

For Lighthouse usage, check [the Lighthouse docs](https://lighthouse-php.netlify.com/)

To regenerate GraphiQL in your project, run the following command:
`php artisan graphiql:publish`
For further information about GraphiQL, please check the [readme for the GraphiQL package](https://github.com/Nohac/laravel-graphiql)
