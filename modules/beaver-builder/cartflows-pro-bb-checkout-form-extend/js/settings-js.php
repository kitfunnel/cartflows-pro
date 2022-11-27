(function($) {

	var $wrapper = $('.fl-node-<?php echo $cf_module->node; //phpcs:ignore ?> .cartflows-bb__checkout-form' );

	var $offer_wrap = $('body').find( '#wcf-pre-checkout-offer-modal' );

	var settings_data = $wrapper.data( 'settings-data' );

	var enable_product_options = settings_data.enable_product_options;
	<!--var enable_order_bump = settings_data.enable_order_bump;-->

	var form = $('.fl-builder-settings');

	var checkout_settings = {
		offer_title: [ settings_data.title_text, '.wcf-content-modal-title h1' ],
		offer_subtitle: [ settings_data.subtitle_text, '.wcf-content-modal-sub-title span' ],
		offer_product_name: [ settings_data.product_name, '.wcf-pre-checkout-offer-product-title h1' ],
		offer_product_desc: [ settings_data.product_desc, '.wcf-pre-checkout-offer-desc span' ],
		offer_accept_button : [ settings_data.accept_button_text, '.wcf-pre-checkout-offer-btn-action.wcf-pre-checkout-add-cart-btn button' ],
		offer_skip_button : [ settings_data.skip_button_text, '.wcf-pre-checkout-offer-btn-action.wcf-pre-checkout-skip-btn .wcf-pre-checkout-skip' ]
	};

	if( 'yes' === enable_product_options ) {

		form.find( "#fl-field-product_options_position .fl-field-label" ).show();
		form.find( "#fl-field-product_options_position select" ).show();
		form.find( "#fl-field-product_options_position .fl-field-description" ).hide();

		form.find( "#fl-field-product_options_skin" ).show();
		form.find( "#fl-field-product_options_images" ).show();
		form.find( "#fl-field-product_option_section_title_text" ).show();

		form.find('#fl-builder-settings-section-product_style').show();

	} else {

		form.find( "#fl-field-product_options_position .fl-field-label" ).hide();
		form.find( "#fl-field-product_options_position select" ).hide();
		form.find( "#fl-field-product_options_position .fl-field-description" ).show();

		form.find( "#fl-field-product_options_skin" ).hide();
		form.find( "#fl-field-product_options_images" ).hide();
		form.find( "#fl-field-product_option_section_title_text" ).hide();

		form.find('#fl-builder-settings-section-product_style').hide();
	}



})(jQuery);
