<?php
// Display admin notice on screen load
function woo_ce_admin_notice( $message = '', $priority = 'updated', $screen = '' ) {

	if( $priority == false || $priority == '' )
		$priority = 'updated';
	if( $message <> '' ) {
		ob_start();
		woo_ce_admin_notice_html( $message, $priority, $screen );
		$output = ob_get_contents();
		ob_end_clean();
		// Check if an existing notice is already in queue
		$existing_notice = get_transient( WOO_CE_PREFIX . '_notice' );
		if( $existing_notice !== false ) {
			$existing_notice = base64_decode( $existing_notice );
			$output = $existing_notice . $output;
		}
		$response = set_transient( WOO_CE_PREFIX . '_notice', base64_encode( $output ), DAY_IN_SECONDS );
		// Check if the Transient was saved
		if( $response !== false )
			add_action( 'admin_notices', 'woo_ce_admin_notice_print' );
	}

}

// HTML template for admin notice
function woo_ce_admin_notice_html( $message = '', $priority = 'updated', $screen = '' ) {

	// Display admin notice on specific screen
	if( !empty( $screen ) ) {

		global $pagenow;

		if( is_array( $screen ) ) {
			if( in_array( $pagenow, $screen ) == false )
				return;
		} else {
			if( $pagenow <> $screen )
				return;
		}

	} ?>
<div id="message" class="<?php echo $priority; ?>">
	<p><?php echo $message; ?></p>
</div>
<?php

}

// Grabs the WordPress transient that holds the admin notice and prints it
function woo_ce_admin_notice_print() {

	$output = get_transient( WOO_CE_PREFIX . '_notice' );
	if( $output !== false ) {
		delete_transient( WOO_CE_PREFIX . '_notice' );
		$output = base64_decode( $output );
		echo $output;
	}

}

// HTML template header on Store Exporter screen
function woo_ce_template_header( $title = '', $icon = 'woocommerce' ) {

	if( $title )
		$output = $title;
	else
		$output = __( 'Store Export', 'woocommerce-exporter' ); ?>
<div id="woo-ce" class="wrap">
	<div id="icon-<?php echo $icon; ?>" class="icon32 icon32-woocommerce-importer"><br /></div>
	<h2>
		<?php echo $output; ?>
	</h2>
<?php

}

// HTML template footer on Store Exporter screen
function woo_ce_template_footer() { ?>
</div>
<!-- .wrap -->
<?php

}

