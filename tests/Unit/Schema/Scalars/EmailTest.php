<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Schema\Scalars;

use DeInternetJongens\LighthouseUtils\Schema\Scalars\Email;
use DeInternetJongens\LighthouseUtils\Tests\Unit\TestCase;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\EmailValidation;
use GraphQL\Error\Error;
use GraphQL\Language\AST\BooleanValueNode;
use GraphQL\Language\AST\StringValueNode;

class EmailTest extends TestCase
{
    public function parseValueDataProvider(): array
    {
        return [
            'Happy flow' => [
                'input' => 'test@example.com'
            ],
            'Invalid pattern' => [
                'input' => 'This is so wrong.',
                'expected exception' => Error::class
            ]
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
                'input' => 'test@example.com',
                'expected result' => 'test@example.com'
            ],
        ];
    }

    /**
     * @return void
     * @throws Error
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @dataProvider serializeDataProvider
     *
     * $email->serialize() doesn't do much, we're just sending the e-mails we have in the response.
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
                'input' => 'test@example.com',
                'node class' => StringValueNode::class,
            ],
            'Invalid format' => [
                'input' => 'Wrong format',
                'node class' => StringValueNode::class,
                'exception' => Error::class,
            ],
            'Invalid node type' => [
                'input' => 'test@example.com',
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

    private function getScalar(): Email
    {
        return new Email(new EmailValidator(), resolve(EmailValidation::class));
    }
}
