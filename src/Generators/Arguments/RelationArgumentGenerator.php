<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Arguments;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class RelationArgumentGenerator
{
    /**
     * Generates a GraphQL mutation argument for a relation field
     *
     * @param Type[] $typeFields
     * @param bool $required Should the relationship fields be required?
     * @return array
     */
    public static function generate(array $typeFields, bool $required = true): array
    {
        $arguments = [];
        $required = $required ? '!' : '';

        foreach ($typeFields as $fieldName => $field) {
            $className = get_class($field);
            if ($className !== ObjectType::class) {
                continue;
            }

            $arguments[] = sprintf('%s_id: ID%s', $fieldName, $required);
        }

        return $arguments;
    }
}
