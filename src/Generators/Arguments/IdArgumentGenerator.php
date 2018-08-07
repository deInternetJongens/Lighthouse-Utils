<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Arguments;

use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\Type;

class IdArgumentGenerator
{
    /**
     * Generates a GraphQL ID argument
     * More information:
     * https://lighthouse-php.netlify.com/docs/schema.html#input-types
     *
     * @param Type[] $typeFields
     * @return array
     */
    public static function generate(array $typeFields): array
    {
        foreach ($typeFields as $fieldName => $field) {
            $className = get_class($field);
            if ($className === IDType::class) {
                return [
                    sprintf('%s: %s!', $fieldName, $field->name)
                ];
            };
        }

        return [];
    }
}
