<?php

use BoldMinded\Experiments\Services\Variation;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

if ( !defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @package     ExpressionEngine
 * @subpackage  Plugins
 * @category    Experiments
 * @author      Brian Litzinger
 * @copyright   Copyright (c) 2012, 2018 - BoldMinded, LLC
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
     * @var \BoldMinded\Experiments\Services\Variation
     */
    private $variationService;

    /**
     * Create a cached options array. {exp:experiments:content} may be called multiple times.
     */
    public function __construct()
    {
        $options = [];

        if ($experimentId = $this->fetchParam('experiment_id')) {
            $options['experimentId'] = $experimentId;
        }

        if ($queryParameter = $this->fetchParam('query_parameter')) {
            $options['queryParameterName'] = $queryParameter;
        }

        if ($randomize = $this->fetchParam('randomize')) {
            $options['randomize'] = $randomize;
        }

        // Get options from the config file
        $eeConfigOptions = ee()->config->item('experiments') ?: [];
        $getParams = ee('Security/XSS')->clean($_GET);

        $this->variationService = ee('experiments:Variation');
        $this->variationService
            ->setOptionsFromConfig($eeConfigOptions)
            ->setOptions($options, $getParams);
    }

    /**
     * {exp:experiments:content choose="{experiment_field_name}"}
     *      {if control}
     *          Control Content #1
     *      {/if}
     *
     *      Always Shown
     *
     *      {if control}
     *          Control Content #2
     *      {/if}
     *
     *      {if variant_1}
     *          Variant Content #1
     *      {/if}
     *
     *      {if variant_2}
     *          Variant Content #2
     *      {/if}
     *
     * `    {if variant_3}
     *          Variant Content #3
     *      {/if}
     *
     *      {if variant_any}
     *          Variant Content #1, #2, or #3
     *      {/if}
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
        $chosen = $this->variationService->getChosen();
        $tagdata = $this->getTagdata();

        $vars = [
            $prefix.'control' => false,
            $prefix.'variant_1' => false,
            $prefix.'variant_2' => false,
            $prefix.'variant_3' => false,
            $prefix.'variant_any' => false,
        ];

        if ($chosen === Variation::VARIANT_CONTROL) {
            $vars[$prefix.'control'] = true;
        } else {
            foreach (range(Variation::VARIANT_CONTROL, Variation::MAX_VARIATIONS) as $num) {
                if ($num === $chosen) {
                    $vars[$prefix.'variant_' . $num] = true;
                    $vars[$prefix.'variant_any'] = true;
                }
            }
        }

        $tagdata = ee()->functions->prep_conditionals($tagdata, [$vars]);
        $tagdata = ee()->TMPL->parse_variables($tagdata, [$vars]);

        return $tagdata;
    }

    /**
     * @deprecated - Please use Bloqs 4.6+ and the new {bloqs:children} tag along with the Bloqs Experiments add-on
     *              https://github.com/litzinger/bloqs-experiments
     *
     * {exp:channel:entries channel="pages" entry_id="{segment_2}"}
     *      {exp:experiments:bloqs}
     *          {blocks_field}
     *              ... blocks ...
     *          {/blocks_field}
     *      {/exp:experiments:bloqs}
     * {/exp:channel:entries}
     *
     * @return string
     */
    public function bloqs()
    {
        $tagdata = $this->getTagdata();
        $tagdata = $this->recurseMatchBloqs($tagdata);

        return $tagdata;
    }

    /**
     * @param string $tagdata
     * @return string
     */
    private function recurseMatchBloqs($tagdata)
    {
        $matches = $this->matchBloqs($tagdata);

        if (empty($matches)) {
            return $tagdata;
        }

        foreach ($matches['chunks'] as $matchKey => $chunk) {
            $vars = $matches['vars'][$matchKey];

            if ($vars !== '') {
                $vars = json_decode(html_entity_decode($vars), true);

                if ($vars === null) {
                    $tagdata = $this->removeWrappingMarkers($tagdata, $matches);
                    $tagdata = $this->recurseMatchBloqs($tagdata);
                }

                $atoms = $this->findExperimentAtoms($vars);

                if (empty($atoms)) {
                    $tagdata = $this->removeWrappingMarkers($tagdata, $matches);
                    $tagdata = $this->recurseMatchBloqs($tagdata);
                }

                $vars = array_filter($vars, function($value, $index) use ($atoms) {
                    return in_array($index, $atoms);
                }, ARRAY_FILTER_USE_BOTH);

                // Should only be 1 Experiment field added to a block. If more were added ignore them and use the first.
                $chosen = reset($vars);

                if (is_numeric($chosen)) {
                    $chosen = (int) $chosen;
                } else {
                    $chosen = null;
                }

                // Remove the block tag pair from the output
                if (!$this->variationService->shouldShowContent($chosen)) {
                    $tagdata = str_replace($chunk, '', $tagdata);
                    $tagdata = $this->recurseMatchBloqs($tagdata);
                }
            }
        }

        return $tagdata;
    }

    /**
     * @param $tagdata
     * @return array
     */
    private function matchBloqs($tagdata)
    {
        preg_match_all('/{!-- bloqs:start:(\d+) vars="(.*?)" --}(.*){!-- bloqs:end:\1 --}/is', $tagdata, $matches);

        if (!$matches) {
            return [];
        }

        return [
            'chunks' => $matches[0],
            'ids' => $matches[1],
            'vars' => $matches[2],
            'content' => $matches[3],
        ];
    }

    /**
     * @param string $tagdata
     * @param array[chunks,id,vars,content] $matches
     * @return string
     */
    private function removeWrappingMarkers($tagdata, $matches)
    {
        foreach ($matches['ids'] as $id) {
            $tagdata = preg_replace('/\{!-- bloqs:(start|end):'. preg_quote($id) .'.*?--\}/is', '', $tagdata);
        }

        return $tagdata;
    }

    /**
     * @param array $vars
     * @return array
     */
    private function findExperimentAtoms($vars)
    {
        if (empty($vars)) {
            return [];
        }

        $result = ee('db')
            ->where_in('id', array_keys($vars))
            ->where('type', 'experiments')
            ->get('blocks_atomdefinition')
            ->result_array();

        return array_column($result, 'id');
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
