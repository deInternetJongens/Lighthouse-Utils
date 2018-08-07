<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators\Mutations;

use DeInternetJongens\LighthouseUtils\Generators\Mutations\DeleteMutationGenerator;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\StringType;

class DeleteMutationGeneratorTest extends TestCase
{
    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            // Happy flow
            [
                'type_name' => 'Club',
                'type_fields' => [
                    'id' => new IDType(),
                ],
                'expected_query' => '    deleteClub(id: ID!): Club @delete @can(if: "deleteClub", model: "User")',
            ],
            // Multiple types given
            [
                'type_name' => 'Club',
                'type_fields' => [
                    'id' => new IDType(),
                    'district_id' => new IDType(),
                ],
                'expected_query' => '    deleteClub(id: ID!): Club @delete @can(if: "deleteClub", model: "User")',
            ],
            // No data given
            [
                'type_name' => '',
                'type_fields' => [],
                'expected_query' => '',
            ],
            // Wrong type given
            [
                'type_name' => 'Club',
                'type_fields' => [
                    'id' => new StringType(),
                ],
                'expected_query' => '',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param string $typeName
     * @param array $typeFields
     * @param string $expectedQuery
     */
    public function testCanGenerateDeleteMutation(string $typeName, array $typeFields, string $expectedQuery): void
    {
        $query = DeleteMutationGenerator::generate($typeName, $typeFields);

        $this->assertEquals($expectedQuery, $query);
    }
}
