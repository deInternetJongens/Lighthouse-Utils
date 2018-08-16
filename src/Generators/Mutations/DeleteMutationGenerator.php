<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Mutations;

use DeInternetJongens\LighthouseUtils\Generators\Arguments\IdArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Models\GraphQLSchema;
use GraphQL\Type\Definition\Type;

class DeleteMutationGenerator
{
    /**
     * Generates a GraphQL mutation that deletes a record
     *
     * @param string $typeName
     * @param Type[] $typeFields
     * @return string
     */
    public static function generate(string $typeName, array $typeFields): string
    {
        $mutationName = 'delete' . $typeName;
        $arguments = IdArgumentGenerator::generate($typeFields);

        if (count($arguments) < 1) {
            return '';
        }

        $mutation = sprintf('    %s(%s)', $mutationName, implode(', ', $arguments));
        $mutation .= sprintf(': %1$s @delete', $typeName);

        if (config('lighthouse-utils.authorization')) {
            $permission = sprintf('delete%1$s', $typeName);
            $mutation .= sprintf(' @can(if: "%1$s", model: "User")', $permission);
        }

        GraphQLSchema::register($mutationName, $typeName, 'mutation', $permission ?? null);

        return $mutation;
    }
}
