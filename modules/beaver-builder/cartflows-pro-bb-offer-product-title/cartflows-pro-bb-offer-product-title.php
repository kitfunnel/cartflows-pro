<?php
/**
 * Offer Product Title Module for Beaver Builder
 *
 * @package offer-product-title
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Product Offer Title Module for Beaver Builder
 *
 * @since 1.6.13
 */
class Cartflows_Pro_BB_Offer_Product_Title extends FLBuilderModule {
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
				'name'            => __( 'Offer Product Title', 'cartflows-pro' ),
				'description'     => __( 'Offer Product Title.', 'cartflows-pro' ),
				'category'        => __( 'Cartflows Modules', 'cartflows-pro' ),
				'group'           => __( 'Cartflows Modules', 'cartflows-pro' ),
				'dir'             => CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-offer-product-title/',
				'url'             => CARTFLOWS_PRO_URL . 'modules/beaver-builder/cartflows-pro-bb-offer-product-title/',
				'partial_refresh' => false, // Defaults to false and can be omitted.
				'icon'            => 'bb-offer-product-title.svg',
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

		if ( '' !== $icon && file_exists( CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-offer-product-title/icon/' . $icon ) ) {

			return file_get_contents( CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-offer-product-title/icon/' . $icon ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		}

		return '';
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module(
	'Cartflows_Pro_BB_Offer_Product_Title',
	array(

		'style' => array(
			'title'    => __( 'Style', 'cartflows-pro' ),
			'sections' => array(
				'link_style' => array(
					'title'  => __( 'Style', 'cartflows-pro' ),
					'fields' => array(
						'text_color' => array(
							'type'        => 'color',
							'label'       => __( 'Text Color', 'cartflows-pro' ),
							'default'     => '',
							'show_reset'  => true,
							'connections' => array( 'color' ),
							'show_alpha'  => true,
							'preview'     => array(
								'type'     => 'css',
								'selector' => '.cartflows-pro-bb__offer-product-title',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'typography' => array(
							'type'       => 'typography',
							'label'      => __( 'Typography', 'cartflows-pro' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.cartflows-pro-bb__offer-product-title',
							),
						),
					),
				),
			),
		),
	)
);
