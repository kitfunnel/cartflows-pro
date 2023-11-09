<?php
/**
 * Mollie Gateway helper functions.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Class Cartflows_Pro_mollie_Gateway_Helper .
 */
class Cartflows_Pro_Mollie_Gateway_Helper {

	/**
	 * Plugin id variable
	 *
	 * @var plugin_id
	 */
	public $plugin_id = 'mollie-payments-for-woocommerce';

	/**
	 * Create payment api variable.
	 *
	 * @var create_payment_api
	 */
	public $create_payment_api = 'https://api.mollie.com/v2/payments';

	/**
	 * Create customer api variable
	 *
	 * @var create_customer_api
	 */
	public $create_customer_api = 'https://api.mollie.com/v2/customers';

	/**
	 * Get payment api variable
	 *
	 * @var get_payment_api
	 */
	public $get_payment_api = 'https://api.mollie.com/v2/payments/';

	/**
	 * Get user mollie customer id from order.
	 *
	 * @param object $order order object.
	 * @return null|string
	 */
	public function get_user_mollie_customer_id( $order ) {

		$mollie_customer_id = $order->get_meta( '_wcf_mollie_customer_id', true );

		if ( ! $mollie_customer_id ) {
			$mollie_customer_id = $order->get_meta( '_mollie_customer_id', true );
		}
		return $mollie_customer_id;
	}

	/**
	 * Get api key.
	 *
	 * @return string
	 */
	public function get_mollie_api_key() {

		$is_live_mode = 'yes' === get_option( $this->plugin_id . '_test_mode_enabled' ) ? false : true;

		if ( $is_live_mode ) {
			$api_key = get_option( $this->plugin_id . '_live_api_key' );
		} else {
			$api_key = get_option( $this->plugin_id . '_test_api_key' );
		}

		return $api_key;
	}

	/**
	 * Get return url.
	 *
	 * @param int    $step_id step id.
	 * @param int    $order_id order id.
	 * @param string $order_key order key.
	 * @param string $session_key session key.
	 *
	 * @return string
	 */
	public function get_return_url( $step_id, $order_id, $order_key, $session_key ) {

		$url = get_permalink( $step_id );

		$args = array(
			'wcf-order'         => $order_id,
			'wcf-key'           => $order_key,
			'wcf-sk'            => $session_key,
			'wcf-mollie-return' => true,
		);

		return add_query_arg( $args, $url );
	}

	/**
	 * Redirect location after successfully completing process_payment
	 *
	 * @param object $payment_object payment object.
	 *
	 * @return string
	 */
	public function get_process_payment_redirect( $payment_object ) {
		/*
		 * Redirect to payment URL
		 */
		return $payment_object->_links->checkout->href;
	}

	/**
	 * Get api response body
	 *
	 * @param string $url api url.
	 * @param array  $args api arguments.
	 */
	public function get_mollie_api_response_body( $url, $args ) {

		$result = wp_remote_get( $url, $args );

		if ( is_wp_error( $result ) ) {
			return false;
		}

		$retrived_body = wp_remote_retrieve_body( $result );

		return json_decode( $retrived_body );

	}

	/**
	 * Get WooCommerce payment geteways.
	 *
	 * @return array
	 */
	public function get_wc_gateway() {

		global $woocommerce;

		$gateways = $woocommerce->payment_gateways->payment_gateways();

		return $gateways[ $this->key ];
	}

	/**
	 * Save mollie customer id for order.
	 *
	 * @param object $order order data.
	 * @param string $customer_id customer id.
	 */
	public function set_mollie_customer_id( $order, $customer_id ) {

		if ( ! empty( $customer_id ) ) {

			try {

				$order->update_meta_data( '_mollie_customer_id', $customer_id );
				$order->update_meta_data( '_wcf_mollie_customer_id', $customer_id );
				$order->save();

				wcf()->logger->log( __FUNCTION__ . ': Stored Mollie customer ID ' . $customer_id . ' with order ' . $order->get_id() );
			} catch ( Exception $e ) {

				wcf()->logger->log( __FUNCTION__ . ": Couldn't load (and save) WooCommerce customer based on order ID " . $order->get_id() );
			}
		}
	}

