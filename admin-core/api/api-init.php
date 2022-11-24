<?php
/**
 * CartFlows Admin Menu.
 *
 * @package CartFlows
 */

namespace CartflowsProAdmin\AdminCore\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Admin_Menu.
 */
class ApiInit {

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
	 * Instance
	 *
	 * @access private
	 * @var string Class object.
	 * @since 1.0.0
	 */
	private $menu_slug;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */

	/**
	 * Init Hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */

	/**
	 * Register API routes.
	 */
}

ApiInit::get_instance();
