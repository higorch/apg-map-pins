<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!function_exists('get_option_apgmappins')) {

    function get_option_apgmappins($option, $key, $value = null, $default = '')
    {
        $opt = get_option($option);

        if ($value) {
            return isset($opt[$key]) && !empty($opt[$key]) && esc_attr($opt[$key]) == $value ? esc_attr($opt[$key]) : $default;
        }

        return isset($opt[$key]) && !empty($opt[$key]) ? esc_attr($opt[$key]) : $default;
    }
}