function woo_ce_export_options_export_format() {

	$woo_cd_url = 'http://www.visser.com.au/woocommerce/plugins/exporter-deluxe/';
	$woo_cd_link = sprintf( '<a href="%s" target="_blank">' . __( 'Store Exporter Deluxe', 'woocommerce-exporter' ) . '</a>', $woo_cd_url );

	ob_start(); ?>
<tr>
	<th>
		<label><?php _e( 'Export format', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<label><input type="radio" name="export_format" value="csv"<?php checked( 'csv', 'csv' ); ?> /> <?php _e( 'CSV', 'woocommerce-exporter' ); ?> <span class="description"><?php _e( '(Comma Separated Values)', 'woocommerce-exporter' ); ?></span></label><br />
		<label><input type="radio" name="export_format" value="xls" disabled="disabled" /> <?php _e( 'Excel (XLS)', 'woocommerce-exporter' ); ?> <span class="description"><?php _e( '(Excel 97-2003)', 'woocommerce-exporter' ); ?> - <?php printf( __( 'available in %s', 'woocommerce-exporter' ), $woo_cd_link ); ?></span></label><br />
		<label><input type="radio" name="export_format" value="xlsx" disabled="disabled" /> <?php _e( 'Excel (XLSX)', 'woocommerce-exporter' ); ?> <span class="description"><?php _e( '(Excel 2007-2013)', 'woocommerce-exporter' ); ?> - <?php printf( __( 'available in %s', 'woocommerce-exporter' ), $woo_cd_link ); ?></span></label><br />
		<label><input type="radio" name="export_format" value="xml" disabled="disabled" /> <?php _e( 'XML', 'woocommerce-exporter' ); ?> <span class="description"><?php _e( '(EXtensible Markup Language)', 'woocommerce-exporter' ); ?> - <?php printf( __( 'available in %s', 'woocommerce-exporter' ), $woo_cd_link ); ?></span></label><br />
		<div class="export-options product-options">
			<label><input type="radio" name="export_format" value="rss" disabled="disabled" /> <?php _e( 'RSS', 'woocommerce-exporter' ); ?> <span class="description"><?php printf( __( '(<attr title="%s">XML</attr> feed in RSS 2.0 format)', 'woocommerce-exporter' ), __( 'EXtensible Markup Language', 'woocommerce-exporter' ) ); ?> - <?php printf( __( 'available in %s', 'woocommerce-exporter' ), $woo_cd_link ); ?></span></label>
		</div>
		<p class="description"><?php _e( 'Adjust the export format to generate different export file formats.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
	ob_end_flush();

}

// Add Export and Docs links to the Plugins screen
function woo_ce_add_settings_link( $links, $file ) {

	// Manually force slug
	$this_plugin = WOO_CE_RELPATH;

	if( $file == $this_plugin ) {
		$docs_url = 'http://www.visser.com.au/docs/';
		$docs_link = sprintf( '<a href="%s" target="_blank">' . __( 'Docs', 'woocommerce-exporter' ) . '</a>', $docs_url );
		$export_link = sprintf( '<a href="%s">' . __( 'Export', 'woocommerce-exporter' ) . '</a>', esc_url( add_query_arg( 'page', 'woo_ce', 'admin.php' ) ) );
		array_unshift( $links, $docs_link );
		array_unshift( $links, $export_link );
	}
	return $links;

}
add_filter( 'plugin_action_links', 'woo_ce_add_settings_link', 10, 2 );

// Add Store Export page to WooCommerce screen IDs
function woo_ce_wc_screen_ids( $screen_ids = array() ) {

	$screen_ids[] = 'woocommerce_page_woo_ce';
	return $screen_ids;

}
add_filter( 'woocommerce_screen_ids', 'woo_ce_wc_screen_ids', 10, 1 );

// Add Store Export to WordPress Administration menu
function woo_ce_admin_menu() {

	$page = add_submenu_page( 'woocommerce', __( 'Store Exporter', 'woocommerce-exporter' ), __( 'Store Export', 'woocommerce-exporter' ), 'view_woocommerce_reports', 'woo_ce', 'woo_ce_html_page' );
	add_action( 'admin_print_styles-' . $page, 'woo_ce_enqueue_scripts' );
	add_action( 'current_screen', 'woo_ce_add_help_tab' );

}
add_action( 'admin_menu', 'woo_ce_admin_menu', 11 );

// Load CSS and jQuery scripts for Store Exporter screen
function woo_ce_enqueue_scripts() {

	// Simple check that WooCommerce is activated
	if( class_exists( 'WooCommerce' ) ) {

		global $woocommerce;

		// Load WooCommerce default Admin styling
		wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );

	}

	// Date Picker Addon
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'jquery-ui-datepicker', plugins_url( '/templates/admin/jquery-ui-datepicker.css', WOO_CE_RELPATH ) );

	// Time Picker, Date Picker Addon
	wp_enqueue_script( 'jquery-ui-timepicker', plugins_url( '/js/jquery.timepicker.js', WOO_CE_RELPATH ), array( 'jquery', 'jquery-ui-datepicker' ) );
	wp_enqueue_style( 'jquery-ui-datepicker', plugins_url( '/templates/admin/jquery-ui-timepicker.css', WOO_CE_RELPATH ) );

	// Chosen
	wp_enqueue_style( 'jquery-chosen', plugins_url( '/templates/admin/chosen.css', WOO_CE_RELPATH ) );
	wp_enqueue_script( 'jquery-chosen', plugins_url( '/js/jquery.chosen.js', WOO_CE_RELPATH ), array( 'jquery' ) );

	// Common
	wp_enqueue_style( 'woo_ce_styles', plugins_url( '/templates/admin/export.css', WOO_CE_RELPATH ) );
	wp_enqueue_script( 'woo_ce_scripts', plugins_url( '/templates/admin/export.js', WOO_CE_RELPATH ), array( 'jquery', 'jquery-ui-sortable' ) );
	wp_enqueue_style( 'dashicons' );

	if( WOO_CE_DEBUG ) {
		wp_enqueue_style( 'jquery-csvToTable', plugins_url( '/templates/admin/jquery-csvtable.css', WOO_CE_RELPATH ) );
		wp_enqueue_script( 'jquery-csvToTable', plugins_url( '/js/jquery.csvToTable.js', WOO_CE_RELPATH ), array( 'jquery' ) );
	}
	wp_enqueue_style( 'woo_vm_styles', plugins_url( '/templates/admin/woocommerce-admin_dashboard_vm-plugins.css', WOO_CE_RELPATH ) );

}

