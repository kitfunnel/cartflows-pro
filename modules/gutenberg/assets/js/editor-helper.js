( function ( $ ) {
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

			previous_step_element.attr( 'data-target', '' );
			next_step_element.attr( 'data-target', 'shipping' );

			$( '.wcf-checkout-breadcrumb.information-step a' ).addClass(
				'wcf-current-step'
			);

			next_step_element.text(
				cfp_blocks_info.multistep_buttons_strings.billing
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

			next_step_element.text(
				cfp_blocks_info.multistep_buttons_strings.shipping
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

			previous_step_element.attr( 'data-target', 'shipping' );
			$( '.wcf-embed-checkout-form' ).addClass( 'wcf-payment' );
			$( '.wcf-checkout-breadcrumb.payment-step a' ).addClass(
				'wcf-current-step'
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
					'.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep form.woocommerce-checkout .woocommerce-billing-fields, .wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep form.woocommerce-checkout .woocommerce-account-fields'
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
		multistep_checkout_buttons_navigation();
	} );
} )( jQuery );
