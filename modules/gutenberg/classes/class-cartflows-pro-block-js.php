<?php
/**
 * Cartflows Pro Block Helper.
 *
 * @package Cartflows Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_Block_JS' ) ) {

	/**
	 * Class Cartflows_Pro_Block_JS.
	 */
	class Cartflows_Pro_Block_JS {

		/**
		 * Adds Google fonts for Offer Yes No Link.
		 *
		 * @since 1.6.13
		 * @param array $attr the blocks attr.
		 */
		public static function blocks_offer_yes_no_link_gfont( $attr ) {

			$link_text_load_google_font = isset( $attr['linkTextLoadGoogleFonts'] ) ? $attr['linkTextLoadGoogleFonts'] : '';
			$link_text_font_family      = isset( $attr['linkTextFontFamily'] ) ? $attr['linkTextFontFamily'] : '';
			$link_text_font_weight      = isset( $attr['linkTextFontWeight'] ) ? $attr['linkTextFontWeight'] : '';
			$link_text_font_subset      = isset( $attr['linkTextFontSubset'] ) ? $attr['linkTextFontSubset'] : '';

			Cartflows_Pro_Gb_Helper::blocks_google_font( $link_text_load_google_font, $link_text_font_family, $link_text_font_weight, $link_text_font_subset );

		}

		/**
		 * Adds Google fonts for Offer Yes No Button.
		 *
		 * @since 1.6.13
		 * @param array $attr the blocks attr.
		 */
		public static function blocks_offer_yes_no_button_gfont( $attr ) {

			$title_load_google_font = isset( $attr['titleLoadGoogleFonts'] ) ? $attr['titleLoadGoogleFonts'] : '';
			$title_font_family      = isset( $attr['titleFontFamily'] ) ? $attr['titleFontFamily'] : '';
			$title_font_weight      = isset( $attr['titleFontWeight'] ) ? $attr['titleFontWeight'] : '';
			$title_font_subset      = isset( $attr['titleFontSubset'] ) ? $attr['titleFontSubset'] : '';

			$sub_title_load_google_font = isset( $attr['subTitleLoadGoogleFonts'] ) ? $attr['subTitleLoadGoogleFonts'] : '';
			$sub_title_font_family      = isset( $attr['subTitleFontFamily'] ) ? $attr['subTitleFontFamily'] : '';
			$sub_title_font_weight      = isset( $attr['subTitleFontWeight'] ) ? $attr['subTitleFontWeight'] : '';
			$sub_title_font_subset      = isset( $attr['subTitleFontSubset'] ) ? $attr['subTitleFontSubset'] : '';

			Cartflows_Pro_Gb_Helper::blocks_google_font( $title_load_google_font, $title_font_family, $title_font_weight, $title_font_subset );
			Cartflows_Pro_Gb_Helper::blocks_google_font( $sub_title_load_google_font, $sub_title_font_family, $sub_title_font_weight, $sub_title_font_subset );

		}

		/**
		 * Adds Google fonts for Offer Product Title.
		 *
		 * @since 1.6.13
		 * @param array $attr the blocks attr.
		 */
		public static function blocks_offer_product_title_gfont( $attr ) {

			$text_load_google_font = isset( $attr['textLoadGoogleFonts'] ) ? $attr['textLoadGoogleFonts'] : '';
			$text_font_family      = isset( $attr['textFontFamily'] ) ? $attr['textFontFamily'] : '';
			$text_font_weight      = isset( $attr['textFontWeight'] ) ? $attr['textFontWeight'] : '';
			$text_font_subset      = isset( $attr['textFontSubset'] ) ? $attr['textFontSubset'] : '';

			Cartflows_Pro_Gb_Helper::blocks_google_font( $text_load_google_font, $text_font_family, $text_font_weight, $text_font_subset );

		}

		/**
		 * Adds Google fonts for Offer Product Description.
		 *
		 * @since 1.6.13
		 * @param array $attr the blocks attr.
		 */
		public static function blocks_offer_product_description_gfont( $attr ) {

			$text_load_google_font = isset( $attr['textLoadGoogleFonts'] ) ? $attr['textLoadGoogleFonts'] : '';
			$text_font_family      = isset( $attr['textFontFamily'] ) ? $attr['textFontFamily'] : '';
			$text_font_weight      = isset( $attr['textFontWeight'] ) ? $attr['textFontWeight'] : '';
			$text_font_subset      = isset( $attr['textFontSubset'] ) ? $attr['textFontSubset'] : '';

			Cartflows_Pro_Gb_Helper::blocks_google_font( $text_load_google_font, $text_font_family, $text_font_weight, $text_font_subset );

		}

		/**
		 * Adds Google fonts for Offer Product Price.
		 *
		 * @since 1.6.13
		 * @param array $attr the blocks attr.
		 */
		public static function blocks_offer_product_price_gfont( $attr ) {

			$text_load_google_font = isset( $attr['textLoadGoogleFonts'] ) ? $attr['textLoadGoogleFonts'] : '';
			$text_font_family      = isset( $attr['textFontFamily'] ) ? $attr['textFontFamily'] : '';
			$text_font_weight      = isset( $attr['textFontWeight'] ) ? $attr['textFontWeight'] : '';
			$text_font_subset      = isset( $attr['textFontSubset'] ) ? $attr['textFontSubset'] : '';

			Cartflows_Pro_Gb_Helper::blocks_google_font( $text_load_google_font, $text_font_family, $text_font_weight, $text_font_subset );

		}

		/**
		 * Adds Google fonts for Offer Product Quantity.
		 *
		 * @since 1.6.13
		 * @param array $attr the blocks attr.
		 */
		public static function blocks_offer_product_quantity_gfont( $attr ) {

			$text_load_google_font = isset( $attr['textLoadGoogleFonts'] ) ? $attr['textLoadGoogleFonts'] : '';
			$text_font_family      = isset( $attr['textFontFamily'] ) ? $attr['textFontFamily'] : '';
			$text_font_weight      = isset( $attr['textFontWeight'] ) ? $attr['textFontWeight'] : '';
			$text_font_subset      = isset( $attr['textFontSubset'] ) ? $attr['textFontSubset'] : '';

			Cartflows_Pro_Gb_Helper::blocks_google_font( $text_load_google_font, $text_font_family, $text_font_weight, $text_font_subset );
		}

		/**
		 * Adds Google fonts for Offer Product Variation.
		 *
		 * @since 1.6.13
		 * @param array $attr the blocks attr.
		 */
		public static function blocks_offer_product_variation_gfont( $attr ) {

			$text_load_google_font = isset( $attr['textLoadGoogleFonts'] ) ? $attr['textLoadGoogleFonts'] : '';
			$text_font_family      = isset( $attr['textFontFamily'] ) ? $attr['textFontFamily'] : '';
			$text_font_weight      = isset( $attr['textFontWeight'] ) ? $attr['textFontWeight'] : '';
			$text_font_subset      = isset( $attr['textFontSubset'] ) ? $attr['textFontSubset'] : '';

			Cartflows_Pro_Gb_Helper::blocks_google_font( $text_load_google_font, $text_font_family, $text_font_weight, $text_font_subset );
		}
	}
}