function woo_ce_add_help_tab() {

	$screen = get_current_screen();
	if( $screen->id <> 'woocommerce_page_woo_ce' )
		return;

	$screen->add_help_tab( array(
		'id' => 'woo_ce',
		'title' => __( 'Store Exporter', 'woocommerce-exporter' ),
		'content' => 
			'<p>' . __( 'Thank you for using Store Exporter :) Should you need help using this Plugin please read the documentation, if an issue persists get in touch with us on the WordPress.org Support tab for this Plugin.', 'woocommerce-exporter' ) . '</p>' .
			'<p><a href="' . 'http://www.visser.com.au/documentation/store-exporter/usage/' . '" target="_blank" class="button button-primary">' . __( 'Documentation', 'woocommerce-exporter' ) . '</a> <a href="' . 'http://wordpress.org/support/plugin/woocommerce-exporter' . '" target="_blank" class="button">' . __( 'Forum Support', 'woocommerce-exporter' ) . '</a></p>'
	) );

}

function woo_ce_plugin_page_notices() {

	global $pagenow;

	if( $pagenow == 'plugins.php' ) {
		if( woo_is_jigo_activated() || woo_is_wpsc_activated() ) {
			$r_plugins = array(
				'woocommerce-exporter/exporter.php',
				'woocommerce-store-exporter/exporter.php'
			);
			$i_plugins = get_plugins();
			foreach( $r_plugins as $path ) {
				if( isset( $i_plugins[$path] ) ) {
					add_action( 'after_plugin_row_' . $path, 'woo_ce_plugin_page_notice', 10, 3 );
					break;
				}
			}
		}
	}

}

// HTML active class for the currently selected tab on the Store Exporter screen
function woo_ce_admin_active_tab( $tab_name = null, $tab = null ) {

	if( isset( $_GET['tab'] ) && !$tab )
		$tab = $_GET['tab'];
	else if( !isset( $_GET['tab'] ) && woo_ce_get_option( 'skip_overview', false ) )
		$tab = 'export';
	else
		$tab = 'overview';

	$output = '';
	if( isset( $tab_name ) && $tab_name ) {
		if( $tab_name == $tab )
			$output = ' nav-tab-active';
	}
	echo $output;

}

