<?php
namespace UCFW\Models\Coupons;

use UCFW\Abstracts\Abstract_Main_Plugin_Class;

use UCFW\Interfaces\Model_Interface;

use UCFW\Helpers\Plugin_Constants;
use UCFW\Helpers\Helper_Functions;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Model that houses the logic of extending the WooCommerce coupon admin panel.
 * Adds additional admin panel and fields for url coupons.
 * Private Model.
 *
 * @since 1.0.0
 */
class URL_Coupon_Admin implements Model_Interface {

    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
    */

    /**
     * Property that holds the single main instance of URL_Coupon_Admin.
     *
     * @since 1.0.0
     * @access private
     * @var URL_Coupon_Admin
     */
    private static $_instance;

    /**
     * Property that houses all the helper functions of the plugin.
     *
     * @since 1.0.0
     * @access private
     * @var Helper_Functions
     */
    private $_helper_functions;

    /**
     * Model that houses all the plugin constants.
     *
     * @since 1.0.0
     * @access private
     * @var Plugin_Constants
     */
    private $_constants;




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
     * @return URL_Coupon_Admin
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin , Plugin_Constants $constants , Helper_Functions $helper_functions ) {

        if ( !self::$_instance instanceof self )
            self::$_instance = new self( $main_plugin , $constants , $helper_functions );
        
        return self::$_instance;

    }

    /**
     * Add new coupon usage restriction controls section.
     *
     * @since 1.0.0
     * @access public
     */
    public function coupon_usage_restriction_controls() {

        echo '<div class="options_group">';
        do_action( 'ucfw_url_coupon_usage_restriction_fields' );
        echo '</div>';

    }

    /**
     * Add coupon user role restriction field.
     *
     * @since 1.0.0
     * @access public
     *
     * @global WP_Post $post Global post object.
     */
    public function add_coupon_user_role_restriction_field() {

        global $post;

        ?>
        <p class="form-field">
            <label for="<?php echo Plugin_Constants::COUPON_USER_ROLES_RESTRICTION; ?>"><?php _e( 'User Role Restrictions' , 'url-coupons-for-woocommerce' ); ?></label>
            <select id="<?php echo Plugin_Constants::COUPON_USER_ROLES_RESTRICTION; ?>" name="<?php echo Plugin_Constants::COUPON_USER_ROLES_RESTRICTION; ?>[]" style="width: 50%;" class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php _e( 'No user role restriction' , 'url-coupons-for-woocommerce' ); ?>">
                <?php
                    $user_role_restrictions = (array) get_post_meta( $post->ID , Plugin_Constants::COUPON_USER_ROLES_RESTRICTION , true );
                    $user_roles             = array( 'guest' => __( 'Guest' , 'url-coupons-for-woocommerce' ) ) + (array) $this->_helper_functions->get_all_user_roles();

                    foreach ( $user_roles as $key => $text )
                        echo '<option value="' . $key . '"' . selected( in_array( $key , $user_role_restrictions ) , true , false ) . '>' . $text . '</option>';
                ?>
            </select> <?php echo wc_help_tip( __( 'Customer must have one of these roles in order for them to avail to this coupon' , 'url-coupons-for-woocommerce' ) ); ?>
            <?php wp_nonce_field( 'ucfw_action_save_coupon_user_roles_restriction' , 'ucfw_nonce_save_coupon_user_roles_restriction' ); ?>
        </p>
        <?php

    }

    /**
     * Add new url coupon data tab to woocommerce coupon admin data tabs.
     *
     * @since 1.0.0
     * @access public
     *
     * @param array $coupon_data_tabs Array of coupon admin data tabs.
     * @return array Modified array of coupon admin data tabs.
     */
    public function url_coupon_admin_data_tab( $coupon_data_tabs ) {
        
        $coupon_data_tabs[ 'url_coupon' ] = array(
                                                'label'  => __( 'URL Coupons', 'url-coupons-for-woocommerce' ),
                                                'target' => 'url_coupon_data',
                                                'class'  => ''
                                            );
        
        return $coupon_data_tabs;

    }

    /**
     * Add url cuopun data panel to woocommerce coupon admin data panels.
     *
     * @since 1.0.0
     * @access public
     */
    public function url_coupon_admin_data_panel() {

        include $this->_constants->VIEWS_ROOT_PATH() . 'coupons' . DIRECTORY_SEPARATOR . 'view-url-coupons-data-panel.php';

    }

    /**
     * Add coupon url field on coupon data panel on coupon edit page.
     *
     * @since 1.0.0
     * @access public
     */
    public function add_coupon_url_field() {
        
        woocommerce_wp_text_input( array(
            'id'                => Plugin_Constants::COUPON_URL,
            'style'             => 'width: 90%;',
            'label'             => __( 'Coupon URL' , 'url-coupons-for-woocommerce' ),
            'description'       => __( '<br>Visitors to this link will have the coupon code applied to their cart automatically.' , 'url-coupons-for-woocommerce' ),
            'type'              => 'url',
            'data_type'         => 'url',
            'custom_attributes' => array( 'readonly' => true )
        ) );

    }

    /**
     * Add disable coupon url field on coupon data panel on coupon edit page.
     *
     * @since 1.0.1
     * @access public
     */
    public function add_disable_coupon_url_field() {

        woocommerce_wp_checkbox( array(
            'id'          => Plugin_Constants::DISABLE_COUPON_URL,
            'label'       => __( 'Disable Coupon URL' , 'url-coupons-for-woocommerce' ),
            'description' => __( 'When checked, it disables the coupon url functionality for the current coupon.' , 'url-coupons-for-woocommerce' )
        ) );

        wp_nonce_field( 'ucfw_action_save_disable_coupon_url_option' , 'ucfw_nonce_save_disable_coupon_url_option' );

    }

    /**
     * Add coupon code url override field on coupon data panel on coupon edit page.
     *
     * @since 1.0.1
     * @access public
     */
    public function add_coupon_code_url_override_field() {

        woocommerce_wp_text_input( array(
            'id'          => Plugin_Constants::COUPON_CODE_URL_OVERRIDE,
            'style'       => 'width: 98%;',
            'label'       => __( 'Code URL Override' , 'url-coupons-for-woocommerce' ),
            'description' => __( '<br>Customize the coupon code on the coupon url. Leave blank to disable feature.' , 'url-coupons-for-woocommerce' ),
            'type'        => 'text',
            'data_type'   => 'text'
        ) );

        wp_nonce_field( 'ucfw_action_save_coupon_code_url_override_option' , 'ucfw_nonce_save_coupon_code_url_override_option' );

    }

    /**
     * Save coupon user roles restriction data.
     *
     * @since 1.0.0
     * @access public
     *
     * @param int $post_id Post id.
     */
    private function _save_coupon_user_roles_restriction_data( $post_id ) {

        // Check nonce
        if ( isset( $_POST[ 'ucfw_nonce_save_coupon_user_roles_restriction' ] ) && wp_verify_nonce( $_POST[ 'ucfw_nonce_save_coupon_user_roles_restriction' ] , 'ucfw_action_save_coupon_user_roles_restriction' ) ) {

            $user_roles             = array( 'guest' => __( 'Guest' , 'url-coupons-for-woocommerce' ) ) + (array) $this->_helper_functions->get_all_user_roles();
            $user_role_restrictions = isset( $_POST[ Plugin_Constants::COUPON_USER_ROLES_RESTRICTION ] ) ? (array) $_POST[ Plugin_Constants::COUPON_USER_ROLES_RESTRICTION ] : array();

            // Only allow valid values
            foreach ( $user_role_restrictions as $index => $role )
                if ( !array_key_exists( $role , $user_roles ) )
                    unset( $user_role_restrictions[ $index ] );

            update_post_meta( $post_id , Plugin_Constants::COUPON_USER_ROLES_RESTRICTION , $user_role_restrictions );

        }

    }
    
    /**
     * Save disable coupon url option.
     *
     * @since 1.0.1
     * @access public
     *
     * @param int $post_id Post id.
     */
    private function _save_disable_coupon_url_option( $post_id ) {

        // Check nonce
        if ( isset( $_POST[ 'ucfw_nonce_save_disable_coupon_url_option' ] ) && wp_verify_nonce( $_POST[ 'ucfw_nonce_save_disable_coupon_url_option' ] , 'ucfw_action_save_disable_coupon_url_option' ) ) {

            $disable_coupon_url = isset( $_POST[ Plugin_Constants::DISABLE_COUPON_URL ] ) && $_POST[ Plugin_Constants::DISABLE_COUPON_URL ] == 'yes' ? 'yes' : 'no';

            update_post_meta( $post_id , Plugin_Constants::DISABLE_COUPON_URL , $disable_coupon_url );

        }

    }

    /**
     * Save coupon code url override option.
     *
     * @since 1.0.1
     * @access public
     *
     * @param int $post_id Post id.
     */
    private function _save_coupon_code_url_override_option( $post_id ) {

        if ( isset( $_POST[ 'ucfw_nonce_save_coupon_code_url_override_option' ] ) && wp_verify_nonce( $_POST[ 'ucfw_nonce_save_coupon_code_url_override_option' ] , 'ucfw_action_save_coupon_code_url_override_option' ) ) {

            $coupon_code_override  = isset( $_POST[ Plugin_Constants::COUPON_CODE_URL_OVERRIDE ] ) ? sanitize_title( trim( $_POST[ Plugin_Constants::COUPON_CODE_URL_OVERRIDE ] ) ) : '';
            
            // Check if coupon has space, we don't support this so we auto override the coupon
            if ( $coupon_code_override == '' && isset( $_POST[ 'post_title' ] ) && strpos( $_POST[ 'post_title' ] , ' ' ) !== false ) {

                $coupon_code_override = $this->_generate_coupon_code_url_override( $post_id );
                $dup_override         = false;

            } else
                $dup_override = $this->_helper_functions->get_coupon_by_code_url_override( $coupon_code_override );
            
            if ( !$dup_override || $dup_override->ID == $post_id || $coupon_code_override == '' ) {
                
                update_post_meta( $post_id , Plugin_Constants::COUPON_CODE_URL_OVERRIDE , $coupon_code_override );

                $this->_sync_coupon_code_override_with_coupon_url( $post_id , $coupon_code_override );
                
            } else
                add_filter( 'redirect_post_location' , array( $this , 'add_dup_code_override_error_query_var' ) , 99 , 1 );
            
        }

    }
    
    /**
     * Generate coupon code url override.
     *
     * @since 1.0.1
     * @access public
     *
     * @param int $post_id Coupon id.
     */
    private function _generate_coupon_code_url_override( $post_id ) {

        if ( isset( $_POST[ 'post_title' ] ) && $_POST[ 'post_title' ] ) {

            $initial_pass = true;

            do {

                if ( $initial_pass ) {

                    $coupon_code_override = sanitize_title( $_POST[ 'post_title' ] );
                    $dup_override = $this->_helper_functions->get_coupon_by_code_url_override( $coupon_code_override );

                } else {

                    // Brute force create unique coupon code url override.
                    // This is for very edge cases.

                    $coupon_code_override = sanitize_title( $_POST[ 'post_title' ] ) . '-' . rand( 1 , 100 );
                    $dup_override = $this->_helper_functions->get_coupon_by_code_url_override( $coupon_code_override );

                }

                if ( $initial_pass )
                    $initial_pass = false;

            } while( $dup_override && $dup_override->ID != $post_id );

            return $coupon_code_override;

        } else
            return false;
        
    }
    
    /**
     * Sync coupon code url override with coupon url.
     *
     * @since 1.0.1
     * @access public
     *
     * @param int    $post_id              Coupon Id.
     * @param string $coupon_code_override Coupon code override.
     */
    private function _sync_coupon_code_override_with_coupon_url( $post_id , $coupon_code_override ) {

        $coupon_base_url = home_url( '/' ) . $this->_helper_functions->get_coupon_url_endpoint();

        if ( $coupon_code_override )
            update_post_meta( $post_id , Plugin_Constants::COUPON_URL , $coupon_base_url . '/' . $coupon_code_override );
        else
            update_post_meta( $post_id , Plugin_Constants::COUPON_URL , $coupon_base_url . '/' . get_the_title( $post_id ) );
        
    }

    /**
     * Save additional coupon data added by the plugin.
     *
     * @since 1.0.0
     * @access public
     */
    public function save_coupon_data( $post_id ) {

        // On manual click of 'update' , 'publish' or 'save draft' button, execute code inside the if statement
        if ( $this->_helper_functions->check_if_valid_save_post_action( $post_id , 'shop_coupon' ) ) {

            // Save coupon user roles restriction
            $this->_save_coupon_user_roles_restriction_data( $post_id );

            // Save disable coupon url option
            $this->_save_disable_coupon_url_option( $post_id );

            // Save coupon code url override option
            $this->_save_coupon_code_url_override_option( $post_id );

        }

    }

    /**
     * Add additional url query arg to the destination url to specify that it fails to save coupon code url override coz it is duplicate.
     *
     * @since 1.0.1
     * @access public
     *
     * @param string $location Location to redirect.
     */
    public function add_dup_code_override_error_query_var( $location ) {

        remove_filter( 'redirect_post_location' , array( $this , 'add_dup_code_override_error_query_var' ) , 99 );

        return add_query_arg( array( 'dup_code_override' => 1 ) , $location );

    }

    /**
     * Add admin notice when try to save duplicate coupon code url override option.
     *
     * @since 1.0.1
     * @access public
     */
    public function admin_notice_saving_dup_code_override() {

        if ( !isset( $_GET[ 'dup_code_override' ] ) ) return;

        global $post;

        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e( 'Failed to save <b>Code URL Override</b>. Already used by another coupon.' , 'url-coupons-for-woocommerce' ); ?></p>
        </div>
        <?php

    }

    /**
     * Execute url coupon model.
     *
     * @inherit UCFW\Interfaces\Model_Interface
     * 
     * @since 1.0.0
     * @access public
     */
    public function run() {

        // Extend coupon restrictions
        add_action( 'woocommerce_coupon_options_usage_restriction' , array( $this , 'coupon_usage_restriction_controls' ) );
        add_action( 'ucfw_url_coupon_usage_restriction_fields' ,     array( $this , 'add_coupon_user_role_restriction_field' ) );

        // Add 'URL Coupons' tab on coupon edit admin
        add_filter( 'woocommerce_coupon_data_tabs' ,   array( $this , 'url_coupon_admin_data_tab' ) , 10 , 1 );
        add_action( 'woocommerce_coupon_data_panels' , array( $this , 'url_coupon_admin_data_panel' ) );

        // 'URL Coupons' tab options
        add_action( 'ucfw_url_coupon_data_panel_fields' , array( $this , 'add_disable_coupon_url_field' ) , 10 );
        add_action( 'ucfw_url_coupon_data_panel_fields' , array( $this , 'add_coupon_url_field' ) , 10 );
        add_action( 'ucfw_url_coupon_data_panel_fields' , array( $this , 'add_coupon_code_url_override_field' ) , 10 );

        add_action( 'save_post' , array( $this , 'save_coupon_data' ) , 20 , 1 );

        add_action( 'admin_notices' , array( $this , 'admin_notice_saving_dup_code_override' ) , 10 );

    }

}
