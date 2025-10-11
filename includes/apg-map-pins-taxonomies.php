<?php

if (!defined('ABSPATH')) {
    exit;
}

class Taxonomies_Apg_Map_Pins
{
    public function __construct()
    {
        add_action('init', array($this, 'rewrite_flush'));
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
        $labels = array(
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
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => false,
            'query_var'         => true,
            'rewrite'           => array('slug' => $taxonomy),
            'meta_box_cb'       => false
        );

        register_taxonomy($taxonomy, array('apg-map-pins'), $args);
    }
}

new Taxonomies_Apg_Map_Pins();
