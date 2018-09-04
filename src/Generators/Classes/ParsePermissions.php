<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Classes;

use DeInternetJongens\LighthouseUtils\Models\GraphQLSchema;
use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Source;

class ParsePermissions
{
    /**
     * @param string $fileContents
     * @return array
     * @throws \GraphQL\Error\SyntaxError
     */
    public function register(string $fileContents): array
    {
        $parser = $this->parseGraphQLSchema($fileContents);

        if (empty($parser->definitions[0])) {
            return [];
        }

        /** @var ObjectTypeDefinitionNode $firstNode */
        $wrapper = $parser->definitions[0];

        $rows = [];

        /** @var NodeList $field */
        foreach ($wrapper->fields as $field) {
            $arguments = [];

            $model = $field->type->type->name->value ?? $field->type->name->value;

            /** @var DirectiveNode $directive */
            foreach ($field->directives as $directive) {
                if ($directive->name->value === 'can') {
                    $arguments[] = $directive->arguments;

                    /** @var ArgumentNode $argument */
                    foreach ($directive->arguments as $argument) {
                        if ($argument->name->value === 'if') {
                            $rows[] = [
                                'name' => $field->name->value,
                                'model' => $model,
                                'type' => strtolower($wrapper->name->value),
                                'permission' => $argument->value->value ?? '',
                            ];
                            GraphQLSchema::register(
                                $field->name->value,
                                $model,
                                strtolower($wrapper->name->value),
                                $argument->value->value
                            );
                        }
                    }
                }
            }
        }

        return $rows;
    }

    /**
     * @param string $fileContents
     * @return DocumentNode
     * @throws \GraphQL\Error\SyntaxError
     */
    private function parseGraphQLSchema(string $fileContents): DocumentNode
    {
        return Parser::parse(new Source($fileContents));
    }
}
