<?php

namespace DeInternetJongens\LighthouseUtils\Events;

class GraphQLSchemaGenerated
{
    public $schema;

    /**
     * Create a new event instance.
     * @return void
     */
    public function __construct($schema)
    {
        $this->schema = $schema;
    }
}
