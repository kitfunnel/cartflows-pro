<?php
/**
 * Mollie Gateway. Credit Card method.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Gateway_Mollie_Credit_Card.
 */
class Cartflows_Pro_Gateway_Mollie_Credit_Card extends Cartflows_Pro_Mollie_Gateway_Helper {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Key name variable
	 *
	 * @var key
	 */
	public $key = 'mollie_wc_gateway_creditcard';

	/**
	 * Refund Supported
	 *
	 * @var is_api_refund
	 */
	public $is_api_refund = true;

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

		add_action( $this->plugin_id . '_customer_return_payment_success', array( $this, 'maybe_setup_upsell' ), 5 );

		/* Create mollie customer id for non logged-in user - Credit card */
		add_filter( 'woocommerce_mollie_wc_gateway_creditcard_args', array( $this, 'maybe_create_mollie_customer_id' ), 10, 2 );

		add_action( 'wp_ajax_wcf_mollie_creditcard_process', array( $this, 'process_credit_card' ) );
		add_action( 'wp_ajax_nopriv_wcf_mollie_creditcard_process', array( $this, 'process_credit_card' ) );

		/**
		 * Mollie CC webhook while creating payment
		 */
		add_action( 'woocommerce_api_cartflows_mollie_cc_webhook', array( $this, 'maybe_handle_mollie_cc_webhook' ) );

		add_action( 'cartflows_offer_child_order_created_' . $this->key, array( $this, 'store_mollie_meta_keys_for_refund' ), 10, 3 );

