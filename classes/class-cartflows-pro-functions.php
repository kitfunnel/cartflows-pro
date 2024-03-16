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
 * Check if cartflows starter activated.
 *
 * @since x.x.x
 */
function _is_cartflows_starter() {

	if ( defined( 'CARTFLOWS_PRO_PLUGIN_TYPE' ) && 'starter' === CARTFLOWS_PRO_PLUGIN_TYPE ) {
		return true;
	}

	return false;
}

/**
 * Check if cartflows plus activated.
 *
 * @since x.x.x
 */
function _is_cartflows_plus() {

	if ( defined( 'CARTFLOWS_PRO_PLUGIN_TYPE' ) && 'plus' === CARTFLOWS_PRO_PLUGIN_TYPE ) {
		return true;
	}

	return false;
}

/**
 * Check if cartflows pluspro activated.
 *
 * @since x.x.x
 */
function _is_cartflows_pluspro() {

	if ( defined( 'CARTFLOWS_PRO_PLUGIN_TYPE' ) && in_array( CARTFLOWS_PRO_PLUGIN_TYPE, array( 'plus', 'pro' ), true ) ) {
		return true;
	}

	return false;
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

		$user_key        = null !== WC()->session ? WC()->session->get_customer_id() : '';
		$cart_data       = null !== WC()->cart ? WC()->cart->get_cart() : '';
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

if ( ! function_exists( 'cartflows_woocommerce_order_review' ) ) {
	/**
	 * Wrapper for update order review. Always use this instead of woocommerce_order_review().
	 */
	function cartflows_woocommerce_order_review() {

		$checkout_id = _get_wcf_checkout_id();

		if ( ! $checkout_id ) {

			$checkout_id = isset( $_GET['wcf_checkout_id'] ) && ! empty( $_GET['wcf_checkout_id'] ) ? intval( wp_unslash( $_GET['wcf_checkout_id'] ) ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$checkout_layout = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-layout' );

		if ( 'multistep-checkout' === $checkout_layout ) {

			ob_start();
			Cartflows_Pro_Multistep_Checkout::get_instance()->custom_order_review_template();
			return ob_get_clean();

		}

		ob_start();
		woocommerce_order_review();
		return ob_get_clean();

	}
}

if ( ! function_exists( 'wcf_print_r' ) ) {
	/**
	 * Prints human-readable information about a variable.
	 *
	 * Some server environments block some debugging functions. This function provides a safe way to
	 * turn an expression into a printable, readable form without calling blocked functions.
	 *
	 * @param mixed $expression The expression to be printed.
	 * @param bool  $return     Optional. Default false. Set to true to return the human-readable string.
	 * @return string|bool False if expression could not be printed. True if the expression was printed.
	 *     If $return is true, a string representation will be returned.
	 */
	function wcf_print_r( $expression, $return = false ) {
		$alternatives = array(
			array(
				'func' => 'print_r',
				'args' => array( $expression, true ),
			),
			array(
				'func' => 'var_export',
				'args' => array( $expression, true ),
			),
			array(
				'func' => 'json_encode',
				'args' => array( $expression ),
			),
			array(
				'func' => 'serialize',
				'args' => array( $expression ),
			),
		);

		$alternatives = apply_filters( 'cartflows_print_r_alternatives', $alternatives, $expression );

		foreach ( $alternatives as $alternative ) {
			if ( function_exists( $alternative['func'] ) ) {
				$res = $alternative['func']( ...$alternative['args'] );
				if ( $return ) {
					return $res;
				}

				echo $res; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				return true;
			}
		}

		return false;
	}
}

