<?php
/**
 * Elementor Classes.
 *
 * @package cartflows
 */

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Checkout Form Widget
 *
 * @since 1.6.13
 */
class CartFlows_Pro_Optin_Form_Extend {

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
	 * Setup actions and filters.
	 *
	 * @since 1.6.13
	 */
	private function __construct() {

	}

	/**
	 * Settings
	 *
	 * @since 1.6.13
	 * @var object $settings
	 */
	public static $settings;

}
/**
 * Initiate the class.
 */
CartFlows_Pro_Optin_Form_Extend::get_instance();
