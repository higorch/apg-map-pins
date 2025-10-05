<?php

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/*
Plugin Name: APG Map Pins
Description: Map pins for your locations
Version: 0.1.0
Author: AP Global
Author URI: https://apglobal.dev
Text Domain: apgmappins
Domain Path: /languages
*/

define('APG_MAP_PINS_DIR_PATH', plugin_dir_path(__FILE__));
define('APG_MAP_PINS_DIR_URL', plugin_dir_url(__FILE__));

require APG_MAP_PINS_DIR_PATH . '/includes/helpers.php';
require APG_MAP_PINS_DIR_PATH . '/includes/apg-map-pins-admin-settings.php';
require APG_MAP_PINS_DIR_PATH . '/includes/apg-map-pins-cpt.php';
require APG_MAP_PINS_DIR_PATH . '/includes/apg-map-pins-taxonomies.php';
require APG_MAP_PINS_DIR_PATH . '/includes/apg-map-pins-metaboxes.php';
require APG_MAP_PINS_DIR_PATH . '/includes/apg-map-pins-frontend.php';
