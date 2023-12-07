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
 * Class Cartflows_Pro_Gateway_Paypal_Standard.
 */
class Cartflows_Pro_Gateway_Paypal_Standard extends Cartflows_Pro_Paypal_Gateway_helper {

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
	public $key = 'paypal';

	/**
	 * Token variable
	 *
	 * @var token
	 */
	public $token = null;

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

		add_filter( 'woocommerce_paypal_args', array( $this, 'modify_paypal_args' ), 10, 2 );

		add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ), 20 );

		add_action( 'valid-paypal-standard-ipn-request', array( $this, 'update_ipn_status' ), - 1 );

		add_filter( 'wcf_order_status_after_order_complete', array( $this, 'modify_order_status' ), 10, 2 );

		add_filter( 'woocommerce_paypal_refund_request', array( $this, 'offer_refund_request_data' ), 10, 4 );

		add_action( 'cartflows_offer_subscription_created', array( $this, 'add_subscription_payment_meta_for_paypal' ), 10, 3 );

	}

	/**
	 * Handles ipn response and save it to order meta.
	 *
	 * @param array $posted Post data after wp_unslash.
	 */
	public function update_ipn_status( $posted ) {

		wcf()->logger->log( 'Data collected from IPN' . wcf_print_r( $posted, true ) );

		$custom = json_decode( $posted['custom'] );

		if ( $custom && is_object( $custom ) ) {
			$order_id = $custom->order_id;
		}

		$order = wc_get_order( $order_id );
		if ( $order && $order instanceof WC_Order && isset( $posted['payment_status'] ) ) {

			$order->update_meta_data( '_wcf_paypal_ipn_status', $posted['payment_status'] );
			$order->save_meta_data();
		}
	}

	/**
	 * Modify order status from ipn status retrieved from order meta.
	 *
	 * @param string   $status order status.
	 * @param WC_Order $order order data.
	 */
	public function modify_order_status( $status, $order ) {

		$get_meta = $order->get_meta( '_wcf_paypal_ipn_status', true );

		if ( empty( $get_meta ) ) {
			$get_meta = $order->get_meta( '_paypal_status', true );

		}
		if ( empty( $get_meta ) ) {
			return $status;
		}

		switch ( $get_meta ) {
			case 'Completed':
			case 'completed':
			case 'pending':
			case 'Pending':
				return $order->needs_processing() ? 'processing' : 'completed';
			case 'failed':
			case 'Failed':
			case 'denied':
			case 'Denied':
			case 'Expired':
			case 'expired':
				return 'failed';

		}

		return $status;
	}

	/**
	 * Modify WooCommerce paypal arguments.
	 *
	 * @param array    $args argumenets for payment.
	 * @param WC_Order $order order data.
	 * @return array
	 */
	public function modify_paypal_args( $args, $order ) {

		wcf()->logger->log( __CLASS__ . '::' . __FUNCTION__ . ' : Entering ' );

		$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
		$flow_id     = wcf()->utils->get_flow_id_from_post_data();

		if ( ! $checkout_id ) {
			return $args;
		}

		wcf()->logger->log( ' Is reference - ' . wcf_pro()->utils->is_reference_transaction() );

		if ( ! wcf_pro()->utils->is_reference_transaction() ) {

			$is_upsell = false;

			if ( wcf_pro()->flow->check_if_next_step_is_offer( $order, $flow_id, $checkout_id ) ) {
				wcf()->logger->log( 'Offer page found.' );

				if ( $this->has_api_details() ) {

					$is_upsell = true;
				}
			}

			if ( $is_upsell ) {

				wcf()->logger->log( 'Reference transaction disabled' );

				$order_id = wcf_pro()->wc_common->get_order_id( $order );

				wcf_pro()->front->setup_upsell( $order_id );

				if ( did_action( 'cartflows_order_started' ) && ( ! wcf_pro()->utils->is_separate_offer_order() ) ) {

					$order_status = $order->get_status();

					$new_status = wcf_pro()->order->get_order_status_slug();

					/**
					 * $new_status = our new status.
					 * $order_status = default status change.
					 */
					do_action( 'cartflows_order_status_change_to_main_order', $new_status, $order_status, $order );

					remove_filter( 'woocommerce_payment_complete_order_status', array( wcf_pro()->order, 'maybe_set_completed_order_status' ), 999 );

					$order->update_status( 'wcf-main-order' );
				}

				$args['return'] = $this->wc_gateway()->get_return_url( $order );

				wcf()->logger->log( 'Paypal Return URL' );
				wcf()->logger->log( $args['return'] );
			}
		} else {

			// Initiate express checkout request.
			$response = $this->initiate_express_checkout_request(
				array(
					'currency'   => $args['currency_code'],
					'return_url' => $this->get_callback_url(
						array(
							'action'   => 'cartflows_paypal_create_billing_agreement',
							'step_id'  => $checkout_id,
							'order_id' => wcf_pro()->wc_common->get_order_id( $order ),
						)
					),
					'cancel_url' => $args['cancel_return'],
					'notify_url' => $args['notify_url'],
					'custom'     => $args['custom'],
					'order'      => $order,
					'step_id'    => $checkout_id,
				)
			);

			if ( ! isset( $response['TOKEN'] ) || '' === $response['TOKEN'] ) {

				wcf()->logger->log( 'Switching back to paypal Standard: Reason: Unable to set Express checkout' );
				wcf()->logger->log( 'Result For setExpressCheckout' . wcf_print_r( $response, true ) );

				return $args;
			}

			$paypal_args = array(
				'cmd'   => '_express-checkout',
				'token' => $response['TOKEN'],
			);

			return $paypal_args;
		}

		wcf()->logger->log( __CLASS__ . '::' . __FUNCTION__ . ' : Leaving ' );

		return $args;
	}

	/**
	 * Check if gateway has API details.
	 *
	 * @return bool
	 */
	public function has_api_details() {

		$has_credentials = false;
		$environment     = ( true === $this->wc_gateway()->testmode ) ? 'sandbox' : 'live';

		$api_prefix = '';

		if ( 'sandbox' === $environment ) {
			$api_prefix = 'sandbox_';
		}

		if ( '' !== $this->wc_gateway()->get_option( $api_prefix . 'api_username' ) && '' !== $this->wc_gateway()->get_option( $api_prefix . 'api_password' ) && '' !== $this->wc_gateway()->get_option( $api_prefix . 'api_signature' ) ) {
			$has_credentials = true;
		}

		return $has_credentials;
	}

	/**
	 * Get Payer ID from option value.
	 *
	 * @return bool
	 */
	public function get_woo_payer_id() {

		$environment = ( true === $this->wc_gateway()->testmode ) ? 'sandbox' : 'live';

		$api_prefix = '';

		if ( 'sandbox' === $environment ) {
			$api_prefix = 'sandbox_';
		}

		$option_key = 'woocommerce_ppec_payer_id_' . $environment . '_' . md5( $this->wc_gateway()->get_option( $api_prefix . 'api_username' ) . ':' . $this->wc_gateway()->get_option( $api_prefix . 'api_password' ) );

		$payer_id = get_option( $option_key );

		if ( $payer_id ) {
			return $payer_id;
		} else {
			$result = $this->get_woo_pal_details();

			if ( ! empty( $result['PAL'] ) ) {
				update_option( $option_key, wc_clean( $result['PAL'] ) );

				return $payer_id;
			}
		}

		return false;
	}

	/**
	 * Get Payer details from option value.
	 *
	 * @return bool
	 */
	public function get_woo_pal_details() {

		$environment = ( true === $this->wc_gateway()->testmode ) ? 'sandbox' : 'live';

		$api_prefix = '';

		if ( 'sandbox' === $environment ) {
			$api_prefix = 'sandbox_';
		}

		$this->setup_api_vars( $this->key, $environment, $this->wc_gateway()->get_option( $api_prefix . 'api_username' ), $this->wc_gateway()->get_option( $api_prefix . 'api_password' ), $this->wc_gateway()->get_option( $api_prefix . 'api_signature' ) );

		$this->add_parameter( 'METHOD', 'GetPalDetails' );
		$this->add_credentials_param( $this->api_username, $this->api_password, $this->api_signature, 124 );
		$request         = new stdClass();
		$request->path   = '';
		$request->method = 'POST';
		$request->body   = $this->to_string();

		return $this->perform_request( $request );

	}

	/**
	 * Load paypal object payment JS.
	 *
	 * @return void
	 */
	public function payment_scripts() {

		if ( _is_wcf_base_offer_type() && ! wcf_pro()->utils->is_reference_transaction() && $this->has_paypal_gateway() ) {

			wp_enqueue_script(
				'wcf-paypal-script',
				'https://www.paypalobjects.com/api/checkout.js',
				array( 'jquery' ),
				CARTFLOWS_PRO_VER,
				true
			);

			if ( ! wcf_pro()->utils->is_zero_value_offered_product() ) {
				$script = $this->generate_script();
				wp_add_inline_script( 'wcf-paypal-script', $script );
			}
		}
	}

	/**
	 * Check if current order has paypal gatway
	 *
	 * @return bool
	 */
	public function has_paypal_gateway() {

		$order_id = isset( $_GET['wcf-order'] ) ? absint( $_GET['wcf-order'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( empty( $order_id ) ) {
			return false;
		}

		$order   = wc_get_order( $order_id );
		$gateway = $order->get_payment_method();

		if ( 'paypal' === $gateway ) {
			return true;
		}

		return false;
	}

	/**
	 * Generate script for paypal payment popup.
	 *
	 * @return string
	 */
	public function generate_script() {

		$environment = ( true === $this->wc_gateway()->testmode ) ? 'sandbox' : 'live';

		ob_start();
		?>
		(function($){ $( function($) {

			var $wcf_ppec = {
				init: function () {
					var getButtons = [
						'wcf-upsell-offer',
						'wcf-downsell-offer'
					];

					$('a[href*="wcf-up-offer-yes"], a[href*="wcf-down-offer-yes"]').each(function(e) {

						var current_id = $(this).attr('id');

						getButtons.push( current_id );
					});

					window.paypalCheckoutReady = function () {
						paypal.checkout.setup(
							'<?php echo esc_attr( $this->get_woo_payer_id() ); ?>',
							{
								environment: '<?php echo esc_attr( $environment ); ?>',
								buttons: getButtons,
								locale: 'en_US',

								click: function () {

									var variation_id = 0;
									var input_qty = 0;

									var variation_wrapper = $('.wcf-offer-product-variation');

									if( variation_wrapper.length > 0 ) {

										var variation_form 	 = variation_wrapper.find('.variations_form'),
											variation_input   = variation_form.find('input.variation_id');

										// Set variation id here.
										variation_id = parseInt( variation_input.val() );

										if( $('.var_not_selected').length > 0 || '' === variation_id || 0 === variation_id ){

											variation_form.find('.variations select').each(function(){

												if( $(this).val().length === 0 ){
													$(this).addClass('var_not_selected');
												}
											});

											$([ document.documentElement, document.body ]).animate({
												scrollTop: variation_form.find('.variations select').offset().top-100
											}, 1000);

											return false;
										}
									}

									var quantity_wrapper = $('.wcf-offer-product-quantity');

									if ( quantity_wrapper.length > 0 ) {

										var quantity_input = quantity_wrapper.find('input[name="quantity"]');
										var quantity_value = parseInt( quantity_input.val() );

										if( quantity_value > 0 ) {
											input_qty = quantity_value;
										}
									}

									var postData = {
										step_id: cartflows.current_step,
										order_id: <?php echo isset( $_GET['wcf-order'] ) ? intval( $_GET['wcf-order'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>,
										order_key: '<?php echo isset( $_GET['wcf-key'] ) ? sanitize_key( wp_unslash( $_GET['wcf-key'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>',
										session_key: '<?php echo isset( $_GET['wcf-sk'] ) ? sanitize_key( wp_unslash( $_GET['wcf-sk'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>',
										variation_id: variation_id,
										input_qty: input_qty,
										action: 'cartflows_front_create_express_checkout_token',
										security: <?php echo sanitize_key( wp_create_nonce( 'cartflows_front_create_express_checkout_token_nonce' ) ); ?>
									};

									paypal.checkout.initXO();

									var action = $.post(cartflows.ajax_url, postData);

									action.done(function (data) {
										paypal.checkout.startFlow(data.token);
									});

									action.fail(function () {
										paypal.checkout.closeFlow();
									});
								}
							}
						);
					}
				}
			};

			$wcf_ppec.init();
		}); })(jQuery);
		<?php

		return ob_get_clean();
	}

	/**
	 * Generates express checkout token
	 *
	 * @return void
	 */
	public function generate_express_checkout_token() {

		$nonce  = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';
		$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_send_json(
				array(
					'result'   => 'error',
					'response' => __( 'Nonce verification failed', 'cartflows-pro' ),
				)
			);
		}

		$step_id      = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
		$order_id     = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
		$order_key    = isset( $_POST['order_key'] ) ? sanitize_text_field( wp_unslash( $_POST['order_key'] ) ) : '';
		$session_key  = isset( $_POST['session_key'] ) ? sanitize_text_field( wp_unslash( $_POST['session_key'] ) ) : '';
		$variation_id = isset( $_POST['variation_id'] ) ? intval( $_POST['variation_id'] ) : '';
		$input_qty    = isset( $_POST['input_qty'] ) ? intval( $_POST['input_qty'] ) : '';

		$is_valid_order = true;

		if ( $is_valid_order ) {

			$order = wc_get_order( $order_id );

			$response = $this->initiate_express_checkout_request(
				array(
					'currency'     => $order ? $order->get_currency() : get_woocommerce_currency(),
					'return_url'   => $this->get_callback_url(
						array(
							'action'       => 'cartflows_paypal_return',
							'step_id'      => $step_id,
							'order_id'     => $order_id,
							'order_key'    => $order_key,
							'session_key'  => $session_key,
							'variation_id' => $variation_id,
							'input_qty'    => $input_qty,
						)
					),
					'cancel_url'   => $this->get_callback_url(
						array(
							'action'       => 'cartflows_paypal_cancel',
							'step_id'      => $step_id,
							'order_id'     => $order_id,
							'order_key'    => $order_key,
							'session_key'  => $session_key,
							'variation_id' => $variation_id,
							'input_qty'    => $input_qty,
						)
					),
					'notify_url'   => $this->get_callback_url( 'notify_url' ),
					'order'        => $order,
					'step_id'      => $step_id,
					'variation_id' => $variation_id,
					'input_qty'    => $input_qty,
				),
				true
			);

			wcf()->logger->log( 'Generate standard checkout token' );
			wcf()->logger->log( wcf_print_r( $response, true ) );

			if ( isset( $response['TOKEN'] ) && '' !== $response['TOKEN'] ) {

				wp_send_json(
					array(
						'result' => 'success',
						'token'  => $response['TOKEN'],
					)
				);
			}
		}

		wp_send_json(
			array(
				'result'   => 'error',
				'response' => $response,
			)
		);
	}

	/**
	 * Initiates express checkout request
	 *
	 * @param array $args arguments.
	 * @param bool  $is_upsell is upsell.
	 * @return array
	 */
	public function initiate_express_checkout_request( $args, $is_upsell = false ) {

		$environment = ( true === $this->wc_gateway()->testmode ) ? 'sandbox' : 'live';

		$api_prefix = '';

		if ( 'sandbox' === $environment ) {
			$api_prefix = 'sandbox_';
		}

		$this->setup_api_vars(
			$this->key,
			$environment,
			$this->wc_gateway()->get_option( $api_prefix . 'api_username' ),
			$this->wc_gateway()->get_option( $api_prefix . 'api_password' ),
			$this->wc_gateway()->get_option( $api_prefix . 'api_signature' )
		);

		$this->add_express_checkout_params( $args, $is_upsell );
		$this->add_credentials_param( $this->api_username, $this->api_password, $this->api_signature, 124 );

		$request         = new stdClass();
		$request->path   = '';
		$request->method = 'POST';
		$request->body   = $this->to_string();

		$flow_id = wcf()->utils->get_flow_id_from_step_id( $args['step_id'] );

		$data = array(
			'paypal' => $this->get_parameters(),
		);

		wcf_pro()->session->update_data( $flow_id, $data );

		return $this->perform_request( $request );
	}

	/**
	 * Adds express checkout parameters
	 *
	 * @param array $args arguments.
	 * @param bool  $is_upsell is upsell.
	 * @return void
	 */
	public function add_express_checkout_params( $args, $is_upsell = false ) {

		// translators: placeholder is blogname.
		$default_description = sprintf( _x( 'Orders with %s', 'data sent to paypal', 'cartflows-pro' ), get_bloginfo( 'name' ) );

		$defaults = array(
			'currency'            => get_woocommerce_currency(),
			'billing_type'        => 'MerchantInitiatedBillingSingleAgreement',
			'billing_description' => html_entity_decode( apply_filters( 'woocommerce_subscriptions_paypal_billing_agreement_description', $default_description, $args ), ENT_NOQUOTES, 'UTF-8' ),
			'maximum_amount'      => null,
			'no_shipping'         => 1,
			'page_style'          => null,
			'brand_name'          => html_entity_decode( get_bloginfo( 'name' ), ENT_NOQUOTES, 'UTF-8' ),
			'landing_page'        => 'login',
			'payment_action'      => 'Sale',
			'custom'              => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$this->set_method( 'SetExpressCheckout' );

		$this->add_parameters(
			array(

				'RETURNURL'   => $args['return_url'],
				'CANCELURL'   => $args['cancel_url'],
				'PAGESTYLE'   => $args['page_style'],
				'BRANDNAME'   => $args['brand_name'],
				'LANDINGPAGE' => ( 'login' === $args['landing_page'] && false === $is_upsell ) ? 'Login' : 'Billing',
				'NOSHIPPING'  => $args['no_shipping'],
				'MAXAMT'      => $args['maximum_amount'],
			)
		);

		if ( false === $is_upsell ) {
			$this->add_parameter( 'L_BILLINGTYPE0', $args['billing_type'] );
			$this->add_parameter( 'L_BILLINGAGREEMENTDESCRIPTION0', get_bloginfo( 'name' ) );
			$this->add_parameter( 'L_BILLINGAGREEMENTCUSTOM0', '' );
		}

		// Add payment parameters.
		if ( isset( $args['order'] ) ) {

			if ( true === $is_upsell ) {
				$this->add_payment_params( $args['order'], $args['step_id'], $args['payment_action'], false, true, $args['variation_id'], $args['input_qty'] );
			} else {
				$this->add_payment_params( $args['order'], $args['step_id'], $args['payment_action'], false, false );
			}
		}

		$set_express_checkout_params = apply_filters( 'cartflows_gateway_paypal_param_setexpresscheckout', $this->get_parameters(), $is_upsell );
		$this->clean_params();
		$this->add_parameters( $set_express_checkout_params );
	}

	/**
	 * Get callback URL for paypal payment API request.
	 *
	 * @param array $args arguments.
	 * @return string
	 */
	public function get_callback_url( $args ) {

		$api_request_url = WC()->api_request_url( 'cartflows_paypal' );

		if ( is_array( $args ) ) {

			return add_query_arg( $args, $api_request_url );
		} else {

			return add_query_arg( 'action', $args, $api_request_url );
		}
	}

	/**
	 * Get WooCommerce payment geteways.
	 *
	 * @return array
	 */
	public function wc_gateway() {

		global $woocommerce;

		$gateways = $woocommerce->payment_gateways->payment_gateways();

		return $gateways[ $this->key ];
	}

	/**
	 * Clean params.
	 *
	 * @return void
	 */
	public function clean_params() {
		$this->parameters = array();
	}

	/**
	 * Return the parsed response object for the request
	 *
	 * @since 1.0.0
	 *
	 * @param string $raw_response_body response body.
	 *
	 * @return object
	 */
	protected function get_parsed_response( $raw_response_body ) {

		wp_parse_str( urldecode( $raw_response_body ), $this->response_params );

		return $this->response_params;
	}

	/**
	 * Set methods and token paramter.
	 *
	 * @param string $token Token string.
	 */
	public function set_express_checkout_method( $token ) {

		$this->set_method( 'GetExpressCheckoutDetails' );
		$this->add_parameter( 'TOKEN', $token );
	}

	/**
	 * Request to get express checkout details.
	 *
	 * @param string $token token.
	 *
	 * @return object
	 */
	public function perform_express_checkout_details_request( $token ) {

		$environment = ( true === $this->wc_gateway()->testmode ) ? 'sandbox' : 'live';

		$api_prefix = '';

		if ( 'sandbox' === $environment ) {
			$api_prefix = 'sandbox_';
		}

		$this->setup_api_vars(
			$this->key,
			$environment,
			$this->wc_gateway()->get_option( $api_prefix . 'api_username' ),
			$this->wc_gateway()->get_option( $api_prefix . 'api_password' ),
			$this->wc_gateway()->get_option( $api_prefix . 'api_signature' )
		);

		$this->set_express_checkout_method( $token );
		$this->add_credentials_param( $this->api_username, $this->api_password, $this->api_signature, 124 );
		$request         = new stdClass();
		$request->path   = '';
		$request->method = 'POST';
		$request->body   = $this->to_string();

		return $this->perform_request( $request );
	}

	/**
	 * Retrieves token for payment.
	 *
	 * @param object $order order details.
	 *
	 * @return string
	 */
	public function get_token( $order ) {

		if ( false == is_null( $this->token ) ) {
			return $this->token;
		}

		$this->token = $order->get_meta( '_paypal_subscription_id' );

		if ( '' == $this->token ) {
			$this->token = $order->get_meta( '_paypal_subscription_id' );
		}

		if ( ! empty( $this->token ) ) {
			return $this->token;
		}

		return apply_filters( 'cartflows_front_gateway_integration_get_token', false, $this );
	}

	/**
	 * After payment process.
	 *
	 * @param array $order order data.
	 * @param array $product product data.
	 * @return bool
	 */
	public function process_offer_payment( $order, $product ) {

		$is_successful = false;

		try {

			$response = $this->process_reference_transaction( $this->get_token( $order ), $order, array(), $product );

			if ( $this->has_error_api_response( $response ) ) {
				wcf()->logger->log( 'PayPal DoReferenceTransactionCall Failed' );
				wcf()->logger->log( wcf_print_r( $response, true ) );
				$is_successful = false;

			} else {

				$is_successful = true;
				$this->store_offer_transaction( $order, $response, $product );
			}
		} catch ( Exception $e ) {

			wcf()->logger->log( 'PayPal DoReferenceTransactionCall Failed' );
			wcf()->logger->log( wcf_print_r( $response, true ) );
		}

		return $is_successful;
	}

	/**
	 * Store Offer Trxn Charge.
	 *
	 * @param WC_Order $order    The order that is being paid for.
	 * @param Object   $response The response that is send from the payment gateway.
	 * @param array    $product  Product data.
	 */
	public function store_offer_transaction( $order, $response, $product ) {

		$order_id = $order->get_id();
		$txn_id   = '';

		if ( ! isset( $response['PAYMENTINFO_0_TRANSACTIONID'] ) ) {
			$txn_id = $response['TRANSACTIONID'];
		} else {
			$txn_id = $response['PAYMENTINFO_0_TRANSACTIONID'];
		}

		$order->update_meta_data( 'cartflows_offer_txn_resp_' . $product['step_id'], $txn_id );
		$order->save();
	}

	/**
	 * Sets up arguments and performs DoReferenceTransaction call
	 *
	 * @param int   $billing_agreement_id agreement ID.
	 * @param array $order order data.
	 * @param array $args arguments.
	 * @param array $product product details.
	 *
	 * @return object
	 */
	public function process_reference_transaction( $billing_agreement_id, $order, $args, $product ) {

		$environment = ( true === $this->wc_gateway()->testmode ) ? 'sandbox' : 'live';
		$api_prefix  = '';

		if ( 'sandbox' === $environment ) {
			$api_prefix = 'sandbox_';
		}

		$this->setup_api_vars(
			$this->key,
			$environment,
			$this->wc_gateway()->get_option( $api_prefix . 'api_username' ),
			$this->wc_gateway()->get_option( $api_prefix . 'api_password' ),
			$this->wc_gateway()->get_option( $api_prefix . 'api_signature' )
		);

		$this->add_reference_trans_args( $billing_agreement_id, $order, $product, $args );

		$this->add_credentials_param( $this->api_username, $this->api_password, $this->api_signature, 124 );

		$request         = new stdClass();
		$request->path   = '';
		$request->method = 'POST';
		$request->body   = $this->to_string();

		return $this->perform_request( $request );
	}

	/**
	 * Charge a payment against a reference token
	 *
	 * @param string   $reference_id the ID of a reference object, e.g. billing agreement ID.
	 * @param WC_Order $order order object.
	 * @param array    $offer_product offer product data.
	 * @param array    $args arguments.
	 *
	 * @since 1.0.0
	 */
	public function add_reference_trans_args( $reference_id, $order, $offer_product, $args = array() ) {

		$defaults = array(
			'amount'               => $offer_product['total'],
			'payment_type'         => 'Any',
			'payment_action'       => 'Sale',
			'return_fraud_filters' => 1,
			'notify_url'           => WC()->api_request_url( 'WC_Gateway_Paypal' ),
			'invoice_number'       => $order->get_id() . '-' . $offer_product['step_id'],
		);

		$args = wp_parse_args( $args, $defaults );

		$this->set_method( 'DoReferenceTransaction' );

		// Set base params.
		$this->add_parameters(
			array(
				'REFERENCEID'      => $reference_id,
				'BUTTONSOURCE'     => 'WooThemes_Cart',
				'RETURNFMFDETAILS' => $args['return_fraud_filters'],
			)
		);

		$this->add_payment_params( $order, $offer_product['step_id'], $args['payment_action'], true, true, $offer_product['id'], $offer_product['qty'] );
	}

	/**
	 * Processes API calls.
	 * This function will be executed if reference Trasaction is disabled.
	 *
	 * @return void
	 */
	public function process_api_calls() {
		//phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['action'] ) ) {
			return;
		}

		$step_id      = isset( $_GET['step_id'] ) ? intval( $_GET['step_id'] ) : 0;
		$order_id     = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;
		$order_key    = isset( $_GET['order_key'] ) ? sanitize_text_field( wp_unslash( $_GET['order_key'] ) ) : '';
		$session_key  = isset( $_GET['session_key'] ) ? sanitize_text_field( wp_unslash( $_GET['session_key'] ) ) : '';
		$variation_id = isset( $_GET['variation_id'] ) ? intval( $_GET['variation_id'] ) : '';
		$input_qty    = isset( $_GET['input_qty'] ) ? intval( $_GET['input_qty'] ) : '';

		switch ( $_GET['action'] ) {

			case 'cartflows_paypal_return':
				$flow_id = wcf()->utils->get_flow_id_from_step_id( $step_id );

				$data = wcf_pro()->session->get_data( $flow_id );

				$offer_product = wcf_pro()->utils->get_offer_data( $step_id, $variation_id, $input_qty, $order_id );

				$order = wc_get_order( $order_id );

				if ( isset( $_GET['token'] ) && ! empty( $_GET['token'] ) ) {

					/**
					 * Setting up necessary data for this api call.
					 */

					$api_response_result = false;

					/**
					 * Get the data we saved while calling setExpressCheckout call.
					 */
					$get_paypal_data = array();

					if ( isset( $data['paypal'] ) ) {
						$get_paypal_data = $data['paypal'];
					}
					// Using WooCommerce function for sanitizing token.
					$express_checkout_details_response = $this->perform_express_checkout_details_request( wc_clean( wp_unslash( $_GET['token'] ) ) );

					wcf()->logger->log( 'Standard checkout token return request' );
					wcf()->logger->log( wcf_print_r( $express_checkout_details_response, true ) );

					/**
					 * Check if product total is greater than 0.
					 */
					if ( $offer_product['total'] > 0 ) {

						/**
						 * Prepare DoExpessCheckout Call to finally charge the user.
						 */
						$do_express_checkout_data = array(
							'TOKEN'   => $express_checkout_details_response['TOKEN'],
							'PAYERID' => $express_checkout_details_response['PAYERID'],
							'METHOD'  => 'DoExpressCheckoutPayment',
						);

						$do_express_checkout_data = wp_parse_args( $do_express_checkout_data, $get_paypal_data );

						$environment = ( true === $this->wc_gateway()->testmode ) ? 'sandbox' : 'live';

						$api_prefix = '';

						if ( 'sandbox' === $environment ) {
							$api_prefix = 'sandbox_';
						}

						/**
						 * Setup & perform DoExpressCheckout API Call.
						 */
						$this->setup_api_vars(
							$this->key,
							$environment,
							$this->wc_gateway()->get_option( $api_prefix . 'api_username' ),
							$this->wc_gateway()->get_option( $api_prefix . 'api_password' ),
							$this->wc_gateway()->get_option( $api_prefix . 'api_signature' )
						);

						$this->add_parameters( $do_express_checkout_data );
						$this->add_credentials_param( $this->api_username, $this->api_password, $this->api_signature, 124 );

						$request         = new stdClass();
						$request->path   = '';
						$request->method = 'POST';
						$request->body   = $this->to_string();

						$response_checkout = $this->perform_request( $request );

						wcf()->logger->log( 'Standard $response_checkout checkout token charge' );
						wcf()->logger->log( wcf_print_r( $response_checkout, true ) );

						if ( false === $this->has_error_api_response( $response_checkout ) ) {
							$api_response_result = true;
							// Store transaction ID for the CartFlows offer.
							$this->store_offer_transaction( $order, $response_checkout, $offer_product );
						}
					} else {
						$api_response_result = true;

						// Store transaction ID for the CartFlows offer.
						$this->store_offer_transaction( $order, $response_checkout, $offer_product );
					}

					/**** DoExpressCheckout Call Completed */
					/**
					 * Allow our subscription addon to make subscription request.
					 */
					$api_response_result = apply_filters( 'cartflows_gateway_in_offer_transaction_paypal_after_express_checkout_response', $api_response_result, $express_checkout_details_response['TOKEN'], $express_checkout_details_response['PAYERID'], $this );

					$result = wcf_pro()->flow->after_offer_charge( $step_id, $order_id, $order_key, $api_response_result, $variation_id, $input_qty );

					wp_safe_redirect( $result['redirect'] );
					exit;

				} else {

					$result = wcf_pro()->flow->after_offer_charge( $step_id, $order_id, $order_key, $api_response_result, $variation_id, $input_qty );

					wp_safe_redirect( $result['redirect'] );
					exit;
				}

				break;

			case 'cartflows_paypal_cancel':
				$url = get_permalink( $step_id );

				$args = array(
					'wcf-order' => $order_id,
					'wcf-key'   => $order_key,
					'wcf-sk'    => $session_key,
				);

				$url = add_query_arg( $args, $url );

				wp_safe_redirect( $url );
				exit;
		}

		//phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Performs express checkout request
	 *
	 * @param string $token token string.
	 * @param array  $order Order data.
	 * @param array  $args arguments data.
	 *
	 * @return object
	 */
	public function perform_express_checkout_request( $token, $order, $args ) {

		$environment = ( true === $this->wc_gateway()->testmode ) ? 'sandbox' : 'live';

		$api_prefix = '';
		if ( 'sandbox' === $environment ) {
			$api_prefix = 'sandbox_';
		}
		$this->setup_api_vars(
			$this->key,
			$environment,
			$this->wc_gateway()->get_option( $api_prefix . 'api_username' ),
			$this->wc_gateway()->get_option( $api_prefix . 'api_password' ),
			$this->wc_gateway()->get_option( $api_prefix . 'api_signature' )
		);

		$this->add_do_express_checkout_params( $token, $order, $args );

		$this->add_credentials_param( $this->api_username, $this->api_password, $this->api_signature, 124 );

		$request         = new stdClass();
		$request->path   = '';
		$request->method = 'POST';
		$request->body   = $this->to_string();

		return $this->perform_request( $request );
	}

	/**
	 * Sets up DoExpressCheckoutPayment API Call arguments
	 *
	 * @param string   $token Unique token of the payment initiated.
	 * @param WC_Order $order order data.
	 * @param array    $args arguments data.
	 */
	public function add_do_express_checkout_params( $token, $order, $args ) {

		$this->set_method( 'DoExpressCheckoutPayment' );

		// set base params.
		$this->add_parameters(
			array(
				'TOKEN'            => $token,
				'PAYERID'          => $args['payer_id'],
				'BUTTONSOURCE'     => 'WooThemes_Cart',
				'RETURNFMFDETAILS' => 1,
			)
		);

		$this->add_payment_params( $order, $args['step_id'], $args['payment_action'], false, false );
	}

	/**
	 * Create billing agreement for future reference transaction.
	 *
	 * @throws Exception Billing agreement errors.
	 */
	public function create_billing_agreement() {
		//phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['action'] ) ) {
			return;
		}

		switch ( $_GET['action'] ) {

			// create billing agreement for reference transaction.
			case 'cartflows_paypal_create_billing_agreement':
				// bail if no token.
				if ( ! isset( $_GET['token'] ) ) {
					return;
				}

				// get token to retrieve checkout details with.
				$token    = esc_attr( sanitize_text_field( wp_unslash( $_GET['token'] ) ) );
				$order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;
				$step_id  = isset( $_GET['step_id'] ) ? intval( $_GET['step_id'] ) : 0;

				try {

					$express_checkout_details_response = $this->perform_express_checkout_details_request( $token );

					// Make sure the billing agreement was accepted.
					if ( 1 == $express_checkout_details_response['BILLINGAGREEMENTACCEPTEDSTATUS'] ) {

						$order = wc_get_order( $order_id );

						if ( is_null( $order ) ) {
							throw new Exception( __( 'Unable to find order for PayPal billing agreement.', 'cartflows-pro' ) );
						}

						// we need to process an initial payment.
						if ( $order->get_total() > 0 ) {

							$billing_agreement_response = $this->perform_express_checkout_request(
								$token,
								$order,
								array(
									'payment_action' => 'Sale',
									'payer_id'       => $this->get_value_from_response( $express_checkout_details_response, 'PAYERID' ),
									'step_id'        => $step_id,
								)
							);
						} else {

							$redirect_url = add_query_arg( 'utm_nooverride', '1', $order->get_checkout_order_received_url() );

							// redirect customer to order received page.
							wp_safe_redirect( esc_url_raw( $redirect_url ) );
							exit;
						}

						if ( $this->has_error_api_response( $billing_agreement_response ) ) {

							$redirect_url = add_query_arg( 'utm_nooverride', '1', $order->get_checkout_order_received_url() );

							// redirect customer to order received page.
							wp_safe_redirect( esc_url_raw( $redirect_url ) );
							exit;
						}

						$order->set_payment_method( 'paypal' );

						// Store the billing agreement ID on the order and subscriptions.
						$order->update_meta_data( '_paypal_subscription_id', $this->get_value_from_response( $billing_agreement_response, 'BILLINGAGREEMENTID' ) );
						$order->save();

						$order->payment_complete( $billing_agreement_response['PAYMENTINFO_0_TRANSACTIONID'] );

						$redirect_url = add_query_arg( 'utm_nooverride', '1', $order->get_checkout_order_received_url() );

						// redirect customer to order received page.
						wp_safe_redirect( esc_url_raw( $redirect_url ) );
						exit;

					} else {

						wp_safe_redirect( wc_get_cart_url() );
						exit;

					}
				} catch ( Exception $e ) {

					wc_add_notice( __( 'An error occurred, please try again or try an alternate form of payment.', 'cartflows-pro' ), 'error' );

					wp_safe_redirect( wc_get_cart_url() );
					exit;
				}

				exit;

		}

		//phpcs:enable WordPress.Security.NonceVerification.Recommended
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

		if ( ! is_null( $refund_amount ) && class_exists( 'WC_Gateway_Paypal' ) ) {

			$paypal = $this->get_wc_gateway();

			if ( $this->is_api_refund ) {

				if ( ! class_exists( 'WC_Gateway_Paypal_API_Handler' ) ) {
					include_once wc()->plugin_path() . '/includes/gateways/paypal/includes/class-wc-gateway-paypal-api-handler.php';  //phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomFunction
				}

				WC_Gateway_Paypal_API_Handler::$api_username  = $paypal->testmode ? $paypal->get_option( 'sandbox_api_username' ) : $paypal->get_option( 'api_username' );
				WC_Gateway_Paypal_API_Handler::$api_password  = $paypal->testmode ? $paypal->get_option( 'sandbox_api_password' ) : $paypal->get_option( 'api_password' );
				WC_Gateway_Paypal_API_Handler::$api_signature = $paypal->testmode ? $paypal->get_option( 'sandbox_api_signature' ) : $paypal->get_option( 'api_signature' );
				WC_Gateway_Paypal_API_Handler::$sandbox       = $paypal->testmode;

				$result = WC_Gateway_Paypal_API_Handler::refund_transaction( $order, $refund_amount, $refund_reason );

				if ( is_wp_error( $result ) ) {
					wcf()->logger->log( "Paypal offer refund failed. Order: {$order_id}, Error: " . wcf_print_r( $result->get_error_message(), true ) );
				} else {
					switch ( strtolower( $result->ACK ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						case 'success':
						case 'successwithwarning':
							$response = $result->REFUNDTRANSACTIONID; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					}
				}
				if ( isset( $result->L_LONGMESSAGE0 ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					wcf()->logger->log( 'Paypal offer refund error message: ' . wcf_print_r( $result->L_LONGMESSAGE0, true ) ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				}
			}
		}

		return $response ? $response : false;
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

		if ( 'paypal' === $order->get_payment_method() ) {

			$subscription->update_meta_data( '_paypal_subscription_id', $order->get_meta( '_paypal_subscription_id', true ) );
			$subscription->save();
		}
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Gateway_Paypal_Standard' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateway_Paypal_Standard::get_instance();
