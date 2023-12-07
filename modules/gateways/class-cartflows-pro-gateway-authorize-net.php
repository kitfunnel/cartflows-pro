<?php
/**
 * Authorize Gateway.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Gateway_Authorize_Net.
 */
class Cartflows_Pro_Gateway_Authorize_Net {

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
	public $key = 'authorize_net_cim_credit_card';

	/**
	 * Extra data required in other functions
	 *
	 * @var extra_data
	 */
	public $extra_data = array();

	/**
	 * Opaque value variable
	 *
	 * @var unset_opaque_value
	 */
	public $unset_opaque_value = false;

	/**
	 * Characterset encoding type
	 *
	 * @var mb_encoding
	 */
	public $mb_encoding = 'UTF-8';

	/**
	 * Offer product
	 *
	 * @var offer_product
	 */
	public $offer_product = array();

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

		/**
		 * Force tokenization when needed for upsell/doownsell
		 */
		add_filter( 'wc_payment_gateway_' . $this->key . '_tokenization_forced', array( $this, 'maybe_force_tokenization' ) );

		/**
		 * Create token for non logged-in user and authorize version greater than 3.0.0.
		 * Force guest tokenization when needed for upsell/doownsell
		 */
		add_filter( 'wc_payment_gateway_' . $this->key . '_process_payment', array( $this, 'create_token_process_payment' ), 10, 3 );

		/**
		*
		* Send the data for the refund request.
		*/
		add_filter( 'wc_authorize_net_cim_api_request_data', array( $this, 'offer_refund_request_data' ), 10, 3 );

		add_action( 'cartflows_offer_subscription_created', array( $this, 'add_subscription_payment_meta_for_authorize_net' ), 10, 3 );
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

