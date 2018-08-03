<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators\Arguments;

use DeInternetJongens\LighthouseUtils\Generators\Arguments\InputFieldsArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\StringType;

class InputFieldsArgumentGeneratorTest extends TestCase
{
    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            // Happy flow, not required field
            [
                'type_fields' => [
                    'name' => new StringType(),
                ],
                'expected_arguments' => [
                    'name: String',
                ],
            ],
            // Happy flow, required field
            [
                'type_fields' => [
                    'name' => new StringType([
                        'generator-required' => true,
                    ]),
                ],
                'expected_arguments' => [
                    'name: String!',
                ],
            ],
            // Wrong type given
            [
                'type_fields' => [
                    'name' => new IDType(),
                ],
                'expected_arguments' => [],
            ],
            // No data given
            [
                'type_fields' => [],
                'expected_arguments' => [],
            ],
            // Type fields that are ignored
            [
                'type_fields' => [
                    'created_at' => new StringType,
                    'updated_at' => new StringType,
                    'deleted_at' => new StringType,
                ],
                'expected_arguments' => [],
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param array $typeFields
     * @param array $expectedArguments
     */
    public function testCanGenerateIdArgument(array $typeFields, array $expectedArguments): void
    {
        $arguments = InputFieldsArgumentGenerator::generate($typeFields);

        $this->assertEquals($expectedArguments, $arguments);
    }
}
