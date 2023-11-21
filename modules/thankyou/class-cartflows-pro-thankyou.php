<?php
/**
 * CartFlows ThankYou
 *
 * @package CartFlows
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'CARTFLOWS_PRO_THANKYOU_DIR', CARTFLOWS_PRO_DIR . 'modules/thankyou/' );

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Thankyou {


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
	 *  Constructor
	 */
	public function __construct() {

		add_action( 'woocommerce_after_order_details', array( $this, 'display_child_orders' ), 20 );
	}

	/**
	 *  Display child orders at thank you page after order details.
	 *
	 * @param object $parent_order order object.
	 */
	public function display_child_orders( $parent_order ) {

		if ( ! $parent_order instanceof WC_Order ) {
			return;
		}

		$child_orders = $parent_order->get_meta( '_cartflows_offer_child_orders' );

		if ( ! empty( $child_orders ) ) {

			foreach ( $child_orders as $child_id => $child_data ) {

				$order_id = $child_id;

				include CARTFLOWS_PRO_THANKYOU_DIR . 'template/child-order-details.php';
			}
		}
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Thankyou::get_instance();
