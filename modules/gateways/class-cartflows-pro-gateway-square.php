<?php
/**
 * Square Credit Card Gateway.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Square\SquareClient;
use Square\Models\Address;
use Square\Models\CreatePaymentRequest;
use Square\Models\RefundPaymentRequest;
use WooCommerce\Square\Utilities\Money_Utility;
use WooCommerce\Square\Framework\Square_Helper;
use WooCommerce\Square\Framework\PaymentGateway\Payment_Gateway_Helper;

/**
 * Class Cartflows_Pro_Gateway_Square.
 */
class Cartflows_Pro_Gateway_Square {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable: Square gateway api_client.
	 *
	 * @var instance
	 */
	public $api_client;

	/**
	 * Key name variable
	 *
	 * @var key
	 */
	public $key = 'square_credit_card';

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

		add_filter( 'wc_' . $this->key . '_payment_form_tokenization_forced', array( $this, 'maybe_force_tokenization' ), 10, 2 );
		add_filter( 'wc_' . $this->key . '_payment_form_tokenization_allowed', array( $this, 'maybe_force_tokenization' ), 10, 2 );

		// Create payment token for the order if not available.
		add_filter( 'wc_payment_gateway_' . $this->key . '_process_payment', array( $this, 'create_token_process_payment' ), 10, 3 );

		add_filter( 'wc_payment_gateway_' . $this->key . '_get_order', array( $this, 'square_get_order_to_add_token' ), 10, 2 );

		add_action( 'cartflows_offer_child_order_created_' . $this->key, array( $this, 'store_square_meta_keys_for_refund' ), 10, 3 );

		add_action( 'cartflows_offer_subscription_created', array( $this, 'add_subscription_payment_meta_for_square' ), 10, 3 );

