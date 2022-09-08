/**
 * BLOCK: Offer Product title
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

import CFP_Block_Icons from '../../../dist/blocks/controls/block-icons';
import './style.scss';
import edit from './edit';

const { __ } = wp.i18n;

const { registerBlockType } = wp.blocks;

if (
	'upsell' === cfp_blocks_info.step_type ||
	( 'downsell' === cfp_blocks_info.step_type &&
		cfp_blocks_info.is_woo_active )
) {
	registerBlockType( 'wcfpb/offer-product-title', {
		title: cfp_blocks_info.blocks[ 'wcfpb/offer-product-title' ].title,
		description:
			cfp_blocks_info.blocks[ 'wcfpb/offer-product-title' ].description,
		icon: CFP_Block_Icons.offer_product_title,
		category: cfp_blocks_info.category,
		keywords: [
			__( 'cartflows', 'cartflows-pro' ),
			__( 'offer product title', 'cartflows-pro' ),
			__( 'product title', 'cartflows-pro' ),
			__( 'cfp', 'cartflows-pro' ),
		],
		edit,
		save() {
			return null;
		},
	} );
}
