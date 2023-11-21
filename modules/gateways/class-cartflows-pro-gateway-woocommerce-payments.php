<?php
/**
 * Stripe Gateway.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WCPay\Payment_Information;
use WCPay\Constants\Payment_Type;
use WCPay\Constants\Payment_Initiated_By;

/**
 * Class Cartflows_Pro_Gateway_Woocommerce_Payments.
 */
class Cartflows_Pro_Gateway_Woocommerce_Payments {

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
	public $key = 'woocommerce_payments';

	/**
	 * Refund supported variable
	 *
	 * @var is_api_refund
	 */
	public $is_api_refund = true;

	/**
	 * Refund supported variable
	 *
	 * @var gateway_service_objects
	 */
	public $gateway_service_objects = array();


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

		add_filter( 'wc_payments_display_save_payment_method_checkbox', array( $this, 'tokenize_if_required' ), 11, 1 );

		add_action( 'cartflows_order_started', array( $this, 'save_3ds_source_for_later' ), 10, 1 );
		add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'add_payment_gateway_option_hidden_field' ), 99 );

		add_action( 'wp_ajax_wcf_woop_create_payment_intent', array( $this, 'create_payment_intent' ) );
		add_action( 'wp_ajax_nopriv_wcf_woop_create_payment_intent', array( $this, 'create_payment_intent' ) );

		add_action( 'cartflows_offer_child_order_created_' . $this->key, array( $this, 'add_required_meta_to_child_order' ), 10, 3 );
		add_action( 'cartflows_offer_subscription_created', array( $this, 'add_subscription_payment_meta' ), 10, 3 );

		$this->prepare_gateway_objects();

	}

	/**
	 * Prepare the array of all the required classes of payment gateway to use it.
	 *
	 * @return void
	 */
	public function prepare_gateway_objects() {

		$api_client          = '';
		$api_account_service = '';
		$database_cache      = '';
		$customer_services   = '';
		$token_service       = '';
		$api_session_service = '';

		if ( class_exists( 'WC_Payments' ) && class_exists( 'WC_Payments_Customer_Service' ) && class_exists( 'WC_Payments_Token_Service' ) ) {
			$api_client          = WC_Payments::get_payments_api_client();
			$api_account_service = WC_Payments::get_account_service();
			$database_cache      = class_exists( 'WCPay\Database_Cache' ) ? WC_Payments::get_database_cache() : null;

			/**
			 * In the latest version of WooCommerce Payments, they have introduced a new parameter to the WC_Payments_Customer_Service class,
			 * So, adding a backward compatibility for the older version which is less than 6.7.0.
			 */
			if ( version_compare( '6.7.1', WCPAY_VERSION_NUMBER, '<=' ) ) {
				// Execute it for the WCPAY version is greater than 6.7.0.
				$customer_services = WC_Payments::get_customer_service();

				// Fallback if in case the customer service is empty.
				if ( empty( $customer_services ) ) {
					$api_session_service = WC_Payments::get_session_service();
					$customer_services   = new WC_Payments_Customer_Service( $api_client, $api_account_service, $database_cache, $api_session_service );
				}
			} else {
				// Execute it for the WCPAY version is less than 6.7.0.
				$customer_services = new WC_Payments_Customer_Service( $api_client, $api_account_service, $database_cache );
			}

			$token_service = new WC_Payments_Token_Service( $api_client, $customer_services );
		}

		$this->gateway_service_objects = array(
			'api_client'          => $api_client ? $api_client : '',
			'api_account_service' => $api_account_service ? $api_account_service : '',
			'database_cache'      => $database_cache ? $database_cache : '',
			'customer_services'   => $customer_services ? $customer_services : '',
			'token_service'       => $token_service ? $token_service : '',
		);

	}

	/**
	 * Create intent for the offer order.
	 */
	public function create_payment_intent() {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$nonce = isset( $_POST['wcf_wc_payment_nonce'] ) && ! empty( $_POST['wcf_wc_payment_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wcf_wc_payment_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'wcf_woop_create_payment_intent' ) ) {
			$response_data = array( 'message' => __( 'Nonce validation failed', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		$variation_id = '';
		$input_qty    = '';

		if ( isset( $_POST['variation_id'] ) ) {
			$variation_id = intval( $_POST['variation_id'] );
		}

		if ( isset( $_POST['input_qty'] ) && ! empty( $_POST['input_qty'] ) ) {
			$input_qty = intval( $_POST['input_qty'] );
		}

		$step_id       = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
		$order_id      = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : 0;
		$offer_product = wcf_pro()->utils->get_offer_data( $step_id, $variation_id, $input_qty, $order_id );
		$order         = wc_get_order( $order_id );

		if ( isset( $offer_product['price'] ) && ( floatval( 0 ) === floatval( $offer_product['price'] )
				|| '' === trim( $offer_product['price'] ) ) ) {
			wp_send_json(
				array(
					'result'  => 'fail',
					'message' => 'Zero value product',
				)
			);
		} else {

			$gateway = $this->get_wc_gateway();

			if ( $gateway ) {

				wcf()->logger->log( 'Gateway object found : ' . wp_json_encode( $gateway ) );

				try {
					$token = $this->retrieve_token_from_order( $order );

					wcf()->logger->log( 'payment_token from order : ' . PHP_EOL . wp_json_encode( $token ) );

					$payment_information = new Payment_Information( '', $order, Payment_Type::SINGLE(), $token, Payment_Initiated_By::CUSTOMER() );

					$response = $this->process_payment_against_intent( $order, $offer_product, $payment_information );

				} catch ( Exception $e ) {

					wcf()->logger->log(
						'==== Gateway Response start ====' . PHP_EOL . wcf_print_r( $response, true ) . PHP_EOL . '==== Error Gateway response end ===='
					);

					wp_send_json(
						array(
							'result'  => 'fail',
							'message' => 'Payment Failed',
						)
					);
				}

				// Process the response for the order.
				if ( ! empty( $response ) ) {

					$intent_id     = $response->get_id() ? $response->get_id() : 0;
					$status        = $response->get_status();
					$charge        = $response->get_charge();
					$charge_id     = ! empty( $charge ) ? $charge->get_id() : null;
					$client_secret = $response->get_client_secret();

					wcf()->logger->log( 'Intent i:e Txn response : ' . wp_json_encode( $response ) );
					wcf()->logger->log( 'Charge ID :  ' . $charge_id );

					$extra_data = array(
						'step_id'   => $step_id,
						'order_id'  => $order_id,
						'intent_id' => $intent_id,
						'charge_id' => $charge_id,
					);

					// Save the intent ID to the order.
					$this->store_order_payment_meta( $order, $payment_information, $extra_data );

					wcf()->logger->log( 'Newly generated Intent ID : ' . $intent_id );

					wp_send_json(
						array(
							'result'        => 'success',
							'status'        => $status,
							'client_secret' => $client_secret,
							'client_intend' => $intent_id,
						)
					);

				}

				wcf()->logger->log( 'Final APi response : ' . wp_json_encode( $response ) );
			}

			wp_send_json(
				array(
					'result'  => 'fail',
					'message' => __( 'No payment. No gateway found', 'cartflows-pro' ),
				)
			);
		}

		wcf()->logger->log( 'Ended: ' . __CLASS__ . '::' . __FUNCTION__ );
	}

	/**
	 * Store the payment meta in the parent order to use it in the child order.
	 *
	 * @param object $order Parent object.
	 * @param object $payment_information Object of payment information with all data.
	 * @param array  $extra_data Any extra data that may require.
	 */
	public function store_order_payment_meta( $order, $payment_information, $extra_data ) {

		$payment_method = $payment_information->get_payment_method();
		$order_id       = $order->get_id();
		$step_id        = $extra_data['step_id'];

		$order->update_meta_data( '_wcf_woop_offer_stripe_intent_id_' . $step_id, $extra_data['intent_id'] );
		$order->update_meta_data( '_wcf_woop_offer_charge_id_' . $step_id . '_' . $order_id, $extra_data['charge_id'] );
		$order->update_meta_data( '_wcf_woop_offer_payment_method_id', $payment_method );
		$order->update_meta_data( '_wcf_woop_offer_wcpay_mode', WC_Payments::get_gateway()->is_in_test_mode() ? 'test' : 'prod' );
		$order->save();
	}

	/**
	 * Retrieve the token from the main order.
	 *
	 * @param object $order Object of current order.
	 * @param bool   $has_token Separator used to log the logs.
	 *
	 * @return WC_Payment_Tokens|mix $token The retrieved order token on success & false otherwise.
	 */
	public function retrieve_token_from_order( $order, $has_token = false ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		if ( $has_token ) {
			wcf()->logger->log( 'Checking for token in the order to confirm the order' );
		}

		$order_tokens = $order->get_payment_tokens();
		$token_id     = end( $order_tokens );
		$token        = $token_id ? WC_Payment_Tokens::get( $token_id ) : null;

		wcf()->logger->log( 'Tokens found: ' . wp_json_encode( $token ) );

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		return $token;

	}

	/**
	 * Tokenize to save source of payment if required
	 *
	 * @param bool $save_source force save source.
	 * @return bool $save_source modified save value.
	 */
	public function tokenize_if_required( $save_source ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
		$flow_id     = wcf()->utils->get_flow_id_from_post_data();

		if ( $checkout_id && $flow_id && wcf_pro()->flow->is_upsell_exist_in_flow( $flow_id, $checkout_id ) ) {
			$save_source = false;
			wcf()->logger->log( 'Force save source enabled' );
		}

		wcf()->logger->log( 'Ended: ' . __CLASS__ . '::' . __FUNCTION__ );

		return $save_source;
	}

	/**
	 * Save 3d source.
	 *
	 * @param int $order_id current order ID.
	 * @return void
	 */
	public function save_3ds_source_for_later( $order_id ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$order = wc_get_order( $order_id );

		if ( $order && $this->key === $order->get_payment_method() && wcf_pro()->flow->check_if_next_step_is_offer( $order ) ) {
			try {

				$payment_method_id = $order->get_meta( '_payment_method_id' );

				wcf()->logger->log( '_payment_method_id : ' . $payment_method_id );

				// Save the payment method (token) to the user's meta.
				$token = $this->gateway_service_objects['token_service']->add_payment_method_to_user( $payment_method_id, wp_get_current_user() );
				wcf()->logger->log( 'Our Token : ' . PHP_EOL . $token );

				// Add the token to the order for safety.
				$this->get_wc_gateway()->add_token_to_order( $order, $token );

				$order->update_meta_data( '_cartflows_' . $this->key . '_source_id', $payment_method_id );
				$order->update_meta_data( '_cartflows_' . $this->key . '_token', $token );

				// Clear the DB cache if any.
				$order->read_meta_data( true );

				wcf()->logger->log( '3DS source saved for later use' );

			} catch ( Exception $e ) {
				wcf()->logger->log( $order->get_id() . ' Unable to save token for 3ds.' . $e->getMessage() );
			}
		}

		wcf()->logger->log( 'Leaving: ' . __CLASS__ . '::' . __FUNCTION__ );
	}

	/**
	 * Render checkout ID hidden field.
	 *
	 * @param array $checkout checkout session data.
	 * @return void
	 */
	public function add_payment_gateway_option_hidden_field( $checkout ) {

		if ( ! _is_wcf_checkout_type() ) {
			return;
		}

		$available_gateways = array();

		foreach ( WC()->payment_gateways->get_available_payment_gateways() as $payment_gateway_id => $payment_gateway_item ) {
			array_push( $available_gateways, $payment_gateway_id );
		}

		$checkout_id = _get_wcf_checkout_id();

		$flow_id   = wcf()->utils->get_flow_id_from_step_id( $checkout_id );
		$is_upsell = wcf_pro()->flow->is_upsell_exist_in_flow( $flow_id, $checkout_id );

		if ( ( is_array( $available_gateways ) && ! in_array( $this->key, $available_gateways, true ) ) || ! $is_upsell ) {
			return;
		}

		echo '<input type="hidden" class="input-hidden wc-woocommerce_payments-new-payment-method" name="wc-woocommerce_payments-new-payment-method" value="true" />';
	}

	/**
	 * Check if token is present.
	 *
	 * @param object $order order data.
	 */
	public function has_token( $order ) {

		$order_id = $order->get_id();

		$token = $this->retrieve_token_from_order( $order, true );

		if ( empty( $token ) ) {
			$source_id = $order->get_meta( '_cartflows_' . $this->key . '_source_id' );
			$token     = $order->get_meta( '_cartflows_' . $this->key . '_token' );
		}

		if ( ! empty( $token ) ) {
			return true;
		}

		return false;
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

		$nonce = isset( $_POST['_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, $product['action'] ) ) {
			return $is_successful;
		}

		if ( ! $this->has_token( $order ) ) {
			return $is_successful;
		}

		if ( isset( $_POST['woop_intent_id'] ) ) {

			$stored_intent_id = $order->get_meta( '_wcf_woop_offer_stripe_intent_id_' . $product['step_id'] );
			$payment_method   = $order->get_meta( '_cartflows_' . $this->key . '_source_id' );
			$intent_id        = sanitize_text_field( wp_unslash( $_POST['woop_intent_id'] ) );

			wcf()->logger->log( 'Intent ID found: ' . $intent_id );
			wcf()->logger->log( 'Stored Intent ID found: ' . $stored_intent_id );

			$confirm_intent = ( $intent_id === $stored_intent_id ) ? true : false;

			if ( $confirm_intent ) {

				wcf()->logger->log( 'Intent ID confirmed' );

				$is_successful = true;
				$data          = array(
					'id'             => $intent_id,
					'payment_method' => $payment_method,
					'customer'       => $order->get_meta( '_stripe_customer_id', true ),
				);

				$this->store_offer_transaction( $order, $data, $product );
			} else {
				wcf()->logger->log( 'Intent ID not matched. Offer order failed ' );
			}
		}

		wcf()->logger->log( 'Ended: ' . __CLASS__ . '::' . __FUNCTION__ );

		return $is_successful;
	}

	/**
	 * Store Offer Trxn Charge.
	 *
	 * @param WC_Order $order    The order that is being paid for.
	 * @param Object   $response The response that is send from the payment gateway.
	 * @param array    $product  The product data.
	 */
	public function store_offer_transaction( $order, $response, $product ) {

		$order->update_meta_data( 'cartflows_offer_txn_resp_' . $product['step_id'], $response['id'] );
		$order->update_meta_data( '_cartflows_offer_txn_stripe_source_id_' . $product['step_id'], $response['payment_method'] );
		$order->update_meta_data( '_cartflows_offer_txn_stripe_customer_id_' . $product['step_id'], $response['customer'] );
		$order->save();
	}

	/**
	 * Create a new PaymentIntent against the new order.
	 *
	 * @param object $order                The order that is being paid for.
	 * @param array  $offer_product        Offer product.
	 * @param object $payment_information  The source that is used for the payment.
	 * @return object $intent               An intent or an error.
	 */
	public function process_payment_against_intent( $order, $offer_product, $payment_information ) {

		$f_name         = $order->get_billing_first_name();
		$l_name         = $order->get_billing_last_name();
		$email          = $order->get_billing_email();
		$customer_id    = $order->get_meta( '_stripe_customer_id' );
		$offer_amount   = WC_Payments_Utils::prepare_amount( $offer_product['price'], $order->get_currency() );
		$step_id        = $offer_product['step_id'];
		$payment_source = WC_Payments::get_gateway()->get_payment_method_ids_enabled_at_checkout( null, true );
		$product        = wc_get_product( $offer_product['id'] );

		$metadata = array(
			'customer_name'        => sanitize_text_field( $f_name ) . ' ' . sanitize_text_field( $l_name ),
			'customer_email'       => sanitize_email( $email ),
			'site_url'             => esc_url( get_site_url() ),
			'payment_type'         => $payment_information->get_payment_type(),
			'order_number'         => $order->get_order_number() . '_' . $offer_product['id'] . '_' . $step_id,

			'statement_descriptor' => apply_filters(
				'cartflows_' . $this->key . '_offer_statement_descriptor',
				substr(
					trim(
						/* translators: %1s order number */
						sprintf( __( 'Order %1$s-OTO', 'cartflows-pro' ), $order->get_order_number() )
					),
					0,
					22
				)
			),
		);

		wcf()->logger->log( 'Current Installed version : ' . WCPAY_VERSION_NUMBER );
		/**
		 * Use this in case to add the backward compatibility of the payment gateway.
		 * We don't need to add the backward compatibility as we are adding the support from scratch newly.
		 */
		if ( version_compare( '5.8.0', WCPAY_VERSION_NUMBER, '>=' ) ) {

			$request = WCPay\Core\Server\Request\Create_And_Confirm_Intention::create();
			$request->set_amount( WC_Payments_Utils::prepare_amount( $offer_amount, $order->get_currency() ) );
			$request->set_currency_code( strtolower( $order->get_currency() ) );
			$request->set_payment_method( $payment_information->get_payment_method() );
			$request->set_customer( $customer_id );
			$request->set_capture_method( $payment_information->is_using_manual_capture() );
			$request->set_metadata( $metadata );
			$request->set_level3( $this->get_level3_data_for_offer_product( $order, $offer_product ) );
			$request->set_off_session( $payment_information->is_merchant_initiated() );
			$request->set_payment_methods( $payment_source );
			$request->set_cvc_confirmation( $payment_information->get_cvc_confirmation() );
			// Make sure that setting fingerprint is performed after setting metadata because metadata will override any values you set before for metadata param.
			$request->set_fingerprint( $payment_information->get_fingerprint() );

			$is_subscription_product = $product->is_type( 'subscription' ) || $product->is_type( 'variable-subscription' ) || $product->is_type( 'subscription_variation' );


			if ( $is_subscription_product ) {
				$mandate = $order->get_meta( '_stripe_mandate_id', true );
				if ( $mandate ) {
					$request->set_mandate( $mandate );
				}
			}

			$intent = $request->send( 'wcpay_create_and_confirm_intent_request', $payment_information );

		} elseif ( version_compare( '3.9.0', WCPAY_VERSION_NUMBER, '<=' ) ) {
			$intent = $this->gateway_service_objects['api_client']->create_and_confirm_intention(
				WC_Payments_Utils::prepare_amount( $offer_amount, $order->get_currency() ),
				strtolower( $order->get_currency() ),
				$payment_source,
				$customer_id,
				$payment_information->is_using_manual_capture(),
				$payment_information->should_save_payment_method_to_store(),
				$payment_information->should_save_payment_method_to_platform(),
				$metadata
			);
		} else {
			$intent = $this->gateway_service_objects['api_client']->create_and_confirm_intention(
				WC_Payments_Utils::prepare_amount( $offer_amount, $order->get_currency() ),
				strtolower( $order->get_currency() ),
				$payment_source,
				$customer_id,
				$payment_information->is_using_manual_capture(),
				$payment_information->should_save_payment_method(),
				$metadata,
				array(),
				$payment_information->is_merchant_initiated()
			);
		}

		wcf()->logger->log( 'Intent APi response: ' . wp_json_encode( $intent ) );

		return $intent;
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

		wcf()->logger->log( 'Start- ' . __CLASS__ . '::' . __FUNCTION__ );

		$transaction_id = $offer_data['transaction_id'];
		$refund_amount  = $offer_data['refund_amount'];
		$order_currency = $order->get_currency( $order );

		$response_id = false;

		if ( ! is_null( $refund_amount ) && ! empty( $transaction_id ) ) {

			$intent    = $this->gateway_service_objects['api_client']->get_intent( $transaction_id );
			$charge_id = $intent->get_charge()->get_id();

			$response = $this->gateway_service_objects['api_client']->refund_charge(
				$charge_id,
				WC_Payments_Utils::prepare_amount( $refund_amount, $order_currency )
			);

			wcf()->logger->log( 'Refund response : ' . wp_json_encode( $response, true ) );

			if ( ! empty( $response->error ) || ! $response ) {
				$response_id = false;
			} else {

				$response_id = isset( $response->id ) ? $response->id : true;

				wcf()->logger->log( 'Refund response ID: ' . $response_id );

			}
		}

		wcf()->logger->log( 'Ended- ' . __CLASS__ . '::' . __FUNCTION__ );

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
	 * Setup the Payment data for Stripe's Automatic Subscription.
	 *
	 * @param WC_Subscription $subscription An instance of a subscription object.
	 * @param object          $order Object of order.
	 * @param array           $offer_product array of offer product.
	 */
	public function add_subscription_payment_meta( $subscription, $order, $offer_product ) {

		if ( $this->key === $order->get_payment_method() ) {

			$txn_id    = $order->get_meta( 'cartflows_offer_txn_resp_' . $offer_product['step_id'], true );
			$intent_id = $order->get_meta( '_wcf_woop_offer_stripe_intent_id_' . $offer_product['step_id'], true );

			$subscription->update_meta_data( '_payment_method_id', $txn_id );
			$subscription->update_meta_data( '_payment_tokens', $order->get_payment_tokens() );
			$subscription->update_meta_data( '_stripe_customer_id', $order->get_meta( '_stripe_customer_id', true ) );
			$subscription->update_meta_data( '_charge_id', $order->get_meta( '_wcf_woop_offer_charge_id_' . $offer_product['step_id'] . '_' . $order->get_id(), true ) );
			$subscription->update_meta_data( '_intent_id', $intent_id );

			$subscription->save_meta_data();
			$subscription->save();

		}
	}


	/**
	 * Save the parent payment meta to child order.
	 *
	 * @param object $parent_order Object of order.
	 * @param object $child_order Object of order.
	 * @param int    $transaction_id transaction id.
	 */
	public function add_required_meta_to_child_order( $parent_order, $child_order, $transaction_id ) {

		wcf()->logger->log( 'Start- ' . __CLASS__ . '::' . __FUNCTION__ );

		$offer_intent_id = array(
			'id' => $transaction_id,
		);

		// Get order/intent/payment data from the parent order object.
		$step_id           = $child_order->get_meta( '_cartflows_offer_step_id', true );
		$intent_id         = $parent_order->get_meta( '_wcf_woop_offer_stripe_intent_id_' . $step_id, true );
		$charge_id         = $parent_order->get_meta( '_wcf_woop_offer_charge_id_' . $step_id . '_' . $parent_order->get_id(), true );
		$payment_method_id = $parent_order->get_meta( '_wcf_woop_offer_payment_method_id', true );
		$payment_mode      = $parent_order->get_meta( '_wcf_woop_offer_wcpay_mode', true );
		$customer_id       = $parent_order->get_meta( '_cartflows_offer_txn_stripe_customer_id_' . $step_id );
		$intent            = $this->gateway_service_objects['api_client']->get_intent( $intent_id );
		$intent_status     = $intent->get_status();

		// Update order/intent/payment data child order object. This data is required for refund/subscription/cancellation of orders.
		$child_order->update_meta_data( '_wcf_woop_offer_stripe_intent_id_' . $step_id, $offer_intent_id );
		$child_order->set_transaction_id( $intent_id );
		$child_order->update_meta_data( '_intent_id', $intent_id );
		$child_order->update_meta_data( '_charge_id', $charge_id );
		$child_order->update_meta_data( '_intention_status', $intent_status );
		$child_order->update_meta_data( '_payment_method_id', $payment_method_id );
		$child_order->update_meta_data( '_stripe_customer_id', $customer_id );

		WC_Payments_Utils::set_order_intent_currency( $child_order, $intent->get_currency() );

		$child_order->save();

		wcf()->logger->log( 'Ended- ' . __CLASS__ . '::' . __FUNCTION__ );
	}

	/**
	 * Prepare offer data to sent in the payment request.
	 *
	 * @param object $order Current Order Object.
	 * @param array  $offer_product Selected offer Product Data.
	 *
	 * @return array $level3_data modified data for the payment request.
	 */
	public function get_level3_data_for_offer_product( $order, $offer_product ) {

		$level3_data = array(
			'merchant_reference' => (string) $order->get_id(), // An alphanumeric string of up to  characters in length. This unique value is assigned by the merchant to identify the order. Also known as an “Order ID”.
			'customer_reference' => (string) $order->get_id(),
			'shipping_amount'    => WC_Payments_Utils::prepare_amount( (float) $offer_product['shipping_fee_tax'], $order->get_currency() ), // The shipping cost, in cents, as a non-negative integer.
			'line_items'         => (object) array(
				'product_code'        => (string) substr( $offer_product['name'], 0, 12 ), // Up to 12 characters that uniquely identify the product.
				'product_description' => substr( $offer_product['desc'], 0, 26 ), // Up to 26 characters long describing the product.
				'unit_cost'           => WC_Payments_Utils::prepare_amount( $offer_product['unit_price'], $order->get_currency() ), // Cost of the product, in cents, as a non-negative integer.
				'quantity'            => $offer_product['qty'], // The number of items of this type sold, as a non-negative integer.
				'tax_amount'          => ! empty( $offer_product['unit_price_tax'] ) ? ( $offer_product['unit_price_tax'] - $offer_product['unit_price'] ) : 0, // The amount of tax this item had added to it, in cents, as a non-negative integer.
				'discount_amount'     => WC_Payments_Utils::prepare_amount( $offer_product['original_price'] - $offer_product['price'], $order->get_currency() ), // The amount an item was discounted—if there was a sale,for example, as a non-negative integer.
			),
		);

		return $level3_data;
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Gateway_Woocommerce_Payments' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateway_Woocommerce_Payments::get_instance();
