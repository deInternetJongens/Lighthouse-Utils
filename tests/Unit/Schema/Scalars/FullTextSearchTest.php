<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Schema\Scalars;

use DeInternetJongens\LighthouseUtils\Schema\Scalars\FullTextSearch as FullTextSearchScalar;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use GraphQL\Language\AST\StringValueNode;

class FullTextSearchTest extends TestCase
{
    public function parseValueDataProvider(): array
    {
        return [
            'Happy flow' => [
                'input' => 'test'
            ],
        ];
    }

    /**
     * @param string $input
     * @param string $expectedException
     * @return void
     * @throws \GraphQL\Error\Error
     * @dataProvider parseValueDataProvider
     */
    public function testParseValue(string $input, string $expectedException = '')
    {

        if ($expectedException !== '') {
            $this->expectException($expectedException);
        }

        $result = $this->getScalar()->parseValue($input);

        $this->assertEquals($input, $result);
    }

    /**
     * @return array
     */
    public function serializeDataProvider(): array
    {
        return [
            'Happy flow' => [
                'input' => 'test',
                'expected result' => 'test'
            ],
        ];
    }

    /**
     * @param $input
     * @param $expectedResult
     * @return void
     * @throws \GraphQL\Error\Error
     * @dataProvider serializeDataProvider
     */
    public function testSerialize($input, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->getScalar()->serialize($input));
    }

    /**
     * @return array
     */
    public function parseLiteralDataProvider(): array
    {
        return [
            'Happy flow' => [
                'input' => 'test',
                'node class' => StringValueNode::class,
            ],
        ];
    }

    /**
     * @param string $input
     * @param string $nodeClass
     * @param string $exception
     * @return void
     * @throws \Exception
     * @dataProvider parseLiteralDataProvider
     */
    public function testParseLiteral(string $input, string $nodeClass, string $exception = ''): void
    {
        if ($exception !== '') {
            $this->expectException($exception);
        }

        $result = $this->getScalar()->parseLiteral(
            new $nodeClass(
                [
                    'value' => $input,
                    'kind' => 'String!',
                ]
            )
        );

        $expectedResult = $input;

        $this->assertEquals($result, $expectedResult);
    }

    private function getScalar(): FullTextSearchScalar
    {
        return new FullTextSearchScalar([]);
    }
}
