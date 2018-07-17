<?php

namespace deinternetjongens\LighthouseGenerators\Generators;

class SchemaGenerator
{
    private $requiredSchemaFileKeys = ['mutations', 'queries', 'types'];

    public function generate(array $schemaFilesPaths): string
    {
        $allPathsPassed = $this->validateFilesPaths($schemaFilesPaths);
        if (! $allPathsPassed) {
            throw new \Exception(
                'The \'schema_paths\' config value is not correct, it should contain a value with a valid path for the following keys: mutations, queries, types`'
            );
        }

        return 'schema shizzle';
    }

    private function generateQueriesFromTypes(string $typesPath)
    {
    }

    private function validateFilesPaths(array $schemaFilesPaths): bool
    {
        if (count($schemaFilesPaths) < 1) {
            return false;
        }

        if (array_diff(array_keys($schemaFilesPaths), $this->requiredSchemaFileKeys)) {
            return false;
        }
        foreach ($schemaFilesPaths as $path) {
            if (empty($path)) {
                return false;
            }
            if (! file_exists($path)) {
                return false;
            }
        }

        return true;
    }
}
