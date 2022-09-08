/**
 * Returns Dynamic Generated CSS
 */

import generateCSS from '../../../dist/blocks/controls/generate-css';
import generateCSSUnit from '../../../dist/blocks/controls/generate-css-unit';

function styling( props ) {
	const {
		align,
		talign,
		malign,
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
		//Border
		borderStyle,
		borderWidth,
		borderRadius,
		borderColor,
		borderHoverColor,
		// Text Color
		textColor,
		textHoverColor,
		// Button Color
		buttonHoverColor,
		// Title
		titleFontFamily,
		titleFontWeight,
		titleFontSize,
		titleFontSizeType,
		titleFontSizeMobile,
		titleFontSizeTablet,
		titleLineHeight,
		titleLineHeightType,
		titleLineHeightMobile,
		titleLineHeightTablet,
		// Sub Title
		subTitleFontFamily,
		subTitleFontWeight,
		subTitleFontSize,
		subTitleFontSizeType,
		subTitleFontSizeMobile,
		subTitleFontSizeTablet,
		subTitleLineHeight,
		subTitleLineHeightType,
		subTitleLineHeightMobile,
		subTitleLineHeightTablet,
		// Title Bottom Margin
		titleBottomSpacing,
		// Icon
		iconPosition,
		iconColor,
		iconHoverColor,
		iconSize,
		iconSpacing,
		// Background
		backgroundType,
		backgroundImageColor,
		backgroundOpacity,
		backgroundColor,
		gradientColor1,
		gradientColor2,
		gradientLocation1,
		gradientLocation2,
		gradientType,
		gradientAngle,
		gradientPosition,
		backgroundPosition,
		backgroundSize,
		backgroundAttachment,
		backgroundImage,
		backgroundRepeat,
		gradientValue,
	} = props.attributes;
	const position = backgroundPosition.replace( '-', ' ' );
	let selectors = {};
	const tablet_selectors = {};
	const mobile_selectors = {};
	selectors = {
		' .wpcfp__offer-yes-no-button-wrap': {
			'text-align': align,
		},
		' .wpcfp__offer-yes-no-button-link:hover': {
			color: textHoverColor,
			'border-color': borderHoverColor,
		},
		' .wpcfp__offer-yes-no-button-link .wpcfp__offer-yes-no-button-content-wrap .wpcfp__offer-yes-no-button-title-wrap': {
			'font-family': titleFontFamily,
			'font-weight': titleFontWeight,
			'font-size': generateCSSUnit( titleFontSize, titleFontSizeType ),
			'line-height': generateCSSUnit(
				titleLineHeight,
				titleLineHeightType
			),
		},
		' .wpcfp__offer-yes-no-button-link .wpcfp__offer-yes-no-button-content-wrap .wpcfp__offer-yes-no-button-sub-title': {
			'font-family': subTitleFontFamily,
			'font-weight': subTitleFontWeight,
			'font-size': generateCSSUnit(
				subTitleFontSize,
				subTitleFontSizeType
			),
			'line-height': generateCSSUnit(
				subTitleLineHeight,
				subTitleLineHeightType
			),
			'margin-top': generateCSSUnit( titleBottomSpacing, 'px' ),
		},
		' .wpcfp__offer-yes-no-button-icon svg': {
			width: generateCSSUnit( iconSize, 'px' ),
			height: generateCSSUnit( iconSize, 'px' ),
			fill: iconColor,
		},
		' .wpcfp__offer-yes-no-button-link:hover .wpcfp__offer-yes-no-button-icon svg': {
			fill: iconHoverColor,
		},
	};

	selectors[ ' .wpcfp__offer-yes-no-button-link' ] = {};

	if ( 'gradient' === backgroundType ) {
		selectors[ ' .wpcfp__offer-yes-no-button-link' ] = {
			'border-style': borderStyle,
			'border-color': borderColor,
			'border-width': generateCSSUnit( borderWidth, 'px' ),
			'border-radius': generateCSSUnit( borderRadius, 'px' ),
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
			color: textColor,
			'text-align': textAlignment,
		};
	}

	if ( 'image' === backgroundType ) {
		selectors[ ' .wpcfp__offer-yes-no-button-link' ] = {
			opacity:
				typeof backgroundOpacity !== 'undefined'
					? backgroundOpacity / 100
					: '',
			'background-color': backgroundImageColor,
			'border-style': borderStyle,
			'border-color': borderColor,
			'border-width': generateCSSUnit( borderWidth, 'px' ),
			'border-radius': generateCSSUnit( borderRadius, 'px' ),
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
			color: textColor,
			'background-image': backgroundImage
				? `url(${ backgroundImage.url })`
				: null,
			'background-position': position,
			'background-attachment': backgroundAttachment,
			'background-repeat': backgroundRepeat,
			'background-size': backgroundSize,
			'text-align': textAlignment,
		};
	} else if ( 'color' === backgroundType ) {
		selectors[ ' .wpcfp__offer-yes-no-button-link' ] = {
			opacity:
				typeof backgroundOpacity !== 'undefined'
					? backgroundOpacity / 100
					: '',
			'background-color': backgroundColor,
			'border-style': borderStyle,
			'border-color': borderColor,
			'border-width': generateCSSUnit( borderWidth, 'px' ),
			'border-radius': generateCSSUnit( borderRadius, 'px' ),
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
			color: textColor,
			'text-align': textAlignment,
		};
		selectors[ ' .wpcfp__offer-yes-no-button-link:hover' ][
			'background-color'
		] = buttonHoverColor;
	} else if ( 'gradient' === backgroundType ) {
		selectors[ ' .wpcfp__offer-yes-no-button-link' ][ 'background-color' ] =
			'transparent';
		selectors[ ' .wpcfp__offer-yes-no-button-link' ].opacity =
			typeof backgroundOpacity !== 'undefined'
				? backgroundOpacity / 100
				: '';
		if ( gradientValue ) {
			selectors[ ' .wpcfp__offer-yes-no-button-link' ][
				'background-image'
			] = gradientValue;
		} else if ( 'linear' === gradientType ) {
			selectors[ ' .wpcfp__offer-yes-no-button-link' ][
				'background-image'
			] = `linear-gradient(${ gradientAngle }deg, ${ gradientColor1 } ${ gradientLocation1 }%, ${ gradientColor2 } ${ gradientLocation2 }%)`;
		} else {
			selectors[ ' .wpcfp__offer-yes-no-button-link' ][
				'background-image'
			] = `radial-gradient( at ${ gradientPosition }, ${ gradientColor1 } ${ gradientLocation1 }%, ${ gradientColor2 } ${ gradientLocation2 }%)`;
		}
	}

	if ( align === 'full' ) {
		selectors[ ' a.wpcfp__offer-yes-no-button-link' ] = {
			width: '100%',
			'justify-content': 'center',
		};
	}

	const margin_type =
		'after_title' === iconPosition ||
		'after_title_sub_title' === iconPosition
			? 'margin-left'
			: 'margin-right';
	selectors[ ' .wpcfp__offer-yes-no-button-icon svg' ][
		margin_type
	] = generateCSSUnit( iconSpacing, 'px' );

	tablet_selectors[ ' .wpcfp__offer-yes-no-button-wrap' ] = {
		'text-align': talign,
	};
	tablet_selectors[ ' .wpcfp__offer-yes-no-button-link' ] = {
		'padding-top': generateCSSUnit( vPaddingTablet, paddingTypeTablet ),
		'padding-bottom': generateCSSUnit( vPaddingTablet, paddingTypeTablet ),
		'padding-left': generateCSSUnit( hPaddingTablet, paddingTypeTablet ),
		'padding-right': generateCSSUnit( hPaddingTablet, paddingTypeTablet ),
	};
	tablet_selectors[
		' .wpcfp__offer-yes-no-button-link .wpcfp__offer-yes-no-button-content-wrap .wpcfp__offer-yes-no-button-title-wrap'
	] = {
		'font-size': generateCSSUnit( titleFontSizeTablet, titleFontSizeType ),
		'line-height': generateCSSUnit(
			titleLineHeightTablet,
			titleLineHeightType
		),
	};
	tablet_selectors[
		' .wpcfp__offer-yes-no-button-link .wpcfp__offer-yes-no-button-content-wrap .wpcfp__offer-yes-no-button-sub-title'
	] = {
		'font-size': generateCSSUnit(
			subTitleFontSizeTablet,
			titleFontSizeType
		),
		'line-height': generateCSSUnit(
			subTitleLineHeightTablet,
			titleLineHeightType
		),
	};

	mobile_selectors[ ' .wpcfp__offer-yes-no-button-wrap' ] = {
		'text-align': malign,
	};
	mobile_selectors[ ' .wpcfp__offer-yes-no-button-link' ] = {
		'padding-top': generateCSSUnit( vPaddingMobile, paddingTypeMobile ),
		'padding-bottom': generateCSSUnit( vPaddingMobile, paddingTypeMobile ),
		'padding-left': generateCSSUnit( hPaddingMobile, paddingTypeMobile ),
		'padding-right': generateCSSUnit( hPaddingMobile, paddingTypeMobile ),
	};
	mobile_selectors[
		' .wpcfp__offer-yes-no-button-link .wpcfp__offer-yes-no-button-content-wrap .wpcfp__offer-yes-no-button-title-wrap'
	] = {
		'font-size': generateCSSUnit( titleFontSizeMobile, titleFontSizeType ),
		'line-height': generateCSSUnit(
			titleLineHeightMobile,
			titleLineHeightType
		),
	};
	mobile_selectors[
		' .wpcfp__offer-yes-no-button-link .wpcfp__offer-yes-no-button-content-wrap .wpcfp__offer-yes-no-button-sub-title'
	] = {
		'font-size': generateCSSUnit(
			subTitleFontSizeMobile,
			titleFontSizeType
		),
		'line-height': generateCSSUnit(
			subTitleLineHeightMobile,
			titleLineHeightType
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
