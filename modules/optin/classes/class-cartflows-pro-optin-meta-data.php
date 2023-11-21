<?php
/**
 * Checkout post meta
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Meta Boxes setup
 */
class Cartflows_Pro_Optin_Meta_Data {

	/**
	 * Instance
	 *
	 * @var Cartflows_Pro_Optin_Meta_Data $instance Class object
	 */
	private static $instance;

	/**
	 * Meta Option
	 *
	 * @var array $meta_option Array of meta option
	 */
	private static $meta_option = null;

	/**
	 * Initiator
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

	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Optin_Meta_Data::get_instance();
