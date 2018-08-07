<?php
namespace DeInternetJongens\LighthouseUtils\Generators\Classes;

class QueriesWithInput
{
    /** @var array $queries */
    private $queries = [];

    /** @var string $inputType */
    private $inputType;

    /**
     * @param array $queries
     * @param string $inputType
     */
    public function __construct(array $queries, string $inputType)
    {
        $this->queries = $queries;
        $this->inputType = $inputType;
    }

    /**
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return (! empty($this->getQueries()) && ! empty($this->getInputType()));
    }

    /**
     * @return array
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * @return string
     */
    public function getInputType(): string
    {
        return $this->inputType;
    }
}
