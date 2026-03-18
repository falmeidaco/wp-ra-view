<?php

include_once 'inc/carbon-fields.php';
include_once 'inc/post-type.php';

function carregar_estilos_tema() {
    wp_enqueue_style(
        'tema-style',
        get_stylesheet_uri(),
        [],
        filemtime(get_stylesheet_directory() . '/style.css')
    );
    wp_enqueue_style(
        'tema-tailwind',
        get_template_directory_uri() . '/assets/css/app.css',
        [],
        filemtime(get_stylesheet_directory() . '/assets/css/app.css')
    );
}

add_action('wp_enqueue_scripts', 'carregar_estilos_tema');

add_theme_support('post-thumbnails');

function permitir_upload_glb($mimes) {
    $mimes['glb'] = 'model/gltf-binary';
    return $mimes;
}
add_filter('upload_mimes', 'permitir_upload_glb');

function permitir_glb_real_mime($data, $file, $filename, $mimes){
    $ext = pathinfo($filename, PATHINFO_EXTENSION);

    if ($ext === 'glb') {
        $data['ext'] = 'glb';
        $data['type'] = 'model/gltf-binary';
    }

    return $data;
}
add_filter('wp_check_filetype_and_ext', 'permitir_glb_real_mime', 10, 4);