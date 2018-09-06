<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators\Arguments;

use DeInternetJongens\LighthouseUtils\Generators\Arguments\InputTypeArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\UnionType;

class InputTypeArgumentGeneratorTest extends TestCase
{
    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            'Happy flow' => [
                'input_name' => 'name',
                'type_fields' => [
                    'name' => new StringType(),
                ],
                'expected_argument' => 'input name {name: String}',
            ],
            'Happy flow, required field' => [
                'input_name' => 'name',
                'type_fields' => [
                    'name' => new StringType([
                        'generator-required' => true,
                    ]),
                ],
                'expected_argument' => 'input name {name: String!}',
            ],
            'Wrong type given' => [
                'input_name' => 'union',
                'type_fields' => [
                    'union' => new UnionType(['name' => 'test']),
                ],
                'expected_argument' => '',
            ],
            'No data given' => [
                'input_name' => '',
                'type_fields' => [],
                'expected_argument' => '',
            ],
            'Type fields that are ignored' => [
                'input_name' => 'club',
                'type_fields' => [
                    'created_at' => new StringType(),
                    'updated_at' => new StringType(),
                    'deleted_at' => new StringType(),
                ],
                'expected_argument' => '',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param string $inputName
     * @param array $typeFields
     * @param string $expectedArgument
     */
    public function testCanGenerateInputTypeArgument(
        string $inputName,
        array $typeFields,
        string $expectedArgument
    ): void {
        $argument = InputTypeArgumentGenerator::generate($inputName, $typeFields, true);

        $this->assertEquals($expectedArgument, $argument);
    }
}
