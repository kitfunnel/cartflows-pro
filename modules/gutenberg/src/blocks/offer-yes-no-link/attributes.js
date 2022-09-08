const attributes = {
	block_id: {
		type: 'string',
	},
	classMigrate: {
		type: 'boolean',
		default: false,
	},

	// for content
	linkText: {
		type: 'string',
		default: 'Accept Offer',
	},

	// for offer action
	offerAction: {
		type: 'string',
		default: 'accept_offer',
	},

	// for text alignment
	linkTextAlignment: {
		type: 'string',
		default: 'center',
	},

	// for margin
	topMargin: {
		type: 'number',
		default: 0,
	},
	bottomMargin: {
		type: 'number',
		default: 0,
	},

	// link text color
	linkTextColor: {
		type: 'string',
		default: '#4169E1',
	},
	linkTextHoverColor: {
		type: 'string',
		default: '',
	},

	// link text font family
	linkTextLoadGoogleFonts: {
		type: 'boolean',
		default: false,
	},
	linkTextFontFamily: {
		type: 'string',
		default: 'Default',
	},
	linkTextFontWeight: {
		type: 'string',
		default: '500',
	},
	linkTextFontSubset: {
		type: 'string',
	},

	// link text font size
	linkTextFontSize: {
		type: 'number',
		default: 20,
	},
	linkTextFontSizeType: {
		type: 'string',
		default: 'px',
	},
	linkTextFontSizeTablet: {
		type: 'number',
	},
	linkTextFontSizeMobile: {
		type: 'number',
	},

	// link text line height
	linkTextLineHeightType: {
		type: 'string',
		default: 'em',
	},
	linkTextLineHeight: {
		type: 'number',
	},
	linkTextLineHeightTablet: {
		type: 'number',
	},
	linkTextLineHeightMobile: {
		type: 'number',
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
	iconPosition: {
		type: 'string',
		default: 'before_link_text',
	},
};

export default attributes;
