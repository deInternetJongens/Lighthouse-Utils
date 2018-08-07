<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators\Mutations;

use DeInternetJongens\LighthouseUtils\Generators\Mutations\UpdateMutationGenerator;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\StringType;

class UpdateMutationGeneratorTest extends TestCase
{
    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            // Happy flow
            [
                'type_name' => 'ClubMember',
                'type_fields' => [
                    'club_id' => new IDType(),
                    'name' => new StringType(),
                    'id' => new StringType(),
                ],
                'expected_query' => '    updateClubMember(club_id: ID!, name: String, id: String): ClubMember @update(model: "ClubMember") @can(if: "update", model: "ClubMember")',
            ],
            // Happy flow, required fields
            [
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
                'expected_query' => '    updateClubMember(club_id: ID!, name: String!, id: String!): ClubMember @update(model: "ClubMember") @can(if: "update", model: "ClubMember")',
            ],
            // No data given
            [
                'type_name' => '',
                'type_fields' => [],
                'expected_query' => '',
            ],
            // Wrong type fields given
            [
                'type_name' => 'ClubMember',
                'type_fields' => [
                    'enum' => new EnumType([
                        'name' => 'enum',
                    ]),
                ],
                'expected_query' => '',
            ],
            // Correct and wrong type fields given
            [
                'type_name' => 'ClubMember',
                'type_fields' => [
                    'club_id' => new IDType(),
                    'name' => new StringType([
                        'generator-required' => true,
                    ]),
                    'enum' => new EnumType([
                        'name' => 'enum',
                    ]),
                ],
                'expected_query' => '    updateClubMember(club_id: ID!, name: String!): ClubMember @update(model: "ClubMember") @can(if: "update", model: "ClubMember")',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param string $typeName
     * @param array $typeFields
     * @param string $expectedQuery
     */
    public function testCanGenerateUpdateMutationForClubMember(
        string $typeName,
        array $typeFields,
        string $expectedQuery
    ): void {
        $query = UpdateMutationGenerator::generate(
            $typeName,
            $typeFields
        );

        $this->assertEquals($expectedQuery, $query);
    }
}
