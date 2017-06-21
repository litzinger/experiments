<?php

if ( !defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @package     ExpressionEngine
 * @subpackage  Fieldtypes
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

class Experiments_ft extends EE_Fieldtype
{
    /**
     * @var array
     */
    public $settings = [];

    /**
     * @var array
     */
    public $info = [
        'name' => EXPERIMENTS_NAME,
        'version' => EXPERIMENTS_VERSION,
    ];

    /**
     * @var bool
     */
    public $has_array_data = false;

    /**
     * @var array
     */
    private $variantOptions = [];

    public function __construct()
    {
        parent::__construct();

        ee()->lang->loadfile('experiments');

        $this->variantOptions = [
            0 => lang('experiments_always_show'),
            1 => lang('experiments_original'),
            2 => lang('experiments_variant'),
        ];
    }

    public function accepts_content_type($name)
    {
        return ($name == 'blocks/1');
    }

    public function display_field($data)
    {
        return $this->renderField($data, $this->field_name);
    }

    public function grid_display_field($data)
    {
        return $this->display_field($data);
    }

    /**
     * @param $data
     * @param $fieldName
     * @return string
     */
    private function renderField($data, $fieldName)
    {
        $data = !is_array($data) ? json_decode($data) : $data;

        return $this->createSelect(
            'variation',
            $fieldName,
            $this->variantOptions,
            (isset($data->variation) ? $data->variation : 0)
        );
    }

    /**
     * @param $optionName
     * @param $fieldName
     * @param $options
     * @param $data
     * @param string $label
     * @param string $instructions
     * @return string
     */
    private function createSelect($optionName, $fieldName, $options, $data, $label = '', $instructions = '')
    {
        $field = '';

        if ($label) {
            $field .= '<label>'. $label .'</label>';
        }
        if ($instructions) {
            $field .= '<label class="blocksft-atom-instructions">'. $instructions .'</label>';
        }

        $field .= form_dropdown($fieldName .'['. url_title($optionName, '_', true) .']', $options, $data);

        return $field;
    }

    /**
     * Just need an empty method here since we are not doing anything special.
     */
    public function grid_save_settings()
    {
        return;
    }

    /**
     * Save Normal Data
     *
     * @param $data
     * @return string
     */
    public function save($data)
    {
        return json_encode($data);
    }

    /**
     * Save Matrix Cell Data
     *
     * @param $data
     * @return string
     */
    public function save_cell($data)
    {
        return $this->save($data);
    }

    /**
     * Matrix/Grid and Default tag rendering
     *
     * @param $data
     * @param array $params
     * @param string $tagdata
     * @return string url
     */
    public function replace_tag($data, $params = [], $tagdata = '')
    {
        $data = [$this->prepareData($data)];
        $tagdata = ee()->TMPL->parse_variables($tagdata, $data);

        return $tagdata;
    }

    /**
     * Allow direct access to a specific value, e.g.
     *
     * {field_name:modifier}
     *
     * @param $data
     * @param array $params
     * @param bool $tagdata
     * @param $modifier
     * @return string
     */
    public function replace_tag_catchall($data, $params = [], $tagdata = false, $modifier)
    {
        $data = $this->prepareData($data);
        return isset($data[$modifier]) ? $data[$modifier] : '';
    }

    /**
     * @param $data
     * @return mixed
     */
    private function prepareData($data)
    {
        return json_decode($data, true);
    }
}
