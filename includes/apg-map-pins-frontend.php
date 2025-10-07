<?php

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Frontend_Apg_Map_Pins
{
    public function __construct()
    {
        add_shortcode('apg_map_pins', array($this, 'apg_map_pins_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'apg_map_pins_enqueue_scripts'));

        add_action('wp_ajax_apg_map_pins_get_locations', [$this, 'get_locations']);
        add_action('wp_ajax_nopriv_apg_map_pins_get_locations', [$this, 'get_locations']);
    }

    public function apg_map_pins_enqueue_scripts()
    {
        wp_enqueue_style('choicesjs', APG_MAP_PINS_DIR_URL . 'assets/plugins/choices.min.css', null, '11.1.0');
        wp_enqueue_style('style-apg-map-pins', APG_MAP_PINS_DIR_URL . 'assets/css/frontend.css', null, '1.0.7');

        wp_enqueue_script('choicesjs', APG_MAP_PINS_DIR_URL . 'assets/plugins/choices.min.js', array('jquery'), '11.1.0', true);
        wp_enqueue_script('frontend-apg-map-pins', APG_MAP_PINS_DIR_URL . 'assets/js/frontend.js', array('jquery'), '1.0.7', true);
        wp_localize_script('frontend-apg-map-pins', 'apg_map_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('apg_map_ajax_nonce')
        ));
    }

    public function apg_map_pins_shortcode($atts, $content = null)
    {
        $a = shortcode_atts(array(
            'id' => '',
        ), $atts);

        ob_start();

        // Torna os dados disponíveis para a view
        $key = get_option_apgmappins('apgmappins_geral', 'authentication_api_key', null, default: null);
        $zoom =  get_option_apgmappins('apgmappins_styles', 'styles_map_zoom', null, 10);

        $view_path = APG_MAP_PINS_DIR_PATH . 'templates/frontend.php';

        if (file_exists($view_path)) {
            include $view_path;
        }

        return ob_get_clean();
    }

    public function get_locations()
    {
        check_ajax_referer('apg_map_ajax_nonce', 'security');

        $args = [
            'post_type'      => 'apg-map-pins',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ];

        $query = new WP_Query($args);
        $locations = [];
        $selects   = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                // --- TAXONOMIAS ---
                $country_terms = wp_get_post_terms($post_id, 'country', ['fields' => 'names']);
                $state_terms   = wp_get_post_terms($post_id, 'state', ['fields' => 'names']);
                $city_terms    = wp_get_post_terms($post_id, 'city', ['fields' => 'names']);

                $country = !empty($country_terms) ? $country_terms[0] : 'No country';
                $state   = !empty($state_terms)   ? $state_terms[0]   : 'No state';
                $city    = !empty($city_terms)    ? $city_terms[0]    : 'No city';

                // --- METABOXES ---
                $entries = get_post_meta($post_id, '_apg_map_pins_details', true);

                $fields = [
                    'latitude'     => $entries['latitude']['value'] ?? '',
                    'longitude'    => $entries['longitude']['value'] ?? '',
                    'landline'     => $entries['landline']['value'] ?? '',
                    'mobile_phone' => $entries['mobile_phone']['value'] ?? '',
                    'email'        => $entries['email']['value'] ?? '',
                    'responsible'  => $entries['responsible']['value'] ?? '',
                    'company'      => $entries['company']['value'] ?? '',
                    'site'         => $entries['site']['value'] ?? '',
                ];

                // --- FLATTENED STRUCTURE PARA MAPA ---
                $locations[] = [
                    'id'      => $post_id,
                    'title'   => get_the_title(),
                    'lat'     => $fields['latitude'],
                    'lng'     => $fields['longitude'],
                    'country' => $country,
                    'state'   => $state,
                    'city'    => $city,
                    'fields'  => $fields,
                ];

                // --- SIMPLIFICADO PARA SELECT ---
                $label = $city . ' (' . $state . ')';
                $selects[$country][$label] = [
                    'id'    => $post_id,
                    'title' => get_the_title(),
                    'lat'   => $fields['latitude'],
                    'lng'   => $fields['longitude'],
                    'data'  => $fields,
                ];
            }

            wp_reset_postdata();
        }

        wp_send_json_success([
            'map'     => $locations,
            'selects' => $selects,
        ]);
    }
}

new Frontend_Apg_Map_Pins();
