<?php
/**
 * Offer
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'CARTFLOWS_PRO_AB_TEST_DIR', CARTFLOWS_PRO_DIR . 'modules/ab-test/' );

/**
 * Initial Setup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Ab_Test {


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
		require_once CARTFLOWS_PRO_AB_TEST_DIR . 'classes/class-cartflows-pro-ab-test-meta.php';
		require_once CARTFLOWS_PRO_AB_TEST_DIR . 'classes/class-cartflows-pro-ab-test-factory.php';
		require_once CARTFLOWS_PRO_AB_TEST_DIR . 'classes/class-cartflows-pro-ab-test-markup.php';
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Ab_Test::get_instance();
