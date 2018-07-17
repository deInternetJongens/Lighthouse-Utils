<?php

namespace deinternetjongens\LighthouseGenerators\Console;

use deinternetjongens\LighthouseGenerators\Generators\SchemaGenerator;
use Illuminate\Console\Command;

class GenerateSchema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lighthouse-generators:generate-schema';
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
     */
    public function handle()
    {
        // Clear the Lighthouse cached schema
        \Cache::forget(config('lighthouse.cache.key'));

        $newSchemaPath = config('lighthouse.schema.register');
        $this->askWithCompletion(
            sprintf(
                'Generating schema in location: "%s", do you want to continue?',
                $newSchemaPath
            ),
            ['yes', 'no'],
            'yes'
        );

        $schemaFilesPaths = config('lighthouse-generators.schema_paths');
        $generatedSchema = $this->schemaGenerator->generate($schemaFilesPaths);
        dump($generatedSchema);

        $this->info('Generation complete. Validating schema.');
        $this->call('lighthouse:validate-schema');
    }
}
