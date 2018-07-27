<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Mutations;

use DeInternetJongens\LighthouseUtils\Generators\Arguments\IdArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Generators\Arguments\InputTypeArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Generators\Arguments\RelationArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Generators\Classes\MutationWithInput;
use GraphQL\Type\Definition\Type;

class UpdateMutationGenerator
{

    /**
     * Generates a GraphQL Mutation to update a record
     *
     * @param string $typeName
     * @param Type[] $typeFields
     * @return MutationWithInput
     */
    public static function generate(string $typeName, array $typeFields): MutationWithInput
    {
        $mutation = '    update' . $typeName;
        $arguments = IdArgumentGenerator::generate($typeFields);
        $arguments = array_merge($arguments, RelationArgumentGenerator::generate($typeFields, false));
        $inputTypeName = sprintf('update%sInput', ucfirst($typeName));
        $arguments[] = sprintf('input: %s!', $inputTypeName);
        $inputType = InputTypeArgumentGenerator::generate($inputTypeName, $typeFields);

        if (count($arguments) < 1) {
            return new MutationWithInput('', '');
        }

        $mutation .= sprintf('(%s)', implode(', ', $arguments));
        $mutation .= sprintf(': %1$s @update(model: "%1$s")', $typeName);

        return new MutationWithInput($mutation, $inputType);
    }
}
