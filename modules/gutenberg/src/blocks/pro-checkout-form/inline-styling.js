import generateCSS from '../../../dist/blocks/controls/generate-css';

function inline( props ) {
	const {
		OverlayBackgroundColor,
		ModalBackgroundColor,
		TitleColor,
		SubtitleColor,
		DescriptionColor,
	} = props.attributes;

	const selectors = {
		' .wcf-pre-checkout-offer-price, body .wcf-progress-bar-nav, .wcf-pre-checkout-skip-btn .wcf-pre-checkout-skip': {
			color: DescriptionColor + ' !important',
		},
		' .wcf-lightbox-content .wcf-content-main-head .wcf-content-modal-title h1, .wcf-lightbox-content #wcf-pre-checkout-offer-content .wcf-pre-checkout-info .wcf-pre-checkout-offer-product-title h1, .wcf-content-main-head .wcf-content-modal-title .wcf_first_name': {
			color: TitleColor + ' !important',
		},
		' .wcf-lightbox-content .wcf-content-main-head .wcf-content-modal-sub-title span, .wcf-content-modal-sub-title span': {
			color: SubtitleColor + ' !important',
		},
		' #wcf-pre-checkout-offer-modal': {
			'background-color': ModalBackgroundColor,
		},
		' #wcf-pre-checkout-offer-content': {
			'background-color': ModalBackgroundColor,
		},
		' .wcf-pre-checkout-full-width': {
			'background-color': OverlayBackgroundColor,
		},
	};

	const inline_selector = `body .wcf-pre-checkout-offer-wrapper`;

	const inline_css = generateCSS( selectors, inline_selector );

	// console.log(inline_css);

	return inline_css;
}

export default inline;
