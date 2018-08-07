<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators\Queries;

use DeInternetJongens\LighthouseUtils\Generators\Queries\FindQueryGenerator;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\IntType;

class FindQueryGeneratorTest extends TestCase
{
    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            'Happy flow' => [
                'type_name' => 'ClubMember',
                'type_fields' => [
                    'id' => new IDType(),
                ],
                'expected_query' => '    clubmember(id: ID! @eq): ClubMember! @find(model: "ClubMember")',
            ],
            'Missing type field given' => [
                'type_name' => 'ClubMember',
                'type_fields' => [
                    'id' => '',
                ],
                'expected_query' => '',
            ],
            'Wrong type field given' => [
                'type_name' => 'ClubMember',
                'type_fields' => [
                    'id' => new IntType(),
                ],
                'expected_query' => '',
            ],
            'No type name given' => [
                'type_name' => '',
                'type_fields' => [
                    'id' => new IDType(),
                ],
                'expected_query' => '    (id: ID! @eq): ! @find(model: "")',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param string $typeName
     * @param array $typeFields
     * @param string $expectedQuery
     */
    public function testCanGenerateFindQueryForIDType(string $typeName, array $typeFields, string $expectedQuery): void
    {
        $query = FindQueryGenerator::generate($typeName, $typeFields);

        $this->assertEquals($expectedQuery, $query, 'Expected query');
    }
}
