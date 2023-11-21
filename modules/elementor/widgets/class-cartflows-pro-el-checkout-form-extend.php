<?php
/**
 * Elementor Classes.
 *
 * @package cartflows
 */

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Checkout Form Widget
 *
 * @since 1.6.13
 */
class Cartflows_Pro_Checkout_Form_Extend {


	/**
	 * Member Variable
	 *
	 * @var object instance
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
	 * Settings
	 *
	 * @since 1.6.13
	 * @var object $settings
	 */
	public static $settings;

	/**
	 * Checkout Settings
	 *
	 * @since 1.6.13
	 * @var object $checkout_settings
	 */
	public static $checkout_settings;

	/**
	 * Setup actions and filters.
	 *
	 * @since 1.6.13
	 */
	private function __construct() {

		// Apply dynamic option filters.
		add_action( 'cartflows_elementor_checkout_options_filters', array( $this, 'dynamic_filters' ), 10, 2 );

		// Add two step control sections.
		add_action( 'elementor/element/checkout-form/section_general_fields/after_section_end', array( $this, 'register_two_step_section_controls' ), 10, 2 );

		// Add section for the product option.
		add_action( 'elementor/element/checkout-form/section_general_fields/after_section_end', array( $this, 'register_product_option_section_controls' ), 10, 2 );

		add_action( 'elementor/element/checkout-form/section_payment_style_fields/after_section_end', array( $this, 'register_two_step_style_controls' ), 10, 2 );

		// Product options control sections.
		add_action( 'elementor/element/checkout-form/section_payment_style_fields/after_section_end', array( $this, 'register_product_options_style_controls' ), 10, 2 );
	}

