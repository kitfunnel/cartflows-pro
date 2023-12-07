<?php
/**
 * Checkout post meta
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Meta Boxes setup
 */
class Cartflows_Pro_Checkout_Meta_Data {

	/**
	 * Instance
	 *
	 * @var Cartflows_Pro_Checkout_Meta_Data $instance Class object
	 */
	private static $instance;

	/**
	 * Meta Option
	 *
	 * @var array $meta_option Array of meta option
	 */
	private static $meta_option = null;

	/**
	 * Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		// Pro settings for checkout step.
		add_filter( 'cartflows_admin_checkout_meta_fields', array( $this, 'meta_fields_react' ), 10, 3 );

		// Pro design settings for checkout step.
		add_filter( 'cartflows_admin_checkout_design_fields', array( $this, 'design_fields_react' ), 10, 3 );

		// Filter the step options data.
		add_filter( 'cartflows_admin_checkout_step_meta_fields', array( $this, 'filter_values' ), 10, 2 );

		// Step API data.
		add_filter( 'cartflows_admin_checkout_step_data', array( $this, 'add_checkout_step_api_data' ), 10, 2 );

		add_filter( 'cartflows_admin_checkout_settings_fields', array( $this, 'add_checkout_settings_pro_fields' ), 10, 1 );
	}

		/**
		 * Add required data to api.
		 *
		 * @param  array $api_data data.
		 * @param  int   $step_id step id.
		 */
	public function add_checkout_step_api_data( $api_data, $step_id ) {
		$opt_steps              = Cartflows_Pro_Admin_Helper::get_opt_steps( $step_id );
		$api_data['step_lists'] = $opt_steps;

		return $api_data;
	}

