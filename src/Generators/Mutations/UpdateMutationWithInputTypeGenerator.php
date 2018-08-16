<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Mutations;

use DeInternetJongens\LighthouseUtils\Generators\Arguments\InputTypeArgumentGenerator;
use DeInternetJongens\LighthouseUtils\Generators\Classes\MutationWithInput;
use DeInternetJongens\LighthouseUtils\Models\GraphQLSchema;
use GraphQL\Type\Definition\Type;

class UpdateMutationWithInputTypeGenerator
{
    /**
     * Generates a GraphQL Mutation to update a record
     * @param string $typeName
     * @param Type[] $typeFields
     * @return MutationWithInput
     */
    public static function generate(string $typeName, array $typeFields): MutationWithInput
    {
        $mutationName = 'update' . $typeName;
        $inputTypeName = sprintf('update%sInput', ucfirst($typeName));
        $inputType = InputTypeArgumentGenerator::generate($inputTypeName, $typeFields, true);

        if (empty($inputType)) {
            return new MutationWithInput('', '');
        }

        $mutation = sprintf('    %s(input: %s!)', $mutationName, $inputTypeName);
        $mutation .= sprintf(': %1$s @update(model: "%1$s", flatten: true)', $typeName);

        if (config('lighthouse-utils.authorization')) {
            $permission = sprintf('update%1$s', $typeName);
            $mutation .= sprintf(' @can(if: "%1$s", model: "User")', $permission);
        }

        GraphQLSchema::register($mutationName, $typeName, 'mutation', $permission ?? null);

        return new MutationWithInput($mutation, $inputType);
    }
}
