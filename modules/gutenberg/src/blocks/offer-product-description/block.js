/**
 * BLOCK: Offer Product Description
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
	registerBlockType( 'wcfpb/offer-product-description', {
		title:
			cfp_blocks_info.blocks[ 'wcfpb/offer-product-description' ].title,
		description:
			cfp_blocks_info.blocks[ 'wcfpb/offer-product-description' ]
				.description,
		icon: CFP_Block_Icons.offer_product_description,
		category: cfp_blocks_info.category,
		keywords: [
			__( 'cartflows', 'cartflows-pro' ),
			__( 'offer product description', 'cartflows-pro' ),
			__( 'cfp', 'cartflows-pro' ),
		],
		edit,
	} );
}
