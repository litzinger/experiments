<?php

use Symfony\Component\OptionsResolver\Options;
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
        'initialized' => false,
        'queryParameterName' => 'v',
        'queryParameterValue' => null,
        'randomize' => true,
    ];

    /**
     * @var \BoldMinded\Experiments\Services\Variation
     */
    private $variationService;

    /**
     * Create a cached options array. {exp:experiments:content} may be called multiple times.
     */
    public function __construct()
    {
        $options = $this->defaultOptions;
        $options['initialized'] = true;

        if ($experimentId = $this->fetchParam('experiment_id')) {
            $options['experimentId'] = $experimentId;
        }

        if ($queryParameter = $this->fetchParam('query_parameter')) {
            $options['queryParameterName'] = $queryParameter;
        }

        if ($randomize = $this->fetchParam('randomize')) {
            $options['randomize'] = $randomize;
        }

        $options['queryParameterValue'] = ee()->input->get($options['queryParameterName']);

        $this->options = $this->configureOptions($options);

        $this->variationService = ee('experiments:Variation');
        $this->variationService->setOptions($this->options);
    }

    /**
     * {exp:experiments:content choose="{experiment_field_name}"}
     *      content to show or hide
     * {/exp:experiments:content}
     *
     * @return string
     */
    public function content()
    {
        $prefix = $this->fetchParam('prefix');
        $choose = $this->fetchParam('choose');

        if ($choose) {
            $choose = (int) $choose;
        }

        $this->variationService->choose($choose);

        $tagdata = $this->getTagdata();

        ee()->load->library('api');
        ee()->legacy_api->instantiate('channel_fields');

        $originalContentSections = ee()->api_channel_fields->get_pair_field($tagdata, 'original', $prefix);
        $variantContentSections = ee()->api_channel_fields->get_pair_field($tagdata, 'variant', $prefix);

        foreach ($originalContentSections as $originalContent) {
            if ($this->variationService->isOriginal() && isset($originalContent[3])) {
                $tagdata = str_replace($originalContent[3], $originalContent[1], $tagdata);
            } else {
                $tagdata = str_replace($originalContent[3], '', $tagdata);
            }
        }

        foreach ($variantContentSections as $variantContent) {
            if ($this->variationService->isVariation() && isset($variantContent[3])) {
                $tagdata = str_replace($variantContent[3], $variantContent[1], $tagdata);
            } else {
                $tagdata = str_replace($variantContent[3], '', $tagdata);
            }
        }

        return $tagdata;
    }

    /**
     * @param array $options
     * @return array
     */
    private function configureOptions(array $options = [])
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([
                'experimentId',
                'chosen',
                'queryParameterName',
                'randomize'
            ])
            ->setDefaults($this->defaultOptions)
            ->setNormalizer('queryParameterValue', function (Options $options, $value) {
                return (int) $value;
            });
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
}
