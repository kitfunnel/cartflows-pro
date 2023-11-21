<?php
/**
 * WCFPB - Offer Product Image.
 *
 * @package Cartflows Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_Gb_Block_Product_Image' ) ) {

	/**
	 * Class Cartflows_Pro_Gb_Block_Product_Image.
	 */
	class Cartflows_Pro_Gb_Block_Product_Image {

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

			$attr = array(
				'block_id'                   => array(
					'type' => 'string',
				),
				'classMigrate'               => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'className'                  => array(
					'type' => 'string',
				),
				// text alignment.
				'alignment'                  => array(
					'type'    => 'string',
					'default' => 'center',
				),
				// image bottom spacing.
				'image_bottom_spacing'       => array(
					'type' => 'number',
				),
				// margin.
				'topMargin'                  => array(
					'type'    => 'number',
					'default' => 0,
				),
				'bottomMargin'               => array(
					'type'    => 'number',
					'default' => 0,
				),
				// Image Border.
				'imageBorderStyle'           => array(
					'type'    => 'string',
					'default' => 'none',
				),
				'imageBorderWidth'           => array(
					'type' => 'number',
				),
				'imageBorderColor'           => array(
					'type' => 'string',
				),
				'imageBorderRadius'          => array(
					'type' => 'number',
				),
				// spacing between thumbnails.
				'spacing_between_thumbnails' => array(
					'type' => 'number',
				),
				// Thumbnail Border.
				'thumbnailBorderStyle'       => array(
					'type'    => 'string',
					'default' => 'none',
				),
				'thumbnailBorderWidth'       => array(
					'type' => 'number',
				),
				'thumbnailBorderColor'       => array(
					'type' => 'string',
				),
				'thumbnailBorderRadius'      => array(
					'type' => 'number',
				),
				'deviceType'                 => array(
					'type'    => 'string',
					'default' => 'Desktop',
				),
				'linkTextFontStyle'          => array(
					'type'    => 'string',
					'default' => '',
				),

				'imageMarginTop'             => array(
					'type'    => 'number',
					'default' => '',
				),
				'imageMarginBottom'          => array(
					'type'    => 'number',
					'default' => 25,
				),
				'imageMarginLeft'            => array(
					'type'    => 'number',
					'default' => '',
				),
				'imageMarginRight'           => array(
					'type'    => 'number',
					'default' => '',
				),
				'imageMarginTopTablet'       => array(
					'type'    => 'number',
					'default' => '',
				),
				'imageMarginRightTablet'     => array(
					'type'    => 'number',
					'default' => '',
				),
				'imageMarginBottomTablet'    => array(
					'type'    => 'number',
					'default' => 25,
				),
				'imageMarginLeftTablet'      => array(
					'type'    => 'number',
					'default' => '',
				),
				'imageMarginTopMobile'       => array(
					'type'    => 'number',
					'default' => '',
				),
				'imageMarginRightMobile'     => array(
					'type'    => 'number',
					'default' => '',
				),
				'imageMarginBottomMobile'    => array(
					'type'    => 'number',
					'default' => 25,
				),
				'imageMarginLeftMobile'      => array(
					'type'    => 'number',
					'default' => '',
				),
				'imageMarginTypeDesktop'     => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'imageMarginTypeTablet'      => array(
					'type'    => 'string',
					'default' => 'px',
				),
				'imageMarginTypeMobile'      => array(
					'type'    => 'string',
					'default' => 'px',
				),

			);

			$image_border_attr     = Cartflows_Pro_Gb_Helper::generate_php_border_attribute( 'image' );
			$thumbnail_border_attr = Cartflows_Pro_Gb_Helper::generate_php_border_attribute( 'thumbnail' );

			$attr = array_merge( $image_border_attr, $thumbnail_border_attr, $attr );

			register_block_type(
				'wcfpb/offer-product-image',
				array(
					'attributes'      => $attr,
					'render_callback' => array( $this, 'render_html' ),
				)
			);

		}

		/**
		 * Render Offer Product Image HTML.
		 *
		 * @param array $attributes Array of block attributes.
		 *
		 * @since 1.6.13
		 */
		public function render_html( $attributes ) {

			$advanced_classes = Cartflows_Pro_Gb_Helper::get_instance()->generate_advanced_setting_classes( $attributes );

			$zindex_wrap = $advanced_classes['zindex_wrap'];

			$main_classes = array(
				'wp-block-wcfpb-offer-product-image',
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
				'wpcfp__offer-product-image',
			);

			ob_start();
			?>
				<div class = "<?php echo esc_attr( implode( ' ', $main_classes ) ); ?>" style="<?php echo esc_attr( implode( '', $zindex_wrap ) ); ?>">
					<div class = "<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
						<?php echo do_shortcode( '[cartflows_offer_product_image]' ); ?>
					</div>
				</div>
				<?php

				return ob_get_clean();
		}


	}

	/**
	 *  Prepare if class 'Cartflows_Pro_Gb_Block_Product_Image' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Pro_Gb_Block_Product_Image::get_instance();
}