	/**
	 * Register Two Step Navigation Button Controls.
	 *
	 * @param array $elementor element data.
	 * @param array $args data.
	 *
	 * @since 1.6.13
	 * @access protected
	 */
	public function register_two_step_section_controls( $elementor, $args ) {

		$elementor->start_controls_section(
			'section_two_step_section_fields',
			array(
				'label'     => __( 'Two Step', 'cartflows-pro' ),
				'condition' => array(
					'layout' => 'two-step',
				),
			)
		);

		$elementor->add_control(
			'enable_note',
			array(
				'label'        => __( 'Enable Checkout Note', 'cartflows-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'YES', 'cartflows-pro' ),
				'label_off'    => __( 'NO', 'cartflows-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$elementor->add_control(
			'note_text',
			array(
				'label'       => __( 'Note Text', 'cartflows-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Get Your FREE copy of CartFlows in just few steps.', 'cartflows-pro' ),
				'default'     => __( 'Get Your FREE copy of CartFlows in just few steps.', 'cartflows-pro' ),
				'label_block' => false,
				'condition'   => array(
					'enable_note' => 'yes',
				),
			)
		);

		$elementor->add_control(
			'two_step_section_heading',
			array(
				'label'     => __( 'Steps', 'cartflows-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$elementor->add_control(
			'step_one_title_text',
			array(
				'label'       => __( 'Step One Title', 'cartflows-pro' ),
				'default'     => __( 'Shipping', 'cartflows-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Shipping', 'cartflows-pro' ),
			)
		);

		$elementor->add_control(
			'step_one_sub_title_text',
			array(
				'label'       => __( 'Step One Sub Title', 'cartflows-pro' ),
				'default'     => __( 'Where to ship it?', 'cartflows-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Where to ship it?', 'cartflows-pro' ),
			)
		);

		$elementor->add_control(
			'step_two_title_text',
			array(
				'label'       => __( 'Step Two Title', 'cartflows-pro' ),
				'default'     => __( 'Payment', 'cartflows-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Payment', 'cartflows-pro' ),
			)
		);

		$elementor->add_control(
			'step_two_sub_title_text',
			array(
				'label'       => __( 'Step Two Sub Title', 'cartflows-pro' ),
				'default'     => __( 'Of your order', 'cartflows-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Of your order', 'cartflows-pro' ),
			)
		);

		$elementor->add_control(
			'offer_button_section',
			array(
				'label'     => __( 'Offer Button', 'cartflows-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$elementor->add_control(
			'offer_button_title_text',
			array(
				'label'       => __( 'Offer Button Title', 'cartflows-pro' ),
				'default'     => __( 'For Special Offer Click Here', 'cartflows-pro' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => __( 'For Special Offer Click Here', 'cartflows-pro' ),
			)
		);

		$elementor->add_control(
			'offer_button_subtitle_text',
			array(
				'label'       => __( 'Offer Button Sub Title', 'cartflows-pro' ),
				'default'     => __( 'Yes! I want this offer!', 'cartflows-pro' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => __( 'Yes! I want this offer!', 'cartflows-pro' ),
			)
		);

		$elementor->end_controls_section();
	}

	/**
	 * Register Two Step style Controls.
	 *
	 * @param array $elementor element data.
	 * @param array $args data.
	 *
	 * @since 1.6.13
	 * @access protected
	 */
	public function register_two_step_style_controls( $elementor, $args ) {

		$elementor->start_controls_section(
			'section_two_step_style_fields',
			array(
				'label'     => __( 'Two Step', 'cartflows-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'enable_note' => 'yes',
					'layout'      => 'two-step',
				),
			)
		);

		$elementor->add_control(
			'note_text_color',
			array(
				'label'     => __( 'Note Text Color', 'cartflows-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'enable_note' => 'yes',
					'layout'      => 'two-step',
				),
			)
		);

		$elementor->add_control(
			'note_bg_color',
			array(
				'label'     => __( 'Note Background Color', 'cartflows-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note
						' => 'background-color: {{VALUE}} !important; border-color: {{VALUE}};',
					'{{WRAPPER}} .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note:before' => 'border-top-color: {{VALUE}};',
				),
				'condition' => array(
					'enable_note' => 'yes',
					'layout'      => 'two-step',
				),
			)
		);

		$elementor->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'note_typography',
				'label'     => __( 'Note Typography', 'cartflows-pro' ),
				'selector'  => '{{WRAPPER}} .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note',
				'condition' => array(
					'enable_note' => 'yes',
					'layout'      => 'two-step',
				),
			)
		);

		$elementor->end_controls_section();
	}

	/**
	 * Register product option section Controls.
	 *
	 * @param object $elementor element data.
	 * @param array  $args data.
	 *
	 * @since 1.6.13
	 * @access protected
	 */
	public function register_product_option_section_controls( $elementor, $args ) {

		$checkout_id = get_the_id();

		$show_product_options = Cartflows_Pro_Helper::is_show_product_options_settings( $checkout_id );

		if ( $show_product_options ) {

			$is_enabled = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-options' );

			$elementor->start_controls_section(
				'section_product_option_section_fields',
				array(
					'label' => __( 'Product Options', 'cartflows-pro' ),
				)
			);

			if ( 'yes' === $is_enabled ) {

				$elementor->add_control(
					'product_options_position',
					array(
						'label'   => __( 'Position', 'cartflows-pro' ),
						'type'    => Controls_Manager::SELECT,
						'default' => '',
						'options' => array(
							'before-customer' => __( 'Before Checkout', 'cartflows-pro' ),
							'after-customer'  => __( 'After Customer Details', 'cartflows-pro' ),
							'before-order'    => __( 'Before Order Review', 'cartflows-pro' ),
						),
					)
				);

				$elementor->add_control(
					'product_options_skin',
					array(
						'label'   => __( 'Skin', 'cartflows-pro' ),
						'type'    => Controls_Manager::SELECT,
						'default' => '',
						'options' => array(
							'classic' => __( 'Classic', 'cartflows-pro' ),
							'cards'   => __( 'Cards', 'cartflows-pro' ),
						),
					)
				);

				$elementor->add_control(
					'product_options_images',
					array(
						'label'   => __( 'Show Product Images', 'cartflows-pro' ),
						'type'    => Controls_Manager::SELECT,
						'default' => '',
						'options' => array(
							'yes' => __( 'Yes', 'cartflows-pro' ),
							'no'  => __( 'No', 'cartflows-pro' ),
						),
					)
				);

				$elementor->add_control(
					'product_option_section_title_text',
					array(
						'label'       => __( 'Section Title Text', 'cartflows-pro' ),
						'type'        => Controls_Manager::TEXT,
						'placeholder' => __( 'Your Products', 'cartflows-pro' ),
					)
				);
			} else {

				$elementor->add_control(
					'product_option_disabled',
					array(
						'type'            => Controls_Manager::RAW_HTML,
						'raw'             => sprintf(
							/* translators: %1$1s, %2$2s Link to meta */
							__( 'Please enable "Product Options" from %1$1smeta settings%2$2s to edit options.', 'cartflows-pro' ),
							'<a href="' . Cartflows_Pro_Helper::get_current_page_edit_url( 'checkout_products' ) . '" target="_blank">',
							'</a>'
						),
						'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
					)
				);
			}

			$elementor->end_controls_section();
		}
	}

	/**
	 * Register product options Style Controls.
	 *
	 * @param object $elementor element data.
	 * @param array  $args data.
	 *
	 * @since 1.6.13
	 */
	public function register_product_options_style_controls( $elementor, $args ) {

		$checkout_id = get_the_id();

		$show_product_options = Cartflows_Pro_Helper::is_show_product_options_settings( $checkout_id );

		if ( $show_product_options ) {

			$is_enabled = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-options' );

			$elementor->start_controls_section(
				'product_options_style_fields',
				array(
					'label' => __( 'Product Options', 'cartflows-pro' ),
					'tab'   => Controls_Manager::TAB_STYLE,
				)
			);

			if ( 'yes' === $is_enabled ) {

				$elementor->add_control(
					'product_text_color',
					array(
						'label'     => __( 'Text Color', 'cartflows-pro' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array(
							'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row' => 'color: {{VALUE}};',
						),
					)
				);

				$elementor->add_control(
					'product_bg_color',
					array(
						'label'     => __( 'Background Color', 'cartflows-pro' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array(
							'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options' => 'background-color: {{VALUE}};',
							'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row' => 'background-color: {{VALUE}};',
						),
					)
				);

				$elementor->add_control(
					'product_option_border_style',
					array(
						'label'       => __( 'Border Style', 'cartflows-pro' ),
						'type'        => Controls_Manager::SELECT,
						'label_block' => false,
						'default'     => '',
						'options'     => array(
							''       => __( 'Default', 'cartflows-pro' ),
							'solid'  => __( 'Solid', 'cartflows-pro' ),
							'double' => __( 'Double', 'cartflows-pro' ),
							'dotted' => __( 'Dotted', 'cartflows-pro' ),
							'dashed' => __( 'Dashed', 'cartflows-pro' ),
						),
						'selectors'   => array(
							'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options' => 'border-style: {{VALUE}};',
							'{{WRAPPER}} .wcf-el-checkout-form .wcf-embed-checkout-form-modern-checkout .wcf-qty-options .wcf-qty-row:not(.wcf-qty-table-titles)' => 'border-style: {{VALUE}} none {{VALUE}} none',
						),
					)
				);

				$elementor->add_control(
					'product_option_border_size',
					array(
						'label'      => __( 'Border Width', 'cartflows-pro' ),
						'type'       => Controls_Manager::DIMENSIONS,
						'size_units' => array( 'px' ),
						'selectors'  => array(
							'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							'{{WRAPPER}} .wcf-el-checkout-form .wcf-embed-checkout-form-modern-checkout .wcf-qty-options .wcf-qty-row:not(.wcf-qty-table-titles)' => 'border-width: {{TOP}}{{UNIT}} 0 {{BOTTOM}}{{UNIT}} 0;',
						),
					)
				);

				$elementor->add_control(
					'product_option_border_color',
					array(
						'label'     => __( 'Border Color', 'cartflows-pro' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array(
							'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options,
							{{WRAPPER}} .wcf-el-checkout-form .wcf-embed-checkout-form-modern-checkout .wcf-qty-options .wcf-qty-row:not(.wcf-qty-table-titles)' => 'border-color: {{VALUE}};',
						),
					)
				);

				$elementor->add_control(
					'product_option_border_radius',
					array(
						'label'      => __( 'Rounded Corners', 'cartflows-pro' ),
						'type'       => Controls_Manager::DIMENSIONS,
						'size_units' => array( 'px', 'em', '%' ),
						'selectors'  => array(
							'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						),
					)
				);

				// Highlight Product CSS options.

				$elementor->add_control(
					'highlight_product',
					array(
						'label'     => __( 'Highlight Product', 'cartflows-pro' ),
						'type'      => Controls_Manager::HEADING,
						'separator' => 'before',
					)
				);

				$elementor->add_control(
					'highlight_product_bg_color',
					array(
						'label'     => __( 'Background Color', 'cartflows-pro' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array(
							'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight' => 'background-color: {{VALUE}};',
						),
					)
				);

				$elementor->add_control(
					'highlight_product_text_color',
					array(
						'label'     => __( 'Text Color', 'cartflows-pro' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array(
							'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight' => 'color: {{VALUE}};',
						),
					)
				);

				$elementor->add_control(
					'highlight_product_option_border_style',
					array(
						'label'       => __( 'Border Style', 'cartflows-pro' ),
						'type'        => Controls_Manager::SELECT,
						'label_block' => false,
						'default'     => '',
						'options'     => array(
							''       => __( 'Default', 'cartflows-pro' ),
							'solid'  => __( 'Solid', 'cartflows-pro' ),
							'double' => __( 'Double', 'cartflows-pro' ),
							'dotted' => __( 'Dotted', 'cartflows-pro' ),
							'dashed' => __( 'Dashed', 'cartflows-pro' ),
						),
						'selectors'   => array(
							'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight, .wcf-el-checkout-form .wcf-embed-checkout-form-modern-checkout .wcf-qty-options .wcf-qty-row.wcf-highlight' => 'border-style: {{VALUE}};',
						),
					)
				);

				$elementor->add_control(
					'highlight_product_border_size',
					array(
						'label'      => __( 'Border Width', 'cartflows-pro' ),
						'type'       => Controls_Manager::DIMENSIONS,
						'size_units' => array( 'px' ),
						'selectors'  => array(
							'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight, .wcf-el-checkout-form .wcf-embed-checkout-form-modern-checkout .wcf-qty-options .wcf-qty-row.wcf-highlight' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						),
					)
				);

				$elementor->add_control(
					'highlight_box_border_color',
					array(
						'label'     => __( 'Border Color', 'cartflows-pro' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array(
							'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight' => 'border-color: {{VALUE}};',

						),
					)
				);

				$elementor->add_control(
					'highlight_product_border_radius',
					array(
						'label'      => __( 'Rounded Corners', 'cartflows-pro' ),
						'type'       => Controls_Manager::DIMENSIONS,
						'size_units' => array( 'px', 'em', '%' ),
						'selectors'  => array(
							'.wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						),
					)
				);

				$elementor->add_control(
					'highlight_flag_text_color',
					array(
						'label'     => __( 'Highlight Flag Text Color', 'cartflows-pro' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array(
							'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight .wcf-highlight-head' => 'color: {{VALUE}};',
						),
					)
				);

				$elementor->add_control(
					'highlight_flag_bg_color',
					array(
						'label'     => __( 'Highlight Flag Background Color', 'cartflows-pro' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array(
							'{{WRAPPER}} .wcf-el-checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight .wcf-highlight-head' => 'background-color: {{VALUE}};',
						),
					)
				);
			} else {
				$elementor->add_control(
					'product_option_style_disabled',
					array(
						'type'            => Controls_Manager::RAW_HTML,
						'raw'             => sprintf(
							/* translators: %1$1s, %2$2s Link to meta */
							__( 'Please enable "Product Options" from %1$1smeta settings%2$2s to apply styles.', 'cartflows-pro' ),
							'<a href="' . Cartflows_Pro_Helper::get_current_page_edit_url( 'checkout_products' ) . '" target="_blank">',
							'</a>'
						),
						'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
					)
				);
			}

			$elementor->end_controls_section();
		}
	}

	/**
	 * Added dynamic filter.
	 *
	 * @param array $settings settings data.
	 *
	 * @since 1.6.13
	 */
	public function dynamic_filters( $settings ) {

		self::$settings = $settings;

		$checkout_id            = get_the_id();
		$enable_product_options = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-options' );
		$enable_checkout_offer  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-pre-checkout-offer' );

		if ( 'yes' === $enable_checkout_offer ) {

			self::$checkout_settings = array(
				'enable_checkout_offer' => $enable_checkout_offer,
			);

			add_filter(
				'cartflows_elementor_checkout_settings',
				function ( $data_settings ) {
					$data_settings = self::$checkout_settings;
					return $data_settings;
				},
				10,
				1
			);
		}

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

				$is_note_enabled = ( 'yes' === self::$settings['enable_note'] ) ? 'yes' : 'no';
				return $is_note_enabled;
			},
			10,
			1
		);

		if ( 'yes' === self::$settings['enable_note'] && '' !== self::$settings['note_text'] ) {

			add_filter(
				'cartflows_checkout_meta_wcf-checkout-box-note-text',
				function ( $checkout_note_text ) {

					$checkout_note_text = self::$settings['note_text'];
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

					$setting_name = $field['setting_name'];

					if ( isset( self::$settings[ $setting_name ] ) && '' !== self::$settings[ $setting_name ] ) {
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
	}
}

/**
 * Initiate the class.
 */
Cartflows_Pro_Checkout_Form_Extend::get_instance();
