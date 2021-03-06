<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators\Classes;

use DeInternetJongens\LighthouseUtils\Generators\Classes\ParseDefinitions;
use DeInternetJongens\LighthouseUtils\Generators\Classes\ParsePermissions;
use DeInternetJongens\LighthouseUtils\Models\GraphQLSchema;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;

class ParseDefinitionsTest extends TestCase
{
    /** @var ParseDefinitions */
    private $definitionsParser;

    /** @var array */
    private $paths;

    protected function setUp()
    {
        parent::setUp();

        $this->definitionsParser = new ParseDefinitions(new ParsePermissions());

        $this->paths = [
            'mutations' => '../../../../tests/Unit/Generators/files/schema/Mutations',
            'queries' => '../../../../tests/Unit/Generators/files/schema/Queries',
        ];
    }

    public function testGetGraphqlDefinitionFilePaths()
    {
        $mutationFiles = $this->definitionsParser->getGraphqlDefinitionFilePaths($this->paths['mutations']);
        $queryFiles = $this->definitionsParser->getGraphqlDefinitionFilePaths($this->paths['queries']);

        $this->assertCount(1, $mutationFiles);
        $this->assertCount(1, $queryFiles);
    }

    public function testParseCustomQueriesFrom()
    {
        $queries = $this->definitionsParser->parseCustomQueriesFrom($this->paths['queries']);

        $this->assertCount(2, $queries);
        $this->assertEquals(
            '    test(id: ID! @eq): Model! @can(if: "testPermission", model: "User")  @find(model: "Model")',
            $queries[0]
        );
        $this->assertEquals(
            '    testTwo(id: ID! @eq): Model! @can(if: "testPermission", model: "User")  @find(model: "Model")',
            $queries[1]
        );
    }

    public function testParseCustomMutationsFrom()
    {
        $mutations = $this->definitionsParser->parseCustomMutationsFrom($this->paths['mutations']);

        $this->assertCount(2, $mutations);
        $this->assertEquals(
            '    test(input: testInput!): Model @create(model: "Model") @can(if: "testPermission", model: "User")',
            $mutations[0]
        );
        $this->assertEquals(
            '    secondTest(input: testInput!): Model @create(model: "Model") @can(if: "testPermission", model: "User")',
            $mutations[1]
        );
    }
}
