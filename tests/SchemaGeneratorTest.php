<?php

namespace deinternetjongens\LighthouseUtils\Tests;

use deinternetjongens\LighthouseUtils\Generators\SchemaGenerator;
use PHPUnit\Framework\TestCase;

class SchemaGeneratorTest extends TestCase
{
    public function testGenerateWithAllRequiredParametersReturnsString()
    {
        $this->markTestIncomplete('This test cannot function without Laravel framework');
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
        $this->expectException(\Exception::class);
        $schemaGenerator->generate([]);
    }

    public function testGenerateWithTwoMissingKeysThrowsException()
    {
        $schemaGenerator = new SchemaGenerator();
        $this->expectException(\Exception::class);
        $schemaGenerator->generate([[
            'mutations' => 'app/GraphQL/Mutations',
        ]]);
    }

    public function testGenerateWithEmptyPathsThrowsException()
    {
        $schemaGenerator = new SchemaGenerator();
        $this->expectException(\Exception::class);
        $schemaGenerator->generate([
            'mutations' => '',
            'queries' => '',
            'types' => '',
        ]);
    }

    public function testGenerateWithNonExistingPathsThrowsException()
    {
        $schemaGenerator = new SchemaGenerator();
        $this->expectException(\Exception::class);
        $schemaGenerator->generate([
            'mutations' => 'this-folder-does-not-exist',
            'queries' => 'this-folder-does-not-exist',
            'types' => 'this-folder-does-not-exist',
        ]);
    }
}
