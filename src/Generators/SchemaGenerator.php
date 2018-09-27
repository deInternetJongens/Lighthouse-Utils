<?php

namespace DeInternetJongens\LighthouseUtils\Generators;

use Config;
use DeInternetJongens\LighthouseUtils\Events\GraphQLSchemaGenerated;
use DeInternetJongens\LighthouseUtils\Exceptions\InvalidConfigurationException;
use DeInternetJongens\LighthouseUtils\Generators\Classes\ParseDefinitions;
use DeInternetJongens\LighthouseUtils\Generators\Mutations\CreateMutationWithInputTypeGenerator;
use DeInternetJongens\LighthouseUtils\Generators\Mutations\DeleteMutationGenerator;
use DeInternetJongens\LighthouseUtils\Generators\Mutations\UpdateMutationWithInputTypeGenerator;
use DeInternetJongens\LighthouseUtils\Generators\Queries\FindQueryGenerator;
use DeInternetJongens\LighthouseUtils\Generators\Queries\PaginateAllQueryGenerator;
use DeInternetJongens\LighthouseUtils\Models\GraphQLSchema;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\Date;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\DateTimeTz;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\Email;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\PostalCodeNl;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\FullTextSearch;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Nuwave\Lighthouse\Schema\Types\Scalars\DateTime;

class SchemaGenerator
{
    /** @var array */
    private $requiredSchemaFileKeys = ['mutations', 'queries', 'types'];

    /** @var array */
    private $supportedGraphQLTypes = [
        IDType::class,
        StringType::class,
        IntType::class,
        FloatType::class,
        ObjectType::class,
        Date::class,
        DateTime::class,
        DateTimeTZ::class,
        PostalCodeNl::class,
        EnumType::class,
        Email::class,
        FullTextSearch::class,
    ];

    /**
     * @var \DeInternetJongens\LighthouseUtils\Generators\Classes\ParseDefinitions
     */
    private $definitionsParser;

    /**
     * SchemaGenerator constructor.
     *
     * @param \DeInternetJongens\LighthouseUtils\Generators\Classes\ParseDefinitions $definitionsParser
     */
    public function __construct(ParseDefinitions $definitionsParser)
    {
        $this->definitionsParser = $definitionsParser;
    }

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
        $authEnabled = config('lighthouse-utils.authorization');
        if ($authEnabled) {
            GraphQLSchema::truncate();
        }

        $this->validateFilesPaths($definitionFileDirectories);

        $schema = $this->getSchemaForFiles($definitionFileDirectories);

        $definedTypes = $this->getDefinedTypesFromSchema($schema, $definitionFileDirectories);

        $queries = $this->generateQueriesForDefinedTypes($definedTypes, $definitionFileDirectories);
        $typesImports = $this->generateGraphqlRelativeImports(
            $this->definitionsParser->getGraphqlDefinitionFilePaths($definitionFileDirectories['types'])
        );

        if ($authEnabled) {
            event(new GraphQLSchemaGenerated(GraphQLSchema::all()));
        }

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

        $typeDefinitionPaths = $this->definitionsParser->getGraphqlDefinitionFilePaths(
            $definitionFileDirectories['types']
        );
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
     * Find the relative file system path between two file system paths
     * As stolen from: https://gist.github.com/ohaal/2936041
     *
     * @param  string $frompath Path to start from
     * @param  string $topath Path we want to end up in
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
        $definedTypes = $this->definitionsParser->getGraphqlDefinitionFilePaths(
            $definitionFileDirectories['types']
        );

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
                $graphQLType = $fieldType->getType();

                //Every required field is defined by a parent 'NonNullType'
                if (method_exists($graphQLType, 'getWrappedType')) {
                    // Clone the field to prevent pass by reference,
                    // because we want to add a config value unique to this field.
                    $graphQLType = clone $graphQLType->getWrappedType();

                    //We want to know later on wether or not a field is required
                    $graphQLType->config['generator-required'] = true;
                }

                if (! in_array(get_class($graphQLType), $this->supportedGraphQLTypes)) {
                    continue;
                };

                // This retrieves the GraphQL type for this field from the webonyx/graphql-php package
                $internalTypes[$typeName][$fieldName] = $graphQLType;
            }
        }

        return $internalTypes;
    }

    /**
     * Auto-generates a query for each definedType
     * These queries contain arguments for each field defined in the Type
     *
     * @param array $definedTypes
     * @param array $definitionFileDirectories
     * @return string
     */
    private function generateQueriesForDefinedTypes(array $definedTypes, array $definitionFileDirectories): string
    {
        $queries = [];
        $mutations = [];
        $inputTypes = [];

        /**
         * @var string $typeName
         * @var Type $type
         */
        foreach ($definedTypes as $typeName => $type) {
            $paginateAndAllQuery = PaginateAllQueryGenerator::generate($typeName, $type);

            if (! empty($paginateAndAllQuery)) {
                $queries[] = $paginateAndAllQuery;
            }
            $findQuery = FindQueryGenerator::generate($typeName, $type);

            if (! empty($findQuery)) {
                $queries[] = $findQuery;
            }

            $createMutation = createMutationWithInputTypeGenerator::generate($typeName, $type);
            if ($createMutation->isNotEmpty()) {
                $mutations[] = $createMutation->getMutation();
                $inputTypes[] = $createMutation->getInputType();
            }

            $updateMutation = updateMutationWithInputTypeGenerator::generate($typeName, $type);
            if ($updateMutation->isNotEmpty()) {
                $mutations[] = $updateMutation->getMutation();
                $inputTypes[] = $updateMutation->getInputType();
            }

            $deleteMutation = DeleteMutationGenerator::generate($typeName, $type);
            if (! empty($deleteMutation)) {
                $mutations[] = $deleteMutation;
            }
        }

        $queries = array_merge(
            $queries,
            $this->definitionsParser->parseCustomQueriesFrom($definitionFileDirectories['queries'])
        );

        $mutations = array_merge(
            $mutations,
            $this->definitionsParser->parseCustomMutationsFrom($definitionFileDirectories['mutations'])
        );

        $return = sprintf("type Query{\r\n%s\r\n}", implode("\r\n", $queries));
        $return .= "\r\n\r\n";
        $return .= sprintf("type Mutation{\r\n%s\r\n}", implode("\r\n", $mutations));
        $return .= "\r\n\r\n";
        $return .= implode("\r\n", $inputTypes);

        return $return;
    }
}
