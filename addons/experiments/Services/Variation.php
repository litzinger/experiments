<?php

namespace BoldMinded\Experiments\Services;

class Variation
{
    const QUERY_PARAM_NAME = 'v';
    const MAX_VARIATIONS = 3;
    const VARIANT_CONTROL = 0;
    const VARIANT_1 = 1;
    const VARIANT_2 = 2;
    const VARIANT_3 = 3;
    const VARIANT_ANY = 99;

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
        'default' => self::VARIANT_CONTROL,
    ];

    /**
     * @var array
     */
    private $options;

    /**
     * @var boolean
     */
    private $isControl;

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
        if (!is_int($chosenVariation)) {
            $this->setIsControl(false);
            $this->setIsVariation(false);

            return $this;
        }

        if ($chosenVariation === self::VARIANT_CONTROL) {
            $this->setIsControl(true);
            $this->setIsVariation(false);

            return $this;
        }

        $this->setIsControl(false);
        $this->setIsVariation(true);

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

        if ($chosen === self::VARIANT_ANY && in_array($this->getChosen(), range(1, self::MAX_VARIATIONS))) {
            return true;
        }

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
            // Randomize option currently only works with Control and Variant 1. If we randomize with multiple variants
            // then we need to know how many there are. Could be 2, could be 3, but currently don't have a means of
            // determining the max variants in play. We can't randomly pick 3 if there are only 2 variations.
            $this->setChosen(rand(self::VARIANT_CONTROL, self::VARIANT_1));
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
    public function isControl()
    {
        return $this->isControl;
    }

    /**
     * @param boolean $isControl
     *
     * @return $this
     */
    public function setIsControl($isControl)
    {
        $this->isControl = $isControl;

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
     * @param string $name
     * @return mixed|null
     */
    public function getOption(string $name)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        return null;
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
            if (array_key_exists($optionKey, $eeConfigOptions)) {
                $this->defaultOptions[$optionKey] = $eeConfigOptions[$optionKey];
            }
        }

        return $this;
    }
}
