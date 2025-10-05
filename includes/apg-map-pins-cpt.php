<?php

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Cpt_Apg_Map_Pins
{
    public function __construct()
    {
        add_action('init', array($this, 'rewrite_flush'));
    }

    public function rewrite_flush()
    {
        $this->register_cpts();

        flush_rewrite_rules();
    }

    public function register_cpts()
    {
        $this->pt_apg_map_pins();
    }

    public function pt_apg_map_pins()
    {
        $labels = array(
            'name'                  => "Locais no mapa",
            'singular_name'         => "Local no mapa",
            'menu_name'             => "Locais no mapa",
            'name_admin_bar'        => "Locais no mapa",
            'add_new'               => "Adicionar novo",
            'add_new_item'          => "Adicionar local",
            'new_item'              => "Novo local",
            'edit_item'             => "Editar local",
            'view_item'             => "Ver local",
            'all_items'             => "Todos locais",
            'search_items'          => "Pesquisar local",
            'not_found'             => "Nenhum local no mapa encontrado.",
            'not_found_in_trash'    => "Nenhum local no mapa encontrado na lixeira.",
            'featured_image'        => "Imagem da capa do local",
            'set_featured_image'    => "Definir imagem de capa",
            'remove_featured_image' => "Remover imagem de capa",
            'use_featured_image'    => "Usar como imagem de capa",
            'archives'              => "Arquivos de locais no mapa",
            'insert_into_item'      => "Inserir local",
            'uploaded_to_this_item' => "Carregado para este local no mapa",
            'filter_items_list'     => "Filtrar lista de locais",
            'items_list_navigation' => "Navegação na lista de locais",
            'items_list'            => "Lista de locais",
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'apg-map-pins'),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title'),
            'menu_icon'          => 'dashicons-location-alt',
        );

        register_post_type('apg-map-pins', $args);
    }
}

new Cpt_Apg_Map_Pins();
