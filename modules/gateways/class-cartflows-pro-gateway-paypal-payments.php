<?php
/**
 * Paypal Gateway.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Gateway_Paypal_Payments.
 */
class Cartflows_Pro_Gateway_Paypal_Payments extends Cartflows_Pro_Paypal_Gateway_helper {

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
	public $key = 'ppcp-gateway';

	/**
	 * Refund supported
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

		define( 'CARTFLOWS_PAYPAL_PAYMENTS_API_URL', 'https://api-m.paypal.com' );
		define( 'CARTFLOWS_PAYPAL_PAYMENTS_SANDBOX_API_URL', 'https://api-m.sandbox.paypal.com' );

		add_filter( 'woocommerce_paypal_refund_request', array( $this, 'offer_refund_request_data' ), 10, 4 );

		add_action( 'cartflows_offer_subscription_created', array( $this, 'add_subscription_payment_meta_for_paypal' ), 10, 3 );

		add_action( 'cartflows_offer_child_order_created_' . $this->key, array( $this, 'store_required_meta_keys_for_refund' ), 10, 3 );

		add_action( 'wp_ajax_wcf_create_paypal_payments_order', array( $this, 'create_paypal_order' ) );
		add_action( 'wp_ajax_nopriv_wcf_create_paypal_payments_order', array( $this, 'create_paypal_order' ) );

		add_action( 'wp_ajax_wcf_capture_paypal_order', array( $this, 'capture_paypal_order' ) );
		add_action( 'wp_ajax_nopriv_wcf_capture_paypal_order', array( $this, 'capture_paypal_order' ) );
	}

	/**
	 * Retrieves token for payment.
	 *
	 * @param object $order order details.
	 *
	 * @return string
	 */
	public function get_token( $order ) {

		$token = '';

		$bearer = get_option( '_transient_ppcp-paypal-bearerppcp-bearer' );

		if ( ! empty( $bearer ) ) {
			$bearer = json_decode( $bearer );
			$token  = $bearer->access_token;
		}

		// Generate new token if token does not exists.
		if ( empty( $token ) ) {
			$payment_env   = $order->get_meta( '_ppcp_paypal_payment_mode' );
			$ppcp_settings = get_option( 'woocommerce-ppcp-settings' );
			$client_id     = $ppcp_settings['client_id'];
			$secret_key    = $ppcp_settings['client_secret'];
			$api_url_base  = 'sandbox' === $payment_env ? CARTFLOWS_PAYPAL_PAYMENTS_SANDBOX_API_URL : CARTFLOWS_PAYPAL_PAYMENTS_API_URL;
			$url           = $api_url_base . '/v1/oauth2/token?grant_type=client_credentials';
			$args          = array(
				'method'  => 'POST',
				'headers' => array(
					// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $secret_key ),
				),
			);

			$response = wp_remote_get( $url, $args );

			if ( ! is_wp_error( $response ) ) {
				$res_body = json_decode( $response['body'] );
				$token    = $res_body->access_token;
			}
		}

		return $token;
	}

	/**
	 * Checks the paypal payment mode.
	 *
	 * @param object $order WC Order.
	 *
	 * @return string Payment mode.
	 */
	public function get_ppcp_meta( $order ) {

		$paypal_settings = get_option( 'woocommerce-ppcp-settings' );

		$gateway_environment = $order->get_meta( '_ppcp_paypal_payment_mode' ) ? $order->get_meta( '_ppcp_paypal_payment_mode' ) : '';

		return array(
			'environment'    => $order->get_meta( '_ppcp_paypal_payment_mode' ),
			'api_base_url'   => 'sandbox' === $gateway_environment ? CARTFLOWS_PAYPAL_PAYMENTS_SANDBOX_API_URL : CARTFLOWS_PAYPAL_PAYMENTS_API_URL,
			'intent'         => $order->get_meta( '_ppcp_paypal_intent' ),
			'merchant_email' => $paypal_settings['merchant_email'],
			'merchant_id'    => $paypal_settings['merchant_id'],
			'invoice_prefix' => $paypal_settings['prefix'],
		);
	}

