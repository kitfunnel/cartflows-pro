<?php
/**
 * WP CLI
 *
 * 1. Run `wp cartflows info`       Info.
 *
 * @since 1.6.15
 *
 * @package Cartflows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Cartflows_Pro_WP_CLI' ) && class_exists( 'WP_CLI_Command' ) ) :

	/**
	 * Cartflows WP CLI
	 */
	class Cartflows_Pro_WP_CLI extends WP_CLI_Command {


		/**
		 * License operations
		 *
		 *  Example: wp cartflows license activate <license_key>
		 *
		 * @since x.x.x
		 * @param  array $args       Arguments.
		 * @param  array $assoc_args Associated Arguments.
		 * @return void
		 */
		public function license( $args = array(), $assoc_args = array() ) {
			$action      = isset( $args[0] ) ? $args[0] : '';
			$license_key = isset( $args[1] ) ? $args[1] : '';

			if ( empty( $action ) ) {
				WP_CLI::error( 'Error: Empty file' );
			}

			if ( 'activate' !== $action && 'deactivate' !== $action ) {
				WP_CLI::error( 'Error: Invalid action' );
			}

			if ( empty( $license_key ) ) {
				WP_CLI::error( 'Error: Empty license key' );
			}

			/* translators: %s license_key */
			$message = sprintf( __( 'Invalid License Key : %s', 'cartflows-pro' ), $license_key );

			if ( 'activate' === $action ) {
				$result = cartflows_pro_activate( $license_key );
				if ( isset( $result['success'] ) && true === $result['success'] ) {
					/* translators: %s license_key */
					$message = sprintf( __( 'License Activated : %s', 'cartflows-pro' ), $license_key );
				}
			} else {
				$result = cartflows_pro_deactivate( $license_key );

				if ( isset( $result['success'] ) && true === $result['success'] ) {
					/* translators: %s license_key */
					$message = sprintf( __( 'License Deactivated : %s', 'cartflows-pro' ), $license_key );
				}
			}

			if ( isset( $result['success'] ) && true === $result['success'] ) {
				WP_CLI::success( 'License Action : \'' . $action . '\'' );
				WP_CLI::success( $message );
			} else {
				WP_CLI::error( $message );
				// Added for debugging purpose.
				WP_CLI::log( print_r( $result ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			}
		}
	}

	/**
	 * Add Command
	 */
	WP_CLI::add_command( 'cartflows', 'Cartflows_Pro_WP_CLI' );

endif;
