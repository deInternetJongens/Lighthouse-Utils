<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators\Arguments;

use DeInternetJongens\LighthouseUtils\Generators\Arguments\IdArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\StringType;

class IdArgumentGeneratorTest extends TestCase
{
    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            'Happy flow' => [
                'type_fields' => [
                    'id' => new IdType(),
                ],
                'expected_arguments' => [
                    'id: ID!',
                ],
            ],
            'Wrong type given' => [
                'type_fields' => [
                    'name' => new StringType(),
                ],
                'expected_arguments' => []
            ],
            'Multiple types given, single id is supported' => [
                'type_fields' => [
                    'id' => new IDType(),
                    'club_id' => new IDType(),
                ],
                'expected_arguments' => [
                    'id: ID!',
                ]
            ],
            'No types given' => [
                'type_fields' => [],
                'expected_arguments' => []
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
        $arguments = IdArgumentGenerator::generate($typeFields);

        $this->assertEquals($expectedArguments, $arguments);
    }
}
