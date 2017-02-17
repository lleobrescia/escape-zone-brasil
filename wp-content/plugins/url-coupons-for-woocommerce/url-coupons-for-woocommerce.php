<?php
/**
 * Plugin Name: URL Coupons for WooCommerce
 * Plugin URI: https://marketingsuiteplugin.com
 * Description: URL coupons for woocommerce
 * Version: 1.0.2
 * Author: Rymera Web Co
 * Author URI: https://rymera.com.au
 * Requires at least: 4.4.2
 * Tested up to: 4.7.0
 *
 * Text Domain: url-coupons-for-woocommerce
 * Domain Path: /languages/
 *
 * @package UCFW
 * @category Core
 * @author Rymera Web Co
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use UCFW\Abstracts\Abstract_Main_Plugin_Class;

use UCFW\Interfaces\Model_Interface;

use UCFW\Models\Bootstrap;
use UCFW\Models\Script_Loader;

use UCFW\Models\Coupons\URL_Coupon_Endpoint;
use UCFW\Models\Coupons\URL_Coupon_Admin;
use UCFW\Models\Coupons\Coupon_Restriction;
use UCFW\Models\Coupons\Coupon_UI;
use UCFW\Models\Coupons\URL_Coupon;

use UCFW\Helpers\Plugin_Constants;
use UCFW\Helpers\Helper_Functions;

/**
 * Register plugin autoloader.
 *
 * @since 1.0.0
 *
 * @param $class_name string Name of the class to load.
 */
spl_autoload_register( function( $class_name ) {

    if ( strpos( $class_name , 'UCFW\\' ) === 0 ) { // Only do autoload for our plugin files
        
        $class_file  = str_replace( array( '\\' , 'UCFW' . DIRECTORY_SEPARATOR ) , array( DIRECTORY_SEPARATOR , '' ) , $class_name ) . '.php';
        
        require_once plugin_dir_path( __FILE__ ) . $class_file;

    }

} );

/**
 * The main plugin class.
 */
class UCFW extends Abstract_Main_Plugin_Class {

    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
    */

    /**
     * Single main instance of Plugin UCFW plugin.
     *
     * @since 1.0.0
     * @access private
     * @var UCFW
     */
    private static $_instance;

    /**
     * Array of missing external plugins that this plugin is depends on.
     *
     * @since 1.0.0
     * @access private
     * @var array
     */
    private $_failed_dependencies;

    
    
    
    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
    */

    /**
     * UCFW constructor.
     *
     * @since 1.0.0
     * @access public
     */
    public function __construct() {

        if ( $this->_check_plugin_dependencies() !== true ) {

            // Display notice that plugin dependency ( WooCommerce ) is not present.
            add_action( 'admin_notices' , array( $this , 'missing_plugin_dependencies_notice' ) );

        } elseif ( $this->_check_plugin_dependency_version_requirements() !== true ) {

            // Display notice that some dependent plugin did not meet the required version.
            add_action( 'admin_notices' , array( $this , 'invalid_plugin_dependency_version_notice' ) );

        } else {

            // Lock 'n Load
            $this->_initialize_plugin_components();
            $this->_run_plugin();

        }

    }

    /**
     * Ensure that only one instance of URL Coupons for WooCommerce is loaded or can be loaded (Singleton Pattern).
     *
     * @since 1.0.0
     * @access public
     *
     * @return UCFW
     */
    public static function get_instance() {

        if ( !self::$_instance instanceof self )
            self::$_instance = new self();

        return self::$_instance;

    }

    /**
     * Check for external plugin dependencies.
     *
     * @since 1.0.0
     * @access private
     *
     * @return mixed Array if there are missing plugin dependencies, True if all plugin dependencies are present.
     */
    private function _check_plugin_dependencies() {

        // Makes sure the plugin is defined before trying to use it
        if ( !function_exists( 'is_plugin_active' ) )
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $this->failed_dependencies = array();
        
        if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

            $this->failed_dependencies[] = array(
                'plugin-key'       => 'woocommerce',
                'plugin-name'      => 'WooCommerce', // We don't translate this coz this is the plugin name
                'plugin-base-name' => 'woocommerce/woocommerce.php'
            );

        }

