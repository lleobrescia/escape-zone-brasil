<?php
namespace UCFW\Helpers\Functions;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Basic logging.
 *
 * @since 1.0.0
 * @param mixed Data to log.
 */
function write_log( $log )  {

    if ( is_array( $log ) || is_object( $log ) )
        error_log( print_r( $log , true ) );
    else
        error_log( $log );

}

/**
 * Check if current user is authorized to manage the plugin on the backend.
 * 
 * @since 1.0.0
 *
 * @return boolean True if authorized, False otherwise.
 */
function current_user_authorized( $user = null ) {

    // Array of roles allowed to access/utilize the plugin
    $admin_roles = apply_filters( 'ucfw_admin_roles' , array( 'administrator' ) );

    if ( is_null( $user ) )
        $user = wp_get_current_user();

    if ( $user->ID )
        return count( array_intersect( ( array ) $user->roles , $admin_roles ) ) ? true : false;
    else
        return false;
    
}

/**
 * Returns the timezone string for a site, even if it's set to a UTC offset
 * 
 * Adapted from http://www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
 * 
 * Reference:
 * http://www.skyverge.com/blog/down-the-rabbit-hole-wordpress-and-timezones/
 * 
 * @since 1.0.0
 *
 * @return string Valid PHP timezone string
 */
function get_site_current_timezone() {

    // if site timezone string exists, return it
    if ( $timezone = get_option( 'timezone_string' ) )
        return $timezone;

    // get UTC offset, if it isn't set then return UTC
    if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) )
        return 'UTC';

    return convert_utc_offset_to_timezone( $utc_offset );
    
}

/**
 * Conver UTC offset to timezone.
 *
 * @since 1.2.0
 * @access public
 *
 * @param float/int/sting $utc_offset UTC offset.
 * @return string valid PHP timezone string
 */
function convert_utc_offset_to_timezone( $utc_offset ) {

    // adjust UTC offset from hours to seconds
    $utc_offset *= 3600;

    // attempt to guess the timezone string from the UTC offset
    if ( $timezone = timezone_name_from_abbr( '' , $utc_offset , 0 ) )
        return $timezone;
    
    // last try, guess timezone string manually
    $is_dst = date( 'I' );

    foreach ( timezone_abbreviations_list() as $abbr )
        foreach ( $abbr as $city )
            if ( $city[ 'dst' ] == $is_dst && $city[ 'offset' ] == $utc_offset )
                return $city[ 'timezone_id' ];
    
    // fallback to UTC
    return 'UTC';

}

/**
 * Get all user roles.
 *
 * @since 1.0.0
 * @access public
 *
 * @global WP_Roles $wp_roles Core class used to implement a user roles API.
 *
 * @return array Array of all site registered user roles. User role key as the key and value is user role text.
 */
function get_all_user_roles() {

    global $wp_roles;
    return $wp_roles->get_names();

}

/**
 * Check validity of a save post action.
 *
 * @since 1.0.0
 * @access private
 *
 * @param int    $post_id   Id of the coupon post.
 * @param string $post_type Post type to check.
 * @return bool True if valid save post action, False otherwise.
 */
function check_if_valid_save_post_action( $post_id , $post_type ) {

    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || !current_user_can( 'edit_page' , $post_id ) || get_post_type() != $post_type || empty( $_POST ) )
        return false;
    else
        return true;
    
}