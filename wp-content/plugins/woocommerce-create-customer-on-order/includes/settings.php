<?php


/**
 * Add a submenu item to the WooCommerce menu
 */
add_action( 'admin_menu', 'cxccoo_admin_menu' );
function cxccoo_admin_menu() {

	add_submenu_page(
		'options-general.php',
		// 'woocommerce',
		__( 'Create Customer on Order', 'create-customer-order' ),
		__( 'Create Customer on Order', 'create-customer-order' ),
		'manage_options', // 'manage_networks',
		'create_customer_settings',
		'cxccoo_admin_page'
	);
	
}

function cxccoo_admin_page() {
	
	// Save settings if data has been posted
	if ( ! empty( $_POST ) )
		cxccoo_save_settings();

	// Add any posted messages
	if ( ! empty( $_GET['wc_error'] ) )
		//self::add_error( stripslashes( $_GET['wc_error'] ) );

	 if ( ! empty( $_GET['wc_message'] ) )
		//self::add_message( stripslashes( $_GET['wc_message'] ) );

	//self::show_messages();
	?>
	<form method="post" id="mainform" action="" enctype="multipart/form-data">
		<div class="cxccoo-wrap-settings woocommerce">
			
			<h1><?php _e( 'Create Customer on Order', 'create-customer-order' ); ?><span class="dashicons dashicons-arrow-right"></span><?php _e( 'Settings', 'create-customer-order' ); ?></h1>
			
			<?php
			$settings = cxccoo_get_settings();
			WC_Admin_Settings::output_fields( $settings );
			?>
			
			<p class="submit">
				<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'create-customer-order' ); ?>" />
				<?php wp_nonce_field( 'woocommerce-settings' ); ?>
			</p>
			
		</div>
	</form>
	
	<?php
}

/**
 * Get settings array
 *
 * @return array
 */
function cxccoo_get_settings() {
	
	// Get all roles.
	$wp_roles = wp_roles();
	
	// Get all the roles that have 'edit_shop_order' role - they are the ones that could possibly use Create Customer on Order.
	$available_users = array();
	foreach ( $wp_roles->roles as $role_key => $role_value ) {
		
		if ( isset( $role_value['capabilities']['edit_shop_order'] ) )
			$available_users[$role_key] = $role_value['name'];
	}
			
	$settings = array(
		
		// --------------------
		
		array(
			'id'   => 'cxccoo_settings',
			'name' => __( 'General Settings', 'create-customer-order' ),
			'type' => 'title',
			'desc' => '',
		),
		array(
			'id'      => 'cxccoo_user_can_create_customers',
			'name'    => __( 'Minimum User Role', 'create-customer-order' ),
			'desc'    => __( 'Set the minimum user role which is allowed to create users.', 'create-customer-order' ),
			'type'    => 'select',
			'options' => array(
				'super_admin' => 'Super Admin',
				'administrator' => 'Administrator',
				'shop_manager' => 'Shop Manager',
			),
			'options' => $available_users,
			'default' => '0',
		),
		array(
			'id'   => 'cxccoo_settings',
			'type' => 'sectionend',
		),
		
		
		array(
			'id'   => 'cxccoo_feature_settings_title',
			'name' => __( "Features", 'create-customer-order' ),
			'desc' => __( "Choose what features are available on the Create Customer form on the WooCommerce Order page.", 'create-customer-order' ),
			'type' => 'title',
		),
		array(
			'id'       => 'cxccoo_user_name_selection',
			'name'     => __( 'Edit Username', 'create-customer-order' ),
			'label'    => '',
			'desc'     => __( '', 'create-customer-order' ),
			'desc_tip' => __( "Show a field to set the new user's username. If unchecked, then their email address will be their username.", 'create-customer-order' ),
			'type'     => 'checkbox',
			'default'  => 'no',
		),
		array(
			'id'       => 'cxccoo_user_role_selection',
			'name'     => __( 'User Role Selection', 'create-customer-order' ),
			'label'    => '',
			'desc'     => __( '', 'create-customer-order' ),
			'desc_tip' => __( 'Check this to enable setting user roles other than Customer for the new user.', 'create-customer-order' ),
			'type'     => 'checkbox',
			'default'  => 'no',
		),
		array(
			'id'      => 'cxccoo_user_role_heirarchy',
			'name'    => __( 'User Role Hierarchy', 'create-customer-order' ),
			'desc'    => __( 'In order to use the User Role Selection feature, we need to know the hierarchy of your user roles, so we can prevent less privileged users creating more privileged users. Please make sure all your role types are represented here in order from most privileged to least privileged. Roles on the same row, separated using pipe "|", have equal privileges. e.g. "custom_role" role has the same capabilities as the "shop_manager" (if you are using custom roles). If you are not using any custom user roles then you can leave this as is.', 'create-customer-order' ),
			'type'    => 'textarea',
			'css'     => 'height: 160px;',
			'default' => "super_admin
administrator
shop_manager | custom_role
customer
subscriber",
			'placeholder' => "super_admin
administrator
shop_manager | custom_role
customer
subscriber",
		),
		array(
			'id'          => 'cxccoo_user_role_default',
			'name'        => __( 'Default User Role', 'create-customer-order' ),
			'desc'        => __( 'Set the default user role for all new users created. Most likely "customer" (all lowercase)', 'create-customer-order' ),
			'type'        => 'text',
			'default'     => 'customer',
			'placeholder' => 'customer',
		),
		array(
			'id'   => 'cxccoo_feature_settings_title',
			'type' => 'sectionend',
		),
		
	);

	return $settings;
}

