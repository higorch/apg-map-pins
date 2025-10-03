<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Setup_APG_Map_Pins_Admin_Settings
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'menu_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'general_admin_notice'));

        add_shortcode('apg_map_pins', array($this, 'apgmappins_shortcode'));
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
        if ('toplevel_page_apgmappins' != $page) return;

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('apgmappins-admin', APG_MAP_PINS_DIR_URL . 'assets/css/admin.css', null, '1.0.0');

        wp_enqueue_script('wp-color-picker-alpha', APG_MAP_PINS_DIR_URL . 'assets/plugins/wp-color-picker-alpha.min.js', array('wp-color-picker'), '1.0.0', true);
        wp_enqueue_script('apgmappins-admin', APG_MAP_PINS_DIR_URL . 'assets/js/admin.js', array('jquery'), '1.0.0', true);
    }

    public function register_settings()
    {
        // Geral
        register_setting('apgmappins_geral', 'apgmappins_geral',  array($this, 'geral_sanitize'));

        add_settings_section('apgmappins_settings_geral',  __('Autenticação', 'apgmappins'),  array($this, 'print_section_info'),  'apgmappins-settings-geral');
        add_settings_field('authentication_api_key', __('API Key (Google Maps)', 'apgmappins'), array($this, 'input_authentication_api_key'), 'apgmappins-settings-geral', 'apgmappins_settings_geral');

        // Styles
        register_setting('apgmappins_styles', 'apgmappins_styles', array($this, 'styles_sanitize'));

        add_settings_section('apgmappins_settings_styles', __('Estilos', 'apgmappins'),  array($this, 'print_section_info'),  'apgmappins-settings-styles');
        add_settings_field('styles_map_zoom', __('Zoom do mapa', 'apgmappins'), array($this, 'input_styles_map_zoom'), 'apgmappins-settings-styles', 'apgmappins_settings_styles');

        // Shortcode
        register_setting('apgmappins_shortcode', 'apgmappins_shortcode', array($this, 'shortcode_sanitize'));

        add_settings_section('apgmappins_settings_shortcode', __('Shortcode', 'apgmappins'),  array($this, 'print_section_info'),  'apgmappins-settings-shortcode');
        add_settings_field('shortcode', __('Código', 'apgmappins'), array($this, 'input_shortcode'), 'apgmappins-settings-shortcode', 'apgmappins_settings_shortcode');
    }

    public function input_authentication_api_key()
    {
        printf('<input class="regular-text" type="text" name="apgmappins_geral[authentication_api_key]" value="%s">', get_option_apgmappins('apgmappins_geral', 'authentication_api_key'));
    }

    public function input_styles_map_zoom()
    {
        printf('<input class="regular-text" type="number" name="apgmappins_styles[styles_map_zoom]" value="%s">', get_option_apgmappins('apgmappins_styles', 'styles_map_zoom', null, 10));
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

        return $inputs;
    }

    public function styles_sanitize($input)
    {
        $inputs = array();

        if (isset($input['styles_map_zoom']))
            $inputs['styles_map_zoom'] = sanitize_text_field($input['styles_map_zoom']);

        return $inputs;
    }

    public function shortcode_sanitize($input)
    {
        $inputs = array();
        return $inputs;
    }

    public function apgmappins_shortcode($atts, $content = null)
    {
        $a = shortcode_atts(array(
            'zoom' => '',
        ), $atts);

        ob_start();

        echo "<div class='apgmappins-shortcode' id='apgmappins-map' data-zoom='{$a['zoom']}'></div>";
        return ob_get_clean();
    }

    public function general_admin_notice()
    {
        $screen = get_current_screen();

        if ($screen->id != 'toplevel_page_apgmappins') return;

        if (isset($_GET['settings-updated'])) {
            if ($_GET['settings-updated'] === 'true') {
                printf('<div class="%1$s"><p>%2$s</p></div>', 'notice notice-success is-dismissible', __('Configurações salvas com sucesso', 'apgmappins'));
            } else {
                printf('<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error is-dismissible', __('Não foi possível salvar as configurações', 'apgmappins'));
            }
        }
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

new Setup_APG_Map_Pins_Admin_Settings();
