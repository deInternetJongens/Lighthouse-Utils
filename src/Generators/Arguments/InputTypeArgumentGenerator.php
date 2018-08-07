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
     * @return string
     */
    public static function generate(string $inputName, array $typeFields): string
    {
        $arguments = InputFieldsArgumentGenerator::generate($typeFields);

        $query = sprintf("input %s {\r\n%s\r\n}", $inputName, implode("\r\n", $arguments));
        return $query;
    }
}