	/**
	 * After payment process.
	 *
	 * @param object $order order data.
	 * @param array  $product product data.
	 * @return bool
	 */
	public function process_offer_payment( $order, $product ) {

		$is_successful = false;
		$txn_id        = '';
		$txn_id        = $order->get_meta( 'cartflows_offer_paypal_txn_id_' . $order->get_id() );

		if ( empty( $txn_id ) ) {
			wcf()->logger->log( 'PayPal order captured but no txn ID found, so order is failed.' );
			$is_successful = false;
		} else {
			$is_successful = true;

			$response = array(
				'id' => $txn_id,
			);

			$this->store_offer_transaction( $order, $response, $product );
		}

		return $is_successful;
	}

	/**
	 * Capture PayPal Order for PayPal Payments Gateway.
	 *
	 * @return json
	 */
	public function create_paypal_order() {

		$nonce = isset( $_POST['_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'wcf_create_paypal_order' ) ) {
			return;
		}

		$data        = array();
		$step_id     = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
		$flow_id     = isset( $_POST['flow_id'] ) ? intval( $_POST['flow_id'] ) : 0;
		$order_id    = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : 0;
		$order_key   = isset( $_POST['order_key'] ) ? sanitize_text_field( wp_unslash( $_POST['order_key'] ) ) : '';
		$session_key = isset( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) ) : ''; //phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE

		$order        = wc_get_order( $order_id );
		$variation_id = '';
		$input_qty    = '';
		$invoice_id   = '';

		$args = array(
			'step_id'        => $step_id,
			'flow_id'        => $flow_id,
			'order_id'       => $order_id,
			'order_key'      => $order_key,
			'order_currency' => wcf_pro()->wc_common->get_currency( $order ),
			'ppcp_data'      => $this->get_ppcp_meta( $order ),
		);

		$token = $this->get_token( $order );

		if ( isset( $_POST['variation_id'] ) && ! empty( $_POST['variation_id'] ) ) {
			$variation_id = intval( $_POST['variation_id'] );
		}
		if ( isset( $_POST['input_qty'] ) && ! empty( $_POST['input_qty'] ) ) {
			$input_qty = intval( $_POST['input_qty'] );
		}

		$offer_product = wcf_pro()->utils->get_offer_data( $step_id, $variation_id, $input_qty, $order_id );

		if ( isset( $offer_product['price'] ) && ( floatval( 0 ) === floatval( $offer_product['price'] )
				|| '' === trim( $offer_product['price'] ) ) ) {

			wcf()->logger->log(
				"Cannot create PayPal Payments Order. The selected product's price is zero. Order: {$order_id}"
			);

			wp_send_json(
				array(
					'result'  => 'fail',
					'message' => __( 'Cannot make the Payment for Zero value product', 'cartflows-pro' ),
				)
			);
		} else {

			$data = array(
				'intent'              => $args['ppcp_data']['intent'],
				'purchase_units'      => $this->get_purchase_units( $order, $offer_product, $args ),
				'application_context' => array(
					'user_action'  => 'CONTINUE',
					'landing_page' => 'LOGIN',
					'brand_name'   => html_entity_decode( get_bloginfo( 'name' ), ENT_NOQUOTES, 'UTF-8' ),
					'return_url'   => $this->get_return_or_cancel_url( $args, $session_key ),
					'cancel_url'   => $this->get_return_or_cancel_url( $args, $session_key, true ),
				),
				'payment_method'      => array(
					'payee_preferred' => 'UNRESTRICTED',
					'payer_selected'  => 'PAYPAL',
				),
				'payment_instruction' => array(
					'disbursement_mode' => 'INSTANT',
					'platform_fees'     => array(
						array(
							'amount' => array(
								'currency_code' => $order->get_currency(),
								'value'         => wcf_float_to_string( $offer_product['unit_price_tax'] ),
							),
						),
					),
				),

			);

			$arguments = array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type'                  => 'application/json',
					'Authorization'                 => 'Bearer ' . $token,
					'PayPal-Partner-Attribution-Id' => 'Wcf_Woo_PPCP',
				),
				'body'    => wp_json_encode( $data ),
			);

			// Refer https://developer.paypal.com/docs/api/orders/v2/ documentation to generate create order endpoint.
			$url = $args['ppcp_data']['api_base_url'] . '/v2/checkout/orders';

