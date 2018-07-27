<?php
namespace DeInternetJongens\LighthouseUtils\Generators\Classes;

class MutationWithInput
{
    /** @var string $mutation */
    private $mutation;

    /** @var string $inputType */
    private $inputType;

    /**
     * @param string $mutation
     * @param string $inputType
     */
    public function __construct(string $mutation, string $inputType)
    {
        $this->mutation = $mutation;
        $this->inputType = $inputType;
    }

    /**
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return (! empty($this->getMutation()) && ! empty($this->getInputType()));
    }

    /**
     * @return string
     */
    public function getMutation(): string
    {
        return $this->mutation;
    }

    /**
     * @return string
     */
    public function getInputType(): string
    {
        return $this->inputType;
    }
}
