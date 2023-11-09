<?php
/**
 * Checkout Plugins - Payment Gateway ( Smart Payment ).
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Gateway_Cppw_Paypal.
 */
class Cartflows_Pro_Gateway_Cppw_Paypal {

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
	public $key = 'cppw_paypal';

	/**
	 * Refund supported variable
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

		add_filter( 'cppw_should_save_billing_agreement', array( $this, 'tokenize_if_required' ) );
		add_filter( 'cppw_should_enable_billing_token_on_checkout', array( $this, 'tokenize_if_required' ) );

		add_action( 'wp_ajax_wcf_cppw_create_paypal_order', array( $this, 'create_payment_order' ) );
		add_action( 'wp_ajax_nopriv_wcf_cppw_create_paypal_order', array( $this, 'create_payment_order' ) );

		add_action( 'cartflows_offer_subscription_created', array( $this, 'add_subscription_payment_meta' ), 10, 3 );

		add_action( 'cartflows_offer_child_order_created_' . $this->key, array( $this, 'add_required_meta_to_child_order' ), 10, 3 );
	}

	/**
	 * Handle payment type of checkout plugin paypal.
	 *
	 * @param string $paypal_order_id paypal order id.
	 * @param array  $payment_source_body payment source body.
	 *
	 * @return object
	 */
	public function handle_payment_type_request( $paypal_order_id, $payment_source_body ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$gateway     = $this->get_wc_gateway();
		$paypal_type = $gateway->paypal_type;

		wcf()->logger->log( 'PayPal Type: ' . $paypal_type );

		if ( 'capture' === $paypal_type ) {
			return $gateway->capture_order_request( $paypal_order_id, $payment_source_body );
		}

		if ( 'authorize' === $paypal_type ) {
			return $gateway->authorize_order_request( $paypal_order_id, $payment_source_body );
		}

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );
	}

	/**
	 * After payment process.
	 *
	 * @param object $order order data.
	 * @param array  $product product data.
	 * @return bool
	 */
	public function process_offer_payment( $order, $product ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$is_successful = false;

		$nonce = isset( $_POST['_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, $product['action'] ) ) {
			wcf()->logger->log( 'Nonce verification failed. ' . __CLASS__ . '::' . __FUNCTION__ );
			return $is_successful;
		}

		// Confirm if same transaction id then only create Woo Offer order.
		$offer_order_tr_id = $order->get_meta( 'cartflows_offer_txn_resp_' . $product['step_id'] );
		$received_tr_id    = isset( $_POST['transaction_id'] ) ? sanitize_text_field( wp_unslash( $_POST['transaction_id'] ) ) : '';

		if ( $offer_order_tr_id === $received_tr_id ) {
			$is_successful = true;
		}

		wcf()->logger->log( 'Payment Status: ' . $is_successful );

		wcf()->logger->log( 'Ended: ' . __CLASS__ . '::' . __FUNCTION__ );

		return $is_successful;
	}

	/**
	 * Verify 3DS and create intent accordingly.
	 *
	 * @return void
	 */
	public function create_payment_order() {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$nonce = isset( $_POST['_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'wcf_cppw_create_paypal_order' ) ) {
			wp_send_json(
				array(
					'result'  => 'fail',
					'message' => 'Nonce verification failed.',
				)
			);

		}

		$variation_id = '';
		$input_qty    = '';

		if ( isset( $_POST['variation_id'] ) && ! empty( $_POST['variation_id'] ) ) {
			$variation_id = intval( $_POST['variation_id'] );
		}

		if ( isset( $_POST['input_qty'] ) && ! empty( $_POST['input_qty'] ) ) {
			$input_qty = intval( $_POST['input_qty'] );
		}

		$step_id       = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
		$order_id      = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : 0;
		$order         = wc_get_order( $order_id );
		$offer_product = wcf_pro()->utils->get_offer_data( $step_id, $variation_id, $input_qty, $order_id );

		if ( isset( $offer_product['price'] ) && ( floatval( 0 ) === floatval( $offer_product['price'] )
				|| '' === trim( $offer_product['price'] ) ) ) {

			wcf()->logger->log( 'Zero value product selected' );
			wp_send_json(
				array(
					'result'  => 'fail',
					'message' => '0 value product',
				)
			);
		} else {

			wcf()->logger->log( 'Creating paypal order.' );

			$gateway = $this->get_wc_gateway();
			// Create paypal order from paypal end.
			$paypal_order = $gateway->create_paypal_order_of_wc( $order, $offer_product['price'] );

			if ( empty( $paypal_order['id'] ) ) {
				wcf()->logger->log( 'No paypal order created.' );
			}

			wcf()->logger->log( 'Paypal Order Response: ' . wcf_print_r( $paypal_order, true ) );

			// Capture newly created paypal order based on billing agreement id.
			$billing_agreement_id = $order->get_meta( CPPW_SUB_AGREEMENT_ID );

			wcf()->logger->log( 'Billing Agreement ID: ' . $billing_agreement_id );

			$payment_source_body = $gateway->generate_payment_source_body( $billing_agreement_id );

			wcf()->logger->log( 'Payment Source Body: ' . wcf_print_r( $payment_source_body, true ) );

			// Handle payment request created above.
			$capture_payment = $this->handle_payment_type_request( $paypal_order['id'], $payment_source_body );

			// Retrive transaction id of offer prder and save it.
			$transaction_id = $gateway->get_transaction_id( $capture_payment );

			wcf()->logger->log( 'Captured Paypal Order: ' . wcf_print_r( $capture_payment, true ) );

			if ( isset( $capture_payment['status'] ) && 'COMPLETED' === $capture_payment['status'] ) {

				if ( 'capture' === $gateway->paypal_type ) {
					$price_breakdown = ! empty( $capture_payment['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown'] ) ? $capture_payment['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown'] : false;
					// Save offer order paypal fee & other data in main order meta. Will update it later in clid order.
					$this->add_fee_net_amount( $order, $price_breakdown, $step_id );
				}

				$order->update_meta_data( 'cartflows_offer_txn_resp_' . $step_id, $transaction_id );
				$order->update_meta_data( 'cartflows_offer' . CPPW_SUB_AGREEMENT_ID . '_' . $step_id, $billing_agreement_id );
				$order->save();

				wcf()->logger->log( 'Transaction ID: ' . $transaction_id );

				wp_send_json(
					array(
						'status'         => 'succeeded',
						'transaction_id' => $transaction_id,
					)
				);

			} else {

				wcf()->logger->log( 'Creating a PayPal order failed due to the order not captured.' );

				wp_send_json(
					array(
						'status'  => 'fail',
						'message' => 'Payment Failed',
					)
				);
			}
		}

		wcf()->logger->log( 'Ended: ' . __CLASS__ . '::' . __FUNCTION__ );
	}

	/**
	 * Add paypal fee
	 *
	 * @param object $order Add order paypal fee.
	 * @param mixed  $price_breakdown Fee amount's breakdown.
	 * @param int    $step_id step id.
	 *
	 * @return void
	 */
	public static function add_fee_net_amount( $order, $price_breakdown, $step_id ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		if ( ! empty( $price_breakdown['paypal_fee']['value'] ) ) {
			$order->update_meta_data( 'cartflows_offer' . CPPW_PAYPAL_FEE . '_' . $step_id, $price_breakdown['paypal_fee']['value'] );
		}
		if ( ! empty( $price_breakdown['net_amount']['value'] ) ) {
			$order->update_meta_data( 'cartflows_offer' . CPPW_PAYPAL_NET . '_' . $step_id, $price_breakdown['net_amount']['value'] );
		}

		wcf()->logger->log( 'Paypal Fee ' . $price_breakdown['paypal_fee']['value'] );
		wcf()->logger->log( 'Paypal Net Amount ' . $price_breakdown['net_amount']['value'] );

		wcf()->logger->log( 'Ended: ' . __CLASS__ . '::' . __FUNCTION__ );
	}


	/**
	 * Tokenize to save source of payment if required
	 *
	 * @param bool $save_source force save source.
	 *
	 * @return bool
	 */
	public function tokenize_if_required( $save_source ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$checkout_id = _get_wcf_checkout_id();
		$flow_id     = wcf()->utils->get_flow_id_from_step_id( $checkout_id );

		if ( ! $checkout_id ) {
			$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			$flow_id     = wcf()->utils->get_flow_id_from_post_data();
		}

		if ( $checkout_id && $flow_id && wcf_pro()->flow->is_upsell_exist_in_flow( $flow_id, $checkout_id ) ) {
			$save_source = true;
			wcf()->logger->log( 'Force save source enabled' );
		}

		wcf()->logger->log( 'Ended: ' . __CLASS__ . '::' . __FUNCTION__ );

		return $save_source;
	}

	/**
	 * Get WooCommerce payment geteway.
	 *
	 * @return object
	 */
	public function get_wc_gateway() {

		$gateways = wc()->payment_gateways->payment_gateways();

		return $gateways[ $this->key ];
	}

	/**
	 * Allow gateways to declare whether they support offer refund
	 *
	 * @return bool
	 */
	public function is_api_refund() {

		return $this->is_api_refund;
	}

	/**
	 * Process offer refund
	 *
	 * @param object $order Order Object.
	 * @param array  $offer_data offer data.
	 *
	 * @return bool.
	 */
	public function process_offer_refund( $order, $offer_data ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$response      = false;
		$refund_amount = strval( $offer_data['refund_amount'] );
		$refund_reason = $offer_data['refund_reason'];

		$gateway = $this->get_wc_gateway();

		$is_refund_success = $gateway->process_refund( $order->get_id(), $refund_amount, $refund_reason );

		if ( $is_refund_success ) {
			$response = true;
		}

		wcf()->logger->log( 'Ended: ' . __CLASS__ . '::' . __FUNCTION__ );

		return $response;
	}


	/**
	 * Setup the Payment data for Stripe's Automatic Subscription.
	 *
	 * @param WC_Subscription|WC_Order $subscription An instance of a subscription object.
	 * @param object                   $order Object of order.
	 * @param array                    $offer_product array of offer product.
	 *
	 * @return void
	 */
	public function add_subscription_payment_meta( $subscription, $order, $offer_product ) {

		if ( 'cppw_paypal' === $order->get_payment_method() ) {
			$subscription->update_meta_data( CPPW_SUB_AGREEMENT_ID, $order->get_meta( CPPW_SUB_AGREEMENT_ID ) );
			$subscription->save();
		}
	}

	/**
	 * Save the parent payment meta to child order.
	 *
	 * @param object $parent_order Object of order.
	 * @param object $child_order Object of order.
	 * @param int    $transaction_id transaction id.
	 *
	 * @return void
	 */
	public function add_required_meta_to_child_order( $parent_order, $child_order, $transaction_id ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$offer_step_id = $child_order->get_meta( '_cartflows_offer_step_id' );

		$child_order->update_meta_data( CPPW_PAYPAL_TRANSACTION_ID, $parent_order->get_meta( 'cartflows_offer_txn_resp_' . $offer_step_id ) );
		$child_order->update_meta_data( CPPW_SUB_AGREEMENT_ID, $parent_order->get_meta( 'cartflows_offer' . CPPW_SUB_AGREEMENT_ID . '_' . $offer_step_id ) );

		$child_order->update_meta_data( CPPW_PAYPAL_FEE, $parent_order->get_meta( 'cartflows_offer' . CPPW_PAYPAL_FEE . '_' . $offer_step_id ) );
		$child_order->update_meta_data( CPPW_PAYPAL_NET, $parent_order->get_meta( 'cartflows_offer' . CPPW_PAYPAL_NET . '_' . $offer_step_id ) );

		$child_order->save();

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Gateway_Cppw_Paypal' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateway_Cppw_Paypal::get_instance();
