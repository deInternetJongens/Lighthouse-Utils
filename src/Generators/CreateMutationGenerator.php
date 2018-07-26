<?php

namespace DeInternetJongens\LighthouseUtils\Generators;

use GraphQL\Type\Definition\Type;

class CreateMutationGenerator
{
    /**
     * Generates a GraphQL Mutation to create a record
     *
     * @param string $typeName
     * @param Type[] $typeFields
     * @return string
     */
    public static function generate(string $typeName, array $typeFields): string
    {
        $query = '    create' . $typeName;

        $arguments = RelationArgumentGenerator::generate($typeFields);
        $arguments = array_merge(InputTypeArgumentGenerator::generate($typeFields), $arguments);

        if (count($arguments) < 1) {
            return '';
        }

        $query .= sprintf('(%s)', implode(', ', $arguments));
        $query .= sprintf(': %1$s @create(model: "%1$s")', $typeName);

        return $query;
    }
}
