const attributes = {
	block_id: {
		type: 'string',
	},
	classMigrate: {
		type: 'boolean',
		default: false,
	},
	align: {
		type: 'string',
		default: 'center',
	},
	malign: {
		type: 'string',
		default: 'center',
	},
	talign: {
		type: 'string',
		default: 'center',
	},

	// for offer action
	offerAction: {
		type: 'string',
		default: 'accept_offer',
	},

	// for content
	offerYesNoButtonTitle: {
		type: 'string',
		default: 'Accept Offer',
	},
	offerYesNoButtonSubTitle: {
		type: 'string',
	},

	// for text alignment
	textAlignment: {
		type: 'string',
		default: 'center',
	},

	// for padding
	paddingTypeDesktop: {
		type: 'string',
		default: 'px',
	},
	paddingTypeTablet: {
		type: 'string',
		default: 'px',
	},
	paddingTypeMobile: {
		type: 'string',
		default: 'px',
	},
	vPaddingDesktop: {
		type: 'number',
		default: 5,
	},
	hPaddingDesktop: {
		type: 'number',
		default: 30,
	},
	vPaddingTablet: {
		type: 'number',
		default: 5,
	},
	hPaddingTablet: {
		type: 'number',
		default: 30,
	},
	vPaddingMobile: {
		type: 'number',
		default: 5,
	},
	hPaddingMobile: {
		type: 'number',
		default: 30,
	},

	// border
	borderStyle: {
		type: 'string',
		default: 'solid',
	},
	borderWidth: {
		type: 'number',
		default: 1,
	},
	borderRadius: {
		type: 'number',
		default: 4,
	},
	borderColor: {
		type: 'string',
		default: '#abb8c3',
	},
	borderHoverColor: {
		type: 'string',
		default: '#abb8c3',
	},

	// text color
	textColor: {
		type: 'string',
		default: '#ffffff',
	},
	textHoverColor: {
		type: 'string',
		default: '#ffffff',
	},
	// button color
	buttonHoverColor: {
		type: 'string',
		default: '#abb8c3',
	},
	// background
	backgroundType: {
		type: 'string',
		default: 'color',
	},
	backgroundImage: {
		type: 'object',
	},
	backgroundPosition: {
		type: 'string',
		default: 'center-center',
	},
	backgroundSize: {
		type: 'string',
		default: 'cover',
	},
	backgroundRepeat: {
		type: 'string',
		default: 'no-repeat',
	},
	backgroundAttachment: {
		type: 'string',
		default: 'scroll',
	},
	backgroundOpacity: {
		type: 'number',
	},
	backgroundImageColor: {
		type: 'string',
		default: '#abb8c3',
	},
	backgroundColor: {
		type: 'string',
		default: '#abb8c3',
	},
	gradientColor1: {
		type: 'string',
		default: '#abb8c3',
	},
	gradientColor2: {
		type: 'string',
		default: '#abb8c3',
	},
	gradientType: {
		type: 'string',
		default: 'linear',
	},
	gradientLocation1: {
		type: 'number',
		default: 0,
	},
	gradientLocation2: {
		type: 'number',
		default: 100,
	},
	gradientAngle: {
		type: 'number',
		default: 0,
	},
	gradientPosition: {
		type: 'string',
		default: 'center center',
	},
	gradientValue: {
		type: 'string',
		default: '',
	},
	// title font family
	titleLoadGoogleFonts: {
		type: 'boolean',
		default: false,
	},
	titleFontFamily: {
		type: 'string',
		default: 'Default',
	},
	titleFontWeight: {
		type: 'string',
		default: '500',
	},
	titleFontSubset: {
		type: 'string',
	},

	// title font size
	titleFontSize: {
		type: 'number',
		default: 20,
	},
	titleFontSizeType: {
		type: 'string',
		default: 'px',
	},
	titleFontSizeTablet: {
		type: 'number',
	},
	titleFontSizeMobile: {
		type: 'number',
	},

	// title line height
	titleLineHeightType: {
		type: 'string',
		default: 'em',
	},
	titleLineHeight: {
		type: 'number',
	},
	titleLineHeightTablet: {
		type: 'number',
	},
	titleLineHeightMobile: {
		type: 'number',
	},

	// sub title font family
	subTitleLoadGoogleFonts: {
		type: 'boolean',
		default: false,
	},
	subTitleFontFamily: {
		type: 'string',
		default: 'Default',
	},
	subTitleFontWeight: {
		type: 'string',
		default: '500',
	},
	subTitleFontSubset: {
		type: 'string',
	},

	// sub title font size
	subTitleFontSize: {
		type: 'number',
		default: 20,
	},
	subTitleFontSizeType: {
		type: 'string',
		default: 'px',
	},
	subTitleFontSizeTablet: {
		type: 'number',
	},
	subTitleFontSizeMobile: {
		type: 'number',
	},

	// sub title line height
	subTitleLineHeightType: {
		type: 'string',
		default: 'em',
	},
	subTitleLineHeight: {
		type: 'number',
	},
	subTitleLineHeightTablet: {
		type: 'number',
	},
	subTitleLineHeightMobile: {
		type: 'number',
	},

	// title bottom spacing
	titleBottomSpacing: {
		type: 'number',
		default: 0,
	},

	// icon
	icon: {
		type: 'string',
		default: '',
	},
	iconSize: {
		type: 'number',
		default: 20,
	},
	iconSpacing: {
		type: 'number',
		default: 0,
	},
	iconColor: {
		type: 'string',
	},
	iconHoverColor: {
		type: 'string',
	},
	iconPosition: {
		type: 'string',
		default: 'before_title',
	},
};

export default attributes;
