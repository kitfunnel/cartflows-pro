( function ( $ ) {
	const CartFlowsProLicense = {
		/**
		 * Init
		 */
		init() {
			this._check_popup();
			this._bind();
		},

		_check_popup() {
			const self = CartFlowsProLicense;
			const open_popup =
				self._getUrlParameter( 'cartflows-license-popup' ) || '';
			if (
				open_popup &&
				'Deactivated' === CartFlowsProLicenseVars.activation_status
			) {
				self._open_popup();
			}
		},

		/**
		 * Binds events
		 */
		_bind() {
			// $( document ).on(
			// 	'click',
			// 	'.cartflows-license-popup-open-button',
			// 	CartFlowsProLicense._export_button_click
			// );
			$( document ).on(
				'click',
				'.cartflows-close-popup-button',
				CartFlowsProLicense._close_popup
			);
			$( document ).on(
				'click',
				'#cartflows-license-popup-overlay',
				CartFlowsProLicense._close_popup
			);
			$( document ).on(
				'click',
				'.cartflows-activate-license',
				CartFlowsProLicense._activate_license
			);
			$( document ).on(
				'click',
				'.cartflows-deactivate-license',
				CartFlowsProLicense._deactivate_license
			);
		},

		/**
		 * Debugging.
		 *
		 * @param {Object} data Mixed data.
		 */
		_log( data ) {
			const date = new Date();
			const time = date.toLocaleTimeString();

			if ( typeof data === 'object' ) {
				console.log(
					'%c ' + JSON.stringify( data ) + ' ' + time,
					'background: #ededed; color: #444'
				);
			} else {
				console.log(
					'%c ' + data + ' ' + time,
					'background: #ededed; color: #444'
				);
			}
		},

		_export_button_click( e ) {
			e.preventDefault();
			CartFlowsProLicense._open_popup();
		},

		_open_popup() {
			const popup = $(
					'#cartflows-license-popup-overlay, #cartflows-license-popup'
				),
				license_key =
					$( '#cartflows-license-popup' ).attr(
						'data-license-key'
					) || '',
				contents = popup.find( '.contents' );

			console.log( license_key );

			// Add validate license window.
			if ( 'Activated' === license_key ) {
				contents.html( wp.template( 'cartflows-deactivate-license' ) );
			} else {
				contents.html( wp.template( 'cartflows-activate-license' ) );
			}

			popup.show();
		},

		_close_popup() {
			const popup = $(
				'#cartflows-license-popup-overlay, #cartflows-license-popup'
			);

			if ( popup.hasClass( 'validating' ) ) {
				// Proceed?
				if (
					! confirm(
						'WARNING! License request not complete!!\n\nPlease wait for a moment until complete the license request.'
					)
				) {
					return;
				}
			}

			popup.hide();
		},

		/**
		 * Import
		 *
		 * @param {Object} event event data.
		 */
		_activate_license( event ) {
			event.preventDefault();

			const parent = $( '#cartflows-license-popup' ),
				license_key = parent.find( '.license_key' ).val() || '';

			if ( ! license_key.length ) {
				return;
			}

			const btn = $( this );

			if ( btn.hasClass( 'disabled' ) || btn.hasClass( 'validating' ) ) {
				return;
			}

			parent.addClass( 'validating' );
			btn.find( '.text' ).text( 'Validating..' );
			const contents = parent.find( '.contents' );

			if ( contents.find( '.notice' ).length ) {
				contents.find( '.notice' ).remove();
			}

			btn.find( '.cartflows-processing' ).addClass( 'is-active' );
			const license_nonce = parent.find( '.license_nonce' ).val() || '';

			$.ajax( {
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'cartflows_activate_license',
					license_key,
					security: license_nonce,
				},
			} )
				.done( function ( data ) {
					parent.removeClass( 'validating' );

					btn.find( '.cartflows-processing' ).removeClass(
						'is-active'
					);

					if ( data.success ) {
						const license_btn = $(
							'.cartflows-license-popup-open-button'
						);
						license_btn
							.removeClass( 'active' )
							.addClass( 'inactive' )
							.text( 'Deactivate License' );
						btn.find( '.text' ).text(
							'Successfully Activated! Reloading..'
						);
						parent.attr( 'data-license-key', license_key );

						setTimeout( function () {
							// CartFlowsProLicense._close_popup();
							location.reload();
						}, 2500 );

						parent
							.find( 'input' )
							.addClass( 'disabled' )
							.attr( 'readonly', 'readonly' );

						// var msg = data.data.message || data.data;
						// if( msg ) {
						// 	contents.append( '<div class="notice notice-success"><p>' + msg + '</p></div>' );
						// }
					} else {
						const msg = data.data.error || data.data || '';
						if ( msg ) {
							contents.append(
								'<div class="notice notice-error"><p>' +
									msg +
									'</p></div>'
							);
						}

						btn.find( '.text' ).text( 'Failed!' );
					}

					// tb_remove();
				} )
				.fail( function () {} )
				.always( function () {} );
		},

		/**
		 * Import
		 *
		 * @param {Object} event event data.
		 */
		_deactivate_license( event ) {
			event.preventDefault();

			const self = $( this );
			const license_btn = $( '.cartflows-license-popup-open-button' );
			const parent = $( '#cartflows-license-popup' );
			const contents = parent.find( '.contents' );
			const deactivate_license_nonce =
				parent.find( '.deactivate_license_nonce' ).val() || '';

			parent.addClass( 'validating' );
			self.find( '.text' ).text( 'Deactivating..' );

			if ( contents.find( '.notice' ).length ) {
				contents.find( '.notice' ).remove();
			}

			self.find( '.cartflows-processing' ).addClass( 'is-active' );

			$.ajax( {
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'cartflows_deactivate_license',
					security: deactivate_license_nonce,
				},
			} )
				.done( function ( data ) {
					parent.removeClass( 'validating' );

					self.find( '.cartflows-processing' ).removeClass(
						'is-active'
					);

					if ( data.success ) {
						license_btn
							.removeClass( 'inactive' )
							.addClass( 'active' )
							.text( 'Activate License' );

						self.find( '.text' ).text(
							'Successfully Deactivated! Reloading..'
						);
						parent.attr( 'data-license-key', '' );

						setTimeout( function () {
							location.reload();
							// CartFlowsProLicense._close_popup();
						}, 2500 );
					} else {
						const msg =
							data.data.message ||
							data.data ||
							data.response ||
							'';
						if ( msg ) {
							contents.append(
								'<div class="notice notice-error"><p>' +
									msg +
									'</p></div>'
							);
						}

						self.find( '.text' ).text( 'Failed!' );
					}

					// tb_remove();
				} )
				.fail( function () {} )
				.always( function () {} );
		},

		_getUrlParameter( param ) {
			const page_url = decodeURIComponent(
					window.location.search.substring( 1 )
				),
				url_variables = page_url.split( '&' );
			let parameter_name, i;

			for ( i = 0; i < url_variables.length; i++ ) {
				parameter_name = url_variables[ i ].split( '=' );

				if ( parameter_name[ 0 ] === param ) {
					return parameter_name[ 1 ] === undefined
						? true
						: parameter_name[ 1 ];
				}
			}
		},
	};

	/**
	 * Initialization
	 */
	$( function () {
		CartFlowsProLicense.init();
	} );
} )( jQuery );
