<?php
/**
 * Update Compatibility
 *
 * @package CartFlows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_Update' ) ) :

	/**
	 * CartFlows Update initial setup
	 *
	 * @since 1.0.0
	 */
	class Cartflows_Pro_Update {

		/**
		 * Class instance.
		 *
		 * @access private
		 * @var Cartflows_Pro_Update $instance Class instance.
		 */
		private static $instance;

		/**
		 * Initiator
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
			add_action( 'admin_init', __CLASS__ . '::init' );
		}

		/**
		 * Init
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function init() {

			do_action( 'cartflows_pro_update_before' );

			// Get auto saved version number.
			$saved_version = get_option( 'cartflows-pro-version', false );

			// Update auto saved version number.
			if ( ! $saved_version ) {
				update_option( 'cartflows-pro-version', CARTFLOWS_PRO_VER );
				return;
			}

			// If equals then return.
			if ( version_compare( $saved_version, CARTFLOWS_PRO_VER, '=' ) ) {
				return;
			}

			// Update to older version than 1.5.5 version.
			if ( version_compare( $saved_version, '1.5.5', '<' ) ) {
				self::v_1_5_5();
			}

			if ( version_compare( $saved_version, '1.7.0', '<' ) ) {
				update_option( 'wcf_order_bump_migrated', 'no' );
			}

			// For older order bump beta only.
			if ( '1.7.0-beta-1' === $saved_version ) {
				if ( function_exists( 'as_enqueue_async_action' ) ) {
					as_enqueue_async_action( 'cartflows_beta_migrate_order_bump_format' );
					update_option( 'wcf-beta-order-bump-page', 1 );
				}
			}

			if ( version_compare( $saved_version, '1.9.0', '<' ) ) {
				$common_settings = get_option( '_cartflows_common', array() );

				if ( ! isset( $common_settings['pre_checkout_offer'] ) || ( isset( $common_settings['pre_checkout_offer'] ) && empty( $common_settings['pre_checkout_offer'] ) ) ) {
					// Set Pre-checkout offer enabled for older users.
					$common_settings['pre_checkout_offer'] = 'enable';
					update_option( '_cartflows_common', $common_settings );
				}

				update_option( 'wcf_pre_checkout_offer_styles_migrated', 'no' );
			}

			// Update required license key to lowercase. Can be removed after 3 major update.
			if ( version_compare( $saved_version, '1.11.1', '<' ) ) {

				$api_key      = get_option( 'wc_am_client_CartFlows_api_key' );
				$api_key_data = get_option( 'wc_am_client_CartFlows' );

				// Take backup of options.
				update_option(
					'cartflows_license_backup_data',
					array(
						'wc_am_client_cartflows_api_key' => $api_key,
						'wc_am_client_cartflows'         => $api_key_data,
					)
				);

				delete_option( 'wc_am_client_CartFlows_api_key' );
				delete_option( 'wc_am_client_CartFlows' );

				// Delete the cached value for old user ( before v1.11.0 and after v1.11.0 ) so WP can add new value.
				wp_cache_flush();

				if ( $api_key_data ) {

					$new_data = array(
						'wc_am_client_cartflows_api_key' => isset( $api_key_data['wc_am_client_CartFlows_api_key'] ) ? $api_key_data['wc_am_client_CartFlows_api_key'] : $api_key_data['wc_am_client_cartflows_api_key'],
					);

					update_option( 'wc_am_client_cartflows', $new_data );
				}

				if ( $api_key ) {
					update_option( 'wc_am_client_cartflows_api_key', $api_key );
				}
			}

			if ( version_compare( $saved_version, '1.11.7', '<' ) ) {
				update_option( 'wcf_show_flow_analytics_notice', 'yes' );
			}
			// Update auto saved version number.
			update_option( 'cartflows-pro-version', CARTFLOWS_PRO_VER );

			do_action( 'cartflows_pro_update_after' );
		}

		/**
		 * Offer orders option.
		 *
		 * @since 1.5.5
		 * @return void
		 */
		public static function v_1_5_5() {

			/* Backward compatible option */
			$old_data = array(
				'separate_offer_orders' => 'merge',
			);

			Cartflows_Helper::update_admin_settings_option( '_cartflows_offer_global_settings', $old_data, false );
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Pro_Update::get_instance();

endif;
