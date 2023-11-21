<?php
/**
 * Checkout default post meta
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Meta Boxes setup
 */
class Cartflows_Pro_Checkout_Default_Meta {

	/**
	 * Instance
	 *
	 * @var object $instance class instance
	 */
	private static $instance;


	/**
	 * Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		add_filter( 'cartflows_checkout_meta_options', array( $this, 'meta_fields' ), 10, 2 );
	}

	/**
	 * Pro all meta fields
	 *
	 * @param array $fields checkout fields.
	 * @param int   $post_id post ID.
	 */
	public function meta_fields( $fields, $post_id ) {

		if ( ! cartflows_pro_is_active_license() && is_admin() ) {
			return $fields;
		}

		$fields['wcf-product-options-data'] = array(
			'default'  => array(),
			'sanitize' => 'FILTER_CARTFLOWS_PRO_CHECKOUT_PRODUCT_OPTIONS',
		);

		$fields['wcf-enable-product-options'] = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-product-opt-title'] = array(
			'default'  => __( 'Your Products', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-your-products-position'] = array(
			'default'  => 'after-customer',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		/* Product Selection */
		$fields['wcf-product-options'] = array(
			'default'  => 'force-all',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-enable-product-variation'] = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-product-variation-options'] = array(
			'default'  => 'inline',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-enable-product-quantity']  = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-show-product-images']      = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-checkout-discount-coupon'] = array(
			'default'  => array(),
			'sanitize' => 'FILTER_CARTFLOWS_ARRAY',
		);

		/* pre-checkout meta fields*/
		$fields['wcf-pre-checkout-offer'] = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-animate-browser-tab'] = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-animate-browser-tab-title'] = array(
			'default'  => __( '___Don\'t miss out the offer___', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-pre-checkout-offer-product']         = array(
			'default'  => array(),
			'sanitize' => 'FILTER_CARTFLOWS_ARRAY',
		);
		$fields['wcf-pre-checkout-offer-desc']            = array(
			'default'  => __( 'Write a few words about this awesome product and tell shoppers why they must get it. You may highlight this as "one time offer" and make it irresistible.', 'cartflows-pro' ),
			'sanitize' => 'FILTER_WP_KSES_POST',
		);
		$fields['wcf-pre-checkout-offer-popup-title']     = array(
			'default'  => __( '{first_name}, Wait! Your Order Is Almost Complete...', 'cartflows-pro' ),
			'sanitize' => 'FILTER_WP_KSES_POST',
		);
		$fields['wcf-pre-checkout-offer-popup-sub-title'] = array(
			'default'  => __( 'We have a special one time offer just for you.', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-pre-checkout-offer-product-title']   = array(
			'default'  => __( 'Product Name', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-pre-checkout-offer-popup-btn-text']  = array(
			'default'  => __( 'Yes, Add to My Order!', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-pre-checkout-offer-popup-skip-btn-text'] = array(
			'default'  => __( 'No, thanks!', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-pre-checkout-offer-discount']            = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-pre-checkout-offer-discount-value']      = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_NUMBER_FLOAT',
		);

		/* Checkout Offer Styling Options */
		$fields['wcf-pre-checkout-offer-bg-color'] = array(
			'default'  => '#eee',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);

		$fields['wcf-pre-checkout-offer-model-bg-color'] = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);

		$fields['wcf-pre-checkout-offer-title-color']    = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-pre-checkout-offer-subtitle-color'] = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);

		$fields['wcf-pre-checkout-offer-desc-color']   = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-pre-checkout-offer-navbar-color'] = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-pre-checkout-offer-button-color'] = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);

		/* Order Bump Options */

		$fields['wcf-order-bumps'] = array(
			'default'  => array(),
			'sanitize' => 'FILTER_DEFAULT',
		);

		/* Highlight product styles*/
		$fields['wcf-product-options-skin']  = array(
			'default'  => 'classic',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-yp-text-color']         = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-yp-bg-color']           = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-yp-hl-text-color']      = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-yp-hl-bg-color']        = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-yp-hl-border-color']    = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-yp-hl-flag-text-color'] = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		$fields['wcf-yp-hl-flag-bg-color']   = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);
		/* Custom Fields Options*/

		$fields['wcf-show-bump-arrow']         = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-show-bump-animate-arrow'] = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-advance-options-fields'] = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		/* Two Step Default Options */

		$fields['wcf-checkout-box-note']      = array(
			'default'  => 'yes',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-checkout-box-note-text'] = array(
			'default'  => __( 'Get Your FREE copy of CartFlows in just few steps.', 'cartflows-pro' ),
			'sanitize' => 'FILTER_WP_KSES_POST',
		);

		$fields['wcf-checkout-box-note-text-color'] = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);

		$fields['wcf-checkout-box-note-bg-color'] = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_COLOR',
		);

		$fields['wcf-checkout-step-one-title']     = array(
			'default'  => __( 'Shipping', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-checkout-step-one-sub-title'] = array(
			'default'  => __( 'Where to ship it?', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-checkout-step-two-title']         = array(
			'default'  => __( 'Payment', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-checkout-step-two-sub-title']     = array(
			'default'  => __( 'Of your order', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-checkout-offer-button-title']     = array(
			'default'  => __( 'For Special Offer Click Here', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);
		$fields['wcf-checkout-offer-button-sub-title'] = array(
			'default'  => __( 'Yes! I want this offer!', 'cartflows-pro' ),
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		/** Comment
		$fields['wcf-checkout-two-step-title-text-color'] = array(
		'default'  => '',
		'sanitize' => 'FILTER_DEFAULT',
		);
		$fields['wcf-checkout-step-bg-color']             = array(
		'default'  => '',
		'sanitize' => 'FILTER_DEFAULT',
		);
		$fields['wcf-checkout-two-step-section-bg-color'] = array(
		'default'  => '#ffffff',
		'sanitize' => 'FILTER_DEFAULT',
		);
		$fields['wcf-checkout-active-step-bg-color']      = array(
		'default'  => '',
		'sanitize' => 'FILTER_DEFAULT',
		); */
		$fields['wcf-checkout-two-step-section-width'] = array(
			'default'  => '500',
			'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
		);

		$fields['wcf-checkout-two-step-section-border'] = array(
			'default'  => 'solid',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-checkout-rules'] = array(
			'default'  => array(
				array(
					'group_id' => substr( md5( wp_rand() ), 2, 3 ),
					'rules'    => array(
						array(
							'rule_id'   => substr( md5( wp_rand() ), 2, 3 ),
							'condition' => 'cart_item',
							'operator'  => '',
							'value'     => '',
						),
					),
					'step_id'  => '',
				),
			),
			'sanitize' => 'FILTER_CARTFLOWS_PRO_CHECKOUT_RULES',
		);

		$fields['wcf-checkout-rules-option'] = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-checkout-rules-default-step'] = array(
			'default'  => '',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		return $fields;
	}

	/**
	 * Multiple order bump default meta.
	 */
	public function order_bump_default_meta() {

		$order_bump_fields = array(
			'id'                      => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'title'                   => array(
				'default'  => __( 'no title', 'cartflows-pro' ),
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'status'                  => array(
				'default'  => false,
				'sanitize' => 'FILTER_VALIDATE_BOOLEAN',
			),
			'product'                 => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
			),
			'quantity'                => array(
				'default'  => 1,
				'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
			),
			'default_state'           => array(
				'default'  => 'no',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'display_quantity_field'  => array(
				'default'  => 'no',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'discount_type'           => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'discount_value'          => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_NUMBER_FLOAT',
			),
			'discount_coupon'         => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'enable_show_image'       => array(
				'default'  => 'yes',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'ob_image_position'       => array(
				'default'  => 'left',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'ob_image_width'          => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
			),
			'product_image'           => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_URL',
			),
			'product_img_obj'         => array(
				'default'  => '',
				'sanitize' => 'FILTER_CARTFLOWS_OB_IMAGE',
			),
			'position'                => array(
				'default'  => 'after-order',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'checkbox_label'          => array(
				'default'  => __( 'Yes, I will take it!', 'cartflows-pro' ),
				'sanitize' => 'FILTER_WP_KSES_POST',
			),
			'action_element'          => array(
				'default'  => 'checkbox',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'title_text'              => array(
				'default'  => __( 'Here is Great Offer!', 'cartflows-pro' ),
				'sanitize' => 'FILTER_WP_KSES_POST',
			),
			'hl_text'                 => array(
				'default'  => __( 'One Time Offer', 'cartflows-pro' ),
				'sanitize' => 'FILTER_WP_KSES_POST',
			),
			'desc_text'               => array(
				'default'  => __( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut, quod hic expedita consectetur vitae nulla sint adipisci cupiditate at.', 'cartflows-pro' ),
				'sanitize' => 'FILTER_WP_KSES_POST',
			),
			'replace_product'         => array(
				'default'  => 'no',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'next_step'               => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
			),
			'style'                   => array(
				'default'  => 'style-1',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'border_style'            => array(
				'default'  => 'inherit',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'box_border_radius'       => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'box_border_width'        => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'box_shadow_color'        => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_COLOR',
			),
			'box_shadow_horizontal'   => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'box_shadow_vertical'     => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'box_shadow_blur'         => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'box_shadow_spread'       => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'show_image_mobile'       => array(
				'default'  => 'yes',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'show_arrow'              => array(
				'default'  => 'no',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'show_animation'          => array(
				'default'  => 'no',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'border_color'            => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_COLOR',
			),
			'bg_color'                => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_COLOR',
			),
			'label_color'             => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_COLOR',
			),
			'title_text_color'        => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_COLOR',
			),
			'label_bg_color'          => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_COLOR',
			),
			'desc_text_color'         => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_COLOR',
			),
			'hl_text_color'           => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_COLOR',
			),
			'button_text_color'       => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_COLOR',
			),
			'button_text_hover_color' => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_COLOR',
			),
			'button_color'            => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_COLOR',
			),
			'button_hover_color'      => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_COLOR',
			),
			'button_border_width'     => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'button_border_radius'    => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'button_border_style'     => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'button_border_color'     => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_COLOR',
			),
			'width'                   => array(
				'default'  => '100',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'label_border_style'      => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'label_border_width'      => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'label_border_radius'     => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'label_border_color'      => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_COLOR',
			),
			'rules'                   => array(
				'default'  => array(
					array(
						'group_id' => substr( md5( wp_rand() ), 2, 3 ),
						'rules'    => array(
							array(
								'rule_id'   => substr( md5( wp_rand() ), 2, 3 ),
								'condition' => 'cart_item',
								'operator'  => '',
								'value'     => '',
							),
						),
					),
				),
				'sanitize' => 'FILTER_SANITIZE_RULES',
			),
			'is_rule'                 => array(
				'default'  => 'no',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
		);

		return $order_bump_fields;
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Checkout_Default_Meta::get_instance();
