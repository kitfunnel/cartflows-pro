/**
 * Returns Dynamic Generated CSS
 */

import generateCSS from '../../../dist/blocks/controls/generate-css';
import generateCSSUnit from '../../../dist/blocks/controls/generate-css-unit';

function styling( props ) {
	const {
		// Alignment
		textAlignment,
		//Padding
		paddingTypeDesktop,
		paddingTypeTablet,
		paddingTypeMobile,
		vPaddingDesktop,
		hPaddingDesktop,
		vPaddingTablet,
		hPaddingTablet,
		vPaddingMobile,
		hPaddingMobile,
		// Text Color
		textColor,
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
		' .wpcfp__offer-product-description': {
			'text-align': textAlignment,
			color: textColor,
			'padding-top': generateCSSUnit(
				vPaddingDesktop,
				paddingTypeDesktop
			),
			'padding-bottom': generateCSSUnit(
				vPaddingDesktop,
				paddingTypeDesktop
			),
			'padding-left': generateCSSUnit(
				hPaddingDesktop,
				paddingTypeDesktop
			),
			'padding-right': generateCSSUnit(
				hPaddingDesktop,
				paddingTypeDesktop
			),
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
	};

	tablet_selectors[ ' .wpcfp__offer-product-description' ] = {
		'padding-top': generateCSSUnit( vPaddingTablet, paddingTypeTablet ),
		'padding-bottom': generateCSSUnit( vPaddingTablet, paddingTypeTablet ),
		'padding-left': generateCSSUnit( hPaddingTablet, paddingTypeTablet ),
		'padding-right': generateCSSUnit( hPaddingTablet, paddingTypeTablet ),
		'font-size': generateCSSUnit( textFontSizeTablet, textFontSizeType ),
		'line-height': generateCSSUnit(
			textLineHeightTablet,
			textLineHeightType
		),
	};

	mobile_selectors[ ' .wpcfp__offer-product-description' ] = {
		'padding-top': generateCSSUnit( vPaddingMobile, paddingTypeMobile ),
		'padding-bottom': generateCSSUnit( vPaddingMobile, paddingTypeMobile ),
		'padding-left': generateCSSUnit( hPaddingMobile, paddingTypeMobile ),
		'padding-right': generateCSSUnit( hPaddingMobile, paddingTypeMobile ),
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
