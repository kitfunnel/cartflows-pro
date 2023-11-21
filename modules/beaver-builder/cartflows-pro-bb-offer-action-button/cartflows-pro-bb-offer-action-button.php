<?php
/**
 * Offer Yes/No button Module for Beaver Builder
 *
 * @package cartflows-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Offer Yes/No button Module for Beaver Builder
 *
 * @since 1.6.13
 */
class Cartflows_Pro_BB_Offer_Action_Button extends FLBuilderModule {
	/**
	 * Constructor function for the module. You must pass the
	 * name, description, dir and url in an array to the parent class.
	 */
	public function __construct() {

		$step_type          = Cartflows_Pro_BB_Helper::cartflows_bb_step_type();
		$is_bb_setting_page = Cartflows_Pro_BB_Helper::wcf_pro_is_bb_setting_page();

		$is_enabled = ( wcf()->is_woo_active && ( 'upsell' === $step_type || 'downsell' === $step_type || $is_bb_setting_page ) ) ? true : false;

		parent::__construct(
			array(
				'name'            => __( 'Offer Yes/No Button', 'cartflows-pro' ),
				'description'     => __( 'Offer Yes/No Button.', 'cartflows-pro' ),
				'category'        => __( 'Cartflows Modules', 'cartflows-pro' ),
				'group'           => __( 'Cartflows Modules', 'cartflows-pro' ),
				'dir'             => CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-offer-action-button/',
				'url'             => CARTFLOWS_PRO_URL . 'modules/beaver-builder/cartflows-pro-bb-offer-action-button/',
				'partial_refresh' => false, // Defaults to false and can be omitted.
				'icon'            => 'bb-action-button.svg',
				'enabled'         => $is_enabled,
			)
		);
	}

