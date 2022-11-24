<?php
/**
 * AB test markup.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * AB test Markup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Ab_Test_Markup {


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

		add_action( 'cartflows_wp', array( $this, 'process_ab_test' ) );
	}

	/**
	 * Process ab test
	 *
	 * @param int $step_id Step id.
	 */
	public function process_ab_test( $step_id ) {

		if ( is_user_logged_in() && current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		$ab_test = wcf_get_ab_test( $step_id );

		if ( $ab_test->is_ab_test_enable() ) {
			$ab_test->run_ab_test();

		} else {
			$ab_test->maybe_redirect_winner_step( $step_id );
		}
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Ab_Test_Markup::get_instance();
