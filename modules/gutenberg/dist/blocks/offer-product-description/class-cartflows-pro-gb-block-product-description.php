<?php
/**
 * WCFPB - Offer Product Description.
 *
 * @package Cartflows Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_Gb_Block_Product_Description' ) ) {

	/**
	 * Class Cartflows_Pro_Gb_Block_Product_Description.
	 */
	class Cartflows_Pro_Gb_Block_Product_Description {

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

			// Activation hook.
			add_action( 'init', array( $this, 'register_blocks' ) );
		}

		/**
		 * Registers the `core/latest-posts` block on server.
		 *
		 * @since 1.6.13
		 */
		public function register_blocks() {

			// Check if the register function exists.
			if ( ! function_exists( 'register_block_type' ) ) {
				return;
			}

			register_block_type(
				'wcfpb/offer-product-description',
				array(
					'attributes'      => array(
						'block_id'                => array(
							'type' => 'string',
						),
						'classMigrate'            => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'className'               => array(
							'type' => 'string',
						),
						// General.
						'shortDescription'        => array(
							'type'    => 'boolean',
							'default' => false,
						),
						// text alignment.
						'textAlignment'           => array(
							'type'    => 'string',
							'default' => 'center',
						),
						'ttextAlignment'          => array(
							'type'    => 'string',
							'default' => 'center',
						),
						'mtextAlignment'          => array(
							'type'    => 'string',
							'default' => 'center',
						),
						// padding.
						'paddingTypeDesktop'      => array(
							'type'    => 'string',
							'default' => 'px',
						),
						'paddingTypeTablet'       => array(
							'type'    => 'string',
							'default' => 'px',
						),
						'paddingTypeMobile'       => array(
							'type'    => 'string',
							'default' => 'px',
						),

						'vPaddingDesktop'         => array(
							'type'    => 'number',
							'default' => 0,
						),
						'hPaddingDesktop'         => array(
							'type'    => 'number',
							'default' => 0,
						),
						'vPaddingTablet'          => array(
							'type'    => 'number',
							'default' => 0,
						),
						'hPaddingTablet'          => array(
							'type'    => 'number',
							'default' => 0,
						),
						'vPaddingMobile'          => array(
							'type'    => 'number',
							'default' => 0,
						),
						'hPaddingMobile'          => array(
							'type'    => 'number',
							'default' => 0,
						),
						// text color.
						'textColor'               => array(
							'type'    => 'string',
							'default' => '',
						),

						// text font family.
						'textLoadGoogleFonts'     => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'textFontFamily'          => array(
							'type' => 'string',
						),
						'textFontWeight'          => array(
							'type' => 'string',
						),
						'textFontSubset'          => array(
							'type' => 'string',
						),
						// text font size.
						'textFontSize'            => array(
							'type' => 'number',
						),
						'textFontSizeType'        => array(
							'type'    => 'string',
							'default' => 'px',
						),
						'textFontSizeTablet'      => array(
							'type' => 'number',
						),
						'textFontSizeMobile'      => array(
							'type' => 'number',
						),
						// text line height.
						'textLineHeightType'      => array(
							'type'    => 'string',
							'default' => 'em',
						),
						'textLineHeight'          => array(
							'type' => 'number',
						),
						'textLineHeightTablet'    => array(
							'type' => 'number',
						),
						'textLineHeightMobile'    => array(
							'type' => 'number',
						),

						// Text Shadow.
						'textShadowColor'         => array(
							'type' => 'string',
						),
						'textShadowHOffset'       => array(
							'type'    => 'number',
							'default' => 0,
						),
						'textShadowVOffset'       => array(
							'type'    => 'number',
							'default' => 0,
						),
						'textShadowBlur'          => array(
							'type' => 'number',
						),
						'deviceType'              => array(
							'type'    => 'string',
							'default' => 'Desktop',
						),

						// new attr.
						'textHoverColor'          => array(
							'type'    => 'string',
							'default' => '',
						),
						'paddingTop'              => array(
							'type'    => 'number',
							'default' => 0,
						),
						'paddingBottom'           => array(
							'type'    => 'number',
							'default' => 0,
						),
						'paddingLeft'             => array(
							'type'    => 'number',
							'default' => 0,
						),
						'paddingRight'            => array(
							'type'    => 'number',
							'default' => 0,
						),
						'paddingTopTablet'        => array(
							'type'    => 'number',
							'default' => 0,
						),
						'paddingBottomTablet'     => array(
							'type'    => 'number',
							'default' => 0,
						),
						'paddingLeftTablet'       => array(
							'type'    => 'number',
							'default' => 0,
						),
						'paddingRightTablet'      => array(
							'type'    => 'number',
							'default' => 0,
						),
						'paddingTopMobile'        => array(
							'type'    => 'number',
							'default' => 0,
						),
						'paddingBottomMobile'     => array(
							'type'    => 'number',
							'default' => 0,
						),
						'paddingLeftMobile'       => array(
							'type'    => 'number',
							'default' => 0,
						),
						'paddingRightMobile'      => array(
							'type'    => 'number',
							'default' => 0,
						),
						'textFontStyle'           => array(
							'type'    => 'string',
							'default' => 'Desktop',
						),

						'textTransform'           => array(
							'type'    => 'string',
							'default' => 'none',
						),
						'textLetterSpacing'       => array(
							'type'    => 'number',
							'default' => '',
						),
						'textLetterSpacingTablet' => array(
							'type'    => 'number',
							'default' => '',
						),
						'textLetterSpacingMobile' => array(
							'type'    => 'number',
							'default' => '',
						),
						'textLetterSpacingType'   => array(
							'type'    => 'string',
							'default' => 'px',
						),
					),
					'render_callback' => array( $this, 'render_html' ),
				)
			);
		}


		/**
		 * Render Offer Product Description HTML.
		 *
		 * @param array $attributes Array of block attributes.
		 *
		 * @since 1.6.13
		 */
		public function render_html( $attributes ) {

			$advanced_classes = Cartflows_Pro_Gb_Helper::get_instance()->generate_advanced_setting_classes( $attributes );
			$zindex_wrap      = $advanced_classes['zindex_wrap'];

			$main_classes = array(
				'wp-block-wcfpb-offer-product-description',
				'cfp-block-' . $attributes['block_id'],
				$advanced_classes['desktop_class'],
				$advanced_classes['tab_class'],
				$advanced_classes['mob_class'],
				$advanced_classes['zindex_extention_enabled'] ? 'uag-blocks-common-selector' : '',
			);

			if ( isset( $attributes['className'] ) ) {
				$main_classes[] = $attributes['className'];
			}

			$classes = array(
				'wpcfp__offer-product-description',
			);

			ob_start();

			?>
				<div class = "<?php echo esc_attr( implode( ' ', $main_classes ) ); ?>" style="<?php echo esc_attr( implode( '', $zindex_wrap ) ); ?>">
					<div class = "<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
						<?php
						if ( $attributes['shortDescription'] ) {
							echo do_shortcode( '[cartflows_offer_product_short_desc]' );
						} else {
							echo do_shortcode( '[cartflows_offer_product_desc]' );
						}
						?>
					</div>
				</div>
				<?php

				return ob_get_clean();
		}


	}

	/**
	 *  Prepare if class 'Cartflows_Pro_Gb_Block_Product_Description' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Pro_Gb_Block_Product_Description::get_instance();
}
