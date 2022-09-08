/**
 * BLOCK: Offer Yes No Link
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

import CFP_Block_Icons from '../../../dist/blocks/controls/block-icons';
import attributes from './attributes';
import edit from './edit';
import save from './save';
import './style.scss';
import './editor.scss';

const { __ } = wp.i18n;

const { registerBlockType } = wp.blocks;

if (
	'upsell' === cfp_blocks_info.step_type ||
	( 'downsell' === cfp_blocks_info.step_type &&
		cfp_blocks_info.is_woo_active )
) {
	registerBlockType( 'wcfpb/offer-yes-no-link', {
		title: cfp_blocks_info.blocks[ 'wcfpb/offer-yes-no-link' ].title,
		description:
			cfp_blocks_info.blocks[ 'wcfpb/offer-yes-no-link' ].description,
		icon: CFP_Block_Icons.yes_no_links,
		category: cfp_blocks_info.category,
		keywords: [
			__( 'cartflows pro', 'cartflows-pro' ),
			__( 'Offer yes no link', 'cartflows-pro' ),
			__( 'cfp', 'cartflows-pro' ),
		],
		supports: {
			anchor: true,
		},
		attributes,
		edit,
		save,
	} );
}
