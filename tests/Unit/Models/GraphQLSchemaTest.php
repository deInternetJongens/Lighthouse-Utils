<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators;

use DeInternetJongens\LighthouseUtils\Models\GraphQLSchema;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;

class GraphQLSchemaTest extends TestCase
{
    public function testAGraphQlSchemaCanBeRegistered()
    {
        $testRow = GraphQLSchema::register('action', 'RandomModel', 'testType', 'testRegister');

        $this->assertEquals(1, GraphQLSchema::count());
        $this->assertEquals('actionRandomModel', $testRow->name);
        $this->assertEquals('RandomModel', $testRow->model);
        $this->assertEquals('testType', $testRow->type);
        $this->assertEquals('testRegister', $testRow->permission);
    }
}
