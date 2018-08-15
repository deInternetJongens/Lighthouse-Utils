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
                'expected_input_type' => 'input whereClubMembersInput {id: ID @eq id_not: ID @not id_in: ID @in id_not_in: ID @not_in id_lt: ID @lt id_lte: ID @lte id_gt: ID @gt id_gte: ID @gte}',
                'expected_queries' => [
                    '    clubmembers(input: whereClubMembersInput): [ClubMember]! @all(model: "ClubMember", flatten: true) @can(if: "findAllClubMembers", model: "User")',
                    '    clubmembersPaginated(input: whereClubMembersInput): [ClubMember]! @paginate(model: "ClubMember", flatten: true) @can(if: "paginateClubMembers", model: "User")',
                ],
            ],
            'Happy flow, string type' => [
                'type_name' => 'ClubMember',
                'type_fields' => [
                    'id' => new StringType(),
                ],
                'expected_input_type' => 'input whereClubMembersInput {id: String @eq id_not: String @not id_in: String @in id_not_in: String @not_in id_lt: String @lt id_lte: String @lte id_gt: String @gt id_gte: String @gte id_contains: String @contains id_not_contains: String @not_contains id_starts_with: String @starts_with id_not_starts_with: String @not_starts_with id_ends_with: String @not_ends_with}',
                'expected_queries' => [
                    '    clubmembers(input: whereClubMembersInput): [ClubMember]! @all(model: "ClubMember", flatten: true) @can(if: "findAllClubMembers", model: "User")',
                    '    clubmembersPaginated(input: whereClubMembersInput): [ClubMember]! @paginate(model: "ClubMember", flatten: true) @can(if: "paginateClubMembers", model: "User")',
                ],
            ],
            'No type fields given' => [
                'type_name' => 'ClubMember',
                'type_fields' => [],
                'expected_input_type' => '',
                'expected_queries' => [],
            ],
            'No type name given' => [
                'type_name' => '',
                'type_fields' => [
                    'id' => new IDType(),
                ],
                'expected_input_type' => 'input whereInput {id: ID @eq id_not: ID @not id_in: ID @in id_not_in: ID @not_in id_lt: ID @lt id_lte: ID @lte id_gt: ID @gt id_gte: ID @gte}',
                'expected_queries' => [
                    '    (input: whereInput): []! @all(model: "", flatten: true) @can(if: "findAll", model: "User")',
                    '    Paginated(input: whereInput): []! @paginate(model: "", flatten: true) @can(if: "paginate", model: "User")',
                ],
            ],
            'Wrong type field given' => [
                'type_name' => '',
                'type_fields' => [
                    'id' => new ObjectType([
                        'name' => 'id'
                    ]),
                ],
                'expected_input_type' => '',
                'expected_queries' => [],
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param string $typeName
     * @param array $typeFields
     * @param string $expectedInputType
     * @param array $expectedQueries
     */
    public function testCanGenerateAllQueryForIDType(
        string $typeName,
        array $typeFields,
        string $expectedInputType,
        array $expectedQueries
    ): void
    {
        $query = PaginateAllQueryGenerator::generate($typeName, $typeFields);

        $this->assertEquals($expectedInputType, $query->getInputType(), 'Expected input type');
        $this->assertEquals($expectedQueries, $query->getQueries(), 'Expected queries');
    }
}
