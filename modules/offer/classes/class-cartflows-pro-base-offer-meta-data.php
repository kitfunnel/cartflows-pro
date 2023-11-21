<?php
/**
 * Optin post meta fields
 *
 * @package CartFlows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Meta Boxes setup
 */
class Cartflows_Pro_Base_Offer_Meta_Data {


	/**
	 * Instance
	 *
	 * @var Cartflows_Pro_Base_Offer_Meta_Data $instance Class object
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
	 * Constructor
	 */
	public function __construct() {

		add_filter( 'cartflows_admin_upsell_step_default_meta_fields', array( $this, 'get_offer_step_default_fields' ), 10, 2 );
		add_filter( 'cartflows_admin_downsell_step_default_meta_fields', array( $this, 'get_offer_step_default_fields' ), 10, 2 );

		add_filter( 'cartflows_admin_upsell_step_meta_settings', array( $this, 'get_settings' ), 10, 2 );
		add_filter( 'cartflows_admin_downsell_step_meta_settings', array( $this, 'get_settings' ), 10, 2 );

		add_filter( 'cartflows_admin_upsell_step_meta_fields', array( $this, 'filter_values' ), 10, 2 );
		add_filter( 'cartflows_admin_downsell_step_meta_fields', array( $this, 'filter_values' ), 10, 2 );

	}



	/**
	 * Filter checkout values
	 *
	 * @param  array $options options.
	 */
	public function filter_values( $options ) {

		if ( ! empty( $options['wcf-offer-product'][0] ) ) {

			$product_id  = intval( $options['wcf-offer-product'][0] );
			$product_obj = wc_get_product( $product_id );

			if ( $product_obj ) {
				$options['wcf-offer-product'] = array(
					'value'          => $product_id,
					'label'          => $product_obj->get_name(),
					'img_url'        => get_the_post_thumbnail_url( $product_id ),
					'original_price' => Cartflows_Pro_Admin_Helper::get_product_original_price( $product_obj ),
				);
			}
		}

		return $options;
	}

	/**
	 * Gget_offer_step_default_fields
	 *
	 * @param  array $step_default_fields step default fields.
	 * @param  int   $step_id Post meta.
	 */
	public function get_offer_step_default_fields( $step_default_fields, $step_id ) {

		$step_default_fields = Cartflows_Pro_Default_Meta::get_instance()->get_offer_fields( $step_id );

		return $step_default_fields;

	}

	/**
	 * Page Header Tabs
	 *
	 * @param  array $settings settings.
	 * @param  int   $step_id Post meta.
	 */
	public function get_settings( $settings, $step_id ) {

		$add_tabs = array(
			'products' => array(
				'title'    => __( 'Products', 'cartflows-pro' ),
				'id'       => 'products',
				'class'    => '',
				'icon'     => 'dashicons-format-aside',
				'priority' => 20,
			),
			'settings' => array(
				'title'    => __( 'Settings', 'cartflows-pro' ),
				'id'       => 'settings',
				'class'    => '',
				'icon'     => 'dashicons-format-aside',
				'priority' => 20,
			),
		);

		$settings_data = $this->get_settings_fields( $step_id );

		$settings = array(
			'tabs'          => $add_tabs,
			'page_settings' => $this->get_page_settings( $step_id ),
			'settings'      => $settings_data,
		);

		return $settings;
	}

