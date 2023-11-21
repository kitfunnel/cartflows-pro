<?php
/**
 * Cartflows Helper.
 *
 * @package CARTFLOWS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Helper.
 */
class Cartflows_Pro_Helper {

	/**
	 * Offer settings data
	 *
	 * @var zapier
	 */
	private static $offer_settings = null;

	/**
	 * Abtest settings data
	 *
	 * @var array
	 */
	private static $abtest_settings = null;

	/**
	 * Get zapier settings.
	 *
	 * @return  array.
	 */
	public static function get_offer_global_settings() {

		if ( null === self::$offer_settings ) {

			$settings_default = apply_filters(
				'cartflows_offer_global_settings',
				array(
					'separate_offer_orders' => 'separate',
				)
			);

			$offer_settings = Cartflows_Helper::get_admin_settings_option( '_cartflows_offer_global_settings', false, false );

			$offer_settings = wp_parse_args( $offer_settings, $settings_default );

			if ( ! did_action( 'wp' ) ) {
				return $offer_settings;
			} else {
				self::$offer_settings = $offer_settings;
			}
		}

		return self::$offer_settings;
	}

	/**
	 * Get AB Test settings.
	 *
	 * @return  array.
	 */
	public static function get_abtest_settings() {

		if ( null === self::$abtest_settings ) {

			$settings_default = apply_filters(
				'cartflows_abtest_settings',
				array(
					'override_permalink' => 'disable',
				)
			);

			$abtest_settings = Cartflows_Helper::get_admin_settings_option( '_cartflows_abtest_settings', false, false );

			$abtest_settings = wp_parse_args( $abtest_settings, $settings_default );

			if ( ! did_action( 'wp' ) ) {
				return $abtest_settings;
			} else {
				self::$abtest_settings = $abtest_settings;
			}
		}

		return self::$abtest_settings;
	}

	/**
	 * Create Edit page link for the widgets.
	 *
	 * @since 1.6.13
	 * @param string $tab The Tab which has to display.
	 * @access public
	 */
	public static function get_current_page_edit_url( $tab ) {

		global $post;

		$url = '';

		if ( $post ) {
			$step_id = $post->ID;
			$flow_id = wcf()->utils->get_flow_id_from_step_id( $step_id );
			$url     = add_query_arg(
				array(
					'flow_id' => $flow_id,
					'step_id' => $step_id,
					'tab'     => $tab,
				),
				admin_url( 'admin.php?page=cartflows&action=wcf-edit-step' )
			);
		}
		return $url;
	}

	/**
	 * Create setting page URL.
	 *
	 * @since 1.6.13
	 * @access public
	 */
	public static function get_setting_page_url() {

		$admin_url = add_query_arg(
			array(
				'page' => 'cartflows_settings',
			),
			admin_url( 'admin.php' )
		);

		return $admin_url;
	}

	/**
	 * Add Checkout field.
	 *
	 * @param string $type Field type.
	 * @param string $field_key Field key.
	 * @param int    $post_id Post id.
	 * @param array  $field_data Field data.
	 * @return  boolean.
	 */
	public static function add_checkout_field( $type, $field_key, $post_id, $field_data = array() ) {

		$fields = Cartflows_Helper::get_checkout_fields( $type, $post_id );

		$fields[ $field_key ] = $field_data;

		if ( CARTFLOWS_STEP_POST_TYPE === get_post_type( $post_id ) ) {
			update_post_meta( $post_id, 'wcf_fields_' . $type, $fields );
		}

		return true;
	}

	/**
	 * Delete checkout field.
	 *
	 * @param string $type Field type.
	 * @param string $field_key Field key.
	 * @param int    $post_id Post id.
	 * @return  array.
	 */
	public static function delete_checkout_field( $type, $field_key, $post_id ) {

		$fields = Cartflows_Helper::get_checkout_fields( $type, $post_id );

		if ( isset( $fields[ $field_key ] ) ) {
			unset( $fields[ $field_key ] );
		}

		if ( CARTFLOWS_STEP_POST_TYPE === get_post_type( $post_id ) ) {
			update_post_meta( $post_id, 'wcf_fields_' . $type, $fields );
		}
		return true;
	}

	/**
	 * Check is error in the received response.
	 *
	 * @param object $response Received API Response.
	 * @return array $result Error result.
	 * @since x.x.x
	 */
	public static function is_api_errors( $response ) {

		$result = array(
			'is_error'      => false,
			'error_message' => __( 'No error found.', 'cartflows-pro' ),
			'error_code'    => 0,
		);

		if ( is_wp_error( $response ) ) {

			$msg        = $response->get_error_message();
			$error_code = $response->get_error_code();

			if ( 'http_request_failed' === $error_code ) {
				$msg = $msg . '<br>' . __( 'API call to create a purchase failed.', 'cartflows-pro' );
			}

			$result['is_error']      = true;
			$result['error_message'] = $msg;
			$result['error_code']    = $error_code;

		} elseif ( ! $response->isSuccess() ) {

			$error_body = $response->getErrors();

			$result['is_error']      = true;
			$result['error_message'] = $error_body;
			$result['error_code']    = $response->getStatusCode();
		} else {
			$result['error_code'] = $response->getStatusCode();
		}

		return $result;
	}

	/**
	 * Show product option settings based on consitions.
	 *
	 * @param int $checkout_id checkout id.
	 */
	public static function is_show_product_options_settings( $checkout_id ) {

		$store_checkout = (int) Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' );
		$flow_id        = (int) wcf()->utils->get_flow_id_from_step_id( $checkout_id );

		if ( $flow_id !== $store_checkout ) {
			return true;
		}

		if ( $flow_id === $store_checkout && apply_filters( 'cartflows_show_store_checkout_product_tab', false ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Confirm if custom price is valid.
	 *
	 * @param int $custom_price custom price.
	 */
	public static function is_valid_custom_price( $custom_price ) {

		if ( $custom_price >= 0 && '' !== $custom_price ) {
			return true;
		}

		return false;
	}
}
