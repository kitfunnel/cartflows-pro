<?php
/**
 * WOO Multicurrency.
 *
 * Compatibility of Plugin: WooCommerce Multi-currency
 * Plugin URI: https://woocommerce.com/products/multi-currency/
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WOOMC\Currency\Detector;
use WOOMC\Product\Info;

/**
 * Class Cartflows_Pro_Utils.
 */
class Cartflows_Pro_Woo_Multicurrency {

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
		add_filter( 'cartflows_filter_display_price', array( $this, 'wcf_modify_product_price' ), 10, 3 );
	}

	/**
	 * Price Converter
	 *
	 * @param int    $product_price product price.
	 * @param int    $product_id current product ID.
	 * @param string $context The context of the action.
	 *
	 * @return int
	 */
	public function wcf_modify_product_price( $product_price, $product_id, $context ) {
		$api_obj = new WOOMC\API();

		$converted_price = '';

		// Convert the received default currency value to the selected currency value.
		$converted_price = $api_obj->convert( $product_price, Detector::currency_from_cookie(), get_option( 'woocommerce_currency' ) );

		if ( 'product' === $context ) {
			// Fetches selected currency price if set from multi currency plugin in product setting.
			$converted_price = $this->get_converted_fixed_product_prices( $product_id, $converted_price );
		}

		// Return the converted value.
		return $converted_price;
	}

	/**
	 * Fetches selected currency price if set from multi currency plugin product setting.
	 *
	 * @param int $product_id current product ID.
	 * @param int $converted_price already converted price.
	 * @return int
	 */
	public function get_converted_fixed_product_prices( $product_id, $converted_price ) {

		$product      = wc_get_product( $product_id );
		$product_info = new Info( $product );
		$price_key    = 'price';

		if ( defined( 'WOOCOMMERCE_MULTICURRENCY_VERSION' ) ) {
			$price_key = version_compare( WOOCOMMERCE_MULTICURRENCY_VERSION, '2.11.0', '>=' ) ? '_price' : $price_key;
		}

		// Return already converted sale or regular price of product.
		return $product_info->is_custom_priced() ? $product_info->get_custom_price( $price_key ) : $converted_price;

	}

}

/**
 *  Prepare if class 'Cartflows_Pro_Frontend' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Woo_Multicurrency::get_instance();

