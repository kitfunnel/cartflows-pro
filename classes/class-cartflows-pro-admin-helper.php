<?php
/**
 * Cartflows Admin Helper.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Admin_Helper.
 */
class Cartflows_Pro_Admin_Helper {

	/**
	 * Member Variable
	 *
	 * @var instance
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
	 * Get steps.
	 *
	 * @param int $step_id Step ID.
	 */
	public static function get_opt_steps( $step_id ) {

		$flow_id    = get_post_meta( $step_id, 'wcf-flow-id', true );
		$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

		$control_steps = array();
		$exclude_id    = $step_id;

		$optgroup = array(
			'upsell'   => esc_html__( 'Upsell ( Woo )', 'cartflows-pro' ),
			'downsell' => esc_html__( 'Downsell ( Woo )', 'cartflows-pro' ),
			'thankyou' => esc_html__( 'Thankyou ( Woo )', 'cartflows-pro' ),
		);

		$steps = array();
		if ( is_array( $flow_steps ) ) {
			foreach ( $flow_steps as $f_index => $f_data ) {
				$control_steps[] = intval( $f_data['id'] );
			}
		}

		$cartflows_steps_args = array(
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post_type'      => 'cartflows_step',
			'post_status'    => 'publish',
			'post__not_in'   => array( $exclude_id ),
			/** 'fields'           => 'ids', */
		);
		array_push(
			$steps,
			array(
				'value' => '',
				'label' => 'Default',
			)
		);

		foreach ( $optgroup as $optgroup_key => $optgroup_value ) {

			$cartflows_steps_args['tax_query'] = array( //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				'relation' => 'AND',
				array(
					'taxonomy' => 'cartflows_step_type',
					'field'    => 'slug',
					'terms'    => $optgroup_key,
				),
				array(
					'taxonomy' => 'cartflows_step_flow',
					'field'    => 'slug',
					'terms'    => 'flow-' . $flow_id,

				),
			);
			$cartflows_steps_query = new WP_Query( $cartflows_steps_args );
			$cartflows_steps       = $cartflows_steps_query->posts;

			if ( ! empty( $cartflows_steps ) ) {

				$steps_found = array();

				foreach ( $cartflows_steps as $key => $cf_step ) {

					if ( ! in_array( $cf_step->ID, $control_steps, true ) ) {
						continue;
					}

					array_push(
						$steps_found,
						array(
							'value' => $cf_step->ID,
							'label' => esc_attr( $cf_step->post_title ),
						)
					);

				}

				array_push(
					$steps,
					array(
						'isopt'   => true,
						'title'   => $optgroup_value,
						'options' => $steps_found,
					)
				);

			}
		}

		return $steps;

	}

	/**
	 * Get product price.
	 *
	 * @param object $product product data.
	 */
	public static function get_product_original_price( $product ) {

		$custom_price = '';

		if ( $product->is_type( 'variable' ) ) {

			$default_attributes = $product->get_default_attributes();

			if ( ! empty( $default_attributes ) ) {

				foreach ( $product->get_children() as $c_in => $variation_id ) {

					if ( 0 === $c_in ) {
						$product_id = $variation_id;
					}

					$single_variation = new WC_Product_Variation( $variation_id );

					if ( $default_attributes == $single_variation->get_attributes() ) {

						$product_id = $variation_id;
						break;
					}
				}
			} else {

				$product_childrens = $product->get_children();

				if ( is_array( $product_childrens ) ) {

					foreach ( $product_childrens  as $c_in => $c_id ) {

						$_child_product = wc_get_product( $c_id );

						if ( $_child_product->is_in_stock() && 'publish' === $_child_product->get_status() ) {
							$product_id = $c_id;
							break;
						}
					}
				}
			}

			if ( ! empty( $product_id ) ) {
				$product = wc_get_product( $product_id );
			}
		}

		if ( $product ) {
			$custom_price = $product->get_price( 'edit' );
		}

		return $custom_price;
	}

	/**
	 * Loop through the input and sanitize each of the values.
	 *
	 * @param array $input_settings input settings.
	 * @return array
	 */
	public static function sanitize_form_inputs( $input_settings = array() ) {
		$new_settings = array();
		foreach ( $input_settings as $key => $val ) {

			if ( is_array( $val ) ) {
				foreach ( $val as $k => $v ) {
					$new_settings[ $key ][ $k ] = ( isset( $val[ $k ] ) ) ? sanitize_text_field( $v ) : '';
				}
			} else {
				$new_settings[ $key ] = ( isset( $input_settings[ $key ] ) ) ? sanitize_text_field( $val ) : '';
			}
		}
		return $new_settings;
	}

