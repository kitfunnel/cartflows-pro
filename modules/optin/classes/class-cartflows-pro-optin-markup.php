<?php
/**
 * Checkout markup.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Checkout Markup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Optin_Markup {



	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var is_divi_enabled
	 */
	public $divi_status = false;

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

		add_filter( 'woocommerce_email_order_meta_fields', array( $this, 'send_custom_fields_in_woo_email' ), 10, 3 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_billing_custom_fields_in_order_meta' ), 10, 1 );

		/**
		 * Optin fields modification
		 * add_filter( 'cartflows_optin_default_fields', array( $this, 'optin_default_fields' ), 10, 2 );
		 * add_filter( 'cartflows_optin_fields', array( $this, 'optin_fields' ), 10, 2 ); */

		/* Scripts */
		add_action( 'cartflows_optin_scripts', array( $this, 'optin_style_scripts' ) );
	}

	/**
	 * Send custom fields in the order email.
	 *
	 * @param array  $fields of fields.
	 * @param string $sent_to_admin domain name to send.
	 * @param object $order of order details.
	 */
	public function send_custom_fields_in_woo_email( $fields, $sent_to_admin, $order ) {

		// Return if order not found.
		if ( ! $order ) {
			return $fields;
		}

		$optin_id = $order->get_meta( '_wcf_optin_id' );

		if ( ! $optin_id ) {
			return $fields;
		}

		// Get custom fields.
		$custom_fields = get_post_meta( $optin_id, 'wcf-optin-enable-custom-fields', true );

		if ( 'yes' === $custom_fields ) {
			// Billing Fields & Values.
			$billing_fields       = get_post_meta( $optin_id, 'wcf_fields_billing', true );
			$saved_billing_fields = get_post_meta( $optin_id, 'wcf-optin-fields-billing', true );
			if ( is_array( $billing_fields ) ) {
				foreach ( $billing_fields as $field => $data ) {
					if ( isset( $saved_billing_fields[ $field ] ) && isset( $data['custom'] ) && $data['custom'] && ( isset( $saved_billing_fields[ $field ]['show_in_email'] ) && $saved_billing_fields[ $field ]['show_in_email'] ) ) {
						$fields[ $field ] = array(
							'label' => $data['label'],
							'value' => $order->get_meta( '_' . $field ),
						);
					}
				}
			}
		}

		return $fields;
	}


	/**
	 * Display billing custom field data on order page
	 *
	 * @param obj $order Order object.
	 * @return void
	 */
	public function display_billing_custom_fields_in_order_meta( $order ) {

		if ( ! $order ) {
			return;
		}

		$optin_id = $order->get_meta( '_wcf_optin_id' );

		if ( ! $optin_id ) {
			return;
		}

		/* Custom Field To Do */
		$custom_fields = get_post_meta( $optin_id, 'wcf-optin-enable-custom-fields', true );

		if ( 'yes' === $custom_fields ) {
			$output = '';

			$saved_billing_fields = get_post_meta( $optin_id, 'wcf-optin-fields-billing', true );
			$billing_fields       = get_post_meta( $optin_id, 'wcf_fields_billing', true );
			if ( is_array( $billing_fields ) ) {
				foreach ( $billing_fields as $field => $data ) {

					if ( isset( $saved_billing_fields[ $field ] ) && isset( $data['custom'] ) && $data['custom'] ) {
						$output .= '<p><strong>' . esc_html( $data['label'] ) . ':</strong> ' . esc_html( $order->get_meta( '_' . $field ) ) . '</p>';
					}
				}
			}

			if ( '' !== $output ) {
				$output = '<h3>' . __( 'Billing Custom Fields', 'cartflows-pro' ) . '</h3>' . $output;
			}

			echo wp_kses_post( $output );
		}
	}

	/**
	 * Load shortcode scripts.
	 *
	 * @return void
	 */
	public function optin_style_scripts() {

		global $post;

		if ( Cartflows_Compatibility::get_instance()->is_divi_enabled() ||
			Cartflows_Compatibility::get_instance()->is_divi_builder_enabled( $post->ID )
		) {
			$this->divi_status = true;
		}

		wp_enqueue_style( 'wcf-pro-optin', wcf_pro()->utils->get_css_url( 'optin-styles' ), '', CARTFLOWS_PRO_VER );

		wp_enqueue_script(
			'wcf-pro-optin',
			wcf_pro()->utils->get_js_url( 'optin' ),
			array( 'jquery' ),
			CARTFLOWS_PRO_VER,
			true
		);

		// Add DIVI Compatibility css if DIVI theme is enabled.
		if ( $this->divi_status ) {
			wp_enqueue_style( 'wcf-optin-styles-divi', wcf_pro()->utils->get_css_url( 'optin-styles-divi' ), '', CARTFLOWS_PRO_VER );
		}
	}

	/**
	 * Prepare default country locale.
	 *
	 * @param array $fields country locale fields.
	 * @param int   $optin_id checkout id.
	 * @return array
	 */
	public function optin_default_fields( $fields, $optin_id ) {

		if ( ! _is_wcf_optin_custom_fields( $optin_id ) ) {
			return $fields;
		}

		$optin_fields = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-optin-fields-billing' );

		if ( ! is_array( $optin_fields ) ) {
			return $fields;
		}

		if ( ! empty( $optin_fields ) && ! empty( $fields ) ) {

			foreach ( $fields as $name => $field ) {

				$fname = 'billing_' . $name;

				$custom_field = isset( $optin_fields[ $fname ] ) ? $optin_fields[ $fname ] : false;

				if ( $custom_field && ! ( isset( $custom_field['enabled'] ) && false == $custom_field['enabled'] ) ) {
					$fields[ $name ]['required'] = isset( $custom_field['required'] ) && $custom_field['required'] ? true : false;
				}
			}
		}

		return $fields;
	}

	/**
	 * Prepare default country locale.
	 *
	 * @param array $fields country locale fields.
	 * @param int   $optin_id checkout id.
	 * @return array
	 */
	public function optin_fields( $fields, $optin_id ) {

		if ( ! _is_wcf_optin_custom_fields( $optin_id ) ) {
			return $fields;
		}

		$optin_fields = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-optin-fields-billing' );

		if ( ! is_array( $optin_fields ) ) {

			return $fields;
		}

		if ( ! empty( $optin_fields ) && ! empty( $fields ) ) {

			$override_required = apply_filters( 'wcf_address_field_override_required', true );

			foreach ( $fields as $name => $field ) {
				$fname = $sname . '_' . $name;

				if ( $this->_is_locale_field( $fname ) && $override_required ) {
					$custom_field = isset( $address_fields[ $fname ] ) ? $address_fields[ $fname ] : false;

					if ( $custom_field && ! ( isset( $custom_field['enabled'] ) && false == $custom_field['enabled'] ) ) {
						$fields[ $name ]['required'] = isset( $custom_field['required'] ) && $custom_field['required'] ? true : false;
					}
				}
			}
		}

		return $fields;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Optin_Markup::get_instance();
