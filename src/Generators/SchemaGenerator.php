<?php

namespace deinternetjongens\LighthouseGenerators\Generators;

use Config;
use Exception;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;

class SchemaGenerator
{
    /** @var array */
    private $requiredSchemaFileKeys = ['mutations', 'queries', 'types'];

    /** @var array */
    private $recognizedGraphqlScalarTypes = ['IDType', 'StringType', 'IntType'];

    /**
     * @param array $schemaFilePaths
     * @return string
     * @throws Exception
     */
    public function generate(array $schemaFilePaths): string
    {
        $this->validateFilesPaths($schemaFilePaths);

        $schema = $this->getSchemaForFiles($schemaFilePaths);
        $definedTypes = $this->getDefinedTypesFromSchema($schema, $schemaFilePaths);

        $queries = $this->generateQueriesForDefinedTypes($definedTypes);
        $typesImports = $this->generateGraphqlRelativeImports($this->getGraphqlFilePaths($schemaFilePaths['types']));

        //Merge queries and types into one file with required newlines
        return sprintf("%s\r\n\r\n%s\r\n", $typesImports, $queries);
    }

    /**
     * @param array $schemaFilePaths
     * @return bool
     * @throws Exception
     */
    private function validateFilesPaths(array $schemaFilePaths): bool
    {
        if (count($schemaFilePaths) < 1) {
            throw new Exception(
                'The "schema_paths" config value is empty, it should contain a value with a valid path for the following keys: mutations, queries, types'
            );
        }

        if (array_diff(array_keys($schemaFilePaths), $this->requiredSchemaFileKeys)) {
            throw new Exception(
                'The "schema_paths" config value is incomplete, it should contain a value with a valid path for the following keys: mutations, queries, types'
            );
        }
        foreach ($schemaFilePaths as $key => $path) {
            if (empty($path)) {
                throw new Exception(
                    sprintf(
                        'The "schema_paths" config value for key %s is empty, it should contain a value with a valid path',
                        $key
                    )
                );
            }
            if (! file_exists($path)) {
                throw new Exception(
                    sprintf('The "schema_paths" config value for key %s contains a path that does not exist', $key)
                );
            }
        }

        return true;
    }

    /**
     * Generates
     *
     * @param array $schemaFilePaths
     * @return Schema
     */
    private function getSchemaForFiles(array $schemaFilePaths): Schema
    {
        $originalSchemaFilePath = Config::get('lighthouse.schema.register');
        //Get a temp folder and file
        $schemaDirectory = dirname(config('lighthouse.schema.register'));
        $tempSchemaFilePath = $schemaDirectory . '/tempschema.graphql';

        $typeDefinitionPaths = $this->getGraphqlFilePaths($schemaFilePaths['types']);
        $relativeTypeImports = $this->generateGraphqlRelativeImports($typeDefinitionPaths);

        if (! file_exists($schemaDirectory)) {
            mkdir($schemaDirectory, 0777, true);
        }

        // Webonyx GraphQL will not generate a schema if there is not at least one query
        // So just pretend we have one
        $placeholderQuery = 'type Query{placeholder:String}';
        $tempSchemaFile = fopen($tempSchemaFilePath, 'wb');
        fwrite($tempSchemaFile, sprintf("%s\r\n%s", $relativeTypeImports, $placeholderQuery));

        //Override the config value where Lighthouse parses it's schdema from
        Config::set('lighthouse.schema.register', $tempSchemaFilePath);
        $schema = graphql()->prepSchema();

        fclose($tempSchemaFile);
        unlink($tempSchemaFilePath);

        //Set the config value back to where we want to the original path
        Config::set('lighthouse.schema.register', $originalSchemaFilePath);

        return $schema;
    }

    /**
     * @param string $typePath
     * @return array
     */
    private function getGraphqlFilePaths(string $typePath): array
    {
        $files = [];
        foreach (glob(sprintf('%s/%s/*.graphql', base_path(), $typePath)) as $file) {
            $files[] = $file;
        }

        return $files;
    }

