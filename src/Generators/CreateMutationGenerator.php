<?php

namespace DeInternetJongens\LighthouseUtils\Generators;

use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class CreateMutationGenerator
{

    /**
     * Generates a GraphQL Mutation to create a record
     *
     * @param string $typeName
     * @param Type[] $typeFields
     * @param array $supportedGraphQLTypes
     * @return string
     */
    public static function generate(string $typeName, array $typeFields, array $supportedGraphQLTypes): string
    {
        $query = '    create' . $typeName;
        $arguments = [];

        foreach ($typeFields as $fieldName => $field) {
            $className = get_class($field);
            if (! in_array(
                    $className,
                    $supportedGraphQLTypes
                ) || $className === IDType::class || str_contains($fieldName, '_at')) {
                continue;
            };

            $argumentType = $field->name;
            if ($className === ObjectType::class) {
                $fieldName .= '_id';
                $argumentType = 'ID';
            }

            $arguments[] = sprintf('%s: %s!', $fieldName, $argumentType);
        }
        if (count($arguments) < 1) {
            return '';
        }

        $query .= sprintf('(%s)', implode(', ', $arguments));
        $query .= sprintf(': %1$s @create(model: "%1$s")', $typeName);

        return $query;
    }
}
