<?php
/**
 * Offer Yes/No Link Module for Beaver Builder
 *
 * @package action-Link
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Offer Yes/No Link Module for Beaver Builder
 *
 * @since 1.6.13
 */
class Cartflows_Pro_BB_Offer_Action_Link extends FLBuilderModule {
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
				'name'            => __( 'Offer Yes/No Link', 'cartflows-pro' ),
				'description'     => __( 'Offer Yes/No Link.', 'cartflows-pro' ),
				'category'        => __( 'Cartflows Modules', 'cartflows-pro' ),
				'group'           => __( 'Cartflows Modules', 'cartflows-pro' ),
				'dir'             => CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-offer-action-link/',
				'url'             => CARTFLOWS_PRO_URL . 'modules/beaver-builder/cartflows-pro-bb-offer-action-link/',
				'partial_refresh' => false, // Defaults to false and can be omitted.
				'icon'            => 'bb-action-link.svg',
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

		if ( '' !== $icon && file_exists( CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-offer-action-link/icon/' . $icon ) ) {

			return file_get_contents( CARTFLOWS_PRO_DIR . 'modules/beaver-builder/cartflows-pro-bb-offer-action-link/icon/' . $icon ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		}

		return '';
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
	'Cartflows_Pro_BB_Offer_Action_Link',
	array(

		'general' => array(
			'title'    => __( 'General', 'cartflows-pro' ),
			'sections' => array(
				'general' => array(
					'title'  => '',
					'fields' => array(
						'offer_action'  => array(
							'type'    => 'select',
							'label'   => __( 'Offer Action', 'cartflows-pro' ),
							'default' => 'accept_offer',
							'options' => array(
								'accept_offer' => __( 'Accept Offer', 'cartflows-pro' ),
								'reject_offer' => __( 'Reject Offer', 'cartflows-pro' ),
							),
						),
						'text'          => array(
							'type'        => 'text',
							'label'       => __( 'Link Text', 'cartflows-pro' ),
							'default'     => __( 'Offer Action', 'cartflows-pro' ),
							'preview'     => array(
								'type'     => 'text',
								'selector' => '.cartflows-pro-bb__action-button-text',
							),
							'connections' => array( 'string' ),
						),
						'icon'          => array(
							'type'        => 'icon',
							'label'       => __( 'Icon', 'cartflows-pro' ),
							'show_remove' => true,
							'preview'     => array(
								'type' => 'none',
							),
						),
						'icon_position' => array(
							'type'    => 'select',
							'label'   => __( 'Icon Position', 'cartflows-pro' ),
							'default' => 'before',
							'options' => array(
								'before' => __( 'Before Title', 'cartflows-pro' ),
								'after'  => __( 'After Title', 'cartflows-pro' ),
							),
							'preview' => array(
								'type' => 'none',
							),
						),
						'icon_spacing'  => array(
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
		'style'   => array(
			'title'    => __( 'Style', 'cartflows-pro' ),
			'sections' => array(
				'link_style' => array(
					'title'  => __( 'Style', 'cartflows-pro' ),
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
								'selector' => '.cartflows-pro-bb__action-link,
								.cartflows-pro-bb__action-link i,
								.cartflows-pro-bb__action-link .cartflows-pro-bb__action-link-text',
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
								'type'     => 'css',
								'selector' => '.cartflows-pro-bb__action-link:hover,
								.cartflows-pro-bb__action-link:hover i,
								.cartflows-pro-bb__action-link:hover .cartflows-pro-bb__action-link-text',
								'property' => 'color',
								'unit'     => 'px',
							),
						),
						'link_typography'  => array(
							'type'       => 'typography',
							'label'      => __( 'Typography', 'cartflows-pro' ),
							'responsive' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.cartflows-pro-bb__action-link',
							),
						),
					),
				),
			),
		),
	)
);
