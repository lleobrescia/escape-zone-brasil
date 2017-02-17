<?php
include_once( WOO_CE_PATH . 'includes/product.php' );
include_once( WOO_CE_PATH . 'includes/product-extend.php' );
include_once( WOO_CE_PATH . 'includes/category.php' );
include_once( WOO_CE_PATH . 'includes/tag.php' );
include_once( WOO_CE_PATH . 'includes/brand.php' );
include_once( WOO_CE_PATH . 'includes/order.php' );
include_once( WOO_CE_PATH . 'includes/customer.php' );
include_once( WOO_CE_PATH . 'includes/user.php' );
include_once( WOO_CE_PATH . 'includes/user-extend.php' );
include_once( WOO_CE_PATH . 'includes/coupon.php' );
include_once( WOO_CE_PATH . 'includes/subscription.php' );
include_once( WOO_CE_PATH . 'includes/product_vendor.php' );
include_once( WOO_CE_PATH . 'includes/commission.php' );
include_once( WOO_CE_PATH . 'includes/shipping_class.php' );

// Check if we are using PHP 5.3 and above
if( version_compare( phpversion(), '5.3' ) >= 0 )
	include_once( WOO_CE_PATH . 'includes/legacy.php' );
include_once( WOO_CE_PATH . 'includes/formatting.php' );

include_once( WOO_CE_PATH . 'includes/export-csv.php' );

