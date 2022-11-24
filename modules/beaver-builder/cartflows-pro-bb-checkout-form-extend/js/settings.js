( function ( $ ) {
	FLBuilder.registerModuleHelper( 'cartflows-bb-checkout-form', {
		init() {
			const form = $( '.fl-builder-settings' ),
				layout = form.find( 'select[name=checkout_layout]' );

			form.find(
				'#fl-field-checkout_layout .fl-field-description'
			).hide();
			form.find( '#fl-field-input_skins .fl-field-description' ).hide();

			// Init validation events.
			this._layout_styleChanged();

			// Validation events.
			layout.on( 'change', $.proxy( this._layout_styleChanged, this ) );

			const module_id = $( '.fl-module-cartflows-bb-checkout-form' ).data(
				'node'
			);

			const $module_wrapper = $(
				'.fl-node-' + module_id + ' .cartflows-bb__checkout-form'
			);

			const settings_data = $module_wrapper.data( 'settings-data' );

			const enable_product_options = settings_data.enable_product_options;
			//var enable_order_bump = settings_data.enable_order_bump;

			if ( 'yes' === enable_product_options ) {
				form.find(
					'#fl-field-product_options_position .fl-field-label'
				).show();
				form.find( '#fl-field-product_options_position select' ).show();
				form.find(
					'#fl-field-product_options_position .fl-field-description'
				).hide();

				form.find( '#fl-field-product_options_skin' ).show();
				form.find( '#fl-field-product_options_images' ).show();
				form.find(
					'#fl-field-product_option_section_title_text'
				).show();

				form.find(
					'#fl-builder-settings-section-product_style'
				).show();
			} else {
				form.find(
					'#fl-field-product_options_position .fl-field-label'
				).hide();
				form.find( '#fl-field-product_options_position select' ).hide();
				form.find(
					'#fl-field-product_options_position .fl-field-description'
				).show();

				form.find( '#fl-field-product_options_skin' ).hide();
				form.find( '#fl-field-product_options_images' ).hide();
				form.find(
					'#fl-field-product_option_section_title_text'
				).hide();

				form.find(
					'#fl-builder-settings-section-product_style'
				).hide();
			}
		},
		_layout_styleChanged() {
			const form = $( '.fl-builder-settings' ),
				layout = form.find( 'select[name=checkout_layout]' ).val();

			if ( 'two-step' === layout ) {
				// form.find( "#fl-field-width" ).hide();
				form.find( '#fl-builder-settings-section-two_step' ).show();
				form.find(
					'#fl-builder-settings-section-two_step_style'
				).show();
			} else {
				form.find( '#fl-builder-settings-section-two_step' ).hide();
				form.find(
					'#fl-builder-settings-section-two_step_style'
				).hide();
			}
		},
	} );
} )( jQuery );
