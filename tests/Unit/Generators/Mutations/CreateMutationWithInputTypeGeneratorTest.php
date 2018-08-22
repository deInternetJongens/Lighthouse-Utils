<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators\Mutations;

use DeInternetJongens\LighthouseUtils\Generators\Mutations\CreateMutationWithInputTypeGenerator;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\StringType;

class CreateMutationWithInputTypeGeneratorTest extends TestCase
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
                'expected_input_type' => 'input createClubMemberInput {name: String id: String}',
                'expected_mutation' => '    createClubMember(input: createClubMemberInput!): ClubMember @create(model: "ClubMember", flatten: true) @can(if: "createClubMember", model: "User")',
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
                'expected_input_type' => 'input createClubMemberInput {name: String! id: String!}',
                'expected_mutation' => '    createClubMember(input: createClubMemberInput!): ClubMember @create(model: "ClubMember", flatten: true) @can(if: "createClubMember", model: "User")',
            ],
            'no type fields given' => [
                'type_name' => 'ClubMember',
                'type_fields' => [],
                'expected_input_type' => '',
                'expected_mutation' => '',
            ],
            'Wrong type fields given' => [
                'type_name' => 'ClubMember',
                'type_fields' => [
                    'enum' => new EnumType([
                        'name' => 'enum',
                    ]),
                ],
                'expected_input_type' => '',
                'expected_mutation' => '',
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
    public function testCanGenerateCreateMutationWithInputTypeForClubMember(
        string $typeName,
        array $typeFields,
        string $expectedInputType,
        string $expectedMutation
    ): void {
        $mutationWithInput = CreateMutationWithInputTypeGenerator::generate(
            $typeName,
            $typeFields
        );

        $this->assertEquals($expectedInputType, $mutationWithInput->getInputType(), 'Expected input type');
        $this->assertEquals($expectedMutation, $mutationWithInput->getMutation(), 'Expected mutation');
    }
}