if( is_admin() ) {

	/* Start of: WordPress Administration */

	include_once( WOO_CE_PATH . 'includes/admin.php' );
	include_once( WOO_CE_PATH . 'includes/settings.php' );

	function woo_ce_detect_non_woo_install() {

		$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter/usage/';
		if( !woo_is_woo_activated() && ( woo_is_jigo_activated() || woo_is_wpsc_activated() ) ) {
			$message = sprintf( __( 'We have detected another e-Commerce Plugin than WooCommerce activated, please check that you are using Store Exporter for the correct platform. <a href="%s" target="_blank">Need help?</a>', 'woocommerce-exporter' ), $troubleshooting_url );
			woo_ce_admin_notice( $message, 'error', 'plugins.php' );
		} else if( !woo_is_woo_activated() ) {
			$message = sprintf( __( 'We have been unable to detect the WooCommerce Plugin activated on this WordPress site, please check that you are using Store Exporter for the correct platform. <a href="%s" target="_blank">Need help?</a>', 'woocommerce-exporter' ), $troubleshooting_url );
			woo_ce_admin_notice( $message, 'error', 'plugins.php' );
		}
		woo_ce_plugin_page_notices();

	}

	// Displays a HTML notice when a WordPress or Store Exporter error is encountered
	function woo_ce_admin_fail_notices() {

		$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter/usage/';

		// If the failed flag is set then prepare for an error notice
		if( isset( $_GET['failed'] ) ) {
			$message = '';
			if( isset( $_GET['message'] ) )
				$message = urldecode( $_GET['message'] );
			if( $message )
				$message = sprintf( __( 'A WordPress or server error caused the exporter to fail, the exporter was provided with a reason: <em>%s</em>', 'woocommerce-exporter' ), $message ) . ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
			else
				$message = __( 'A WordPress or server error caused the exporter to fail, no reason was provided, if this persists please get in touch so we can reproduce and resolve this with you.', 'woocommerce-exporter' ) . ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
			woo_ce_admin_notice_html( $message, 'error' );
		}

		// Displays a HTML notice where the maximum execution time cannot be set
		if( !woo_ce_get_option( 'dismiss_execution_time_prompt', 0 ) ) {
			$max_execution_time = absint( ini_get( 'max_execution_time' ) );
			$response = @ini_set( 'max_execution_time', 120 );
			if( $response == false || ( $response != $max_execution_time ) ) {
				$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_execution_time_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_execution_time_prompt' ) ) ) );
				$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>' . sprintf( __( 'We could not override the PHP configuration option <code>max_execution_time</code>, this will limit the size of possible exports. See: <a href="%s" target="_blank">Increasing PHP max_execution_time configuration option</a>', 'woocommerce-exporter' ), $troubleshooting_url );
				woo_ce_admin_notice_html( $message );
			}
		}

		// Displays a HTML notice where the memory allocated to WordPress falls below 64MB
		if( !woo_ce_get_option( 'dismiss_memory_prompt', 0 ) ) {
			$memory_limit = absint( ini_get( 'memory_limit' ) );
			$minimum_memory_limit = 64;
			if( $memory_limit < $minimum_memory_limit ) {
				$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_memory_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_memory_prompt' ) ) ) );
				$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>' . sprintf( __( 'We recommend setting memory to at least %dMB, your site has only %dMB allocated to it. See: <a href="%s" target="_blank">Increasing memory allocated to PHP</a>', 'woocommerce-exporter' ), $minimum_memory_limit, $memory_limit, $troubleshooting_url );
				woo_ce_admin_notice_html( $message, 'error' );
			}
		}

		// Displays a HTML notice if PHP 5.2 or lower is installed
		if( version_compare( phpversion(), '5.3', '<' ) && !woo_ce_get_option( 'dismiss_php_legacy', 0 ) ) {
			$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_php_legacy', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_php_legacy' ) ) ) );
			$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>' . sprintf( __( 'Your PHP version (%s) is not supported and is very much out of date, since 2010 all users are strongly encouraged to upgrade to PHP 5.3+ and above. Contact your hosting provider to make this happen. See: <a href="%s" target="_blank">Migrating from PHP 5.2 to 5.3</a>', 'woocommerce-exporter' ), phpversion(), $troubleshooting_url );
			woo_ce_admin_notice_html( $message, 'error' );
		}

		// Displays HTML notice if there are more than 2500 Subscriptions
		if( !woo_ce_get_option( 'dismiss_subscription_prompt', 0 ) ) {
			if( class_exists( 'WC_Subscriptions' ) ) {
				$wcs_version = woo_ce_get_wc_subscriptions_version();
				if( version_compare( $wcs_version, '2.0.1', '<' ) ) {
					if( method_exists( 'WC_Subscriptions', 'is_large_site' ) ) {
						// Does this store have roughly more than 3000 Subscriptions
						if( WC_Subscriptions::is_large_site() ) {
							$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_subscription_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_subscription_prompt' ) ) ) );
							$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>' . __( 'We\'ve detected the <em>is_large_site</em> flag has been set within WooCommerce Subscriptions. Please get in touch if exports are incomplete as we need to spin up an alternative export process to export Subscriptions from large stores.', 'woocommerce-exporter' ) . ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
							woo_ce_admin_notice_html( $message, 'error' );
						}
					}
				}
			}
		}

		// If the export failed the WordPress Transient will still exist
		if( get_transient( WOO_CE_PREFIX . '_running' ) ) {
			$message = __( 'A WordPress or server error caused the exporter to fail with a blank screen, this is either a memory or timeout issue, please get in touch so we can reproduce and resolve this.', 'woocommerce-exporter' ) . ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
			woo_ce_admin_notice_html( $message, 'error' );
			delete_transient( WOO_CE_PREFIX . '_running' );
		}
		// Displays a HTML notice if Archives is disabled and the Archives tab is opened
		if(
			woo_ce_get_option( 'delete_file', '1' ) == 1
			&& ( isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '' ) == 'archive'
			&& ( !woo_ce_get_option( 'dismiss_archives_prompt', 0 ) )
		) {
			$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_archives_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_archives_prompt' ) ) ) );
			$override_url = esc_url( add_query_arg( array( 'action' => 'hide_archives_tab', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_archives_tab' ) ) ) );
			$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>' . __( 'It looks like the saving of export archives is disabled from the Enabled Archives option on the Settings tab, would you like to hide the Archives tab aswell?', 'woocommerce-exporter' ) . '<br /><br /><a href="' . $override_url . '" class="button-primary">' . __( 'Hide Archives tab', 'woocommerce-exporter' ) . '</a>';
			woo_ce_admin_notice_html( $message );
		}

		// Displays a HTML notice if Archives are detected without a Post Status of private
		if( woo_ce_get_unprotected_archives( array( 'count' => true ) ) && !woo_ce_get_option( 'dismiss_archives_privacy_prompt', 0 ) ) {
			$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_archives_privacy_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_archives_privacy_prompt' ) ) ) );
			$override_url = esc_url( add_query_arg( array( 'action' => 'override_archives_privacy', '_wpnonce' => wp_create_nonce( 'woo_ce_override_archives_privacy' ) ) ) );
			$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>' . __( 'It looks like some archived exports require updating, would you like to hide these archived exports now?', 'woocommerce-exporter' ) . '<br /><br /><a href="' . $override_url . '" class="button-primary">' . __( 'Update export archives', 'woocommerce-exporter' ) . '</a>';
			woo_ce_admin_notice_html( $message );
		}

	}

	// Saves the state of Export fields for next export
	function woo_ce_save_fields( $export_type = '', $fields = array(), $sorting = array() ) {

		// Default fields
		if( $fields == false && !is_array( $fields ) )
			$fields = array();
		$export_types = array_keys( woo_ce_get_export_types() );
		if( in_array( $export_type, $export_types ) && !empty( $fields ) ) {
			woo_ce_update_option( $export_type . '_fields', array_map( 'sanitize_text_field', $fields ) );
			woo_ce_update_option( $export_type . '_sorting', array_map( 'absint', $sorting ) );
		}

	}

	// Returns number of an Export type prior to export, used on Store Exporter screen
	function woo_ce_get_export_type_count( $export_type = '', $args = array() ) {

		global $wpdb;

		$count_sql = null;
		$woocommerce_version = woo_get_woo_version();

		switch( $export_type ) {

			case 'product':
				$count = woo_ce_get_export_type_product_count();
				break;

			case 'category':
				$count = woo_ce_get_export_type_category_count();
				break;

			case 'tag':
				$count = woo_ce_get_export_type_tag_count();
				break;

			case 'order':
				$count = woo_ce_get_export_type_order_count();
				break;

			case 'customer':
				$count = woo_ce_get_export_type_customer_count();
				break;

			case 'user':
				$count = woo_ce_get_export_type_user_count();
				break;

			case 'review':
				$count = woo_ce_get_export_type_review_count();
				break;

			case 'coupon':
				$count = woo_ce_get_export_type_coupon_count();
				break;

			case 'shipping_class':
				$count = woo_ce_get_export_type_shipping_class_count();
				break;

			case 'attribute':
				$count = woo_ce_get_export_type_attribute_count();
				break;

			// Allow Plugin/Theme authors to populate their own custom export type counts
			default:
				$count = 0;
				$count = apply_filters( 'woo_ce_get_export_type_count', $count, $export_type, $args );
				break;

		}
		if( isset( $count ) || $count_sql ) {
			if( isset( $count ) ) {
				if( is_object( $count ) ) {
					$count = (array)$count;
					$count = absint( array_sum( $count ) );
				}
				return $count;
			} else {
				if( $count_sql )
					$count = $wpdb->get_var( $count_sql );
				else
					$count = 0;
			}
			return $count;
		} else {
			return 0;
		}

	}

	// In-line display of export file and export details when viewed via WordPress Media screen
	function woo_ce_read_export_file( $post = false ) {

		if( empty( $post ) ) {
			if( isset( $_GET['post'] ) )
				$post = get_post( $_GET['post'] );
		}

		if( $post->post_type != 'attachment' )
			return;

		// Check if the Post matches one of our Post Mime Types
		if( !in_array( $post->post_mime_type, array_values( woo_ce_get_mime_types() ) ) )
			return;

		$filepath = get_attached_file( $post->ID );

		// We can only read CSV, TSV and XML file types, the others are encoded
		if( in_array( $post->post_mime_type, array( 'text/csv', 'text/tab-separated-values', 'application/xml', 'application/rss+xml' ) ) ) {

			$contents = __( 'No export entries were found, please try again with different export filters.', 'woocommerce-exporter' );
			if( file_exists( $filepath ) ) {
				$contents = file_get_contents( $filepath );
			} else {
				// This resets the _wp_attached_file Post meta key to the correct value
				update_attached_file( $post->ID, $post->guid );
				// Try grabbing the file contents again
				$filepath = get_attached_file( $post->ID );
				if( file_exists( $filepath ) ) {
					$handle = fopen( $filepath, "r" );
					$contents = stream_get_contents( $handle );
					fclose( $handle );
				}
			}
			if( !empty( $contents ) )
				include_once( WOO_CE_PATH . 'templates/admin/media-csv_file.php' );

		}

		// We can still show the Export Details for any supported Post Mime Type
		$export_type = get_post_meta( $post->ID, '_woo_export_type', true );
		$columns = get_post_meta( $post->ID, '_woo_columns', true );
		$rows = get_post_meta( $post->ID, '_woo_rows', true );
		$scheduled_id = get_post_meta( $post->ID, '_scheduled_id', true );
		$start_time = get_post_meta( $post->ID, '_woo_start_time', true );
		$end_time = get_post_meta( $post->ID, '_woo_end_time', true );
		$idle_memory_start = get_post_meta( $post->ID, '_woo_idle_memory_start', true );
		$data_memory_start = get_post_meta( $post->ID, '_woo_data_memory_start', true );
		$data_memory_end = get_post_meta( $post->ID, '_woo_data_memory_end', true );
		$idle_memory_end = get_post_meta( $post->ID, '_woo_idle_memory_end', true );

		include_once( WOO_CE_PATH . 'templates/admin/media-export_details.php' );

	}
	add_action( 'edit_form_after_editor', 'woo_ce_read_export_file' );

	// Returns label of Export type slug used on Store Exporter screen
	function woo_ce_export_type_label( $export_type = '', $echo = false ) {

		$output = '';
		if( !empty( $export_type ) ) {
			$export_types = woo_ce_get_export_types();
			if( array_key_exists( $export_type, $export_types ) )
				$output = $export_types[$export_type];
		}
		if( $echo )
			echo $output;
		else
			return $output;

	}

	// Returns a list of archived exports
	function woo_ce_get_archive_files() {

		$post_type = 'attachment';
		$meta_key = '_woo_export_type';
		$args = array(
			'post_type' => $post_type,
			'post_mime_type' => array_values( woo_ce_get_mime_types() ),
			'meta_key' => $meta_key,
			'meta_value' => null,
			'post_status' => 'any',
			'posts_per_page' => -1
		);
		if( isset( $_GET['filter'] ) ) {
			$filter = $_GET['filter'];
			if( !empty( $filter ) )
				$args['meta_value'] = $filter;
		}
		$files = get_posts( $args );
		return $files;

	}

	function woo_ce_nuke_archive_files() {

		$post_type = 'attachment';
		$meta_key = '_woo_export_type';
		$args = array(
			'post_type' => $post_type,
			'post_mime_type' => array_values( woo_ce_get_mime_types() ),
			'meta_key' => $meta_key,
			'meta_value' => null,
			'post_status' => 'any',
			'posts_per_page' => -1,
			'fields' => 'ids'
		);
		$post_query = new WP_Query( $args );
		if( !empty( $post_query->found_posts ) ) {
			foreach( $post_query->posts as $post )
				wp_delete_attachment( $post, true );
			return true;
		}

	}

	// Delete all WordPress Options generated by Store Exporter
	function woo_ce_nuke_options() {

		global $wpdb;

		$prefix = 'woo_ce_%';

		// Get a list of WordPress Options prefixed by woo_ce_
		$options_sql = $wpdb->prepare( "SELECT `option_name` FROM `" . $wpdb->prefix . "options` WHERE `option_name` LIKE %s", $prefix );
		$options = $wpdb->get_col( $options_sql );
		if( !empty( $options ) ) {
			$count = 0;
			// Get a count of WordPress Options to be deleted
			$size = count( $options );
			foreach( $options as $option ) {
				// Get a count of deleted WordPress Options
				if( delete_option( $option ) )
					$count++;
			}
			// Compare the count of WordPress Options vs deleted WordPress Options
			if( $count == $size )
				return true;
		}

	}

	// Reset all dismissed notices within Store Exporter
	function woo_ce_nuke_dismissed_notices() {

		global $wpdb;

		$prefix = 'woo_ce_dismiss_%';

		// Get a list of WordPress Options prefixed by woo_ce_dismiss_
		$options_sql = $wpdb->prepare( "SELECT `option_name` FROM `" . $wpdb->prefix . "options` WHERE `option_name` LIKE %s", $prefix );
		$options = $wpdb->get_col( $options_sql );
		if( !empty( $options ) ) {
			foreach( $options as $option )
				delete_option( $option );
		}

	}

	// Returns a list of Attachments which are exposed to the public
	function woo_ce_get_unprotected_archives( $postarr = array() ) {

		$post_type = 'attachment';
		$meta_key = '_woo_export_type';
		$args = array(
			'post_type' => $post_type,
			'post_mime_type' => array_values( woo_ce_get_mime_types() ),
			'meta_key' => $meta_key,
			'post_status' => 'inherit',
			'posts_per_page' => -1,
			'fields' => 'ids'
		);
		$args = wp_parse_args( $postarr, $args );
		$post_query = new WP_Query( $args );
		if( !empty( $post_query->found_posts ) ) {
			// Check if we are returning a count or list
			if( isset( $postarr['count'] ) ) {
				return $post_query->found_posts;
			}
			return $post_query->posts;
		}

	}

	function woo_ce_update_archives_privacy() {

		$attachments = woo_ce_get_unprotected_archives();
		if( !empty( $attachments ) ) {
			foreach( $attachments as $post_ID ) {
				$args = array(
					'ID' => $post_ID,
					'post_status' => 'private'
				);
				wp_update_post( $args );
			}
			return true;
		}

	}

	// Returns an archived export with additional details
	function woo_ce_get_archive_file( $file = '' ) {

		$wp_upload_dir = wp_upload_dir();
		$file->export_type = get_post_meta( $file->ID, '_woo_export_type', true );
		$file->export_type_label = woo_ce_export_type_label( $file->export_type );
		if( empty( $file->export_type ) )
			$file->export_type = __( 'Unassigned', 'woocommerce-exporter' );
		if( empty( $file->guid ) )
			$file->guid = $wp_upload_dir['url'] . '/' . basename( $file->post_title );
		$file->post_mime_type = get_post_mime_type( $file->ID );
		if( !$file->post_mime_type )
			$file->post_mime_type = __( 'N/A', 'woocommerce-exporter' );
		$file->media_icon = wp_get_attachment_image( $file->ID, array( 80, 60 ), true );
		if( $author_name = get_user_by( 'id', $file->post_author ) )
			$file->post_author_name = $author_name->display_name;
		$file->post_date = woo_ce_format_archive_date( $file->ID );
		unset( $author_name, $t_time, $time );
		return $file;

	}

	// HTML template for displaying the current export type filter on the Archives screen
	function woo_ce_archives_quicklink_current( $current = '' ) {

		$output = '';
		if( isset( $_GET['filter'] ) ) {
			$filter = $_GET['filter'];
			if( $filter == $current )
				$output = ' class="current"';
		} else if( $current == 'all' ) {
			$output = ' class="current"';
		}
		echo $output;

	}

	// HTML template for displaying the number of each export type filter on the Archives screen
	function woo_ce_archives_quicklink_count( $type = '' ) {

		$post_type = 'attachment';
		$meta_key = '_woo_export_type';
		$args = array(
			'post_type' => $post_type,
			'meta_key' => $meta_key,
			'meta_value' => null,
			'numberposts' => -1,
			'post_status' => 'any',
			'fields' => 'ids'
		);
		if( !empty( $type ) )
			$args['meta_value'] = $type;
		$post_query = new WP_Query( $args );
		return absint( $post_query->found_posts );

	}

	/* End of: WordPress Administration */

}

// Export process for CSV file
function woo_ce_export_dataset( $export_type = null, &$output = null ) {

	global $export;

	$separator = $export->delimiter;
	$export->columns = array();
	$export->total_rows = 0;
	$export->total_columns = 0;

	$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/usage/';

	set_transient( WOO_CE_PREFIX . '_running', time(), woo_ce_get_option( 'timeout', MINUTE_IN_SECONDS ) );

	// Load up the fatal error notice if we 500 Internal Server Error (memory), hit a server timeout or encounter a fatal PHP error
	add_action( 'shutdown', 'woo_ce_fatal_error' );

	// Drop in our content filters here
	add_filter( 'sanitize_key', 'woo_ce_sanitize_key' );
	add_filter( 'attribute_escape', 'woo_ce_attribute_escape', 10, 2 );

	switch( $export_type ) {

		// Products
		case 'product':
			$fields = woo_ce_get_product_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_product_field( $key );
			}
			$export->total_columns = $size = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( $products = woo_ce_get_products( $export->args ) ) {
				$export->total_rows = count( $products );
				// Generate the export headers
				if( in_array( $export->export_format, array( 'csv' ) ) ) {
					for( $i = 0; $i < $size; $i++ ) {
						if( $i == ( $size - 1 ) )
							$output .= woo_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . "\n";
						else
							$output .= woo_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . $separator;
					}
				}
				$weight_unit = get_option( 'woocommerce_weight_unit' );
				$dimension_unit = get_option( 'woocommerce_dimension_unit' );
				$height_unit = $dimension_unit;
				$width_unit = $dimension_unit;
				$length_unit = $dimension_unit;
				if( !empty( $export->fields ) ) {
					foreach( $products as $product ) {

						$product = woo_ce_get_product_data( $product, $export->args );
						foreach( $export->fields as $key => $field ) {
							if( isset( $product->$key ) ) {
								if( is_array( $field ) ) {
									foreach( $field as $array_key => $array_value ) {
										if( !is_array( $array_value ) ) {
											if( in_array( $export->export_format, array( 'csv' ) ) )
												$output .= woo_ce_escape_csv_value( $array_value, $export->delimiter, $export->escape_formatting );
										}
									}
								} else {
									if( in_array( $export->export_format, array( 'csv' ) ) )
										$output .= woo_ce_escape_csv_value( $product->$key, $export->delimiter, $export->escape_formatting );
								}
							}
							if( in_array( $export->export_format, array( 'csv' ) ) )
								$output .= $separator;
						}

						if( in_array( $export->export_format, array( 'csv' ) ) )
							$output = substr( $output, 0, -1 ) . "\n";
					}
				}
				unset( $products, $product );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Categories
		case 'category':
			$fields = woo_ce_get_category_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_category_field( $key );
			}
			$export->total_columns = $size = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			$category_args = array(
				'orderby' => ( isset( $export->args['category_orderby'] ) ? $export->args['category_orderby'] : 'ID' ),
				'order' => ( isset( $export->args['category_order'] ) ? $export->args['category_order'] : 'ASC' ),
			);
			if( $categories = woo_ce_get_product_categories( $category_args ) ) {
				$export->total_rows = count( $categories );
				// Generate the export headers
				if( in_array( $export->export_format, array( 'csv' ) ) ) {
					for( $i = 0; $i < $size; $i++ ) {
						if( $i == ( $size - 1 ) )
							$output .= woo_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . "\n";
						else
							$output .= woo_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . $separator;
					}
				}
				if( !empty( $export->fields ) ) {
					foreach( $categories as $category ) {

						foreach( $export->fields as $key => $field ) {
							if( isset( $category->$key ) ) {
								if( in_array( $export->export_format, array( 'csv' ) ) )
									$output .= woo_ce_escape_csv_value( $category->$key, $export->delimiter, $export->escape_formatting );
							}
							if( in_array( $export->export_format, array( 'csv' ) ) )
								$output .= $separator;
						}
						if( in_array( $export->export_format, array( 'csv' ) ) )
							$output = substr( $output, 0, -1 ) . "\n";
					}
				}
				unset( $categories, $category );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Tags
		case 'tag':
			$fields = woo_ce_get_tag_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_tag_field( $key );
			}
			$export->total_columns = $size = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			$tag_args = array(
				'orderby' => ( isset( $export->args['tag_orderby'] ) ? $export->args['tag_orderby'] : 'ID' ),
				'order' => ( isset( $export->args['tag_order'] ) ? $export->args['tag_order'] : 'ASC' ),
			);
			if( $tags = woo_ce_get_product_tags( $tag_args ) ) {
				$export->total_rows = count( $tags );
				// Generate the export headers
				if( in_array( $export->export_format, array( 'csv' ) ) ) {
					for( $i = 0; $i < $size; $i++ ) {
						if( $i == ( $size - 1 ) )
							$output .= woo_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . "\n";
						else
							$output .= woo_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . $separator;
					}
				}
				if( !empty( $export->fields ) ) {
					foreach( $tags as $tag ) {

						foreach( $export->fields as $key => $field ) {
							if( isset( $tag->$key ) ) {
								if( in_array( $export->export_format, array( 'csv' ) ) )
									$output .= woo_ce_escape_csv_value( $tag->$key, $export->delimiter, $export->escape_formatting );
							}
							if( in_array( $export->export_format, array( 'csv' ) ) )
								$output .= $separator;
						}
						if( in_array( $export->export_format, array( 'csv' ) ) )
							$output = substr( $output, 0, -1 ) . "\n";
					}
				}
				unset( $tags, $tag );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Users
		case 'user':
			$fields = woo_ce_get_user_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_user_field( $key );
			}
			$export->total_columns = $size = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( $users = woo_ce_get_users( $export->args ) ) {
				// Generate the export headers
				if( in_array( $export->export_format, array( 'csv' ) ) ) {
					$i = 0;
					foreach( $export->columns as $column ) {
						if( $i == ( $size - 1 ) )
							$output .= woo_ce_escape_csv_value( $column, $export->delimiter, $export->escape_formatting ) . "\n";
						else
							$output .= woo_ce_escape_csv_value( $column, $export->delimiter, $export->escape_formatting ) . $separator;
						$i++;
					}
				}
				if( !empty( $export->fields ) ) {
					foreach( $users as $user ) {

						$user = woo_ce_get_user_data( $user, $export->args );

						foreach( $export->fields as $key => $field ) {
							if( isset( $user->$key ) ) {
								if( in_array( $export->export_format, array( 'csv' ) ) )
									$output .= woo_ce_escape_csv_value( $user->$key, $export->delimiter, $export->escape_formatting );
							}
							if( in_array( $export->export_format, array( 'csv' ) ) )
								$output .= $separator;
						}
						if( in_array( $export->export_format, array( 'csv' ) ) )
							$output = substr( $output, 0, -1 ) . "\n";

					}
				}
				unset( $users, $user );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

	}

	// Remove our content filters here to play nice with other Plugins
	remove_filter( 'sanitize_key', 'woo_ce_sanitize_key' );
	remove_filter( 'attribute_escape', 'woo_ce_attribute_escape' );

	// Remove our fatal error notice so not to conflict with the CRON or scheduled export engine	
	remove_action( 'shutdown', 'woo_ce_fatal_error' );

	// Export completed successfully
	delete_transient( WOO_CE_PREFIX . '_running' );

	// Check that the export file is populated, export columns have been assigned and rows counted
	if( $output && $export->total_rows && $export->total_columns ) {
		if( in_array( $export->export_format, array( 'csv' ) ) ) {
			$output = woo_ce_file_encoding( $output );
			if( $export->export_format == 'csv' && $export->bom && ( WOO_CE_DEBUG == false ) )
				$output = "\xEF\xBB\xBF" . $output;
		}
		if( WOO_CE_DEBUG && !$export->cron ) {
			$response = set_transient( WOO_CE_PREFIX . '_debug_log', base64_encode( $output ), woo_ce_get_option( 'timeout', MINUTE_IN_SECONDS ) );
			if( $response !== true ) {
				$message = __( 'The export contents were too large to store in a single WordPress transient, use the Volume offset / Limit volume options to reduce the size of your export and try again.', 'woocommerce-exporter' ) . ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
				if( function_exists( 'woo_ce_admin_notice' ) )
					woo_ce_admin_notice( $message, 'error' );
				else
					error_log( sprintf( '[store-exporter] woo_ce_export_dataset() - %s', $message ) );
			}
		} else {
			return $output;
		}
	}

}

function woo_ce_fatal_error() {

	global $export;

	$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter/usage/';

	$error = error_get_last();
	if( $error !== null ) {
		$message = '';
		$notice = sprintf( __( 'Refer to the following error and contact us on http://wordpress.org/plugins/woocommerce-exporter/ for further assistance. Error: <code>%s</code>', 'woocommerce-exporter' ), $error['message'] );
		if ( substr( $error['message'], 0, 22 ) === 'Maximum execution time' ) {
			$message = __( 'The server\'s maximum execution time is too low to complete this export. This is commonly due to a low timeout limit set by your hosting provider or PHP Safe Mode being enabled.', 'woocommerce-exporter' ) . ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
		} elseif ( substr( $error['message'], 0, 19 ) === 'Allowed memory size' ) {
			$message = __( 'The server\'s maximum memory size is too low to complete this export. Consider increasing available memory to WordPress or reducing the size of your export.', 'woocommerce-exporter' ) . ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
		} else if( $error['type'] === E_ERROR ) {
			// Test if it's WP All Import conflicting with the PHPExcel library
			if( substr( $error['message'], 0, 33 ) == "Class 'PHPExcel_Writer_Excel2007'" && ( strstr( $error['file'], 'wp-all-import' ) !== false ) ) {
				$message = __( 'A fatal PHP error was encountered during the export process, this was due to the Plugin WP All Import pre-loading the PHPExcel library. Contact the Plugin author Soflyy for more information.', 'woocommerce-exporter' );
			} else {
				$message = __( 'A fatal PHP error was encountered during the export process, we couldn\'t detect or diagnose it further.', 'woocommerce-exporter' ) . ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
			}
		}
		if( !empty( $message ) ) {

			// Save a record to the PHP error log
			woo_ce_error_log( sprintf( __( 'Fatal error: %s - PHP response: %s in %s on line %s', 'woocommerce-exporter' ), $message, $error['message'], $error['file'], $error['line'] ) );
			error_log( sprintf( __( 'Fatal error: %s - PHP response: %s in %s on line %s', 'woocommerce-exporter' ), $message, $error['message'], $error['file'], $error['line'] ) );

			// Only display the message if this is a manual export
			if( ( !$export->cron && !$export->scheduled_export ) ) {
				$output = '<div id="message" class="error"><p>' . sprintf( __( '<strong>[store-exporter]</strong> An unexpected error occurred. %s', 'woocommerce-exporter' ), $message ) . '</p><p>' . $notice . '</p></div>';
				echo $output;
			}

		}
	}

}

// List of Export types used on Store Exporter screen
function woo_ce_get_export_types() {

	$types = array(
		'product' => __( 'Products', 'woocommerce-exporter' ),
		'category' => __( 'Categories', 'woocommerce-exporter' ),
		'tag' => __( 'Tags', 'woocommerce-exporter' ),
		'user' => __( 'Users', 'woocommerce-exporter' )
	);
	$types = apply_filters( 'woo_ce_export_types', $types );
	return $types;

}

// Returns the Post object of the export file saved as an attachment to the WordPress Media library
function woo_ce_save_file_attachment( $filename = '', $post_mime_type = 'text/csv' ) {

	if( !empty( $filename ) ) {
		$post_type = 'woo-export';
		$args = array(
			'post_status' => 'private',
			'post_title' => $filename,
			'post_type' => $post_type,
			'post_mime_type' => $post_mime_type
		);
		$post_ID = wp_insert_attachment( $args, $filename );
		if( is_wp_error( $post_ID ) )
			woo_ce_error_log( sprintf( 'save_file_attachment() - $s: %s', $filename, $result->get_error_message() ) );
		else
			return $post_ID;
	}

}

// Updates the GUID of the export file attachment to match the correct file URL
function woo_ce_save_file_guid( $post_ID, $export_type, $upload_url = '' ) {

	add_post_meta( $post_ID, '_woo_export_type', $export_type );
	if( !empty( $upload_url ) ) {
		$args = array(
			'ID' => $post_ID,
			'guid' => $upload_url
		);
		wp_update_post( $args );
	}

}

// Save critical export details against the archived export
function woo_ce_save_file_details( $post_ID ) {

	global $export;

	add_post_meta( $post_ID, '_woo_start_time', $export->start_time );
	add_post_meta( $post_ID, '_woo_idle_memory_start', $export->idle_memory_start );
	add_post_meta( $post_ID, '_woo_columns', $export->total_columns );
	add_post_meta( $post_ID, '_woo_rows', $export->total_rows );
	add_post_meta( $post_ID, '_woo_data_memory_start', $export->data_memory_start );
	add_post_meta( $post_ID, '_woo_data_memory_end', $export->data_memory_end );

}

// Update detail of existing archived export
function woo_ce_update_file_detail( $post_ID, $detail, $value ) {

	if( strstr( $detail, '_woo_' ) !== false )
		update_post_meta( $post_ID, $detail, $value );

}

// Returns a list of allowed Export type statuses, can be overridden on a per-Export type basis
function woo_ce_post_statuses( $extra_status = array(), $override = false ) {

	$output = array(
		'publish',
		'pending',
		'draft',
		'future',
		'private',
		'trash'
	);
	if( $override ) {
		$output = $extra_status;
	} else {
		if( $extra_status )
			$output = array_merge( $output, $extra_status );
	}
	return $output;

}

function woo_ce_get_mime_types() {

	$mime_types = array(
		'csv' => 'text/csv',
		'tsv' => 'text/tab-separated-values',
		'xls' => 'application/vnd.ms-excel',
		'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'xml' => 'application/xml',
		'rss' => 'application/rss+xml'
	);
	return $mime_types;

}

function woo_ce_get_mime_type_extension( $mime_type, $search_by = 'extension' ) {

	$mime_types = woo_ce_get_mime_types();
	if( $search_by == 'extension' ) {
		if( isset( $mime_types[$mime_type] ) )
			return $mime_types[$mime_type];
	} else if( $search_by == 'mime_type' ) {
		if( $key = array_search( $mime_type, $mime_types ) )
			return strtoupper( $key );
	}

}

function woo_ce_add_missing_mime_type( $mime_types = array() ) {

	// Add CSV mime type if it has been removed
	if( !isset( $mime_types['csv'] ) )
		$mime_types['csv'] = 'text/csv';
	// Add TSV mime type if it has been removed
	if( !isset( $mime_types['tsv'] ) )
		$mime_types['tsv'] = 'text/tab-separated-values';
	// Add XLS mime type if it has been removed
	if( !isset( $mime_types['xls'] ) )
		$mime_types['xls'] = 'application/vnd.ms-excel';
	// Add XLSX mime type if it has been removed
	if( !isset( $mime_types['xlsx'] ) )
		$mime_types['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
	// Add XML mime type if it has been removed
	if( !isset( $mime_types['xml'] ) )
		$mime_types['xml'] = 'application/xml';
	// Add RSS mime type if it has been removed
	if( !isset( $mime_types['rss'] ) )
		$mime_types['rss'] = 'application/rss+xml';
	return $mime_types;

}
add_filter( 'upload_mimes', 'woo_ce_add_missing_mime_type' );

if( !function_exists( 'woo_ce_sort_fields' ) ) {
	function woo_ce_sort_fields( $key ) {

		return $key;

	}
}

// Add Store Export to filter types on the WordPress Media screen
function woo_ce_add_post_mime_type( $post_mime_types = array() ) {

	$post_mime_types['text/csv'] = array( __( 'Store Exports (CSV)', 'woocommerce-exporter' ), __( 'Manage Store Exports (CSV)', 'woocommerce-exporter' ), _n_noop( 'Store Export - CSV <span class="count">(%s)</span>', 'Store Exports - CSV <span class="count">(%s)</span>' ) );
	return $post_mime_types;

}
add_filter( 'post_mime_types', 'woo_ce_add_post_mime_type' );

function woo_ce_current_memory_usage() {

	$output = '';
	if( function_exists( 'memory_get_usage' ) )
		$output = round( memory_get_usage( true ) / 1024 / 1024, 2 );
	return $output;

}

function woo_ce_get_start_of_week_day() {

	global $wp_locale;

	$output = 'Monday';
	$start_of_week = get_option( 'start_of_week', 0 );
	for( $day_index = 0; $day_index <= 6; $day_index++ ) {
		if( $start_of_week == $day_index ) {
			$output = $wp_locale->get_weekday( $day_index );
			break;
		}
	}
	return $output;

}

function woo_ce_detect_product_brands() {

	if( class_exists( 'WC_Brands' ) || class_exists( 'woo_brands' ) || taxonomy_exists( apply_filters( 'woo_ce_brand_term_taxonomy', 'product_brand' ) ) )
		return true;

}

// List of WordPress Plugins that Store Exporter integrates with
function woo_ce_admin_modules_list( $module_status = false ) {

	$modules = array();
	$modules[] = array(
		'name' => 'aioseop',
		'title' => __( 'All in One SEO Pack', 'woocommerce-exporter' ),
		'description' => __( 'Optimize your WooCommerce Products for Search Engines. Requires Store Toolkit for All in One SEO Pack integration.', 'woocommerce-exporter' ),
		'url' => 'http://wordpress.org/extend/plugins/all-in-one-seo-pack/',
		'slug' => 'all-in-one-seo-pack',
		'function' => 'aioseop_activate'
	);
	$modules[] = array(
		'name' => 'store_toolkit',
		'title' => __( 'Store Toolkit', 'woocommerce-exporter' ),
		'description' => __( 'Store Toolkit includes a growing set of commonly-used WooCommerce administration tools aimed at web developers and store maintainers.', 'woocommerce-exporter' ),
		'url' => 'http://wordpress.org/extend/plugins/woocommerce-store-toolkit/',
		'slug' => 'woocommerce-store-toolkit',
		'function' => 'woo_st_admin_init'
	);
	$modules[] = array(
		'name' => 'ultimate_seo',
		'title' => __( 'SEO Ultimate', 'woocommerce-exporter' ),
		'description' => __( 'This all-in-one SEO plugin gives you control over Product details.', 'woocommerce-exporter' ),
		'url' => 'http://wordpress.org/extend/plugins/seo-ultimate/',
		'slug' => 'seo-ultimate',
		'function' => 'su_wp_incompat_notice'
	);
	$modules[] = array(
		'name' => 'gpf',
		'title' => __( 'Advanced Google Product Feed', 'woocommerce-exporter' ),
		'description' => __( 'Easily configure data to be added to your Google Merchant Centre feed.', 'woocommerce-exporter' ),
		'url' => 'http://www.leewillis.co.uk/wordpress-plugins/',
		'function' => 'woocommerce_gpf_install'
	);
	$modules[] = array(
		'name' => 'wpseo',
		'title' => __( 'WordPress SEO by Yoast', 'woocommerce-exporter' ),
		'description' => __( 'The first true all-in-one SEO solution for WordPress.', 'woocommerce-exporter' ),
		'url' => 'http://yoast.com/wordpress/seo/#utm_source=wpadmin&utm_medium=plugin&utm_campaign=wpseoplugin',
		'slug' => 'wordpress-seo',
		'function' => 'wpseo_admin_init'
	);
	$modules[] = array(
		'name' => 'msrp',
		'title' => __( 'WooCommerce MSRP Pricing', 'woocommerce-exporter' ),
		'description' => __( 'Define and display MSRP prices (Manufacturer\'s suggested retail price) to your customers.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/msrp-pricing/',
		'function' => 'woocommerce_msrp_activate'
	);
	$modules[] = array(
		'name' => 'wc_brands',
		'title' => __( 'WooCommerce Brands Addon', 'woocommerce-exporter' ),
		'description' => __( 'Create, assign and list brands for products, and allow customers to filter by brand.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/brands/',
		'class' => 'WC_Brands'
	);
	$modules[] = array(
		'name' => 'wc_cog',
		'title' => __( 'Cost of Goods', 'woocommerce-exporter' ),
		'description' => __( 'Easily track total profit and cost of goods by adding a Cost of Good field to simple and variable products.', 'woocommerce-exporter' ),
		'url' => 'http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/',
		'class' => 'WC_COG'
	);
	$modules[] = array(
		'name' => 'per_product_shipping',
		'title' => __( 'Per Product Shipping', 'woocommerce-exporter' ),
		'description' => __( 'Define separate shipping costs per product which are combined at checkout to provide a total shipping cost.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/per-product-shipping/',
		'class' => 'WC_Shipping_Per_Product_Init'
	);
	$modules[] = array(
		'name' => 'vendors',
		'title' => __( 'Product Vendors', 'woocommerce-exporter' ),
		'description' => __( 'Turn your store into a multi-vendor marketplace (such as Etsy or Creative Market).', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/product-vendors/',
		'class' => 'WC_Product_Vendors'
	);
	$modules[] = array(
		'name' => 'wc_vendors',
		'title' => __( 'WC Vendors', 'woocommerce-exporter' ),
		'description' => __( 'Allow vendors to sell their own products and receive a commission for each sale.', 'woocommerce-exporter' ),
		'url' => 'http://wcvendors.com',
		'class' => 'WC_Vendors'
	);
	$modules[] = array(
		'name' => 'acf',
		'title' => __( 'Advanced Custom Fields', 'woocommerce-exporter' ),
		'description' => __( 'Powerful fields for WordPress developers.', 'woocommerce-exporter' ),
		'url' => 'http://www.advancedcustomfields.com',
		'class' => 'acf'
	);
	$modules[] = array(
		'name' => 'product_addons',
		'title' => __( 'Product Add-ons', 'woocommerce-exporter' ),
		'description' => __( 'Allow your customers to customise your products by adding input boxes, dropdowns or a field set of checkboxes.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/product-add-ons/',
		'class' => 'Product_Addon_Admin'
	);
	$modules[] = array(
		'name' => 'seq',
		'title' => __( 'WooCommerce Sequential Order Numbers', 'woocommerce-exporter' ),
		'description' => __( 'This plugin extends the WooCommerce e-commerce plugin by setting sequential order numbers for new orders.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-sequential-order-numbers/',
		'slug' => 'woocommerce-sequential-order-numbers',
		'class' => 'WC_Seq_Order_Number'
	);
	$modules[] = array(
		'name' => 'seq_pro',
		'title' => __( 'WooCommerce Sequential Order Numbers Pro', 'woocommerce-exporter' ),
		'description' => __( 'Tame your WooCommerce Order Numbers.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/sequential-order-numbers-pro/',
		'class' => 'WC_Seq_Order_Number_Pro'
	);
	$modules[] = array(
		'name' => 'print_invoice_delivery_note',
		'title' => __( 'WooCommerce Print Invoice & Delivery Note', 'woocommerce-exporter' ),
		'description' => __( 'Print invoices and delivery notes for WooCommerce orders.', 'woocommerce-exporter' ),
		'url' => 'http://wordpress.org/plugins/woocommerce-delivery-notes/',
		'slug' => 'woocommerce-delivery-notes',
		'class' => 'WooCommerce_Delivery_Notes'
	);
	$modules[] = array(
		'name' => 'pdf_invoices_packing_slips',
		'title' => __( 'WooCommerce PDF Invoices & Packing Slips', 'woocommerce-exporter' ),
		'description' => __( 'Create, print & automatically email PDF invoices & packing slips for WooCommerce orders.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/',
		'slug' => 'woocommerce-pdf-invoices-packing-slips',
		'class' => 'WooCommerce_PDF_Invoices'
	);
	$modules[] = array(
		'name' => 'checkout_manager',
		'title' => __( 'WooCommerce Checkout Manager & WooCommerce Checkout Manager Pro', 'woocommerce-exporter' ),
		'description' => __( 'Manages the WooCommerce Checkout page and WooCommerce Checkout processes.', 'woocommerce-exporter' ),
		'url' => 'http://wordpress.org/plugins/woocommerce-checkout-manager/',
		'slug' => 'woocommerce-checkout-manager',
		'function' => array( 'wccs_install', 'wooccm_install', 'wccs_install_pro' )
	);
	$modules[] = array(
		'name' => 'pgsk',
		'title' => __( 'Poor Guys Swiss Knife', 'woocommerce-exporter' ),
		'description' => __( 'A Swiss Knife for WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'http://wordpress.org/plugins/woocommerce-poor-guys-swiss-knife/',
		'slug' => 'woocommerce-poor-guys-swiss-knife',
		'function' => 'wcpgsk_init'
	);
	$modules[] = array(
		'name' => 'checkout_field_editor',
		'title' => __( 'Checkout Field Editor', 'woocommerce-exporter' ),
		'description' => __( 'Add, edit and remove fields shown on your WooCommerce checkout page.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/woocommerce-checkout-field-editor/',
		'function' => 'woocommerce_init_checkout_field_editor'
	);
	$modules[] = array(
		'name' => 'checkout_field_manager',
		'title' => __( 'Checkout Field Manager', 'woocommerce-exporter' ),
		'description' => __( 'Quickly and effortlessly add, remove and re-orders fields in the checkout process.', 'woocommerce-exporter' ),
		'url' => 'http://61extensions.com/shop/woocommerce-checkout-field-manager/',
		'function' => 'sod_woocommerce_checkout_manager_settings'
	);
	$modules[] = array(
		'name' => 'checkout_addons',
		'title' => __( 'WooCommerce Checkout Add-Ons', 'woocommerce-exporter' ),
		'description' => __( 'Add fields at checkout for add-on products and services while optionally setting a cost for each add-on.', 'woocommerce-exporter' ),
		'url' => 'http://www.skyverge.com/product/woocommerce-checkout-add-ons/',
		'function' => 'init_woocommerce_checkout_add_ons'
	);
	$modules[] = array(
		'name' => 'local_pickup_plus',
		'title' => __( 'Local Pickup Plus', 'woocommerce-exporter' ),
		'description' => __( 'Let customers pick up products from specific locations.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/local-pickup-plus/',
		'class' => 'WC_Local_Pickup_Plus'
	);
	$modules[] = array(
		'name' => 'gravity_forms',
		'title' => __( 'Gravity Forms', 'woocommerce-exporter' ),
		'description' => __( 'Gravity Forms is hands down the best contact form plugin for WordPress powered websites.', 'woocommerce-exporter' ),
		'url' => 'http://www.gravityforms.com/',
		'class' => 'RGForms'
	);
	$modules[] = array(
		'name' => 'currency_switcher',
		'title' => __( 'WooCommerce Currency Switcher', 'woocommerce-exporter' ),
		'description' => __( 'Currency Switcher for WooCommerce allows your shop to display prices and accept payments in multiple currencies.', 'woocommerce-exporter' ),
		'url' => 'http://aelia.co/shop/currency-switcher-woocommerce/',
		'class' => 'WC_Aelia_CurrencySwitcher'
	);
	$modules[] = array(
		'name' => 'subscriptions',
		'title' => __( 'WooCommerce Subscriptions', 'woocommerce-exporter' ),
		'description' => __( 'WC Subscriptions makes it easy to create and manage products with recurring payments.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/woocommerce-subscriptions/',
		'class' => 'WC_Subscriptions_Manager'
	);
	$modules[] = array(
		'name' => 'extra_product_options',
		'title' => __( 'Extra Product Options', 'woocommerce-exporter' ),
		'description' => __( 'Create extra price fields globally or per-Product', 'woocommerce-exporter' ),
		'url' => 'http://codecanyon.net/item/woocommerce-extra-product-options/7908619',
		'class' => 'TM_Extra_Product_Options'
	);
	$modules[] = array(
		'name' => 'woocommerce_jetpack',
		'title' => __( 'Booster for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Supercharge your WooCommerce site with these awesome powerful features (formally WooCommerce Jetpack).', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-jetpack/',
		'slug' => 'woocommerce-jetpack',
		'class' => 'WC_Jetpack'
	);
	$modules[] = array(
		'name' => 'woocommerce_jetpack_plus',
		'title' => __( 'Booster Plus', 'woocommerce-exporter' ),
		'description' => __( 'Unlock all WooCommerce Booster features and supercharge your WordPress WooCommerce site even more (formally WooCommerce Jetpack Plus).', 'woocommerce-exporter' ),
		'url' => 'http://woojetpack.com/shop/wordpress-woocommerce-jetpack-plus/',
		'class' => 'WC_Jetpack_Plus'
	);
	$modules[] = array(
		'name' => 'woocommerce_brands',
		'title' => __( 'WooCommerce Brands', 'woocommerce-exporter' ),
		'description' => __( 'Woocommerce Brands Plugin. After Install and active this plugin you\'ll have some shortcode and some widget for display your brands in fornt-end website.', 'woocommerce-exporter' ),
		'url' => 'http://proword.net/Woocommerce_Brands/',
		'class' => 'woo_brands'
	);
	$modules[] = array(
		'name' => 'woocommerce_bookings',
		'title' => __( 'WooCommerce Bookings', 'woocommerce-exporter' ),
		'description' => __( 'Setup bookable products such as for reservations, services and hires.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/woocommerce-bookings/',
		'class' => 'WC_Bookings'
	);
	$modules[] = array(
		'name' => 'eu_vat',
		'title' => __( 'WooCommerce EU VAT Number', 'woocommerce-exporter' ),
		'description' => __( 'The EU VAT Number extension lets you collect and validate EU VAT numbers during checkout to identify B2B transactions verses B2C.', 'woocommerce-exporter' ),
		'url' => 'https://www.woothemes.com/products/eu-vat-number/',
		'function' => '__wc_eu_vat_number_init'
	);
	$modules[] = array(
		'name' => 'aelia_eu_vat',
		'title' => __( 'WooCommerce EU VAT Assistant', 'woocommerce-exporter' ),
		'description' => __( 'Assists with EU VAT compliance, for the new VAT regime beginning 1st January 2015.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-eu-vat-assistant/',
		'slug' => 'woocommerce-eu-vat-assistant',
		'class' => 'Aelia_WC_RequirementsChecks'
	);
	$modules[] = array(
		'name' => 'hear_about_us',
		'title' => __( 'WooCommerce Hear About Us', 'woocommerce-exporter' ),
		'description' => __( 'Ask where your new customers come from at Checkout.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-hear-about-us/',
		'slug' => 'woocommerce-hear-about-us', // Define this if the Plugin is hosted on the WordPress repo
		'class' => 'WooCommerce_HearAboutUs'
	);
	$modules[] = array(
		'name' => 'wholesale_pricing',
		'title' => __( 'WooCommerce Wholesale Pricing', 'woocommerce-exporter' ),
		'description' => __( 'Allows you to set wholesale prices for products and variations.', 'woocommerce-exporter' ),
		'url' => 'http://ignitewoo.com/woocommerce-extensions-plugins-themes/woocommerce-wholesale-pricing/',
		'class' => 'woocommerce_wholesale_pricing'
	);
	$modules[] = array(
		'name' => 'woocommerce_barcodes',
		'title' => __( 'Barcodes for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Allows you to add GTIN (former EAN) codes natively to your products.', 'woocommerce-exporter' ),
		'url' => 'http://www.wolkenkraft.com/produkte/barcodes-fuer-woocommerce/',
		'function' => 'wpps_requirements_met'
	);
	$modules[] = array(
		'name' => 'woocommerce_smart_coupons',
		'title' => __( 'WooCommerce Smart Coupons', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce Smart Coupons lets customers buy gift certificates, store credits or coupons easily.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/smart-coupons/',
		'class' => 'WC_Smart_Coupons'
	);
	$modules[] = array(
		'name' => 'woocommerce_preorders',
		'title' => __( 'WooCommerce Pre-Orders', 'woocommerce-exporter' ),
		'description' => __( 'Sell pre-orders for products in your WooCommerce store.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/woocommerce-pre-orders/',
		'class' => 'WC_Pre_Orders'
	);
	$modules[] = array(
		'name' => 'order_numbers_basic',
		'title' => __( 'WooCommerce Basic Ordernumbers', 'woocommerce-exporter' ),
		'description' => __( 'Lets the user freely configure the order numbers in WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'http://open-tools.net/woocommerce/advanced-ordernumbers-for-woocommerce.html',
		'class' => 'OpenToolsOrdernumbersBasic'
	);
	$modules[] = array(
		'name' => 'admin_custom_order_fields',
		'title' => __( 'WooCommerce Admin Custom Order Fields', 'woocommerce-exporter' ),
		'description' => __( 'Easily add custom fields to your WooCommerce orders and display them in the Orders admin, the My Orders section and order emails.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/woocommerce-admin-custom-order-fields/',
		'function' => 'init_woocommerce_admin_custom_order_fields'
	);
	$modules[] = array(
		'name' => 'table_rate_shipping_plus',
		'title' => __( 'WooCommerce Table Rate Shipping Plus', 'woocommerce-exporter' ),
		'description' => __( 'Calculate shipping costs based on destination, weight and price.', 'woocommerce-exporter' ),
		'url' => 'http://mangohour.com/plugins/woocommerce-table-rate-shipping',
		'function' => 'mh_wc_table_rate_plus_init'
	);
	$modules[] = array(
		'name' => 'woocommerce-extra-checkout-fields-for-brazil',
		'title' => __( 'WooCommerce Extra Checkout Fields for Brazil', 'woocommerce-exporter' ),
		'description' => __( 'Adds Brazilian checkout fields in WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/',
		'slug' => 'woocommerce-extra-checkout-fields-for-brazil',
		'class' => 'Extra_Checkout_Fields_For_Brazil'
	);
	$modules[] = array(
		'name' => 'woocommerce_gravityforms',
		'title' => __( 'WooCommerce Gravity Forms Product Add-Ons', 'woocommerce-exporter' ),
		'description' => __( 'Allows you to use Gravity Forms on individual WooCommerce products.', 'woocommerce-exporter' ),
		'url' => 'https://www.woothemes.com/products/gravity-forms-add-ons/',
		'class' => 'woocommerce_gravityforms'
	);
	$modules[] = array(
		'name' => 'woocommerce_quickdonation',
		'title' => __( 'WooCommerce Quick Donation', 'woocommerce-exporter' ),
		'description' => __( 'Turns WooCommerce into online donation.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-quick-donation/',
		'slug' => 'woocommerce-quick-donation',
		'class' => 'WooCommerce_Quick_Donation'
	);
	$modules[] = array(
		'name' => 'woocommerce_easycheckout',
		'title' => __( 'Easy Checkout Fields Editor', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce Easy Checkout Fields Editor', 'woocommerce-exporter' ),
		'url' => 'http://codecanyon.net/item/woocommerce-easy-checkout-field-editor/9799777',
		'function' => 'pcmfe_admin_form_field'
	);
	$modules[] = array(
		'name' => 'woocommerce_productfees',
		'title' => __( 'Product Fees', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce Easy Checkout Fields Editor', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-product-fees/',
		'slug' => 'woocommerce-product-fees',
		'class' => 'WooCommerce_Product_Fees'
	);
	$modules[] = array(
		'name' => 'woocommerce_events',
		'title' => __( 'WooCommerce Events', 'woocommerce-exporter' ),
		'description' => __( 'Adds event and ticketing features to WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'http://www.woocommerceevents.com/',
		'class' => 'WooCommerce_Events'
	);
	$modules[] = array(
		'name' => 'woocommerce_tabmanager',
		'title' => __( 'WooCommerce Tab Manager', 'woocommerce-exporter' ),
		'description' => __( 'A product tab manager for WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/woocommerce-tab-manager/',
		'class' => 'WC_Tab_Manager'
	);
	$modules[] = array(
		'name' => 'woocommerce_customfields',
		'title' => __( 'WooCommerce Custom Fields', 'woocommerce-exporter' ),
		'description' => __( 'Create custom fields for WooCommerce product and checkout pages.', 'woocommerce-exporter' ),
		'url' => 'http://www.rightpress.net/woocommerce-custom-fields',
		'class' => 'RP_WCCF'
	);
	$modules[] = array(
		'name' => 'barcode_isbn',
		'title' => __( 'WooCommerce Barcode & ISBN', 'woocommerce-exporter' ),
		'description' => __( 'A plugin to add a barcode & ISBN to WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-barcode-isbn/',
		'slug' => 'woocommerce-barcode-isbn',
		'function' => 'woo_add_barcode'
	);
	$modules[] = array(
		'name' => 'video_product_tab',
		'title' => __( 'WooCommerce Video Product Tab', 'woocommerce-exporter' ),
		'description' => __( 'Extends WooCommerce to allow you to add a Video to the Product page.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-video-product-tab/',
		'slug' => 'woocommerce-video-product-tab',
		'class' => 'WooCommerce_Video_Product_Tab'
	);
	$modules[] = array(
		'name' => 'external_featured_image',
		'title' => __( 'Nelio External Featured Image', 'woocommerce-exporter' ),
		'description' => __( 'Use external images from anywhere as the featured image of your pages and posts.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/external-featured-image/',
		'slug' => 'external-featured-image', // Define this if the Plugin is hosted on the WordPress repo
		'function' => '_nelioefi_url'
	);
	$modules[] = array(
		'name' => 'variation_swatches_photos',
		'title' => __( 'WooCommerce Variation Swatches and Photos', 'woocommerce-exporter' ),
		'description' => __( 'Configure colors and photos for shoppers on your site to use when picking variations.', 'woocommerce-exporter' ),
		'url' => 'https://www.woothemes.com/products/variation-swatches-and-photos/',
		'class' => 'WC_SwatchesPlugin'
	);
	$modules[] = array(
		'name' => 'uploads',
		'title' => __( 'WooCommerce Uploads', 'woocommerce-exporter' ),
		'description' => __( 'Upload files in WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'https://wpfortune.com/shop/plugins/woocommerce-uploads/',
		'class' => 'WPF_Uploads'
	);
	$modules[] = array(
		'name' => 'posr',
		'title' => __( 'WooCommerce Profit of Sales Report', 'woocommerce-exporter' ),
		'description' => __( 'This plugin provides Profit of Sales Report based on Cost of Goods.', 'woocommerce-exporter' ),
		'url' => 'http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590',
		'function' => 'POSRFront' // Define this for function detection, if Class rename attribute to class
	);
	$modules[] = array(
		'name' => 'orddd',
		'title' => __( 'Order Delivery Date Pro for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Allows customers to choose their preferred Order Delivery Date & Delivery Time during checkout.', 'woocommerce-exporter' ),
		'url' => 'https://www.tychesoftwares.com/store/premium-plugins/order-delivery-date-for-woocommerce-pro-21/',
		'class' => 'order_delivery_date'
	);

/*
	$modules[] = array(
		'name' => '',
		'title' => __( '', 'woocommerce-exporter' ),
		'description' => __( '', 'woocommerce-exporter' ),
		'url' => '',
		'slug' => '', // Define this if the Plugin is hosted on the WordPress repo
		'function' => '' // Define this for function detection, if Class rename attribute to class
	);
*/

	$modules = apply_filters( 'woo_ce_modules_addons', $modules );

	// Check if the existing Transient exists
	$modules_all = count( $modules );
	$cached = get_transient( WOO_CE_PREFIX . '_modules_all_count' );
	if( $cached == false ) {
		set_transient( WOO_CE_PREFIX . '_modules_all_count', $modules_all, DAY_IN_SECONDS );
	}

	$modules_active = 0;
	$modules_inactive = 0;

	if( !empty( $modules ) ) {
		$user_capability = 'install_plugins';
		foreach( $modules as $key => $module ) {
			$modules[$key]['status'] = 'inactive';
			// Check if each module is activated
			if( isset( $module['function'] ) ) {
				if( is_array( $module['function'] ) ) {
					$size = count( $module['function'] );
					for( $i = 0; $i < $size; $i++ ) {
						if( function_exists( $module['function'][$i] ) ) {
							$modules[$key]['status'] = 'active';
							$modules_active++;
							break;
						}
					}
				} else {
					if( function_exists( $module['function'] ) ) {
						$modules[$key]['status'] = 'active';
						$modules_active++;
					}
				}
			} else if( isset( $module['class'] ) ) {
				if( is_array( $module['class'] ) ) {
					$size = count( $module['class'] );
					for( $i = 0; $i < $size; $i++ ) {
						if( class_exists( $module['class'][$i] ) ) {
							$modules[$key]['status'] = 'active';
							$modules_active++;
							break;
						}
					}
				} else {
					if( class_exists( $module['class'] ) ) {
						$modules[$key]['status'] = 'active';
						$modules_active++;
					}
				}
			}
			// Filter Modules by Module Status
			if( !empty( $module_status ) ) {
				switch( $module_status ) {

					case 'active':
						if( $modules[$key]['status'] == 'inactive' )
							unset( $modules[$key] );
						break;

					case 'inactive':
						if( $modules[$key]['status'] == 'active' )
							unset( $modules[$key] );
						break;

				}
			}
			// Check that we've got these resources available
			if( isset( $modules[$key] ) && function_exists( 'current_user_can' ) && did_action( 'init' ) ) {
				// Check if the Plugin has a slug and if User can install Plugins
				if( current_user_can( $user_capability ) && isset( $module['slug'] ) )
					$modules[$key]['url'] = admin_url( sprintf( 'plugin-install.php?tab=search&type=term&s=%s', $module['slug'] ) );
			}
		}
	}

	// Check if the existing Transient exists
	$cached = get_transient( WOO_CE_PREFIX . '_modules_active_count' );
	if( $cached == false ) {
		set_transient( WOO_CE_PREFIX . '_modules_active_count', $modules_active, DAY_IN_SECONDS );
	}

	// Check if the existing Transient exists
	$cached = get_transient( WOO_CE_PREFIX . '_modules_inactive_count' );
	if( $cached == false ) {
		$modules_inactive = $modules_all - $modules_active;
		set_transient( WOO_CE_PREFIX . '_modules_inactive_count', $modules_inactive, DAY_IN_SECONDS );
	}

	return $modules;

}

function woo_ce_error_log( $message = '' ) {

	if( $message == '' )
		return;

	if( class_exists( 'WC_Logger' ) ) {
		$logger = new WC_Logger();
		$logger->add( WOO_CE_PREFIX, $message );
		return true;
	} else {
		// Fallback where the WooCommerce logging engine is unavailable
		error_log( sprintf( '[store-exporter] %s', $message ) );
	}

}

function woo_ce_error_get_last_message() {

	$output = '-';
	if( function_exists( 'error_get_last' ) ) {
		$last_error = error_get_last();
		if( isset( $last_error ) && isset( $last_error['message'] ) ) {
			$output = $last_error['message'];
		}
		unset( $last_error );
	}
	return $output;

}

function woo_ce_get_option( $option = null, $default = false, $allow_empty = false ) {

	$output = false;
	if( $option !== null ) {
		$separator = '_';
		$output = get_option( WOO_CE_PREFIX . $separator . $option, $default );
		if( $allow_empty == false && $output != 0 && ( $output == false || $output == '' ) )
			$output = $default;
	}
	return $output;

}

function woo_ce_update_option( $option = null, $value = null ) {

	$output = false;
	if( $option !== null && $value !== null ) {
		$separator = '_';
		$output = update_option( WOO_CE_PREFIX . $separator . $option, $value );
	}
	return $output;

}
?>