	/**
	 * Maybe get mollie customer id from prev order for non logged-in user.
	 *
	 * @param string $billing_email user email.
	 *
	 * @return null|string
	 */
	public function maybe_get_mollie_customer_id_from_order( $billing_email ) {

		$mollie_customer_id = null;

		$prev_orders_by_meta = new WP_Query(
			array(
				'post_type'   => 'shop_order',
				'post_status' => 'any',
				'meta_query'  => array( //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'AND',
					array(
						'key'     => '_billing_email',
						'value'   => $billing_email,
						'compare' => '=',
					),
					array(
						'key'     => '_mollie_customer_id',
						'compare' => 'EXISTS',
					),
				),
				'fields'      => 'ids',
				'order'       => 'ASC',
			)
		);

		if ( is_array( $prev_orders_by_meta->posts ) && count( $prev_orders_by_meta->posts ) > 0 ) {

			$prev_order_id = $prev_orders_by_meta->posts[0];

			$prev_order = wc_get_order( $prev_order_id );

			$mollie_customer_id = $prev_order->get_meta( '_mollie_customer_id', true );
		}

		return $mollie_customer_id;
	}

	/**
	 * Format currency value according to mollie.
	 *
	 * @param string $value Order value.
	 * @param string $currency currency.
	 */
	public function format_currency_value( $value, $currency ) {
		$currencies_with_no_decimals = array( 'JPY', 'ISK' );

		if ( in_array( $currency, $currencies_with_no_decimals, true ) ) {
			return number_format( $value, 0, '.', '' );
		}

		return number_format( $value, 2, '.', '' );
	}

	/**
	 * Set response code.
	 *
	 * @param int $status_code Status code.
	 */
	public function set_http_response_code( $status_code ) {
		if ( PHP_SAPI !== 'cli' && ! headers_sent() ) {
			if ( function_exists( 'http_response_code' ) ) {
				http_response_code( $status_code );
			} else {
				header( ' ', true, $status_code );
			}
		}
	}

	/**
	 * Save required meta keys to refund seperate order.
	 *
	 * @param object $parent_order Parent order Object.
	 * @param object $child_order Child order Object.
	 * @param string $transaction_id id.
	 */
	public function store_mollie_meta_keys_for_refund( $parent_order, $child_order, $transaction_id ) {

		if ( ! empty( $transaction_id ) ) {

			$payment_mode = $parent_order->get_meta( '_mollie_payment_mode' );
			$child_order->update_meta_data( '_mollie_order_id', $transaction_id );
			$child_order->update_meta_data( '_mollie_payment_id', $transaction_id );
			$child_order->update_meta_data( '_mollie_payment_mode', $payment_mode );
			$child_order->save();
		}
	}

	/**
	 * Process offer refund
	 *
	 * @param object $order Order Object.
	 * @param array  $offer_data offer data.
	 *
	 * @return string/bool.
	 */
	public function process_offer_refund( $order, $offer_data ) {

		$transaction_id = $offer_data['transaction_id'];
		$refund_amount  = number_format( $offer_data['refund_amount'], 2 );
		$refund_reason  = $offer_data['refund_reason'];
		$order_currency = $order->get_currency( $order );

		$response_id = false;

		wcf()->logger->log( __FUNCTION__ . ': Transaction ID to refund: ' . $transaction_id . ' & Order id: ' . $order->get_id() );

		if ( ! is_null( $refund_amount ) && isset( $transaction_id ) ) {

			$api_key = $this->get_mollie_api_key();

			$get_payment = $this->get_payment_api . $transaction_id;

			$arguments = array(
				'method'  => 'GET',
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $api_key,
				),
			);

			$payment = $this->get_mollie_api_response_body( $get_payment, $arguments );

			if ( $payment && null !== $payment->amountRemaining && $payment->amountRemaining->currency === $order_currency && $payment->amountRemaining->value >= $refund_amount ) { //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

				// https://docs.mollie.com/reference/v2/refunds-api/create-refund.
				$refund_api_url = 'https://api.mollie.com/v2/payments/' . $transaction_id . '/refunds';

				$refund_args = array(
					'amount' => array(
						'currency' => $order_currency,
						'value'    => $refund_amount,
					),
				);

				$arguments = array(
					'method'  => 'POST',
					'headers' => array(
						'Content-Type'  => 'application/json',
						'Authorization' => 'Bearer ' . $api_key,
					),
					'body'    => wp_json_encode( $refund_args ),
				);

				$refund_response = $this->get_mollie_api_response_body( $refund_api_url, $arguments );

				if ( $refund_response ) {
					$response_id = $refund_response->id;
				}
			}
		}
		wcf()->logger->log( __FUNCTION__ . 'Refund ID: ' . $response_id );

