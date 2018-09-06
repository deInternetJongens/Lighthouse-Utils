<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators\Mutations;

use DeInternetJongens\LighthouseUtils\Generators\Mutations\UpdateMutationWithInputTypeGenerator;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\UnionType;

class UpdateMutationWithInputTypeGeneratorTest extends TestCase
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
                'expected_input_type' => 'input updateClubMemberInput {name: String id: String club_id: ID!}',
                'expected_mutation' => '    updateClubMember(input: updateClubMemberInput!): ClubMember @update(model: "ClubMember", flatten: true) @can(if: "updateClubMember", model: "User")',
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
                'expected_input_type' => 'input updateClubMemberInput {name: String! id: String! club_id: ID!}',
                'expected_mutation' => '    updateClubMember(input: updateClubMemberInput!): ClubMember @update(model: "ClubMember", flatten: true) @can(if: "updateClubMember", model: "User")',
            ],
            'No data given' => [
                'type_name' => '',
                'type_fields' => [],
                'expected_input_type' => '',
                'expected_mutation' => '',
            ],
            'Wrong type fields given' => [
                'type_name' => 'ClubMember',
                'type_fields' => [
                    'union' => new UnionType([
                        'name' => 'union',
                    ]),
                ],
                'expected_input_type' => '',
                'expected_mutation' => '',
            ],
            'Correct and wrong type fields given' => [
                'type_name' => 'ClubMember',
                'type_fields' => [
                    'club_id' => new IDType(),
                    'name' => new StringType([
                        'generator-required' => true,
                    ]),
                    'union' => new UnionType([
                        'name' => 'union',
                    ]),
                ],
                'expected_input_type' => 'input updateClubMemberInput {name: String! club_id: ID!}',
                'expected_mutation' => '    updateClubMember(input: updateClubMemberInput!): ClubMember @update(model: "ClubMember", flatten: true) @can(if: "updateClubMember", model: "User")',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param string $typeName
     * @param array $typeFields
     * @param string $expectedInputType
     * @param string $expectedMutation
     */
    public function testCanGenerateUpdateMutationWithInputTypeForClubMember(
        string $typeName,
        array $typeFields,
        string $expectedInputType,
        string $expectedMutation
    ): void {
        $mutationWithInput = UpdateMutationWithInputTypeGenerator::generate(
            $typeName,
            $typeFields
        );

        $this->assertEquals($expectedInputType, $mutationWithInput->getInputType(), 'Expected input type');
        $this->assertEquals($expectedMutation, $mutationWithInput->getMutation(), 'Expected mutation');
    }
}
