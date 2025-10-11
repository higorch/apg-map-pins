<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Admin_Settings_Apg_Map_Pins
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'menu_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function menu_page()
    {
        add_options_page(
            __('APG Map Pins', 'apgmappins'), // Título da página
            __('APG Map Pins', 'apgmappins'), // Texto no menu de Configurações
            'manage_options',                 // Permissão
            'apgmappins',                     // Slug
            array($this, 'configs')           // Callback para renderizar a página
        );
    }

    public function configs()
    {
        require APG_MAP_PINS_DIR_PATH . 'templates/admin-settings.php';
    }

    public function enqueue_scripts($page)
    {
        $screen = get_current_screen();

        $is_settings_page = ($page === 'settings_page_apgmappins');
        $is_apg_map_pins_post_type = ($screen && $screen->post_type === 'apg-map-pins');

        if (!$is_settings_page && !$is_apg_map_pins_post_type) return;

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('choicesjs', APG_MAP_PINS_DIR_URL . 'assets/plugins/choices.min.css', null, '11.1.0');
        wp_enqueue_style('apgmappins-admin', APG_MAP_PINS_DIR_URL . 'assets/css/admin.css', null, '1.0.3');

        wp_enqueue_script('wp-color-picker-alpha', APG_MAP_PINS_DIR_URL . 'assets/plugins/wp-color-picker-alpha.js', array('wp-color-picker'), '3.0.4', true);
        wp_enqueue_script('choicesjs', APG_MAP_PINS_DIR_URL . 'assets/plugins/choices.min.js', array('jquery'), '11.1.0', true);
        wp_enqueue_script('apgmappins-admin', APG_MAP_PINS_DIR_URL . 'assets/js/admin.js', array('jquery'), '1.0.3', true);
        wp_localize_script('apgmappins-admin', 'apg_map_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('apg_map_ajax_nonce')
        ));
    }

    public function register_settings()
    {
        // Geral
        register_setting('apgmappins_geral', 'apgmappins_geral',  array($this, 'geral_sanitize'));

        add_settings_section('apgmappins_settings_geral',  __('Autenticação', 'apgmappins'),  array($this, 'print_section_info'),  'apgmappins-settings-geral');
        add_settings_field('authentication_api_key', __('API Key (Google Maps)', 'apgmappins'), array($this, 'input_authentication_api_key'), 'apgmappins-settings-geral', 'apgmappins_settings_geral');
        add_settings_field('map_title', __('Titulo do mapa', 'apgmappins'), array($this, 'input_map_title'), 'apgmappins-settings-geral', 'apgmappins_settings_geral');

        // Styles
        register_setting('apgmappins_styles', 'apgmappins_styles', array($this, 'styles_sanitize'));

        add_settings_section('apgmappins_settings_styles', __('Estilos', 'apgmappins'),  array($this, 'print_section_info'),  'apgmappins-settings-styles');
        add_settings_field('styles_map_zoom', __('Zoom do mapa', 'apgmappins'), array($this, 'input_styles_map_zoom'), 'apgmappins-settings-styles', 'apgmappins_settings_styles');
        add_settings_field('styles_water_color', __('Cor da água', 'apgmappins'), array($this, 'input_styles_water_color'), 'apgmappins-settings-styles', 'apgmappins_settings_styles');
        add_settings_field('styles_marker_fill_color', __('Cor geral maracador', 'apgmappins'), array($this, 'input_styles_marker_fill_color'), 'apgmappins-settings-styles', 'apgmappins_settings_styles');
        add_settings_field('styles_marker_stroke_color', __('Cor da borda marcador', 'apgmappins'), array($this, 'input_styles_marker_stroke_color'), 'apgmappins-settings-styles', 'apgmappins_settings_styles');

        add_settings_field('styles_landscape_color', __('Cor da paisagem', 'apgmappins'), array($this, 'input_styles_landscape_color'), 'apgmappins-settings-styles', 'apgmappins_settings_styles');
        add_settings_field('styles_road_color', __('Cor da estrada', 'apgmappins'), array($this, 'input_styles_road_color'), 'apgmappins-settings-styles', 'apgmappins_settings_styles');
        add_settings_field('styles_road_labels_text_color', __('Cor textos da estrada', 'apgmappins'), array($this, 'input_styles_road_labels_text_color'), 'apgmappins-settings-styles', 'apgmappins_settings_styles');
        add_settings_field('styles_administrative_color', __('Cor áreas administrativas', 'apgmappins'), array($this, 'input_styles_administrative_color'), 'apgmappins-settings-styles', 'apgmappins_settings_styles');
        add_settings_field('styles_administrative_labels_text_color', __('Cor texto áreas administrativas', 'apgmappins'), array($this, 'input_styles_administrative_labels_text_color'), 'apgmappins-settings-styles', 'apgmappins_settings_styles');

        // Shortcode
        register_setting('apgmappins_shortcode', 'apgmappins_shortcode', array($this, 'shortcode_sanitize'));

        add_settings_section('apgmappins_settings_shortcode', __('Shortcode', 'apgmappins'),  array($this, 'print_section_info'),  'apgmappins-settings-shortcode');
        add_settings_field('shortcode', __('Código', 'apgmappins'), array($this, 'input_shortcode'), 'apgmappins-settings-shortcode', 'apgmappins_settings_shortcode');
    }

    public function input_authentication_api_key()
    {
        printf('<input class="regular-text" type="text" name="apgmappins_geral[authentication_api_key]" value="%s">', get_option_apgmappins('apgmappins_geral', 'authentication_api_key'));
    }

    public function input_map_title()
    {
        printf('<input class="regular-text" type="text" name="apgmappins_geral[map_title]" value="%s">', get_option_apgmappins('apgmappins_geral', 'map_title', null, __('Locais e representantes', 'apgmappins')));
    }

    public function input_styles_map_zoom()
    {
        printf('<input class="regular-text" type="number" name="apgmappins_styles[styles_map_zoom]" value="%s">', get_option_apgmappins('apgmappins_styles', 'styles_map_zoom', null, 10));
    }

    public function input_styles_water_color()
    {
        $color = '#9474ff';
        printf('<input class="color-picker" data-alpha-enabled="true" type="text" name="apgmappins_styles[styles_water_color]" value="%s">', get_option_apgmappins('apgmappins_styles', 'styles_water_color', null, $color));
    }

    public function input_styles_marker_fill_color()
    {
        $color = '#522aab';
        printf('<input class="color-picker" data-alpha-enabled="true" type="text" name="apgmappins_styles[styles_marker_fill_color]" value="%s">', get_option_apgmappins('apgmappins_styles', 'styles_marker_fill_color', null, $color));
    }

    public function input_styles_marker_stroke_color()
    {
        $color = '#FFFFFF';
        printf('<input class="color-picker" data-alpha-enabled="true" type="text" name="apgmappins_styles[styles_marker_stroke_color]" value="%s">', get_option_apgmappins('apgmappins_styles', 'styles_marker_stroke_color', null, $color));
    }

    public function input_styles_landscape_color()
    {
        $color = '#F5F5F5';
        printf('<input class="color-picker" data-alpha-enabled="true" type="text" name="apgmappins_styles[styles_landscape_colo]" value="%s">', get_option_apgmappins('apgmappins_styles', 'styles_landscape_colo', null, $color));
    }

    public function input_styles_road_color()
    {
        $color = '#FFFFFF';
        printf('<input class="color-picker" data-alpha-enabled="true" type="text" name="apgmappins_styles[styles_road_color]" value="%s">', get_option_apgmappins('apgmappins_styles', 'styles_road_color', null, $color));
    }

    public function input_styles_road_labels_text_color()
    {
        $color = '#4B0082';
        printf('<input class="color-picker" data-alpha-enabled="true" type="text" name="apgmappins_styles[styles_road_labels_text_color]" value="%s">', get_option_apgmappins('apgmappins_styles', 'styles_road_labels_text_color', null, $color));
    }

    public function input_styles_administrative_color()
    {
        $color = '#9474ff';
        printf('<input class="color-picker" data-alpha-enabled="true" type="text" name="apgmappins_styles[styles_administrative_color]" value="%s">', get_option_apgmappins('apgmappins_styles', 'styles_administrative_color', null, $color));
    }

    public function input_styles_administrative_labels_text_color()
    {
        $color = '#9474ff';
        printf('<input class="color-picker" data-alpha-enabled="true" type="text" name="apgmappins_styles[styles_administrative_labels_text_color]" value="%s">', get_option_apgmappins('apgmappins_styles', 'styles_administrative_labels_text_color', null, $color));
    }

    public function input_shortcode()
    {
        echo '<code>[apg_map_pins zoom="10"]</code>';
    }

    public function geral_sanitize($input)
    {
        $inputs = array();

        if (isset($input['authentication_api_key']))
            $inputs['authentication_api_key'] = sanitize_text_field($input['authentication_api_key']);

        if (isset($input['map_title']))
            $inputs['map_title'] = sanitize_text_field($input['map_title']);

        return $inputs;
    }

    public function styles_sanitize($input)
    {
        $inputs = array();

        if (isset($input['styles_map_zoom']))
            $inputs['styles_map_zoom'] = sanitize_text_field($input['styles_map_zoom']);

        if (isset($input['styles_water_color']))
            $inputs['styles_water_color'] = sanitize_text_field($input['styles_water_color']);

        if (isset($input['styles_marker_fill_color']))
            $inputs['styles_marker_fill_color'] = sanitize_text_field($input['styles_marker_fill_color']);

        if (isset($input['styles_marker_stroke_color']))
            $inputs['styles_marker_stroke_color'] = sanitize_text_field($input['styles_marker_stroke_color']);

        if (isset($input['styles_landscape_color']))
            $inputs['styles_landscape_color'] = sanitize_text_field($input['styles_landscape_color']);

        if (isset($input['styles_road_color']))
            $inputs['styles_road_color'] = sanitize_text_field($input['styles_road_color']);

        if (isset($input['styles_road_labels_text_color']))
            $inputs['styles_road_labels_text_color'] = sanitize_text_field($input['styles_road_labels_text_color']);

        if (isset($input['styles_administrative_color']))
            $inputs['styles_administrative_color'] = sanitize_text_field($input['styles_administrative_color']);

        if (isset($input['styles_administrative_labels_text_color']))
            $inputs['styles_administrative_labels_text_color'] = sanitize_text_field($input['styles_administrative_labels_text_color']);

        return $inputs;
    }

    public function shortcode_sanitize($input)
    {
        $inputs = array();
        return $inputs;
    }

    public function print_section_info($args)
    {
        if (esc_html($args['id']) == 'apgmappins_settings_geral') {
            printf('<p class="description">%s</p>', __('Configurações para a autenticação.', 'apgmappins'));
        }

        if (esc_html($args['id']) == 'apgmappins_settings_styles') {
            printf('<p class="description">%s</p>',  __('Configurações para os estilos.', 'apgmappins'));
        }

        if (esc_html($args['id']) == 'apgmappins_settings_shortcode') {
            printf('<p class="description">%s</p>',  __('Copie o código e adicione na seção quer mostrar o mapa.', 'apgmappins'));
        }
    }
}

new Admin_Settings_Apg_Map_Pins();
