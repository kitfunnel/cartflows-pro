/**
 * Returns Dynamic Generated CSS
 */

import generateCSS from '../../../dist/blocks/controls/generate-css';
import generateCSSUnit from '../../../dist/blocks/controls/generate-css-unit';

function styling( props ) {
	const {
		// Alignment
		textAlignment,
		//Margin
		topMargin,
		bottomMargin,
		// Text Color
		textColor,
		textHoverColor,
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
		' .wpcfp__offer-product-title': {
			'text-align': textAlignment,
			color: textColor,
			'margin-top': generateCSSUnit( topMargin, 'px' ),
			'margin-bottom': generateCSSUnit( bottomMargin, 'px' ),
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
		' .wpcfp__offer-product-title:hover': {
			color: textHoverColor,
		},
	};

	tablet_selectors[ ' .wpcfp__offer-product-title' ] = {
		'font-size': generateCSSUnit( textFontSizeTablet, textFontSizeType ),
		'line-height': generateCSSUnit(
			textLineHeightTablet,
			textLineHeightType
		),
	};

	mobile_selectors[ ' .wpcfp__offer-product-title' ] = {
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