	/**
	 * Function to get the icon for the module
	 *
	 * @param string $icon gets the icon for the module.
	 */
	public function get_icon( $icon = '' ) {

		if ( '' !== $icon && file_exists( CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-offer-action-button/icon/' . $icon ) ) {

			return file_get_contents( CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-offer-action-button/icon/' . $icon ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		}

		return '';
	}

	/**
	 * Function that gets the class names.
	 */
	public function get_classname() {
		$classname = 'cartflows-pro-bb__action-button-wrap cartflows-pro-bb__action-button-wrap cartflows-pro-bb__action-creative-button-wrap';

		if ( ! empty( $this->settings->width ) ) {
			$classname .= ' cartflows-pro-bb__action-button-width-' . $this->settings->width;
			$classname .= ' cartflows-pro-bb__action-creative-button-width-' . $this->settings->width;
		}
		if ( ! empty( $this->settings->align ) ) {
			$classname .= ' cartflows-pro-bb__action-button-' . $this->settings->align;
			$classname .= ' cartflows-pro-bb__action-creative-button-' . $this->settings->align;
		}
		if ( ! empty( $this->settings->mob_align ) ) {
			$classname .= ' cartflows-pro-bb__action-button-reponsive-' . $this->settings->mob_align;
			$classname .= ' cartflows-pro-bb__action-creative-button-reponsive-' . $this->settings->mob_align;
		}
		if ( ! empty( $this->settings->icon ) ) {
			$classname .= ' cartflows-pro-bb__action-button-has-icon';
			$classname .= ' cartflows-pro-bb__action-creative-button-has-icon';
		}

		if ( empty( $this->settings->text ) ) {
			$classname .= ' cartflows-pro-bb__action-creative-button-icon-no-text';
		}

		return $classname;
	}

	/**
	 * Function that gets the button styling.
	 */
	public function get_button_style() {
		$btn_style = '';

		if ( ! empty( $this->settings->style ) && 'transparent' == $this->settings->style ) {
			if ( isset( $this->settings->transparent_button_options ) && ! empty( $this->settings->transparent_button_options ) ) {
				$btn_style .= ' cartflows-pro-bb__action-' . $this->settings->transparent_button_options . '-btn';
			}
		}

		if ( ! empty( $this->settings->style ) && 'threed' == $this->settings->style ) {
			if ( isset( $this->settings->threed_button_options ) && ! empty( $this->settings->threed_button_options ) ) {
				$btn_style .= ' cartflows-pro-bb__action-' . $this->settings->threed_button_options . '-btn';
			}
		}

		return $btn_style;
	}

	/**
	 * Function that gets the class names.
	 */
	public function get_link() {
		$step_type = Cartflows_Pro_BB_Helper::cartflows_bb_step_type();

		if ( 'upsell' === $step_type || 'downsell' === $step_type ) {
			$accept_offer_link = 'upsell' === $step_type ? '?class=wcf-up-offer-yes' : '?class=wcf-down-offer-yes';
			$reject_offer_link = 'upsell' === $step_type ? '?class=wcf-up-offer-no' : '?class=wcf-down-offer-no';
		}

		$action_link = ( 'accept_offer' === $this->settings->offer_action ) ? $accept_offer_link : $reject_offer_link;

		return $action_link;

	}

}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module(
	'Cartflows_Pro_BB_Offer_Action_Button',
	array(

		'general'             => array(
			'title'    => __( 'General', 'cartflows-pro' ),
			'sections' => array(
				'general' => array(
					'title'  => '',
					'fields' => array(
						'offer_action'   => array(
							'type'    => 'select',
							'label'   => __( 'Offer Action', 'cartflows-pro' ),
							'default' => 'accept_offer',
							'options' => array(
								'accept_offer' => __( 'Accept Offer', 'cartflows-pro' ),
								'reject_offer' => __( 'Reject Offer', 'cartflows-pro' ),
							),
						),
						'text'           => array(
							'type'        => 'text',
							'label'       => __( 'Title', 'cartflows-pro' ),
							'default'     => __( 'Offer Action', 'cartflows-pro' ),
							'preview'     => array(
								'type'     => 'text',
								'selector' => '.cartflows-pro-bb__action-button-text',
							),
							'connections' => array( 'string' ),
						),
						'description'    => array(
							'type'        => 'text',
							'label'       => __( 'Sub Title', 'cartflows-pro' ),
							'preview'     => array(
								'type'     => 'text',
								'selector' => '.cartflows-pro-bb__action-button-description',
							),
							'connections' => array( 'string' ),
						),
						'icon'           => array(
							'type'        => 'icon',
							'label'       => __( 'Icon', 'cartflows-pro' ),
							'show_remove' => true,
							'preview'     => array(
								'type' => 'none',
							),
						),
						'icon_position'  => array(
							'type'    => 'select',
							'label'   => __( 'Icon Position', 'cartflows-pro' ),
							'default' => 'before',
							'options' => array(
								'before'            => __( 'Before Title', 'cartflows-pro' ),
								'after'             => __( 'After Title', 'cartflows-pro' ),
								'before_title_desc' => __( 'Before Title & Sub Title', 'cartflows-pro' ),
								'after_title_desc'  => __( 'After Title & Sub Title', 'cartflows-pro' ),
							),
							'preview' => array(
								'type' => 'none',
							),
							'toggle'  => array(
								'before_title_desc' => array(
									'fields' => array( 'verticle_align' ),
								),
								'after_title_desc'  => array(
									'fields' => array( 'verticle_align' ),
								),
							),
						),
						'verticle_align' => array(
							'type'    => 'select',
							'label'   => __( 'Vertical Alignment', 'cartflows-pro' ),
							'default' => 'top',
							'options' => array(
								'top'    => __( 'Top', 'cartflows-pro' ),
								'middle' => __( 'Middle', 'cartflows-pro' ),
							),
							'preview' => array(
								'type' => 'none',
							),
						),
						'icon_spacing'   => array(
							'type'      => 'unit',
							'label'     => __( 'Icon Spacing', 'cartflows-pro' ),
							'slider'    => true,
							'units'     => array( 'px' ),
							'maxlength' => '30',
							'size'      => '5',
						),
					),
				),
			),
		),
		'style'               => array(
			'title'    => __( 'Style', 'cartflows-pro' ),
			'sections' => array(
				'style'      => array(
					'title'  => __( 'Style', 'cartflows-pro' ),
					'fields' => array(
						'style'                      => array(
							'type'    => 'select',
							'label'   => __( 'Type', 'cartflows-pro' ),
							'default' => 'default',
							'class'   => 'creative_button_styles',
							'options' => array(
								'default'     => __( 'Default', 'cartflows-pro' ),
								'flat'        => __( 'Flat', 'cartflows-pro' ),
								'gradient'    => __( 'Gradient', 'cartflows-pro' ),
								'transparent' => __( 'Transparent', 'cartflows-pro' ),
								'threed'      => __( '3D', 'cartflows-pro' ),
							),
							'toggle'  => array(
								'default' => array(
									'fields' => array( 'button_padding_dimension', 'button_border', 'border_hover_color' ),
								),
							),
						),
						'border_size'                => array(
							'type'        => 'unit',
							'label'       => __( 'Border Size', 'cartflows-pro' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '3',
							'size'        => '5',
							'placeholder' => '2',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.cartflows-pro-bb__action-creative-button-wrap a',
								'property' => 'border-width',
								'unit'     => 'px',
							),
						),
						'transparent_button_options' => array(
							'type'    => 'select',
							'label'   => __( 'Hover Styles', 'cartflows-pro' ),
							'default' => 'transparent-fade',
							'options' => array(
								'none'                    => __( 'None', 'cartflows-pro' ),
								'transparent-fade'        => __( 'Fade Background', 'cartflows-pro' ),
								'transparent-fill-top'    => __( 'Fill Background From Top', 'cartflows-pro' ),
								'transparent-fill-bottom' => __( 'Fill Background From Bottom', 'cartflows-pro' ),
								'transparent-fill-left'   => __( 'Fill Background From Left', 'cartflows-pro' ),
								'transparent-fill-right'  => __( 'Fill Background From Right', 'cartflows-pro' ),
								'transparent-fill-center' => __( 'Fill Background Vertical', 'cartflows-pro' ),
								'transparent-fill-diagonal' => __( 'Fill Background Diagonal', 'cartflows-pro' ),
								'transparent-fill-horizontal' => __( 'Fill Background Horizontal', 'cartflows-pro' ),
							),
						),
						'threed_button_options'      => array(
							'type'    => 'select',
							'label'   => __( 'Hover Styles', 'cartflows-pro' ),
							'default' => 'threed_down',
							'options' => array(
								'threed_down'    => __( 'Move Down', 'cartflows-pro' ),
								'threed_up'      => __( 'Move Up', 'cartflows-pro' ),
								'threed_left'    => __( 'Move Left', 'cartflows-pro' ),
								'threed_right'   => __( 'Move Right', 'cartflows-pro' ),
								'animate_top'    => __( 'Animate Top', 'cartflows-pro' ),
								'animate_bottom' => __( 'Animate Bottom', 'cartflows-pro' ),
							),
						),
					),
				),
				'formatting' => array(
					'title'  => __( 'Structure', 'cartflows-pro' ),
					'fields' => array(
						'width'                    => array(
							'type'    => 'select',
							'label'   => __( 'Width', 'cartflows-pro' ),
							'default' => 'auto',
							'options' => array(
								'auto'   => _x( 'Auto', 'Width.', 'cartflows-pro' ),
								'full'   => __( 'Full Width', 'cartflows-pro' ),
								'custom' => __( 'Custom', 'cartflows-pro' ),
							),
							'toggle'  => array(
								'auto'   => array(
									'fields' => array( 'align', 'mob_align' ),
								),
								'full'   => array(
									'fields' => array(),
								),
								'custom' => array(
									'fields' => array( 'align', 'mob_align', 'custom_width', 'custom_height', 'padding_top_bottom', 'padding_left_right' ),
								),
							),
						),
						'align'                    => array(
							'type'    => 'align',
							'label'   => __( 'Alignment', 'cartflows-pro' ),
							'default' => 'center',
							'options' => array(
								'center' => __( 'Center', 'cartflows-pro' ),
								'left'   => __( 'Left', 'cartflows-pro' ),
								'right'  => __( 'Right', 'cartflows-pro' ),
							),
						),
						'mob_align'                => array(
							'type'    => 'align',
							'label'   => __( 'Mobile Alignment', 'cartflows-pro' ),
							'default' => 'center',
							'options' => array(
								'center' => __( 'Center', 'cartflows-pro' ),
								'left'   => __( 'Left', 'cartflows-pro' ),
								'right'  => __( 'Right', 'cartflows-pro' ),
							),
						),
						'button_padding_dimension' => array(
							'type'       => 'dimension',
							'label'      => __( 'Padding', 'cartflows-pro' ),
							'slider'     => true,
							'units'      => array( 'px' ),
							'responsive' => true,
							'preview'    => array(
								'type'      => 'css',
								'selector'  => 'a.cartflows-pro-bb__action-button,
								.cartflows-pro-bb__next-step-creative-button-wrap a, .cartflows-pro-bb__action-creative-button-wrap a:visited',
								'property'  => 'padding',
								'unit'      => 'px',
								'important' => true,
							),
						),
						'button_border'            => array(
							'type'    => 'border',
							'label'   => __( 'Border', 'cartflows-pro' ),
							'slider'  => true,
							'units'   => array( 'px' ),
							'preview' => array(
								'type'      => 'css',
								'selector'  => 'a.cartflows-pro-bb__action-button,
								.cartflows-pro-bb__next-step-creative-button-wrap a',
								'property'  => 'border',
								'unit'      => 'px',
								'important' => true,
							),
						),
						'border_hover_color'       => array(
							'type'        => 'color',
							'label'       => __( 'Border Hover Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type' => 'none',
							),
						),
						'custom_width'             => array(
							'type'      => 'unit',
							'label'     => __( 'Custom Width', 'cartflows-pro' ),
							'default'   => '200',
							'maxlength' => '3',
							'size'      => '4',
							'slider'    => true,
							'units'     => array( 'px' ),
						),
						'custom_height'            => array(
							'type'      => 'unit',
							'label'     => __( 'Custom Height', 'cartflows-pro' ),
							'default'   => '45',
							'maxlength' => '3',
							'size'      => '4',
							'slider'    => true,
							'units'     => array( 'px' ),
						),
						'padding_top_bottom'       => array(
							'type'        => 'unit',
							'label'       => __( 'Padding Top/Bottom', 'cartflows-pro' ),
							'placeholder' => '0',
							'maxlength'   => '3',
							'size'        => '4',
							'slider'      => true,
							'units'       => array( 'px' ),
						),
						'padding_left_right'       => array(
							'type'        => 'unit',
							'label'       => __( 'Padding Left/Right', 'cartflows-pro' ),
							'placeholder' => '0',
							'maxlength'   => '3',
							'size'        => '4',
							'slider'      => true,
							'units'       => array( 'px' ),
						),
						'border_radius'            => array(
							'type'      => 'unit',
							'label'     => __( 'Round Corners', 'cartflows-pro' ),
							'maxlength' => '3',
							'size'      => '4',
							'slider'    => true,
							'units'     => array( 'px' ),
						),
					),
				),
				'colors'     => array(
					'title'  => __( 'Colors', 'cartflows-pro' ),
					'fields' => array(
						'text_color'       => array(
							'type'        => 'color',
							'label'       => __( 'Text Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.cartflows-pro-bb__action-creative-button-wrap a, .cartflows-pro-bb__action-creative-button-wrap a *, .cartflows-pro-bb__action-button-description',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'text_hover_color' => array(
							'type'        => 'color',
							'label'       => __( 'Text Hover Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type' => 'none',
							),
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.cartflows-pro-bb__action-creative-button-wrap a:hover, .cartflows-pro-bb__action-creative-button-wrap a:hover *, .cartflows-pro-bb__action-creative-button-wrap a:hover .cartflows-pro-bb__action-button-description, .cartflows-pro-bb__action-creative-button-wrap a:hover ..cartflows-pro-bb__action-button-icon',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'bg_color'         => array(
							'type'        => 'color',
							'label'       => __( 'Background Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
						),
						'bg_hover_color'   => array(
							'type'        => 'color',
							'label'       => __( 'Background Hover Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type' => 'none',
							),
						),
						'hover_attribute'  => array(
							'type'    => 'select',
							'label'   => __( 'Apply Hover Color To', 'cartflows-pro' ),
							'default' => 'bg',
							'options' => array(
								'border' => __( 'Border', 'cartflows-pro' ),
								'bg'     => __( 'Background', 'cartflows-pro' ),
							),
							'width'   => '75px',
						),

					),
				),
			),
		),
		'creative_typography' => array(
			'title'    => __( 'Typography', 'cartflows-pro' ),
			'sections' => array(
				'typography' => array(
					'title'  => __( 'Button Settings', 'cartflows-pro' ),
					'fields' => array(
						'icon_size'   => array(
							'type'      => 'unit',
							'label'     => __( 'Icon Size', 'cartflows-pro' ),
							'slider'    => true,
							'units'     => array( 'px' ),
							'maxlength' => '30',
							'size'      => '5',
						),
						'button_typo' => array(
							'type'       => 'typography',
							'label'      => __( 'Typography', 'cartflows-pro' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.cartflows-pro-bb__next-step-creative-button',
							),
						),
					),
				),
			),
		),
	)
);
