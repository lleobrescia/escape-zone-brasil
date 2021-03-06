(function($){
	
	acf.field_group = acf.model.extend({
		
		// vars
		$fields: null,
		$locations: null,
		$options: null,
		
		actions: {
			'ready': 'init'
		},
		
		filters: {
			'get_fields 99': 'get_fields'
		},
		
		events: {
			'submit #post':					'submit',
			'click a[href="#"]':			'preventDefault',
			'click .submitdelete': 			'trash',
			'mouseenter .acf-field-list': 	'sortable'
		},
		
		
		/*
		*  init
		*
		*  This function will run on document ready and initialize the module
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		init: function(){
			
			// $el
			this.$fields = $('#acf-field-group-fields');
			this.$locations = $('#acf-field-group-locations');
			this.$options = $('#acf-field-group-options');
			
			
			// disable validation
			acf.validation.active = 0;
		    
		},
		
		
		/*
		*  sortable
		*
		*  This function will add sortable to the feild group list
		*  sortable is added on mouseover to speed up page load
		*
		*  @type	function
		*  @date	28/10/2015
		*  @since	5.3.2
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		sortable: function( e ){
			
			// bail early if already sortable
			if( e.$el.hasClass('ui-sortable') ) {
				
				return;
				
			}
			
			
			// vars
			var self = this;
			
			
			// sortable
			e.$el.sortable({
				handle: '.acf-sortable-handle',
				connectWith: '.acf-field-list',
				update: function(event, ui){
					
					// vars
					var $el = ui.item;
					
					
					// render
					self.render_fields();
					
					
					// actions
					acf.do_action('sortstop', $el);
					
				}
			});
			
		},
		
		
		/*
		*  get_fields
		*
		*  This function will remove fields from the clone index
		*  Without this, field JS such as Select2 may run on fields which are used as a template
		*
		*  @type	function
		*  @date	15/08/2015
		*  @since	5.2.3
		*
		*  @param	$fields (selection)
		*  @return	$fields
		*/
		
		get_fields: function( $fields ) {
			 	
			return $fields.not('.acf-field-object[data-id="acfcloneindex"] .acf-field');
		
		},
		
		
		/*
		*  preventDefault
		*
		*  This helper will preventDefault on all events for empty links
		*
		*  @type	function
		*  @date	18/08/2015
		*  @since	5.2.3
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		preventDefault: function( e ){
			
			e.preventDefault();
			
		},
		
		
		/*
		*  render_fields
		*
		*  This function is triggered by a change in field order, and will update the field icon number
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		render_fields: function(){
			
			// reference
			var self = this;
			
			
			// update order numbers
			$('.acf-field-list').each(function(){
				
				// vars
				var $fields = $(this).children('.acf-field-object').not('[data-id="acfcloneindex"]');
				
				
				// loop over fields
				$fields.each(function( i ){
					
					// update meta
					self.update_field_meta( $(this), 'menu_order', i );
					
					
					// update icon number
					$(this).children('.handle').find('.acf-icon').html( i+1 );
					
				});
				
				
				// show no fields message
				if( !$fields.exists() ){
					
					$(this).children('.no-fields-message').show();
					
				} else {
					
					$(this).children('.no-fields-message').hide();
					
				}
				
			});
			
		},
		
		
		/*
		*  get_field_meta
		*
		*  This function will return an input value for a field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @param	name
		*  @return	(string)
		*/
		
		get_field_meta: function( $el, name ){
			
			//console.log( 'get_field_meta(%o, %o)', $el, name );
			
			// vars
	    	var $input = $el.find('> .meta > .input-' + name);
	    	
	    	
	    	// bail early if no input
			if( !$input.exists() ) {
				
				//console.log( '- aborted due to no input' );
				return false;
				
			}
			
			
			// return
			return $input.val();
			
		},
		
		
		/*
		*  update_field_meta
		*
		*  This function will update an input value for a field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @param	name
		*  @param	value
		*  @return	n/a
		*/
		
		update_field_meta: function( $el, name, value ){
			
			//console.log( 'update_field_meta(%o, %o, %o)', $el, name, value );
			
			// vars
	    	var $input = $el.find('> .meta > .input-' + name);
	    	
	    	
	    	// create hidden input if doesn't exist
			if( !$input.exists() ) {
				
				// vars
				var html = $el.find('> .meta > .input-ID').outerHTML();
				
				
				// replcae
				html = acf.str_replace('ID', name, html);
								
				
				// update $input
				$input = $(html);
				
				
				// reset value
				$input.val( value );
				
				
				// append
				$el.children('.meta').append( $input );
				
				//console.log( '- created new input' );
				
			}
			
			
			// bail early if no change
			if( $input.val() == value ) {
				
				//console.log( '- aborted due to no change in input value' );
				return;
			}
			
			
			// update value
			$input.val( value );
			
			
			// bail early if updating save
			if( name == 'save' ) {
				
				//console.log( '- aborted due to name == save' );
				return;
				
			}
			
			
			// meta has changed, update save
			this.save_field( $el, 'meta' );
			
		},
		
		
		/*
		*  delete_field_meta
		*
		*  This function will return an input value for a field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @param	name
		*  @return	(string)
		*/
		
		delete_field_meta: function( $el, name ){
			
			//console.log( 'delete_field_meta(%o, %o, %o)', $el, name );
			
			// vars
	    	var $input = $el.find('> .meta > .input-' + name);
	    	
	    	
	    	// bail early if not exists
			if( !$input.exists() ) {
			
				//console.log( '- aborted due to no input' );
				return;
				
			}
			
			
			// remove
			$input.remove();
			
			
			// meta has changed, update save
			this.save_field( $el, 'meta' );
			
		},
		
		
		/*
		*  save_field
		*
		*  This function will update the changed input for a given field making sure it is saved on submit
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		save_field: function( $el, type ){
			
			//console.log('save_field(%o %o)', $el, type);
			
			// defaults
			type = type || 'settings';
			
			
			// vars
			var value = this.get_field_meta( $el, 'save' );
			
			
			// bail early if already 'settings'
			if( value == 'settings' ) {
				
				return;
				
			}
			
			
			// bail early if no change
			if( value == type ) {
				
				return;
				
			}
			
			
			// update meta
			this.update_field_meta( $el, 'save', type );
			
			
			// action for 3rd party customization
			acf.do_action('save_field', $el, type);
			
		},
		
		
		/*
		*  submit
		*
		*  This function is triggered when submitting the form and provides validation prior to posting the data
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	(boolean)
		*/
		
		submit: function( e ){
			
			// reference
			var self = this;
			
			
			// vars
			var $title = $('#titlewrap #title');
			
			
			// title empty
			if( !$title.val() ) {
				
				// prevent default
				e.preventDefault();
				
				
				// unlock form
				acf.validation.toggle( e.$el, 'unlock' );
				
				
				// alert
				alert( acf._e('title_is_required') );
				
				
				// focus
				$title.focus();
				
			}
			
			
			// close / delete fields
			$('.acf-field-object').each(function(){
				
				// vars
				var save = self.get_field_meta( $(this), 'save'),
					ID = self.get_field_meta( $(this), 'ID'),
					open = $(this).hasClass('open');
				
				
				// clone
				if( ID == 'acfcloneindex' ) {
					
					$(this).remove();
					return;
					
				}
				
				
				// close
				if( open ) {
					
					self.close_field( $(this) );
					
				}
				
				
				// remove unnecessary inputs
				if( save == 'settings' ) {
					
					// allow all settings to save (new field, changed field)
					
				} else if( save == 'meta' ) {
					
					$(this).children('.settings').find('[name^="acf_fields[' + ID + ']"]').remove();
					
				} else {
					
					$(this).find('[name^="acf_fields[' + ID + ']"]').remove();
					
				}
				
			});

		},
		
		
		/*
		*  trash
		*
		*  This function is triggered when moving the field group to trash
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	(boolean)
		*/
		
		trash: function( e ){
			
			var result = confirm( acf._e('move_to_trash') );
			
			if( !result ) {
				
				e.preventDefault();
				
			}
			
		},
		
		
		/*
		*  render_field
		*
		*  This function will update the field's info
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		render_field: function( $el ){
			
			// vars
			var label = $el.find('.field-label:first').val(),
				name = $el.find('.field-name:first').val(),
				type = $el.find('.field-type:first option:selected').text(),
				required = $el.find('.field-required:first input:checked').val();
			
			
			// update label
			$el.find('> .handle .li-field-label strong a').text( label );
			
			
			// update required
			$el.find('> .handle .li-field-label .acf-required').remove();
			
			if( required == '1' ) {
				
				$el.find('> .handle .li-field-label strong').append('<span class="acf-required">*</span>');
				
			}
			
			
			// update name
			$el.find('> .handle .li-field-name').text( name );
			
			
			// update type
			$el.find('> .handle .li-field-type').text( type );
			
		},
		
		
		/*
		*  edit_field
		*
		*  This function is triggered when clicking on a field. It will open / close a fields settings
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		edit_field: function( $field ){
			
			// toggle
			if( $field.hasClass('open') ) {
			
				this.close_field( $field );
				
			} else {
			
				this.open_field( $field );
				
			}
			
		},
		
		
		/*
		*  open_field
		*
		*  This function will open a fields settings
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		open_field: function( $el ){
			
			// bail early if already open
			if( $el.hasClass('open') ) {
			
				return false;
				
			}
			
			
			// add class
			$el.addClass('open');
			
			
			// action for 3rd party customization
			acf.do_action('open_field', $el);
			
			
			// animate toggle
			$el.children('.settings').animate({ 'height' : 'toggle' }, 250 );
			
		},
		
		
		/*
		*  close_field
		*
		*  This function will open a fields settings
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		close_field: function( $el ){
			
			// bail early if already closed
			if( !$el.hasClass('open') ) {
			
				return false;
				
			}
			
			
			// remove class
			$el.removeClass('open');
			
			
			// action for 3rd party customization
			acf.do_action('close_field', $el);
			
			
			// animate toggle
			$el.children('.settings').animate({ 'height' : 'toggle' }, 250 );
			
		},
		
		
		/*
		*  wipe_field
		*
		*  This function will prepare a new field by updating the input names
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		wipe_field: function( $el ){
			
			// vars
			var id = $el.attr('data-id'),
				key = $el.attr('data-key'),
				new_id = acf.get_uniqid(),
				new_key = 'field_' + new_id;
			
			
			// update attr
			$el.attr('data-id', new_id);
			$el.attr('data-key', new_key);
			$el.attr('data-orig', key);
			
			
			// update hidden inputs
			this.update_field_meta( $el, 'ID', '' );
			this.update_field_meta( $el, 'key', new_key );
			
			
			// update attributes
			$el.find('[id*="' + id + '"]').each(function(){	
			
				$(this).attr('id', $(this).attr('id').replace(id, new_id) );
				
			});
			
			$el.find('[name*="' + id + '"]').each(function(){	
			
				$(this).attr('name', $(this).attr('name').replace(id, new_id) );
				
			});
			
			
			// update key
			$el.find('> .handle .pre-field-key').text( new_key );
			
			
			// remove sortable classes
			$el.find('.ui-sortable').removeClass('ui-sortable');
			
			
			// action for 3rd party customization
			acf.do_action('wipe_field', $el);
			
		},
		
		
		/*
		*  add_field
		*
		*  This function will add a new field to a field list
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$fields
		*  @return	n/a
		*/
		
		add_field: function( $fields ){
			
			// clone tr
			var $clone = $fields.children('.acf-field-object[data-id="acfcloneindex"]'),
				$el = $clone.clone(),
				$label = $el.find('.field-label:first'),
				$name = $el.find('.field-name:first');
			
			
			// update names
			this.wipe_field( $el );
			
			
			// append to table
			$clone.before( $el );
			
			
			// clear name
			$label.val('');
			$name.val('');
			
			
			// focus after form has dropped down
			setTimeout(function(){
			
	        	$label.focus();
	        	
	        }, 251);
			
			
			// update order numbers
			this.render_fields();
			
			
			// trigger append
			acf.do_action('append', $el);
			
			
			// open up form
			this.edit_field( $el );
			
			
			// action for 3rd party customization
			acf.do_action('add_field', $el);
			
		},
		
		
		/*
		*  duplicate_field
		*
		*  This function will duplicate a field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	$el2
		*/
		
		duplicate_field: function( $el ){
			
			// allow acf to modify DOM
			acf.do_action('before_duplicate', $el);
			
			
			// vars
			var $el2 = $el.clone(),
				$label = $el2.find('.field-label:first'),
				$name = $el2.find('.field-name:first');
			
			
			// remove JS functionality
			acf.do_action('remove', $el2);
			
			
			// update names
			this.wipe_field( $el2 );
			
			
			// allow acf to modify DOM
			acf.do_action('after_duplicate', $el, $el2);
			
			
			// append to table
			$el.after( $el2 );
			
			
			// trigger append
			acf.do_action('append', $el2);
			
			
			// focus after form has dropped down
			setTimeout(function(){
			
	        	$label.focus();
	        	
	        }, 251);
	        
			
			// update order numbers
			this.render_fields();
			
			
			// open up form
			if( $el.hasClass('open') ) {
			
				this.close_field( $el );
				
			} else {
			
				this.open_field( $el2 );
				
			}
			
			
			// update new_field label / name
			$label.val( $label.val() + ' (' + acf._e('copy') + ')' );
			$name.val( $name.val() + '_' + acf._e('copy') );
			
			
			// save field
			this.save_field( $el2 );
			
			
			// render field
			this.render_field( $el2 );
			
			
			// action for 3rd party customization
			acf.do_action('duplicate_field', $el2);
			
			
			// return
			return $el2;
			
		},
		
		
		/*
		*  move_field
		*
		*  This function will launch a popup to move a field to another field group
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$field
		*  @return	n/a
		*/
		
		move_field: function( $field ){
			
			// reference
			var self = this;
			
			
			// AJAX data
			var ajax_data = acf.prepare_for_ajax({
				'action':	'acf/field_group/move_field',
				'field_id':	this.get_field_meta( $field, 'ID' )
			});
			
			
			// vars
			var warning = false;
			
			
			// validate
			if( !ajax_data.field_id ) {
				
				// Case: field not saved to DB
				warning = true;
				
			} else if( this.get_field_meta( $field, 'save' ) == 'settings' ) {
				
				// Case: field's settings have changed
				warning = true;
				
			} else {
				
				// Case: sub field's settings have changed
				$field.find('.acf-field-object').not('[data-id="acfcloneindex"]').each(function(){
					
					if( !self.get_field_meta( $(this), 'ID' ) ) {
						
						// Case: field not saved to DB
						warning = true;
						return false;
						
					} else if( self.get_field_meta( $(this), 'save' ) == 'settings' ) {
						
						// Case: field's settings have changed
						warning = true;
						
					}
					
				});
				
			}
			
			
			// bail early if can't move
			if( warning ) {
				
				alert( acf._e('move_field_warning') );
				return;
				
			}
			
			
			// open popup
			acf.open_popup({
				title	: acf._e('move_field'),
				loading	: true,
				height	: 145
			});
			
			
			// get HTML
			$.ajax({
				url: acf.get('ajaxurl'),
				data: ajax_data,
				type: 'post',
				dataType: 'html',
				success: function(html){
				
					self.move_field_confirm( $field, html );
					
				}
			});
			
		},
		
		
		/*
		*  move_field_confirm
		*
		*  This function will move a field to another field group
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		move_field_confirm: function( $field, html ){
			
			// reference
			var self = this;
			
			
			// update popup
			acf.update_popup({
				content : html
			});
			
			
			// AJAX data
			var ajax_data = {
				'action'			: 'acf/field_group/move_field',
				'nonce'				: acf.get('nonce'),
				'field_id'			: this.get_field_meta($field, 'ID'),
				'field_group_id'	: 0
			};
			
			
			// submit form
			$('#acf-move-field-form').on('submit', function(){

				ajax_data.field_group_id = $(this).find('select').val();
				
				
				// get HTML
				$.ajax({
					url: acf.get('ajaxurl'),
					data: ajax_data,
					type: 'post',
					dataType: 'html',
					success: function(html){
					
						acf.update_popup({
							content : html
						});
						
						
						// remove the field without actually deleting it
						self.remove_field( $field );
						
					}
				});
				
				return false;
				
			});
			
		},
		
		
		/*
		*  delete_field
		*
		*  This function will delete a field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @param	animation
		*  @return	n/a
		*/
		
		delete_field: function( $el, animation ){
			
			// defaults
			animation = animation || true;
			
			
			// vars
			var id = this.get_field_meta($el, 'ID');
			
			
			// bail early if cloneindex
			if( id == 'acfcloneindex' ) {
				
				return;
				
			}
			
			
			// add to remove list
			if( id ) {
			
				$('#input-delete-fields').val( $('#input-delete-fields').val() + '|' + id );	
				
			}
			
			
			// action for 3rd party customization
			acf.do_action('delete_field', $el);
			
			
			// bail early if no animation
			if( animation ) {
				
				this.remove_field( $el );
				
			}
						
		},
		
		
		/*
		*  remove_field
		*
		*  This function will visualy remove a field
		*
		*  @type	function
		*  @date	24/10/2014
		*  @since	5.0.9
		*
		*  @param	$el
		*  @param	animation
		*  @return	n/a
		*/
		
		remove_field: function( $el ){
			
			// reference
			var self = this;
			
			
			// vars
			var $field_list	= $el.closest('.acf-field-list');
			
			
			// set layout
			$el.css({
				height		: $el.height(),
				width		: $el.width(),
				position	: 'absolute'
			});
			
			
			// wrap field
			$el.wrap( '<div class="temp-field-wrap" style="height:' + $el.height() + 'px"></div>' );
			
			
			// fade $el
			$el.animate({ opacity : 0 }, 250);
			
			
			// close field
			var end_height = 0,
				$show = false;
			
			
			if( $field_list.children('.acf-field-object').length == 1 ) {
			
				$show = $field_list.children('.no-fields-message');
				end_height = $show.outerHeight();
				
			}
			
			$el.parent('.temp-field-wrap').animate({ height : end_height }, 250, function(){
				
				// show another element
				if( $show ) {
				
					$show.show();
					
				}
				
				
				// action for 3rd party customization 
				acf.do_action('remove', $(this));
				
				
				// remove $el
				$(this).remove();
				
				
				// render fields becuase they have changed
				self.render_fields();
				
			});
						
		},
		
		
		/*
		*  change_field_type
		*
		*  This function will update the field's settings based on the new field type
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$select
		*  @return	n/a
		*/
		
		change_field_type: function( $select ){
			
			// vars
			var $tbody		= $select.closest('tbody'),
				$el			= $tbody.closest('.acf-field-object'),
				$parent		= $el.parent().closest('.acf-field-object'),
				
				key			= $el.attr('data-key'),
				old_type	= $el.attr('data-type'),
				new_type	= $select.val();
				
			
			// update class
			$el.removeClass('acf-field-object-' + old_type.replace('_', '-'));
			$el.addClass('acf-field-object-' + new_type.replace('_', '-'));
			
			
			// update atts
			$el.attr('data-type', new_type);
			
			
			// abort XHR if this field is already loading AJAX data
			if( $el.data('xhr') ) {
			
				$el.data('xhr').abort();
				
			}
			
			
			// get settings
			var $settings = $tbody.children('.acf-field[data-setting="' + old_type + '"]'),
				html = '';
			
			
			// populate settings html
			$settings.each(function(){
				
				html += $(this).outerHTML();
				
			});
			
			
			// remove settings
			$settings.remove();
			
			
			// save field settings html
			acf.update( key + '_settings_' + old_type, html );
			
			
			// render field
			this.render_field( $el );
			
			
			// show field options if they already exist
			html = acf.get( key + '_settings_' + new_type );
			
			if( html ) {
				
				// append settings
				$tbody.children('.acf-field[data-name="conditional_logic"]').before( html );
				
				
				// remove field settings html
				acf.update( key + '_settings_' + new_type, '' );
				
				
				// trigger event
				acf.do_action('change_field_type', $el);
				
				
				// return
				return;
			}
			
			
			// add loading
			var $tr = $('<tr class="acf-field"><td class="acf-label"></td><td class="acf-input"><div class="acf-loading"></div></td></tr>');
			
			
			// add $tr
			$tbody.children('.acf-field[data-name="conditional_logic"]').before( $tr );
			
			
			var ajax_data = {
				action		: 'acf/field_group/render_field_settings',
				nonce		: acf.o.nonce,
				parent		: acf.o.post_id,
				field_group	: acf.o.post_id,
				prefix		: $select.attr('name').replace('[type]', ''),
				type		: new_type,
			};
			
			
			// parent
			if( $parent.exists() ) {
				
				ajax_data.parent = this.get_field_meta( $parent, 'ID' );
				
			}
			
			
			// ajax
			var xhr = $.ajax({
				url: acf.o.ajaxurl,
				data: ajax_data,
				type: 'post',
				dataType: 'html',
				success: function( html ){
					
					// bail early if no html
					if( !html ) {
					
						return;
						
					}
					
					
					// vars
					var $new_tr = $(html);
					
					
					// replace
					$tr.after( $new_tr );
					
					
					// trigger event
					acf.do_action('append', $new_tr);
					acf.do_action('change_field_type', $el);

					
				},
				complete : function(){
					
					// this function will also be triggered by $el.data('xhr').abort();
					$tr.remove();
					
				}
			});
			
			
			// update el data
			$el.data('xhr', xhr);
			
		},
		
		/*
		*  change_field_label
		*
		*  This function is triggered when changing the field's label
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		change_field_label: function( $el ) {
			
			// vars
			var $label = $el.find('.field-label:first'),
				$name = $el.find('.field-name:first'),
				type = $el.attr('data-type');
				
			
			// render name
			if( $name.val() == '' ) {
				
				// vars
				var s = $label.val();
				
				
				// sanitize
				s = acf.str_sanitize(s);
				
				
				// update name
				$name.val( s ).trigger('change');
				
			}
			
			
			// render field
			this.render_field( $el );
			
			
			// action for 3rd party customization
			acf.do_action('change_field_label', $el);
			
		},
		
		/*
		*  change_field_name
		*
		*  This function is triggered when changing the field's name
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		change_field_name: function( $el ) {
			
			// vars
			var $name = $el.find('.field-name:first');
			
			if( $name.val().substr(0, 6) === 'field_' ) {
				
				alert( acf._e('field_name_start') );
				
				setTimeout(function(){
					
					$name.focus();
					
				}, 1);
				
			}
			
		}
		
	});
	
	
	/*
	*  field
	*
	*  This model will handle field events
	*
	*  @type	function
	*  @date	19/08/2015
	*  @since	5.2.3
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.field_group.field = acf.model.extend({
		
		events: {
			'click .edit-field':		'edit',
			'click .duplicate-field':	'duplicate',
			'click .move-field':		'move',
			'click .delete-field':		'delete',
			'click .add-field':			'add',
			
			'change .field-type':		'change_type',
			'blur .field-label':		'change_label',
			'blur .field-name':			'change_name',
			
			'keyup .field-label':				'render',
			'keyup .field-name':				'render',
			'change .field-required input':		'render',
			
			'change .acf-field-object input':		'save',
			'change .acf-field-object textarea':	'save',
			'change .acf-field-object select':		'save'
		},
		
		event: function( e ){
			
			// append $field
			e.$field = e.$el.closest('.acf-field-object');
			
			
			// return
			return e;
			
		},
		
		
		/*
		*  events
		*
		*  description
		*
		*  @type	function
		*  @date	19/08/2015
		*  @since	5.2.3
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		edit: function( e ){
			
			acf.field_group.edit_field( e.$field );
				
		},
		
		duplicate: function( e ){
			
			acf.field_group.duplicate_field( e.$field );
				
		},
		
		move: function( e ){
			
			acf.field_group.move_field( e.$field );
				
		},
		
		delete: function( e ){
			
			acf.field_group.delete_field( e.$field );
				
		},
		
		add: function( e ){
			
			var $list = e.$el.closest('.acf-field-list-wrap').children('.acf-field-list');
			
			acf.field_group.add_field( $list );
				
		},
		
		change_type: function( e ){
			
			acf.field_group.change_field_type( e.$el );
			
		},
		
		change_label: function( e ){
			
			acf.field_group.change_field_label( e.$field );
			
		},
		
		change_name: function( e ){
			
			acf.field_group.change_field_name( e.$field );
			
		},
		
		render: function( e ){
			
			acf.field_group.render_field( e.$field );
				
		},
		
		save: function( e ){
			
			acf.field_group.save_field( e.$field );
				
		}
		
	});
	
	
	/*
	*  conditions
	*
	*  This model will handle conditional logic events
	*
	*  @type	function
	*  @date	19/08/2015
	*  @since	5.2.3
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.field_group.conditional_logic = acf.model.extend({
		
		actions: {
			'open_field':			'render_field',
			'change_field_label':	'render_fields',
			'change_field_type':	'render_fields'
		},
		
		events: {
			'click .add-conditional-rule':			'add_rule',
			'click .add-conditional-group':			'add_group',
			'click .remove-conditional-rule':		'remove_rule',
			'change .conditional-toggle input':		'change_toggle',
			'change .conditional-rule-param':		'change_param'
		},
		
		
		/*
		*  render_fields
		*
		*  description
		*
		*  @type	function
		*  @date	19/08/2015
		*  @since	5.2.3
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		render_fields: function(){
			
			var self = this;
			
			$('.acf-field-object.open').each(function(){
					
				self.render_field( $(this) );
				
			});	
			
		},
		
		
		/*
		*  render_field
		*
		*  This function will render the conditional logic fields for a given field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$field
		*  @return	n/a
		*/
		
		render_field: function( $field ){
			
			// reference
			var self = this;
			
			
			// vars
			var key			= $field.attr('data-key'),
				$ancestors	= $field.parents('.acf-field-list'),
				$tr			= $field.find('.acf-field[data-name="conditional_logic"]:last');
				
			
			// choices
			var choices	= [];
			
			
			// loop over ancestors
			$.each( $ancestors, function( i ){
				
				// vars
				var group = (i == 0) ? acf._e('sibling_fields') : acf._e('parent_fields');
				
				
				// loop over fields
				$(this).children('.acf-field-object').each(function(){
					
					// vars
					var $this_field	= $(this),
						this_key	= $this_field.attr('data-key'),
						this_type	= $this_field.attr('data-type'),
						this_label	= $this_field.find('.field-label:first').val();
					
					
					// validate
					if( $.inArray(this_type, ['select', 'checkbox', 'true_false', 'radio']) === -1 ) {
						
						return;
						
					} else if( this_key == 'acfcloneindex' ) {
						
						return;
						
					} else if( this_key == key ) {
						
						return;
						
					}
										
					
					// add this field to available triggers
					choices.push({
						value:	this_key,
						label:	this_label,
						group:	group
					});
					
				});
				
			});
				
			
			// empty?
			if( !choices.length ) {
				
				choices.push({
					value: '',
					label: acf._e('no_fields')
				});
				
			}
			
			
			// create select fields
			$tr.find('.rule').each(function(){
				
				self.render_rule( $(this), choices );
				
			});
			
		},
		
		
		/*
		*  populate_triggers
		*
		*  description
		*
		*  @type	function
		*  @date	22/08/2015
		*  @since	5.2.3
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		render_rule: function( $tr, triggers ) {
			
			// vars
			var $trigger	= $tr.find('.conditional-rule-param'),
				$value		= $tr.find('.conditional-rule-value');
				
				
			// populate triggers
			if( triggers ) {
				
				acf.render_select( $trigger, triggers );
				
			}
			
			
			// vars
			var $field		= $('.acf-field-object[data-key="' + $trigger.val() + '"]'),
				field_type	= $field.attr('data-type'),
				choices		= [];
			
			
			// populate choices
			if( field_type == "true_false" ) {
				
				choices.push({
					'value': 1,
					'label': acf._e('checked')
				});
			
			// select				
			} else if( field_type == "select" || field_type == "checkbox" || field_type == "radio" ) {
				
				// vars
				var lines = $field.find('.acf-field[data-name="choices"] textarea').val().split("\n");	
				
				$.each(lines, function(i, line){
					
					// explode
					line = line.split(':');
					
					
					// default label to value
					line[1] = line[1] || line[0];
					
					
					// append					
					choices.push({
						'value': $.trim( line[0] ),
						'label': $.trim( line[1] )
					});
					
				});
				
				
				// allow null
				var $allow_null = $field.find('.acf-field[data-name="allow_null"]');
				
				if( $allow_null.exists() ) {
					
					if( $allow_null.find('input:checked').val() == '1' ) {
						
						choices.unshift({
							'value': '',
							'label': acf._e('null')
						});
						
					}
					
				}
				
			}
			
			
			// update select
			acf.render_select( $value, choices );
			
		},
		
		
		/*
		*  change_toggle
		*
		*  This function is triggered by changing the 'Conditional Logic' radio button
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$input
		*  @return	n/a
		*/
		
		change_toggle: function( e ){
			
			// vars
			var $input = e.$el,
				val = $input.val(),
				$td = $input.closest('.acf-input');
				
			
			if( val == "1" ) {
				
				$td.find('.rule-groups').show();
				$td.find('.rule-groups').find('[name]').removeAttr('disabled');
			
			} else {
				
				$td.find('.rule-groups').hide();
				$td.find('.rule-groups').find('[name]').attr('disabled', 'disabled');
			
			}
			
		},
		
		
		/*
		*  change_trigger
		*
		*  This function is triggered by changing a 'Conditional Logic' trigger
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$select
		*  @return	n/a
		*/
		
		change_param: function( e ){
			
			// vars
			var $rule = e.$el.closest('.rule');
			
			
			// render		
			this.render_rule( $rule );
			
		},
		
		
		/*
		*  add_rule
		*
		*  This function will add a new rule below the specified $tr
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		add_rule: function( e ){
			
			// vars
			var $tr = e.$el.closest('tr');
			
			
			// duplicate
			$tr2 = acf.duplicate( $tr );
			
			
			// save field
			$tr2.find('select:first').trigger('change');
						
		},
		
		
		/*
		*  remove_rule
		*
		*  This function will remove the $tr and potentially the group
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		remove_rule: function( e ){
			
			// vars
			var $tr = e.$el.closest('tr');

			
			// save field
			$tr.find('select:first').trigger('change');
			
			
			if( $tr.siblings('tr').length == 0 ) {
				
				// remove group
				$tr.closest('.rule-group').remove();
				
			}
			
			
			// remove tr
			$tr.remove();
				
			
		},
		
		
		/*
		*  add_group
		*
		*  This function will add a new rule group to the given $groups container
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		add_group: function( e ){
			
			// vars
			var $groups = e.$el.closest('.rule-groups'),
				$group = $groups.find('.rule-group:last');
			
			
			// duplicate
			$group2 = acf.duplicate( $group );
			
			
			// update h4
			$group2.find('h4').text( acf._e('or') );
			
			
			// remove all tr's except the first one
			$group2.find('tr:not(:first)').remove();
			
			
			// save field
			$group2.find('select:first').trigger('change');
			
		}
		
	});
	
	
	/*
	*  locations
	*
	*  This model will handle location rule events
	*
	*  @type	function
	*  @date	19/08/2015
	*  @since	5.2.3
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.field_group.locations = acf.model.extend({
		
		events: {
			'click .add-location-rule':		'add_rule',
			'click .add-location-group':	'add_group',
			'click .remove-location-rule':	'remove_rule',
			'change .location-rule-param':	'change_rule'
		},
		
		
		/*
		*  add_rule
		*
		*  This function will add a new rule below the specified $tr
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		add_rule: function( e ){
			
			// vars
			var $tr = e.$el.closest('tr');
			
			
			// duplicate
			$tr2 = acf.duplicate( $tr );
			
		},
		
		
		/*
		*  remove_rule
		*
		*  This function will remove the $tr and potentially the group
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		remove_rule: function( e ){
			
			// vars
			var $tr = e.$el.closest('tr');

			
			// save field
			$tr.find('select:first').trigger('change');
			
			
			if( $tr.siblings('tr').length == 0 ) {
				
				// remove group
				$tr.closest('.rule-group').remove();
				
			}
			
			
			// remove tr
			$tr.remove();
				
			
		},
		
		
		/*
		*  add_group
		*
		*  This function will add a new rule group to the given $groups container
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		add_group: function( e ){
			
			// vars
			var $groups = e.$el.closest('.rule-groups'),
				$group = $groups.find('.rule-group:last');
			
			
			// duplicate
			$group2 = acf.duplicate( $group );
			
			
			// update h4
			$group2.find('h4').text( acf._e('or') );
			
			
			// remove all tr's except the first one
			$group2.find('tr:not(:first)').remove();
			
		},
		
		
		/*
		*  change_rule
		*
		*  This function is triggered when changing a location rule trigger
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$select
		*  @return	n/a
		*/
		
		change_rule: function( e ){
				
			// vars
			var $select = e.$el,
				$tr = $select.closest('tr'),
				rule_id = $tr.attr('data-id'),
				$group = $tr.closest('.rule-group'),
				group_id = $group.attr('data-id');
			
			
			// add loading gif
			var $div = $('<div class="acf-loading"></div>');
			
			$tr.find('td.value').html( $div );
			
			
			// load location html
			$.ajax({
				url: acf.get('ajaxurl'),
				data: acf.prepare_for_ajax({
					'action':	'acf/field_group/render_location_value',
					'rule_id':	rule_id,
					'group_id':	group_id,
					'param':	$select.val(),
					'value':	''
				}),
				type: 'post',
				dataType: 'html',
				success: function(html){
	
					$div.replaceWith(html);
	
				}
			});
			
		}
	});
	
	
	
	/*
	*  Append
	*
	*  description
	*
	*  @type	function
	*  @date	12/02/2015
	*  @since	5.1.5
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.add_action('open_field change_field_type', function( $el ){
		
		// clear name
		$el.find('.acf-field[data-append]').each(function(){
			
			// vars
			var append = $(this).data('append');
			
			
			// find sibling
			$sibling = $(this).siblings('[data-name="' + append + '"]');
			
			
			// bail early if no $sibling
			if( !$sibling.exists() ) {
				
				return;
				
			}
			
			
			// vars
			var $wrap = $sibling.children('.acf-input'),
				$ul = $wrap.children('.acf-hl');
			
			
			if( !$ul.exists() ) {
				
				$wrap.wrapInner('<ul class="acf-hl"><li></li></ul>');
				
				$ul = $wrap.children('.acf-hl');
			}
			
			
			// create $li
			var $li = $('<li></li>').append( $(this).children('.acf-input').children() );
			
			
			// append $li
			$ul.append( $li );
			
			
			// update cols
			$ul.attr('data-cols', $ul.children().length );
			
			
			// remove
			$(this).remove();
			
		});
			
	});
	
	
	/*
	*  Select
	*
	*  This field type requires some extra logic for its settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	var acf_settings_select = acf.model.extend({
		
		actions: {
			'open_field':			'render',
			'change_field_type':	'render'
		},
		
		events: {
			'change .acf-field[data-name="ui"] input': 'render'
		},
		
		event: function( e ){
			
			// override
			return e.$el.closest('.acf-field-object');
			
		},
		
		render: function( $el ){
			
			// bail early if not correct field type
			if( $el.attr('data-type') != 'select' ) {
				
				return;
				
			}
			
			
			// vars
			var val = $el.find('.acf-field[data-name="ui"] input:checked').val();
			
			
			// show / hide
			if( val == '1' ) {
			
				$el.find('.acf-field[data-name="ajax"]').show();
				
			} else {
			
				$el.find('.acf-field[data-name="ajax"]').hide();
				
			}
			
		}		
		
	});
		
	
	/*
	*  Radio
	*
	*  This field type requires some extra logic for its settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	var acf_settings_radio = acf.model.extend({
		
		actions: {
			'open_field':			'render',
			'change_field_type':	'render'
		},
		
		events: {
			'change .acf-field[data-name="other_choice"] input': 'render'
		},
		
		event: function( e ){
			
			// override
			return e.$el.closest('.acf-field-object');
			
		},
		
		render: function( $el ){
			
			// bail early if not correct field type
			if( $el.attr('data-type') != 'radio' ) {
				
				return;
				
			}
			
			
			// vars
			var val = $el.find('.acf-field[data-name="other_choice"] input:checked').val();
			
			if( val == '1' ) {
				
				$el.find('.acf-field[data-name="save_other_choice"]').show();
				
			} else {
				
				$el.find('.acf-field[data-name="save_other_choice"]').hide();
				$el.find('.acf-field[data-name="save_other_choice"] input').prop('checked', false);
				
			}
			
		}		
		
	});
		
	
	/*
	*  Date Picker
	*
	*  This field type requires some extra logic for its settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	var acf_settings_date_picker = acf.model.extend({
		
		actions: {
			'open_field':			'render',
			'change_field_type':	'render'
		},
		
		events: {
			'change .acf-field[data-name="display_format"] input':	'render',
			'change .acf-field[data-name="return_format"] input':	'render'
		},
		
		event: function( e ){
			
			// override
			return e.$el.closest('.acf-field-object');
			
		},
		
		render: function( $el ){
			
			// bail early if not correct field type
			if( $el.attr('data-type') != 'date_picker' ) {
				
				return;
				
			}
			
			
			$.each(['display_format', 'return_format'], function(k,v){
				
				// vars
				var $radio = $el.find('.acf-field[data-name="' + v + '"] input[type="radio"]:checked'),
					$other = $el.find('.acf-field[data-name="' + v + '"] input[type="text"]');
				
				
				// display val
				if( $radio.val() != 'other' ) {
				
					$other.val( $radio.val() );
					
				}
				
			});
			
		}		
		
	});
	
	
	/*
	*  Date Time Picker
	*
	*  This field type requires some extra logic for its settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	var acf_settings_date_time_picker = acf.model.extend({
		
		actions: {
			'open_field':			'render',
			'change_field_type':	'render'
		},
		
		events: {
			'change .acf-field-object-date-time-picker input[type="radio"]':	'render',
		},
		
		event: function( e ){
			
			// override
			return e.$el.closest('.acf-field-object');
			
		},
		
		render: function( $el ){
			
			// bail early if not correct field type
			if( $el.attr('data-type') != 'date_time_picker' ) return;
			
			
			// loop
			$el.find('.acf-radio-list[data-other_choice="1"]').each(function(){
				
				// vars
				var $ul = $(this),
					$radio = $ul.find('input[type="radio"]:checked'),
					$other = $ul.find('input[type="text"]');
				
				
				// display val
				if( $radio.val() != 'other' ) {
				
					$other.val( $radio.val() );
					
				}
				
			});
		}		
		
	});
	
	
	/*
	*  Time Picker
	*
	*  This field type requires some extra logic for its settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	var acf_settings_time_picker = acf.model.extend({
		
		actions: {
			'open_field':			'render',
			'change_field_type':	'render'
		},
		
		events: {
			'change .acf-field-object-time-picker input[type="radio"]':	'render',
		},
		
		event: function( e ){
			
			// override
			return e.$el.closest('.acf-field-object');
			
		},
		
		render: function( $el ){
			
			// bail early if not correct field type
			if( $el.attr('data-type') != 'time_picker' ) return;
			
			
			// loop
			$el.find('.acf-radio-list[data-other_choice="1"]').each(function(){
				
				// vars
				var $ul = $(this),
					$radio = $ul.find('input[type="radio"]:checked'),
					$other = $ul.find('input[type="text"]');
				
				
				// display val
				if( $radio.val() != 'other' ) {
				
					$othe