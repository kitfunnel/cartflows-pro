<?php
/**
 * Affiliate wp.
 *
 * @package CARTFLOWS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Utils.
 */
class Cartflows_Pro_Affiliate_Wp {

	/**
	 * Member Variable
	 *
	 * @var instance
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
	 * Constructor
	 */
	public function __construct() {
		add_action( 'cartflows_offer_accepted', array( $this, 'add_offer_affiliate' ), 10, 2 );
	}

	/**
	 * Add upsell/downsell affiliate to order
	 *
	 * @param object $order order data.
	 * @param object $offer_product offer product data.
	 * @return void
	 */
	public function add_offer_affiliate( $order, $offer_product ) {

		if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
			define( 'WOOCOMMERCE_CHECKOUT', true );
		}

		if ( class_exists( 'Affiliate_WP_WooCommerce' ) ) {

			$afw_woo  = new Affiliate_WP_WooCommerce();
			$order_id = '';

			if ( is_object( $order ) ) {

				if ( ! wcf_pro()->utils->is_separate_offer_order() ) {

					$order_id = $order->get_id();
				} else {
					// Get last placed child order ID.
					$order_id = end( array_keys( $order->get_meta( '_cartflows_offer_child_orders' ) ) );
				}

				$afw_woo->add_pending_referral( $order_id );
			}
		}
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Affiliate_Wp' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Affiliate_Wp::get_instance();

