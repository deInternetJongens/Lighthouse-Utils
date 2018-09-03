<?php

namespace DeInternetJongens\LighthouseUtils\Schema\Scalars;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\EnumValueDefinition;

class Gender extends EnumType
{
    /** @var string */
    public $name = 'Gender';

    /** @var string */
    public $description = 'A gender. Options: male, female and other.';

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $config['values'] = [
            new EnumValueDefinition(['name' => 'male', 'value' => 'male']),
            new EnumValueDefinition(['name' => 'female', 'value' => 'female']),
            new EnumValueDefinition(['name' => 'other', 'value' => 'other']),
            new EnumValueDefinition(['name' => 'unknown', 'value' => 'unknown']),
        ];

        parent::__construct($config);
    }
}
