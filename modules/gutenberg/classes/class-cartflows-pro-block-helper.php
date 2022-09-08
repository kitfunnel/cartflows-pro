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

			$attr = array_merge( $defaults, (array) $attr );

			$product_text_color    = ( isset( $attr['productTextColor'] ) ? $attr['productTextColor'] : '' );
			$product_text_bg_color = ( isset( $attr['productTextBgColor'] ) ? $attr['productTextBgColor'] : '' );
			$product_border_style  = ( isset( $attr['productBorderStyle'] ) ? $attr['productBorderStyle'] : '' );
			$product_border_width  = ( isset( $attr['productBorderWidth'] ) ? $attr['productBorderWidth'] : '' );
			$product_border_color  = ( isset( $attr['productBorderColor'] ) ? $attr['productBorderColor'] : '' );
			$product_border_radius = ( isset( $attr['productBorderRadius'] ) ? $attr['productBorderRadius'] : '' );

			$product_title_text_color = ( isset( $attr['productTitleTextColor'] ) ? $attr['productTitleTextColor'] : '' );

			$product_highlight_bg_color        = ( isset( $attr['productHighlightBgColor'] ) ? $attr['productHighlightBgColor'] : '' );
			$product_highlight_text_color      = ( isset( $attr['productHighlightTextColor'] ) ? $attr['productHighlightTextColor'] : '' );
			$product_highlight_border_style    = ( isset( $attr['productHighlightBorderstyle'] ) ? $attr['productHighlightBorderstyle'] : '' );
			$product_highlight_border_width    = ( isset( $attr['productHighlightBorderWidth'] ) ? $attr['productHighlightBorderWidth'] . 'px' : '' );
			$product_highlight_border_radious  = ( isset( $attr['productHighlightBorderRadius'] ) ? $attr['productHighlightBorderRadius'] . 'px' : '' );
			$product_highlight_border_color    = ( isset( $attr['productHighlightBorderColor'] ) ? $attr['productHighlightBorderColor'] : '' );
			$product_highlight_flat_text_color = ( isset( $attr['productHighlightFlagTextColor'] ) ? $attr['productHighlightFlagTextColor'] : '' );
			$product_highlight_flat_bg_color   = ( isset( $attr['productHighlightFlagBgColor'] ) ? $attr['productHighlightFlagBgColor'] : '' );

			$orderbump_text_bg_color = ( isset( $attr['orderbumpTextBgColor'] ) ? $attr['orderbumpTextBgColor'] : '' );
			$orderbump_border_radius = ( isset( $attr['orderbumpBorderRadius'] ) ? $attr['orderbumpBorderRadius'] : '' );
			$orderbump_border_color  = ( isset( $attr['orderbumpBorderColor'] ) ? $attr['orderbumpBorderColor'] : '' );
			$orderbump_border_width  = ( isset( $attr['orderbumpBorderWidth'] ) ? $attr['orderbumpBorderWidth'] : '' );
			$orderbump_border_style  = ( isset( $attr['orderbumpBorderStyle'] ) ? $attr['orderbumpBorderStyle'] : '' );

			$orderbump_text_color        = ( isset( $attr['orderbumpTextColor'] ) ? $attr['orderbumpTextColor'] : '' );
			$orderbumpcontent_bg_color   = ( isset( $attr['orderbumpcontentBgColor'] ) ? $attr['orderbumpcontentBgColor'] : '' );
			$orderbumpcontent_text_color = ( isset( $attr['orderbumpcontentTextColor'] ) ? $attr['orderbumpcontentTextColor'] : '' );

			$description_color        = ( isset( $attr['DescriptionColor'] ) ? $attr['DescriptionColor'] : '' );
			$title_color              = ( isset( $attr['TitleColor'] ) ? $attr['TitleColor'] : '' );
			$subtitle_color           = ( isset( $attr['SubtitleColor'] ) ? $attr['SubtitleColor'] : '' );
			$modal_background_color   = ( isset( $attr['ModalBackgroundColor'] ) ? $attr['ModalBackgroundColor'] : '' );
			$overlay_background_color = ( isset( $attr['OverlayBackgroundColor'] ) ? $attr['OverlayBackgroundColor'] : '' );

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

			$pre_checkout_selectors = array(
				'.wcf-pre-checkout-offer-wrapper .wcf-pre-checkout-offer-price, body .wcf-progress-bar-nav, .wcf-pre-checkout-skip-btn .wcf-pre-checkout-skip' => array(
					'color' => $description_color . ' !important',
				),
				' .wcf-pre-checkout-offer-wrapper .wcf-lightbox-content .wcf-content-main-head .wcf-content-modal-title h1, .wcf-lightbox-content #wcf-pre-checkout-offer-content .wcf-pre-checkout-info .wcf-pre-checkout-offer-product-title h1, .wcf-content-main-head .wcf-content-modal-title .wcf_first_name' => array(
					'color' => $title_color . ' !important',
				),
				' .wcf-pre-checkout-offer-wrapper .wcf-lightbox-content .wcf-content-main-head .wcf-content-modal-sub-title span, .wcf-content-modal-sub-title span' => array(
					'color' => $subtitle_color . ' !important',
				),
				' .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-modal'   => array(
					'background-color' => $modal_background_color,
				),
				' .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content' => array(
					'background-color' => $modal_background_color,
				),
				' .wcf-pre-checkout-offer-wrapper.wcf-pre-checkout-full-width'    => array(
					'background-color' => $overlay_background_color,
				),
			);
			$t_selectors            = array();
			$m_selectors            = array();
			$selectors              = array(

				' .cf-block-' . $id . ' .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .wcf-qty-options .wcf-qty-header .wcf-field-label, .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout .wcf-qty-options .wcf-qty-row' => array(
					'color' => $product_text_color,
				),
				' .cf-block-' . $id . ' .wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options, .cf-block-' . $id . ' .wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row' => array(
					'background-color' => $product_text_bg_color,
					'border-style'     => $product_border_style,
					'border-width'     => Cartflows_Pro_Gb_Helper::get_css_value( $product_border_width, 'px' ),
					'border-color'     => $product_border_color,
					'border-radius'    => Cartflows_Pro_Gb_Helper::get_css_value( $product_border_radius, 'px' ),
				),
				' .cf-block-' . $id . ' .wcf-product-option-wrap #your_products_heading' => array(
					'color' => $product_title_text_color,
				),
				' .cf-block-' . $id . ' .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight' => array(
					'background-color' => $product_highlight_bg_color,
					'color'            => $product_highlight_text_color,
					'border-style'     => $product_highlight_border_style,
					'border-width'     => $product_highlight_border_width,
					'border-radius'    => $product_highlight_border_radious,
					'border-color'     => $product_highlight_border_color,
				),
				'.cf-block-' . $id . ' .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight .wcf-highlight-head' => array(
					'color'            => $product_highlight_flat_text_color,
					'background-color' => $product_highlight_flat_bg_color,
				),

				/*
				' .cf-block-' . $id . ' .wcf-bump-order-wrap'    => array(
				// 'background-color' => $orderbump_text_bg_color,
				// 'border-style'     => $orderbump_border_style,
				// 'border-radius'    => Cartflows_Pro_Gb_Helper::get_css_value( $orderbump_border_radius, 'px' ),
				// 'border-width'     => Cartflows_Pro_Gb_Helper::get_css_value( $orderbump_border_width, 'px' ),
				// 'border-color'     => $orderbump_border_color,
				// ),
				// ' .cf-block-' . $id . ' .wcf-bump-order-wrap .wcf-bump-order-field-wrap, .wcf-bump-order-wrap' => array(
				// 'border-width' => Cartflows_Pro_Gb_Helper::get_css_value( $orderbump_border_width, 'px' ),
				// 'border-color' => $orderbump_border_color,
				// ),
				// ' .cf-block-' . $id . ' .wcf-bump-order-style-2 .wcf-bump-order-field-wrap, .wcf-bump-order-style-1 .wcf-content-container' => array(
				// 'border-top-style' => $orderbump_border_style,
				// 'border-width'     => Cartflows_Pro_Gb_Helper::get_css_value( 1, 'px' ),
				// 'border-color'     => $orderbump_border_color,
				// ),
				// ' .cf-block-' . $id . ' .wcf-bump-order-wrap .wcf-bump-order-field-wrap, #payment .wcf-bump-order-wrap .wcf-bump-order-field-wrap .wcf-bump-order-label' => array(
				// 'background-color' => $orderbump_text_bg_color,
				// 'color'            => $orderbump_text_color,
				// ),
				// ' .cf-block-' . $id . ' .wcf-bump-order-content' => array(
				// 'color'            => $orderbumpcontent_text_color,
				// 'background-color' => $orderbumpcontent_bg_color,
				// ),
				*/
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

			$m_selectors[' .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note'] = array(
				'font-size'   => Cartflows_Pro_Gb_Helper::get_css_value( $tstext_font_size_mobile, $tstext_font_size_type ),
				'line-height' => Cartflows_Pro_Gb_Helper::get_css_value( $tstext_line_height_mobile, $tstext_line_height_type ),
			);

			$t_selectors[' .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note'] = array(
				'font-size'   => Cartflows_Pro_Gb_Helper::get_css_value( $tstext_font_size_tablet, $tstext_font_size_type ),
				'line-height' => Cartflows_Pro_Gb_Helper::get_css_value( $tstext_line_height_tablet, $tstext_line_height_type ),
			);

			$combined_selectors = array(
				'desktop' => $selectors + $pre_checkout_selectors,
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
					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['topMargin'], 'px' ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['bottomMargin'], 'px' ),
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

			$selectors = array(

				' .wpcfp__offer-yes-no-button-wrap'       => array(
					'text-align' => $attr['align'],
				),
				' .wpcfp__offer-yes-no-button-link:hover' => array(
					'color'        => $attr['textHoverColor'],
					'border-color' => $attr['borderHoverColor'],
				),
				' .wpcfp__offer-yes-no-button-link .wpcfp__offer-yes-no-button-content-wrap .wpcfp__offer-yes-no-button-sub-title' => array(
					'margin-top' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['titleBottomSpacing'], 'px' ),
				),
				' .wpcfp__offer-yes-no-button-icon svg'   => array(
					'width'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['iconSize'], 'px' ),
					'height' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['iconSize'], 'px' ),
					'fill'   => $attr['iconColor'],
				),
				' .wpcfp__offer-yes-no-button-link:hover .wpcfp__offer-yes-no-button-icon svg' => array(
					'fill' => $attr['iconHoverColor'],
				),
			);
			if ( 'full' === $attr['align'] ) {
				$selectors[' a.wpcfp__offer-yes-no-button-link'] = array(
					'width'           => '100%',
					'justify-content' => 'center',
				);
			}

			$selectors[' .wpcfp__offer-yes-no-button-link'] = array();

			if ( 'gradient' == $attr['backgroundType'] ) {
				$selectors[' .wpcfp__offer-yes-no-button-link'] = array(
					'border-style'   => $attr['borderStyle'],
					'border-color'   => $attr['borderColor'],
					'border-width'   => Cartflows_Gb_Helper::get_css_value( $attr['borderWidth'], 'px' ),
					'border-radius'  => Cartflows_Gb_Helper::get_css_value( $attr['borderRadius'], 'px' ),
					'padding-top'    => Cartflows_Gb_Helper::get_css_value( $attr['vPaddingDesktop'], $attr['paddingTypeDesktop'] ),
					'padding-bottom' => Cartflows_Gb_Helper::get_css_value( $attr['vPaddingDesktop'], $attr['paddingTypeDesktop'] ),
					'padding-left'   => Cartflows_Gb_Helper::get_css_value( $attr['hPaddingDesktop'], $attr['paddingTypeDesktop'] ),
					'padding-right'  => Cartflows_Gb_Helper::get_css_value( $attr['hPaddingDesktop'], $attr['paddingTypeDesktop'] ),
					'color'          => $attr['textColor'],
					'text-align'     => $attr['textAlignment'],
				);
			}

			$position = str_replace( '-', ' ', $attr['backgroundPosition'] );

			if ( 'image' == $attr['backgroundType'] ) {
				$selectors[' .wpcfp__offer-yes-no-button-link'] = array(
					'opacity'               => ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : 0,
					'background-color'      => $attr['backgroundImageColor'],
					'border-style'          => $attr['borderStyle'],
					'border-color'          => $attr['borderColor'],
					'border-width'          => Cartflows_Gb_Helper::get_css_value( $attr['borderWidth'], 'px' ),
					'border-radius'         => Cartflows_Gb_Helper::get_css_value( $attr['borderRadius'], 'px' ),
					'padding-top'           => Cartflows_Gb_Helper::get_css_value( $attr['vPaddingDesktop'], $attr['paddingTypeDesktop'] ),
					'padding-bottom'        => Cartflows_Gb_Helper::get_css_value( $attr['vPaddingDesktop'], $attr['paddingTypeDesktop'] ),
					'padding-left'          => Cartflows_Gb_Helper::get_css_value( $attr['hPaddingDesktop'], $attr['paddingTypeDesktop'] ),
					'padding-right'         => Cartflows_Gb_Helper::get_css_value( $attr['hPaddingDesktop'], $attr['paddingTypeDesktop'] ),
					'color'                 => $attr['textColor'],
					'background-image'      => ( isset( $attr['backgroundImage'] ) && isset( $attr['backgroundImage']['url'] ) ) ? "url('" . $attr['backgroundImage']['url'] . "' )" : null,
					'background-position'   => $position,
					'background-attachment' => $attr['backgroundAttachment'],
					'background-repeat'     => $attr['backgroundRepeat'],
					'background-size'       => $attr['backgroundSize'],
					'text-align'            => $attr['textAlignment'],
				);
			} elseif ( 'color' == $attr['backgroundType'] ) {
				$selectors[' .wpcfp__offer-yes-no-button-link']                           = array(
					'opacity'          => ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : 0,
					'background-color' => $attr['backgroundColor'],
					'border-style'     => $attr['borderStyle'],
					'border-color'     => $attr['borderColor'],
					'border-width'     => Cartflows_Gb_Helper::get_css_value( $attr['borderWidth'], 'px' ),
					'border-radius'    => Cartflows_Gb_Helper::get_css_value( $attr['borderRadius'], 'px' ),
					'padding-top'      => Cartflows_Gb_Helper::get_css_value( $attr['vPaddingDesktop'], $attr['paddingTypeDesktop'] ),
					'padding-bottom'   => Cartflows_Gb_Helper::get_css_value( $attr['vPaddingDesktop'], $attr['paddingTypeDesktop'] ),
					'padding-left'     => Cartflows_Gb_Helper::get_css_value( $attr['hPaddingDesktop'], $attr['paddingTypeDesktop'] ),
					'padding-right'    => Cartflows_Gb_Helper::get_css_value( $attr['hPaddingDesktop'], $attr['paddingTypeDesktop'] ),
					'color'            => $attr['textColor'],
					'text-align'       => $attr['textAlignment'],
				);
				$selectors[' .wpcfp__offer-yes-no-button-link:hover']['background-color'] = $attr['buttonHoverColor'];
			} elseif ( 'gradient' === $attr['backgroundType'] ) {

				$selectors[' .wpcfp__offer-yes-no-button-link']['background-color'] = 'transparent';
				$selectors[' .wpcfp__offer-yes-no-button-link']['opacity']          = ( isset( $attr['backgroundOpacity'] ) && '' !== $attr['backgroundOpacity'] ) ? $attr['backgroundOpacity'] / 100 : 0;
				if ( $attr['gradientValue'] ) {
					$selectors[' .wpcfp__offer-yes-no-button-link']['background-image'] = $attr['gradientValue'];

				} else {
					if ( 'linear' === $attr['gradientType'] ) {

						$selectors[' .wpcfp__offer-yes-no-button-link']['background-image'] = "linear-gradient(${ $attr['gradientAngle'] }deg, ${ $attr['gradientColor1'] } ${ $attr['gradientLocation1'] }%, ${ $attr['gradientColor2'] } ${ $attr['gradientLocation2'] }%)";
					} else {

						$selectors[' .wpcfp__offer-yes-no-button-link']['background-image'] = "radial-gradient( at ${ $attr['gradientPosition'] }, ${ $attr['gradientColor1'] } ${ $attr['gradientLocation1'] }%, ${ $attr['gradientColor2'] } ${ $attr['gradientLocation2'] }%)";
					}
				}
			}

			$margin_type = ( 'after_title' === $attr['iconPosition'] || 'after_title_sub_title' === $attr['iconPosition'] ) ? 'margin-left' : 'margin-right';

			$selectors[' .wpcfp__offer-yes-no-button-icon svg'][ $margin_type ] = Cartflows_Pro_Gb_Helper::get_css_value( $attr['iconSpacing'], 'px' );

			$t_selectors = array(
				' .wpcfp__offer-yes-no-button-wrap' => array(
					'text-align' => $attr['talign'],
				),
				' .wpcfp__offer-yes-no-button-link' => array(
					'padding-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['vPaddingTablet'], $attr['paddingTypeTablet'] ),
					'padding-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['vPaddingTablet'], $attr['paddingTypeTablet'] ),
					'padding-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['hPaddingTablet'], $attr['paddingTypeTablet'] ),
					'padding-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['hPaddingTablet'], $attr['paddingTypeTablet'] ),
				),
			);

			$m_selectors = array(
				' .wpcfp__offer-yes-no-button-wrap' => array(
					'text-align' => $attr['malign'],
				),
				' .wpcfp__offer-yes-no-button-link' => array(
					'padding-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['vPaddingMobile'], $attr['paddingTypeMobile'] ),
					'padding-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['vPaddingMobile'], $attr['paddingTypeMobile'] ),
					'padding-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['hPaddingMobile'], $attr['paddingTypeMobile'] ),
					'padding-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['hPaddingMobile'], $attr['paddingTypeMobile'] ),
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

			$m_selectors = array();
			$t_selectors = array();

			$selectors = array(

				' .wpcfp__offer-product-title'       => array(
					'text-align'    => $attr['textAlignment'],
					'color'         => $attr['textColor'],
					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['topMargin'], 'px' ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['bottomMargin'], 'px' ),
					'text-shadow'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowHOffset'], 'px' ) . ' ' . Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowVOffset'], 'px' ) . ' ' . Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowBlur'], 'px' ) . ' ' . $attr['textShadowColor'],
				),
				' .wpcfp__offer-product-title:hover' => array(
					'color' => $attr['textHoverColor'],
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

				' .wpcfp__offer-product-description' => array(
					'text-align'     => $attr['textAlignment'],
					'color'          => $attr['textColor'],
					'padding-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['vPaddingDesktop'], $attr['paddingTypeDesktop'] ),
					'padding-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['vPaddingDesktop'], $attr['paddingTypeDesktop'] ),
					'padding-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['hPaddingDesktop'], $attr['paddingTypeDesktop'] ),
					'padding-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['hPaddingDesktop'], $attr['paddingTypeDesktop'] ),
					'text-shadow'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowHOffset'], 'px' ) . ' ' . Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowVOffset'], 'px' ) . ' ' . Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowBlur'], 'px' ) . ' ' . $attr['textShadowColor'],
				),

			);

			$t_selectors = array(
				' .wpcfp__offer-product-description' => array(
					'padding-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['vPaddingTablet'], $attr['paddingTypeTablet'] ),
					'padding-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['vPaddingTablet'], $attr['paddingTypeTablet'] ),
					'padding-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['hPaddingTablet'], $attr['paddingTypeTablet'] ),
					'padding-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['hPaddingTablet'], $attr['paddingTypeTablet'] ),
				),
			);

			$m_selectors = array(
				' .wpcfp__offer-product-description' => array(
					'padding-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['vPaddingMobile'], $attr['paddingTypeMobile'] ),
					'padding-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['vPaddingMobile'], $attr['paddingTypeMobile'] ),
					'padding-left'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['hPaddingMobile'], $attr['paddingTypeMobile'] ),
					'padding-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['hPaddingMobile'], $attr['paddingTypeMobile'] ),
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
					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['topMargin'], 'px' ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['bottomMargin'], 'px' ),
					'text-shadow'   => Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowHOffset'], 'px' ) . ' ' . Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowVOffset'], 'px' ) . ' ' . Cartflows_Pro_Gb_Helper::get_css_value( $attr['textShadowBlur'], 'px' ) . ' ' . $attr['textShadowColor'],
				),
				' .wpcfp__offer-product-price:hover' => array(
					'color' => $attr['textHoverColor'],
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
				' .wpcfp__offer-product-quantity .quantity .input-text.qty.text' => array(
					'color'            => $attr['inputTextColor'],
					'background-color' => $attr['backgroundColor'],
					'border-style'     => $attr['borderStyle'],
					'border-color'     => $attr['borderColor'],
					'border-width'     => Cartflows_Pro_Gb_Helper::get_css_value( $attr['borderWidth'], 'px' ),
					'border-radius'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['borderRadius'], 'px' ),
					'margin-top'       => Cartflows_Pro_Gb_Helper::get_css_value( $attr['label_bottom_spacing'], 'px' ),
				),
				' .wpcfp__offer-product-quantity' => array(
					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['topMargin'], 'px' ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['bottomMargin'], 'px' ),
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
				' .wpcfp__offer-product-variation .wcf-embeded-product-variation-wrap .variations td.label' => array(
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['label_bottom_spacing'], 'px' ),
				),
				' .wpcfp__offer-product-variation'       => array(
					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['topMargin'], 'px' ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['bottomMargin'], 'px' ),
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

			$selectors = array(

				' .woocommerce-product-gallery .woocommerce-product-gallery__image' => array(
					'text-align' => $attr['alignment'],
				),
				' .woocommerce-product-gallery .woocommerce-product-gallery__wrapper' => array(
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['image_bottom_spacing'], 'px' ),
				),
				' .woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image img' => array(
					'border-style'  => $attr['imageBorderStyle'],
					'border-color'  => $attr['imageBorderColor'],
					'border-width'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['imageBorderWidth'], 'px' ),
					'border-radius' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['imageBorderRadius'], 'px' ),
				),
				' .woocommerce-product-gallery ol li:not(:last-child)' => array(
					'margin-right'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['spacing_between_thumbnails'], 'px' ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['spacing_between_thumbnails'], 'px' ),
				),
				' .woocommerce-product-gallery ol li img' => array(
					'border-style'  => $attr['thumbnailBorderStyle'],
					'border-color'  => $attr['thumbnailBorderColor'],
					'border-width'  => Cartflows_Pro_Gb_Helper::get_css_value( $attr['thumbnailBorderWidth'], 'px' ),
					'border-radius' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['thumbnailBorderRadius'], 'px' ),
				),
				' .wpcfp__offer-product-image'            => array(
					'margin-top'    => Cartflows_Pro_Gb_Helper::get_css_value( $attr['topMargin'], 'px' ),
					'margin-bottom' => Cartflows_Pro_Gb_Helper::get_css_value( $attr['bottomMargin'], 'px' ),
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
