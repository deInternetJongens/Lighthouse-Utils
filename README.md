# Lighthouse Generators

Package description: This package can generate queries for the [Lighthouse GraphQL library](https://github.com/nuwave/lighthouse).

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

CHANGE ME

