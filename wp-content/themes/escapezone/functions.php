<?php
	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

/**
	* Enable support for navgation
	*/
function register_my_menu() {
  register_nav_menu('header-menu',__( 'Header Menu' ));
}
add_action( 'init', 'register_my_menu' );

/**
	* Enable support for site logo
	*/
add_theme_support( 'custom-logo', array(
	'height'      => 51,
	'width'       => 320,
	'flex-width'  => true,
) );

// ADD scripts ao tema
function my_scripts()
{
  wp_deregister_script('jquery');

  // Styles
   wp_enqueue_style(
    'normalize',
    'https://cdn.jsdelivr.net/normalize/3.0.3/normalize.min.css'
   );
   wp_enqueue_style(
    'bootstrap',
    'https://cdn.jsdelivr.net/bootstrap/3.3.7/css/bootstrap.min.css',
    'normalize'
   );
  wp_enqueue_style(
    'Style',
    get_stylesheet_uri(),
    array( 'normalize','bootstrap')
   );
  // JS
  wp_enqueue_script(
    'jquery',
    'https://cdn.jsdelivr.net/jquery/3.1.0/jquery.min.js'
  );
  wp_enqueue_script(
    'bootstrapjs',
    'https://cdn.jsdelivr.net/bootstrap/3.3.7/js/bootstrap.min.js',
    'jquery'
  );
  wp_enqueue_script(
    'modernizr',
    'https://cdn.jsdelivr.net/modernizr/2.8.3/modernizr.min.js',
    'jquery'
  );
  wp_register_script(
    'angularjs',
    'https://cdn.jsdelivr.net/angularjs/1.5.8/angular.min.js'
  );
  // wp_register_script(
  //   'angularjs-route',
  //   'https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.5.5/angular-route.min.js'
  // );
  // wp_register_script(
  //   'angularjs-animate',
  //   'https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.5.5/angular-animate.min.js'
  // );
  wp_enqueue_script(
    'global_script',
    get_stylesheet_directory_uri() . '/js/escape.js',
    array( 'angularjs')
  );
  wp_localize_script(
    'global_script',
    'myLocalized',
    array(
      'tema' =>trailingslashit( get_template_directory_uri() )
    )
  );
}
add_action( 'wp_enqueue_scripts', 'my_scripts' );

//Adiciona um novo menu, em conjunto com o plugin advanced-custom-fields
if(function_exists('acf_add_options_page')){
  $args = array(
      'page_title'     => 'Contato',
      'menu_title'     => 'Contato',
      'menu_slug'      => 'contato',
      'capability'     => 'edit_posts',
      'parent_slug'    => '',
      'position'       => false,
      'icon_url'       => 'dashicons-media-text'
  );
  acf_add_options_page($args );
}
?>