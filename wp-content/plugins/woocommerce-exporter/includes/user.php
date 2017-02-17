<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	function woo_ce_get_export_type_user_count() {

		$count = 0;
		// Check if the existing Transient exists
		$cached = get_transient( WOO_CE_PREFIX . '_user_count' );
		if( $cached == false ) {
			if( $users = count_users() )
				$count = ( isset( $users['total_users'] ) ? $users['total_users'] : 0 );
			set_transient( WOO_CE_PREFIX . '_user_count', $count, HOUR_IN_SECONDS );
		} else {
			$count = $cached;
		}
		return $count;

	}

	// HTML template for User Sorting widget on Store Exporter screen
	function woo_ce_user_sorting() {

		$orderby = woo_ce_get_option( 'user_orderby', 'ID' );
		$order = woo_ce_get_option( 'user_order', 'ASC' );

		ob_start(); ?>
<p><label><?php _e( 'User Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="user_orderby">
		<option value="ID"<?php selected( 'ID', $orderby ); ?>><?php _e( 'User ID', 'woocommerce-exporter' ); ?></option>
		<option value="display_name"<?php selected( 'display_name', $orderby ); ?>><?php _e( 'Display Name', 'woocommerce-exporter' ); ?></option>
		<option value="user_name"<?php selected( 'user_name', $orderby ); ?>><?php _e( 'Name', 'woocommerce-exporter' ); ?></option>
		<option value="user_login"<?php selected( 'user_login', $orderby ); ?>><?php _e( 'Username', 'woocommerce-exporter' ); ?></option>
		<option value="nicename"<?php selected( 'nicename', $orderby ); ?>><?php _e( 'Nickname', 'woocommerce-exporter' ); ?></option>
		<option value="email"<?php selected( 'email', $orderby ); ?>><?php _e( 'E-mail', 'woocommerce-exporter' ); ?></option>
		<option value="url"<?php selected( 'url', $orderby ); ?>><?php _e( 'Website', 'woocommerce-exporter' ); ?></option>
		<option value="registered"<?php selected( 'registered', $orderby ); ?>><?php _e( 'Date Registered', 'woocommerce-exporter' ); ?></option>
		<option value="rand"<?php selected( 'rand', $orderby ); ?>><?php _e( 'Random', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="user_order">
		<option value="ASC"<?php selected( 'ASC', $order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Users within the exported file. By default this is set to export User by User ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
		ob_end_flush();

	}

	// HTML template for disabled Custom Users widget on Store Exporter screen
	function woo_ce_users_custom_fields() {

		$woo_cd_url = 'http://www.visser.com.au/woocommerce/plugins/exporter-deluxe/';
		$woo_cd_link = sprintf( '<a href="%s" target="_blank">' . __( 'Store Exporter Deluxe', 'woocommerce-exporter' ) . '</a>', $woo_cd_url );

		$custom_users = ' - ';

		$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/usage/';

		ob_start(); ?>
<form method="post" id="export-users-custom-fields" class="export-options user-options">
	<div id="poststuff">

		<div class="postbox" id="export-options user-options">
			<h3 class="hndle"><?php _e( 'Custom User Fields', 'woocommerce-exporter' ); ?></h3>
			<div class="inside">
				<p class="description"><?php _e( 'To include additional custom User meta in the Export Users table above fill the Users text box then click Save Custom Fields.', 'woocommerce-exporter' ); ?></p>
				<p class="description"><?php printf( __( 'For more information on exporting custom User meta consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ); ?></p>
				<table class="form-table">

					<tr>
						<th>
							<label><?php _e( 'User meta', 'woocommerce-exporter' ); ?></label>
						</th>
						<td>
							<textarea name="custom_users" rows="5" cols="70"><?php echo esc_textarea( $custom_users ); ?></textarea>
							<span class="description"> - <?php printf( __( 'available in %s', 'woocommerce-exporter' ), $woo_cd_link ); ?></span>
							<p class="description"><?php _e( 'Include additional custom User meta in your export file by adding each custom User meta name to a new line above.<br />For example: <code>Customer UA, Customer IP Address</code>', 'woocommerce-exporter' ); ?></p>
						</td>
					</tr>

				</table>
				<p class="submit">
					<input type="button" class="button button-disabled" value="<?php _e( 'Save Custom Fields', 'woocommerce-exporter' ); ?>" />
				</p>
			</div>
			<!-- .inside -->
		</div>
		<!-- .postbox -->

	</div>
	<!-- #poststuff -->
	<input type="hidden" name="action" value="update" />
</form>
<!-- #export-users-custom-fields -->
<?php
		ob_end_flush();

	}

	/* End of: WordPress Administration */

}

// Returns a list of User export columns
function woo_ce_get_user_fields( $format = 'full' ) {

	$export_type = 'user';

	$fields = array();
	$fields[] = array(
		'name' => 'user_id',
		'label' => __( 'User ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_name',
		'label' => __( 'Username', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_role',
		'label' => __( 'User Role', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'first_name',
		'label' => __( 'First Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'last_name',
		'label' => __( 'Last Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'full_name',
		'label' => __( 'Full Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'nick_name',
		'label' => __( 'Nickname', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'email',
		'label' => __( 'E-mail', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'orders',
		'label' => __( 'Orders', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'money_spent',
		'label' => __( 'Money Spent', 'woocommerce-exporter' ),
		'disabled' => 1
	);
	$fields[] = array(
		'name' => 'url',
		'label' => __( 'Website', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'date_registered',
		'label' => __( 'Date Registered', 'woocommerce-exporter' )
	);

/*
	$fields[] = array(
		'name' => '',
		'label' => __( '', 'woocommerce-exporter' )
	);
*/

	// Drop in our content filters here
	add_filter( 'sanitize_key', 'woo_ce_sanitize_key' );

	// Allow Plugin/Theme authors to add support for additional columns
	$fields = apply_filters( sprintf( WOO_CE_PREFIX . '_%s_fields', $export_type ), $fields, $export_type );

	// Remove our content filters here to play nice with other Plugins
	remove_filter( 'sanitize_key', 'woo_ce_sanitize_key' );

	$remember = woo_ce_get_option( $export_type . '_fields', array() );
	if( !empty( $remember ) ) {
		$remember = maybe_unserialize( $remember );
		$size = count( $fields );
		for( $i = 0; $i < $size; $i++ ) {
			$fields[$i]['disabled'] = ( isset( $fields[$i]['disabled'] ) ? $fields[$i]['disabled'] : 0 );
			$fields[$i]['default'] = 1;
			// If not found turn off default
			if( !array_key_exists( $fields[$i]['name'], $remember ) )
				$fields[$i]['default'] = 0;
		}
	}

	switch( $format ) {

		case 'summary':
			$output = array();
			$size = count( $fields );
			for( $i = 0; $i < $size; $i++ ) {
				if( isset( $fields[$i] ) )
					$output[$fields[$i]['name']] = 'on';
			}
			return $output;
			break;

		case 'full':
		default:
			$sorting = woo_ce_get_option( $export_type . '_sorting', array() );
			$size = count( $fields );
			for( $i = 0; $i < $size; $i++ ) {
				$fields[$i]['reset'] = $i;
				$fields[$i]['order'] = ( isset( $sorting[$fields[$i]['name']] ) ? $sorting[$fields[$i]['name']] : $i );
			}
			// Check if we are using PHP 5.3 and above
			if( version_compare( phpversion(), '5.3' ) >= 0 )
				usort( $fields, woo_ce_sort_fields( 'order' ) );
			return $fields;
			break;

	}

}

// Check if we should override field labels from the Field Editor
function woo_ce_override_user_field_labels( $fields = array() ) {

	$labels = woo_ce_get_option( 'user_labels', array() );
	if( !empty( $labels ) ) {
		foreach( $fields as $key => $field ) {
			if( isset( $labels[$field['name']] ) )
				$fields[$key]['label'] = $labels[$field['name']];
		}
	}
	return $fields;

}
add_filter( 'woo_ce_user_fields', 'woo_ce_override_user_field_labels', 11 );

// Returns the export column header label based on an export column slug
function woo_ce_get_user_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = woo_ce_get_user_fields();
		$size = count( $fields );
		for( $i = 0; $i < $size; $i++ ) {
			if( $fields[$i]['name'] == $name ) {
				switch( $format ) {

					case 'name':
						$output = $fields[$i]['label'];
						break;

					case 'full':
						$output = $fields[$i];
						break;

				}
				$i = $size;
			}
		}
	}
	return $output;

}

// Returns a list of User IDs
function woo_ce_get_users( $args = array() ) {

	global $export;

	$limit_volume = 0;
	$offset = 0;
	$orderby = 'login';
	$order = 'ASC';

	if( $args ) {
		$limit_volume = ( isset( $args['limit_volume'] ) ? $args['limit_volume'] : 0 );
		if( $limit_volume == -1 )
			$limit_volume = 0;
		$offset = ( isset( $args['offset'] ) ? $args['offset'] : 0 );
		$orderby = ( isset( $args['user_orderby'] ) ? $args['user_orderby'] : 'login' );
		$order = ( isset( $args['user_order'] ) ? $args['user_order'] : 'ASC' );
	}
	$args = array(
		'offset' => $offset,
		'number' => $limit_volume,
		'order' => $order,
		'offset' => $offset,
		'fields' => 'ids'
	);
	if( $user_ids = new WP_User_Query( $args ) ) {
		$users = array();
		$export->total_rows = $user_ids->total_users;
		foreach( $user_ids->results as $user_id )
			$users[] = $user_id;
		return $users;
	}

}

function woo_ce_get_user_data( $user_id = 0, $args = array() ) {

	$defaults = array();
	$args = wp_parse_args( $args, $defaults );

	// Get User details
	$user_data = get_userdata( $user_id );

	$user = new stdClass;
	if( $user_data !== false ) {
		$user->ID = $user_data->ID;
		$user->user_id = $user_data->ID;
		$user->user_name = $user_data->user_login;
		$user->user_role = $user_data->roles[0];
		$user->first_name = $user_data->first_name;
		$user->last_name = $user_data->last_name;
		$user->full_name = sprintf( apply_filters( 'woo_ce_get_user_data_full_name', '%s %s' ), $user->first_name, $user->last_name );
		$user->nick_name = $user_data->user_nicename;
		$user->email = $user_data->user_email;
		$user->url = $user_data->user_url;
		$user->date_registered = $user_data->user_registered;
	}

	// Allow Plugin/Theme authors to add support for additional User columns
	return apply_filters( 'woo_ce_user', $user );
	
}

// Returns a list of WordPress User Roles
function woo_ce_get_user_roles() {

	global $wp_roles;

	$user_roles = $wp_roles->roles;
	return $user_roles;

}

function woo_ce_format_user_role_label( $user_role = '' ) {

	global $wp_roles;

	$output = $user_role;
	if( $user_role ) {
		$user_roles = woo_ce_get_user_roles();
		if( isset( $user_roles[$user_role] ) )
			$output = ucfirst( $user_roles[$user_role]['name'] );
		unset( $user_roles );
	}
	return $output;

}

?>