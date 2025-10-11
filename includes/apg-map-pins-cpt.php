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
            'name'                  => __("Locais no mapa", 'apgmappins'),
            'singular_name'         => __("Local no mapa", 'apgmappins'),
            'menu_name'             => __("Locais no mapa", 'apgmappins'),
            'name_admin_bar'        => __("Locais no mapa", 'apgmappins'),
            'add_new'               => __("Adicionar novo", 'apgmappins'),
            'add_new_item'          => __("Adicionar local", 'apgmappins'),
            'new_item'              => __("Novo local", 'apgmappins'),
            'edit_item'             => __("Editar local", 'apgmappins'),
            'view_item'             => __("Ver local", 'apgmappins'),
            'all_items'             => __("Todos locais", 'apgmappins'),
            'search_items'          => __("Pesquisar local", 'apgmappins'),
            'not_found'             => __("Nenhum local no mapa encontrado.", 'apgmappins'),
            'not_found_in_trash'    => __("Nenhum local no mapa encontrado na lixeira.", 'apgmappins'),
            'featured_image'        => __("Imagem da capa do local", 'apgmappins'),
            'set_featured_image'    => __("Definir imagem de capa", 'apgmappins'),
            'remove_featured_image' => __("Remover imagem de capa", 'apgmappins'),
            'use_featured_image'    => __("Usar como imagem de capa", 'apgmappins'),
            'archives'              => __("Arquivos de locais no mapa", 'apgmappins'),
            'insert_into_item'      => __("Inserir local", 'apgmappins'),
            'uploaded_to_this_item' => __("Carregado para este local no mapa", 'apgmappins'),
            'filter_items_list'     => __("Filtrar lista de locais", 'apgmappins'),
            'items_list_navigation' => __("Navegação na lista de locais", 'apgmappins'),
            'items_list'            => __("Lista de locais", 'apgmappins'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
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