	/**
	 * Prepare ob rules settings.
	 */
	public function get_ob_rules_data() {

		$string_operators   = array(
			array(
				'label' => __( 'matches any of', 'cartflows-pro' ),
				'value' => 'any',
			),
			array(
				'label' => __( 'matches all of', 'cartflows-pro' ),
				'value' => 'all',
			),
			array(
				'label' => __( 'matches none of', 'cartflows-pro' ),
				'value' => 'none',
			),
		);
		$math_operators     = array(
			array(
				'label' => __( 'is equal to', 'cartflows-pro' ),
				'value' => '==',
			),
			array(
				'label' => __( 'is not equal to', 'cartflows-pro' ),
				'value' => '!=',
			),
			array(
				'label' => __( 'is greater than', 'cartflows-pro' ),
				'value' => '>',
			),
			array(
				'label' => __( 'is less than', 'cartflows-pro' ),
				'value' => '<',
			),
			array(
				'label' => __( 'is greater or equal to', 'cartflows-pro' ),
				'value' => '>=',
			),
			array(
				'label' => __( 'is less or equal to', 'cartflows-pro' ),
				'value' => '<=',
			),
		);
		$shipping_operators = array(
			array(
				'label' => __( 'matches any of', 'cartflows-pro' ),
				'value' => 'any',
			),
			array(
				'label' => __( 'matches none of', 'cartflows-pro' ),
				'value' => 'none',
			),
		);

		$coupon_operators = array(
			array(
				'label' => __( 'matches any of', 'cartflows-pro' ),
				'value' => 'any',
			),
			array(
				'label' => __( 'matches all of', 'cartflows-pro' ),
				'value' => 'all',
			),
			array(
				'label' => __( 'matches none of', 'cartflows-pro' ),
				'value' => 'none',
			),
			array(
				'label' => __( 'exist', 'cartflows-pro' ),
				'value' => 'exist',
			),
			array(
				'label' => __( 'not exist', 'cartflows-pro' ),
				'value' => 'not_exist',
			),
		);

		return array(
			'conditions' => array(
				array(
					'title'   => __( 'Cart', 'cartflows-pro' ),
					'isopt'   => true,
					'options' => array(
						array(
							'label' => __( 'Product(s)', 'cartflows-pro' ),
							'value' => 'cart_item',
						),
						array(
							'label' => __( 'Product category(s)', 'cartflows-pro' ),
							'value' => 'cart_item_category',
						),
						array(
							'label' => __( 'Product tag(s)', 'cartflows-pro' ),
							'value' => 'cart_item_tag',
						),
						array(
							'label' => __( 'Total', 'cartflows-pro' ),
							'value' => 'cart_total',
						),
						array(
							'label' => __( 'Coupon(s)', 'cartflows-pro' ),
							'value' => 'cart_coupons',
						),
						array(
							'label' => __( 'Shipping method', 'cartflows-pro' ),
							'value' => 'cart_shipping_method',
						),
					),
				),

				array(
					'title'   => __( 'Geography', 'cartflows-pro' ),
					'isopt'   => true,
					'options' => array(
						array(
							'label' => __( 'Shipping country', 'cartflows-pro' ),
							'value' => 'cart_shipping_country',
						),
						array(
							'label' => __( 'Billing country', 'cartflows-pro' ),
							'value' => 'cart_billing_country',
						),
					),
				),
			),

			'field_data' => array(
				'cart_item'             => array(
					'operator' => $string_operators,
					'fields'   => array(
						array(
							'type'        => 'product',
							'placeholder' => __( 'Search for products..', 'cartflows-pro' ),
							'isMulti'     => true,
						),
					),
				),
				'cart_item_category'    => array(
					'operator' => $string_operators,
					'fields'   => array(
						array(
							'type'        => 'select2',
							'options'     => $this->get_product_categories(),
							'placeholder' => __( 'Search for products cat..', 'cartflows-pro' ),
							'isMulti'     => true,
						),
					),
				),
				'cart_item_tag'         => array(
					'operator' => $string_operators,
					'fields'   => array(
						array(
							'type'        => 'select2',
							'options'     => $this->get_product_tags(),
							'placeholder' => __( 'Search for products tags..', 'cartflows-pro' ),
							'isMulti'     => true,
						),
					),
				),
				'cart_total'            => array(
					'operator' => $math_operators,
					'fields'   => array(
						array(
							'type' => 'number',
						),
					),
				),
				'cart_coupons'          => array(
					'operator' => $coupon_operators,
					'fields'   => array(
						array(
							'type'        => 'coupon',
							'placeholder' => __( 'Search for coupons..', 'cartflows-pro' ),
							'isMulti'     => true,
						),
					),
				),
				'cart_shipping_method'  => array(
					'operator' => $shipping_operators,
					'fields'   => array(
						array(
							'type'        => 'select2',
							'placeholder' => __( 'Search for shipping methods..', 'cartflows-pro' ),
							'isMulti'     => true,
							'options'     => $this->get_shipping_methods(),
						),
					),
				),
				'cart_shipping_country' => array(
					'operator' => $shipping_operators,
					'fields'   => array(
						array(
							'type'        => 'select2',
							'placeholder' => __( 'Search for country..', 'cartflows-pro' ),
							'isMulti'     => true,
							'options'     => $this->get_allowed_countries(),
						),
					),
				),
				'cart_billing_country'  => array(
					'operator' => $shipping_operators,
					'fields'   => array(
						array(
							'type'        => 'select2',
							'placeholder' => __( 'Search for country..', 'cartflows-pro' ),
							'isMulti'     => true,
							'options'     => $this->get_allowed_countries(),
						),
					),
				),
			),
		);
	}
	/**
	 * Prepare checkout rules settings.
	 */
	public function get_checkout_rules_settings() {

		$string_operators        = array(
			array(
				'label' => __( 'matches any of', 'cartflows-pro' ),
				'value' => 'any',
			),
			array(
				'label' => __( 'matches all of', 'cartflows-pro' ),
				'value' => 'all',
			),
			array(
				'label' => __( 'matches none of', 'cartflows-pro' ),
				'value' => 'none',
			),
		);
		$math_operators          = array(
			array(
				'label' => __( 'is equal to', 'cartflows-pro' ),
				'value' => '==',
			),
			array(
				'label' => __( 'is not equal to', 'cartflows-pro' ),
				'value' => '!=',
			),
			array(
				'label' => __( 'is greater than', 'cartflows-pro' ),
				'value' => '>',
			),
			array(
				'label' => __( 'is less than', 'cartflows-pro' ),
				'value' => '<',
			),
			array(
				'label' => __( 'is greater or equal to', 'cartflows-pro' ),
				'value' => '>=',
			),
			array(
				'label' => __( 'is less or equal to', 'cartflows-pro' ),
				'value' => '<=',
			),
		);
		$shipping_operators      = array(
			array(
				'label' => __( 'matches any of', 'cartflows-pro' ),
				'value' => 'any',
			),
			array(
				'label' => __( 'matches none of', 'cartflows-pro' ),
				'value' => 'none',
			),
		);
		$coupon_operators        = array(
			array(
				'label' => __( 'matches any of', 'cartflows-pro' ),
				'value' => 'any',
			),
			array(
				'label' => __( 'matches all of', 'cartflows-pro' ),
				'value' => 'all',
			),
			array(
				'label' => __( 'matches none of', 'cartflows-pro' ),
				'value' => 'none',
			),
			array(
				'label' => __( 'exist', 'cartflows-pro' ),
				'value' => 'exist',
			),
			array(
				'label' => __( 'not exist', 'cartflows-pro' ),
				'value' => 'not_exist',
			),
		);
		$custom_fields_operators = array(
			array(
				'label' => __( 'is equal to', 'cartflows-pro' ),
				'value' => '===',
			),
			array(
				'label' => __( 'is not equal to', 'cartflows-pro' ),
				'value' => '!==',
			),
		);

		return array(
			'conditions' => array(
				array(
					'title'   => __( 'Order', 'cartflows-pro' ),
					'isopt'   => true,
					'options' => array(
						array(
							'label' => __( 'Product(s)', 'cartflows-pro' ),
							'value' => 'cart_item',
						),
						array(
							'label' => __( 'Product category(s)', 'cartflows-pro' ),
							'value' => 'cart_item_category',
						),
						array(
							'label' => __( 'Product tag(s)', 'cartflows-pro' ),
							'value' => 'cart_item_tag',
						),
						array(
							'label' => __( 'Total', 'cartflows-pro' ),
							'value' => 'cart_total',
						),
						array(
							'label' => __( 'Coupon(s)', 'cartflows-pro' ),
							'value' => 'cart_coupons',
						),
						array(
							'label' => __( 'Shipping method', 'cartflows-pro' ),
							'value' => 'cart_shipping_method',
						),
						array(
							'label' => __( 'Payment Method', 'cartflows-pro' ),
							'value' => 'cart_payment_method',
						),
					),
				),

				array(
					'title'   => __( 'Geography', 'cartflows-pro' ),
					'isopt'   => true,
					'options' => array(
						array(
							'label' => __( 'Shipping country', 'cartflows-pro' ),
							'value' => 'cart_shipping_country',
						),
						array(
							'label' => __( 'Billing country', 'cartflows-pro' ),
							'value' => 'cart_billing_country',
						),
					),
				),
				array(
					'title'   => __( 'Custom Fields', 'cartflows-pro' ),
					'isopt'   => true,
					'options' => array(
						array(
							'label' => __( 'Order Custom Field', 'cartflows-pro' ),
							'value' => 'order_custom_field',
						),
					),
				),
			),
			'field_data' => array(
				'cart_item'             => array(
					'operator' => $string_operators,
					'fields'   => array(
						array(
							'type'        => 'product',
							'placeholder' => __( 'Search for products..', 'cartflows-pro' ),
							'isMulti'     => true,
						),
					),
				),
				'cart_item_category'    => array(
					'operator' => $string_operators,
					'fields'   => array(
						array(
							'type'        => 'select2',
							'options'     => $this->get_product_categories(),
							'placeholder' => __( 'Search for products cat..', 'cartflows-pro' ),
							'isMulti'     => true,
						),
					),
				),
				'cart_item_tag'         => array(
					'operator' => $string_operators,
					'fields'   => array(
						array(
							'type'        => 'select2',
							'options'     => $this->get_product_tags(),
							'placeholder' => __( 'Search for products tags..', 'cartflows-pro' ),
							'isMulti'     => true,
						),
					),
				),
				'cart_total'            => array(
					'operator' => $math_operators,
					'fields'   => array(
						array(
							'type' => 'number',
						),
					),
				),
				'cart_coupons'          => array(
					'operator' => $coupon_operators,
					'fields'   => array(
						array(
							'type'        => 'coupon',
							'placeholder' => __( 'Search for coupons..', 'cartflows-pro' ),
							'isMulti'     => true,
						),
					),
				),
				'cart_shipping_method'  => array(
					'operator' => $shipping_operators,
					'fields'   => array(
						array(
							'type'        => 'select2',
							'placeholder' => __( 'Search for shipping methods..', 'cartflows-pro' ),
							'isMulti'     => true,
							'options'     => $this->get_shipping_methods(),
						),
					),
				),
				'cart_shipping_country' => array(
					'operator' => $shipping_operators,
					'fields'   => array(
						array(
							'type'        => 'select2',
							'placeholder' => __( 'Search for country..', 'cartflows-pro' ),
							'isMulti'     => true,
							'options'     => $this->get_allowed_countries(),
						),
					),
				),
				'cart_billing_country'  => array(
					'operator' => $shipping_operators,
					'fields'   => array(
						array(
							'type'        => 'select2',
							'placeholder' => __( 'Search for country..', 'cartflows-pro' ),
							'isMulti'     => true,
							'options'     => $this->get_allowed_countries(),
						),
					),
				),
				'cart_payment_method'   => array(
					'operator' => $shipping_operators,
					'fields'   => array(
						array(
							'type'        => 'select2',
							'placeholder' => __( 'Search for payment method..', 'cartflows-pro' ),
							'isMulti'     => true,
							'options'     => $this->get_supported_payment_methods(),
						),
					),
				),
				'order_custom_field'    => array(
					'operator' => $custom_fields_operators,
					'fields'   => array(
						array(
							'type'   => 'multitext',
							'fields' => array(
								array(
									'placeholder' => __( 'Enter custom field meta key', 'cartflows-pro' ),
									'name'        => 'meta_key',
								),
								array(
									'placeholder' => __( 'Expected custom field meta value', 'cartflows-pro' ),
									'name'        => 'meta_value',
								),
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Add pro checkout settings.
	 *
	 * @param array $settings settings.
	 */
	public function add_checkout_settings_pro_fields( $settings ) {
		$settings['settings']['advanced']['fields']['wcf-animate-browser-tab']      = array(
			'type'         => 'toggle',
			'label'        => __( 'Enable Browser Tab Animation', 'cartflows-pro' ),
			'name'         => 'wcf-animate-browser-tab',
			'is_fullwidth' => true,
		);
		$settings['settings']['advanced']['fields']['wcf-animate-browser-tab-text'] = array(
			'type'          => 'text',
			'label'         => __( 'Title Text', 'cartflows-pro' ),
			'name'          => 'wcf-animate-browser-tab-title',
			'display_align' => 'vertical',
			'conditions'    => array(
				'fields' => array(
					array(
						'name'     => 'wcf-animate-browser-tab',
						'operator' => '===',
						'value'    => 'yes',
					),
				),
			),
		);

		return $settings;
	}

	/**
	 * Get supported payment methods.
	 */
	public function get_supported_payment_methods() {

		$payment_method_found = array();

		if ( ! class_exists( 'Cartflows_Pro_Gateways' ) ) {
			return $payment_method_found;
		}

		$supported_gateways = array_keys( Cartflows_Pro_Gateways::get_instance()->get_supported_gateways() );

		$woo_available_gateways = WC()->payment_gateways->get_available_payment_gateways();

		foreach ( $woo_available_gateways as $method_id => $method ) {

			if ( in_array( $method_id, $supported_gateways, true ) ) {
				array_push(
					$payment_method_found,
					array(
						'value' => $method_id,
						'label' => $method->method_title,
					)
				);
			}
		}

		return $payment_method_found;
	}


	/**
	 * Get shipping methods.
	 */
	public function get_shipping_methods() {

		$shipping_method_found = array();

		foreach ( WC()->shipping()->get_shipping_methods() as $method_id => $method ) {
			array_push(
				$shipping_method_found,
				array(
					'value' => $method_id,
					'label' => $method->get_method_title(),
				)
			);
		}

		return $shipping_method_found;
	}

	/**
	 * Get countries list.
	 */
	public function get_allowed_countries() {

		$countries = WC()->countries->get_allowed_countries();

		$countries_found = array();

		if ( $countries ) {

			foreach ( $countries as $key => $country ) {
				array_push(
					$countries_found,
					array(
						'value' => $key,
						'label' => $country,
					)
				);
			}
		}

		return $countries_found;
	}

	/**
	 * Get product categories.
	 */
	public function get_product_categories() {

		$categories = get_terms( 'product_cat', array( 'hide_empty' => false ) );

		$category_found = array();

		if ( $categories && ! is_wp_error( $categories ) ) {

			foreach ( $categories as $category ) {
				array_push(
					$category_found,
					array(
						'value' => $category->term_id,
						'label' => $category->name,
					)
				);
			}
		}

		return $category_found;
	}

	/**
	 * Get product tags.
	 */
	public function get_product_tags() {

		$terms = get_terms( 'product_tag', array( 'hide_empty' => false ) );

		$tags_found = array();

		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				array_push(
					$tags_found,
					array(
						'value' => $term->term_id,
						'label' => $term->name,
					)
				);
			}
		}

		return $tags_found;
	}

	/**
	 * Filter checkout values
	 *
	 * @param  array $options options.
	 * @param  int   $step_id step id.
	 */
	public function filter_values( $options, $step_id ) {

		$admin_helper = Cartflows_Pro_Admin_Helper::get_instance();

		// @todo Remove this code after v1.7.4 update.
		// Start.
		if ( ! empty( $options['wcf-order-bump-product'][0] ) ) {

			$product_id                        = intval( $options['wcf-order-bump-product'][0] );
			$options['wcf-order-bump-product'] = $admin_helper::get_products_label( array( $product_id ) );
		}

		if ( ! empty( $options['wcf-order-bump-discount-coupon'][0] ) ) {

			$all_discount_types = wc_get_coupon_types();

			$coupon_code   = $options['wcf-order-bump-discount-coupon'][0];
			$coupon_data   = new WC_Coupon( $coupon_code );
			$coupon_id     = $coupon_data->get_id();
			$discount_type = get_post_meta( $coupon_id, 'discount_type', true );

			if ( $discount_type ) {
				$options['wcf-order-bump-discount-coupon'] = array(
					'value' => $coupon_code,
					'label' => get_the_title( $coupon_id ) . ' (Type: ' . $all_discount_types[ $discount_type ] . ')',
				);
			}
		}
		// End.

		if ( ! empty( $options['wcf-pre-checkout-offer-product'][0] ) ) {
			$product_id                                = intval( $options['wcf-pre-checkout-offer-product'][0] );
			$options['wcf-pre-checkout-offer-product'] = $admin_helper::get_products_label( array( $product_id ) );
		}

		if ( ! empty( $options['wcf-checkout-discount-coupon'][0] ) ) {

			$all_discount_types = wc_get_coupon_types();

			$coupon_code   = $options['wcf-checkout-discount-coupon'][0];
			$coupon_data   = new WC_Coupon( $coupon_code );
			$coupon_id     = $coupon_data->get_id();
			$discount_type = get_post_meta( $coupon_id, 'discount_type', true );

			if ( $discount_type ) {
				$options['wcf-checkout-discount-coupon'] = array(
					'value' => $coupon_code,
					'label' => get_the_title( $coupon_id ) . ' (Type: ' . $all_discount_types[ $discount_type ] . ')',
				);
			}
		}

		if ( ( isset( $options['wcf-checkout-rules-option'] ) && 'yes' === $options['wcf-checkout-rules-option'] ) && isset( $options['wcf-checkout-rules'] ) ) {
			$options['wcf-checkout-rules'] = $this->filter_checkout_rules_values( $options['wcf-checkout-rules'] );
		}

		return $options;
	}

		/**
		 * Filter checkout rules.
		 *
		 * @param array $conditions conditions data.
		 */
	public function filter_checkout_rules_values( $conditions ) {

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

		$admin_helper = Cartflows_Pro_Admin_Helper::get_instance();

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
	 * Add meta fields
	 *
	 * @param array $settings checkout fields.
	 * @param int   $step_id step id.
	 * @param array $options options.
	 */
	public function meta_fields_react( $settings, $step_id, $options ) {

		$flow_id = get_post_meta( $step_id, 'wcf-flow-id', true );

		$opt_steps = Cartflows_Pro_Admin_Helper::get_opt_steps( $step_id );

		if ( cartflows_pro_is_active_license() ) {
			$settings['settings']['coupon']['fields'] = array(
				'coupon'     => array(
					'type'        => 'coupon',
					'name'        => 'wcf-checkout-discount-coupon',
					'label'       => __( 'Select Coupon', 'cartflows-pro' ),
					'placeholder' => __( 'Search for a coupon', 'cartflows-pro' ),
					'multiple'    => false,
					'allow_clear' => true,
				),
				'coupon-doc' => array(
					'type'    => 'doc',
					/* translators: %1$1s: link html start, %2$12: link html end*/
					'content' => sprintf( __( 'For more information about the CartFlows coupon please %1$1s Click here.%2$2s', 'cartflows-pro' ), '<a href="https://cartflows.com/docs/enable-coupons-on-cartflows-page/" target="_blank" class="!text-gray-600">', '</a>' ),
				),
			);

			$settings['settings']['product-options']['fields'] = array(
				'wcf-enable-product-options'              => array(
					'type'         => 'toggle',
					'label'        => __( 'Enable Product Options', 'cartflows-pro' ),
					'name'         => 'wcf-enable-product-options',
					'is_fullwidth' => true,
				),

				'wcf-product-options'                     => array(
					'type'       => 'radio',
					'label'      => __( 'Enable Conditions', 'cartflows-pro' ),
					'name'       => 'wcf-product-options',
					'options'    => array(
						array(
							'value' => 'force-all',
							'label' => __( 'Restrict user to purchase all products', 'cartflows-pro' ),
						),
						array(
							'value' => 'single-selection',
							'label' => __( 'Let user select one product from all options', 'cartflows-pro' ),
						),
						array(
							'value' => 'multiple-selection',
							'label' => __( 'Let user select multiple products from all options', 'cartflows-pro' ),
						),
					),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-enable-product-options',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),
				'wcf-product-options-seperator'           => array(
					'type'       => 'separator',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-enable-product-options',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),

				'enable-variation'                        => array(
					'type'         => 'toggle',
					'label'        => __( 'Enable Variations', 'cartflows-pro' ),
					'name'         => 'wcf-enable-product-variation',
					'is_fullwidth' => true,
					'conditions'   => array(
						'fields' => array(
							array(
								'name'     => 'wcf-enable-product-options',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),
				'wcf-product-variation-options'           => array(
					'type'        => 'radio',
					'label'       => '',
					'name'        => 'wcf-product-variation-options',
					'child_class' => 'wcf-child-field',
					'options'     => array(
						array(
							'value' => 'inline',
							'label' => __( 'Show variations inline', 'cartflows-pro' ),
						),
						array(
							'value' => 'popup',
							'label' => __( 'Show variations in popup', 'cartflows-pro' ),
						),
					),
					'conditions'  => array(
						'fields' => array(
							array(
								'name'     => 'wcf-enable-product-options',
								'operator' => '===',
								'value'    => 'yes',
							),
							array(
								'name'     => 'wcf-enable-product-variation',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),
				'wcf-product-variation-options-seperator' => array(
					'type'       => 'separator',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-enable-product-options',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),
				'wcf-enable-product-quantity'             => array(
					'type'         => 'toggle',
					'label'        => __( 'Enable Quantity', 'cartflows-pro' ),
					'name'         => 'wcf-enable-product-quantity',
					'is_fullwidth' => true,
					'conditions'   => array(
						'fields' => array(
							array(
								'name'     => 'wcf-enable-product-options',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),
				'product-option-doc'                      => array(
					'type'       => 'doc',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-enable-product-options',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
					/* translators: %1$1s: link html start, %2$12: link html end*/
					'content'    => sprintf( __( 'For more information about the product option settings %1$1s Click here. %2$2s', 'cartflows-pro' ), '<a href="https://cartflows.com/docs/set-default-product-in-product-options/" target="_blank">', '</a>' ),
				),

			);
		}

			// Multiple order bump options arrays start.
			$settings['settings']['multiple-order-bump-product']['fields'] = array(

				'wcf-ob-replace'                  => array(
					'type'  => 'toggle',
					'label' => __( 'Replace First Product', 'cartflows-pro' ),
					'name'  => 'replace_product',
					/* translators: %1$1s, %2$2s Link to meta */
					'desc'  => sprintf(
						__( 'It will replace the first selected product (from checkout products) with the order bump product. %1$1sLearn More »%2$2s', 'cartflows-pro' ), //phpcs:ignore
						'<a href="https://cartflows.com/docs/replace-first-product-with-order-bump-checkout-page/" target="_blank">',
						'</a>'
					),

				),

				'wcf-ob-default-state-seperator'  => array(
					'type' => 'separator',
				),

				'wcf-ob-default-state'            => array(
					'type'  => 'toggle',
					'label' => __( 'Show Pre-Checked', 'cartflows-pro' ),
					'name'  => 'default_state',
					'desc'  => __( 'It will pre-check this order bump and add the assigned product to the cart on the checkout page.', 'cartflows-pro' ),
				),

				'wcf-ob-quantity-field-seperator' => array(
					'type' => 'separator',
				),

				'wcf-ob-quantity-field'           => array(
					'type'  => 'toggle',
					'label' => __( 'Enable Quantity Field', 'cartflows-pro' ),
					'name'  => 'display_quantity_field',
					'desc'  => __( 'It will display the quantity field in the order bump description.', 'cartflows-pro' ),
				),

			);

			if ( class_exists( 'Cartflows_Helper' ) && Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' ) === $flow_id ) {
				unset( $settings['settings']['multiple-order-bump-product']['fields']['wcf-ob-replace'] );
			}

			$settings['settings']['multiple-order-bump-design']['fields'] = array(

				// Array of fields which are Categorized in sections.
				'ob-section-layout'      => array(
					'id'             => 'layout',
					'type'           => 'section',
					'title'          => __( 'Layout', 'cartflows-pro' ),
					'section_fields' => array(
						'wcf-ob-skin'     => array(
							'type'          => 'select',
							'label'         => __( 'Order Bump Skin', 'cartflows-pro' ),
							'name'          => 'style',
							'options'       => array(
								array(
									'value' => 'style-1',
									'label' => esc_html__( 'Style 1', 'cartflows-pro' ),
								),
								array(
									'value' => 'style-2',
									'label' => esc_html__( 'Style 2', 'cartflows-pro' ),
								),
								array(
									'value' => 'style-3',
									'label' => esc_html__( 'Style 3', 'cartflows-pro' ),
								),
								array(
									'value' => 'style-4',
									'label' => esc_html__( 'Style 4', 'cartflows-pro' ),
								),
								array(
									'value' => 'style-5',
									'label' => esc_html__( 'Style 5', 'cartflows-pro' ),
								),
							),
							'display_align' => 'vertical',
						),
						'wcf-ob-width'    => array(
							'type'          => 'select',
							'label'         => __( 'Width', 'cartflows-pro' ),
							'name'          => 'width',
							'options'       => array(
								array(
									'value' => '50',
									'label' => esc_html__( '50%', 'cartflows-pro' ),
								),
								array(
									'value' => '100',
									'label' => esc_html__( '100%', 'cartflows-pro' ),
								),
							),
							'display_align' => 'vertical',
						),
						'wcf-ob-position' => array(
							'type'          => 'select',
							'label'         => __( 'Position', 'cartflows-pro' ),
							'name'          => 'position',
							'options'       => array(
								array(
									'value' => 'before-checkout',
									'label' => esc_html__( 'Before Checkout', 'cartflows-pro' ),
								),
								array(
									'value' => 'after-customer',
									'label' => esc_html__( 'After Customer Details', 'cartflows-pro' ),
								),
								array(
									'value' => 'after-order',
									'label' => esc_html__( 'After Order', 'cartflows-pro' ),
								),
								array(
									'value' => 'after-payment',
									'label' => esc_html__( 'After Payment', 'cartflows-pro' ),
								),
							),
							'display_align' => 'vertical',
						),
					),
				),
				'ob-section-colors'      => array(
					'id'             => 'colors',
					'type'           => 'section',
					'title'          => __( 'Colors', 'cartflows-pro' ),
					'section_fields' => array(
						'wcf-ob-desc-color'            => array(
							'type'  => 'color-picker',
							'label' => __( 'Description Text Color', 'cartflows-pro' ),
							'name'  => 'desc_text_color',
						),
						'wcf-ob-title-color'           => array(
							'type'       => 'color-picker',
							'label'      => __( 'Title Text Color', 'cartflows-pro' ),
							'name'       => 'title_text_color',
							'conditions' => array(
								'fields' => array(
									array(
										'name'     => 'style',
										'operator' => 'in',
										'value'    => array( 'style-3', 'style-4', 'style-5' ),
									),
								),
							),
						),
						'wcf-ob-label-color'           => array(
							'type'       => 'color-picker',
							'label'      => __( 'Label Text Color', 'cartflows-pro' ),
							'name'       => 'label_color',
							'conditions' => array(
								'relation' => 'or',
								'fields'   => array(
									array(
										'name'     => 'style',
										'operator' => 'in',
										'value'    => array( 'style-1', 'style-2' ),
									),
									array(
										'relation' => 'and',
										'fields'   => array(
											array(
												'name'     => 'action_element',
												'operator' => '==',
												'value'    => 'checkbox',
											),
											array(
												'name'     => 'style',
												'operator' => '==',
												'value'    => 'style-5',
											),
										),
									),
								),
							),
						),
						'wcf-ob-label-bg-color'        => array(
							'type'       => 'color-picker',
							'label'      => __( 'Label Background Color', 'cartflows-pro' ),
							'name'       => 'label_bg_color',
							'conditions' => array(
								'relation' => 'or',
								'fields'   => array(
									array(
										'name'     => 'style',
										'operator' => 'in',
										'value'    => array( 'style-1', 'style-2' ),
									),
									array(
										'relation' => 'and',
										'fields'   => array(
											array(
												'name'     => 'action_element',
												'operator' => '==',
												'value'    => 'checkbox',
											),
											array(
												'name'     => 'style',
												'operator' => '==',
												'value'    => 'style-5',
											),
										),
									),
								),
							),
						),
						'wcf-ob-button-text-color'     => array(
							'type'       => 'color-picker',
							'label'      => __( 'Button Text Color', 'cartflows-pro' ),
							'name'       => 'button_text_color',
							'conditions' => array(
								'relation' => 'or',
								'fields'   => array(
									array(
										'name'     => 'style',
										'operator' => 'in',
										'value'    => array( 'style-4' ),
									),
									array(
										'relation' => 'and',
										'fields'   => array(
											array(
												'name'     => 'action_element',
												'operator' => '==',
												'value'    => 'button',
											),
											array(
												'name'     => 'style',
												'operator' => '==',
												'value'    => 'style-5',
											),
										),
									),
								),
							),
						),
						'wcf-ob-button-text-hover-bg-color' => array(
							'type'       => 'color-picker',
							'label'      => __( 'Button Text Hover Color', 'cartflows-pro' ),
							'name'       => 'button_text_hover_color',
							'conditions' => array(
								'relation' => 'or',
								'fields'   => array(
									array(
										'name'     => 'style',
										'operator' => 'in',
										'value'    => array( 'style-4' ),
									),
									array(
										'relation' => 'and',
										'fields'   => array(
											array(
												'name'     => 'action_element',
												'operator' => '==',
												'value'    => 'button',
											),
											array(
												'name'     => 'style',
												'operator' => '==',
												'value'    => 'style-5',
											),
										),
									),
								),
							),
						),
						'wcf-ob-button-text-bg-color'  => array(
							'type'       => 'color-picker',
							'label'      => __( 'Button Background Color', 'cartflows-pro' ),
							'name'       => 'button_color',
							'conditions' => array(
								'relation' => 'or',
								'fields'   => array(
									array(
										'name'     => 'style',
										'operator' => 'in',
										'value'    => array( 'style-4' ),
									),
									array(
										'relation' => 'and',
										'fields'   => array(
											array(
												'name'     => 'action_element',
												'operator' => '==',
												'value'    => 'button',
											),
											array(
												'name'     => 'style',
												'operator' => '==',
												'value'    => 'style-5',
											),
										),
									),
								),
							),
						),
						'wcf-ob-button-hover-bg-color' => array(
							'type'       => 'color-picker',
							'label'      => __( 'Button Background Hover Color', 'cartflows-pro' ),
							'name'       => 'button_hover_color',
							'conditions' => array(
								'relation' => 'or',
								'fields'   => array(
									array(
										'name'     => 'style',
										'operator' => 'in',
										'value'    => array( 'style-4' ),
									),
									array(
										'relation' => 'and',
										'fields'   => array(
											array(
												'name'     => 'action_element',
												'operator' => '==',
												'value'    => 'button',
											),
											array(
												'name'     => 'style',
												'operator' => '==',
												'value'    => 'style-5',
											),
										),
									),
								),
							),
						),
						'wcf-ob-highlight-text-color'  => array(
							'type'       => 'color-picker',
							'label'      => __( 'Highlight Text Color', 'cartflows-pro' ),
							'name'       => 'hl_text_color',
							'conditions' => array(
								'fields' => array(
									array(
										'name'     => 'style',
										'operator' => '!in',
										'value'    => array( 'style-3', 'style-4', 'style-5' ),
									),
								),
							),
						),
						'wcf-ob-bg-color'              => array(
							'type'  => 'color-picker',
							'label' => __( 'Box Background Color', 'cartflows-pro' ),
							'name'  => 'bg_color',
						),
					),
				),
				'ob-section-borders'     => array(
					'id'             => 'borders',
					'type'           => 'section',
					'title'          => __( 'Borders', 'cartflows-pro' ),
					'section_fields' => array(
						'wcf-ob-label-border-style'   => array(
							'type'          => 'select',
							'label'         => __( 'Label Border Style', 'cartflows-pro' ),
							'name'          => 'label_border_style',
							'options'       => array(
								array(
									'value' => 'inherit',
									'label' => esc_html__( 'Default', 'cartflows-pro' ),
								),
								array(
									'value' => 'dashed',
									'label' => esc_html__( 'Dashed', 'cartflows-pro' ),
								),
								array(
									'value' => 'dotted',
									'label' => esc_html__( 'Dotted', 'cartflows-pro' ),
								),
								array(
									'value' => 'solid',
									'label' => esc_html__( 'Solid', 'cartflows-pro' ),
								),
								array(
									'value' => 'none',
									'label' => esc_html__( 'None', 'cartflows-pro' ),
								),

							),
							'conditions'    => array(
								'fields' => array(
									array(
										'name'     => 'style',
										'operator' => 'in',
										'value'    => array( 'style-5' ),
									),
									array(
										'name'     => 'action_element',
										'operator' => '===',
										'value'    => 'checkbox',
									),
								),
							),
							'display_align' => 'vertical',
						),
						'wcf-ob-label-border-width'   => array(
							'type'          => 'number',
							'label'         => __( 'Label Border Width', 'cartflows-pro' ),
							'name'          => 'label_border_width',
							'min'           => '0',
							'afterfield'    => 'px',
							'width'         => '80px',
							'conditions'    => array(
								'fields' => array(
									array(
										'name'     => 'style',
										'operator' => 'in',
										'value'    => array( 'style-5' ),
									),
									array(
										'name'     => 'action_element',
										'operator' => '===',
										'value'    => 'checkbox',
									),
								),
							),
							'display_align' => 'vertical',
						),
						'wcf-ob-label-border-radius'  => array(
							'type'          => 'number',
							'label'         => __( 'Label Border Radius', 'cartflows-pro' ),
							'name'          => 'label_border_radius',
							'min'           => '0',
							'afterfield'    => 'px',
							'width'         => '80px',
							'conditions'    => array(
								'fields' => array(
									array(
										'name'     => 'style',
										'operator' => 'in',
										'value'    => array( 'style-5' ),
									),
									array(
										'name'     => 'action_element',
										'operator' => '===',
										'value'    => 'checkbox',
									),
								),
							),
							'display_align' => 'vertical',
						),
						'wcf-ob-label-border-color'   => array(
							'type'          => 'color-picker',
							'label'         => __( 'label Border Color', 'cartflows-pro' ),
							'name'          => 'label_border_color',
							'withBg'        => true,
							'conditions'    => array(
								'fields' => array(
									array(
										'name'     => 'style',
										'operator' => 'in',
										'value'    => array( 'style-5' ),
									),
									array(
										'name'     => 'action_element',
										'operator' => '===',
										'value'    => 'checkbox',
									),
								),
							),
							'display_align' => 'vertical',
						),

						'wcf-ob-button-border-style'  => array(
							'type'          => 'select',
							'label'         => __( 'Button Border Style', 'cartflows-pro' ),
							'name'          => 'button_border_style',
							'options'       => array(
								array(
									'value' => 'inherit',
									'label' => esc_html__( 'Default', 'cartflows-pro' ),
								),
								array(
									'value' => 'dashed',
									'label' => esc_html__( 'Dashed', 'cartflows-pro' ),
								),
								array(
									'value' => 'dotted',
									'label' => esc_html__( 'Dotted', 'cartflows-pro' ),
								),
								array(
									'value' => 'solid',
									'label' => esc_html__( 'Solid', 'cartflows-pro' ),
								),
								array(
									'value' => 'none',
									'label' => esc_html__( 'None', 'cartflows-pro' ),
								),

							),
							'conditions'    => array(
								'relation' => 'or',
								'fields'   => array(
									array(
										'name'     => 'style',
										'operator' => 'in',
										'value'    => array( 'style-4' ),
									),
									array(
										'relation' => 'and',
										'fields'   => array(
											array(
												'name'     => 'action_element',
												'operator' => '==',
												'value'    => 'button',
											),
											array(
												'name'     => 'style',
												'operator' => '==',
												'value'    => 'style-5',
											),
										),
									),
								),
							),
							'display_align' => 'vertical',
						),
						'wcf-ob-button-border-width'  => array(
							'type'          => 'number',
							'label'         => __( 'Button Border Width', 'cartflows-pro' ),
							'name'          => 'button_border_width',
							'min'           => '0',
							'afterfield'    => 'px',
							'width'         => '80px',
							'conditions'    => array(
								'relation' => 'or',
								'fields'   => array(
									array(
										'name'     => 'style',
										'operator' => 'in',
										'value'    => array( 'style-4' ),
									),
									array(
										'relation' => 'and',
										'fields'   => array(
											array(
												'name'     => 'action_element',
												'operator' => '==',
												'value'    => 'button',
											),
											array(
												'name'     => 'style',
												'operator' => '==',
												'value'    => 'style-5',
											),
										),
									),
								),
							),
							'display_align' => 'vertical',
						),
						'wcf-ob-button-border-radius' => array(
							'type'          => 'number',
							'label'         => __( 'Button Border Radius', 'cartflows-pro' ),
							'name'          => 'button_border_radius',
							'min'           => '0',
							'afterfield'    => 'px',
							'width'         => '80px',
							'conditions'    => array(
								'relation' => 'or',
								'fields'   => array(
									array(
										'name'     => 'style',
										'operator' => 'in',
										'value'    => array( 'style-4' ),
									),
									array(
										'relation' => 'and',
										'fields'   => array(
											array(
												'name'     => 'action_element',
												'operator' => '==',
												'value'    => 'button',
											),
											array(
												'name'     => 'style',
												'operator' => '==',
												'value'    => 'style-5',
											),
										),
									),
								),
							),
							'display_align' => 'vertical',
						),
						'wcf-ob-button-border-color'  => array(
							'type'          => 'color-picker',
							'withBg'        => true,
							'label'         => __( 'Button Border Color', 'cartflows-pro' ),
							'name'          => 'button_border_color',
							'conditions'    => array(
								'relation' => 'or',
								'fields'   => array(
									array(
										'name'     => 'style',
										'operator' => 'in',
										'value'    => array( 'style-4' ),
									),
									array(
										'relation' => 'and',
										'fields'   => array(
											array(
												'name'     => 'action_element',
												'operator' => '==',
												'value'    => 'button',
											),
											array(
												'name'     => 'style',
												'operator' => '==',
												'value'    => 'style-5',
											),
										),
									),
								),
							),
							'display_align' => 'vertical',
							'withBg'        => true,
						),

						'wcf-ob-box-border-style'     => array(
							'type'          => 'select',
							'label'         => __( 'Box Border Style', 'cartflows-pro' ),
							'name'          => 'border_style',
							'options'       => array(
								array(
									'value' => 'inherit',
									'label' => esc_html__( 'Default', 'cartflows-pro' ),
								),
								array(
									'value' => 'dashed',
									'label' => esc_html__( 'Dashed', 'cartflows-pro' ),
								),
								array(
									'value' => 'dotted',
									'label' => esc_html__( 'Dotted', 'cartflows-pro' ),
								),
								array(
									'value' => 'solid',
									'label' => esc_html__( 'Solid', 'cartflows-pro' ),
								),
								array(
									'value' => 'none',
									'label' => esc_html__( 'None', 'cartflows-pro' ),
								),

							),
							'display_align' => 'vertical',
						),
						'wcf-ob-box-border-width'     => array(
							'type'          => 'number',
							'label'         => __( 'Box Border Width', 'cartflows-pro' ),
							'name'          => 'box_border_width',
							'min'           => '0',
							'afterfield'    => 'px',
							'width'         => '80px',
							'display_align' => 'vertical',
						),
						'wcf-ob-box-border-radius'    => array(
							'type'          => 'number',
							'label'         => __( 'Box Border Radius', 'cartflows-pro' ),
							'name'          => 'box_border_radius',
							'min'           => '0',
							'afterfield'    => 'px',
							'width'         => '80px',
							'display_align' => 'vertical',
						),
						'wcf-ob-box-border-color'     => array(
							'type'          => 'color-picker',
							'withBg'        => true,
							'label'         => __( 'Box Border Color', 'cartflows-pro' ),
							'name'          => 'border_color',
							'display_align' => 'vertical',
						),
					),
				),
				'ob-section-shadow'      => array(
					'id'             => 'shadow',
					'type'           => 'section',
					'title'          => __( 'Shadows', 'cartflows-pro' ),
					'section_fields' => array(
						'wcf-ob-box-shadow-horizontal' => array(
							'type'          => 'number',
							'label'         => __( 'Horizontal', 'cartflows-pro' ),
							'name'          => 'box_shadow_horizontal',
							'min'           => '0',
							'afterfield'    => 'px',
							'width'         => '80px',
							'display_align' => 'vertical',
						),
						'wcf-ob-box-shadow-vertical'   => array(
							'type'          => 'number',
							'label'         => __( 'Vertical', 'cartflows-pro' ),
							'name'          => 'box_shadow_vertical',
							'min'           => '0',
							'afterfield'    => 'px',
							'width'         => '80px',
							'display_align' => 'vertical',
						),
						'wcf-ob-box-shadow-blur'       => array(
							'type'          => 'number',
							'label'         => __( 'Blur', 'cartflows-pro' ),
							'name'          => 'box_shadow_blur',
							'min'           => '0',
							'afterfield'    => 'px',
							'width'         => '80px',
							'display_align' => 'vertical',
						),
						'wcf-ob-box-shadow-spread'     => array(
							'type'          => 'number',
							'label'         => __( 'Spread', 'cartflows-pro' ),
							'name'          => 'box_shadow_spread',
							'min'           => '0',
							'afterfield'    => 'px',
							'width'         => '80px',
							'display_align' => 'vertical',
						),
						'wcf-ob-box-shadow-color'      => array(
							'type'          => 'color-picker',
							'label'         => __( 'Box Shadow Color', 'cartflows-pro' ),
							'name'          => 'box_shadow_color',
							'display_align' => 'vertical',
							'withBg'        => true,
						),
					),
				),

				// Array of fields which are kept outside the section.
				'wcf-ob-arrow'           => array(
					'type'       => 'toggle',
					'label'      => __( 'Enable Arrow ', 'cartflows-pro' ),
					'name'       => 'show_arrow',
					'conditions' => array(
						'relation' => 'or',
						'fields'   => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-1', 'style-2', 'style-3' ),
							),
							array(
								'relation' => 'and',
								'fields'   => array(
									array(
										'name'     => 'action_element',
										'operator' => '==',
										'value'    => 'checkbox',
									),
									array(
										'name'     => 'style',
										'operator' => '==',
										'value'    => 'style-5',
									),
								),
							),
						),
					),
				),
				'wcf-ob-arrow-animation' => array(
					'type'       => 'toggle',
					'label'      => __( 'Enable Animation ', 'cartflows-pro' ),
					'name'       => 'show_animation',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'show_arrow',
								'operator' => '===',
								'value'    => 'yes',
							),
							array(
								'name'     => 'style',
								'operator' => '!in',
								'value'    => array( 'style-4' ),
							),
							array(
								'relation' => 'and',
								'fields'   => array(
									array(
										'name'     => 'action_element',
										'operator' => '===',
										'value'    => 'checkbox',
									),
								),
							),
						),
					),
				),

			);
			$settings['settings']['multiple-order-bump-content']['fields'] = array(

				'heading-ob-bump-action'     => array(
					'type'       => 'heading',
					'label'      => esc_html__( 'Action Element', 'cartflows-pro' ),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-5' ),
							),
						),
					),
				),

				'wcf-ob-action-element'      => array(
					'type'       => 'select',
					'label'      => __( 'Element', 'cartflows-pro' ),
					'name'       => 'action_element',
					'options'    => array(
						array(
							'value' => 'checkbox',
							'label' => esc_html__( 'Checkbox', 'cartflows-pro' ),
						),
						array(
							'value' => 'button',
							'label' => esc_html__( 'Button', 'cartflows-pro' ),
						),
					),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-5' ),
							),
						),
					),
				),

				'wcf-ob-label'               => array(
					'type'          => 'text',
					'label'         => __( 'Checkbox Label', 'cartflows-pro' ),
					'name'          => 'checkbox_label',
					'conditions'    => array(
						'relation' => 'or',
						'fields'   => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-1', 'style-2' ),
							),
							array(
								'relation' => 'and',
								'fields'   => array(
									array(
										'name'     => 'action_element',
										'operator' => '==',
										'value'    => 'checkbox',
									),
									array(
										'name'     => 'style',
										'operator' => '==',
										'value'    => 'style-5',
									),
								),
							),
						),
					),
					'display_align' => 'vertical',
				),

				'wcf-ob-title'               => array(
					'type'          => 'text',
					'label'         => __( 'Title', 'cartflows-pro' ),
					'name'          => 'title_text',
					'conditions'    => array(
						'fields' => array(
							array(
								'name'     => 'style',
								'operator' => '!in',
								'value'    => array( 'style-1', 'style-2' ),
							),
						),
					),
					'display_align' => 'vertical',
				),

				'wcf-ob-highlight-text'      => array(
					'type'          => 'text',
					'label'         => __( 'Highlight Text', 'cartflows-pro' ),
					'name'          => 'hl_text',
					'conditions'    => array(
						'fields' => array(
							array(
								'name'     => 'style',
								'operator' => '!in',
								'value'    => array( 'style-3', 'style-4', 'style-5' ),
							),
						),
					),
					'display_align' => 'vertical',
				),
				'wcf-ob-product-desc'        => array(
					'type'          => 'wp-editor',
					'label'         => __( 'Description', 'cartflows-pro' ),
					'name'          => 'desc_text',
					'rows'          => '7',
					'cols'          => '39',
					'id'            => 'wcf-order-bump-desc-editor',
					'desc'          => __( 'Use {{product_name}}, {{product_desc}}, {{product_price}} & {{quantity}} to fetch respective product details.', 'cartflows-pro' ),
					'display_align' => 'vertical',
				),

				'wcf-ob-enable-image-option' => array(
					'type'  => 'checkbox',
					'label' => __( 'Enable Image Options', 'cartflows-pro' ),
					'name'  => 'enable_show_image',
				),

				'product-image'              => array(
					'type'          => 'image-selector',
					'label'         => __( 'Product Image', 'cartflows-pro' ),
					'name'          => 'product_image',
					'isNameArray'   => true,
					'objName'       => 'product_img_obj',
					'tooltip'       => __( 'By default, product image will be shown. If product image is not set then placeholder image will be used as product image.', 'cartflows-pro' ),
					'conditions'    => array(
						'fields' => array(
							array(
								'name'     => 'enable_show_image',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
					'desc'          => __( 'Minimum image size should be 300 X 300 in pixes for ideal display.', 'cartflows-pro' ),
					'display_align' => 'vertical',
				),

				'wcf-ob-image-position'      => array(
					'type'          => 'select',
					'label'         => __( 'Image Position', 'cartflows-pro' ),
					'name'          => 'ob_image_position',
					'options'       => array(
						array(
							'value' => 'left',
							'label' => esc_html__( 'Left', 'cartflows-pro' ),
						),
						array(
							'value' => 'top',
							'label' => esc_html__( 'Top', 'cartflows-pro' ),
						),
						array(
							'value' => 'right',
							'label' => esc_html__( 'Right', 'cartflows-pro' ),
						),
					),
					'conditions'    => array(
						'fields' => array(
							array(
								'name'     => 'enable_show_image',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
					'display_align' => 'vertical',
				),

				'wcf-ob-image-width'         => array(
					'type'          => 'number',
					'label'         => __( 'Image Width', 'cartflows-pro' ),
					'name'          => 'ob_image_width',
					'min'           => 1,
					'afterfield'    => 'px',
					'width'         => '80px',
					'tooltip'       => __( 'Keep value empty for 100% width', 'cartflows-pro' ),
					'conditions'    => array(
						'fields' => array(
							array(
								'name'     => 'enable_show_image',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
					'display_align' => 'vertical',
				),

				'wcf-ob-image-option'        => array(
					'type'       => 'toggle',
					'label'      => __( 'Show Image on Tab and Mobile', 'cartflows-pro' ),
					'name'       => 'show_image_mobile',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'enable_show_image',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),
			);
			$settings['settings']['multiple-order-bump-rules'] = $this->get_ob_rules_data();
			// Multiple order bump options arrays end.

			$common             = Cartflows_Helper::get_common_settings();
			$pre_checkout_offer = $common['pre_checkout_offer'];

			if ( 'enable' === $pre_checkout_offer ) {
				$settings['settings']['checkout-offer-product']['fields'] = array(
					'wcf-co-product'        => array(
						'type'                   => 'product',
						'label'                  => __( 'Select Product', 'cartflows-pro' ),
						'name'                   => 'wcf-pre-checkout-offer-product',
						'excluded_product_types' => array( 'grouped' ),
						'include_product_types'  => array( 'braintree-subscription', 'braintree-variable-subscription' ),
						'placeholder'            => __( 'Type to search for a product', 'cartflows-pro' ),
						'conditions'             => array(
							'fields' => array(
								array(
									'name'     => 'wcf-pre-checkout-offer',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),
					),
					'wcf-co-discount'       => array(
						'type'          => 'select',
						'label'         => __( 'Discount Type', 'cartflows-pro' ),
						'name'          => 'wcf-pre-checkout-offer-discount',
						'display_align' => 'vertical',
						'options'       => array(
							array(
								'value' => '',
								'label' => esc_html__( 'Original', 'cartflows-pro' ),
							),
							array(
								'value' => 'discount_percent',
								'label' => esc_html__( 'Discount Percentage', 'cartflows-pro' ),
							),
							array(
								'value' => 'discount_price',
								'label' => esc_html__( 'Discount Price', 'cartflows-pro' ),
							),
						),

						'conditions'    => array(
							'fields' => array(
								array(
									'name'     => 'wcf-pre-checkout-offer',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),
					),
					'wcf-co-dicount-value'  => array(
						'type'          => 'number',
						'label'         => __( 'Discount Value', 'cartflows-pro' ),
						'name'          => 'wcf-pre-checkout-offer-discount-value',
						'display_align' => 'vertical',
						'conditions'    => array(
							'fields' => array(
								array(
									'name'     => 'wcf-pre-checkout-offer',
									'operator' => '===',
									'value'    => 'yes',
								),
								array(
									'name'     => 'wcf-pre-checkout-offer-discount',
									'operator' => 'in',
									'value'    => array( 'discount_price', 'discount_percent' ),
								),
							),
						),
					),
					'wcf-co-original-price' => array(
						'type'          => 'text',
						'label'         => __( 'Original Price', 'cartflows-pro' ),
						'name'          => 'wcf-pre-checkout-offer-product[original_price]',
						'tooltip'       => __( 'This is the unit price of product', 'cartflows-pro' ),
						'readonly'      => true,
						'display_align' => 'vertical',
						'conditions'    => array(
							'fields' => array(
								array(
									'name'     => 'wcf-pre-checkout-offer',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),
					),
					'wcf-co-sell-price'     => array(
						'type'          => 'text',
						'label'         => __( 'Sell Price', 'cartflows-pro' ),
						'name'          => 'wcf-pre-checkout-offer-product[sell_price]',
						'tooltip'       => __( 'This is the unit discounted price of product', 'cartflows-pro' ),
						'readonly'      => true,
						'display_align' => 'vertical',
						'conditions'    => array(
							'fields' => array(
								array(
									'name'     => 'wcf-pre-checkout-offer',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),
					),
				);
				$settings['settings']['checkout-offer-content']['fields'] = array(
					'checkout-offer-title'            => array(
						'type'          => 'text',
						'label'         => __( 'Title Text', 'cartflows-pro' ),
						'name'          => 'wcf-pre-checkout-offer-popup-title',
						'placeholder'   => esc_html__( '{first_name}, Wait! Your Order Is Almost Complete...', 'cartflows-pro' ),
						'display_align' => 'vertical',
						'conditions'    => array(
							'fields' => array(
								array(
									'name'     => 'wcf-pre-checkout-offer',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),

					),
					'checkout-offer-subtitle'         => array(
						'type'          => 'text',
						'label'         => __( 'Sub-title Text', 'cartflows-pro' ),
						'name'          => 'wcf-pre-checkout-offer-popup-sub-title',
						'display_align' => 'vertical',
						'conditions'    => array(
							'fields' => array(
								array(
									'name'     => 'wcf-pre-checkout-offer',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),
					),
					'checkout-offer-product-title'    => array(
						'type'          => 'text',
						'label'         => __( 'Product Title', 'cartflows-pro' ),
						'name'          => 'wcf-pre-checkout-offer-product-title',
						'help'          => esc_html__( 'Enter to override default product title.', 'cartflows-pro' ),
						'display_align' => 'vertical',
						'conditions'    => array(
							'fields' => array(
								array(
									'name'     => 'wcf-pre-checkout-offer',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),
					),
					'checkout-offer-product-desc'     => array(
						'type'          => 'textarea',
						'label'         => __( 'Product Description', 'cartflows-pro' ),
						'name'          => 'wcf-pre-checkout-offer-desc',
						'placeholder'   => esc_html__( 'Write a few words about this awesome product and tell shoppers why they must get it. You may highlight this as "one time offer" and make it irresistible.', 'cartflows-pro' ),
						'display_align' => 'vertical',
						'conditions'    => array(
							'fields' => array(
								array(
									'name'     => 'wcf-pre-checkout-offer',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),
					),

					'checkout-offer-button-text'      => array(
						'type'          => 'text',
						'label'         => __( 'Order Button Text', 'cartflows-pro' ),
						'name'          => 'wcf-pre-checkout-offer-popup-btn-text',
						'placeholder'   => esc_html__( 'Yes, Add to My Order!', 'cartflows-pro' ),
						'display_align' => 'vertical',
						'conditions'    => array(
							'fields' => array(
								array(
									'name'     => 'wcf-pre-checkout-offer',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),
					),
					'checkout-offer-skip-button-text' => array(
						'type'          => 'text',
						'label'         => __( 'Skip Button Text', 'cartflows-pro' ),
						'name'          => 'wcf-pre-checkout-offer-popup-skip-btn-text',
						'placeholder'   => esc_html__( 'No, thanks!', 'cartflows-pro' ),
						'display_align' => 'vertical',
						'conditions'    => array(
							'fields' => array(
								array(
									'name'     => 'wcf-pre-checkout-offer',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),
					),

				);
				$settings['settings']['checkout-offer-styles']['fields'] = array(

					'checkout-offer-style=section' => array(
						'id'             => 'co-color',
						'type'           => 'color',
						'title'          => __( 'Colors', 'cartflows-pro' ),
						'section_fields' => array(

							'checkout-offer-navbar-color' => array(
								'type'       => 'color-picker',
								'label'      => __( 'NavBar Color', 'cartflows-pro' ),
								'name'       => 'wcf-pre-checkout-offer-navbar-color',
								'conditions' => array(
									'fields' => array(
										array(
											'name'     => 'wcf-pre-checkout-offer',
											'operator' => '===',
											'value'    => 'yes',
										),
									),
								),
							),

							'checkout-offer-title-color'  => array(
								'type'       => 'color-picker',
								'label'      => __( 'Title Color', 'cartflows-pro' ),
								'name'       => 'wcf-pre-checkout-offer-title-color',
								'conditions' => array(
									'fields' => array(
										array(
											'name'     => 'wcf-pre-checkout-offer',
											'operator' => '===',
											'value'    => 'yes',
										),
									),
								),
							),

							'checkout-offer-subtitle-color' => array(
								'type'       => 'color-picker',
								'label'      => __( 'Subtitle Color', 'cartflows-pro' ),
								'name'       => 'wcf-pre-checkout-offer-subtitle-color',
								'conditions' => array(
									'fields' => array(
										array(
											'name'     => 'wcf-pre-checkout-offer',
											'operator' => '===',
											'value'    => 'yes',
										),
									),
								),
							),

							'checkout-offer-desc-color'   => array(
								'type'       => 'color-picker',
								'label'      => __( 'Description Text Color', 'cartflows-pro' ),
								'name'       => 'wcf-pre-checkout-offer-desc-color',
								'conditions' => array(
									'fields' => array(
										array(
											'name'     => 'wcf-pre-checkout-offer',
											'operator' => '===',
											'value'    => 'yes',
										),
									),
								),
							),

							'checkout-offer-button-color' => array(
								'type'       => 'color-picker',
								'label'      => __( 'Button Text Color', 'cartflows-pro' ),
								'name'       => 'wcf-pre-checkout-offer-button-color',
								'conditions' => array(
									'fields' => array(
										array(
											'name'     => 'wcf-pre-checkout-offer',
											'operator' => '===',
											'value'    => 'yes',
										),
									),
								),
							),

							'checkout-offer-bg-color'     => array(
								'type'       => 'color-picker',
								'label'      => __( 'Overlay Background Color', 'cartflows-pro' ),
								'name'       => 'wcf-pre-checkout-offer-bg-color',
								'conditions' => array(
									'fields' => array(
										array(
											'name'     => 'wcf-pre-checkout-offer',
											'operator' => '===',
											'value'    => 'yes',
										),
									),
								),
							),

							'checkout-offer-overlay-bg-color' => array(
								'type'       => 'color-picker',
								'label'      => __( 'Background Color', 'cartflows-pro' ),
								'name'       => 'wcf-pre-checkout-offer-model-bg-color',
								'conditions' => array(
									'fields' => array(
										array(
											'name'     => 'wcf-pre-checkout-offer',
											'operator' => '===',
											'value'    => 'yes',
										),
									),
								),
							),
						),
					),

				);
			}

			$settings['settings']['checkout-settings']['fields']['wcf-animate-browser-tab'] = array(
				'type'         => 'toggle',
				'label'        => __( 'Enable Browser Tab Animation', 'cartflows-pro' ),
				'name'         => 'wcf-animate-browser-tab',
				'is_fullwidth' => true,
			);

			$settings['settings']['checkout-settings']['fields']['wcf-animate-browser-tab-text'] = array(
				'type'          => 'text',
				'label'         => __( 'Title Text', 'cartflows-pro' ),
				'name'          => 'wcf-animate-browser-tab-title',
				'display_align' => 'vertical',
				'conditions'    => array(
					'fields' => array(
						array(
							'name'     => 'wcf-animate-browser-tab',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			);

			$settings['settings']['rules'] = $this->get_checkout_rules_settings();

			return $settings;
	}


	/**
	 * Add meta fields
	 *
	 * @param array $settings checkout fields.
	 * @param array $options options.
	 * @param array $flow_id flow id.
	 */
	public function design_fields_react( $settings, $options, $flow_id ) {

		$settings['settings']['checkout-two-step-design'] = array(
			'title'      => __( 'Two-Step Design', 'cartflows-pro' ),
			'slug'       => 'two_step_design',
			'priority'   => 40,
			'conditions' => array(
				'relation' => 'and',
				'fields'   => array(
					array(
						'name'     => 'wcf-checkout-layout',
						'operator' => '===',
						'value'    => 'two-step',
					),
				),
			),
			'fields'     => array(
				'checkout-note'            => array(
					'type'         => 'toggle',
					'label'        => __( 'Enable Note Text', 'cartflows-pro' ),
					'name'         => 'wcf-checkout-box-note',
					'after'        => esc_html__( 'Enable Checkout Note', 'cartflows-pro' ),
					'is_fullwidth' => true,
				),
				'checkout-note-text'       => array(
					'type'          => 'text',
					'label'         => __( 'Note Text', 'cartflows-pro' ),
					'name'          => 'wcf-checkout-box-note-text',
					'conditions'    => array(
						'fields' => array(
							array(
								'name'     => 'wcf-checkout-box-note',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
					'display_align' => 'vertical',
				),
				'checkout-note-text-color' => array(
					'type'       => 'color-picker',
					'label'      => __( 'Text Color', 'cartflows-pro' ),
					'name'       => 'wcf-checkout-box-note-text-color',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-checkout-box-note',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),
				'checkout-note-bg-color'   => array(
					'type'       => 'color-picker',
					'label'      => __( 'Note Box Background Color', 'cartflows-pro' ),
					'name'       => 'wcf-checkout-box-note-bg-color',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-checkout-box-note',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),

				'step-one-title'           => array(
					'type'          => 'text',
					'label'         => __( 'Step One Title', 'cartflows-pro' ),
					'name'          => 'wcf-checkout-step-one-title',
					'display_align' => 'vertical',
				),
				'step-one-subtitle'        => array(
					'type'          => 'text',
					'label'         => __( 'Step One Sub Title', 'cartflows-pro' ),
					'name'          => 'wcf-checkout-step-one-sub-title',
					'display_align' => 'vertical',
				),
				'step-two-title'           => array(
					'type'          => 'text',
					'label'         => __( 'Step Two Title', 'cartflows-pro' ),
					'name'          => 'wcf-checkout-step-two-title',
					'display_align' => 'vertical',
				),
				'step-two-subtitle'        => array(
					'type'          => 'text',
					'label'         => __( 'Step Two Sub Title', 'cartflows-pro' ),
					'name'          => 'wcf-checkout-step-two-sub-title',
					'display_align' => 'vertical',
				),
				'step-section-width'       => array(
					'type'          => 'number',
					'label'         => __( 'Step Section Width', 'cartflows-pro' ),
					'name'          => 'wcf-checkout-two-step-section-width',
					'display_align' => 'vertical',
				),
				'step-border'              => array(
					'type'          => 'select',
					'label'         => __( 'Step Border', 'cartflows-pro' ),
					'name'          => 'wcf-checkout-two-step-section-border',
					'display_align' => 'vertical',
					'options'       => array(
						array(
							'value' => 'none',
							'label' => esc_html__( 'None', 'cartflows-pro' ),
						),
						array(
							'value' => 'solid',
							'label' => esc_html__( 'Solid', 'cartflows-pro' ),
						),
					),
				),

				'offer-button-title'       => array(
					'type'          => 'text',
					'label'         => __( 'Offer Button Title', 'cartflows-pro' ),
					'name'          => 'wcf-checkout-offer-button-title',
					'display_align' => 'vertical',
				),
				'offer-button-subtitle'    => array(
					'type'          => 'text',
					'label'         => __( 'Offer Button Sub Title', 'cartflows-pro' ),
					'name'          => 'wcf-checkout-offer-button-sub-title',
					'display_align' => 'vertical',
				),

			),
		);


		if ( absint( Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' ) ) === $flow_id && ! apply_filters( 'cartflows_show_store_checkout_product_tab', false ) ) {
			return $settings;
		}

		$settings['settings']['product-option-design'] = array(
			'title'    => __( 'Product Options', 'cartflows-pro' ),
			'slug'     => 'product_options',
			'priority' => 50,
			'fields'   => array(
				'product-options-title'              => array(
					'type'          => 'text',
					'label'         => __( 'Section Title', 'cartflows-pro' ),
					'name'          => 'wcf-product-opt-title',
					'placeholder'   => esc_html__( 'Your Products', 'cartflows-pro' ),
					'display_align' => 'vertical',

				),
				'product-options-position'           => array(
					'type'          => 'select',
					'label'         => __( 'Section Position', 'cartflows-pro' ),
					'name'          => 'wcf-your-products-position',
					'display_align' => 'vertical',
					'options'       => array(
						array(
							'value' => 'before-customer',
							'label' => __( 'Before Checkout Section', 'cartflows-pro' ),
						),
						array(
							'value' => 'after-customer',
							'label' => __( 'After Customer Details', 'cartflows-pro' ),
						),
						array(
							'value' => 'before-order',
							'label' => __( 'Before Order Review', 'cartflows-pro' ),
						),
					),
				),
				'product-options-skin'               => array(
					'type'          => 'select',
					'label'         => __( 'Skins', 'cartflows-pro' ),
					'name'          => 'wcf-product-options-skin',
					'display_align' => 'vertical',
					'options'       => array(
						array(
							'value' => 'classic',
							'label' => __( 'Classic', 'cartflows-pro' ),
						),
						array(
							'value' => 'cards',
							'label' => __( 'Cards', 'cartflows-pro' ),
						),
					),
				),
				'product-options-image'              => array(
					'type'         => 'toggle',
					'label'        => __( 'Show Product Images', 'cartflows-pro' ),
					'name'         => 'wcf-show-product-images',
					'tooltip'      => __( 'It will add images on checkout page.', 'cartflows-pro' ),
					'is_fullwidth' => true,
				),

				'product-options-product-text-color' => array(
					'type'  => 'color-picker',
					'label' => __( 'Product Text Color', 'cartflows-pro' ),
					'name'  => 'wcf-yp-text-color',
				),
				'product-options-product-bg-color'   => array(
					'type'  => 'color-picker',
					'label' => __( 'Product Background Color', 'cartflows-pro' ),
					'name'  => 'wcf-yp-bg-color',
				),

				'product-options-hl-text-color'      => array(
					'type'  => 'color-picker',
					'label' => __( 'Highlight Product Text Color', 'cartflows-pro' ),
					'name'  => 'wcf-yp-hl-text-color',
				),
				'product-options-hl-bg-color'        => array(
					'type'  => 'color-picker',
					'label' => __( 'Highlight Product Background Color', 'cartflows-pro' ),
					'name'  => 'wcf-yp-hl-bg-color',
				),
				'product-options-hl-box-bg-color'    => array(
					'type'  => 'color-picker',
					'label' => __( 'Highlight Box Border Color', 'cartflows-pro' ),
					'name'  => 'wcf-yp-hl-border-color',
				),
				'product-options-hl-flag-text-color' => array(
					'type'  => 'color-picker',
					'label' => __( 'Highlight Flag Text Color', 'cartflows-pro' ),
					'name'  => 'wcf-yp-hl-flag-text-color',
				),
				'product-options-hl-flag-bg-color'   => array(
					'type'  => 'color-picker',
					'label' => __( 'Highlight Flag Background Color', 'cartflows-pro' ),
					'name'  => 'wcf-yp-hl-flag-bg-color',
				),

			),
		);

		return $settings;
	}

	/**
	 * Add custom meta fields
	 *
	 * @param string $id id.
	 */
	public function get_product_option_fields( $id ) {

		$data = wcf()->options->get_checkout_meta_value( $id, 'wcf-product-options-data' );

		$checkout_products = wcf_pro()->utils->get_selected_product_options_data( $id, $data );

		$product_option_data = array();

		if ( ! empty( $checkout_products ) ) {
			foreach ( $checkout_products as $key => $value ) {

				if ( ! isset( $value['product'] ) || empty( $value['product'] ) ) {
					return;
				}

				$product = wc_get_product( $value['product'] );

				if ( ! is_object( $product ) ) {
					continue;
				}

				$product_id   = $product->get_id();
				$product_name = $product->get_name();
				$unique_id    = isset( $value['unique_id'] ) ? $value['unique_id'] : '';

				$selected_data = array(
					'product_name'           => $product_name,
					'product_id'             => $product_id,
					'key'                    => $key,
					'input_product_name'     => isset( $value['product_name'] ) ? $value['product_name'] : $product_name,
					'input_subtext'          => isset( $value['product_subtext'] ) ? $value['product_subtext'] : '',
					'input_enable_highlight' => isset( $value['enable_highlight'] ) ? $value['enable_highlight'] : '',
					'input_highlight_text'   => isset( $value['highlight_text'] ) ? $value['highlight_text'] : '',
					'add_to_cart'            => isset( $value['add_to_cart'] ) ? $value['add_to_cart'] : 'yes',
					'unique_id'              => $unique_id,
				);

				$product_option_data[ $key ] = $selected_data;
			}
		}

		return $product_option_data;

	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Checkout_Meta_Data::get_instance();
