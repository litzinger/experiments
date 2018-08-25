<?php

namespace BoldMinded\Experiments\Services;

class Variation
{
    /**
     * @var int
     */
    private $chosen;

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
     * @return $this
     */
    public function choose($chosenVariation = null)
    {
        if (!$chosenVariation) {
            $this->chooseVariation();
            $chosenVariation = $this->getChosen();
        }

        // If its not a valid variation, or it is defined as 'Always Show'
        if (!is_int($chosenVariation) || $chosenVariation === 0) {
            $this->setIsOriginal(false);
            $this->setIsVariation(false);

            return $this;
        }

        if ($chosenVariation === 2) {
            $this->setIsOriginal(false);
            $this->setIsVariation(true);

            return $this;
        }

        $this->setIsOriginal(true);
        $this->setIsVariation(false);

        return $this;
    }

    /**
     * @param $chosen
     * @return bool
     */
    public function shouldShowContent($chosen)
    {
        $this->chooseVariation();

        if ($this->getChosen() !== $chosen && $chosen !== 0) {
            return false;
        }

        return true;
    }

    /**
     * Randomize or override the chosen variation via a GET parameter
     *
     * @return $this
     */
    private function chooseVariation()
    {
        if ($this->getChosen() !== null) {
            return $this;
        }

        $queryParameterValue = $this->options['queryParameterValue'];

        if ($queryParameterValue && is_numeric($queryParameterValue)) {
            $this->setChosen((int) $queryParameterValue);
        } elseif ($this->options['randomize'] === true && $this->getChosen() === null) {
            $this->setChosen(rand(1, 2));
        }

        return $this;
    }

    /**
     * @param int $chosen
     */
    public function setChosen($chosen)
    {
        $this->chosen = $chosen;

        return $this;
    }

    /**
     * @return int
     */
    public function getChosen()
    {
        return $this->chosen;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options;

        return $this;
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
