<?php
/**
 * Cartflows Order Bumps Rules.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Order_Bump_Rules.
 */
class Cartflows_Pro_Order_Bump_Rules {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var ob_product_id
	 */
	private static $ob_product_id;
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
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * Conditional redirection.
	 *
	 * @param array $ob_data order bump data.
	 */
	public function is_order_bump_visble( $ob_data ) {

		if ( ! isset( $ob_data['is_rule'] ) || 'no' === $ob_data['is_rule'] ) {
			return true;
		}

		if ( ! is_array( $ob_data ) || empty( $ob_data ) ) {
			return true;
		}

		$group_result        = false;
		$rule_groups         = $ob_data['rules'];
		self::$ob_product_id = intval( $ob_data['product'] );

		foreach ( $rule_groups as $group_index => $group_data ) {

			if ( ! empty( $group_data['rules'] ) ) {

				$group_result = $this->get_group_rules_result( $group_data['rules'] );

				// If group result is true then return true.
				if ( $group_result ) {
					break;
				}
			}
		}

		return $group_result;
	}

	/**
	 * Get Group result.
	 *
	 * @param array $rules rules.
	 */
	public function get_group_rules_result( $rules ) {

		$result = true;

		foreach ( $rules as $rule_index => $rule_data ) {

			$operator = $rule_data['operator'];
			$value    = $rule_data['value'];

			switch ( $rule_data['condition'] ) {

				case 'cart_item':
					$items  = $this->get_cart_items();
					$result = $this->compare_string_values( $items, $value, $operator );
					break;

				case 'cart_item_category':
					$item_terms = $this->get_cart_items_categories();
					$result     = $this->compare_string_values( $item_terms, $value, $operator );
					break;

				case 'cart_item_tag':
					$item_terms = $this->get_cart_items_tags();
					$result     = $this->compare_string_values( $item_terms, $value, $operator );
					break;

				case 'cart_total':
					$order_total = $this->get_cart_total();
					$result      = $this->compare_number_values( $order_total, $value, $operator );
					break;

				case 'cart_coupons':
					$coupon_used = $this->get_cart_coupons();
					$result      = $this->compare_string_values( $coupon_used, $value, $operator );
					break;

				case 'cart_shipping_method':
					$shipping_methods = $this->get_cart_shipping_method();
					$result           = $this->compare_string_values( $shipping_methods, $value, $operator );
					break;
				case 'cart_shipping_country':
					$shipping_country = $this->get_cart_shipping_country();
					$result           = $this->compare_string_values( $shipping_country, $value, $operator );
					break;

				case 'cart_billing_country':
					$billing_country = $this->get_cart_billing_country();
					$result          = $this->compare_string_values( $billing_country, $value, $operator );
					break;

				default:
					$result = false;
			}

			// If one of rule is false break the loop.
			if ( false === $result ) {
				break;
			}
		}

		return $result;
	}


	/**
	 * Get cart item categories.
	 */
	public function get_cart_items() {

		$item = array();

		$cart_contents = (array) WC()->cart->cart_contents;

		if ( $cart_contents && is_array( $cart_contents ) && count( $cart_contents ) > 0 ) {
			foreach ( $cart_contents as $cart_item ) {
				$product_id = 0 !== $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];

				if ( isset( $cart_item['cartflows_bump'] ) && self::$ob_product_id === $cart_item['product_id'] ) { // Do not consider order bump product itself.
					continue;
				}
				array_push( $item, $product_id );
			}
		}

