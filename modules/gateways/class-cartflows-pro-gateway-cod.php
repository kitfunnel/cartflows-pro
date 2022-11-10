<?php
/**
 * Cod Gateway.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Gateway_Cod.
 */
class Cartflows_Pro_Gateway_Cod {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Refund supported
	 *
	 * @var is_api_refund
	 */
	public $is_api_refund = false;

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

		add_filter( 'woocommerce_cod_process_payment_order_status', array( $this, 'maybe_setup_upsell_cod' ), 999, 2 );
	}

	/**
	 * Loads module files.
	 *
	 * @since 1.0.0
	 * @param string $order_status order status.
	 * @param array  $order order data.
	 * @return string
	 */
	public function maybe_setup_upsell_cod( $order_status, $order ) {

		wcf()->logger->log( 'COD Process payment order status called woocommerce_cod_process_payment_order_status' );

		// Set upsell and get new status based on condition.
		$order_status = wcf_pro()->front->set_upsell_return_new_order_status( $order_status, $order );

		return $order_status;
	}

	/**
	 * Process offer payment
	 *
	 * @since 1.0.0
	 * @param array $order order data.
	 * @param array $product product data.
	 * @return bool
	 */
	public function process_offer_payment( $order, $product ) {

		return true;
	}

	/**
	 * Is gateway support offer refund
	 *
	 * @return bool
	 */
	public function is_api_refund() {

		return $this->is_api_refund;
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Gateway_Cod' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateway_Cod::get_instance();
