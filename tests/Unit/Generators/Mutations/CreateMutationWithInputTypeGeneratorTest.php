<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators\Mutations;

use DeInternetJongens\LighthouseUtils\Generators\Mutations\CreateMutationWithInputTypeGenerator;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\UnionType;

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
                'containsMutationAndInputType' => true
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
                'containsMutationAndInputType' => true
            ],
            'no type fields given' => [
                'type_name' => 'ClubMember',
                'type_fields' => [],
                'expected_input_type' => '',
                'expected_mutation' => '',
                'containsMutationAndInputType' => false
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
                'containsMutationAndInputType' => false
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param string $typeName
     * @param array $typeFields
     * @param string $expectedInputType
     * @param string $expectedMutation
     * @param bool $containsMutationAndInputType
     */
    public function testCanGenerateCreateMutationWithInputTypeForClubMember(
        string $typeName,
        array $typeFields,
        string $expectedInputType,
        string $expectedMutation,
        $containsMutationAndInputType = true
    ): void {
        $mutationWithInput = CreateMutationWithInputTypeGenerator::generate(
            $typeName,
            $typeFields
        );

        $this->assertEquals($containsMutationAndInputType, $mutationWithInput->isNotEmpty(), 'Contains a mutation and input type');
        $this->assertEquals($expectedInputType, $mutationWithInput->getInputType(), 'Expected input type');
        $this->assertEquals($expectedMutation, $mutationWithInput->getMutation(), 'Expected mutation');
    }
}
