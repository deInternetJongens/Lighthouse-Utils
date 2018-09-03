<?php

namespace Tests\Unit\Schema\Scalars;

use DeInternetJongens\LighthouseUtils\Schema\Scalars\PostalCode;
use Faker\Factory;
use Faker\Generator;
use GraphQL\Error\Error;
use PHPUnit\Framework\TestCase;

class PostalCodeTest extends TestCase
{
    public function parseValueDataProvider(): array
    {
        return [
            'Happy flow' => [
                'input' => '8111BS'
            ],
            'Postalcode wrong pattern with space' => [
                'input' => '8111 BS',
                'expected exception' => Error::class,
            ],
            'Postalcode wrong pattern no numbers' => [
                'input' => 'AAAABS',
                'expected exception' => Error::class,
            ],
            'Postalcode wrong pattern no letters' => [
                'input' => '123412',
                'expected exception' => Error::class,
            ],
        ];
    }

    /**
     * @param string $input
     * @param string $expectedException
     * @return void
     * @throws Error
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @dataProvider parseValueDataProvider
     */
    public function testParseValue(string $input, string $expectedException = '')
    {
        if($expectedException !== '') {
            $this->expectException($expectedException );
        }

        $result = $this->getScalar()->parseValue($input);

        $this->assertEquals($input, $result);
    }

    /**
     * @return void
     * @throws Error
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @dataProvider serializeDataProvider
     */
    public function testSerialize()
    {
        $faker = new Factory();

        for($i = 0; $i < 10; $i++) {
            $input = $faker->postcode;
            $result = $this->getScalar()->serialize($input);
            $this->assertEquals($input, $result);
        }
    }

    public function testParseLiteral()
    {
    }

    private function getScalar(): PostalCode
    {
        return new PostalCode();
    }
}
