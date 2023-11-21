<?php
/**
 * WCFPB - Offer Product Variation.
 *
 * @package Cartflows Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_Gb_Block_Product_Variation' ) ) {

	/**
	 * Class Cartflows_Pro_Gb_Block_Product_Variation.
	 */
	class Cartflows_Pro_Gb_Block_Product_Variation {

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
				'wcfpb/offer-product-variation',
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
						// text alignment.
						'alignment'               => array(
							'type'    => 'string',
							'default' => 'center',
						),
						// width.
						'width'                   => array(
							'type' => 'number',
						),
						// Label bottom spacing.
						'label_bottom_spacing'    => array(
							'type' => 'number',
						),
						// margin.
						'topMargin'               => array(
							'type'    => 'number',
							'default' => 0,
						),
						'bottomMargin'            => array(
							'type'    => 'number',
							'default' => 0,
						),
						// label color.
						'labelColor'              => array(
							'type'    => 'string',
							'default' => '',
						),
						// input color.
						'inputTextColor'          => array(
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
						'textFontStyle'           => array(
							'type'    => 'string',
							'default' => 'Desktop',
						),

						'textMarginTop'           => array(
							'type'    => 'number',
							'default' => '',
						),
						'textMarginBottom'        => array(
							'type'    => 'number',
							'default' => 25,
						),
						'textMarginLeft'          => array(
							'type'    => 'number',
							'default' => '',
						),
						'textMarginRight'         => array(
							'type'    => 'number',
							'default' => '',
						),
						'textMarginTopTablet'     => array(
							'type'    => 'number',
							'default' => '',
						),
						'textMarginRightTablet'   => array(
							'type'    => 'number',
							'default' => '',
						),
						'textMarginBottomTablet'  => array(
							'type'    => 'number',
							'default' => 25,
						),
						'textMarginLeftTablet'    => array(
							'type'    => 'number',
							'default' => '',
						),
						'textMarginTopMobile'     => array(
							'type'    => 'number',
							'default' => '',
						),
						'textMarginRightMobile'   => array(
							'type'    => 'number',
							'default' => '',
						),
						'textMarginBottomMobile'  => array(
							'type'    => 'number',
							'default' => 25,
						),
						'textMarginLeftMobile'    => array(
							'type'    => 'number',
							'default' => '',
						),
						'textMarginTypeDesktop'   => array(
							'type'    => 'string',
							'default' => 'px',
						),
						'textMarginTypeTablet'    => array(
							'type'    => 'string',
							'default' => 'px',
						),
						'textMarginTypeMobile'    => array(
							'type'    => 'string',
							'default' => 'px',
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
		 * Render Offer Product Variation HTML.
		 *
		 * @param array $attributes Array of block attributes.
		 *
		 * @since 1.6.13
		 */
		public function render_html( $attributes ) {

			$advanced_classes = Cartflows_Pro_Gb_Helper::get_instance()->generate_advanced_setting_classes( $attributes );
			$zindex_wrap      = $advanced_classes['zindex_wrap'];

			$main_classes = array(
				'wp-block-wcfpb-offer-product-variation',
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
				'wpcfp__offer-product-variation',
			);

			ob_start();
			?>
				<div class = "<?php echo esc_attr( implode( ' ', $main_classes ) ); ?>" style="<?php echo esc_attr( implode( '', $zindex_wrap ) ); ?>">
					<div class = "<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
						<?php echo do_shortcode( '[cartflows_offer_product_variation]' ); ?>
					</div>
				</div>
				<?php

				return ob_get_clean();
		}


	}

	/**
	 *  Prepare if class 'Cartflows_Pro_Gb_Block_Product_Variation' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Pro_Gb_Block_Product_Variation::get_instance();
}
