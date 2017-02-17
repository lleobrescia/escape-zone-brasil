<?php
namespace UCFW\Helpers;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Model that houses all the plugin constants.
 * Note as much as possible, we need to make this class succinct as the only purpose of this is to house all the constants that is utilized by the plugin.
 * Therefore we omit class member comments and minimize comments as much as possible.
 * In fact the only verbouse comment here is this comment you are reading right now.
 * And guess what, it just got worse coz now this comment takes 5 lines instead of 3.
 *
 * @since 1.0.0
 */
class Plugin_Constants {

    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
    */

    private static $_instance;

    // Plugin configuration constants
    const TOKEN               = 'ucfw';
    const INSTALLED_VERSION   = 'ucfw_installed_version';
    const VERSION             = '1.0.1';
    const TEXT_DOMAIN         = 'url-coupons-for-woocommerce';
    const THEME_TEMPLATE_PATH = 'url-coupons-for-woocommerce';

    // Coupon Meta Constants

    const COUPON_USER_ROLES_RESTRICTION = 'ucfw_coupon_user_roles_restriction';
    const DISABLE_COUPON_URL            = 'ucfw_disable_coupon_url';
    const COUPON_URL                    = 'ucfw_coupon_url';
    const COUPON_CODE_URL_OVERRIDE      = 'ucfw_coupon_code_url_override';

    // Settings Constants

    // General Section
    const COUPON_ENDPOINT                     = 'ucfw_coupon_endpoint';
    const AFTER_APPLY_COUPON_REDIRECT_URL     = 'ucfw_after_apply_coupon_redirect_url';
    const INVALID_COUPON_REDIRECT_URL         = 'ucfw_invalid_coupon_redirect_url';
    const HIDE_COUPON_UI_ON_CART_AND_CHECKOUT = 'ucfw_hide_coupon_ui_on_cart_and_checkout';

    // Help Section
    const CLEAN_UP_PLUGIN_OPTIONS = 'ucfw_clean_up_plugin_options';



    
    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
    */

    public function __construct() {

        // Path constants
        $this->_MAIN_PLUGIN_FILE_PATH = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'url-coupons-for-woocommerce' . DIRECTORY_SEPARATOR . 'url-coupons-for-woocommerce.php';
        $this->_PLUGIN_DIR_PATH       = plugin_dir_path( $this->_MAIN_PLUGIN_FILE_PATH );
        $this->_PLUGIN_DIR_URL        = plugin_dir_url( $this->_MAIN_PLUGIN_FILE_PATH );
        $this->_PLUGIN_BASENAME       = plugin_basename( dirname( $this->_MAIN_PLUGIN_FILE_PATH ) );

        $this->_CSS_ROOT_URL          = $this->_PLUGIN_DIR_URL . 'css/';
        $this->_IMAGES_ROOT_URL       = $this->_PLUGIN_DIR_URL . 'images/';
        $this->_JS_ROOT_URL           = $this->_PLUGIN_DIR_URL . 'js/';

        $this->_VIEWS_ROOT_PATH       = $this->_PLUGIN_DIR_PATH . 'views/';
        $this->_TEMPLATES_ROOT_PATH   = $this->_PLUGIN_DIR_PATH . 'templates/';
        $this->_LOGS_ROOT_PATH        = $this->_PLUGIN_DIR_PATH . 'logs/';

    }

    public static function get_instance() {

        if ( !self::$_instance instanceof self )
            self::$_instance = new self();
        
        return self::$_instance;

    }

    public function MAIN_PLUGIN_FILE_PATH() {
        return $this->_MAIN_PLUGIN_FILE_PATH;
    }

    public function PLUGIN_DIR_PATH() {
        return $this->_PLUGIN_DIR_PATH;
    }

    public function PLUGIN_DIR_URL() {
        return $this->_PLUGIN_DIR_URL;
    }

    public function PLUGIN_BASENAME() {
        return $this->_PLUGIN_BASENAME;
    }    

    public function CSS_ROOT_URL() {
        return $this->_CSS_ROOT_URL;
    }

    public function IMAGES_ROOT_URL() {
        return $this->_IMAGES_ROOT_URL;
    }

    public function JS_ROOT_URL() {
        return $this->_JS_ROOT_URL;
    }

    public function VIEWS_ROOT_PATH() {
        return $this->_VIEWS_ROOT_PATH;
    }

    public function TEMPLATES_ROOT_PATH() {
        return $this->_TEMPLATES_ROOT_PATH;
    }
    
    public function LOGS_ROOT_PATH() {
        return $this->_LOGS_ROOT_PATH;
    }

}