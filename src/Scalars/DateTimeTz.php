<?php

namespace DeInternetJongens\LighthouseUtils\Scalars;

use Carbon\Carbon;
use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use InvalidArgumentException;

class DateTimeTz extends ScalarType
{
    const FORMAT = 'Y-m-d H:i:sP';

    /** @var string */
    public $name = 'DateTimeTz';

    /** @var string */
    public $description = 'A date string with format Y-m-d H:i:s+P. Example: "2018-01-01 13:00:00+00:00"';

    /**
     * @param Carbon $value
     * @return string
     * @throws Error
     */
    public function serialize($value)
    {
        if(! $value instanceof Carbon){
            $value = $this->parseValue($value);
        }

        return $value->format(self::FORMAT);
    }

    /**
     * @param string $value
     * @return Carbon
     * @throws Error
     */
    public function parseValue($value)
    {
        try {
            return Carbon::createFromFormat(self::FORMAT, $value);
        } catch (InvalidArgumentException $exception) {
            throw new Error(Utils::printSafeJson($exception->getMessage()));
        }
    }

    /**
     * @param Node $valueNode
     * @param array|null $variables
     * @return Carbon
     * @throws Error
     */
    public function parseLiteral($valueNode, array $variables = null)
    {
        if (! $valueNode instanceof StringValueNode) {
            throw new Error('Query error: Can only parse strings got: ' . $valueNode->kind, [$valueNode]);
        }

        return $this->parseValue($valueNode->value);
    }
}
