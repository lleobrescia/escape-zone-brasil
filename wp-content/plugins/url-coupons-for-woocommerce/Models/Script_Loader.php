<?php
namespace UCFW\Models;

use UCFW\Abstracts\Abstract_Main_Plugin_Class;

use UCFW\Interfaces\Model_Interface;

use UCFW\Helpers\Plugin_Constants;
use UCFW\Helpers\Helper_Functions;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Model that houses the logic of loading plugin scripts.
 * Private Model.
 *
 * @since 1.0.0
 */
class Script_Loader implements Model_Interface {

    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
    */

    /**
     * Property that holds the single main instance of Bootstrap.
     *
     * @since 1.0.0
     * @access private
     * @var Bootstrap
     */
    private static $_instance;

    /**
     * Model that houses all the plugin constants.
     *
     * @since 1.0.0
     * @access private
     * @var Plugin_Constants
     */
    private $_constants;

    /**
     * Property that houses all the helper functions of the plugin.
     *
     * @since 1.0.0
     * @access private
     * @var Helper_Functions
     */
    private $_helper_functions;



    
    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Class constructor.
     * 
     * @since 1.0.0
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     */
    public function __construct( Abstract_Main_Plugin_Class $main_plugin , Plugin_Constants $constants , Helper_Functions $helper_functions ) {

        $this->_constants        = $constants;
        $this->_helper_functions = $helper_functions;

        $main_plugin->add_to_all_plugin_models( $this );

    }

    /**
     * Ensure that only one instance of this class is loaded or can be loaded ( Singleton Pattern ).
     * 
     * @since 1.0.0
     * @access public
     * 
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     * @return Bootstrap
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin , Plugin_Constants $constants , Helper_Functions $helper_functions ) {

        if ( !self::$_instance instanceof self )
            self::$_instance = new self( $main_plugin , $constants , $helper_functions );
        
        return self::$_instance;

    }

    /**
     * Load backend js and css scripts.
     *
     * @since 1.0.0
     * @access public
     *
     * @param string $handle Unique identifier of the current backend page.
     */
    public function load_backend_scripts( $handle ) {

        $screen = get_current_screen();

        $post_type = get_post_type();
        if ( !$post_type && isset( $_GET[ 'post_type' ] ) )
            $post_type = $_GET[ 'post_type' ];
        
        if ( ( $handle == 'post-new.php' || $handle == 'post.php' ) && $post_type == 'shop_coupon' ) {

            wp_enqueue_script( 'ucfw_clipboardjs_js' , $this->_constants->JS_ROOT_URL() . 'lib/clipboardjs/clipboard.min.js' , array( 'jquery' ) , Plugin_Constants::VERSION , true );
            wp_enqueue_script( 'ucfw_coupons-admin_js' , $this->_constants->JS_ROOT_URL() . 'coupons/coupons-admin.js' , array( 'ucfw_clipboardjs_js' , 'jquery' ) , Plugin_Constants::VERSION , true );
            
            wp_localize_script( 'ucfw_coupons-admin_js' , 'ucfw_coupons_admin_params' , array(
                'coupon_url_id'       => Plugin_Constants::COUPON_URL,
                'img_root_url'        => $this->_constants->IMAGES_ROOT_URL(),
                'i18n_copied'         => __( 'Copied to clipboard' , 'url-coupons-for-woocommerce' ),
                'i18n_failed_to_copy' => __( 'Failed to copy to clipboard' , 'url-coupons-for-woocommerce' )
            ) );

        }
        
    }

    /**
     * Load frontend js and css scripts.
     *
     * @since 1.0.0
     * @access public
     */
    public function load_frontend_scripts() {

        global $post, $wp;

    }

    /**
     * Execute plugin script loader.
     *
     * @inherit UCFW\Interfaces\Model_Interface
     * 
     * @since 1.0.0
     * @access public
     */
    public function run () {

        add_action( 'admin_enqueue_scripts' , array( $this , 'load_backend_scripts' ) , 10 , 1 );
        add_action( 'wp_enqueue_scripts' , array( $this , 'load_frontend_scripts' ) );

    }

}