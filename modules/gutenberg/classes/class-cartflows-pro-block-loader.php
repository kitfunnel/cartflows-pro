<?php
/**
 * Cartflows Pro Blocks Loader.
 *
 * @package Cartflows Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_Block_Loader' ) ) {

	/**
	 * Class BSF_Cartflows_Pro_Loader.
	 */
	final class Cartflows_Pro_Block_Loader {

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

			define( 'CFP_TABLET_BREAKPOINT', '976' );
			define( 'CFP_MOBILE_BREAKPOINT', '767' );

			$this->load_helper_files();
			$this->load_extended_blocks();

			if ( _is_cartflows_pluspro() ) {
				$this->load_gb_blocks();
			}

		}

			/**
			 * Loads plugin files.
			 *
			 * @since 1.6.13
			 *
			 * @return void
			 */
		public function load_helper_files() {
			require_once CARTFLOWS_PRO_DIR . 'modules/gutenberg/classes/class-cartflows-pro-gb-helper.php';
			require_once CARTFLOWS_PRO_DIR . 'modules/gutenberg/classes/class-cartflows-pro-init-blocks.php';
		}

		/**
		 * Loads plugin files.
		 *
		 * @since 1.6.13
		 *
		 * @return void
		 */
		public function load_extended_blocks() {

			if ( wcf()->is_woo_active ) {
				require_once CARTFLOWS_PRO_DIR . 'modules/gutenberg/dist/blocks/cartflows-pro-gb-checkout-form-extend/class-cartflows-pro-checkout-form.php';
			}
		}

		/**
		 * Loads plugin files.
		 *
		 * @since 1.6.13
		 *
		 * @return void
		 */
		public function load_gb_blocks() {

			if ( wcf()->is_woo_active ) {
				require_once CARTFLOWS_PRO_DIR . 'modules/gutenberg/dist/blocks/offer-product-title/class-cartflows-pro-gb-block-product-title.php';
				require_once CARTFLOWS_PRO_DIR . 'modules/gutenberg/dist/blocks/offer-product-description/class-cartflows-pro-gb-block-product-description.php';
				require_once CARTFLOWS_PRO_DIR . 'modules/gutenberg/dist/blocks/offer-product-price/class-cartflows-pro-gb-block-product-price.php';
				require_once CARTFLOWS_PRO_DIR . 'modules/gutenberg/dist/blocks/offer-product-quantity/class-cartflows-pro-gb-block-product-quantity.php';
				require_once CARTFLOWS_PRO_DIR . 'modules/gutenberg/dist/blocks/offer-product-variation/class-cartflows-pro-gb-block-product-variation.php';
				require_once CARTFLOWS_PRO_DIR . 'modules/gutenberg/dist/blocks/offer-product-image/class-cartflows-pro-gb-block-product-image.php';
				require_once CARTFLOWS_PRO_DIR . 'modules/gutenberg/dist/blocks/cartflows-pro-gb-checkout-form-extend/class-cartflows-pro-checkout-form.php';
			}
		}
	}

	Cartflows_Pro_Block_Loader::get_instance();
}

