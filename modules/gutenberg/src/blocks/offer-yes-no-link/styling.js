/**
 * Returns Dynamic Generated CSS
 */

import generateCSS from '../../../dist/blocks/controls/generate-css';
import generateCSSUnit from '../../../dist/blocks/controls/generate-css-unit';

function styling( props ) {
	const {
		// Icon
		iconSize,
		iconPosition,
		iconSpacing,
		// Alignment
		linkTextAlignment,
		//Margin
		topMargin,
		bottomMargin,
		// Text Color
		linkTextColor,
		linkTextHoverColor,
		// Title
		linkTextFontFamily,
		linkTextFontWeight,
		linkTextFontSize,
		linkTextFontSizeType,
		linkTextFontSizeMobile,
		linkTextFontSizeTablet,
		linkTextLineHeight,
		linkTextLineHeightType,
		linkTextLineHeightMobile,
		linkTextLineHeightTablet,
	} = props.attributes;

	let selectors = {};
	const tablet_selectors = {};
	const mobile_selectors = {};

	selectors = {
		' .wpcfp__offer-yes-no-link': {
			'text-align': linkTextAlignment,
			'margin-top': generateCSSUnit( topMargin, 'px' ),
			'margin-bottom': generateCSSUnit( bottomMargin, 'px' ),
		},
		' .wpcfp__offer-yes-no-link-url': {
			color: linkTextColor,
		},
		' .wpcfp__offer-yes-no-link-url:hover': {
			color: linkTextHoverColor,
		},

		' .wpcfp__offer-yes-no-link-url .wpcfp__offer-yes-no-link-text-wrap': {
			'font-family': linkTextFontFamily,
			'font-weight': linkTextFontWeight,
			'font-size': generateCSSUnit(
				linkTextFontSize,
				linkTextFontSizeType
			),
			'line-height': generateCSSUnit(
				linkTextLineHeight,
				linkTextLineHeightType
			),
		},

		' .wpcfp__offer-yes-no-link-icon svg': {
			width: generateCSSUnit( iconSize, 'px' ),
			height: generateCSSUnit( iconSize, 'px' ),
			fill: linkTextColor,
		},
		' .wpcfp__offer-yes-no-link-url:hover .wpcfp__offer-yes-no-link-icon svg': {
			fill: linkTextHoverColor,
		},
	};

	const margin_type =
		'after_link_text' === iconPosition ? 'margin-left' : 'margin-right';
	selectors[ ' .wpcfp__offer-yes-no-link-icon svg' ][
		margin_type
	] = generateCSSUnit( iconSpacing, 'px' );

	tablet_selectors[
		' .wpcfp__offer-yes-no-link-url .wpcfp__offer-yes-no-link-text-wrap'
	] = {
		'font-size': generateCSSUnit(
			linkTextFontSizeTablet,
			linkTextFontSizeType
		),
		'line-height': generateCSSUnit(
			linkTextLineHeightTablet,
			linkTextLineHeightType
		),
	};

	mobile_selectors[
		' .wpcfp__offer-yes-no-link-url .wpcfp__offer-yes-no-link-text-wrap'
	] = {
		'font-size': generateCSSUnit(
			linkTextFontSizeMobile,
			linkTextFontSizeType
		),
		'line-height': generateCSSUnit(
			linkTextLineHeightMobile,
			linkTextLineHeightType
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