			$ppcp_resp = wp_remote_get( $url, $arguments );

			if ( is_wp_error( $ppcp_resp ) ) {

				$json_response = array(
					'status'          => false,
					'message'         => $ppcp_resp->get_error_message(),
					'paypal_order_id' => '',
					'redirect_url'    => '',
					'response'        => $ppcp_resp,
				);

				wcf()->logger->log(
					"PayPal order is not created. Order: {$order_id}, Error: " .
					wp_json_encode( $ppcp_resp->get_error_message() )
				);

				return wp_send_json( $json_response );

			} else {

				$retrived_body = wp_remote_retrieve_body( $ppcp_resp );

				$response = json_decode( $retrived_body );

				$json_response = array(
					'result'          => false,
					'message'         => __( 'PayPal order is not created', 'cartflows-pro' ),
					'paypal_order_id' => '',
					'redirect_url'    => '',
					'response'        => $response,
				);

				if ( isset( $response->status ) && 'CREATED' === $response->status ) {

					$approve_link = $response->links[1]->href;

					// Update Order Creared ID (PayPal Order ID) in the order.
					$order->update_meta_data( 'cartflows_paypal_order_id_' . $order->get_id(), $response->id );
					$order->save();

					wcf()->logger->log(
						"Order Created for WC-Order: {$order_id}"
					);

					$json_response = array(
						'status'          => 'success',
						'message'         => __( 'Order created successfully', 'cartflows-pro' ),
						'paypal_order_id' => $response->id,
						'redirect'        => $approve_link,
						'response'        => $response,
					);

				}
			}

