jQuery( function($){
	$( document ).ready( function() {
		
		/*
		 * Settings Page.
		 */
		
		// Toggle Settings
		var expiration_row = $('#cxccoo_user_role_heirarchy').closest('tr');
		var expiration_checked = $("#cxccoo_user_role_selection:checked").length;

		var expiration_opt_in = $("#cxccoo_user_role_selection");

		if (expiration_checked == 0) {
			expiration_row.hide();
		}

		$('#cxccoo_user_role_selection').click(function() {
			expiration_row.toggle();
		});

		
		/*
		 * Address's on Order Page.
		 */
		
		// Move the Save to Address checkboxes to their correct places - no filter to get them exactly where we want them.
		if ( $('.order_data_column .load_customer_billing').parents('.order_data_column').find('h4').length ) {
			
			// Backwards Compat - Old WC.
			var heading = $('.order_data_column .load_customer_billing').parents('.order_data_column').find('h4');
			heading.prepend( $('.cxccoo-save-billing-address') );

			var heading = $('.order_data_column .billing-same-as-shipping').parents('.order_data_column').find('h4');
			heading.prepend( $('.cxccoo-save-shipping-address') );
		}
		else {

			// Backwards Compat - New WC.
			var heading = $('.order_data_column .load_customer_billing').parents('.order_data_column').find('h3');
			heading.append( $('.cxccoo-save-billing-address') );

			var heading = $('.order_data_column .billing-same-as-shipping').parents('.order_data_column').find('h3');
			heading.append( $('.cxccoo-save-shipping-address') );
			
			$('.cxccoo-save-billing-address').addClass('cxccoo-order-save-actions-spacing');
			$('.cxccoo-save-shipping-address').addClass('cxccoo-order-save-actions-spacing');
		}

		// Only show 'Save To User' checkboxes when 'Edit Address' is clicked
		$( document ).on( 'click', 'a.edit_address', function() {
			var column_holder = $( this ).parents( '.order_data_column' );
			column_holder.find( '.cxccoo-order-save-actions' ).show();
			//column_holder.find( 'input[type="checkbox"]' ).attr('checked', true);
		});
		
		
		/*
		 * `Create Customer` Form.
		 */
		
		var $i = 0;
		$( woocommerce_create_customer_order_params.select_search_inputs ).each( function(){
			
			// Debugging: note the index for internal use.
			$i++;
			
			// Cache elements.
			var $wc_search_input = $(this);
			var $wc_search_input_container = $wc_search_input.parent();
			var $create_customer_container = $( $('#cxccoo-create-customer-template').html() );
			var $create_customer_modal = $create_customer_container.find('.cxccoo-create-form');
		
			// Debugging: not the number of the modal, so we can check it's the correct modal for the button pushed.
			// $create_customer_container.find('[class$="create-user-main-button"]').append( $( '<i style="font-weight: normal;">&nbsp; ID: <strong>' + $i + '</strong></i>' ) );
			// $create_customer_container.find('[class$="modal-title"]').append( $( '<i style="font-weight: normal;">&nbsp; ID: <strong>' + $i + '</strong></i>' ) );
			
			// Add `Create New Customer` interface to after any of the `Search Customer` interfaces (with some specificity/restrictions so we only add them where we know it will work).
			$wc_search_input_container.append( $create_customer_container );
			
			// Handle `Create New Customer` button.
			$create_customer_container.find('.cxccoo-button.cxccoo-create-user-main-button').click(function() {

				open_modal(
					$create_customer_modal,
					{
						position            : 'center',
						close_button        : true,
						close_click_outside : false,
					}
				);
				
				return false;
			});
			
			// Handle pushing `enter/return` on `Create Customer` form.
			$create_customer_modal.find('input').keypress(function(e){
				if ( e.keyCode == 13 ) {
					$('.cxccoo-create-user-form-submit').click();
					return false;
				}
			});
			
			// Handle Cancel button on `Create Customer` form.
			$create_customer_modal.find('.cxccoo-button.cxccoo-create-user-form-cancel').click(function() {
				close_modal();
				return false;
			});
			
			// Debug: Auto Open Form.
			//$('.cxccoo-button.cxccoo-create-user-main-button').click();

			// Main `Create Customer` action.
			$create_customer_modal.find('.cxccoo-button.cxccoo-create-user-form-submit').click(function() {
				
				// Get values.
				var email_address = $.trim( $create_customer_modal.find( '#cxccoo_email_address' ).val() );
				var first_name    = $.trim( $create_customer_modal.find( '#cxccoo_first_name' ).val() );
				var last_name     = $.trim( $create_customer_modal.find( '#cxccoo_last_name' ).val() );
				var username      = $.trim( $create_customer_modal.find( '#cxccoo_username' ).val() );
				var user_role     = $.trim( $create_customer_modal.find( '#cxccoo_role' ).val() );
				var disable_email = ( $create_customer_modal.find( '#cxccoo_disable_email' ).is(':checked') ) ? 'true' : 'false';

				$create_customer_modal.block({
					message: null,
					overlayCSS: {
						background: '#fff url( ' + woocommerce_create_customer_order_params.plugin_url + '/assets/images/select2-spinner.gif ) no-repeat center',
						opacity: 0.6
					}
				});
				
				
				$.ajax({
					type     : 'post',
					dataType : 'json',
					url      : woocommerce_create_customer_order_params.ajax_url,
					data     : {
						action: 		'woocommerce_order_create_user',
						email_address: 	email_address,
						first_name: 	first_name,
						last_name: 		last_name,
						username:       username,
						user_role: 		user_role,
						disable_email:  disable_email,
						security: 		woocommerce_create_customer_order_params.create_customer_nonce,
					},
					success: function( data ) {
						
						// First remove all form errors, to avoid duplicates.
						$create_customer_modal.find('.cxccoo-create-customer-form-error').remove();
						
						// Validation.
						if ( 'email_empty' == data.error_message ) {
							
							$el = $( '<div class="inline error cxccoo-create-customer-form-error"><p><strong>'+ woocommerce_create_customer_order_params.msg_error +'</strong>: '+ woocommerce_create_customer_order_params.msg_email_empty +'.</p></div>' );
							$el.insertBefore( $create_customer_modal.find( '#cxccoo_email_address' ) );
							$create_customer_modal.unblock();
						}
						else if ( 'email_invalid' == data.error_message ) {
							
							$el = $( '<div class="inline error cxccoo-create-customer-form-error"><p><strong>'+ woocommerce_create_customer_order_params.msg_error +'</strong>: '+ woocommerce_create_customer_order_params.msg_email_invalid +'.</p></div>' );
							$el.insertBefore( $create_customer_modal.find( '#cxccoo_email_address' ) );
							$create_customer_modal.unblock();
						}
						else if ( 'email_exists' == data.error_message ) {
							
							$el = $( '<div class="inline error cxccoo-create-customer-form-error"><p><strong>'+ woocommerce_create_customer_order_params.msg_error +'</strong>: '+ woocommerce_create_customer_order_params.msg_email_exists +'.</p></div>' );
							$el.insertBefore( $create_customer_modal.find( '#cxccoo_email_address' ) );
							$create_customer_modal.unblock();
						}
						else if ( 'username_invalid' == data.error_message ) {
							
							$el = $( '<div class="inline error cxccoo-create-customer-form-error"><p><strong>'+ woocommerce_create_customer_order_params.msg_error +'</strong>: '+ woocommerce_create_customer_order_params.msg_username_invalid +'.</p></div>' );
							$el.insertBefore( $create_customer_modal.find( '#cxccoo_username' ) );
							$create_customer_modal.unblock();
						}
						else if ( 'username_exists' == data.error_message ) {
							
							$el = $( '<div class="inline error cxccoo-create-customer-form-error"><p><strong>'+ woocommerce_create_customer_order_params.msg_error +'</strong>: '+ woocommerce_create_customer_order_params.msg_email_exists_username +'.</p></div>' );
							$el.insertBefore( $create_customer_modal.find( '#cxccoo_email_address' ) );
							$create_customer_modal.unblock();
						}
						else if ( 'role_unable' == data.error_message ) {
							
							$el = $( '<div class="inline error cxccoo-create-customer-form-error"><p><strong>'+ woocommerce_create_customer_order_params.msg_error +'</strong>: '+ woocommerce_create_customer_order_params.msg_role +'.</p></div>' );
							$el.insertBefore( $create_customer_modal.find( '#cxccoo_role' ) );
							$create_customer_modal.unblock();
						}
						else {

							// Success...
							var user_id = data.user_id;
							var username = data.username;

							// Update the WC Select2 input.
							$wc_search_input_container.find('.wc-customer-search.enhanced')
								.select2({
									data: [{ id: user_id, text: username }]
								});
							
							// Update the WC input.
							$wc_search_input_container.find( 'input.wc-customer-search' )
								.val( user_id )
								.trigger("change");

							// Reset our interface.
							$create_customer_modal.unblock();
							$create_customer_modal.find( '#cxccoo_email_address' ).val("");
							$create_customer_modal.find( '#cxccoo_first_name' ).val("");
							$create_customer_modal.find( '#cxccoo_last_name' ).val("");

							// Auto check the 'Save to Customer' checkboxes so details are saved.
							$create_customer_modal.find( '#cxccoo-save-billing-address-input' ).attr('checked','checked');
							$create_customer_modal.find( '#cxccoo-save-shipping-address-input' ).attr('checked','checked');
							
							
							// TODO: Show a success notification, then remove a few seconds later.
							// $el = $( '<div id="message" class="updated fade"><p><strong>'+ woocommerce_create_customer_order_params.msg_successful +'</strong>: '+ woocommerce_create_customer_order_params.msg_success +'.</p></div>' );
							// $el.insertAfter( $( '.cxccoo-button.cxccoo-create-user-main-button' ).parents("p:eq(0)"));
							
							// setTimeout(function(){
							// 	$create_customer_modal.find(".updated.fade").fadeOut().remove();
							// }, 8000);
							
							
							// Close our interface.
							$create_customer_modal.find( '.cxccoo-button.cxccoo-create-user-form-cancel' ).trigger("click");
						}
					},
					error: function(xhr, status, error) {
						
					}
				});

				return false;
			});
			
		});
		
		
		
		/**
		 * RE-USABLE COMPONENTS.
		 */
			
		// Helper function to check if we are in responsive/mobile.
		function is_mobile() {
			return ( $( window ).width() < 610 );
		}
		
		/**
		 * Modal Popups.
		 */
		
		function init_modal( $close_button ) {

			// Add the required elements if they not in the page yet.
			if ( ! $('.cxccoo-component-modal-popup').length ) {

				// Add the required elements to the dom.
				$('body').append( '<div class="cxccoo-component-modal-temp component-modal-hard-hide"></div>' );
				$('body').append( '<div class="cxccoo-component-modal-cover component-modal-hard-hide"></div>' );

				$popup_html = '';
				$popup_html += '<div class="cxccoo-component-modal-wrap cxccoo-component-modal-popup component-modal-hard-hide">';
				$popup_html += '	<div class="cxccoo-component-modal-container">';
				$popup_html += '		<div class="cxccoo-component-modal-content">';
				$popup_html += '			<span class="cxccoo-component-modal-cross cxccoo-top-bar-cross cxccoo-icon-cancel"></span>';
				$popup_html += '		</div>';
				$popup_html += '	</div>';
				$popup_html += '</div>';
				$('body').append( $popup_html );
				
				// Handle `close_button`.
				$( document ).on( 'click', '.cxccoo-component-modal-cross', function() {
					close_modal();
					return false;
				});

				// Handle `close_click_outside`.
				$('html').click(function(event) {
					if (
							0 === $('[class*="component-modal-popup"]').filter('[class*="component-modal-hard-hide"]').length &&
							0 !== $('[class*="-close-click-outside"]').length &&
							0 === $(event.target).parents('[class*="component-modal-content"]').length
						) {
						close_modal();
						return false;
					}
				});
			}
		}
		function open_modal( $content, $settings ) {
			
			// Set defaults
			$defaults = {
				position            : 'center',
				cover               : true,
				close_button        : true,
				close_click_outside : true,
			};
			$settings = $.extend( true, $defaults, $settings );

			// Init modal - incase this is first run.
			init_modal( $settings.close_button );

			// Move any elements that may already be in the modal out, to the temp holder, as well as the close cross.
			$('.cxccoo-component-modal-temp').append( $('.cxccoo-component-modal-content').children().not('.cxccoo-component-modal-cross') );

			// Get content to load in modal.
			if ( 'string' == typeof $content ) {
				$content = $( $content );
			}

			// If content to load doesn't exist then rather close the whole modal and bail.
			if ( ! $content.length ) {
				close_modal();
				console.log( 'Content to load into modal does not exists.' );
				return;
			}

			// Enable whether to close when clicked outside the modal.
			if ( $settings.close_click_outside )
				$('.cxccoo-component-modal-popup').addClass('cxccoo-close-click-outside');
			else
				$('.cxccoo-component-modal-popup').removeClass('cxccoo-close-click-outside');

			// Show/Hide the close button.
			if ( $settings.close_button )
				$('.cxccoo-component-modal-content').find('.cxccoo-component-modal-cross').show();
			else
				$('.cxccoo-component-modal-content').find('.cxccoo-component-modal-cross').hide();

			// Add the intended content into the modal.
			$('.cxccoo-component-modal-content').prepend( $content );
			
			// Make sure this modal has the highest z-index, and hide any others that are open (incase we open 2 of our modals at once).
			var $open_modals = $('[class*="component-modal-cover"], [class*="component-modal-wrap"]').not('[class*="component-modal-hard-hide"], [class*="cxccoo-"]'); // Only check our 'open' modals.
			if ( $open_modals.length ) {
				
				$z_index = 0;
				$open_modals.each(function(){
					if ( $z_index < $(this).css('z-index') ) {
						// Loop the open modals and see which has the highest z-index.
						$z_index = $(this).css('z-index');
					}
				});
				
				// Set the current modal (modal & cover) to higher z-index than the existing one.
				$('.cxccoo-component-modal-cover, .cxccoo-component-modal-wrap').css( 'z-index', $z_index + 1 );
				
				// Hide the other open modal - only temporarily, by not adding the `hard-hide`.
				$open_modals.removeClass('component-modal-play-in').addClass('component-modal-play-out');
			}

			// Remove the class that's hiding the modal.
			$content.removeClass( 'component-modal-hard-hide' );

			// Apply positioning.
			// $('.cxccoo-component-modal-popup')
			// 	.removeClass( 'cxccoo-modal-position-center cxccoo-modal-position-top-right cxccoo-modal-position-top-center' )
			// 	.addClass( 'cxccoo-modal-position-' + $settings.position );

			// Move to top of page if Mobile.
			// if ( is_mobile() ) {
			// 	$('.cxccoo-component-modal-popup').css({ top: $(document).scrollTop() + 80 });
			// 	console.log( $(document).scrollTop() );
			// }

			// Control the overflow of long page content.
			$('html').css({ 'margin-right': '17px', 'overflow': 'hidden' });

			// Set a tiny defer timeout so that CSS fade-ins happen correctly.
			setTimeout(function() {

				// Move elements into the viewport by removing hard-hide, then fade in the elements.
				$('.cxccoo-component-modal-popup').removeClass( 'component-modal-hard-hide' );
				$('.cxccoo-component-modal-popup').addClass( 'component-modal-play-in' );
			}, 1 );

			// Optionally show the back cover. (not when in mobile)
			if ( $settings.cover ) {
				$('.cxccoo-component-modal-cover').removeClass( 'component-modal-hard-hide' );
				$('.cxccoo-component-modal-cover').addClass( 'component-modal-play-in' );
			}
			else {
				// If not showing then make sure to fade it out.
				$('.cxccoo-component-modal-cover').removeClass( 'component-modal-play-in' );
				setTimeout(function() {
					$('.cxccoo-component-modal-cover').addClass( 'component-modal-hard-hide' );
				}, 200 );
			}
		}
		function close_modal() {

			// Close the select 2 lip when clicking outside the modal to close.
			$('#cxccoo-select2-user-search').select2('close');

			// Fade out the elements.
			$('.cxccoo-component-modal-cover, .cxccoo-component-modal-popup').removeClass( 'component-modal-play-in' );
			
			// Hide the other open modals that were soft hidden by the openning of this one.
			var $open_modals = $('[class*="component-modal-cover"], [class*="component-modal-wrap"]').not('[class*="component-modal-hard-hide"], [class*="cxccoo-"]'); // Only check our 'open' modals.
			$open_modals.removeClass( 'component-modal-play-out' ).addClass( 'component-modal-play-in' );
			
			// Move elements out the viewport by adding hard-hide.
			setTimeout(function() {
				$('.cxccoo-component-modal-cover, .cxccoo-component-modal-popup').addClass( 'component-modal-hard-hide' );

				// Remove specific positioning.
				$('.cxccoo-component-modal-popup')
					.removeClass( 'cxccoo-modal-position-center cxccoo-modal-position-top-right cxccoo-modal-position-top-center' )
					.css({ top : '' });

				// Control the overflow of long page content - return it to normal.
				if ( ! $('[class*="component-modal-popup"]').filter('[class*="component-modal-play-in"]').length ) {
					$('html').css({ 'margin-right': '', 'overflow': '' });
				}

			}, 200 );
		}
		function resize_modal( $to_height ) {

			// Init modal - incase this is first run.
			init_modal();

			// Cache elements.
			$modal_popup = $('.cxccoo-component-modal-popup');

			// Get the intended heights.
			var $to_height = ( $to_height ) ? $to_height : $modal_popup.outerHeight();
			var $margin_top = ( $to_height / 2 );

			// Temporarily enable margin-top transition, do the height-ing/margin-ing, then remove the transtion.
			$modal_popup.css({ height: $to_height, marginTop: -$margin_top, transitionDelay: '0s', transition: 'margin .3s' });
			setTimeout( function(){
				$modal_popup.css({ height: '', transitionDelay: '', transition: '' });
			}, 1000 );
		}
		
	});

});