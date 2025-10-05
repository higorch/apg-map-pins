<?php

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Frontend_Apg_Map_Pins
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'apg_map_pins_enqueue_scripts'));
    }

    public function apg_map_pins_enqueue_scripts()
    {
        wp_enqueue_style('style-apg-map-pins', APG_MAP_PINS_DIR_URL . 'assets/css/frontend.css', null, '1.0.0');

        wp_enqueue_script('frontend-apg-map-pins', APG_MAP_PINS_DIR_URL . 'assets/js/frontend.js', array('jquery'), '1.0.0', true);
    }
}

new Frontend_Apg_Map_Pins();