// HTML template for each tab on the Store Exporter screen
function woo_ce_tab_template( $tab = '' ) {

	if( !$tab )
		$tab = 'overview';

	// Store Exporter Deluxe
	$woo_cd_url = 'http://www.visser.com.au/woocommerce/plugins/exporter-deluxe/';
	$woo_cd_link = sprintf( '<a href="%s" target="_blank">' . __( 'Store Exporter Deluxe', 'woocommerce-exporter' ) . '</a>', $woo_cd_url );

	$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

	switch( $tab ) {

		case 'overview':
			$skip_overview = woo_ce_get_option( 'skip_overview', false );
			break;

		case 'export':
			$export_type = sanitize_text_field( ( isset( $_POST['dataset'] ) ? $_POST['dataset'] : woo_ce_get_option( 'last_export', 'product' ) ) );
			$export_types = array_keys( woo_ce_get_export_types() );

			// Check if the default export type exists
			if( !in_array( $export_type, $export_types ) )
				$export_type = 'product';

			$product = woo_ce_get_export_type_count( 'product' );
			$category = woo_ce_get_export_type_count( 'category' );
			$tag = woo_ce_get_export_type_count( 'tag' );
			$brand = '999';
			$order = '999';
			$customer = '999';
			$user = woo_ce_get_export_type_count( 'user' );
			$review = '999';
			$coupon = '999';
			$attribute = '999';
			$subscription = '999';
			$product_vendor = '999';
			$commission = '999';
			$shipping_class = '999';
			$ticket = '999';

			add_action( 'woo_ce_export_options', 'woo_ce_export_options_export_format' );
			if( $product_fields = woo_ce_get_product_fields() ) {
				foreach( $product_fields as $key => $product_field )
					$product_fields[$key]['disabled'] = ( isset( $product_field['disabled'] ) ? $product_field['disabled'] : 0 );
				add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_product_category' );
				add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_product_tag' );
				add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_product_brand' );
				add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_product_vendor' );
				add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_product_status' );
				add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_product_type' );
				add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_stock_status' );
				add_action( 'woo_ce_export_product_options_after_table', 'woo_ce_product_sorting' );
				add_action( 'woo_ce_export_options', 'woo_ce_products_upsells_formatting' );
				add_action( 'woo_ce_export_options', 'woo_ce_products_crosssells_formatting' );
				add_action( 'woo_ce_export_options', 'woo_ce_export_options_gallery_format' );
				add_action( 'woo_ce_export_after_form', 'woo_ce_products_custom_fields' );
			}
			if( $category_fields = woo_ce_get_category_fields() ) {
				foreach( $category_fields as $key => $category_field )
					$category_fields[$key]['disabled'] = ( isset( $category_field['disabled'] ) ? $category_field['disabled'] : 0 );
				add_action( 'woo_ce_export_category_options_after_table', 'woo_ce_category_sorting' );
			}
			if( $tag_fields = woo_ce_get_tag_fields() ) {
				foreach( $tag_fields as $key => $tag_field )
					$tag_fields[$key]['disabled'] = ( isset( $tag_field['disabled'] ) ? $tag_field['disabled'] : 0 );
				add_action( 'woo_ce_export_tag_options_after_table', 'woo_ce_tag_sorting' );
			}
			if( $brand_fields = woo_ce_get_brand_fields() ) {
				foreach( $brand_fields as $key => $brand_field )
					$brand_fields[$key]['disabled'] = ( isset( $brand_field['disabled'] ) ? $brand_field['disabled'] : 0 );
				add_action( 'woo_ce_export_brand_options_before_table', 'woo_ce_brand_sorting' );
			}
			if( $order_fields = woo_ce_get_order_fields() ) {
				add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_date' );
				add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_status' );
				add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_customer' );
				add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_billing_country' );
				add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_shipping_country' );
				add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_user_role' );
				add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_coupon' );
				add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_product' );
				add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_product_category' );
				add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_product_tag' );
				add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_product_brand' );
				add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_order_id' );
				add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_payment_gateway' );
				add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_shipping_method' );
				add_action( 'woo_ce_export_order_options_after_table', 'woo_ce_order_sorting' );
				add_action( 'woo_ce_export_options', 'woo_ce_orders_items_formatting' );
				add_action( 'woo_ce_export_options', 'woo_ce_orders_max_order_items' );
				add_action( 'woo_ce_export_options', 'woo_ce_orders_items_types' );
				add_action( 'woo_ce_export_after_form', 'woo_ce_orders_custom_fields' );
			}
			if( $customer_fields = woo_ce_get_customer_fields() ) {
				add_action( 'woo_ce_export_customer_options_before_table', 'woo_ce_customers_filter_by_status' );
				add_action( 'woo_ce_export_customer_options_before_table', 'woo_ce_customers_filter_by_user_role' );
				add_action( 'woo_ce_export_after_form', 'woo_ce_customers_custom_fields' );
			}
			if( $user_fields = woo_ce_get_user_fields() ) {
				foreach( $user_fields as $key => $user_field )
					$user_fields[$key]['disabled'] = ( isset( $user_field['disabled'] ) ? $user_field['disabled'] : 0 );
				add_action( 'woo_ce_export_user_options_after_table', 'woo_ce_user_sorting' );
				add_action( 'woo_ce_export_after_form', 'woo_ce_users_custom_fields' );
			}
			if( $coupon_fields = woo_ce_get_coupon_fields() ) {
				add_action( 'woo_ce_export_coupon_options_before_table', 'woo_ce_coupon_sorting' );
			}
			if( $subscription_fields = woo_ce_get_subscription_fields() ) {
				add_action( 'woo_ce_export_subscription_options_before_table', 'woo_ce_subscriptions_filter_by_subscription_status' );
				add_action( 'woo_ce_export_subscription_options_before_table', 'woo_ce_subscriptions_filter_by_subscription_product' );
			}
			$product_vendor_fields = woo_ce_get_product_vendor_fields();
			if( $commission_fields = woo_ce_get_commission_fields() ) {
				add_action( 'woo_ce_export_commission_options_before_table', 'woo_ce_commissions_filter_by_date' );
				add_action( 'woo_ce_export_commission_options_before_table', 'woo_ce_commissions_filter_by_product_vendor' );
				add_action( 'woo_ce_export_commission_options_before_table', 'woo_ce_commissions_filter_by_commission_status' );
				add_action( 'woo_ce_export_commission_options_before_table', 'woo_ce_commission_sorting' );
			}
			if( $shipping_class_fields = woo_ce_get_shipping_class_fields() ) {
				add_action( 'woo_ce_export_shipping_class_options_after_table', 'woo_ce_shipping_class_sorting' );
			}
			$attribute_fields = false;

			// Export options
			$limit_volume = woo_ce_get_option( 'limit_volume' );
			$offset = woo_ce_get_option( 'offset' );
			break;

		case 'fields':
			$export_type = ( isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : '' );
			$export_types = array_keys( woo_ce_get_export_types() );
			$fields = array();
			if( in_array( $export_type, $export_types ) ) {
				if( has_filter( 'woo_ce_' . $export_type . '_fields', 'woo_ce_override_' . $export_type . '_field_labels' ) )
					remove_filter( 'woo_ce_' . $export_type . '_fields', 'woo_ce_override_' . $export_type . '_field_labels', 11 );
				if( function_exists( sprintf( 'woo_ce_get_%s_fields', $export_type ) ) )
					$fields = call_user_func( 'woo_ce_get_' . $export_type . '_fields' );
				$labels = woo_ce_get_option( $export_type . '_labels', array() );
			}
			break;

		case 'archive':
			if( isset( $_GET['deleted'] ) ) {
				$message = __( 'Archived export has been deleted.', 'woocommerce-exporter' );
				woo_ce_admin_notice( $message );
			}
			if( $files = woo_ce_get_archive_files() ) {
				foreach( $files as $key => $file )
					$files[$key] = woo_ce_get_archive_file( $file );
			}
			break;

		case 'settings':
			$export_filename = woo_ce_get_option( 'export_filename', '' );
			// Default export filename
			if( $export_filename == false )
				$export_filename = '%store_name%-export_%dataset%-%date%-%time%-%random%.csv';
			$delete_file = woo_ce_get_option( 'delete_file', 1 );
			$timeout = woo_ce_get_option( 'timeout', 0 );
			$encoding = woo_ce_get_option( 'encoding', 'UTF-8' );
			$bom = woo_ce_get_option( 'bom', 1 );
			$delimiter = woo_ce_get_option( 'delimiter', ',' );
			$category_separator = woo_ce_get_option( 'category_separator', '|' );
			$escape_formatting = woo_ce_get_option( 'escape_formatting', 'all' );
			$date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );
			// Reset the Date Format if corrupted
			if( $date_format == '1' || $date_format == '' || $date_format == false )
				$date_format = 'd/m/Y';
			$file_encodings = ( function_exists( 'mb_list_encodings' ) ? mb_list_encodings() : false );
			add_action( 'woo_ce_export_settings_top', 'woo_ce_export_settings_quicklinks' );
			add_action( 'woo_ce_export_settings_after', 'woo_ce_export_settings_csv' );
			add_action( 'woo_ce_export_settings_after', 'woo_ce_export_settings_extend' );
			break;

		case 'tools':
			// Product Importer Deluxe
			$woo_pd_url = 'http://www.visser.com.au/woocommerce/plugins/product-importer-deluxe/';
			$woo_pd_target = ' target="_blank"';
			if( function_exists( 'woo_pd_init' ) ) {
				$woo_pd_url = esc_url( add_query_arg( array( 'page' => 'woo_pd', 'tab' => null ) ) );
				$woo_pd_target = false;
			}

			// Store Toolkit
			$woo_st_url = 'http://www.visser.com.au/woocommerce/plugins/store-toolkit/';
			$woo_st_target = ' target="_blank"';
			if( function_exists( 'woo_st_admin_init' ) ) {
				$woo_st_url = esc_url( add_query_arg( array( 'page' => 'woo_st', 'tab' => null ) ) );
				$woo_st_target = false;
			}

			// Export modules
			$module_status = ( isset( $_GET['module_status'] ) ? sanitize_text_field( $_GET['module_status'] ) : false );
			$modules = woo_ce_admin_modules_list( $module_status );
			$modules_all = get_transient( WOO_CE_PREFIX . '_modules_all_count' );
			$modules_active = get_transient( WOO_CE_PREFIX . '_modules_active_count' );
			$modules_inactive = get_transient( WOO_CE_PREFIX . '_modules_inactive_count' );
			break;

	}
	if( $tab ) {
		if( file_exists( WOO_CE_PATH . 'templates/admin/tabs-' . $tab . '.php' ) ) {
			include_once( WOO_CE_PATH . 'templates/admin/tabs-' . $tab . '.php' );
		} else {
			$message = sprintf( __( 'We couldn\'t load the export template file <code>%s</code> within <code>%s</code>, this file should be present.', 'woocommerce-exporter' ), 'tabs-' . $tab . '.php', WOO_CE_PATH . 'templates/admin/...' );
			woo_ce_admin_notice_html( $message, 'error' );
			ob_start(); ?>
<p><?php _e( 'You can see this error for one of a few common reasons', 'woocommerce-exporter' ); ?>:</p>
<ul class="ul-disc">
	<li><?php _e( 'WordPress was unable to create this file when the Plugin was installed or updated', 'woocommerce-exporter' ); ?></li>
	<li><?php _e( 'The Plugin files have been recently changed and there has been a file conflict', 'woocommerce-exporter' ); ?></li>
	<li><?php _e( 'The Plugin file has been locked and cannot be opened by WordPress', 'woocommerce-exporter' ); ?></li>
</ul>
<p><?php _e( 'Jump onto our website and download a fresh copy of this Plugin as it might be enough to fix this issue. If this persists get in touch with us.', 'woocommerce-exporter' ); ?></p>
<?php
			ob_end_flush();
		}
	}

}

