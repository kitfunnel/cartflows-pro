<?php
/**
 * Cartflows Pro license log.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CartFlows_Licence_Log.
 */
class CartFlows_Licence_Log {

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
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->get_license_log();
	}


	/**
	 * Show the log page contents for file log handler.
	 */
	public function get_license_log() {

		if ( ! class_exists( 'CartFlows_Pro_Licence' ) ) {
			return;
		}

		// Get license class instance.
		$cartflows_license_instance = cartflows_pro_license_instance();

		// Get license Key.
		$data        = get_option( 'wc_am_client_' . $cartflows_license_instance->product_id . '_api_key', array() );
		$license_key = isset( $data['api_key'] ) ? $data['api_key'] : '';

		// Prepare license args.
		$args = array(
			'request'     => 'update',
			'slug'        => CARTFLOWS_PRO_SLUG,
			'plugin_name' => $cartflows_license_instance->product_id,
			'version'     => $cartflows_license_instance->wc_am_software_version,
			'product_id'  => CARTFLOWS_PRO_PRODUCT_ID,
			'api_key'     => $license_key,
			'instance'    => $cartflows_license_instance->wc_am_instance_id,
		);

		// Prepare Update Call URL.
		$target_url = esc_url_raw( add_query_arg( 'wc-api', 'wc-am-api', CARTFLOWS_SERVER_URL ) . '&' . http_build_query( $args ) );
		// Ignoring the timeout rule because sometimes it takes time to connect and validate things.
		$request = wp_safe_remote_post( $target_url, array( 'timeout' => 15 ) ); // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout

		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
			return false;
		}

		// Response after update call.
		$response = json_decode( wp_remote_retrieve_body( $request ) );

		include_once CARTFLOWS_PRO_DIR . 'admin-core/views/license-log.php';
	}

}

CartFlows_Licence_Log::get_instance();
