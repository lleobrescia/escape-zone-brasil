<?php
namespace UCFW\Models;

use UCFW\Helpers\Plugin_Constants;
use UCFW\Helpers\Helper_Functions;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class UCFW_Settings extends \WC_Settings_Page {

    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
    */

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
     * UCFW_Settings constructor.
     *
     * @since 1.0.0
     * @access public
     *
     * @param Plugin_Constants $constants        Plugin constants object.
     * @param Helper_Functions $helper_functions Helper functions object.
     */
    public function __construct( Plugin_Constants $constants , Helper_Functions $helper_functions ) {

        $this->_constants        = $constants;
        $this->_helper_functions = $helper_functions;
        $this->id                = 'ucfw_settings';
        $this->label             = __( 'URL Coupons' , 'url-coupon-for-woocommerce' );

        add_filter( 'woocommerce_settings_tabs_array' , array( $this , 'add_settings_page' ) , 30 ); // 30 so it is after the API tab
        add_action( 'woocommerce_settings_' . $this->id , array( $this , 'output' ) );
        add_action( 'woocommerce_settings_save_' . $this->id , array( $this , 'save' ) );
        add_action( 'woocommerce_sections_' . $this->id , array( $this , 'output_sections' ) );

        // Custom settings fields
        add_action( 'woocommerce_admin_field_ucfw_help_resources_field' , array( $this , 'render_ucfw_help_resources_field' ) );
        add_action( 'woocommerce_admin_field_ucfw_ms_banner_controls' , array( $this , 'render_ucfw_ms_banner_controls' ) );

        do_action( 'ucfw_settings_construct' );

    }

    /**
     * Get sections.
     * 
     * @since 1.0.0
     * @access public
     * 
     * @return array
     */
    public function get_sections() {

        $sections = array(
            ''                          => __( 'General' , 'url-coupon-for-woocommerce' ),
            'ucfw_setting_help_section' => __( 'Help' , 'url-coupon-for-woocommerce' )
        );

        return apply_filters( 'woocommerce_get_sections_' . $this->id , $sections );

    }

    /**
     * Output the settings.
     * 
     * @since 1.0.0
     * @access public
     */
    public function output() {

        global $current_section;

        $settings = $this->get_settings( $current_section );
        \WC_Admin_Settings::output_fields( $settings );

    }

    /**
     * Save settings.
     *
     * @since 1.0.0
     * @access public
     */
    public function save() {

        global $current_section;

        $settings = $this->get_settings( $current_section );

        do_action( 'ucfw_before_save_settings' , $current_section , $settings );

        \WC_Admin_Settings::save_fields( $settings );

        do_action( 'ucfw_after_save_settings' , $current_section , $settings );

    }

    /**
     * Get settings array.
     *
     * @since 1.0.0
     * @access public
     * 
     * @param  string $current_section Current settings section.
     * @return array  Array of options for the current setting section.
     */
    public function get_settings( $current_section = '' ) {

        if ( $current_section == 'ucfw_setting_help_section' ) {

            // Help Section Options
            $settings = apply_filters( 'ucfw_setting_help_section_options' , $this->_get_help_section_options() );

        } else {

            // General Section Options
            $settings = apply_filters( 'ucfw_setting_general_section_options' , $this->_get_general_section_options() );

        }

        return apply_filters( 'woocommerce_get_settings_' . $this->id , $settings , $current_section );

    }




    /*
    |--------------------------------------------------------------------------------------------------------------
    | Section Settings
    |--------------------------------------------------------------------------------------------------------------
    */

    /**
     * Get general section options.
     *
     * @since 1.0.0
     * @access private
     *
     * @return array
     */
    private function _get_general_section_options() {

        return array(

            array(
                'title' => __( 'General Options', 'url-coupon-for-woocommerce' ),
                'type'  => 'title',
                'desc'  => '',
                'id'    => 'ucfw_general_main_title'
            ),
            
            array(
                'name' => '',
                'type' => 'ucfw_ms_banner_controls',
                'desc' => '',
                'id'   => 'ucfw_ms_banner',
            ),

            array(
                'title'    => __( 'URL Prefix' , 'url-coupon-for-woocommerce' ),
                'type'     => 'text',
                'desc'     => __( 'The prefix to be used before the coupon code. Eg. [siteurl]/coupon/[coupon-name]' , 'url-coupon-for-woocommerce' ),
                'desc_tip' => true,
                'id'       => Plugin_Constants::COUPON_ENDPOINT,
                'default'  => 'coupon' // Don't translate, its an endpoint
            ),

            array(
                'title'     => __( 'Redirect To URL After Applying Coupon' , 'url-coupon-for-woocommerce' ),
                'type'      => 'text',
                'desc'      => __( "Optional. Will redirect the user to the provided URL when coupon has been applied. You can also pass query args to the URL for the following variables: {ucfw_coupon_code}, {ucfw_coupon_applied} or {ucfw_coupon_error_message} and they will be replaced with proper data. Eg. ?foo={ucfw_coupon_error_message}, then test the 'foo' query arg to get the message if there is one." , 'url-coupon-for-woocommerce' ),
                'id'        => Plugin_Constants::AFTER_APPLY_COUPON_REDIRECT_URL,
                'css'       => 'width: 500px; display: block;'
            ),

            array(
                'title' => __( 'Redirect to url if invalid coupon is visited' , 'url-coupon-for-woocommerce' ),
                'type'  => 'text',
                'desc'  => __( "Optional. Will redirect the user to the provided URL when an invalid coupon has been attempted. You can also pass query args to the URL for the following variables {ucfw_coupon_code} or {ucfw_coupon_error_message} and it will be replaced with proper data. Eg. ?foo={ucfw_coupon_error_message}, then test the 'foo' query arg to get the message if there is one." , 'url-coupon-for-woocommerce' ),
                'id'    => Plugin_Constants::INVALID_COUPON_REDIRECT_URL,
                'css'   => 'width: 500px; display: block;'
            ),

            array(
                'title' => __( 'Hide coupon fields' , 'url-coupon-for-woocommerce' ),
                'type'  => 'checkbox',
                'desc'  => __( 'Hide the coupon fields from the cart and checkout pages on the front end.' , 'url-coupon-for-woocommerce' ),
                'id'    => Plugin_Constants::HIDE_COUPON_UI_ON_CART_AND_CHECKOUT
            ),
            
            array(
                'type' => 'sectionend',
                'id'   => 'ucfw_general_sectionend'
            )

        );

    }

    /**
     * Get help section options
     *
     * @since 1.0.0
     * @access private
     *
     * @return array
     */
    private function _get_help_section_options() {

        return array(

            array(
                'title' => __( 'Help Options' , 'url-coupon-for-woocommerce' ),
                'type'  => 'title',
                'desc'  => '',
                'id'    => 'ucfw_help_main_title'
            ),

            array(
                'name'  =>  '',
                'type'  =>  'ucfw_help_resources_field',
                'desc'  =>  '',
                'id'    =>  'ucfw_help_resources',
            ),

            array(
                'title' => __( 'Clean up plugin options on un-installation' , 'url-coupon-for-woocommerce' ),
                'type'  => 'checkbox',
                'desc'  => __( 'If checked, removes all plugin options when this plugin is uninstalled. <b>Warning:</b> This process is irreversible.' , 'url-coupon-for-woocommerce' ),
                'id'    => Plugin_Constants::CLEAN_UP_PLUGIN_OPTIONS
            ),

            array(
                'type' => 'sectionend',
                'id'   => 'ucfw_help_sectionend'
            )

        );

    }




    /*
    |--------------------------------------------------------------------------------------------------------------
    | Custom Settings Fields
    |--------------------------------------------------------------------------------------------------------------
    */

    /**
     * Render help resources controls.
     *
     * @since 1.0.0
     * @access public
     *
     * @param $value
     */
    public function render_ucfw_help_resources_field( $value ) {
        ?>

        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for=""><?php _e( 'Knowledge Base' , 'url-coupon-for-woocommerce' ); ?></label>
            </th>
            <td class="forminp forminp-<?php echo sanitize_title( $value[ 'type' ] ); ?>">
                <?php echo sprintf( __( 'Looking for documentation? Please see our growing <a href="%1$s" target="_blank">Knowledge Base</a>' , 'url-coupon-for-woocommerce' ) , "https://marketingsuiteplugin.com/knowledge-base/timed-email-offers/?utm_source=ucfw&utm_medium=Settings%20Help&utm_campaign=ucfw" ); ?>
            </td>
        </tr>

        <?php
    }

    /**
     * Render MS promo banner.
     *
     * @since 1.0.0
     * @access public
     *
     * @param $value
     */
    public function render_ucfw_ms_banner_controls( $value ) {
        ?>

        <tr valign="top">
            <th scope="row" class="titledesc" colspan="2">
                <a style="outline: none; display: inline-block;" target="_blank" href="https://marketingsuiteplugin.com/free/?utm_source=UCFW&utm_medium=Settings">
                    <img style="outline: none; border: 0;" src="<?php echo $this->_constants->IMAGES_ROOT_URL() . 'MS_Banner.jpg'; ?>" alt="<?php _e( 'Marketing Suite Plugin' , 'url-coupons-for-woocommerce' ); ?>"/>
                </a>
            </th>
        </tr>

        <?php
    }

}
