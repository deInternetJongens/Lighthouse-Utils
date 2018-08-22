<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators\Arguments;

use DeInternetJongens\LighthouseUtils\Generators\Arguments\RelationArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\StringType;

class RelationArgumentGeneratorTest extends TestCase
{
    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            // Happy flow, not required
            [
                'type_fields' => [
                    'club' => new ObjectType([
                        'name' => 'club',
                    ]),
                ],
                'expected_arguments' => [
                    'club_id: ID',
                ],
            ],
            // Happy flow, required
            [
                'type_fields' => [
                    'club' => new ObjectType([
                        'name' => 'club',
                        'generator-required' => true,
                    ]),
                ],
                'expected_arguments' => [
                    'club_id: ID!',
                ],
            ],
            // Multiple types, not required
            [
                'type_fields' => [
                    'club' => new ObjectType([
                        'name' => 'club',
                    ]),
                    'club_member' => new ObjectType([
                        'name' => 'club_member',
                    ]),
                ],
                'expected_arguments' => [
                    'club_id: ID',
                    'club_member_id: ID'
                ],
            ],
            // Multiple types, required
            [
                'type_fields' => [
                    'club' => new ObjectType([
                        'name' => 'club',
                        'generator-required' => true,
                    ]),
                    'club_member' => new ObjectType([
                        'name' => 'club_member',
                        'generator-required' => true,
                    ]),
                ],
                'expected_arguments' => [
                    'club_id: ID!',
                    'club_member_id: ID!'
                ],
            ],
            // Wrong type
            [
                'type_fields' => [
                    'club' => new StringType(),
                ],
                'expected_arguments' => [],
            ],
            // Wrong type, but required
            [
                'type_fields' => [
                    'club' => new StringType([
                        'generator-required' => true,
                    ]),
                ],
                'expected_arguments' => [],
            ],
            // No data given
            [
                'type_fields' => [],
                'expected_arguments' => [],
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param array $typeFields
     * @param bool $required
     * @param array $expectedArguments
     */
    public function testCanGenerateRelationArgument(array $typeFields, array $expectedArguments): void
    {
        $arguments = RelationArgumentGenerator::generate($typeFields);

        $this->assertEquals($expectedArguments, $arguments);
    }
}
