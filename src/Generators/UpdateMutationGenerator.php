<?php

namespace DeInternetJongens\LighthouseUtils\Generators;

use GraphQL\Type\Definition\Type;

class UpdateMutationGenerator
{

    /**
     * Generates a GraphQL Mutation to update a record
     *
     * @param string $typeName
     * @param Type[] $typeFields
     * @return string
     */
    public static function generate(string $typeName, array $typeFields): string
    {
        $query = '    update' . $typeName;
        $arguments = IdArgumentGenerator::generate($typeFields);
        $arguments = array_merge(RelationArgumentGenerator::generate($typeFields, false), $arguments);
        $arguments = array_merge(InputTypeArgumentGenerator::generate($typeFields), $arguments);

        if (count($arguments) < 1) {
            return '';
        }

        $query .= sprintf('(%s)', implode(', ', $arguments));
        $query .= sprintf(': %1$s @update(model: "%1$s")', $typeName);

        return $query;
    }
}
