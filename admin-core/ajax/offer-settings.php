<?php
/**
 * CartFlows Flows ajax actions.
 *
 * @package CartFlows
 */

namespace CartflowsProAdmin\AdminCore\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use CartflowsProAdmin\AdminCore\Ajax\AjaxBase;

/**
 * Class Flows.
 */
class OfferSettings extends AjaxBase {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register_ajax_events.
	 *
	 * @return void
	 */
	public function register_ajax_events() {

		if ( current_user_can( 'cartflows_manage_settings' ) ) {

			$ajax_events = array(
				'save_miscellaneous_settings',
			);

			$this->init_ajax_events( $ajax_events );
		}
	}

	/**
	 * Save settings.
	 *
	 * @return void
	 */
	public function save_miscellaneous_settings() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_save_miscellaneous_settings', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		if ( isset( $_POST ) ) {

			if ( isset( $_POST['_cartflows_offer_global_settings'] ) ) {
				// Loop through the input and sanitize each of the values.
				// We are sanitizing inputs using our sanitization function. Hence ignoring sanitization rule.
				$new_settings = \Cartflows_Pro_Admin_Helper::sanitize_form_inputs( wp_unslash( $_POST['_cartflows_offer_global_settings'] ) ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				\Cartflows_Helper::update_admin_settings_option( '_cartflows_offer_global_settings', $new_settings, false );
			}

			if ( isset( $_POST['_cartflows_abtest_settings'] ) ) {
				// Loop through the input and sanitize each of the values.
				$new_settings = \Cartflows_Pro_Admin_Helper::sanitize_form_inputs( wp_unslash( $_POST['_cartflows_abtest_settings'] ) ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				\Cartflows_Helper::update_admin_settings_option( '_cartflows_abtest_settings', $new_settings, false );
			}

			if ( isset( $_POST['_cartflows_experimental_flow_analytics'] ) ) {
				$new_settings = sanitize_text_field( wp_unslash( $_POST['_cartflows_experimental_flow_analytics'] ) );
				\Cartflows_Helper::update_admin_settings_option( '_cartflows_experimental_flow_analytics', $new_settings, false );
			}
		}

		$response_data = array(
			'messsage' => __( 'Successfully saved data!', 'cartflows-pro' ),
		);
		wp_send_json_success( $response_data );
	}
}
