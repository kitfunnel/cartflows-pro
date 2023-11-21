<?php
/**
 * Downsell markup
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Checkout Markup
 *
 * @since 1.0.0
 */
class Cartflows_Downsell_Markup extends Cartflows_Pro_Base_Offer_Markup {


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
	 *  Constructor
	 */
	public function __construct() {

		add_action( 'wp_ajax_wcf_downsell_accepted', array( $this, 'process_downsell_accepted' ) );
		add_action( 'wp_ajax_nopriv_wcf_downsell_accepted', array( $this, 'process_downsell_accepted' ) );

		add_action( 'wp_ajax_wcf_downsell_rejected', array( $this, 'process_downsell_rejected' ) );
		add_action( 'wp_ajax_nopriv_wcf_downsell_rejected', array( $this, 'process_downsell_rejected' ) );

	}

	/**
	 * Process down sell acceptance.
	 *
	 * @param boolean $verify_nonce nonce check.
	 */
	public function process_downsell_accepted( $verify_nonce = true ) {

		$nonce = isset( $_POST['_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ) : '';
		if ( $verify_nonce && ! wp_verify_nonce( $nonce, 'wcf_downsell_accepted' ) ) {
			return;
		}

		$offer_action = isset( $_POST['offer_action'] ) ? sanitize_text_field( wp_unslash( $_POST['offer_action'] ) ) : '';
		$step_id      = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
		$product_id   = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		$order_id     = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : 0;
		$order_key    = isset( $_POST['order_key'] ) ? sanitize_text_field( wp_unslash( $_POST['order_key'] ) ) : '';

		$variation_id = '';
		$input_qty    = '';

		if ( isset( $_POST['variation_id'] ) ) {
			$variation_id = intval( $_POST['variation_id'] );
		}

		if ( isset( $_POST['input_qty'] ) && ! empty( $_POST['input_qty'] ) ) {
			$input_qty = intval( $_POST['input_qty'] );
		}

		$result = array(
			'status'   => 'failed',
			'redirect' => '#',
			'message'  => __( 'Order does not exist', 'cartflows-pro' ),
		);

		if ( $order_id && $product_id ) {

			$result = array(
				'status'   => 'failed',
				'redirect' => '#',
				'message'  => __( 'Downsell Payment Failed', 'cartflows-pro' ),
			);

			$order = wc_get_order( $order_id );

			$extra_data = array(
				'order_id'      => $order_id,
				'product_id'    => $product_id,
				'variation_id'  => $variation_id,
				'input_qty'     => $input_qty,
				'order_key'     => $order_key,
				'template_type' => 'downsell',
				'action'        => 'wcf_downsell_accepted',
			);

			$result = $this->offer_accepted( $step_id, $extra_data, $result );
		}

		// send json.
		wp_send_json( $result );
	}

	/**
	 * Process down sell rejected.
	 */
	public function process_downsell_rejected() {

		$nonce = isset( $_POST['_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'wcf_downsell_rejected' ) ) {
			return;
		}

		$step_id   = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
		$order_id  = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
		$order_key = isset( $_POST['order_key'] ) ? sanitize_text_field( wp_unslash( $_POST['order_key'] ) ) : '';

		$result = array(
			'status'   => 'failed',
			'redirect' => '#',
			'message'  => __( 'Current Step Not Found', 'cartflows-pro' ),
		);

		if ( $step_id ) {

			$result = array(
				'status'   => 'failed',
				'redirect' => '#',
				'message'  => __( 'Order does not exist', 'cartflows-pro' ),
			);

			if ( $order_id ) {

				$extra_data = array(
					'action'        => 'offer_rejected',
					'order_id'      => $order_id,
					'order_key'     => $order_key,
					'template_type' => 'downsell',
				);
			}

			$result = $this->offer_rejected( $step_id, $extra_data, $result );
		}

		// send json.
		wp_send_json( $result );
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Downsell_Markup::get_instance();
