<?php
namespace UCFW\Models\Coupons;

use UCFW\Abstracts\Abstract_Main_Plugin_Class;

use UCFW\Interfaces\Model_Interface;
use UCFW\Interfaces\Activatable_Interface;

use UCFW\Helpers\Plugin_Constants;
use UCFW\Helpers\Helper_Functions;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Model that houses the logic of extending the coupon system of woocommerce.
 * It houses the logic of handling coupon url.
 * Public Model.
 *
 * @since 1.0.0
 */
class URL_Coupon implements Model_Interface , Activatable_Interface {

    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
    */

    /**
     * Property that holds the single main instance of URL_Coupon.
     *
     * @since 1.0.0
     * @access private
     * @var URL_Coupon
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

    /**
     * Coupon endpoint set.
     *
     * @since 1.0.0
     * @access private
     * @var string
     */
    private $_coupon_endpoint;

    /**
     * Coupon base url.
     *
     * @since 1.0.0
     * @access private
     * @var string
     */
    private $_coupon_base_url;

    


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
        $this->_coupon_endpoint  = $this->_helper_functions->get_coupon_url_endpoint();
        $this->_coupon_base_url  = home_url( '/' ) . $this->_coupon_endpoint;

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
     * @return URL_Coupon
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin , Plugin_Constants $constants , Helper_Functions $helper_functions ) {

        if ( !self::$_instance instanceof self )
            self::$_instance = new self( $main_plugin , $constants , $helper_functions );
        
        return self::$_instance;

    }

    /**
     * Generate coupon url for all existing coupons and save it to the each coupon's meta.
     * 
     * @inherit UCFW\Interfaces\Activatable_Interface
     * 
     * @since 1.0.0
     * @access public
     *
     * @global wpdb $wpdb Object that contains a set of functions used to interact with a database.
     */
    public function activate() {

        global $wpdb;

        $wpdb->query("
                    INSERT INTO $wpdb->postmeta ( post_id , meta_key , meta_value )
                    SELECT $wpdb->posts.ID , '" . Plugin_Constants::COUPON_URL . "' , concat( '" . $this->_coupon_base_url . "/' , $wpdb->posts.post_title )
                    FROM $wpdb->posts
                    WHERE $wpdb->posts.post_type = 'shop_coupon'
                    AND $wpdb->posts.ID NOT IN (
                        SELECT $wpdb->posts.ID
                        FROM $wpdb->posts
                        INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
                        WHERE $wpdb->postmeta.meta_key = '" . Plugin_Constants::COUPON_URL . "' )
                    ");
        
    }

    /**
     * Update coupon url additional data whenever the coupon is saved.
     *
     * @since 1.0.0
     * @access public
     *
     * @param int $post_id Id of the coupon post.
     */
    public function save_coupon_data( $post_id ) {

        // On manual click of 'update' , 'publish' or 'save draft' button, execute code inside the if statement
        if ( $this->_helper_functions->check_if_valid_save_post_action( $post_id , 'shop_coupon' ) ) {

            update_post_meta( $post_id , Plugin_Constants::COUPON_URL , $this->_coupon_base_url . '/' . get_the_title( $post_id ) );
            
            do_action( 'ucfw_save_coupon_data' , $post_id );

        }

    }

    /**
     * Check if coupon is valid.
     *
     * @since 1.0.1
     * @access public 
     *
     * @param WC_Coupon $coupon WC_Coupon object.
     * @return boolean Flag that determines if coupon is valid or not.
     */
    public function check_if_valid_coupon( $coupon ) {

        return $coupon->is_valid() && get_post_meta( $coupon->id , Plugin_Constants::DISABLE_COUPON_URL , true ) != 'yes';

    }

    /**
     * Process coupon url.
     *
     * @since 1.0.0
     * @access public
     */
    public function process_coupon_url() {

        if ( get_query_var( $this->_coupon_endpoint ) ) {

            if ( isset( $_GET[ 'code' ] ) && !empty( $_GET[ 'code' ] ) && apply_filters( 'ucfw_check_additional_coupon_endpoint_args' , true ) ) {
                
                $coupon_args = apply_filters( 'ucfw_extract_coupon_endpoint_args' , array( 'code' => sanitize_title( $_GET[ 'code' ] ) ) ); // Coupon codes are just post titles. So we pass it through 'sanitize_title'

                $coupon = new \WC_Coupon( $coupon_args[ 'code' ] );

                if ( $this->check_if_valid_coupon( $coupon ) ) {

                    do_action( 'ucfw_before_apply_coupon' , $coupon , $coupon_args );

                    $result_args = array();

                    // Initialize cart session
                    WC()->session->set_customer_session_cookie( true );

                    if ( WC()->cart->add_discount( $coupon_args[ 'code' ] ) ) {
                        
                        $result_args[ 'coupon_applied' ] = true;
                        $result_args[ 'title' ]          = apply_filters( 'ucfw_coupon_successfully_applied_title' , __( 'Coupon Applied Successfully' , 'url-coupons-for-woocommerce' ) );
                        $result_args[ 'content' ]        = apply_filters( 'ucfw_coupon_successfully_applied_content' , __( 'Coupon Applied Successfully, please improve text' , 'url-coupons-for-woocommerce' ) );
                        
                    } else {
                        
                        $result_args[ 'coupon_applied' ]       = false;
                        $result_args[ 'title' ]                = apply_filters( 'ucfw_coupon_failed_to_apply_title' , __( 'Failed To Apply Coupon' , 'url-coupons-for-woocommerce' ) );
                        $result_args[ 'content' ]              = apply_filters( 'ucfw_coupon_failed_to_apply_content' , __( 'Failed To Apply Coupon, please improve text.' , 'url-coupons-for-woocommerce' ) );
                        
                        // Note: This is the only way of retrieving what might be the error that caused the coupon to not be applied successfully.
                        // This isn't reliable as we are only getting the latest added error notice from wc_notices, which might not be set by the coupon
                        // but its better than nothing, its a bit ok too coz if coupon failed to apply, woocommerce add error notice about it anyways
                        $coupon_error_message                  = wc_get_notices( 'error' );
                        $result_args[ 'coupon_error_message' ] = end( $coupon_error_message );

                    }

                    $result_args = apply_filters( 'ucfw_additional_coupon_application_result_args' , $result_args , $coupon_args , $coupon );

                    do_action( 'ucfw_after_apply_coupon' , $coupon , $coupon_args , $result_args );

                } else {

                    $result_args = apply_filters(
                        'ucfw_additional_invalid_coupon_args' ,
                        array( 
                            'title' => apply_filters( 'ucfw_invalid_coupon_title' , __( 'Invalid Coupon' , 'url-coupons-for-woocommerce' ) )
                        )
                    );

                    do_action( 'ucfw_invalid_coupon' , $coupon , $coupon_args , $result_args );

                }
                
            } else
                do_action( 'ucfw_incomplete_coupon_endpoint_args' );

        }
        
    }

    /**
     * Remove any trailing forward slash on coupon code.
     * These slashes could be added if coupon is accessed via this way coupon/coupon-code.
     * WordPress itself will append trailing forward slash at the end making it coupon-coupon-code/.
     *
     * @since 1.0.0
     * @access public
     *
     * @param array $coupon_args Array of coupon url arguments.
     * @return array Modified array of coupon url arguments.
     */
    public function remove_trailing_forward_slash_on_coupon_code( $coupon_args ) {
        
        $coupon_args[ 'code' ] = str_replace( '/' , '' , $coupon_args[ 'code' ] );
        
        return $coupon_args;
        
    }

    /**
     * Convert overridden coupon code to actual coupon code.
     *
     * @since 1.0.1
     * @access public
     *
     * @param array $coupon_args Array of coupon arguments. Must have 'code' key containing the coupon code.
     * @return array Modified array of coupon arguments.
     */
    public function convert_overridden_code_to_actual_coupon_code( $coupon_args ) {

        $code   = $coupon_args[ 'code' ];
        $coupon = $this->_helper_functions->get_coupon_by_code_url_override( $code );

        if ( !$coupon )
            return $coupon_args; // Meaning this coupon code is not masked, it is already the actual code
        else {

            $coupon_args[ 'code' ] = get_the_title( $coupon->ID ); // Get actual coupon code
            return $coupon_args;

        }

    }

    /**
     * Load appropriate view after coupon has been applied. 
     * This function doesn't care if its successfully applied or not.
     *
     * @since 1.0.0
     * @access public
     *
     * @param WC_Coupon $coupon      WooCommerce coupon object. Could be valid or invalid coupon object.
     * @param array     $coupon_args Coupon url additional arguments.
     * @param array     $result_args Array of arguments that is yielded from the result of applying the coupon to cart.
     */
    public function load_appropriate_view_after_coupon_apply( $coupon , $coupon_args , $result_args ) {

        $redirect_url = get_option( Plugin_Constants::AFTER_APPLY_COUPON_REDIRECT_URL , '' );

        if ( filter_var( $redirect_url , FILTER_VALIDATE_URL ) ) {

            $redirect_url = $this->_process_after_coupon_apply_redirect_url_query_args( $redirect_url , $coupon , $coupon_args , $result_args );
            $this->_redirect_to_url( $redirect_url );

        } else {
            
            wp_safe_redirect( WC()->cart->get_cart_url() );
            exit();

        }
        
    }
    
    /**
     * Load appropriate view whenever an invalid coupon is accessed via coupon url.
     *
     * @since 1.0.0
     * @access public
     *
     * @param WC_Coupon $coupon      WooCommerce coupon object. Could be valid or invalid coupon object.
     * @param array     $coupon_args Coupon url additional arguments.
     * @param array     $result_args Array of arguments that is yielded from attempting to apply invalid coupon to cart.
     */
    public function load_appropriate_view_for_invalid_coupon( $coupon , $coupon_args , $result_args ) {

        $redirect_url = get_option( Plugin_Constants::INVALID_COUPON_REDIRECT_URL , '' );

        if ( filter_var( $redirect_url , FILTER_VALIDATE_URL ) ) {

            $redirect_url = $this->_process_invalid_coupon_redirect_url_query_args( $redirect_url , $coupon , $coupon_args , $result_args );
            $this->_redirect_to_url( $redirect_url );

        } else {

            wc_add_notice( $this->get_coupon_error_message( $coupon ) , 'error' );
            wp_safe_redirect( WC()->cart->get_cart_url() );
            exit();
            
        }

    }

    /**
     * Load appropriate view if coupon endpoint is accessed and with invalid query arguments.
     *
     * @since 1.0.0
     * @access public
     */
    public function load_appropriate_view_for_invalid_coupon_endpoint_query_args() {

        do_action( 'ucfw_load_appropriate_view_for_invalid_coupon_endpoint_query_args' );

        wp_safe_redirect( WC()->cart->get_cart_url() );
        exit();

    }

    /**
     * Process after coupon apply redirect url query vars. Replace em with actual data.
     *
     * @since 1.0.0
     * @access public
     *
     * @param  string    $redirect_url URL to redirect after coupon apply ( Successful or not ).
     * @param  WC_Coupon $coupon       WooCommerce coupon object. Could be valid or invalid coupon object.
     * @param  array     $coupon_args  Array of coupon arguments.
     * @param  array     $result_args  Array of arguments that is yielded after the coupon is applied ( Successful or not ).
     * @return string Modified redirect url.
     */
    private function _process_after_coupon_apply_redirect_url_query_args( $redirect_url , $coupon , $coupon_args , $result_args ) {
        
        if ( !empty( $redirect_url ) ) {

            $query_args = apply_filters(
                'ucfw_after_coupon_apply_redirect_url_query_args' , 
                array( '{ucfw_coupon_code}' , '{ucfw_coupon_applied}' , '{ucfw_coupon_error_message}' )
            );
            
            $coupon_code          = isset( $coupon_args[ 'code' ] ) ? urlencode( $coupon_args[ 'code' ] ) : '';
            $coupon_applied       = isset( $result_args[ 'coupon_applied' ] ) ? ( $result_args[ 'coupon_applied' ] ? 1 : 0 ) : '';
            $coupon_error_message = isset( $result_args[ 'coupon_error_message' ] ) && !empty( $result_args[ 'coupon_error_message' ] ) ? urlencode( $result_args[ 'coupon_error_message' ] ) : '';

            $query_args_replacements = apply_filters(
                'ucfw_after_coupon_apply_redirect_url_query_args_replacements' , 
                array( $coupon_code , $coupon_applied , $coupon_error_message )
            );

            $redirect_url = str_replace( $query_args , $query_args_replacements , $redirect_url );

        }

        return $redirect_url;

    }

    /**
     * Process invalid coupon redirect url query vars. Replace em with actual data.
     *
     * @since 1.0.0
     * @access public
     *
     * @param string    $redirect_url URL to redirect if invalid coupon is visited via coupon url.
     * @param WC_Coupon $coupon       WooCommerce coupon object. Could be valid or invalid coupon object.
     * @param array     $coupon_args  Coupon url additional arguments.
     * @param array     $result_args  Array of arguments that is yielded from attempting to apply invalid coupon to cart.
     */
    private function _process_invalid_coupon_redirect_url_query_args( $redirect_url , $coupon , $coupon_args , $result_args ) {

        if ( !empty( $redirect_url ) ) {

            $query_args = apply_filters(
                'ucfw_invalid_coupon_redirect_url_query_args' , 
                array( '{ucfw_coupon_code}' , '{ucfw_coupon_error_message}' )
            );

            $coupon_code          = isset( $coupon_args[ 'code' ] ) ? urlencode( $coupon_args[ 'code' ] ) : '';
            $coupon_error_message = urlencode( $this->get_coupon_error_message( $coupon ) );

            $query_args_replacements = apply_filters(
                'ucfw_invalid_coupon_redirect_url_query_args_replacements' , 
                array( $coupon_code , $coupon_error_message )
            );

            $redirect_url = str_replace( $query_args , $query_args_replacements , $redirect_url );

        }

        return $redirect_url;

    }

    /**
     * Get coupon error message.
     *
     * @since 1.0.1
     * @access public
     *
     * @param WC_Coupon $coupon WC_Coupon object.
     * @return string Coupon error message.
     */
    public function get_coupon_error_message( $coupon ) {

        if ( get_post_meta( $coupon->id , Plugin_Constants::DISABLE_COUPON_URL , true ) == 'yes' )
            return __( 'Inactive coupon url' , 'url-coupons-for-woocommerce' );
        else
            return $coupon->get_error_message();
        
    }
    
    /**
     * Redirect to a given url.
     *
     * @since 1.0.0
     * @access private
     *
     * @param string $redirect_url sURL to redirect.
     */
    private function _redirect_to_url( $redirect_url ) {

        wp_redirect( $redirect_url );
        exit();
        
    }

    /**
     * Print coupon url link via shotcode.
     *
     * @since 1.0.0
     * @access public
     *
     * @param array  $atts    Shortcode attributes.
     * @param string $content Shortcode content.
     * @return string Shortcode markup.
     */
    public function coupon_url_shortcode( $atts , $content = '' ) {

        $atts = shortcode_atts( array(
            'code'  => '',
            'class' => 'coupon-url'
        ) , $atts , 'url_coupon' );

        // Shortcode Validation
        if ( !isset( $atts[ 'code' ] ) || empty( $atts[ 'code' ] ) )
            return apply_filters( 'ucfw_missing_code_in_coupon_url_shortcode_error_markup' , __( '<pre>Please provide coupon code</pre>' , 'url-coupons-for-woocommerce' ) );
        
        $coupon = new \WC_Coupon( $atts[ 'code' ] );

        if ( !$coupon->is_valid() )
            return apply_filters( 'ucfw_invalid_coupon_in_coupon_url_shortcode_error_markup' , __( '<pre>' . $coupon->get_error_message() . '</pre>' , 'url-coupons-for-woocommerce' ) , $coupon , $atts , $content );

        $err_markup = apply_filters( 'ucfw_additional_coupon_url_shortcode_validation' , null , $coupon , $atts , $content );
        if ( !is_null( $err_markup ) )
            return $err_markup;

        // Validation passed
        $content = !empty( $content ) ? $content : $atts[ 'code' ]; // Content defaults to coupon code


        // TODO: Should be /code/coupon-code as per default
        return apply_filters(
            'ucfw_coupon_url_shortcode_return_markup' ,
            '<a class="' . $atts[ 'class' ] . '" href="' . $this->_coupon_base_url . '?code=' . $atts[ 'code' ] . '">' . $content . '</a>'
        );

    }

    /**
     * Execute codebase that extends the functionality of woocommerce coupon system.
     *
     * @inherit UCFW\Interfaces\Model_Interface
     * 
     * @since 1.0.0
     * @access public
     */
    public function run() {

        add_action( 'save_post' , array( $this , 'save_coupon_data' ) , 10 , 1 );

        add_action( 'template_redirect' , array( $this , 'process_coupon_url' ) );
        add_filter( 'ucfw_extract_coupon_endpoint_args' , array( $this , 'remove_trailing_forward_slash_on_coupon_code' ) , 10 , 1 );
        add_filter( 'ucfw_extract_coupon_endpoint_args' , array( $this , 'convert_overridden_code_to_actual_coupon_code' ) , 20 , 1 );
        add_action( 'ucfw_after_apply_coupon' , array( $this , 'load_appropriate_view_after_coupon_apply' ) , 10 , 3 );
        add_action( 'ucfw_invalid_coupon' , array( $this , 'load_appropriate_view_for_invalid_coupon' ) , 10 , 3 );
        add_action( 'ucfw_incomplete_coupon_endpoint_args' , array( $this , 'load_appropriate_view_for_invalid_coupon_endpoint_query_args' ) );

        add_shortcode( 'url_coupon' , array( $this , 'coupon_url_shortcode' ) , 10 , 2 );

    }

}