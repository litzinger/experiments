<?php

namespace BoldMinded\Experiments\Services;

class Variation
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var boolean
     */
    private $isOriginal;

    /**
     * @var boolean
     */
    private $isVariation;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @return null
     */
    public function choose($chosenVariation = null)
    {
        if (!$chosenVariation) {
            $chosenVariation = $this->chooseVariation();
        }

        // If its not a valid variation, or it is defined as 'Always Show'
        if (!is_int($chosenVariation) || $chosenVariation === 0) {
            $this->setIsOriginal(false);
            $this->setIsVariation(false);

            return;
        }

        if ($chosenVariation === 2) {
            $this->setIsOriginal(false);
            $this->setIsVariation(true);

            return;
        }

        $this->setIsOriginal(true);
        $this->setIsVariation(false);
    }

    /**
     * Randomize or override the chosen variation via a GET parameter
     *
     * @return int
     */
    private function chooseVariation()
    {
        if (isset($this->options['chosen'])) {
            return $this->options['chosen'];
        }

        $queryParameterValue = $this->options['queryParameterValue'];

        if ($queryParameterValue && is_numeric($queryParameterValue)) {
            $this->options['chosen'] = $queryParameterValue;
        } elseif ($this->options['randomize'] === true && $this->options['chosen'] === null) {
            $this->options['chosen'] = rand(1, 2);
        }

        return (int) $this->options['chosen'];
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @return boolean
     */
    public function isOriginal()
    {
        return $this->isOriginal;
    }

    /**
     * @param boolean $isOriginal
     * @return $this
     */
    public function setIsOriginal($isOriginal)
    {
        $this->isOriginal = $isOriginal;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isVariation()
    {
        return $this->isVariation;
    }

    /**
     * @param boolean $isVariation
     * @return $this
     */
    public function setIsVariation($isVariation)
    {
        $this->isVariation = $isVariation;

        return $this;
    }
}
