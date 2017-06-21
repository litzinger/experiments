<?php

if ( !defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @package     ExpressionEngine
 * @subpackage  Plugins
 * @category    Bloqs Experiments
 * @author      Brian Litzinger
 * @copyright   Copyright (c) 2012, 2017 - BoldMinded, LLC
 * @link        http://boldminded.com/add-ons/bloqs-experiments
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

require PATH_THIRD.'bloqs_experiments/addon.setup.php';

class Bloqs_experiments {

    private $config = [
        'query_parameter' => 'v',
        'randomize' => true,
    ];

    public function run()
    {
        $experimentId = $this->fetchParam('experiment_id');
        $experimentType = $this->fetchParam('experiment_type');

        $this->config = $this->validateConfig();
    }

    public function wrap()
    {
        $tagdata = $this->getTagdata();
        $variation = (int) $this->fetchParam('variation', 1);

        if (!is_int($variation)) {
            return $tagdata;
        }

        if ($this->config['chosen'] == $variation) {

        }
    }

    private function fetchParam($name, $defaultValue = null)
    {
        return ee()->TMPL->fetch_param($name, $defaultValue);
    }

    private function getTagdata()
    {
        return ee()->TMPL->tagdata;
    }

    private function validateConfig()
    {
        // OptionsResolver
        return [];
    }

}
