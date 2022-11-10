<?php
/**
 * Bacs Gateway.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Gateway_Bacs.
 */
class Cartflows_Pro_Gateway_Bacs {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Key name variable.
	 *
	 * @var key
	 */
	public $key = 'bacs';

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

		add_filter( 'woocommerce_bacs_process_payment_order_status', array( $this, 'maybe_setup_upsell_bacs' ), 999, 2 );

		add_action( 'woocommerce_email_before_order_table', array( $this, 'add_bank_details_in_email' ), 10, 3 );
	}

	/**
	 * Add Bank Detail content to the WC emails as it received from the order.
	 *
	 * @param WC_Order $order Order object.
	 * @param bool     $sent_to_admin Sent to admin.
	 * @param bool     $plain_text Email format: plain text or HTML.
	 */
	public function add_bank_details_in_email( $order, $sent_to_admin, $plain_text = false ) {

		if ( ! is_object( $order ) || ! $sent_to_admin ) {
			return false;
		}

		$gateway = $order->get_payment_method();

		if ( $this->key !== $gateway ) {
			return false;
		}

		if ( $order->has_status( 'on-hold' ) ) {
			if ( $this->get_wc_gateway()->instructions ) {
				echo wp_kses_post( wpautop( wptexturize( $this->get_wc_gateway()->instructions ) ) . PHP_EOL );
			}

			add_action( 'woocommerce_bacs_accounts', array( $this, 'modify_bank_details' ) );
		}

	}

	/**
	 * Pass Bank Details to the WC emails as it received from the order.
	 *
	 * @param array $bank_details Bank Details.
	 * @return array Bank Details.
	 */
	public function modify_bank_details( $bank_details ) {
		return $bank_details;
	}

	/**
	 * Setup upsell bacs.
	 *
	 * @since 1.0.0
	 * @param string $order_status order status.
	 * @param array  $order order data.
	 * @return string
	 */
	public function maybe_setup_upsell_bacs( $order_status, $order ) {

		wcf()->logger->log( 'BACS Process payment order status called woocommerce_cod_process_payment_order_status' );

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

	/**
	 * Get WooCommerce payment geteways.
	 *
	 * @return array
	 */
	public function get_wc_gateway() {

		global $woocommerce;

		$gateways = $woocommerce->payment_gateways->payment_gateways();

		return $gateways[ $this->key ];
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Gateway_Bacs' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateway_Bacs::get_instance();
