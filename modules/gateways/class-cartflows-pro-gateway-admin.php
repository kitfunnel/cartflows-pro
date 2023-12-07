<?php
/**
 * CartFlows Gateway Admin.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Gateway_Admin.
 */
class Cartflows_Pro_Gateway_Admin {

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

		add_filter( 'cartflows_admin_localized_vars', array( $this, 'localize_required_vars' ), 10, 1 );
	}

	/**
	 * Get payment gateways.
	 *
	 * @param array $localize localized variables.
	 */
	public function localize_required_vars( $localize ) {

		if ( ! wcf_pro()->is_woo_active ) {
			return $localize;
		}

		if ( ! class_exists( 'Cartflows_Pro_Gateways' ) ) {
			return $localize;

		}

		$supported_gateways = Cartflows_Pro_Gateways::get_instance()->get_supported_gateways();

		$woo_available_gateways = WC()->payment_gateways->get_available_payment_gateways();

		$available_gateways = array();

		foreach ( $woo_available_gateways as  $key => $value ) {
			$available_gateways[ $key ]['method_title'] = $value->method_title;
		}

		$localize['supported_payment_gateways'] = $supported_gateways;

		$localize['available_payment_gateways'] = $available_gateways;

		return $localize;
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Gateway_Admin' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateway_Admin::get_instance();
