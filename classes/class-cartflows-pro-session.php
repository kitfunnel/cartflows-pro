<?php
/**
 * Session
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
// Ignoring below rule as we need to find out the best to replace the already build cookie logic.
//phpcs:disable WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE
/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Session {


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
	 *  Constructor
	 */
	public function __construct() {

		define( 'CARTFLOWS_SESSION_EXPIRE_TIME', 30 );

	}

	/**
	 *  Get cookie path
	 *
	 * @return string cookiepath.
	 */
	public function get_cookiepath() {

		return COOKIEPATH ? COOKIEPATH : '/';
	}

	/**
	 *  Set session
	 *
	 * @param int   $flow_id flow ID.
	 * @param array $data trasient data.
	 */
	public function set_session( $flow_id, $data = array() ) {

		if ( isset( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) ) {
			$key = sanitize_text_field( wp_unslash( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) );
		} else {
			$key = $flow_id . '_' . md5( time() . wp_rand() );
		}

		$expiration_time = $this->get_session_expire_time();
		$cookiepath      = $this->get_cookiepath();

		// Set the browser cookie to expire in 30 minutes.
		setcookie( CARTFLOWS_SESSION_COOKIE . $flow_id, $key, time() + $expiration_time * MINUTE_IN_SECONDS, $cookiepath, COOKIE_DOMAIN, CARTFLOWS_HTTPS ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie

		// Try to grab the transient from the database, if it exists.
		$transient = $data;

		// Store the transient, but expire in 30 minutes.
		set_transient( 'cartflows_data_' . $key, $transient, $expiration_time * MINUTE_IN_SECONDS );

		wp_cache_set( 'cartflows_data_' . $key, $transient );

		wcf()->logger->log( '==== Session Set Info ====' . PHP_EOL . 'Flow-' . $flow_id . ' Session Set : ' . $key . PHP_EOL . 'Flow Info: ' . wp_json_encode( $transient ) . PHP_EOL . ' Current Step-' . wcf_get_current_step_type() . PHP_EOL . '==== Session Set Info ====' . PHP_EOL );
	}

	/**
	 *  Get session expiry time ( in minutes )
	 *
	 * @return int
	 */
	public function get_session_expire_time() {

		return apply_filters( 'cartflows_session_data_expire_time', CARTFLOWS_SESSION_EXPIRE_TIME );
	}

	/**
	 *  Update session
	 *
	 * @param int   $flow_id flow ID.
	 * @param array $data trasient data.
	 */
	public function update_session( $flow_id, $data = array() ) {

		if ( ! isset( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) ) {

			$this->set_session( $flow_id, $data );
		}

		$key = sanitize_text_field( wp_unslash( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) );

		// Try to grab the transient from the database, if it exists.
		$transient = get_transient( 'cartflows_data_' . $key );

		$expiration_time = $this->get_session_expire_time();
		$cookiepath      = $this->get_cookiepath();

		// Set the browser cookie to expire in 30 minutes.
		setcookie( CARTFLOWS_SESSION_COOKIE . $flow_id, $key, time() + $expiration_time * MINUTE_IN_SECONDS, $cookiepath, COOKIE_DOMAIN, CARTFLOWS_HTTPS ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie

		// Store the transient, but expire in 30 minutes.
		set_transient( 'cartflows_data_' . $key, $transient, $expiration_time * MINUTE_IN_SECONDS );

		wp_cache_set( 'cartflows_data_' . $key, $transient );
	}

	/**
	 *  Destroy session
	 *
	 * @param int $flow_id flow ID.
	 */
	public function destroy_session( $flow_id ) {

		if ( isset( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) ) {

			$key        = sanitize_text_field( wp_unslash( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) );
			$cookiepath = $this->get_cookiepath();

			// Delete Transient.
			delete_transient( 'cartflows_data_' . $key );

			wp_cache_delete( 'cartflows_data_' . $key );

			unset( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] );

			// empty value and expiration one hour before.
			setcookie( CARTFLOWS_SESSION_COOKIE . $flow_id, $key, time() - 3600, $cookiepath, COOKIE_DOMAIN, CARTFLOWS_HTTPS ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie

			wcf()->logger->log( '==== Start ====' . PHP_EOL . 'Flow-' . $flow_id . ' Session Destroyed : ' . $key . PHP_EOL . ' Current Step-' . wcf_get_current_step_type() . PHP_EOL . '==== Start ====' . PHP_EOL );
		}
	}

	/**
	 *  Get session
	 */
	public function get_session() {

		if ( isset( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) ) {

			$key = sanitize_text_field( wp_unslash( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) );

			$data = get_transient( 'cartflows_data_' . $key );
		}
	}

	/**
	 *  Update transient data for cart flows.
	 *
	 * @param int   $flow_id flow ID.
	 * @param array $data data.
	 */
	public function update_data( $flow_id, $data = array() ) {

		if ( isset( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) ) {

			$key = sanitize_text_field( wp_unslash( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) );

			// Try to grab the transient from the database, if it exists.
			$transient = get_transient( 'cartflows_data_' . $key );

			if ( ! is_array( $transient ) ) {
				$transient = array();
			}

			$transient = array_merge( $transient, $data );

			$expiration_time = $this->get_session_expire_time();

			// Store the transient, but expire in 30 minutes.
			set_transient( 'cartflows_data_' . $key, $transient, $expiration_time * MINUTE_IN_SECONDS );

			wp_cache_set( 'cartflows_data_' . $key, $transient );
		}
	}

	/**
	 *  Update transient data for cart flows.
	 *
	 * @param int $flow_id flow ID.
	 * @return bool
	 */
	public function get_data( $flow_id ) {

		if ( isset( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) ) {

			$key = sanitize_text_field( wp_unslash( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) );

			// Try to grab the transient from the database, if it exists.
			$transient = get_transient( 'cartflows_data_' . $key );

			if ( is_array( $transient ) ) {
				return $transient;
			}
		}

		return false;
	}


	/**
	 *  Check if session is active.
	 *
	 * @param int $flow_id flow ID.
	 * @return bool
	 */
	public function is_active_session( $flow_id ) {

		$is_active = false;

		wcf()->logger->log( 'Active session : Entering' );

		// Reason for PHPCS ignore: Used to check the session key is available in the Cookie & the URL to identify the state of the page/order.
		//phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['wcf-sk'] ) && isset( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) ) {

			global $post;

			$step_id = $post && $post->ID ? $post->ID : 0;

			wcf()->logger->log( 'Session key and cookie found' );

			$sk  = sanitize_text_field( wp_unslash( $_GET['wcf-sk'] ) );
			$key = sanitize_text_field( wp_unslash( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) );

			if ( $sk === $key ) {

				wcf()->logger->log( 'Session key and cookie matched' );

				if ( isset( $_GET['wcf-order'] ) && isset( $_GET['wcf-key'] ) ) {

					wcf()->logger->log( 'Order and order key found' );

					// Get the order.
					$order_id  = empty( $_GET['wcf-order'] ) ? 0 : absint( $_GET['wcf-order'] );
					$order_key = empty( $_GET['wcf-key'] ) ? '' : sanitize_text_field( wp_unslash( $_GET['wcf-key'] ) );

					if ( $order_id > 0 ) {

						$order = wc_get_order( $order_id );

						wcf()->logger->log( 'Order object found' );

						if ( $order && $order->get_order_key() === $order_key ) {
							$is_active = true;
							wcf()->logger->log( 'Order verified. Current Page : ' . wcf_get_current_step_type() . ' ID : ' . $step_id );
						}
					}
				}
			}
		}

		//phpcs:enable WordPress.Security.NonceVerification.Recommended

		wcf()->logger->log( 'Active session : Exit' );

		return apply_filters( 'cartflows_is_active_session', $is_active );
	}

	/**
	 * Get session key for flow
	 *
	 * @param int $flow_id flow ID.
	 * @return bool
	 */
	public function get_session_key( $flow_id ) {

		if ( isset( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) ) {

			$key = sanitize_text_field( wp_unslash( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) );

			return $key;
		}

		return false;
	}
}
//phpcs:enable WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE
/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Session::get_instance();
