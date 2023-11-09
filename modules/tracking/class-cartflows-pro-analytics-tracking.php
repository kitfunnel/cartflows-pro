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
class Cartflows_Pro_Analytics_Tracking {

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
			add_action( 'template_redirect', array( $this, 'save_analytics_data' ) );
			add_action( 'cartflows_offer_accepted', array( $this, 'save_offer_conversion' ), 20, 2 );
		}

	}
	//phpcs:disable WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE
	/**
	 *  Save analytics data.
	 */
	public function save_analytics_data() {

		if ( wcf()->utils->is_step_post_type() ) {

			global $post;

			$current_flow = wcf()->utils->get_flow_id();
			$analytics    = wcf()->options->get_flow_meta_value( $current_flow, 'wcf-enable-analytics' );

			if ( ! $current_flow ) {
				return;
			}

			if ( 'no' === $analytics ) {
				return;
			}

			$current_step = $post->ID;
			$cookie_name  = CARTFLOWS_VISITED_FLOW_COOKIE . $current_flow;
			$cookie       = isset( $_COOKIE[ $cookie_name ] ) ? json_decode( sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) ), true ) : array();
			$is_returning = in_array( $current_step, $cookie, true );

			if ( ! $is_returning ) {
				array_push( $cookie, $current_step );
			}

			setcookie( $cookie_name, wp_json_encode( $cookie ), strtotime( '+1 year' ), '/', COOKIE_DOMAIN, CARTFLOWS_HTTPS ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie

			$this->save_conversion_data( $current_step );
			$this->save_visit( $current_flow, $current_step, $is_returning );
		}
	}

	/**
	 * Save conversion data except upsell/downsell.
	 *
	 * @param int $step_id step ID.
	 *
	 * @since 1.6.13
	 */
	public function save_conversion_data( $step_id ) {

		$wcf_step_obj    = wcf_pro_get_step( $step_id );
		$flow_id         = $wcf_step_obj->get_flow_id();
		$prev_control_id = $wcf_step_obj->get_prev_control_id();

		$this->save_conversion( $flow_id, $prev_control_id, true );
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

		$step_id      = $offer_product['step_id'];
		$wcf_step_obj = wcf_pro_get_step( $step_id );
		$flow_id      = $wcf_step_obj->get_flow_id();
		$control_id   = $wcf_step_obj->get_control_step();

		$this->save_conversion( $flow_id, $control_id, false );
	}

	/**
	 * Save single conversion by step id.
	 *
	 * @param int  $flow_id       flow ID.
	 * @param int  $control_id    To find and create conversion.
	 * @param bool $exclude_offer exclude offer conversion.
	 *
	 * @since 1.6.13
	 */
	public function save_conversion( $flow_id, $control_id, $exclude_offer = true ) {

		wcf()->logger->log( __CLASS__ . '::' . __FUNCTION__ . ' : Entering ' );

		$step_cookie_name = CARTFLOWS_VISITED_STEP_COOKIE . $flow_id;
		$step_cookie_data = isset( $_COOKIE[ $step_cookie_name ] ) ? json_decode( sanitize_text_field( wp_unslash( $_COOKIE[ $step_cookie_name ] ) ), true ) : array();

		wcf()->logger->log( PHP_EOL . '==== Log Start ====' );

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

				$visit_db      = $wpdb->prefix . CARTFLOWS_PRO_VISITS_TABLE;
				$visit_meta_db = $wpdb->prefix . CARTFLOWS_PRO_VISITS_META_TABLE;

				$wpdb->update( //phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
					$visit_meta_db,
					array(
						'visit_id'   => $prev_step_data['visit_id'],
						'meta_key'   => 'conversion',
						'meta_value' => 'yes', //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					),
					array(
						'visit_id' => $prev_step_data['visit_id'],
						'meta_key' => 'conversion', //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					)
				);// db call ok;.

				/* Update data in cokkie */
				$step_cookie_data[ $control_id ]['conversion'] = 'yes';

				setcookie( CARTFLOWS_VISITED_STEP_COOKIE . $flow_id, wp_json_encode( $step_cookie_data ), strtotime( '+1 year' ), '/', COOKIE_DOMAIN, CARTFLOWS_HTTPS ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie
				$_COOKIE[ $step_cookie_name ] = $step_cookie_data;

				wcf()->logger->log( 'Conversion Stored : ' . PHP_EOL . wcf_print_r( $step_cookie_data, true ) . PHP_EOL );
			}
		}

		wcf()->logger->log( '==== Log End ====' . PHP_EOL );

		wcf()->logger->log( __CLASS__ . '::' . __FUNCTION__ . ' : Exit ' );
	}

	/**
	 * Save visits and visit meta in database.
	 *
	 * @param int  $flow_id flow ID.
	 * @param int  $step_id step ID.
	 * @param bool $is_returning is returning visitor.
	 *
	 * @since 1.0.0
	 */
	public function save_visit( $flow_id, $step_id, $is_returning ) {

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
		$wcf_step_obj     = wcf_pro_get_step( $step_id );
		$step_cookie_name = CARTFLOWS_VISITED_STEP_COOKIE . $flow_id;
		$step_cookie_data = isset( $_COOKIE[ $step_cookie_name ] ) ? json_decode( sanitize_text_field( wp_unslash( $_COOKIE[ $step_cookie_name ] ) ), true ) : array();
		$step_control_id  = $wcf_step_obj->get_control_step();
		$step_type        = $wcf_step_obj->get_step_type();

		$step_cookie_data[ $step_control_id ] = array(
			'control_step_id' => $step_control_id,
			'current_step_id' => $step_id,
			'step_type'       => $step_type,
			'visit_id'        => $visit_id,
			'conversion'      => 'no',
		);

		setcookie( CARTFLOWS_VISITED_STEP_COOKIE . $flow_id, wp_json_encode( $step_cookie_data ), strtotime( '+1 year' ), '/', COOKIE_DOMAIN, CARTFLOWS_HTTPS ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie
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

		return $localize;
	}

	//phpcs:enable WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE
}

Cartflows_Pro_Analytics_Tracking::get_instance();
