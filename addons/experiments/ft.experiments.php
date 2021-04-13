<?php

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
            '' => lang('experiments_always_show'),
            0 => lang('experiments_control'),
            1 => lang('experiments_variant_1'),
            2 => lang('experiments_variant_2'),
            3 => lang('experiments_variant_3'),
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
        return $this->createSelect(
            'variation',
            $fieldName,
            $this->variantOptions,
            ($data ? $data : 0)
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

        $name = ee('Format')->make('Text', $optionName)->urlSlug();

        return $field . form_dropdown($fieldName .'['. $name .']', $options, $data);
    }

    /**
     * @param $data
     * @param array $params
     * @param string $tagdata
     * @return string url
     */
    public function replace_tag($data, $params = [], $tagdata = '')
    {
        return ee()->TMPL->parse_variables($tagdata, [$data]);
    }
}
