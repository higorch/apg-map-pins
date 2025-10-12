<?php

if (!defined('ABSPATH')) {
    exit;
}

class Taxonomies_Apg_Map_Pins
{
    public function __construct()
    {
        add_action('init', [$this, 'register_taxonomies']);

        // Substitui o dropdown "ascendente" apenas para 'territories'
        add_filter('wp_dropdown_cats', [$this, 'filter_territory_parent_dropdown'], 10, 2);

        // JS: evita que AJAX adicione filhos no select de parent
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_js']);
    }

    public function register_taxonomies()
    {
        $this->register_taxonomy('territories', __('Território', 'apgmappins'), __('Territórios', 'apgmappins'));
    }

    private function register_taxonomy($taxonomy, $singular, $plural)
    {
        $labels = [
            'name'              => $plural,
            'singular_name'     => $singular,
            'search_items'      => __('Buscar', 'apgmappins') . " $plural",
            'all_items'         => __('Todos os', 'apgmappins') . " $plural",
            'parent_item'       => "$singular " . __('ascendente', 'apgmappins'),
            'parent_item_colon' => "$singular " . __('ascendente:', 'apgmappins'),
            'edit_item'         => __('Editar', 'apgmappins') . " $singular",
            'update_item'       => __('Atualizar', 'apgmappins') . " $singular",
            'add_new_item'      => __('Adicionar novo', 'apgmappins') . " $singular",
            'new_item_name'     => __('Nome do novo', 'apgmappins') . " $singular",
            'menu_name'         => $plural,
        ];

        $args = [
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => false,
            'query_var'         => true,
            'rewrite'           => ['slug' => $taxonomy],
            'meta_box_cb'       => false,
        ];

        register_taxonomy($taxonomy, ['apg-map-pins'], $args);
    }

    /**
     * Substitui o HTML do dropdown "ascendente" para listar apenas termos raiz
     */
    public function filter_territory_parent_dropdown($output, $args)
    {
        if (empty($args['taxonomy']) || !in_array($args['taxonomy'], ['territories'])) {
            return $output;
        }

        $output = preg_replace('/<option[^>]*class="level-[1-9][^"]*"[^>]*>.*?<\/option>/s', '', $output);

        return $output;
    }

    /**
     * Adiciona JS no admin para evitar que AJAX adicione filhos no select
     */
    public function enqueue_admin_js($hook)
    {
        $taxonomy = $_GET['taxonomy'] ?? '';

        if ($hook !== 'edit-tags.php' || !in_array($taxonomy, ['territories'])) {
            return;
        }

        wp_add_inline_script('jquery-core', "
            jQuery(document).ready(function($){
                $(document).ajaxSuccess(function(event, xhr, settings) {
                    if (settings.data && settings.data.includes('action=add-tag') && settings.data.includes('taxonomy=territories')) {
                        var select = $('select#parent');
                        select.find('option').each(function(){
                            if (!$(this).hasClass('level-0') && $(this).val() != '-1') {
                                $(this).remove();
                            }
                        });
                    }
                });
            });
        ");
    }
}

new Taxonomies_Apg_Map_Pins();
