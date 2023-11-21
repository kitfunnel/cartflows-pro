<?php
/**
 * CartFlows Flows ajax actions.
 *
 * @package CartFlows
 */

namespace CartflowsProAdmin\AdminCore\Ajax;

use CartflowsProAdmin\AdminCore\Ajax\AjaxBase;
use CartflowsProAdmin\AdminCore\inc\AdminHelper;

/**
 * Class Flows.
 */
class MultipleOrderBump extends AjaxBase {

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
			'delete_order_bump',
			'add_order_bump',
			'clone_order_bump',
			'update_order_bump_title',
			'update_order_bump_status',
			'get_current_order_bump',
			'save_order_bump_settings',
			'reorder_order_bumps',
		);

		$this->init_ajax_events( $ajax_events );
	}

	/**
	 * Save order bump settings.
	 *
	 * @return void
	 */
	public function save_order_bump_settings() {

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
		if ( ! check_ajax_referer( 'cartflows_pro_save_order_bump_settings', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$response_data = array(
			'success'  => false,
			'messsage' => __( 'Order Bump data not saved.', 'cartflows-pro' ),
		);

		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : '';
		$ob_id   = isset( $_POST['ob_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ob_id'] ) ) : '';
		$ob_tab  = isset( $_POST['ob_tab'] ) ? sanitize_text_field( wp_unslash( $_POST['ob_tab'] ) ) : '';

		if ( ! empty( $ob_id ) && ! empty( $step_id ) ) {

			$order_bumps = get_post_meta( $step_id, 'wcf-order-bumps', true );

			$new_data = isset( $_POST ) ? $_POST : array();

			$default_data = \Cartflows_Pro_Checkout_Default_Meta::get_instance()->order_bump_default_meta();

			$default_data_keys = array_keys( $default_data );

			if ( is_array( $order_bumps ) && ! empty( $order_bumps ) ) {

				foreach ( $order_bumps as $index => $ob_data ) {

					if ( $ob_data['id'] === $ob_id ) {

						foreach ( $new_data as $data_key => $data_value ) {

							if ( ! in_array( $data_key, $default_data_keys, true ) ) {
								continue;
							}

							$data_value                         = $this->sanitize_multiple_order_bump_values( $data_value, $default_data[ $data_key ]['sanitize'], $step_id, $order_bumps, $index, $new_data );
							$order_bumps[ $index ][ $data_key ] = $data_value;
						}



						if ( in_array( 'product', array_keys( $new_data ), true ) ) { // Checking if product tab. Can update later.
							$order_bumps[ $index ]['product_image']   = '';
							$order_bumps[ $index ]['product_img_obj'] = '';
							$order_bumps[ $index ]['desc_text']       = false; // Can't set it as empty. Case: If user want to put desc empty.
						}
						update_post_meta( $step_id, 'wcf-order-bumps', $order_bumps );
						delete_post_meta( $step_id, 'wcf-pro-dynamic-css' );
						break;
					}
				}

				$response_data = array(
					'messsage' => __( 'Order bump settings saved successfully!', 'cartflows-pro' ),
					'success'  => true,
				);

				if ( in_array( 'product', array_keys( $new_data ), true ) ) {
					$current_ob                  = $this->get_ob_data( $step_id, $ob_id );
					$response_data['current_ob'] = $current_ob;
				}
			}

			wp_send_json_error( $response_data );
		} else {
			wp_send_json_error( $response_data );
		}
	}

	/**
	 * Sanitixe order bump values.
	 *
	 * @param string $data_value meta value.
	 * @param string $filter filter type.
	 * @param int    $step_id checkout id.
	 * @param array  $order_bumps order bumps.
	 * @param int    $index order bump index.
	 * @param int    $new_data order bump data.
	 */
	public function sanitize_multiple_order_bump_values( $data_value, $filter, $step_id, $order_bumps, $index, $new_data ) {

		switch ( $filter ) {

			case 'FILTER_SANITIZE_STRING':
				$meta_value = sanitize_text_field( wp_unslash( $data_value ) );
				break;

			case 'FILTER_VALIDATE_BOOLEAN':
				$meta_value = filter_var( $data_value, FILTER_VALIDATE_BOOLEAN );
				break;

			case 'FILTER_SANITIZE_URL':
				$meta_value = filter_var( $data_value, FILTER_SANITIZE_URL );
				break;

			case 'FILTER_SANITIZE_NUMBER_INT':
				$meta_value = filter_var( $data_value, FILTER_SANITIZE_NUMBER_INT );
				break;

			case 'FILTER_SANITIZE_NUMBER_FLOAT':
				$meta_value = filter_var( $data_value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
				break;

			case 'FILTER_WP_KSES_POST':
				$meta_value = wp_kses_post( $data_value );
				break;

			case 'FILTER_SANITIZE_COLOR':
				$meta_value = sanitize_hex_color( $data_value );
				break;

			case 'FILTER_SANITIZE_RULES':
				$sanitized_data = array();

				if ( is_array( $data_value ) && ! empty( $data_value ) ) {
					foreach ( $data_value as $group_index => $group_data ) {

						if ( is_array( $group_data ) && is_array( $group_data['rules'] ) ) {

							$rules = $this->sanitize_rules( $group_data['rules'] );

							$sanitized_data[ $group_index ]['group_id'] = sanitize_text_field( $group_data['group_id'] );
							$sanitized_data[ $group_index ]['rules']    = $rules;
						}
					}
				}
				$meta_value = $sanitized_data;
				break;

			case 'FILTER_CARTFLOWS_OB_IMAGE':
				$image_data = '';

				if ( ! empty( $data_value ) && ! is_serialized( $data_value ) ) {
					$image_obj = json_decode( stripcslashes( wp_unslash( $data_value ) ), true );
					$image_url = isset( $image_obj['sizes'] ) ? $image_obj['sizes'] : array();

					$image_id = isset( $image_obj['id'] ) ? intval( $image_obj['id'] ) : 0;

					if ( 0 !== $image_id ) {
						$image_data = array(
							'id'  => $image_id,
							'url' => array(
								'thumbnail' => isset( $image_url['thumbnail']['url'] ) ? esc_url_raw( $image_url['thumbnail']['url'] ) : '',
								'medium'    => isset( $image_url['medium']['url'] ) ? esc_url_raw( $image_url['medium']['url'] ) : '',
								'full'      => isset( $image_url['full']['url'] ) ? esc_url_raw( $image_url['full']['url'] ) : '',
							),
						);
					}
				}
				$meta_value = $image_data;
				break;

			default:
				$meta_value = sanitize_text_field( wp_unslash( $data_value ) );
				break;
		}

		return $meta_value;
	}


	/**
	 * Sanitize rules.
	 *
	 * @param array $rules rules.
	 */
	public function sanitize_rules( $rules ) {

		$sanitized_rules = array();
		foreach ( $rules as $index => $rule_data ) {

			if ( ! empty( $rule_data['value'] ) ) {
				$sanitized_rules[] = $this->sanitize_rule_data( $rule_data );
			}
		}

		return $sanitized_rules;

	}

	/**
	 * Sanitize rule data.
	 *
	 * @param array $rule_data rule data.
	 */
	public static function sanitize_rule_data( $rule_data ) {

		$sanitized_input = '';

		if ( is_array( $rule_data ) ) {

			$sanitized_input = array();

			foreach ( $rule_data as $key => $value ) {
				$sanitized_key                     = sanitize_text_field( $key );
				$sanitized_input[ $sanitized_key ] = self::sanitize_rule_data( $value );
			}
		} else {
			$sanitized_input = sanitize_text_field( $rule_data );
		}

		return $sanitized_input;
	}

	/**
	 * Get order bump.
	 *
	 * @return void
	 */
	public function get_current_order_bump() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );
			wp_send_json_error( $response_data );
		}


		if ( empty( $_POST ) ) {
			$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_get_current_order_bump', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}


		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : '';
		$ob_id   = isset( $_POST['ob_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ob_id'] ) ) : 0;

		$current_ob = array();

		if ( ! empty( $ob_id ) && ! empty( $step_id ) ) {

			$current_ob = $this->get_ob_data( $step_id, $ob_id );

			$response_data = array(
				'success'    => true,
				'messsage'   => __( 'Order Bump Data Retrieved', 'cartflows-pro' ),
				'current_ob' => $current_ob,
			);
		} else {

			$response_data = array(
				'success'    => false,
				'messsage'   => __( 'No data.', 'cartflows-pro' ),
				'current_ob' => $current_ob,
			);
		}

		wp_send_json_success( $response_data );

	}

	/**
	 * Get ob data.
	 *
	 * @param int $step_id step id.
	 * @param int $ob_id ob id.
	 */
	public function get_ob_data( $step_id, $ob_id ) {

		$order_bumps = get_post_meta( $step_id, 'wcf-order-bumps', true );

		$admin_helper = \Cartflows_Pro_Admin_Helper::get_instance();

		$default_meta = \Cartflows_Pro_Checkout_Default_Meta::get_instance()->order_bump_default_meta();

		$ob_default_meta = array();
		foreach ( $default_meta as $key => $value ) {
			$ob_default_meta[ $key ] = $value['default'];
		}

		if ( is_array( $order_bumps ) ) {
			foreach ( $order_bumps as $index => $ob_data ) {

				if ( $ob_id === $ob_data['id'] ) {

					$ob_data = wp_parse_args( $ob_data, $ob_default_meta );

					$product_id = 0;

					if ( ! empty( $ob_data['product'] ) ) {
						$product_id         = intval( $ob_data['product'] );
						$ob_data['product'] = $admin_helper::get_products_label( array( $product_id ) );
					}

					$ob_data = $this->add_ob_product_data( $product_id, $ob_data );

					if ( ! empty( $ob_data['discount_coupon'] ) ) {
						$ob_data['discount_coupon'] = $admin_helper::get_coupons_label( array( $ob_data['discount_coupon'] ) );
					}

					if ( 'yes' === $ob_data['is_rule'] ) {
						$ob_data['rules'] = $this->filter_ob_rules( $ob_data['rules'] );
					}

					break;
				}
			}
		}

		return $ob_data;

	}

	/**
	 * Add product data.
	 *
	 * @param int   $product_id product id.
	 * @param array $ob_data order bump data.
	 */
	public function add_ob_product_data( $product_id, $ob_data ) {

		$product_obj = wc_get_product( $product_id );

		if ( ! empty( $ob_data['product'] ) && $product_obj ) {

			// Update product image.
			if ( empty( $ob_data['product_image'] ) ) {

				$product_image = get_the_post_thumbnail_url( $product_id );

				if ( empty( $product_image ) ) {
					$product_image = esc_url_raw( CARTFLOWS_PRO_URL . 'assets/images/image-placeholder.png' );
				}

				$ob_data['product_image'] = $product_image;
			}

			// Update product description.
			if ( empty( $ob_data['desc_text'] ) ) {
				$product_desc         = $product_obj->get_short_description();
				$ob_data['desc_text'] = $product_desc . '<br>' . "\r\n{{product_price}}";
			}

			// Calculate product custom price.

			$custom_price       = wcf_pro()->utils->get_calculated_discount( $ob_data['discount_type'], $ob_data['discount_value'], $ob_data['product'][0]['original_price'] );
			$product_price_data = \Cartflows_Pro_Order_Bump_Product::get_instance()->get_taxable_product_price( $product_obj, $ob_data['product'][0]['original_price'], $custom_price );

			$display_price = '';

			if ( \Cartflows_Pro_Helper::is_valid_custom_price( $custom_price ) ) {
				$display_price  = '<del class="wcf-regular-price">' . wc_price( $product_price_data['product_price'] ) . '</del>';
				$display_price .= ' <span class="wcf-discount-price">' . wc_price( $product_price_data['custom_price'] ) . '</span>';
			} else {
				$display_price = '<span class="wcf-normal-price">' . wc_price( $product_price_data['product_price'] ) . '</span>';
			}

			$ob_data['product'][0]['display_price'] = $display_price;

		} else {
			$ob_data['product_image'] = esc_url_raw( CARTFLOWS_PRO_URL . 'assets/images/image-placeholder.png' );
		}

		return $ob_data;

	}


	/**
	 * Filter ob rules.
	 *
	 * @param array $conditions conditions data.
	 */
	public function filter_ob_rules( $conditions ) {

		if ( is_array( $conditions ) ) {

			foreach ( $conditions as $group_index => $group_data ) {

				if ( is_array( $group_data ) & ! empty( $group_data['rules'] ) ) {

					$conditions[ $group_index ]['rules'] = $this->filter_rules_data( $group_data['rules'] );
				}
			}
		}

		return $conditions;

	}

	/**
	 * Filter rule options.
	 *
	 * @param array $rules rule.
	 */
	public function filter_rules_data( $rules ) {

		$admin_helper = \Cartflows_Pro_Admin_Helper::get_instance();

		foreach ( $rules as $rule_index => $rule_data ) {

			if ( is_array( $rule_data['value'] ) && ! empty( $rule_data['value'][0] ) ) {

				switch ( $rule_data['condition'] ) {
					case 'cart_item':
							$rules[ $rule_index ]['value'] = $admin_helper::get_products_label( $rule_data['value'] );
						break;
					case 'cart_shipping_method':
							$rules[ $rule_index ]['value'] = $admin_helper::get_labels( $rule_data['value'] );

						break;
					case 'cart_item_category':
							$rules[ $rule_index ]['value'] = $admin_helper::get_products_cat_label( $rule_data['value'] );

						break;
					case 'cart_item_tag':
							$rules[ $rule_index ]['value'] = $admin_helper::get_products_tag_label( $rule_data['value'] );

						break;
					case 'cart_coupons':
							$rules[ $rule_index ]['value'] = $admin_helper::get_coupons_label( $rule_data['value'] );

						break;
					case 'cart_payment_method':
							$rules[ $rule_index ]['value'] = $admin_helper::get_payment_methods_label( $rule_data['value'] );

						break;
					case 'cart_shipping_country':
					case 'cart_billing_country':
							$rules[ $rule_index ]['value'] = $admin_helper::get_country_label( $rule_data['value'] );

						break;
					default:
						break;
				}
			}
		}

		return $rules;
	}

	/**
	 * Delete order bump.
	 *
	 * @return void
	 */
	public function update_order_bump_status() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_update_order_bump_status', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$response_data = array(
			'success'  => false,
			'messsage' => __( 'Cant update order bump status!', 'cartflows-pro' ),
		);

		$post_id   = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$step_id   = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
		$ob_id     = isset( $_POST['ob_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ob_id'] ) ) : 0;
		$ob_status = isset( $_POST['ob_status'] ) && 'true' === $_POST['ob_status'] ? true : false;

		if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json_error( $response_data );
		}

		if ( ! empty( $ob_id ) && ! empty( $step_id ) ) {

			$order_bumps = get_post_meta( $step_id, 'wcf-order-bumps', true );

			if ( is_array( $order_bumps ) && ! empty( $order_bumps ) ) {
				foreach ( $order_bumps as $index => $ob_data ) {

					if ( $ob_id === $ob_data['id'] ) {

						$order_bumps[ $index ]['status'] = $ob_status;

					}
				}

				update_post_meta( $step_id, 'wcf-order-bumps', $order_bumps );

				AdminHelper::clear_current_step_css( $step_id );

				$response_data = array(
					'success'  => true,
					'messsage' => __( 'Order Bump Status Updated Succesfully', 'cartflows-pro' ),
				);
				wp_send_json_success( $response_data );
			}

			wp_send_json_error( $response_data );

		} else {

			wp_send_json_error( $response_data );
		}
	}

	/**
	 * Delete order bump.
	 *
	 * @return void
	 */
	public function delete_order_bump() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_delete_order_bump', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$response_data = array(
			'success'  => false,
			'messsage' => __( 'Cant delete order bump!', 'cartflows-pro' ),
		);

		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
		$ob_id   = isset( $_POST['ob_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ob_id'] ) ) : 0;

		if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json_error( $response_data );
		}

		if ( ! empty( $ob_id ) && ! empty( $step_id ) ) {

			$order_bumps = get_post_meta( $step_id, 'wcf-order-bumps', true );
			$new_ob_data = array();

			if ( is_array( $order_bumps ) && ! empty( $order_bumps ) ) {
				foreach ( $order_bumps as $index => $ob_data ) {

					if ( $ob_id === $ob_data['id'] ) {

						unset( $order_bumps[ $index ] );
						$new_ob_data = array_values( $order_bumps );
						update_post_meta( $step_id, 'wcf-order-bumps', $new_ob_data );
						AdminHelper::clear_current_step_css( $step_id );
						break;
					}
				}

				$response_data = array(
					'success'     => true,
					'messsage'    => __( 'Order Bump Deleted Succesfully', 'cartflows-pro' ),
					'order_bumps' => $new_ob_data,
				);
				wp_send_json_success( $response_data );
			}
			wp_send_json_error( $response_data );

		} else {
			wp_send_json_error( $response_data );
		}

	}

	/**
	 * Add Order Bump.
	 *
	 * @return void
	 */
	public function add_order_bump() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );
			wp_send_json_error( $response_data );
		}

		$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows-pro' ) );
		if ( empty( $_POST ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_add_order_bump', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}


		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
		$ob_id   = isset( $_POST['ob_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ob_id'] ) ) : 0;

		$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : 'no title';

		if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json_error( $response_data );
		}

		if ( ! empty( $ob_id ) && ! empty( $step_id ) ) {

			$order_bumps = get_post_meta( $step_id, 'wcf-order-bumps', true );

			if ( ! is_array( $order_bumps ) ) {
				$order_bumps = array();
			}

			$new_ob_data = AdminHelper::add_default_order_bump_data( $ob_id, $title, $order_bumps );
			update_post_meta( $step_id, 'wcf-order-bumps', $new_ob_data );

			$response_data = array(
				'success'     => true,
				'messsage'    => __( 'Order Bump Added Succesfully', 'cartflows-pro' ),
				'order_bumps' => $new_ob_data,
			);
			wp_send_json_success( $response_data );
		} else {

			$response_data = array(
				'messsage' => __( 'Cant add order bump!', 'cartflows-pro' ),
				'success'  => false,
			);
			wp_send_json_error( $response_data );
		}
	}

	/**
	 * Clone Order Bump
	 *
	 * @return void
	 */
	public function clone_order_bump() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_clone_order_bump', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}


		$response_data = array(
			'messsage' => __( 'Can\'t clone the order bump! Order Bump ID or Page ID not found.', 'cartflows-pro' ),
			'success'  => false,
		);

		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
		$ob_id   = isset( $_POST['ob_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ob_id'] ) ) : '';

		if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json_error( $response_data );
		}

		if ( ! empty( $ob_id ) && ! empty( $step_id ) ) {

			// Get all the order bumps for current step.
			$order_bumps = get_post_meta( $step_id, 'wcf-order-bumps', true );

			if ( is_array( $order_bumps ) && ! empty( $order_bumps ) ) {

				foreach ( $order_bumps as $index => $ob_data ) {

					if ( $ob_id === $ob_data['id'] ) {

						// Generate random ID for new order Bump.
						$new_ob_id = substr( md5( wp_rand() ), 2, 3 );

						$data_to_clone = $ob_data;

						// Replace/Change the ID and Title to new.
						$data_to_clone['id']     = $new_ob_id;
						$data_to_clone['title']  = $data_to_clone['title'] . __( ' Clone', 'cartflows-pro' );
						$data_to_clone['status'] = false;

						array_push( $order_bumps, $data_to_clone );

						update_post_meta( $step_id, 'wcf-order-bumps', $order_bumps );
						break;
					}
				}

				$response_data = array(
					'success'     => true,
					'messsage'    => __( 'Order Bump Cloned Succesfully', 'cartflows-pro' ),
					'order_bumps' => $order_bumps,
				);

				wp_send_json_success( $response_data );
			}

			wp_send_json_error( $response_data );

		} else {
			wp_send_json_error( $response_data );
		}
	}

	/**
	 * Update order bump title.
	 */
	public function update_order_bump_title() {
		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );
			wp_send_json_error( $response_data );
		}

		$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows-pro' ) );
		if ( empty( $_POST ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_update_order_bump_title', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
		$ob_id   = isset( $_POST['ob_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ob_id'] ) ) : 0;

		$new_title = isset( $_POST['new_title'] ) ? sanitize_text_field( wp_unslash( $_POST['new_title'] ) ) : 'no title';

		if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json_error( $response_data );
		}

		$order_bumps = get_post_meta( $step_id, 'wcf-order-bumps', true );

		if ( is_array( $order_bumps ) && ! empty( $order_bumps ) ) {

			foreach ( $order_bumps as $index => $ob_data ) {

				if ( $ob_id === $ob_data['id'] ) {

					$order_bumps[ $index ]['title'] = $new_title;
					break;
				}
			}

			update_post_meta( $step_id, 'wcf-order-bumps', $order_bumps );

			$response_data = array(
				'success'     => true,
				'messsage'    => __( 'Order Bump Title Updated Succesfully', 'cartflows-pro' ),
				'order_bumps' => $order_bumps,
			);
			wp_send_json_success( $response_data );
		}

		$response_data = array(
			'success'     => true,
			'messsage'    => __( 'Order Bump Title Not Updated.', 'cartflows-pro' ),
			'order_bumps' => $order_bumps,
		);

		wp_send_json_error( $response_data );

	}

	/**
	 * Reorder order bumps.
	 */
	public function reorder_order_bumps() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_reorder_order_bumps', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;

		$sorted_obs = isset( $_POST['sorted_obs'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['sorted_obs'] ) ) ) : array();

		$result = array(
			'status' => false,
			'text'   => __( 'Order Bumps not sorted.', 'cartflows-pro' ),
		);

		if ( ! $step_id || empty( $sorted_obs ) ) {
			wp_send_json( $result );
		}

		if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json_error( $result );
		}

		$new_order_bumps = array();

		foreach ( $sorted_obs as $index => $ob_id ) {

			$get_ob_data = $this->get_order_bump_data_by_id( $step_id, $ob_id );

			if ( ! empty( $get_ob_data ) ) {
				array_push( $new_order_bumps, $get_ob_data );
			}
		}

		update_post_meta( $step_id, 'wcf-order-bumps', $new_order_bumps );

		$result = array(
			'status' => true,
			/* translators: %s step id */
			'text'   => sprintf( __( 'Order Bump sorted for step - %s', 'cartflows-pro' ), $step_id ),
		);

		wp_send_json( $result );
	}

	/**
	 * Get order bump.
	 *
	 * @param int    $checkout_id checkout id.
	 * @param string $ob_id order bump id.
	 */
	public function get_order_bump_data_by_id( $checkout_id, $ob_id ) {

		$order_bumps = get_post_meta( $checkout_id, 'wcf-order-bumps', true );

		$required_ob_data = array();

		if ( is_array( $order_bumps ) && ! empty( $order_bumps ) ) {

			foreach ( $order_bumps as $index => $ob_data ) {

				if ( $ob_id === $ob_data['id'] ) {
					$required_ob_data = $ob_data;
					break;
				}
			}
		}

		return $required_ob_data;
	}


}
