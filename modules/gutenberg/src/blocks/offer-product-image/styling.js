/**
 * Returns Dynamic Generated CSS
 */

import generateCSS from '../../../dist/blocks/controls/generate-css';
import generateCSSUnit from '../../../dist/blocks/controls/generate-css-unit';

function styling( props ) {
	const {
		// Alignment
		alignment,
		// Image Bottom Spacing
		image_bottom_spacing,
		//Margin
		topMargin,
		bottomMargin,
		// Image Border
		imageBorderStyle,
		imageBorderWidth,
		imageBorderColor,
		imageBorderRadius,
		// Spacing Between Thumbnails
		spacing_between_thumbnails,
		// Thumbnail Border
		thumbnailBorderStyle,
		thumbnailBorderWidth,
		thumbnailBorderColor,
		thumbnailBorderRadius,
	} = props.attributes;

	let selectors = {};
	const tablet_selectors = {};
	const mobile_selectors = {};

	selectors = {
		' .woocommerce-product-gallery .woocommerce-product-gallery__image': {
			'text-align': alignment,
		},
		' .woocommerce-product-gallery .woocommerce-product-gallery__wrapper': {
			'margin-bottom': generateCSSUnit( image_bottom_spacing, 'px' ),
		},
		' .woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image img': {
			'border-style': imageBorderStyle,
			'border-color': imageBorderColor,
			'border-width': generateCSSUnit( imageBorderWidth, 'px' ),
			'border-radius': generateCSSUnit( imageBorderRadius, 'px' ),
		},

		' .woocommerce-product-gallery ol li:not(:last-child)': {
			'margin-right': generateCSSUnit( spacing_between_thumbnails, 'px' ),
			'margin-bottom': generateCSSUnit(
				spacing_between_thumbnails,
				'px'
			),
		},
		' .woocommerce-product-gallery ol li img': {
			'border-style': thumbnailBorderStyle,
			'border-color': thumbnailBorderColor,
			'border-width': generateCSSUnit( thumbnailBorderWidth, 'px' ),
			'border-radius': generateCSSUnit( thumbnailBorderRadius, 'px' ),
		},

		' .wpcfp__offer-product-image': {
			'margin-top': generateCSSUnit( topMargin, 'px' ),
			'margin-bottom': generateCSSUnit( bottomMargin, 'px' ),
		},
	};

	const base_selector = `.editor-styles-wrapper .cfp-block-${ props.clientId.substr(
		0,
		8
	) }`;

	let styling_css = generateCSS( selectors, base_selector );

	styling_css += generateCSS(
		tablet_selectors,
		base_selector,
		true,
		'tablet'
	);

	styling_css += generateCSS(
		mobile_selectors,
		base_selector,
		true,
		'mobile'
	);

	return styling_css;
}

export default styling;
