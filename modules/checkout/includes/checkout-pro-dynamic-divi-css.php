<?php
/**
 * Dynamic checkout divi css
 *
 * @package cartflows
 */

// phpcs:ignore

$output .= "

	#wcf-quick-view-content{
		font-family: {$base_font_family};
	}
	#wcf-quick-view-content .summary-content .product_title{
		color: {$section_heading_color};
		font-family: {$heading_font_family};
	    font-weight: {$heading_font_weight};
	}
	#wcf-quick-view-content .summary-content .variations select {
		color: {$field_color};
		background: {$field_bg_color};
		border-color: {$field_border_color};
		padding-top: {$field_tb_padding}px;
		padding-bottom: {$field_tb_padding}px;
		padding-left: {$field_lr_padding}px;
		padding-right: {$field_lr_padding}px;
		min-height: {$field_input_size};
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}
	#wcf-quick-view-content .summary-content .single_variation_wrap .woocommerce-variation-add-to-cart button{
		color: {$submit_color};
		background: {$submit_bg_color};
		padding-top: {$submit_tb_padding}px;
		padding-bottom: {$submit_tb_padding}px;
		padding-left: {$submit_lr_padding}px;
		padding-right: {$submit_lr_padding}px;
		border-color: {$submit_border_color};
		min-height: {$submit_button_height};
		font-family: {$button_font_family};
	    font-weight: {$button_font_weight};
	}
	#wcf-quick-view-content .summary-content a{
		color: {$primary_color};
	}
	#wcf-quick-view-content .summary-content .woocommerce-product-rating .star-rating, 
	#wcf-quick-view-content .summary-content .woocommerce-product-rating .comment-form-rating .stars a, 
	#wcf-quick-view-content .summary-content .woocommerce-product-rating .star-rating::before{
	    color: {$primary_color};
	}
	.et_pb_module #wcf-embed-checkout-form.wcf-bump-order-wrap.wcf-before-checkout .wcf-bump-order-field-wrap input[type=checkbox]:checked:before, .et_pb_module #wcf-embed-checkout-form.wcf-bump-order-wrap.wcf-after-customer .wcf-bump-order-field-wrap input[type=checkbox]:checked:before, .et_pb_module #wcf-embed-checkout-form.wcf-bump-order-wrap.wcf-before-checkout .wcf-bump-order-field-wrap input[type=checkbox]:checked:before, .et_pb_module #wcf-embed-checkout-form.wcf-product-option-wrap .wcf-qty-row div [type='checkbox']:checked:before {
		color: {$primary_color};
	}
	.et_pb_module #wcf-embed-checkout-form.wcf-product-option-wrap .wcf-qty-row input[type=radio]:checked:before{
		background-color:{$primary_color};
	}
	.et_pb_module #wcf-embed-checkout-form.wcf-bump-order-wrap.wcf-before-checkout .wcf-bump-order-field-wrap input[type=checkbox]:focus,
	.et_pb_module #wcf-embed-checkout-form.wcf-bump-order-wrap.wcf-after-customer .wcf-bump-order-field-wrap input[type=checkbox]:focus,
	.et_pb_module #wcf-embed-checkout-form.wcf-bump-order-wrap.wcf-before-checkout .wcf-bump-order-field-wrap input[type=checkbox]:focus,
	.et_pb_module #wcf-embed-checkout-form.wcf-product-option-wrap .wcf-qty-row div [type='checkbox']:focus,
	.et_pb_module #wcf-embed-checkout-form.wcf-product-option-wrap .wcf-qty-row div [type='radio']:checked:focus,
	.et_pb_module #wcf-embed-checkout-form.wcf-product-option-wrap .wcf-qty-row div [type='radio']:not(:checked):focus{
		border-color: {$primary_color};
		box-shadow: 0 0 2px rgba( " . $r . ', ' . $g . ', ' . $b . ", .8);
	}
	.et_pb_module #wcf-embed-checkout-form.woocommerce-checkout #your_products_heading{
		color: {$section_heading_color};
		font-family: {$heading_font_family};
	    font-weight: {$heading_font_weight};
	}
	img.emoji, img.wp-smiley {}
	";

