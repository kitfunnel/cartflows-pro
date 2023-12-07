<?php
/**
 * Frontend & Markup
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Flow Markup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Flow_Frontend {


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

		if ( _is_cartflows_pluspro() ) {
			/* Analytics */
			add_action( 'wp_footer', array( $this, 'footer_markup' ) );
		}
	}

	/**
	 *  Footer markup
	 */
	public function footer_markup() {

		if ( _is_wcf_base_offer_type() ) {

			$open_class = '';

			// Reason for PHPCS ignore: This is used only to check the gateway has sent any response or not and used to print the HTML in the footer on WP action.
			if ( isset( $_GET['wcf-mollie-return'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$open_class = ' show ';
			}

			global $post;
			$process_order_text = wcf_pro()->options->get_offers_meta_value( $post->ID, 'wcf-offer-order-process-text' );
			$offer_success_note = wcf_pro()->options->get_offers_meta_value( $post->ID, 'wcf-offer-order-success-note' );
			// @codingStandardsIgnoreStart
			?>
			<div class="wcf-loader-bg <?php echo $open_class; ?>">
				<div class="wcf-loader-wrap">
					<div class="wcf-loader"><?php _e( 'Loading...', 'cartflows-pro' ); ?></div>
					<div class="wcf-order-msg">
						<p class="wcf-process-msg" ><?php echo esc_html( $process_order_text ) ?></p>
						<p class="wcf-note wcf-note-yes"><?php echo esc_html( $offer_success_note ); ?></p>
						<p class="wcf-note wcf-note-no"><?php _e( 'Please wait while we redirect you...', 'cartflows-pro' ); ?></p>
					</div>
				</div>
			</div>
			<?php
			// @codingStandardsIgnoreEnd
		}
	}

	/**
	 * Get next step URL.
	 *
	 * @since 1.0.0
	 * @param int   $step_id step ID.
	 * @param array $data order data.
	 *
	 * @return string
	 */
	public function get_next_step_url( $step_id, $data ) {

		$wcf_step_obj  = wcf_pro_get_step( $step_id );
		$step_id       = intval( $step_id );
		$flow_id       = $wcf_step_obj->get_flow_id();
		$flow_steps    = $wcf_step_obj->get_flow_steps();
		$control_step  = $wcf_step_obj->get_control_step();
		$link          = '#';
		$next_step_id  = false;
		$order_id      = $data['order_id'];
		$order_key     = $data['order_key'];
		$template_type = isset( $data['template_type'] ) ? $data['template_type'] : '';
		$session_key   = wcf_pro()->session->get_session_key( $flow_id );

		if ( ! $flow_id ) {
			return $link;
		}

		if ( 'upsell' === $template_type ) {

			if ( 'offer_accepted' === $data['action'] ) {
				$next_step_id = $this->get_next_step_id_for_upsell_accepted( $wcf_step_obj, $flow_steps, $step_id, $control_step );
			} else {
				$next_step_id = $this->get_next_step_id_for_upsell_rejected( $wcf_step_obj, $flow_steps, $step_id, $control_step );
			}
		} elseif ( 'downsell' === $template_type ) {

			if ( 'offer_accepted' === $data['action'] ) {
				$next_step_id = $this->get_next_step_id_for_downsell_accepted( $wcf_step_obj, $flow_steps, $step_id, $control_step );
			} else {
				$next_step_id = $this->get_next_step_id_for_downsell_rejected( $wcf_step_obj, $flow_steps, $step_id, $control_step );
			}
		} else {

			/* This is normal next step of flow */
			$next_step_id = $wcf_step_obj->get_next_step_id();
		}

		$order = wc_get_order( $order_id );

		if ( $order ) {
			$next_step_id = Cartflows_Pro_Base_Offer_Markup::get_instance()->maybe_skip_offer( $next_step_id, $order );
		}

		if ( $next_step_id ) {

			$this->may_be_complete_order( $next_step_id, $order_id );

			$query_args = array(
				'wcf-order' => $order_id,
				'wcf-key'   => $order_key,
			);

			if ( $session_key ) {
				$query_args['wcf-sk'] = $session_key;
			}

			// Add extra query strings of URL to the next step URL, if exists.
			$query_args = wcf_pro()->utils->may_be_append_query_string( $query_args );

			$link = add_query_arg( $query_args, get_permalink( $next_step_id ) );
		}

		return $link;
	}

	/**
	 * Normalize status if template is of type thank you page.
	 *
	 * @since 1.0.0
	 * @param int $next_step_id next step ID.
	 * @param int $order_id order id.
	 *
	 * @return void
	 */
	public function may_be_complete_order( $next_step_id, $order_id ) {

		wcf()->logger->log( 'Entering: ' . __CLASS__ . '::' . __FUNCTION__ );

		$template_type = get_post_meta( $next_step_id, 'wcf-step-type', true );

		if ( 'thankyou' === $template_type ) {

			$order = wc_get_order( $order_id );

			wcf_pro()->order->may_be_normalize_status( $order );
		}
	}

	/**
	 * Get next step id for upsell.
	 *
	 * @since 1.0.0
	 * @param object $wcf_step_obj step object.
	 * @param array  $steps        flow steps.
	 * @param int    $step_id      step ID.
	 * @param int    $control_step control step ID.
	 *
	 * @return int
	 */
	public function get_next_step_id_for_upsell_accepted( $wcf_step_obj, $steps, $step_id, $control_step ) {

		$next_step_id    = false;
		$next_step_index = false;

		if ( empty( $steps ) ) {
			return $next_step_id;
		} else {
			$next_yes_step = get_post_meta( $step_id, 'wcf-yes-next-step', true );

			if ( ! empty( $next_yes_step ) ) {

				$next_step_id = $next_yes_step;
			} else {

				foreach ( $steps as $i => $step ) {

					if ( intval( $step['id'] ) === $control_step ) {

						$next_step_index = $i + 1;
						break;
					}
				}

				while ( $next_step_index && isset( $steps[ $next_step_index ] ) ) {

					$temp_next_step_id       = $steps[ $next_step_index ]['id'];
					$temp_next_step_template = get_post_meta( $temp_next_step_id, 'wcf-step-type', true );

					if ( 'downsell' === $temp_next_step_template ) {
						$next_step_index++;
					} else {
						$next_step_id = $temp_next_step_id;
						break;
					}
				}
			}
		}

		return $next_step_id;
	}

	/**
	 * Get next step id for upsell rejected.
	 *
	 * @since 1.0.0
	 * @param object $wcf_step_obj step object.
	 * @param array  $steps        flow steps.
	 * @param int    $step_id      step ID.
	 * @param int    $control_step control step ID.
	 *
	 * @return int
	 */
	public function get_next_step_id_for_upsell_rejected( $wcf_step_obj, $steps, $step_id, $control_step ) {

		$next_step_id = false;

		if ( empty( $steps ) ) {
			return $next_step_id;
		} else {

			$next_no_step = get_post_meta( $step_id, 'wcf-no-next-step', true );

			if ( ! empty( $next_no_step ) ) {
				$next_step_id = $next_no_step;
			} else {

				$next_step_id = $wcf_step_obj->get_next_step_id();
			}
		}

		return $next_step_id;
	}

	/**
	 * Get next step id for downsell accepted.
	 *
	 * @since 1.0.0
	 * @param object $wcf_step_obj step object.
	 * @param array  $steps        flow steps.
	 * @param int    $step_id      step ID.
	 * @param int    $control_step control step ID.
	 *
	 * @return int
	 */
	public function get_next_step_id_for_downsell_accepted( $wcf_step_obj, $steps, $step_id, $control_step ) {

		$next_step_id = false;

		if ( empty( $steps ) ) {
			return $next_step_id;
		} else {
			$next_yes_step = get_post_meta( $step_id, 'wcf-yes-next-step', true );

			if ( ! empty( $next_yes_step ) ) {
				$next_step_id = $next_yes_step;
			} else {

				$next_step_id = $wcf_step_obj->get_next_step_id();
			}
		}

		return $next_step_id;
	}

	/**
	 * Get next step id for downsell rejected.
	 *
	 * @since 1.0.0
	 * @param object $wcf_step_obj step object.
	 * @param array  $steps        flow steps.
	 * @param int    $step_id      step ID.
	 * @param int    $control_step control step ID.
	 *
	 * @return int
	 */
	public function get_next_step_id_for_downsell_rejected( $wcf_step_obj, $steps, $step_id, $control_step ) {

		$next_step_id = false;

		if ( empty( $steps ) ) {
			return $next_step_id;
		} else {

			$next_no_step = get_post_meta( $step_id, 'wcf-no-next-step', true );

			if ( ! empty( $next_no_step ) ) {
				$next_step_id = $next_no_step;
			} else {

				$next_step_id = $wcf_step_obj->get_next_step_id();
			}
		}

		return $next_step_id;
	}

	/**
	 * Get thank you page URL.
	 *
	 * @since 1.0.0
	 * @param int   $step_id step ID.
	 * @param array $data order data.
	 *
	 * @return string
	 */
	public function get_thankyou_page_url( $step_id, $data ) {

		$wcf_step_obj = wcf_pro_get_step( $step_id );
		$step_id      = intval( $step_id );
		$flow_id      = $wcf_step_obj->get_flow_id();
		$link         = '#';
		$order_id     = $data['order_id'];
		$order_key    = $data['order_key'];

		if ( ! $flow_id ) {
			return $link;
		}

		$thankyou_id = $wcf_step_obj->get_thankyou_page_id();

		if ( $thankyou_id ) {

			$this->may_be_complete_order( $thankyou_id, $order_id );

			$link = add_query_arg(
				array(
					'wcf-order' => $order_id,
					'wcf-key'   => $order_key,
				),
				get_permalink( $thankyou_id )
			);
		}

		return $link;
	}

	/**
	 * Check if upsell exists.
	 *
	 * @since 1.0.0
	 * @param object $order order data.
	 * @param int    $flow_id Flow Id.
	 * @param int    $step_id Step Id.
	 *
	 * @return bool
	 */
	public function check_if_next_step_is_offer( $order, $flow_id = 0, $step_id = 0 ) {

		$offer_step_exist = false;

		if ( $order ) {
			$step_id = wcf()->utils->get_checkout_id_from_order( $order );
		}

		if ( ! $flow_id ) {
			$flow_id = wcf()->utils->get_flow_id_from_order( $order );
		}

		$is_offer_exist_in_flow = $this->is_upsell_exist_in_flow( $flow_id, $step_id );

		if ( ! $is_offer_exist_in_flow ) {
			return $offer_step_exist;
		}

		$wcf_step_obj = wcf_pro_get_step( $step_id );
		$next_step_id = $wcf_step_obj->get_next_step_id();

		// Check if is next step upsell.
		$is_dynamic_rules = wcf()->options->get_checkout_meta_value( $step_id, 'wcf-checkout-rules-option' );

		if ( 'no' === $is_dynamic_rules ) {
			$wcf_next_step_obj = wcf_pro_get_step( $next_step_id );
			$offer_step_exist  = $wcf_next_step_obj->is_offer_page();
		} else {

			$next_step_id = apply_filters( 'cartflows_checkout_next_step_id', $next_step_id, $order, $step_id );

			$wcf_next_step_obj = wcf_pro_get_step( $next_step_id );
			$offer_step_exist  = $wcf_next_step_obj->is_offer_page();
		}

		return $offer_step_exist;
	}

	/**
	 * Check if upsell exists in flow.
	 *
	 * @since x.x.x
	 * @param int $flow_id Flow Id.
	 * @param int $step_id Step Id.
	 *
	 * @return bool
	 */
	public function is_upsell_exist_in_flow( $flow_id, $step_id ) {

		$offer_step_exist = false;

		if ( $flow_id && $step_id ) {

			$wcf_step_obj = wcf_pro_get_step( $step_id );

			$flow_steps = $wcf_step_obj->get_flow_steps();

			$control_step_id = $wcf_step_obj->get_control_step();

			if ( is_array( $flow_steps ) ) {

				$current_step_found = false;

				foreach ( $flow_steps as $index => $data ) {

					if ( $current_step_found ) {
						if ( in_array( $data['type'], array( 'upsell', 'downsell' ), true ) ) {

							$offer_step_exist = true;
							break;
						} elseif ( 'thankyou' === $data['type'] ) {
							break;
						}
					} else {
						// Check for the control step.
						if ( intval( $data['id'] ) === intval( $control_step_id ) ) {
							$current_step_found = true;
						}
					}
				}
			}
		}

		return $offer_step_exist;
	}

	/**
	 * Actions after offer charge completes.
	 *
	 * @since 1.0.0
	 * @param array $step_id step id.
	 * @param array $order_id order id.
	 * @param array $order_key order key.
	 * @param array $is_charge_success is charge successful.
	 * @param int   $variation_id variation id.
	 * @param int   $input_qty input qty.
	 *
	 * @return array
	 */
	public function after_offer_charge( $step_id, $order_id, $order_key, $is_charge_success = false, $variation_id = '', $input_qty = '' ) {

		$result = array();

		$step_type = wcf()->utils->get_step_type( $step_id );

		if ( $is_charge_success ) {

			$order = wc_get_order( $order_id );

			$offer_product = wcf_pro()->utils->get_offer_data( $step_id, $variation_id, $input_qty, $order_id );

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

		return $result;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Flow_Frontend::get_instance();
