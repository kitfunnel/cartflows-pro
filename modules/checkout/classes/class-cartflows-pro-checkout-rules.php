<?php
/**
 * Cartflows Checkout Rules
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Checkout_Rules.
 */
class Cartflows_Pro_Checkout_Rules {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var order_data
	 */
	private static $order_data = array();

	/**
	 * Member Variable
	 *
	 * @var order_data
	 */
	private static $dynamic_step_id = null;

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

		add_filter( 'cartflows_checkout_next_step_id', array( $this, 'conditional_redirection_after_checkout' ), 10, 3 );
	}

	/**
	 * Conditional redirection.
	 *
	 * @param string $next_step_id next step.
	 * @param object $order order data.
	 * @param int    $checkout_id id.
	 */
	public function conditional_redirection_after_checkout( $next_step_id, $order, $checkout_id ) {

		if ( null === self::$dynamic_step_id ) {

			$is_rules_enabled = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-rules-option' );

			if ( 'no' === $is_rules_enabled ) {
				return $next_step_id;
			}

			$conditions = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-rules' );

			if ( ! is_array( $conditions ) || empty( $conditions ) ) {
				return $next_step_id;
			}

			$this->set_order_data( $order );

			$group_result = false;

			foreach ( $conditions as $group_index => $group_data ) {

				if ( ! empty( $group_data['rules'] ) ) {

					$group_result = $this->get_group_rules_result( $group_data['rules'], $order );

					// If group result is true then return step_id.
					if ( $group_result ) {
						$next_step_id = '' === $group_data['step_id'] ? $next_step_id : $group_data['step_id'];
						break;
					}
				}
			}

			// If all group are false then refirect to step selected in default step field.
			if ( false === $group_result ) {
				$default_step = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-rules-default-step' );
				$next_step_id = empty( $default_step ) ? $next_step_id : $default_step;
			}

			self::$order_data = null;

			self::$dynamic_step_id = $next_step_id;
		}

		return self::$dynamic_step_id;
	}

	/**
	 * Set order data.
	 *
	 * @param object $order order data.
	 */
	public function set_order_data( $order ) {

		$order_items  = $order->get_items();
		$products_ids = array();

		foreach ( $order_items as $item_id => $item ) {

			$product_id     = $item->get_product_id();
			$variation_id   = $item->get_variation_id();
			$products_ids[] = ! empty( $variation_id ) ? $variation_id : $product_id;
		}
		self::$order_data['product_ids'] = $products_ids;
		self::$order_data['order_obj']   = $order;
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
					$result = $this->compare_string_values( self::$order_data['product_ids'], $value, $operator );
					break;

				case 'cart_item_category':
					$item_terms = $this->get_order_items_categories();
					$result     = $this->compare_string_values( $item_terms, $value, $operator );
					break;

				case 'cart_item_tag':
					$item_terms = $this->get_order_items_tags();
					$result     = $this->compare_string_values( $item_terms, $value, $operator );
					break;

				case 'cart_total':
					$order_total = $this->get_order_total();
					$result      = $this->compare_number_values( $order_total, $value, $operator );
					break;

				case 'cart_coupons':
					$coupon_used = $this->get_order_coupons();
					$result      = $this->compare_string_values( $coupon_used, $value, $operator );
					break;

				case 'cart_shipping_method':
					$shipping_methods = $this->get_order_shipping_method();
					$result           = $this->compare_string_values( $shipping_methods, $value, $operator );
					break;
				case 'cart_shipping_country':
					$shipping_country = $this->get_order_shipping_country();
					$result           = $this->compare_string_values( $shipping_country, $value, $operator );
					break;

				case 'cart_billing_country':
					$billing_country = $this->get_order_billing_country();
					$result          = $this->compare_string_values( $billing_country, $value, $operator );
					break;

				case 'cart_payment_method':
					$payment_method = $this->get_order_payment_method();
					$result         = $this->compare_string_values( $payment_method, $value, $operator );
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
	 * Get order item categories.
	 */
	public function get_order_items_categories() {

		$item_terms = array();

		if ( isset( self::$order_data['item_categories'] ) ) {
			$item_terms = self::$order_data['item_categories'];
		} else {

			$products_ids = self::$order_data['product_ids'];

			foreach ( $products_ids as $index => $product_id ) {

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

				$cat_terms  = wp_get_object_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
				$item_terms = array_merge( $item_terms, $cat_terms );
			}

			self::$order_data['item_categories'] = $item_terms;
		}

		return $item_terms;
	}

	/**
	 * Get order item tags.
	 */
	public function get_order_items_tags() {

		$item_terms = array();

		if ( isset( self::$order_data['item_tags'] ) ) {
			$item_terms = self::$order_data['item_tags'];
		} else {

			$products_ids = self::$order_data['product_ids'];

			foreach ( $products_ids as $index => $product_id ) {

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

				$tag_terms  = wp_get_object_terms( $product_id, 'product_tag', array( 'fields' => 'ids' ) );
				$item_terms = array_merge( $item_terms, $tag_terms );
			}

			self::$order_data['item_tags'] = $item_terms;
		}

		return $item_terms;
	}

	/**
	 * Get order shipping method.
	 */
	public function get_order_shipping_method() {

		$shipping_methods = array();

		if ( isset( self::$order_data['shipping_method'] ) ) {
			$shipping_methods = self::$order_data['shipping_method'];
		} else {

			foreach ( self::$order_data['order_obj']->get_shipping_methods() as $method ) {
				// extract method slug only, discard instance id.
				if ( ( $split = strpos( $method['method_id'], ':' ) ) ) { //phpcs:ignore
					$shipping_methods[] = substr( $method['method_id'], 0, $split );
				} else {
					$shipping_methods[] = $method['method_id'];
				}
			}

			self::$order_data['shipping_method'] = $shipping_methods;
		}

		return $shipping_methods;
	}

	/**
	 * Get order coupons.
	 */
	public function get_order_coupons() {

		$coupon_used = array();

		if ( isset( self::$order_data['coupon_used'] ) ) {
			$coupon_used = self::$order_data['coupon_used'];
		} else {
			$coupon_used                     = self::$order_data['order_obj']->get_used_coupons();
			self::$order_data['coupon_used'] = $coupon_used;
		}

		return $coupon_used;
	}

	/**
	 * Get order total.
	 */
	public function get_order_total() {

		$order_total = array();

		if ( isset( self::$order_data['order_total'] ) ) {
			$order_total = self::$order_data['order_total'];
		} else {
			$order_total                     = self::$order_data['order_obj']->get_total();
			self::$order_data['order_total'] = $order_total;
		}

		return $order_total;
	}

	/**
	 * Get order billing country.
	 */
	public function get_order_billing_country() {

		$billing_country = array();

		if ( isset( self::$order_data['billing_country'] ) ) {
			$billing_country = self::$order_data['billing_country'];
		} else {
			$billing_country                     = array( self::$order_data['order_obj']->get_billing_country() );
			self::$order_data['billing_country'] = $billing_country;
		}

		return $billing_country;
	}

	/**
	 * Get order shipping country.
	 */
	public function get_order_shipping_country() {

		$shipping_country = array();

		if ( isset( self::$order_data['shipping_country'] ) ) {

			$shipping_country = self::$order_data['shipping_country'];

		} else {
			$shipping_country                     = array( self::$order_data['order_obj']->get_shipping_country() );
			self::$order_data['shipping_country'] = $shipping_country;

		}

		return $shipping_country;
	}

	/**
	 * Get order payment method.
	 */
	public function get_order_payment_method() {

		$payment_method = array();

		if ( isset( self::$order_data['payment_method'] ) ) {
			$payment_method = self::$order_data['payment_method'];
		} else {
			$payment_method                     = array( self::$order_data['order_obj']->get_payment_method() );
			self::$order_data['payment_method'] = $payment_method;
		}

		return $payment_method;
	}

	/**
	 * Compare string values.
	 *
	 * @param array  $order_values order values.
	 * @param array  $rule_values rules values.
	 * @param object $operator rule operator.
	 */
	public function compare_string_values( $order_values, $rule_values, $operator ) {

		switch ( $operator ) {
			case 'all':
					$result = count( array_intersect( $rule_values, $order_values ) ) === count( $rule_values );
				break;
			case 'any':
					$result = count( array_intersect( $rule_values, $order_values ) ) >= 1;
				break;
			case 'none':
					$result = ( count( array_intersect( $rule_values, $order_values ) ) === 0 );
				break;
			case 'exist':
				$result = ( count( $order_values ) >= 1 );
				break;
			case 'not_exist':
				$result = ( count( $order_values ) === 0 );
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
	 * @param string $order_value order values.
	 * @param array  $rule_value rules values.
	 * @param string $operator rule operator.
	 */
	public function compare_number_values( $order_value, $rule_value, $operator ) {
		// Convert the encoded operator at the time of sanitizing back to HTML entity for comparison.
		$operator = htmlspecialchars_decode( $operator );

		switch ( $operator ) {
			case '==':
				$result = $order_value === $rule_value;
				break;
			case '!=':
				$result = $order_value !== $rule_value;
				break;
			case '>':
				$result = $order_value > $rule_value;
				break;
			case '<':
				$result = $order_value < $rule_value;
				break;
			case '<=':
				$result = $order_value <= $rule_value;
				break;
			case '>=':
				$result = $order_value >= $rule_value;
				break;
			default:
				$result = false;
				break;
		}

		return $result;
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Checkout_Rules' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Checkout_Rules::get_instance();
