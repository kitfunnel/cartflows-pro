<?php
/**
 * Cartflows Pro tracking.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Tracking.
 */
class Cartflows_Pro_Tracking {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var fb_pixel_settings
	 */
	private static $fb_pixel_settings;

	/**
	 * Member Variable
	 *
	 * @var ga_settings
	 */
	private static $ga_settings;

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

		self::$fb_pixel_settings = Cartflows_Helper::get_facebook_settings();

		self::$ga_settings = Cartflows_Helper::get_google_analytics_settings();

		if ( 'enable' === self::$fb_pixel_settings['facebook_pixel_tracking'] ) {
			/* Facebook Pixel */
			add_action( 'wcf_order_bump_item_added', array( $this, 'trigger_fb_event' ) );
			add_action( 'wcf_after_quantity_update', array( $this, 'trigger_fb_event' ) );
			add_action( 'wcf_after_force_all_selection', array( $this, 'trigger_fb_event' ) );
			add_action( 'wcf_after_multiple_selection', array( $this, 'trigger_fb_event' ) );
			add_action( 'wcf_after_single_selection', array( $this, 'trigger_fb_event' ) );

			add_action( 'cartflows_offer_product_processed', array( $this, 'save_offer_data_for_fb' ), 10, 3 );
			add_filter( 'cartflows_view_content_offer', array( $this, 'trigger_offer_viewcontent_event' ), 10, 2 );
			add_action( 'cartflows_facebook_pixel_events', array( $this, 'trigger_offer_purchase_event' ), 10 );

			add_action( 'wcf_order_bump_item_removed', array( $this, 'update_cart_data_for_fb_event' ) );
		}

