<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Queries;

use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\Type;

class FindQueryGenerator
{
    /**
     * Generates a GraphQL query that returns one entity by ID
     *
     * @param string $typeName
     * @param Type[] $typeFields
     * @return string
     */
    public static function generate(string $typeName, array $typeFields): string
    {
        $query =  '    ' . strtolower($typeName);
        $arguments = [];

        //Loop through fields to find the 'ID' field.
        foreach ($typeFields as $fieldName => $field) {
            if (false === ($field instanceof IDType)) {
                continue;
            }

            $arguments[] = sprintf('%s: %s! @eq', $fieldName, $field->name);
            break;
        }

        if (count($arguments) < 1) {
            return '';
        }

        $query .= sprintf('(%s)', implode(', ', $arguments));
        $query .= sprintf(': %1$s! @find(model: "%1$s")', $typeName);
        $query .= sprintf(' @can(if: "find", model: "%1$s")', $typeName);

        return $query;
    }
}
