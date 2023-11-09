<?php
/**
 * Cartflows Frontend.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Frontend.
 */
class Cartflows_Pro_Frontend {

	/**
	 * Member Variable
	 *
	 * @var instance
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
	 * Constructor
	 */
	public function __construct() {

		/* Set / Destroy Flow Sessions. Set data */
		add_action( 'wp', array( $this, 'init_actions' ), 1 );

		/* Enqueue global required scripts */
		add_action( 'wp', array( $this, 'wp_actions' ), 55 );

		/* Redirect to next step in flow if next step is other than thank you */
		add_action( 'cartflows_order_started', array( $this, 'set_next_step_url' ) );

		if ( _is_cartflows_pluspro() ) {

			/* Setup Upsell for for payment gatways. Only if we are in out flow */
			add_action( 'woocommerce_pre_payment_complete', array( $this, 'maybe_setup_upsell' ), 99, 1 );
			/* Setup upsell for other gatways which are not covered in "woocommerce_pre_payment_complete" hook */
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'maybe_setup_upsell_ignore_gateways' ), 100, 3 );

			add_action( 'cartflows_order_status_change_to_main_order', array( $this, 'register_cron_for_order_success' ), 10, 3 );
			add_action( 'cartflows_order_status_change_to_main_order', array( $this, 'update_main_order_data_in_transient' ), 10, 3 );