			return wp_send_json( $json_response );
		}
	}

	/**
	 * Capture PayPal Order for PayPal Payments Gateway.
	 *
	 * @return json
	 */
	public function capture_paypal_order() {

		$nonce = isset( $_POST['_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'wcf_capture_paypal_order' ) ) {
			return;
		}

		$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : 0;

		$order = wc_get_order( $order_id );

		$token           = $this->get_token( $order );
		$paypal_order_id = $order->get_meta( 'cartflows_paypal_order_id_' . $order->get_id() );
		$api_base_url    = 'sandbox' === $order->get_meta( '_ppcp_paypal_payment_mode' ) ? CARTFLOWS_PAYPAL_PAYMENTS_SANDBOX_API_URL : CARTFLOWS_PAYPAL_PAYMENTS_API_URL;

		$capture_args = array(
			'method'  => 'POST',
			'headers' => array(
				'Authorization'                 => 'Bearer ' . $token,
				'Content-Type'                  => 'application/json',
				'Prefer'                        => 'return=representation',
				'PayPal-Partner-Attribution-Id' => 'Wcf_Woo_PPCP',
			),
		);

		$capture_url   = $api_base_url . '/v2/checkout/orders/' . $paypal_order_id . '/capture';
		$captured_resp = wp_remote_get( $capture_url, $capture_args );

		if ( is_wp_error( $captured_resp ) ) {

			$json_response = array(
				'status'          => false,
				'message'         => $captured_resp->get_error_message(),
				'paypal_order_id' => '',
				'redirect_url'    => '',
				'response'        => $captured_resp,
			);

			wcf()->logger->log(
				"Order Created but not captured. For WC-Order: {$order_id}, Error: " .
				wp_json_encode( $captured_resp->get_error_message() )
			);

		} else {

			$retrived_body = wp_remote_retrieve_body( $captured_resp );

			$resp_body = json_decode( $retrived_body );

			$json_response = array(
				'result'          => false,
				'message'         => __( 'PayPal order is not created', 'cartflows-pro' ),
				'paypal_order_id' => '',
				'redirect_url'    => '',
				'response'        => $resp_body,
			);

			if ( 'COMPLETED' === $resp_body->status ) {
				$txn_id = $resp_body->purchase_units[0]->payments->captures[0]->id;

				// Update Order Captured Txn ID (PayPal Txn ID) in the order.
				$order->update_meta_data( 'cartflows_offer_paypal_txn_id_' . $order->get_id(), $txn_id );
				$order->save();

				wcf()->logger->log(
					"Order Created and captured. Order: {$order_id}"
				);

				$json_response = array(
					'status'          => 'success',
					'message'         => __( 'Order Captured successfully', 'cartflows-pro' ),
					'paypal_order_id' => $resp_body->id,
					'response'        => $resp_body,
				);

			}
		}

		return wp_send_json( $json_response );
	}

	/**
	 * Create purchase unite for create order.
	 *
	 * @param object $order WC Order.
	 * @param array  $offer_product upsell/downsell product.
	 * @param object $args Posted and payment gateway setting data.
	 *
	 * @return array $purchase_unit.
	 */
	public function get_purchase_units( $order, $offer_product, $args ) {

		$invoice_id = $args['ppcp_data']['invoice_prefix'] . '-wcf-' . $args['order_id'] . '_' . $args['step_id'];

		$purchase_unit = array(
			'reference_id' => 'default',
			'amount'       => array(
				'currency_code' => $args['order_currency'],
				'value'         => wcf_float_to_string( $offer_product['total'] ),
				'breakdown'     => $this->get_item_breakdown( $order, $offer_product ),
			),
			/* translators: %s order id. */
			'description'  => sprintf( __( 'One Time Offer - %s', 'cartflows-pro' ), $order->get_id() ),
			'items'        => array(
				$this->add_offer_item_data( $order, $offer_product ),
			),
			'payee'        => array(
				'email_address' => $args['ppcp_data']['merchant_email'],
				'merchant_id'   => $args['ppcp_data']['merchant_id'],
			),
			'shipping'     => array(
				'name' => array(
					'full_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				),
			),
			'custom_id'    => $invoice_id,
			'invoice_id'   => $invoice_id,
		);

		return array( $purchase_unit );
	}

	/**
	 * Create breakdown for item amount.
	 *
	 * @param object $order WC Order.
	 * @param array  $offer_product upsell/downsell product.
	 *
	 * @return array $breakdown item amount breakdown.
	 */
	public function get_item_breakdown( $order, $offer_product ) {

		$breakdown = array();

		$breakdown['item_total'] = array(
			'currency_code' => wcf_pro()->wc_common->get_currency( $order ),
			'value'         => wcf_float_to_string( $offer_product['total'] ),
		);

		if ( ! empty( $offer_product['amount_diff'] ) ) {
			$breakdown['discount'] = array(
				'currency_code' => wcf_pro()->wc_common->get_currency( $order ),
				'value'         => wcf_float_to_string( $offer_product['amount_diff'] ),
			);
		}

		if ( ! empty( $offer_product['shipping_fee'] ) ) {
			$breakdown['shipping'] = array(
				'currency_code' => wcf_pro()->wc_common->get_currency( $order ),
				'value'         => wcf_float_to_string( $offer_product['shipping_fee_tax'] ),
			);
		}

		return $breakdown;
	}

	/**
	 * Add product's item data.
	 *
	 * @param object $order WC Order.
	 * @param array  $offer_product upsell/downsell product.
	 *
	 * @return array $offer_items item data.
	 */
	public function add_offer_item_data( $order, $offer_product ) {

		$product_desc = wp_strip_all_tags( $offer_product['desc'] );

		// Truncate the description to match the max allowed length by PayPal that is of 127 chars.
		$product_desc = ( strlen( $product_desc ) > 125 ) ? substr( $product_desc, 0, 125 ) . '..' : $product_desc;

		$offer_items = array(
			'name'        => $offer_product['name'],
			'unit_amount' => array(
				'currency_code' => wcf_pro()->wc_common->get_currency( $order ),
				'value'         => wcf_float_to_string( $offer_product['total'] ),
			),
			'quantity'    => $offer_product['qty'],
			'description' => $product_desc,
		);

		return $offer_items;

	}

	/**
	 * Get return url.
	 *
	 * @param array  $args required arguments.
	 * @param string $session_key session key.
	 * @param bool   $cancel key for action.
	 *
	 * @return string
	 */
	public function get_return_or_cancel_url( $args, $session_key, $cancel = false ) {

		$url = get_permalink( $args['step_id'] );

		$args = array(
			'wcf-order' => $args['order_id'],
			'wcf-key'   => $args['order_key'],
			'wcf-sk'    => $session_key,
		);

		if ( $cancel ) {
			$args['wcf-ppcp-cancel'] = true;
		} else {
			$args['wcf-ppcp-return'] = true;
		}

		return add_query_arg( $args, $url );
	}

	/**
	 * Is gateway support offer refund
	 *
	 * @return bool
	 */
	public function is_api_refund() {

		return $this->is_api_refund;
	}

	/**
	 * Store Offer Trxn Charge.
	 *
	 * @param WC_Order $order    The order that is being paid for.
	 * @param Object   $response The response that is send from the payment gateway.
	 * @param array    $product  Product data.
	 */
	public function store_offer_transaction( $order, $response, $product ) {

		wcf()->logger->log( 'PayPal Payments : Store Offer Transaction :: Transaction ID = ' . $response['id'] . ' Captured' );

		$order->update_meta_data( 'cartflows_offer_txn_resp_' . $product['step_id'], $response['id'] );
		$order->save();
	}

	/**
	 * Modify argument for offer refund
	 *
	 * @param array  $request request.
	 * @param object $order the order object.
	 * @param string $amount refund amount.
	 * @param string $reason refund reason.
	 *
	 * @return object
	 */
	public function offer_refund_request_data( $request, $order, $amount, $reason ) {
		//phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['cartflows_refund'] ) ) {

			$payment_method = $order->get_payment_method();

			if ( $this->key === $payment_method ) {

				if ( isset( $_POST['transaction_id'] ) && ! empty( $_POST['transaction_id'] ) ) {
					$request['TRANSACTIONID'] = sanitize_text_field( wp_unslash( $_POST['transaction_id'] ) );
				}
			}
		}

		return $request;
		//phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Process offer refund.
	 *
	 * @param WC_Order $order order data.
	 * @param array    $offer_data offer data.
	 *
	 * @return bool
	 */
	public function process_offer_refund( $order, $offer_data ) {

		$order_id       = $offer_data['order_id'];
		$transaction_id = $offer_data['transaction_id'];
		$refund_amount  = $offer_data['refund_amount'];
		$refund_reason  = $offer_data['refund_reason'];

		$response = false;

		$gateway = $this->get_wc_gateway();

		if ( $this->is_api_refund ) {
			$result = $gateway->process_refund( $order->get_id(), $refund_amount, $refund_reason );

			if ( is_wp_error( $result ) ) {
				wcf()->logger->log( "Paypal offer refund failed. Order: {$order_id}, Error: " . wcf_print_r( $result->get_error_message(), true ) );
			} elseif ( $result ) {
				$response = $result;
			}
		}

		return $response;
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
	 * Setup the Payment data for Paypal Automatic Subscription.
	 *
	 * @param WC_Subscription $subscription An instance of a subscription object.
	 * @param object          $order Object of order.
	 * @param array           $offer_product array of offer product.
	 */
	public function add_subscription_payment_meta_for_paypal( $subscription, $order, $offer_product ) {
		if ( 'ppcp-gateway' === $order->get_payment_method() ) {

			$subscription->update_meta_data( '_ppcp_paypal_order_id', $order->get_meta( '_ppcp_paypal_order_id', true ) );
			$subscription->update_meta_data( 'payment_token_id', $order->get_meta( 'payment_token_id', true ) );
			$subscription->save();
		}
	}

	/**
	 * Save required keys in child order for refund purpose.
	 *
	 * @param object $parent_order parent order object.
	 * @param object $child_order child order object.
	 * @param string $transaction_id child order trandaction id.
	 */
	public function store_required_meta_keys_for_refund( $parent_order, $child_order, $transaction_id ) {
		if ( ! empty( $transaction_id ) ) {

			$paypal_order_id = $parent_order->get_meta( 'cartflows_paypal_order_id_' . $parent_order->get_id() );

			$child_order->update_meta_data( '_ppcp_paypal_order_id', $paypal_order_id );
			$child_order->update_meta_data( '_ppcp_paypal_intent', 'CAPTURE' );
			$child_order->save();
		}
	}

}

/**
 *  Prepare if class 'Cartflows_Pro_Gateway_Paypal_Payments' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateway_Paypal_Payments::get_instance();
