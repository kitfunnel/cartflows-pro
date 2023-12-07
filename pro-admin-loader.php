<?php
/**
 * CartFlows Admin.
 *
 * @package CartFlows
 */

namespace CartflowsProAdmin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use CartflowsProAdmin\AdminCore\Api\ApiInit;
use CartflowsProAdmin\AdminCore\Ajax\AjaxInit;
use CartflowsProAdmin\AdminCore\Inc\AdminHooks;
use CartflowsProAdmin\AdminCore\Inc\GlobalSettingsPro;
use CartflowsProAdmin\AdminCore\Inc\StoreCheckout;

/**
 * Class Admin_Loader.
 */
class Pro_Admin_Loader {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Autoload classes.
	 *
	 * @param string $class class name.
	 */
	public function autoload( $class ) {

		if ( 0 !== strpos( $class, __NAMESPACE__ ) ) {
			return;
		}

		$class_to_load = $class;

		if ( ! class_exists( $class_to_load ) ) {
			$filename = strtolower(
				preg_replace(
					array( '/^' . __NAMESPACE__ . '\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ),
					array( '', '$1-$2', '-', DIRECTORY_SEPARATOR ),
					$class_to_load
				)
			);

			$file = CARTFLOWS_PRO_DIR . $filename . '.php';

			// if the file redable, include it.
			if ( is_readable( $file ) ) {
				include $file;
			}
		}
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		spl_autoload_register( array( $this, 'autoload' ) );
		$this->setup_classes();
	}

	/**
	 * Include required classes.
	 */
	public function setup_classes() {

		/* Init API */
		ApiInit::get_instance();

		GlobalSettingsPro::get_instance();

		AdminHooks::get_instance();

		StoreCheckout::get_instance();

		if ( is_admin() ) {
			/* Ajax init */
			AjaxInit::get_instance();

		}
	}
}

Pro_Admin_Loader::get_instance();
