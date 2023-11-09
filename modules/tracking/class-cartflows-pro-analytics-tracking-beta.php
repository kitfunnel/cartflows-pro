<?php
/**
 * Flow
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'CARTFLOWS_TRACKING_DIR', CARTFLOWS_PRO_DIR . 'modules/tracking/' );
define( 'CARTFLOWS_TRACKING_URL', CARTFLOWS_PRO_URL . 'modules/tracking/' );

/**
 * Class for analytics tracking.
 */
class Cartflows_Pro_Analytics_Tracking_Beta {

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
	 * Constructor function that initializes required actions and hooks
	 */
	public function __construct() {

		if ( ! is_admin() ) {
			add_filter( 'global_cartflows_js_localize', array( $this, 'add_localize_vars' ), 10, 1 );
		}

		if ( ! ( is_user_logged_in() && current_user_can( 'cartflows_manage_flows_steps' ) ) ) {
			add_action( 'cartflows_offer_accepted', array( $this, 'save_offer_conversion' ), 20, 2 );
			add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		}
	}

	/**
	 * Register analytics API route.
	 */
	public function register_routes() {

		$namespace = 'cartflows-pro/v1';
		$rest_base = '/flow-analytics/';

		register_rest_route(
			$namespace,
			$rest_base,
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'save_analytics_data' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Save analytics data.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 */
	public function save_analytics_data( $request ) {

		$data = $request->get_body();

		if ( ! empty( $data ) ) {
			$data = json_decode( $data, true );
		}

		$current_flow     = isset( $data['flow_id'] ) ? intval( $data['flow_id'] ) : false;
		$current_step     = isset( $data['step_id'] ) ? intval( $data['step_id'] ) : false;
		$is_returning     = isset( $data['is_returning'] ) ? intval( $data['is_returning'] ) : '';
		$flow_cookie_data = isset( $data['flow_cookie_data'] ) ? json_decode( $data['flow_cookie_data'], true ) : array();
		$step_cookie_data = isset( $data['step_cookie_data'] ) && ! empty( $data['step_cookie_data'] ) ? json_decode( wp_unslash( $data['step_cookie_data'] ), true ) : array();

		if ( ! $current_flow || ! $current_step ) {
			return;
		}

		$analytics = wcf()->options->get_flow_meta_value( $current_flow, 'wcf-enable-analytics' );

		if ( 'no' === $analytics ) {
			return;
		}

		$wcf_step_obj    = wcf_pro_get_step( $current_step );
		$flow_id         = $wcf_step_obj->get_flow_id();
		$prev_control_id = $wcf_step_obj->get_prev_control_id_for_analytics( $flow_id, $flow_cookie_data );

		$this->save_conversion_data( $flow_id, $prev_control_id, $step_cookie_data );
		$current_step_visit_data = $this->save_visit( $current_step, $is_returning );

		$response = array(
			'success'            => true,
			'prev_control_id'    => $prev_control_id,
			'current_step_visit' => wp_json_encode( $current_step_visit_data ),
		);

		$response = rest_ensure_response( $response );
		return $response;
	}

	/**
	 * Save conversion data except upsell/downsell.
	 *
	 * @param int   $flow_id flow ID.
	 * @param int   $prev_control_id step ID.
	 * @param array $step_cookie_data step cookie data.
	 *
	 * @since 1.6.13
	 */
	public function save_conversion_data( $flow_id, $prev_control_id, $step_cookie_data ) {

		$this->save_conversion( $flow_id, $prev_control_id, $step_cookie_data, true );
	}

	/**
	 * Save upsell/downsell conversion.
	 *
	 * @param object $order         Parent order object data.
	 * @param array  $offer_product offer product data.
	 *
	 * @since 1.6.13
	 */
	public function save_offer_conversion( $order, $offer_product ) {

		$step_id = intval( $offer_product['step_id'] );

		// Case: In test mode step id is not available. So getting it from $_POST for correct analytics.
		if ( empty( $step_id ) ) {
			$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : false; //phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		$wcf_step_obj = wcf_pro_get_step( $step_id );
		$flow_id      = $wcf_step_obj->get_flow_id();
		$control_id   = $wcf_step_obj->get_control_step();

		$step_cookie_name = CARTFLOWS_VISITED_STEP_COOKIE . $flow_id;
		$step_cookie_data = isset( $_COOKIE[ $step_cookie_name ] ) ? json_decode( sanitize_text_field( wp_unslash( $_COOKIE[ $step_cookie_name ] ) ), true ) : array(); //phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE

		$this->save_conversion( $flow_id, $control_id, $step_cookie_data, false );
	}

	/**
	 * Save single conversion by step id.
	 *
	 * @param int   $flow_id       flow ID.
	 * @param int   $control_id    To find and create conversion.
	 * @param array $step_cookie_data step cookie data.
	 * @param bool  $exclude_offer exclude offer conversion.
	 *
	 * @since 1.6.13
	 */
	public function save_conversion( $flow_id, $control_id, $step_cookie_data, $exclude_offer = true ) {

		wcf()->logger->log( __CLASS__ . '::' . __FUNCTION__ . ' : Entering ' );

		wcf()->logger->log( PHP_EOL . '==== Log Start ====' . PHP_EOL );

		wcf()->logger->log( 'Step cookie data : ' . PHP_EOL . wp_json_encode( $step_cookie_data ) );

		if ( $control_id && isset( $step_cookie_data[ $control_id ] ) ) {

			$prev_step_data  = $step_cookie_data[ $control_id ];
			$prev_step_type  = $prev_step_data['step_type'];
			$skip_type       = array( 'upsell', 'downsell' );
			$save_conversion = true;

			if ( $exclude_offer && in_array( $prev_step_type, $skip_type, true ) ) {
				$save_conversion = false;
			}

			wcf()->logger->log( 'Previous step cookie data : ' . PHP_EOL . wp_json_encode( $prev_step_data ) . PHP_EOL . 'Conversion Saved: ' . $save_conversion . PHP_EOL );

			if ( $save_conversion && 'no' === $prev_step_data['conversion'] ) {

				/* Update entry in db */
				global $wpdb;

				$visit_meta_db = $wpdb->prefix . CARTFLOWS_PRO_VISITS_META_TABLE;
				//phpcs:disable WordPress.DB.SlowDBQuery
				$wpdb->update( //phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
					$visit_meta_db,
					array(
						'visit_id'   => $prev_step_data['visit_id'],
						'meta_key'   => 'conversion',
						'meta_value' => 'yes',
					),
					array(
						'visit_id' => $prev_step_data['visit_id'],
						'meta_key' => 'conversion',
					)
				);// db call ok;.
				//phpcs:enable WordPress.DB.SlowDBQuery
			}
		}

		wcf()->logger->log( '==== Log End ====' . PHP_EOL );

		wcf()->logger->log( __CLASS__ . '::' . __FUNCTION__ . ' : Exit ' );
	}

	/**
	 * Save visits and visit meta in database.
	 *
	 * @param int  $step_id step ID.
	 * @param bool $is_returning is returning visitor.
	 *
	 * @since 1.0.0
	 */
	public function save_visit( $step_id, $is_returning ) {

		global $wpdb;

		$visit_db      = $wpdb->prefix . CARTFLOWS_PRO_VISITS_TABLE;
		$visit_meta_db = $wpdb->prefix . CARTFLOWS_PRO_VISITS_META_TABLE;
		$visit_type    = 'new';
		$http_referer  = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';

		if ( $is_returning ) {
			$visit_type = 'return';
		}

		// insert visit entry.
		$wpdb->insert(
			$visit_db,
			array(
				'step_id'      => $step_id,
				'date_visited' => current_time( 'Y-m-d H:i:s' ),
				'visit_type'   => $visit_type,
			)
		);// db call ok;.

		$visit_id = $wpdb->insert_id;

		$meta_data = array(
			'http_referer' => $http_referer,
			'conversion'   => 'no',
		);

		foreach ( $meta_data as $key => $value ) {

			// make sure there is a key and a value before saving.
			if ( ! $key || ! $value ) {
				continue;
			}

			$wpdb->insert(
				$visit_meta_db,
				array(
					'visit_id'   => $visit_id,
					'meta_key'   => $key,
					'meta_value' => $value, //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			);// db call ok;.
		}

		/* Set current visit id */
		$wcf_step_obj    = wcf_pro_get_step( $step_id );
		$step_control_id = $wcf_step_obj->get_control_step();
		$step_type       = $wcf_step_obj->get_step_type();

		return array(
			'control_step_id' => $step_control_id,
			'current_step_id' => $step_id,
			'step_type'       => $step_type,
			'visit_id'        => $visit_id,
			'conversion'      => 'no',
		);

	}

	/**
	 * Add localize variables.
	 *
	 * @param array $localize localize array.
	 *
	 * @since 1.0.0
	 */
	public function add_localize_vars( $localize ) {

		global $post;
		$step_id               = $post->ID;
		$analytics_track_nonce = wp_create_nonce( 'wcf-analytics-nonce-' . $step_id );

		$localize['analytics_nonce'] = $analytics_track_nonce;
		$localize['flow_cookie']     = CARTFLOWS_VISITED_FLOW_COOKIE;
		$localize['step_cookie']     = CARTFLOWS_VISITED_STEP_COOKIE;

		return $localize;
	}
}

Cartflows_Pro_Analytics_Tracking_Beta::get_instance();
