<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators\Mutations;

use DeInternetJongens\LighthouseUtils\Generators\Mutations\CreateMutationGenerator;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\UnionType;

class CreateMutationGeneratorTest extends TestCase
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
                    'club_id' => new IDType(),
                    'name' => new StringType(),
                    'id' => new StringType(),
                ],
                'expected_query' => '    createClubMember(name: String, id: String): ClubMember @create(model: "ClubMember") @can(if: "createClubMember", model: "User")',
            ],
            'Happy flow, required fields' => [
                'type_name' => 'ClubMember',
                'type_fields' => [
                    'club_id' => new IDType(),
                    'name' => new StringType([
                        'generator-required' => true,
                    ]),
                    'id' => new StringType([
                        'generator-required' => true,
                    ]),
                ],
                'expected_query' => '    createClubMember(name: String!, id: String!): ClubMember @create(model: "ClubMember") @can(if: "createClubMember", model: "User")',
            ],
            'No type fields given' => [
                'type_name' => 'ClubMember',
                'type_fields' => [],
                'expected_query' => '',
            ],
            'Wrong type fields given' => [
                'type_name' => 'ClubMember',
                'type_fields' => [
                    'union' => new UnionType(
                        [
                            'name' => 'union',
                        ]
                    ),
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
    public function testCanGenerateCreateMutationForClubMember(
        string $typeName,
        array $typeFields,
        string $expectedQuery
    ): void {
        $mutation = CreateMutationGenerator::generate(
            $typeName,
            $typeFields
        );

        $this->assertEquals($expectedQuery, $mutation, 'Expected mutation');
    }
}