// Display the memory usage in the screen footer
function woo_ce_admin_footer_text( $footer_text = '' ) {

	$current_screen = get_current_screen();
	$pages = array(
		'woocommerce_page_woo_ce'
	);
	// Check to make sure we're on the Export screen
	if ( isset( $current_screen->id ) && apply_filters( 'woo_ce_display_admin_footer_text', in_array( $current_screen->id, $pages ) ) ) {
		$memory_usage = woo_ce_current_memory_usage( false );
		$memory_limit = absint( ini_get( 'memory_limit' ) );
		$memory_percent = absint( $memory_usage / $memory_limit * 100 );
		$memory_color = 'font-weight:normal;';
		if( $memory_percent > 75 )
			$memory_color = 'font-weight:bold; color:orange;';
		if( $memory_percent > 90 )
			$memory_color = 'font-weight:bold; color:red;';
		$footer_text .= ' | ' . sprintf( __( 'Memory: %s of %s MB (%s)', 'woocommerce-exporter' ), $memory_usage, $memory_limit, sprintf( '<span style="%s">%s</span>', $memory_color, $memory_percent . '%' ) );
	}
	return $footer_text;

}

function woo_ce_modules_status_class( $status = 'inactive' ) {

	$output = '';
	switch( $status ) {

		case 'active':
			$output = 'green';
			break;

		case 'inactive':
			$output = 'yellow';
			break;

	}
	echo $output;

}

