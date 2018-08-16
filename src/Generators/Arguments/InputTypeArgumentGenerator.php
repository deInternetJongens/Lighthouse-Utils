<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Arguments;

use GraphQL\Type\Definition\Type;

class InputTypeArgumentGenerator
{
    /**
     * Generates a GraphQL Input Type
     * More information:
     * https://lighthouse-php.netlify.com/docs/schema.html#input-types
     *
     * @param string $inputName
     * @param Type[] $typeFields
     * @param bool $generateIdField
     * @return string
     */
    public static function generate(string $inputName, array $typeFields, bool $generateIdField = false): string
    {
        $arguments = InputFieldsArgumentGenerator::generate($typeFields);
        $arguments = array_merge($arguments, RelationArgumentGenerator::generate($typeFields));
        if ($generateIdField) {
            $arguments = array_merge($arguments, IdArgumentGenerator::generate($typeFields));
        }

        if (count($arguments) < 1) {
            return '';
        }

        $query = sprintf('input %s {%s}', $inputName, implode(' ', $arguments));
        return $query;
    }
}