/* Add css to your order table when variation is enabled*/
if ( 'yes' == $enable_product_options ) {
	$output .= "
	.et_pb_module #wcf-embed-checkout-form .wcf-yp-skin-classic .wcf-qty-options,
	.et_pb_module #wcf-embed-checkout-form .wcf-yp-skin-cards .wcf-qty-row {
		background-color:{$yp_bg_color};
		color:{$yp_text_color};
	}
	.et_pb_module #wcf-embed-checkout-form .wcf-qty-options .wcf-highlight {
		color:{$yp_hl_text_color};
	  	background-color: {$yp_hl_bg_color};
	  	border-color: {$yp_hl_border_color};
	}
	.et_pb_module #wcf-embed-checkout-form .wcf-qty-options .wcf-highlight-head{
		background: {$yp_flag_bg_color};
		color: {$yp_flag_text_color};
		font-family: {$input_font_family};
	}
	/*.et_pb_module #wcf-embed-checkout-form table.shop_table td:first-child,
	.et_pb_module #wcf-embed-checkout-form table.shop_table th:first-child{
	    text-align: left;
	}
	.et_pb_module #wcf-embed-checkout-form table.shop_table td:last-child,
	.et_pb_module #wcf-embed-checkout-form table.shop_table th:last-child{
	    text-align: right;
	}*/
	img.emoji, img.wp-smiley {}";
}

/* For two Step Layout start */

if ( 'two-step' === $checkout_layout ) {

	$output .= "
		.et_pb_module #wcf-embed-checkout-form.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note{
		    border-color: {$two_step_box_bg_color} !important;
		    background-color: {$two_step_box_bg_color} !important;
		    color: {$two_step_box_text_color} !important;
		}
		.et_pb_module #wcf-embed-checkout-form.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note:before{
			border-top-color:{$two_step_box_bg_color} !important;
		}
		.et_pb_module #wcf-embed-checkout-form.wcf-embed-checkout-form-two-step{
			max-width: {$step_two_width}px;
		}
		.et_pb_module #wcf-embed-checkout-form.wcf-embed-checkout-form-two-step .woocommerce{
			border-left-style:{$two_step_section_border};
		    border-right-style:{$two_step_section_border};
		    border-bottom-style:{$two_step_section_border};
		}

		.et_pb_module #wcf-embed-checkout-form.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-nav{
			border-top-style: {$two_step_section_border};
			border-left-style: {$two_step_section_border};
			border-right-style: {$two_step_section_border};
		}

		.et_pb_module #wcf-embed-checkout-form.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps .steps.wcf-current:before{
			background-color: {$primary_color};
		}
		.et_pb_module #wcf-embed-checkout-form.wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button{
			color: {$submit_color} !important;
			background-color: {$submit_bg_color} !important;
			padding-top: {$submit_tb_padding}px;
			padding-bottom: {$submit_tb_padding}px;
			padding-left: {$submit_lr_padding}px;
			padding-right: {$submit_lr_padding}px;
			border-color: {$submit_border_color} !important;
			min-height: {$submit_button_height};
			font-family: {$button_font_family};
		    font-weight: {$button_font_weight};
		}
		.et_pb_module #wcf-embed-checkout-form.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-nav-btns .wcf-next-button:hover{
			color: {$submit_hover_color} !important;
			background-color: {$submit_bg_hover_color} !important;
			border-color: {$submit_border_hover_color} !important;
		}
		.et_pb_module #wcf-embed-checkout-form.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps .wcf-current .step-name{
			color: {$section_heading_color} !important;
		}
	";
}
/* For two Step Layout close */
