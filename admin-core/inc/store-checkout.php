<?php
/**
 * CartFlows Store Checkout.
 *
 * @package CartFlows
 */

namespace CartflowsProAdmin\AdminCore\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class StoreCheckout.
 */
class StoreCheckout {



	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.10.0
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 1.10.0
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
	 * @since 1.10.0
	 */
	public function __construct() {

		add_action( 'cartflows_ab_test_update_control', array( $this, 'set_control_global_checkout' ), 10, 2 );
	}

	/**
	 * Sets strore checkout control step ( type = checkout ) to global checkout
	 *
	 * @param int   $flow_id current flow id.
	 * @param array $variant control variant array.
	 * @return void
	 * @since 1.10.0
	 */
	public function set_control_global_checkout( $flow_id, $variant ) {
		if ( intval( get_option( '_cartflows_store_checkout', false ) ) !== $flow_id ) {
			return;
		}

		if ( 'checkout' === $variant['type'] ) {
			$common_settings                    = \Cartflows_Helper::get_common_settings();
			$common_settings['global_checkout'] = $variant['id'];
			update_option( '_cartflows_common', $common_settings );
		}
	}
}
