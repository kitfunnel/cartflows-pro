<?php
/**
 * Cartflows Checkout Field Optimization.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Checkout_Tab_Animation.
 */
class Cartflows_Pro_Checkout_Tab_Animation {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Checkout ID
	 *
	 * @var checkout_id
	 */
	public static $cached_checkout_id = null;

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
		add_action( 'wp_enqueue_scripts', array( $this, 'add_frontend_scripts' ) );
	}

	/**
	 *  Add frontend scripts.
	 */
	public function add_frontend_scripts() {

		if ( ! _is_wcf_checkout_type() ) {
			return;
		}

		global $post;

		$checkout_id = $post->ID;

		$this->add_frontend_localize_animate_scripts( $checkout_id );
	}

	/**
	 * Add localize script for animate title.
	 *
	 * @param int $checkout_id Checkout id.
	 */
	public function add_frontend_localize_animate_scripts( $checkout_id ) {

		$localize['enabled'] = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-animate-browser-tab' );
		$localize['title']   = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-animate-browser-tab-title' );

		$localize_script  = '<!-- script to print the admin localized variables -->';
		$localize_script .= '<script type="text/javascript">';
		$localize_script .= 'var cartflows_animate_tab_fields = ' . wp_json_encode( $localize ) . ';';
		$localize_script .= '</script>';

		// Reason for PHPCS ignore: Used to localize the strings which will be displayed on the browser's tab.
		echo $localize_script; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Admin' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Checkout_Tab_Animation::get_instance();
