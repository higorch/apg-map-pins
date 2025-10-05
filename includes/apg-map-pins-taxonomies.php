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
        $this->register_taxonomy('pais', 'País', 'Países');
        $this->register_taxonomy('estado', 'Estado', 'Estados');
        $this->register_taxonomy('cidade', 'Cidade', 'Cidades');
    }

    private function register_taxonomy($taxonomy, $singular, $plural)
    {
        $labels = array(
            'name'              => $plural,
            'singular_name'     => $singular,
            'search_items'      => "Buscar $plural",
            'all_items'         => "Todos os $plural",
            'parent_item'       => "$singular pai",
            'parent_item_colon' => "$singular pai:",
            'edit_item'         => "Editar $singular",
            'update_item'       => "Atualizar $singular",
            'add_new_item'      => "Adicionar novo $singular",
            'new_item_name'     => "Nome do novo $singular",
            'menu_name'         => $plural,
        );

        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => false,
            'query_var'         => true,
            'rewrite'           => array('slug' => $taxonomy),
        );

        register_taxonomy($taxonomy, array('apg-map-pins'), $args);
    }
}

new Taxonomies_Apg_Map_Pins();
