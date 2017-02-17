<?php
namespace UCFW\Helpers\Constants;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Plugin configuration constants
define( __NAMESPACE__ . '\TOKEN' ,             'ucfw' );
define( __NAMESPACE__ . '\INSTALLED_VERSION' , 'ucfw_installed_version' );
define( __NAMESPACE__ . '\VERSION' ,           '1.0.0' );
define( __NAMESPACE__ . '\TEXT_DOMAIN' ,       'url-coupons-for-woocommerce' );


// Path constants
define( __NAMESPACE__ . '\MAIN_PLUGIN_FILE_PATH' , WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'url-coupons-for-woocommerce' . DIRECTORY_SEPARATOR . 'url-coupons-for-woocommerce.php' );
define( __NAMESPACE__ . '\PLUGIN_DIR_PATH' ,       plugin_dir_path( namespace\MAIN_PLUGIN_FILE_PATH ) );
define( __NAMESPACE__ . '\PLUGIN_DIR_URL' ,        plugin_dir_url( namespace\MAIN_PLUGIN_FILE_PATH ) );
define( __NAMESPACE__ . '\PLUGIN_BASENAME' ,       plugin_basename( dirname( namespace\MAIN_PLUGIN_FILE_PATH ) ) );

define( __NAMESPACE__ . '\CSS_ROOT_URL' ,    namespace\PLUGIN_DIR_URL . 'css/' );
define( __NAMESPACE__ . '\IMAGES_ROOT_URL' , namespace\PLUGIN_DIR_URL . 'images/' );
define( __NAMESPACE__ . '\JS_ROOT_URL' ,     namespace\PLUGIN_DIR_URL . 'js/' );

define( __NAMESPACE__ . '\VIEWS_ROOT_PATH' , namespace\PLUGIN_DIR_PATH . 'views/' );

define( __NAMESPACE__ . '\TEMPLATES_ROOT_PATH' , namespace\PLUGIN_DIR_PATH . 'templates/' );
define( __NAMESPACE__ . '\THEME_TEMPLATE_PATH' , 'url-coupons-for-woocommerce' );


// Coupon Meta Constants

define( __NAMESPACE__ . '\COUPON_USER_ROLES_RESTRICTION' , 'ucfw_coupon_user_roles_restriction' );
define( __NAMESPACE__ . '\COUPON_URL' , 'ucfw_coupon_url' );


// Settings Constants

// General Section
define( __NAMESPACE__ . '\COUPON_ENDPOINT' ,                     'ucfw_coupon_endpoint' );
define( __NAMESPACE__ . '\AFTER_APPLY_COUPON_REDIRECT_URL' ,     'ucfw_after_apply_coupon_redirect_url' );
define( __NAMESPACE__ . '\INVALID_COUPON_REDIRECT_URL' ,         'ucfw_invalid_coupon_redirect_url' );
define( __NAMESPACE__ . '\HIDE_COUPON_UI_ON_CART_AND_CHECKOUT' , 'ucfw_hide_coupon_ui_on_cart_and_checkout' );

// Help Section
define( __NAMESPACE__ . '\CLEAN_UP_PLUGIN_OPTIONS' , 'ucfw_clean_up_plugin_options' );