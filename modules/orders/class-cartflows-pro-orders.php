<?php
/**
 * CartFlows Orders
 *
 * @package CartFlows
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Orders {


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

		/* Register New Order Status */
		add_filter( 'woocommerce_register_shop_order_post_statuses', array( $this, 'register_new_order_status' ), 99 );

		/* Add order Status to WooCommerce options */
		add_filter( 'wc_order_statuses', array( $this, 'update_to_native_stauses' ), 99 );

		add_action( 'carflows_schedule_normalize_order_status', array( $this, 'schedule_normalize_order_status' ), 99, 3 );

		if ( ! wcf_pro()->utils->is_separate_offer_order() ) {
			/* Only for merged order Order Status to main order */
			add_action( 'cartflows_order_started', array( $this, 'register_order_status_to_main_order' ), 10 );
		}
	}

	/**
	 * Get order status slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_order_status_slug() {

		return 'wc-wcf-main-order';
	}

	/**
	 * Get order status title.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_order_status_title() {

		return _x( 'Main Order Accepted (CF)', 'Order status', 'cartflows-pro' );
	}

	/**
	 * Register new order status.
	 *
	 * @since 1.0.0
	 * @param string $order_status order status.
	 *
	 * @return array
	 */
	public function register_new_order_status( $order_status ) {

		$order_status_title = $this->get_order_status_title();

		$order_status[ $this->get_order_status_slug() ] = array(
			'label'                     => $order_status_title,
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: Single count value */
			'label_count'               => _n_noop( 'Main Order Accepted <span class="count">(%s)</span>', 'Main Order Accepted <span class="count">(%s)</span>', 'cartflows-pro' ),
		);

		return $order_status;
	}

	/**
	 * Update native statuses.
	 *
	 * @since 1.0.0
	 * @param string $order_status Order status.
	 *
	 * @return array
	 */
	public function update_to_native_stauses( $order_status ) {

		$order_status[ $this->get_order_status_slug() ] = $this->get_order_status_title();

		return $order_status;
	}

	/**
	 * Add upsell product and order meta.
	 *
	 * @since 1.0.0
	 * @param array $order order.
	 * @param array $upsell_product upsell product.
	 * @return void
	 */
	public function add_upsell_product( $order, $upsell_product ) {
		wcf()->logger->log( 'Start-' . __CLASS__ . '::' . __FUNCTION__ );

		$this->add_offer_product( $order, $upsell_product, 'upsell' );

		wcf()->logger->log( 'End-' . __CLASS__ . '::' . __FUNCTION__ );
	}

	/**
	 * Add downsell product.
	 *
	 * @since 1.0.0
	 * @param array $order order.
	 * @param array $downsell_product downsell product.
	 * @return void
	 */
	public function add_downsell_product( $order, $downsell_product ) {

		$this->add_offer_product( $order, $downsell_product, 'downsell' );
	}

	/**
	 * Add shipping to order.
	 *
	 * @param array $product_data offer product.
	 * @param array $order parent order.
	 * @return void
	 */
	public function add_shipping_charges_to_order( $product_data, $order ) {

		// Add the shipping charges.
			$item = new WC_Order_Item_Shipping();
				$item->set_props(
					array(
						'method_title' => 'Flat rate',
						'method_id'    => '',
						'total'        => $product_data['shipping_fee'],
					)
				);

				$item->save();
				$order->add_item( $item );

				// Show product details in shipping rates section of order data.
				$product_name         = $product_data['name'] . ' &times; ' . $product_data['qty'];
				$offer_shipping_items = array( $product_name );
				$item_id              = $item->get_id();
				$offer_itmes          = implode( ',', $offer_shipping_items );
				wc_add_order_item_meta( $item_id, 'Items', $offer_itmes );

				$order->calculate_totals();
				$order->save();
	}

	/**
	 * Add offer product.
	 *
	 * @since 1.0.0
	 * @param array  $order order.
	 * @param array  $product_data offer product.
	 * @param string $type offer product type.
	 * @return void
	 */
	public function add_offer_product( $order, $product_data, $type = 'upsell' ) {

		wcf()->logger->log( 'Start-' . __CLASS__ . '::' . __FUNCTION__ );
		wcf()->logger->log( 'Offer type - ' . $type );

		$transaction_id = $order->get_meta( 'cartflows_offer_txn_resp_' . $product_data['step_id'] );
		$child_order    = null;

		if ( ! wcf_pro()->utils->is_separate_offer_order() ) {

			$item_id = $order->add_product( wc_get_product( $product_data['id'] ), $product_data['qty'], $product_data['args'] );

			/**
			 * This filter 'cartflows_display_offer_shipping_fee' will be true always.
			 * Can be used to hide/show the shipping fee on thank you page depending on the product selected.
			 */
			if ( 0 < $product_data['shipping_fee'] || apply_filters( 'cartflows_display_offer_shipping_fee', false, $product_data ) ) {
				$this->add_shipping_charges_to_order( $product_data, $order );
				wc_add_order_item_meta( $item_id, '_cartflows_offer_shipping_fee', $product_data['shipping_fee_tax'] );
			}
			wc_add_order_item_meta( $item_id, '_cartflows_' . $type, 'yes' );
			wc_add_order_item_meta( $item_id, '_cartflows_step_id', $product_data['step_id'] );
			wc_add_order_item_meta( $item_id, '_cartflows_offer_txn_id', $transaction_id );

			$order->calculate_totals();

		} else {

			// Set transaction_id in product data.
			$product_data['transaction_id'] = $transaction_id;

			$child_order = $this->create_child_order( $order, $product_data, $type );
		}

		do_action( 'cartflows_offer_product_processed', $order, $product_data, $child_order );

		wcf()->logger->log( 'End-' . __CLASS__ . '::' . __FUNCTION__ );
	}

	/**
	 * Normalize order status.
	 *
	 * @since 1.0.0
	 * @param object $order order.
	 * @return void
	 */
	public function may_be_normalize_status( $order ) {

		wcf()->logger->log( 'Entering: ' . __CLASS__ . '::' . __FUNCTION__ );
		wcf()->logger->log( 'Order status: ' . $order->get_status() );

		/* @todo : Check if it is our status */
		$flow_id = wcf()->utils->get_flow_id_from_order( $order );

		$before_normal = 'pending';
		$normal_status = 'processing';

		/* Get status change data from order */
		$order_status_change_data = $order->get_meta( '_cartflows_order_status_change' );

		if ( isset( $order_status_change_data['before_normal'] ) && isset( $order_status_change_data['normal_status'] ) ) {

			$before_normal = $order_status_change_data['before_normal'];
			$normal_status = $order_status_change_data['normal_status'];
		} else {

			$session_data = wcf_pro()->session->get_data( $flow_id );

			if ( $session_data ) {

				$before_normal = isset( $session_data['before_normal'] ) ? $session_data['before_normal'] : $before_normal;
				$normal_status = isset( $session_data['normal_status'] ) ? $session_data['normal_status'] : $normal_status;
			}
		}

		$this->do_normalize_status( $order, $before_normal, $normal_status );
	}

	/**
	 * Normalize order status.
	 *
	 * @since 1.0.0
	 * @param array  $order order.
	 * @param string $before_normal before status.
	 * @param string $normal_status normal status.
	 * @return void
	 */
	public function do_normalize_status( $order, $before_normal = 'pending', $normal_status = 'processing' ) {

		wcf()->logger->log( 'Entering: ' . __CLASS__ . '::' . __FUNCTION__ );
		wcf()->logger->log( 'Before Normal: ' . $before_normal );
		wcf()->logger->log( 'Normal: ' . $normal_status );

		wcf()->logger->log( 'order data: ' . $order );

		if ( false === is_a( $order, 'WC_Order' ) ) {
			return;
		}

		$current_status = $order->get_status();

		if ( 'wcf-main-order' !== $current_status ) {
			return;
		}

		/* Setup Beofore Normal Status */
		$order->update_status( $before_normal );

		$normal_status = apply_filters( 'wcf_order_status_after_order_complete', $normal_status, $order );

		/* Setup Normal Staus */
		$order->update_status( $normal_status );

	}

	/**
	 * Schedule normalize order status.
	 *
	 * @since 1.0.0
	 * @param int    $order_id order id.
	 * @param string $before_normal before status.
	 * @param string $normal_status normal status.
	 * @return void
	 */
	public function schedule_normalize_order_status( $order_id, $before_normal, $normal_status ) {

		$order = wc_get_order( $order_id );

		$this->do_normalize_status( $order, $before_normal, $normal_status );
	}

	/**
	 * Register order status to main order.
	 *
	 * @since 1.0.0
	 * @param array $order order data.
	 * @return void
	 */
	public function register_order_status_to_main_order( $order ) {

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		$payment_method = $order->get_payment_method();

		if ( 'cod' === $payment_method || 'bacs' === $payment_method ) {
			return;
		}

		add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'maybe_set_completed_order_status' ), 999, 3 );
	}

	/**
	 * Set order status to complete.
	 *
	 * @since 1.0.0
	 * @param string $order_status order status.
	 * @param int    $id order id.
	 * @param array  $order order data.
	 * @return string
	 */
	public function maybe_set_completed_order_status( $order_status, $id, $order ) {

		wcf()->logger->log( __CLASS__ . '::maybe_set_completed_order_status' );

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return $order_status;
		}

		remove_filter( 'woocommerce_payment_complete_order_status', array( $this, 'maybe_set_completed_order_status' ), 999 );

		$new_status = $this->get_order_status_slug();

		/**
		 * $new_status = our new status
		 * $order_status = default status change
		 */
		do_action( 'cartflows_order_status_change_to_main_order', $new_status, $order_status, $order );

		return $this->get_order_status_slug();

	}

	/**
	 * Create child offer order.
	 *
	 * @since 1.0.0
	 * @param object $parent_order order.
	 * @param array  $product_data offer product.
	 * @param string $type         offer product type.
	 */
	public function create_child_order( $parent_order, $product_data, $type = 'upsell' ) {

		wcf()->logger->log( 'Start-' . __CLASS__ . '::' . __FUNCTION__ );

		$order = false;

		if ( ! empty( $parent_order ) ) {

			$parent_order_id      = $parent_order->get_id();
			$parent_order_billing = $parent_order->get_address( 'billing' );
			$flow_id              = $parent_order->get_meta( '_wcf_flow_id' );

			if ( apply_filters( 'cartflows_force_create_child_order', ! empty( $parent_order_billing['email'] ), $parent_order ) ) {

				$customer_id = $parent_order->get_customer_id();

				$order = wc_create_order(
					array(
						'customer_id' => $customer_id,
						'status'      => 'wc-pending',
					)
				);
				/* Set Order type */
				$order->update_meta_data( '_cartflows_offer', 'yes' );
				$order->update_meta_data( '_cartflows_offer_type', $type );
				$order->update_meta_data( '_cartflows_parent_flow_id', $flow_id );
				$order->update_meta_data( '_cartflows_offer_step_id', $product_data['step_id'] );
				$order->update_meta_data( '_cartflows_offer_parent_id', $parent_order_id );

				$item_id = $order->add_product( wc_get_product( $product_data['id'] ), $product_data['qty'], $product_data['args'] );

				if ( 0 < $product_data['shipping_fee'] || apply_filters( 'cartflows_display_offer_shipping_fee', false, $product_data ) ) {
					$this->add_shipping_charges_to_order( $product_data, $order );
				}

				wc_add_order_item_meta( $item_id, '_cartflows_' . $type, 'yes' );
				wc_add_order_item_meta( $item_id, '_cartflows_step_id', $product_data['step_id'] );

				$order->set_address( $parent_order->get_address( 'billing' ), 'billing' );
				$order->set_address( $parent_order->get_address( 'shipping' ), 'shipping' );

				// Set shipping data.
				$order->set_payment_method( $parent_order->get_payment_method() );
				$order->set_payment_method_title( $parent_order->get_payment_method_title() );

				if ( ! wc_tax_enabled() ) {
					// Reports won't track orders fix.
					$order->set_shipping_tax( 0 );
					$order->set_cart_tax( 0 );
				}

				$order->calculate_totals();

				$offer_orders_meta = $parent_order->get_meta( '_cartflows_offer_child_orders' );

				if ( ! is_array( $offer_orders_meta ) ) {
					$offer_orders_meta = array();
				}

				$offer_orders_meta[ $order->get_id() ] = array( 'type' => $type );

				$parent_order->update_meta_data( '_cartflows_offer_child_orders', $offer_orders_meta );

				// Cancel the main order if replace order is enabled.
				$this->cancel_parent_order( $parent_order, $product_data, $type, $order );

				// Save the order.
				$parent_order->save();

				// Save the child order.
				$order->save();

				wcf()->logger->log( 'Child Order created - ' . $order->get_id() );
			}
		}

		if ( $order ) {

			$transaction_id = $product_data['transaction_id'];

			remove_action( 'woocommerce_pre_payment_complete', array( Cartflows_Pro_Frontend::get_instance(), 'maybe_setup_upsell' ), 99, 1 );

			do_action( 'cartflows_child_offer_before_payment_complete', $order, $product_data, $parent_order );

			$this->payment_complete( $order, $transaction_id );

			$order->set_transaction_id( $transaction_id );
			$order->save();

			$transaction_id_note = '';

			if ( ! empty( $transaction_id ) ) {
				$transaction_id_note = sprintf( ' (Transaction ID: %s)', $transaction_id );
			}

			$order->add_order_note( 'Offer Accepted | ' . $type . ' | Step ID - ' . $product_data['step_id'] . ' | ' . $transaction_id_note );

			do_action( 'cartflows_offer_child_order_created', $parent_order, $order, $transaction_id );
			do_action( 'cartflows_offer_child_order_created_' . $parent_order->get_payment_method(), $parent_order, $order, $transaction_id );
		}

		wcf()->logger->log( 'End-' . __CLASS__ . '::' . __FUNCTION__ );

		return $order;
	}

	/**
	 * Cancel the parent order.
	 *
	 * @param object $parent_order order.
	 * @param array  $product_data offer data.
	 * @param string $type offer type.
	 * @param object $order child order.
	 */
	public function cancel_parent_order( $parent_order, $product_data, $type, $order ) {

		$is_cancal_main_order = get_post_meta( $product_data['step_id'], 'wcf-replace-main-order', true );

		if ( 'yes' === $is_cancal_main_order && $product_data['cancal_main_order'] && ! $parent_order->has_status( 'cancelled' ) ) {

			do_action( 'cartflows_offer_before_main_order_cancel', $parent_order );

			$parent_order->update_status( 'cancelled' );
			/* translators: %s step type */
			$parent_order->add_order_note( sprintf( __( 'Order has been cancelled as the user has upgraded to the CartFlows %s order.', 'cartflows-pro' ), $type ) );
			$parent_order->update_meta_data( '_cartflows_main_order_status', 'cancelled' );

			$order->update_meta_data( '_cartflows_offer_amount_diff', $product_data['amount_diff'] );

			do_action( 'cartflows_offer_after_main_order_cancel', $parent_order );
		}
	}

	/**
	 * Complete payment of child order offer.
	 *
	 * @since 1.0.0
	 * @param object $order          order.
	 * @param string $transaction_id Transaction id.
	 */
	public function payment_complete( $order, $transaction_id = '' ) {

		$payment_method = $order->get_payment_method();

		if ( 'cod' === $payment_method ) {
			$order->set_status( 'processing' );
			wc_reduce_stock_levels( $order );
		} elseif ( 'bacs' === $payment_method ) {
			$order->set_status( 'on-hold' );
			wc_reduce_stock_levels( $order );
		} else {
			$order->payment_complete( $transaction_id );
		}
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Orders::get_instance();
