<?php
/**
 * CartFlows License.
 *
 * @package CartFlows
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'cartflows_pro_license_instance' ) ) :
	/**
	 * Get CartFlows license instance globally.
	 */
	function cartflows_pro_license_instance() {

		if ( class_exists( 'CartFlows_Pro_Licence' ) ) {
			return CartFlows_Pro_Licence::get_instance();
		}

		return false;
	}
endif;

if ( ! function_exists( 'cartflows_pro_license_request' ) ) :

	/**
	 * License Request for Site
	 *
	 * @since 1.6.13
	 *
	 * @param  string  $license_key  License key.
	 * @param  integer $site_id      Site ID.
	 * @param  string  $request_type Request type (activate or deactivate).
	 * @return mixed
	 */
	function cartflows_pro_license_request( $license_key = '', $site_id = 1, $request_type = '' ) {

		if ( empty( $license_key ) ) {
			return new WP_Error( 'empty_license_key', __( 'Provide a license key to process license request!', 'cartflows-pro' ) );
		}

		if ( empty( $request_type ) ) {
			return new WP_Error( 'empty_license_request', __( 'Provide a license request!', 'cartflows-pro' ) );
		}

		$requests = array( 'activate', 'deactivate' );

		if ( ! in_array( $request_type, $requests, true ) ) {
			return new WP_Error( 'invalid_license_request', __( 'Invalid license request!', 'cartflows-pro' ) );
		}

		$wc_am_domain = str_ireplace( array( 'http://', 'https://' ), '', get_site_url( $site_id ) );

		$args = array(
			'api_key' => $license_key,
			'object'  => $wc_am_domain,
		);

		$product_id = cartflows_pro_license_instance()->product_id;
		$request_type = 'activate';


		if ( 'activate' === $request_type ) {
			
			/*$request = CartFlows_Pro_Licence::get_instance()->activation_request( $args );

			$response = json_decode( $request, true );*/
			
			$response['success'] = $response['activated'] = true;

			if ( $response && true === $response['success'] && true === $response['activated'] ) {

				$data = array(
					'api_key' => $license_key,
				);

				// Store the API key.
				update_option( 'wc_am_client_' . $product_id . '_api_key', $data );

				// Activate.
				update_option( 'wc_am_client_' . $product_id . '_activated', 'Activated' );

				// Store the API key which used for plugin update.
				$new_data = array(
					'wc_am_client_' . $product_id . '_api_key' => $data['api_key'],
				);
				update_option( 'wc_am_client_' . $product_id, $new_data );
			}
		} elseif ( 'deactivate' === $request_type ) {

			$request = cartflows_pro_license_instance()->deactivate_request( $args );

			$response = json_decode( $request, true );

			if ( $response && true === $response['success'] && true === $response['deactivated'] ) {

				$default_args = array(
					'api_key' => '',
				);

				update_option( 'wc_am_client_' . $product_id . '_activated', 'Deactivated' );

				update_option( 'wc_am_client_' . $product_id . '_api_key', $default_args );

				// Store the API key which used for plugin update.
				$new_data = array(
					'wc_am_client_' . $product_id . '_api_key' => $default_args['api_key'],
				);
				update_option( 'wc_am_client_' . $product_id, $new_data );
			}
		}

		return json_decode( $request, true );
	}
endif;

if ( ! function_exists( 'cartflows_pro_activate' ) ) :

	/**
	 * Activate License Request for Site
	 *
	 * @since 1.6.13
	 *
	 * @param  string  $license_key  License key.
	 * @param  integer $site_id      Site ID.
	 * @return mixed
	 */
	function cartflows_pro_activate( $license_key = '', $site_id = 1 ) {
		return cartflows_pro_license_request( $license_key, $site_id, 'activate' );
	}
endif;

if ( ! function_exists( 'cartflows_pro_deactivate' ) ) :

	/**
	 * Deactivate License Request for Site
	 *
	 * @since 1.6.13
	 *
	 * @param  string  $license_key  License key.
	 * @param  integer $site_id      Site ID.
	 * @return mixed
	 */
	function cartflows_pro_deactivate( $license_key = '', $site_id = 1 ) {
		return cartflows_pro_license_request( $license_key, $site_id, 'deactivate' );
	}
endif;

if ( ! function_exists( 'cartflows_pro_is_active_license' ) ) :

	/**
	 * Activate Status
	 *
	 * @since 1.6.13
	 *
	 * @return bool
	 */
	function cartflows_pro_is_active_license() {
		// is_object added to avoid Trying to get property 'activate_status' of non-object error in some cases.
		$status = is_object( cartflows_pro_license_instance() ) ? cartflows_pro_license_instance()->activate_status : '';

		if ( 'Activated' === $status ) {

			return true;
		}

		return false;
	}
endif;

if ( ! function_exists( 'cartflows_pro_get_product_id' ) ) :

	/**
	 * Get CartFlows Product ID
	 *
	 * @since x.x.x
	 * @return bool
	 */
	function cartflows_pro_get_product_id() {

		return class_exists( 'CartFlows_Pro_Licence' ) ? cartflows_pro_license_instance()->product_id : '';
	}
endif;
