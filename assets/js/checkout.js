( function ( $ ) {
	const wcf_page_title_notification = {
		vars: {
			originalTitle: document.title,
			interval: null,
		},
		On( notification, intervalSpeed ) {
			const _this = this;
			_this.vars.interval = setInterval(
				function () {
					document.title =
						_this.vars.originalTitle === document.title
							? notification
							: _this.vars.originalTitle;
				},
				intervalSpeed ? intervalSpeed : 1000
			);
		},
		Off() {
			clearInterval( this.vars.interval );
			document.title = this.vars.originalTitle;
		},
	};

	const wcf_update_product_options_cart_item_key = function () {
		$( document ).on(
			'wcf_cart_data_restored',
			function ( e, wcf_cart_data ) {
				if ( 'yes' === cartflows.is_product_options && wcf_cart_data ) {
					$( '.wcf-qty-row' ).each( function () {
						const product_data = $( this ).data( 'options' );
						if ( product_data ) {
							if ( product_data.unique_id in wcf_cart_data ) {
								product_data.cart_item_key =
									wcf_cart_data[ product_data.unique_id ];
								$( this ).attr(
									'data-options',
									JSON.stringify( product_data )
								);
							}
						}
					} );
				}
			}
		);
	};

	const wcf_animate_browser_tab = function () {
		if ( 'yes' !== cartflows_animate_tab_fields.enabled ) {
			return;
		}

		$( window ).blur( function () {
			wcf_page_title_notification.On(
				cartflows_animate_tab_fields.title
			);
		} );

		$( window ).on( 'focus', function () {
			wcf_page_title_notification.Off();
		} );
	};

	// Scroll to top for the two step navigation.
	const wcf_scroll_to_top = function ( scrollTo ) {
		if ( scrollTo.length ) {
			event.preventDefault();
			$( 'html, body' )
				.stop()
				.animate(
					{
						scrollTop: scrollTo.offset().top - 50,
					},
					100
				);
		}
	};

	const wcf_display_spinner = function () {
		$(
			'.woocommerce-checkout-review-order-table, .wcf-product-option-wrap, .wcf-bump-order-wrap'
		).block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6,
			},
		} );
	};

	const wcf_remove_spinner = function ( rsp ) {
		if ( ! cartflows.wcf_refresh_checkout ) {
			if ( jQuery( '.wc_payment_methods' ).length ) {
				if ( rsp.hasOwnProperty( 'cart_total' ) ) {
					// Update the Fragements for order bump & product options add/remove.
					if ( rsp.hasOwnProperty( 'fragments' ) ) {
						$.each( rsp.fragments, function ( key, value ) {
							// Update the Fragments for place order button to update the cart price displayed on button.
							if ( '#place_order' === key ) {
								const selected_payment_gateway = $(
									".wc_payment_methods input[name='payment_method']:checked"
								);

								if ( selected_payment_gateway.length > 0 ) {
									$( key ).replaceWith( value );
									const selected_payment_method = selected_payment_gateway.val();
									if (
										'ppcp-gateway' ===
											selected_payment_method ||
										'ppcp' === selected_payment_method
									) {
										$( key ).css( 'display', 'none' );
									}
									selected_payment_gateway.trigger( 'click' );
								}
							} else {
								$( key ).replaceWith( value );
							}
						} );
					}

					if ( parseFloat( rsp.cart_total ) > 0 ) {
						/**
						 * Universal trigger just like update_checkout but of CartFlows.
						 * This trigger will be trigger after Order Bump, product options, quantity update in processes.
						 */
						$( 'body' ).trigger( 'cartflows_update_checkout' );

						$(
							'.woocommerce-checkout-review-order-table, .wcf-product-option-wrap, .wcf-bump-order-wrap'
						).unblock();
					} else {
						$( 'body' ).trigger( 'update_checkout' );
						$(
							'.woocommerce-checkout-review-order-table, .wcf-product-option-wrap, .wcf-bump-order-wrap'
						).unblock();
					}
				}
			} else {
				$( 'body' ).trigger( 'update_checkout' );
				$(
					'.woocommerce-checkout-review-order-table, .wcf-product-option-wrap, .wcf-bump-order-wrap'
				).unblock();
			}
		} else {
			$( 'body' ).trigger( 'update_checkout' );
			$(
				'.woocommerce-checkout-review-order-table, .wcf-product-option-wrap, .wcf-bump-order-wrap'
			).unblock();
		}
	};

	/**
	 * Re-calculate the cart total by triggering 'updated_cart_totals' trigger.
	 */
	const wcf_re_calculate_totals = function () {
		// Re-calculate the cart total
		$( document.body ).trigger( 'updated_cart_totals' );
	};

	const wcf_product_quantity_var_options = function () {
		const wcf_variation_validation_trigger_click = function () {
			$( 'form.woocommerce-checkout' ).on(
				'checkout_place_order',
				function ( e ) {
					const invalid_var = $(
						'.wcf-select-variation-attribute.wcf-invalid-variation'
					);

					if ( invalid_var.length > 0 ) {
						e.preventDefault();
						wcf_scroll_to_top( invalid_var );
						return ! 1;
					}
				}
			);

			$( '.wcf-select-variation-attribute' ).on( 'click', function ( e ) {
				e.preventDefault();

				const wrap = $( this ).closest( '.wcf-qty-row' );

				wrap.find( '.wcf-item-choose-options a' ).trigger( 'click' );
			} );
		};

		$( '.wcf-qty-row' ).on( 'click', function ( event ) {
			if (
				$( event.target ).is(
					'.wcf-multiple-sel, .wcf-single-sel, .wcf-qty, .wcf-qty-selection, .wcf-variable-item-popup-text, .wcf-qty-selection-btn, .wcf-qty-increment-icon, .wcf-qty-decrement-icon'
				)
			) {
				return;
			}

			const single_selection = $( this ).find( '.wcf-single-sel' ),
				multiple_selection = $( this ).find( '.wcf-multiple-sel' );

			if ( single_selection.length > 0 ) {
				single_selection.trigger( 'click' );
			} else if ( multiple_selection.length > 0 ) {
				multiple_selection.trigger( 'click' );
			}
		} );

		/* Single Selection */
		$( document ).on( 'change', '.wcf-single-sel', function () {
			const $this = $( this );
			const wrapper = $this.closest( '.wcf-qty-options' );
			const wrap = $this.closest( '.wcf-qty-row' );
			const option = wrap.data( 'options' );
			const input = wrap.find( '.wcf-qty input' );
			let input_quantity = parseInt( input.val() );
			const checkout_id = $( '._wcf_checkout_id' ).val();

			if ( 0 >= input_quantity || isNaN( input_quantity ) ) {
				input.val( 1 );
				input_quantity = 1;
			}

			option.input_quantity = input_quantity;
			option.checkout_id = checkout_id;

			const post_data = $( 'form.checkout' ).serialize();

			wcf_display_spinner();
			wrapper.addClass( 'wcf-loading' );
			$.ajax( {
				url: cartflows.ajax_url,
				data: {
					action: 'wcf_single_selection',
					option,
					post_data,
					security: cartflows.wcf_single_selection_nonce,
				},
				dataType: 'json',
				type: 'POST',
				success( response ) {
					if ( response.hasOwnProperty( 'cartflows_data' ) ) {
						wrap.find( '.wcf-display-quantity' ).html(
							response.cartflows_data.display_quantity
						);
						wrap.find( '.wcf-display-price' ).html(
							response.cartflows_data.display_price
						);
						wrap.find( '.wcf-display-discount-value' ).html(
							response.cartflows_data.display_discount_value
						);
						wrap.find( '.wcf-display-discount-percent' ).html(
							response.cartflows_data.display_discount_percent
						);

						wrap.find( '.wcf_subscription_price' ).html(
							response.cartflows_data.subscription_price
						);
						wrap.find( '.wcf_subscription_fee' ).html(
							response.cartflows_data.sign_up_fee
						);
						console.log( response );
					}

					wcf_remove_spinner( response );
					wrapper.removeClass( 'wcf-loading' );
					option.cart_item_key = response.cart_item_key;
					wrap.attr( 'data-options', JSON.stringify( option ) );

					// Re-calculate the cart total
					wcf_re_calculate_totals();
				},
				error() {
					$( '.woocommerce-checkout-review-order-table' ).unblock();
				},
			} );
		} );

		/* Multiple Selection */
		$( document ).on( 'change', '.wcf-multiple-sel', function () {
			const checked_cb = $( '.wcf-multiple-sel:checked' );
			const $this = $( this );
			const wrap = $this.closest( '.wcf-qty-row' );

			const input = wrap.find( '.wcf-qty input' );
			let input_quantity = parseInt( input.val() );

			if ( 0 >= input_quantity || isNaN( input_quantity ) ) {
				input.val( 1 );
				input_quantity = 1;
			}

			if ( 0 === checked_cb.length ) {
				$this.prop( 'checked', true );
				$this.prop( 'disabled', true );
				return;
			}

			if ( 1 === checked_cb.length ) {
				checked_cb.prop( 'disabled', true );
			} else {
				checked_cb.removeAttr( 'disabled' );
			}

			const option = wrap.data( 'options' );
			const checkout_id = $( '._wcf_checkout_id' ).val();
			option.checkout_id = checkout_id;
			option.input_quantity = input_quantity;
			option.checked = 'no';
			const post_data = $( 'form.checkout' ).serialize();

			if ( $this.is( ':checked' ) ) {
				option.checked = 'yes';
			}

			wcf_display_spinner();
			$( '.wcf-qty-options' ).addClass( 'wcf-loading' );
			$.ajax( {
				url: cartflows.ajax_url,
				data: {
					action: 'wcf_multiple_selection',
					option,
					post_data,
					security: cartflows.wcf_multiple_selection_nonce,
				},
				dataType: 'json',
				type: 'POST',
				success( response ) {
					if ( response.hasOwnProperty( 'cartflows_data' ) ) {
						wrap.find( '.wcf-display-quantity' ).html(
							response.cartflows_data.display_quantity
						);
						wrap.find( '.wcf-display-price' ).html(
							response.cartflows_data.display_price
						);
						wrap.find( '.wcf-display-discount-value' ).html(
							response.cartflows_data.display_discount_value
						);
						wrap.find( '.wcf-display-discount-percent' ).html(
							response.cartflows_data.display_discount_percent
						);

						wrap.find( '.wcf_subscription_price' ).html(
							response.cartflows_data.subscription_price
						);
						wrap.find( '.wcf_subscription_fee' ).html(
							response.cartflows_data.sign_up_fee
						);
					}

					wcf_remove_spinner( response );
					$( '.wcf-qty-options' ).removeClass( 'wcf-loading' );
					option.cart_item_key = response.cart_item_key;
					wrap.attr( 'data-options', JSON.stringify( option ) );

					// Re-calculate the cart total
					wcf_re_calculate_totals();
				},
				error() {
					$( '.woocommerce-checkout-review-order-table' ).unblock();
				},
			} );
		} );

		/* Force All Selection */
		$( document ).on( 'change', '.wcf-var-sel', function () {
			const $this = $( this );
			const wrap = $this.closest( '.wcf-qty-row' );
			const input = wrap.find( '.wcf-qty input' );
			const option = wrap.data( 'options' );
			const input_quantity = parseInt( input.val() );
			const checkout_id = $( '._wcf_checkout_id' ).val();

			option.checkout_id = checkout_id;
			option.input_quantity = input_quantity;

			wcf_display_spinner();
			$( '.wcf-qty-options' ).addClass( 'wcf-loading' );
			$.ajax( {
				url: cartflows.ajax_url,
				data: {
					action: 'wcf_variation_selection',
					option,
					security: cartflows.wcf_variation_selection_nonce,
				},
				dataType: 'json',
				type: 'POST',
				success( response ) {
					if ( response.hasOwnProperty( 'cartflows_data' ) ) {
						wrap.find( '.wcf-display-quantity' ).html(
							response.cartflows_data.display_quantity
						);
						wrap.find( '.wcf-display-price' ).html(
							response.cartflows_data.display_price
						);
						wrap.find( '.wcf-display-discount-value' ).html(
							response.cartflows_data.display_discount_value
						);
						wrap.find( '.wcf-display-discount-percent' ).html(
							response.cartflows_data.display_discount_percent
						);

						wrap.find( '.wcf_subscription_price' ).html(
							response.cartflows_data.subscription_price
						);
						wrap.find( '.wcf_subscription_fee' ).html(
							response.cartflows_data.sign_up_fee
						);
					}

					wcf_remove_spinner( response );
					$( '.wcf-qty-options' ).removeClass( 'wcf-loading' );
					// Re-calculate the cart total
					wcf_re_calculate_totals();
				},
				error() {
					$( '.woocommerce-checkout-review-order-table' ).unblock();
				},
			} );
		} );

		/* Quantity Selection For All type */
		$( document ).on( 'change', '.wcf-qty-selection', function () {
			const $this = $( this ),
				wrap = $this.closest( '.wcf-qty-row' ),
				item_selector = wrap.find( '.wcf-item-selector' );

			if ( item_selector.length > 0 ) {
				const selector_input = item_selector.find( 'input' );

				if (
					selector_input.length > 0 &&
					! selector_input.is( ':checked' )
				) {
					return;
				}
			}

			const is_sold_individually = Boolean( $this.data( 'sale-limit' ) );

			// Skip the ajax call if the sold individual option is enabled for the selected product OR a variation.
			if ( is_sold_individually ) {
				return;
			}

			const option = wrap.data( 'options' ),
				checkout_id = $( '._wcf_checkout_id' ).val();

			let input_quantity = parseInt( $this.val() );
			if ( 0 >= input_quantity || isNaN( input_quantity ) ) {
				$this.val( 1 );
				input_quantity = 1;
			}

			option.input_quantity = input_quantity;
			option.checkout_id = checkout_id;
			const post_data = $( 'form.checkout' ).serialize();

			if ( typeof data !== 'undefined' ) {
				option.cart_item_key = data.cart_item_key;
			}

			wcf_display_spinner();

			$( '.wcf-qty-options' ).addClass( 'wcf-loading' );

			$.ajax( {
				url: cartflows.ajax_url,
				data: {
					action: 'wcf_quantity_update',
					option,
					post_data,
					security: cartflows.wcf_quantity_update_nonce,
				},
				dataType: 'json',
				type: 'POST',
				success( response ) {
					if ( response.hasOwnProperty( 'cartflows_data' ) ) {
						wrap.find( '.wcf-display-quantity' ).html(
							response.cartflows_data.display_quantity
						);
						wrap.find( '.wcf-display-price' ).html(
							response.cartflows_data.display_price
						);
						wrap.find( '.wcf-display-discount-value' ).html(
							response.cartflows_data.display_discount_value
						);
						wrap.find( '.wcf-display-discount-percent' ).html(
							response.cartflows_data.display_discount_percent
						);

						wrap.find( '.wcf_subscription_price' ).html(
							response.cartflows_data.subscription_price
						);
						wrap.find( '.wcf_subscription_fee' ).html(
							response.cartflows_data.sign_up_fee
						);
					}

					wcf_remove_spinner( response );
					$( '.wcf-qty-options' ).removeClass( 'wcf-loading' );
					// Re-calculate the cart total
					wcf_re_calculate_totals();
				},
				error() {
					$( '.woocommerce-checkout-review-order-table' ).unblock();
				},
			} );
		} );

		/* Variation attribute and click trigger */
		wcf_variation_validation_trigger_click();

		/* Variation Popup */
		wcf_quick_view();
	};

	const wcf_do_not_remove_single_product = function () {
		const checked_cb = $( '.wcf-multiple-sel:checked' );

		if ( 1 === checked_cb.length ) {
			checked_cb.prop( 'checked', true );
			checked_cb.prop( 'disabled', true );
		}
	};

	const wcf_remove_product = function () {
		$( document ).on( 'cartflows_remove_product', function ( e, p_id ) {
			jQuery( '.wcf-multiple-sel[value="' + p_id + '"]' ).prop(
				'checked',
				false
			);
			jQuery( '.wcf-single-sel[value="' + p_id + '"]' ).prop(
				'checked',
				false
			);
		} );
	};

	const wcf_order_bump_ajax = function () {
		let wcf_order_bump_clicked = false;

		$( document ).on( 'change', '.wcf-bump-order-cb', function () {
			if ( true === wcf_order_bump_clicked ) {
				return false;
			}

			wcf_order_bump_clicked = true;

			const $this = $( this );
			const product_id = $this.data( 'ob_data' ).product_id;
			const checkout_id = $( '[name=_wcf_checkout_id]' ).val();
			const bump_offer_data = $this.attr( 'data-ob_data' ),
				ob_id = $this.data( 'ob_data' ).ob_id;
			const bump_offer_qty = $this
				.closest( '.wcf-bump-order-content' )
				.find( '.wcf-order-bump-quantity-updater' )
				.val();

			const button_class = $this
				.closest( '.wcf-bump-order-field-wrap' )
				.find( '.wcf-bump-order-cb-button' );
			const bump_offer_wrap = $this.closest( '.wcf-bump-order-wrap' );

			let is_add_to_cart = '';

			const data = {
				post_data: $( 'form.checkout' ).serialize(),
				security: cartflows.wcf_bump_order_process_nonce,
				_wcf_checkout_id: checkout_id,
				_wcf_product_id: product_id,
				_bump_offer_data: bump_offer_data ? bump_offer_data : '',
				_bump_offer_qty: bump_offer_qty > 0 ? bump_offer_qty : '',
				action: 'wcf_bump_order_process',
			};

			let bump_product_ids = $( '[name=_wcf_bump_products]' ).val();

			if ( '' === bump_product_ids ) {
				bump_product_ids = {};
			} else {
				bump_product_ids = JSON.parse( bump_product_ids );
			}

			if ( $this.is( ':checked' ) ) {
				data._wcf_bump_product_action = 'add_bump_product';
				is_add_to_cart = true;
			} else {
				data._wcf_bump_product_action = 'remove_bump_product';
				is_add_to_cart = false;
			}

			// Display spinner for specific order bump.
			bump_offer_wrap.block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6,
				},
			} );

			$.ajax( {
				url: cartflows.ajax_url,
				data,
				dataType: 'json',
				type: 'POST',
				success( response ) {
					wcf_remove_spinner( response );
					if ( button_class.length > 0 ) {
						if ( is_add_to_cart ) {
							button_class.toggleClass(
								'wcf-bump-add-to-cart wcf-bump-remove-from-cart'
							);
							button_class.text( $this.data( 'remove' ) );
						} else {
							button_class.toggleClass(
								'wcf-bump-remove-from-cart wcf-bump-add-to-cart'
							);
							button_class.text( $this.data( 'add' ) );
						}
					}

					if ( $this.is( ':checked' ) ) {
						bump_product_ids[ ob_id ] = {
							id: product_id,
							price: response.cartflows_data.total_product_price,
						};
					} else {
						delete bump_product_ids[ ob_id ];
					}
					$( '[name=_wcf_bump_products]' ).val(
						JSON.stringify( bump_product_ids )
					);
					// Re-calculate the cart total
					wcf_re_calculate_totals();
				},
				error() {
					$( '.woocommerce-checkout-review-order-table' ).unblock();
				},
			} );

			wcf_order_bump_clicked = false;

			return false;
		} );
	};

	const wcf_nav_tab_hide_show_events = function () {
		/* Ready */
		wcf_nav_tab_hide_show();

		$( '.wcf-embed-checkout-form-two-step .woocommerce' ).addClass(
			'step-one'
		);

		/* Change Custom Field*/
		$(
			'.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps a'
		).on( 'click', function ( e ) {
			e.preventDefault();
			wcf_nav_tab_hide_show();
		} );

		/* Change on click of next button */
		$(
			'.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-nav-btns a'
		).on( 'click', function ( e ) {
			e.preventDefault();

			// Check form validation before go to step two.

			wcf_nav_tab_hide_show_next_btn();
		} );
	};

	const wcf_nav_tab_hide_show_next_btn = function () {
		if ( wcf_two_step_validations() ) {
			$(
				'.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps div.wcf-current'
			).removeClass( 'wcf-current' );

			const selector = $(
				'.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-nav-btns a'
			).attr( 'href' );

			if ( selector === '#customer_details' ) {
				wcf_scroll_to_top( $( '.wcf-embed-checkout-form-nav' ) );
				$(
					'.wcf-embed-checkout-form-two-step .woocommerce'
				).removeClass( 'step-two' );
				$( '.wcf-embed-checkout-form-two-step .woocommerce' ).addClass(
					'step-one'
				);
				$( '.wcf-embed-checkout-form-two-step' )
					.find( '.step-one' )
					.addClass( 'wcf-current' );
			} else if ( selector === '#wcf-order-wrap' ) {
				wcf_scroll_to_top( $( '.wcf-embed-checkout-form-nav' ) );
				$(
					'.wcf-embed-checkout-form-two-step .woocommerce'
				).removeClass( 'step-one' );
				$( '.wcf-embed-checkout-form-two-step .woocommerce' ).addClass(
					'step-two'
				);
				$( '.wcf-embed-checkout-form-two-step' )
					.find( '.step-two' )
					.addClass( 'wcf-current' );
			}
		}
	};

	const wcf_nav_tab_hide_show = function () {
		$(
			'.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps a'
		).on( 'click', function ( e ) {
			e.preventDefault();

			// Check form validation before go to step two.
			// if ( $("div").hasClass("wcf-embed-checkout-form-two-step") && ! $('form[name="checkout"]').valid() ) {
			// 	return false;
			// }

			if ( wcf_two_step_validations() ) {
				const $this = $( this ),
					wrap = $this.closest(
						'.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps div'
					);
				// 	validated = wcf_woocommerce_field_validate();

				// if(validated === false){
				// 	return false;
				// }

				$(
					'.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps div.wcf-current'
				).removeClass( 'wcf-current' );

				wrap.addClass( 'wcf-current' );

				const selector = $this
					.closest(
						'.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps div a'
					)
					.attr( 'href' );
				if ( selector === '#customer_details' ) {
					$(
						'.wcf-embed-checkout-form-two-step .woocommerce'
					).removeClass( 'step-two' );
					$(
						'.wcf-embed-checkout-form-two-step .woocommerce'
					).addClass( 'step-one' );
				} else if ( selector === '#wcf-order-wrap' ) {
					$(
						'.wcf-embed-checkout-form-two-step .woocommerce'
					).removeClass( 'step-one' );
					$(
						'.wcf-embed-checkout-form-two-step .woocommerce'
					).addClass( 'step-two' );
				}
			}
		} );
	};

	/* Autocomplete Zip Code */
	const wcf_autocomplete_zip_data = function () {
		let zip_code_timeout;

		$( document.body ).on(
			'textInput input change keypress paste',
			'#billing_postcode, #shipping_postcode',
			function () {
				const $this = $( this ),
					type = $this.attr( 'id' ).split( '_' )[ 0 ],
					country = $( '#' + type + '_country' ).val();

				if ( '' === country ) {
					return;
				}

				const zip_code = $this.val().trim();
				if ( '' === zip_code ) {
					return;
				}

				clearTimeout( zip_code_timeout );

				zip_code_timeout = setTimeout( function () {
					if ( -1 === [ 'GB', 'CA' ].indexOf( country ) ) {
						get_zip_data_and_update( type, country, zip_code );
					}
				}, 800 );
			}
		);

		const get_zip_data_and_update = function ( type, country, zip_code ) {
			$.ajax( {
				url: 'https://api.zippopotam.us/' + country + '/' + zip_code,
				cache: ! 0,
				dataType: 'json',
				type: 'GET',
				success( result ) {
					$.each( result.places, function () {
						$( '#' + type + '_city' )
							.val( this[ 'place name' ] )
							.trigger( 'change' );
						$( '[name="' + type + '_state"]:visible' )
							.val( this[ 'state abbreviation' ] )
							.trigger( 'change' );
						return false;
					} );
				},
				error() {},
			} );
		};
	};

	/**
	 * Quick View
	 */
	const wcf_quick_view = function () {
		const quick_view_btn = $( '.wcf-item-choose-options a' );

		const modal_wrap = $( '.wcf-quick-view-wrapper' );

		modal_wrap.appendTo( document.body );

		const wcf_quick_view_bg = modal_wrap.find( '.wcf-quick-view-bg' ),
			wcf_qv_modal = modal_wrap.find( '#wcf-quick-view-modal' ),
			wcf_qv_content = wcf_qv_modal.find( '#wcf-quick-view-content' ),
			wcf_qv_close_btn = wcf_qv_modal.find( '#wcf-quick-view-close' ),
			wcf_qv_wrapper = wcf_qv_modal.find( '.wcf-content-main-wrapper' );

		quick_view_btn.off( 'click' ).on( 'click', function ( e ) {
			e.preventDefault();

			const $this = $( this );

			/* Check if product is selected */
			const cls_wrap = $this.closest( '.wcf-item' );

			if (
				! cls_wrap.find( '.wcf-item-selector input' ).is( ':checked' )
			) {
				cls_wrap.find( '.wcf-item-selector input' ).trigger( 'click' );
			}

			const product_id = $this.data( 'product' );

			$this.addClass( 'wcf-variation-popup-open' );

			if ( ! wcf_qv_modal.hasClass( 'loading' ) ) {
				wcf_qv_modal.addClass( 'loading' );
			}

			if ( ! wcf_quick_view_bg.hasClass( 'wcf-quick-view-bg-ready' ) ) {
				wcf_quick_view_bg.addClass( 'wcf-quick-view-bg-ready' );
			}

			$( document ).trigger( 'wcf_quick_view_loading' );

			wcf_qv_ajax_call( $this, product_id );
		} );

		const wcf_qv_ajax_call = function ( t, product_id ) {
			wcf_qv_modal.css( 'opacity', 0 );

			$.ajax( {
				url: cartflows.ajax_url,
				data: {
					action: 'wcf_woo_quick_view',
					product_id,
				},
				dataType: 'html',
				type: 'POST',
				success( data ) {
					wcf_qv_content.html( data );
					wcf_qv_content_height();
				},
			} );
		};

		const wcf_qv_content_height = function () {
			// Variation Form
			const form_variation = wcf_qv_content.find( '.variations_form' );

			form_variation.trigger( 'check_variations' );
			form_variation.trigger( 'reset_image' );

			if ( ! wcf_qv_modal.hasClass( 'open' ) ) {
				wcf_qv_modal.removeClass( 'loading' ).addClass( 'open' );

				const scrollbar_width = wcf_get_scrollbar_width();
				const $html = $( 'html' );

				$html.css( 'margin-right', scrollbar_width );
				$html.addClass( 'wcf-quick-view-is-open' );
			}

			if (
				form_variation.length > 0 &&
				'function' === typeof form_variation.wc_variation_form
			) {
				form_variation.wc_variation_form();
				form_variation.find( 'select' ).trigger( 'change' );
			}

			/*wcf_qv_content.imagesLoaded( function(e) {

				var image_slider_wrap = wcf_qv_modal.find('.wcf-qv-image-slider');

				if ( image_slider_wrap.find('li').length > 1 ) {
					image_slider_wrap.flexslider({
						animation: "slide",
						start: function( slider ){
							setTimeout(function() {
								wcf_update_summary_height( true );
							}, 300);
						},
					});
				}else{
					setTimeout(function() {
						wcf_update_summary_height( true );
					}, 300);
				}
			});*/

			const image_slider_wrap = wcf_qv_modal.find(
				'.wcf-qv-image-slider'
			);

			if ( image_slider_wrap.find( 'li' ).length > 1 ) {
				image_slider_wrap.flexslider( {
					animation: 'slide',
					start() {
						setTimeout( function () {
							wcf_update_summary_height( true );
						}, 300 );
					},
				} );
			} else {
				setTimeout( function () {
					wcf_update_summary_height( true );
				}, 300 );
			}

			// Add trrigger to slide back to the varations selected image while in flexslider.
			$( document ).on(
				'woocommerce_gallery_reset_slide_position',
				function () {
					// Varations image is always replaced at the first index of the slider.
					image_slider_wrap.flexslider( 0 );
				}
			);

			// stop loader
			$( document ).trigger( 'wcf_quick_view_loader_stop' );
		};

		const wcf_qv_close_modal = function () {
			// Close box by click overlay
			wcf_qv_wrapper.on( 'click', function ( e ) {
				if ( this === e.target ) {
					wcf_qv_close();
				}
			} );

			// Close box with esc key
			$( document ).on( 'keyup', function ( e ) {
				if ( e.keyCode === 27 ) {
					wcf_qv_close();
				}
			} );

			// Close box by click close button
			wcf_qv_close_btn.on( 'click', function ( e ) {
				e.preventDefault();
				wcf_qv_close();
			} );

			const wcf_qv_close = function () {
				wcf_quick_view_bg.removeClass( 'wcf-quick-view-bg-ready' );
				wcf_qv_modal.removeClass( 'open' ).removeClass( 'loading' );
				$( 'html' ).removeClass( 'wcf-quick-view-is-open' );
				$( 'html' ).css( 'margin-right', '' );

				quick_view_btn.removeClass( 'wcf-variation-popup-open' );

				setTimeout( function () {
					wcf_qv_content.html( '' );
				}, 600 );
			};
		};

		/*var	ast_qv_center_modal = function() {

			ast_qv_wrapper.css({
				'width'     : '',
				'height'    : ''
			});

			ast_qv_wrapper_w 	= ast_qv_wrapper.width(),
			ast_qv_wrapper_h 	= ast_qv_wrapper.height();

			var window_w = $(window).width(),
				window_h = $(window).height(),
				width    = ( ( window_w - 60 ) > ast_qv_wrapper_w ) ? ast_qv_wrapper_w : ( window_w - 60 ),
				height   = ( ( window_h - 120 ) > ast_qv_wrapper_h ) ? ast_qv_wrapper_h : ( window_h - 120 );

			ast_qv_wrapper.css({
				'left' : (( window_w/2 ) - ( width/2 )),
				'top' : (( window_h/2 ) - ( height/2 )),
				'width'     : width + 'px',
				'height'    : height + 'px'
			});
		};

		*/
		const wcf_update_summary_height = function ( update_css ) {
			const quick_view = wcf_qv_content,
				img_height = quick_view
					.find( '.product .wcf-qv-image-slider' )
					.first()
					.height(),
				summary = quick_view.find( '.product .summary.entry-summary' ),
				content = summary.css( 'content' );

			if (
				'undefined' !== typeof content &&
				544 === content.replace( /[^0-9]/g, '' ) &&
				0 !== img_height &&
				null !== img_height
			) {
				summary.css( 'height', img_height );
			} else {
				summary.css( 'height', '' );
			}

			if ( true === update_css ) {
				wcf_qv_modal.css( 'opacity', 1 );
			}
		};

		const wcf_get_scrollbar_width = function () {
			const div = $(
				'<div style="width:50px;height:50px;overflow:hidden;position:absolute;top:-200px;left:-200px;"><div style="height:100px;"></div>'
			);
			// Append our div, do our calculation and then remove it
			$( 'body' ).append( div );
			const w1 = $( 'div', div ).innerWidth();
			div.css( 'overflow-y', 'scroll' );
			const w2 = $( 'div', div ).innerWidth();
			$( div ).remove();

			return w1 - w2;
		};

		wcf_qv_close_modal();
		//wcf_update_summary_height();

		window.addEventListener( 'resize', function () {
			wcf_update_summary_height();
		} );

		/* Add to cart ajax */
		/**
		 * wcf_add_to_cart_ajax class.
		 */
		const wcf_add_to_cart_ajax = function () {
			modal_wrap
				.off(
					'click',
					'#wcf-quick-view-content .single_add_to_cart_button'
				)
				.off( 'wcf_added_to_cart' )
				.on(
					'click',
					'#wcf-quick-view-content .single_add_to_cart_button',
					this.onAddToCart
				)
				.on( 'wcf_added_to_cart', this.updateButton );
		};

		/**
		 * Handle the add to cart event.
		 *
		 * @param {Object} e event data.
		 */
		wcf_add_to_cart_ajax.prototype.onAddToCart = function ( e ) {
			e.preventDefault();
			const $form = $( this ).closest( 'form' );

			// If the form inputs are invalid
			if ( ! $form[ 0 ].checkValidity() ) {
				$form[ 0 ].reportValidity();
				return false;
			}

			const $thisbutton = $( this ),
				product_id =
					$form.find( 'input[name="product_id"]' ).val() || '',
				variation_id =
					$form.find( 'input[name="variation_id"]' ).val() || '',
				choose_var = $( '.wcf-variation-popup-open' ),
				qty_wrap = choose_var.closest( '.wcf-qty-row' ),
				qty_selection = qty_wrap.find( '.wcf-qty-selection' ),
				input_quantity = qty_selection.val() || 1,
				qty_options = qty_wrap.data( 'options' ),
				checkout_id = $( '._wcf_checkout_id' ).val(),
				item_wrap = qty_wrap.find( '.wcf-item-wrap' );

			qty_options.input_quantity = input_quantity;
			qty_options.checkout_id = checkout_id;

			if ( $thisbutton.is( '.single_add_to_cart_button' ) ) {
				$thisbutton.removeClass( 'added' );
				$thisbutton.addClass( 'loading' );

				// Ajax action.
				if ( variation_id !== '' ) {
					jQuery.ajax( {
						url: cartflows.ajax_url,
						type: 'POST',
						data: {
							action: 'wcf_add_cart_single_product',
							form_data: $form.serialize(),
							product_id,
							variation_id,
							quantity: input_quantity,
							option: qty_options,
							security: cartflows.wcf_quick_view_add_cart_nonce,
						},
						dataType: 'json',
						success( response ) {
							console.log( response );

							if (
								response.hasOwnProperty( 'cartflows_data' ) &&
								'yes' === response.cartflows_data.added_to_cart
							) {
								const result = response.cartflows_data;

								/* Update Attributes to Name in summary */
								choose_var
									.closest( '.wcf-item' )
									.find( '.wcf-display-attributes' )
									.html( result.display_attr );

								choose_var
									.closest( '.wcf-item' )
									.find( '.wcf-item-image' )
									.html( result.variation_image );

								/* Update Variaiton id in attributes */
								choose_var.attr(
									'data-variation',
									result.variation_id
								);
								qty_options.variation_id = result.variation_id;
								qty_options.original_price =
									result.original_price;
								qty_options.discounted_price =
									result.discounted_price;

								qty_options.subscription_price =
									result.subscription_price;
								qty_options.sign_up_fee = result.signup_fee;
								qty_wrap.attr(
									'data-options',
									JSON.stringify( qty_options )
								);

								/* Item selector */
								const var_selection = qty_wrap.find(
									'.wcf-item-selector'
								);

								if ( var_selection.length > 0 ) {
									const var_options = qty_wrap.data(
										'options'
									);

									var_options.variation_id =
										result.variation_id;

									qty_wrap.attr(
										'data-options',
										JSON.stringify( var_options )
									);
								}

								/* Set display data */
								qty_wrap
									.find( '.wcf-display-quantity' )
									.html( result.display_quantity );
								qty_wrap
									.find( '.wcf-display-price' )
									.html( result.display_price );
								qty_wrap
									.find( '.wcf-display-discount-value' )
									.html( result.display_discount_value );
								qty_wrap
									.find( '.wcf-display-discount-percent' )
									.html( result.display_discount_percent );
								item_wrap
									.find( '.wcf_subscription_price' )
									.html( result.display_subscription_price );
								item_wrap
									.find( '.wcf_subscription_period' )
									.html(
										result.display_subscription_details
									);
								item_wrap
									.find( '.wcf_subscription_fee' )
									.html( result.display_signup_fee );
								item_wrap
									.find( '.wcf_subscription_free_trial' )
									.html( result.trial_period_string );
							}

							// Trigger event so themes can refresh other areas.
							$( document.body ).trigger( 'wc_fragment_refresh' );

							modal_wrap.trigger( 'wcf_added_to_cart', [
								$thisbutton,
							] );
						},
					} );
				} /*else {
					jQuery.ajax ({
						url: cartflows.ajax_url,
						type:'POST',
						data:'action=wcf_add_cart_single_product&product_id=' + product_id + '&quantity=' + quantity,

						success:function(results) {
							// Trigger event so themes can refresh other areas.
							$( document.body ).trigger( 'wc_fragment_refresh' );
							//modal_wrap.trigger( 'wcf_added_to_cart', [ $thisbutton ] );

							$( "body" ).trigger( "update_checkout" );

							wcf_qv_close_btn.trigger( 'click' );
						}
					});
				}*/
			}
		};

		/**
		 * Update cart page elements after add to cart events.
		 *
		 */
		wcf_add_to_cart_ajax.prototype.updateButton = function () {
			$( 'body' ).trigger( 'update_checkout' );

			wcf_qv_close_btn.trigger( 'click' );
		};

		/**
		 * Init wcf_add_to_cart_ajax.
		 */
		new wcf_add_to_cart_ajax();
	};

	const wcf_two_step_validations = function () {
		const $billing_inputs = $(
			'.wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-billing-fields, .wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-account-fields'
		).find(
			'input[type="text"], input[type="tel"], input[type="email"], input[type="password"]'
		);

		const $billing_chekboxes = $(
			'.wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-billing-fields, .wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-account-fields'
		).find( 'input[type="checkbox"]' );

		const $billing_select = $(
			'.wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-billing-fields'
		).find( '.select2' );

		const $shipping_inputs = $(
			'.wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-shipping-fields'
		).find(
			'input[type="text"], input[type="tel"], input[type="email"], input[type="password"]'
		);

		const $shipping_chekboxes = $(
			'.wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-shipping-fields .woocommerce-shipping-fields__field-wrapper'
		).find( 'input[type="checkbox"]' );

		const $shipping_select = $(
			'.wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-shipping-fields'
		).find( '.select2' );

		const is_ship_to_diff = $(
			'.wcf-embed-checkout-form-two-step form.woocommerce-checkout'
		)
			.find(
				'h3#ship-to-different-address input[type="checkbox"]:checked'
			)
			.val();

		//Add focus class on clicked on input types
		let access = true,
			field_focus = '';

		Array.from( $billing_inputs ).forEach( function ( $this ) {
			const type = $this.type,
				name = $this.name,
				field_row = $this.closest( '.form-row' ),
				field_value = $.trim( $this.value );

			let has_class = field_row.classList.contains( 'validate-required' );
			// whiteSpace  = /\s/g.test(field_value);

			if ( name === 'account_password' || name === 'account_username' ) {
				const create_acc_checkbox = document.getElementById(
					'createaccount'
				);

				if ( create_acc_checkbox ) {
					if ( $( create_acc_checkbox ).is( ':checked' ) ) {
						has_class = true;
					} else {
						has_class = false;
					}
				} else {
					has_class = true;
				}
			}

			if ( has_class && '' === field_value ) {
				$this.classList.add( 'field-required' );
				access = false;
				if ( '' === field_focus ) {
					field_focus = $this;
				}
			} else {
				if (
					'email' === type &&
					false ===
						/^([a-zA-Z0-9_\+\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,14})$/.test(
							field_value
						)
				) {
					$this.classList.add( 'field-required' );
					access = false;

					if ( '' === field_focus ) {
						field_focus = $this;
					}
				}

				$this.classList.remove( 'field-required' );
			}
		} );

		Array.from( $billing_chekboxes ).forEach( function ( $this ) {
			const field_row = $this.closest( '.form-row' ),
				has_class = field_row.classList.contains( 'validate-required' );
			let field_value = false;

			if ( $( $this ).is( ':checked' ) ) {
				field_value = true;
			}

			if ( has_class && false === field_value ) {
				$this.classList.add( 'field-required' );
				access = false;
				if ( '' === field_focus ) {
					field_focus = $this;
				}
			} else {
				$this.classList.remove( 'field-required' );
			}
		} );

		Array.from( $billing_select ).forEach( function ( $this ) {
			const field_row = $this.closest( '.form-row' ),
				has_class = field_row.classList.contains( 'validate-required' ),
				field_value = $.trim(
					field_row.querySelector(
						'.select2-selection__rendered[title]'
					)
				);
			//Need to update naming convention.
			name = field_row.querySelector( 'select' ).name; //eslint-disable-line

			if ( has_class && '' === field_value ) {
				$this.classList.add( 'field-required' );
				access = false;
				if ( '' === field_focus ) {
					field_focus = $this;
				}
			} else {
				$this.classList.remove( 'field-required' );
			}
		} );

		if ( '1' === is_ship_to_diff ) {
			Array.from( $shipping_inputs ).forEach( function ( $this ) {
				const type = $this.type,
					field_row = $this.closest( '.form-row' ),
					has_class = field_row.classList.contains(
						'validate-required'
					),
					field_value = $.trim( $this.value );

				if ( has_class && '' === field_value ) {
					$this.classList.add( 'field-required' );
					access = false;

					if ( '' === field_focus ) {
						field_focus = $this;
					}
				} else {
					if (
						'email' === type &&
						false ===
							/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(
								field_value
							)
					) {
						$this.classList.add( 'field-required' );
						access = false;

						if ( '' === field_focus ) {
							field_focus = $this;
						}
					}

					$this.classList.remove( 'field-required' );
				}
			} );

			Array.from( $shipping_select ).forEach( function ( $this ) {
				const field_row = $this.closest( '.form-row' ),
					has_class = field_row.classList.contains(
						'validate-required'
					),
					field_value = $.trim(
						field_row.querySelector(
							'.select2-selection__rendered[title]'
						)
					);
				//Need to update naming convention.
				name = field_row.querySelector( 'select' ).name; //eslint-disable-line

				if ( has_class && '' === field_value ) {
					$this.classList.add( 'field-required' );
					access = false;

					if ( '' === field_focus ) {
						field_focus = $this;
					}
				} else {
					$this.classList.remove( 'field-required' );
				}
			} );

			Array.from( $shipping_chekboxes ).forEach( function ( $this ) {
				const field_row = $this.closest( '.form-row' ),
					has_class = field_row.classList.contains(
						'validate-required'
					);
				let field_value = false;

				if ( $( $this ).is( ':checked' ) ) {
					field_value = true;
				}

				if ( has_class && false === field_value ) {
					$this.classList.add( 'field-required' );
					access = false;
					if ( '' === field_focus ) {
						field_focus = $this;
					}
				} else {
					$this.classList.remove( 'field-required' );
				}
			} );
		}

		// Focus the errored field
		if ( '' !== field_focus ) {
			field_focus.focus();
		}

		return access;
	};

	/*$(".wcf-qty [type='number']").on('keyup mouseup', function (event) {
		var input = jQuery(this).val();
		var input2 = input+jQuery(this).text();
		var step = jQuery(this).attr("step");
		var min = jQuery(this).attr("min");

		if(!Number.isNaN(input2)){
			if(parseInt(input2) > min){

				var remainder = input2%step;
				if(remainder !== 0){
					var new_value = parseInt(input2)  -  remainder;
					jQuery(this).val(new_value);

				}
			}
		}
	}).trigger('mouseup');

	jQuery(".wcf-qty [type='number']").on('focusout',function (evt) {

		// var input = String.fromCharCode(evt.which);

		var input = jQuery(this).val();

		// var input_text = jQuery(this).text();
		// if ((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105)) {

		var min = jQuery(this).attr("min");

		if(!Number.isNaN(input)){
			if(parseInt(input) < min){
				jQuery(this).val(min);
			}

		}
	});*/

	const wcf_order_bump_buttons = function () {
		$( document ).on( 'click', '.wcf-bump-order-cb-button', function () {
			const $this = $( this ),
				parent_class = $this.closest( '.wcf-bump-order-action' ),
				input = parent_class.find( 'input[type="checkbox"]' );

			if ( $this.hasClass( 'wcf-bump-add-to-cart' ) ) {
				input.attr( 'checked', true );
				$this.text( $this.data( 'adding' ) );
			} else {
				input.attr( 'checked', false );

				$this.text( $this.data( 'removing' ) );
			}

			parent_class.find( '.wcf-bump-order-cb' ).trigger( 'change' );
		} );
	};

	const quantity_changer = function () {
		$( '.wcf-qty-selection-btn' ).click( function ( e ) {
			e.preventDefault();

			const quantity_input = $( this )
					.parents( '.wcf-qty' )
					.find( '.wcf-qty-selection' ),
				is_sold_individually = Boolean(
					quantity_input.data( 'sale-limit' )
				);

			// Return if the product is set to sold 1 per order.
			if ( is_sold_individually ) {
				return false;
			}

			const val = parseInt( quantity_input.val(), 10 ),
				min_value = quantity_input.attr( 'min' );

			if ( $( e.target ).hasClass( 'wcf-qty-increment' ) ) {
				quantity_input.val( val + 1 );
			} else {
				quantity_input.val( val <= min_value ? min_value : val - 1 );
			}
			$( '.wcf-qty-selection' ).trigger( 'change' );
		} );
	};

	const ob_quantity_changer = function () {
		let wcf_order_bump_qty_update = false;

		$( document ).on( 'click', '.wcf-ob-qty-selection-btn', function ( e ) {
			e.preventDefault();

			if ( true === wcf_order_bump_qty_update ) {
				return false;
			}

			wcf_order_bump_qty_update = true;

			const $this = $( this ),
				quantity_input = $this
					.parents( '.wcf-ob-qty-selection-wrap' )
					.find( '.wcf-order-bump-quantity-updater' ),
				min_value = quantity_input.attr( 'min' );
			let updated_qty = '';

			const val = parseInt( quantity_input.val(), 10 );

			if ( $( e.target ).hasClass( 'wcf-ob-qty-increment' ) ) {
				updated_qty = val + 1;
				quantity_input.val( updated_qty );
			} else {
				updated_qty = val <= min_value ? min_value : val - 1;
				quantity_input.val( updated_qty );
			}

			// Make a ajax call to update the quantity in the cart.
			update_ob_quantity( $this, updated_qty );

			wcf_order_bump_qty_update = false;

			$( '.wcf-order-bump-quantity-updater' ).trigger( 'change' );
		} );
	};

	const update_ob_quantity = function ( $this, updated_qty ) {
		const bump_offer_wrap = $this.closest( '.wcf-bump-order-wrap' ),
			checkbox_field = bump_offer_wrap.find( '#wcf-bump-order-cb' ),
			bump_offer_data = checkbox_field.attr( 'data-ob_data' ),
			ob_data = checkbox_field.data( 'ob_data' ),
			product_id = ob_data.product_id;

		const data = {
			_wcf_product_id: product_id,
			_bump_offer_data: bump_offer_data ? bump_offer_data : '',
			_bump_offer_qty: updated_qty > 0 ? updated_qty : '',
			security: cartflows.wcf_update_order_bump_qty_nonce,
			action: 'wcf_update_order_bump_qty',
		};

		if ( false === checkbox_field.is( ':checked' ) ) {
			return;
		}
		data._wcf_bump_product_action = 'add_bump_product';

		// Display spinner for specific order bump.
		bump_offer_wrap.block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6,
			},
		} );

		wcf_display_spinner();

		$.ajax( {
			url: cartflows.ajax_url,
			data,
			dataType: 'json',
			type: 'POST',
			success( response ) {
				// Re-calculate the cart total
				wcf_re_calculate_totals();
				wcf_remove_spinner( response );
			},
			error() {
				$( '.woocommerce-checkout-review-order-table' ).unblock();
			},
		} );

		return false;
	};

	const multistep_checkout_buttons_navigation = function () {
		let previous_step = 'billing',
			current_step = 'billing';

		// Next button.
		$( document ).on(
			'click',
			'.wcf-multistep-nav-next-btn',
			function wcf_show_next_step( e ) {
				e.preventDefault();

				const next_step_element = $( '.wcf-multistep-nav-next-btn' ),
					next_step_element_target = next_step_element.attr(
						'data-target'
					);
				if ( current_step !== next_step_element_target ) {
					if ( wcf_multistep_field_validations() ) {
						remove_current_step_class();

						previous_step = current_step;
						current_step = next_step_element_target;

						if ( 'shipping' === next_step_element_target ) {
							show_shipping_step();
						} else if ( 'payment' === next_step_element_target ) {
							show_payment_step();
						}

						scrollToTop();
					}
				}
			}
		);

		// Back button.
		$( document ).on(
			'click',
			'.wcf-multistep-nav-back-btn',
			function wcf_show_previous_step( e ) {
				e.preventDefault();
				const previous_step_element = $(
					'.wcf-multistep-nav-back-btn'
				);
				previous_step_element_target = previous_step_element.attr(
					'data-target'
				);

				if ( current_step !== previous_step_element_target ) {
					if ( wcf_multistep_field_validations() ) {
						remove_current_step_class();
						previous_step = current_step;
						current_step = previous_step_element_target;

						if ( '' === previous_step_element_target ) {
							previous_step_element.css( 'visibility', 'hidden' );
						} else if (
							'shipping' === previous_step_element_target
						) {
							show_shipping_step();
						} else if (
							'billing' === previous_step_element_target
						) {
							show_billing_step();
						}
						scrollToTop();
					}
				}
			}
		);

		// Breadcrumbs.
		$( document ).on(
			'click',
			'.wcf-checkout-breadcrumb a',
			function wcf_show_previous_step( e ) {
				e.preventDefault();

				const breadcrum_tab = $( e.target ).attr( 'data-tab' );

				if ( current_step !== breadcrum_tab ) {
					if ( wcf_multistep_field_validations() ) {
						previous_step = current_step;
						current_step = breadcrum_tab;

						remove_current_step_class();

						if ( 'billing' === breadcrum_tab ) {
							show_billing_step();
						} else if ( 'shipping' === breadcrum_tab ) {
							show_shipping_step();
						} else if ( 'payment' === breadcrum_tab ) {
							show_payment_step();
						}

						scrollToTop();
					}
				}
			}
		);

		// Custom sections.
		$( document ).on(
			'click',
			'.wcf-step-link',
			function wcf_show_previous_step( e ) {
				e.preventDefault();

				const tab_target = $( e.target ).attr( 'data-target' );

				if ( current_step !== tab_target ) {
					if ( wcf_multistep_field_validations() ) {
						previous_step = current_step;
						current_step = tab_target;

						remove_current_step_class();

						if ( 'billing' === tab_target ) {
							show_billing_step();
						} else if ( 'shipping' === tab_target ) {
							show_shipping_step();
						}
					}
				}
			}
		);

		function show_billing_step() {
			const next_step_element = $( '.wcf-multistep-nav-next-btn' ),
				previous_step_element = $( '.wcf-multistep-nav-back-btn' );
			if ( 'shipping' === previous_step ) {
				$( '.wcf-embed-checkout-form' ).removeClass( 'wcf-shipping' );
			}

			if ( 'payment' === previous_step ) {
				$( '.wcf-embed-checkout-form' ).removeClass( 'wcf-payment' );
			}

			$( '.wcf-embed-checkout-form' ).addClass( 'wcf-billing' );

			if ( cartflows.is_hide_shipping_tab ) {
				next_step_element.attr( 'data-target', 'payment' );
			} else {
				next_step_element.attr( 'data-target', 'shipping' );
			}

			previous_step_element.attr( 'data-target', '' );

			$( '.wcf-checkout-breadcrumb.information-step a' ).addClass(
				'wcf-current-step'
			);

			next_step_element.text(
				cartflows.multistep_buttons_strings.billing
			);
		}

		function show_shipping_step() {
			const next_step_element = $( '.wcf-multistep-nav-next-btn' ),
				previous_step_element = $( '.wcf-multistep-nav-back-btn' );
			if ( 'billing' === previous_step ) {
				$( '.wcf-embed-checkout-form' ).removeClass( 'wcf-billing' );
			}

			if ( 'payment' === previous_step ) {
				$( '.wcf-embed-checkout-form' ).removeClass( 'wcf-payment' );
			}

			$( '.wcf-embed-checkout-form' ).addClass( 'wcf-shipping' );

			next_step_element.attr( 'data-target', 'payment' );
			previous_step_element.attr( 'data-target', 'billing' );
			$( '.wcf-checkout-breadcrumb.shipping-step a' ).addClass(
				'wcf-current-step'
			);

			updateEmailValue();

			next_step_element.text(
				cartflows.multistep_buttons_strings.shipping
			);
		}

		function show_payment_step() {
			const previous_step_element = $( '.wcf-multistep-nav-back-btn' );
			if ( 'shipping' === previous_step ) {
				$( '.wcf-embed-checkout-form' ).removeClass( 'wcf-shipping' );
			}

			if ( 'billing' === previous_step ) {
				$( '.wcf-embed-checkout-form' ).removeClass( 'wcf-billing' );
			}

			if ( cartflows.is_hide_shipping_tab ) {
				previous_step_element.attr( 'data-target', 'billing' );
			} else {
				previous_step_element.attr( 'data-target', 'shipping' );
			}

			$( '.wcf-embed-checkout-form' ).addClass( 'wcf-payment' );
			$( '.wcf-checkout-breadcrumb.payment-step a' ).addClass(
				'wcf-current-step'
			);

			updateEmailValue();
		}
		function updateEmailValue() {
			// Update email if it is changed.
			$( '.wcf-review-detail-content.contact-details' ).text(
				$( '.wcf-embed-checkout-form form.woocommerce-checkout' )
					.find( 'input[type="email"]' )
					.val()
			);
		}

		function scrollToTop(
			scroll_to = 'wcf-embed-checkout-form',
			delay = 1000
		) {
			$( 'html, body' ).animate(
				{
					scrollTop: $( '#' + scroll_to ).offset().top,
				},
				delay
			);
		}
		function remove_current_step_class() {
			if ( $( '.wcf-current-step' ).length ) {
				$( '.wcf-current-step' ).removeClass( 'wcf-current-step' );
			}
		}

		const wcf_multistep_field_validations = function () {
			//Add focus class on clicked on input types
			let access = true,
				field_focus = '';
			if ( 'billing' === current_step ) {
				const $billing_inputs = $(
					'.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep form.woocommerce-checkout .woocommerce-billing-fields-custom, .wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep form.woocommerce-checkout .woocommerce-billing-fields, .wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep form.woocommerce-checkout .woocommerce-account-fields'
				).find(
					'input[type="text"], input[type="tel"], input[type="email"], input[type="password"]'
				);

				const $billing_chekboxes = $(
					'.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep form.woocommerce-checkout .woocommerce-billing-fields, .wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep form.woocommerce-checkout .woocommerce-account-fields'
				).find( 'input[type="checkbox"]' );

				const $billing_select = $(
					'.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep form.woocommerce-checkout .woocommerce-billing-fields'
				).find( '.select2' );

				Array.from( $billing_inputs ).forEach( function ( $this ) {
					const type = $this.type,
						name = $this.name,
						field_row = $this.closest( '.form-row' ),
						field_value = $.trim( $this.value );

					let has_class = field_row.classList.contains(
						'validate-required'
					);
					// whiteSpace  = /\s/g.test(field_value);
					if ( name === 'billing_password' ) {
						if (
							$( '.wcf-email-validation-block' ).hasClass(
								'success'
							) &&
							! cartflows.is_guest_checkout
						) {
							has_class = true;
						} else {
							has_class = false;
						}
					}

					if (
						name === 'account_password' ||
						name === 'account_username'
					) {
						const create_acc_checkbox = document.getElementById(
							'createaccount'
						);

						if ( create_acc_checkbox ) {
							if ( $( create_acc_checkbox ).is( ':checked' ) ) {
								has_class = true;
							} else {
								has_class = false;
							}
						} else {
							has_class = true;
						}
					}

					if ( has_class && '' === field_value ) {
						$this.classList.add( 'field-required' );
						access = false;
						if ( '' === field_focus ) {
							field_focus = $this;
						}
					} else {
						if (
							'email' === type &&
							false ===
								/^([a-zA-Z0-9_\+\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,14})$/.test(
									field_value
								)
						) {
							$this.classList.add( 'field-required' );
							access = false;

							if ( '' === field_focus ) {
								field_focus = $this;
							}
						}

						$this.classList.remove( 'field-required' );
					}
				} );

				Array.from( $billing_chekboxes ).forEach( function ( $this ) {
					const field_row = $this.closest( '.form-row' ),
						has_class = field_row.classList.contains(
							'validate-required'
						);
					let field_value = false;

					if ( $( $this ).is( ':checked' ) ) {
						field_value = true;
					}

					if ( has_class && false === field_value ) {
						$this.classList.add( 'field-required' );
						access = false;
						if ( '' === field_focus ) {
							field_focus = $this;
						}
					} else {
						$this.classList.remove( 'field-required' );
					}
				} );

				Array.from( $billing_select ).forEach( function ( $this ) {
					const field_row = $this.closest( '.form-row' ),
						has_class = field_row.classList.contains(
							'validate-required'
						),
						field_value = $.trim(
							field_row.querySelector(
								'.select2-selection__rendered[title]'
							)
						);
					//Need to update naming convention.
					name = field_row.querySelector( 'select' ).name; //eslint-disable-line

					if ( has_class && '' === field_value ) {
						$this.classList.add( 'field-required' );
						access = false;
						if ( '' === field_focus ) {
							field_focus = $this;
						}
					} else {
						$this.classList.remove( 'field-required' );
					}
				} );
			}

			if ( 'shipping' === current_step ) {
				const $shipping_inputs = $(
					'.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep form.woocommerce-checkout .woocommerce-shipping-fields'
				).find(
					'input[type="text"], input[type="tel"], input[type="email"], input[type="password"]'
				);

				const $shipping_chekboxes = $(
					'.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep form.woocommerce-checkout .woocommerce-shipping-fields .woocommerce-shipping-fields__field-wrapper'
				).find( 'input[type="checkbox"]' );

				const $shipping_select = $(
					'.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep form.woocommerce-checkout .woocommerce-shipping-fields'
				).find( '.select2' );

				const is_ship_to_diff = $(
					'.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep form.woocommerce-checkout'
				)
					.find(
						'h3#ship-to-different-address input[type="checkbox"]:checked'
					)
					.val();

				if ( '1' === is_ship_to_diff ) {
					Array.from( $shipping_inputs ).forEach( function ( $this ) {
						const type = $this.type,
							field_row = $this.closest( '.form-row' ),
							has_class = field_row.classList.contains(
								'validate-required'
							),
							field_value = $.trim( $this.value );

						if ( has_class && '' === field_value ) {
							$this.classList.add( 'field-required' );
							access = false;

							if ( '' === field_focus ) {
								field_focus = $this;
							}
						} else {
							if (
								'email' === type &&
								false ===
									/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(
										field_value
									)
							) {
								$this.classList.add( 'field-required' );
								access = false;

								if ( '' === field_focus ) {
									field_focus = $this;
								}
							}

							$this.classList.remove( 'field-required' );
						}
					} );

					Array.from( $shipping_select ).forEach( function ( $this ) {
						const field_row = $this.closest( '.form-row' ),
							has_class = field_row.classList.contains(
								'validate-required'
							),
							field_value = $.trim(
								field_row.querySelector(
									'.select2-selection__rendered[title]'
								)
							);
						//Need to update naming convention.
						name = field_row.querySelector( 'select' ).name; //eslint-disable-line

						if ( has_class && '' === field_value ) {
							$this.classList.add( 'field-required' );
							access = false;

							if ( '' === field_focus ) {
								field_focus = $this;
							}
						} else {
							$this.classList.remove( 'field-required' );
						}
					} );

					Array.from( $shipping_chekboxes ).forEach( function (
						$this
					) {
						const field_row = $this.closest( '.form-row' ),
							has_class = field_row.classList.contains(
								'validate-required'
							);
						let field_value = false;

						if ( $( $this ).is( ':checked' ) ) {
							field_value = true;
						}

						if ( has_class && false === field_value ) {
							$this.classList.add( 'field-required' );
							access = false;
							if ( '' === field_focus ) {
								field_focus = $this;
							}
						} else {
							$this.classList.remove( 'field-required' );
						}
					} );
				}
			}

			// Focus the errored field
			if ( '' !== field_focus ) {
				field_focus.focus();
			}

			return access;
		};
	};

	$( function () {
		wcf_remove_product();

		wcf_animate_browser_tab();

		if ( 'yes' === cartflows.allow_autocomplete_zipcode ) {
			wcf_autocomplete_zip_data();
		}

		wcf_product_quantity_var_options();
		wcf_do_not_remove_single_product();
		wcf_order_bump_ajax();

		wcf_order_bump_buttons();

		if ( $( '.wcf-embed-checkout-form-two-step' ).length > 0 ) {
			wcf_nav_tab_hide_show_events();
		}

		//In multi checkout case we need to update the cart item key of the data-option for the product options.
		wcf_update_product_options_cart_item_key();
		quantity_changer();

		ob_quantity_changer();

		multistep_checkout_buttons_navigation();
	} );
} )( jQuery );