		add_action( 'cartflows_offer_subscription_created', array( $this, 'add_subscription_payment_meta_for_mollie' ), 10, 3 );
	}

	/**
	 * May be setup upsell.
	 *
	 * @param object $order Order object.
	 *
	 * @return void
	 */
	public function maybe_setup_upsell( $order ) {

		Cartflows_Pro_Frontend::get_instance()->start_the_upsell_flow( $order );
	}

	/**
	 * Get webhook url.
	 *
	 * @param int    $step_id step id.
	 * @param int    $order_id order id.
	 * @param string $order_key order key.
	 *
	 * @return string
	 */
	public function get_webhook_url( $step_id, $order_id, $order_key ) {

		$url = WC()->api_request_url( 'cartflows_mollie_cc_webhook' );

		$args = array(
			'step_id'   => $step_id,
			'order_id'  => $order_id,
			'order_key' => $order_key,
		);

		return add_query_arg( $args, $url );
	}

	/**
	 * After payment process.
	 *
	 * @param object $order order data.
	 * @param array  $product product data.
	 * @return array
	 */
	public function process_offer_payment( $order, $product ) {

		$is_successful = false;

		$tr_id = $order->get_meta( '_' . $product['step_id'] . '_tr_id', true );

		$response_data = array(
			'id' => $tr_id,
		);

		$this->store_offer_transaction( $order, $response_data, $product );

		if ( '' !== $tr_id ) {

			$is_successful = true;
		}

		return $is_successful;
	}

	/**
	 * Store Offer Trxn Charge.
	 *
	 * @param WC_Order $order            The order that is being paid for.
	 * @param Object   $response           The response that is send from the payment gateway.
	 * @param array    $product             The product data.
	 */
	public function store_offer_transaction( $order, $response, $product ) {

		wcf()->logger->log( 'Mollie Credit Card : Offer Transaction :: Transaction ID = ' . $response['id'] . ' Captured' );

		$order->update_meta_data( 'cartflows_offer_txn_resp_' . $product['step_id'], $response['id'] );
		$order->save();
	}

	/**
	 * Process credit card upsell payment.
	 *
	 * @return bool
	 */
	public function process_credit_card() {

		$nonce = isset( $_POST['_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'wcf_mollie_creditcard_process' ) ) {
			return;
		}

		$step_id   = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
		$flow_id   = isset( $_POST['flow_id'] ) ? intval( $_POST['flow_id'] ) : 0;
		$order_id  = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : 0;
		$order_key = isset( $_POST['order_key'] ) ? sanitize_text_field( wp_unslash( $_POST['order_key'] ) ) : '';

		$session_key  = isset( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) ) : ''; //phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE
		$order        = wc_get_order( $order_id );
		$variation_id = '';
		$input_qty    = '';

		if ( isset( $_POST['variation_id'] ) && ! empty( $_POST['variation_id'] ) ) {
			$variation_id = intval( $_POST['variation_id'] );
		}
		if ( isset( $_POST['input_qty'] ) && ! empty( $_POST['input_qty'] ) ) {
			$input_qty = intval( $_POST['input_qty'] );
		}
		// May need to update the data if variation and quantity changes using offer shortcodes ).
		$product_data = array(
			'variation_id' => $variation_id,
			'input_qty'    => $input_qty,
		);

		$order->update_meta_data( 'wcf_offer_product_data_' . $step_id, $product_data );
		$order->save();

		$offer_product = wcf_pro()->utils->get_offer_data( $step_id, $variation_id, $input_qty, $order_id );

		if ( isset( $offer_product['price'] ) && ( floatval( 0 ) === floatval( $offer_product['price'] )
				|| '' === trim( $offer_product['price'] ) ) ) {
			wp_send_json(
				array(
					'result'  => 'fail',
					'message' => __( '0 value product', 'cartflows-pro' ),
				)
			);
		} else {

			$api_key = $this->get_mollie_api_key();

			$customer_id = $this->get_user_mollie_customer_id( $order );

			if ( $customer_id ) {

				$data = array(
					'amount'      => array(
						'currency' => $order->get_currency(),
						'value'    => $this->format_currency_value( $offer_product['price'], $order->get_currency() ),
					),
					'description' => "One-click payment {$order_id}_{$step_id}",
					'redirectUrl' => $this->get_return_url( $step_id, $order_id, $order_key, $session_key ),
					'webhookUrl'  => $this->get_webhook_url( $step_id, $order_id, $order_key ),
					'method'      => 'creditcard',
					'metadata'    => array(
						'order_id' => $order_id,
					),
					'customerId'  => $customer_id,
				);

				$arguments = array(
					'method'  => 'POST',
					'headers' => array(
						'Content-Type'  => 'application/json',
						'Authorization' => 'Bearer ' . $api_key,
					),
					'body'    => wp_json_encode( $data ),
				);

				$payment_object = $this->get_mollie_api_response_body( $this->create_payment_api, $arguments );

				if ( $payment_object ) {

					wp_send_json(
						array(
							'result'   => 'success',
							'redirect' => $this->get_process_payment_redirect( $payment_object ),
						)
					);
				}
			}

			wp_send_json(
				array(
					'result'  => 'fail',
					'message' => __( 'Customer id not found. Payment failed', 'cartflows-pro' ),
				)
			);
		}
	}

	/**
	 * Handle mollie payment webhook.
	 *
	 * @return void
	 */
	public function maybe_handle_mollie_cc_webhook() {
		//phpcs:disable WordPress.Security.NonceVerification
		// Webhook test by Mollie.
		if ( isset( $_GET['testByMollie'] ) ) {
			wcf()->logger->log( __METHOD__ . ': Webhook tested by Mollie.' );
			return;
		}

		if ( empty( $_GET['order_id'] ) || empty( $_GET['order_key'] ) || empty( $_GET['step_id'] ) ) {
			$this->set_http_response_code( 400 );
			wcf()->logger->log( __METHOD__ . ':  No order ID or order key provided.' );
			return;
		}

		$step_id   = sanitize_text_field( wp_unslash( $_GET['step_id'] ) );
		$order_id  = sanitize_text_field( wp_unslash( $_GET['order_id'] ) );
		$order_key = sanitize_text_field( wp_unslash( $_GET['order_key'] ) );

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			$this->set_http_response_code( 404 );
			wcf()->logger->log( __METHOD__ . ":  Could not find order $order_id." );
			return;
		}

		if ( ! $order->key_is_valid( $order_key ) ) {
			$this->set_http_response_code( 401 );
			wcf()->logger->log( __METHOD__ . ":  Invalid key key for order $order_id." );
			return;
		}

		// No Mollie payment id provided.
		if ( empty( $_POST['id'] ) ) {
			$this->set_http_response_code( 400 );
			wcf()->logger->log( __METHOD__ . ': No payment object ID provided.' );
			return;
		}

		$payment_object_id = sanitize_text_field( wp_unslash( $_POST['id'] ) );

		$get_payment = $this->get_payment_api . $payment_object_id;

		$api_key = $this->get_mollie_api_key();

		$arguments = array(
			'method'  => 'GET',
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_key,
			),
		);

		// Load the payment from Mollie, do not use cache.
		try {
			$payment = $this->get_mollie_api_response_body( $get_payment, $arguments );
		} catch ( Exception $e ) {
			$this->set_http_response_code( 400 );
			return;
		}

		// Payment not found.
		if ( ! $payment ) {
			$this->set_http_response_code( 404 );
			wcf()->logger->log( __METHOD__ . ": payment object $payment_object_id not found." );
			return;
		}

		if ( $order_id != $payment->metadata->order_id ) {
			$this->set_http_response_code( 400 );
			wcf()->logger->log( __METHOD__ . ": Order ID does not match order_id in payment metadata. Payment ID {$payment->id}, order ID $order_id" );
			return;
		}

		$order->update_meta_data( '_' . $step_id . '_tr_id', $payment_object_id );
		$order->save();

		$order->add_order_note( __( 'Mollie credit card payment processed.', 'cartflows-pro' ) );
		//phpcs:enable WordPress.Security.NonceVerification
	}

	/**
	 * Is gateway support offer refund?
	 *
	 * @return bool
	 */
	public function is_api_refund() {

		return $this->is_api_refund;
	}


}

/**
 *  Prepare if class 'Cartflows_Pro_Gateway_Mollie_Credit_Card' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateway_Mollie_Credit_Card::get_instance();