		if ( 'enable' === self::$ga_settings['enable_google_analytics'] ) {
			/* Google analyics add to cart */
			add_action( 'wcf_order_bump_item_added', array( $this, 'trigger_ga_add_to_cart_event' ) );
			add_action( 'wcf_after_quantity_update', array( $this, 'trigger_ga_add_to_cart_event' ) );
			add_action( 'wcf_after_force_all_selection', array( $this, 'trigger_ga_add_to_cart_event' ) );
			add_action( 'wcf_after_multiple_selection', array( $this, 'trigger_ga_add_to_cart_event' ) );
			add_action( 'wcf_after_single_selection', array( $this, 'trigger_ga_add_to_cart_event' ) );

			/* Google analyics remove from cart */
			add_action( 'wcf_order_bump_item_removed', array( $this, 'trigger_ga_remove_from_cart_event' ) );

			add_action( 'cartflows_offer_product_processed', array( $this, 'save_offer_data_for_ga' ), 10, 3 );
			add_action( 'cartflows_google_analytics_events', array( $this, 'trigger_offer_purchase_event_for_ga' ), 10 );
		}
	}

	/**
	 * Update the cart data for add payment info event when order bump product is removed from cart.
	 */
	public function update_cart_data_for_fb_event() {

		add_filter(
			'woocommerce_update_order_review_fragments',
			function( $data ) {

				if ( 'enable' === self::$fb_pixel_settings['facebook_pixel_add_payment_info'] ) {
					$data['fb_add_payment_info_data'] = wp_json_encode( Cartflows_Tracking::get_instance()->prepare_cart_data_fb_response( 'add_payment_info' ) );
				}

				return $data;
			}
		);

	}

	/**
	 * Add updated cart and product in Ajax response.
	 *
	 * @param integer $product_id product id.
	 */
	public function trigger_fb_event( $product_id ) {

		add_filter(
			'woocommerce_update_order_review_fragments',
			function( $data ) use ( $product_id ) {
				$data['added_to_cart_data'] = $this->prepare_fb_response( $product_id );

				if ( 'enable' === self::$fb_pixel_settings['facebook_pixel_add_payment_info'] ) {
					$data['fb_add_payment_info_data'] = wp_json_encode( Cartflows_Tracking::get_instance()->prepare_cart_data_fb_response( 'add_payment_info' ) );
				}

				return $data;
			}
		);

	}

	/**
	 * Save the offer details in transient to use for facebook pixel.
	 *
	 * @param object $parent_order parent order id.
	 * @param array  $offer_data offer product data.
	 * @param object $child_order child order.
	 */
	public function save_offer_data_for_fb( $parent_order, $offer_data, $child_order = null ) {
		$order_id = null;
		$user_key = WC()->session->get_customer_id();

		if ( $child_order ) {
			$order_id = $child_order->get_id();
		} else {
			$order_id = $parent_order->get_id();
		}

		$data = array(
			'order_id'      => $order_id,
			'offer_product' => $offer_data,
		);

		set_transient( 'wcf-offer-details-for-fbp-' . $user_key, $data );
	}


	/**
	 * Save the offer details in transient to use for Google Analytics.
	 *
	 * @param object $parent_order parent order id.
	 * @param array  $offer_data offer product data.
	 * @param object $child_order child order.
	 */
	public function save_offer_data_for_ga( $parent_order, $offer_data, $child_order = null ) {

		$order_id = null;
		$user_key = WC()->session->get_customer_id();

		if ( $child_order ) {
			$order_id = $child_order->get_id();
		} else {
			$order_id = $parent_order->get_id();
		}

		$data = array(
			'order_id'      => $order_id,
			'offer_product' => $offer_data,
		);

		set_transient( 'wcf-offer-details-for-ga-' . $user_key, $data );
	}

	/**
	 * Trigger the purchase event for the upsell/downsell offer.
	 */
	public function trigger_offer_purchase_event() {

		if ( isset( $_GET['wcf-order'] ) && 'enable' === self::$fb_pixel_settings['facebook_pixel_purchase_complete'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$order_id = intval( $_GET['wcf-order'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$user_key = WC()->session->get_customer_id();

			$offer_data = get_transient( 'wcf-offer-details-for-fbp-' . $user_key );

			if ( empty( $offer_data ) ) {
				return;
			}

			$purchase_details = $this->prepare_offer_purchase_data_fb_response( $order_id, $offer_data );

			delete_transient( 'wcf-offer-details-for-fbp-' . $user_key );

			if ( ! empty( $purchase_details ) ) {

				$purchase_details = wp_json_encode( $purchase_details );
				$event_script     = "
					<script type='text/javascript'>
						fbq( 'track', 'Purchase', $purchase_details );
					</script>";

				// Reason for PHPCS ignore: Printing Google Analytics script. No input taken from the user except GA ID.
				echo html_entity_decode( $event_script ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}

	/**
	 * Trigger the view content event for the upsell/downsell offer.
	 *
	 * @param array $params Facebook event parameters array.
	 * @param int   $step_id current step ID.
	 *
	 * @return array
	 */
	public function trigger_offer_viewcontent_event( $params, $step_id ) {

		// Upsell/Downsell Page.
		if ( isset( $step_id ) && wcf()->utils->check_is_offer_page( $step_id ) ) {
			return $this->prepare_viewcontent_data_fb_response( $params, $step_id );
		}

		return $params;
	}

	/**
	 * Prepare view content data for fb response.
	 *
	 * @param array $params Facebook event parameters array.
	 * @param int   $step_id current step id.
	 *
	 * @return array
	 */
	public function prepare_viewcontent_data_fb_response( $params, $step_id ) {

		$product_data   = array();
		$content_ids    = array();
		$category_names = '';
		$product_names  = '';

		// Get offer page data.
		$offer_product = wcf_pro()->utils->get_offer_data( $step_id );

		// Add offer data only if the offer data is array and set.
		if ( ! empty( $offer_product ) && is_array( $offer_product ) ) {

			$content_ids[]  = $offer_product['id'];
			$category_names = wp_strip_all_tags( wc_get_product_category_list( $offer_product['id'] ) );

			$product_data = array(
				'cart_contents'  => array(
					'id'       => $offer_product['id'],
					'name'     => $offer_product['name'],
					'price'    => $offer_product['price'],
					'quantity' => $offer_product['qty'],
				),
				'content_ids'    => $offer_product['id'],
				'product_names'  => $offer_product['name'],
				'category_names' => $category_names,
			);

			$params['content_ids']  = $product_data['content_ids'];
			$params['currency']     = get_woocommerce_currency();
			$params['value']        = $offer_product['total'];
			$params['content_type'] = 'product';
			$params['contents']     = wp_json_encode( $product_data['cart_contents'] );
		}

		return $params;
	}

	/**
	 * Trigger the purchase event for the upsell/downsell offer.
	 */
	public function trigger_offer_purchase_event_for_ga() {

		if ( isset( $_GET['wcf-order'] ) && 'enable' === self::$ga_settings['enable_add_payment_info'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$order_id = intval( $_GET['wcf-order'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$user_key   = WC()->session->get_customer_id();
			$offer_data = get_transient( 'wcf-offer-details-for-ga-' . $user_key );

			if ( empty( $offer_data ) ) {
				return;
			}

			$purchase_details = $this->prepare_offer_purchase_data_ga_response( $order_id, $offer_data );
			delete_transient( 'wcf-offer-details-for-ga-' . $user_key );

			if ( ! empty( $purchase_details ) ) {

				$purchase_data = wp_json_encode( $purchase_details );

				$event_script = "
					<script type='text/javascript'>
						gtag( 'event', 'purchase', $purchase_data );
					</script>";

				// Reason for PHPCS ignore: Printing Google Analytics script. No input taken from the user except GA ID.
				echo html_entity_decode( $event_script ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}

	/**
	 * Prepare the purchase event data for the facebook pixel.
	 *
	 * @param integer $order_id order id.
	 * @param array   $offer_data offer data.
	 */
	public function prepare_offer_purchase_data_fb_response( $order_id, $offer_data ) {

		$purchase_data = array();

		$product_data = $offer_data['offer_product'];

		if ( empty( $product_data ) ) {
			return $purchase_data;
		}

		$purchase_data['content_type'] = 'product';
		$purchase_data['currency']     = wcf()->options->get_checkout_meta_value( $order_id, '_order_currency' );
		$purchase_data['userAgent']    = wcf()->options->get_checkout_meta_value( $order_id, '_customer_user_agent' );
		$purchase_data['plugin']       = 'CartFlows-Offer';

		$purchase_data['content_ids'][]      = (string) $product_data['id'];
		$purchase_data['content_names'][]    = $product_data['name'];
		$purchase_data['content_category'][] = wp_strip_all_tags( wc_get_product_category_list( $product_data['id'] ) );
		$purchase_data['value']              = $product_data['total'];
		$purchase_data['transaction_id']     = $offer_data['order_id'];

		if ( ! wcf_pro()->utils->is_separate_offer_order() ) {
			$purchase_data['transaction_id'] = $offer_data['order_id'] . '_' . $product_data['step_id'];
		}

		return $purchase_data;
	}

	/**
	 * Prepare the purchase event data for the google analytics.
	 *
	 * @param integer $order_id order id.
	 * @param array   $offer_data offer data.
	 */
	public function prepare_offer_purchase_data_ga_response( $order_id, $offer_data ) {

		$purchase_data = array();

		$product_data = $offer_data['offer_product'];

		if ( empty( $product_data ) ) {
			return $purchase_data;
		}

		$ga_tracking_id = esc_attr( self::$ga_settings['google_analytics_id'] );

		$shipping_tax = $product_data['shipping_fee_tax'] - $product_data['shipping_fee'];
		$products_tax = $product_data['qty'] * ( $product_data['unit_price_tax'] - intval( $product_data['unit_price'] ) );

		$purchase_data = array(
			'send_to'         => $ga_tracking_id,
			'event_category'  => 'Enhanced-Ecommerce',
			'transaction_id'  => $offer_data['order_id'],
			'affiliation'     => get_bloginfo( 'name' ),
			'value'           => $this->format_number( $product_data['total'] ),
			'currency'        => wcf()->options->get_checkout_meta_value( $order_id, '_order_currency' ),
			'shipping'        => $product_data['shipping_fee_tax'],
			'tax'             => $this->format_number( $shipping_tax + $products_tax ),
			'items'           => array(
				array(
					'id'       => $product_data['id'],
					'name'     => $product_data['name'],
					'quantity' => $product_data['qty'],
					'price'    => $this->format_number( $product_data['unit_price_tax'] ),
				),
			),
			'non_interaction' => true,
		);

		if ( ! wcf_pro()->utils->is_separate_offer_order() ) {
			$purchase_data['transaction_id'] = $offer_data['order_id'] . '_' . $product_data['step_id'];
		}

		return $purchase_data;
	}




	/**
	 * Add updated cart and product in Ajax response.
	 *
	 * @param integer $product_id product id.
	 */
	public function trigger_ga_add_to_cart_event( $product_id ) {

		add_filter(
			'woocommerce_update_order_review_fragments',
			function( $data ) use ( $product_id ) {
				$data['ga_added_to_cart_data'] = $this->prepare_ga_response( $product_id );

				if ( 'enable' === self::$ga_settings['enable_add_payment_info'] ) {
					$data['ga_add_payment_info_data'] = wp_json_encode( Cartflows_Tracking::get_instance()->prepare_cart_data_ga_response() );
				}

				return $data;
			}
		);

	}

	/**
	 * Add updated cart and product in Ajax response.
	 *
	 * @param integer $product_id product id.
	 */
	public function trigger_ga_remove_from_cart_event( $product_id ) {

		add_filter(
			'woocommerce_update_order_review_fragments',
			function( $data ) use ( $product_id ) {

				$product_data = array();
				if ( isset( $data['removed_order_bump_data'] ) ) {
					$product_data = $data['removed_order_bump_data'];
				}
				$data['ga_remove_to_cart_data'] = $this->prepare_ga_response( $product_id, $product_data );

				if ( 'enable' === self::$ga_settings['enable_add_payment_info'] ) {
					$data['ga_add_payment_info_data'] = wp_json_encode( Cartflows_Tracking::get_instance()->prepare_cart_data_ga_response() );
				}

				return $data;
			}
		);
	}

	/**
	 * Prepare response for facebook.
	 *
	 * @param integer $product_id product id.
	 * @return array
	 */
	public function prepare_fb_response( $product_id ) {

		$response     = array();
		$product_data = array();
		$product      = wc_get_product( $product_id );
		$items        = WC()->cart->get_cart();

		foreach ( $items as $index => $item ) {
			if ( $item['product_id'] === $product_id ) {
				$product_data = $item;
				break;
			}
		}

		if ( ! empty( $product_data ) ) {

			$add_to_cart['content_type']       = 'product';
			$add_to_cart['plugin']             = 'CartFlows-OrderBump';
			$add_to_cart['user_roles']         = implode( ', ', wp_get_current_user()->roles );
			$add_to_cart['content_category'][] = wp_strip_all_tags( wc_get_product_category_list( $product->get_id() ) );
			$add_to_cart['currency']           = get_woocommerce_currency();
			$add_to_cart['value']              = $this->format_number( $product_data['line_subtotal'] + $product_data['line_subtotal_tax'] );
			$add_to_cart['content_name']       = $product->get_title();
			$add_to_cart['content_ids'][]      = (string) $item['product_id'];

			$add_to_cart['contents'] = wp_json_encode(
				array(
					array(
						'id'         => $product_data['product_id'],
						'name'       => $product->get_title(),
						'quantity'   => $product_data['quantity'],
						'item_price' => $this->format_number( $product_data['line_subtotal'] + $product_data['line_subtotal_tax'] ),
					),
				)
			);

			// Put it in single variable.
			$response['added_to_cart'] = $add_to_cart;
		}

		return $response;

	}

	/**
	 * Prepare response for Google Analytics for Bump Order.
	 *
	 * @param integer $product_id product id.
	 * @param array   $product_data product data.
	 */
	public function prepare_ga_response( $product_id, $product_data = array() ) {

		$response = array();
		$data     = array(
			'quantity' => 1,
			'price'    => 0,
		);

		$product = wc_get_product( $product_id );

		if ( $product ) {

			$items = WC()->cart->get_cart();

			foreach ( $items as $index => $item ) {
				if ( $item['product_id'] === $product_id ) {
					$data['quantity'] = $item['quantity'];
					$data['price']    = $item['line_subtotal'] + $item['line_subtotal_tax'];
					break;
				}
			}

			// For remove from cart event of the order bump.
			if ( ! empty( $product_data ) ) {
				$data['quantity'] = $product_data['quantity'];
				$data['price']    = $product_data['line_subtotal'] + $product_data['line_subtotal_tax'];
			}

			$add_to_cart_ob = array(
				'send_to'         => self::$ga_settings['google_analytics_id'],
				'event_category'  => 'Enhanced-Ecommerce',
				'currency'        => get_woocommerce_currency(),
				'value'           => $this->format_number( $data['price'] ),
				'items'           => array(
					array(
						'id'       => $product_id,
						'name'     => $product->get_title(),
						'sku'      => $product->get_sku(),
						'category' => wp_strip_all_tags( wc_get_product_category_list( $product->get_id() ) ),
						'price'    => $this->format_number( $data['price'] ),
						'quantity' => $data['quantity'],
					),
				),
				'non_interaction' => true,
			);

			$response['add_to_cart']      = wp_json_encode( $add_to_cart_ob );
			$response['remove_from_cart'] = wp_json_encode( $add_to_cart_ob );
		}

		return $response;
	}

	/**
	 * Get decimal of price.
	 *
	 * @param integer $price price.
	 */
	public function format_number( $price ) {

		return number_format( floatval( $price ), wc_get_price_decimals(), '.', '' );
	}

}
/**
 *  Prepare if class 'Cartflows_Pro_Frontend' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Tracking::get_instance();
