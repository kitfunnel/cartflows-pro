<?php
/**
 * Checkout markup.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Checkout Markup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Checkout_Markup {



	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var is_divi_enabled
	 */
	public $divi_status = false;

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
	 *  Constructor
	 */
	public function __construct() {

		$this->include_required_class();

		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_checkout_fields' ), 10, 2 );

		/* Scripts */
		add_action( 'cartflows_checkout_scripts', array( $this, 'checkout_order_scripts' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'load_compatibility_scripts_for_pro' ), 102 );

		/** Filter
		add_filter( 'cartflows_checkout_layout_template', array( $this, 'include_checkout_template' ), 10, 1 ); */

		add_action( 'cartflows_checkout_form_before', array( $this, 'two_step_actions' ), 10, 1 );

		add_action( 'cartflows_checkout_after_configure_cart', array( $this, 'after_configure_cart' ), 10, 1 );

		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_billing_custom_order_meta' ), 10, 1 );
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'display_shipping_custom_order_meta' ), 10, 1 );

		add_filter( 'woocommerce_email_order_meta_fields', array( $this, 'custom_woo_email_order_meta_fields' ), 10, 3 );

		add_filter( 'global_cartflows_js_localize', array( $this, 'add_frontend_localize_scripts' ) );

		add_filter( 'woocommerce_form_field_hidden', array( $this, 'wcf_form_field_hidden' ), 10, 4 );

		add_action( 'cartflows_checkout_before_shortcode', array( $this, 'add_products_using_url' ), 10 );

		add_action( 'cartflows_checkout_before_shortcode', array( $this, 'apply_url_coupon' ), 30 ); // Execute this action after the above action to avoid applying the coupon early.

		add_filter( 'cartflows_selected_checkout_products', array( $this, 'update_the_checkout_products_data' ), 10, 2 );

		add_filter( 'cartflows_skip_configure_cart', array( $this, 'skip_cart_configuration' ), 10, 2 );
	}

	/**
	 * Skip the cart configuration when product is added from URL.
	 *
	 * @param bool $skip_cart is skip cart.
	 * @param int  $checkout_id checkout id.
	 */
	public function skip_cart_configuration( $skip_cart, $checkout_id ) {

		if ( ! empty( $_GET['wcf-add-to-cart'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			WC()->cart->empty_cart();
			$skip_cart = true;
		}

		return $skip_cart;
	}

	/**
	 * Add the product in cart through URL.
	 *
	 * @return void
	 */
	public function add_products_using_url() {

		$url_products = apply_filters( 'cartflows_add_to_cart_products_from_url', true );

		if ( $url_products ) {

			$products = isset( $_GET['wcf-add-to-cart'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_GET['wcf-add-to-cart'] ) ) ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$quantity = isset( $_GET['wcf-qty'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_GET['wcf-qty'] ) ) ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$checkout_id = _get_wcf_checkout_id();

			if ( ! empty( $products ) ) {
				foreach ( $products as $key => $product_id ) {

					$product_id = wcf_pro()->utils->get_variable_variation_product_id( $product_id );

					$product = wc_get_product( $product_id );

					if ( $product ) {
						$product_qunatity = isset( $quantity[ $key ] ) && ! empty( $quantity[ $key ] ) ? $quantity[ $key ] : 1;
						WC()->cart->add_to_cart( $product_id, $product_qunatity );
					}
				}

				Cartflows_Checkout_Markup::get_instance()->set_active_checkout_cookie_data( $checkout_id );
			}
		}
	}

	/**
	 * Merge product options array with checkout products.
	 *
	 * @param array $products products.
	 * @param int   $checkout_id checkout id.
	 */
	public function update_the_checkout_products_data( $products, $checkout_id ) {

		$products_options = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-options' );

		if ( 'yes' === $products_options && $products ) {

			$products_data             = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-options-data' );
			$products_option_condition = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-options' );

			foreach ( $products as $key => $data ) {

				$unique_key = $data['unique_id'];

				if ( $unique_key && isset( $products_data[ $unique_key ] ) ) {
					$products[ $key ] = wp_parse_args( $products_data[ $unique_key ], $data );
				}

				if ( ! isset( $products_data[ $unique_key ]['add_to_cart'] ) && 'single-selection' === $products_option_condition ) {
					$products[ $key ]['add_to_cart'] = 'no';
				}
			}

			$cart_value = array_column( $products, 'add_to_cart' );

			if ( ! in_array( 'yes', $cart_value, true ) ) {
				$products[0]['add_to_cart'] = 'yes';
			}
		}

		return $products;

	}

	/**
	 * Apply the coupon if available in url.
	 */
	public function apply_url_coupon() {

		$url_coupon = apply_filters( 'cartflows_apply_coupon_from_url', true );

		if ( $url_coupon ) {

			$coupon = isset( $_GET['coupon'] ) ? sanitize_text_field( wp_unslash( $_GET['coupon'] ) ) : false; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( $coupon ) {
				if ( WC()->cart->has_discount( $coupon ) ) {
					return;
				}
				WC()->cart->apply_coupon( $coupon );
			}
		}
	}

	/**
	 * Two Step Layout Actions.
	 *
	 * @param int $checkout_id checkout id.
	 * @since 1.1.9
	 */
	public function two_step_actions( $checkout_id ) {

		$checkout_layout = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-layout' );

		if ( 'two-step' == $checkout_layout ) {
			add_action( 'cartflows_add_before_main_section', array( $this, 'get_checkout_form_note' ), 10, 1 );

			add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'add_two_step_first_step_wrapper' ), 13 );

			add_action( 'cartflows_add_before_main_section', array( $this, 'add_two_step_second_step_wrapper' ), 11 );

			add_action( 'cartflows_add_before_main_section', array( $this, 'add_two_step_nav_menu' ), 12, 1 );

			add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'add_two_step_next_btn' ), 12 );

			add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'add_two_step_closing_div' ), 13 );

			add_action( 'cartflows_add_after_main_section', array( $this, 'add_two_step_closing_div' ), 14 );
		}
	}

	/**
	 * Hidden Field Actions.
	 *
	 * @param string $field field.
	 * @param string $key key.
	 * @param array  $args args.
	 * @param string $value value.
	 *
	 * @since 1.1.9
	 */
	public function wcf_form_field_hidden( $field = '', $key = '', $args = array(), $value = '' ) {
		$field = '<input type="hidden" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" class="input-hidden" /> ';
		return $field;
	}

	/**
	 * Send custom fields in the order email.
	 *
	 * @param array  $fields of fields.
	 * @param string $sent_to_admin domain name to send.
	 * @param object $order of order details.
	 */
	public function custom_woo_email_order_meta_fields( $fields, $sent_to_admin, $order ) {

		// Return if order not found.
		if ( ! $order ) {
			return $fields;
		}

		$order_id    = $order->get_id();
		$checkout_id = $order->get_meta( '_wcf_checkout_id' );

		if ( ! $checkout_id ) {
			return $fields;
		}

		// Get custom fields.
		$custom_fields = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );

		if ( 'yes' === $custom_fields ) {
			// Billing Fields & Values.
			$billing_fields = get_post_meta( $checkout_id, 'wcf_field_order_billing', true );

			if ( empty( $billing_fields ) ) {
				$billing_fields = get_post_meta( $checkout_id, 'wcf_fields_billing', true );
			}
			if ( is_array( $billing_fields ) ) {
				foreach ( $billing_fields as $field => $data ) {
					if ( ( isset( $data['custom'] ) && $data['custom'] ) && ( isset( $data['show_in_email'] ) && $data['show_in_email'] ) ) {
						$fields[ $field ] = array(
							'label' => $data['label'],
							'value' => $order->get_meta( '_' . $field ),
						);
					}
				}
			}

			// Shipping Fields & Values.
			$shipping_fields = get_post_meta( $checkout_id, 'wcf_field_order_shipping', true );

			if ( empty( $shipping_fields ) ) {
				$shipping_fields = get_post_meta( $checkout_id, 'wcf_fields_shipping', true );
			}
			if ( is_array( $shipping_fields ) ) {
				foreach ( $shipping_fields as $field => $data ) {
					if ( ( isset( $data['custom'] ) && $data['custom'] ) && ( isset( $data['show_in_email'] ) && $data['show_in_email'] ) ) {
						$fields[ $field ] = array(
							'label' => $data['label'],
							'value' => $order->get_meta( '_' . $field ),
						);
					}
				}
			}
		}

		return $fields;
	}

	/**
	 * After configure cart.
	 *
	 * @param int $checkout_id checkout id.
	 */
	public function after_configure_cart( $checkout_id ) {

		$discount_coupon = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-discount-coupon' );

		if ( is_array( $discount_coupon ) && ! empty( $discount_coupon ) ) {
			$discount_coupon = reset( $discount_coupon );
		}

		if ( ! empty( $discount_coupon ) ) {
			$show_coupon_msg = apply_filters( 'cartflows_show_applied_coupon_message', true );

			if ( ! $show_coupon_msg ) {
				add_filter( 'woocommerce_coupon_message', '__return_empty_string' );
			}

			WC()->cart->add_discount( $discount_coupon );

			if ( ! $show_coupon_msg ) {
				remove_filter( 'woocommerce_coupon_message', '__return_empty_string' );
			}
		}
	}

	/**
	 *  Add markup classes
	 *
	 * @return void
	 */
	public function include_required_class() {

		include_once CARTFLOWS_PRO_CHECKOUT_DIR . 'classes/class-cartflows-pre-checkout-offer-product.php';
		include_once CARTFLOWS_PRO_CHECKOUT_DIR . 'classes/class-cartflows-pro-product-options.php';
		include_once CARTFLOWS_PRO_CHECKOUT_DIR . 'classes/class-cartflows-pro-checkout-tab-animation.php';
		include_once CARTFLOWS_PRO_CHECKOUT_DIR . 'classes/class-cartflows-pro-checkout-rules.php';

		/* Start-Plus-Feature */
		if ( defined( 'CARTFLOWS_PRO_PLUGIN_TYPE' ) && in_array( CARTFLOWS_PRO_PLUGIN_TYPE, array( 'plus', 'pro' ), true ) ) {

			include_once CARTFLOWS_PRO_CHECKOUT_DIR . 'classes/class-cartflows-pro-order-bump-product.php';
			include_once CARTFLOWS_PRO_CHECKOUT_DIR . 'classes/class-cartflows-pro-order-bump-rules.php';
		}
		/* End-Plus-Feature */
	}

	/**
	 * Load shortcode scripts.
	 *
	 * @return void
	 */
	public function checkout_order_scripts() {

		global $post;

		if ( Cartflows_Compatibility::get_instance()->is_divi_enabled() ||
			Cartflows_Compatibility::get_instance()->is_divi_builder_enabled( $post->ID )
		) {
			$this->divi_status = true;
		}

		wp_enqueue_style( 'wcf-pro-checkout', wcf_pro()->utils->get_css_url( 'checkout-styles' ), '', CARTFLOWS_PRO_VER );
		wp_enqueue_style( 'wcf-pro-multistep-checkout', wcf_pro()->utils->get_css_url( 'multistep-checkout' ), '', CARTFLOWS_PRO_VER );

		wp_enqueue_script(
			'wcf-pro-checkout',
			wcf_pro()->utils->get_js_url( 'checkout' ),
			array( 'jquery' ),
			CARTFLOWS_PRO_VER,
			true
		);

		$checkout_id = $post->ID;

		$pre_checkout_offer = get_post_meta( $checkout_id, 'wcf-pre-checkout-offer', true );

		if ( 'yes' === $pre_checkout_offer ) {
			wp_enqueue_script(
				'wcf-pro-pre-checkout',
				wcf_pro()->utils->get_js_url( 'pre-checkout' ),
				array( 'jquery', 'jquery-ui-dialog' ),
				CARTFLOWS_PRO_VER,
				true
			);
		}

		wp_enqueue_style( 'dashicons' );

		$style = get_post_meta( $checkout_id, 'wcf-pro-dynamic-css', true );

		$css_version = get_post_meta( $checkout_id, 'wcf-pro-dynamic-css-version', true );

		if ( empty( $style ) || CARTFLOWS_ASSETS_VERSION !== $css_version ) {
			$style = $this->generate_style();
			update_post_meta( $checkout_id, 'wcf-pro-dynamic-css', $style );
			update_post_meta( $checkout_id, 'wcf-pro-dynamic-css-version', CARTFLOWS_ASSETS_VERSION );
		}

		wp_add_inline_style( 'wcf-pro-checkout', $style );
	}

	/**
	 * Load compatibility scripts.
	 *
	 * @return void
	 */
	public function load_compatibility_scripts_for_pro() {

		if ( ! _is_wcf_checkout_type() ) {
			return;
		}

		// Add DIVI Compatibility css if DIVI theme is enabled.
		if ( $this->divi_status ) {
			wp_enqueue_style( 'wcf-checkout-styles-divi', wcf_pro()->utils->get_css_url( 'checkout-styles-divi' ), '', CARTFLOWS_PRO_VER );
		}
	}

	/**
	 * Generate styles.
	 *
	 * @return string
	 */
	public function generate_style() {

		if ( ! _is_wcf_checkout_type() ) {
			return;
		}

		global $post;

		$checkout_id = $post->ID;

		$output = '';

		$enable_design_setting = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-design-settings' );

		$output .= $this->order_bump_dynamic_css( $checkout_id );

		/* Pre-checkout offer*/
		$is_pre_checkut_upsell = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-pre-checkout-offer' );

		$primary_color    = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-primary-color' );
		$base_font_family = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-base-font-family' );

		if ( 'yes' === $is_pre_checkut_upsell ) {
			$pre_checkout_bg_color       = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-pre-checkout-offer-bg-color' );
			$pre_checkout_model_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-pre-checkout-offer-model-bg-color' );
			$pre_checkout_title_color    = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-pre-checkout-offer-title-color' );
			$pre_checkout_subtitle_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-pre-checkout-offer-subtitle-color' );
			$pre_checkout_desc_color     = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-pre-checkout-offer-desc-color' );

			$navbar_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-pre-checkout-offer-navbar-color' );
			$button_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-pre-checkout-offer-button-color' );

			$pre_checkout_navbar_color = ! empty( $navbar_color ) ? $navbar_color : $primary_color;
			$pre_checkout_button_color = ! empty( $button_color ) ? $button_color : $primary_color;

			include CARTFLOWS_PRO_CHECKOUT_DIR . 'includes/pre-checkout-offer-dynamic-css.php';
		}
		/* Pre-checkout offer*/

		if ( 'yes' !== $enable_design_setting ) {
			return $output;
		}

		/* For single product quick view lightbox popup*/
		$r = '';
		$g = '';
		$b = '';

		$submit_tb_padding = '';
		$submit_lr_padding = '';

		$field_heading_color = '';
		$field_color         = '';
		$field_input_size    = '';
		$field_bg_color      = '';
		$field_border_color  = '';
		$field_tb_padding    = '';
		$field_lr_padding    = '';

		$input_font_family = '';
		$input_font_weight = '';

		$submit_button_height      = '';
		$submit_color              = '';
		$submit_bg_color           = $primary_color;
		$submit_border_color       = $primary_color;
		$submit_hover_color        = '';
		$submit_bg_hover_color     = $primary_color;
		$submit_border_hover_color = $primary_color;
		$section_heading_color     = $primary_color;
		$section_bg_color          = $primary_color;

		$is_advance_option = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-advance-options-fields' );

		$button_font_family  = '';
		$button_font_weight  = '';
		$heading_font_family = '';
		$heading_font_weight = '';
		$base_font_family    = $base_font_family;

		if ( 'yes' == $is_advance_option ) {
			// Buttons, inputs, title : size, font, color, width options.
			$section_heading_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-heading-color' );

			$section_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-section-bg-color' );

			$field_heading_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-heading-color' );

			$submit_tb_padding = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-tb-padding' );

			$submit_lr_padding = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-lr-padding' );

			$field_input_size = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-input-field-size' );

			$field_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-color' );

			$field_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-bg-color' );

			$field_border_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-border-color' );

			$field_tb_padding = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-tb-padding' );

			$field_lr_padding = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-lr-padding' );

			$submit_button_height = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-input-button-size' );

			$submit_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-color' );

			$submit_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-bg-color', $primary_color );

			$submit_border_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-border-color', $primary_color );

			$submit_hover_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-hover-color' );

			$submit_bg_hover_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-bg-hover-color', $primary_color );

			$submit_border_hover_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-border-hover-color', $primary_color );

			// Font and weight options.
			$button_font_family = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-button-font-family' );

			$button_font_weight = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-button-font-weight' );

			$input_font_family = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-input-font-family' );

			$input_font_weight = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-input-font-weight' );

			$heading_font_family = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-heading-font-family' );

			$heading_font_weight = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-heading-font-weight' );

		}

		if ( isset( $primary_color ) ) {
			list($r, $g, $b) = sscanf( $primary_color, '#%02x%02x%02x' );
		}

		$enable_product_options = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-options' );
		$product_option         = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-options' );
		$variation_option       = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-variation-options' );

		// Remove margin for perticular product variation option.
		if ( 'force-all' == $product_option && 'popup' == $variation_option ) {
			$output .= '.wcf-product-option-wrap .wcf-qty-options .wcf-qty-row .wcf-item-choose-options{
				margin: 5px 0 0 0px;
			}';
		}
		// Remove margin for perticular product variation option.

		// Highlight products styles.
		$yp_text_color      = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-yp-text-color' );
		$yp_bg_color        = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-yp-bg-color' );
		$yp_hl_text_color   = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-yp-hl-text-color' );
		$yp_hl_bg_color     = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-yp-hl-bg-color' );
		$yp_hl_border_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-yp-hl-border-color' );
		$yp_flag_text_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-yp-hl-flag-text-color' );
		$yp_flag_bg_color   = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-yp-hl-flag-bg-color' );

		// Bump Order.
		$bump_border_style         = '';
		$bump_border_color         = '';
		$bump_bg_color             = '';
		$bump_label_color          = '';
		$bump_label_bg_color       = '';
		$bump_desc_text_color      = '';
		$bump_hl_text_color        = '';
		$bump_blinking_arrow_color = '';

		/*
		 * Two step Layout
		 */

		// Get checkout page layout.
		$checkout_layout = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-layout' );

		if ( 'two-step' === $checkout_layout ) {

			$checkout_note_enabled = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-box-note', false );

			$step_two_width = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-two-step-section-width' );

			$two_step_box_text_color = '';

			$two_step_box_bg_color = '';

			$two_step_section_border = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-two-step-section-border' );

			if ( 'yes' == $checkout_note_enabled ) {
				$two_step_box_text_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-box-note-text-color', '' );

				$two_step_box_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-box-note-bg-color', $primary_color );
			}
		}
		/* Two step Layout */

		// Override CSS vars for product options.
		$output     .= '.wcf-product-option-wrap {';
			$output .= ! empty( $yp_text_color ) ? '--wcf-yp-text-color: ' . $yp_text_color . ';' : '';
			$output .= ! empty( $yp_bg_color ) ? '--wcf-yp-bg-color: ' . $yp_bg_color . ';' : '';
			$output .= ! empty( $yp_hl_text_color ) ? '--wcf-yp-hl-text-color: ' . $yp_hl_text_color . ';' : '';
			$output .= ! empty( $yp_hl_bg_color ) ? '--wcf-yp-hl-bg-color: ' . $yp_hl_bg_color . ';' : '';
			$output .= ! empty( $yp_hl_border_color ) ? '--wcf-yp-hl-border-color: ' . $yp_hl_border_color . ';' : '';
			$output .= ! empty( $yp_flag_text_color ) ? '--wcf-yp-hl-flag-text-color: ' . $yp_flag_text_color . ';' : '';
			$output .= ! empty( $yp_flag_bg_color ) ? '--wcf-yp-hl-flag-bg-color: ' . $yp_flag_bg_color . ';' : '';
		$output     .= '}';

		if ( $this->divi_status ) {

			include CARTFLOWS_PRO_CHECKOUT_DIR . 'includes/checkout-pro-dynamic-divi-css.php';

		} else {

			include CARTFLOWS_PRO_CHECKOUT_DIR . 'includes/checkout-pro-dynamic-css.php';
		}

		return $output;
	}

		/**
		 * To generate the dynamic css for multiple order bumps.
		 *
		 * @param  int $checkout_id checkout id.
		 */
	public function order_bump_dynamic_css( $checkout_id ) {

		$multi_ob = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bumps' );
		$ob_css   = '';

		if ( empty( $multi_ob ) && 'yes' !== get_post_meta( $checkout_id, 'wcf-order-bump-migrated', true ) && 'yes' === wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump' ) ) {

			$product_image     = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-image' );
			$show_image_mobile = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-show-bump-image-mobile' );
			$show_image_mobile = 'yes' === $show_image_mobile ? 'no' : 'yes';

			$old_ob = array(
				'status'            => true,
				'enable_show_image' => ! empty( $product_image ) ? 'yes' : 'no',
				'ob_image_position' => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-image-position' ),
				'ob_image_width'    => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-image-width' ),
				'show_image_mobile' => $show_image_mobile,
				'style'             => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-style' ),
				'border_color'      => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-border-color' ),
				'border_style'      => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-border-style' ),
				'bg_color'          => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-bg-color' ),
				'label_color'       => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-label-color' ),
				'label_bg_color'    => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-label-bg-color' ),
				'desc_text_color'   => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-desc-text-color' ),
				'hl_text_color'     => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-hl-text-color' ),
			);
			array_push( $multi_ob, $old_ob );
		}

		if ( is_array( $multi_ob ) && ! empty( $multi_ob ) ) {

			$default_meta    = Cartflows_Pro_Checkout_Default_Meta::get_instance()->order_bump_default_meta();
			$ob_default_meta = array();
			foreach ( $default_meta as $key => $value ) {
				$ob_default_meta[ $key ] = $value['default'];
			}

			foreach ( $multi_ob as $index => $order_bump_data ) {

				if ( ! $order_bump_data['status'] ) {
					continue;
				}

				$order_bump_data         = wp_parse_args( $order_bump_data, $ob_default_meta );
				$ob_id                   = isset( $order_bump_data['id'] ) ? $order_bump_data['id'] : '';
				$bump_border_style_value = isset( $order_bump_data['style'] ) ? $order_bump_data['style'] : 'style-1';

				// Define bump order layout.
				if ( 'inherit' !== $bump_border_style_value ) {
					$bump_border_style = $order_bump_data['border_style'];
				}

				if ( in_array( $order_bump_data['style'], array( 'style-1', 'style-2', 'style-3', 'style-4', 'style-5' ), true ) && 'inherit' === $bump_border_style ) {
					$bump_border_style = 'solid';
				}

				// Get bump order styling values.
				$order_bump_position    = isset( $order_bump_data['position'] ) ? $order_bump_data['position'] : 'after-order';
				$bump_border_color      = isset( $order_bump_data['border_color'] ) ? $order_bump_data['border_color'] : '';
				$bump_bg_color          = isset( $order_bump_data['bg_color'] ) ? $order_bump_data['bg_color'] : '';
				$bump_box_border_width  = isset( $order_bump_data['box_border_width'] ) ? $order_bump_data['box_border_width'] : '';
				$bump_box_border_radius = isset( $order_bump_data['box_border_radius'] ) ? $order_bump_data['box_border_radius'] : '';

				$bump_box_shadow_color      = isset( $order_bump_data['box_shadow_color'] ) ? $order_bump_data['box_shadow_color'] : '';
				$bump_box_shadow_horizontal = isset( $order_bump_data['box_shadow_horizontal'] ) ? $order_bump_data['box_shadow_horizontal'] : '';
				$bump_box_shadow_vertical   = isset( $order_bump_data['box_shadow_vertical'] ) ? $order_bump_data['box_shadow_vertical'] : '';
				$bump_box_shadow_blur       = isset( $order_bump_data['box_shadow_blur'] ) ? $order_bump_data['box_shadow_blur'] : '';
				$bump_box_shadow_spread     = isset( $order_bump_data['box_shadow_spread'] ) ? $order_bump_data['box_shadow_spread'] : '';

				$bump_label_color = isset( $order_bump_data['label_color'] ) ? $order_bump_data['label_color'] : '';

				if ( in_array( $order_bump_data['style'], array( 'style-3', 'style-4' ), true ) ) {
					$bump_label_color = isset( $order_bump_data['title_text_color'] ) ? $order_bump_data['title_text_color'] : '';
				}

				$bump_label_bg_color = isset( $order_bump_data['label_bg_color'] ) ? $order_bump_data['label_bg_color'] : '';

				$bump_desc_text_color = isset( $order_bump_data['desc_text_color'] ) ? $order_bump_data['desc_text_color'] : '';

				$bump_hl_text_color = isset( $order_bump_data['hl_text_color'] ) ? $order_bump_data['hl_text_color'] : '';

				$bump_button_text_color = isset( $order_bump_data['button_text_color'] ) ? $order_bump_data['button_text_color'] : '';
				$bump_button_color      = isset( $order_bump_data['button_color'] ) ? $order_bump_data['button_color'] : '';

				$bump_button_hover_color      = isset( $order_bump_data['button_hover_color'] ) ? $order_bump_data['button_hover_color'] : '';
				$bump_button_text_hover_color = isset( $order_bump_data['button_text_hover_color'] ) ? $order_bump_data['button_text_hover_color'] : '';

				$bump_button_border_width  = isset( $order_bump_data['button_border_width'] ) ? $order_bump_data['button_border_width'] : '';
				$bump_button_border_style  = isset( $order_bump_data['button_border_style'] ) ? $order_bump_data['button_border_style'] : '';
				$bump_button_border_color  = isset( $order_bump_data['button_border_color'] ) ? $order_bump_data['button_border_color'] : '';
				$bump_button_border_radius = isset( $order_bump_data['button_border_radius'] ) ? $order_bump_data['button_border_radius'] : '';

				$bump_blinking_arrow_color = '';

				$enabled_bump_image        = isset( $order_bump_data['enable_show_image'] ) ? $order_bump_data['enable_show_image'] : 'no';
				$bump_image_position       = isset( $order_bump_data['ob_image_position'] ) ? $order_bump_data['ob_image_position'] : 'left';
				$bump_image_width          = isset( $order_bump_data['ob_image_width'] ) ? $order_bump_data['ob_image_width'] : '';
				$enabled_bump_image_mobile = isset( $order_bump_data['show_image_mobile'] ) ? $order_bump_data['show_image_mobile'] : 'yes';

				$bump_width = isset( $order_bump_data['width'] ) ? $order_bump_data['width'] : '100';

				$label_border_style  = isset( $order_bump_data['label_border_style'] ) ? $order_bump_data['label_border_style'] : 'solid';
				$label_border_width  = isset( $order_bump_data['label_border_width'] ) ? $order_bump_data['label_border_width'] : 'solid';
				$label_border_radius = isset( $order_bump_data['label_border_radius'] ) ? $order_bump_data['label_border_radius'] : 'solid';

				$label_border_color = isset( $order_bump_data['label_border_color'] ) ? $order_bump_data['label_border_color'] : '';

				$bump_title_color = isset( $order_bump_data['title_text_color'] ) ? $order_bump_data['title_text_color'] : '';

				if ( 'inherit' === $label_border_style && 'style-5' === $order_bump_data['style'] ) {
					$label_border_style = '';
				} else {
					$label_border_style = 'solid';
				}

				if ( in_array( $order_bump_data['style'], array( 'style-4', 'style-5' ), true ) && 'inherit' === $bump_button_border_style ) {
					$bump_button_border_style = '';
				}

				include CARTFLOWS_PRO_CHECKOUT_DIR . 'includes/order-bump-dynamic-css.php';

			}
		}

		return $ob_css;
	}

	/**
	 * Save checkout fields.
	 *
	 * @param int   $order_id order id.
	 * @param array $posted posted data.
	 * @return void
	 */
	public function save_checkout_fields( $order_id, $posted ) {

		if ( isset( $_POST['_wcf_bump_products'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			// We are sanitizing the input after decoding.
			$order_bumps = json_decode( wp_unslash( $_POST['_wcf_bump_products'] ), true ); //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( ! empty( $order_bumps ) ) {

				$ob_data = array();

				foreach ( $order_bumps as $key => $order_bump ) {

					if ( is_array( $order_bump ) ) {
						foreach ( $order_bump as $data_key => $data ) {
							$ob_data[ sanitize_text_field( $key ) ][ sanitize_text_field( $data_key ) ] = sanitize_text_field( $data );
						}
					}
				}

				$order = wc_get_order( $order_id );

				if ( $order ) {
					$order->update_meta_data( '_wcf_bump_products', $ob_data );
					$order->save();
				}
			}
		}
	}

	/**
	 * Save checkout fields.
	 *
	 * @param string $layout_style layout style.
	 * @return link
	 */
	public function include_checkout_template( $layout_style ) {

		if ( ( 'two-step' === $layout_style ) || ( 'one-column' === $layout_style ) ) {
			return CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/embed/checkout-template-simple.php';
		}

		return $layout_style;
	}

	/**
	 * Display Two Step Nav Menu.
	 *
	 * @param string $layout_style layout style.
	 * @return markup
	 */
	public function add_two_step_nav_menu( $layout_style ) {

		if ( 'two-step' === $layout_style ) {
			// Get Checkout ID.
			global $post;

			$checkout_id = false;

			if ( _is_wcf_checkout_type() ) {
				$checkout_id = $post->ID;
			} else {

				if ( is_admin() && isset( $_POST['id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
					$checkout_id = intval( $_POST['id'] );// phpcs:ignore WordPress.Security.NonceVerification.Missing
				}
			}

			if ( ! $checkout_id ) {
				return;
			}

			// Get/Set default values.
			$is_note_enabled         = '';
			$checkout_note           = '';
			$step_one_title          = '';
			$step_one_sub_title      = '';
			$step_two_title          = '';
			$step_two_sub_title      = '';
			$two_step_section_border = '';

			// Get default values from the default meta to show if the advance option is not enabled.
			$all_fields = Cartflows_Default_Meta::get_instance()->get_checkout_fields( $checkout_id );

			$step_one_title     = $all_fields['wcf-checkout-step-one-title']['default'];
			$step_one_sub_title = $all_fields['wcf-checkout-step-one-sub-title']['default'];
			$step_two_title     = $all_fields['wcf-checkout-step-two-title']['default'];
			$step_two_sub_title = $all_fields['wcf-checkout-step-two-sub-title']['default'];

			// Get the values form the applied settings.
			// Get step titles.
			$step_one_title     = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-step-one-title', '' );
			$step_one_sub_title = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-step-one-sub-title', '' );
			$step_two_title     = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-step-two-title', '' );
			$step_two_sub_title = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-step-two-sub-title', '' );

			$two_step_section_border = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-two-step-section-border' );

			$two_step_html = '';

				$two_step_html .= "<div class='wcf-embed-checkout-form-nav wcf-border-" . esc_attr( $two_step_section_border ) . " '>";

				$two_step_html             .= "<ul class='wcf-embed-checkout-form-steps'>";
					$two_step_html         .= "<div class='steps step-one wcf-current'>";
						$two_step_html     .= "<a href='#customer_details'>";
							$two_step_html .= "<div class='step-number'>1</div>";

							$two_step_html .= "<div class='step-heading'>";

								$two_step_html .= "<div class='step-name'>" . esc_html( $step_one_title ) . '</div>';
								$two_step_html .= "<div class='step-sub-name'>" . esc_html( $step_one_sub_title ) . '</div>';

							$two_step_html .= '</div>';

						$two_step_html .= '</a>';
					$two_step_html     .= '</div>';

					$two_step_html         .= "<div class='steps step-two'>";
						$two_step_html     .= "<a href='#wcf-order-wrap'>";
							$two_step_html .= "<div class='step-number'>2</div>";

								$two_step_html .= "<div class='step-heading'>";

									$two_step_html .= "<div class='step-name'>" . esc_html( $step_two_title ) . '</div>';
									$two_step_html .= "<div class='step-sub-name'>" . esc_html( $step_two_sub_title ) . '</div>';

								$two_step_html .= '</div>';

						$two_step_html .= '</a>';
					$two_step_html     .= '</div>';

				$two_step_html .= '</ul>';
			$two_step_html     .= '</div>';

			echo wp_kses_post( $two_step_html );
		}

		return $layout_style;
	}

	/**
	 * Display Two Step note box.
	 *
	 * @param string $layout_style layout style.
	 * @return void
	 */
	public function get_checkout_form_note( $layout_style ) {

		// Get Checkout ID.
		$checkout_id = false;

		global $post;

		if ( $post ) {
			$checkout_id = $post->ID;
		} else {

			if ( is_admin() && isset( $_POST['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$checkout_id = intval( $_POST['id'] );// phpcs:ignore WordPress.Security.NonceVerification
			}
		}

		if ( ! $checkout_id ) {
			return;
		}

		$is_note_enabled = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-box-note', false );

		if ( 'yes' == $is_note_enabled ) {

			$checkout_note = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-box-note-text', '' );

			$two_step_note = '';

			$two_step_note .= "<div class='wcf-embed-checkout-form-note'>";

			$two_step_note .= '<p>' . wp_kses_post( $checkout_note ) . '</p>';

			$two_step_note .= '</div>';

			echo wp_kses_post( $two_step_note );
		}
	}

	/**
	 * Display Two Step Nav Next Button.
	 */
	public function add_two_step_next_btn() {

		global $post;

		$checkout_id = false;

		if ( _is_wcf_checkout_type() ) {
			$checkout_id = $post->ID;
		} else {

			if ( is_admin() && isset( $_POST['id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
				$checkout_id = intval( $_POST['id'] );//phpcs:ignore WordPress.Security.NonceVerification
			}
		}

		if ( ! $checkout_id ) {
			return;
		}

		$checkout_layout = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-layout' );

		$button_title = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-offer-button-title', '' );

		$button_sub_title = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-offer-button-sub-title', '' );

		if ( 'two-step' === $checkout_layout ) {
			$two_step_next_btn_html = '';

			$two_step_next_btn_html .= '<div class="wcf-embed-checkout-form-nav-btns">';

				$two_step_next_btn_html     .= '<a href="#wcf-order-wrap" class="button wcf-next-button" >';
					$two_step_next_btn_html .= '<span class="wcf-next-button-content">';

			if ( '' != $button_title ) {
						$two_step_next_btn_html     .= '<span class="wcf-next-button-icon-wrap">';
							$two_step_next_btn_html .= '<span class="dashicons dashicons-arrow-right-alt"></span>';
							$two_step_next_btn_html .= '<span class="wcf-button-text">' . esc_html( $button_title ) . '</span>';
						$two_step_next_btn_html     .= '</span>';
			}

			if ( '' != $button_sub_title ) {
						$two_step_next_btn_html .= '<span class="wcf-button-sub-text">' . esc_html( $button_sub_title ) . '</span>';
			}
					$two_step_next_btn_html .= '</span>';
				$two_step_next_btn_html     .= '</a>';

			$two_step_next_btn_html .= '</div>';

			echo wp_kses_post( $two_step_next_btn_html );
		}
	}

	/**
	 * Display billing custom field data on order page
	 *
	 * @param obj $order Order object.
	 * @return void
	 */
	public function display_billing_custom_order_meta( $order ) {

		if ( ! $order ) {
			return;
		}

		$checkout_id = $order->get_meta( '_wcf_checkout_id' );

		/* Custom Field To Do */
		$custom_fields = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );

		if ( 'yes' === $custom_fields ) {
			$output = '';

			$billing_fields = get_post_meta( $checkout_id, 'wcf_fields_billing', true );
			if ( is_array( $billing_fields ) ) {
				foreach ( $billing_fields as $field => $data ) {
					if ( isset( $data['custom'] ) && $data['custom'] ) {
						$output .= '<p><strong>' . $data['label'] . ':</strong> ' . $order->get_meta( '_' . $field ) . '</p>';
					}
				}
			}

			if ( '' !== $output ) {
				$output = '<h3>' . __( 'Billing Custom Fields', 'cartflows-pro' ) . '</h3>' . $output;
			}

			echo wp_kses_post( $output );
		}
	}

	/**
	 * Display shipping custom field data on order page
	 *
	 * @param obj $order Order object.
	 * @return void
	 */
	public function display_shipping_custom_order_meta( $order ) {

		if ( ! $order ) {
			return;
		}

		$order_id    = $order->get_id();
		$checkout_id = $order->get_meta( '_wcf_checkout_id' );

		/* Custom Field To Do */
		$custom_fields = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );

		if ( 'yes' === $custom_fields ) {
			$output = '';

			$shipping_fields = get_post_meta( $checkout_id, 'wcf_fields_shipping', true );
			if ( is_array( $shipping_fields ) ) {
				foreach ( $shipping_fields as $field => $data ) {
					if ( isset( $data['custom'] ) && $data['custom'] ) {
						$output .= '<p><strong>' . $data['label'] . ':</strong> ' . $order->get_meta( '_' . $field ) . '</p>';
					}
				}
			}

			if ( '' !== $output ) {
				$output = '<h3>' . __( 'Shipping Custom Fields', 'cartflows-pro' ) . '</h3>' . $output;
			}

			echo wp_kses_post( $output );
		}
	}

	/**
	 * Add second step opening dev
	 *
	 * @since 1.1.9
	 */
	public function add_two_step_second_step_wrapper() {
		echo "<div class='wcf-two-step-wrap'> ";
	}

	/**
	 * Add first step opening dev
	 *
	 * @since X.X.X
	 */
	public function add_two_step_first_step_wrapper() {
		echo '<div class="wcf-checkout-fields-wrapper">';
	}

	/**
	 * Add Startng & closing dev
	 *
	 * @since 1.1.9
	 */
	public function add_two_step_closing_div() {
		echo '</div> ';
	}


	/**
	 * Add localize variables.
	 *
	 * @since 1.1.5
	 * @param array $localize settings.
	 * @return array $localize settings.
	 */
	public function add_frontend_localize_scripts( $localize ) {

		$localize['allow_autocomplete_zipcode'] = apply_filters( 'cartflows_autocomplete_zip_data', 'no' );
		$localize['add_to_cart_text']           = __( 'Processing...', 'cartflows-pro' );
		$localize['wcf_refresh_checkout']       = apply_filters( 'cartflows_checkout_trigger_update_order_review', false );

		return $localize;
	}

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Checkout_Markup::get_instance();
