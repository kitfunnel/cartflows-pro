<?php
/**
 * Square Credit Card Gateway.
 *
 * Remove this file after 2 updates.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SquareConnect\Configuration;
use Square\Models\CreatePaymentRequest;
use SquareConnect\ApiClient;
use SquareConnect\Api\PaymentsApi;
use SquareConnect\Api\RefundsApi;
use SquareConnect\Model\RefundPaymentRequest;


/**
 * Class Cartflows_Pro_Gateway_Square_Old.
 */
class Cartflows_Pro_Gateway_Square_Old {

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

		if ( isset( $_POST['post_data'] ) ) {

			$post_data = array();

			$post_raw_data = sanitize_text_field( wp_unslash( $_POST['post_data'] ) );

			parse_str( $post_raw_data, $post_data );

			$checkout_id = wcf_pro()->utils->get_checkout_id_from_data( $post_data );
			$flow_id     = wcf_pro()->utils->get_flow_id_from_data( $post_data );

			if ( $checkout_id && $flow_id && wcf_pro()->flow->is_upsell_exist_in_flow( $flow_id, $checkout_id ) ) {
				$force_tokenization = true;
				wcf()->logger->log( 'Force save source enabled' );
			}
		}
		wcf()->logger->log( $force_tokenization );

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

		$order = wc_get_order( $order_id );
		if ( $this->key === $order->get_payment_method() ) {
			$this->set_square_gateway_config();

			$order = $this->get_wc_gateway()->get_order( $order );
			if ( empty( $order->payment->token ) && $order->get_customer_id() < 1 ) {
				try {
					$this->get_wc_gateway()->get_payment_tokens_handler()->create_token( $order );
				} catch ( Exception $e ) {
					wcf()->logger->log( '\n' . '==== Exception Occured ==== \n' . 'Can not create the token.' ); //phpcs:ignore
				}
			}
		}

		return $process_payment;
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
	 * After payment process.
	 *
	 * @param array $order order data.
	 * @param array $product product data.
	 * @return array
	 */
	public function process_offer_payment( $order, $product ) {

		$is_successful = false;

		// set up the square configuration.
		$this->set_square_gateway_config();

		$fields = $this->prepare_order_data( $order, $product );

		// API call for create payment.
		$payment_api = new PaymentsApi( $this->api_client );
		$response    = $payment_api->createPayment( $fields );

		if ( empty( $response['error'] ) && isset( $response['payment'] ) ) {

			$payment_data   = $response['payment'];
			$transaction_id = $payment_data['id'];
			$order_id       = $order->get_id();
			$order->update_status( 'processing' );
			$this->store_offer_transaction( $order, $transaction_id, $product );
			$is_successful = true;

		} else {
			wcf()->logger->log( '\n' . '==== Product Start ==== \n' . print_r( $product, true ) . '==== Product End ==== \n ==== Error Response start ==== \n' . print_r( $response, true ) . '==== Error Response end ====\n' ); //phpcs:ignore
		}

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

		$idempotency_key = strval( $order->get_id() . '_' . $product['step_id'] );
		$location_id     = $this->location_id;
		$currency        = $order->get_currency();

		$customer_id        = $order->get_customer_id();
		$_customer_user     = $order->get_meta( '_customer_user' );
		$customer_card_id   = $order->get_meta( '_wc_square_credit_card_payment_token' );
		$square_customer_id = $order->get_meta( '_wc_square_credit_card_customer_id' );

		if ( empty( $square_customer_id ) ) {
			$square_customer_id = $order->get_meta( '_square_customer_id' );
		}

		$shipping_address = array(
			'address_line_1'                  => $order->get_shipping_address_1() ? $order->get_shipping_address_1() : $order->get_billing_address_1(),
			'address_line_2'                  => $order->get_shipping_address_2() ? $order->get_shipping_address_2() : $order->get_billing_address_2(),
			'locality'                        => $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city(),
			'administrative_district_level_1' => $order->get_shipping_state() ? $order->get_shipping_state() : $order->get_billing_state(),
			'postal_code'                     => $order->get_shipping_postcode() ? $order->get_shipping_postcode() : $order->get_billing_postcode(),
			'country'                         => $order->get_shipping_country() ? $order->get_shipping_country() : $order->get_billing_country(),
		);

		$billing_address = array(
			'address_line_1'                  => $order->get_billing_address_1(),
			'address_line_2'                  => $order->get_billing_address_2(),
			'locality'                        => $order->get_billing_city(),
			'administrative_district_level_1' => $order->get_billing_state(),
			'postal_code'                     => $order->get_billing_postcode(),
			'country'                         => $order->get_billing_country() ? $order->get_billing_country() : $order->get_shipping_country(),
		);

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

		// Set host.
		$this->api_config = new Configuration();
		$this->api_config->setHost( 'https://connect.squareup.com' );

		$mode = get_option( 'wc_square_settings' );
		if ( 'yes' === $mode['enable_sandbox'] ) {
			$this->api_config->setHost( 'https://connect.squareupsandbox.com' );
		}

		$this->api_config->setAccessToken( $this->access_token );
		$this->api_client = new ApiClient( $this->api_config );

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
			$child_order->update_meta_data( '_wc_square_credit_card_authorization_code', $transaction_id );
			$child_order->update_meta_data( '_wc_square_credit_card_customer_id', $parent_order->get_meta( '_wc_square_credit_card_customer_id', true ) );
			$child_order->update_meta_data( '_wc_square_credit_card_square_order_id', $transaction_id );
			$child_order->update_meta_data( '_wc_square_credit_card_trans_id', $transaction_id );
			$child_order->update_meta_data( '_wc_square_credit_card_charge_captured', 'yes' );

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
			$fields = array(
				'idempotency_key' => strval( $offer_data['order_id'] ),
				'amount_money'    => array(
					'amount'   => (int) $this->format_amount( $offer_data['refund_amount'], $currancy ),
					'currency' => $currancy,
				),
				'payment_id'      => $offer_data['transaction_id'],
				'reason'          => $offer_data['refund_reason'],
			);

			// API call for refund payment.
			$refund_api = new RefundsApi( $this->api_client );
			$response   = $refund_api->refundPayment( $fields );

			if ( empty( $response['error'] ) && isset( $response['refund'] ) ) {

				$refund_data = $response['refund'];
				$response_id = $refund_data['id'];
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

		if ( 'square_credit_card' === $order->get_payment_method() ) {

			$subscription->update_meta_data( '_wc_square_credit_card_payment_token', $order->get_meta( '_wc_square_credit_card_payment_token', true ) );
			$subscription->update_meta_data( '_wc_square_credit_card_customer_id', $order->get_meta( '_wc_square_credit_card_customer_id', true ) );
			$subscription->save();
		}
	}

	/**
	 * Reduce the offer product stock.
	 *
	 * @param object $order Object of order.
	 * @param array  $offer_product array of offer product.
	 */
	public function may_reduce_the_offer_product_stock( $order, $offer_product ) {

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

}

/**
 *  Prepare if class 'Cartflows_Pro_Gateway_Square_Old' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateway_Square_Old::get_instance();