		add_action( 'cartflows_offer_accepted', array( $this, 'may_reduce_the_offer_product_stock' ), 10, 2 );

	}

	/**
	 * Forces tokenization for upsell/downsells.
	 *
	 * @since 1.5.0
	 *
	 * @param bool $force_tokenization whether tokenization should be forced.
	 * @return bool
	 */
	public function maybe_force_tokenization( $force_tokenization ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		if ( isset( $_POST['post_data'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing

			$post_data = array();

			$post_raw_data = sanitize_text_field( wp_unslash( $_POST['post_data'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			parse_str( $post_raw_data, $post_data );

			$checkout_id = wcf_pro()->utils->get_checkout_id_from_data( $post_data );
			$flow_id     = wcf_pro()->utils->get_flow_id_from_data( $post_data );

			if ( $checkout_id && $flow_id && wcf_pro()->flow->is_upsell_exist_in_flow( $flow_id, $checkout_id ) ) {
				$force_tokenization = true;
				wcf()->logger->log( 'Force save source enabled' );
			}
		}
		wcf()->logger->log( $force_tokenization );

		wcf()->logger->log( 'Ended: ' . __CLASS__ . '::' . __FUNCTION__ );

		return $force_tokenization;
	}


	/**
	 * Add token to order.
	 *
	 * @param object $order order data.
	 * @param object $gateway class instance.
	 */
	public function square_get_order_to_add_token( $order, $gateway ) {

		if ( $this->key === $gateway->id ) {

			$this->set_square_gateway_config();

			if ( empty( $order->payment->token ) ) {
				$order->payment->token = $order->get_meta( '_wc_square_credit_card_payment_token' );
			}
		}

		return $order;
	}

	/**
	 * Create token.
	 *
	 * @since 1.5.0
	 *
	 * @param array  $process_payment gateway data.
	 * @param int    $order_id order id.
	 * @param object $gateway class instance.
	 * @return array
	 */
	public function create_token_process_payment( $process_payment, $order_id, $gateway ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$order       = wc_get_order( $order_id );
		$flow_id     = wcf()->utils->get_flow_id_from_order( $order );
		$checkout_id = wcf()->utils->get_checkout_id_from_order( $order );

		wcf()->logger->log( 'Flow ID: ' . $flow_id . ' Checkout ID : ' . $checkout_id );

		if ( ! empty( $order ) && $this->key === $order->get_payment_method() && ( $checkout_id && $flow_id && wcf_pro()->flow->is_upsell_exist_in_flow( $flow_id, $checkout_id ) ) ) {

			wcf()->logger->log( 'Upsell exists in the flow. Let\'s add the token to the order.' );

			$this->set_square_gateway_config();

			$order = $this->get_wc_gateway()->get_order( $order );

			try {

				if ( isset( $order->payment->token ) && ! empty( $order->payment->token ) ) {

					wcf()->logger->log( 'Token Found : ' . $order->payment->token );

					$gateway->add_transaction_data( $order );

					// otherwise tokenize.
				} else {
					wcf()->logger->log( 'Token Not Found. Creating one' );

					$order = $this->update_order_with_payment_info( $order );
					$order = $gateway->get_payment_tokens_handler()->create_token( $order );

					wcf()->logger->log( 'Updated order after creation token : ' . wp_json_encode( $order ) );
				}
			} catch ( Exception $e ) {
				wcf()->logger->log( 'Order Details: ' . wcf_print_r( $order, true ) );
				wcf()->logger->log( '\n ==== Exception Occurred ==== \n Can not create the token. Error Response: ' . $e->getMessage() );
			}
		}

		wcf()->logger->log( 'Ended: ' . __CLASS__ . '::' . __FUNCTION__ );

		return $process_payment;
	}


	/**
	 * Get WooCommerce payment geteways.
	 *
	 * @return object
	 */
	public function get_wc_gateway() {

		global $woocommerce;

		$gateways = $woocommerce->payment_gateways->payment_gateways();

		return $gateways[ $this->key ];
	}

	/**
	 * After payment process.
	 *
	 * @param object $order order data.
	 * @param array  $product product data.
	 * @return array
	 */
	public function process_offer_payment( $order, $product ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$is_successful = false;

		// set up the square configuration.
		$this->set_square_gateway_config();

		$fields = $this->prepare_order_data( $order, $product );

		$amount_to_pay = Money_Utility::amount_to_money( $product['total'], $fields['amount_money']['currency'] );

		// Create payment request for the offer.
		$request = new CreatePaymentRequest(
			$fields['source_id'],
			$fields['idempotency_key'],
			$amount_to_pay
		);

		$request->setNote( (string) apply_filters( 'cartflows_' . $this->key . 'offer_statement_descriptor', $fields['note'] ) );
		$request->setAutocomplete( true );
		$request->setCustomerId( $fields['customer_id'] );
		$request->setLocationId( $fields['location_id'] );
		$request->setBillingAddress( $fields['billing_address'] ); // Get billing address from the main order and append as it is.
		$request->setShippingAddress( $fields['shipping_address'] ); // Default to null from the gateway side.

		// Process actual payment against the offer.
		$response = $this->api_client->getPaymentsApi()->createPayment( $request );

		// Evaluate the API call for error findings.
		$errors = Cartflows_Pro_Helper::is_api_errors( $response );

		if ( $errors['is_error'] ) {
			wcf()->logger->log(
				'==== API Error: ====' . PHP_EOL . wcf_print_r( $errors, true ) . PHP_EOL . '==== Gateway Response start ====' . PHP_EOL . wcf_print_r( $response, true ) . PHP_EOL . '==== Error Gateway response end ===='
			);
		} else {
			$transaction_id = $response->getResult()->getPayment()->getId();
			$order->update_status( 'processing' );
			$this->store_offer_transaction( $order, $transaction_id, $product );
			$is_successful = true;
		}

		wcf()->logger->log( 'Ended: ' . __CLASS__ . '::' . __FUNCTION__ );

		return $is_successful;

	}

	/**
	 * Prepare data for payment.
	 *
	 * @param object $order order data.
	 * @param array  $product product data.
	 * @return array
	 */
	public function prepare_order_data( $order, $product ) {
		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$idempotency_key  = strval( $order->get_id() . '_' . $product['step_id'] );
		$location_id      = $this->location_id;
		$currency         = $order->get_currency();
		$shipping_address = null;

		$customer_id        = $order->get_customer_id();
		$_customer_user     = $order->get_meta( '_customer_user' );
		$customer_card_id   = $order->get_meta( '_wc_square_credit_card_payment_token' );
		$square_customer_id = $order->get_meta( '_wc_square_credit_card_customer_id' );

		if ( empty( $square_customer_id ) ) {
			$square_customer_id = $order->get_meta( '_square_customer_id' );
		}

		// Prepare Billing address for the order.
		$billing_address = new Address();
		$billing_address->setFirstName( $order->get_billing_first_name() );
		$billing_address->setLastName( $order->get_billing_last_name() );
		$billing_address->setAddressLine1( $order->get_billing_address_1() );
		$billing_address->setAddressLine2( $order->get_billing_address_2() );
		$billing_address->setLocality( $order->get_billing_city() );
		$billing_address->setAdministrativeDistrictLevel1( $order->get_billing_state() );
		$billing_address->setPostalCode( $order->get_billing_postcode() );
		$billing_address->setCountry( $order->get_billing_country() ? $order->get_billing_country() : $order->get_shipping_country() );

		// Prepare Shipping address for the order.
		if ( $order->get_shipping_address_1( 'edit' ) || $order->get_shipping_address_2( 'edit' ) ) {

			$shipping_address = new Address();
			$shipping_address->setFirstName( $order->get_shipping_first_name() );
			$shipping_address->setLastName( $order->get_shipping_last_name() );
			$shipping_address->setAddressLine1( $order->get_shipping_address_1() ? $order->get_shipping_address_1() : $order->get_billing_address_1() );
			$shipping_address->setAddressLine2( $order->get_shipping_address_2() ? $order->get_shipping_address_2() : $order->get_billing_address_2() );
			$shipping_address->setLocality( $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city() );
			$shipping_address->setAdministrativeDistrictLevel1( $order->get_shipping_state() ? $order->get_shipping_state() : $order->get_billing_state() );
			$shipping_address->setPostalCode( $order->get_shipping_postcode() ? $order->get_shipping_postcode() : $order->get_billing_postcode() );
			$shipping_address->setCountry( $order->get_shipping_country() ? $order->get_shipping_country() : $order->get_billing_country() );
		}

		$fields = array(
			'idempotency_key'  => $idempotency_key,
			'location_id'      => $location_id,
			'amount_money'     => array(
				'amount'   => (int) $this->format_amount( $product['total'], $currency ),
				'currency' => $currency,
			),
			'source_id'        => $customer_card_id,
			'customer_id'      => $square_customer_id,
			'shipping_address' => $shipping_address,
			'billing_address'  => $billing_address,
			'reference_id'     => (string) $order->get_id(),
			/* translators: %1$s: site name, %2$s: order id, %3$s: step id */
			'note'             => sprintf( __( '%1$s - Order %2$s_%3$s - One Click Payment', 'cartflows-pro' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $order->get_id(), $product['step_id'] ),
		);

		wcf()->logger->log( 'Offer Order Data: ' . wp_json_encode( $fields ) );

		wcf()->logger->log( 'Ended: ' . __CLASS__ . '::' . __FUNCTION__ );
		return $fields;

	}

	/**
	 * Process amount to be passed to Square payment API.
	 *
	 * @param int    $total order total.
	 * @param string $currency currancy.
	 */
	public function format_amount( $total, $currency = '' ) {
		if ( ! $currency ) {
			$currency = get_woocommerce_currency();
		}

		switch ( strtoupper( $currency ) ) {
			// Zero decimal currencies.
			case 'BIF':
			case 'CLP':
			case 'DJF':
			case 'GNF':
			case 'JPY':
			case 'KMF':
			case 'KRW':
			case 'MGA':
			case 'PYG':
			case 'RWF':
			case 'VND':
			case 'VUV':
			case 'XAF':
			case 'XOF':
			case 'XPF':
				$total = absint( $total );
				break;
			default:
				$total = round( $total, 2 ) * 100; // In cents.
				break;
		}

		return $total;
	}


	/**
	 * Store Offer Trxn Charge.
	 *
	 * @param WC_Order $order    The order that is being paid for.
	 * @param Object   $response The response that is send from the payment gateway.
	 * @param array    $product  The product data.
	 */
	public function store_offer_transaction( $order, $response, $product ) {

		$order->update_meta_data( 'cartflows_offer_txn_resp_' . $product['step_id'], $response );
		$order->save();
	}

	/**
	 * Set up the required configuration for payment.
	 */
	public function set_square_gateway_config() {

		// Set the access tokan.
		$this->access_token = $this->get_wc_gateway()->get_plugin()->get_settings_handler()->get_access_token();
		$this->access_token = empty( $this->access_token ) ? $this->get_wc_gateway()->get_plugin()->get_settings_handler()->get_option( 'sandbox_token' ) : $this->access_token;

		// Set the location id.
		$this->location_id = $this->get_wc_gateway()->get_plugin()->get_settings_handler()->get_location_id();

		$this->api_client = new SquareClient(
			array(
				'accessToken' => $this->access_token,
				'environment' => wc_square()->get_settings_handler()->get_environment(),
			)
		);

	}

	/**
	 * Save required meta keys to refund seperate order.
	 *
	 * @param object $parent_order Parent order Object.
	 * @param object $child_order Child order Object.
	 * @param string $transaction_id id.
	 */
	public function store_square_meta_keys_for_refund( $parent_order, $child_order, $transaction_id ) {

		if ( ! empty( $transaction_id ) ) {

			$child_order->update_meta_data( '_wc_square_credit_card_square_location_id', $parent_order->get_meta( '_wc_square_credit_card_square_location_id', true ) );
			$child_order->update_meta_data( '_wc_square_credit_card_customer_id', $parent_order->get_meta( '_wc_square_credit_card_customer_id', true ) );
			$child_order->update_meta_data( '_wc_square_credit_card_authorization_code', $transaction_id );
			$child_order->update_meta_data( '_wc_square_credit_card_square_order_id', $transaction_id );
			$child_order->update_meta_data( '_wc_square_credit_card_trans_id', $transaction_id );
			$child_order->update_meta_data( '_wc_square_credit_card_charge_captured', 'yes' );
			$child_order->update_meta_data( '_wc_square_credit_card_square_version', $parent_order->get_meta( '_wc_square_credit_card_square_version', true ) );

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

		// set up the square configuration.
		$this->set_square_gateway_config();

		$currancy    = $order->get_currency();
		$response_id = false;

		if ( ! is_null( $offer_data['refund_amount'] ) ) {

			// Refund data.
			$fields = array(
				'idempotency_key' => strval( $offer_data['order_id'] ),
				'amount_money'    => Money_Utility::amount_to_money( $offer_data['refund_amount'], $currancy ),
				'payment_id'      => $offer_data['transaction_id'],
				'reason'          => $offer_data['refund_reason'],
			);

			// Create refund request.
			$refund_request = new RefundPaymentRequest(
				$fields['idempotency_key'],
				$fields['amount_money'],
				$fields['payment_id']
			);

			// API call to initiate refund payment.
			$refund_response = $this->api_client->getRefundsApi()->refundPayment( $refund_request );

			$errors = Cartflows_Pro_Helper::is_api_errors( $refund_response );

			if ( $errors['is_error'] ) {

				// For support team, then can ask the user to set WP_DEBUG to true to log the error message.
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// Using it for debugging purpose.
					error_log( 'CartFlows Square gateway refund failed: Response: ' . PHP_EOL . wcf_print_r( $errors, true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}
			} else {
				$response_id = $refund_response->getResult()->getRefund()->getId();
			}
		}

		return $response_id;
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
	 * Setup the Payment data for Square's Automatic Subscription.
	 *
	 * @param WC_Subscription $subscription An instance of a subscription object.
	 * @param object          $order Object of order.
	 * @param array           $offer_product array of offer product.
	 */
	public function add_subscription_payment_meta_for_square( $subscription, $order, $offer_product ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		if ( 'square_credit_card' === $order->get_payment_method() ) {

			$subscription->update_meta_data( '_wc_square_credit_card_payment_token', $order->get_meta( '_wc_square_credit_card_payment_token', true ) );
			$subscription->update_meta_data( '_wc_square_credit_card_customer_id', $order->get_meta( '_wc_square_credit_card_customer_id', true ) );
			$subscription->save();

			wcf()->logger->log( 'Subscription meta added' );
		}

		wcf()->logger->log( 'Ended: ' . __CLASS__ . '::' . __FUNCTION__ );
	}

	/**
	 * Reduce the offer product stock.
	 *
	 * @param object $order Object of order.
	 * @param array  $offer_product array of offer product.
	 */
	public function may_reduce_the_offer_product_stock( $order, $offer_product ) {

		// Do not update the quantity if the page gateway is not the one which is required.
		if ( 'square_credit_card' !== $order->get_payment_method() ) {
			return;
		}

		$product = wc_get_product( $offer_product['id'] );

		if ( ! wcf_pro()->utils->is_separate_offer_order() && $product->managing_stock() ) {

			$new_stock = wc_update_product_stock( $offer_product['id'], $offer_product['qty'], 'decrease' );

			$changes[] = array(
				'product' => $product,
				'from'    => $new_stock + intval( $offer_product['qty'] ),
				'to'      => $new_stock,
			);
			wc_trigger_stock_change_notifications( $order, $changes );

		}
	}

	/**
	 * Return order information for creating customer response
	 *
	 * @param object $order Object of current order.
	 *
	 * @return object|\WC_Order
	 * @since 1.6.4
	 */
	public function update_order_with_payment_info( $order ) {

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		$order->payment = new \stdClass();

		if ( empty( $order->payment->token ) ) {
			$order->payment->nonce          = Square_Helper::get_post( 'wc-square-credit-card-payment-nonce' );
			$order->payment->card_type      = Payment_Gateway_Helper::normalize_card_type( Square_Helper::get_post( 'wc-square-credit-card-card-type' ) );
			$order->payment->account_number = substr( Square_Helper::get_post( 'wc-square-credit-card-last-four' ), -4 );
			$order->payment->last_four      = substr( Square_Helper::get_post( 'wc-square-credit-card-last-four' ), -4 );
			$order->payment->exp_month      = Square_Helper::get_post( 'wc-square-credit-card-exp-month' );
			$order->payment->exp_year       = Square_Helper::get_post( 'wc-square-credit-card-exp-year' );
			$order->payment->postcode       = Square_Helper::get_post( 'wc-square-credit-card-payment-postcode' );
		}

		return $order;
	}

}

/**
 *  Prepare if class 'Cartflows_Pro_Gateway_Square' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateway_Square::get_instance();
