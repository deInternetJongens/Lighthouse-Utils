<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Mutations;

use DeInternetJongens\LighthouseUtils\Generators\Arguments\IdArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Generators\Arguments\InputFieldsArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Generators\Arguments\RelationArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Models\GraphQLSchema;
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
        $mutationName = 'update' . $typeName;
        $arguments = IdArgumentGenerator::generate($typeFields);
        $arguments = array_merge($arguments, RelationArgumentGenerator::generate($typeFields, false));
        $arguments = array_merge($arguments, InputFieldsArgumentGenerator::generate($typeFields));

        if (count($arguments) < 1) {
            return '';
        }

        $mutation = sprintf('    %s(%s)', $mutationName, implode(', ', $arguments));
        $mutation .= sprintf(': %1$s @update(model: "%1$s")', $typeName);

        if (config('lighthouse-utils.authorization')) {
            $permission = sprintf('update%1$s', $typeName);
            $mutation .= sprintf(' @can(if: "%1$s", model: "User")', $permission);
        }

        GraphQLSchema::register($mutationName, $typeName, 'mutation', $permission ?? null);

        return $mutation;
    }
}
