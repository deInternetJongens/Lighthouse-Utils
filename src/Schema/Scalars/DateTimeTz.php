<?php

namespace DeInternetJongens\LighthouseUtils\Schema\Scalars;

use Carbon\Carbon;
use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use InvalidArgumentException;

class DateTimeTz extends ScalarType
{
    private const FORMAT = 'Y-m-d H:i:sP';

    /** @var string */
    public $name = 'DateTimeTz';

    /** @var string */
    public $description = 'A date string with format Y-m-d H:i:s+P. Example: "2018-01-01 13:00:00+00:00"';

    /**
     * @inheritdoc
     */
    public function serialize($value)
    {
        $value->format(self::FORMAT);

        $timeZoneInHours = (float)substr($value->getTimezone()->getName(), 0, 3);

        $value->addHours($timeZoneInHours);

        return $value->format('Y-m-d H:i:s');
    }

    /**
     * @inheritdoc
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
