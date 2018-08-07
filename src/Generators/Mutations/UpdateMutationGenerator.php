<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Mutations;

use DeInternetJongens\LighthouseUtils\Generators\Arguments\IdArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Generators\Arguments\InputFieldsArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Generators\Arguments\RelationArgumentGenerator;
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
        $arguments = array_merge($arguments, RelationArgumentGenerator::generate($typeFields, false));
        $arguments = array_merge($arguments, InputFieldsArgumentGenerator::generate($typeFields));

        if (count($arguments) < 1) {
            return '';
        }

        $query .= sprintf('(%s)', implode(', ', $arguments));
        $query .= sprintf(': %1$s @update(model: "%1$s")', $typeName);
        $query .= sprintf(' @can(if: "update", model: "%1$s")', $typeName);

        return $query;
    }
}
