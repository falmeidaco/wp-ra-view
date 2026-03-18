<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/* Setup Carbon Fields */

function raview_crb_load()
{
  require_once  get_template_directory() . '/vendor/autoload.php';
  \Carbon_Fields\Carbon_Fields::boot();
}
add_action('after_setup_theme', 'raview_crb_load');

/* Setup Campos Customizados */
function raview_crb_fields_setup()
{

  Container::make('post_meta', 'Configurações do Arquivo GLB')
    ->where('post_type', '=', 'objetora')
    ->add_fields([
      Field::make('file', 'glb_file', __('Arquivo GLB')),
    ]);
}

add_action('carbon_fields_register_fields', 'raview_crb_fields_setup');
