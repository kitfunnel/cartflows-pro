<?php
/**
 * Cartflows Pro Config.
 *
 * @package Cartflows Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_Block_Config' ) ) {

	/**
	 * Class Cartflows_Pro_Block_Config.
	 */
	class Cartflows_Pro_Block_Config {

		/**
		 * Block Attributes
		 *
		 * @var block_attributes
		 */
		public static $block_attributes = null;

		/**
		 * Block Assets
		 *
		 * @var block_attributes
		 */
		public static $block_assets = null;

		/**
		 * Get Widget List.
		 *
		 * @since 1.6.13
		 *
		 * @return array The Widget List.
		 */
		public static function get_block_attributes() {

			if ( null === self::$block_attributes ) {
				self::$block_attributes = array(
					'wcfpb/offer-yes-no-link'         => array(
						'slug'        => '',
						'title'       => __( 'Offer Yes/No Link', 'cartflows-pro' ),
						'description' => '',
						'default'     => true,
						'attributes'  => array(
							'classMigrate'             => false,
							'linkTextAlignment'        => 'center',
							'topMargin'                => '0',
							'bottomMargin'             => '0',
							'linkTextColor'            => '#4169E1',
							'linkTextHoverColor'       => '',
							'linkTextFontFamily'       => '',
							'linkTextFontSize'         => '20',
							'linkTextFontWeight'       => '500',
							'linkTextFontSizeType'     => 'px',
							'linkTextFontSizeTablet'   => '',
							'linkTextFontSizeMobile'   => '',
							'linkTextLineHeightType'   => 'em',
							'linkTextLineHeight'       => '',
							'linkTextLineHeightTablet' => '',
							'linkTextLineHeightMobile' => '',
							'iconSize'                 => '20',
							'iconSpacing'              => '0',
							'iconPosition'             => 'before_link_text',
						),
					),
					'wcfpb/offer-yes-no-button'       => array(
						'slug'        => '',
						'title'       => __( 'Offer Yes/No Button', 'cartflows-pro' ),
						'description' => '',
						'default'     => true,
						'attributes'  => array(
							'classMigrate'             => false,
							'borderStyle'              => 'solid',
							'align'                    => 'center',
							'malign'                   => 'center',
							'talign'                   => 'center',
							'borderWidth'              => '1',
							'borderColor'              => '#abb8c3',
							'borderHoverColor'         => '#abb8c3',
							'borderRadius'             => '4',
							'backgroundType'           => 'color',
							'buttonHoverColor'         => '#abb8c3',
							'backgroundImage'          => '',
							'backgroundPosition'       => 'center-center',
							'backgroundSize'           => 'cover',
							'backgroundRepeat'         => 'no-repeat',
							'backgroundAttachment'     => 'scroll',
							'backgroundColor'          => '#abb8c3',
							'gradientColor1'           => '#abb8c3',
							'gradientColor2'           => '#abb8c3',
							'gradientType'             => 'linear',
							'gradientLocation1'        => '0',
							'gradientLocation2'        => '100',
							'gradientAngle'            => '0',
							'gradientPosition'         => 'center center',
							'backgroundOpacity'        => '100',
							'backgroundImageColor'     => '#abb8c3',
							'gradientValue'            => '',
							'paddingTypeDesktop'       => 'px',
							'paddingTypeTablet'        => 'px',
							'paddingTypeMobile'        => 'px',
							'vPaddingDesktop'          => '5',
							'hPaddingDesktop'          => '30',
							'vPaddingTablet'           => '5',
							'hPaddingTablet'           => '30',
							'vPaddingMobile'           => '5',
							'hPaddingMobile'           => '30',
							'textAlignment'            => 'center',
							'textColor'                => '#ffffff',
							'textHoverColor'           => '#ffffff',
							'titleFontFamily'          => '',
							'titleFontSize'            => '20',
							'titleFontWeight'          => '500',
							'titleFontSizeType'        => 'px',
							'titleFontSizeTablet'      => '',
							'titleFontSizeMobile'      => '',
							'titleLineHeightType'      => 'em',
							'titleLineHeight'          => '',
							'titleLineHeightTablet'    => '',
							'titleLineHeightMobile'    => '',
							'subTitleFontFamily'       => '',
							'subTitleFontWeight'       => '500',
							'subTitleFontSize'         => '20',
							'subTitleFontSizeType'     => 'px',
							'subTitleFontSizeTablet'   => '',
							'subTitleFontSizeMobile'   => '',
							'subTitleLineHeightType'   => 'em',
							'subTitleLineHeight'       => '',
							'subTitleLineHeightTablet' => '',
							'subTitleLineHeightMobile' => '',
							'titleBottomSpacing'       => '0',
							'iconSize'                 => '20',
							'iconSpacing'              => '0',
							'iconColor'                => '',
							'iconHoverColor'           => '',
							'iconPosition'             => 'before_title',
						),
					),
					'wcfpb/offer-product-title'       => array(
						'slug'        => '',
						'title'       => __( 'Offer Product Title', 'cartflows-pro' ),
						'description' => '',
						'default'     => true,
						'attributes'  => array(
							'classMigrate'         => false,
							'textAlignment'        => 'center',
							'topMargin'            => '0',
							'bottomMargin'         => '0',
							'textColor'            => '',
							'textHoverColor'       => '',
							'textFontFamily'       => '',
							'textFontSize'         => '',
							'textFontWeight'       => '',
							'textFontSizeType'     => 'px',
							'textFontSizeTablet'   => '',
							'textFontSizeMobile'   => '',
							'textLineHeightType'   => 'em',
							'textLineHeight'       => '',
							'textLineHeightTablet' => '',
							'textLineHeightMobile' => '',
							'textShadowColor'      => '',
							'textShadowHOffset'    => '0',
							'textShadowVOffset'    => '0',
							'textShadowBlur'       => '',
						),
					),
					'wcfpb/offer-product-description' => array(
						'slug'        => '',
						'title'       => __( 'Offer Product Description', 'cartflows-pro' ),
						'description' => '',
						'default'     => true,
						'attributes'  => array(
							'classMigrate'         => false,
							'textAlignment'        => 'center',
							'paddingTypeDesktop'   => 'px',
							'paddingTypeTablet'    => 'px',
							'paddingTypeMobile'    => 'px',
							'vPaddingDesktop'      => '0',
							'hPaddingDesktop'      => '0',
							'vPaddingTablet'       => '0',
							'hPaddingTablet'       => '0',
							'vPaddingMobile'       => '0',
							'hPaddingMobile'       => '0',
							'textColor'            => '',
							'textFontFamily'       => '',
							'textFontSize'         => '',
							'textFontWeight'       => '',
							'textFontSizeType'     => 'px',
							'textFontSizeTablet'   => '',
							'textFontSizeMobile'   => '',
							'textLineHeightType'   => 'em',
							'textLineHeight'       => '',
							'textLineHeightTablet' => '',
							'textLineHeightMobile' => '',
							'textShadowColor'      => '',
							'textShadowHOffset'    => '0',
							'textShadowVOffset'    => '0',
							'textShadowBlur'       => '',
						),
					),
					'wcfpb/offer-product-price'       => array(
						'slug'        => '',
						'title'       => __( 'Offer Product Price', 'cartflows-pro' ),
						'description' => '',
						'default'     => true,
						'attributes'  => array(
							'classMigrate'         => false,
							'textAlignment'        => 'center',
							'topMargin'            => '0',
							'bottomMargin'         => '0',
							'textColor'            => '',
							'textHoverColor'       => '',
							'textFontFamily'       => '',
							'textFontSize'         => '',
							'textFontWeight'       => '',
							'textFontSizeType'     => 'px',
							'textFontSizeTablet'   => '',
							'textFontSizeMobile'   => '',
							'textLineHeightType'   => 'em',
							'textLineHeight'       => '',
							'textLineHeightTablet' => '',
							'textLineHeightMobile' => '',
							'textShadowColor'      => '',
							'textShadowHOffset'    => '0',
							'textShadowVOffset'    => '0',
							'textShadowBlur'       => '',
						),
					),
					'wcfpb/offer-product-quantity'    => array(
						'slug'        => '',
						'title'       => __( 'Offer Product Quantity', 'cartflows-pro' ),
						'description' => '',
						'default'     => true,
						'attributes'  => array(
							'classMigrate'         => false,
							'alignment'            => 'center',
							'width'                => '',
							'label_bottom_spacing' => '',
							'topMargin'            => '0',
							'bottomMargin'         => '0',
							'labelColor'           => '',
							'inputTextColor'       => '',
							'backgroundColor'      => '',
							'textFontFamily'       => '',
							'textFontSize'         => '',
							'textFontWeight'       => '',
							'textFontSizeType'     => 'px',
							'textFontSizeTablet'   => '',
							'textFontSizeMobile'   => '',
							'textLineHeightType'   => 'em',
							'textLineHeight'       => '',
							'textLineHeightTablet' => '',
							'textLineHeightMobile' => '',
							'textShadowColor'      => '',
							'textShadowHOffset'    => '0',
							'textShadowVOffset'    => '0',
							'textShadowBlur'       => '',
							'borderStyle'          => 'solid',
							'borderWidth'          => '',
							'borderColor'          => '',
							'borderRadius'         => '',
						),
					),
					'wcfpb/offer-product-variation'   => array(
						'slug'        => '',
						'title'       => __( 'Offer Product Variation', 'cartflows-pro' ),
						'description' => '',
						'default'     => true,
						'attributes'  => array(
							'classMigrate'         => false,
							'alignment'            => 'center',
							'width'                => '',
							'label_bottom_spacing' => '',
							'topMargin'            => '0',
							'bottomMargin'         => '0',
							'labelColor'           => '',
							'inputTextColor'       => '',
							'textFontFamily'       => '',
							'textFontSize'         => '',
							'textFontWeight'       => '',
							'textFontSizeType'     => 'px',
							'textFontSizeTablet'   => '',
							'textFontSizeMobile'   => '',
							'textLineHeightType'   => 'em',
							'textLineHeight'       => '',
							'textLineHeightTablet' => '',
							'textLineHeightMobile' => '',
							'textShadowColor'      => '',
							'textShadowHOffset'    => '0',
							'textShadowVOffset'    => '0',
							'textShadowBlur'       => '',

						),
					),
					'wcfpb/offer-product-image'       => array(
						'slug'        => '',
						'title'       => __( 'Offer Product Image', 'cartflows-pro' ),
						'description' => '',
						'default'     => true,
						'attributes'  => array(
							'classMigrate'               => false,
							'alignment'                  => 'center',
							'image_bottom_spacing'       => '',
							'topMargin'                  => '0',
							'bottomMargin'               => '0',
							'imageBorderStyle'           => 'none',
							'imageBorderWidth'           => '',
							'imageBorderColor'           => '',
							'imageBorderRadius'          => '',
							'spacing_between_thumbnails' => '',
							'thumbnailBorderStyle'       => 'none',
							'thumbnailBorderWidth'       => '',
							'thumbnailBorderColor'       => '',
							'thumbnailBorderRadius'      => '',
						),
					),

				);
			}
				return self::$block_attributes;
		}

				/**
				 * Get Block Assets.
				 *
				 * @since 1.6.13
				 *
				 * @return array The Asset List.
				 */
		public static function get_block_assets() {

			if ( null === self::$block_assets ) {
				self::$block_assets = array();
			}
			return self::$block_assets;
		}
	}
}