		return $force_tokenization;
	}

	/**
	 * Create token for non logged-in user and authorize version greater than 3.0.0
	 * Forces guest tokenization for upsell/downsells.
	 *
	 * @since 1.5.0
	 *
	 * @param array  $result gateway data.
	 * @param int    $order_id order id.
	 * @param object $anet_obj class instance.
	 * @return array
	 */
	public function create_token_process_payment( $result, $order_id, $anet_obj ) {

		$create_token = false;
		$checkout_id  = wcf()->utils->get_checkout_id_from_post_data();
		$flow_id      = wcf()->utils->get_flow_id_from_post_data();

		if ( $checkout_id && $flow_id && wcf_pro()->flow->is_upsell_exist_in_flow( $flow_id, $checkout_id ) ) {
			$create_token = true;
		}

		$order       = $this->get_wc_gateway()->get_order( $order_id );
		$get_user_id = $order->get_user_id();

		if ( $create_token && empty( $get_user_id ) ) {

			try {

				// Check if token is already exist.
				if ( isset( $order->payment->token ) && $order->payment->token ) {

					$this->get_wc_gateway()->add_transaction_data( $order );
				} else {
					// else create new token.
					$order_for_shipping = $order;

					try {
						$order = $this->get_wc_gateway()->get_payment_tokens_handler()->create_token( $order );
					} catch ( Exception $e ) {

						$re  = '/[0-9]+/';
						$str = $e->getMessage();

						preg_match_all( $re, $str, $matches, PREG_SET_ORDER, 0 );

						if ( $matches && is_array( $matches ) && isset( $matches[0][0] ) && '00039' === $matches[0][0] ) {

							$get_order_by_meta = new WP_Query(
								array(
									'post_type'   => 'shop_order',
									'post_status' => 'any',
									'meta_query'  => array( //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
										array(
											'key'     => '_wc_authorize_net_cim_credit_card_customer_id',
											'value'   => $matches[1][0],
											'compare' => '=',
										),
									),
									'fields'      => 'ids',
									'order'       => 'ASC',
								)
							);

							if ( is_array( $get_order_by_meta->posts ) && count( $get_order_by_meta->posts ) > 0 ) {

								$this->extra_data['authorize_net_cim_order_id'] = $get_order_by_meta->posts[0];

								$order_for_shipping = $this->get_wc_gateway()->get_order( $get_order_by_meta->posts[0] );

								$this->extra_data['authorize_net_cim_customer_id'] = $matches[1][0];
							}
						}
					}
					// otherwise tokenize the payment method.
					$this->unset_opaque_value = true;
					$order                    = $this->get_order( $order );
					$this->get_wc_gateway()->add_transaction_data( $order );
					/**
					 * We need to create shipping ID for the current user on Authorize.Net CIM API
					 * As ShippingAddressID is important for the cases when business owner has shipping-filters enabled in their merchant account.
					 */
					try {

						/**
						 * When we are in a case when there is a returning user & not logged in then in this case there are chances that shipping API request might fail.
						 * In this case we need to try and get shipping ID from the order meta and set this up for further.
						 */
						$response = $this->get_wc_gateway()->get_api()->create_shipping_address( $order );

					} catch ( Exception $e ) {

						$response = intval( $order_for_shipping->get_meta( '_authorize_cim_shipping_address_id' ) );

					}

					$shipping_address_id                 = is_numeric( $response ) ? $response : $response->get_shipping_address_id();
					$order->payment->shipping_address_id = $shipping_address_id;

					$this->get_wc_gateway()->add_transaction_data( $order );
					$this->do_main_transaction( $order );
				}

				$result = array(
					'result'   => 'success',
					'redirect' => $this->get_wc_gateway()->get_return_url( $order ),
				);
			} catch ( Exception $e ) {
				$result = array(
					'result'  => 'failure',
					'message' => $e->getMessage(),
				);

			}
		}

		return $result;
	}

	/**
	 * Get current site name.
	 *
	 * @return string
	 */
	public function get_current_site_name() {

		return ( is_multisite() ) ? get_blog_details()->blogname : get_bloginfo( 'name' );
	}

	/**
	 * Get order.
	 *
	 * @param object $order order object.
	 * @return object.
	 */
	public function get_order( $order ) {

		if ( $order instanceof WC_Order && $this->key === $order->get_payment_method() ) {

			if ( $this->has_token( $order ) && ! is_checkout_pay_page() ) {

				$order_id = $order->get_id();

				// retrieve the payment token.
				$order->payment->token = $this->get_wc_gateway()->get_order_meta( $order_id, 'payment_token' );
				$token_from_gateway    = $this->get_token( $order );
				if ( empty( $order->payment->token ) && ! empty( $token_from_gateway ) ) {
					$order->payment->token = $token_from_gateway;
				}
				// retrieve the optional customer id.
				$order->customer_id = $this->get_wc_gateway()->get_order_meta( $order_id, 'customer_id' );

				/* May be we need customer id from session */
				$customer_id_from_session = isset( $this->extra_data['authorize_net_cim_customer_id'] ) ? $this->extra_data['authorize_net_cim_customer_id'] : '';

				if ( empty( $order->customer_id ) && ! empty( $customer_id_from_session ) ) {
					$order->customer_id = $customer_id_from_session;
				}

				// set token data on order.
				if ( $this->get_wc_gateway()->get_payment_tokens_handler()->user_has_token( $order->get_user_id(), $order->payment->token ) ) {

					// an existing registered user with a saved payment token.
					$token = $this->get_wc_gateway()->get_payment_tokens_handler()->get_token( $order->get_user_id(), $order->payment->token );

					// account last four.
					$order->payment->account_number = $token->get_last_four();

					if ( $this->get_wc_gateway()->is_credit_card_gateway() ) {

						// card type.
						$order->payment->card_type = $token->get_card_type();

						// exp month/year.
						$order->payment->exp_month = $token->get_exp_month();
						$order->payment->exp_year  = $token->get_exp_year();

					} elseif ( $this->get_wc_gateway()->is_echeck_gateway() ) {

						// account type (checking/savings).
						$order->payment->account_type = $token->get_account_type();
					}
				} else {

					// a guest user means that token data must be set from the original order.

					// account number.
					$order->payment->account_number = $this->get_wc_gateway()->get_order_meta( $order_id, 'account_four' );

					if ( $this->get_wc_gateway()->is_credit_card_gateway() ) {

						// card type.
						$order->payment->card_type = $this->get_wc_gateway()->get_order_meta( $order_id, 'card_type' );

						// expiry date.
						$expiry_date = $this->get_wc_gateway()->get_order_meta( $order_id, 'card_expiry_date' );

						if ( ! empty( $expiry_date ) ) {
							list( $exp_year, $exp_month ) = explode( '-', $expiry_date );
							$order->payment->exp_month    = $exp_month;
							$order->payment->exp_year     = $exp_year;
						}
					} elseif ( $this->get_wc_gateway()->is_echeck_gateway() ) {

						// account type.
						$order->payment->account_type = $this->get_wc_gateway()->get_order_meta( $order_id, 'account_type' );
					}
				}
			}

			$response = intval( $order->get_meta( '_authorize_cim_shipping_address_id' ) );
			if ( ! empty( $response ) ) {
				$order->payment->shipping_address_id = $response;
			}

			if ( true === $this->unset_opaque_value && isset( $order->payment->opaque_value ) ) {
				unset( $order->payment->opaque_value );
			}
		}

		return $order;
	}

	/**
	 * We cloned the function that we need to fire main transaction in the case when accept.js in is action and user is not logged in.
	 *
	 * @param object $order order object.
	 * @throws Exception Token exception.
	 */
	private function do_main_transaction( $order ) {

		try {

			// order description.
			/* translators: %1s Release payment */
			$order->description = sprintf( __( '%1$s - Release Payment for Order %2$s', 'cartflows-pro' ), esc_html( $this->get_current_site_name() ), $order->get_order_number() );

			// token is required.
			if ( ! $order->payment->token ) {
				throw new Exception( __( 'Payment token missing/invalid.', 'cartflows-pro' ) );
			}

			// perform the transaction.
			if ( $this->get_wc_gateway()->is_credit_card_gateway() ) {

				if ( $this->get_wc_gateway()->perform_credit_card_charge( $order ) ) {
					$response = $this->get_wc_gateway()->get_api()->credit_card_charge( $order );
				} else {
					$response = $this->get_wc_gateway()->get_api()->credit_card_authorization( $order );
				}
			} elseif ( $this->get_wc_gateway()->is_echeck_gateway() ) {
				$response = $this->get_wc_gateway()->get_api()->check_debit( $order );
			}

			// success! update order record.
			if ( $response->transaction_approved() ) {

				$last_four = substr( $order->payment->account_number, - 4 );

				// order note based on gateway type.
				if ( $this->get_wc_gateway()->is_credit_card_gateway() ) {
					/* translators: %1s payment released */
					$message = sprintf( __( '%1$s %2$s Release Payment Approved: %3$s ending in %4$s (expires %5$s)', 'cartflows-pro' ), $this->get_wc_gateway()->get_method_title(), $this->get_wc_gateway()->perform_credit_card_authorization( $order ) ? 'Authorization' : 'Charge', ! empty( $order->payment->card_type ) ? $order->payment->card_type : 'card', $last_four, ( ! empty( $order->payment->exp_month ) && ! empty( $order->payment->exp_year ) ? $order->payment->exp_month . '/' . substr( $order->payment->exp_year, - 2 ) : 'n/a' ) );

				}

				// adds the transaction id (if any) to the order note.
				if ( $response->get_transaction_id() ) {
					/* translators: %1s transaction id */
					$message .= ' ' . sprintf( __( '(Transaction ID %s)', 'cartflows-pro' ), $response->get_transaction_id() );
				}

				$order->add_order_note( $message );
			}

			if ( $response->transaction_approved() || $response->transaction_held() ) {

				// add the standard transaction data.
				$this->get_wc_gateway()->add_transaction_data( $order, $response );

				// allow the concrete class to add any gateway-specific transaction data to the order.
				$this->get_wc_gateway()->add_payment_gateway_transaction_data( $order, $response );

				// if the transaction was held (ie fraud validation failure) mark it as such.
				if ( $response->transaction_held() || ( $this->get_wc_gateway()->supports( 'authorization' ) && $this->get_wc_gateway()->perform_credit_card_authorization( $order ) ) ) {

					$this->get_wc_gateway()->mark_order_as_held( $order, $this->get_wc_gateway()->supports( 'authorization' ) && $this->get_wc_gateway()->perform_credit_card_authorization( $order ) ? __( 'Authorization only transaction', 'cartflows-pro' ) : $response->get_status_message(), $response );

					wc_reduce_stock_levels( $order->get_id() );
				} else {
					// otherwise complete the order.
					$order->payment_complete();
				}
			} else {

				// failure.
				throw new Exception( sprintf( '%s: %s', $response->get_status_code(), $response->get_status_message() ) );

			}
		} catch ( Exception $e ) {

			// Mark order as failed.
			/* translators: %1s payment failed message */
			$this->get_wc_gateway()->mark_order_as_failed( $order, sprintf( __( 'Pre-Order Release Payment Failed: %s', 'cartflows-pro' ), $e->getMessage() ) );

		}
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

	/********************************** Helper Function Start *********************************/

	/**
	 * Truncates a given string. The total length of return string will not exceed the given length.
	 * The given length will include omission string
	 *
	 * @param string $string text to truncate.
	 * @param int    $length total desired length of string, including omission.
	 * @param string $omission omission text, defaults to '...'.
	 *
	 * @return string
	 * @since 1.5.0
	 */
	public function string_truncate( $string, $length, $omission = '...' ) {

		if ( extension_loaded( 'mbstring' ) ) {

			if ( mb_strlen( $string, $this->mb_encoding ) <= $length ) {
				return $string;
			}

			$length -= mb_strlen( $omission, $this->mb_encoding );

			return mb_substr( $string, 0, $length, $this->mb_encoding ) . $omission;

		} else {

			$string = $this->str_to_ascii( $string );

			if ( strlen( $string ) <= $length ) {
				return $string;
			}

			$length -= strlen( $omission );

			return substr( $string, 0, $length ) . $omission;
		}
	}

	/********************************** Helper Function End *********************************/

	/**
	 * Get the token from order if availble.
	 *
	 * @param object $order order object.
	 * @return bool
	 */
	public function has_token( $order ) {

		$token = $order->get_meta( '_wc_' . $this->key . '_payment_token' );

		if ( ! empty( $token ) ) {
			return true;
		}

		/* Fallback */
		if ( isset( $this->extra_data['authorize_net_cim_order_id'] ) ) {

			$fallback_order_id = $this->extra_data['authorize_net_cim_order_id'];

			$fallback_order = wc_get_order( $fallback_order_id );

			$token = $fallback_order->get_meta( '_wc_' . $this->key . '_payment_token' );

			if ( ! empty( $token ) ) {

				if ( $order ) {
					$order->update_meta_data( '_wc_' . $this->key . '_payment_token', $token );
					$order->save();
				}

				return true;
			}
		}

		return false;
	}


	/**
	 * Get the token from order if availble.
	 *
	 * @param object $order order object.
	 * @return string
	 */
	public function get_token( $order ) {

		$token = $order->get_meta( '_wc_' . $this->key . '_payment_token' );

		if ( ! empty( $token ) ) {
			return $token;
		}

		return '';
	}

	/**
	 * Get customer id by order
	 *
	 * @param object $order order object.
	 *
	 * @return string customer id
	 */
	public function get_customer_id( $order ) {

		$customer_id = $order->get_meta( '_wc_' . $this->key . '_customer_id' );

		if ( ! empty( $customer_id ) ) {
			return $customer_id;
		}

		return '';
	}

	/**
	 * Order items.
	 *
	 * @return array
	 * @since 1.5.0
	 */
	protected function get_line_items() {

		$line_items    = array();
		$offer_product = $this->offer_product;

		if ( isset( $offer_product['id'] ) && $offer_product['id'] > 0 ) {

			$line_items[] = array(
				'itemId'      => $this->string_truncate( $offer_product['id'], 31 ),
				'name'        => $this->string_truncate( $offer_product['name'], 31 ),
				'description' => $this->string_truncate( $offer_product['desc'], 255 ),
				'quantity'    => $offer_product['qty'],
				'unitPrice'   => number_format( (float) $offer_product['total'], 2, '.', '' ),
			);
		}

		return $line_items;
	}

	/**
	 * Request attributes.
	 *
	 * @param array $request request data.
	 * @return array
	 * @since 1.5.0
	 */
	public function get_request_attributes( $request ) {

		return array(
			'method'      => 'POST',
			'timeout'     => MINUTE_IN_SECONDS,
			'redirection' => 0,
			'httpversion' => '1.0',
			'sslverify'   => true,
			'blocking'    => true,
			'headers'     => array(
				'content-type' => 'application/json',
				'accept'       => 'application/json',
			),
			'body'        => wp_json_encode( $request ),
			'cookies'     => array(),
		);
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

		// Assign offer product.
		$this->offer_product = $product;

		if ( ! $this->has_token( $order ) ) {

			wcf()->logger->log( 'Authorize payment : No token found' );
			return $is_successful;
		}

		try {

			$gateway     = $this->get_wc_gateway();
			$api         = $gateway->get_api();
			$environment = $gateway->get_environment();
			$url         = ( 'production' === $environment ) ? $api::PRODUCTION_ENDPOINT : $api::TEST_ENDPOINT;

			/**
			 * Get order object and modify according to your need of token and other details.
			 */
			add_filter( 'wc_payment_gateway_' . $this->key . '_get_order', array( $this, 'get_update_order' ), 999 );

			$new_order = $gateway->get_order( $order );

			$request = $this->create_transaction_request( 'capture', $new_order );

			$attributes = $this->get_request_attributes( $request );
			$response   = wp_safe_remote_request( $url, $attributes );
			$body       = wp_remote_retrieve_body( $response );
			$body       = preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $body );

			$result = json_decode( $body, true );

			if ( is_wp_error( $response ) ) {

				wcf()->logger->log( 'Authorize Net WP Error : ' . wcf_print_r( $response, true ) );
			} else {

				if (
					isset( $result['messages'] ) &&
					isset( $result['messages']['message'][0]['code'] ) &&
					'I00001' === $result['messages']['message'][0]['code']
				) {

					$is_successful = true;

					/*
					Get transaction id here
					transaction_id
					*/
					$trasaction_id = $this->get_offer_transaction_id( $result['directResponse'] );

					$response_data = array(
						'id' => $trasaction_id,
					);

					$this->store_offer_transaction( $order, $response_data, $product );

				} else {
					wcf()->logger->log( 'Authorize Net Response Error : ' . wcf_print_r( $result, true ) );
					/* translators: %1s error message */
					$order_note = sprintf( __( 'Authorize.net CIM Transaction Failed (%s)', 'cartflows-pro' ), $result['messages']['message'][0]['text'] );
					$order->add_order_note( $order_note );
				}
			}
		} catch ( Exception $e ) {
			// todo Show actual error if any.

			wcf()->logger->log( 'Authorize Net exception catch : ' . wcf_print_r( $e, true ) );

			/* translators: %1s site name */
			$note = sprintf( __( 'Authorize.Net CIM Transaction Failed (%s)', 'cartflows-pro' ), $e->getMessage() );

			$order->add_order_note( $note );
		}

		return $is_successful;
	}

	/**
	 * Set upsell/downsell offer order.
	 *
	 * @param object $order order data.
	 * @return object
	 */
	public function get_update_order( $order ) {

		if ( $order instanceof WC_Order && $this->key === $order->get_payment_method() ) {

			if ( $this->has_token( $order ) && ! is_checkout_pay_page() ) {

				$order_id = $order->get_id();

				// retrieve the payment token.
				$order->payment->token = $this->get_wc_gateway()->get_order_meta( $order_id, 'payment_token' );

				$token_from_gateway = $this->get_token( $order );
				if ( empty( $order->payment->token ) && ! empty( $token_from_gateway ) ) {
					$order->payment->token = $token_from_gateway;
				}

				// retrieve the optional customer id.
				$order->customer_id = $this->get_wc_gateway()->get_order_meta( $order_id, 'customer_id' );

				// set token data on order.
				if ( $this->get_wc_gateway()->get_payment_tokens_handler()->user_has_token( $order->get_user_id(), $order->payment->token ) ) {

					// an existing registered user with a saved payment token.
					$token = $this->get_wc_gateway()->get_payment_tokens_handler()->get_token( $order->get_user_id(), $order->payment->token );

					// account last four.
					$order->payment->account_number = $token->get_last_four();

					if ( $this->get_wc_gateway()->is_credit_card_gateway() ) {

						// card type.
						$order->payment->card_type = $token->get_card_type();

						// exp month/year.
						$order->payment->exp_month = $token->get_exp_month();
						$order->payment->exp_year  = $token->get_exp_year();

					} elseif ( $this->get_wc_gateway()->is_echeck_gateway() ) {

						// account type (checking/savings).
						$order->payment->account_type = $token->get_account_type();
					}
				} else {

					// a guest user means that token data must be set from the original order.

					// account number.
					$order->payment->account_number = $this->get_wc_gateway()->get_order_meta( $order_id, 'account_four' );

					if ( $this->get_wc_gateway()->is_credit_card_gateway() ) {

						// card type.
						$order->payment->card_type = $this->get_wc_gateway()->get_order_meta( $order_id, 'card_type' );

						// expiry date.
						$expiry_date = $this->get_wc_gateway()->get_order_meta( $order_id, 'card_expiry_date' );

						if ( ! empty( $expiry_date ) ) {
							list( $exp_year, $exp_month ) = explode( '-', $expiry_date );
							$order->payment->exp_month    = $exp_month;
							$order->payment->exp_year     = $exp_year;
						}
					} elseif ( $this->get_wc_gateway()->is_echeck_gateway() ) {

						// account type.
						$order->payment->account_type = $this->get_wc_gateway()->get_order_meta( $order_id, 'account_type' );
					}
				}
			}

			$response = intval( $order->get_meta( '_authorize_cim_shipping_address_id' ) );
			if ( ! empty( $response ) ) {
				$order->payment->shipping_address_id = $response;
			}

			if ( true === $this->unset_opaque_value && isset( $order->payment->opaque_value ) ) {
				unset( $order->payment->opaque_value );
			}
		}

		return $order;
	}

	/**
	 * Set upsell/downsell offer order.
	 *
	 * @param string $type transactin type.
	 * @param object $new_order order data.
	 * @return object
	 */
	protected function create_transaction_request( $type, $new_order ) {

		$order            = $new_order;
		$transaction_type = ( 'auth_only' === $type ) ? 'profileTransAuthOnly' : 'profileTransAuthCapture';

		$offer_product = $this->offer_product;

		/**
		 * We need to create shipping ID for the current user on Authorize.Net CIM API
		 * As ShippingAddressID is important for the cases when business owner has shipping-filters enabled in their merchant account.
		 */
		if ( isset( $order->payment ) && isset( $order->payment->shipping_address_id ) && ! empty( $order->payment->shipping_address_id ) ) {
			$shipping_address_id = $order->payment->shipping_address_id;
		} else {

			$response = $this->get_wc_gateway()->get_api()->create_shipping_address( $order );

			$shipping_address_id = is_numeric( $response ) ? $response : $response->get_shipping_address_id();

		}

		return array(
			'createCustomerProfileTransactionRequest' => array(
				'merchantAuthentication' => array(
					'name'           => wc_clean( $this->get_wc_gateway()->get_api_login_id() ),
					'transactionKey' => wc_clean( $this->get_wc_gateway()->get_api_transaction_key() ),
				),
				'refId'                  => $order->get_id() . '_' . $offer_product['step_id'],
				'transaction'            => array(
					$transaction_type => array(
						'amount'                    => number_format( (float) $offer_product['total'], 2, '.', '' ),
						'tax'                       => array(),
						'shipping'                  => array(),
						'lineItems'                 => $this->get_line_items(),
						'customerProfileId'         => $this->get_customer_id( $order ),
						'customerPaymentProfileId'  => $this->get_token( $order ),
						'customerShippingAddressId' => $shipping_address_id,
						'order'                     => array(
							'invoiceNumber'       => $order->get_id() . '_' . $offer_product['step_id'],
							'description'         => $this->string_truncate( $offer_product['desc'], 255 ),
							'purchaseOrderNumber' => $this->string_truncate( preg_replace( '/\W/', '', $order->payment->po_number ), 25 ),
						),

					),
				),

				/*
				 * Extra Option if any
				 * 'extraOptions' get_extra_options
				 */
			),
		);
	}

	/**
	 * Creating refund request data.
	 *
	 * @param array  $request_data request data.
	 * @param object $order order object.
	 * @param string $gateway gateway.
	 *
	 * @return array
	 */
	public function offer_refund_request_data( $request_data, $order, $gateway ) {
		//phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['cartflows_refund'] ) ) {

			$order_id = $order->get_id();
			$step_id  = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
			$offer_id = isset( $_POST['offer_id'] ) ? intval( $_POST['offer_id'] ) : 0;

			if ( isset( $request_data['createCustomerProfileTransactionRequest'] ) &&
				isset( $request_data['createCustomerProfileTransactionRequest']['refId'] )
			) {

				$request_data['createCustomerProfileTransactionRequest']['refId'] = $order_id . '_' . $step_id;
			}

			if ( isset( $request_data['createCustomerProfileTransactionRequest'] ) &&
				isset( $request_data['createCustomerProfileTransactionRequest']['transaction'] ) &&
				isset( $request_data['createCustomerProfileTransactionRequest']['transaction']['profileTransRefund'] ) &&
				isset( $request_data['createCustomerProfileTransactionRequest']['transaction']['profileTransRefund']['order'] )
				&& isset( $request_data['createCustomerProfileTransactionRequest']['transaction']['profileTransRefund']['order']['invoiceNumber'] )
			) {
				$request_data['createCustomerProfileTransactionRequest']['transaction']['profileTransRefund']['order']['invoiceNumber'] = $order_id . '_' . $step_id;
			}
		}

		return $request_data;
		//phpcs:enable WordPress.Security.NonceVerification.Missing
	}


	/**
	 * Process offer refund.
	 *
	 * @param object $order the order object.
	 * @param array  $offer_data offer data.
	 *
	 * @return bool
	 */
	public function process_offer_refund( $order, $offer_data ) {

		$order_id       = $offer_data['order_id'];
		$transaction_id = $offer_data['transaction_id'];
		$refund_amount  = $offer_data['refund_amount'];
		$refund_reason  = $offer_data['refund_reason'];
		$gateway        = $this->get_wc_gateway();
		$api            = $gateway->get_api();
		$response_id    = false;

		$order->refund           = new stdClass();
		$order->refund->trans_id = $transaction_id;
		$order->refund->amount   = number_format( $refund_amount, 2, '.', '' );
		$order->refund->reason   = $refund_reason;

		// profile refund/void.
		$order->refund->customer_profile_id         = $gateway->get_order_meta( $order, 'customer_id' );
		$order->refund->customer_payment_profile_id = $gateway->get_order_meta( $order, 'payment_token' );

		$response = $api->refund( $order );

		$response_id = $response->get_transaction_id();

		if ( ! $response_id ) {
			$response    = $api->void( $order );
			$response_id = $response->get_transaction_id();
		}

		return $response_id;
	}

	/**
	 * Is gateway support offer refund?
	 *
	 * @return bool
	 */
	public function is_api_refund() {

		return $this->is_api_refund;
	}

	/**
	 * Store Offer Trxn Charge.
	 *
	 * @param WC_Order $order            The order that is being paid for.
	 * @param Object   $response           The response that is send from the payment gateway.
	 * @param array    $product             The product data.
	 */
	public function store_offer_transaction( $order, $response, $product ) {

		$order_id = $order->get_id();

		wcf()->logger->log( 'Authorize Net : Offer Transaction :: Transaction ID = ' . $response['id'] . ' Captured' );

		$order->update_meta_data( 'cartflows_offer_txn_resp_' . $product['step_id'], $response['id'] );
		$order->save();
	}

	/**
	 * Setup the Payment data for Authorize.net Automatic Subscription.
	 *
	 * @param WC_Subscription $subscription An instance of a subscription object.
	 * @param object          $order Object of order.
	 * @param array           $offer_product array of offer product.
	 */
	public function add_subscription_payment_meta_for_authorize_net( $subscription, $order, $offer_product ) {

		if ( 'authorize_net_cim_credit_card' === $order->get_payment_method() ) {

			$subscription->update_meta_data( '_wc_authorize_net_cim_credit_card_customer_id', $order->get_meta( '_wc_authorize_net_cim_credit_card_customer_id', true ) );
			$subscription->update_meta_data( '_wc_authorize_net_cim_credit_card_payment_token', $order->get_meta( '_wc_authorize_net_cim_credit_card_payment_token', true ) );
			$subscription->save();
		}
	}

	/**
	 * Get Offer Transaction ID.
	 *
	 * @param object $response The response that is send from the payment gateway.
	 * @return string
	 */
	private function get_offer_transaction_id( $response ) {

		$response = explode( ',', $response );

		if ( empty( $response ) ) {
			return '';
		}

		// offset array by 1 to match Authorize.Net's order, mainly for readability.
		array_unshift( $response, null );

		$new_direct_response = array();

		// direct response fields are URL encoded, but we currently do not use any fields.
		// (e.g. billing/shipping details) that would be affected by that.
		$response_fields = array(
			'response_code'        => 1,
			'response_subcode'     => 2,
			'response_reason_code' => 3,
			'response_reason_text' => 4,
			'authorization_code'   => 5,
			'avs_response'         => 6,
			'transaction_id'       => 7,
			'amount'               => 10,
			'account_type'         => 11, // CC or ECHECK.
			'transaction_type'     => 12, // AUTH_ONLY or AUTH_CAPTUREVOID probably.
			'csc_response'         => 39,
			'cavv_response'        => 40,
			'account_last_four'    => 51,
			'card_type'            => 52,
		);

		foreach ( $response_fields as $field => $order ) {

			$new_direct_response[ $field ] = ( isset( $response[ $order ] ) ) ? $response[ $order ] : '';
		}

		return isset( $new_direct_response['transaction_id'] ) && '' !== $new_direct_response['transaction_id'] ? $new_direct_response['transaction_id'] : '';
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Gateway_Authorize_Net' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateway_Authorize_Net::get_instance();