    /**
     * @param array $files
     * @return string
     */
    private function generateGraphqlRelativeImports(array $files): string
    {
        $imports = [];
        foreach ($files as $file) {
            $file = $this->getRelativePath(dirname(config('lighthouse.schema.register')), $file);
            $imports[] = sprintf('#import %s', $file);
        }

        return implode("\r\n", $imports);
    }

    /**
     *
     * Find the relative file system path between two file system paths
     * As stolen from: https://gist.github.com/ohaal/2936041
     *
     * @param  string $frompath Path to start from
     * @param  string $topath Path we want to end up in
     *
     * @return string             Path leading from $frompath to $topath
     */
    private function getRelativePath($frompath, $topath)
    {
        $from = explode(DIRECTORY_SEPARATOR, $frompath); // Folders/File
        $to = explode(DIRECTORY_SEPARATOR, $topath); // Folders/File
        $relpath = '';

        $i = 0;
        // Find how far the path is the same
        while (isset($from[$i]) && isset($to[$i])) {
            if ($from[$i] != $to[$i]) {
                break;
            }
            $i++;
        }
        $j = count($from) - 1;
        // Add '..' until the path is the same
        while ($i <= $j) {
            if (! empty($from[$j])) {
                $relpath .= '..' . DIRECTORY_SEPARATOR;
            }
            $j--;
        }
        // Go to folder from where it starts differing
        while (isset($to[$i])) {
            if (! empty($to[$i])) {
                $relpath .= $to[$i] . DIRECTORY_SEPARATOR;
            }
            $i++;
        }

        // Strip last separator
        return substr($relpath, 0, -1);
    }

    /**
     * @param Schema $schema
     * @param array $schemaFilePaths
     * @return array
     */
    private function getDefinedTypesFromSchema(Schema $schema, array $schemaFilePaths): array
    {
        $definedTypes = $this->getGraphqlFilePaths($schemaFilePaths['types']);
        foreach ($definedTypes as $key => $type) {
            $definedTypes[$key] = str_replace('.graphql', '', basename($type));
        }

        $internalTypes = [];
        /**
         * @var string $typeName
         * @var ObjectType $type
         */
        foreach ($schema->getTypeMap() as $typeName => $type) {
            if (! in_array($typeName, $definedTypes) || ! method_exists($type, 'getFields')) {
                continue;
            }

            /**
             * @var string $fieldName
             * @var FieldDefinition $fieldType
             */
            foreach ($type->getFields() as $fieldName => $fieldType) {
                $internalType = $fieldType->getType();
                if (! method_exists($internalType, 'getWrappedType')) {
                    continue;
                }

                // This retrieves the GraphQL type for this field from the webonyx/graphql-php package
                $internalTypes[$typeName][$fieldName] = $internalType->getWrappedType();
            }
        }

        return $internalTypes;
    }

    /**
     * @param array $definedTypes
     * @return string
     */
    private function generateQueriesForDefinedTypes(array $definedTypes): string
    {
        $queries = [];
        foreach ($definedTypes as $typeName => $typeFields) {
            $query = '    ' . str_plural(strtolower($typeName));
            $fields = [];

            foreach ($typeFields as $fieldName => $fieldType) {
                if (! in_array(class_basename($fieldType), $this->recognizedGraphqlScalarTypes)) {
                    continue;
                };
                $fields[] = sprintf('%s: %s @eq', $fieldName, $fieldType->name);
            }
            if (count($fields) < 0) {
                continue;
            }

            $query .= sprintf('(%s)', implode(', ', $fields));
            $query .= sprintf(': [%1$s!]! @paginate(model: "%1$s")', $typeName);
            $queries[] = $query;
        }
        $queries = implode("\r\n", $queries);
        $queries = sprintf("type Query{\r\n%s\r\n}", $queries);

        return $queries;
    }
}
