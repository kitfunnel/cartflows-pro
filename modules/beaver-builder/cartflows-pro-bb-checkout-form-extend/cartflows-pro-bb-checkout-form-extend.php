<?php
/**
 * Cartflows Pro Checkout Form extend File
 *
 * @since 1.6.13
 * @package cartflows-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_BB_Checkout_Form_Extend' ) ) {

	/**
	 * This class initializes Checkout Form
	 *
	 * @class Cartflows_Pro_BB_Checkout_Form_Extend
	 */
	final class Cartflows_Pro_BB_Checkout_Form_Extend {

		/**
		 * Initializes module.
		 *
		 * @since 1.6.13
		 * @return void
		 */
		public static function init() {

			// Apply dynamic option filters.
			add_action( 'cartflows_bb_checkout_options_filters', __CLASS__ . '::bb_checkout_dynamic_filters', 10, 1 );
			add_filter( 'fl_builder_register_settings_form', __CLASS__ . '::bb_checkout_form_extend', 1010, 2 );
			add_filter( 'fl_builder_render_css', __CLASS__ . '::bb_checkout_form_css', 10, 3 );
			add_filter( 'fl_builder_render_js', __CLASS__ . '::bb_checkout_form_js', 10, 3 );
		}

		/**
		 * Dynamic filters.
		 *
		 * @param array $settings Settings array.
		 *
		 * @since 1.6.13
		 */
		public static function bb_checkout_dynamic_filters( $settings ) {

			$checkout_id            = get_the_ID();
			$enable_product_options = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-options' );

			// Controls filters.
			$checkout_fields = array(

				// Two step texts.
				array(
					'filter_slug'  => 'wcf-checkout-step-one-title',
					'setting_name' => 'step_one_title_text',
				),
				array(
					'filter_slug'  => 'wcf-checkout-step-one-sub-title',
					'setting_name' => 'step_one_sub_title_text',
				),
				array(
					'filter_slug'  => 'wcf-checkout-step-two-title',
					'setting_name' => 'step_two_title_text',
				),
				array(
					'filter_slug'  => 'wcf-checkout-step-two-sub-title',
					'setting_name' => 'step_two_sub_title_text',
				),

				array(
					'filter_slug'  => 'wcf-checkout-offer-button-title',
					'setting_name' => 'offer_button_title_text',
				),
				array(
					'filter_slug'  => 'wcf-checkout-offer-button-sub-title',
					'setting_name' => 'offer_button_subtitle_text',
				),
			);

			if ( isset( $checkout_fields ) && is_array( $checkout_fields ) ) {

				foreach ( $checkout_fields as $key => $field ) {

					$setting_name  = $field['setting_name'];
					$setting_value = $settings->$setting_name;

					if ( '' !== $setting_value ) {

						add_filter(
							'cartflows_checkout_meta_' . $field['filter_slug'],
							function ( $value ) use ( $setting_value ) {

								$value = $setting_value;

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
				function ( $is_note_enabled ) use ( $settings ) {

					$is_note_enabled = ( 'yes' === $settings->enable_note ) ? 'yes' : 'no';
					return $is_note_enabled;
				},
				10,
				1
			);

			if ( 'yes' === $settings->enable_note && '' !== $settings->note_text ) {

				add_filter(
					'cartflows_checkout_meta_wcf-checkout-box-note-text',
					function ( $checkout_note_text ) use ( $settings ) {
						$checkout_note_text = $settings->note_text;
						return $checkout_note_text;
					},
					10,
					1
				);
			}

			// Product options.
			if ( 'yes' === $enable_product_options ) {

				$product_options_fields = array(
					array(
						'filter_slug'  => 'wcf-product-options-skin',
						'setting_name' => 'product_options_skin',
					),
					array(
						'filter_slug'  => 'wcf-show-product-images',
						'setting_name' => 'product_options_images',
					),
					array(
						'filter_slug'  => 'wcf-product-opt-title',
						'setting_name' => 'product_option_section_title_text',
					),
					array(
						'filter_slug'  => 'wcf-your-products-position',
						'setting_name' => 'product_options_position',
					),
				);

				if ( isset( $product_options_fields ) && is_array( $product_options_fields ) ) {

					foreach ( $product_options_fields as $key => $field ) {

						$setting_name  = $field['setting_name'];
						$setting_value = isset( $settings->$setting_name ) ? $settings->$setting_name : '';

						if ( '' !== $setting_value ) {

							add_filter(
								'cartflows_checkout_meta_' . $field['filter_slug'],
								function ( $value ) use ( $setting_value ) {

									$value = $setting_value;

									return $value;
								},
								10,
								1
							);
						}
					}
				}
			}

			add_filter(
				'cartflows_bb_checkout_settings',
				function ( $data_settings ) use ( $settings, $enable_product_options ) {

					$data_settings = array(

						'enable_product_options' => $enable_product_options,
					);

					return $data_settings;
				},
				10,
				1
			);

		}

		/**
		 * Extending row setting.
		 *
		 * @param array  $form Settings array.
		 * @param string $id module id.
		 *
		 * @since 1.6.13
		 */
		public static function bb_checkout_form_extend( $form, $id ) {

			if ( 'cartflows-bb-checkout-form' == $id ) {

				$checkout_id = get_the_id();

				$show_product_options = Cartflows_Pro_Helper::is_show_product_options_settings( $checkout_id );

				if ( $show_product_options ) {

					$form['general']['sections']['product_options'] = array(
						'title'  => 'Product Options',
						'fields' => array(
							'product_options_position' => array(
								'type'        => 'select',
								'label'       => __( 'Select Position', 'cartflows-pro' ),
								'default'     => 'after-customer',
								'options'     => array(
									'before-customer' => __( 'Before Checkout', 'cartflows-pro' ),
									'after-customer'  => __( 'After Customer Details', 'cartflows-pro' ),
									'before-order'    => __( 'Before Order Review', 'cartflows-pro' ),
								),
								/* translators: %s: link */
								'description' => sprintf(
									/* translators: %1$1s, %2$2s Link to meta */
									__( 'Please enable "Product-Options" from %1$1shere%2$2s to edit options.', 'cartflows-pro' ),
									'<a href="' . Cartflows_Pro_Helper::get_current_page_edit_url( 'checkout_products' ) . '" target="_blank" class="cartflows-bb-note">',
									'</a>'
								),
							),
							'product_options_skin'     => array(
								'type'    => 'select',
								'label'   => __( 'Skin', 'cartflows-pro' ),
								'default' => 'classic',
								'options' => array(
									'classic' => __( 'Classic', 'cartflows-pro' ),
									'cards'   => __( 'Cards', 'cartflows-pro' ),
								),
							),
							'product_options_images'   => array(
								'type'    => 'select',
								'label'   => __( 'Show Product Images', 'cartflows-pro' ),
								'default' => 'no',
								'options' => array(
									'yes' => __( 'Yes', 'cartflows-pro' ),
									'no'  => __( 'No', 'cartflows-pro' ),
								),
							),
							'product_option_section_title_text' => array(
								'type'        => 'text',
								'label'       => __( 'Section Title Text', 'cartflows-pro' ),
								'placeholder' => __( 'Your Products', 'cartflows-pro' ),
								'connections' => array( 'string' ),
							),
						),
					);

					$form['style']['sections']['product_style'] = array(
						'title'  => __( 'Product Options', 'cartflows-pro' ),
						'fields' => array(
							'product_text_color'           => array(
								'type'        => 'color',
								'label'       => __( 'Text Color', 'cartflows-pro' ),
								'default'     => '',
								'show_reset'  => true,
								'connections' => array( 'color' ),
								'show_alpha'  => true,
								'preview'     => array(
									'type'     => 'css',
									'selector' => '.wcf-qty-options .wcf-qty-row',
									'property' => 'color',
									'unit'     => 'px',
								),
							),
							'product_bg_color'             => array(
								'type'        => 'color',
								'label'       => __( 'Background Color', 'cartflows-pro' ),
								'default'     => '',
								'show_reset'  => true,
								'connections' => array( 'color' ),
								'show_alpha'  => true,
								'preview'     => array(
									'type'     => 'css',
									'selector' => '.wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options,
									.wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row',
									'property' => 'background-color',
									'unit'     => 'px',
								),
							),
							'product_option_border_style'  => array(
								'type'    => 'select',
								'label'   => __( 'Border Style', 'cartflows-pro' ),
								'default' => 'none',
								'help'    => __( 'The type of border to use. Double borders must have a width of at least 3px to render properly.', 'cartflows-pro' ),
								'options' => array(
									'none'   => __( 'None', 'cartflows-pro' ),
									'solid'  => __( 'Solid', 'cartflows-pro' ),
									'dashed' => __( 'Dashed', 'cartflows-pro' ),
									'dotted' => __( 'Dotted', 'cartflows-pro' ),
									'double' => __( 'Double', 'cartflows-pro' ),
								),
								'toggle'  => array(
									'solid'  => array(
										'fields' => array( 'product_option_border_size', 'product_option_border_radius', 'product_option_border_color' ),
									),
									'dashed' => array(
										'fields' => array( 'product_option_border_size', 'product_option_border_radius', 'product_option_border_color' ),
									),
									'dotted' => array(
										'fields' => array( 'product_option_border_size', 'product_option_border_radius', 'product_option_border_color' ),
									),
									'double' => array(
										'fields' => array( 'product_option_border_size', 'product_option_border_radius', 'product_option_border_color' ),
									),
								),
								'preview' => array(
									'type'     => 'css',
									'selector' => '.wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options,
									.wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row',
									'property' => 'border-style',
								),
							),
							'product_option_border_size'   => array(
								'type'        => 'unit',
								'label'       => __( 'Border Width', 'cartflows-pro' ),
								'slider'      => true,
								'units'       => array( 'px' ),
								'maxlength'   => '3',
								'size'        => '6',
								'placeholder' => '1',
								'preview'     => array(
									'type'     => 'css',
									'selector' => '.wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options,
									.wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row',
									'property' => 'border-width',
									'unit'     => 'px',
								),
							),
							'product_option_border_color'  => array(
								'type'       => 'color',
								'label'      => __( 'Border Color', 'cartflows-pro' ),
								'default'    => '',
								'show_reset' => true,
								'show_alpha' => true,
								'preview'    => array(
									'type'     => 'css',
									'selector' => '.wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options,
									.wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row',
									'property' => 'border-color',
									'unit'     => 'px',
								),
							),
							'product_option_border_radius' => array(
								'type'        => 'unit',
								'label'       => __( 'Border Radius', 'cartflows-pro' ),
								'slider'      => true,
								'units'       => array( 'px' ),
								'maxlength'   => '3',
								'size'        => '6',
								'placeholder' => '0',
								'preview'     => array(
									'type'     => 'css',
									'selector' => '.wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options,
									.wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row',
									'property' => 'border-radius',
									'unit'     => 'px',
								),
							),
							'highlight_product_bg_color'   => array(
								'type'        => 'color',
								'label'       => __( 'Highlight Product Background Color', 'cartflows-pro' ),
								'default'     => '',
								'show_reset'  => true,
								'connections' => array( 'color' ),
								'show_alpha'  => true,
								'preview'     => array(
									'type'     => 'css',
									'selector' => '.wcf-qty-options .wcf-qty-row.wcf-highlight',
									'property' => 'background-color',
									'unit'     => 'px',
								),
							),
							'highlight_product_text_color' => array(
								'type'        => 'color',
								'label'       => __( 'Highlight Product Text Color', 'cartflows-pro' ),
								'default'     => '',
								'show_reset'  => true,
								'connections' => array( 'color' ),
								'show_alpha'  => true,
								'preview'     => array(
									'type'     => 'css',
									'selector' => '.wcf-qty-options .wcf-qty-row.wcf-highlight',
									'property' => 'color',
									'unit'     => 'px',
								),
							),
							'highlight_product_border_style' => array(
								'type'    => 'select',
								'label'   => __( 'Highlight Product Border Style', 'cartflows-pro' ),
								'default' => 'none',
								'help'    => __( 'The type of border to use. Double borders must have a width of at least 3px to render properly.', 'cartflows-pro' ),
								'options' => array(
									'none'   => __( 'None', 'cartflows-pro' ),
									'solid'  => __( 'Solid', 'cartflows-pro' ),
									'dashed' => __( 'Dashed', 'cartflows-pro' ),
									'dotted' => __( 'Dotted', 'cartflows-pro' ),
									'double' => __( 'Double', 'cartflows-pro' ),
								),
								'toggle'  => array(
									'solid'  => array(
										'fields' => array( 'highlight_product_border_size', 'highlight_product_border_radius', 'highlight_product_border_color' ),
									),
									'dashed' => array(
										'fields' => array( 'highlight_product_border_size', 'highlight_product_border_radius', 'highlight_product_border_color' ),
									),
									'dotted' => array(
										'fields' => array( 'highlight_product_border_size', 'highlight_product_border_radius', 'highlight_product_border_color' ),
									),
									'double' => array(
										'fields' => array( 'highlight_product_border_size', 'highlight_product_border_radius', 'highlight_product_border_color' ),
									),
								),
								'preview' => array(
									'type'     => 'css',
									'selector' => '.wcf-qty-options .wcf-qty-row.wcf-highlight',
									'property' => 'border-style',
								),
							),
							'highlight_product_border_size' => array(
								'type'        => 'unit',
								'label'       => __( 'Border Width', 'cartflows-pro' ),
								'slider'      => true,
								'units'       => array( 'px' ),
								'maxlength'   => '3',
								'size'        => '6',
								'placeholder' => '1',
								'preview'     => array(
									'type'     => 'css',
									'selector' => '.wcf-qty-options .wcf-qty-row.wcf-highlight',
									'property' => 'border-width',
									'unit'     => 'px',
								),
							),
							'highlight_product_border_color' => array(
								'type'       => 'color',
								'label'      => __( 'Border Color', 'cartflows-pro' ),
								'default'    => '',
								'show_reset' => true,
								'show_alpha' => true,
								'preview'    => array(
									'type'     => 'css',
									'selector' => '.wcf-qty-options .wcf-qty-row.wcf-highlight',
									'property' => 'border-color',
									'unit'     => 'px',
								),
							),
							'highlight_product_border_radius' => array(
								'type'        => 'unit',
								'label'       => __( 'Border Radius', 'cartflows-pro' ),
								'slider'      => true,
								'units'       => array( 'px' ),
								'maxlength'   => '3',
								'size'        => '6',
								'placeholder' => '0',
								'preview'     => array(
									'type'     => 'css',
									'selector' => '.wcf-qty-options .wcf-qty-row.wcf-highlight',
									'property' => 'border-radius',
									'unit'     => 'px',
								),
							),
							'highlight_flag_text_color'    => array(
								'type'        => 'color',
								'label'       => __( 'Highlight Flag Text Color', 'cartflows-pro' ),
								'default'     => '',
								'show_reset'  => true,
								'connections' => array( 'color' ),
								'show_alpha'  => true,
								'preview'     => array(
									'type'     => 'css',
									'selector' => '.wcf-qty-options .wcf-qty-row.wcf-highlight .wcf-highlight-head',
									'property' => 'color',
									'unit'     => 'px',
								),
							),
							'highlight_flag_bg_color'      => array(
								'type'        => 'color',
								'label'       => __( 'Highlight Flag Background Color', 'cartflows-pro' ),
								'default'     => '',
								'show_reset'  => true,
								'connections' => array( 'color' ),
								'show_alpha'  => true,
								'preview'     => array(
									'type'     => 'css',
									'selector' => '.wcf-qty-options .wcf-qty-row.wcf-highlight .wcf-highlight-head',
									'property' => 'background-color',
									'unit'     => 'px',
								),
							),
						),
					);
				}

				// Content related options.
				$form['general']['sections']['two_step'] = array(
					'title'  => 'Two Step',
					'fields' => array(
						'enable_note'                => array(
							'type'    => 'select',
							'label'   => __( 'Enable Checkout Note', 'cartflows-pro' ),
							'default' => 'yes',
							'options' => array(
								'yes' => __( 'Yes', 'cartflows-pro' ),
								'no'  => __( 'No', 'cartflows-pro' ),
							),
							'toggle'  => array(
								'yes' => array(
									'fields' => array( 'note_text' ),
								),
							),
						),
						'note_text'                  => array(
							'type'        => 'text',
							'label'       => __( 'Note Text', 'cartflows-pro' ),
							'placeholder' => __( 'Get Your FREE copy of CartFlows in just few steps.', 'cartflows-pro' ),
							'connections' => array( 'string' ),
						),
						'step_one_title_text'        => array(
							'type'        => 'text',
							'label'       => __( 'Step One Title', 'cartflows-pro' ),
							'placeholder' => __( 'Shipping', 'cartflows-pro' ),
							'connections' => array( 'string' ),
						),
						'step_one_sub_title_text'    => array(
							'type'        => 'text',
							'label'       => __( 'Step One Sub Title', 'cartflows-pro' ),
							'placeholder' => __( 'Where to ship it?', 'cartflows-pro' ),
							'connections' => array( 'string' ),
						),
						'step_two_title_text'        => array(
							'type'        => 'text',
							'label'       => __( 'Step Two Title', 'cartflows-pro' ),
							'placeholder' => __( 'Payment', 'cartflows-pro' ),
							'connections' => array( 'string' ),
						),
						'step_two_sub_title_text'    => array(
							'type'        => 'text',
							'label'       => __( 'Step Two Sub Title', 'cartflows-pro' ),
							'placeholder' => __( 'Of your order', 'cartflows-pro' ),
							'connections' => array( 'string' ),
						),
						'offer_button_title_text'    => array(
							'type'        => 'text',
							'label'       => __( 'Offer Button Title', 'cartflows-pro' ),
							'placeholder' => __( 'For Special Offer Click Here', 'cartflows-pro' ),
							'connections' => array( 'string' ),
						),
						'offer_button_subtitle_text' => array(
							'type'        => 'text',
							'label'       => __( 'Offer Button Sub Title', 'cartflows-pro' ),
							'placeholder' => __( 'Yes! I want this offer!', 'cartflows-pro' ),
							'connections' => array( 'string' ),
						),
					),
				);

				// Style options.
				$form['style']['sections']['two_step_style'] = array(
					'title'  => __( 'Two Step', 'cartflows-pro' ),
					'fields' => array(
						'note_text_color' => array(
							'type'        => 'color',
							'label'       => __( 'Note Text Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-embed-checkout-form-note',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'note_bg_color'   => array(
							'type'        => 'color',
							'label'       => __( 'Note Background Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.wcf-embed-checkout-form-note',
								'property' => 'background-color',
								'unit'     => 'px',
							),
						),
						'note_typography' => array(
							'type'       => 'typography',
							'label'      => __( 'Note Typography', 'cartflows-pro' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.wcf-embed-checkout-form-note',
							),
						),
					),
				);
			}

			return $form;
		}

		/**
		 * Extending row setting.
		 *
		 * @param file  $css gets the Modules CSS.
		 * @param array $nodes Modules array.
		 * @param array $global_settings module global settings.
		 *
		 * @since 1.6.13
		 */
		public static function bb_checkout_form_css( $css, $nodes, $global_settings ) {

			foreach ( $nodes['modules'] as $module_object ) {

				if ( 'cartflows-bb-checkout-form' === $module_object->slug ) {

					$module_id       = $module_object->node;
					$module_settings = $module_object->settings;
					$module_slug     = $module_object->slug;

					ob_start();
					include CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-checkout-form-extend/includes/frontend-css.php';
					$css .= ob_get_clean();
				}
			}

			return $css;
		}

		/**
		 * Render Global uabb-layout-builder js
		 *
		 * @since 1.6.13
		 * @param file   $js Gets the js file contents.
		 * @param array  $nodes Gets the nodes of the layout builder.
		 * @param object $global_settings Gets the object for the Layout builder.
		 */
		public static function bb_checkout_form_js( $js, $nodes, $global_settings ) {

			return $js;
		}

	}

	Cartflows_Pro_BB_Checkout_Form_Extend::init();
}
