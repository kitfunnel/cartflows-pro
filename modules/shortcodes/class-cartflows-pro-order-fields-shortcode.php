<?php
/**
 * Order Fields
 *
 * @package cartflows-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Initial Setup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Order_Fields_Shortcode {

	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  Constructor
	 */
	public function __construct() {

		/* Order fields shortcode */
		add_shortcode( 'cartflows_order_fields', array( $this, 'order_fields_markup' ) );
		add_shortcode( 'cartflows_url_fields', array( $this, 'url_fields_markup' ) );
	}

	/**
	 * Order details shortcode markup.
	 *
	 * @param array $atts attributes ( $atts['key'] can be first_name, last_name, email, phone, city ).
	 * @return string
	 */
	public function order_fields_markup( $atts ) {

		$output = '';

		if ( ! wcf()->utils->is_step_post_type() ) {
			return $output;
		}

		$def_val = isset( $atts['default'] ) ? sanitize_text_field( $atts['default'] ) : '';

		/* Set default value */
		$output = $def_val;

		$order_id  = isset( $_GET['wcf-order'] ) ? intval( $_GET['wcf-order'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order_key = isset( $_GET['wcf-key'] ) ? sanitize_text_field( wp_unslash( $_GET['wcf-key'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! empty( $order_key ) && ! empty( $order_id ) ) {

			$order = wc_get_order( $order_id );

			$field = isset( $atts['field'] ) ? sanitize_text_field( $atts['field'] ) : '';

			// Validate order key.
			if ( ! $order || $order->get_order_key() !== $order_key ) {
				return $output;
			}

			$order_data = $order->get_data();
			$type       = isset( $atts['type'] ) ? sanitize_text_field( $atts['type'] ) : 'billing';

			if ( 'billing' === $type || 'shipping' === $type ) {

				$details = isset( $order_data[ $type ] ) ? $order_data[ $type ] : array();

				if ( '' !== $field ) {

					if ( ! empty( $details ) && isset( $details[ $field ] ) && '' !== $details[ $field ] ) {
						$output = $details[ $field ];
					}
				}
			} elseif ( 'total' === $type ) {

				$field_val = isset( $order_data[ $type ] ) ? $order_data[ $type ] : '';

				if ( '' !== $field_val ) {

					$output = wc_price( $field_val, array( 'decimals' => 0 ) );
				}
			}
		}

		return $output;
	}

	/**
	 * Optin details shortcode markup.
	 *
	 * @param array $atts attributes ( $atts['key'] can be first_name, last_name, email ).
	 * @return string
	 */
	public function url_fields_markup( $atts ) {

		$output = '';

		$def_val = isset( $atts['default'] ) ? sanitize_text_field( $atts['default'] ) : '';

		$output = $def_val;

		$field = isset( $atts['field'] ) ? sanitize_text_field( $atts['field'] ) : '';

		if ( ! empty( $field ) && isset( $_GET[ $field ] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$url_param = isset( $_GET[ $field ] ) ? sanitize_text_field( wp_unslash( $_GET[ $field ] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( ! empty( $url_param ) ) {

				$output = $url_param;
			}
		}
		return $output;
	}
}


/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Order_Fields_Shortcode::get_instance();
