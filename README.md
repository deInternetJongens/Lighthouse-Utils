# Lighthouse Utils

This package can generate queries for the [Lighthouse GraphQL library](https://github.com/nuwave/lighthouse).
This is not a standalone package, so Lighthouse is listed as a dependency.
A GraphiQL interface is also included to test your GraphQL interface.

## Installation

Install via composer
```bash
composer require deinternetjongens/lighthouse-utils
```

### Register Service Provider

**Note! This and next step are optional if you use laravel>=5.5 with package
auto discovery feature.**

Add service provider to `config/app.php` in `providers` section
```php
deinternetjongens\LighthouseUtils\ServiceProvider::class,
```

### Register Facade

Register package facade in `config/app.php` in `aliases` section
```php
deinternetjongens\LighthouseUtils\Facades\LighthouseUtils::class,
```

### Publish Configuration File

```bash
php artisan vendor:publish --provider="DeInternetJongens\LighthouseUtils\ServiceProvider" --tag="config"
``` 

## Contributing

Before committing, please run 
`./automate.sh`

This script will run all code style checks and phpunit tests. Fix all errors before opening a pull request.

## Usage

This package uses Laravel Auto Discovery to register itself with your application. 
It exposes a GraphQL interface interface on the `/graphql` route.

To get started, run the following command in your Laravel application:
```bash
php artisan vendor:publish --provider="Nuwave\Lighthouse\Providers\LighthouseServiceProvider" --tag="config"
```

## Migrations
This package stores the generated schema in the database so the schema is available outside the `schema.graphql` and can be used to sync permission.
Publish the migration and migrate the database.
```bash
php artisan vendor:publish --provider="DeInternetJongens\LighthouseUtils\ServiceProvider" --tag="migrations"
php artisan migrate
```

## Authorization
To protect your queries and migrations from unauthorized users just enable the `authorization` in the published config file.
When a schema is generated the event `DeInternetJongens\LighthouseUtils\Events\GraphQLSchemaGenerated` will be fired.
In your application you can listen for this event to sync the generated permissions with your application.
The event has a `schema` variable with the generated schema.

### Schema
Define your GraphQL schema by adding Types and Mutations in `app/GraphQL/Queries` and `app/GraphQL/Mutations` folders.
If you want to change these paths, public the config file for this package and change the paths there.  

For more information on schemas and basic Lighthouse usage, check [the Lighthouse docs](https://lighthouse-php.netlify.com/)

To generate your `schema.graphql` file, run the following command:

```bash
php artisan lighthouse-utils:generate-schema
```
The schema will be generated to the path as defined in the Lighthouse config, `lighthouse.schema.register`
