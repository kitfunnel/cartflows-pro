<?php
/**
 * Offer
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'CARTFLOWS_PRO_BASE_OFFER_DIR', CARTFLOWS_PRO_DIR . 'modules/offer/' );

/**
 * Initial Setup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Base_Offer {


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
		require_once CARTFLOWS_PRO_BASE_OFFER_DIR . 'classes/class-cartflows-pro-base-offer-shortcodes.php';
		require_once CARTFLOWS_PRO_BASE_OFFER_DIR . 'classes/class-cartflows-pro-base-offer-markup.php';

		require_once CARTFLOWS_PRO_BASE_OFFER_DIR . 'classes/class-cartflows-pro-base-offer-meta-data.php';

		/* Offer order hierachy of separate orders */
		require_once CARTFLOWS_PRO_BASE_OFFER_DIR . 'classes/class-cartflows-pro-offer-order-meta.php';

		if ( class_exists( 'WC_Subscriptions' ) ) {
			require_once CARTFLOWS_PRO_BASE_OFFER_DIR . 'classes/class-cartflows-pro-offer-subscriptions.php';
		}
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Base_Offer::get_instance();
