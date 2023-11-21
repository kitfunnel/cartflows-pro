( function ( $ ) {
	const migrate_to_new_order_bump = function () {
		$( 'a.migrate-to-new-ob' ).on( 'click', function ( e ) {
			e.preventDefault();

			const content = $( this ).closest( '.wcf-notice-content' ),
				text = CartFlows_Pro_Common_Vars.ob_notice_text;

			const data = {
				action: 'cartflows_migrate_order_bump',
				security: CartFlows_Pro_Common_Vars.ob_migration_nonce,
			};

			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data,

				success( response ) {
					if ( response.success ) {
						console.log(
							'Action scheduled for order bump migration.'
						);
						content.html( text );
					}
				},
			} );
		} );
	};

	const migrate_pre_checkout_offer_styles = function () {
		$( 'a.migrate-to-new-styling-option' ).on( 'click', function ( e ) {
			e.preventDefault();
			const content = $( this ).closest( '.wcf-notice-content' ),
				text =
					CartFlows_Pro_Common_Vars.pre_checkout_offer_migration_notice;

			const data = {
				action: 'cartflows_migrate_pre_checkout_offer_styles',
				security:
					CartFlows_Pro_Common_Vars.pre_checkout_offer_migration_nonce,
			};

			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data,

				success( response ) {
					if ( response.success ) {
						console.log(
							'Action scheduled for pre checkout offer styles migration.'
						);
						content.html( text );
					}
				},
			} );
		} );
	};

	const dismiss_flow_analytics_notice = function () {
		$( document ).on(
			'click',
			'.flow-analytics-notice.wcf-dismissible-notice button.notice-dismiss',
			function ( e ) {
				e.preventDefault();

				const data = {
					action: 'cartflows_dismiss_flow_analytics_notice',
					security:
						CartFlows_Pro_Common_Vars.flow_analytics_dismiss_notice_nonce,
				};

				$.ajax( {
					type: 'POST',
					url: ajaxurl,
					data,

					success( response ) {
						if ( response.success ) {
							console.log( 'Funnel Analytics Notice Ignored.' );
						}
					},
				} );
			}
		);
	};

	$( function () {
		migrate_to_new_order_bump();
		migrate_pre_checkout_offer_styles();
		dismiss_flow_analytics_notice();
	} );
} )( jQuery );
