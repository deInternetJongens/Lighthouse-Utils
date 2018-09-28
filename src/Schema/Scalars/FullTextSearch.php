<?php

namespace DeInternetJongens\LighthouseUtils\Schema\Scalars;

use GraphQL\Type\Definition\ScalarType;

class FullTextSearch extends ScalarType
{
    public $name = 'FullTextSearch';

    public $description = 'Extension of String type. When querying this field, you can extend the query via the model Scope fullTextSearch';

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
