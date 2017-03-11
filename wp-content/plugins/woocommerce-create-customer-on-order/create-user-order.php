<?php
/**
 * Plugin Name: WooCommerce Create Customer on Order
 * Plugin URI: https://codecanyon.net/item/create-customer-on-order-for-woocommerce/6395319?ref=cxThemes&utm_source=create%20customer%20on%20order&utm_campaign=commercial%20plugin%20upsell&utm_medium=plugins%20page%20view%20details
 * Description: Save time and simplify your life by having the ability to create a new Customer directly on the WooCommerce Order screen
 * Author: cxThemes
 * Author URI: https://codecanyon.net/item/create-customer-on-order-for-woocommerce/6395319?ref=cxThemes&utm_source=create%20customer%20on%20order&utm_campaign=commercial%20plugin%20upsell&utm_medium=plugins%20page%20view%20details
 * Version: 1.26
 * Text Domain: create-customer-order
 * Domain Path: /languages/
 *
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-ADD-USER-ORDER
 * @author    cxThemes
 * @category  WooCommerce
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Define Constants
 */
define( 'WC_CREATE_CUSTOMER_ON_ORDER_VERSION', '1.26' );
define( 'WC_CREATE_CUSTOMER_ON_ORDER_REQUIRED_WOOCOMMERCE_VERSION', 2.3 );
define( 'WC_CREATE_CUSTOMER_ON_ORDER_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WC_CREATE_CUSTOMER_ON_ORDER_URI', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'WC_CREATE_CUSTOMER_ON_ORDER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); // woocommerce-email-control/ec-email-control.php

/**
 * Update Check
 */
require 'includes/updates/cxthemes-plugin-update-checker.php';
$wc_create_customer_order_update = new CX_Create_Customer_On_Order_Plugin_Update_Checker( __FILE__,	'woocommerce-create-customer-on-order' );

/**
 * Check if WooCommerce is active, and is required WooCommerce version.
 */
if ( ! WC_Create_Customer_On_Order::is_woocommerce_active() || version_compare( get_option( 'woocommerce_version' ), WC_CREATE_CUSTOMER_ON_ORDER_REQUIRED_WOOCOMMERCE_VERSION, '<' ) ){
	add_action( 'admin_notices', array( 'WC_Create_Customer_On_Order', 'woocommerce_inactive_notice' ) );
	return;
}

/**
 * Includes
 */
include_once( 'includes/settings.php' );

/**
 * Main Class.
 */
class WC_Create_Customer_On_Order {

	private $id = 'woocommerce_create_customer_order';

	private static $instance;

	/**
	* Get Instance creates a singleton class that's cached to stop duplicate instances
	*/
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	/**
	* Construct empty on purpose
	*/
	private function __construct() {}

	/**
	* Init behaves like, and replaces, construct
	*/
	public function init(){

		// Check if WooCommerce is active, and is required WooCommerce version.
		if ( ! class_exists( 'WooCommerce' ) || version_compare( get_option( 'woocommerce_db_version' ), WC_CREATE_CUSTOMER_ON_ORDER_REQUIRED_WOOCOMMERCE_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_inactive_notice' ) );
			return;
		}
		
		// Localization
		add_action( 'init',    array( $this, 'load_translation' ) );
		
		// Enqueue Scripts
		// add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		
		// Render `Create Customer` Form.
		// add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'create_customer_on_order_page' ) ); // WC Order Page
		// add_action( 'woocommerce_bookings_after_create_booking_page', array( $this, 'create_customer_on_order_page' ) ); // WC Booking Plugin Page
		add_action( 'admin_footer', array( $this, 'create_customer_on_order_page' ) ); // All Pages - New Method.
		add_action( 'wp_footer', array( $this, 'create_customer_on_order_page' ) ); // All Pages - New Method.
		
		// `Create Customer` Form Ajax.
		add_action( 'wp_ajax_woocommerce_order_create_user', array( $this, 'woocommerce_create_customer_on_order' ) );
		add_action( 'wp_ajax_nopriv_woocommerce_order_create_user', array( $this, 'woocommerce_create_customer_on_order' ) );
		
		// WC Order page - Save address's to customer
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'update_customer_on_order_page' ) );
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_address_from_order_to_customer') );
		
		// WC Lost Password Page - Change it to behave like a first-time-login page.
		add_action( 'loop_start', array( $this, 'change_lost_password_title' ) ); // Change Lost Password Title
		add_filter( 'woocommerce_reset_password_message', array( $this, 'change_lost_password_text' ) ); // Change Lost Password Text.
		add_action( 'woocommerce_customer_reset_password', array( $this, 'update_customer_password_state' ) ); // Record that the user set their password.
		add_action( 'init', array( $this, 'successfully_activated_account' ) ); // Display notice that the account activation successful.
	}

	/**
	 * Localization
	 */
	public function load_translation() {
		
		// Domain ID - used in eg __( 'Text', 'pluginname' )
		$domain = 'create-customer-order';
		
		// get the languages locale eg 'en_US'
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		
		// Look for languages here: wp-content/languages/pluginname/pluginname-en_US.mo
		load_textdomain( $domain, WP_LANG_DIR . "/{$domain}/{$domain}-{$locale}.mo" ); // Don't mention this location in the docs - but keep it for legacy.
		
		// Look for languages here: wp-content/languages/plugins/pluginname-en_US.mo
		load_textdomain( $domain, WP_LANG_DIR . "/plugins/{$domain}-{$locale}.mo" );
		
		// Look for languages here: wp-content/languages/pluginname-en_US.mo
		load_textdomain( $domain, WP_LANG_DIR . "/{$domain}-{$locale}.mo" );
		
		// Look for languages here: wp-content/plugins/pluginname/languages/pluginname-en_US.mo
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . "/languages/" );
		
		
		if ( isset( $_REQUEST['cxccoo-test-user'] ) ) {
			
			$roles = WC_Create_Customer_On_Order::get_all_user_roles( 'names' );

			// If a filter empties the select - then make sure at least Customer is available.
			if ( empty( $roles ) ) $roles = array( 'customer' => 'Customer' );

			foreach ( $roles as $role_key => $role_label ) {
				if ( ! WC_Create_Customer_On_Order::current_user_is_equal_or_higher_than( $role_key ) ) $role_key = '';
				?>
				<div <?php if ( '' == $role_key ) echo 'class="cxccoo-user-capability-restricted"' ?> value="<?php echo $role_key; ?>" <?php if ( "customer" == $role_key ) { echo 'selected="selected"'; } ?> >
					<?php echo $role_label; ?> <?php if ( '' == $role_key ) _e( '(Your user capability prevents this)', 'create-customer-order' ) ?>
				</div>
				<?php
			}
		}
	}

	/**
	 * Add create customer form to Order Page
	 */
	public function create_customer_on_order_page() {
		
		// Check user is logged in, and has `edit_shop_order` capabilities.
		// if ( is_user_logged_in() && current_user_can( 'edit_shop_order' ) ) return false;
		
		// Enqueue Scripts - do this here so the scripts only ever enqued if this template is written to page.
		$this->admin_enqueue_scripts();
		
		?>
		<script id="cxccoo-create-customer-template" type="text/template">
			
			<div class="cxccoo-create-user-container">
				
				<a class="cxccoo-button cxccoo-create-user-main-button">
					<?php _e( 'Create New Customer', 'create-customer-order' ); ?>
					<span class="cxccoo-create-user-icon">&nbsp;</span>
				</a>
				
				<div class="cxccoo-create-form cxccoo-modal-form component-modal-hard-hide">
				
					<div class="cxccoo-modal-form-title">
						<?php _e( 'Create Customer', 'shop-as-customer' ); ?>
					</div>
					
					<div class="cxccoo-modal-form-content-inner">
						
						<a class="cxccoo-settings-icon dashicons dashicons-admin-generic" title="<?php echo esc_attr( __( 'Edit Settings', 'create-customer-order' ) ); ?>" href="<?php echo esc_url( admin_url( 'options-general.php?page=create_customer_settings' ) ); ?>">
							<!-- Settings -->
						</a>
					
						<div class="cxccoo-modal-form-row">
							<label for="cxccoo_first_name">
								<?php _e( 'First Name', 'create-customer-order' ); ?>
							</label>
							<input type="text" name="cxccoo_first_name" id="cxccoo_first_name" value="" />
						</div>
						
						<div class="cxccoo-modal-form-row">
							<label for="cxccoo_last_name">
								<?php _e( 'Last Name', 'create-customer-order' ); ?>
							</label>
							<input type="text" name="cxccoo_last_name" id="cxccoo_last_name" value="" />
						</div>
						
						<div class="cxccoo-modal-form-row">
							<label for="cxccoo_email_address">
								<?php _e( 'Email Address', 'create-customer-order' ); ?>
								<span class="cxccoo-required-field">*</span>
							</label>
							<input type="text" name="cxccoo_email_address" id="cxccoo_email_address" value="" />
							
						</div>
						
						<?php if ( 'yes' == cxccoo_get_option( 'cxccoo_user_name_selection' ) ) { ?>
							<div class="cxccoo-modal-form-row">
								<label for="cxccoo_username">
									<?php _e( 'Set Username (optional)', 'create-customer-order' ); ?>
								</label>
								<input type="text" name="cxccoo_username" id="cxccoo_username" value="" />
							</div>
						<?php } ?>
						
						<?php
						if ( 'yes' == cxccoo_get_option( 'cxccoo_user_role_selection' ) ) {
							
							$roles = WC_Create_Customer_On_Order::get_all_user_roles( 'names' );
							
							// If empty then make sure at least Customer is available.
							if ( empty( $roles ) ) $roles = array( 'customer' => 'Customer' );
							
							// Get the default role selection.
							$role_default = cxccoo_get_option( 'cxccoo_user_role_default' );
							
							if ( ! array_key_exists( $role_default, $roles ) ) $role_default = 'customer';
							?>
							<div class="cxccoo-modal-form-row">
								<label for="cxccoo_role"><?php _e( 'Role', 'create-customer-order' ); ?></label>
								<select name="cxccoo_role" id="cxccoo_role">
									<?php
									foreach ( $roles as $role_key => $role_label ) {
										if ( ! WC_Create_Customer_On_Order::current_user_is_equal_or_higher_than( $role_key ) ) $role_key = '';
										?>
										<option <?php if ( '' == $role_key ) echo 'class="cxccoo-user-capability-restricted"' ?> value="<?php echo $role_key; ?>" <?php if ( $role_default == $role_key ) { echo 'selected="selected"'; } ?> >
											<?php echo $role_label; ?> <?php if ( '' == $role_key ) _e( '(Your user capability prevents this)', 'create-customer-order' ) ?>
										</option>
										<?php
									}
									?>
								</select>
							</div>
							<?php
						}
						else {
							
							// Get the default role selection.
							$role_default = cxccoo_get_option( 'cxccoo_user_role_default' );
							?>
							<input type="hidden" name="cxccoo_role" id="cxccoo_role" value="<?php echo esc_attr( $role_default ); ?>" />
							<?php
						}
						?>
						
						<?php
						/*
						 * To auto pretick the disable registration email checkbox, add the following line to your theme functions.php
						 * add_filter( 'woocommerce_create_customer_disable_email', '__return_true' );
						 */
						$disable_email = apply_filters( 'woocommerce_create_customer_disable_email', false );
						?>
						<div class="cxccoo-modal-form-row cxccoo-modal-form-row-tall">
							<label for="cxccoo_disable_email">
								<input type="checkbox" <?php echo ( $disable_email ) ? 'checked="checked"' : ''; ?> name="cxccoo_disable_email" id="cxccoo_disable_email" value="yes" />
								<?php _e( 'Disable customer registration email', 'create-customer-order' ); ?>
							</label>
						</div>
						
						<div class="cxccoo-modal-form-row cxccoo-modal-form-row-buttons">
							
							<a class="cxccoo-button cxccoo-create-user-form-cancel">
								<?php _e( 'Cancel', 'create-customer-order' ); ?>
							</a>
							
							<a class="cxccoo-button cxccoo-button-primary cxccoo-button-align-right cxccoo-create-user-form-submit">
								<?php _e( 'Create Customer', 'create-customer-order' ); ?>
							</a>
							
						</div>
					
					</div>
					
				</div>
				
			</div>
		
		</script>

		<?php
		// Old Method - is rather done in the .js now.
		/*wc_enqueue_js("
			
			// Move on WooCommerce Order page.
			// jQuery('.cxccoo-modal-form').insertAfter( jQuery('#order_data #customer_user').parents('.form-field:eq(0)') );
			
			// Move on WooCommerce Booking Plugin Order page.
			// jQuery('.wc_booking_page_create_booking .cxccoo-modal-form').insertAfter( jQuery('.wc_booking_page_create_booking .wc-customer-search.enhanced') );
			
			// jQuery('.cxccoo-create-user-container').insertBefore( '.wc-customer-search' );
			// jQuery('.wc-customer-search').parent().append( jQuery('.cxccoo-create-user-container') );
		");*/
		
	}

	/**
	 * Add Save to customer checkboxes above Billing and Shipping Details on Order page
	 */
	public function update_customer_on_order_page() {
		?>
		<label class="cxccoo-order-save-actions cxccoo-save-billing-address">
			<?php _e( "Save to Customer", 'create-customer-order' ); ?>
			<input type="checkbox" name="cxccoo-save-billing-address-input" id="cxccoo-save-billing-address-input" value="true" />
		</label>
		<label class="cxccoo-order-save-actions cxccoo-save-shipping-address">
			<?php _e( "Save to Customer", 'create-customer-order' ); ?>
			<input type="checkbox" name="cxccoo-save-shipping-address-input" id="cxccoo-save-shipping-address-input" value="true" />
		</label>
		<?php
	}

	/**
	 * Include admin scripts
	 */
	public function admin_enqueue_scripts() {
		
		/*
		$screen = get_current_screen();
		if (
				'shop_order' == $screen->id ||
				'woocommerce_page_create_customer_settings' == $screen->id || // Old settings page location
				'settings_page_create_customer_settings' == $screen->id || // New settings page location
				'wc_booking_page_create_booking' == $screen->id || // Bookings plugin.
				'shop_subscription' == $screen->id // Subscription plugin.
			) {*/
			
			wp_enqueue_style(
				'woocommerce-create-customer-order',
				WC_CREATE_CUSTOMER_ON_ORDER_URI . '/assets/css/create-user-on-order.css',
				array(),
				WC_CREATE_CUSTOMER_ON_ORDER_VERSION,
				'screen'
			);
			
			wp_enqueue_script(
				'woocommerce-create-customer-order',
				WC_CREATE_CUSTOMER_ON_ORDER_URI . '/assets/js/create-user-on-order.js',
				array( 'jquery', 'jquery-blockui' ),
				WC_CREATE_CUSTOMER_ON_ORDER_VERSION
			);
			wp_localize_script( 'woocommerce-create-customer-order', 'woocommerce_create_customer_order_params', array(
				'plugin_url'                => WC()->plugin_url(),
				'ajax_url'                  => admin_url( 'admin-ajax.php' ),
				'create_customer_nonce'     => wp_create_nonce( 'create-customer' ),
				'home_url'                  => get_home_url(),
				'msg_email_exists'          => __( 'Email Address already exists', 'create-customer-order'),
				'msg_email_empty'           => __( 'Please enter an email address', 'create-customer-order'),
				'msg_email_invalid'         => __( 'Invalid Email Address', 'create-customer-order'),
				'msg_email_exists_username' => __( 'This email address already exists as another users Username', 'create-customer-order'),
				'msg_username_invalid'      => __( 'Invalid Username', 'create-customer-order'),
				'msg_success'               => __( 'User created and linked to this order', 'create-customer-order'),
				'msg_email_valid'           => __( 'Please enter a valid email address', 'create-customer-order'),
				'msg_successful'            => __( 'Success', 'create-customer-order'),
				'msg_error'                 => __( 'Error', 'create-customer-order'),
				'msg_role'                  => __( 'Your user role does not have the capability to create a user with this role', 'create-customer-order'),
				'allow_role_selection'      => cxccoo_get_option( 'cxccoo_user_role_selection' ),
				'select_search_inputs'      => implode( ', ', apply_filters( 'cxccoo_customer_search_inputs', array(
					
					// WooCommerce.
					'.post-type-shop_order #order_data input.wc-customer-search',
					
					// WooCommerce Bookings.
					'.post-type-wc_booking .form-table input.wc-customer-search',
					'.post-type-wc_booking #booking_data input.wc-customer-search',
					
					// WooCommerce Subscriptions.
					'.post-type-shop_subscription #order_data input.wc-customer-search',
					
					// Shop as Customer for WooCommerce.
					'.cxsac-switch-form input.wc-customer-search',
				) ) ),
			) );
			
			/**
			 * Fontello.
			 */
			wp_enqueue_style(
				'cxccoo-icon-font',
				WC_CREATE_CUSTOMER_ON_ORDER_URI . '/assets/fontello/css/cxccoo-icon-font.css',
				array(),
				WC_CREATE_CUSTOMER_ON_ORDER_VERSION
			);
			
		// }
	}

	/**
	* Create customer via ajax on Order page
	*
	* @access public
	* @return void
	*/
	public function woocommerce_create_customer_on_order() {
		global $wpdb;

		check_ajax_referer( 'create-customer', 'security' );

		$email_address = ( isset( $_POST['email_address'] ) ) ? $_POST['email_address'] : '';
		$first_name    = ( isset( $_POST['first_name'] ) ) ? sanitize_text_field( $_POST['first_name'] ) : '';
		$last_name     = ( isset( $_POST['last_name'] ) ) ? sanitize_text_field( $_POST['last_name'] ) : '';
		$username      = ( isset( $_POST['username'] ) ) ? sanitize_text_field( $_POST['username'] ) : '';
		$user_role     = ( isset( $_POST['user_role'] ) ) ? sanitize_text_field( $_POST['user_role'] ) : '';
		$disable_email = ( isset( $_POST['disable_email'] ) && 'true' === $_POST['disable_email'] ) ? true : false;
		
		if ( '' == $first_name && '' == $last_name ) {
			$display_name = substr( $email_address, 0, strpos( $email_address, '@' ) );
		}
		else {
			$display_name = trim( $first_name . " " . $last_name );
		}

		$error = false;
		
		// Email validation
		if ( empty( $email_address ) ) {
			echo json_encode( array( "error_message" => "email_empty" ) );
			die();
		}
		if ( ! is_email( $email_address ) ) {
			echo json_encode( array( "error_message" => "email_invalid" ) );
			die();
		}
		if ( email_exists( $email_address ) ) {
			echo json_encode( array( "error_message" => "email_exists" ) );
			die();
		}
		
		// Username validation
		
		if ( empty( $username ) ) {
			
			// If no username then use the email address.
			$username = $email_address;
			
			if ( ! validate_username( $username ) ) {
				
				// The email is not valid username e.g. test!@test.com
				// so sanitise it and grab the first part e.g. test.
				$username = sanitize_user( $username, TRUE );
				$username = substr( $username, 0, strpos( $username, '@' ) );
			}
			
			if ( username_exists( $username ) ) {
				
				// The previous username exists so try combine Firstname Lastname.
				if ( '' != $first_name || '' != $last_name ) {
					$username = trim( $first_name . ' ' . $last_name );
				}
			}
			
			if ( '' == $username || username_exists( $username ) ){
				
				// The previous username is empty or exists.
				// so grab all we have - the beginning and middle part of the email
				// e.g. testtest
				$username = sanitize_user( $email_address, TRUE );
				$username = substr( $username, 0, strrpos( $username, '.' ) );
				$username = str_replace( '@', '', $username );
			}
			
			// We have no more options so proceed with what we have.
		}
		
		// echo json_encode( array( "error_message" => "email_empty", "error_message_testyyy" => $username ) );
		// die();
		
		if ( ! validate_username( $username ) ) {
			echo json_encode( array( "error_message" => "username_invalid" ) );
			die();
		}
		if ( username_exists( $username ) ) {
			echo json_encode( array( "error_message" => "username_exists" ) );
			die();
		}
		
		// Role validation
		if ( 'yes' == cxccoo_get_option( 'cxccoo_user_role_selection' ) ) {
			if ( ! $this->current_user_is_equal_or_higher_than( $user_role ) ) {
				echo json_encode( array( "error_message" => "role_unable" ) );
				die();
			}
		}
		else {
			$user_role = cxccoo_get_option( 'cxccoo_user_role_default' );
		}
		
		// Generate password.
		$password = wp_generate_password();
		
		// Create the new user.
		$user_id = wp_create_user( $username, $password, $email_address );

		// Update the new user.
		wp_update_user( array (
			'ID'           => $user_id,
			'first_name'   => $first_name,
			'last_name'    => $last_name,
			'role'         => ( 'super_admin' == $user_role ) ? 'administrator' : $user_role,
			'display_name' => $display_name,
			'nickname'     => $display_name,
		) ) ;
		
		// Super Admin is not a role, it must be set this way, using `grant_super_admin()`.
		if ( 'super_admin' == $user_role ) {
			grant_super_admin( $user_id );
		}

		// Set the password.
		update_user_meta( $user_id, "create_customer_on_order_password", true );

		// Set the other info - billing
		update_user_meta( $user_id, "billing_first_name", $first_name );
		update_user_meta( $user_id, "billing_last_name", $last_name );
		update_user_meta( $user_id, "billing_email", $email_address );

		// Set the other info  - shipping
		update_user_meta( $user_id, "shipping_first_name", $first_name );
		update_user_meta( $user_id, "shipping_last_name", $last_name );
		
		// Re-get the user data.
		$user_data = get_user_by( 'login', $username );
		
		
		// Save the 'user_activation_key' that the user uses to set/reset their password.
		if ( version_compare( get_option( 'woocommerce_version' ), '2.6.5', '>=' ) ) {
			
			// Get password reset key (function introduced in WordPress 4.4, WC started using in 2.6.5).
			$key = get_password_reset_key( $user_data );
		}
		else {
			
			// redefining user_login ensures we return the right case in the email
			$user_login = $user_data->user_login;

			$key = wp_generate_password( 20, false );

			// Now insert the key, hashed, into the DB.
			if ( empty( $wp_hasher ) ) {
				require_once ABSPATH . 'wp-includes/class-phpass.php';
				$wp_hasher = new PasswordHash( 8, true );
			}

			$hashed = $wp_hasher->HashPassword( $key );
			
			$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );
		}
		
		// Generate lost-password link.
		$lost_password_link = esc_url_raw( add_query_arg(
			array(
				'key'   => $key,
				'login' => rawurlencode( $username ),
			),
			wp_lostpassword_url()
		) );
		
		if ( ! $disable_email ) {
			$this->send_register_email( $email_address, $lost_password_link, $username );
		}
		
		// Echo JSON return.
		echo json_encode( array(
			'user_id'  => $user_id,
			'username' => $username,
		) );
		
		die();
	}
	
	public function can_current_user_create_role( $role = '' ) {
		
		if ( '' == $role ) {
			return FALSE;
		}
		elseif ( 'administrator' == $role && !current_user_can( 'manage_options' ) ) {
			return FALSE;
		}
		elseif ( 'shop_manager' == $role && !current_user_can( 'edit_posts' ) ) {
			return FALSE;
		}
		elseif ( !current_user_can( 'edit_posts' ) ) {
			return FALSE;
		}
		
		// If we get here user shold not be able to do this.
		return TRUE;
	}
	
	/**
	 * Test a users capability
	 */
	public static function current_user_is_equal_or_higher_than( $role_check = 'administrator' ) {
		
		// Get all the user roles.
		$heirarchy = self::get_all_user_roles( 'heirarchy' );
		
		// Get the current user info - so we can get his roles.
		$user_info = wp_get_current_user();
		$user_info->ID;
		$user_info->roles;
		
		$passed = FALSE;
		foreach ( $user_info->roles as $role ) {
			
			// Skip if these role types are not accounted for in our role Setting
			if ( ! isset( $heirarchy[$role] ) || ! isset( $heirarchy[$role_check] ) ) continue;
			
			if ( $heirarchy[$role_check] >= $heirarchy[$role] ) $passed = TRUE;
		}
		
		// Special check for Super Admin.
		if ( 'super_admin' == $role_check && current_user_can( 'manage_network' ) ) {
			$passed = TRUE;
		}
		
		return $passed;
	}
	
	
	/**
	 * Returns an array of All the user roles we've saved, in order, with a heirarchical number as a value.
	 *
	 * @param    string   $format   heirarchy|names.
	 * @return   array              Roles in either of the above formats.
	 */
	public static function get_all_user_roles( $format = 'heirarchy' ) {
		
		// Get the saved settings.
		$roles = cxccoo_get_option( 'cxccoo_user_role_heirarchy' );
		
		if ( '' == trim( $roles ) ) {
			$roles = cxccoo_get_default( 'cxccoo_user_role_heirarchy' );
		}
		
		$roles = explode( "\r\n", $roles );
		
		// Explode each line in the textarea into an array, noting the heirarchy as a number.
		$index = 0;
		$new_roles = array();
		foreach ( $roles as $role_key => $role_value ) {
			$new_roles[$role_value] = $index;
			$index++;
		}
		$roles = $new_roles;
		
		// Explode the lines that have `|`, allowing the user to have roles that are on the same level.
		$index = 0;
		$new_roles = array();
		foreach ( $roles as $role_key => $role_value ) {
			$new_keys = explode( '|', $role_key );
			foreach ( $new_keys as $new_key_value ) {
				$new_key_value = trim( $new_key_value ); // Trim the value incase the user typed spaces around the `|`
				$new_roles[$new_key_value] = $index;
			}
			$index++;
		}
		$roles = $new_roles;
		
		if ( 'heirarchy' == $format ) {
			
			// Get `role_key => Role Name` format.
			
			return $roles;
		}
		else {
			
			// Get `role_key => role_heirarchy` format.
			
			$role_names = wp_roles()->role_names;
			
			// If multisite then make this option available.
			if ( is_multisite() )
				$role_names = array( 'super_admin' => 'Super Admin' ) + $role_names;
			
			$new_roles = array();
			foreach ( $roles as $role_key => $role_heirarchy ) {
				if ( isset( $role_names[$role_key] ) )
					$new_roles[$role_key] = $role_names[$role_key];
			}
			
			return $new_roles;
		}
	}
	
	/**
	 * Change Lost Password page title.
	 */
	function change_lost_password_title( $array ){
		global $wp_query;
		
		if ( $array === $wp_query ) {
			// Is main loop, so add title.
			add_filter( 'the_title', array( $this, 'change_lost_password_title_text' ) );
		}
		else {
			// Is not main loop, so remove title.
			remove_filter( 'the_title', array( $this, 'change_lost_password_title_text' ) );
		}
	}
	public function change_lost_password_title_text( $page_title ) {

		$is_lost_pass_page = false;

		// Check if is lost pass page
		if ( function_exists( 'wc_get_endpoint_url' ) ) { // Above WC 2.1
			if ( is_wc_endpoint_url( 'lost-password' ) ) $is_lost_pass_page = true;
		}
		else {
			if ( is_page( woocommerce_get_page_id( 'lost_password' ) ) ) $is_lost_pass_page = true;
		}

		// Only do this is lost pass page, and that we have a login to check against
		if ( $is_lost_pass_page && ( $user = $this->get_login_user() ) ){
			
			$password_not_changed = get_user_meta( $user->ID, 'create_customer_on_order_password', true );
			
			if ( $password_not_changed ) {
				
				$page_title = __( 'Set your Password', 'create-customer-order' );
			}
		}

		return $page_title;
	}

	/**
	 * Change Lost Password page text.
	 */
	public function change_lost_password_text( $msg ) {
		
		if ( $user = $this->get_login_user() ) {
			
			$password_not_changed = get_user_meta( $user->ID, 'create_customer_on_order_password', true );
			
			if ( $password_not_changed ) {
				
				$msg = __( 'As this is your first time logging in, please set your password.', 'create-customer-order');
			}
		}

		return $msg;
	}

	/**
	 * After customer submits reset-password is redirect to my-accounts page, and record that the user has set his new password.
	 */
	public function update_customer_password_state( $user ) {
		
		$password_not_changed = get_user_meta( $user->ID, 'create_customer_on_order_password', true );

		if ( $password_not_changed ) {
			
			delete_user_meta( $user->ID, 'create_customer_on_order_password' );
			?>
			<script type="text/javascript">
				window.location = '<?php echo add_query_arg( array( 'successfully-activated-account' => 'yes' ), get_permalink( woocommerce_get_page_id ( 'myaccount' ) ) ); ?>';
			</script>
			<?php

			die;
		}
	}
	
	/**
	 * Disiplay notice after successfully activated account.
	 */
	public function successfully_activated_account() {
		
		if ( isset( $_GET['successfully-activated-account'] ) && 'yes' == $_GET['successfully-activated-account'] ) {
			
			wc_add_notice( __( 'You have successfully activated your account. Please login with your email address and new password', 'create-customer-order' ) );
		}
	}
	
	/**
	 * After customer submits reset-password is redirect to my-accounts page and account set to standard behaviour.
	 */
	public function get_login_user() {
		
		$user = FALSE;
		
		if ( isset( $_COOKIE[ 'wp-resetpass-' . COOKIEHASH ] ) && 0 < strpos( $_COOKIE[ 'wp-resetpass-' . COOKIEHASH ], ':' ) ) {
			
			list( $rp_login, $rp_key ) = array_map( 'wc_clean', explode( ':', wp_unslash( $_COOKIE[ 'wp-resetpass-' . COOKIEHASH ] ), 2 ) );
			$user = WC_Shortcode_My_Account::check_password_reset_key( $rp_key, $rp_login );
		}
		elseif ( isset( $_GET['login'] ) ) {
			
			$username = esc_attr( $_GET['login'] );
			$user = get_user_by( 'login', $username );
		}
		
		return $user;
	}

	/**
	 * Send custom register email with lost password reset link
	 */
	public function send_register_email( $email_address, $link, $username ) {

		// Email Heading
		$email_heading = __("Your account has been created", 'create-customer-order');
		apply_filters( "woocommerce_create_customer_order_email_title", $email_heading );

		// Email Subject
		$email_subject = __("Your account on %s", 'create-customer-order');
		$email_subject = sprintf( $email_subject, get_bloginfo("name") );
		apply_filters("woocommerce_create_customer_order_email_subject", $email_subject);

		// Email Headers
		$headers[] = 'From: '.get_option( 'woocommerce_email_from_name' ).' <'.get_option( 'woocommerce_email_from_address' ).'>';
		add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

		// Email Message
		$email_message = __("Hi, we've created an account for you on our site.

<strong>Username:</strong> %s
<strong>Password:</strong> Please %s to set your new password

Copy and paste this link into your browser if you are having trouble with the above password link %s

Thank-you
%s", 'create-customer-order');

		$email_message = nl2br( sprintf(
			$email_message,
			//get_bloginfo( 'name' ),
			$username,
			'<a href="' . $link . '">' . __( "click here", 'create-customer-order' ) . '</a>',
			$link,
			get_bloginfo("name")
		) );

		// Email - Start
		ob_start();

		// This is necessary so that the following actions are hooked by WooCommerce.
		$mailer = WC()->mailer();
		
		// The best way to call WC header
		do_action( 'woocommerce_email_header', $email_heading, NULL );

		echo $email_message;
		
		// The best way to call WC footer
		do_action( 'woocommerce_email_footer', $email_heading, NULL );

		// Email Message - End
		$email_message = ob_get_clean();
		
		// Allow people to filter our message.
		apply_filters( 'woocommerce_create_customer_order_email_msg', $email_message );

		// Send Email
		$status = wc_mail( $email_address, $email_subject, $email_message, $headers );

		remove_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
	}

	/**
	 * WP Mail Filter - Set email body as HTML
	 */
	public function set_html_content_type() {
		return 'text/html';
	}

	/**
	 * Save Billing and Shipping details to the customer when checkboxes are checked on Order page
	 */
	public function save_address_from_order_to_customer( $post_id, $post=null ) {
		$user_id = absint( $_POST['customer_user'] );

		$save_to_billing_address = ( isset( $_POST['cxccoo-save-billing-address-input'] ) ) ? $_POST['cxccoo-save-billing-address-input'] : '';
		$save_to_shipping_address = ( isset( $_POST['cxccoo-save-shipping-address-input'] ) ) ? $_POST['cxccoo-save-shipping-address-input'] : '';

		if ($save_to_billing_address == 'true') {
			update_user_meta( $user_id, 'billing_first_name', woocommerce_clean( $_POST['_billing_first_name'] ) );
			update_user_meta( $user_id, 'billing_last_name', woocommerce_clean( $_POST['_billing_last_name'] ) );
			update_user_meta( $user_id, 'billing_company', woocommerce_clean( $_POST['_billing_company'] ) );
			update_user_meta( $user_id, 'billing_address_1', woocommerce_clean( $_POST['_billing_address_1'] ) );
			update_user_meta( $user_id, 'billing_address_2', woocommerce_clean( $_POST['_billing_address_2'] ) );
			update_user_meta( $user_id, 'billing_city', woocommerce_clean( $_POST['_billing_city'] ) );
			update_user_meta( $user_id, 'billing_postcode', woocommerce_clean( $_POST['_billing_postcode'] ) );
			update_user_meta( $user_id, 'billing_country', woocommerce_clean( $_POST['_billing_country'] ) );
			update_user_meta( $user_id, 'billing_state', woocommerce_clean( $_POST['_billing_state'] ) );
			update_user_meta( $user_id, 'billing_email', woocommerce_clean( $_POST['_billing_email'] ) );
			update_user_meta( $user_id, 'billing_phone', woocommerce_clean( $_POST['_billing_phone'] ) );
		}

		if ($save_to_shipping_address == 'true') {
			update_user_meta( $user_id, 'shipping_first_name', woocommerce_clean( $_POST['_shipping_first_name'] ) );
			update_user_meta( $user_id, 'shipping_last_name', woocommerce_clean( $_POST['_shipping_last_name'] ) );
			update_user_meta( $user_id, 'shipping_company', woocommerce_clean( $_POST['_shipping_company'] ) );
			update_user_meta( $user_id, 'shipping_address_1', woocommerce_clean( $_POST['_shipping_address_1'] ) );
			update_user_meta( $user_id, 'shipping_address_2', woocommerce_clean( $_POST['_shipping_address_2'] ) );
			update_user_meta( $user_id, 'shipping_city', woocommerce_clean( $_POST['_shipping_city'] ) );
			update_user_meta( $user_id, 'shipping_postcode', woocommerce_clean( $_POST['_shipping_postcode'] ) );
			update_user_meta( $user_id, 'shipping_country', woocommerce_clean( $_POST['_shipping_country'] ) );
			update_user_meta( $user_id, 'shipping_state', woocommerce_clean( $_POST['_shipping_state'] ) );
		}
	}
	
	/**
	 * Is WooCommerce active.
	 */
	public static function is_woocommerce_active() {
		
		$active_plugins = (array) get_option( 'active_plugins', array() );
		
		if ( is_multisite() )
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		
		return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
	}

	/**
	 * Display Notifications on specific criteria.
	 *
	 * @since	2.14
	 */
	public static function woocommerce_inactive_notice() {
		if ( current_user_can( 'activate_plugins' ) ) :
			if ( !class_exists( 'WooCommerce' ) ) :
				?>
				<div id="message" class="error">
					<p>
						<?php
						printf(
							__( '%sCreate Customer on Order for WooCommerce needs WooCommerce%s %sWooCommerce%s must be active for Create Customer on Order to work. Please install & activate WooCommerce.', 'create-customer-order' ),
							'<strong>',
							'</strong><br>',
							'<a href="http://wordpress.org/extend/plugins/woocommerce/" target="_blank" >',
							'</a>'
						);
						?>
					</p>
				</div>
				<?php
			elseif ( version_compare( get_option( 'woocommerce_db_version' ), WC_CREATE_CUSTOMER_ON_ORDER_REQUIRED_WOOCOMMERCE_VERSION, '<' ) ) :
				?>
				<div id="message" class="error">
					<!--<p style="float: right; color: #9A9A9A; font-size: 13px; font-style: italic;">For more information <a href="http://cxthemes.com/plugins/update-notice.html" target="_blank" style="color: inheret;">click here</a></p>-->
					<p>
						<?php
						printf(
							__( '%sCreate Customer on Order for WooCommerce is inactive%s This version of Create Customer on Order requires WooCommerce %s or newer. For more information about our WooCommerce version support %sclick here%s.', 'create-customer-order' ),
							'<strong>',
							'</strong><br>',
							WC_CREATE_CUSTOMER_ON_ORDER_REQUIRED_WOOCOMMERCE_VERSION,
							'<a href="https://helpcx.zendesk.com/hc/en-us/articles/202241041/" target="_blank" style="color: inheret;" >',
							'</a>'
						);
						?>
					</p>
					<div style="clear:both;"></div>
				</div>
				<?php
			endif;
		endif;
	}

}

/**
 * Instantiate plugin.
 */

if( !function_exists( 'init_wc_create_customer_on_order' ) ) {
	function init_wc_create_customer_on_order() {

		global $wc_create_customer_on_order;

		$wc_create_customer_on_order = WC_Create_Customer_On_Order::get_instance();
	}
}
add_action( 'plugins_loaded', 'init_wc_create_customer_on_order' );
