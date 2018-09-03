<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Classes;

use DeInternetJongens\LighthouseUtils\Models\GraphQLSchema;
use GraphQL\Error\SyntaxError;
use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Source;

class ParseDefinitions
{
    /**
     * @param string $path
     * @return array
     */
    public function parseCustomQueriesFrom(string $path)
    {
        return $this->parseCustomSchemaFrom($path, 'Query');
    }

    /**
     * @param string $path
     * @return array
     */
    public function parseCustomMutationsFrom(string $path)
    {
        return $this->parseCustomSchemaFrom($path, 'Mutation');
    }

    /**
     * @param string $typePath
     * @return array
     */
    public function getGraphqlDefinitionFilePaths(string $typePath): array
    {
        $files = [];
        foreach (glob(sprintf('%s/%s/*.graphql', base_path(), $typePath)) as $file) {
            $files[] = $file;
        }

        return $files;
    }

    /**
     * @param string $customSchemaPath
     * @param string $type
     * @return array
     */
    private function parseCustomSchemaFrom(string $customSchemaPath, string $type)
    {
        $customSchema = [];
        foreach ($this->getGraphqlDefinitionFilePaths($customSchemaPath) as $path) {
            $returnData = $this->extractSchema($type, $this->getSchemaFrom($path));

            $customSchema = array_merge($customSchema, $returnData);
        }

        return $customSchema;
    }

    /**
     * @param string $path
     * @return bool|string
     */
    private function getSchemaFrom(string $path)
    {
        $file = fopen($path, "r");

        $fileContents = fread($file, filesize($path));

        fclose($file);

        return $fileContents;
    }

    /**
     * @param string $type
     * @param string $fileContents
     * @return array
     */
    private function extractSchema(string $type, string $fileContents): array
    {
        $this->registerPermissions($fileContents);

        $rawSchemaRows = $this->extractRawRowsFromSchema($type, $fileContents);

        $whitespaceRemovedRows = $this->removeWhitespaceFromRows($rawSchemaRows);

        $cleared = array_filter($whitespaceRemovedRows);

        return $this->indentRows($cleared);
    }

    /**
     * @param array $rawSchemaRows
     * @return array
     */
    private function removeWhitespaceFromRows(array $rawSchemaRows): array
    {
        return array_map(
            'trim',
            $rawSchemaRows
        );
    }

    /**
     * @param array $cleared
     * @return array
     */
    private function indentRows(array $cleared): array
    {
        return array_map(function ($row) {
            return '    ' . $row;
        }, $cleared);
    }

    /**
     * @param string $type
     * @param string $fileContents
     * @return array
     */
    private function extractRawRowsFromSchema(string $type, string $fileContents): array
    {
        return explode(
            "\n",
            str_replace(["type", $type, "{", "}"], "", $fileContents)
        );
    }

    /**
     * @param string $fileContents
     * @return array
     */
    private function registerPermissions(string $fileContents)
    {
        /** @var DocumentNode $parser */
        try {
            $parser = Parser::parse(new Source($fileContents));
        } catch (SyntaxError $e) {
        }

        /** @var \GraphQL\Language\AST\NodeList $nodeList */
        $nodeList = $parser->definitions;

        /** @var ObjectTypeDefinitionNode $firstNode */
        $wrapper = $nodeList[0];

        $cans = [];

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
                            $cans[] = [
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

        return $cans;
    }
}