function woo_ce_modules_status_label( $status = 'inactive' ) {

	$output = '';
	switch( $status ) {

		case 'active':
			$output = __( 'OK', 'woocommerce-exporter' );
			break;

		case 'inactive':
			$output = __( 'Install', 'woocommerce-exporter' );
			break;

	}
	echo $output;

}

// HTML template for header prompt on Store Exporter screen
function woo_ce_support_donate() {

	$output = '';
	$show = true;
	if( function_exists( 'woo_vl_we_love_your_plugins' ) ) {
		if( in_array( WOO_CE_DIRNAME, woo_vl_we_love_your_plugins() ) )
			$show = false;
	}
	if( $show ) {
		$donate_url = 'http://www.visser.com.au/donate/';
		$rate_url = 'http://wordpress.org/support/view/plugin-reviews/' . WOO_CE_DIRNAME;
		$output = '
<div id="support-donate_rate" class="support-donate_rate">
	<p>' . sprintf( __( '<strong>Like this Plugin?</strong> %s and %s.', 'woocommerce-exporter' ), '<a href="' . $donate_url . '" target="_blank">' . __( 'Donate to support this Plugin', 'woocommerce-exporter' ) . '</a>', '<a href="' . esc_url( add_query_arg( array( 'rate' => '5' ), $rate_url ) ) . '#postform" target="_blank">rate / review us on WordPress.org</a>' ) . '</p>
</div>
';
	}
	echo $output;

}
?>
