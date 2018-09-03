<?php

namespace DeInternetJongens\LighthouseUtils\Schema\Scalars;

use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

class PostalCode extends ScalarType
{
    private const PATTERN = '/^\d{4}[a-zA-Z]{2}$/';

    /** @var string */
    public $name = 'PostalCode';

    /** @var string */
    public $description = 'A valid postalcode with pattern [1234aa]. Example: 1234AA.';

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

        return $this->parseValue($valueNode);
    }
}
