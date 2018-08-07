<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators\Arguments;

use DeInternetJongens\LighthouseUtils\Generators\Arguments\InputTypeArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\StringType;

class InputTypeArgumentGeneratorTest extends TestCase
{
    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            // Happy flow
            [
                'input_name' => 'name',
                'type_fields' => [
                    'name' => new StringType(),
                ],
                'expected_argument' => "input name {\r\nname: String\r\n}",
            ],
            // Happy flow, required field
            [
                'input_name' => 'name',
                'type_fields' => [
                    'name' => new StringType([
                        'generator-required' => true,
                    ]),
                ],
                'expected_argument' => "input name {\r\nname: String!\r\n}",
            ],
            // Wrong type given
            [
                'input_name' => 'id',
                'type_fields' => [
                    'id' => new IDType(),
                ],
                'expected_argument' => "input id {\r\n\r\n}",
            ],
            // No data given
            [
                'input_name' => '',
                'type_fields' => [],
                'expected_argument' => "input  {\r\n\r\n}",
            ],
            // Type fields that are ignored
            [
                'input_name' => 'club',
                'type_fields' => [
                    'created_at' => new StringType(),
                    'updated_at' => new StringType(),
                    'deleted_at' => new StringType(),
                ],
                'expected_argument' => "input club {\r\n\r\n}",
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
        $argument = InputTypeArgumentGenerator::generate($inputName, $typeFields);

        $this->assertEquals($expectedArgument, $argument);
    }
}
