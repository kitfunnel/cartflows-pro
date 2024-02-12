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
use CartflowsProAdmin\AdminCore\Inc\AdminHelper;


/**
 * Class Flows.
 */
class FormFields extends AjaxBase {

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

		$ajax_events = array(
			'prepare_custom_field',
			'delete_custom_field',
		);

		$this->init_ajax_events( $ajax_events );
	}

	/**
	 * Prepare custom field.
	 *
	 * @return void
	 */
	public function prepare_custom_field() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_prepare_custom_field', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$add_to  = isset( $_POST['add_to'] ) ? sanitize_text_field( wp_unslash( $_POST['add_to'] ) ) : '';
		$type    = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
		$options = isset( $_POST['options'] ) ? sanitize_text_field( wp_unslash( $_POST['options'] ) ) : '';
		$label   = isset( $_POST['label'] ) ? sanitize_text_field( wp_unslash( $_POST['label'] ) ) : '';
		// We need to replace the whhitespace with underscore,hence we are sanitizing input late.
		$name          = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( str_replace( ' ', '_', $_POST['name'] ) ) ) : ''; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$placeholder   = isset( $_POST['placeholder'] ) ? sanitize_text_field( wp_unslash( $_POST['placeholder'] ) ) : '';
		$width         = isset( $_POST['width'] ) ? sanitize_text_field( wp_unslash( $_POST['width'] ) ) : '';
		$default_value = isset( $_POST['default'] ) ? sanitize_text_field( wp_unslash( $_POST['default'] ) ) : '';
		$optimized     = isset( $_POST['optimized'] ) ? sanitize_text_field( wp_unslash( $_POST['optimized'] ) ) : 'no';
		$show_in_email = isset( $_POST['show_in_email'] ) ? sanitize_text_field( wp_unslash( $_POST['show_in_email'] ) ) : 'yes';
		$is_required   = isset( $_POST['required'] ) ? sanitize_text_field( wp_unslash( $_POST['required'] ) ) : 'no';

		$save_field_name = isset( $_POST['save_field_name'] ) ? sanitize_text_field( wp_unslash( $_POST['save_field_name'] ) ) : '';
		$min_value       = isset( $_POST['min'] ) ? intval( $_POST['min'] ) : '';
		$max_value       = isset( $_POST['max'] ) ? intval( $_POST['max'] ) : '';
		$date_input      = isset( $_POST['date_input'] ) ? sanitize_text_field( wp_unslash( $_POST['date_input'] ) ) : 'datetime-local';

		$min_date = isset( $_POST['min_date'] ) ? sanitize_text_field( wp_unslash( $_POST['min_date'] ) ) : '';
		$max_date = isset( $_POST['max_date'] ) ? sanitize_text_field( wp_unslash( $_POST['max_date'] ) ) : '';


		if ( '' !== $name ) {

			$name = $add_to . '_' . sanitize_key( $name );

			$field_data = array(
				'type'          => $type,
				'label'         => $label,
				'placeholder'   => $placeholder,
				'class'         => array( 'form-row-wide' ),
				'label_class'   => array(),
				'show_in_email' => $show_in_email,
				'required'      => $is_required,
				'custom'        => true,
				'default'       => $default_value,
				'options'       => $options,
				'date_input'    => $date_input,
				'optimized'     => $optimized,
				'width'         => $width,
				'name'          => $name,
				'key'           => $name,
				'enabled'       => 'yes',
			);

			// Specific to date input.
			if ( 'datetime' === $type ) {
				$field_data['type'] = $date_input;

				$field_data['custom_attributes'] = array(
					'min' => $min_date,
					'max' => $max_date,
				);
			}

			if ( 'select' === $type || 'radio' === $type ) {
				$field_data['options'] = $options;
			}

			if ( 'number' === $type ) {
				$field_data['custom_attributes'] = array(
					'min' => $min_value,
					'max' => $max_value,
				);
			}

			$new_field = $field_data;   
			/* Add checkout field */
			\Cartflows_Pro_Helper::add_checkout_field( $add_to, $name, $post_id, $field_data );

			if ( 'wcf_field_order_' === $save_field_name ) {
				$field_data = AdminHelper::prepare_checkout_field_settings( $field_data, $post_id, $add_to );
			} else {
				$field_data = AdminHelper::prepare_optin_field_settings( $field_data, $post_id, $add_to );
			}

			$response_data = array(
				'messsage'   => __( 'Custom field prepared.', 'cartflows-pro' ),
				'add_to'     => $add_to,
				'field_data' => $field_data,
				'new_field'  => $new_field,
			);
			wp_send_json_success( $response_data );
		} else {

			$response_data = array( 'messsage' => __( 'Name field is empty!', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

	}
	

	/**
	 * Delete Field.
	 *
	 * @return void
	 */
	public function delete_custom_field() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_delete_custom_field', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		if ( $_POST ) {
			$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
			$type    = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
			$key     = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
			$step    = isset( $_POST['step'] ) ? sanitize_text_field( wp_unslash( $_POST['step'] ) ) : '';

			$billing_fields  = array();
			$shipping_fields = array();

			if ( '' !== $key ) {

				\Cartflows_Pro_Helper::delete_checkout_field( $type, $key, $post_id );

				$billing_fields  = $this->get_checkout_fields( 'billing', $post_id );
				$shipping_fields = $this->get_checkout_fields( 'shipping', $post_id );

				$checkout_meta = \Cartflows_Checkout_Meta_Data::get_instance();
				$optin_meta    = \Cartflows_Optin_Meta_Data::get_instance();
				// Prepare the data with new fields.
				if ( 'checkout' === $step ) {
					$billing_fields  = $checkout_meta->get_field_settings( $post_id, 'billing', $billing_fields );
					$shipping_fields = $checkout_meta->get_field_settings( $post_id, 'shipping', $shipping_fields );
				} else {
					$billing_fields = $optin_meta->get_field_settings( $post_id, 'billing', $billing_fields );
				}

				$data = array(
					'status'          => true,
					'billing_fields'  => $billing_fields,
					'shipping_fields' => $shipping_fields,
				);

				wp_send_json( $data );

			}
		}

		wp_send_json_success( $response_data );

	}

	/**
	 * Get_checkout_fields
	 *
	 * @param string $key key.
	 * @param string $post_id post_id.
	 */
	public function get_checkout_fields( $key, $post_id ) {

		$saved_fields = get_post_meta( $post_id, 'wcf_fields_' . $key, true );

		if ( ! $saved_fields ) {
			$saved_fields = array();
		}

		$fields = array_filter( $saved_fields );

		if ( empty( $fields ) ) {
			if ( 'billing' === $key || 'shipping' === $key ) {

				$fields = WC()->countries->get_address_fields( WC()->countries->get_base_country(), $key . '_' );

				if ( CARTFLOWS_STEP_POST_TYPE === get_post_type( intval( $post_id ) ) ) {
					update_post_meta( $post_id, 'wcf_fields_' . $key, $fields );
				}
			}
		}

		return $fields;
	}
}
