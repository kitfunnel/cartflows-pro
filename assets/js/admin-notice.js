( function ( $ ) {
	const CartFlowsProAdminNotice = {
		/**
		 * Init
		 */
		init() {
			this._bind();
		},

		/**
		 * Binds events
		 */
		_bind() {
			$( document ).on(
				'click',
				'.cartflows-dismissible-notice .notice-dismiss',
				CartFlowsProAdminNotice.disable_license_admin_notice
			);
		},

		/**
		 * Import
		 *
		 * @param {Object} event event data.
		 */
		disable_license_admin_notice( event ) {
			event.preventDefault();
			$.ajax( {
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'cartflows_disable_activate_license_notice',
					security: CartFlowsProAdminNoticeVars._nonce,
				},
			} )
				.done( function () {} )
				.fail( function () {} )
				.always( function () {} );
		},
	};

	/**
	 * Initialization
	 */
	$( function () {
		CartFlowsProAdminNotice.init();
	} );
} )( jQuery );
