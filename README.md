# Lighthouse Utils (no longer maintained)
[![Build Status](https://travis-ci.org/deInternetJongens/Lighthouse-Utils.svg?branch=develop)](https://travis-ci.org/deInternetJongens/Lighthouse-Utils)
[![Code Coverage](https://codecov.io/gh/deInternetJongens/Lighthouse-Utils/branch/develop/graph/badge.svg)](https://codecov.io/gh/deInternetJongens/Lighthouse-Utils)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/deInternetJongens/Lighthouse-Utils/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/deInternetJongens/Lighthouse-Utils/?branch=develop)
[![Latest Unstable Version](https://poser.pugx.org/deinternetjongens/lighthouse-utils/v/unstable)](https://packagist.org/packages/deinternetjongens/lighthouse-utils)

This package can generate queries for the [Lighthouse GraphQL library](https://github.com/nuwave/lighthouse).
This is not a standalone package, so Lighthouse is listed as a dependency.

To generate queries, all you need to do is define a couple of [GraphQL Types](https://lighthouse-php.netlify.com/docs/schema.html) and run the generate command. 
Scroll down to 'Schema' to learn more.  

We also include a couple of [Directives](https://lighthouse-php.netlify.com/docs/directives-queries.html) and [Scalar types](https://lighthouse-php.netlify.com/docs/schema.html).
More about that later on.

## Installation

Install via composer
```bash
composer require deinternetjongens/lighthouse-utils
```
Alternatively, you can try the example installation below.

### Example installation

An example installation is available at: https://github.com/maarten00/lighthouse-utils-example

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
php artisan vendor:publish --provider="Nuwave\Lighthouse\Providers\LighthouseServiceProvider"
php artisan vendor:publish --provider="DeInternetJongens\LighthouseUtils\ServiceProvider" --tag="migrations"
php artisan migrate
```
A config file will be generated: `config/lighthouse.php`. You can change these values if you want.  

## Schema generation

Define your GraphQL schema by adding Types, Queries and Mutations in `app/GraphQL/Types`, `app/GraphQL/Queries` and `app/GraphQL/Mutations` directories.
If you want to change these paths, publish the config file for this package and change the paths there.

Currently this package only supports custom Types, Queries and Mutations are ignored. 
You can define your custom types and run the command below to auto-generate queries and mutations for those types.
In the future it will be possible to add custom queries and mutations to the above directories. These will then also be used in the generated schema. 

For more information on schemas and basic Lighthouse usage, check [the Lighthouse docs](https://lighthouse-php.netlify.com/)

To generate your `schema.graphql` file, run the following command:

```bash
php artisan lighthouse-utils:generate-schema
```
The schema will be generated to the path as defined in the Lighthouse config, `lighthouse.schema.register`

## Custom Queries and Mutations

It might happen that you need a custom query or mutation beside the generated schema. In this package you have the ability to add custom queries and mutations by creating `.graphql` files in the default directories
`app/GraphQL/Queries` and `app/GraphQL/Mutations` *(These directories are adjustable by editing the `config/lighthouse.php` file)* 

Take for example a custom query to retrieve an instance of a model:
```graphql
type Query{
    customQuery(id: ID! @eq): Model! @find(model: "Model")
}
```

This query will be parsed after running the schema generation command and will be added to the Query section of the `schema.graphql`

### Scalar types

Currently two scalar types are included. More about scalar type usage can be [found here](https://lighthouse-php.netlify.com/docs/schema-scalars.html).

#### Date

A date string with format Y-m-d. Example: "2018-01-01"

`scalar Date @scalar(class: "DeInternetJongens\\LighthouseUtils\\Schema\\Scalars\\Date")`

#### DateTimeTZ

A date string with format Y-m-d H:i:s+P. Example: "2018-01-01 13:00:00+00:00"

`scalar DateTimeTz @scalar(class: "DeInternetJongens\\LighthouseUtils\\Schema\\Scalars\\DateTimeTz")`

#### PostalCodeNl

A postal code as valid for The Netherlands, format 1111aa. Example: "7311SZ"

#### Email

An RFC 5321 compliant e-mail

#### FullTextSearch

Indicates that a field searches through multiple fields.
To use this scalar, you need to add a scope on your model `scopeFullTextSearch`. Example: 
```php
public function scopeFullTextSearch(Builder $builder, $value)
{
    return $builder->whereRaw("column_one % ? OR column_two % ?", [$value, $value])
        ->orderByRaw('column_two <-> ?', [$value]);
}
```

In the scope you can basically define whatever kind of query you want.

### Directives

To run more advanced queries, a couple of directives are included. 
These are automatically registered with Lighthouse, so you can use them at your own discretion.

Currently these directives are included:

- contains
- ends_with
- gte
- gt
- lte
- lt
- in (comma seperated string)
- not_contains
- not_ends_with
- not
- not_in (comma seperated string)
- not_starts_with
- starts_with
- fullTextSearch

### Migrations

This package stores the generated schema in the database, so the schema is available outside the `schema.graphql` and can be used to sync permission.
Publish the migration and migrate the database.

```bash
php artisan vendor:publish --provider="DeInternetJongens\LighthouseUtils\ServiceProvider" --tag="migrations"
php artisan migrate
```


### Authorization

To protect your queries and migrations from unauthorized users, you can enable the Authorization feature.
To enable authorization, make sure you have published the config for this package and add the following line to your .env file:

`LIGHTHOUSE_UTILS_AUTHORIZATION=true`

When a schema is generated the event `DeInternetJongens\LighthouseUtils\Events\GraphQLSchemaGenerated` will be fired.
In your application you can listen for this event to sync the generated permissions with your application.
The event has a `schema` variable with the generated schema.

The generated queries and their corresponding permissions will also be persisted to your database to the `graphql_schema` table. 
An Eloquent model for this table is included with this package.