	/**
	 * Get_page_settings
	 *
	 * @param string $step_id step id.
	 */
	public function get_page_settings( $step_id ) {

		$options = $this->get_data( $step_id );

		$settings = array(
			'settings' => array(
				'product' => array(
					'title'    => __( 'Product', 'cartflows-pro' ),
					'priority' => 20,
					'fields'   => array(
						'product-settings-separator'       => array(
							'type' => 'separator',
						),

						'offer-replace-settings'           => ! wcf_pro()->utils->is_separate_offer_order() ?
						array(
							'type'    => 'doc',
							'content' => sprintf(
								/* translators: %1$1s, %2$2s Link to meta */
								__( 'Do you want to cancel the main order on the purchase of upsell/downsell offer?<br>Please set the "Create a new child order" option in the %1$1sOffer Global Settings%2$2s to use the cancel primary order option.', 'cartflows-pro' ),
								'<a href="' . Cartflows_Pro_Helper::get_setting_page_url() . '" target="_blank">',
								'</a>'
							),
						)
							:
						array(
							'type'    => 'toggle',
							'label'   => __( 'Replace Main Order', 'cartflows-pro' ),
							'name'    => 'wcf-replace-main-order',
							'desc'    => sprintf(
								/* translators: %1$1s, %2$2s Link to meta */
								__( 'Note: If "Cancel Main Order?" option is enabled then on the purchase of upsell/downsell offer it will charge the difference of main order total and this product. %1$1sLearn More >>%2$2s', 'cartflows-pro' ),
								'<a href="https://cartflows.com/docs/replace-main-checkout-order-with-upsell-downsell" target="_blank">',
								'</a>'
							),
							'tooltip' => __( 'If this option is enabled, it will cancel the main order on the purchase of upsell/downsell offer.', 'cartflows-pro' ),
						),

						'replace-order-settings-separator' => array(
							'type' => 'separator',
						),

						'skip-offer-settings'              => array(
							'type'  => 'toggle',
							'name'  => 'wcf-skip-offer',
							'label' => __( 'Skip Offer', 'cartflows-pro' ),
							'desc'  => __( 'Exclude the offer if the buyer has previously purchased the selected product.', 'cartflows-pro' ),
						),

					),

				),
			),
		);

		return $settings;
	}

