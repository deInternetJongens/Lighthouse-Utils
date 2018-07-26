<?php

namespace DeInternetJongens\LighthouseUtils\Generators;

use GraphQL\Type\Definition\IDType;
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
        $arguments = [];

        //Loop through fields to find the 'ID' field.
        foreach ($typeFields as $fieldName => $field) {
            if (get_class($field) !== IDType::class) {
                continue;
            };
            $arguments[] = sprintf('%s: %s! @eq', $fieldName, $field->name);
            break;
        }
        if (count($arguments) < 1) {
            return '';
        }

        $query .= sprintf('(%s)', implode(', ', $arguments));
        $query .= sprintf(': %1$s @delete', $typeName);

        return $query;
    }
}
