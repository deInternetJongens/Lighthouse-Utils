<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators\Queries;

use DeInternetJongens\LighthouseUtils\Generators\Queries\PaginateAllQueryGenerator;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\StringType;

class PaginateAllQueryGeneratorTest extends TestCase
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
                "    clubmembers(id: ID @eq, id_not: ID @not, id_in: ID @in, id_not_in: ID @not_in, id_lt: ID @lt, id_lte: ID @lte, id_gt: ID @gt, id_gte: ID @gte): [ClubMember]! @all(model: \"ClubMember\") @can(if: \"AllClubMembers\", model: \"User\")\r\n    clubmembersPaginated(id: ID @eq, id_not: ID @not, id_in: ID @in, id_not_in: ID @not_in, id_lt: ID @lt, id_lte: ID @lte, id_gt: ID @gt, id_gte: ID @gte): [ClubMember]! @paginate(model: \"ClubMember\") @can(if: \"paginateClubMembers\", model: \"User\")",

            ],
            'Happy flow, string type' => [
                'type_name' => 'ClubMember',
                'type_fields' => [
                    'id' => new StringType(),
                ],
                'expected_query' => "    clubmembers(id: String @eq, id_not: String @not, id_in: String @in, id_not_in: String @not_in, id_lt: String @lt, id_lte: String @lte, id_gt: String @gt, id_gte: String @gte, id_contains: String @contains, id_not_contains: String @not_contains, id_starts_with: String @starts_with, id_not_starts_with: String @not_starts_with, id_ends_with: String @not_ends_with): [ClubMember]! @all(model: \"ClubMember\") @can(if: \"AllClubMembers\", model: \"User\")\r\n    clubmembersPaginated(id: String @eq, id_not: String @not, id_in: String @in, id_not_in: String @not_in, id_lt: String @lt, id_lte: String @lte, id_gt: String @gt, id_gte: String @gte, id_contains: String @contains, id_not_contains: String @not_contains, id_starts_with: String @starts_with, id_not_starts_with: String @not_starts_with, id_ends_with: String @not_ends_with): [ClubMember]! @paginate(model: \"ClubMember\") @can(if: \"paginateClubMembers\", model: \"User\")",
            ],
            'No type fields given' => [
                'type_name' => 'ClubMember',
                'type_fields' => [],
                'expected_query' => '',
            ],
            'No type name given' => [
                'type_name' => '',
                'type_fields' => [
                    'id' => new IDType(),
                ],
                'expected_query' => "    (id: ID @eq, id_not: ID @not, id_in: ID @in, id_not_in: ID @not_in, id_lt: ID @lt, id_lte: ID @lte, id_gt: ID @gt, id_gte: ID @gte): []! @all(model: \"\") @can(if: \"All\", model: \"User\")\r\n    Paginated(id: ID @eq, id_not: ID @not, id_in: ID @in, id_not_in: ID @not_in, id_lt: ID @lt, id_lte: ID @lte, id_gt: ID @gt, id_gte: ID @gte): []! @paginate(model: \"\") @can(if: \"paginate\", model: \"User\")",
            ],
            'Wrong type field given' => [
                'type_name' => '',
                'type_fields' => [
                    'id' => new ObjectType([
                        'name' => 'id'
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
     * @param string $expectedQueries
     */
    public function testCanGenerateAllQueryForIDType(
        string $typeName,
        array $typeFields,
        string $expectedQueries
    ): void {
        $query = PaginateAllQueryGenerator::generate($typeName, $typeFields);

        $this->assertEquals($expectedQueries, $query, 'Expected query');
    }
}
