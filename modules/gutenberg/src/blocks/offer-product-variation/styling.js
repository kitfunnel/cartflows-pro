/**
 * Returns Dynamic Generated CSS
 */

import generateCSS from '../../../dist/blocks/controls/generate-css';
import generateCSSUnit from '../../../dist/blocks/controls/generate-css-unit';

function styling( props ) {
	const {
		// Alignment
		alignment,
		// Width
		width,
		// Label bottom spacing
		label_bottom_spacing,
		//Margin
		topMargin,
		bottomMargin,
		// Label Color
		labelColor,
		// Input Text Color
		inputTextColor,
		// Text
		textFontFamily,
		textFontWeight,
		textFontSize,
		textFontSizeType,
		textFontSizeMobile,
		textFontSizeTablet,
		textLineHeight,
		textLineHeightType,
		textLineHeightMobile,
		textLineHeightTablet,
		// Text Shadow
		textShadowColor,
		textShadowHOffset,
		textShadowVOffset,
		textShadowBlur,
	} = props.attributes;

	let selectors = {};
	const tablet_selectors = {};
	const mobile_selectors = {};

	selectors = {
		' .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations': {
			'max-width': generateCSSUnit( width, '%' ),
		},
		' .wpcfp__offer-product-variation, .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations .value select, .wpcfp__offer-product-variation .label label': {
			'font-family': textFontFamily,
			'font-weight': textFontWeight,
			'font-size': generateCSSUnit( textFontSize, textFontSizeType ),
			'line-height': generateCSSUnit(
				textLineHeight,
				textLineHeightType
			),
			'text-shadow':
				generateCSSUnit( textShadowHOffset, 'px' ) +
				' ' +
				generateCSSUnit( textShadowVOffset, 'px' ) +
				' ' +
				generateCSSUnit( textShadowBlur, 'px' ) +
				' ' +
				textShadowColor,
		},
		' .wpcfp__offer-product-variation label': {
			color: labelColor,
		},
		' .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations .value select': {
			color: inputTextColor,
		},
		' .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations td.label': {
			'margin-bottom': generateCSSUnit( label_bottom_spacing, 'px' ),
		},
		' .wpcfp__offer-product-variation': {
			'margin-top': generateCSSUnit( topMargin, 'px' ),
			'margin-bottom': generateCSSUnit( bottomMargin, 'px' ),
		},
	};

	if ( 'left' === alignment ) {
		selectors[
			' .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations'
		][ 'margin-right' ] = 'auto';
	} else if ( 'right' === alignment ) {
		selectors[
			' .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations'
		][ 'margin-left' ] = 'auto';
	} else {
		selectors[
			' .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations'
		][ 'margin-right' ] = 'auto';
		selectors[
			' .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations'
		][ 'margin-left' ] = 'auto';
	}

	tablet_selectors[
		' .wpcfp__offer-product-variation, .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations .value select, .wpcfp__offer-product-variation .label label'
	] = {
		'font-size': generateCSSUnit( textFontSizeTablet, textFontSizeType ),
		'line-height': generateCSSUnit(
			textLineHeightTablet,
			textLineHeightType
		),
	};

	mobile_selectors[
		' .wpcfp__offer-product-variation, .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations .value select, .wpcfp__offer-product-variation .label label'
	] = {
		'font-size': generateCSSUnit( textFontSizeMobile, textFontSizeType ),
		'line-height': generateCSSUnit(
			textLineHeightMobile,
			textLineHeightType
		),
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
