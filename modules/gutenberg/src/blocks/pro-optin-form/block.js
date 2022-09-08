const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor;

const OptinProFilterBlocks = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const {
			attributes: { input_skins },
		} = props;

		const input_field = document.getElementById( 'wcf-optin-form' );

		if ( input_field !== null ) {
			if ( input_skins === 'floating-labels' ) {
				input_field.classList.remove( 'wcf-field-default' );
				input_field.classList.add( 'wcf-field-floating-labels' );
			} else {
				input_field.classList.remove( 'wcf-field-floating-labels' );
				input_field.classList.add( 'wcf-field-default' );
			}
		}

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls></InspectorControls>
			</Fragment>
		);
	};
}, 'OptinProFilterBlocks' );

if ( 'optin' === cfp_blocks_info.step_type && cfp_blocks_info.is_woo_active ) {
	addFilter(
		'editor.BlockEdit',
		'optin/cfp-optin-filter-blocks',
		OptinProFilterBlocks
	);
}
