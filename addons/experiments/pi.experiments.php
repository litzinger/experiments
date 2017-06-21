<?php

use Symfony\Component\OptionsResolver\OptionsResolver;

if ( !defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @package     ExpressionEngine
 * @subpackage  Plugins
 * @category    Experiments
 * @author      Brian Litzinger
 * @copyright   Copyright (c) 2012, 2017 - BoldMinded, LLC
 * @link        http://boldminded.com/add-ons/experiments
 * @license
 *
 * Copyright (c) 2017. BoldMinded, LLC
 * All rights reserved.
 *
 * This source is commercial software. Use of this software requires a
 * site license for each domain it is used on. Use of this software or any
 * of its source code without express written permission in the form of
 * a purchased commercial or other license is prohibited.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE.
 *
 * As part of the license agreement for this software, all modifications
 * to this source must be submitted to the original author for review and
 * possible inclusion in future releases. No compensation will be provided
 * for patches, although where possible we will attribute each contribution
 * in file revision notes. Submitting such modifications constitutes
 * assignment of copyright to the original author (Brian Litzinger and
 * BoldMinded, LLC) for such modifications. If you do not wish to assign
 * copyright to the original author, your license to  use and modify this
 * source is null and void. Use of this software constitutes your agreement
 * to this clause.
 */

require PATH_THIRD.'experiments/addon.setup.php';

class Experiments {

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var array
     */
    private $defaultOptions = [
        'experimentId' => '',
        'chosen' => null,
        'queryParameter' => 'v',
        'randomize' => true,
        'initialized' => false,
    ];

    /**
     * Create a cached options array. {exp:experiments:content} may be called multiple times.
     */
    public function __construct()
    {
        if (!isset(ee()->session->cache['experiments'])) {
            ee()->session->cache['experiments'] = [];
        }

        $this->options =& ee()->session->cache['experiments'];
    }

    public function run()
    {
        $options = $this->defaultOptions;
        $options['initialized'] = true;

        if ($experimentId = $this->fetchParam('experiment_id')) {
            $options['experimentId'] = $experimentId;
        }

        if ($queryParameter = $this->fetchParam('query_parameter')) {
            $options['queryParameter'] = $queryParameter;
        }

        if ($randomize = $this->fetchParam('randomize')) {
            $options['randomize'] = $randomize;
        }

        $this->options = $this->configureOptions($options);
    }

    /**
     * @return string
     */
    public function content()
    {
        if ($this->options['initialized'] === false) {
            $this->run();
        }

        $this->chooseVariation();
        $tagdata = $this->getTagdata();
        $variation = (int) $this->fetchParam('variation', $this->options['chosen']);

        // If its not a valid variation, or it is defined as 'Always Show'
        if (!is_int($variation) || $variation === 0) {
            return $tagdata;
        }

        // If the selected variation for the content is not what was randomly chosen,
        // or overridden via a query parameter, do not return the content.
        if ($this->options['chosen'] != $variation) {
            return '';
        }

        return $tagdata;
    }

    /**
     * Randomize or override the chosen variation via a GET parameter
     */
    private function chooseVariation()
    {
        $queryParameter = $this->getQueryParameter();

        if ($queryParameter && is_numeric($queryParameter)) {
            $this->options['chosen'] = (int) $queryParameter;
        } elseif ($this->options['randomize'] === true && $this->options['chosen'] === null) {
            $this->options['chosen'] = rand(1, 2);
        }
    }

    /**
     * @param array $options
     * @return array
     */
    private function configureOptions(Array $options)
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([
                'experimentId',
                'chosen',
                'queryParameter',
                'randomize'
            ])
            ->setDefaults($this->defaultOptions)
        ;

        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * @param $name
     * @param null $defaultValue
     * @return bool|string
     */
    private function fetchParam($name, $defaultValue = null)
    {
        $value = ee()->TMPL->fetch_param($name, $defaultValue);

        if (in_array($value, ['yes', 'y'])) {
            return true;
        }

        if (in_array($value, ['no', 'n'])) {
            return false;
        }

        return $value;
    }

    /**
     * @return string
     */
    private function getTagdata()
    {
        return ee()->TMPL->tagdata;
    }

    /**
     * @return string|null
     */
    private function getQueryParameter()
    {
        return ee()->input->get($this->options['queryParameter']);
    }
}
