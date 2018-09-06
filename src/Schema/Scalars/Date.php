<?php

namespace DeInternetJongens\LighthouseUtils\Schema\Scalars;

use Carbon\Carbon;
use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\LeafType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use InvalidArgumentException;

class Date extends ScalarType
{
    private const FORMAT = 'Y-m-d';

    /** @var string */
    public $name = 'Date';

    /** @var string */
    public $description = 'A date string with format Y-m-d. Example: "2018-01-01"';

    /**
     * @inheritdoc
     */
    public function serialize($value)
    {
        return $value->format(self::FORMAT);
    }

    /**
     * @inheritdoc
     */
    public function parseValue($value)
    {
        try {
            return Carbon::createFromFormat('' . self::FORMAT, $value);
        } catch (InvalidArgumentException $exception) {
            throw new Error(Utils::printSafeJson($exception->getMessage()));
        }
    }

    /**
     * @inheritdoc
     */
    public function parseLiteral($valueNode, array $variables = null)
    {
        if (! $valueNode instanceof StringValueNode) {
            throw new Error('Query error: Can only parse strings got: ' . $valueNode->kind, [$valueNode]);
        }

        return $this->parseValue($valueNode->value);
    }
}
