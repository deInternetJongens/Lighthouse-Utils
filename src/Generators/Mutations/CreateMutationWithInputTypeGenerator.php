<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Mutations;

use DeInternetJongens\LighthouseUtils\Generators\Arguments\InputTypeArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Generators\Classes\MutationWithInput;
use GraphQL\Type\Definition\Type;

class CreateMutationWithInputTypeGenerator
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

        $inputTypeName = sprintf('create%sInput', ucfirst($typeName));
        $inputType = InputTypeArgumentGenerator::generate($inputTypeName, $typeFields);

        if (empty($inputType)) {
            return new MutationWithInput('', '');
        }

        $mutation .= sprintf('(input: %s!)', $inputTypeName);
        $mutation .= sprintf(': %1$s @create(model: "%1$s", flatten: true)', $typeName);

        return new MutationWithInput($mutation, $inputType);
    }
}
