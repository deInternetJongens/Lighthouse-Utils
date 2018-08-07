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
                'expected_input_type' => '    input updateClubMemberInput {club_id: ID!name: Stringid: String}',
                'expected_mutation' => '    updateClubMember(input: updateClubMemberInput!): ClubMember @update(model: "ClubMember", flatten: true)',
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
                'expected_input_type' => '    input updateClubMemberInput {club_id: ID!name: String!id: String!}',
                'expected_mutation' => '    updateClubMember(input: updateClubMemberInput!): ClubMember @update(model: "ClubMember", flatten: true)',
            ],
            // No data given
            [
                'type_name' => '',
                'type_fields' => [],
                'expected_input_type' => '',
                'expected_mutation' => '',
            ],
            // Wrong type fields given
            [
                'type_name' => 'ClubMember',
                'type_fields' => [
                    'enum' => new EnumType([
                        'name' => 'enum',
                    ]),
                ],
                'expected_input_type' => '',
                'expected_mutation' => '',
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
                'expected_input_type' => '    input updateClubMemberInput {club_id: ID!name: String!}',
                'expected_mutation' => '    updateClubMember(input: updateClubMemberInput!): ClubMember @update(model: "ClubMember", flatten: true)',
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
    public function testCanGenerateUpdateMutationForClubMember(
        string $typeName,
        array $typeFields,
        string $expectedInputType,
        string $expectedMutation
    ): void {
        $mutationWithInput = UpdateMutationGenerator::generate(
            $typeName,
            $typeFields
        );

        $this->assertEquals($expectedInputType, str_replace(["\r", "\n"], '', $mutationWithInput->getInputType()));
        $this->assertEquals($expectedMutation, $mutationWithInput->getMutation());
    }
}
