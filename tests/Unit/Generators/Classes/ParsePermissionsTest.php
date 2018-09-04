<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators\Classes;

use DeInternetJongens\LighthouseUtils\Generators\Classes\ParsePermissions;
use DeInternetJongens\LighthouseUtils\Models\GraphQLSchema;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;

class ParsePermissionsTest extends TestCase
{
    /** @var ParsePermissions */
    private $permissionParser;

    protected function setUp()
    {
        parent::setUp();

        $this->permissionParser = new ParsePermissions();
    }

    public function testGraphQLSchemaRowsAreRegistered()
    {
        $graphQLSchema = 'type Query{
                test(id: ID! @eq): Model! @can(if: "testPermission", model: "User")  @find(model: "Model")
                testTwo(id: ID! @eq): Model! @can(if: "testPermission", model: "User")  @find(model: "Model")
            }';

        $queries = $this->permissionParser->register($graphQLSchema);

        $this->assertCount(
            count($queries),
            GraphQLSchema::where('permission', 'testPermission')->get()
        );
    }

    public function testQueryWithSyntaxErrorThrowsSyntaxErrorException()
    {
        $graphQLSchema = 'type Query{
                test(id: ID! @eq):  @find(model: "Model")
            }';

        $this->expectException(\GraphQL\Error\SyntaxError::class);

        $this->permissionParser->register($graphQLSchema);
    }

    public function testQueryWithoutCan()
    {
        $graphQLSchema = 'type Query{
                test(id: ID! @eq): Model! @find(model: "Model")
            }';

        $this->permissionParser->register($graphQLSchema);

        $this->assertCount(
            0,
            GraphQLSchema::all()
        );
    }
}
