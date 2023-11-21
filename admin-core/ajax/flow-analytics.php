<?php
/**
 * CartFlows Flows ajax actions.
 *
 * @package CartFlows
 */

namespace CartflowsProAdmin\AdminCore\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use CartflowsProAdmin\AdminCore\Ajax\AjaxBase;

/**
 * Class Flows.
 */
class FlowAnalytics extends AjaxBase {

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
	 * Register_ajax_events.
	 *
	 * @return void
	 */
	public function register_ajax_events() {

		$ajax_events = array(
			'set_visit_data',
			'reset_flow_analytics',
		);

		$this->init_ajax_events( $ajax_events );
	}

	/**
	 * Visits map.
	 */
	public function set_visit_data() {

		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );

		/**
		 * Check permission
		 */
		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_set_visit_data', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		if ( ! wcf()->is_woo_active ) {
			return array(
				'revenue'   => array(),
				'all_steps' => array(),
				'currency'  => '',
			);
		}

		$flow_id    = isset( $_POST['flow_id'] ) ? intval( $_POST['flow_id'] ) : 0;
		$start_date = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '';
		$end_date   = isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '';

		$analytics_data = $this->flow_analytics_data( $flow_id, $start_date, $end_date );

		$response = array(
			'revenue'   => $analytics_data['earning'],
			'all_steps' => $analytics_data['visits'],
			'currency'  => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '',
		);

		wp_send_json_success( $response );

	}

	/**
	 * Get analytics for specific flow.
	 *
	 * @param int    $flow_id flow id.
	 * @param string $start_date start date.
	 * @param string $end_date end date.
	 * @return array
	 */
	public function flow_analytics_data( $flow_id, $start_date, $end_date ) {

		$report = \Cartflows_Pro_Analytics_Reports::get_instance();

		$earning = $report->get_earnings( $flow_id, $start_date, $end_date );

		$visits = $report->fetch_visits( $flow_id, $start_date, $end_date );

		return array(
			'earning' => $earning,
			'visits'  => $report->visits_map( $flow_id, $visits, $earning ),
		);
	}

	/**
	 * Reset_flow_analytics
	 */
	public function reset_flow_analytics() {

		$response_data = array( 'message' => $this->get_error_msg( 'permission' ) );

		/**
		 * Check permission
		 */
		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_reset_flow_analytics', 'security', false ) ) {
			$response_data = array( 'message' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		global $wpdb;
		$visit_db = $wpdb->prefix . CARTFLOWS_PRO_VISITS_TABLE;
		$flow_id  = isset( $_POST['flow_id'] ) ? intval( $_POST['flow_id'] ) : 0;

		$result = array(
			'status' => false,
			'text'   => sprintf( __( 'Invalid Flow ID has been provided.', 'cartflows-pro' ) ),
		);

		if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) ) {
			wp_send_json_error( $result );
		}

		if ( 0 !== $flow_id ) {
			$get_steps = wcf()->flow->get_steps( $flow_id );
			$step_ids  = implode( ',', wp_list_pluck( $get_steps, 'id' ) );
			// Prepare adds singlw quotes thats why can not use it.
			$wpdb->query( "DELETE FROM {$visit_db} WHERE step_id IN(" . $step_ids . ')' ); //phpcs:ignore WordPress.DB.PreparedSQL, WordPress.DB.DirectDatabaseQuery

			$reset_date = current_time( 'Y-m-d H:i:s' );

			update_post_meta( $flow_id, 'wcf-analytics-reset-date', $reset_date );

			wp_send_json_success( true );
		}
		wp_send_json_error( false );
	}
}
