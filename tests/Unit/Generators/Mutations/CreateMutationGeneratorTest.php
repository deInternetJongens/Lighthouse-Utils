<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators\Mutations;

use DeInternetJongens\LighthouseUtils\Generators\Mutations\CreateMutationGenerator;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\StringType;

class CreateMutationGeneratorTest extends TestCase
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
                'expected_query' => '    createClubMember(name: String, id: String): ClubMember @create(model: "ClubMember")',
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
                'expected_query' => '    createClubMember(name: String!, id: String!): ClubMember @create(model: "ClubMember")',
            ],
            // No type fields given
            [
                'type_name' => 'ClubMember',
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
        $query = CreateMutationGenerator::generate(
            $typeName,
            $typeFields
        );

        $this->assertEquals($expectedQuery, $query);
    }
}