        return !empty( $this->failed_dependencies ) ? $this->failed_dependencies : true;

    }

    /**
     * Check plugin dependency version requirements.
     * 
     * @since 1.0.0
     * @access private
     * 
     * @return boolean True if plugin dependency version requirement is meet, False otherwise.
     */
    private function _check_plugin_dependency_version_requirements() {

        return true;

    }

    /**
     * Add notice to notify users that some plugin dependencies of this plugin is missing.
     *
     * @since 1.0.0
     * @access public
     */
    public function missing_plugin_dependencies_notice() {

        if ( !empty( $this->failed_dependencies ) ) {

            $admin_notice_msg = '';

            foreach ( $this->failed_dependencies as $failed_dependency ) {

                $failed_dep_plugin_file = trailingslashit( WP_PLUGIN_DIR ) . plugin_basename( $failed_dependency[ 'plugin-base-name' ] );

                if ( file_exists( $failed_dep_plugin_file ) )
                    $failed_dep_install_text = '<a href="' . wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $failed_dependency[ 'plugin-base-name' ] . '&amp;plugin_status=all&amp;s' , 'activate-plugin_' . $failed_dependency[ 'plugin-base-name' ] ) . '" title="' . __( 'Activate this plugin' , 'url-coupons-for-woocommerce' ) . '" class="edit">' . __( 'Click here to activate &rarr;' , 'url-coupons-for-woocommerce' ) . '</a>';
                else
                    $failed_dep_install_text = '<a href="' . wp_nonce_url( 'update.php?action=install-plugin&amp;plugin=' . $failed_dependency[ 'plugin-key' ] , 'install-plugin_' . $failed_dependency[ 'plugin-key' ] ) . '" title="' . __( 'Install this plugin' , 'url-coupons-for-woocommerce' ) . '">' . __( 'Click here to install from WordPress.org repo &rarr;' , 'url-coupons-for-woocommerce' ) . '</a>';
                
                $admin_notice_msg .= sprintf( __( '<br/>Please ensure you have the <a href="%1$s" target="_blank">%2$s</a> plugin installed and activated.<br/>' , 'url-coupons-for-woocommerce' ) , 'http://wordpress.org/plugins/' . $failed_dependency[ 'plugin-key' ] . '/' , $failed_dependency[ 'plugin-name' ] );
                $admin_notice_msg .= $failed_dep_install_text . '<br/>';

            } ?>

            <div class="notice notice-error">
                <p>
                    <?php _e( '<b>URL Coupons for WooCommerce</b> plugin missing dependency.<br/>' , 'url-coupons-for-woocommerce' ); ?>
                    <?php echo $admin_notice_msg; ?>
                </p>
            </div>

        <?php }

    }

    /**
     * Add notice to notify user that some plugin dependencies did not meet the required version for the current version of this plugin.
     *
     * @since 1.0.0
     * @access public
     */
    public function invalid_plugin_dependency_version_notice() {
        // Notice message here...
    }

    /**
     * Initialize plugin components.
     * 
     * @since 1.0.0
     * @access private
     */
    private function _initialize_plugin_components() {
        
        $plugin_constants = Plugin_Constants::get_instance();
        $helper_functions = Helper_Functions::get_instance( $plugin_constants );
        $coupon_endpoint  = URL_Coupon_Endpoint::get_instance( $this , $plugin_constants , $helper_functions );

        Bootstrap::get_instance( 
            $this , 
            $plugin_constants , 
            $helper_functions ,
            array( $coupon_endpoint , URL_Coupon::get_instance( $this , $plugin_constants , $helper_functions ) ) , // Activatables
            array( $coupon_endpoint ), // Initializables
            array( $coupon_endpoint ) // Deactivatables
        );
        Script_Loader::get_instance( $this , $plugin_constants , $helper_functions );
        URL_Coupon_Admin::get_instance( $this , $plugin_constants , $helper_functions );
        Coupon_Restriction::get_instance( $this , $plugin_constants , $helper_functions );
        Coupon_UI::get_instance( $this , $plugin_constants , $helper_functions );

    }
    
    /**
     * Run the plugin. ( Runs the various plugin components ).
     * 
     * @since 1.0.0
     * @access private
     */
    private function _run_plugin() {
        
        foreach ( $this->__all_models as $model )
            if ( $model instanceof Model_Interface )
                $model->run();
        
    }

}

/**
 * Returns the main instance of UCFW to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return UCFW Main instance of the plugin.
 */
function UCFW() {

    return UCFW::get_instance();

}

// Let's Roll!
$GLOBALS[ 'UCFW' ] = UCFW();
