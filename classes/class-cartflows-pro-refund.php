<?php
/**
 * Cartflows Admin.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
/**
 * Class Cartflows_Pro_Refund.
 */
class Cartflows_Pro_Refund {

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

		add_action( 'wp_ajax_wcf_admin_refund_offer', array( $this, 'process_cancellation' ) );

		/* Add Upsell Refund Metabox in the WooCommerce's Orders page */
		add_action( 'add_meta_boxes', array( $this, 'add_offer_refund_meta_box' ) );

		// Enqueue refund order script & style.
		add_action( 'admin_enqueue_scripts', array( $this, 'add_refund_offer_script' ) );
	}

	/**
	 * Process the refund of offer product.
	 *
	 * @hook wp_ajax_wcf_admin_refund_offer
	 * @return void
	 */
	public function process_cancellation() {

		/**
		 * Check permission
		 */
		if ( ! current_user_can( 'cartflows_manage_settings' ) ) {

			$result['data'] = array(
				'error' => __( 'Permission denied!', 'cartflows-pro' ),
			);

			wp_send_json_error( $result );
		}

		$result = array(
			'success' => false,
			'msg'     => __( 'Unexpected error occoured', 'cartflows-pro' ),
		);

		if ( ! class_exists( 'Cartflows_Pro_Gateways' ) ) {
			wp_send_json( $result );
		}

		if ( isset( $_POST['security'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'wcf_admin_refund_offer_nonce' ) ) {

			$order_id               = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
			$step_id                = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
			$offer_id               = isset( $_POST['offer_id'] ) ? intval( $_POST['offer_id'] ) : 0;
			$offer_amount           = isset( $_POST['offer_amt'] ) ? floatval( $_POST['offer_amt'] ) : 0;
			$transaction_id         = isset( $_POST['transaction_id'] ) ? sanitize_text_field( wp_unslash( $_POST['transaction_id'] ) ) : '';
			$refund_amount          = $offer_amount;
			$refund_reason          = isset( $_POST['refund_reason'] ) ? sanitize_text_field( wp_unslash( $_POST['refund_reason'] ) ) : '';
			$api_refund             = filter_input( INPUT_POST, 'api_refund', FILTER_VALIDATE_BOOLEAN );
			$restock_refunded_items = filter_input( INPUT_POST, 'restock_refunded_items', FILTER_VALIDATE_BOOLEAN );

			$offer_data = array(
				'order_id'       => $order_id,
				'offer_id'       => $offer_id,
				'transaction_id' => $transaction_id,
				'refund_amount'  => $refund_amount,
				'refund_reason'  => $refund_reason,
			);

			$result = array(
				'success' => false,
				'msg'     => __( 'Refund unsuccessful', 'cartflows-pro' ),
			);

			// Get order object.
			$order = wc_get_order( $order_id );

			$payment_method = $order->get_payment_method();

			$gateway = wcf_pro()->gateways->load_gateway( $payment_method );

			if ( $gateway->is_api_refund() ) {
				$refund_txn_id = $gateway->process_offer_refund( $order, $offer_data );
			}

			if ( false !== $refund_txn_id ) {

				// Get Line items.
				$cartflows_offers = $order->get_items( 'line_item' );
				$offer_item       = WC_Order_Factory::get_order_item( $offer_id );
				$offer_item       = WC_Order_Factory::get_order_item( $offer_id );

				// Prepare the line items.
				$line_items[ $offer_id ] = array(
					'qty'          => max( $offer_item->get_quantity(), 0 ),
					'refund_total' => wc_format_decimal( $offer_item->get_total() ),
					'refund_tax'   => array(),
				);

				$order_taxes    = $order->get_taxes();
				$tax_data       = $offer_item->get_taxes();
				$tax_item_total = array();

				foreach ( $order_taxes as $tax_item ) {
					$tax_item_id                    = $tax_item->get_rate_id();
					$tax_item_total[ $tax_item_id ] = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : 0;
				}

				$line_items[ $offer_id ]['refund_tax'] = array_filter( array_map( 'wc_format_decimal', $tax_item_total ) );

				$refund = wc_create_refund(
					array(
						'amount'         => $refund_amount,
						'reason'         => $refund_reason,
						'order_id'       => $order_id,
						'refund_payment' => false,
						'line_items'     => $line_items,
						'restock_items'  => true,
					)
				);

				if ( is_wp_error( $refund ) ) {

					$order->add_order_note( 'Refund of amount - ' . wc_format_decimal( $offer_item->get_total() ) . ' - CartFlows Offer ID - ' . $offer_id . ' - failed for order - ' . $order_id . '. Reason - ' . $refund_reason );

					$result['success'] = true;
					$result['msg']     = __( 'Refund Unsuccessful', 'cartflows-pro' );
				} else {

					wc_update_order_item_meta( $offer_id, '_cartflows_refunded', 'yes' );

					$order->add_order_note( 'Refund of amount - ' . wc_format_decimal( $offer_item->get_total() ) . ' - CartFlows Offer ID - ' . $offer_id . ' - completed for order - ' . $order_id . '. Reason - ' . $refund_reason . ' & Refund ID : ' . $refund_txn_id );

					$result['success'] = true;
					$result['msg']     = __( 'Refund Successful', 'cartflows-pro' );
				}
			}
		}

		wp_send_json( $result );
	}

	/**
	 * Metabox to display the Upsell Offer refund settings.
	 *
	 * @return void
	 */
	public function add_offer_refund_meta_box() {

		if ( ! class_exists( 'Cartflows_Pro_Gateways' ) ) {
			return;
		}

		if ( ! $this->is_show_refund_metabox() ) {
			return;
		}

		$order_screen_id = $this->is_custom_order_table_controller_available()
		? wc_get_page_screen_id( 'shop-order' )
		: 'shop_order'; // It will decide whether to show meta box on old order page or new one based on Woo Settings.

		// Add the refund offer order metabox.
		add_meta_box(
			'wcf-offer-refund-metabox',
			__( 'CartFlows Refund Offers', 'cartflows-pro' ),
			array( $this, 'refund_offer_metabox_callback' ),
			$order_screen_id,
			'normal'
		);

	}

	/**
	 * Check if required class and custom order is enabled.
	 *
	 * @return bool
	 */
	public function is_custom_order_table_controller_available() {

		return class_exists( 'Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ? true : false;

	}

	/**
	 * Maybe show refund meta box.
	 *
	 * @return bool
	 */
	public function is_show_refund_metabox() {

		global $post;

		$current_screen_id = get_current_screen()->id;

		if ( in_array( $current_screen_id, array( 'shop_order', 'woocommerce_page_wc-orders' ), true ) ) { // 'woocommerce_page_wc-orders' is screen id of the new order page.

			$order_id = $post ? $post->ID : 0;

			/**
			 * Reason for PHPCS ignore: Retrieving the order ID from the URL to check the current page is order page,
			 * and has the flow ID and a valid order placed via CartFlows.
			*/
			if ( ! $order_id && isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$order_id = intval( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}

			if ( ! $order_id ) {
				return false;
			}

			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				return false;
			}

			$flow_id = $order->get_meta( '_wcf_flow_id' );

			if ( $flow_id < 1 ) {
				return false;
			}

			$is_valid_order = $this->is_valid_order_for_refund( $order );

			if ( ! $is_valid_order ) {
				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * Include HTML view of the refund metabox.
	 *
	 * @return void
	 */
	public function refund_offer_metabox_callback() {

		include CARTFLOWS_PRO_DIR . 'admin/views/html-refund-offer.php';
	}

	/**
	 * Include HTML view of the refund metabox.
	 *
	 * @return void
	 */
	public function add_refund_offer_script() {

		if ( ! is_admin() ) {
			return;
		}

		if ( ! $this->is_show_refund_metabox() ) {
			return;
		}

		// Enqueue meta-box css.
		wp_enqueue_style( 'cartflows-refund-offer-metabox', CARTFLOWS_PRO_URL . 'admin/meta-assets/css/refund-offer-meta-box.css', array(), CARTFLOWS_PRO_VER );

		// Enqueue meta-box js.
		wp_enqueue_script( 'cartflows-refund-offer-metabox', CARTFLOWS_PRO_URL . 'admin/meta-assets/js/refund-offer-meta-box.js', array( 'jquery' ), CARTFLOWS_PRO_VER, true );

	}

	/**
	 * Show refund box only for valid orders.
	 *
	 * @param  WC_Order $order The order object.
	 * @return bool     $show true/false.
	 */
	public function is_valid_order_for_refund( $order ) {

		$is_valid = false;

		$is_offer = $this->is_offer_present_in_order( $order );

		if ( $is_offer ) {

			$payment_method = $order->get_payment_method();
			$gateway_obj    = wcf_pro()->gateways->load_gateway( $payment_method );
			$main_txn_id    = $order->get_transaction_id();

			// If gateway supported and main transaction id not empty.
			if ( $gateway_obj->is_api_refund() && ! empty( $main_txn_id ) ) {
				$is_valid = true;
			}
		}

		return $is_valid;
	}

	/**
	 * Check for the offer is present.
	 *
	 * @param  WC_Order $order The order object.
	 * @return bool     $offer_exists true/false.
	 */
	public function is_offer_present_in_order( $order ) {

		$offer_exists = false;

		foreach ( $order->get_items() as $item_id => $item_data ) {

			$is_upsell   = wc_get_order_item_meta( $item_id, '_cartflows_upsell', true );
			$is_downsell = wc_get_order_item_meta( $item_id, '_cartflows_downsell', true );
			$is_txn_id   = wc_get_order_item_meta( $item_id, '_cartflows_offer_txn_id', true );

			if ( 'yes' == $is_upsell || 'yes' == $is_downsell && ! empty( $is_txn_id ) ) {
				$offer_exists = true;
				break;
			}
		}

		return $offer_exists;
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Refund' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Refund::get_instance();
