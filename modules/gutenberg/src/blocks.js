/**
 * Gutenberg Blocks
 *
 * All blocks related JavaScript files should be imported here.
 * You can create a new block folder in this dir and include code
 * for that block here as well.
 *
 * All blocks should be included here since this is the file that
 * Webpack is compiling as the input file.
 */

import './blocks/offer-yes-no-link/block.js';
import './blocks/offer-yes-no-button/block.js';
import './blocks/offer-product-title/block.js';
import './blocks/offer-product-description/block.js';
import './blocks/offer-product-price/block.js';
import './blocks/offer-product-quantity/block.js';
import './blocks/offer-product-variation/block.js';
import './blocks/offer-product-image/block.js';
import './blocks/pro-optin-form/block.js';
import './blocks/pro-checkout-form/block.js';

import CFP_Block_Icons from '../dist/blocks/controls/block-icons';

const { updateCategory } = wp.blocks;

updateCategory( 'cartflows-pro', {
	icon: CFP_Block_Icons.logo,
} );
