<?php

class Experiment_ft extends EE_Fieldtype
{

    public $settings = [];
    public $has_array_data = false;
    public $info = [
        'name'      => 'Jamf Experiment (A/B Testing)',
        'version'   => '1.0.0',
    ];

    private $cache = [];

    private $variantOptions = [
        'all' => 'Always Show',
        '1' => 'Original',
        '2' => 'Variant'
    ];

    /**
     * Constructor
     *
     * @access  public
     */
    public function __construct()
    {
        parent::__construct();

        if (!isset(ee()->session->cache['jamfExperiment'])) {
            ee()->session->cache['jamfExperiment'] = [];
        }

        $this->cache =& ee()->session->cache['jamfExperiment'];
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

        return $this->createSelect('Variation', $fieldName, $this->variantOptions, (isset($data->variation) ? $data->variation : 'all'), 'The current variation group this content belongs to.');
    }

    /**
     * @param $label
     * @param $name
     * @param $data
     * @param string $instructions
     * @return string
     */
    private function createInput($label, $name, $data, $instructions = '')
    {
        $field = '<label>'. $label .'</label>';
        if ($instructions) {
            $field .= '<label class="blocksft-atom-instructions">'. $instructions .'</label>';
        }
        $name = $name .'['. url_title($label, '_', true) .']';
        $field .= form_input($name, $data);

        return $field;
    }

    /**
     * @param $label
     * @param $name
     * @param $options
     * @param $data
     * @param string $instructions
     * @return string
     */
    private function createSelect($label, $name, $options, $data, $instructions = '')
    {
        $field = '<label>'. $label .'</label>';
        if ($instructions) {
            $field .= '<label class="blocksft-atom-instructions">'. $instructions .'</label>';
        }
        $name = $name .'['. url_title($label, '_', true) .']';
        $field .= form_dropdown($name, $options, $data);

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
    public function replace_tag($data, $params = array(), $tagdata = '')
    {
        $data = array($this->prepareData($data));
        $tagdata = ee()->TMPL->parse_variables($tagdata, $data);

        return $tagdata;
    }

    /**
     * Allow direct access to a specific value, e.g.
     *
     * {myfield:modifier}
     *
     * @param $data
     * @param array $params
     * @param bool $tagdata
     * @param $modifier
     * @return string
     */
    public function replace_tag_catchall($data, $params = array(), $tagdata = false, $modifier)
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
