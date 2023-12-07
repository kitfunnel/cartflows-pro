<?php
/**
 * CartFlows Global Data.
 *
 * @package CartFlows
 */

namespace CartflowsProAdmin\AdminCore\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use CartflowsAdmin\AdminCore\Inc\AdminHelper;
/**
 * Class flowMeta.
 */
class GlobalSettingsPro {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * CartFlows PRO license status
	 *
	 * @access private
	 * @var string $license_status Current License status.
	 * @since x.x.x
	 */
	private static $license_status = 'Deactivated';

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
	 * @since 1.6.13
	 */
	public function __construct() {

		self::$license_status = AdminHelper::get_admin_settings_option( 'wc_am_client_' . \CartFlows_Pro_Licence::get_instance()->product_id . '_activated', false, false );

		add_filter( 'cartflows_admin_global_settings_data', array( $this, 'get_pro_global_settings' ), 10, 1 );

		add_filter( 'cartflows_admin_global_data_options', array( $this, 'get_pro_global_options' ), 10, 1 );

		add_action( 'cartflows_admin_save_global_settings', array( $this, 'save_pro_global_settings' ), 10, 2 );
	}

	/**
	 * Get_pro_global_options
	 *
	 * @param string $setting_tab tab slug.
	 * @param string $action action name.
	 */
	public static function save_pro_global_settings( $setting_tab, $action ) {

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( $action, 'security', false ) ) {
			$response_data = array( 'messsage' => __( 'Nonce verification failed.', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		if ( isset( $_POST ) ) {

			switch ( $setting_tab ) {

				case 'offer':
					if ( isset( $_POST['_cartflows_offer_global_settings'] ) ) {
						// Loop through the input and sanitize each of the values.
						$new_settings = \Cartflows_Pro_Admin_Helper::sanitize_form_inputs( wp_unslash( $_POST['_cartflows_offer_global_settings'] ) ); //phpcs:ignore
						\Cartflows_Helper::update_admin_settings_option( '_cartflows_offer_global_settings', $new_settings, false );
					}
					break;

				case 'permalink':
					if ( isset( $_POST['_cartflows_abtest_settings'] ) ) {
						// Loop through the input and sanitize each of the values.
						$new_settings = \Cartflows_Pro_Admin_Helper::sanitize_form_inputs( wp_unslash( $_POST['_cartflows_abtest_settings'] ) ); //phpcs:ignore
						\Cartflows_Helper::update_admin_settings_option( '_cartflows_abtest_settings', $new_settings, false );
					}
					break;

				default:
			}
		}
	}

	/**
	 * Get_pro_global_options
	 *
	 * @param string $options options.
	 * @return array $options Modified localized array options.
	 */
	public static function get_pro_global_options( $options ) {

		$settings_default = apply_filters(
			'cartflows_offer_global_settings',
			array(
				'separate_offer_orders' => 'separate',
			)
		);

		$offer_settings = AdminHelper::get_admin_settings_option( '_cartflows_offer_global_settings', false, false );

		$offer_settings = wp_parse_args( $offer_settings, $settings_default );

		foreach ( $offer_settings as $key => $data ) {
			$options[ '_cartflows_offer_global_settings[' . $key . ']' ] = $data;
		}

		$settings_default = apply_filters(
			'cartflows_abtest_settings',
			array(
				'override_permalink' => 'disable',
			)
		);

		$abtest_settings = AdminHelper::get_admin_settings_option( '_cartflows_abtest_settings', false, false );

		$abtest_settings = wp_parse_args( $abtest_settings, $settings_default );

		foreach ( $abtest_settings as $key => $data ) {
			$options[ '_cartflows_abtest_settings[' . $key . ']' ] = $data;
		}

		// Get license info.
		$options['license_status'] = self::$license_status;

		if ( 'Activated' === self::$license_status ) {
			$license_data           = AdminHelper::get_admin_settings_option( 'wc_am_client_' . \CartFlows_Pro_Licence::get_instance()->product_id . '_api_key', false, false );
			$options['license_key'] = preg_replace( '/[A-Za-z0-9]/', '*', ! empty( $license_data['api_key'] ) ? $license_data['api_key'] : '' );
		} else {
			$options['license_key'] = '';
		}

		return $options;
	}


	/**
	 * Page Header Tabs.
	 *
	 * @param string $settings settings.
	 */
	public static function get_pro_global_settings( $settings ) {

		$settings['general']['fields']['pre-checkout-offer'] = array(
			'type'     => 'checkbox',
			'name'     => '_cartflows_common[pre_checkout_offer]',
			'label'    => __( 'Enable Pre Checkout Offers', 'cartflows-pro' ),
			'desc'     => __( 'If enable, it will add the Pre Checkout Offer settings in checkout step settings.', 'cartflows-pro' ),
			'backComp' => true,

		);

		$settings['general']['fields']['pre-checkout-offer-seperator'] = array(
			'type' => 'separator',
		);

		$settings['general']['fields']['ref-paypal-trns'] = array(
			'type'     => 'checkbox',
			'name'     => '_cartflows_common[paypal_reference_transactions]',
			'label'    => __( 'Enable PayPal Reference Transactions.', 'cartflows-pro' ),
			/* translators: %1$1s: link html start, %2$12: link html end*/
			'desc'     => sprintf( __( 'This option will work with %1$1s PayPal Standard%2$2s & %3$3s PayPal Checkout%4$4s Gateways only. To know more about PayPal reference transactions %5$5s click here. %6$6s', 'cartflows-pro' ), '<a href="https://docs.woocommerce.com/document/paypal-standard/" target="_blank">', '</a>', '<a href="https://wordpress.org/plugins/woocommerce-gateway-paypal-express-checkout/" target="_blank">', '</a>', '<a href="https://cartflows.com/docs/how-to-enable-paypal-reference-transactions/" target="_blank">', '</a>' ),
			'backComp' => true,
		);

		$settings['offer'] = array(
			'title'  => '',
			'fields' => array(
				'order-optoin' => array(
					'type'    => 'radio',
					'name'    => '_cartflows_offer_global_settings[separate_offer_orders]',
					'options' => array(
						array(
							'value' => 'separate',
							'label' => __( 'Create a new child order (Recommended)', 'cartflows-pro' ),
							'desc'  => __( 'This option create a new order for all accepted upsell/downsell offers. Main order will be parent order for them.', 'cartflows-pro' ),
						),
						array(
							'value' => 'merge',
							'label' => __( 'Add to main order', 'cartflows-pro' ),
							'desc'  => __( 'This option will merge all accepted upsell/downsell offers into main order.', 'cartflows-pro' ),
						),
					),
				),
				'order-doc'    => array(
					'type'    => 'doc',
					/* translators: %1$1s: link html start, %2$12: link html end*/
					'content' => sprintf( __( 'For more information about the offer settings please %1$1s Click here. %2$2s', 'cartflows-pro' ), '<a href="https://cartflows.com/docs/introducing-separate-orders-feature/" target="_blank">', '</a>' ),
				),
			),
		);

		// Get license Key.
		$field_class = 'Activated' === self::$license_status ? 'input-field cartflows-license-key disabled' : 'input-field cartflows-license-key';

		$settings['license'] = array(
			'title'  => '',
			'fields' => array(
				'license-field' => array(
					'type'        => 'password',
					'label'       => __( 'Enter License Key', 'cartflows-pro' ),
					'placeholder' => __( 'Enter your license key', 'cartflows-pro' ),
					'name'        => 'license_key',
					'class'       => $field_class,
					'readonly'    => 'Activated' === self::$license_status ? true : false,
					'desc'        => sprintf(
						/* translators: %1$1s: link html start, %2$12: link html end*/
						__( 'If you don\'t have License key, you can get it from %1$shere%2$s', 'cartflows-pro' ),
						'<a href="https://my.cartflows.com/api-keys/" target="_blank">',
						'</a>'
					),
				),
			),
		);

		/* Start-Pro-Feature */
		$settings['permalink']['fields']['ab-testing-heading-seperator'] = array(
			'type' => 'separator',
		);
		$settings['permalink']['fields']['ab-testing-heading']           = array(
			'type'     => 'heading',
			'label'    => __( 'A/B test Pemalink', 'cartflows-pro' ),
			'backComp' => true,
		);
		$settings['permalink']['fields']['override-permalink']           = array(
			'type'     => 'checkbox',
			'name'     => '_cartflows_abtest_settings[override_permalink]',
			'label'    => __( 'Override Permalink for A/B test', 'cartflows-pro' ),
			'desc'     => __( 'If enable, it will use same permalink for all variants.', 'cartflows-pro' ),
			'backComp' => true,
		);
		$settings['permalink']['fields']['override-permalink-seperator'] = array(
			'type' => 'separator',
		);
		/* End-Pro-Feature */

		return $settings;
	}
}
