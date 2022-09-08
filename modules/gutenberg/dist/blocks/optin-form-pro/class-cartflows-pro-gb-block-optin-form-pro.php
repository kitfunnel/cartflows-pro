<?php
/**
 * WCFPB - Optin Form Pro.
 *
 * @package Cartflows Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_Gb_Block_Optin_Form_Pro' ) ) {

	/**
	 * Class Cartflows_Pro_Gb_Block_Optin_Form_Pro.
	 */
	class Cartflows_Pro_Gb_Block_Optin_Form_Pro {

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
	 *  Prepare if class 'Cartflows_Pro_Gb_Block_Optin_Form_Pro' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Pro_Gb_Block_Optin_Form_Pro::get_instance();
}
