<?php

if (! defined('ABSPATH')) {
    exit;
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
        wp_enqueue_style('style-apg-map-pins', APG_MAP_PINS_DIR_URL . 'assets/css/frontend.css', null, '3.0.1');

        // Choices e frontend
        wp_enqueue_script('choicesjs', APG_MAP_PINS_DIR_URL . 'assets/plugins/choices.min.js', array('jquery'), '11.1.0', true);
        wp_enqueue_script('frontend-apg-map-pins', APG_MAP_PINS_DIR_URL . 'assets/js/frontend.js', array('jquery', 'choicesjs'), '3.0.1', true);

        // Localize ajax + styles/key (map key must be safe to expose)
        wp_localize_script('frontend-apg-map-pins', 'apg_map_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('apg_map_ajax_nonce'),
        ));
    }

    public function apg_map_pins_shortcode($atts, $content = null)
    {
        // $a = shortcode_atts(array(
        //     'id' => '',
        // ), $atts);

        ob_start();

        // Torna os dados disponíveis para a view
        $key = get_option_apgmappins('apgmappins_geral', 'authentication_api_key', null, default: null);
        $map_side_bar_details = get_option_apgmappins('apgmappins_geral', 'map_side_bar_details', null, default: null);
        $title = get_option_apgmappins('apgmappins_geral', 'map_title', null, default: __('Locais e representantes', 'apgmappins'));
        $styles = wp_json_encode([
            "water_color" => get_option_apgmappins('apgmappins_styles', 'styles_water_color', null, "#9474ff"),
            "landscape_color" => get_option_apgmappins('apgmappins_styles', 'styles_landscape_color', null, "#F5F5F5"),
            "road_color" => get_option_apgmappins('apgmappins_styles', 'styles_road_color', null, "#FFFFFF"),
            "road_labels_text_color" => get_option_apgmappins('apgmappins_styles', 'styles_road_labels_text_color', null, "#4B0082"),
            "administrative_color" => get_option_apgmappins('apgmappins_styles', 'styles_administrative_color', null, "#9474ff"),
            "administrative_labels_text_color" => get_option_apgmappins('apgmappins_styles', 'styles_administrative_labels_text_color', null, "#9474ff"),
        ]);

        $view_path = APG_MAP_PINS_DIR_PATH . 'templates/frontend.php';

        if (file_exists($view_path)) {
            include $view_path;
        }

        return ob_get_clean();
    }

    /**
     * AJAX: retorna locais para o mapa e estrutura para o select agrupado por país (termo pai)
     * Usa a taxonomia 'territories' com hierarquia máxima pai > filho.
     */
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

                // --- PEGA O ID DO TERRITÓRIO SALVO NA METABOX ---
                $entries = get_post_meta($post_id, '_apg_map_pins_details', true);
                $territory_id = $entries['territory']['value'] ?? 0;

                if (!$territory_id) {
                    continue; // sem territory selecionado
                }

                // --- PEGA O TERMO PELO ID ---
                $child_term = get_term($territory_id, 'territories');
                if (!$child_term || is_wp_error($child_term)) {
                    continue;
                }

                // --- VERIFICA PAI ---
                if (!$child_term->parent) {
                    continue; // o termo salvo é pai, pulamos
                }

                $parent_term = get_term($child_term->parent, 'territories');
                $parent_id   = $parent_term ? $parent_term->term_id : 0;
                $parent_name = $parent_term ? $parent_term->name : 'Sem país';

                $child_id   = $child_term->term_id;
                $child_name = $child_term->name;

                // --- METABOXES ---
                $fields = [
                    'marker_fill_color'     => $entries['marker_fill_color']['value'] ?? '',
                    'marker_stroke_color'   => $entries['marker_stroke_color']['value'] ?? '',
                    'territory'             => $entries['territory']['value'] ?? '',
                    'latitude'              => $entries['latitude']['value'] ?? '',
                    'longitude'             => $entries['longitude']['value'] ?? '',
                    'landline'              => $entries['landline']['value'] ?? '',
                    'mobile_phone'          => $entries['mobile_phone']['value'] ?? '',
                    'email'                 => $entries['email']['value'] ?? '',
                    'responsible'           => $entries['responsible']['value'] ?? '',
                    'company'               => $entries['company']['value'] ?? '',
                    'site'                  => $entries['site']['value'] ?? '',
                ];

                // Ignora se lat/lng inválidos
                if ($fields['latitude'] === '' || $fields['longitude'] === '') {
                    continue;
                }

                // --- FLATTENED STRUCTURE PARA MAPA ---
                $locations[] = [
                    'id'      => $post_id,
                    'title'   => get_the_title(),
                    'lat'     => $fields['latitude'],
                    'lng'     => $fields['longitude'],
                    'country' => $parent_name,
                    'state'   => ['id' => $parent_id, 'name' => $parent_name],
                    'city'    => ['id' => $child_id,  'name' => $child_name],
                    'fields'  => $fields,
                ];

                // --- AGRUPAMENTO PARA SELECT (país -> cidades) ---
                if (!isset($selects[$parent_id])) {
                    $selects[$parent_id] = [
                        'id'     => $parent_id,
                        'name'   => $parent_name,
                        'locals' => [],
                    ];
                }

                // Evita duplicados de cidades
                $exists = array_filter(
                    $selects[$parent_id]['locals'],
                    fn($local) => $local['id'] === $child_id
                );

                if (empty($exists)) {
                    $selects[$parent_id]['locals'][] = [
                        'id'    => $child_id,
                        'label' => $child_name,
                    ];
                }
            }

            wp_reset_postdata();
        }

        // Reindexa selects para array (Choices espera array)
        $selects = array_values($selects);

        wp_send_json_success([
            'map'     => $locations,
            'selects' => $selects,
        ]);
    }
}

new Frontend_Apg_Map_Pins();
