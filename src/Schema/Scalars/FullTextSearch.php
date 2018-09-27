<?php

namespace DeInternetJongens\LighthouseUtils\Schema\Scalars;

use GraphQL\Type\Definition\ScalarType;

class FullTextSearch extends ScalarType
{
    public $name = 'FullTextSearch';

    public $description = 'To enable fulltext searching on a type';

    public function serialize($value)
    {
        return $value;
    }

    public function parseValue($value)
    {
        return $value;
    }

    public function parseLiteral($valueNode, array $variables = null)
    {
        return $this->parseValue($valueNode->value);
    }
}
