<?php

if (!defined('ABSPATH')) {
    exit;
}

class Taxonomies_Apg_Map_Pins
{
    public function __construct()
    {
        add_action('init', [$this, 'rewrite_flush']);
        add_filter('taxonomy_parent_dropdown_args', [$this, 'limit_parent_dropdown_for_territories'], 10, 3);
    }

    public function rewrite_flush()
    {
        $this->register_taxonomies();
        flush_rewrite_rules();
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
            'parent_item'       => "$singular " . __('pai', 'apgmappins'),
            'parent_item_colon' => "$singular " . __('pai:', 'apgmappins'),
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
     * Mostra apenas termos de nível 0 no dropdown "Pai" da tela de taxonomia 'territories'
     */
    public function limit_parent_dropdown_for_territories($args, $taxonomy, $context)
    {
        if ($taxonomy !== 'territories') {
            return $args;
        }

        // limita o dropdown a termos sem pai
        $args['parent'] = 0;
        $args['depth']  = 1;

        return $args;
    }
}

new Taxonomies_Apg_Map_Pins();
