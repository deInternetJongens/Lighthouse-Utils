<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Mutations;

use DeInternetJongens\LighthouseUtils\Generators\Arguments\IdArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Generators\Arguments\InputFieldsArgumentGenerator;
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
        $inputTypeName = sprintf('update%sInput', $typeName);

        $inputTypeArguments = IdArgumentGenerator::generate($typeFields);
        $inputTypeArguments = array_merge($inputTypeArguments, RelationArgumentGenerator::generate($typeFields, false));
        $inputTypeArguments = array_merge($inputTypeArguments, InputFieldsArgumentGenerator::generate($typeFields));
        $inputType = sprintf("    input %s {\r\n%s\r\n}", $inputTypeName, implode($inputTypeArguments, "\r\n"));

        if (count($inputTypeArguments) < 1) {
            return new MutationWithInput('', '');
        }

        $mutation .= sprintf('(input: %s!)', $inputTypeName);
        $mutation .= sprintf(': %1$s @update(model: "%1$s", flatten: true)', $typeName);

        return new MutationWithInput($mutation, $inputType);
    }
}
