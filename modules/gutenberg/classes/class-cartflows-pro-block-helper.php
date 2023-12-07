<?php
/**
 * Cartflows Pro Block Helper.
 *
 * @package Cartflows Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_Block_Helper' ) ) {

	/**
	 * Class Cartflows_Pro_Block_Helper.
	 */
	class Cartflows_Pro_Block_Helper {


		/**
		 * Get Offer Yes No Link CSS
		 *
		 * @since 1.6.13
		 * @param array  $attr The block attributes.
		 * @param string $id The selector ID.
		 * @return array The Widget List.
		 */
		public static function get_checkout_form_pro_css( $attr, $id ) {

			$defaults = Cartflows_Gb_Helper::$block_list['wcfb/checkout-form']['attributes'];

			$product_border_attribute    = Cartflows_Pro_Gb_Helper::generate_border_attribute( 'product' );
			$product_hl_border_attribute = Cartflows_Pro_Gb_Helper::generate_border_attribute( 'productHighlight' );

			$defaults = array_merge( $defaults, $product_border_attribute, $product_hl_border_attribute );
			$attr     = array_merge( $defaults, (array) $attr );

			$productborder_css        = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'product' );
			$productborder_css_tablet = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'product', 'tablet' );
			$productborder_css_mobile = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'product', 'mobile' );

			$product_highlightborder_css        = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'productHighlight' );
			$product_highlightborder_css_tablet = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'productHighlight', 'tablet' );
			$product_highlightborder_css_mobile = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'productHighlight', 'mobile' );

			$product_text_color         = ( isset( $attr['productTextColor'] ) ? $attr['productTextColor'] : '' );
			$product_text_bg_color      = ( isset( $attr['productTextBgColor'] ) ? $attr['productTextBgColor'] : '' );
			$product_border_style       = ( isset( $attr['productBorderStyle'] ) ? $attr['productBorderStyle'] : '' );
			$product_border_width       = ( isset( $attr['productBorderWidth'] ) ? $attr['productBorderWidth'] : '' );
			$product_border_color       = ( isset( $attr['productBorderColor'] ) ? $attr['productBorderColor'] : '' );
			$product_border_hover_color = ( isset( $attr['productBorderHColor'] ) ? $attr['productBorderHColor'] : '' );
			$product_border_radius      = ( isset( $attr['productBorderRadius'] ) ? $attr['productBorderRadius'] : '' );

			$product_title_text_color = ( isset( $attr['productTitleTextColor'] ) ? $attr['productTitleTextColor'] : '' );

			$product_highlight_bg_color           = ( isset( $attr['productHighlightBgColor'] ) ? $attr['productHighlightBgColor'] : '' );
			$product_highlight_text_color         = ( isset( $attr['productHighlightTextColor'] ) ? $attr['productHighlightTextColor'] : '' );
			$product_highlight_border_style       = ( isset( $attr['productHighlightBorderstyle'] ) ? $attr['productHighlightBorderstyle'] : '' );
			$product_highlight_border_width       = ( isset( $attr['productHighlightBorderWidth'] ) ? $attr['productHighlightBorderWidth'] . 'px' : '' );
			$product_highlight_border_radious     = ( isset( $attr['productHighlightBorderRadius'] ) ? $attr['productHighlightBorderRadius'] . 'px' : '' );
			$product_highlight_border_color       = ( isset( $attr['productHighlightBorderColor'] ) ? $attr['productHighlightBorderColor'] : '' );
			$product_highlight_border_hover_color = ( isset( $attr['productHighlightBorderHColor'] ) ? $attr['productHighlightBorderHColor'] : '' );
			$product_highlight_flat_text_color    = ( isset( $attr['productHighlightFlagTextColor'] ) ? $attr['productHighlightFlagTextColor'] : '' );
			$product_highlight_flat_bg_color      = ( isset( $attr['productHighlightFlagBgColor'] ) ? $attr['productHighlightFlagBgColor'] : '' );

			$two_step_bg_color   = ( isset( $attr['twoStepBgColor'] ) ? $attr['twoStepBgColor'] : '' );
			$two_step_text_color = ( isset( $attr['twoStepTextColor'] ) ? $attr['twoStepTextColor'] : '' );

			$tstext_font_family        = ( isset( $attr['tstextFontFamily'] ) ? $attr['tstextFontFamily'] : 'Default' );
			$tstext_font_weight        = ( isset( $attr['tstextFontWeight'] ) ? $attr['tstextFontWeight'] : '' );
			$tstext_font_size_type     = ( isset( $attr['tstextFontSizeType'] ) ? $attr['tstextFontSizeType'] : 'px' );
			$tstext_line_height_type   = ( isset( $attr['tstextLineHeightType'] ) ? $attr['tstextLineHeightType'] : 'em' );
			$tstext_font_size          = ( isset( $attr['tstextFontSize'] ) ? $attr['tstextFontSize'] : '' );
			$tstext_line_height        = ( isset( $attr['tstextLineHeight'] ) ? $attr['tstextLineHeight'] : '' );
			$tstext_font_size_tablet   = ( isset( $attr['tstextFontSizeTablet'] ) ? $attr['tstextFontSizeTablet'] : '' );
			$tstext_font_size_mobile   = ( isset( $attr['tstextFontSizeMobile'] ) ? $attr['tstextFontSizeMobile'] : '' );
			$tstext_line_height_mobile = ( isset( $attr['tstextLineHeightMobile'] ) ? $attr['tstextLineHeightMobile'] : '' );
			$tstext_line_height_tablet = ( isset( $attr['tstextLineHeightTablet'] ) ? $attr['tstextLineHeightTablet'] : '' );

			$t_selectors = array();
			$m_selectors = array();
			$selectors   = array(

				' .cf-block-' . $id . ' .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .wcf-qty-options .wcf-qty-header .wcf-field-label, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .wcf-yp-skin-classic .wcf-qty-options .wcf-qty-row' => array(
					'color' => $product_text_color,
				),
				' .cf-block-' . $id . ' .wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options, .cf-block-' . $id . ' .wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row' => array_merge(
					$productborder_css,
					array(
						'background-color' => $product_text_bg_color,
					)
				),
				' .cf-block-' . $id . ' .wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options:hover, .cf-block-' . $id . ' .wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row:hover' => array(
					'border-color' => $product_border_hover_color,
				),
				' .cf-block-' . $id . ' .wcf-product-option-wrap #your_products_heading' => array(
					'color' => $product_title_text_color,
				),
				' .cf-block-' . $id . ' .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight' => array_merge(
					$product_highlightborder_css,
					array(
						'background-color' => $product_highlight_bg_color,
						'color'            => $product_highlight_text_color,
					)
				),
				' .cf-block-' . $id . ' .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight:hover' => array(
					'border-color' => $product_highlight_border_hover_color,
				),
				'.cf-block-' . $id . ' .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight .wcf-highlight-head' => array(
					'color'            => $product_highlight_flat_text_color,
					'background-color' => $product_highlight_flat_bg_color,
				),

				' .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note' => array(
					'color'            => $two_step_text_color,
					'background-color' => $two_step_bg_color,
					'border-color'     => $two_step_bg_color,
					'font-family'      => $tstext_font_family,
					'font-weight'      => $tstext_font_weight,
					'font-size'        => Cartflows_Pro_Gb_Helper::get_css_value( $tstext_font_size, $tstext_font_size_type ),
					'line-height'      => Cartflows_Pro_Gb_Helper::get_css_value( $tstext_line_height, $tstext_line_height_type ),
				),
				' .cf-block-' . $id . ' .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note:before' => array(
					'border-top-color' => $two_step_bg_color,
				),
			);

			$t_selectors = array(
				' .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note' => array(
					'font-size'   => Cartflows_Pro_Gb_Helper::get_css_value( $tstext_font_size_tablet, $tstext_font_size_type ),
					'line-height' => Cartflows_Pro_Gb_Helper::get_css_value( $tstext_line_height_tablet, $tstext_line_height_type ),
				),
				' .cf-block-' . $id . ' .wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options, .cf-block-' . $id . ' .wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row' => $productborder_css_tablet,
				' .cf-block-' . $id . ' .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight' => $product_highlightborder_css_tablet,
			);

			$m_selectors = array(
				' .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note' => array(
					'font-size'   => Cartflows_Pro_Gb_Helper::get_css_value( $tstext_font_size_mobile, $tstext_font_size_type ),
					'line-height' => Cartflows_Pro_Gb_Helper::get_css_value( $tstext_line_height_mobile, $tstext_line_height_type ),
				),
				' .cf-block-' . $id . ' .wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options, .cf-block-' . $id . ' .wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row' => $productborder_css_mobile,
				' .cf-block-' . $id . ' .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight' => $product_highlightborder_css_mobile,
			);

			$combined_selectors = array(
				'desktop' => $selectors,
				'tablet'  => $t_selectors,
				'mobile'  => $m_selectors,
			);

			return Cartflows_Pro_Gb_Helper::generate_all_css( $combined_selectors, ' body ' );
		}

		/**
		 * Get Offer Yes No Link CSS
		 *
		 * @since 1.6.13
		 * @param array  $attr The block attributes.
		 * @param string $id The selector ID.
		 * @return array The Widget List.
		 */
		public static function get_offer_yes_no_link_css( $attr, $id ) {

			$defaults = Cartflows_Pro_Gb_Helper::$block_list['wcfpb/offer-yes-no-link']['attributes'];

			$attr = array_merge( $defaults, (array) $attr );

			$m_selectors = array();
			$t_selectors = array();

			$selectors = array(

				' .wpcfp__offer-yes-no-link'           => array(
					'text-align'    => $attr['linkTextAlignment'],
					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['linkTextMarginTop'], $attr['linkTextMarginTypeDesktop'] ),
					'margin-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['linkTextMarginRight'], $attr['linkTextMarginTypeDesktop'] ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['linkTextMarginBottom'], $attr['linkTextMarginTypeDesktop'] ),
					'margin-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['linkTextMarginLeft'], $attr['linkTextMarginTypeDesktop'] ),
				),

				' .wpcfp__offer-yes-no-link .wpcfp__offer-yes-no-link-text' => array(
					'text-transform' => $attr['linkTextTransform'],
					'letter-spacing' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['linkTextLetterSpacing'], $attr['linkTextLetterSpacingType'] ),
				),

				' .wpcfp__offer-yes-no-link-url'       => array(
					'color' => $attr['linkTextColor'],
				),
				' .wpcfp__offer-yes-no-link-url:hover' => array(
					'color' => $attr['linkTextHoverColor'],
				),

				' .wpcfp__offer-yes-no-link-icon svg'  => array(
					'width'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['iconSize'], 'px' ),
					'height' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['iconSize'], 'px' ),
					'fill'   => $attr['linkTextColor'],
				),
				' .wpcfp__offer-yes-no-link-url:hover .wpcfp__offer-yes-no-link-icon svg' => array(
					'fill' => $attr['linkTextHoverColor'],
				),

			);

			$margin_type = ( 'after_link_text' === $attr['iconPosition'] ) ? 'margin-left' : 'margin-right';
			$selectors[' .wpcfp__offer-yes-no-link-icon svg'][ $margin_type ] = Cartflows_Pro_Gb_Helper::get_css_value( $attr['iconSpacing'], 'px' );

			$t_selectors        = array(
				' .wpcfp__offer-yes-no-link' => array(
					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['linkTextMarginTopTablet'], $attr['linkTextMarginTypeTablet'] ),
					'margin-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['linkTextMarginRightTablet'], $attr['linkTextMarginTypeTablet'] ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['linkTextMarginBottomTablet'], $attr['linkTextMarginTypeTablet'] ),
					'margin-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['linkTextMarginLeftTablet'], $attr['linkTextMarginTypeTablet'] ),
					'text-align'    => $attr['tlinkTextAlignment'],
				),
				' .wpcfp__offer-yes-no-link .wpcfp__offer-yes-no-link-text' => array(
					'letter-spacing' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['linkTextLetterSpacingTablet'], $attr['linkTextLetterSpacingType'] ),
				),
			);
			$m_selectors        = array(
				' .wpcfp__offer-yes-no-link' => array(
					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['linkTextMarginTopMobile'], $attr['linkTextMarginTypeMobile'] ),
					'margin-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['linkTextMarginRightMobile'], $attr['linkTextMarginTypeMobile'] ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['linkTextMarginBottomMobile'], $attr['linkTextMarginTypeMobile'] ),
					'margin-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['linkTextMarginLeftMobile'], $attr['linkTextMarginTypeMobile'] ),
					'text-align'    => $attr['mlinkTextAlignment'],
				),
				' .wpcfp__offer-yes-no-link .wpcfp__offer-yes-no-link-text' => array(
					'letter-spacing' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['linkTextLetterSpacingMobile'], $attr['linkTextLetterSpacingType'] ),
				),
			);
			$combined_selectors = array(
				'desktop' => $selectors,
				'tablet'  => $t_selectors,
				'mobile'  => $m_selectors,
			);

			$combined_selectors = Cartflows_Pro_Gb_Helper::get_typography_css( $attr, 'linkText', ' .wpcfp__offer-yes-no-link-url .wpcfp__offer-yes-no-link-text-wrap', $combined_selectors );

			return Cartflows_Pro_Gb_Helper::generate_all_css( $combined_selectors, ' .cfp-block-' . $id );
		}

		/**
		 * Get Offer Yes No Button CSS
		 *
		 * @since 1.6.13
		 * @param array  $attr The block attributes.
		 * @param string $id The selector ID.
		 * @return array The Widget List.
		 */
		public static function get_offer_yes_no_button_css( $attr, $id ) {

			$defaults = Cartflows_Pro_Gb_Helper::$block_list['wcfpb/offer-yes-no-button']['attributes'];

			$attr = array_merge( $defaults, (array) $attr );

			$m_selectors = array();
			$t_selectors = array();

			$border_css        = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'btn' );
			$border_css        = Cartflows_Block_Helper::generate_deprecated_border_css(
				$border_css,
				( isset( $attr['borderWidth'] ) ? $attr['borderWidth'] : '' ),
				( isset( $attr['borderRadius'] ) ? $attr['borderRadius'] : '' ),
				( isset( $attr['borderColor'] ) ? $attr['borderColor'] : '' ),
				( isset( $attr['borderStyle'] ) ? $attr['borderStyle'] : '' ),
				( isset( $attr['borderHColor'] ) ? $attr['borderHColor'] : '' )
			);
			$border_css_tablet = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'btn', 'tablet' );
			$border_css_mobile = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'btn', 'mobile' );

			$selectors = array(

				' .wpcfp__offer-yes-no-button-wrap'       => array(
					'text-align' => $attr['align'],
				),
				' .wpcfp__offer-yes-no-button-link:hover' => array(
					'color'        => $attr['textHoverColor'],
					'border-color' => $attr['btnBorderHColor'],
				),
				' .wpcfp__offer-yes-no-button-link .wpcfp__offer-yes-no-button-content-wrap .wpcfp__offer-yes-no-button-title' => array(
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['titleBottomSpacing'], 'px' ),
				),
				' .wpcfp__offer-yes-no-button-icon svg'   => array(
					'width'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['iconSize'], 'px' ),
					'height' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['iconSize'], 'px' ),
					'fill'   => $attr['iconColor'],
				),
				' .wpcfp__offer-yes-no-button-link:hover .wpcfp__offer-yes-no-button-icon svg' => array(
					'fill' => $attr['iconHoverColor'],
				),
				' .wpcfp__offer-yes-no-button-link .wpcfp__offer-yes-no-button-content-wrap .wpcfp__offer-yes-no-button-title-wrap' => array(
					'text-transform' => $attr['titleTransform'],
					'letter-spacing' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['titleLetterSpacing'], $attr['titleLetterSpacingType'] ),
				),
				' .wpcfp__offer-yes-no-button-link .wpcfp__offer-yes-no-button-content-wrap .wpcfp__offer-yes-no-button-sub-title' => array(
					'text-transform' => $attr['subTitleTransform'],
					'letter-spacing' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['subTitleLetterSpacing'], $attr['subTitleLetterSpacingType'] ),
				),
			);
			if ( 'full' === $attr['align'] ) {
				$selectors[' a.wpcfp__offer-yes-no-button-link']['width']           = '100%';
				$selectors[' a.wpcfp__offer-yes-no-button-link']['justify-content'] = 'center';
			}

			$selectors[' .wpcfp__offer-yes-no-button-link'] = array();

			if ( 'gradient' == $attr['backgroundType'] ) {
				$selectors[' .wpcfp__offer-yes-no-button-link'] = array_merge(
					$border_css,
					array(
						'color'          => $attr['textColor'],
						'text-align'     => $attr['textAlignment'],
						'padding-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnTop'], $attr['paddingTypeDesktop'] ),
						'padding-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnBottom'], $attr['paddingTypeDesktop'] ),
						'padding-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnLeft'], $attr['paddingTypeDesktop'] ),
						'padding-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnRight'], $attr['paddingTypeDesktop'] ),
					)
				);
			}

			$position = str_replace( '-', ' ', $attr['backgroundPosition'] );

			if ( 'image' == $attr['backgroundType'] ) {
				$selectors[' .wpcfp__offer-yes-no-button-link'] = array_merge(
					$border_css,
					array(
						'opacity'               => ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : 0,
						'background-color'      => $attr['backgroundImageColor'],
						'color'                 => $attr['textColor'],
						'background-image'      => ( isset( $attr['backgroundImage'] ) && isset( $attr['backgroundImage']['url'] ) ) ? "url('" . $attr['backgroundImage']['url'] . "' )" : null,
						'background-position'   => $position,
						'background-attachment' => $attr['backgroundAttachment'],
						'background-repeat'     => $attr['backgroundRepeat'],
						'background-size'       => $attr['backgroundSize'],
						'text-align'            => $attr['textAlignment'],
						'padding-top'           => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnTop'], $attr['paddingTypeDesktop'] ),
						'padding-bottom'        => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnBottom'], $attr['paddingTypeDesktop'] ),
						'padding-left'          => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnLeft'], $attr['paddingTypeDesktop'] ),
						'padding-right'         => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnRight'], $attr['paddingTypeDesktop'] ),
					)
				);
			} elseif ( 'color' == $attr['backgroundType'] ) {
				$selectors[' .wpcfp__offer-yes-no-button-link']                           = array_merge(
					$border_css,
					array(
						'opacity'          => ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : 0,
						'background-color' => $attr['backgroundColor'],
						'color'            => $attr['textColor'],
						'text-align'       => $attr['textAlignment'],
						'padding-top'      => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnTop'], $attr['paddingTypeDesktop'] ),
						'padding-bottom'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnBottom'], $attr['paddingTypeDesktop'] ),
						'padding-left'     => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnLeft'], $attr['paddingTypeDesktop'] ),
						'padding-right'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnRight'], $attr['paddingTypeDesktop'] ),
					)
				);
				$selectors[' .wpcfp__offer-yes-no-button-link:hover']['background-color'] = $attr['buttonHoverColor'];
			} elseif ( 'gradient' === $attr['backgroundType'] ) {

				$selectors[' .wpcfp__offer-yes-no-button-link']['background-color'] = 'transparent';
				$selectors[' .wpcfp__offer-yes-no-button-link']['opacity']          = ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : 0;
				if ( $attr['gradientValue'] ) {
					$selectors[' .wpcfp__offer-yes-no-button-link']['background-image'] = $attr['gradientValue'];

				} else {
					if ( 'linear' === $attr['gradientType'] ) {

						$selectors[' .wpcfp__offer-yes-no-button-link']['background-image'] = "linear-gradient({${ $attr['gradientAngle'] }}deg, {${ $attr['gradientColor1'] }} {${ $attr['gradientLocation1'] }}%, {${ $attr['gradientColor2'] }} {${ $attr['gradientLocation2'] }}%)";
					} else {

						$selectors[' .wpcfp__offer-yes-no-button-link']['background-image'] = "radial-gradient( at {${ $attr['gradientPosition'] }}, {${ $attr['gradientColor1'] }} {${ $attr['gradientLocation1'] }}%, {${ $attr['gradientColor2'] }} {${ $attr['gradientLocation2'] }}%)";
					}
				}
			}

			$margin_type = ( 'after_title' === $attr['iconPosition'] || 'after_title_sub_title' === $attr['iconPosition'] ) ? 'margin-left' : 'margin-right';

			$selectors[' .wpcfp__offer-yes-no-button-icon svg'][ $margin_type ] = Cartflows_Pro_Gb_Helper::get_css_value( $attr['iconSpacing'], 'px' );

			$t_selectors = array(
				' .wpcfp__offer-yes-no-button-wrap' => array(
					'text-align' => $attr['talign'],
				),
				' .wpcfp__offer-yes-no-button-link' => array_merge(
					$border_css_tablet,
					array(
						'padding-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnTopTablet'], $attr['paddingTypeTablet'] ),
						'padding-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnBottomTablet'], $attr['paddingTypeTablet'] ),
						'padding-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnLeftTablet'], $attr['paddingTypeTablet'] ),
						'padding-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnRightTablet'], $attr['paddingTypeTablet'] ),
					)
				),
				' .wpcfp__offer-yes-no-button-link .wpcfp__offer-yes-no-button-content-wrap .wpcfp__offer-yes-no-button-title-wrap' => array(
					'text-transform' => $attr['titleTransform'],
					'letter-spacing' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['titleLetterSpacingTablet'], $attr['titleLetterSpacingType'] ),
				),
				' .wpcfp__offer-yes-no-button-link .wpcfp__offer-yes-no-button-content-wrap .wpcfp__offer-yes-no-button-sub-title' => array(
					'text-transform' => $attr['titleTransform'],
					'letter-spacing' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['titleLetterSpacingTablet'], $attr['titleLetterSpacingType'] ),
				),

			);

			$m_selectors = array(
				' .wpcfp__offer-yes-no-button-wrap' => array(
					'text-align' => $attr['malign'],
				),
				' .wpcfp__offer-yes-no-button-link' => array_merge(
					$border_css_mobile,
					array(
						'padding-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnTopMobile'], $attr['paddingTypeMobile'] ),
						'padding-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnBottomMobile'], $attr['paddingTypeMobile'] ),
						'padding-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnLeftMobile'], $attr['paddingTypeMobile'] ),
						'padding-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBtnRightMobile'], $attr['paddingTypeMobile'] ),
					)
				),
				' .wpcfp__offer-yes-no-button-link .wpcfp__offer-yes-no-button-content-wrap .wpcfp__offer-yes-no-button-title-wrap' => array(
					'text-transform' => $attr['titleTransform'],
					'letter-spacing' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['titleLetterSpacingMobile'], $attr['titleLetterSpacingType'] ),
				),
				' .wpcfp__offer-yes-no-button-link .wpcfp__offer-yes-no-button-content-wrap .wpcfp__offer-yes-no-button-sub-title' => array(
					'text-transform' => $attr['titleTransform'],
					'letter-spacing' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['titleLetterSpacingMobile'], $attr['titleLetterSpacingType'] ),
				),
			);

			$combined_selectors = array(
				'desktop' => $selectors,
				'tablet'  => $t_selectors,
				'mobile'  => $m_selectors,
			);

			$combined_selectors = Cartflows_Pro_Gb_Helper::get_typography_css( $attr, 'title', ' .wpcfp__offer-yes-no-button-link .wpcfp__offer-yes-no-button-content-wrap .wpcfp__offer-yes-no-button-title-wrap', $combined_selectors );
			$combined_selectors = Cartflows_Pro_Gb_Helper::get_typography_css( $attr, 'subTitle', ' .wpcfp__offer-yes-no-button-link .wpcfp__offer-yes-no-button-content-wrap .wpcfp__offer-yes-no-button-sub-title', $combined_selectors );

			return Cartflows_Pro_Gb_Helper::generate_all_css( $combined_selectors, ' .cfp-block-' . $id );
		}

		/**
		 * Get Offer Product Title CSS
		 *
		 * @since 1.6.13
		 * @param array  $attr The block attributes.
		 * @param string $id The selector ID.
		 * @return array The Widget List.
		 */
		public static function get_offer_product_title_css( $attr, $id ) {

			$defaults = Cartflows_Pro_Gb_Helper::$block_list['wcfpb/offer-product-title']['attributes'];

			$attr = array_merge( $defaults, (array) $attr );

			$selectors = array(

				' .wpcfp__offer-product-title'       => array(
					'text-align'     => $attr['textAlignment'],
					'color'          => $attr['textColor'],

					'text-shadow'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowHOffset'], 'px' ) . ' ' . Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowVOffset'], 'px' ) . ' ' . Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowBlur'], 'px' ) . ' ' . $attr['textShadowColor'],

					'margin-top'     => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginTop'], $attr['textMarginTypeDesktop'] ),
					'margin-right'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginRight'], $attr['textMarginTypeDesktop'] ),
					'margin-bottom'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginBottom'], $attr['textMarginTypeDesktop'] ),
					'margin-left'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginLeft'], $attr['textMarginTypeDesktop'] ),
					'text-transform' => $attr['textTransform'],
					'letter-spacing' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textLetterSpacing'], $attr['textLetterSpacingType'] ),
				),
				' .wpcfp__offer-product-title:hover' => array(
					'color' => $attr['textHoverColor'],
				),

			);

			$t_selectors = array(
				' .wpcfp__offer-product-title' => array(
					'margin-top'     => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginTopTablet'], $attr['textMarginTypeTablet'] ),
					'margin-right'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginRightTablet'], $attr['textMarginTypeTablet'] ),
					'margin-bottom'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginBottomTablet'], $attr['textMarginTypeTablet'] ),
					'margin-left'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginLeftTablet'], $attr['textMarginTypeTablet'] ),
					'text-align'     => $attr['ttextAlignment'],
					'letter-spacing' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textLetterSpacingTablet'], $attr['textLetterSpacingType'] ),
				),
			);
			$m_selectors = array(
				' .wpcfp__offer-product-title' => array(
					'margin-top'     => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginTopMobile'], $attr['textMarginTypeMobile'] ),
					'margin-right'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginRightMobile'], $attr['textMarginTypeMobile'] ),
					'margin-bottom'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginBottomMobile'], $attr['textMarginTypeMobile'] ),
					'margin-left'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginLeftMobile'], $attr['textMarginTypeMobile'] ),
					'text-align'     => $attr['mtextAlignment'],
					'letter-spacing' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textLetterSpacingMobile'], $attr['textLetterSpacingType'] ),
				),
			);

			$combined_selectors = array(
				'desktop' => $selectors,
				'tablet'  => $t_selectors,
				'mobile'  => $m_selectors,
			);

			$combined_selectors = Cartflows_Pro_Gb_Helper::get_typography_css( $attr, 'text', ' .wpcfp__offer-product-title', $combined_selectors );

			return Cartflows_Pro_Gb_Helper::generate_all_css( $combined_selectors, ' .cfp-block-' . $id );

		}

		/**
		 * Get Offer Product Description CSS
		 *
		 * @since 1.6.13
		 * @param array  $attr The block attributes.
		 * @param string $id The selector ID.
		 * @return array The Widget List.
		 */
		public static function get_offer_product_description_css( $attr, $id ) {

			$defaults = Cartflows_Pro_Gb_Helper::$block_list['wcfpb/offer-product-description']['attributes'];

			$attr = array_merge( $defaults, (array) $attr );

			$m_selectors = array();
			$t_selectors = array();

			$selectors = array(

				' .wpcfp__offer-product-description'       => array(
					'text-align'     => $attr['textAlignment'],
					'color'          => $attr['textColor'],
					'padding-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingTop'], $attr['paddingTypeDesktop'] ),
					'padding-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBottom'], $attr['paddingTypeDesktop'] ),
					'padding-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingLeft'], $attr['paddingTypeDesktop'] ),
					'padding-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingRight'], $attr['paddingTypeDesktop'] ),
					'text-shadow'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowHOffset'], 'px' ) . ' ' . Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowVOffset'], 'px' ) . ' ' . Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowBlur'], 'px' ) . ' ' . $attr['textShadowColor'],
					'text-transform' => $attr['textTransform'],
					'letter-spacing' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textLetterSpacing'], $attr['textLetterSpacingType'] ),
				),

				' .wpcfp__offer-product-description:hover' => array(
					'color' => $attr['textHoverColor'],
				),

			);

			$t_selectors = array(
				' .wpcfp__offer-product-description' => array(
					'padding-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingTopTablet'], $attr['paddingTypeTablet'] ),
					'padding-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBottomTablet'], $attr['paddingTypeTablet'] ),
					'padding-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingLeftTablet'], $attr['paddingTypeTablet'] ),
					'padding-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingRightTablet'], $attr['paddingTypeTablet'] ),
					'letter-spacing' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textLetterSpacingTablet'], $attr['textLetterSpacingType'] ),
					'text-align'     => $attr['ttextAlignment'],
				),
			);

			$m_selectors = array(
				' .wpcfp__offer-product-description' => array(
					'padding-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingTopMobile'], $attr['paddingTypeMobile'] ),
					'padding-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingBottomMobile'], $attr['paddingTypeMobile'] ),
					'padding-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingLeftMobile'], $attr['paddingTypeMobile'] ),
					'padding-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['paddingRightMobile'], $attr['paddingTypeMobile'] ),
					'text-align'     => $attr['mtextAlignment'],
					'letter-spacing' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textLetterSpacingMobile'], $attr['textLetterSpacingType'] ),
				),
			);

			$combined_selectors = array(
				'desktop' => $selectors,
				'tablet'  => $t_selectors,
				'mobile'  => $m_selectors,
			);

			$combined_selectors = Cartflows_Pro_Gb_Helper::get_typography_css( $attr, 'text', ' .wpcfp__offer-product-description', $combined_selectors );

			return Cartflows_Pro_Gb_Helper::generate_all_css( $combined_selectors, ' .cfp-block-' . $id );
		}

		/**
		 * Get Offer Product Price CSS
		 *
		 * @since 1.6.13
		 * @param array  $attr The block attributes.
		 * @param string $id The selector ID.
		 * @return array The Widget List.
		 */
		public static function get_offer_product_price_css( $attr, $id ) {

			$defaults = Cartflows_Pro_Gb_Helper::$block_list['wcfpb/offer-product-price']['attributes'];

			$attr = array_merge( $defaults, (array) $attr );

			$m_selectors = array();
			$t_selectors = array();

			$selectors = array(

				' .wpcfp__offer-product-price'       => array(
					'text-align'    => $attr['textAlignment'],
					'color'         => $attr['textColor'],

					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginTop'], $attr['textMarginTypeDesktop'] ),
					'margin-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginRight'], $attr['textMarginTypeDesktop'] ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginBottom'], $attr['textMarginTypeDesktop'] ),
					'margin-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginLeft'], $attr['textMarginTypeDesktop'] ),
					'text-shadow'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowHOffset'], 'px' ) . ' ' . Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowVOffset'], 'px' ) . ' ' . Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowBlur'], 'px' ) . ' ' . $attr['textShadowColor'],
				),
				' .wpcfp__offer-product-price:hover' => array(
					'color' => $attr['textHoverColor'],
				),

			);

			$t_selectors = array(
				' .wpcfp__offer-product-price' => array(
					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginTopTablet'], $attr['textMarginTypeTablet'] ),
					'margin-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginRightTablet'], $attr['textMarginTypeTablet'] ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginBottomTablet'], $attr['textMarginTypeTablet'] ),
					'margin-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginLeftTablet'], $attr['textMarginTypeTablet'] ),
					'text-align'    => $attr['ttextAlignment'],
				),
			);
			$m_selectors = array(
				' .wpcfp__offer-product-price' => array(
					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginTopMobile'], $attr['textMarginTypeMobile'] ),
					'margin-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginRightMobile'], $attr['textMarginTypeMobile'] ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginBottomMobile'], $attr['textMarginTypeMobile'] ),
					'margin-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginLeftMobile'], $attr['textMarginTypeMobile'] ),
					'text-align'    => $attr['mtextAlignment'],
				),
			);

			$combined_selectors = array(
				'desktop' => $selectors,
				'tablet'  => $t_selectors,
				'mobile'  => $m_selectors,
			);

			$combined_selectors = Cartflows_Pro_Gb_Helper::get_typography_css( $attr, 'text', ' .wpcfp__offer-product-price .wcf-offer-price', $combined_selectors );

			return Cartflows_Pro_Gb_Helper::generate_all_css( $combined_selectors, ' .cfp-block-' . $id );
		}

		/**
		 * Get Offer Product Quantity CSS
		 *
		 * @since 1.6.13
		 * @param array  $attr The block attributes.
		 * @param string $id The selector ID.
		 * @return array The Widget List.
		 */
		public static function get_offer_product_quantity_css( $attr, $id ) {

			$defaults = Cartflows_Pro_Gb_Helper::$block_list['wcfpb/offer-product-quantity']['attributes'];

			$attr = array_merge( $defaults, (array) $attr );

			$m_selectors = array();
			$t_selectors = array();

			$border_css        = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'content' );
			$border_css        = Cartflows_Block_Helper::generate_deprecated_border_css(
				$border_css,
				( isset( $attr['borderWidth'] ) ? $attr['borderWidth'] : '' ),
				( isset( $attr['borderRadius'] ) ? $attr['borderRadius'] : '' ),
				( isset( $attr['borderColor'] ) ? $attr['borderColor'] : '' ),
				( isset( $attr['borderStyle'] ) ? $attr['borderStyle'] : '' ),
				( isset( $attr['borderHColor'] ) ? $attr['borderHColor'] : '' )
			);
			$border_css_tablet = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'content', 'tablet' );
			$border_css_mobile = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'content', 'mobile' );

			$selectors = array(

				' .wpcfp__offer-product-quantity .quantity' => array(
					'max-width' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['width'], '%' ),
				),
				' .wpcfp__offer-product-quantity .quantity .screen-reader-text, .wpcfp__offer-product-quantity .quantity .input-text.qty.text' => array(
					'text-shadow' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowHOffset'], 'px' ) . ' ' . Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowVOffset'], 'px' ) . ' ' . Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowBlur'], 'px' ) . ' ' . $attr['textShadowColor'],
				),
				' .wpcfp__offer-product-quantity .quantity .screen-reader-text' => array(
					'color' => $attr['labelColor'],
				),
				' .wpcfp__offer-product-quantity .quantity .input-text.qty.text' => array_merge(
					$border_css,
					array(
						'color'            => $attr['inputTextColor'],
						'background-color' => $attr['backgroundColor'],

						'margin-top'       => Cartflows_Pro_Gb_Helper::get_css_value( $attr['label_bottom_spacing'], 'px' ),
					)
				),
				' .wpcfp__offer-product-quantity .quantity .input-text.qty.text:hover' => array(
					'border-color' => $attr['contentBorderHColor'],
				),
				' .wpcfp__offer-product-quantity' => array(

					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginTop'], $attr['textMarginTypeDesktop'] ),
					'margin-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginRight'], $attr['textMarginTypeDesktop'] ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginBottom'], $attr['textMarginTypeDesktop'] ),
					'margin-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginLeft'], $attr['textMarginTypeDesktop'] ),
				),

			);

			if ( 'left' === $attr['alignment'] ) {
				$selectors[' .wpcfp__offer-product-quantity .quantity']['margin-right'] = 'auto';
			} elseif ( 'right' === $attr['alignment'] ) {
				$selectors[' .wpcfp__offer-product-quantity .quantity']['margin-left'] = 'auto';
			} else {
				$selectors[' .wpcfp__offer-product-quantity .quantity']['margin-right'] = 'auto';
				$selectors[' .wpcfp__offer-product-quantity .quantity']['margin-left']  = 'auto';
			}

			$t_selectors = array(
				' .wpcfp__offer-product-quantity .quantity .input-text.qty.text' => $border_css_tablet,
				' .wpcfp__offer-product-quantity' => array(

					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginTopTablet'], $attr['textMarginTypeTablet'] ),
					'margin-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginRightTablet'], $attr['textMarginTypeTablet'] ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginBottomTablet'], $attr['textMarginTypeTablet'] ),
					'margin-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginLeftTablet'], $attr['textMarginTypeTablet'] ),
				),
			);
			$m_selectors = array(
				' .wpcfp__offer-product-quantity .quantity .input-text.qty.text' => $border_css_mobile,
				' .wpcfp__offer-product-quantity' => array(

					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginTopMobile'], $attr['textMarginTypeMobile'] ),
					'margin-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginRightMobile'], $attr['textMarginTypeMobile'] ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginBottomMobile'], $attr['textMarginTypeMobile'] ),
					'margin-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginLeftMobile'], $attr['textMarginTypeMobile'] ),
				),
			);

			$combined_selectors = array(
				'desktop' => $selectors,
				'tablet'  => $t_selectors,
				'mobile'  => $m_selectors,
			);

			$combined_selectors = Cartflows_Pro_Gb_Helper::get_typography_css( $attr, 'text', ' .wpcfp__offer-product-quantity .quantity .screen-reader-text, .wpcfp__offer-product-quantity .quantity .input-text.qty.text', $combined_selectors );

			return Cartflows_Pro_Gb_Helper::generate_all_css( $combined_selectors, ' .cfp-block-' . $id );
		}

		/**
		 * Get Offer Product Variation CSS
		 *
		 * @since 1.6.13
		 * @param array  $attr The block attributes.
		 * @param string $id The selector ID.
		 * @return array The Widget List.
		 */
		public static function get_offer_product_variation_css( $attr, $id ) {

			$defaults = Cartflows_Pro_Gb_Helper::$block_list['wcfpb/offer-product-variation']['attributes'];

			$attr = array_merge( $defaults, (array) $attr );

			$m_selectors = array();
			$t_selectors = array();

			$selectors = array(

				' .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations' => array(
					'max-width' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['width'], '%' ),
				),
				' .wpcfp__offer-product-variation, .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations .value select, .wpcfp__offer-product-variation .label label' => array(
					'text-shadow' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowHOffset'], 'px' ) . ' ' . Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowVOffset'], 'px' ) . ' ' . Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowBlur'], 'px' ) . ' ' . $attr['textShadowColor'],
				),
				' .wpcfp__offer-product-variation label' => array(
					'color' => $attr['labelColor'],
				),
				' .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations .value select' => array(
					'color' => $attr['inputTextColor'],
				),
				' .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations td.value' => array(
					'margin-top' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['label_bottom_spacing'], 'px' ),
				),
				' .wpcfp__offer-product-variation'       => array(

					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginTop'], $attr['textMarginTypeDesktop'] ),
					'margin-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginRight'], $attr['textMarginTypeDesktop'] ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginBottom'], $attr['textMarginTypeDesktop'] ),
					'margin-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginLeft'], $attr['textMarginTypeDesktop'] ),
				),

			);

			if ( 'left' === $attr['alignment'] ) {
				$selectors[' .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations']['margin-right'] = 'auto';
			} elseif ( 'right' === $attr['alignment'] ) {
				$selectors[' .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations']['margin-left'] = 'auto';
			} else {
				$selectors[' .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations']['margin-right'] = 'auto';
				$selectors[' .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations']['margin-left']  = 'auto';
			}

			$t_selectors = array(
				' .wpcfp__offer-product-variation' => array(
					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginTopTablet'], $attr['textMarginTypeTablet'] ),
					'margin-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginRightTablet'], $attr['textMarginTypeTablet'] ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginBottomTablet'], $attr['textMarginTypeTablet'] ),
					'margin-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginLeftTablet'], $attr['textMarginTypeTablet'] ),
				),
			);
			$m_selectors = array(
				' .wpcfp__offer-product-variation' => array(
					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginTopMobile'], $attr['textMarginTypeMobile'] ),
					'margin-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginRightMobile'], $attr['textMarginTypeMobile'] ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginBottomMobile'], $attr['textMarginTypeMobile'] ),
					'margin-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textMarginLeftMobile'], $attr['textMarginTypeMobile'] ),
				),
			);

			$combined_selectors = array(
				'desktop' => $selectors,
				'tablet'  => $t_selectors,
				'mobile'  => $m_selectors,
			);

			$combined_selectors = Cartflows_Pro_Gb_Helper::get_typography_css( $attr, 'text', ' .wpcfp__offer-product-variation, .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations .value select, .wpcfp__offer-product-variation .label label', $combined_selectors );

			return Cartflows_Pro_Gb_Helper::generate_all_css( $combined_selectors, ' .cfp-block-' . $id );
		}

		/**
		 * Get Offer Product Image CSS
		 *
		 * @since 1.6.13
		 * @param array  $attr The block attributes.
		 * @param string $id The selector ID.
		 * @return array The Widget List.
		 */
		public static function get_offer_product_image_css( $attr, $id ) {

			$defaults = Cartflows_Pro_Gb_Helper::$block_list['wcfpb/offer-product-image']['attributes'];

			$attr = array_merge( $defaults, (array) $attr );

			$m_selectors = array();
			$t_selectors = array();

			$imageborder_css = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'image' );

			$imageborder_css_tablet = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'image', 'tablet' );
			$imageborder_css_mobile = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'image', 'mobile' );

			$thumbnailborder_css        = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'thumbnail' );
			$thumbnailborder_css_tablet = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'thumbnail', 'tablet' );
			$thumbnailborder_css_mobile = Cartflows_Pro_Gb_Helper::generate_border_css( $attr, 'thumbnail', 'mobile' );

			$selectors = array(

				' .woocommerce-product-gallery .woocommerce-product-gallery__image' => array(
					'text-align' => $attr['alignment'],
				),
				' .woocommerce-product-gallery .woocommerce-product-gallery__wrapper' => array(
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['image_bottom_spacing'], 'px' ),
				),
				' .woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image img' => $imageborder_css,
				' .woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image img:hover' => array(
					'border-color' => $attr['imageBorderHColor'],
				),
				' .woocommerce-product-gallery ol li:not(:last-child)' => array(
					'margin-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['spacing_between_thumbnails'], 'px' ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['spacing_between_thumbnails'], 'px' ),
				),
				' .woocommerce-product-gallery ol li img' => $thumbnailborder_css,
				' .woocommerce-product-gallery ol li img:hover' => array(
					'border-color' => $attr['thumbnailBorderHColor'],
				),
				' .wpcfp__offer-product-image'            => array(
					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['imageMarginTop'], $attr['imageMarginTypeDesktop'] ),
					'margin-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['imageMarginRight'], $attr['imageMarginTypeDesktop'] ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['imageMarginBottom'], $attr['imageMarginTypeDesktop'] ),
					'margin-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['imageMarginLeft'], $attr['imageMarginTypeDesktop'] ),
				),

			);

			$t_selectors = array(
				' .woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image img' => $imageborder_css_tablet,
				' .woocommerce-product-gallery ol li img' => $thumbnailborder_css_tablet,
				' .wpcfp__offer-product-image'            => array(
					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['imageMarginTopTablet'], $attr['imageMarginTypeTablet'] ),
					'margin-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['imageMarginRightTablet'], $attr['imageMarginTypeTablet'] ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['imageMarginBottomTablet'], $attr['imageMarginTypeTablet'] ),
					'margin-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['imageMarginLeftTablet'], $attr['imageMarginTypeTablet'] ),
				),
			);

			$m_selectors = array(
				' .woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image img' => $imageborder_css_mobile,
				' .woocommerce-product-gallery ol li img' => $thumbnailborder_css_mobile,
				' .wpcfp__offer-product-image'            => array(
					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['imageMarginTopMobile'], $attr['imageMarginTypeMobile'] ),
					'margin-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['imageMarginRightMobile'], $attr['imageMarginTypeMobile'] ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['imageMarginBottomMobile'], $attr['imageMarginTypeMobile'] ),
					'margin-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['imageMarginLeftMobile'], $attr['imageMarginTypeMobile'] ),
				),
			);

			$combined_selectors = array(
				'desktop' => $selectors,
				'tablet'  => $t_selectors,
				'mobile'  => $m_selectors,
			);

			return Cartflows_Pro_Gb_Helper::generate_all_css( $combined_selectors, ' .cfp-block-' . $id );
		}

	}
}
