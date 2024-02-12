<?php
/**
 * Cartflows Admin.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Admin.
 */
class Cartflows_Pro_Admin {

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
		add_filter( 'cartflows_licence_args', array( $this, 'licence_args' ) );
		add_filter( 'cartflows_common_settings_default', array( $this, 'set_default_settings' ) );

		// Change String of Offer Item Meta.
		add_filter( 'woocommerce_order_item_display_meta_key', array( $this, 'change_order_item_meta_title' ), 20, 3 );

		// Hide Order Bump Metadata from the order list.
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'custom_woocommerce_hidden_order_itemmeta' ), 10, 1 );

		/* Add pro version class to body */
		add_action( 'admin_body_class', array( $this, 'add_admin_pro_body_class' ) );

		add_filter( 'cartflows_google_analytics_settings_default', array( $this, 'add_google_analytics_pro_events' ) );

		if ( _is_cartflows_pluspro() ) {

			$this->include_refund_order_section();
		}

		add_filter( 'get_user_option_meta-box-order_' . CARTFLOWS_STEP_POST_TYPE, array( $this, 'metabox_order' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'load_notice_script' ) );

		add_action( 'wp_ajax_cartflows_migrate_order_bump', array( $this, 'migrate_order_bump' ) );

		add_action( 'wp_ajax_cartflows_migrate_pre_checkout_offer_styles', array( $this, 'migrate_pre_checkout_styles' ) );

		add_action( 'admin_notices', array( $this, 'migration_conditional_notices' ) );

		add_filter( 'install_plugin_overwrite_comparison', array( $this, 'update_override_comparison_html' ), 10, 3 );

		add_filter( 'cartflows_admin_exclude_import_meta_keys', array( $this, 'add_meta_keys_to_exclude_from_import' ) );

		// Let WooCommerce know, CartFlows Pro is compatible with HPOS.
		add_action( 'before_woocommerce_init', array( $this, 'declare_woo_hpos_compatibility' ) );

		add_action( 'wp_ajax_cartflows_dismiss_flow_analytics_notice', array( $this, 'dismiss_flow_analytics_notice' ) );

		add_filter( 'cartflows_step_importer_args', array( $this, 'update_meta_before_import_json' ), 10, 1 );

		add_filter( 'cartflows_template_import_meta_data', array( $this, 'update_meta_before_import_template' ), 10, 1 );
	}

	/**
	 * Remove ob product id from meta data.
	 *
	 * @param array $pre_post_data step data.
	 *
	 * @return array
	 */
	public function update_meta_before_import_json( $pre_post_data ) {

		if ( isset( $pre_post_data['meta_input'] ) ) {

			$step_meta_data = $pre_post_data['meta_input'];

			if ( isset( $step_meta_data['wcf-order-bumps'] ) && is_array( $step_meta_data['wcf-order-bumps'] ) ) {

				$order_bumps = $step_meta_data['wcf-order-bumps'];

				foreach ( $order_bumps as $key => $ob_data ) {

					if ( isset( $ob_data['product'] ) ) {
						unset( $order_bumps[ $key ]['product'] );
					}
				}

				$pre_post_data['meta_input']['wcf-order-bumps'] = $order_bumps;
			}
		}

		return $pre_post_data;
	}

	/**
	 * Remove ob product id from meta data.
	 *
	 * @param array $step_meta_data step meta data.
	 *
	 * @return array.
	 */
	public function update_meta_before_import_template( $step_meta_data ) {

		if ( ! empty( $step_meta_data ) && isset( $step_meta_data['wcf-order-bumps'] ) ) {

			$order_bumps  = $step_meta_data['wcf-order-bumps'][0];
			$is_serialize = is_serialized( $order_bumps, true );

			if ( ! empty( $order_bumps ) ) {

				if ( $is_serialize ) {
					$order_bumps = maybe_unserialize( stripslashes( $order_bumps ) );
				} elseif ( ! is_array( $order_bumps ) ) {
					$order_bumps = json_decode( stripslashes( $order_bumps ), true );
				}

				if ( is_array( $order_bumps ) ) {
					foreach ( $order_bumps as $key => $ob_data ) {
						if ( isset( $ob_data['product'] ) ) {
							unset( $order_bumps[ $key ]['product'] );
						}
					}
				}

				$step_meta_data['wcf-order-bumps'][0] = $is_serialize && is_array( $order_bumps ) ? maybe_serialize( $order_bumps ) : $order_bumps;
			}
		}

		return $step_meta_data;
	}

	/**
	 *  Declare the woo HPOS compatibility.
	 */
	public function declare_woo_hpos_compatibility() {

		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', CARTFLOWS_PRO_FILE, true );
		}
	}

	/**
	 * Exclude meta keys for pro.
	 *
	 * @param array $meta_keys meta keys.
	 */
	public function add_meta_keys_to_exclude_from_import( $meta_keys ) {

		$pro_meta_keys = array(
			'wcf-ab-test',
			'wcf-yes-next-step',
			'wcf-no-next-step',
			'wcf-offer-product',
			'wcf-order-bump-product',
			'wcf-pre-checkout-offer-product',
			'wcf-product-options-data',
			'wcf-yes-next-step',
			'wcf-no-next-step',
			'wcf-checkout-rules',
		);

		$meta_keys = array_merge( $meta_keys, $pro_meta_keys );
		return $meta_keys;
	}

	/**
	 * Show notice before downgrading to lower plan.
	 *
	 * @param string $table_html table html.
	 * @param array  $current_plugin current plugin data.
	 * @param array  $new_plugin new plugin data.
	 *
	 * @since x.x.x
	 */
	public function update_override_comparison_html( $table_html, $current_plugin, $new_plugin ) {

		if ( isset( $current_plugin['Name'] ) ) {

			if ( $current_plugin['Name'] === $new_plugin['Name'] ) {
				return $table_html;
			}

			if ( 'CartFlows Pro' === $current_plugin['Name'] ) {
				$table_html .= sprintf( /* translators: %1$s: HTML, %2$s: HTML */
					__( '%1$sYou are installing the lower plan of the CartFlows compared to what currently you have, it will disable some pro features that you might be using. %2$s', 'cartflows-pro' ),
					'<strong>',
					'</strong>'
				);
			} elseif ( 'CartFlows Plus' === $current_plugin['Name'] && 'CartFlows Starter' === $new_plugin['Name'] ) {
				$table_html .= sprintf( /* translators: %1$s: HTML, %2$s: HTML */
					__( '%1$sYou are installing the lower plan of the CartFlows compared to what currently you have, it will disable some plus features that you might be using. %2$s', 'cartflows-pro' ),
					'<strong>',
					'</strong>'
				);
			}
		}

		return $table_html;
	}
	/**
	 * Show migration in process notice.
	 *
	 * @since 1.7.0
	 */
	public function order_bump_migration_processing_notice() {

		$action_schedular_page_url = admin_url( 'admin.php?page=wc-status&tab=action-scheduler&s=cartflows' );

		?>
			<div class="notice notice-info wcf-notice">
				<div class="wcf-notice-container" style="padding: 10px 0; font-size:14px;">
					<div class="wcf-notice-content">
						<span><?php esc_html_e( 'CartFlows is migrating the old order bump to the new one in the background. The migration process may take a little while, so please be patient.', 'cartflows-pro' ); ?><span>
						<span><a href="<?php echo esc_url( $action_schedular_page_url ); ?>" style="text-decoration:none;" > <?php esc_html_e( 'View Progress >>', 'cartflows-pro' ); ?></a></span>
					</div>
				</div>
			</div>
		<?php

	}

	/**
	 * Show conditional migration notices.
	 *
	 * @since 1.7.0
	 */
	public function migration_conditional_notices() {

		if ( ! wcf_pro()->is_woo_active ) {
			return;
		}

		if ( ! $this->allowed_screen_for_notices() ) {
			return;
		}

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		if ( 'no' === get_option( 'wcf_order_bump_migrated', false ) ) {
			$this->migrate_order_bump_notice();
		} elseif ( 'processing' === get_option( 'wcf_order_bump_migrated', false ) ) {
			$this->order_bump_migration_processing_notice();
		} elseif ( 'done' === get_option( 'wcf_migration_complete_status', false ) ) {
			$this->migration_complete_notice();
		}

		if ( 'no' === get_option( 'wcf_pre_checkout_offer_styles_migrated', false ) ) {
			$this->migrate_pre_checkout_styles_notice();
		} elseif ( 'processing' === get_option( 'wcf_pre_checkout_offer_styles_migrated', false ) ) {
			$this->pre_checkout_offer_style_migration_processing_notice();
		} elseif ( 'done' === get_option( 'wcf_pre_checkout_offer_styles_migration_complete_status', false ) ) {
			$this->pre_checkout_offer_migration_complete_notice();
		}
	}

	/**
	 * Show migration complete notice.
	 *
	 * @since 1.7.0
	 */
	public function migration_complete_notice() {
		?>
		<div class="notice notice-info wcf-notice">
			<div class="wcf-notice-container" style="padding: 10px 0; font-size:14px;">
				<div class="wcf-notice-content">
					<span><?php esc_html_e( 'Hurray! CartFlows Multiple Order Bump migration process is completed successfully.', 'cartflows-pro' ); ?></span>
				</div>
			</div>
		</div>
		<?php

		delete_option( 'wcf_migration_complete_status' );
	}

	/**
	 * Migrate order bump ajax call.
	 *
	 * @since 1.7.0
	 */
	public function migrate_order_bump() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows-migrate-order-bump', 'security', false ) ) {
			return;
		}

		wcf()->logger->migration_log( 'Initiating Order Bump migration process.' );

		$response_data = array(
			'success'  => false,
			'messsage' => __( 'Cannot schedule a migration. Action scheduler function not found.', 'cartflows-pro' ),
		);

		if ( function_exists( 'as_enqueue_async_action' ) ) {

			as_enqueue_async_action( 'cartflows_migrate_order_bumps' );
			update_option( 'wcf_order_bump_migrated', 'processing' );

			$response_data = array(
				'success'  => true,
				'messsage' => __( 'Order Bump 1st migration action scheduled successfully.', 'cartflows-pro' ),
			);
		}

		wcf()->logger->migration_log( $response_data['messsage'] );

		wp_send_json_success( $response_data );
	}

	/**
	 * New UI notice.
	 *
	 * @since 1.7.0
	 */
	public function load_notice_script() {

		if ( ! $this->allowed_screen_for_notices() || ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		// Loading Script file.
		wp_enqueue_script( 'cartflows-pro-notice', CARTFLOWS_PRO_URL . 'admin/assets/js/notices.js', array( 'jquery' ), CARTFLOWS_PRO_VER, false );

		$action_schedular_page_url = admin_url( 'admin.php?page=wc-status&tab=action-scheduler&s=cartflows' );

		$vars = array(
			'ajaxurl'                             => admin_url( 'admin-ajax.php' ),
			'ob_migration_nonce'                  => wp_create_nonce( 'cartflows-migrate-order-bump' ),
			'pre_checkout_offer_migration_nonce'  => wp_create_nonce( 'cartflows-migrate-pre-checkout-offer-styles' ),
			/* translators: %1$1s: link html start, %2$12: link html end*/
			'ob_notice_text'                      => sprintf( __( 'CartFlows is migrating the old order bump to the new one in the background. The migration process may take a little while, so please be patient. %1$1s View Progress >> %2$2s', 'cartflows-pro' ), '<a href="' . $action_schedular_page_url . '" style="text-decoration:none;">', '</a>' ),
			/* translators: %1$1s: link html start, %2$12: link html end*/
			'pre_checkout_offer_migration_notice' => sprintf( __( 'CartFlows is migrating the pre checkout offer styles in the background. The migration process may take a little while, so please be patient. %1$1s View Progress >> %2$2s', 'cartflows-pro' ), '<a href="' . $action_schedular_page_url . '" style="text-decoration:none;">', '</a>' ),
			'flow_analytics_dismiss_notice_nonce' => wp_create_nonce( 'cartflows-dismiss-flow-analytics-notice' ),
		);

		wp_localize_script( 'cartflows-pro-notice', 'CartFlows_Pro_Common_Vars', $vars );
	}

	/**
	 * Migrate Order Bump notice.
	 *
	 * @since 1.7.0
	 */
	public function migrate_order_bump_notice() {

		?>
		<div class="notice notice-info wcf-notice">
				<div class="wcf-notice-container" style="padding: 10px 0; font-size:14px;">
					<div class="wcf-notice-content">
						<span><?php esc_html_e( 'We have introduced the multiple order bump feature. If your checkout page has the order bump feature enabled, you need to migrate an old order bump settings to the new multiple order bump settings.', 'cartflows-pro' ); ?></span>
						<div class="wcf-notice-actions" style="margin: 10px 0 3px 0px;">
							<a class="button button-primary migrate-to-new-ob" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cartflows_pro_migrate_order_bump', 'update' ), 'migrate_order_bump_nonce', 'migrate_order_bump_nonce' ) ); ?>"><?php esc_html_e( 'Migrate Order Bump', 'cartflows-pro' ); ?></a>
						</div>
					</div>
				</div>
			</div>
		<?php
	}

	/**
	 * Migrate Order Bump notice.
	 *
	 * @since 1.7.0
	 */
	public function migrate_pre_checkout_styles_notice() {

		?>
		<div class="notice notice-info wcf-notice">
				<div class="wcf-notice-container" style="padding: 10px 0; font-size:14px;">
					<div class="wcf-notice-content">
						<span><?php esc_html_e( 'We have moved the pre-checkout offer styling option from the page builder settings and added it to the checkout offer tab. If your checkout page has the pre-checkout feature enabled, then you need to migrate those settings.', 'cartflows-pro' ); ?></span>
						<div class="wcf-notice-actions" style="margin: 10px 0 3px 0px;">
							<a class="button button-primary migrate-to-new-styling-option" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cartflows_pro_migrate_pre_checkout_offer_styles', 'update' ), 'migrate_pre_checkout_offer_styles_nonce', 'migrate_pre_checkout_offer_styles_nonce' ) ); ?>"><?php esc_html_e( 'Migrate pre-checkout offer styles', 'cartflows-pro' ); ?></a>
						</div>
					</div>
				</div>
			</div>
		<?php
	}

	/**
	 * Show migration complete notice.
	 *
	 * @since 1.7.0
	 */
	public function pre_checkout_offer_migration_complete_notice() {
		?>
		<div class="notice notice-info wcf-notice">
			<div class="wcf-notice-container" style="padding: 10px 0; font-size:14px;">
				<div class="wcf-notice-content">
					<span><?php esc_html_e( 'Hurray! CartFlows pre checkout offer styles migration process is completed successfully.', 'cartflows-pro' ); ?></span>
				</div>
			</div>
		</div>
		<?php

		delete_option( 'wcf_pre_checkout_offer_styles_migration_complete_status' );
	}

		/**
		 * Show migration in process notice.
		 *
		 * @since 1.7.0
		 */
	public function pre_checkout_offer_style_migration_processing_notice() {

		$action_schedular_page_url = admin_url( 'admin.php?page=wc-status&tab=action-scheduler&s=cartflows' );

		?>
			<div class="notice notice-info wcf-notice">
				<div class="wcf-notice-container" style="padding: 10px 0; font-size:14px;">
					<div class="wcf-notice-content">
						<span><?php esc_html_e( 'CartFlows is migrating the pre checkout offer styles in the background. The migration process may take a little while, so please be patient.', 'cartflows-pro' ); ?><span>
						<span><a href="<?php echo esc_url( $action_schedular_page_url ); ?>" style="text-decoration:none;" > <?php esc_html_e( 'View Progress >>', 'cartflows-pro' ); ?></a></span>
					</div>
				</div>
			</div>
		<?php

	}

	/**
	 * Migrate order bump ajax call.
	 *
	 * @since 1.7.0
	 */
	public function migrate_pre_checkout_styles() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows-migrate-pre-checkout-offer-styles', 'security', false ) ) {
			return;
		}

		wcf()->logger->migration_log( 'Initiating pre checkout offer migration process.' );

		$response_data = array(
			'success'  => false,
			'messsage' => __( 'Cannot schedule a migration. Action scheduler function not found.', 'cartflows-pro' ),
		);

		if ( function_exists( 'as_enqueue_async_action' ) ) {

			as_enqueue_async_action( 'cartflows_migrate_pre_checkout_offer_styles' );
			update_option( 'wcf_pre_checkout_offer_styles_migrated', 'processing' );

			$response_data = array(
				'success'  => true,
				'messsage' => __( '1st migration action scheduled successfully.', 'cartflows-pro' ),
			);
		}

		wcf()->logger->migration_log( $response_data['messsage'] );

		wp_send_json_success( $response_data );
	}


	/**
	 * Check allowed screen for notices.
	 *
	 * @since 1.7.0
	 * @return bool
	 */
	public function allowed_screen_for_notices() {

		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$allowed_screens = array(
			'toplevel_page_cartflows',
			'dashboard',
			'plugins',
		);

		if ( in_array( $screen_id, $allowed_screens, true ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Metabox fixed orders.
	 *
	 * @param  array $pro_metabox_order Metabox orders.
	 * @return array
	 */
	public function metabox_order( $pro_metabox_order ) {

		if ( isset( $pro_metabox_order['side'] ) ) {

			$pro_metabox_order['side'] = str_replace(
				array(
					'wcf-offer-settings',
				),
				'',
				$pro_metabox_order['side']
			);
		}

		return $pro_metabox_order;
	}

	/**
	 * License arguments for Rest API Request.
	 *
	 * @param  array $defaults License arguments.
	 * @return array           License arguments.
	 */
	public function licence_args( $defaults ) {

		if ( ! class_exists( 'CartFlows_Pro_Licence' ) ) {
			return $defaults;
		}

		// Get license class instance.
		$cartflows_license_instance = cartflows_pro_license_instance();

		$data = get_option( 'wc_am_client_' . $cartflows_license_instance->product_id . '_api_key', array() );

		$licence_key = isset( $data['api_key'] ) ? esc_attr( $data['api_key'] ) : '';

		$args = array(
			'request'     => 'status',
			'product_id'  => CARTFLOWS_PRO_PRODUCT_ID,
			'instance'    => $cartflows_license_instance->wc_am_instance_id,
			'object'      => $cartflows_license_instance->wc_am_domain,
			'licence_key' => $licence_key,
		);

		return apply_filters( 'cartflows_pro_licence_args', wp_parse_args( $args, $defaults ) );
	}

	/**
	 * Set default options for settings.
	 *
	 * @param array $settings settings data.
	 * @since 1.0.0
	 */
	public function set_default_settings( $settings ) {

		$settings['paypal_reference_transactions'] = 'disable';
		$settings['pre_checkout_offer']            = 'disable';

		return $settings;
	}

	/**
	 * Hide order meta-data from order list backend.
	 *
	 * @param array $arr order meta data.
	 * @return array
	 * @since 1.0.0
	 */
	public function custom_woocommerce_hidden_order_itemmeta( $arr ) {
		$arr[] = '_cartflows_step_id';
		return $arr;
	}

	/**
	 * Changing a meta title
	 *
	 * @param  string        $key  The meta key.
	 * @param  WC_Meta_Data  $meta The meta object.
	 * @param  WC_Order_Item $item The order item object.
	 * @return string        The title.
	 */
	public function change_order_item_meta_title( $key, $meta, $item ) {

		if ( '_cartflows_upsell' === $meta->key ) {
			$key = __( 'Upsell Offer', 'cartflows-pro' );
		} elseif ( '_cartflows_downsell' === $meta->key ) {
			$key = __( 'Downsell Offer', 'cartflows-pro' );
		}

		return $key;
	}

	/**
	 * Admin body classes.
	 *
	 * Body classes to be added to <body> tag in admin page
	 *
	 * @param String $classes body classes returned from the filter.
	 * @return String body classes to be added to <body> tag in admin page
	 */
	public static function add_admin_pro_body_class( $classes ) {

		$classes .= ' cartflows-pro-' . CARTFLOWS_PRO_VER;

		return $classes;
	}


	/**
	 * Google analytics settings.
	 *
	 * @param array $google_analytics_settings_data settings.
	 *
	 * @return array $google_analytics_settings_data Modified settings.
	 */
	public function add_google_analytics_pro_events( $google_analytics_settings_data ) {

		$google_analytics_settings_data['enable_bump_order_add_to_cart'] = 'disable';

		return $google_analytics_settings_data;
	}

	/**
	 * Offer refund.
	 */
	public function include_refund_order_section() {
		/* Refund */
		include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-refund.php';
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Admin' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Admin::get_instance();