/**
 * Save Settings.
 *
 * Loops though the woocommerce options array and outputs each field.
 *
 * @access public
 * @return bool
 */
function cxccoo_save_settings() {
	
	if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'woocommerce-settings' ) )
		die( __( 'Action failed. Please refresh the page and retry.', 'create-customer-order' ) );
	
	$settings = cxccoo_get_settings();
	
	if ( empty( $_POST ) )
		return false;
	
	// Options to update will be stored here
	$update_options = array();

	// Loop options and get values to save
	foreach ( $settings as $value ) {

		if ( ! isset( $value['id'] ) )
			continue;

		$type = isset( $value['type'] ) ? sanitize_title( $value['type'] ) : '';

		// Get the option name
		$option_value = null;

		switch ( $type ) {

			// Standard types
			case "checkbox" :

				if ( isset( $_POST[ $value['id'] ] ) ) {
					$option_value = 'yes';
				} else {
					$option_value = 'no';
				}

			break;

			case "textarea" :

				if ( isset( $_POST[$value['id']] ) ) {
					$option_value = wp_kses_post( trim( stripslashes( $_POST[ $value['id'] ] ) ) );
				} else {
					$option_value = '';
				}

			break;

			case "text" :
			case 'email':
			case 'number':
			case "select" :
			case "color" :
			case 'password' :
			case "single_select_page" :
			case "single_select_country" :
			case 'radio' :

				if ( $value['id'] == 'woocommerce_price_thousand_sep' || $value['id'] == 'woocommerce_price_decimal_sep' ) {

					// price separators get a special treatment as they should allow a spaces (don't trim)
					if ( isset( $_POST[ $value['id'] ] )  ) {
						$option_value = wp_kses_post( stripslashes( $_POST[ $value['id'] ] ) );
					} else {
						$option_value = '';
					}

				} elseif ( $value['id'] == 'woocommerce_price_num_decimals' ) {

					// price separators get a special treatment as they should allow a spaces (don't trim)
					if ( isset( $_POST[ $value['id'] ] )  ) {
						$option_value = absint( $_POST[ $value['id'] ] );
					} else {
					   $option_value = 2;
					}

				} elseif ( $value['id'] == 'woocommerce_hold_stock_minutes' ) {

					// Allow > 0 or set to ''
					if ( ! empty( $_POST[ $value['id'] ] )  ) {
						$option_value = absint( $_POST[ $value['id'] ] );
					} else {
						$option_value = '';
					}

					wp_clear_scheduled_hook( 'woocommerce_cancel_unpaid_orders' );

					if ( $option_value != '' )
						wp_schedule_single_event( time() + ( absint( $option_value ) * 60 ), 'woocommerce_cancel_unpaid_orders' );

				} else {

				   if ( isset( $_POST[$value['id']] ) ) {
						$option_value = woocommerce_clean( stripslashes( $_POST[ $value['id'] ] ) );
					} else {
						$option_value = '';
					}

				}

			break;

			// Special types
			case "multiselect" :
			case "multi_select_countries" :

				// Get countries array
				if ( isset( $_POST[ $value['id'] ] ) )
					$selected_countries = array_map( 'wc_clean', array_map( 'stripslashes', (array) $_POST[ $value['id'] ] ) );
				else
					$selected_countries = array();

				$option_value = $selected_countries;

			break;

			case "image_width" :

				if ( isset( $_POST[$value['id'] ]['width'] ) ) {

					$update_options[ $value['id'] ]['width']  = woocommerce_clean( stripslashes( $_POST[ $value['id'] ]['width'] ) );
					$update_options[ $value['id'] ]['height'] = woocommerce_clean( stripslashes( $_POST[ $value['id'] ]['height'] ) );

					if ( isset( $_POST[ $value['id'] ]['crop'] ) )
						$update_options[ $value['id'] ]['crop'] = 1;
					else
						$update_options[ $value['id'] ]['crop'] = 0;

				} else {
					$update_options[ $value['id'] ]['width'] 	= $value['default']['width'];
					$update_options[ $value['id'] ]['height'] 	= $value['default']['height'];
					$update_options[ $value['id'] ]['crop'] 	= $value['default']['crop'];
				}

			break;

			// Custom handling
			default :

				do_action( 'woocommerce_update_option_' . $type, $value );

			break;

		}

		if ( ! is_null( $option_value ) ) {
			// Check if option is an array
			if ( strstr( $value['id'], '[' ) ) {

				parse_str( $value['id'], $option_array );

				// Option name is first key
				$option_name = current( array_keys( $option_array ) );

				// Get old option value
				if ( ! isset( $update_options[ $option_name ] ) )
					 $update_options[ $option_name ] = get_option( $option_name, array() );

				if ( ! is_array( $update_options[ $option_name ] ) )
					$update_options[ $option_name ] = array();

				// Set keys and value
				$key = key( $option_array[ $option_name ] );

				$update_options[ $option_name ][ $key ] = $option_value;

			// Single value
			} else {
				$update_options[ $value['id'] ] = $option_value;
			}
		}

		// Custom handling
		do_action( 'woocommerce_update_option', $value );
	}

	// Now save the options
	foreach( $update_options as $name => $value ) {
		
		$current_option = get_option( $name );
		$current_default = cxccoo_get_default( $name );
		
		if ( $value === $current_default ) {
			delete_option( $name );
		}
		else if ( $value !== $current_option ) {
			update_option( $name, $value );
		}
	}

	return true;
}

/**
 * Get one of our options.
 *
 * Automatically mixes in our defaults if nothing is saved yet.
 *
 * @param  string $key key name of the option.
 * @return mixed       the value stored with the option, or the default if nothing stored yet.
 */
function cxccoo_get_option( $key ) {
	return get_option( $key, cxccoo_get_default( $key ) );
}

/**
 * Get one of defaults options.
 *
 * @param  string $key key name of the option.
 * @return mixed       the default set for that option, or FALSE if none has been set.
 */
function cxccoo_get_default( $key ) {
	
	$settings = cxccoo_get_settings();
	
	$default = FALSE;
	
	foreach ( $settings as $setting ) {
		if ( isset( $setting['id'] ) && $key == $setting['id'] && isset( $setting['default'] ) ) {
			$default = $setting['default'];
		}
	}
	
	return $default;
}


?>