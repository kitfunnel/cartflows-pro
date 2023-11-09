<?php
/**
 * Offer markup.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Offer Markup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Base_Offer_Markup {


	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

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
	 *  Constructor
	 */
	public function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'offer_scripts' ) );
	}

	/**
	 *  Offer script
	 */
	public function offer_scripts() {

		if ( _is_wcf_base_offer_type() ) {

			global $post;

			$product_id = '';
			$step_id    = $post->ID;
			$flow_id    = wcf()->utils->get_flow_id_from_step_id( $step_id );

			if ( wcf()->flow->is_flow_testmode( $flow_id ) ) {

				$offer_product = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-product', 'dummy' );

				if ( 'dummy' === $offer_product ) {

					$args = array(
						'posts_per_page' => 1,
						'orderby'        => 'rand',
						'post_type'      => 'product',
					);

					$random_product = get_posts( $args );

					if ( isset( $random_product[0]->ID ) ) {
						$offer_product = array(
							$random_product[0]->ID,
						);
					}
				}
			} else {
				$offer_product = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-product' );
			}

			if ( isset( $offer_product[0] ) ) {

				$product_id = $offer_product[0];
			}

			$order_id   = ( isset( $_GET['wcf-order'] ) ) ? intval( $_GET['wcf-order'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order_key  = ( isset( $_GET['wcf-key'] ) ) ? sanitize_text_field( wp_unslash( $_GET['wcf-key'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order      = wc_get_order( $order_id );
			$skip_offer = 'no';
			$offer_type = get_post_meta( $step_id, 'wcf-step-type', true );

			$payment_method = '';
			if ( $order ) {

				$payment_method = $order->get_payment_method();

				$gateways = array( 'paypal', 'ppec_paypal' );
				$gateways = apply_filters( 'cartflows_offer_supported_payment_gateway_slugs', $gateways );
				if ( ( in_array( $payment_method, $gateways, true ) ) && ! wcf_pro()->utils->is_reference_transaction() && ! wcf_pro()->utils->is_zero_value_offered_product() ) {

					$skip_offer = 'yes';
				}
			}

			$currency_symbol    = get_woocommerce_currency_symbol();
			$discount_type      = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-discount' );
			$discount_value     = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-discount-value' );
			$flat_shipping_rate = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-flat-shipping-value' );

			$localize = array(
				'step_id'                     => $step_id,
				'product_id'                  => $product_id,
				'order_id'                    => $order_id,
				'order_key'                   => $order_key,
				'skip_offer'                  => $skip_offer,
				'offer_type'                  => $offer_type,
				'discount_type'               => $discount_type,
				'discount_value'              => $discount_value,
				'flat_shipping_rate'          => $flat_shipping_rate,
				'currency_symbol'             => $currency_symbol,
				'wcf_downsell_accepted_nonce' => wp_create_nonce( 'wcf_downsell_accepted' ),
				'wcf_downsell_rejected_nonce' => wp_create_nonce( 'wcf_downsell_rejected' ),
				'wcf_upsell_accepted_nonce'   => wp_create_nonce( 'wcf_upsell_accepted' ),
				'wcf_upsell_rejected_nonce'   => wp_create_nonce( 'wcf_upsell_rejected' ),
				'payment_method'              => $payment_method,
			);

			if ( 'stripe' === $payment_method ) {
				$localize['wcf_stripe_sca_check_nonce'] = wp_create_nonce( 'wcf_stripe_sca_check' );
				wp_register_script( 'stripe', 'https://js.stripe.com/v3/', '', '3.0', true );
				wp_enqueue_script( 'stripe' );
			}

			if ( 'cpsw_stripe' === $payment_method ) {
				$localize['wcf_cpsw_create_payment_intent_nonce'] = wp_create_nonce( 'wcf_cpsw_create_payment_intent' );
			}

			if ( 'mollie_wc_gateway_creditcard' === $payment_method ) {
				$localize['wcf_mollie_creditcard_process_nonce'] = wp_create_nonce( 'wcf_mollie_creditcard_process' );
			}

			if ( 'mollie_wc_gateway_ideal' === $payment_method ) {
				$localize['wcf_mollie_ideal_process_nonce'] = wp_create_nonce( 'wcf_mollie_ideal_process' );
			}

			if ( 'ppcp-gateway' === $payment_method ) {
				$localize['wcf_create_paypal_order_nonce']  = wp_create_nonce( 'wcf_create_paypal_order' );
				$localize['wcf_capture_paypal_order_nonce'] = wp_create_nonce( 'wcf_capture_paypal_order' );
			}

			if ( 'woocommerce_payments' === $payment_method ) {
				$localize['wcf_woop_create_payment_intent_nonce'] = wp_create_nonce( 'wcf_woop_create_payment_intent' );
				wp_register_script( 'stripe', 'https://js.stripe.com/v3/', '', '3.0', true );
				wp_enqueue_script( 'stripe' );
			}

			if ( 'cppw_paypal' === $payment_method ) {
				$localize['wcf_cppw_create_paypal_order_nonce'] = wp_create_nonce( 'wcf_cppw_create_paypal_order' );
			}

			$localize = apply_filters( 'cartflows_offer_js_localize', $localize );

			$localize_script  = '<!-- script to print the admin localized variables -->';
			$localize_script .= '<script type="text/javascript">';
			$localize_script .= 'var cartflows_offer = ' . wp_json_encode( $localize ) . ';';
			$localize_script .= '</script>';

			// Reason for PHPCS ignore: Used to localize the strings which will be displayed on the browser's tab.
			echo $localize_script; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Offer accepeted
	 *
	 * @param int   $step_id Flow step id.
	 * @param array $extra_data extra data.
	 * @param array $result process result.
	 * @since 1.0.0
	 */
	public function offer_accepted( $step_id, $extra_data, $result ) {

		wcf()->logger->log( 'Start-' . __CLASS__ . '::' . __FUNCTION__ );

		// Update the offer data changes made on offer page, like quantity and variation.
		$order = wc_get_order( $extra_data['order_id'] );
		if ( is_object( $order ) ) {
			$updated_offer_data = $order->get_meta( 'wcf_offer_product_data_' . $step_id, true );
			if ( is_array( $updated_offer_data ) && ! empty( $updated_offer_data ) ) {
				$extra_data = array_merge( $extra_data, $updated_offer_data );
			}
		}

		$order_id          = $extra_data['order_id'];
		$order_key         = $extra_data['order_key'];
		$product_id        = $extra_data['product_id'];
		$variation_id      = $extra_data['variation_id'];
		$input_qty         = $extra_data['input_qty'];
		$step_type         = $extra_data['template_type'];
		$is_charge_success = false;
		$order             = wc_get_order( $order_id );

		$offer_product = wcf_pro()->utils->get_offer_data( $step_id, $variation_id, $input_qty, $order_id );

		$is_offer_accepted = $order->get_meta( 'wcf_is_offer_purchased', true );

		if ( ! empty( $is_offer_accepted ) ) {

			$is_offer_accepted = json_decode( $is_offer_accepted, true );

			if (
				$order_id === $is_offer_accepted['offer_order_id'] &&
				(
					$step_id === $is_offer_accepted['offer_step_id'] &&
					$order_key === $is_offer_accepted['offer_order_key']
				) &&
				'offer_accepted' === $is_offer_accepted['offer_status']
			) {

				/* Get Redirect URL */
				$next_step_url = wcf_pro()->flow->get_next_step_url(
					$step_id,
					array(
						'action'        => 'offer_accepted',
						'order_id'      => $order_id,
						'order_key'     => $order_key,
						'template_type' => $step_type,
					)
				);

				$result = array(
					'status'   => 'failed',
					'redirect' => $next_step_url,
					'message'  => __( 'Seems like this order is been already purchased.', 'cartflows-pro' ),
				);

				wcf()->logger->log( 'Offer is already purchased. Redirecting to URL : ' . $next_step_url );
				wcf()->logger->log( 'Use-case: The user might have clicked the back button on browser and trying to re-purchase the offer.', 'cartflows-pro' );

				return $result;
			}
		}

		// check if product is in stock if stock management is enabled.

		$product        = wc_get_product( $product_id );
		$stock_quantity = $product ? $product->get_stock_quantity() : 0;

		if ( $product && $product->managing_stock() && $stock_quantity < intval( $offer_product['qty'] ) ) {

			$data = array(
				'order_id'  => $order_id,
				'order_key' => $order_key,
			);

			// @todo Need some conditional redirection if there is downsell.
			$next_step_url = wcf_pro()->flow->get_next_step_url( $step_id, $data );

			$result = array(
				'status'   => 'failed',
				'redirect' => $next_step_url,
				'message'  => __( 'Oooops! Product is out of stock.', 'cartflows-pro' ),
			);

			wcf()->logger->log( 'Order-' . $order_id . ' ' . $step_type . ' Offer Payment Failed. Product is out of stock. Redirected to next step.' );

			return $result;
		}

		// Restrict the purchase of product if the product price is in negative.
		if ( isset( $offer_product['price'] ) && ( floatval( $offer_product['price'] ) < floatval( 0 ) ) ) {

			$data = array(
				'order_id'  => $order_id,
				'order_key' => $order_key,
			);

			// @todo Need some conditional redirection if there is downsell.
			$next_step_url = wcf_pro()->flow->get_next_step_url( $step_id, $data );

			$result = array(
				'status'   => 'failed',
				'redirect' => $next_step_url,
				'message'  => __( 'Oooops! Product\'s price is not correct.', 'cartflows-pro' ),
			);

			wcf()->logger->log( 'Order-' . $order_id . ' ' . $step_type . ' Offer Payment Failed. Product price is in negative format. Redirected to next step.' );

			return $result;
		}

		if ( isset( $offer_product['price'] ) && ( floatval( 0 ) === floatval( $offer_product['price'] ) || '' === trim( $offer_product['price'] ) ) ) {

			$is_charge_success = true;
		} else {

			$order_gateway = $order->get_payment_method();

			wcf()->logger->log( 'Order-' . $order->get_id() . ' ' . $order_gateway . ' - Payment gateway' );

			$gateway_obj = wcf_pro()->gateways->load_gateway( $order_gateway );

			if ( $gateway_obj ) {

				wcf()->logger->log( 'Order-' . $order->get_id() . ' Payment gateway charge' );
				$offer_product['action'] = $extra_data['action'];
				$is_charge_success       = $gateway_obj->process_offer_payment( $order, $offer_product );
			}
		}

		wcf()->logger->log( 'Is Charge Success - ' . $is_charge_success );

		if ( $is_charge_success ) {

			if ( 'upsell' === $step_type ) {
				/* Add Product To Main Order */
				wcf_pro()->order->add_upsell_product( $order, $offer_product );

			} else {
				wcf_pro()->order->add_downsell_product( $order, $offer_product );
			}

			do_action( 'cartflows_offer_accepted', $order, $offer_product );

			/**
			 * We need to reduce stock here.
			 *
			 * @todo
			 * reduce_stock();
			 */

			$data = array(
				'action'        => 'offer_accepted',
				'order_id'      => $order_id,
				'order_key'     => $order_key,
				'template_type' => $step_type,
			);

			/* Get Redirect URL */
			$next_step_url = wcf_pro()->flow->get_next_step_url( $step_id, $data );

			$result = array(
				'status'   => 'success',
				'redirect' => $next_step_url,
				'message'  => wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-order-success-text' ),
			);

			// Set meta-option to verify that this offer is accepted or rejected.
			$this->update_order_and_step_status(
				$order,
				array(
					'offer_status'    => $data['action'],
					'offer_step_id'   => $step_id,
					'offer_order_id'  => $order_id,
					'offer_order_key' => $order_key,
				)
			);

			wcf()->logger->log( 'Redirect URL : ' . $next_step_url );
			wcf()->logger->log( 'Order-' . $order_id . ' ' . $step_type . ' Offer accepted for step ID : ' . $step_id );
		} else {

			/* @todo if payment failed redirect to last page or not */
			$data = array(
				'order_id'  => $order_id,
				'order_key' => $order_key,
			);

			$thank_you_page_url = wcf_pro()->flow->get_thankyou_page_url( $step_id, $data );

			$result = array(
				'status'   => 'failed',
				'redirect' => $thank_you_page_url,
				'message'  => wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-order-failure-text' ),
			);

			wcf()->logger->log( 'Order-' . $order_id . ' ' . $step_type . ' Offer Payment Failed. Redirected to thankyou step.' );
		}

		wcf()->logger->log( 'End-' . __CLASS__ . '::' . __FUNCTION__ );

		return $result;
	}

	/**
	 * Offer rejected
	 *
	 * @param int   $step_id Flow step id.
	 * @param array $extra_data extra data.
	 * @param array $result process result.
	 * @since 1.0.0
	 */
	public function offer_rejected( $step_id, $extra_data, $result ) {

		/* Get Redirect URL */
		$next_step_url = wcf_pro()->flow->get_next_step_url( $step_id, $extra_data );

		$order_id  = $extra_data['order_id'];
		$step_type = $extra_data['template_type'];

		$order         = wc_get_order( $order_id );
		$offer_product = wcf_pro()->utils->get_offer_data( $step_id );

		$result = array(
			'status'   => 'success',
			'redirect' => $next_step_url,
			'message'  => __( 'Redirecting...', 'cartflows-pro' ),
		);

		wcf()->logger->log( 'Order-' . $order_id . ' ' . $step_type . ' Offer rejected' );

		do_action( 'cartflows_offer_rejected', $order, $offer_product );

		return $result;
	}

	/**
	 * Offer rejected
	 *
	 * @param int    $step_id Flow step id.
	 * @param object $order order data.
	 */
	public function maybe_skip_offer( $step_id, $order ) {

		$is_skip_offer = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-skip-offer', false );
		$billing_email = $order->get_billing_email();

		if ( 'yes' === $is_skip_offer && $billing_email ) {

			$offer_product    = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-product', false );
			$offer_product_id = $offer_product[0];

			$is_purchased = wc_customer_bought_product( $billing_email, get_current_user_id(), $offer_product_id );

			if ( $is_purchased ) {

				$wcf_step_obj     = wcf_pro_get_step( $step_id );
				$step_to_redirect = $wcf_step_obj->get_next_step_id();

				/* Get Redirect URL */
				$step_id = $this->get_next_step_id_for_skip_offer( $step_id, $step_to_redirect );

			}
		}

		return $step_id;
	}

	/**
	 * Get next step id for skipped offers.
	 *
	 * @param int $current_step_id step id.
	 * @param int $step_to_redirect default step id.
	 */
	public function get_next_step_id_for_skip_offer( $current_step_id, $step_to_redirect ) {

		$flow_id = wcf()->utils->get_flow_id_from_step_id( $current_step_id );
		$steps   = get_post_meta( $flow_id, 'wcf-steps', true );

		if ( $steps ) {

			$current_step_found = false;

			foreach ( $steps as $index => $step ) {

				if ( $current_step_found ) {

					if ( in_array( $step['type'], array( 'upsell', 'thankyou' ), true ) ) {

						$step_to_redirect = $step['id'];
						break;
					}
				} else {

					if ( intval( $step['id'] ) === $current_step_id ) {

						$current_step_found = true;
					}
				}
			}
		}

		return $step_to_redirect;

	}

	/**
	 * Update the order and step status in the step's post-meta.
	 *
	 * @param wc_order $order The current step ID.
	 * @param array    $offer_purchased_data The current offer data.
	 *
	 * @return void
	 */
	public function update_order_and_step_status( $order = '', $offer_purchased_data = '' ) {

		if ( ! empty( $order ) && ! empty( $offer_purchased_data ) ) {
			$order->update_meta_data( 'wcf_is_offer_purchased', wp_json_encode( $offer_purchased_data ) );
		}

	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Base_Offer_Markup::get_instance();
