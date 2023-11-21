<?php
/**
 * Pre checkout offer css
 *
 * @package cartflows-pro
 */

/* For pre-checout offer start */

$output .= '
	/* Pre Checkout upsell */
';

$output .= "
	.wcf-pre-checkout-offer-wrapper.wcf-pre-checkout-full-width{
		background:{$pre_checkout_bg_color};
	}
	.wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-modal{
		font-family:{$base_font_family};
	}
	.wcf-pre-checkout-offer-wrapper .wcf-nav-bar-step.active .wcf-progress-nav-step{
		background: {$pre_checkout_navbar_color} !important;
	}
	.wcf-pre-checkout-offer-wrapper .wcf-nav-bar-step.active .wcf-nav-bar-step-line:before,
	.wcf-pre-checkout-offer-wrapper .wcf-nav-bar-step.active .wcf-nav-bar-step-line:after{
		background: {$pre_checkout_navbar_color} !important;
	}
	.wcf-pre-checkout-offer-wrapper .wcf-content-main-head .wcf-content-modal-title .wcf_first_name{
		color:{$primary_color};
	}
	.wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn{
		border-color: {$pre_checkout_button_color} !important;
		background:{$pre_checkout_button_color} !important;
	}
	.wcf-pre-checkout-offer-wrapper .wcf-nav-bar-step.active .wcf-nav-bar-title:before{
		color: {$pre_checkout_desc_color};
	}

	.wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-modal,
	.wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content{
		background-color: {$pre_checkout_model_bg_color};
	}

	.wcf-pre-checkout-offer-wrapper .wcf-lightbox-content .wcf-content-main-head .wcf-content-modal-title h1,
	.wcf-pre-checkout-offer-wrapper .wcf-lightbox-content #wcf-pre-checkout-offer-content .wcf-pre-checkout-info .wcf-pre-checkout-offer-product-title h1,
	.wcf-pre-checkout-offer-wrapper .wcf-content-main-head .wcf-content-modal-title .wcf_first_name{
		color:{$pre_checkout_title_color} !important;
	}
	.wcf-pre-checkout-offer-wrapper .wcf-lightbox-content .wcf-content-main-head .wcf-content-modal-sub-title span,
	.wcf-pre-checkout-offer-wrapper .wcf-content-modal-sub-title span{
		color:{$pre_checkout_subtitle_color} !important;
	}

	.wcf-pre-checkout-offer-wrapper .wcf-pre-checkout-offer-desc span,
	.wcf-pre-checkout-offer-wrapper .wcf-pre-checkout-offer-price,
	.wcf-progress-bar-nav,
	.wcf-pre-checkout-offer-wrapper .wcf-pre-checkout-skip-btn .wcf-pre-checkout-skip{
		color:{$pre_checkout_desc_color} !important;
	}
";

$output .= '
	/* Pre Checkout upsell */
';
/* For pre-checout offer close */
