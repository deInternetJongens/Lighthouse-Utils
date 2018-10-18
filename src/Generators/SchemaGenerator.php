<?php

namespace DeInternetJongens\LighthouseUtils\Generators;

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
use DeInternetJongens\LighthouseUtils\Schema\Scalars\FullTextSearch;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\PostalCodeNl;
use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Nuwave\Lighthouse\Events\BuildingAST;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;
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
        BooleanType::class,
    ];

    /**
     * @var \DeInternetJongens\LighthouseUtils\Generators\Classes\ParseDefinitions
     */
    private $definitionsParser;

    /** @var SchemaSourceProvider */
    private $schemaSourceProvider;

    /**
     * SchemaGenerator constructor.
     *
     * @param \DeInternetJongens\LighthouseUtils\Generators\Classes\ParseDefinitions $definitionsParser
     * @param SchemaSourceProvider $schemaSourceProvider
     */
    public function __construct(ParseDefinitions $definitionsParser, SchemaSourceProvider $schemaSourceProvider)
    {
        $this->definitionsParser = $definitionsParser;
        $this->schemaSourceProvider = $schemaSourceProvider;
    }

    /**
     * Generates a schema from an array of definition file directories
     * For now, this only supports Types.
     * In the future it should also support Mutations and Queries.
     *
     * @param array $definitionFileDirectories
     * @return string Generated Schema with Types and Queries
     * @throws InvalidConfigurationException
     * @throws \Nuwave\Lighthouse\Exceptions\DirectiveException
     * @throws \Nuwave\Lighthouse\Exceptions\ParseException
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
        $typesImports = $this->concatSchemaDefinitionFilesFromPath(
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
     * @throws \Nuwave\Lighthouse\Exceptions\DirectiveException
     * @throws \Nuwave\Lighthouse\Exceptions\ParseException
     */
    private function getSchemaForFiles(array $definitionFileDirectories): Schema
    {
        resolve('events')->listen(
            BuildingAST::class,
            function () use ($definitionFileDirectories) {
                $typeDefinitionPaths = $this->definitionsParser->getGraphqlDefinitionFilePaths(
                    $definitionFileDirectories['types']
                );
                $relativeTypeImports = $this->concatSchemaDefinitionFilesFromPath($typeDefinitionPaths);

                // Webonyx GraphQL will not generate a schema if there is not at least one query
                // So just pretend we have one
                $placeholderQuery = 'type Query{placeholder:String}';
                return "$relativeTypeImports\r\n$placeholderQuery";
            }
        );

        $schema = graphql()->prepSchema();

        return $schema;
    }

    /**
     * @param array $schemaDefinitionFilePaths
     * @return string
     */
    private function concatSchemaDefinitionFilesFromPath(array $schemaDefinitionFilePaths): string
    {
        $concatenatedImports = '';
        foreach ($schemaDefinitionFilePaths as $filePath) {
            $concatenatedImports .= file_get_contents($filePath);
            $concatenatedImports .= "\r\n";
        }

        return $concatenatedImports;
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
