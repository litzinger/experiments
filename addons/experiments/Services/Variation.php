<?php

namespace BoldMinded\Experiments\Services;

class Variation
{
    const QUERY_PARAM_NAME = 'v';
    const MAX_VARIATIONS = 3;

    /**
     * @var int
     */
    private $chosen;

    /**
     * @var array
     */
    private $defaultOptions = [
        'queryParameterName' => self::QUERY_PARAM_NAME,
        'queryParameterValue' => null,
        'randomize' => false,
        'default' => 0,
    ];

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
     *
     * @return bool
     */
    public function shouldShowContent($chosen)
    {
        $this->chooseVariation();

        if (is_int($chosen) && $this->getChosen() !== $chosen) {
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
            $this->setChosen(rand(0, self::MAX_VARIATIONS));
        } elseif (is_int($this->options['default']) && in_array($this->options['default'], range(0, self::MAX_VARIATIONS))) {
            $this->setChosen($this->options['default']);
        }

        return $this;
    }

    /**
     * @param int $chosen
     *
     * @return $this
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
     * @return boolean
     */
    public function isOriginal()
    {
        return $this->isOriginal;
    }

    /**
     * @param boolean $isOriginal
     *
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
     *
     * @return $this
     */
    public function setIsVariation($isVariation)
    {
        $this->isVariation = $isVariation;

        return $this;
    }

    /**
     * @param array $options
     * @param array $getParams
     * @return $this
     */
    public function setOptions(array $options = [], array $getParams = [])
    {
        $this->options = $this->defaultOptions;

        if (isset($options['queryParameterName'])) {
            $this->options['queryParameterName'] = $options['queryParameterName'];
        }

        if (isset($options['randomize'])) {
            $this->options['randomize'] = $options['randomize'];
        }

        if (isset($options['default'])) {
            $this->options['default'] = $options['default'];
        }

        if (isset($getParams[$this->options['queryParameterName']])) {
            $this->options['queryParameterValue'] = (int) $getParams[$this->options['queryParameterName']];
        }

        return $this;
    }

    /**
     * @param array $eeConfigOptions
     *
     * @return $this
     */
    public function setOptionsFromConfig(array $eeConfigOptions = [])
    {
        foreach ($this->defaultOptions as $optionKey => $optionValue) {
            if (isset($eeConfigOptions[$optionKey])) {
                $this->defaultOptions[$optionKey] = $eeConfigOptions[$optionKey];
            }
        }

        return $this;
    }
}
