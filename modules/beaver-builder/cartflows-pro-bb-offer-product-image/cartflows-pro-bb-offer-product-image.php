<?php
/**
 * Offer product image Module for Beaver Builder
 *
 * @package cartflows-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Offer product image Module for Beaver Builder
 *
 * @since 1.6.13
 */
class Cartflows_Pro_BB_Offer_Product_Image extends FLBuilderModule {

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
				'name'            => __( 'Offer Product Image', 'cartflows-pro' ),
				'description'     => __( 'Offer Product Image.', 'cartflows-pro' ),
				'category'        => __( 'Cartflows Modules', 'cartflows-pro' ),
				'group'           => __( 'Cartflows Modules', 'cartflows-pro' ),
				'dir'             => CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-offer-product-image/',
				'url'             => CARTFLOWS_PRO_URL . 'modules/beaver-builder/cartflows-pro-bb-offer-product-image/',
				'partial_refresh' => false, // Defaults to false and can be omitted.
				'icon'            => 'bb-product-image.svg',
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

		if ( '' !== $icon && file_exists( CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-offer-product-image/icon/' . $icon ) ) {

			return file_get_contents( CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-offer-product-image/icon/' . $icon ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		}

		return '';
	}

}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module(
	'Cartflows_Pro_BB_Offer_Product_Image',
	array(

		'style' => array(
			'title'    => __( 'Style', 'cartflows-pro' ),
			'sections' => array(
				'image_style'     => array(
					'title'  => __( 'Offer Product Image', 'cartflows-pro' ),
					'fields' => array(
						'align'               => array(
							'type'    => 'align',
							'label'   => __( 'Alignment', 'cartflows-pro' ),
							'default' => 'left',
							'options' => array(
								'center' => __( 'Center', 'cartflows-pro' ),
								'left'   => __( 'Left', 'cartflows-pro' ),
								'right'  => __( 'Right', 'cartflows-pro' ),
							),
							'preview' => array(
								'type'     => 'css',
								'selector' => '.woocommerce-product-gallery .woocommerce-product-gallery__image',
								'property' => 'text-align',
							),
						),
						'image_spacing'       => array(
							'type'      => 'unit',
							'label'     => __( 'Image Bottom Spacing', 'cartflows-pro' ),
							'slider'    => true,
							'units'     => array( 'px' ),
							'maxlength' => '100',
							'size'      => '5',
							'preview'   => array(
								'type'     => 'css',
								'selector' => '.woocommerce-product-gallery .woocommerce-product-gallery__wrapper',
								'property' => 'margin-bottom',
								'unit'     => 'px',
							),
						),
						'image_border_style'  => array(
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
									'fields' => array( 'image_border_width', 'image_border_color' ),
								),
								'dashed' => array(
									'fields' => array( 'image_border_width', 'image_border_color' ),
								),
								'dotted' => array(
									'fields' => array( 'image_border_width', 'image_border_color' ),
								),
								'double' => array(
									'fields' => array( 'image_border_width', 'image_border_color' ),
								),
							),
							'preview' => array(
								'type'     => 'css',
								'selector' => '.woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image img',
								'property' => 'border-style',
							),
						),
						'image_border_width'  => array(
							'type'        => 'unit',
							'label'       => __( 'Border Width', 'cartflows-pro' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '3',
							'size'        => '6',
							'placeholder' => '1',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image img',
								'property' => 'border-width',
								'unit'     => 'px',
							),
						),
						'image_border_color'  => array(
							'type'       => 'color',
							'label'      => __( 'Border Color', 'cartflows-pro' ),
							'default'    => '',
							'show_reset' => true,
							'show_alpha' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image img',
								'property' => 'border-color',
								'unit'     => 'px',
							),
						),
						'image_border_radius' => array(
							'type'        => 'unit',
							'label'       => __( 'Border Radius', 'cartflows-pro' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '3',
							'size'        => '6',
							'placeholder' => '0',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image img',
								'property' => 'border-radius',
								'unit'     => 'px',
							),
						),
					),
				),
				'thumbnail_style' => array(
					'title'  => __( 'Thumbnails', 'cartflows-pro' ),
					'fields' => array(
						'thumbnails_spacing'       => array(
							'type'      => 'unit',
							'label'     => __( 'Spacing between Thumbnails', 'cartflows-pro' ),
							'slider'    => true,
							'units'     => array( 'px' ),
							'maxlength' => '100',
							'size'      => '5',
						),
						'thumbnails_border_style'  => array(
							'type'    => 'select',
							'label'   => __( 'Thumbnails Border Style', 'cartflows-pro' ),
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
									'fields' => array( 'thumbnails_border_width', 'thumbnails_border_color' ),
								),
								'dashed' => array(
									'fields' => array( 'thumbnails_border_width', 'thumbnails_border_color' ),
								),
								'dotted' => array(
									'fields' => array( 'thumbnails_border_width', 'thumbnails_border_color' ),
								),
								'double' => array(
									'fields' => array( 'thumbnails_border_width', 'thumbnails_border_color' ),
								),
							),
							'preview' => array(
								'type'     => 'css',
								'selector' => '.woocommerce-product-gallery ol li img',
								'property' => 'border-style',
							),
						),
						'thumbnails_border_width'  => array(
							'type'        => 'unit',
							'label'       => __( 'Border Width', 'cartflows-pro' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '3',
							'size'        => '6',
							'placeholder' => '1',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-product-gallery ol li img',
								'property' => 'border-width',
								'unit'     => 'px',
							),
						),
						'thumbnails_border_color'  => array(
							'type'       => 'color',
							'label'      => __( 'Border Color', 'cartflows-pro' ),
							'default'    => '',
							'show_reset' => true,
							'show_alpha' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.woocommerce-product-gallery ol li img',
								'property' => 'border-color',
								'unit'     => 'px',
							),
						),
						'thumbnails_border_radius' => array(
							'type'        => 'unit',
							'label'       => __( 'Border Radius', 'cartflows-pro' ),
							'slider'      => true,
							'units'       => array( 'px' ),
							'maxlength'   => '3',
							'size'        => '6',
							'placeholder' => '0',
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.woocommerce-product-gallery ol li img',
								'property' => 'border-radius',
								'unit'     => 'px',
							),
						),
					),
				),
			),
		),
	)
);
