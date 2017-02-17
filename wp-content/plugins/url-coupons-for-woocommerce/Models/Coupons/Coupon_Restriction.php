<?php
namespace UCFW\Models\Coupons;

use UCFW\Abstracts\Abstract_Main_Plugin_Class;

use UCFW\Interfaces\Model_Interface;

use UCFW\Helpers\Plugin_Constants;
use UCFW\Helpers\Helper_Functions;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Model that houses the logic of implementing additional coupon restrictions.
 * Public Model.
 *
 * @since 1.0.0
 */
class Coupon_Restriction implements Model_Interface {

    /*
    |--------------------------------------------------------------------------
    | Class Constants
    |--------------------------------------------------------------------------
    */

    // We'll start with 1000 coz why not?

    /**
     * Error code thrown if user has no role required to avail the current coupon.
     *
     * @since 1.0.0
     * @var int
     */
    const E_UCFW_COUPON_INVALID_USER_ROLE = 1000;

    


    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
    */

    /**
     * Property that holds the single main instance of Coupon_Restriction.
     *
     * @since 1.0.0
     * @access private
     * @var Coupon_Restriction
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
     * @return Coupon_Restriction
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin , Plugin_Constants $constants , Helper_Functions $helper_functions ) {

        if ( !self::$_instance instanceof self )
            self::$_instance = new self( $main_plugin , $constants , $helper_functions );
        
        return self::$_instance;

    }

    /**
     * Execute additional coupon restriction filters.
     *
     * @since 1.0.0
     * @access public
     *
     * @param boolean   $valid  Boolean flag that determines if coupon is valid or not.
     * @param WC_Coupon $coupon WC_Coupon object.
     * @return boolean Boolean flag that determines if coupon is valid or not.
     */
    public function check_additional_coupon_restrictions( $valid , $coupon ) {

        $this->check_if_user_is_authorized( $coupon );

        do_action( 'ucfw_additional_coupon_restrictions' , $coupon );

        return $valid;

    }

    /**
     * Check if current user is authorized to avail the current coupon based on the user's roles.
     *
     * @since 1.0.0
     * @access public
     *
     * @param WC_Coupon $coupon WC_Coupon object.
     */
    public function check_if_user_is_authorized( $coupon ) {

        $current_user            = wp_get_current_user();
        $user_roles_restrictions = get_post_meta( $coupon->id , Plugin_Constants::COUPON_USER_ROLES_RESTRICTION , true );
        if ( !is_array( $user_roles_restrictions ) )
            $user_roles_restrictions = array();

        if ( !empty( $user_roles_restrictions ) ) {

            $intersecting_roles = array_intersect( $current_user->roles , $user_roles_restrictions );

            if ( ( !$current_user->ID && !in_array( 'guest' , $user_roles_restrictions ) ) || ( $current_user->ID && empty( $intersecting_roles ) ) )
                throw new \Exception( self::E_UCFW_COUPON_INVALID_USER_ROLE );
            
        }
        
    }

    /**
     * Filter custom error codes and return the equivalent error message.
     *
     * @since 1.0.0
     * @access public
     *
     * @param string    $err      Error message.
     * @param int       $err_code Error code.
     * @param WC_Coupon $coupon   WC_Coupon object.
     * @return string Error message.
     */
    public function filter_custom_coupon_error_codes( $err , $err_code , $coupon ) {

        switch ( $err_code ) {

            case self::E_UCFW_COUPON_INVALID_USER_ROLE:
                return __( "Sorry, you aren't authorized to use this coupon" , 'url-coupons-for-woocommerce' );
                break;
            
        }

        return $err;

    }

    /**
     * Execute the code base that extends the woocommerce coupon restrictions functionality.
     *
     * @inherit UCFW\Interfaces\Model_Interface
     *
     * @since 1.0.0
     * @access public
     */
    public function run() {

        add_filter( 'woocommerce_coupon_is_valid' , array( $this , 'check_additional_coupon_restrictions' ) , 10 , 2 );
        add_filter( 'woocommerce_coupon_error' , array( $this , 'filter_custom_coupon_error_codes' ) , 10 , 3 );

    }

}