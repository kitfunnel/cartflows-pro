<?php
/**
 * CartFlows Admin Menu.
 *
 * @package CartFlows
 */

namespace CartflowsProAdmin\AdminCore\Inc;

use CartflowsProAdmin\AdminCore\Ajax\FlowAnalytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Admin_Menu.
 */
class AdminHooks {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->initialize_hooks();
	}

	/**
	 * Init Hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function initialize_hooks() {
		add_filter( 'cartflows_admin_localized_vars', array( $this, 'localize_required_vars' ), 10, 1 );

		add_action( 'cartflows_admin_save_step_meta', array( $this, 'save_step_actions' ), 10, 1 );

		add_filter( 'cartflows_admin_flows_step_data', array( $this, 'add_flow_steps_data' ), 10, 1 );
		/* Start-Pro-Woo-Feature */
		add_action( 'cartflows_admin_log', array( $this, 'show_pro_log' ), 10, 1 );
		/* End-Pro-Woo-Feature */

		add_filter( 'cartflows_admin_flows_page_data', array( $this, 'add_analytics_data_of_flow' ), 10, 1 );

		add_filter( 'cartflows_admin_flow_data', array( $this, 'add_flow_analytics_data' ), 999, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'add_debugger_styles' ) );
	}

	/**
	 *  Add license debug page CSS.
	 *
	 * @return void
	 */
	public function add_debugger_styles() {
		wp_enqueue_style( 'cartflows-license-debug', CARTFLOWS_PRO_URL . 'admin/assets/css/debugger.css', array(), CARTFLOWS_PRO_VER );
	}


	/**
	 * Add pro analytics data.
	 *
	 * @param array $flow_data flow data.
	 * @param int   $flow_id flow id.
	 *
	 * @return array
	 */
	public function add_flow_analytics_data( $flow_data, $flow_id ) {

		if ( ! wcf()->is_woo_active ) {
			return $flow_data;
		}

		$start_date = gmdate( 'Y-m-d', strtotime( '-7 days' ) );
		$end_date   = gmdate( 'Y-m-d' );

		$analytics_data = FlowAnalytics::get_instance()->flow_analytics_data( $flow_id, $start_date, $end_date );

		$analytics_data = array(
			'revenue'   => $analytics_data['earning'],
			'all_steps' => $analytics_data['visits'],
			'currency'  => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '',
		);

		$flow_data['flow_analytics'] = $analytics_data;

		return $flow_data;
	}

	/* Start-Pro-Woo-Feature */
	/**
	 * Add flow analytics data.
	 *
	 * @param array $page_data flow data.
	 *
	 * @return array
	 */
	public function add_analytics_data_of_flow( $page_data ) {

		if ( wcf()->is_woo_active && ! empty( $page_data ) && isset( $page_data['items'] ) && ! empty( $page_data['items'] ) ) {

			$report = \Cartflows_Pro_Analytics_Reports::get_instance();

			foreach ( $page_data['items'] as $index => $flow ) {
				$page_data['items'][ $index ]['revenue'] = str_replace( '&nbsp;', '', wc_price( $report->get_gross_sale_by_flow( $flow['ID'] ) ) );
			}
		}

		return $page_data;
	}

	/* Start-Pro-Woo-Feature */
	/**
	 * Show pro log.
	 *
	 * @param string $log_name log name.
	 */
	public function show_pro_log( $log_name ) {

		if ( 'wcf-license' === $log_name ) {
			include_once CARTFLOWS_PRO_DIR . 'admin-core/inc/cartflows-license-log.php';
		}
	}
	/* End-Pro-Woo-Feature */

	/**
	 * Add pro flows step data.
	 *
	 * @param array $steps flow steps.
	 */
	public function add_flow_steps_data( $steps ) {

		$is_checkout = 0;
		$step_length = count( $steps );

		foreach ( $steps as $in => $step ) {

			if ( 'checkout' === $step['type'] ) {

				$is_checkout++;

				$is_rules = get_post_meta( $step['id'], 'wcf-checkout-rules-option', true );

				if ( 'yes' === $is_rules ) {

					$steps[ $in ]['conditions'] = array();

					$conditions = get_post_meta( $step['id'], 'wcf-checkout-rules', true );

					if ( ! empty( $conditions ) ) {
						foreach ( $conditions as $group_data ) {
							if ( '' === $group_data['step_id'] ) {
								$wcf_step_obj          = wcf_pro_get_step( $step['id'] );
								$group_data['step_id'] = $wcf_step_obj->get_next_step_id();
							}

							$steps[ $in ]['conditions'][] = $group_data;
						}
					}

					$default_step = get_post_meta( $step['id'], 'wcf-checkout-rules-default-step', true );

					if ( '' === $default_step ) {
						$wcf_step_obj = wcf_pro_get_step( $step['id'] );
						$default_step = $wcf_step_obj->get_next_step_id();
					}

					$steps[ $in ]['default_step'] = $default_step;
				}
			}

			if ( in_array( $step['type'], array( 'upsell', 'downsell' ), true ) ) {

				$is_thankyou = 0;

				// Check if next remaining steps has thank you page.
				for ( $i = $in; $i < $step_length; $i++ ) {
					if ( 'thankyou' === $steps[ $i ]['type'] ) {
						$is_thankyou++;
					}
				}

				if ( $is_checkout > 0 && $is_thankyou > 0 ) {

					$wcf_step_obj = wcf_pro_get_step( $step['id'] );
					$flow_steps   = $wcf_step_obj->get_flow_steps();
					$control_step = $wcf_step_obj->get_control_step();
					if ( 'upsell' === $step['type'] ) {
						$next_yes_steps = wcf_pro()->flow->get_next_step_id_for_upsell_accepted( $wcf_step_obj, $flow_steps, $step['id'], $control_step );
						$next_no_steps  = wcf_pro()->flow->get_next_step_id_for_upsell_rejected( $wcf_step_obj, $flow_steps, $step['id'], $control_step );
					}

					if ( 'downsell' === $step['type'] ) {
						$next_yes_steps = wcf_pro()->flow->get_next_step_id_for_downsell_accepted( $wcf_step_obj, $flow_steps, $step['id'], $control_step );
						$next_no_steps  = wcf_pro()->flow->get_next_step_id_for_downsell_rejected( $wcf_step_obj, $flow_steps, $step['id'], $control_step );
					}

					if ( ! empty( $next_yes_steps ) && false !== get_post_status( $next_yes_steps ) ) {

						$yes_label = __( 'YES : ', 'cartflows-pro' ) . get_the_title( $next_yes_steps );
					} else {
						$yes_label = __( 'YES : Step not Found', 'cartflows-pro' );
					}

					if ( ! empty( $next_no_steps ) && false !== get_post_status( $next_no_steps ) ) {

						$no_label = __( 'No : ', 'cartflows-pro' ) . get_the_title( $next_no_steps );
					} else {
						$no_label = __( 'No : Step not Found', 'cartflows-pro' );
					}

					$steps[ $in ]['offer_yes_next_step'] = $yes_label;
					$steps[ $in ]['offer_no_next_step']  = $no_label;
					$steps[ $in ]['offer_yes_step_id']   = intval( $next_yes_steps );
					$steps[ $in ]['offer_no_step_id']    = intval( $next_no_steps );
				}
			}
		}

		return $steps;
	}


	/**
	 * Save step pro action.
	 *
	 * @param int $step_id step id.
	 */
	public function save_step_actions( $step_id ) {

		delete_post_meta( $step_id, 'wcf-pro-dynamic-css' );
	}

	/**
	 * Get payment gateways.
	 *
	 * @param array $localize localized variables.
	 */
	public function localize_required_vars( $localize ) {

		if ( ! wcf_pro()->is_woo_active ) {
			return $localize;
		}

		$product_id = \CartFlows_Pro_Licence::get_instance()->product_id;

		$localize['cf_pro_type']                = CARTFLOWS_PRO_PLUGIN_TYPE;
		$localize['license_status']             = get_option( 'wc_am_client_' . $product_id . '_activated', '' );
		$localize['license_activation_nonce']   = wp_create_nonce( 'cartflows_license_activation_nonce' );
		$localize['license_deactivation_nonce'] = wp_create_nonce( 'cartflows_license_deactivation_nonce' );
		$localize['is_order_bump_migrated']     = get_option( 'wcf_order_bump_migrated', false );

		return $localize;
	}
}

AdminHooks::get_instance();
