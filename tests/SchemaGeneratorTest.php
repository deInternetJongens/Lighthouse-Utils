<?php

namespace DeInternetJongens\LighthouseUtils\Tests;

use DeInternetJongens\LighthouseUtils\Exceptions\InvalidConfigurationException;
use DeInternetJongens\LighthouseUtils\Generators\SchemaGenerator;

class SchemaGeneratorTest extends TestCase
{
    public function testGenerateWithAllRequiredParametersReturnsString()
    {
        $schemaGenerator = new SchemaGenerator();
        $schema = $schemaGenerator->generate([
            'mutations' => __DIR__ . '/files/schema/Mutations',
            'queries' => __DIR__ . '/files/schema/Queries',
            'types' => __DIR__ . '/files/schema/Types',
        ]);

        $this->assertNotEmpty($schema, 'Schema is not empty');
    }

    public function testGenerateWithEmptyArrayThrowsException()
    {
        $schemaGenerator = new SchemaGenerator();
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "schema_paths" config value is empty, it should contain a value with a valid path for the following keys: mutations, queries, types');
        $schemaGenerator->generate([]);
    }

    public function testGenerateWithTwoMissingKeysThrowsException()
    {
        $schemaGenerator = new SchemaGenerator();
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "schema_paths" config value is incomplete, it should contain a value with a valid path for the following keys: mutations, queries, types');
        $schemaGenerator->generate([[
            'mutations' => 'app/GraphQL/Mutations',
        ]]);
    }

    public function testGenerateWithEmptyPathsThrowsException()
    {
        $schemaGenerator = new SchemaGenerator();
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
        $schemaGenerator = new SchemaGenerator();
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "schema_paths" config value for key "mutations" contains a path that does not exist');
        $schemaGenerator->generate([
            'mutations' => 'this-folder-does-not-exist',
            'queries' => 'this-folder-does-not-exist',
            'types' => 'this-folder-does-not-exist',
        ]);
    }
}
