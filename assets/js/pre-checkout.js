jQuery( function ( $ ) {
	if ( jQuery( '#wcf-pre-checkout-offer-modal' ).length < 1 ) {
		return;
	}

	let current_gateway = '';

	function wcf_pre_checkout_process() {
		const payment_method = jQuery( 'form.checkout' )
			.find( 'input[name="payment_method"]:checked' )
			.val();

		current_gateway = payment_method;

		jQuery( '.wcf_validation_error' ).remove();

		jQuery( 'form.checkout' )
			.addClass( 'loader' )
			.block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6,
				},
			} );

		$.ajax( {
			url: wc_checkout_params.wc_ajax_url
				.toString()
				.replace( '%%endpoint%%', 'wcf_validate_form' ),
			data:
				$( 'form.checkout' ).serialize() +
				'&security=' +
				cartflows.wcf_pre_checkout_validate_form_nonce,
			dataType: 'json',
			type: 'POST',
			success( response ) {
				try {
					jQuery( 'form.checkout' ).removeClass( 'loader' ).unblock();
					let display = '';
					if ( response.result === 'success' ) {
						if (
							'authorize_net_cim_credit_card' === payment_method
						) {
							display = 'yes';
						} else {
							const payment_method_verified = jQuery(
								'form.checkout'
							).triggerHandler(
								'checkout_place_order_' + payment_method
							);

							if ( payment_method_verified === undefined ) {
								display = 'yes';
							} else if (
								payment_method_verified !== undefined &&
								payment_method_verified === true
							) {
								display = 'yes';
							} else {
								display = 'no';
							}
						}

						if ( display === 'yes' ) {
							const billing_first_name = jQuery(
								'#billing_first_name'
							).val();
							const old_title = jQuery(
								'.wcf-pre-checkout-offer-wrapper .wcf-content-modal-title h1'
							).text();

							const result = old_title.match( /{([^}]+)}/ );

							if (
								result !== null &&
								billing_first_name.length !== 0
							) {
								const str_tag = result[ 1 ];
								let new_title = '';
								if ( str_tag === 'first_name' ) {
									new_title = old_title.replace(
										/{(.*?)}/,
										'<span class="wcf_first_name"></span>'
									);
								} else {
									new_title = old_title.replace(
										/{(.*?)}/,
										''
									);
								}

								$(
									'.wcf-pre-checkout-offer-wrapper .wcf-content-modal-title h1'
								).html( new_title );

								$( '.wcf_first_name' ).text(
									billing_first_name
								);
							}

							$( '.wcf-pre-checkout-offer-wrapper' ).addClass(
								'open'
							);
							$( 'html' ).addClass(
								'wcf-pre-checkout-offer-open'
							);
							$( '.wcf-pre-checkout-offer-action' ).val( '' );

							setTimeout( function () {
								update_popup_height();
							}, 100 );
						} //end of display
					} else if ( response.result === 'failure' ) {
						throw 'Result failure';
					} else {
						throw 'Invalid response';
					}
				} catch ( err ) {
					if ( response.messages ) {
						jQuery( 'form.checkout' ).prepend(
							'<div class="wcf_validation_error">' +
								response.messages +
								'</div>'
						);

						jQuery( 'form.checkout' )
							.removeClass( 'processing' )
							.unblock();
						jQuery( 'form.checkout' )
							.find( '.input-text, select, input:checkbox' )
							.trigger( 'validate' )
							.blur();
						let scrollElement = $( '.wcf_validation_error' );

						if ( ! scrollElement.length ) {
							scrollElement = $( '.form.checkout' );
						}
						$.scroll_to_notices( scrollElement );
					}
				} //end of catch
			}, //end of success
		} ); //end of ajax
	}

	jQuery( 'form.checkout' ).on(
		'checkout_place_order.wcf_pre_checkout',
		function () {
			if ( jQuery( '.wcf-pre-checkout-offer-action' ).val() === 'add' ) {
				setTimeout( function () {
					wcf_pre_checkout_process();
				}, 100 );

				return false;
			}
		}
	);

	$( 'body' ).on( 'click', '.wcf-pre-checkout-offer-btn', function ( e ) {
		e.preventDefault();

		const checkout_id = $( '._wcf_checkout_id' ).val();
		if ( checkout_id !== '' ) {
			$( '.wcf-pre-checkout-offer-btn' ).html(
				cartflows.add_to_cart_text
			);
			$.ajax( {
				url: cartflows.ajax_url,
				data: {
					action: 'wcf_add_to_cart',
					product_quantity: 1,
					checkout_id,
					security: cartflows.wcf_pre_checkout_offer_nonce,
				},
				dataType: 'json',
				type: 'POST',
				success( response ) {
					/* Unbind Event */
					jQuery( 'form.checkout' ).off(
						'checkout_place_order.wcf_pre_checkout'
					);

					if ( 'authorize_net_cim_credit_card' === current_gateway ) {
						$( 'body' ).trigger( 'update_checkout', {
							update_shipping_method: false,
						} );

						// Fire updated_checkout event.
						$( document.body ).on(
							'updated_checkout.wcf_pre_checkout',
							function ( data ) {
								console.log( data );
								$( '.wcf-pre-checkout-offer-action' ).val( '' );
								$( '.wcf-pre-checkout-offer-btn' ).html(
									response.message
								);
								$(
									'.wcf-pre-checkout-offer-wrapper'
								).removeClass( 'open' );
								$( 'html' ).removeClass(
									'wcf-pre-checkout-offer-open'
								);
								$( 'form.checkout' ).submit();

								/* Unbind Event */
								$( document.body ).off(
									'updated_checkout.wcf_pre_checkout'
								);
							}
						);
					} else {
						$( '.wcf-pre-checkout-offer-action' ).val( '' );
						$( '.wcf-pre-checkout-offer-btn' ).html(
							response.message
						);

						setTimeout( function () {
							$( '.wcf-pre-checkout-offer-wrapper' ).removeClass(
								'open'
							);
							$( 'html' ).removeClass(
								'wcf-pre-checkout-offer-open'
							);
							$( 'form.checkout' ).submit();
						}, 600 );
					}
				},
			} );
		}
	} );

	$( 'body' ).on( 'click', '.wcf-pre-checkout-skip', function () {
		/* Unbind Event */
		jQuery( 'form.checkout' ).off(
			'checkout_place_order.wcf_pre_checkout'
		);

		$( '.wcf-pre-checkout-offer-action' ).val( '' );
		$( '.wcf-pre-checkout-offer-wrapper' ).removeClass( 'open' );
		$( 'html' ).removeClass( 'wcf-pre-checkout-offer-open' );
		$( 'form.checkout' ).submit();
	} );

	$( window ).on( 'resize', function () {
		update_popup_height();
	} );

	const update_popup_height = function () {
		const window_height = $( window ).height(),
			popup_height = $( '#wcf-pre-checkout-offer-modal' ).height();

		if ( popup_height > window_height ) {
			$( 'html' ).removeClass( 'wcf-pre-checkout-screen-size' );
		} else {
			$( 'html' ).addClass( 'wcf-pre-checkout-screen-size' );
		}
	};
} );
