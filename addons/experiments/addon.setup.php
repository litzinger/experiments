<?php

// Make sure our packages are loaded
require 'vendor/autoload.php';

if (! defined('EXPERIMENTS_VERSION')) {
    define('EXPERIMENTS_NAME', 'Experiments');
    define('EXPERIMENTS_VERSION', '1.0.0');
}

return [
    'author'      => 'BoldMinded',
    'author_url'  => 'https://boldminded.com/add-ons/experiments',
    'docs_url'    => 'http://docs.boldminded.com/experiments',
    'name'        => EXPERIMENTS_NAME,
    'description' => 'A/B Experiments',
    'version'     => EXPERIMENTS_VERSION,
    'namespace'   => 'BoldMinded\Experiments',
    'settings_exist' => false,
];
