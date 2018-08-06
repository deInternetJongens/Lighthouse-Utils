<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Arguments;

use DeInternetJongens\LighthouseUtils\Scalars\Date;
use DeInternetJongens\LighthouseUtils\Scalars\DateTimeTz;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;

class InputFieldsArgumentGenerator
{
    /** @var array */
    private static $supportedGraphQLTypes = [
        StringType::class,
        IntType::class,
        FloatType::class,
        Date::class,
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
