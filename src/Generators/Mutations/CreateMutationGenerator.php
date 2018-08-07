<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Mutations;

use DeInternetJongens\LighthouseUtils\Generators\Arguments\InputFieldsArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Generators\Arguments\RelationArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Generators\Classes\MutationWithInput;
use GraphQL\Type\Definition\Type;

class CreateMutationGenerator
{
    /**
     * Generates a GraphQL Mutation to create a record
     *
     * @param string $typeName
     * @param Type[] $typeFields
     * @return MutationWithInput
     */
    public static function generate(string $typeName, array $typeFields): MutationWithInput
    {
        $mutation = '    create' . $typeName;
        $inputTypeName = sprintf('create%sInput', $typeName);

        $inputTypeArguments = InputFieldsArgumentGenerator::generate($typeFields);
        $inputTypeArguments = array_merge(RelationArgumentGenerator::generate($typeFields), $inputTypeArguments);
        $inputType = sprintf("    input %s {\r\n%s\r\n}", $inputTypeName, implode($inputTypeArguments, "\r\n"));

        if (count($inputTypeArguments) < 1) {
            return new MutationWithInput('', '');
        }

        $mutation .= sprintf('(input: %s!)', $inputTypeName);
        $mutation .= sprintf(': %1$s @create(model: "%1$s", flatten:true)', $typeName);

        return new MutationWithInput($mutation, $inputType);
    }
}
