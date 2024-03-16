<?php
/**
 * Stripe Gateway.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Gateway_Stripe.
 */
class Cartflows_Pro_Gateway_Stripe {

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
	public $key = 'stripe';

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

		add_filter( 'wc_stripe_force_save_source', array( $this, 'tokenize_if_required' ) );

		add_filter( 'wc_stripe_3ds_source', array( $this, 'save_3ds_source_for_later' ), 10, 2 );

		add_action( 'wc_gateway_stripe_process_response', array( $this, 'redirect_using_wc_function' ), 10, 2 );

		add_action( 'wp_ajax_wcf_stripe_sca_check', array( $this, 'check_stripe_sca' ) );
		add_action( 'wp_ajax_nopriv_wcf_stripe_sca_check', array( $this, 'check_stripe_sca' ) );

		add_action( 'cartflows_offer_subscription_created', array( $this, 'add_subscription_payment_meta' ), 10, 3 );

		add_action( 'cartflows_offer_before_main_order_cancel', array( $this, 'remove_the_stripe_refund_action' ), 10, 1 );

		add_action( 'cartflows_offer_child_order_created_' . $this->key, array( $this, 'add_required_meta_to_child_order' ), 10, 3 );

	}

	/**
	 * Verify 3DS and create intent accordingly.
	 *
	 * @return bool
	 */
	public function check_stripe_sca() {

		$nonce = isset( $_POST['_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'wcf_stripe_sca_check' ) ) {
			return;
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
		$offer_type    = isset( $_POST['offer_type'] ) ? sanitize_text_field( wp_unslash( $_POST['offer_type'] ) ) : '';
		$offer_action  = isset( $_POST['offer_action'] ) ? sanitize_text_field( wp_unslash( $_POST['offer_action'] ) ) : '';
		$order         = wc_get_order( $order_id );
		$offer_product = wcf_pro()->utils->get_offer_data( $step_id, $variation_id, $input_qty, $order_id );

		if ( isset( $offer_product['price'] ) && ( floatval( 0 ) >= floatval( $offer_product['price'] )
				|| '' === trim( $offer_product['price'] ) ) ) {
			wp_send_json(
				array(
					'result'  => 'fail',
					'message' => '0 value product',
				)
			);
		} else {

			$gateway = $this->get_wc_gateway();

			if ( $gateway ) {

				$order_source = $gateway->prepare_order_source( $order );
				$is_3ds       = isset( $order_source->source_object->card->three_d_secure ) ? $order_source->source_object->card->three_d_secure : false;
				// Check if 3DS required.
				if ( isset( $is_3ds ) && 'optional' !== $is_3ds && 'yes' === $offer_action ) {

					$intent = $this->create_intent( $order, $order_source, $offer_product );

					if ( ! empty( $intent->error ) ) {
						wp_send_json(
							array(
								'result'  => 'fail',
								'message' => $intent->error,
							)
						);
					}

					if ( empty( $intent->error ) ) {
						// Confirm the intent.
						$intent = $this->confirm_payment_intent( $intent, $order, $order_source, $offer_product );
					}

					// Process as there is no need to confirm the intent as it is not 3Ds enabled.
					$main_settings = get_option( 'woocommerce_stripe_settings' );
					$testmode      = ( ! empty( $main_settings['testmode'] ) && 'yes' === $main_settings['testmode'] ) ? true : false;

					if ( $testmode ) {
						$publishable_key = ! empty( $main_settings['test_publishable_key'] ) ? $main_settings['test_publishable_key'] : '';
					} else {
						$publishable_key = ! empty( $main_settings['publishable_key'] ) ? $main_settings['publishable_key'] : '';
					}

					/**
					 * Proceed and check intent status
					 */
					if ( ! empty( $intent ) ) {

						// Save the intent ID to the order.
						if ( $order ) {
							$step_id = filter_input( INPUT_POST, 'step_id', FILTER_VALIDATE_INT );
							$order->update_meta_data( '_stripe_intent_id_' . $step_id, $intent->id );
							$order->save();
						}

						// If the intent requires a 3DS flow, redirect to it.
						if ( 'requires_action' === $intent->status ) {

							/**
							 * Return the intent_secret so that it can be authenticated by the client.
							 */
							wp_send_json(
								array(
									'result'        => 'success',
									'intent_secret' => $intent->client_secret,
									'stripe_pk'     => $publishable_key,
								)
							);

						}

						// Process the order as the intent does not needs any manual 3ds authentication.
						wp_send_json(
							array(
								'result'        => 'success',
								'redirect'      => $gateway->get_return_url( $order ),
								'intent_secret' => $intent->client_secret,
								'stripe_pk'     => $publishable_key,
							)
						);
					}
				}
			}

			wp_send_json(
				array(
					'result'  => 'fail',
					'message' => 'No 3ds payment',
				)
			);
		}
	}

	/**
	 * Tokenize to save source of payment if required
	 *
	 * @param bool $save_source force save source.
	 */
	public function tokenize_if_required( $save_source ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
		$flow_id     = wcf()->utils->get_flow_id_from_post_data();

		if ( $checkout_id && $flow_id && wcf_pro()->flow->is_upsell_exist_in_flow( $flow_id, $checkout_id ) ) {
			$save_source = true;
			wcf()->logger->log( 'Force save source enabled' );
		}

		return $save_source;
	}

	/**
	 * Save 3d source.
	 *
	 * @param array  $post_data Threads data.
	 * @param object $order order data.
	 */
	public function save_3ds_source_for_later( $post_data, $order ) {

		if ( $order && wcf_pro()->flow->check_if_next_step_is_offer( $order ) ) {

			$order->update_meta_data( '_cartflows_stripe_source_id', $post_data['three_d_secure']['card'] );

			$order->save();

			wcf()->logger->log( '3ds source saved for later use' );
		}

		return $post_data;
	}

	/**
	 * Redirection to order received URL.
	 *
	 * @param array  $response response data.
	 * @param object $order order data.
	 */
	public function redirect_using_wc_function( $response, $order ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		if ( 1 === did_action( 'cartflows_order_started' ) && 1 === did_action( 'wc_gateway_stripe_process_redirect_payment' ) ) {

			$get_url = $order->get_checkout_order_received_url();
			wp_safe_redirect( $get_url );
			exit();
		}
	}

	/**
	 * Check if token is present.
	 *
	 * @param object $order order data.
	 */
	public function has_token( $order ) {

		$token = $order->get_meta( '_cartflows_stripe_source_id' );

		if ( empty( $token ) ) {
			$token = $order->get_meta( '_stripe_source_id' );
		}

		if ( ! empty( $token ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get WooCommerce payment getaways.
	 *
	 * @return object Requested Payment Gateway Object.
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

		$is_successful = false;


		$nonce = isset( $_POST['_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, $product['action'] ) ) {
			return $is_successful;
		}

		if ( ! $this->has_token( $order ) ) {
			return $is_successful;
		}

		// Verification of 3DS.
		$skip_payment = filter_input( INPUT_POST, 'stripe_sca_payment', FILTER_VALIDATE_BOOLEAN );

		if ( $skip_payment ) {
			$_stripe_intent_id    = $order->get_meta( '_stripe_intent_id_' . $product['step_id'] );
			$intent_id            = isset( $_POST['stripe_intent_id'] ) ? sanitize_text_field( wp_unslash( $_POST['stripe_intent_id'] ) ) : '';
			$confirm_skip_payment = ( $intent_id === $_stripe_intent_id ) ? true : false;

			if ( $confirm_skip_payment ) {

				$is_successful = true;
				// Retrive Charge ID for current product purchase.
				$charge_response = WC_Stripe_API::retrieve( 'charges?payment_intent=' . $intent_id );
				$charge_data     = reset( $charge_response->data );

				$this->update_stripe_payout_details( $order, $charge_data );
				$this->store_offer_transaction( $order, $charge_data, $product );
			}
		} else {

			try {

				$gateway = $this->get_wc_gateway();

				$order_source = $gateway->prepare_order_source( $order );

				$response = WC_Stripe_API::request( $this->generate_payment_request( $order, $order_source, $product ) );

				if ( ! is_wp_error( $response ) ) {

					if ( ! empty( $response->error ) ) {

						wcf()->logger->log( '\n ==== Product Start ==== \n' . wcf_print_r( $product, true ) . '==== Product End ==== \n ==== Error Response start ==== \n' . wcf_print_r( $response, true ) . '==== Error Response end ====\n' );

						$is_successful = false;
					} else {

						/** '_transaction_id', $response->id */
						$is_successful = true;

						$this->update_stripe_payout_details( $order, $response );
						$this->store_offer_transaction( $order, $response, $product );
					}
				}
				// @todo Show actual error if any.
			} catch ( Exception $e ) {
				// @todo Exception catch to show actual error.
				$is_successful = false;
			}
		}

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

		$order->update_meta_data( 'cartflows_offer_txn_resp_' . $product['step_id'], $response->id );
		$order->save();
	}

	/**
	 * Update Stripe Payout for the Offers.
	 *
	 * @param WC_Order $order           The order that is being paid for.
	 * @param object   $response        The response that is send from the payment gateway.
	 */
	public function update_stripe_payout_details( $order, $response ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$fee = ! empty( $response->balance_transaction->fee ) ? WC_Stripe_Helper::format_balance_fee( $response->balance_transaction, 'fee' ) : 0;
		$net = ! empty( $response->balance_transaction->net ) ? WC_Stripe_Helper::format_balance_fee( $response->balance_transaction, 'net' ) : 0;

		$fee = $fee + floatval( WC_Stripe_Helper::get_stripe_fee( $order ) );
		$net = $net + floatval( WC_Stripe_Helper::get_stripe_net( $order ) );

		WC_Stripe_Helper::update_stripe_fee( $order, $fee );
		WC_Stripe_Helper::update_stripe_net( $order, $net );

		wcf()->logger->log( 'Stripe Payout updated.' );

		wcf()->logger->log( 'Ended: ' . __CLASS__ . '::' . __FUNCTION__ );
	}

	/**
	 * Create a new PaymentIntent.
	 *
	 * @param WC_Order $order           The order that is being paid for.
	 * @param object   $prepared_source The source that is used for the payment.
	 * @param object   $product offer product.
	 * @return object                   An intent or an error.
	 */
	public function create_intent( $order, $prepared_source, $product ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		// The request for a charge contains metadata for the intent.
		$full_request = $this->generate_payment_request( $order, $prepared_source, $product );

		$request = array(
			'amount'                      => WC_Stripe_Helper::get_stripe_amount( $product['price'] ),
			'currency'                    => strtolower( $order->get_currency() ),
			'description'                 => $full_request['description'],
			'metadata'                    => $full_request['metadata'],
			'statement_descriptor_suffix' => WC_Stripe_Helper::clean_statement_descriptor( $full_request['statement_descriptor'] ),
			'capture_method'              => ( 'true' === $full_request['capture'] ) ? 'automatic' : 'manual',
			'payment_method_types'        => array(
				'card',
			),
			'customer'                    => $prepared_source->customer,
		);

		$request = WC_Stripe_Helper::add_payment_method_to_request_array( $prepared_source->source, $request );

		if ( $prepared_source->customer ) {
			$request['customer'] = $prepared_source->customer;
		}

		wcf()->logger->log( 'Create Intent request : ' . wcf_print_r( $request, true ) );

		// Create an intent that awaits an action.
		$intent = WC_Stripe_API::request( $request, 'payment_intents' );

		if ( ! empty( $intent->error ) ) {

			wcf()->logger->log( 'Create Intent request Error: ' . wcf_print_r( $intent, true ) );

			return $intent;
		}

		wcf()->logger->log( 'Ended: ' . __CLASS__ . '::' . __FUNCTION__ );

		return $intent;
	}

	/**
	 * Confirm the payment intent for the upsell offer.
	 *
	 * This check is been added and made compulsory by the WooCommerce Stripe.
	 * As they have changed the method from Source API to Payment Methods API.
	 *
	 * @param object $intent The offer payment intent object.
	 * @param object $order The main/parent order object.
	 * @param object $prepared_source The payment source used in the main/primary order.
	 * @param array  $offer_product The product data selected on the offer pages.
	 *
	 * @return object $confirmed_intent The payment intent which is now, confirmed for use.
	 *
	 * @since x.x.x
	 */
	public function confirm_payment_intent( $intent, $order, $prepared_source, $offer_product ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		wcf()->logger->log( 'Intent Status : ' . $intent->status );

		if ( 'requires_confirmation' !== $intent->status ) {
			return $intent;
		}

		$confirmed_intent = '';

		// Confirm the intent & capture the payment charges.
		$confirm_intent_request = WC_Stripe_Helper::add_payment_method_to_request_array( $prepared_source->source, array() );

		$offer_product_data = array(
			'merchant_reference' => (string) $order->get_order_number() . '_' . $offer_product['id'] . '_' . $offer_product['step_id'],
			'shipping_amount'    => WC_Stripe_Helper::get_stripe_amount( (float) $offer_product['shipping_fee_tax'], $order->get_currency() ),
			'line_items'         => (object) array(
				'product_code'        => (string) substr( $offer_product['name'], 0, 12 ),
				'product_description' => substr( $offer_product['desc'], 0, 26 ),
				'unit_cost'           => WC_Stripe_Helper::get_stripe_amount( $offer_product['unit_price'], $order->get_currency() ),
				'quantity'            => $offer_product['qty'],
				'tax_amount'          => ! empty( $offer_product['unit_price_tax'] ) ? ( $offer_product['unit_price_tax'] - $offer_product['unit_price'] ) : 0,
				'discount_amount'     => WC_Stripe_Helper::get_stripe_amount( $offer_product['original_price'] - $offer_product['price'], $order->get_currency() ),
			),
		);

		wcf()->logger->log( 'Confirm Intent Request : ' . wcf_print_r( $confirm_intent_request, true ) );

		$confirmed_intent = WC_Stripe_API::request_with_level3_data(
			$confirm_intent_request,
			"payment_intents/$intent->id/confirm",
			$offer_product_data,
			$order
		);

		if ( ! empty( $confirmed_intent->error ) ) {
			wcf()->logger->log( 'Confirm Intent Request Error : ' . wcf_print_r( $confirmed_intent, true ) );
			return $confirmed_intent;
		}

		wcf()->logger->log( 'Confirmed Intent Status : ' . $confirmed_intent->status . ' For Order ID : ' . $order->get_id() );

		wcf()->logger->log( 'Ended: ' . __CLASS__ . '::' . __FUNCTION__ );

		return $confirmed_intent;

	}

	/**
	 * Generate payment request.
	 *
	 * @param object $order order data.
	 * @param object $order_source order source.
	 * @param array  $product product data.
	 * @return array
	 */
	protected function generate_payment_request( $order, $order_source, $product ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$gateway               = $this->get_wc_gateway();
		$post_data             = array();
		$post_data['currency'] = strtolower( $order ? $order->get_currency() : get_woocommerce_currency() );
		$post_data['amount']   = WC_Stripe_Helper::get_stripe_amount( $product['price'], $post_data['currency'] );
		/* translators: %1s site name */
		$post_data['description'] = sprintf( __( '%1$s - Order %2$s - One Time offer', 'cartflows-pro' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $order->get_order_number() );

		/* translators: %1s order number */
		$post_data['statement_descriptor'] = sprintf( __( 'Order %1$s-OTO', 'cartflows-pro' ), $order->get_order_number() );

		$post_data['capture'] = $gateway->capture ? 'true' : 'false';
		$billing_first_name   = $order->get_billing_first_name();
		$billing_last_name    = $order->get_billing_last_name();
		$billing_email        = $order->get_billing_email();

		if ( ! empty( $billing_email ) && apply_filters( 'wc_stripe_send_stripe_receipt', false ) ) {
			$post_data['receipt_email'] = $billing_email;
		}

		$metadata = array(
			__( 'customer_name', 'cartflows-pro' )  => sanitize_text_field( $billing_first_name ) . ' ' . sanitize_text_field( $billing_last_name ),
			__( 'customer_email', 'cartflows-pro' ) => sanitize_email( $billing_email ),
			'order_id'                              => $order->get_order_number() . '_' . $product['id'] . '_' . $product['step_id'],
		);

		$post_data['expand[]'] = 'balance_transaction';
		$post_data['metadata'] = apply_filters( 'wc_stripe_payment_metadata', $metadata, $order, $order_source );

		if ( $order_source->customer ) {
			$post_data['customer'] = $order_source->customer;
		}

		if ( $order_source->source ) {

			$source_3ds = $order->get_meta( '_cartflows_stripe_source_id', true );

			$post_data['source'] = ( '' !== $source_3ds ) ? $source_3ds : $order_source->source;
		}

		wcf()->logger->log( 'Ended: ' . __CLASS__ . '::' . __FUNCTION__ );

		return apply_filters( 'wc_stripe_generate_payment_request', $post_data, $order, $order_source );
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
		$refund_amount  = $offer_data['refund_amount'];

		$order_currency = $order->get_currency( $order );

		$request     = array();
		$response_id = false;

		if ( ! is_null( $refund_amount ) && class_exists( 'WC_Stripe_Helper' ) && class_exists( 'WC_Stripe_API' ) ) {

			$request['amount'] = WC_Stripe_Helper::get_stripe_amount( $refund_amount, $order_currency );
			$request['charge'] = $transaction_id;
			$response          = WC_Stripe_API::request( $request, 'refunds' );

			if ( ! empty( $response->error ) || ! $response ) {
				$response_id = false;
			} else {
				/**
				 * Update sripe transaction amounts
				 */
				$this->get_wc_gateway()->update_fees( $order, $response->balance_transaction );

				$response_id = isset( $response->id ) ? $response->id : true;
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
	 * Setup the Payment data for Stripe's Automatic Subscription.
	 *
	 * @param WC_Subscription $subscription An instance of a subscription object.
	 * @param object          $order Object of order.
	 * @param array           $offer_product array of offer product.
	 */
	public function add_subscription_payment_meta( $subscription, $order, $offer_product ) {

		if ( 'stripe' === $order->get_payment_method() ) {

			$subscription->update_meta_data( '_stripe_source_id', $order->get_meta( '_stripe_source_id', true ) );
			$subscription->update_meta_data( '_stripe_customer_id', $order->get_meta( '_stripe_customer_id', true ) );
			$subscription->save();
		}
	}

	/**
	 * Setup the Payment data for Stripe's Automatic Subscription.
	 *
	 * @param object $parent_order Object of order.
	 */
	public function remove_the_stripe_refund_action( $parent_order ) {

		// Stripe refund the order if status is cancelled hence need to avoid it by removing below action.
		if ( 'stripe' === $parent_order->get_payment_method() ) {
			remove_action( 'woocommerce_order_status_cancelled', array( WC_Stripe_Order_Handler::get_instance(), 'cancel_payment' ) );
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

		// In order to refund the upsell childe order, stripe checks if charge is captured or not.Hence need to add below key.
		$child_order->update_meta_data( '_stripe_charge_captured', 'yes' );
		$child_order->save();
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Gateway_Stripe' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateway_Stripe::get_instance();