	/**
	 * Get settings data.
	 *
	 * @param  int $step_id Post ID.
	 */
	public function get_settings_fields( $step_id ) {

		$step_type = get_post_meta( $step_id, 'wcf-step-type', true );
		$flow_id   = get_post_meta( $step_id, 'wcf-flow-id', true );

		if ( 'upsell' === $step_type ) {

			$offer_yes_link = wcf()->utils->get_linking_url(
				array( 'class' => 'wcf-up-offer-yes' )
			);

			$offer_no_link = wcf()->utils->get_linking_url(
				array( 'class' => 'wcf-up-offer-no' )
			);
		}

		if ( 'downsell' === $step_type ) {

			$offer_yes_link = wcf()->utils->get_linking_url(
				array( 'class' => 'wcf-down-offer-yes' )
			);

			$offer_no_link = wcf()->utils->get_linking_url(
				array( 'class' => 'wcf-down-offer-no' )
			);
		}

		$options = $this->get_data( $step_id );

		$opt_steps = Cartflows_Pro_Admin_Helper::get_opt_steps( $step_id );

		$settings = array(
			'settings' => array(
				'shortcode'               => array(
					'title'    => __( 'Shortcodes', 'cartflows-pro' ),
					'slug'     => 'shortcodes',
					'priority' => 40,
					'fields'   => array(
						'offer-accept-link'         => array(
							'type'          => 'text',
							'label'         => __( 'Accept Offer Link', 'cartflows-pro' ),
							'value'         => $offer_yes_link,
							'readonly'      => true,
							'display_align' => 'vertical',
						),
						'offer-reject-link'         => array(
							'type'          => 'text',
							'label'         => __( 'Decline Offer Link', 'cartflows-pro' ),
							'value'         => $offer_no_link,
							'readonly'      => true,
							'display_align' => 'vertical',
						),
						'product-variation'         => array(
							'type'          => 'text',
							'label'         => __( 'Product Variation ', 'cartflows-pro' ),
							'value'         => '[cartflows_offer_product_variation]',
							'help'          => esc_html__( 'Add this shortcode to your offer page for variation selection. If product is variable, it will show variations.', 'cartflows-pro' ),
							'readonly'      => true,
							'display_align' => 'vertical',
						),
						'product-quantity'          => array(
							'type'          => 'text',
							'label'         => __( 'Product Quantity', 'cartflows-pro' ),
							'value'         => '[cartflows_offer_product_quantity]',
							'help'          => esc_html__( 'Add this shortcode to your offer page for quantity selection.', 'cartflows-pro' ),
							'readonly'      => true,
							'display_align' => 'vertical',
						),
						'product-title'             => array(
							'type'          => 'text',
							'label'         => __( 'Product Title', 'cartflows-pro' ),
							'value'         => '[cartflows_offer_product_title]',
							'readonly'      => true,
							'display_align' => 'vertical',
						),
						'product-description'       => array(
							'type'          => 'text',
							'label'         => __( 'Product Description', 'cartflows-pro' ),
							'value'         => '[cartflows_offer_product_desc]',
							'readonly'      => true,
							'display_align' => 'vertical',
						),
						'product-short-description' => array(
							'type'          => 'text',
							'label'         => __( 'Product Short Description', 'cartflows-pro' ),
							'value'         => '[cartflows_offer_product_short_desc]',
							'readonly'      => true,
							'display_align' => 'vertical',
						),
						'product-price'             => array(
							'type'          => 'text',
							'label'         => __( 'Product Price', 'cartflows-pro' ),
							'value'         => '[cartflows_offer_product_price]',
							'help'          => __( 'This shortcode will show the products single quantity price.', 'cartflows-pro' ),
							'readonly'      => true,
							'display_align' => 'vertical',
						),
						'product-image'             => array(
							'type'          => 'text',
							'label'         => __( 'Product Image', 'cartflows-pro' ),
							'value'         => '[cartflows_offer_product_image]',
							'readonly'      => true,
							'display_align' => 'vertical',
						),

					),
				),
				'general'                 => array(
					'title'    => __( 'General', 'cartflows-pro' ),
					'slug'     => 'general',
					'priority' => 10,
					'fields'   => array(
						'slug'               => array(
							'type'          => 'text',
							'name'          => 'post_name',
							'label'         => __( 'Step Slug', 'cartflows-pro' ),
							'value'         => get_post_field( 'post_name', $step_id ),
							'display_align' => 'vertical',
						),
						'step-note'          => array(
							'type'          => 'textarea',
							'name'          => 'wcf-step-note',
							'label'         => __( 'Step Note', 'cartflows-pro' ),
							'value'         => get_post_meta( $step_id, 'wcf-step-note', true ),
							'rows'          => 2,
							'cols'          => 38,
							'display_align' => 'vertical',
						),

						'offer-process-text' => array(
							'type'          => 'text',
							'label'         => __( 'Offer Processing', 'cartflows-pro' ),
							'name'          => 'wcf-offer-order-process-text',
							'placeholder'   => __( 'Processing Order...', 'cartflows-pro' ),
							'display_align' => 'vertical',
						),
						'offer-success-text' => array(
							'type'          => 'text',
							'label'         => __( 'Offer Success', 'cartflows-pro' ),
							'name'          => 'wcf-offer-order-success-text',
							'placeholder'   => __( 'Product Added Successfully.', 'cartflows-pro' ),
							'display_align' => 'vertical',
						),
						'offer-failure-text' => array(
							'type'          => 'text',
							'label'         => __( 'Offer Failure', 'cartflows-pro' ),
							'name'          => 'wcf-offer-order-failure-text',
							'placeholder'   => __( 'Oooops! Your Payment Failed.', 'cartflows-pro' ),
							'display_align' => 'vertical',
						),
						'offer-success-note' => array(
							'type'          => 'text',
							'label'         => __( 'Offer Success Note', 'cartflows-pro' ),
							'name'          => 'wcf-offer-order-success-note',
							'placeholder'   => __( 'Please wait while we process your payment...', 'cartflows-pro' ),
							'display_align' => 'vertical',
						),
					),
				),

				'conditional-redirection' => array(
					'title'    => __( 'Conditional Redirection', 'cartflows-pro' ),
					'slug'     => 'conditional_redirection',
					'priority' => 20,
					'fields'   => array(
						'offer-yes-next-step' => array(
							'type'            => 'select',
							'label'           => __( 'Offer - Yes Next Step', 'cartflows-pro' ),
							'optgroup'        => array(
								'upsell'   => esc_html__( 'Upsell &lpar;Woo&rpar;', 'cartflows-pro' ),
								'downsell' => esc_html__( 'Downsell &lpar;Woo&rpar;', 'cartflows-pro' ),
								'thankyou' => esc_html__( 'Thankyou &lpar;Woo&rpar;', 'cartflows-pro' ),
							),
							'name'            => 'wcf-yes-next-step',
							'value'           => $options['wcf-yes-next-step'],
							'data-flow-id'    => $flow_id,
							'data-exclude-id' => $step_id,
							'options'         => $opt_steps,
							'display_align'   => 'vertical',
						),

						'offer-next-step-doc' => array(
							'type'            => 'select',
							'label'           => __( 'Offer - No Next Step', 'cartflows-pro' ),
							'optgroup'        => array(
								'upsell'   => esc_html__( 'Upsell &lpar;Woo&rpar;', 'cartflows-pro' ),
								'downsell' => esc_html__( 'Downsell &lpar;Woo&rpar;', 'cartflows-pro' ),
								'thankyou' => esc_html__( 'Thankyou &lpar;Woo&rpar;', 'cartflows-pro' ),
							),
							'name'            => 'wcf-no-next-step',
							'value'           => $options['wcf-no-next-step'],
							'data-flow-id'    => $flow_id,
							'data-exclude-id' => $step_id,
							'options'         => $opt_steps,
							'display_align'   => 'vertical',
						),
						'offer-no-next-step'  => array(
							'type'    => 'doc',
							/* translators: %1$1s: link html start, %2$12: link html end*/
							'content' => sprintf( __( 'For more information about the conditional redirection please %1$1sClick here.%2$2s', 'cartflows-pro' ), '<a href="https://cartflows.com/docs/create-conditional-upsell-downsell/" target="_blank">', '</a>' ),
						),
					),
				),

				'custom-scripts'          => array(
					'title'    => __( 'Custom Script', 'cartflows-pro' ),
					'slug'     => 'custom_scripts',
					'priority' => 30,
					'fields'   => array(
						'wcf-checkout-custom-script' => array(
							'type'          => 'textarea',
							'label'         => __( 'Custom Script', 'cartflows-pro' ),
							'name'          => 'wcf-custom-script',
							'value'         => $options['wcf-custom-script'],
							'display_align' => 'vertical',
						),
						'offer-success-script'       => array(
							'type'          => 'textarea',
							'label'         => __( 'Offer Success Script', 'cartflows-pro' ),
							'name'          => 'wcf-offer-accept-script',
							'value'         => $options['wcf-offer-accept-script'],
							'tooltip'       => __( 'Add your custom script which you need to run when the offer is accepted.', 'cartflows-pro' ),
							'display_align' => 'vertical',
						),
						'offer-rejected-script'      => array(
							'type'          => 'textarea',
							'label'         => __( 'Offer Rejected Script', 'cartflows-pro' ),
							'name'          => 'wcf-offer-reject-script',
							'value'         => $options['wcf-offer-reject-script'],
							'tooltip'       => __( 'Add your custom script which you need to run when the offer is rejected.', 'cartflows-pro' ),
							/* translators: %1$1s: link html start, %2$12: link html end*/
							'desc'          => sprintf( __( 'Use {{order_id}}, {{product_id}} & {{quantity}} and more shortcodes to fetch offer details. %1$1sClick here.%2$2s to know more.', 'cartflows-pro' ), '<a href="https://cartflows.com/docs/offers-js-triggers-shortcodes/" target="_blank">', '</a>' ),
							'display_align' => 'vertical',
						),
					),
				),
			),
		);

		return $settings;
	}

	/**
	 * Get data.
	 *
	 * @param  int $step_id Post ID.
	 */
	public function get_data( $step_id ) {

		$optin_data = array();

		// Stored data.
		$stored_meta = get_post_meta( $step_id );

		// Default.
		$default_data = self::get_meta_option( $step_id );

		// Set stored and override defaults.
		foreach ( $default_data as $key => $value ) {
			if ( array_key_exists( $key, $stored_meta ) ) {
				$optin_data[ $key ] = ( isset( $stored_meta[ $key ][0] ) ) ? maybe_unserialize( $stored_meta[ $key ][0] ) : '';
			} else {
				$optin_data[ $key ] = ( isset( $default_data[ $key ]['default'] ) ) ? $default_data[ $key ]['default'] : '';
			}
		}

		return $optin_data;

	}

	/**
	 * Get meta.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function get_meta_option( $post_id ) {

		$meta_option = wcf_pro()->options->get_offer_fields( $post_id );

		return $meta_option;

	}



}
Cartflows_Pro_Base_Offer_Meta_Data::get_instance();


