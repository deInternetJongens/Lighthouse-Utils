<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Mutations;

use DeInternetJongens\LighthouseUtils\Generators\Arguments\InputFieldsArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Generators\Arguments\RelationArgumentGenerator;
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
        $mutation = '    create' . $typeName;

        $arguments = RelationArgumentGenerator::generate($typeFields);
        $arguments = array_merge($arguments, InputFieldsArgumentGenerator::generate($typeFields));

        if (count($arguments) < 1) {
            return '';
        }

        $mutation .= sprintf('(%s)', implode(', ', $arguments));
        $mutation .= sprintf(': %1$s @create(model: "%1$s")', $typeName);

        return $mutation;
    }
}
