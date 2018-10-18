<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators;

use DeInternetJongens\LighthouseUtils\Events\GraphQLSchemaGenerated;
use DeInternetJongens\LighthouseUtils\Exceptions\InvalidConfigurationException;
use DeInternetJongens\LighthouseUtils\Generators\SchemaGenerator;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use Illuminate\Support\Facades\Event;

class SchemaGeneratorTest extends TestCase
{
    public function testGenerateWithAllRequiredParametersReturnsString()
    {
        $schemaGenerator = app()->make(SchemaGenerator::class);
        $schema = $schemaGenerator->generate([
            'mutations' => __DIR__ . '/files/emptySchema/Mutations',
            'queries' => __DIR__ . '/files/emptySchema/Queries',
            'types' => __DIR__ . '/files/emptySchema/Types',
        ]);

        $this->assertNotEmpty($schema, 'Schema is not empty');
    }

    public function testGenerateWithEmptyArrayThrowsException()
    {
        $schemaGenerator = app()->make(SchemaGenerator::class);
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "schema_paths" config value is empty, it should contain a value with a valid path for the following keys: mutations, queries, types');
        $schemaGenerator->generate([]);
    }

    public function testGenerateWithTwoMissingKeysThrowsException()
    {
        $schemaGenerator = app()->make(SchemaGenerator::class);
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "schema_paths" config value is incomplete, it should contain a value with a valid path for the following keys: mutations, queries, types');
        $schemaGenerator->generate([
            [
                'mutations' => 'app/GraphQL/Mutations',
            ],
        ]);
    }

    public function testGenerateWithEmptyPathsThrowsException()
    {
        $schemaGenerator = app()->make(SchemaGenerator::class);
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "schema_paths" config value for key "mutations" is empty, it should contain a value with a valid path');
        $schemaGenerator->generate([
            'mutations' => '',
            'queries' => '',
            'types' => '',
        ]);
    }

    public function testGenerateWithNonExistingPathsThrowsException()
    {
        $schemaGenerator = app()->make(SchemaGenerator::class);
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "schema_paths" config value for key "mutations" contains a path that does not exist');
        $schemaGenerator->generate([
            'mutations' => 'this-folder-does-not-exist',
            'queries' => 'this-folder-does-not-exist',
            'types' => 'this-folder-does-not-exist',
        ]);
    }

    public function testEventIsFiredAfterGeneratingSchema()
    {
        Event::fake();

        $schemaGenerator = app()->make(SchemaGenerator::class);

        $schemaGenerator->generate([
            'mutations' => __DIR__ . '/files/emptySchema/Mutations',
            'queries' => __DIR__ . '/files/emptySchema/Queries',
            'types' => __DIR__ . '/files/emptySchema/Types',
        ]);

        Event::assertDispatched(GraphQLSchemaGenerated::class);
    }
}
