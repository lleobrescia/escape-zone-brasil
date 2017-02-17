/* global jQuery */
jQuery( document ).ready( function( $ ) {
    
    $( "#" + ucfw_coupons_admin_params.coupon_url_id ).after( 
        '<a class="button button-secondary" id="copy-coupon-url" data-clipboard-target="#' + ucfw_coupons_admin_params.coupon_url_id + '" style="cursor: pointer; height: 26px; border-top-left-radius: 0; border-bottom-left-radius: 0;">' +
            '<img src="' + ucfw_coupons_admin_params.img_root_url + 'clippy.svg" style="position: relative; top: 3px; width: 16px; height: auto;">' +
        '</a>'
    );

    var clipboard = new Clipboard( '#copy-coupon-url' );

    clipboard.on( 'success' , function( e ) {

        e.clearSelection();
        alert( ucfw_coupons_admin_params.i18n_copied );

    } );

    clipboard.on( 'error' , function( e ) {

        alert( ucfw_coupons_admin_params.i18n_failed_to_copy );

    } );

} );
