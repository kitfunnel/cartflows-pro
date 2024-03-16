<?php
/**
 * Title and Subtext
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="wcf-item-wrap">
<?php

$actual_product_name = $rc_product_obj->get_name();
$display_title       = '';

if ( ! empty( $data['product_name'] ) ) {
	$display_title = $data['product_name'];
} else {
	$display_title = '{{product_name}}{{quantity}}';
}

if ( $data['variable'] ) {

	$actual_product_name = $current_product->get_name();
	$attribute_data      = $this->get_selected_attributes( $current_product, $rc_product_obj );

	if ( is_array( $attribute_data ) && ! empty( $attribute_data ) ) {

		$display_title .= '<div class="wcf-display-attributes">';
		foreach ( $attribute_data as $att_slug => $att_data ) {

			$att_value = $att_data['value'];

			if ( empty( $att_value ) ) {
				$att_value = '<a class="wcf-select-variation-attribute wcf-invalid-variation" href="#"><span>' . __( 'Select', 'cartflows-pro' ) . '</span></a>';
			}

			$display_title .= '<span class="wcf-att-inner">';
			$display_title .= $att_data['label'] . ': ' . $att_value;
			$display_title .= '<span class="wcf-att-sep">,</span>';
			$display_title .= '</span>';
		}
		$display_title .= '</div>';
	}
}

if ( in_array( $price_sel_data['type'], array( 'subscription', 'variable-subscription', 'subscription_variation' ), true ) ) {

	$product = $rc_product_obj;

	if ( isset( $data['variable-subscription'] ) && $data['variable-subscription'] ) {
		$product = wc_get_product( $price_sel_data['variation_id'] );
	}

	$display_title     .= '<div class="wcf-display-subscription-details">';
		$display_title .= '<span class="wcf_subscription_price">' . wc_price( $price_sel_data['subscription_price'] * $price_sel_data['quantity'] ) . '</span>';
		$display_title .= wcf_pro()->is_wcs_active ? '<span class="wcf_subscription_period">' . __( ' every ', 'cartflows-pro' ) . wcs_get_subscription_period_strings( WC_Subscriptions_Product::get_interval( $product ), WC_Subscriptions_Product::get_period( $product ) ) . '</span>' : '';
		$display_title .= '<span class="wcf_subscription_free_trial">' . $price_sel_data['trial_period_string'] . '</span>';

	if ( ! empty( $price_sel_data['sign_up_fee'] ) ) {
		$display_title .= '<span class="wcf_subscription_fee_text">' . __( ' and a ', 'cartflows-pro' );
		$display_title .= '<span class="wcf_subscription_fee">' . wc_price( $price_sel_data['sign_up_fee'] * $price_sel_data['quantity'] ) . '</span>';
		$display_title .= __( ' sign-up fee ', 'cartflows-pro' ) . '</span>';
	}

	$display_title .= '</div>';
}

$to_replace = array(
	'{{product_name}}',
	'{{quantity}}',
);

$with_replace = array(
	'<span class="wcf-display-title">' . $actual_product_name . '</span>',
	'<span class="wcf-display-title-quantity"><span class="dashicons dashicons-no-alt"></span><span class="wcf-display-quantity">' . $data['quantity'] . '</span></span>',
);

echo wp_kses_post( str_replace( $to_replace, $with_replace, $display_title ) );
?>
</div>

