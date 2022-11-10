<?php
/**
 * Modules Loader
 *
 * @package Beaver Builder Modules
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Initial Setup
 *
 * @since 1.6.13
 */
class Cartflows_Pro_BB_Modules_Loader {

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
	 * Constructor function that initializes required actions and hooks
	 */
	public function __construct() {

		if ( _is_cartflows_pluspro() ) {
			add_action( 'wp', array( $this, 'init' ), 2 );
			add_action( 'admin_init', array( $this, 'register_pro_modules' ), 2 );
		}
		$this->modules_extend_files();
	}

	/**
	 * Function that initializes init function
	 *
	 * @since 1.6.13
	 */
	public function init() {
		$this->include_modules_files();
	}

	/**
	 * Returns Script array.
	 *
	 * @return array()
	 * @since 1.6.13
	 */
	public static function get_module_list() {

		$widget_list = array(
			'cartflows-pro-bb-offer-action-button',
			'cartflows-pro-bb-offer-action-link',
			'cartflows-pro-bb-offer-product-description',
			'cartflows-pro-bb-offer-product-price',
			'cartflows-pro-bb-offer-product-variation',
			'cartflows-pro-bb-offer-product-image',
			'cartflows-pro-bb-offer-product-title',
			'cartflows-pro-bb-offer-product-quantity',
		);

		return $widget_list;
	}

	/**
	 * Include Widgets files
	 *
	 * Load widgets files
	 *
	 * @since 1.6.13
	 * @access public
	 */
	public function include_modules_files() {

		global $post;

		if ( ! isset( $post ) ) {
			return;
		}

		$post_type = $post->post_type;

		if ( 'cartflows_step' === $post_type && class_exists( 'FLBuilder' ) ) {

			$this->get_module_files();
		}
	}

	/**
	 * Register CartFlows Pro Modules
	 *
	 * Register modules early to disply them on options page
	 *
	 * @since 1.6.13
	 * @access public
	 */
	public function register_pro_modules() {

		if ( is_admin() && isset( $_GET['page'] ) && 'fl-builder-settings' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) && class_exists( 'FLBuilder' ) ) {
			$this->get_module_files();
		}
	}

	/**
	 * Get Module files
	 *
	 * @since 1.6.13
	 * @access public
	 */
	public function get_module_files() {

		$widget_list = $this->get_module_list();

		if ( ! empty( $widget_list ) ) {
			foreach ( $widget_list as $handle => $data ) {
				include_once CARTFLOWS_PRO_DIR . 'modules/beaver-builder/' . $data . '/' . $data . '.php';
			}
		}
	}

	/**
	 * Extend widget with pro functionality.
	 *
	 * @since 1.6.13
	 */
	public function modules_extend_files() {

		if ( class_exists( 'FLBuilder' ) ) {
			include_once CARTFLOWS_PRO_DIR . 'modules/beaver-builder/classes/class-cartflows-pro-bb-helper.php';

			require_once CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-checkout-form-extend/cartflows-pro-bb-checkout-form-extend.php';
			require_once CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-optin-form-extend/cartflows-pro-bb-optin-form-extend.php';
		}

	}

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_BB_Modules_Loader::get_instance();
