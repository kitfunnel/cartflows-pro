( function ( $ ) {
	/**
	 * Initialize selectWoo
	 */
	const wcf_initialize_selectwoo = function () {
		const select_fields = jQuery( '.form-row select' );

		$( select_fields ).each( function () {
			$( this ).selectWoo();
		} );
	};

	$( function () {
		wcf_initialize_selectwoo();
	} );
} )( jQuery );
