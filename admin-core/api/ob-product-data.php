<?php
/**
 * CartFlows Order Bump Product data.
 *
 * @package CartFlows Pro
 */

namespace CartflowsProAdmin\AdminCore\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsProAdmin\AdminCore\Api\ApiBase;

/**
 * Class ObProductData.
 */
class ObProductData extends ApiBase {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '/admin/ob-product-data/';

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 2.0.0
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 2.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Init Hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_routes() {

		$namespace = $this->get_api_namespace();

		register_rest_route(
			$namespace,
			$this->rest_base . '(?P<id>[\d-]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Step ID.', 'cartflows-pro' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_ob_item_data' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get ob item data.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_ob_item_data( $request ) {

		$product_id = $request->get_param( 'id' );

		/* Prepare data */
		$data = array(
			'id' => $product_id,
		);

		if ( ! wcf()->is_woo_active ) {
			return new \WP_Error( 'cartflows_rest_cannot_call', __( 'Sorry, WooCommerce need to be installed & activated to call API.', 'cartflows-pro' ) );
		}

		$product = wc_get_product( $product_id );

		if ( $product ) {

			$admin_helper = \Cartflows_Pro_Admin_Helper::get_instance();
			$product_data = $admin_helper::get_products_label( array( $product_id ) );


			$product_image = get_the_post_thumbnail_url( $product_id );
			if ( empty( $product_image ) ) {
				$product_image = esc_url_raw( CARTFLOWS_PRO_URL . 'assets/images/image-placeholder.png' );
			}

			$product_desc = $product->get_short_description() . '<br>' . "\r\n{{product_price}}";


			$product_price_data               = \Cartflows_Pro_Order_Bump_Product::get_instance()->get_taxable_product_price( $product, $product_data[0]['original_price'], 0 );
			$product_data[0]['display_price'] = '<span class="wcf-normal-price">' . wc_price( $product_price_data['product_price'] ) . '</span>';


			$data = array(
				'product'       => $product_data,
				'desc_text'     => $product_desc,
				'product_image' => $product_image,
			);
		}

		$response = new \WP_REST_Response( $data );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * Check whether a given request has permission to read notes.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return new \WP_Error( 'cartflows_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'cartflows-pro' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}
}
