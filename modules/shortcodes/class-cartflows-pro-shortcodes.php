<?php
/**
 * Widgets
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'CARTFLOWS_PRO_SHORTCODES_DIR', CARTFLOWS_PRO_DIR . 'modules/shortcodes/' );
define( 'CARTFLOWS_PRO_SHORTCODES_URL', CARTFLOWS_PRO_URL . 'modules/shortcodes/' );

/**
 * Initial Setup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Shortcodes {


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

		require_once CARTFLOWS_PRO_SHORTCODES_DIR . 'class-cartflows-pro-order-fields-shortcode.php';
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Shortcodes::get_instance();
