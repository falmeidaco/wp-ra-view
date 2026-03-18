<?php

/* post type for Portfilio */
function objetora_post_type() {
  $labels = array(
    'name'               => _x( 'ObjetoRAs', 'post type general name', 'textdomain' ),
    'singular_name'      => _x( 'ObjetoRA', 'post type singular name', 'textdomain' ),
    'menu_name'          => _x( 'ObjetoRAs', 'admin menu', 'textdomain' ),
    'name_admin_bar'     => _x( 'ObjetoRA', 'add new on admin bar', 'textdomain' ),
    'add_new'            => __( 'Novo', 'textdomain' ), 
    'add_new_item'       => __( 'Novo ObjetoRA', 'textdomain' ),
    'new_item'           => __( 'Novo ObjetoRA', 'textdomain' ),
    'edit_item'          => __( 'Editar ObjetoRA', 'textdomain' ),
    'view_item'          => __( 'Ver ObjetoRA', 'textdomain' ),
    'all_items'          => __( 'Todos ObjetoRAs', 'textdomain' ),
    'search_items'       => __( 'Procurar ObjetoRAs', 'textdomain' ),
    'parent_item_colon'  => __( 'ObjetoRAs Pai:', 'textdomain' ),
    'not_found'          => __( 'Nenhum ObjetoRA encontrado.', 'textdomain' ),
    'not_found_in_trash' => __( 'Nenhum ObjetoRA encontrado no lixo.', 'textdomain' ),
  );

  $args = array(
    'labels'             => $labels,
    'public'             => true,
    'publicly_queryable' => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'query_var'          => true,
    'rewrite'            => array( 'slug' => 'objetora' ),
    'capability_type'    => 'post',
    'has_archive'        => true,
    'hierarchical'       => false,
    'menu_position'      => null,
    'show_in_rest'       => false,
    'supports'           => array( 'title', 'thumbnail'),
  );

  register_post_type( 'objetora', $args );
}

add_action( 'init', 'objetora_post_type' );