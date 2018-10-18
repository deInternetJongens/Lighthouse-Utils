<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Classes;

class ParseDefinitions
{
    /** @var ParsePermissions */
    private $permissionsParser;

    /**
     * ParseDefinitions constructor.
     *
     * @param ParsePermissions $permissionsParser
     */
    public function __construct(ParsePermissions $permissionsParser)
    {
        $this->permissionsParser = $permissionsParser;
    }

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
     * @throws \GraphQL\Error\SyntaxError
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
     * @throws \GraphQL\Error\SyntaxError
     */
    private function extractSchema(string $type, string $fileContents): array
    {
        $this->permissionsParser->register($fileContents);

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
}
