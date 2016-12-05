<?php
	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

  @ini_set( 'upload_max_size' , '64M' );
@ini_set( 'post_max_size', '64M');
@ini_set( 'max_execution_time', '300' );

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
      'position'       => 4.4,
      'icon_url'       => 'dashicons-media-text'
  );
  acf_add_options_page($args );
}

/**
  * Register a post type, with REST API support
  *
  * Based on example at: http://codex.wordpress.org/Function_Reference/register_post_type
  */
add_action( 'init', 'faq' );
function faq() {
  $labels = array(
      'name'               => _x( 'FAQ', 'post type general name' ),
      'singular_name'      => _x( 'FAQ', 'post type singular name' ),
      'menu_name'          => _x( 'FAQ', 'admin menu' ),
      'name_admin_bar'     => _x( 'FAQ', 'add new on admin bar' ),
      'add_new'            => _x( 'Adicionar Novo', 'item' ),
      'add_new_item'       => __( 'Adicionar Novo FAQ' ),
      'new_item'           => __( 'Novo FAQ' ),
      'update_item'        => __( 'Salvar' ),
      'edit_item'          => __( 'Editar FAQ' ),
      'view_item'          => __( 'Ver FAQ' ),
      'all_items'          => __( 'Todos FAQ' ),
      'search_items'       => __( 'Procurar FAQ' ),
      'parent_item_colon'  => __( 'Parent Itens:' ),
      'not_found'          => __( 'FAQ não encontrado.' ),
      'not_found_in_trash' => __( 'FAQ não encontrado.' )
  );

  $args = array(
      'labels'                => $labels,
      'public'                => true,
      'publicly_queryable'    => true,
      'show_ui'               => true,
      'show_in_rest'          => true,
      'show_in_menu'          => true,
      'query_var'             => true,
      'rewrite'               => array( 'slug' => 'faq' ),
      'capability_type'       => 'post',
      'has_archive'           => true,
      'menu_icon'             => 'dashicons-clipboard',
      'hierarchical'          => false,
      'menu_position'         => 5,
      'rest_base'             => 'faq',
      'rest_controller_class' => 'WP_REST_Posts_Controller',
      'supports'              => array( 'title')
  );

  register_post_type( 'faq', $args );
}

// Changing the Menu Order
function custom_menu_order($menu_ord) {
    if (!$menu_ord) return true;
     
    return array(
        'index.php', // Dashboard
        'edit.php?post_type=product', //produtos
        'edit.php?post_type=wc_booking', //booking
        'woocommerce', //woocommerce
        'separator1', //  separator
        'edit.php?post_type=faq', // FAQ
        'contato', // contato
        'edit.php?post_type=page', // Pages
        'separator2', //  separator
        'users.php', //usuarios
        'link-manager.php', // Links
        'themes.php', // Appearance
        'upload.php', // Media
        'plugins.php', // Plugins
        'users.php', // Users
        'tools.php', // Tools
        'options-general.php', // Settings
        'wpcf7', //contact
        'separator-last', // Last separator
        'edit.php?post_type=acf-field-group', //ACF
        'edit.php', // Posts
        'edit-comments.php', // Comments
    );
}
add_filter('custom_menu_order', 'custom_menu_order'); // Activate custom_menu_order
add_filter('menu_order', 'custom_menu_order');


// Renaming Menus and Remove
function edit_admin_menus() {
    global $menu;
    global $submenu;
     
    $menu[27][0] = 'Reservas'; // Change booking to Reservas
    $menu[27][6] ='dashicons-calendar-alt';
    $submenu['edit.php?post_type=wc_booking'][5][0] = 'Todas as Reservas';

    $menu[26][0] = 'Jogos'; // Change produtos to jogos
    $submenu['edit.php?post_type=product'][5][0] = 'Jogos';
    $submenu['edit.php?post_type=product'][10][0] = 'Adicionar Jogo';


    remove_submenu_page('edit.php?post_type=wc_booking','edit.php?post_type=bookable_resource');//remove resource from booking
    remove_submenu_page('edit.php?post_type=product','edit-tags.php?taxonomy=product_cat&amp;post_type=product');//remove categoria
    remove_submenu_page('edit.php?post_type=product','edit-tags.php?taxonomy=product_tag&amp;post_type=product');//remove tag
    remove_submenu_page('edit.php?post_type=product','product_attributes');//remove atributo

    // var_dump($submenu['edit.php?post_type=product']);exit;
}
add_action( 'admin_menu', 'edit_admin_menus' );

//Set booing for default product type
function mw_custom_product_type_change( $product_types ) {
  $new_array = array( 'booking' => $product_types['booking'] );
  $product_types = $new_array;
  return $product_types;
}
add_filter( 'product_type_selector', 'mw_custom_product_type_change', 20 );

//Marca Virtual como Default
function cs_wc_product_type_options( $product_type_options ) {
    $product_type_options['virtual']['default'] = 'yes';
    return $product_type_options;
}
add_filter( 'product_type_options', 'cs_wc_product_type_options' );
?>