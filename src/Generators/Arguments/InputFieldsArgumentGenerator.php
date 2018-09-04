<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Arguments;

use DeInternetJongens\LighthouseUtils\Schema\Scalars\Date;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\DateTimeTz;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use Nuwave\Lighthouse\Schema\Types\Scalars\DateTime;

class InputFieldsArgumentGenerator
{
    /** @var array */
    private static $supportedGraphQLTypes = [
        StringType::class,
        IntType::class,
        FloatType::class,
        Date::class,
        DateTime::class,
        DateTimeTz::class
    ];

    /** @var array */
    private static $ignoredColumns = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Generates a GraphQL Arguments for a mutation
     * More information:
     * https://lighthouse-php.netlify.com/docs/schema.html#input-types
     *
     * @param Type[] $typeFields
     * @return array
     */
    public static function generate(array $typeFields): array
    {
        $arguments = [];
        foreach ($typeFields as $fieldName => $field) {
            $className = get_class($field);
            if (! in_array($className, self::$supportedGraphQLTypes) || in_array($fieldName, self::$ignoredColumns)) {
                continue;
            };

            $required = isset($field->config['generator-required']) && $field->config['generator-required'] === true ? '!' : '';
            $arguments[] = sprintf('%s: %s%s', $fieldName, $field->name, $required);
        }

        return $arguments;
    }
}
