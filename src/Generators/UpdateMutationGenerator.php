<?php

namespace DeInternetJongens\LighthouseUtils\Generators;

use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class UpdateMutationGenerator
{

    /**
     * Generates a GraphQL Mutation to update a record
     *
     * @param string $typeName
     * @param Type[] $typeFields
     * @param array $supportedGraphQLTypes
     * @return string
     */
    public static function generate(string $typeName, array $typeFields, array $supportedGraphQLTypes): string
    {
        $query = '    update' . $typeName;
        $arguments = [];

        foreach ($typeFields as $fieldName => $field) {
            $className = get_class($field);
            if (! in_array($className, $supportedGraphQLTypes) || str_contains($fieldName, '_at')) {
                continue;
            };

            $required = '';
            $argumentType = $field->name;
            if ($className === IDType::class) {
                $required = '!';
            }
            if ($className === ObjectType::class) {
                $fieldName .= '_id';
                $argumentType = 'ID';
            }

            $arguments[] = sprintf('%s: %s%s', $fieldName, $argumentType, $required);
        }
        if (count($arguments) < 1) {
            return '';
        }

        $query .= sprintf('(%s)', implode(', ', $arguments));
        $query .= sprintf(': %1$s @update(model: "%1$s")', $typeName);

        return $query;
    }
}
