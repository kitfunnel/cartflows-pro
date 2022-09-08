<?php
/**
 * WCFPB - Offer Product Title.
 *
 * @package Cartflows Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_Gb_Block_Product_Title' ) ) {

	/**
	 * Class Cartflows_Pro_Gb_Block_Product_Title.
	 */
	class Cartflows_Pro_Gb_Block_Product_Title {

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
				'wcfpb/offer-product-title',
				array(
					'attributes'      => array(
						'block_id'             => array(
							'type' => 'string',
						),
						'classMigrate'         => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'className'            => array(
							'type' => 'string',
						),
						// text alignment.
						'textAlignment'        => array(
							'type'    => 'string',
							'default' => 'center',
						),
						// margin.
						'topMargin'            => array(
							'type'    => 'number',
							'default' => 0,
						),
						'bottomMargin'         => array(
							'type'    => 'number',
							'default' => 0,
						),
						// text color.
						'textColor'            => array(
							'type'    => 'string',
							'default' => '',
						),
						'textHoverColor'       => array(
							'type'    => 'string',
							'default' => '',
						),

						// text font family.
						'textLoadGoogleFonts'  => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'textFontFamily'       => array(
							'type' => 'string',
						),
						'textFontWeight'       => array(
							'type' => 'string',
						),
						'textFontSubset'       => array(
							'type' => 'string',
						),
						// text font size.
						'textFontSize'         => array(
							'type' => 'number',
						),
						'textFontSizeType'     => array(
							'type'    => 'string',
							'default' => 'px',
						),
						'textFontSizeTablet'   => array(
							'type' => 'number',
						),
						'textFontSizeMobile'   => array(
							'type' => 'number',
						),
						// text line height.
						'textLineHeightType'   => array(
							'type'    => 'string',
							'default' => 'em',
						),
						'textLineHeight'       => array(
							'type' => 'number',
						),
						'textLineHeightTablet' => array(
							'type' => 'number',
						),
						'textLineHeightMobile' => array(
							'type' => 'number',
						),

						// Text Shadow.
						'textShadowColor'      => array(
							'type' => 'string',
						),
						'textShadowHOffset'    => array(
							'type'    => 'number',
							'default' => 0,
						),
						'textShadowVOffset'    => array(
							'type'    => 'number',
							'default' => 0,
						),
						'textShadowBlur'       => array(
							'type' => 'number',
						),
						'deviceType'           => array(
							'type'    => 'string',
							'default' => 'Desktop',
						),
					),
					'render_callback' => array( $this, 'render_html' ),
				)
			);

		}


		/**
		 * Render Offer Product Title HTML.
		 *
		 * @param array $attributes Array of block attributes.
		 *
		 * @since 1.6.13
		 */
		public function render_html( $attributes ) {

			$main_classes = array(
				'wp-block-wcfpb-offer-product-title',
				'cfp-block-' . $attributes['block_id'],
			);

			if ( isset( $attributes['className'] ) ) {
				$main_classes[] = $attributes['className'];
			}

			$classes = array(
				'wpcfp__offer-product-title',
			);

			ob_start();

			?>
				<div class = "<?php echo esc_attr( implode( ' ', $main_classes ) ); ?>">
					<div class = "<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
						<?php
							echo do_shortcode( '[cartflows_offer_product_title]' );
						?>
					</div>
				</div>
				<?php

				return ob_get_clean();
		}


	}

	/**
	 *  Prepare if class 'Cartflows_Pro_Gb_Block_Product_Title' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Pro_Gb_Block_Product_Title::get_instance();
}
