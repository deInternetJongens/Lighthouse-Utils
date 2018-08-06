<?php

namespace Tests\Unit\Schema\Scalars;

use Carbon\Carbon;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\Date;
use GraphQL\Error\Error;
use GraphQL\Language\AST\BooleanValueNode;
use GraphQL\Language\AST\StringValueNode;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

class DateTest extends TestCase
{
    const FORMAT = 'Y-m-d';

    /**
     * @return array
     */
    public function parseValueDataProvider(): array
    {
        return [
          'Happy flow' => [
            'input' => '2018-09-06',
          ],
          'Invalid date format' => [
            'input' => '2018-09',
            'exception' => Error::class,
          ],
        ];
    }

    /**
     * @param string $input
     * @param string $exception
     * @return void
     * @throws \GraphQL\Error\Error
     * @throws \InvalidArgumentException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @dataProvider parseValueDataProvider
     */
    public function testParseValue(string $input, string $exception = ''): void
    {
        $dateScalar = new Date();

        if ($exception !== '') {
            $this->expectException($exception);
        }

        $result = $dateScalar->parseValue($input);
        $expectedResult = Carbon::createFromFormat(self::FORMAT, $input);

        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @return void
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testSerialize(): void
    {
        $input = Carbon::now();

        $dateScalar = new Date();

        $result = $dateScalar->serialize($input);
        $expectedResult = $input->toDateString();

        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @return array
     */
    public function parseLiteralDataProvider(): array
    {
        return [
          'Happy flow' => [
            'input' => '2018-09-06',
            'node class' => StringValueNode::class,
          ],
          'Invalid date format' => [
            'input' => '2018-09',
            'node class' => StringValueNode::class,
            'exception' => Error::class,
          ],
          'Invalid node type' => [
            'input' => '2018-09-06',
            'node class' => BooleanValueNode::class,
            'exception' => Error::class,
          ],
        ];
    }

    /**
     * @param string $input
     * @param string $nodeClass
     * @param string $exception
     * @return void
     * @throws Error
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws \InvalidArgumentException
     * @dataProvider parseLiteralDataProvider
     */
    public function testParseLiteral(string $input, string $nodeClass, string $exception = ''): void
    {
        $dateScalar = new Date();

        if ($exception !== '') {
            $this->expectException($exception);
        }

        $result = $dateScalar->parseLiteral(
          new $nodeClass(
            [
              'value' => $input,
              'kind' => 'String!',
            ]
          )
        );
        $expectedResult = Carbon::createFromFormat(self::FORMAT, $input);

        $this->assertEquals($result, $expectedResult);
    }
}