			add_action( 'wp_head', array( $this, 'render_offer_accept_reject_scripts' ) );
			add_filter( 'cartflows_dynamic_js_vars', array( $this, 'maybe_replace_pro_vars' ), 10, 1 );
		}
	}

	/**
	 * Set next step url.
	 *
	 * @param object $order order object.
	 * @since 1.0.0
	 */
	public function set_next_step_url( $order ) {

		/* Modify the checkout order received url to go thank you page in our flow */
		remove_filter( 'woocommerce_get_checkout_order_received_url', array( Cartflows_Frontend::get_instance(), 'redirect_to_thankyou_page' ), 10, 2 );

		add_filter( 'woocommerce_get_checkout_order_received_url', array( $this, 'show_offer_step' ), 10, 2 );
	}

	/**
	 * Show offer step.
	 *
	 * @param string $order_recieve_url recieve url.
	 * @param object $order order object.
	 * @since 1.0.0
	 */
	public function show_offer_step( $order_recieve_url, $order ) {

		wcf()->logger->log( 'Start-' . __CLASS__ . '::' . __FUNCTION__ );

		if ( $order && $order->get_status() !== 'failed' ) {

			if ( _is_wcf_doing_checkout_ajax() ) {

				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();

				if ( ! $checkout_id ) {
					$checkout_id = wcf()->utils->get_checkout_id_from_order( $order );
				}
			} else {
				$checkout_id = wcf()->utils->get_checkout_id_from_order( $order );
			}

			wcf()->logger->log( 'Checkout ID: ' . $checkout_id );

			if ( $checkout_id ) {

				$wcf_step_obj = wcf_pro_get_step( $checkout_id );
				$next_step_id = $wcf_step_obj->get_next_step_id();
				$flow_id      = $wcf_step_obj->get_flow_id();

				$next_step_id = apply_filters( 'cartflows_checkout_next_step_id', $next_step_id, $order, $checkout_id );

				if ( $next_step_id ) {

					$order_recieve_url = get_permalink( $next_step_id );

					$session_key = wcf_pro()->session->get_session_key( $flow_id );

					$query_args = array(
						'wcf-order' => $order->get_id(),
						'wcf-key'   => $order->get_order_key(),
					);

					if ( $session_key ) {
						$query_args['wcf-sk'] = $session_key;
					}

					$order_recieve_url = add_query_arg( $query_args, $order_recieve_url );
				}
			}
		}

		wcf()->logger->log( 'End-' . __CLASS__ . '::' . __FUNCTION__ );

		return $order_recieve_url;
	}

	/**
	 * Register Cron.
	 *
	 * @param string $new_status new status.
	 * @param string $normal_status normal status.
	 * @param object $order order object.
	 * @since 1.0.0
	 */
	public function register_cron_for_order_success( $new_status, $normal_status, $order ) {

		if ( false === is_a( $order, 'WC_Order' ) ) {

			/* Not Valid Order */
			wcf()->logger->log( 'Not a valid order' );

			return;
		}

		wcf()->logger->log( 'register_cron_for_order_success' );
		wcf()->logger->log( 'new-status - ' . $new_status );

		if ( wcf_pro()->order->get_order_status_slug() !== $new_status ) {

			/* Not Valid Order Status */
			wcf()->logger->log( 'Not a valid order status' );

			return;
		}

		$args = array(
			'order_id'      => $order->get_id(),
			'before_normal' => $order->get_status(), // Pending.
			'normal_status' => $normal_status, // On Hold/Processing etc.
		);

		if ( false === wp_next_scheduled( 'carflows_schedule_normalize_order_status', $args ) ) {

			/* Filter to change the cron time */

			$cron_time = apply_filters( 'cartflows_order_status_cron_time', 30 );

			/* Setup Schedule */

			wp_schedule_single_event( time() + ( $cron_time * MINUTE_IN_SECONDS ), 'carflows_schedule_normalize_order_status', $args );

			wcf()->logger->log( 'Order-' . $order->get_id() . ' Cron Scheduled for Normalize Order Status' );
		}
	}

	/**
	 * Update main order data in transient.
	 *
	 * @param string $new_status new status.
	 * @param string $normal_status normal status.
	 * @param object $order order object.
	 * @since 1.0.0
	 */
	public function update_main_order_data_in_transient( $new_status, $normal_status, $order ) {

		if ( false === is_a( $order, 'WC_Order' ) ) {

			/* Not Valid Order */
			wcf()->logger->log( 'Not a valid order' );

			return;
		}

		wcf()->logger->log( 'new-status - ' . $new_status );

		if ( wcf_pro()->order->get_order_status_slug() !== $new_status ) {

			/* Not Valid Order Status */
			wcf()->logger->log( 'Not a valid order status' );

			return;
		}

		$flow_id = $order->get_meta( '_wcf_flow_id' );

		$data = array(
			'order_id'      => $order->get_id(),
			'before_normal' => $order->get_status(), // Pending.
			'normal_status' => $normal_status, // On Hold/Processing etc.
		);

		wcf()->logger->log( 'Gateway status change - Flow-' . $flow_id . ' Order-' . $order->get_id() . wp_json_encode( $data ) );

		wcf_pro()->session->update_data( $flow_id, $data );

		/* Add status change key order */
		$order->update_meta_data( '_cartflows_order_status_change', $data );
		$order->save();
	}

	/**
	 * Init Actions.
	 *
	 * @since 1.0.0
	 */
	public function init_actions() {

		$this->set_flow_session();
	}

	/**
	 * Set flow session.
	 *
	 * @since 1.0.0
	 */
	public function set_flow_session() {

		if ( wcf()->utils->is_step_post_type() ) {

			wcf()->utils->do_not_cache();

			$flow_id = wcf()->utils->get_flow_id();

			if ( ! $flow_id ) {
				return;
			}

			if ( _is_wcf_thankyou_type() ) {

				// Destroy Session On Thank You Page.
				wcf_pro()->session->destroy_session( $flow_id );

			} elseif ( _is_wcf_landing_type() || _is_wcf_checkout_type() ) {

				$data = array(
					'flow_id' => $flow_id,
					'steps'   => get_post_meta( $flow_id, 'wcf-steps', true ),
				);

				wcf_pro()->session->set_session( $flow_id, $data );

			} elseif ( _is_wcf_upsell_type() || _is_wcf_downsell_type() ) {

				if ( wcf()->flow->is_flow_testmode( $flow_id ) ) {
					return;
				}

				if ( ! ( is_user_logged_in() && current_user_can( 'cartflows_manage_flows_steps' ) ) ) {

					if ( ! wcf_pro()->session->is_active_session( $flow_id ) ) {
						wp_die( esc_html__( 'Your session is expired', 'cartflows-pro' ) );
					}
				}
			}
		}

	}

	/**
	 * Setup upsell common. Used for paypal standard.
	 *
	 * @param int $order_id Order id.
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function setup_upsell( $order_id = '' ) {

		wcf()->logger->log( 'Force setup upsell' );

		if ( '' == $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );

		$this->start_the_upsell_flow( $order );
	}

	/**
	 * Maybe setup upsell.
	 *
	 * @param int $order_id Order id.
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function maybe_setup_upsell( $order_id = '' ) {

		wcf()->logger->log( ' woocommerce_pre_payment_complete ' );

		if ( '' == $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );

		$this->start_the_upsell_flow( $order );

	}

	/**
	 * Ignore Gateways checkout processed.
	 *
	 * @param int    $order_id order id.
	 * @param array  $posted_data post data.
	 * @param object $order order object.
	 * @since 1.0.0
	 */
	public function maybe_setup_upsell_ignore_gateways( $order_id, $posted_data, $order ) {
		wcf()->logger->log( ' woocommerce_checkout_order_processed ' );

		if ( '' == $order_id ) {
			return;
		}

		// Added here again to solve the issue: Some times checkout was redirecting to default thank you page instead of upsell/downsell.
		$order_gateway = $order->get_payment_method();

		$gateways = array( 'cod', 'bacs', 'stripe', 'ppec_paypal', 'ppcp-gateway', 'mollie_wc_gateway_ideal', 'mollie_wc_gateway_creditcard', 'cpsw_stripe', 'square_credit_card', 'cppw_paypal' );
		$gateways = apply_filters( 'cartflows_offer_supported_payment_gateway_slugs', $gateways );

		if ( in_array( $order_gateway, $gateways, true ) ) {

			$this->start_the_upsell_flow( $order );
		}
		// Added here again to solve the issue: Some times checkout was redirecting to default thank you page instead of upsell/downsell.
	}

	/**
	 * Start the upsell flow.
	 *
	 * @param object $order Order object.
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function start_the_upsell_flow( $order ) {

		if ( ! is_object( $order ) ) {

			wcf()->logger->log( 'Not valid order' );
		}

		if ( ! wcf_pro()->flow->check_if_next_step_is_offer( $order ) ) {

			wcf()->logger->log( 'Order-' . $order->get_id() . ' Upsell not exists' );

			return;
		}

		if ( ! class_exists( 'Cartflows_Pro_Gateways' ) ) {
			return;
		}

		$order_gateway = $order->get_payment_method();

		$gateway_obj = wcf_pro()->gateways->load_gateway( $order_gateway );

		if ( $gateway_obj ) {

			wcf()->logger->log( 'Order-' . $order->get_id() . ' Flow Started' );

			/* Checkout recive url filter */
			do_action( 'cartflows_order_started', $order );

		} else {
			wcf()->logger->log( 'Order-' . $order->get_id() . ' Gateway object not found' );
		}
	}

	/**
	 * Set upsell and return new status based on condition.
	 *
	 * @since 1.5.5
	 * @param string $order_status order status.
	 * @param object $order order data.
	 * @return string
	 */
	public function set_upsell_return_new_order_status( $order_status, $order ) {

		if ( false === is_a( $order, 'WC_Order' ) ) {

			// Create Log.
			wcf()->logger->log( 'Not a valid order' );

			return $order_status;
		}

		$flow_id = $order->get_meta( '_wcf_flow_id' );

		if ( ! wcf_pro()->flow->check_if_next_step_is_offer( $order ) ) {

			wcf()->logger->log( 'Flow-' . $flow_id . ' Order-' . $order->get_id() . ' Upsell not exists' );

			return $order_status;
		}

		do_action( 'cartflows_order_started', $order );

		/* If offer order is separate then don't change main order status */
		if ( ! wcf_pro()->utils->is_separate_offer_order() ) {

			$new_status = wcf_pro()->order->get_order_status_slug();

			$data = array(
				'flow_id'  => $flow_id,
				'order_id' => $order->get_id(),
			);

			wcf_pro()->session->set_session( $flow_id, $data );

			/**
			 * $new_status = our new status
			 * $order_status = default status change
			 */
			do_action( 'cartflows_order_status_change_to_main_order', $new_status, $order_status, $order );

			wcf()->logger->log( 'Flow-' . $flow_id . ' Order-' . $order->get_id() . ' Status changed to Main Order' );

			return $new_status;
		} else {
			wcf()->logger->log( 'Flow-' . $flow_id . ' Order-' . $order->get_id() . ' No need to change Status. Separate order option is set' );
		}

		return $order_status;
	}

	/**
	 * Render offer accepted/rejected script on upsell/downsell pages.
	 */
	public function render_offer_accept_reject_scripts() {

		global $post;

		if ( $post && wcf()->utils->is_step_post_type() ) {

			/**
			 * Reason for PHPCS ignore: Printing the JS script in the head part of the page.
			 * Used only to confirm the order is placed and the user is on CartFlows offer page.
			 * */
			$order_id = isset( $_GET['wcf-order'] ) ? sanitize_text_field( wp_unslash( $_GET['wcf-order'] ) ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( 0 === $order_id ) {
				return;
			}

			$offer_accept_script      = '';
			$offer_reject_script      = '';
			$offer_accept_script_html = '';
			$offer_reject_script_html = '';

			$step_id   = $post->ID;
			$step_type = wcf_get_step_type( $step_id );

			$offer_accept_script = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-accept-script' );
			$offer_reject_script = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-reject-script' );

			if ( '' !== $offer_accept_script ) {

				if ( false === strpos( $offer_accept_script, htmlentities( '<script' ) ) ) {
					$offer_accept_script_html = '<script type="text/javascript" id="wcf_offer_accepted_trigger"> document.addEventListener("wcf_' . $step_type . '_accepted", function(e) {
						const event_data = e.detail; ';

					$offer_accept_script_html .= $this->may_be_replace_shortcodes( $offer_accept_script );

					$offer_accept_script_html .= '}, false );</script>';
				}

				echo '<!-- Start CartFlows offer accept custom Script -->';
				echo html_entity_decode( $offer_accept_script_html ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<!-- End CartFlows offer accept custom Script -->';
			}

			if ( '' !== $offer_reject_script ) {

				if ( false === strpos( $offer_reject_script, htmlentities( '<script' ) ) ) {
					$offer_reject_script_html = '<script type="text/javascript" id="wcf_offer_rejected_trigger"> document.addEventListener("wcf_' . $step_type . '_rejected", function(e) {
						const event_data = e.detail; ';

					$offer_reject_script_html .= $this->may_be_replace_shortcodes( $offer_reject_script );

					$offer_reject_script_html .= '}, false );</script>';
				}

				echo '<!-- Start CartFlows offer reject custom Script -->';
				echo html_entity_decode( $offer_reject_script_html ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<!-- End CartFlows offer reject custom Script -->';
			}
		}
	}

	/**
	 * WP Actions.
	 *
	 * @since 1.0.0
	 */
	public function wp_actions() {

		if ( wcf()->utils->is_step_post_type() ) {

			add_action( 'wp_enqueue_scripts', array( $this, 'global_flow_scripts' ), 20 );

			/* Add pro version class to body frontend */
			add_filter( 'body_class', array( $this, 'pro_body_class' ) );
		}
	}

	/**
	 * Global flow scripts.
	 *
	 * @since 1.0.0
	 */
	public function global_flow_scripts() {

		if ( wcf()->utils->is_step_post_type() ) {

			wp_enqueue_style(
				'wcf-pro-frontend-global',
				wcf_pro()->utils->get_css_url( 'frontend' ),
				array(),
				CARTFLOWS_PRO_VER
			);

			wp_enqueue_script(
				'wcf-pro-frontend-global',
				wcf_pro()->utils->get_js_url( 'frontend' ),
				array( 'jquery' ),
				CARTFLOWS_PRO_VER,
				false
			);

			if ( ! ( is_user_logged_in() && current_user_can( 'cartflows_manage_flows_steps' ) ) ) {

				$exper_flow_analytics = Cartflows_Helper::get_admin_settings_option( '_cartflows_experimental_flow_analytics', false );

				if ( 'enable' === $exper_flow_analytics ) {

					wp_enqueue_script(
						'wcf-pro-analytics-global',
						wcf_pro()->utils->get_js_url( 'analytics' ),
						array( 'jquery' ),
						CARTFLOWS_PRO_VER,
						false
					);
				}
			}
		}
	}

	/**
	 * Replace JS vars for offer pages.
	 *
	 * @param string $script custom script.
	 * @return string $script modified custom script.
	 *
	 * @since x.x.x
	 */
	public function maybe_replace_pro_vars( $script ) {

		if ( _is_wcf_base_offer_type() ) {

			global $post;

			$post_id = false;

			if ( $post ) {
				$post_id = $post->ID;
			}

			if ( ! $post_id ) {
				return $script;
			}

			$offer_product = wcf_pro()->utils->get_offer_data( $post_id );

			if ( empty( $offer_product ) || ! is_array( $offer_product ) ) {
				return $script;
			}

			$script = str_replace( '{{offer_product_name}}', $offer_product['name'], $script );
			$script = str_replace( '{{offer_product_qty}}', $offer_product['qty'], $script );
			$script = str_replace( '{{offer_product_price}}', $offer_product['price'], $script );
		}

		return $script;
	}

	/**
	 * Add pro version class to body in frontend.
	 *
	 * @since 1.1.5
	 * @param array $classes classes.
	 * @return array $classes classes.
	 */
	public function pro_body_class( $classes ) {

		$classes[] = ' cartflows-pro-' . CARTFLOWS_PRO_VER;

		return $classes;
	}

	/**
	 * Function to replace the shortcode available in the offer accept/reject script.
	 *
	 * @param string $script Offer accept/reject script.
	 *
	 * @return string $script Modified string of script.
	 */
	public function may_be_replace_shortcodes( $script ) {

		// List of shortcodes to be used in the offer scripts.
		$shortcodes_to_replace = array(
			'{{order_id}}',
			'{{flow_id}}',
			'{{step_id}}',
			'{{product_id}}',
			'{{variation_id}}',
			'{{quantity}}',
			'{{offer_type}}',
		);

		// Array of JS variables to replace in the script.
		$array_map = array(
			'event_data.order_id',
			'event_data.flow_id',
			'event_data.step_id',
			'event_data.product_id',
			'event_data.variation_id',
			'event_data.input_qty',
			'event_data.offer_type',
		);

		// Replace the shortcodes available in the script.
		$script = str_replace( $shortcodes_to_replace, $array_map, $script );

		// Return the modified script.
		return $script;
	}

}

/**
 *  Prepare if class 'Cartflows_Pro_Frontend' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Frontend::get_instance();
