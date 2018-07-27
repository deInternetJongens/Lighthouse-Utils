<?php
namespace DeInternetJongens\LighthouseUtils\Generators\Classes;

class MutationWithInput
{
    /** @var string $mutation */
    /** @var string $inputType */
    private $mutation, $inputType;

    /**
     * @param string $mutation
     * @param string $inputType
     */
    public function __construct(string $mutation, string $inputType)
    {
        $this->mutation = $mutation;
        $this->inputType = $inputType;
    }

    public function isNotEmpty(){
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
