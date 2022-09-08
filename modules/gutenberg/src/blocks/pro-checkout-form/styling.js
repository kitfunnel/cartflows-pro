import generateCSS from '../../../dist/blocks/controls/generate-css';
import generateCSSUnit from '../../../dist/blocks/controls/generate-css-unit';

function styling( props ) {
	const {
		productBorderColor,
		productBorderRadius,
		productBorderWidth,
		productBorderStyle,
		productTextBgColor,
		productTextColor,
		productTitleTextColor,
		productHighlightBgColor,
		productHighlightTextColor,
		productHighlightBorderstyle,
		productHighlightBorderWidth,
		productHighlightBorderRadius,
		productHighlightBorderColor,
		productHighlightFlagTextColor,
		productHighlightFlagBgColor,
		twoStepBgColor,
		twoStepTextColor,
		// Text
		tstextFontFamily,
		tstextFontWeight,
		tstextFontSize,
		tstextFontSizeType,
		tstextFontSizeMobile,
		tstextFontSizeTablet,
		tstextLineHeight,
		tstextLineHeightType,
		tstextLineHeightMobile,
		tstextLineHeightTablet,
	} = props.attributes;

	const tablet_selectors = {};
	const mobile_selectors = {};

	const selectors = {
		' .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .wcf-qty-options .wcf-qty-header .wcf-field-label, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .wcf-qty-options .wcf-qty-row': {
			color: productTextColor,
		},
		' .wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options, .wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row': {
			'background-color': productTextBgColor,
			'border-style': productBorderStyle,
			'border-width': generateCSSUnit( productBorderWidth, 'px' ),
			'border-color': productBorderColor,
			'border-radius': generateCSSUnit( productBorderRadius, 'px' ),
		},
		' .wcf-product-option-wrap #your_products_heading': {
			color: productTitleTextColor,
		},
		' .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight': {
			'background-color': productHighlightBgColor,
			color: productHighlightTextColor,
			'border-style': productHighlightBorderstyle,
			'border-width': productHighlightBorderWidth
				? productHighlightBorderWidth + 'px'
				: '',
			'border-radius': productHighlightBorderRadius
				? productHighlightBorderRadius + 'px'
				: '',
			'border-color': productHighlightBorderColor,
		},
		' .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight .wcf-highlight-head': {
			color: productHighlightFlagTextColor,
			'background-color': productHighlightFlagBgColor,
		},
		' .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note': {
			color: twoStepTextColor,
			'background-color': twoStepBgColor + ' !important',
			'border-color': twoStepBgColor,
			'font-family': tstextFontFamily,
			'font-weight': tstextFontWeight,
			'font-size': generateCSSUnit( tstextFontSize, tstextFontSizeType ),
			'line-height': generateCSSUnit(
				tstextLineHeight,
				tstextLineHeightType
			),
		},
		' .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note:before': {
			'border-top-color': twoStepBgColor,
		},
	};

	tablet_selectors[
		' .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note'
	] = {
		'font-size': generateCSSUnit(
			tstextFontSizeTablet,
			tstextFontSizeType
		),
		'line-height': generateCSSUnit(
			tstextLineHeightTablet,
			tstextLineHeightType
		),
	};

	mobile_selectors[
		' .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note'
	] = {
		'font-size': generateCSSUnit(
			tstextFontSizeMobile,
			tstextFontSizeType
		),
		'line-height': generateCSSUnit(
			tstextLineHeightMobile,
			tstextLineHeightType
		),
	};

	const base_selector = `.editor-styles-wrapper .cf-block-${ props.clientId.substr(
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
