<?php

namespace Tests\Unit\Schema\Scalars;

use Carbon\Carbon;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\DateTimeTz;
use GraphQL\Error\Error;
use GraphQL\Language\AST\BooleanValueNode;
use GraphQL\Language\AST\StringValueNode;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

class DateTimeTzTest extends TestCase
{
    const FORMAT = 'Y-m-d H:i:sP';

    /**
     * @return array
     */
    public function parseValueDataProvider(): array
    {
        return [
          'Happy flow' => [
            'input' => '2018-09-06 13:00:00+02:00',
          ],
          'Happy flow without minutes in timezone' => [
            'input' => '2018-09-06 13:00:00+02',
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
     * @throws Error
     * @throws \InvalidArgumentException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @dataProvider parseValueDataProvider
     */
    public function testParseValue(string $input, string $exception = ''): void
    {
        $dateScalar = new DateTimeTz();

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
     * @throws \InvalidArgumentException
     */
    public function testSerialize(): void
    {
        $expectedResult = '2018-09-06 13:00:00+02:00';

        $input = Carbon::createFromFormat(self::FORMAT, $expectedResult);

        $dateScalar = new DateTimeTz();

        $result = $dateScalar->serialize($input);

        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @return array
     */
    public function parseLiteralDataProvider(): array
    {
        return [
          'Happy flow' => [
            'input' => '2018-09-06 13:00:00+02:00',
            'node class' => StringValueNode::class,
          ],
          'Invalid date format' => [
            'input' => '2018-09',
            'node class' => StringValueNode::class,
            'exception' => Error::class,
          ],
          'Invalid node type' => [
            'input' => '2018-09-06 13:00:00+02:00',
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
     * @throws \InvalidArgumentException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @dataProvider parseLiteralDataProvider
     */
    public function testParseLiteral(string $input, string $nodeClass, string $exception = ''): void
    {
        $dateScalar = new DateTimeTz();

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
