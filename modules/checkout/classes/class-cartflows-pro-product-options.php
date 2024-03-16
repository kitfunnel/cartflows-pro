<?php
/**
 * Variation Product Options
 *
 * @package carflows-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Variation
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Product_Options {

	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var object product_option
	 */
	private static $product_option_data = array();

	/**
	 * Member Variable
	 *
	 * @var object product_option
	 */
	private static $product_option = 'force-all';

	/**
	 * Member Variable
	 *
	 * @var object is_variation
	 */
	private static $is_variation = 'no';

	/**
	 * Member Variable
	 *
	 * @var object is_quantity
	 */
	private static $is_quantity = 'no';

	/**
	 * Member Variable
	 *
	 * @var object is_quantity
	 */
	private static $variation_as = 'inline';

	/**
	 * Member Variable
	 *
	 * @var object title
	 */
	private static $title = '';

	/**
	 * Member Variable
	 *
	 * @var object all_main_products
	 */
	private static $all_main_products = null;

	/**
	 * Member Variable
	 *
	 * @var object cart_products
	 */
	private static $cart_products = array();

	/**
	 * Member Variable
	 *
	 * @var object is_quantity
	 */
	private static $cart_items = array();

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

		if ( ! is_admin() ) {
			add_filter( 'global_cartflows_js_localize', array( $this, 'add_localize_vars' ), 10, 1 );
		}

		/* Product Selection Options */

		add_action( 'cartflows_checkout_form_before', array( $this, 'product_variation_option_position' ) );

		/* Force All Selection */
		add_action( 'wp_ajax_wcf_variation_selection', array( $this, 'variation_selection' ) );
		add_action( 'wp_ajax_nopriv_wcf_variation_selection', array( $this, 'variation_selection' ) );

		/* Multiple Selection */
		add_action( 'wp_ajax_wcf_multiple_selection', array( $this, 'multiple_selection' ) );
		add_action( 'wp_ajax_nopriv_wcf_multiple_selection', array( $this, 'multiple_selection' ) );

		/* Single Selection */
		add_action( 'wp_ajax_wcf_single_selection', array( $this, 'single_selection' ) );
		add_action( 'wp_ajax_nopriv_wcf_single_selection', array( $this, 'single_selection' ) );

		/* Quantity Ajax */
		add_action( 'wp_ajax_wcf_quantity_update', array( $this, 'quantity_update' ) );
		add_action( 'wp_ajax_nopriv_wcf_quantity_update', array( $this, 'quantity_update' ) );

		/* Wp Footer Action */
		add_action( 'wp_footer', array( $this, 'variation_popup' ) );

		// Quick view ajax.
		add_action( 'wp_ajax_wcf_woo_quick_view', array( $this, 'load_quick_view_product' ) );
		add_action( 'wp_ajax_nopriv_wcf_woo_quick_view', array( $this, 'load_quick_view_product' ) );

		/* Add TO Cart */
		add_action( 'wp_ajax_wcf_add_cart_single_product', array( $this, 'add_cart_single_product_ajax' ) );
		add_action( 'wp_ajax_nopriv_wcf_add_cart_single_product', array( $this, 'add_cart_single_product_ajax' ) );

	}

	/**
	 * Add localize variables.
	 *
	 * @param array $localize localize array.
	 *
	 * @since 1.0.0
	 */
	public function add_localize_vars( $localize ) {

		global $post;
		$step_id = $post->ID;

		$localize['wcf_bump_order_process_nonce']    = wp_create_nonce( 'wcf_bump_order_process' );
		$localize['wcf_update_order_bump_qty_nonce'] = wp_create_nonce( 'wcf_update_order_bump_qty' );
		$localize['wcf_multiple_selection_nonce']    = wp_create_nonce( 'wcf_multiple_selection' );
		$localize['wcf_single_selection_nonce']      = wp_create_nonce( 'wcf_single_selection' );
		$localize['wcf_quantity_update_nonce']       = wp_create_nonce( 'wcf_quantity_update' );
		$localize['wcf_variation_selection_nonce']   = wp_create_nonce( 'wcf_variation_selection' );
		$localize['wcf_quick_view_add_cart_nonce']   = wp_create_nonce( 'wcf_quick_view_add_cart' );

		$localize['is_product_options'] = wcf()->options->get_checkout_meta_value( $step_id, 'wcf-enable-product-options' );

		return $localize;
	}

	/**
	 * Product Variation option position
	 *
	 * @param int $checkout_id checkout id.
	 */
	public function product_variation_option_position( $checkout_id ) {

		$your_product_position = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-your-products-position' );

		self::$product_option_data['position'] = $your_product_position;

		switch ( $your_product_position ) {
			case 'after-customer':
				add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'product_selection_option' ) );
				break;

			case 'before-customer':
				add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'product_selection_option' ), 11, 1 );
				break;

			case 'before-order':
				add_action( 'cartflows_woocommerce_checkout_before_order_heading', array( $this, 'product_selection_option' ) );
				break;

			default:
				add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'product_selection_option' ) );
				break;
		}

	}

	/**
	 * Product selection options
	 */
	public function product_selection_option() {

		$checkout_id = false;

		if ( _is_wcf_checkout_type() ) {

			global $post;

			$checkout_id = $post->ID;
		} else {

			if ( is_admin() && isset( $_POST['id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
				$checkout_id = intval( $_POST['id'] );// phpcs:ignore WordPress.Security.NonceVerification
			}
		}

		if ( $checkout_id ) {

			$is_product_options = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-options' );

			if ( 'yes' !== $is_product_options ) {
				return;
			}

			$product_sel_option = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-options' );

			$is_product_variation = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-variation' );
			$is_product_quantity  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-quantity' );
			$variation_as         = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-variation-options' );
			$title                = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-opt-title' );

			self::$product_option = $product_sel_option;
			self::$is_variation   = $is_product_variation;
			self::$is_quantity    = $is_product_quantity;
			self::$variation_as   = $variation_as;
			self::$title          = $title;

			self::$product_option_data['selected_skin']  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-options-skin' );
			self::$product_option_data['product_images'] = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-show-product-images' );

			/* Preapre cart products variable */
			$this->prepare_cart_products();

			/* Print your product options markup */
			$this->show_your_product_options( $checkout_id, $product_sel_option );
		}
	}

	/**
	 * Prepare cart products
	 */
	public function prepare_cart_products() {

		$cart_products = array();

		$get_cart = WC()->cart->get_cart();

		foreach ( $get_cart as $cart_item_key => $cart_item ) {

			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$_product->quantity                   = $cart_item['quantity'];
				$cart_products[ $_product->get_id() ] = $_product;
			}
		}

		self::$cart_items    = $get_cart;
		self::$cart_products = $cart_products;
	}

	/**
	 * Get all selected products
	 *
	 * @param int $post_id post id.
	 * @return array product IDs.
	 */
	public function get_all_main_products( $post_id ) {

		if ( null === self::$all_main_products ) {

			$product_option_data = wcf()->options->get_checkout_meta_value( $post_id, 'wcf-product-options-data' );
			$checkout_products   = wcf_pro()->utils->get_selected_product_options_data( $post_id, $product_option_data );
			// merge two array.
			foreach ( $checkout_products as $in => $data ) {

				$unique_id = isset( $data['unique_id'] ) ? $data['unique_id'] : '';

				if ( isset( $product_option_data[ $unique_id ] ) ) {
					$checkout_products[ $in ] = wp_parse_args( $product_option_data[ $unique_id ], $checkout_products[ $in ] );
				}

				$checkout_products[ $in ]['product_id']       = $data['product'];
				$checkout_products[ $in ]['default_quantity'] = 1;

				if ( ! isset( $data['cart_item_key'] ) ) {
					$checkout_products[ $in ]['cart_item_key'] = '';
				}

				$_product = wc_get_product( $data['product'] );

				if ( ! empty( $_product ) ) {

					$checkout_products[ $in ]['variable']  = false;
					$checkout_products[ $in ]['variation'] = false;

					if ( $_product->is_type( 'variable' ) ) {

						$checkout_products[ $in ]['variable'] = true;
					}

					if ( $_product->is_type( 'variation' ) ) {

						$checkout_products[ $in ]['variation'] = true;
					}

					if ( $_product->is_type( 'variable-subscription' ) ) {

						$checkout_products[ $in ]['variable-subscription'] = true;
					}

					if ( $_product->is_type( 'subscription_variation' ) ) {

						$checkout_products[ $in ]['subscription_variation'] = true;
					}

					if ( $_product->is_type( 'subscription' ) ) {
						$checkout_products[ $in ]['subscription'] = true;
					}
				}
			}

			self::$all_main_products = $checkout_products;
		}

		return self::$all_main_products;
	}

	/*================ Force all products option ===========================================*/

	/**
	 * Quantity selection markup
	 *
	 * @param object $current_product product.
	 * @param array  $data product data.
	 * @return string
	 */
	public function force_all_product_markup( $current_product, $data ) {
		$output         = '';
		$selection_type = 'force-all';
		if ( $data['variable'] || $data['variation'] ) {

			if ( $data['variable'] ) {

				$current_variation_id = false;
				$show                 = false;
				$single_variation     = false;

				$default_attributes = $current_product->get_default_attributes();

				if ( ! empty( $default_attributes ) ) {

					foreach ( $current_product->get_children() as $c_in => $variation_id ) {

						$single_variation = new WC_Product_Variation( $variation_id );

						if ( ! $single_variation->is_in_stock() ) {
							continue;
						}

						if ( 'yes' === self::$is_variation ) {
							if ( 'inline' === self::$variation_as ) {

								$output .= $this->get_variation_product_markup( $current_product, $single_variation, 'inline', $data, $selection_type );

							} elseif ( 'popup' === self::$variation_as ) {

								if ( $default_attributes == $single_variation->get_attributes() ) {
									$output .= $this->get_variation_product_markup( $current_product, $single_variation, 'popup', $data, $selection_type );
									break;
								}
							}
						} elseif ( $default_attributes == $single_variation->get_attributes() ) {
							$output .= $this->get_variation_product_markup( $current_product, $single_variation, '', $data, $selection_type );
						}
					}
				} else {

					$product_childrens = $current_product->get_children();

					if ( is_array( $product_childrens ) && 'yes' === self::$is_variation ) {

						if ( 'inline' === self::$variation_as ) {
							foreach ( $product_childrens  as $c_in => $c_id ) {

								$single_variation = new WC_Product_Variation( $c_id );

								$output .= $this->get_variation_product_markup( $current_product, $single_variation, 'inline', $data, $selection_type );
							}
						} else {

							if ( isset( $product_childrens[0] ) ) {

								$single_variation_product = $this->get_variation_product_from_cart( $current_product->get_id() );
								$single_variation_product = $single_variation_product ? $single_variation_product : $product_childrens[0];
								$single_variation         = new WC_Product_Variation( $single_variation_product );

								$output .= $this->get_variation_product_markup( $current_product, $single_variation, 'popup', $data, $selection_type );
							}
						}
					} elseif ( isset( $product_childrens[0] ) ) {

						$single_variation = new WC_Product_Variation( $product_childrens[0] );

						$output .= $this->get_variation_product_markup( $current_product, $single_variation, '', $data, $selection_type );
					}
				}
			} else {
				$single_variation = $current_product;
				$parent_product   = wc_get_product( $current_product->get_parent_id() );

				$output .= $this->get_variation_product_markup( $parent_product, $single_variation, '', $data, $selection_type );
			}
		} else {
			$output .= $this->get_normal_product_markup( $current_product, $data, $selection_type );
		}

		return $output;
	}

	/*================ Single selection options =============================================*/

	/**
	 * Quantity selection markup
	 *
	 * @param object $current_product product obj.
	 * @param array  $data product data.
	 * @return string
	 */
	public function single_sel_product_markup( $current_product, $data ) {

		$output         = '';
		$selection_type = 'single-selection';
		if ( $data['variable'] || $data['variation'] ) {

			if ( $data['variable'] ) {

				$current_variation_id = false;
				$show                 = false;
				$single_variation     = false;

				$default_attributes = $current_product->get_default_attributes();

				if ( ! empty( $default_attributes ) ) {

					foreach ( $current_product->get_children() as $var_index => $variation_id ) {

						$single_variation = new WC_Product_Variation( $variation_id );

						if ( 'yes' === self::$is_variation ) {

							if ( 'popup' === self::$variation_as ) {

								if ( $default_attributes == $single_variation->get_attributes() ) {
									$output .= $this->get_variation_product_markup( $current_product, $single_variation, 'popup', $data, $selection_type );
									break;
								}
							} else {

								$output .= $this->get_variation_product_markup( $current_product, $single_variation, '', $data, $selection_type );
							}
						} elseif ( $default_attributes == $single_variation->get_attributes() ) {
							$output .= $this->get_variation_product_markup( $current_product, $single_variation, '', $data, $selection_type );
						}
					}
				} else {

					$product_childrens = $current_product->get_children();

					if ( is_array( $product_childrens ) && 'yes' === self::$is_variation ) {

						foreach ( $product_childrens  as $c_in => $c_id ) {

							$single_variation = new WC_Product_Variation( $c_id );

							if ( ! $single_variation->is_in_stock() || 'publish' !== $single_variation->get_status() ) {
								continue;
							}

							if ( 'popup' === self::$variation_as ) {

								$output .= $this->get_variation_product_markup( $current_product, $single_variation, 'popup', $data, $selection_type );
								break;

							} else {
								$output .= $this->get_variation_product_markup( $current_product, $single_variation, '', $data, $selection_type );
							}
						}
					} elseif ( isset( $product_childrens[0] ) ) {

						$single_variation = new WC_Product_Variation( $product_childrens[0] );

						$output .= $this->get_variation_product_markup( $current_product, $single_variation, '', $data, $selection_type );
					}
				}
			} else {
				$single_variation = $current_product;
				$parent_product   = wc_get_product( $current_product->get_parent_id() );

				$output .= $this->get_variation_product_markup( $parent_product, $single_variation, '', $data, $selection_type );
			}
		} else {
			$output .= $this->get_normal_product_markup( $current_product, $data, $selection_type );
		}

		return $output;
	}



	/*================ Multiple selection options ============================================*/

	/**
	 * Quantity selection markup
	 *
	 * @param object $current_product product.
	 * @param array  $data product data.
	 * @return string
	 */
	public function multiple_sel_product_markup( $current_product, $data ) {
		$output         = '';
		$selection_type = 'multiple-selection';
		if ( $data['variable'] || $data['variation'] ) {

			if ( $data['variable'] ) {

				$current_variation_id = false;
				$show                 = false;
				$single_variation     = false;

				$default_attributes = $current_product->get_default_attributes();

				if ( ! empty( $default_attributes ) ) {

					foreach ( $current_product->get_children() as $var_index => $variation_id ) {

						$single_variation = new WC_Product_Variation( $variation_id );

						if ( 'yes' === self::$is_variation ) {

							if ( 'popup' === self::$variation_as ) {
								if ( $default_attributes == $single_variation->get_attributes() ) {
									$output .= $this->get_variation_product_markup( $current_product, $single_variation, 'popup', $data, $selection_type );
									break;
								}
							} else {

								$output .= $this->get_variation_product_markup( $current_product, $single_variation, '', $data, $selection_type );
							}
						} elseif ( $default_attributes == $single_variation->get_attributes() ) {
							$output .= $this->get_variation_product_markup( $current_product, $single_variation, '', $data, $selection_type );
						}
					}
				} else {

					$product_childrens = $current_product->get_children();

					if ( is_array( $product_childrens ) && 'yes' === self::$is_variation ) {

						foreach ( $product_childrens  as $c_in => $c_id ) {

							$single_variation = new WC_Product_Variation( $c_id );

							if ( ! $single_variation->is_in_stock() || 'publish' !== $single_variation->get_status() ) {
								continue;
							}

							if ( 'popup' === self::$variation_as ) {

								$output .= $this->get_variation_product_markup( $current_product, $single_variation, 'popup', $data, $selection_type );
								break;

							} else {
								$output .= $this->get_variation_product_markup( $current_product, $single_variation, '', $data, $selection_type );
							}
						}
					} elseif ( isset( $product_childrens[0] ) ) {

						$single_variation = new WC_Product_Variation( $product_childrens[0] );

						$output .= $this->get_variation_product_markup( $current_product, $single_variation, '', $data, $selection_type );
					}
				}
			} else {
				$single_variation = $current_product;
				$parent_product   = wc_get_product( $current_product->get_parent_id() );

				$output .= $this->get_variation_product_markup( $parent_product, $single_variation, '', $data, $selection_type );
			}
		} else {
			$output .= $this->get_normal_product_markup( $current_product, $data, $selection_type );
		}

		return $output;
	}

	/*=====================================================================================*/

	/**
	 * Quantity update in cart
	 */
	public function quantity_update() {

		$nonce = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'wcf_quantity_update' ) ) {
			return;
		}

		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		$option         = isset( $_POST['option'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option'] ) ) : '';
		$product_id     = intval( $option['product_id'] );
		$_product       = wc_get_product( $product_id );
		$input_quantity = intval( $option['input_quantity'] );
		$input_quantity = ( 0 >= $input_quantity ) ? 1 : $input_quantity;
		$quantity       = intval( $option['quantity'] );
		$product_type   = sanitize_text_field( $option['type'] );
		$mode           = sanitize_text_field( $option['mode'] );
		$final_quantity = intval( $input_quantity * $quantity );
		$variation_id   = intval( $option['variation_id'] );
		$variations     = array();
		$cart_products  = array();
		$new_key        = '';

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			$cart_products[ $cart_item['product_id'] ] = $cart_item_key;

			if ( $cart_item['variation_id'] > 0 ) {
				$variations[ $cart_item['variation_id'] ] = $cart_item_key;
			}
		}

		if ( 'variation' === $product_type ) {

			$variation_id = intval( $option['variation_id'] );

			if ( ! isset( $variations[ $variation_id ] ) ) {
				WC()->cart->add_to_cart( $product_id, $final_quantity, $variation_id );
			}
		} else {
			if ( ! isset( $cart_products[ $product_id ] ) ) {
				WC()->cart->add_to_cart( $product_id, $final_quantity );
			}
		}

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			if ( in_array( $product_type, array( 'variation', 'variable-subscription', 'subscription_variation' ), true ) ) {
				if ( isset( $variations[ $variation_id ] ) && ( $cart_item['quantity'] != $final_quantity && ! $this->is_product_sold_individually( $cart_item ) ) && ( $cart_item['variation_id'] == $variation_id ) && ( $cart_item_key == $variations[ $variation_id ] ) ) {
					WC()->cart->set_quantity( $cart_item_key, $final_quantity );
				}

				if ( isset( $variations[ $variation_id ] ) && ( 0 == $final_quantity ) && ( $cart_item['variation_id'] == $variation_id ) ) {
					WC()->cart->remove_cart_item( $cart_item_key );
				}
			} else {

				if ( isset( $cart_products[ $product_id ] ) && ( $cart_item['quantity'] != $final_quantity && ! $this->is_product_sold_individually( $cart_item ) ) && ( $cart_item['product_id'] == $product_id ) && $cart_item_key == $option['cart_item_key'] ) {
					WC()->cart->set_quantity( $cart_item_key, $final_quantity );
				}

				if ( isset( $cart_products[ $product_id ] ) && ( 0 == $final_quantity ) && ( $cart_item['product_id'] == $product_id ) ) {
					WC()->cart->remove_cart_item( $cart_item_key );
				}
			}
		}

		/* Get display discounted data */
		$display_discount_data = $this->calculate_input_discount_data( $option['original_price'], $option['discounted_price'], $final_quantity );

		$data = array(
			'display_quantity'         => $final_quantity,
			'display_price'            => $display_discount_data['display_price'],
			'display_discount_value'   => $display_discount_data['discount_value'],
			'display_discount_percent' => $display_discount_data['discount_percent'],
		);

		if ( in_array( $product_type, array( 'subscription', 'variable-subscription', 'subscription_variation' ), true ) ) {
			$data['subscription_price'] = wc_price( $option['subscription_price'] * $final_quantity );
			$data['sign_up_fee']        = wc_price( $option['sign_up_fee'] * $final_quantity );
		}

		do_action( 'wcf_after_quantity_update', $product_id );
		wcf_update_the_checkout_transient( $option['checkout_id'] );
		wp_send_json( wcf_pro()->utils->get_fragments( $new_key, $data ) );
	}

	/************** Ajax *************************************************************************/

	/**
	 * Force All Selection
	 */
	public function variation_selection() {

		$nonce = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'wcf_variation_selection' ) ) {
			return;
		}

		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		$option         = isset( $_POST['option'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option'] ) ) : '';
		$product_id     = intval( $option['product_id'] );
		$mode           = $option['mode'];
		$variation_id   = intval( $option['variation_id'] );
		$input_quantity = intval( $option['input_quantity'] );
		$final_quantity = $input_quantity * $option['quantity'];
		$_product       = wc_get_product( $product_id );
		$type           = sanitize_text_field( $option['type'] );
		$variation      = '';

		$assigned_products = $this->get_all_main_products( $option['checkout_id'] );

		foreach ( $assigned_products as $key => $value ) {
			if ( ( intval( $value['product_id'] ) === $product_id || intval( $value['product_id'] ) === $variation_id ) && $option['unique_id'] === $value['unique_id'] ) {
				$discount_type  = ! empty( $value['discount_type'] ) ? $value['discount_type'] : '';
				$discount_value = ! empty( $value['discount_value'] ) ? $value['discount_value'] : '';
				if ( $variation_id > 0 ) {
					$variation      = wc_get_product( $variation_id );
					$_product_price = $variation->get_price();
				} else {
					$_product_price = $_product->get_price();
				}
			}
		}

		$custom_price   = wcf_pro()->utils->get_calculated_discount( $discount_type, $discount_value, $_product_price );
		$cart_item_data = array();

		// Set the Product's custom price even if it is zero. Discount may have applied.
		if ( Cartflows_Pro_Helper::is_valid_custom_price( $custom_price ) ) {
			$cart_item_data = array(
				'custom_price' => $custom_price,
				'option'       => $option,
			);
		}

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			if ( $cart_item['product_id'] === $product_id ) {

				WC()->cart->remove_cart_item( $cart_item_key );
			}
		}

		$new_key = WC()->cart->add_to_cart( $product_id, $final_quantity, $variation_id, array(), $cart_item_data );

		$product_prices_data = $this->get_calculated_product_prices( $_product, $_product_price, $custom_price, $variation );
		/* Get display discounted data */
		$display_discount_data = $this->calculate_input_discount_data( $product_prices_data['product_price'], $product_prices_data['custom_price'], $final_quantity );

		$data = array(
			'display_quantity'         => $final_quantity,
			'display_price'            => $display_discount_data['display_price'],
			'display_discount_value'   => $display_discount_data['discount_value'],
			'display_discount_percent' => $display_discount_data['discount_percent'],
		);

		if ( in_array( $type, array( 'variation', 'variable-subscription', 'subscription_variation' ), true ) ) {
			$data['subscription_price'] = wc_price( $option['subscription_price'] * $final_quantity );
			$data['sign_up_fee']        = wc_price( $option['sign_up_fee'] * $final_quantity );
		}

		do_action( 'wcf_after_force_all_selection', $variation_id );
		wcf_update_the_checkout_transient( $option['checkout_id'] );
		wp_send_json( wcf_pro()->utils->get_fragments( $new_key, $data ) );
	}

	/**
	 * Multiple Selection
	 */
	public function multiple_selection() {

		$nonce = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'wcf_multiple_selection' ) ) {
			return;
		}

		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		$option            = isset( $_POST['option'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option'] ) ) : '';
		$product_id        = intval( $option['product_id'] );
		$variation_id      = isset( $option['variation_id'] ) ? intval( $option['variation_id'] ) : 0;
		$type              = sanitize_text_field( $option['type'] );
		$is_checked        = sanitize_text_field( $option['checked'] );
		$input_quantity    = intval( $option['input_quantity'] );
		$input_quantity    = ( 0 >= $input_quantity ) ? 1 : $input_quantity;
		$final_quantity    = intval( $input_quantity * $option['quantity'] );
		$new_key           = '';
		$discount_type     = '';
		$discount_value    = '';
		$_product          = wc_get_product( $product_id );
		$_product_price    = $_product->get_price( $product_id );
		$assigned_products = $this->get_all_main_products( $option['checkout_id'] );
		$variation         = '';

		foreach ( $assigned_products as $key => $value ) {
			if ( ( intval( $value['product_id'] ) === $product_id || intval( $value['product_id'] ) === $variation_id ) && $option['unique_id'] === $value['unique_id'] ) {
				$discount_type  = ! empty( $value['discount_type'] ) ? $value['discount_type'] : '';
				$discount_value = ! empty( $value['discount_value'] ) ? $value['discount_value'] : '';
				if ( $variation_id > 0 ) {
					$variation      = wc_get_product( $variation_id );
					$_product_price = $variation->get_price();
				} else {
					$_product_price = $_product->get_price();
				}
			}
		}

		$custom_price   = wcf_pro()->utils->get_calculated_discount( $discount_type, $discount_value, $_product_price );
		$cart_item_data = array();

		// Set the Product's custom price even if it is zero. Discount may have applied.
		if ( Cartflows_Pro_Helper::is_valid_custom_price( $custom_price ) ) {
			$cart_item_data = array(
				'custom_price' => $custom_price,
				'option'       => $option,
			);
		}

		if ( 'yes' === $is_checked ) {

			if ( in_array( $type, array( 'variation', 'variable-subscription', 'subscription_variation' ), true ) ) {

				$new_key = WC()->cart->add_to_cart( $product_id, $final_quantity, $variation_id, array(), $cart_item_data );

			} else {

				$new_key = WC()->cart->add_to_cart( $product_id, $final_quantity, 0, array(), $cart_item_data );
			}

			do_action( 'wcf_after_multiple_selection', $product_id );
		} else {

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

				if ( 'variation' === $type ) {

					if ( $cart_item_key === $option['cart_item_key'] ) {
						WC()->cart->remove_cart_item( $cart_item_key );
					}
				} else {
					if ( $cart_item_key === $option['cart_item_key'] ) {
						WC()->cart->remove_cart_item( $cart_item_key );
					}
				}
			}
		}

		$product_prices_data = $this->get_calculated_product_prices( $_product, $_product_price, $custom_price, $variation );
		/* Get display discounted data */
		$display_discount_data = $this->calculate_input_discount_data( $product_prices_data['product_price'], $product_prices_data['custom_price'], $final_quantity );

		$data = array(
			'display_quantity'         => $final_quantity,
			'display_price'            => $display_discount_data['display_price'],
			'display_discount_value'   => $display_discount_data['discount_value'],
			'display_discount_percent' => $display_discount_data['discount_percent'],
		);

		if ( in_array( $type, array( 'variation', 'variable-subscription', 'subscription_variation' ), true ) ) {
			$data['subscription_price'] = wc_price( $option['subscription_price'] * $final_quantity );
			$data['sign_up_fee']        = wc_price( $option['sign_up_fee'] * $final_quantity );
		}

		do_action( 'wcf_after_multiple_selection', $product_id );
		wcf_update_the_checkout_transient( $option['checkout_id'] );
		wp_send_json( wcf_pro()->utils->get_fragments( $new_key, $data ) );
	}

	/**
	 * Single Selection
	 */
	public function single_selection() {

		$nonce = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'wcf_single_selection' ) ) {
			return;
		}

		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		$option            = isset( $_POST['option'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option'] ) ) : '';
		$product_id        = intval( $option['product_id'] );
		$variation_id      = isset( $option['variation_id'] ) ? intval( $option['variation_id'] ) : 0;
		$type              = sanitize_text_field( $option['type'] );
		$input_quantity    = intval( $option['input_quantity'] );
		$input_quantity    = ( 0 >= $input_quantity ) ? 1 : $input_quantity;
		$final_quantity    = intval( $option['quantity'] * $input_quantity );
		$checkout_id       = intval( $option['checkout_id'] );
		$discount_type     = '';
		$discount_value    = '';
		$_product          = wc_get_product( $product_id );
		$_product_price    = $_product->get_price( $product_id );
		$assigned_products = $this->get_all_main_products( $checkout_id );
		$variation         = '';

		$products = array();

		if ( ! empty( $assigned_products ) ) {

			foreach ( $assigned_products as $key => $value ) {

				if ( $value['variable'] ) {
					$temp_product = wc_get_product( $value['product_id'] );
					$children     = $temp_product->get_children();
					$products     = array_merge( $products, $children );
				}

				array_push( $products, intval( $value['product_id'] ) );
			}
		}

		$this->remove_products_from_cart( $products );

		// get value for calculate discount.
		foreach ( $assigned_products as $key => $value ) {
			if ( ( intval( $value['product_id'] ) === $product_id || intval( $value['product_id'] ) === $variation_id ) && $option['unique_id'] === $value['unique_id'] ) {
				$discount_type  = ! empty( $value['discount_type'] ) ? $value['discount_type'] : '';
				$discount_value = ! empty( $value['discount_value'] ) ? $value['discount_value'] : '';
				if ( $variation_id > 0 ) {
					$variation      = wc_get_product( $variation_id );
					$_product_price = $variation->get_price();
				} else {
					$_product_price = $_product->get_price();
				}
			}
		}

		$custom_price   = wcf_pro()->utils->get_calculated_discount( $discount_type, $discount_value, $_product_price, $variation );
		$cart_item_data = array();
		if ( Cartflows_Pro_Helper::is_valid_custom_price( $custom_price ) ) {
			$cart_item_data = array(
				'custom_price' => $custom_price,
			);
		}

		$new_key = '';

		if ( in_array( $type, array( 'variation', 'variable-subscription', 'subscription_variation' ), true ) ) {

			$new_key = WC()->cart->add_to_cart( $product_id, $final_quantity, $variation_id, array(), $cart_item_data );
		} else {

			$new_key = WC()->cart->add_to_cart( $product_id, $final_quantity, 0, array(), $cart_item_data );
		}

		$product_prices_data = $this->get_calculated_product_prices( $_product, $_product_price, $custom_price, $variation );

		/* Get display discounted data */
		$display_discount_data = $this->calculate_input_discount_data( $product_prices_data['product_price'], $product_prices_data['custom_price'], $final_quantity );

		$data = array(
			'display_quantity'         => $final_quantity,
			'display_price'            => $display_discount_data['display_price'],
			'display_discount_value'   => $display_discount_data['discount_value'],
			'display_discount_percent' => $display_discount_data['discount_percent'],
		);

		if ( in_array( $type, array( 'subscription', 'variable-subscription', 'subscription_variation' ), true ) ) {
			$data['subscription_price'] = wc_price( $option['subscription_price'] * $final_quantity );
			$data['sign_up_fee']        = wc_price( $option['sign_up_fee'] * $final_quantity );
		}

		do_action( 'wcf_after_single_selection', $product_id );
		wcf_update_the_checkout_transient( $checkout_id );
		wp_send_json( wcf_pro()->utils->get_fragments( $new_key, $data ) );
	}

	/**
	 * Calculate the product price with tax and sign up fee.
	 *
	 * @param array  $_product product array.
	 * @param array  $_product_price product price.
	 * @param array  $custom_price custome price.
	 * @param object $variation variation product.
	 * */
	public function get_calculated_product_prices( $_product, $_product_price, $custom_price, $variation ) {

		$tax_enabled = get_option( 'woocommerce_calc_taxes' );

		if ( $variation ) {
			$_product = $variation;
		}

		if ( 'yes' === $tax_enabled ) {
			$_product_price = $this->get_taxable_product_price( $_product, $_product_price );
			if ( '' !== $custom_price ) {
				$custom_price = $this->get_taxable_product_price( $_product, $custom_price );
			}
		}

		if ( wcf_pro()->is_wcs_active && $_product->is_type( 'subscription' ) || $_product->is_type( 'variable-subscription' ) || $_product->is_type( 'subscription_variation' ) ) {
			$_product_price += $this->get_subscription_sign_up_fee( $_product );

			if ( '' !== $custom_price ) {
				$custom_price += $this->get_subscription_sign_up_fee( $_product );
			}

			if ( ! empty( $_product_price ) && WC_Subscriptions_Product::get_trial_length( $_product ) > 0 ) {
				$_product_price = $this->get_subscription_sign_up_fee( $_product );
				$custom_price   = '';
			}
		}

		$product_prices_data = array(
			'product_price' => $_product_price,
			'custom_price'  => $custom_price,
		);

		return $product_prices_data;

	}


	/**
	 * Check product in cart and remove.
	 *
	 * @since 1.1.5
	 * @param array $products product array.
	 * @return void.
	 * */
	public function remove_products_from_cart( $products ) {
		if ( ! empty( $products ) ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

				$label = 'product_id';
				if ( 0 !== $cart_item['variation_id'] ) {

					$label = 'variation_id';
				}

				if ( in_array( $cart_item[ $label ], $products, true ) ) {

					WC()->cart->remove_cart_item( $cart_item_key );
				}
			}
		}
	}

	/**************************************** Popups *************************************/

	/**
	 * Variation Popup
	 */
	public function variation_popup() {

		if ( _is_wcf_checkout_type() ) {
			wp_enqueue_script( 'wc-add-to-cart-variation' );
			wp_enqueue_script( 'flexslider' );

			include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/quick-view/quick-view-modal.php';
		}
	}

	/**
	 * Load Quick View Product.
	 *
	 * @since 0.0.1
	 * @access public
	 */
	public function load_quick_view_product() {

		if ( ! isset( $_REQUEST['product_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			die();
		}

		// Action
		// add_action( 'cartflows_woo_quick_view_product_image', 'woocommerce_show_product_sale_flash', 10 ); */
		// Image.
		add_action( 'cartflows_woo_quick_view_product_image', array( $this, 'quick_view_product_images_markup' ), 20 );

		// Summary.
		add_action( 'cartflows_woo_quick_view_product_summary', array( $this, 'quick_view_product_content_structure' ), 10 );

		$product_id = intval( $_REQUEST['product_id'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// set the main wp query for the product.
		wp( 'p=' . $product_id . '&post_type=product' );

		ob_start();

		// load content template.
		include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/quick-view/quick-view-product.php';

		// Reason for PHPCS ignore: wp_kses_post will not work as it removing input tags hence ignoring below rule.
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		die();
	}

	/**
	 * Quick view product images markup.
	 */
	public function quick_view_product_images_markup() {

		include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/quick-view/quick-view-product-image.php';
	}

	/**
	 * Product Option title.
	 *
	 * @return title.
	 */
	public function product_option_title() {

		return apply_filters( 'cartflows_product_option_title', self::$title );
	}

	/**
	 * Choose a vatiation text.
	 *
	 * @return text.
	 */
	public function variation_popup_toggle_text() {

		return apply_filters( 'cartflows_variation_popup_toggle_text', __( 'Choose a Variation', 'cartflows-pro' ) );
	}

	/**
	 * Quick view product content structure.
	 */
	public function quick_view_product_content_structure() {

		global $product;

		$post_id = $product->get_id();

		$single_structure = apply_filters(
			'cartflows_quick_view_product_structure',
			array(
				'title',
				// 'ratings',
				'price',
				'short_desc',
				// 'meta',
				'add_cart',
			)
		);

		if ( is_array( $single_structure ) && ! empty( $single_structure ) ) {

			foreach ( $single_structure as $value ) {

				switch ( $value ) {
					case 'title':
						/**
						 * Add Product Title on single product page for all products.
						 */
						do_action( 'cartflows_quick_view_title_before', $post_id );
						woocommerce_template_single_title();
						do_action( 'cartflows_quick_view_title_after', $post_id );
						break;
					case 'price':
						/**
						 * Add Product Price on single product page for all products.
						 */
						do_action( 'cartflows_quick_view_price_before', $post_id );
						woocommerce_template_single_price();
						do_action( 'cartflows_quick_view_price_after', $post_id );
						break;
					case 'ratings':
						/**
						 * Add rating on single product page for all products.
						 */
						do_action( 'cartflows_quick_view_rating_before', $post_id );
						woocommerce_template_single_rating();
						do_action( 'cartflows_quick_view_rating_after', $post_id );
						break;
					case 'short_desc':
						do_action( 'cartflows_quick_view_short_description_before', $post_id );
						woocommerce_template_single_excerpt();
						do_action( 'cartflows_quick_view_short_description_after', $post_id );
						break;
					case 'add_cart':
						do_action( 'cartflows_quick_view_add_to_cart_before', $post_id );
						woocommerce_template_single_add_to_cart();
						do_action( 'cartflows_quick_view_add_to_cart_after', $post_id );
						break;
					case 'meta':
						do_action( 'cartflows_quick_view_category_before', $post_id );
						woocommerce_template_single_meta();
						do_action( 'cartflows_quick_view_category_after', $post_id );
						break;
					default:
						break;
				}
			}
		}
	}

	/**
	 * Handle adding variable products to the cart.
	 *
	 * @param array $form_data Form data of the user selction.
	 * @param int   $product_id Product ID to add to the cart.
	 * @param int   $final_quantity Input quantity to add to the cart.
	 * @param array $variation_id Variation ID to add to the cart.
	 * @param array $cart_item_data Extra data to add in the cart.
	 * @return array data.
	 */
	public function handle_add_to_cart_variation_attributes( $form_data, $product_id, $final_quantity, $variation_id, $cart_item_data ) {

		$data = array(
			'cart_key'       => '',
			'variation_data' => array(),
		);

		$variation_id       = empty( $form_data['variation_id'] ) ? '' : absint( wp_unslash( $form_data['variation_id'] ) );
		$quantity           = wc_stock_amount( wp_unslash( $final_quantity ) );
		$missing_attributes = array();
		$variations         = array();
		$adding_to_cart     = wc_get_product( $product_id );

		if ( ! $adding_to_cart ) {
			return false;
		}

		// If the $product_id was in fact a variation ID, update the variables.
		if ( $adding_to_cart->is_type( 'variation' ) ) {
			$variation_id   = $product_id;
			$product_id     = $adding_to_cart->get_parent_id();
			$adding_to_cart = wc_get_product( $product_id );

			if ( ! $adding_to_cart ) {
				return false;
			}
		}

		// Gather posted attributes.
		$posted_attributes = array();

		foreach ( $adding_to_cart->get_attributes() as $attribute ) {
			if ( ! $attribute['is_variation'] ) {
				continue;
			}
			$attribute_key = 'attribute_' . sanitize_title( $attribute['name'] );

			if ( isset( $form_data[ $attribute_key ] ) ) {
				if ( $attribute['is_taxonomy'] ) {
					$value = sanitize_title( wp_unslash( $form_data[ $attribute_key ] ) );
				} else {
					$value = html_entity_decode( wc_clean( wp_unslash( $form_data[ $attribute_key ] ) ), ENT_QUOTES, get_bloginfo( 'charset' ) );
				}

				$posted_attributes[ $attribute_key ] = $value;
			}
		}

		// Check the data we have is valid.
		$variation_data = wc_get_product_variation_attributes( $variation_id );

		foreach ( $adding_to_cart->get_attributes() as $attribute ) {
			if ( ! $attribute['is_variation'] ) {
				continue;
			}

			// Get valid value from variation data.
			$attribute_key = 'attribute_' . sanitize_title( $attribute['name'] );
			$valid_value   = isset( $variation_data[ $attribute_key ] ) ? $variation_data[ $attribute_key ] : '';

			/**
			 * If the attribute value was posted, check if it's valid.
			 *
			 * If no attribute was posted, only error if the variation has an 'any' attribute which requires a value.
			 */
			if ( isset( $posted_attributes[ $attribute_key ] ) ) {
				$value = $posted_attributes[ $attribute_key ];

				// Allow if valid or show error.
				if ( $valid_value === $value ) {
					$variations[ $attribute_key ] = $value;
				} elseif ( '' === $valid_value && in_array( $value, $attribute->get_slugs(), true ) ) {
					// If valid values are empty, this is an 'any' variation so get all possible values.
					$variations[ $attribute_key ] = $value;
				} else {
					/* translators: %s: Attribute name. */
					$data['error'] = sprintf( __( 'Invalid value posted for %s', 'cartflows-pro' ), wc_attribute_label( $attribute['name'] ) );
				}
			} elseif ( '' === $valid_value ) {
				$missing_attributes[] = wc_attribute_label( $attribute['name'] );
			}
		}

		if ( ! empty( $missing_attributes ) ) {
			/* translators: %s: Attribute name. */
			$data['error'] = sprintf( _n( '%s is a required field', '%s are required fields', count( $missing_attributes ), 'cartflows-pro' ), wc_format_list_of_items( $missing_attributes ) );
		}

		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );

		$data['variation_data'] = $variations;
		$data['cart_key']       = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations, $cart_item_data );

		return $data;
	}

	/**
	 * Single Product add to cart ajax request
	 *
	 * @since 1.1.0
	 *
	 * @return void.
	 */
	public function add_cart_single_product_ajax() {

		$nonce = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'wcf_quick_view_add_cart' ) ) {
			return;
		}

		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		$form_data = isset( $_POST['form_data'] ) ? sanitize_text_field( wp_unslash( $_POST['form_data'] ) ) : '';

		parse_str( $form_data, $form_data );

		$option         = isset( $_POST['option'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['option'] ) ) : '';
		$product_id     = intval( $option['product_id'] );
		$product_id     = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		$variation_id   = isset( $_POST['variation_id'] ) ? intval( $_POST['variation_id'] ) : 0;
		$input_quantity = intval( $option['input_quantity'] );
		$final_quantity = intval( $option['quantity'] * $input_quantity );

		$checkout_id       = intval( $option['checkout_id'] );
		$discount_type     = '';
		$discount_value    = '';
		$_product          = wc_get_product( $product_id );
		$_product_price    = $_product->get_price( $product_id );
		$assigned_products = $this->get_all_main_products( $checkout_id );
		$new_key           = '';
		$product_type      = '';
		$variation         = '';
		$trial_period      = '';
		$data              = array(
			'name'          => '',
			'product_id'    => $product_id,
			'variation_id'  => $variation_id,
			'added_to_cart' => 'no',
			'price'         => false,
		);

		if ( $variation_id ) {
			// get value for calculate discount.
			foreach ( $assigned_products as $key => $value ) {
				if ( ( intval( $value['product_id'] ) === $product_id || intval( $value['product_id'] ) === $variation_id ) && $option['unique_id'] === $value['unique_id'] ) {
					$discount_type  = ! empty( $value['discount_type'] ) ? $value['discount_type'] : '';
					$discount_value = ! empty( $value['discount_value'] ) ? $value['discount_value'] : '';
					$variation      = wc_get_product( $variation_id );
					$_product_price = $variation->get_price();
					$product_type   = $variation->get_type();
				}
			}

			$custom_price   = wcf_pro()->utils->get_calculated_discount( $discount_type, $discount_value, $_product_price );
			$cart_item_data = array();

			// Set the Product's custom price even if it is zero. Discount may have applied.
			if ( Cartflows_Pro_Helper::is_valid_custom_price( $custom_price ) ) {
				$cart_item_data = array(
					'custom_price' => $custom_price,
				);
			}

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

				if ( $cart_item['product_id'] === $product_id ) {

					WC()->cart->remove_cart_item( $cart_item_key );
				}
			}

			$add_cart_data = $this->handle_add_to_cart_variation_attributes( $form_data, $product_id, $final_quantity, $variation_id, $cart_item_data );
			$new_key       = $add_cart_data['cart_key'];

			$product_prices_data = $this->get_calculated_product_prices( $_product, $_product_price, $custom_price, $variation );
			/* Get display discounted data */
			$display_discount_data = $this->calculate_input_discount_data( $product_prices_data['product_price'], $product_prices_data['custom_price'], $final_quantity );

			/* Get selected attribute */
			$attribute_data = $this->get_selected_attributes( $_product, $variation, $add_cart_data['variation_data'] );
			$display_attr   = '';

			if ( is_array( $attribute_data ) && ! empty( $attribute_data ) ) {

				foreach ( $attribute_data as $att_slug => $att_data ) {
					$display_attr .= '<span class="wcf-att-inner">';
					$display_attr .= $att_data['label'] . ': ' . $att_data['value'];
					$display_attr .= '<span class="wcf-att-sep">,</span>';
					$display_attr .= '</span>';
				}
			}

			$signup_fee           = '';
			$subscription_price   = '';
			$subscription_details = '';

			$is_subscription = false;

			if ( 'subscription_variation' === $product_type && wcf_pro()->is_wcs_active ) {
				$is_subscription = true;

				$price = '' !== $custom_price ? $custom_price : $_product_price;

				$subscription_price = $this->get_taxable_product_price( $variation, $price );

				$subscription_price = $subscription_price * $final_quantity;
				$signup_fee         = $this->get_subscription_sign_up_fee( $variation ) * $final_quantity;

				$display_subscription_price   = wc_price( $subscription_price );
				$display_subscription_details = __( ' every ', 'cartflows-pro' ) . WC_Subscriptions_Product::get_interval( $variation ) . ' ' . WC_Subscriptions_Product::get_period( $variation );
				$display_signup_fee           = wc_price( $signup_fee );

				$_product_price = $subscription_price + $signup_fee;

				// If product has free trial then show sign up fee only.
				if ( WC_Subscriptions_Product::get_trial_length( $variation->get_id() ) > 0 ) {
					$_product_price = $signup_fee;
					$trial_period   = $this->get_subscription_trial_period( $variation );
				}
				$custom_price = '' !== $custom_price ? $custom_price + $signup_fee : $custom_price;
			}

			$data = array(
				'name'                     => $variation->get_name(),
				'display_attr'             => $display_attr,
				'product_id'               => $product_id,
				'variation_id'             => $variation_id,
				'variation_image'          => $variation->get_image(),
				'added_to_cart'            => 'yes',
				'original_price'           => $_product_price,
				'discounted_price'         => $custom_price,
				'price'                    => '<strong>' . wc_price( $variation->get_price() ) . '</strong>',
				'display_quantity'         => $final_quantity,
				'display_price'            => $display_discount_data['display_price'],
				'display_discount_value'   => $display_discount_data['discount_value'],
				'display_discount_percent' => $display_discount_data['discount_percent'],
			);

			if ( $is_subscription ) {
				$data['display_subscription_price']   = $display_subscription_price;
				$data['display_subscription_details'] = $display_subscription_details;
				$data['display_signup_fee']           = $display_signup_fee;

				$data['subscription_price']  = $subscription_price;
				$data['signup_fee']          = $signup_fee;
				$data['trial_period_string'] = $trial_period;
			}
		}

		do_action( 'wcf_after_quick_view_selection', $variation_id );
		wcf_update_the_checkout_transient( $checkout_id );
		wp_send_json( wcf_pro()->utils->get_fragments( $new_key, $data ) );
	}

	/**
	 * Get Cart product variation.
	 *
	 * @since 1.1.5
	 * @param int $product_id product_id.
	 * @return int variation_id.
	 * */
	public function get_variation_product_from_cart( $product_id ) {
		$variation_id = 0;
		$get_cart     = WC()->cart->get_cart();
		foreach ( $get_cart as $cart_item_key => $cart_item ) {

			if ( $cart_item['product_id'] == $product_id ) {
				$variation_id = $cart_item['variation_id'];
				break;
			}
		}
		return $variation_id;
	}

	/**
	 * Get Cart product quantity.
	 *
	 * @since 1.1.0
	 * @param int $product_id product_id.
	 * @return int $qty.
	 */
	public function get_cart_product_quantity( $product_id ) {
		$qty          = 1;
		$cart_product = isset( self::$cart_products[ $product_id ] ) ? self::$cart_products[ $product_id ] : null;
		if ( isset( $cart_product ) && $cart_product->quantity ) {
			$qty = $cart_product->quantity;
		}
		return $qty;
	}

	/**
	 * Calculate discount for product.
	 *
	 * @param string $discount_type discount type.
	 * @param int    $discount_value discount value.
	 * @param int    $product_price product price.
	 * @return int
	 * @since 1.1.5
	 */
	public function calculate_pre_checkout_discount( $discount_type, $discount_value, $product_price ) {

		$custom_price = '';

		if ( 'discount_percent' === $discount_type ) {

			if ( $discount_value > 0 ) {
				$custom_price = $product_price - ( ( $product_price * $discount_value ) / 100 );
			}
		} elseif ( 'discount_price' === $discount_type ) {

			if ( $discount_value > 0 ) {
				$custom_price = $product_price - $discount_value;
			}
		}

		return $custom_price;
	}


	/**
	 * Your product comman header section.
	 *
	 * @param int    $checkout_id checkout id.
	 * @param string $type optin type.
	 */
	public function show_your_product_options( $checkout_id, $type ) {

		$products = $this->get_all_main_products( $checkout_id );

		if ( ! is_array( $products ) || empty( $products ) ) {
			return;
		}

		include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/your-product/your-product-section.php';
	}


	/**
	 * Your product highlight class.
	 *
	 * @param array $data product data.
	 */
	public function get_product_highlight_data( $data ) {

		$highlight_data = array(
			'parent_class' => '',
			'html_markup'  => '',
		);

		if ( isset( $data['enable_highlight'] ) && 'yes' === $data['enable_highlight'] && ! empty( $data['highlight_text'] ) ) {
			$highlight_data = array(
				'parent_class' => ' wcf-highlight',
				'html_markup'  => '<span class="wcf-highlight-head">' . $data['highlight_text'] . '</span>',
			);
		}

		return $highlight_data;
	}

	/**
	 * Calculate display discounted data when user input quanity or select product.
	 *
	 * @param float $original_price original price.
	 * @param float $discounted_price discounted price.
	 * @param int   $final_quantity final quantity.
	 */
	public function calculate_input_discount_data( $original_price, $discounted_price, $final_quantity ) {

		$display_data = array(
			'discount_value'   => '',
			'discount_percent' => '',
		);

		$total_original_price = $original_price * $final_quantity;
		$save_value           = '';
		$save_percent         = '';

		if ( '' === $discounted_price ) {
			$display_data['display_price'] = wc_price( $total_original_price );
		} else {
			$total_discounted_price           = $discounted_price * $final_quantity;
			$display_data['display_price']    = '<del>' . wc_price( $total_original_price ) . '</del> <ins>' . wc_price( $total_discounted_price ) . '</ins>';
			$save_value                       = $total_original_price - $total_discounted_price;
			$save_percent                     = $save_value / $total_original_price * 100;
			$display_data['discount_value']   = wc_price( $save_value );
			$display_data['discount_percent'] = number_format( (float) $save_percent, 2, '.', '' ) . '%';
		}

		return $display_data;
	}

	/**
	 * Your product common price section.
	 *
	 * @param WC_Product $current_product product data.
	 * @param array      $data data.
	 * @param WC_Product $single_variation product data.
	 */
	public function your_product_price( $current_product, $data, $single_variation ) {

		if ( ! empty( $single_variation ) ) {
			$product_price = $single_variation->get_price();
			$product       = $single_variation;
		} else {
			$product_price = $current_product->get_price();
			$product       = $current_product;
		}

		$sign_up_fee            = 0;
		$is_tax                 = false;
		$original_product_price = $product_price;
		$subscription_price     = $product_price;
		$trial_period           = '';

		$tax_enabled = get_option( 'woocommerce_calc_taxes' );
		if ( 'yes' === $tax_enabled ) {
			$is_tax                 = true;
			$custom_price           = $this->get_taxable_product_price( $product, $product_price );
			$original_product_price = $custom_price;
		}

		$is_subscription_product = $product->is_type( 'subscription' ) || ( isset( $data['variable-subscription'] ) && $data['variable-subscription'] ) || ( isset( $data['subscription_variation'] ) && $data['subscription_variation'] );
		if ( $is_subscription_product && wcf_pro()->is_wcs_active ) {
			$sign_up_fee            = $this->get_subscription_sign_up_fee( $product );
			$subscription_price     = $original_product_price;
			$original_product_price = $original_product_price + $sign_up_fee;

			// If product has free trial then show sign up fee only.
			if ( WC_Subscriptions_Product::get_trial_length( $product->get_id() ) > 0 ) {
				$original_product_price = ! empty( $sign_up_fee ) ? $sign_up_fee : $original_product_price;
				$trial_period           = $this->get_subscription_trial_period( $product );
			}
		}

		$total_original_price = $original_product_price * $data['quantity'];
		$original_price       = wc_price( $total_original_price );
		$discount_type        = $data['discount_type'];
		$quantity             = intval( $data['quantity'] );

		$discounted_price       = '';
		$total_discounted_price = '';
		$save_value             = '';
		$save_percent           = '';

		if ( ! empty( $discount_type ) && is_numeric( $data['discount_value'] ) ) {

			$discount_value     = wcf_pro_filter_price( $data['discount_value'] );
			$discounted_price   = $this->calculate_pre_checkout_discount( $discount_type, $discount_value, $product_price );
			$discounted_price   = $is_tax ? $this->get_taxable_product_price( $product, $discounted_price ) : $discounted_price;
			$subscription_price = $discounted_price;

			if ( $is_subscription_product && wcf_pro()->is_wcs_active ) {
				/**
				 * Default: signup fees is zero.
				 *
				 * Case 1: Return the signup fees as it is if the subscription product has a free trial period.
				 * Case 2: Add the signup fees and discounted price if there is no trial period to a subscription product.
				 * Case 3: In case of non subscription product, the signup fees will be zero and only discount_price will be forwarded.
				 */
				$discounted_price = WC_Subscriptions_Product::get_trial_length( $product->get_id() ) > 0 ? $sign_up_fee : $discounted_price + $sign_up_fee;
			}


			$total_discounted_price = $discounted_price * $quantity;
			$save_value             = $total_original_price - $total_discounted_price;

			/**
			 * Calculate the discount percentage only if there is a discount.
			 * Save value is not empty and greater than zero.
			 *
			 * P.S: We don't show the product amount with tax. They are displayed without tax irrespective of tax setting in WooCommerce.
			 * */
			if ( ! empty( $save_value ) ) {
				$original_price = wc_format_sale_price( $original_price, $total_discounted_price );
				$save_percent   = $save_value / $total_original_price * 100;
				$save_value     = wc_price( $save_value );
				$save_percent   = number_format( (float) $save_percent, 2, '.', '' ) . '%';
			}
		}

		$product_id   = $current_product->get_id();
		$variation_id = 0 !== $single_variation ? $single_variation->get_id() : 0;

		if ( $is_subscription_product ) {
			$product_type = 'subscription';
		} elseif ( $variation_id > 0 ) {
			$product_type = 'variation';
		} else {
			$product_type = 'simple';
		}

		if ( isset( $data['variable-subscription'] ) && $data['variable-subscription'] ) {
			$product_type = 'variable-subscription';
		}

		if ( isset( $data['subscription_variation'] ) && $data['subscription_variation'] ) {
			$product_type = 'subscription_variation';
		}

		$sel_data = array(
			'product_id'             => $product_id,
			'variation_id'           => $variation_id,
			'type'                   => $product_type,
			'unique_id'              => $data['unique_id'],
			'mode'                   => 'quantity',
			'highlight_text'         => isset( $data['highlight_text'] ) ? $data['highlight_text'] : '',
			'quantity'               => $data['quantity'],
			'default_quantity'       => $data['default_quantity'],
			'original_price'         => $original_product_price,
			'discounted_price'       => $discounted_price,
			'total_discounted_price' => $total_discounted_price,
			'currency'               => get_woocommerce_currency_symbol(),
			'cart_item_key'          => $data['cart_item_key'],
			'save_value'             => $save_value,
			'save_percent'           => $save_percent,
			'sign_up_fee'            => $sign_up_fee,
			'subscription_price'     => $subscription_price,
			'trial_period_string'    => $trial_period,
		);

		$return = array(
			'sel_data'       => $sel_data,
			'qty'            => $quantity,
			/**
			 * This original price contains the HTML, with scratched original price and the discounted price.
			 * This option is used to display the value on product options section.
			 */
			'original_price' => $original_price,
		);

		return $return;
	}

	/**
	 * Get subscription product trial period and length.
	 *
	 * @param WC_Product $product product data.
	 */
	public function get_subscription_trial_period( $product ) {

		$free_trial_string = '';

		// Always send the product id instead of the product object as parameter to the WC_Subscriptions_Product functions.
		$subscription_trial_length = WC_Subscriptions_Product::get_trial_length( $product->get_id() );

		if ( 0 !== $subscription_trial_length ) {

			$subscription_trial_period = WC_Subscriptions_Product::get_trial_period( $product->get_id() );
			/* translators:  %1$s %2$s : trial length trial period */
			$free_trial_string = sprintf( __( ' with %1$s %2$s free trial ', 'cartflows-pro' ), $subscription_trial_length, $subscription_trial_period );
		}

		return $free_trial_string;
	}

	/**
	 * Get subscription product sign up fee.
	 *
	 * @param WC_Product $product product data.
	 */
	public function get_subscription_sign_up_fee( $product ) {

		// Always send the product id instead of the product object as parameter to the WC_Subscriptions_Product functions.
		$subscription_signup_fees = WC_Subscriptions_Product::get_sign_up_fee( $product->get_id() );

		$tax_enabled = get_option( 'woocommerce_calc_taxes' );

		if ( 'yes' === $tax_enabled ) {
			$subscription_signup_fees = $this->get_taxable_product_price( $product, $subscription_signup_fees );
		}

		return $subscription_signup_fees;
	}

	/**
	 * Get product price with tax.
	 *
	 * @param array $product product data.
	 * @param int   $product_price product price.
	 */
	public function get_taxable_product_price( $product, $product_price ) {

		$display_type = get_option( 'woocommerce_tax_display_cart' );

		if ( 'excl' === $display_type ) {
			$product_price = wc_get_price_excluding_tax( $product, array( 'price' => $product_price ) );
		} else {
			$product_price = wc_get_price_including_tax( $product, array( 'price' => $product_price ) );
		}

		return $product_price;
	}

	/**
	 * Get selected attribute data.
	 *
	 * @param WC_Product $current_product product data.
	 * @param WC_Product $single_variation product data.
	 * @param array      $form_data selected form data.
	 *
	 * @return array attributes.
	 */
	public function get_selected_attributes( $current_product, $single_variation, $form_data = array() ) {

		$parent_attributes = $current_product->get_attributes();
		$attribute_data    = array();

		foreach ( $parent_attributes as $att_key => $att_obj ) {

			$att_value = $single_variation->get_attribute( $att_key );

			if ( empty( $att_value ) && isset( $form_data[ 'attribute_' . $att_key ] ) ) {
				$att_value = $form_data[ 'attribute_' . $att_key ];
			}

			$attribute_data[ $att_key ] = array(
				'label' => wc_attribute_label( $att_obj->get_name() ),
				'value' => $att_value,
			);
		}

		return $attribute_data;
	}



	/**
	 * Normal product markup.
	 *
	 * @param array $current_product product data.
	 * @param array $data data.
	 * @param array $selection_type selection type.
	 */
	public function get_normal_product_markup( $current_product, $data, $selection_type ) {
		include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/your-product/normal-product-markup.php';
	}


	/**
	 * Variation peoduct markup.
	 *
	 * @param array $current_product product data.
	 * @param array $single_variation variation data.
	 * @param array $type show type.
	 * @param array $data products.
	 * @param array $selection_type selection type.
	 */
	public function get_variation_product_markup( $current_product, $single_variation, $type, $data, $selection_type ) {
		include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/your-product/variation-product-markup.php';
	}

	/**
	 * Check is the product enabled to sold individually or not.
	 *
	 * @since x.x.x
	 * @param array $cart_item Current Cart Items Data.
	 * @return boolean
	 */
	public function is_product_sold_individually( $cart_item ) {

		$product_id = isset( $cart_item['variation_id'] ) && ! empty( $cart_item['variation_id'] ) ? absint( $cart_item['variation_id'] ) : absint( $cart_item['product_id'] );

		if ( empty( $product_id ) ) {
			return false;
		}

		$product = wc_get_product( $product_id );

		if ( empty( $product ) ) {
			return false;
		}

		return $product->is_sold_individually();
	}
}
/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Product_Options::get_instance();
