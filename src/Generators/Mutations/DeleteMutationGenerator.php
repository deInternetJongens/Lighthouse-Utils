<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Mutations;

use DeInternetJongens\LighthouseUtils\Generators\Arguments\IdArgumentGenerator;
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
        $query = '    delete' . $typeName;
        $arguments = IdArgumentGenerator::generate($typeFields);

        if (count($arguments) < 1) {
            return '';
        }

        $query .= sprintf('(%s)', implode(', ', $arguments));
        $query .= sprintf(': %1$s @delete', $typeName);
        $query .= sprintf(' @can(if: "delete", model: "%1$s")', $typeName);

        return $query;
    }
}
