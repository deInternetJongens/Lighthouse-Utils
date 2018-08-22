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
    public static function generate(array $typeFields): array
    {
        $arguments = [];

        foreach ($typeFields as $fieldName => $field) {
            $config = $field->config;

            $required =  isset($config['generator-required']) ? ($config['generator-required'] ? '!' : '') : '';
            $className = get_class($field);
            if ($className !== ObjectType::class) {
                continue;
            }

            $arguments[] = sprintf('%s_id: ID%s', $fieldName, $required);
        }

        return $arguments;
    }
}
