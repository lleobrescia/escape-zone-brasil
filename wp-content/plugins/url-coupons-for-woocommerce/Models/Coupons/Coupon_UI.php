<?php
namespace UCFW\Models\Coupons;

use UCFW\Abstracts\Abstract_Main_Plugin_Class;

use UCFW\Interfaces\Model_Interface;

use UCFW\Helpers\Plugin_Constants;
use UCFW\Helpers\Helper_Functions;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Model that houses the logic of extending/modifying the coupon UI on the front end.
 * Public Model.
 *
 * @since 1.0.0
 */
class Coupon_UI implements Model_Interface {

    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
    */

    /**
     * Property that holds the single main instance of Coupon_UI.
     *
     * @since 1.0.0
     * @access private
     * @var Coupon_UI
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
        $main_plugin->add_to_public_models( $this );

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
     * @return Coupon_UI
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin , Plugin_Constants $constants , Helper_Functions $helper_functions ) {

        if ( !self::$_instance instanceof self )
            self::$_instance = new self( $main_plugin , $constants , $helper_functions );
        
        return self::$_instance;

    }

    /**
     * Hide coupon ui on cart page.
     *
     * @since 1.0.0
     * @access public
     */
    public function hide_coupon_ui_on_cart_page() {

        do_action( 'ucfw_before_hide_coupon_ui_on_cart_page' ); ?>

        <script>
            jQuery( document ).ready( function( $ ) {

                $( 'body.woocommerce-cart .shop_table div.coupon' ).find( 'label[ for="coupon_code" ] , #coupon_code , input[ name="apply_coupon" ]' ).remove();

                <?php do_action( 'ucfw_hide_coupon_ui_on_cart_page' ); ?>

            } );
        </script>

        <?php do_action( 'ucfw_after_hide_coupon_ui_on_cart_page' );

    }

    /**
     * Hide coupon ui on checkout page.
     *
     * @since 1.0.0
     * @access public
     */
    public function hide_coupon_ui_on_checkout_page() {
        
        remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form' );

    }

    /**
     * Execute the code base that modifies the UI of woocommerce coupon on the front end.
     *
     * @inherit UCFW\Interfaces\Model_Interface
     *
     * @since 1.0.0
     * @access public
     */
    public function run() {

        if ( get_option( Plugin_Constants::HIDE_COUPON_UI_ON_CART_AND_CHECKOUT , false ) == 'yes' ) {

            add_action( 'woocommerce_cart_coupon' , array( $this , 'hide_coupon_ui_on_cart_page' ) );
            add_action( 'wp_head' , array( $this , 'hide_coupon_ui_on_checkout_page' ) );

        }

    }

}