		return $item;
	}

	/**
	 * Get cart item categories.
	 */
	public function get_cart_items_categories() {

		$item_terms = array();

		$cart_contents = WC()->cart->get_cart_contents();

		if ( $cart_contents && is_array( $cart_contents ) && count( $cart_contents ) > 0 ) {
			foreach ( $cart_contents as $cart_item ) {

				if ( isset( $cart_item['cartflows_bump'] ) && self::$ob_product_id === $cart_item['product_id'] ) { // Do not consider order bump product itself.
					continue;
				}
				$product_id = $cart_item['product_id'];

				// Get Product object.
				$product = wc_get_product( $product_id );
				/**
				 * Check the type of the product.
				 *
				 * This condition is added to get the product categories.
				 * As varation of a product is getting purchased and it does not contains the categories.
				*/
				if ( $product->is_type( 'variation' ) ) {

					$product_id = $product->get_parent_id();
				}

				$terms = wp_get_object_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

				$item_terms = array_merge( $item_terms, $terms );
			}
		}

		return $item_terms;
	}

	/**
	 * Get cart item tags.
	 */
	public function get_cart_items_tags() {

		$item_terms = array();

		$cart_contents = WC()->cart->get_cart_contents();
		if ( $cart_contents && is_array( $cart_contents ) && count( $cart_contents ) > 0 ) {
			foreach ( $cart_contents as $cart_item ) {
				if ( isset( $cart_item['cartflows_bump'] ) && self::$ob_product_id === $cart_item['product_id'] ) { // Do not consider order bump product itself.
					continue;
				}
				$product_id = $cart_item['product_id'];

				// Get Product object.
				$product = wc_get_product( $product_id );
				/**
				 * Check the type of the product.
				 *
				 * This condition is added to get the product categories.
				 * As varation of a product is getting purchased and it does not contains the tags.
				*/
				if ( $product->is_type( 'variation' ) ) {

					$product_id = $product->get_parent_id();
				}

				$terms = wp_get_object_terms( $product_id, 'product_tag', array( 'fields' => 'ids' ) );

				$item_terms = array_merge( $item_terms, $terms );
			}
		}

		return $item_terms;
	}

	/**
	 * Get cart shipping method.
	 */
	public function get_cart_shipping_method() {

		$shipping_methods = array();

		$selected_shipping = WC()->session->get( 'chosen_shipping_methods' );       if ( $selected_shipping ) {
			foreach ( $selected_shipping as $method ) {
				// extract method slug only, discard instance id.
				$split = strpos( $method, ':' );
				if ( $split ) {
					$shipping_methods[] = substr( $method, 0, $split );
				} else {
					$shipping_methods[] = $method;
				}
			}
		}

		return $shipping_methods;
	}

	/**
	 * Get cart coupons.
	 */
	public function get_cart_coupons() {

		$used_coupons = array();

		$cart_coupons = WC()->cart->get_coupons();

		if ( $cart_coupons && is_array( $cart_coupons ) && count( $cart_coupons ) > 0 ) {
			$used_coupons = array_keys( $cart_coupons );
		}

		return $used_coupons;
	}

	/**
	 * Get cart total.
	 */
	public function get_cart_total() {
		return WC()->cart->get_total( 'edit' );
	}

	/**
	 * Get cart billing country.
	 */
	public function get_cart_billing_country() {
		return array( WC()->customer->get_billing_country( 'edit' ) );
	}

	/**
	 * Get cart shipping country.
	 */
	public function get_cart_shipping_country() {
		return array( WC()->customer->get_shipping_country( 'edit' ) );
	}


	/**
	 * Compare string values.
	 *
	 * @param array  $cart_values cart values.
	 * @param array  $rule_values rules values.
	 * @param object $operator rule operator.
	 */
	public function compare_string_values( $cart_values, $rule_values, $operator ) {

		switch ( $operator ) {
			case 'all':
					$result = count( array_intersect( $rule_values, $cart_values ) ) === count( $rule_values );
				break;
			case 'any':
					$result = count( array_intersect( $rule_values, $cart_values ) ) >= 1;
				break;
			case 'none':
					$result = ( count( array_intersect( $rule_values, $cart_values ) ) === 0 );
				break;
			case 'exist':
				$result = ( count( $cart_values ) >= 1 );
				break;
			case 'not_exist':
				$result = ( count( $cart_values ) === 0 );
				break;
			default:
				$result = false;
				break;
		}

		return $result;
	}

	/**
	 * Compare string values.
	 *
	 * @param string $cart_value cart values.
	 * @param array  $rule_value rules values.
	 * @param string $operator rule operator.
	 */
	public function compare_number_values( $cart_value, $rule_value, $operator ) {
		// Convert the encoded operator at the time of sanitizing back to HTML entity for comparison.
		$operator = htmlspecialchars_decode( $operator );

		switch ( $operator ) {
			case '==':
				$result = $cart_value === $rule_value;
				break;
			case '!=':
				$result = $cart_value !== $rule_value;
				break;
			case '>':
				$result = $cart_value > $rule_value;
				break;
			case '<':
				$result = $cart_value < $rule_value;
				break;
			case '<=':
				$result = $cart_value <= $rule_value;
				break;
			case '>=':
				$result = $cart_value >= $rule_value;
				break;
			default:
				$result = false;
				break;
		}

		return $result;
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Order_Bump_Rules' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Order_Bump_Rules::get_instance();