	/**
	 * Update admin settings.
	 *
	 * @param string $key key.
	 * @param bool   $value key.
	 * @param bool   $network network.
	 */
	public static function update_admin_settings_option( $key, $value, $network = false ) {

		// Update the site-wide option since we're in the network admin.
		if ( $network && is_multisite() ) {
			update_site_option( $key, $value );
		} else {
			update_option( $key, $value );
		}

	}

	/**
	 * Get productss labels.
	 *
	 * @param array $product_ids values.
	 */
	public static function get_products_label( $product_ids ) {

		$products_label = array();

		foreach ( $product_ids as $key => $product_id ) {

			$product_obj = wc_get_product( $product_id );

			if ( $product_obj ) {
				$product_name     = $product_obj->get_name();
				$products_label[] = array(
					'value'          => $product_id,
					'label'          => $product_name . ' (#' . $product_obj->get_id() . ')',
					'product_name'   => $product_name,
					'product_desc'   => $product_obj->get_short_description(),
					'original_price' => self::get_product_original_price( $product_obj ),
					'product_image'  => get_the_post_thumbnail_url( $product_obj->get_id() ),
				);
			}
		}

		return $products_label;
	}


	/**
	 * Get coupons labels.
	 *
	 * @param array $coupons_ids id.
	 */
	public static function get_coupons_label( $coupons_ids ) {

		$coupons_label = array();

		foreach ( $coupons_ids as $key => $coupon_code ) {

			$coupon_data = new WC_Coupon( $coupon_code );

			if ( $coupon_data ) {

				$coupon_id       = $coupon_data->get_id();
				$coupons_label[] = array(
					'value' => $coupon_code,
					'label' => get_the_title( $coupon_id ),
				);
			}
		}

		return $coupons_label;
	}

	/**
	 * Get labels.
	 *
	 * @param array $payment_gateways values.
	 */
	public static function get_payment_methods_label( $payment_gateways ) {

		$gateway_labels = array();

		$selected_payment_method = WC()->payment_gateways->payment_gateways();

		foreach ( $payment_gateways as $index => $gateway_key ) {

			$gateway_labels[] = array(
				'value' => $gateway_key,
				'label' => $selected_payment_method[ $gateway_key ]->method_title,
			);
		}

		return $gateway_labels;
	}

	/**
	 * Get terms labels.
	 *
	 * @param array $terms values.
	 */
	public static function get_products_tag_label( $terms ) {

		$labels = array();

		foreach ( $terms as $index => $term ) {

			$term_data = get_term_by( 'id', $term, 'product_tag', 'ARRAY_A' );

			$labels[] = array(
				'value' => $term_data['term_id'],
				'label' => $term_data['name'],
			);
		}

		return $labels;
	}

	/**
	 * Get category labels.
	 *
	 * @param array $categories values.
	 */
	public static function get_products_cat_label( $categories ) {

		$labels = array();

		foreach ( $categories as $index => $category ) {

			$category_data = get_term_by( 'id', $category, 'product_cat', 'ARRAY_A' );

			$labels[] = array(
				'value' => $category_data['term_id'],
				'label' => $category_data['name'],
			);
		}

		return $labels;
	}


	/**
	 * Get labels.
	 *
	 * @param array $data_array values.
	 */
	public static function get_labels( $data_array ) {

		$labels = array();

		foreach ( $data_array as $key => $data_name ) {

			$labels[] = array(
				'value' => $data_name,
				'label' => ucfirst( preg_replace( '/[._-]+/', ' ', $data_name ) ),
			);
		}

		return $labels;
	}

	/**
	 * Get country labels.
	 *
	 * @param array $countries_ids values.
	 */
	public static function get_country_label( $countries_ids ) {

		$country_label = array();

		$countries = WC()->countries->get_allowed_countries();

		foreach ( $countries_ids as $key => $country ) {

			$country_label[] = array(
				'value' => $country,
				'label' => $countries[ $country ],
			);

		}

		return $country_label;
	}

}

/**
 *  Prepare if class 'Cartflows_Pro_Admin' exist.
 *  Kicking this off by calling 'get_instance()' method
 *  Cartflows_Pro_Admin_Helper::get_instance();
 */
