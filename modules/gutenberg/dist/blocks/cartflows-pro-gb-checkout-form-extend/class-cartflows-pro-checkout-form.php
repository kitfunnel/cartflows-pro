<?php
/**
 * Cartflows_Pro_Checkout_Form
 *
 * @package Cartflows Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_Checkout_Form' ) ) {

	/**
	 * Class Cartflows_Pro_Checkout_Form.
	 */
	class Cartflows_Pro_Checkout_Form {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Member Variable
		 *
		 * @since 1.6.13
		 * @var instance
		 */
		public static $block_list;

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

			add_filter( 'cartflows_gutenberg_cf_attributes_filters', array( $this, 'checkout_form_pro_attributes' ) );

			add_action( 'cartflows_gutenberg_checkout_options_filters', array( $this, 'dynamic_filters' ), 10, 2 );

		}

		/**
		 * Settings
		 *
		 * @since 1.6.13
		 * @var object $settings
		 */
		public static $settings;

		/**
		 * Dynamic filters.
		 *
		 * @param array $settings Settings array.
		 *
		 * @since 1.6.13
		 */
		public function dynamic_filters( $settings ) {

			self::$settings = $settings;

			$checkout_id = get_the_id();

			$checkout_fields = array(

				// Two step texts.
				array(
					'filter_slug'  => 'wcf-checkout-step-one-title',
					'setting_name' => 'stepOneTitleText',
				),
				array(
					'filter_slug'  => 'wcf-checkout-step-one-sub-title',
					'setting_name' => 'stepOneSubTitleText',
				),
				array(
					'filter_slug'  => 'wcf-checkout-step-two-title',
					'setting_name' => 'stepTwoTitleText',
				),
				array(
					'filter_slug'  => 'wcf-checkout-step-two-sub-title',
					'setting_name' => 'stepTwoSubTitleText',
				),
				array(
					'filter_slug'  => 'wcf-checkout-step-two-next-button-title-text',
					'setting_name' => 'offerButtonTitleText',
				),
				array(
					'filter_slug'  => 'wcf-checkout-step-two-next-button-subtitle-text',
					'setting_name' => 'offerButtonSubTitleText',
				),

			);

			if ( isset( $checkout_fields ) && is_array( $checkout_fields ) ) {

				foreach ( $checkout_fields as $key => $field ) {

					$setting_name = $field['setting_name'];

					if ( '' !== self::$settings[ $setting_name ] ) {

						add_filter(
							'cartflows_checkout_meta_' . $field['filter_slug'],
							function ( $value ) use ( $setting_name ) {

								$value = self::$settings[ $setting_name ];

								return $value;
							},
							10,
							1
						);
					}
				}
			}

			add_filter(
				'cartflows_checkout_meta_wcf-checkout-box-note',
				function ( $is_note_enabled ) {

					$is_note_enabled = ( 'yes' === self::$settings['enableNote'] ) ? 'yes' : 'no';
					return $is_note_enabled;
				},
				10,
				1
			);

			if ( 'yes' === self::$settings['enableNote'] && '' !== self::$settings['noteText'] ) {

				add_filter(
					'cartflows_checkout_meta_wcf-checkout-box-note-text',
					function ( $checkout_note_text ) {

						$checkout_note_text = self::$settings['noteText'];
						return $checkout_note_text;
					},
					10,
					1
				);
			}

			// Checkout offer.

			$checkout_offer_fields = array(
				array(
					'filter_slug'  => 'wcf-checkout-offer-button-title',
					'setting_name' => 'offerButtonTitleText',
				),
				array(
					'filter_slug'  => 'wcf-checkout-offer-button-sub-title',
					'setting_name' => 'offerButtonSubTitleText',
				),
			);

			if ( isset( $checkout_offer_fields ) && is_array( $checkout_offer_fields ) ) {

				foreach ( $checkout_offer_fields as $key => $field ) {

					$setting_name = $field['setting_name'];

					if ( '' !== self::$settings[ $setting_name ] ) {
						add_filter(
							'cartflows_checkout_meta_' . $field['filter_slug'],
							function ( $value ) use ( $setting_name ) {

								$value = self::$settings[ $setting_name ];

								return $value;
							},
							10,
							1
						);
					}
				}
			}

			// Product options.

			$product_options_fields = array(

				array(
					'filter_slug'  => 'wcf-product-options-skin',
					'setting_name' => 'productOptionsSkin',
				),
				array(
					'filter_slug'  => 'wcf-show-product-images',
					'setting_name' => 'productOptionsImages',
				),
				array(
					'filter_slug'  => 'wcf-product-opt-title',
					'setting_name' => 'productOptionsSectionTitleText',
				),
				array(
					'filter_slug'  => 'wcf-your-products-position',
					'setting_name' => 'sectionposition',
				),
			);

			if ( isset( $product_options_fields ) && is_array( $product_options_fields ) ) {

				foreach ( $product_options_fields as $key => $field ) {

					$setting_name = $field['setting_name'];

					if ( '' !== self::$settings[ $setting_name ] ) {
						add_filter(
							'cartflows_checkout_meta_' . $field['filter_slug'],
							function ( $value ) use ( $setting_name ) {

								$value = self::$settings[ $setting_name ];

								return $value;
							},
							10,
							1
						);
					}
				}
			}
		}

		/**
		 * Registers the `core/latest-posts` block on server.
		 *
		 * @param array $attr Array of block attributes.
		 *
		 * @since 1.6.13
		 */
		public function checkout_form_pro_attributes( $attr ) {

			$product_section_border_attr   = Cartflows_Pro_Gb_Helper::generate_php_border_attribute( 'product' );
			$highlight_product_border_attr = Cartflows_Pro_Gb_Helper::generate_php_border_attribute( 'productHighlight' );

			$attributes = array(
				'productBorderColor'               => array(
					'type'    => 'string',
					'default' => '',
				),
				'productBorderRadius'              => array(
					'type'    => 'string',
					'default' => '',
				),
				'productBorderWidth'               => array(
					'type'    => 'string',
					'default' => '',
				),
				'productBorderStyle'               => array(
					'type'    => 'string',
					'default' => 'none',
				),
				'productTextBgColor'               => array(
					'type'    => 'string',
					'default' => '',
				),
				'productTextColor'                 => array(
					'type'    => 'string',
					'default' => '',
				),
				'productTitleTextColor'            => array(
					'type'    => 'string',
					'default' => '',
				),
				'productHighlightBgColor'          => array(
					'type'    => 'string',
					'default' => '',
				),
				'productHighlightTextColor'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'productHighlightBorderstyle'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'productHighlightBorderWidth'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'productHighlightBorderRadius'     => array(
					'type'    => 'string',
					'default' => '',
				),
				'productHighlightBorderColor'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'productHighlightFlagTextColor'    => array(
					'type'    => 'string',
					'default' => '',
				),
				'productHighlightFlagBgColor'      => array(
					'type'    => 'string',
					'default' => '',
				),

				'sectionposition'                  => array(
					'type'    => 'string',
					'default' => '',
				),
				'twoStepBgColor'                   => array(
					'type'    => 'string',
					'default' => '',
				),
				'twoStepTextColor'                 => array(
					'type'    => 'string',
					'default' => '',
				),

				'productOptionsSkin'               => array(
					'type'    => 'string',
					'default' => '',
				),
				'productOptionsImages'             => array(
					'type'    => 'string',
					'default' => '',
				),
				'productOptionsSectionTitleText'   => array(
					'type'    => 'string',
					'default' => '',
				),
				'enableNote'                       => array(
					'type'    => 'string',
					'default' => 'yes',
				),
				'noteText'                         => array(
					'type'    => 'string',
					'default' => __(
						'Get Your FREE copy of CartFlows in just few steps.',
						'cartflows-pro'
					),
				),
				'stepOneTitleText'                 => array(
					'type'    => 'string',
					'default' => __( 'Shipping', 'cartflows-pro' ),
				),
				'stepOneSubTitleText'              => array(
					'type'    => 'string',
					'default' => __( 'Where to ship it?', 'cartflows-pro' ),
				),
				'stepTwoTitleText'                 => array(
					'type'    => 'string',
					'default' => __( 'Payment', 'cartflows-pro' ),
				),
				'stepTwoSubTitleText'              => array(
					'type'    => 'string',
					'default' => __( 'Of your order', 'cartflows-pro' ),
				),
				'offerButtonTitleText'             => array(
					'type'    => 'string',
					'default' => __( 'For Special Offer Click Here', 'cartflows-pro' ),
				),
				'offerButtonSubTitleText'          => array(
					'type'    => 'string',
					'default' => __( 'Yes! I want this offer!', 'cartflows-pro' ),
				),
				'tstextFontSize'                   => array(
					'type'    => 'number',
					'default' => '',
				),
				'tstextFontSizeType'               => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'tstextFontSizeTablet'             => array(
					'type' => 'number',
				),
				'tstextFontSizeMobile'             => array(
					'type' => 'number',
				),
				'tstextFontFamily'                 => array(
					'type'    => 'string',
					'default' => 'Default',
				),
				'tstextFontWeight'                 => array(
					'type' => 'string',
				),
				'tstextFontSubset'                 => array(
					'type' => 'string',
				),
				'tstextLineHeightType'             => array(
					'type'    => 'string',
					'default' => 'em',
				),
				'tstextLineHeight'                 => array(
					'type' => 'number',
				),
				'tstextLineHeightTablet'           => array(
					'type' => 'number',
				),
				'tstextLineHeightMobile'           => array(
					'type' => 'number',
				),
				'tstextLoadGoogleFonts'            => array(
					'type'    => 'boolean',
					'default' => false,
				),

				'productHighlightBorderHoverColor' => array(
					'type'    => 'string',
					'default' => '',
				),
				'productBorderHoverColor'          => array(
					'type'    => 'string',
					'default' => '',
				),

				'tstextFontStyle'                  => array(
					'type'    => 'string',
					'default' => '',
				),

			);

			$attributes = array_merge( $product_section_border_attr, $highlight_product_border_attr, $attributes );

			return array_merge( $attr, $attributes );
		}
	}

	/**
	*  Prepare if class 'Cartflows_Pro_Checkout_Form' exist.
	*  Kicking this off by calling 'get_instance()' method
	*/
	Cartflows_Pro_Checkout_Form::get_instance();
}
