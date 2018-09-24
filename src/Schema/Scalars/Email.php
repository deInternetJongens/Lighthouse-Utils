<?php

namespace DeInternetJongens\LighthouseUtils\Schema\Scalars;

use Egulias\EmailValidator\EmailValidator;
use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

class Email extends ScalarType
{
    private const PATTERN = '/^\d{4}[a-zA-Z]{2}$/';

    /** @var string */
    public $name = 'PostalCodeNl';

    /** @var string */
    public $description = 'A valid postalcode for The Netherlands with pattern [1234aa]. Example: 1234AA.';

    public function __construct(EmailValidator $emailValidator)
    {
    }

    /**
     * @inheritDoc
     */
    public function serialize($value)
    {
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function parseValue($value)
    {
        if (! \preg_match(self::PATTERN, $value)) {
            throw new Error(sprintf('Input error: Expected valid postal code with pattern [1234aa], got: [%s]', $value));
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function parseLiteral($valueNode, array $variables = null)
    {
        if (! $valueNode instanceof StringValueNode) {
            throw new Error('Query error: Can only parse strings got: ' . $valueNode->kind, [$valueNode]);
        }

        return $this->parseValue($valueNode->value);
    }
}
