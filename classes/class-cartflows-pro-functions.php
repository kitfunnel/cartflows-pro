<?php
/**
 * Cartflows Functions.
 *
 * @package CARTFLOWS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Is custom checkout?
 *
 * @param int $checkout_id checkout ID.
 * @since 1.0.0
 */
function _is_wcf_optin_custom_fields( $checkout_id ) {

	$is_custom = wcf()->options->get_optin_meta_value( $checkout_id, 'wcf-optin-enable-custom-fields' );

	if ( 'yes' === $is_custom ) {

		return true;
	}

	return false;
}

/**
 * Get get step object.
 *
 * @param int $step_id current step ID.
 * @since 1.5.9
 */
function wcf_pro_get_step( $step_id ) {

	if ( ! isset( wcf_pro()->wcf_step_objs[ $step_id ] ) ) {

		wcf_pro()->wcf_step_objs[ $step_id ] = new Cartflows_Pro_Step_Factory( $step_id );
	}

	return wcf_pro()->wcf_step_objs[ $step_id ];
}

/**
 * Get ab test
 *
 * @param int $step_id current step ID.
 * @since 1.0.0
 */
function wcf_get_ab_test( $step_id ) {

	return new Cartflows_Pro_Ab_Test_Factory( $step_id );

}

/**
 * Get Current Step
 */
function wcf_get_current_step_type() {

	$current_step = '-';

	if ( wcf()->utils->is_step_post_type() ) {

		global $wcf_step;

		$current_step = $wcf_step->get_step_type();

	}

	return $current_step;

}


if ( ! function_exists( 'wcf_update_the_checkout_transient' ) ) {
	/**
	 * Update the transient.
	 *
	 * @param int $checkout_id checkout id.
	 */
	function wcf_update_the_checkout_transient( $checkout_id ) {

		$user_key        = WC()->session->get_customer_id();
		$cart_data       = WC()->cart->get_cart();
		$expiration_time = 30;

		if ( ! empty( $user_key ) && ! empty( $checkout_id ) ) {
			set_transient( 'wcf_user_' . $user_key . '_checkout_' . $checkout_id, $cart_data, $expiration_time * MINUTE_IN_SECONDS );
		}
	}
}

if ( ! function_exists( 'wcf_clean' ) ) {

	/**
	 * Clean variables using sanitize_text_field.
	 *
	 * @param string|array $var Data to sanitize.
	 * @return string|array
	 */
	function wcf_clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( 'wcf_clean', $var );
		} else {
			return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		}
	}
}

if ( ! function_exists( 'wcf_pro_filter_price' ) ) {

	/**
	 * Filter the price.
	 *
	 * @param int    $price price.
	 * @param int    $product_id current product ID.
	 * @param string $context context of action. Context can be view or edit.
	 *
	 * @access public
	 * @return float
	 */
	function wcf_pro_filter_price( $price, $product_id = false, $context = 'convert' ) {

		if ( $price ) {
			$price = apply_filters( 'cartflows_filter_display_price', floatval( $price ), $product_id, $context );
		}

		return $price;
	}
}

if ( ! function_exists( 'wcf_float_to_string' ) ) {

	/**
	 * Covert the given price into string.
	 *
	 * @param int $price price.
	 *
	 * @access public
	 * @return float
	 */
	function wcf_float_to_string( $price ) {

		if ( $price ) {
			$price = number_format( (string) $price, 2, '.', '' );
		}

		return $price;
	}
}

