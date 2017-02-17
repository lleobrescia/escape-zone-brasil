<?php
namespace UCFW\Models\Coupons;

use UCFW\Abstracts\Abstract_Main_Plugin_Class;

use UCFW\Interfaces\Model_Interface;
use UCFW\Interfaces\Activatable_Interface;
use UCFW\Interfaces\Initializable_Interface;
use UCFW\Interfaces\Deactivatable_Interface;

use UCFW\Helpers\Plugin_Constants;
use UCFW\Helpers\Helper_Functions;

/**
 * Model that houses the logic of managing the plugin endpoints.
 * Private Model.
 * 
 * @since 1.0.0
 */
class URL_Coupon_Endpoint implements Model_Interface , Activatable_Interface , Initializable_Interface , Deactivatable_Interface {

    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
    */

    /**
     * Property that holds the single main instance of URL_Coupon_Endpoint.
     *
     * @since 1.0.0
     * @access private
     * @var URL_Coupon_Endpoint
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
     * @return URL_Coupon
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin , Plugin_Constants $constants , Helper_Functions $helper_functions ) {

        if ( !self::$_instance instanceof self )
            self::$_instance = new self( $main_plugin , $constants , $helper_functions );
        
        return self::$_instance;

    }

    /**
     * Add custom rewrite rule and sync coupon endpoint to existing coupon url.
     * 
     * @inherit UCFW\Interfaces\Activatable_Interface
     * 
     * @since 1.0.0
     * @access public
     *
     * @global wpdb $wpdb Object that contains a set of functions used to interact with a database.
     */
    public function activate() {

        $this->_add_coupon_endpoint_rewrite_rule();
        $this->sync_coupon_endpoint_to_existing_coupon_url();
        
    }

    /**
     * Add rewrite endpoint. Custom endpoints must be added via init, no workaround.
     * Rewrite rule must be in init too, Why? well if they happend to update the permalink,
     * and since we didn't add our rewrite rule on init, our customer rewrite rule won't get saved.
     * No worries, it won't dup if already registered ( as its rewrite rule already added on activate).
     * 
     * @inherit UCFW\Interfaces\Initializable_Interface
     * 
     * @since 1.0.0
     * @access public
     */
    public function initialize() {

        $this->_add_coupon_endpoint();
        $this->_add_coupon_endpoint_rewrite_rule();

    }

    /**
     * Remove any custom rewrite rules.
     * Cleans up any residual rewrite rules on htaccess.
     * 
     * @inherit UCFW\Interfaces\Deactivatable_Interface
     * 
     * @since 1.0.0
     * @access public
     */
    public function deactivate() {

        $this->_remove_coupon_endpoint_rewrite_rule();
        
    }

    /**
     * Add coupon endpoint.
     *
     * @since 1.0.0
     * @access public
     */
    private function _add_coupon_endpoint() {

        $endpoint = $this->_helper_functions->get_coupon_url_endpoint();

        add_rewrite_endpoint( $endpoint , EP_ALL );
        
    }

    /**
     * Add coupon endpoint rewrite rule.
     * Allowing access of coupons via coupon/coupon-code url format.
     *
     * @since 1.0.0
     * @access public
     */
    private function _add_coupon_endpoint_rewrite_rule() {

        $endpoint = $this->_helper_functions->get_coupon_url_endpoint();

        add_rewrite_rule( $endpoint . '/(.+)/?' , $endpoint . '?code=$1' , 'top' );

    }

    /**
     * Remove coupon endpoint rewrite rule.
     * Cleans up any residual rewrite rules on htaccess.
     *
     * @since 1.0.1
     * @access private
     *
     * @global WP_Rewrite $wp_rewrite Core class used to implement a rewrite component API.
     */
    private function _remove_coupon_endpoint_rewrite_rule() {

        global $wp_rewrite;

        $endpoint = $this->_helper_functions->get_coupon_url_endpoint();

        unset( $wp_rewrite->non_wp_rules[ $endpoint . '/(.+)/?' ] );

        $wp_rewrite->flush_rules();

    }

    /**
     * Add endpoint to query vars that the site recognizes.
     * 
     * @since 1.0.0
     * @access public
     */
    public function initialize_query_vars( $query_vars ) {

        $query_vars[] = $this->_coupon_endpoint;

        return $query_vars;

    }

    /**
     * Initialize the endpoint if present on the current url.
     *
     * @since 1.0.0
     * @access public
     */
    public function initialize_request( $query_vars ) {
        
        if ( isset( $query_vars[ $this->_coupon_endpoint ] ) )
            $query_vars[ $this->_coupon_endpoint ] = true;
        
        return $query_vars;

    }

