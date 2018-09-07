<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Generators\Arguments;

use DeInternetJongens\LighthouseUtils\Generators\Arguments\InputFieldsArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\Date;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\DateTimeTz;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\PostalCodeNl;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\StringType;
use Nuwave\Lighthouse\Schema\Types\Scalars\DateTime;

class InputFieldsArgumentGeneratorTest extends TestCase
{
    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            'Happy flow, not required field' => [
                'type_fields' => [
                    'name' => new StringType(),
                ],
                'expected_arguments' => [
                    'name: String',
                ],
            ],
            'Happy flow, required field' => [
                'type_fields' => [
                    'name' => new StringType([
                        'generator-required' => true,
                    ]),
                ],
                'expected_arguments' => [
                    'name: String!',
                ],
            ],
            'Wrong type given' => [
                'type_fields' => [
                    'name' => new IDType(),
                ],
                'expected_arguments' => [],
            ],
            'No data given' => [
                'type_fields' => [],
                'expected_arguments' => [],
            ],
            'Type fields that are ignored' => [
                'type_fields' => [
                    'created_at' => new StringType,
                    'updated_at' => new StringType,
                    'deleted_at' => new StringType,
                ],
                'expected_arguments' => [],
            ],
            'Custom scalar DateTime is supported' => [
                'type_fields' => [
                    'date' => new DateTime(),
                ],
                'expected_arguments' => [
                    'date: DateTime',
                ],
            ],
            'Custom scalar Date is supported' => [
                'type_fields' => [
                    'date' => new Date(),
                ],
                'expected_arguments' => [
                    'date: Date',
                ],
            ],
            'Custom scalar DateTimeTz is supported' => [
                'type_fields' => [
                    'date_with_tz' => new DateTimeTz(),
                ],
                'expected_arguments' => [
                    'date_with_tz: DateTimeTz',
                ],
            ],
            'Custom scalar PostalCodeNl is supported' => [
                'type_fields' => [
                    'postal_code' => new PostalCodeNl(),
                ],
                'expected_arguments' => [
                    'postal_code: PostalCodeNl',
                ],
            ]
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
