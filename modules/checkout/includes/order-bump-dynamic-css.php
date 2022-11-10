<?php
/**
 * Order Bump Dynamic CSS.
 *
 * @package cartflows
 */

if ( $this->divi_status ) {
	$ob_css .= "
	.wcf-bump-order-wrap.wcf-bump-order-{$ob_id}{
		background: {$bump_bg_color};
		border-style: {$bump_border_style};
		border-color: {$bump_border_color};
		border-radius: {$bump_box_border_radius};
		border-width: {$bump_box_border_width};
		box-shadow: {$bump_box_shadow_horizontal}px {$bump_box_shadow_vertical}px {$bump_box_shadow_blur}px {$bump_box_shadow_spread}px $bump_box_shadow_color;
	}
	.wcf-bump-order-wrap.wcf-bump-order-{$ob_id}.wcf-bump-order-style-2 .wcf-bump-order-field-wrap {
		border-color: {$bump_border_color};
		border-top-style: {$bump_border_style};
	}
	.wcf-bump-order-wrap.wcf-bump-order-{$ob_id}.wcf-bump-order-style-1 .wcf-bump-order-field-wrap {
		border-color: {$bump_border_color};
		border-bottom-style: {$bump_border_style};
	}

	.et_pb_module #wcf-embed-checkout-form.wcf-bump-order-wrap.wcf-bump-order-{$ob_id} .wcf-bump-order-field-wrap{
		background: {$bump_label_bg_color};
	}
	.et_pb_module #wcf-embed-checkout-form.wcf-bump-order-wrap.wcf-bump-order-{$ob_id} .wcf-bump-order-field-wrap label{
		color: {$bump_label_color};
	}
	.et_pb_module #wcf-embed-checkout-form.wcf-bump-order-wrap.wcf-bump-order-{$ob_id} .wcf-bump-order-desc{
		color: {$bump_desc_text_color};
	}
	.et_pb_module #wcf-embed-checkout-form.wcf-bump-order-wrap.wcf-bump-order-{$ob_id} .wcf-bump-order-bump-highlight {
		color: {$bump_hl_text_color};
	}
	.et_pb_module #wcf-embed-checkout-form .wcf-bump-order-wrap.wcf-bump-order-{$ob_id} .dashicons-arrow-right-alt,
	.et_pb_module #wcf-embed-checkout-form .wcf-bump-order-wrap.wcf-bump-order-{$ob_id} .dashicons-arrow-left-alt{
		color: {$bump_blinking_arrow_color};
	}
	.et_pb_module #wcf-embed-checkout-form .wcf-bump-order-wrap.wcf-bump-order-{$ob_id}.wcf-bump-order-style-2{
		background: {$bump_bg_color};
		border-style: {$bump_border_style};
		border-color: {$bump_border_color};
	}
	.et_pb_module #wcf-embed-checkout-form .wcf-bump-order-{$ob_id}.wcf-bump-order-style-2 .wcf-bump-order-field-wrap{
		border-color: {$bump_border_color}!important;
		border-top-style: {$bump_border_style}!important;
	}

	.et_pb_module #wcf-embed-checkout-form .wcf-{$order_bump_position}.wcf-bump-order-{$ob_id} .wcf-bump-order-image img{
			width: {$bump_image_width}px;
		}
	img.emoji, img.wp-smiley {}
	";

	if ( 'yes' === $enabled_bump_image_mobile ) {
		$ob_css .= "
		 	@media only screen and (max-width: 520px){
				.et_pb_module #wcf-embed-checkout-form .wcf-bump-order-wrap.wcf-bump-order-{$ob_id} .wcf-bump-order-image {
		 		display: block;
		 	}
	 	}
	 ";
	} else {
		$ob_css .= "
			@media only screen and (max-width: 768px){
				.et_pb_module #wcf-embed-checkout-form .wcf-bump-order-wrap.wcf-bump-order-{$ob_id} .wcf-bump-order-image {
		 			display: none;
		 	}
		";
	}
} else {
	$ob_css .= "
		.wcf-bump-order-wrap.wcf-bump-order-{$ob_id}{
			background: {$bump_bg_color};
			border-style: {$bump_border_style};
			border-color: {$bump_border_color};
			border-radius: {$bump_box_border_radius}px;
			border-width: {$bump_box_border_width}px;
			box-shadow: {$bump_box_shadow_horizontal}px {$bump_box_shadow_vertical}px {$bump_box_shadow_blur}px {$bump_box_shadow_spread}px $bump_box_shadow_color;
		}
		.wcf-bump-order-wrap.wcf-bump-order-{$ob_id}.wcf-bump-order-style-2 .wcf-bump-order-field-wrap {
		    border-color: {$bump_border_color};
		    border-top-style: {$bump_border_style};
			border-width: {$bump_box_border_width}px;
		}
		.wcf-bump-order-wrap.wcf-bump-order-{$ob_id}.wcf-bump-order-style-1 .wcf-bump-order-field-wrap {
		    border-color: {$bump_border_color};
		    border-bottom-style: {$bump_border_style};
			border-width: {$bump_box_border_width}px;
		}
		.wcf-bump-order-wrap.wcf-bump-order-{$ob_id}.wcf-bump-order-style-1 .wcf-bump-order-field-wrap,
		.wcf-bump-order-wrap.wcf-bump-order-{$ob_id}.wcf-bump-order-style-2 .wcf-bump-order-field-wrap,
		.wcf-bump-order-wrap.wcf-bump-order-{$ob_id}.wcf-bump-order-style-5 .wcf-bump-order-field-wrap .wcf-bump-order-action:not(.wcf-ob-action-button){
		    background: {$bump_label_bg_color};
		}
		.wcf-bump-order-wrap.wcf-bump-order-{$ob_id} .wcf-bump-order-field-wrap label{
			color: {$bump_label_color};
		}
		.wcf-bump-order-wrap.wcf-bump-order-{$ob_id} .wcf-bump-order-desc{
			color: {$bump_desc_text_color};
		}
		.wcf-bump-order-wrap.wcf-bump-order-{$ob_id} .wcf-bump-order-bump-highlight {
			color: {$bump_hl_text_color};
		}
		.wcf-bump-order-wrap.wcf-bump-order-{$ob_id} .dashicons-arrow-right-alt,
		.wcf-bump-order-wrap.wcf-bump-order-{$ob_id} .dashicons-arrow-left-alt{
			color: {$bump_blinking_arrow_color};
		}

		.wcf-bump-order-wrap.wcf-bump-order-{$ob_id}.wcf-bump-order-style-4 .wcf-bump-order-content .wcf-bump-order-cb-button,
		.wcf-bump-order-wrap.wcf-bump-order-{$ob_id}.wcf-bump-order-style-5 .wcf-bump-order-content .wcf-bump-order-cb-button{
			background: {$bump_button_color};
			color: {$bump_button_text_color};
			border-width: {$bump_button_border_width}px;
			border-style: {$bump_button_border_style};
			border-color: {$bump_button_border_color};
			border-radius: {$bump_button_border_radius}px;
		}

		.wcf-bump-order-wrap.wcf-bump-order-{$ob_id}.wcf-bump-order-style-4 .wcf-bump-order-content .wcf-bump-order-cb-button:hover,
		.wcf-bump-order-wrap.wcf-bump-order-{$ob_id}.wcf-bump-order-style-5 .wcf-bump-order-content .wcf-bump-order-cb-button:hover{
			background: {$bump_button_hover_color};
			color: {$bump_button_text_hover_color};
		}

		.wcf-bump-order-wrap.wcf-bump-order-{$ob_id}.wcf-bump-order-style-5 .wcf-bump-order-field-wrap .wcf-bump-order-action:not(.wcf-ob-action-button){
		    border-style: {$label_border_style};
			border-width: {$label_border_width}px;
			border-radius: {$label_border_radius}px;
			border-color: {$label_border_color};
		}

		.wcf-bump-order-wrap.wcf-bump-order-{$ob_id}.wcf-bump-order-style-5 .wcf-bump-order-field-wrap .wcf-bump-order-label label{
			color: {$bump_title_color};
		}

		.wcf-bump-order-wrap.wcf-{$order_bump_position}.wcf-bump-order-{$ob_id} .wcf-bump-order-image {
			max-width: {$bump_image_width}px;
		}

		img.emoji, img.wp-smiley {}
		";

	if ( ! empty( $bump_label_bg_color ) ) {
		$ob_css .= "
				.wcf-bump-order-wrap.wcf-bump-order-{$ob_id}.wcf-bump-order-style-5 .wcf-bump-order-field-wrap .wcf-bump-order-action:not(.wcf-ob-action-button){
					padding: 5px 10px;
				}
			";
	}

	if ( 'yes' === $enabled_bump_image_mobile ) {
		$ob_css .= "
				@media only screen and (max-width: 520px){
					.wcf-embed-checkout-form .wcf-bump-order-wrap.wcf-bump-order-{$ob_id} .wcf-bump-order-image {
					    display: block;
					    width:100%;
					}
				}
			";
	} else {

		$ob_css .= "
			@media only screen and (max-width: 768px){
					.wcf-embed-checkout-form .wcf-bump-order-wrap.wcf-bump-order-{$ob_id} .wcf-bump-order-image {
					    display: none;
					}
				}
		";
	}
}