		return $response_id;
	}

	/**
	 * Setup the Payment data for mollie Automatic Subscription.
	 *
	 * @param WC_Subscription $subscription An instance of a subscription object.
	 * @param object          $order Object of order.
	 * @param array           $offer_product array of offer product.
	 */
	public function add_subscription_payment_meta_for_mollie( $subscription, $order, $offer_product ) {

		if ( in_array( $order->get_payment_method(), array( 'mollie_wc_gateway_creditcard', 'mollie_wc_gateway_ideal' ), true ) ) {

			$subscription->update_meta_data( '_mollie_payment_id', $order->get_meta( '_mollie_payment_id', true ) );
			$subscription->update_meta_data( '_mollie_payment_mode', $order->get_meta( '_mollie_payment_mode', true ) );
			$subscription->update_meta_data( '_mollie_customer_id', $order->get_meta( '_mollie_customer_id', true ) );
			$subscription->save();
		}
	}

	/**
	 * Create mollie customer id for non logged-in user.
	 *
	 * @param array  $data payment args.
	 * @param object $order order data.
	 * @return array
	 */
	public function maybe_create_mollie_customer_id( $data, $order ) {

		if ( isset( $data['payment']['customerId'] ) && null !== $data['payment']['customerId'] ) {
			return $data;
		}

		$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
		$flow_id     = wcf()->utils->get_flow_id_from_post_data();

		if ( $checkout_id && $flow_id ) {

			// Try to create a new Mollie Customer.
			try {

				$billing_first_name = $order->get_billing_first_name();
				$billing_last_name  = $order->get_billing_last_name();
				$billing_email      = $order->get_billing_email();

				$customer_id = $this->maybe_get_mollie_customer_id_from_order( $billing_email );

				if ( null === $customer_id ) {

					// Get the best name for use as Mollie Customer name.
					$user_full_name = $billing_first_name . ' ' . $billing_last_name;

					$api_key = $this->get_mollie_api_key();

					$customer_data = array(
						'name'     => trim( $user_full_name ),
						'email'    => trim( $billing_email ),
						'metadata' => array( 'order_id' => $order->get_id() ),
					);

					$arguments = array(
						'method'  => 'POST',
						'headers' => array(
							'Content-Type'  => 'application/json',
							'Authorization' => 'Bearer ' . $api_key,
						),
						'body'    => wp_json_encode( $customer_data ),
					);

					$response = $this->get_mollie_api_response_body( $this->create_customer_api, $arguments );

					if ( $response && isset( $response->id ) ) {
						$customer_id = $response->id;
					}
				}

				$this->set_mollie_customer_id( $order, $customer_id );

				wcf()->logger->log( __FUNCTION__ . ": Created a Mollie Customer ($customer_id) for order with ID " . $order->get_id() );

				$data['payment']['customerId'] = $customer_id;

			} catch ( Exception $e ) {
				wcf()->logger->log( __FUNCTION__ . ': Could not create Mollie Customer for order with ID ' . $order->get_id() . $e->getMessage() . ' (' . get_class( $e ) . ')' );
			}

			wcf()->logger->log( 'Force save source enabled' );
		}

		return $data;
	}
}
