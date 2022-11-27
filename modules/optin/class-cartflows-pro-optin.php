<?php
/**
 * Checkout
 *
 * @package CartFlows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'CARTFLOWS_PRO_OPTIN_DIR', CARTFLOWS_PRO_DIR . 'modules/optin/' );
define( 'CARTFLOWS_PRO_OPTIN_URL', CARTFLOWS_PRO_URL . 'modules/optin/' );

/**
 * Initial Setup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Optin {


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
		require_once CARTFLOWS_PRO_OPTIN_DIR . 'classes/class-cartflows-pro-optin-markup.php';
		require_once CARTFLOWS_PRO_OPTIN_DIR . 'classes/class-cartflows-pro-optin-default-meta.php';
		require_once CARTFLOWS_PRO_OPTIN_DIR . 'classes/class-cartflows-pro-optin-meta-data.php';
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Optin::get_instance();