    /**
     * Sanitize coupon endpoint option value.
     *
     * @since 1.0.1
     * @access public
     */
    public function sanitize_coupon_endpoint_option_value( $value , $option , $raw_value ) {

        if ( $value )
            return sanitize_title( $value );
        else
            return 'coupon';

    }

    /**
     * When coupon endpoint is updated on the plugin settings. 
     * Update endpoint and rewrite rules.
     * Sync endpoint to existing coupon url.
     *
     * @since 1.0.1
     * @access public
     *
     * @param string $current_section Current settings option being saved.
     * @param array  $settings        Current settings section.
     */
    public function refresh_coupon_endpoint_and_resync_coupon_url( $current_section , $settings ) {

        if ( $current_section == "" ) {

            $this->_add_coupon_endpoint();
            $this->_add_coupon_endpoint_rewrite_rule();
            $this->sync_coupon_endpoint_to_existing_coupon_url();

        }

    }

    /**
     * Sync coupon endpoint to existing coupon url.
     *
     * @since 1.0.1
     * @access public
     */
    public function sync_coupon_endpoint_to_existing_coupon_url() {

        global $wpdb;

        // We need to regenerate the base url
        // The value that this variable $this->_coupon_base_url holds is the base url with old coupon endpoint
        // It will be updated but only later after reload, after options are saved.
        // Since we need to trigger the codes below before reload, then that is why we need to regenerate the coupon base url
        $coupon_base_url = home_url( '/' ) . $this->_helper_functions->get_coupon_url_endpoint() . "/";

        // For coupons with no coupon code url overrides
        $wpdb->query(
                    "UPDATE $wpdb->postmeta AS post_meta_table
                    JOIN (
                        SELECT *
                        FROM $wpdb->posts
                        INNER JOIN $wpdb->postmeta
                        ON $wpdb->posts.ID = $wpdb->postmeta.post_id
                        WHERE $wpdb->posts.post_type = 'shop_coupon'
                        AND $wpdb->postmeta.meta_key = '" . Plugin_Constants::COUPON_CODE_URL_OVERRIDE . "'
                        AND $wpdb->postmeta.meta_value = ''
                    ) AS posts_table ON post_meta_table.post_id = posts_table.ID
                    SET post_meta_table.meta_value = concat( '" . $coupon_base_url . "' , posts_table.post_title )
                    WHERE post_meta_table.meta_key = '" . Plugin_Constants::COUPON_URL . "'"
                    );
        
        // For coupons with coupon code url overrides
        $wpdb->query(
                    "UPDATE $wpdb->postmeta AS post_meta_table
                    JOIN (
                        SELECT $wpdb->posts.* , $wpdb->postmeta.meta_value as coupon_code_url_override
                        FROM $wpdb->posts
                        INNER JOIN $wpdb->postmeta
                        ON $wpdb->posts.ID = $wpdb->postmeta.post_id
                        WHERE $wpdb->posts.post_type = 'shop_coupon'
                        AND $wpdb->postmeta.meta_key = '" . Plugin_Constants::COUPON_CODE_URL_OVERRIDE . "'
                        AND $wpdb->postmeta.meta_value != ''
                    ) AS posts_table ON post_meta_table.post_id = posts_table.ID
                    SET post_meta_table.meta_value = concat( '" . $coupon_base_url . "' , posts_table.coupon_code_url_override )
                    WHERE post_meta_table.meta_key = '" . Plugin_Constants::COUPON_URL . "'"
                    );
        
        flush_rewrite_rules();
        
    }

    /**
     * Execute code that registers and initializes plugin endpoints.
     *
     * @inherit UCFW\Interfaces\Model_Interface
     *  
     * @since 1.0.0
     * @access public
     */
    public function run() {

        // Initialize url coupon endpoint
        add_filter( 'query_vars' , array( $this , 'initialize_query_vars' ) , 10 , 1 );
        add_filter( 'request' ,    array( $this , 'initialize_request' ) ,    10 , 1 );

        add_filter( "woocommerce_admin_settings_sanitize_option_" . Plugin_Constants::COUPON_ENDPOINT , array( $this , 'sanitize_coupon_endpoint_option_value' ) , 10 , 3 );

        add_action( 'ucfw_after_save_settings' , array( $this , 'refresh_coupon_endpoint_and_resync_coupon_url' ) , 10 , 2 );

    }

}
