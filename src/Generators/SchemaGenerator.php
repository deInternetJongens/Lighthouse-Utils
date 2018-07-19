<?php

namespace DeInternetJongens\LighthouseUtils\Generators;

use Config;
use DeInternetJongens\LighthouseUtils\Exceptions\InvalidConfigurationException;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class SchemaGenerator
{
    /** @var array */
    private $requiredSchemaFileKeys = ['mutations', 'queries', 'types'];

    /** @var array */
    private $recognizedGraphQLTypes = ['IDType', 'StringType', 'IntType', 'FloatType'];

    /**
     * Generates a schema from an array of definition file directories
     * For now, this only supports Types.
     * In the future it should also support Mutations and Queries.
     *
     * @param array $definitionFileDirectories
     * @return string Generated Schema with Types and Queries
     * @throws InvalidConfigurationException
     */
    public function generate(array $definitionFileDirectories): string
    {
        $this->validateFilesPaths($definitionFileDirectories);

        $schema = $this->getSchemaForFiles($definitionFileDirectories);
        $definedTypes = $this->getDefinedTypesFromSchema($schema, $definitionFileDirectories);

        $queries = $this->generateQueriesForDefinedTypes($definedTypes);
        $typesImports = $this->generateGraphqlRelativeImports($this->getGraphqlDefinitionFilePaths($definitionFileDirectories['types']));

        //Merge queries and types into one file with required newlines
        return sprintf("%s\r\n\r\n%s\r\n", $typesImports, $queries);
    }

    /**
     * Validates if the given defintionFileDirectories contains;
     * - All required keys
     * - Filled values for each key
     * - Existing paths for each key
     *
     * @param array $definitionFileDirectories
     * @return bool
     * @throws InvalidConfigurationException
     */
    private function validateFilesPaths(array $definitionFileDirectories): bool
    {
        if (count($definitionFileDirectories) < 1) {
            throw new InvalidConfigurationException(
                'The "schema_paths" config value is empty, it should contain a value with a valid path for the following keys: mutations, queries, types'
            );
        }

        if (array_diff(array_keys($definitionFileDirectories), $this->requiredSchemaFileKeys)) {
            throw new InvalidConfigurationException(
                'The "schema_paths" config value is incomplete, it should contain a value with a valid path for the following keys: mutations, queries, types'
            );
        }
        foreach ($definitionFileDirectories as $key => $path) {
            if (empty($path)) {
                throw new InvalidConfigurationException(
                    sprintf(
                        'The "schema_paths" config value for key "%s" is empty, it should contain a value with a valid path',
                        $key
                    )
                );
            }

            if (! file_exists($path)) {
                throw new InvalidConfigurationException(
                    sprintf('The "schema_paths" config value for key "%s" contains a path that does not exist', $key)
                );
            }
        }

        return true;
    }

    /**
     * Generates a GraphQL schema for a set of definition files
     * Definition files can only be Types at this time
     * In the future this should also support Mutations and Queries
     *
     * @param array $definitionFileDirectories
     * @return Schema
     */
    private function getSchemaForFiles(array $definitionFileDirectories): Schema
    {
        $originalSchemaFilePath = Config::get('lighthouse.schema.register');
        //Get a temp folder and file
        $schemaDirectory = dirname(config('lighthouse.schema.register'));
        $tempSchemaFilePath = $schemaDirectory . '/tempschema.graphql';

        $typeDefinitionPaths = $this->getGraphqlDefinitionFilePaths($definitionFileDirectories['types']);
        $relativeTypeImports = $this->generateGraphqlRelativeImports($typeDefinitionPaths);

        if (! file_exists($schemaDirectory)) {
            mkdir($schemaDirectory, 0777, true);
        }

        // Webonyx GraphQL will not generate a schema if there is not at least one query
        // So just pretend we have one
        $placeholderQuery = 'type Query{placeholder:String}';
        $tempSchemaFile = fopen($tempSchemaFilePath, 'wb');
        fwrite($tempSchemaFile, sprintf("%s\r\n%s", $relativeTypeImports, $placeholderQuery));

        //Override the config value where Lighthouse parses it's schema from
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
    private function getGraphqlDefinitionFilePaths(string $typePath): array
    {
        $files = [];
        foreach (glob(sprintf('%s/%s/*.graphql', base_path(), $typePath)) as $file) {
            $files[] = $file;
        }

        return $files;
    }

    /**
     * Generates
     *
     * @param array $schemaDefinitionFilePaths
     * @return string
     */
    private function generateGraphqlRelativeImports(array $schemaDefinitionFilePaths): string
    {
        $imports = [];
        foreach ($schemaDefinitionFilePaths as $file) {
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
     * Parse defined types from a schema into an array with the native GraphQL Scalar types for each field
     *
     * @param Schema $schema
     * @param array $definitionFileDirectories
     * @return Type[]
     */
    private function getDefinedTypesFromSchema(Schema $schema, array $definitionFileDirectories): array
    {
        $definedTypes = $this->getGraphqlDefinitionFilePaths($definitionFileDirectories['types']);
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
                if (method_exists($internalType, 'getWrappedType')) {
                    $internalType = $internalType->getWrappedType();
                }
                if (! in_array(class_basename($internalType), $this->recognizedGraphQLTypes)) {
                    continue;
                };

                // This retrieves the GraphQL type for this field from the webonyx/graphql-php package
                $internalTypes[$typeName][$fieldName] = $internalType;
            }
        }

        return $internalTypes;
    }

    /**
     * Auto-generates a query for each definedType
     * These queries contain arguments for each field defined in the Type
     *
     * @param array $definedTypes
     * @return string
     */
    private function generateQueriesForDefinedTypes(array $definedTypes): string
    {
        $queries = [];
        $mutations = [];
        /**
         * @var string $typeName
         * @var Type $type
         */
        foreach ($definedTypes as $typeName => $type) {
            $paginatedWhereQuery = $this->generatePaginatedWhereQuery($typeName, $type);

            if (! empty($paginatedWhereQuery)) {
                $queries[] = $paginatedWhereQuery;
            }
            $findQuery = $this->generateFindQuery($typeName, $type);

            if (! empty($findQuery)) {
                $queries[] = $findQuery;
            }

            $findQuery = $this->generateCreateQuery($typeName, $type);
            if (! empty($findQuery)) {
                $mutations[] = $findQuery;
            }

            $findQuery = $this->generateUpdateQuery($typeName, $type);
            if (! empty($findQuery)) {
                $mutations[] = $findQuery;
            }
        }
        $return = sprintf("type Query{\r\n%s\r\n}", implode("\r\n", $queries));
        $return .= "\r\n\r\n";
        $return .= sprintf("type Mutation{\r\n%s\r\n}", implode("\r\n", $mutations));

        return $return;
    }

    /**
     * Generates a GraphQL query that returns multiple arguments with arguments for each field
     *
     * @param string $typeName
     * @param Type[] $typeFields
     * @return string
     */
    private function generatePaginatedWhereQuery(string $typeName, array $typeFields): string
    {
        $query = '    ' . str_plural(strtolower($typeName));
        $arguments = [];

        foreach ($typeFields as $fieldName => $field) {
            if (! in_array(class_basename($field), $this->recognizedGraphQLTypes)) {
                continue;
            };

            // Add all our custom directives
            $arguments[] = sprintf('%s: %s @eq', $fieldName, $field->name);
            $arguments[] = sprintf('%s_not: %s @not', $fieldName, $field->name);
            $arguments[] = sprintf('%s_in: %s @in', $fieldName, $field->name);
            $arguments[] = sprintf('%s_not_in: %s @not_in', $fieldName, $field->name);
            $arguments[] = sprintf('%s_lt: %s @lt', $fieldName, $field->name);
            $arguments[] = sprintf('%s_lte: %s @lte', $fieldName, $field->name);
            $arguments[] = sprintf('%s_gt: %s @gt', $fieldName, $field->name);
            $arguments[] = sprintf('%s_gte: %s @gte', $fieldName, $field->name);
            
            if(\strtolower($field->name) === 'string') {
                $arguments[] = sprintf('%s_contains: %s @contains', $fieldName, $field->name);
                $arguments[] = sprintf('%s_not_contains: %s @not_contains', $fieldName, $field->name);
                $arguments[] = sprintf('%s_starts_with: %s @starts_with', $fieldName, $field->name);
                $arguments[] = sprintf('%s_not_starts_with: %s @not_starts_with', $fieldName, $field->name);
                $arguments[] = sprintf('%s_ends_with: %s @not_ends_with', $fieldName, $field->name);
            }
        }
        if (count($arguments) < 1) {
            return '';
        }

        $query .= sprintf('(%s)', implode(', ', $arguments));
        $query .= sprintf(': [%1$s]! @paginate(model: "%1$s")', $typeName);

        return $query;
    }

    /**
     * Generates a GraphQL query that returns one entity by ID
     *
     * @param string $typeName
     * @param Type[] $typeFields
     * @return string
     */
    private function generateFindQuery(string $typeName, array $typeFields): string
    {
        $query =  '    ' . strtolower($typeName);
        $arguments = [];

        //Loop through fields to find the 'ID' field.
        foreach ($typeFields as $fieldName => $field) {
            if (class_basename($field) !== 'IDType') {
                continue;
            };
            $arguments[] = sprintf('%s: %s! @eq', $fieldName, $field->name);
            break;
        }
        if (count($arguments) < 1) {
            return '';
        }

        $query .= sprintf('(%s)', implode(', ', $arguments));
        $query .= sprintf(': %1$s! @find(model: "%1$s")', $typeName);

        return $query;
    }

    /**
     * Generates a GraphQL Mutation to create a record
     *
     * @param string $typeName
     * @param Type[] $typeFields
     * @return string
     */
    private function generateCreateQuery(string $typeName, array $typeFields): string
    {
        $query = '    create' . $typeName;
        $arguments = [];

        foreach ($typeFields as $fieldName => $field) {
            $classBaseName = class_basename($field);
            if (! in_array($classBaseName, $this->recognizedGraphQLTypes) || $classBaseName === 'IDType' || str_contains($fieldName, '_at')) {
                continue;
            };
            $arguments[] = sprintf('%s: %s!', $fieldName, $field->name);
        }
        if (count($arguments) < 1) {
            return '';
        }

        $query .= sprintf('(%s)', implode(', ', $arguments));
        $query .= sprintf(': %1$s @create(model: "%1$s")', $typeName);

        return $query;
    }
}
