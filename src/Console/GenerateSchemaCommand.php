<?php

namespace deinternetjongens\LighthouseUtils\Console;

use deinternetjongens\LighthouseUtils\Generators\SchemaGenerator;
use Illuminate\Console\Command;

class GenerateSchemaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lighthouse-utils:generate-schema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a new schema from seperate files and validate the generated schema.';

    /** @var SchemaGenerator */
    private $schemaGenerator;

    /**
     * @param SchemaGenerator $schemaGenerator
     */
    public function __construct(SchemaGenerator $schemaGenerator)
    {
        $this->schemaGenerator = $schemaGenerator;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        // Clear the Lighthouse cached schema
        $this->call('lighthouse:clear-cache');

        $schemaFilePath = config('lighthouse.schema.register');
        $this->askWithCompletion(
            sprintf(
                'Generating schema in location: "%s", do you want to continue?',
                $schemaFilePath
            ),
            ['yes', 'no'],
            'yes'
        );

        $schemaFilesPaths = config('lighthouse-utils.schema_paths');
        $generatedSchema = $this->schemaGenerator->generate($schemaFilesPaths);

        $schemaFile = fopen($schemaFilePath, 'wb');
        fwrite($schemaFile, $generatedSchema);

        $this->info('Generation complete. Validating schema.');
        $this->call('lighthouse:validate-schema');
    }
}
