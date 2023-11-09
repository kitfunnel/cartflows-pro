<?php
/**
 * Utils.
 *
 * @package CARTFLOWS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Utils.
 */
class Cartflows_Pro_Utils {

	/**
	 * Member Variable
	 *
	 * @var instance
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
	 * Clone step by ID
	 *
	 * @param int $post_id    Step id.
	 *
	 * @return int|bool
	 */
	public function clone_step( $post_id ) {

		global $wpdb;

		/**
		 * And all the original post data then
		 */
		$post = get_post( $post_id );

		/**
		 * If post data exists, create the post duplicate
		 */
		if ( isset( $post ) && null !== $post ) {

			/**
			 * Assign current user to be the new post author
			 */
			$current_user    = wp_get_current_user();
			$new_post_author = $current_user->ID;

			/**
			 * New post data array
			 */
			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => $post->post_status,
				'post_title'     => $post->post_title . ' Clone',
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order,
			);

			/**
			 * Insert the post
			 */
			$new_step_id = wp_insert_post( $args );

			/**
			 * Get all current post terms ad set them to the new post
			 */
			// returns array of taxonomy names for post type, ex array("category", "post_tag");.
			$taxonomies = get_object_taxonomies( $post->post_type );

			foreach ( $taxonomies as $taxonomy ) {

				$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );

				wp_set_object_terms( $new_step_id, $post_terms, $taxonomy, false );
			}

			/**
			 * Duplicate all post meta just in two SQL queries
			 */
			// @codingStandardsIgnoreStart
			$post_meta_infos = $wpdb->get_results(
				"SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id"
			);
			// @codingStandardsIgnoreEnd

			if ( ! empty( $post_meta_infos ) ) {

				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES ";

				$sql_query_sel = array();

				foreach ( $post_meta_infos as $meta_info ) {

					$meta_key = $meta_info->meta_key;

					if ( '_wp_old_slug' === $meta_key ) {
						continue;
					}

					$meta_value = addslashes( $meta_info->meta_value );

					$sql_query_sel[] = "($new_step_id, '$meta_key', '$meta_value')";
				}

				$sql_query .= implode( ',', $sql_query_sel );

				// @codingStandardsIgnoreStart
				$wpdb->query( $sql_query );
    			// @codingStandardsIgnoreEnd
			}

			/* Clear Page Builder Cache */
			wcf()->utils->clear_cache();

			/**
			 * Return new step id
			 */
			return $new_step_id;
		}

		return false;
	}

	/**
	 * We are using this function mostly in ajax on checkout page
	 *
	 * @param array $post_data post data.
	 * @return bool
	 */
	public function get_checkout_id_from_data( $post_data ) {

		if ( isset( $post_data['_wcf_checkout_id'] ) ) {

			$checkout_id = filter_var( wp_unslash( $post_data['_wcf_checkout_id'] ), FILTER_SANITIZE_NUMBER_INT );

			return intval( $checkout_id );
		}

		return false;
	}

	/**
	 * We are using this function mostly in ajax on checkout page
	 *
	 * @param array $post_data post data.
	 * @return bool
	 */
	public function get_flow_id_from_data( $post_data ) {

		if ( isset( $post_data['_wcf_flow_id'] ) ) {

			$flow_id = filter_var( wp_unslash( $post_data['_wcf_flow_id'] ), FILTER_SANITIZE_NUMBER_INT );

			return intval( $flow_id );
		}

		return false;
	}

	/**
	 * Fetch updated fragments after cart update.
	 *
	 * @param string $new_key product key.
	 * @param array  $data extra data.
	 * @return array.
	 */
	public function get_fragments( $new_key, $data = array() ) {

		$woocommerce_order_review = cartflows_woocommerce_order_review();

		ob_start();
		$this->woocommerce_checkout_place_order_button();
		$woocommerce_checkout_place_order_button = ob_get_clean();

		$response = array(
			'cart_total'    => WC()->cart->total,
			'cart_item_key' => $new_key,
			'fragments'     => apply_filters(
				'woocommerce_update_order_review_fragments',
				array(
					'.woocommerce-checkout-review-order-table' => $woocommerce_order_review,
					'#place_order' => $woocommerce_checkout_place_order_button,
				)
			),
		);

		if ( ! empty( $data ) ) {
			$response['cartflows_data'] = $data;
		}

		return $response;
	}

	/**
	 * Output the place order button on the checkout.
	 */
	public function woocommerce_checkout_place_order_button() {

		$order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'cartflows-pro' ) );

		include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/checkout-button/checkout-button.php';
	}

	/**
	 * Get offer data
	 *
	 * @param int $step_id step ID.
	 * @param int $selected_product_id product ID.
	 * @param int $input_qty qty.
	 * @param int $order_id parent order id.
	 * @return array
	 */
	public function get_offer_data( $step_id, $selected_product_id = '', $input_qty = '', $order_id = 0 ) {
		$data               = array();
		$subscription_types = array( 'subscription', 'variable-subscription', 'subscription_variation' );
		$amount_diff        = 0;
		$cancel_main_order  = false;
		$order              = '';
		$product_id         = 0;

		if ( empty( $selected_product_id ) ) {
			$offer_product = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-product' );

			if ( isset( $offer_product[0] ) ) {
				$product_id = $offer_product[0];
			}
		} else {
			$product_id = $selected_product_id;
		}

		$product_id = $this->get_variable_variation_product_id( $product_id );

		$product = wc_get_product( $product_id );

		if ( $product ) {

			$product_type   = $product->get_type();
			$original_price = wcf_pro_filter_price( $product->get_price( 'edit' ), $product_id, 'product' );
			$custom_price   = $original_price;

			if ( ! empty( $input_qty ) ) {
				/* Product Quantity */

				$product_qty = intval( $input_qty );
			} else {
				$product_qty = intval( wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-quantity' ) );
			}

			/* Offer Discount */
			$discount_type = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-discount' );

			if ( ! empty( $discount_type ) ) {

				$discount_value = floatval( wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-discount-value' ) );

				if ( 'discount_percent' === $discount_type ) {

					if ( $discount_value > 0 ) {
						$custom_price = $custom_price - ( ( $custom_price * $discount_value ) / 100 );
					}
				} elseif ( 'discount_price' === $discount_type ) {

					if ( $discount_value > 0 ) {
						$custom_price = $custom_price - $discount_value;
					}
				}
			}

			/* Set unit discount price */
			$unit_price     = $custom_price;
			$unit_price_tax = $custom_price;

			$display_price          = $unit_price;
			$display_original_price = $original_price;

			/* Set Product Price */
			$product_price = $custom_price;

			$tax_enabled = get_option( 'woocommerce_calc_taxes' );

			$shipping_fee = wcf_pro_filter_price( floatval( wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-flat-shipping-value' ) ) );

			// If tax rates are enabled.
			if ( 'yes' === $tax_enabled ) {

				// Price excluding tax.
				if ( wc_prices_include_tax() ) {
					$product_price     = wc_get_price_excluding_tax( $product, array( 'price' => $custom_price ) );
					$shipping_excl_tax = wc_get_price_excluding_tax( $product, array( 'price' => $shipping_fee ) );
				} else {
					$custom_price      = wc_get_price_including_tax( $product, array( 'price' => $custom_price ) );
					$shipping_incl_tax = wc_get_price_including_tax( $product, array( 'price' => $shipping_fee ) );
				}

				/* Set unit price with tax */
				$unit_price_tax = $custom_price;

				// Display the product price including/excluding tax settings.
				$display_price = $this->get_taxable_offer_product_price( $product, $display_price, $unit_price )['display_price'];

				// Need to update the display original price in case if upsell has the discount.
				$display_original_price = $this->get_taxable_offer_product_price( $product, $display_price, $original_price )['original_price'];
			}
			$shipping_incl_tax = isset( $shipping_incl_tax ) ? $shipping_incl_tax : $shipping_fee;
			$shipping_excl_tax = isset( $shipping_excl_tax ) ? $shipping_excl_tax : $shipping_fee;

			if ( in_array( $product_type, $subscription_types, true ) && class_exists( 'WC_Subscriptions_Product' ) ) {

				$subscription_signup_fees     = WC_Subscriptions_Product::get_sign_up_fee( $product );
				$subscription_signup_fees_new = 0;
				if ( 0 !== $subscription_signup_fees ) {
					if ( wc_prices_include_tax() ) {
						$subscription_signup_fees_new = wc_get_price_excluding_tax( $product, array( 'price' => $subscription_signup_fees ) );
						$product_price               += $subscription_signup_fees_new;
						$custom_price                += $subscription_signup_fees;
					} else {
						$subscription_signup_fees_new = wc_get_price_including_tax( $product, array( 'price' => $subscription_signup_fees ) );
						$product_price               += $subscription_signup_fees;
						$custom_price                += $subscription_signup_fees_new;
					}
				}

				if ( WC_Subscriptions_Product::get_trial_length( $product ) > 0 ) {
					if ( wc_prices_include_tax() ) {
						$product_price = $subscription_signup_fees_new;
						$custom_price  = $subscription_signup_fees;
					} else {
						$product_price = $subscription_signup_fees;
						$custom_price  = $subscription_signup_fees_new;
					}

					$original_price = $product_price;
					$unit_price     = $product_price;
					$unit_price_tax = $custom_price;
				}
			}

			if ( $product_qty > 1 ) {
				$custom_price  = $custom_price * $product_qty;
				$product_price = $product_price * $product_qty;
			}

			// add shipping fee to the custom price.
			$custom_price += $shipping_incl_tax;

			$is_cancal_main_order = get_post_meta( $step_id, 'wcf-replace-main-order', true );

			if ( 'yes' === $is_cancal_main_order && $this->is_separate_offer_order() && 0 !== $order_id ) {

				$order = wc_get_order( $order_id );

				if ( is_object( $order ) && ! $order->has_status( 'cancelled' ) ) {

					$order_total = $order->get_total();
					if ( $custom_price >= $order_total ) {
							$amount_diff       = $custom_price - $order_total;
							$custom_price      = $amount_diff;
							$cancel_main_order = true;
					}
				}
			}

			$data = array(
				'step_id'                 => $step_id,
				'id'                      => $product_id,
				'name'                    => $product->get_title(),
				'desc'                    => $product->get_description(),
				'qty'                     => $product_qty,
				'original_price'          => $original_price,
				'unit_price'              => $unit_price,
				'unit_price_tax'          => $unit_price_tax, // This is the single product price with tax required for paypal.
				'args'                    => array(          // This is the original product prices required while adding the product in cart, excluding tax.
					'subtotal' => $product_price,
					'total'    => $product_price,
				),
				'shipping_fee'            => $shipping_excl_tax,
				'shipping_fee_tax'        => $shipping_incl_tax,
				'price'                   => $custom_price,
				'url'                     => $product->get_permalink(),
				'total_unit_price_amount' => $unit_price_tax * $product_qty, // This is the order subtotal required for the paypal standard.
				'total'                   => $custom_price,
				'cancal_main_order'       => $cancel_main_order,
				'amount_diff'             => $amount_diff,
				'display_price'           => $display_price,
				'display_original_price'  => $display_original_price,
			);
		}

		return $data;
	}

	/**
	 * Get product price with tax.
	 *
	 * @param array $product product data.
	 * @param int   $display_price product price.
	 * @param int   $original_price original product price.
	 */
	public function get_taxable_offer_product_price( $product, $display_price, $original_price ) {

		$display_type = get_option( 'woocommerce_tax_display_cart' );

		if ( 'excl' === $display_type ) {
			$display_price  = wc_get_price_excluding_tax( $product, array( 'price' => $display_price ) );
			$original_price = wc_get_price_excluding_tax( $product, array( 'price' => $original_price ) );
		} else {
			$display_price  = wc_get_price_including_tax( $product, array( 'price' => $display_price ) );
			$original_price = wc_get_price_including_tax( $product, array( 'price' => $original_price ) );
		}

		$data = array(
			'display_price'  => $display_price,
			'original_price' => $original_price,
		);

		return $data;
	}

	/**
	 * Check if reference transaction for paypal is enabled.
	 *
	 * @return bool
	 */
	public function is_reference_transaction() {

		$settings = Cartflows_Helper::get_common_settings();

		if ( isset( $settings['paypal_reference_transactions'] ) && 'enable' == $settings['paypal_reference_transactions'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if offered product has zero value.
	 *
	 * @return bool
	 */
	public function is_zero_value_offered_product() {
		global $post;
		$step_id       = $post->ID;
		$offer_product = $this->get_offer_data( $step_id );
		if ( array_key_exists( 'total', $offer_product ) && 0 != $offer_product['total'] ) {
			return false;
		}
		return true;
	}

	/**
	 * Check if separate order.
	 *
	 * @return bool
	 */
	public function is_separate_offer_order() {

		$settings = Cartflows_Pro_Helper::get_offer_global_settings();

		if ( isset( $settings['separate_offer_orders'] ) && 'separate' === $settings['separate_offer_orders'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Get assets urls
	 *
	 * @return array
	 * @since 1.1.6
	 */
	public function get_assets_path() {

		$rtl = '';

		if ( is_rtl() ) {
			$rtl = '-rtl';
		}

		$file_prefix = '';
		$dir_name    = '';

		$is_min = apply_filters( 'cartflows_load_min_assets', false );

		if ( $is_min ) {
			$file_prefix = '.min';
			$dir_name    = 'min-';
		}

		$js_gen_path  = CARTFLOWS_PRO_URL . 'assets/' . $dir_name . 'js/';
		$css_gen_path = CARTFLOWS_PRO_URL . 'assets/' . $dir_name . 'css/';

		return array(
			'css'         => $css_gen_path,
			'js'          => $js_gen_path,
			'file_prefix' => $file_prefix,
			'rtl'         => $rtl,
		);
	}

	/**
	 * Get assets css url
	 *
	 * @param string $file file name.
	 * @return string
	 * @since 1.1.6
	 */
	public function get_css_url( $file ) {

		$assets_vars = wcf_pro()->assets_vars;

		$url = $assets_vars['css'] . $file . $assets_vars['rtl'] . $assets_vars['file_prefix'] . '.css';

		return $url;
	}

	/**
	 * Get assets js url
	 *
	 * @param string $file file name.
	 * @return string
	 * @since 1.1.6
	 */
	public function get_js_url( $file ) {

		$assets_vars = wcf_pro()->assets_vars;

		$url = $assets_vars['js'] . $file . $assets_vars['file_prefix'] . '.js';

		return $url;
	}

	/**
	 * Apply coupon.
	 *
	 * @param string $discount_type discount type.
	 * @param string $discount_coupon discount coupon.
	 * @return int
	 * @since 1.1.5
	 */
	public function apply_discount_coupon( $discount_type, $discount_coupon ) {

		$coupon_applied = false;

		if ( 'coupon' === $discount_type && ! empty( $discount_coupon ) ) {

			WC()->cart->add_discount( $discount_coupon );

			$coupon_applied = true;
		}

		return $coupon_applied;
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
	public function get_calculated_discount( $discount_type, $discount_value, $product_price ) {

		$custom_price = '';

		if ( ! empty( $discount_type ) ) {
			if ( 'discount_percent' === $discount_type ) {

				if ( $discount_value > 0 ) {
					$custom_price = $product_price - ( ( $product_price * $discount_value ) / 100 );
				}
			} elseif ( 'discount_price' === $discount_type ) {

				if ( $discount_value > 0 ) {
					$custom_price = $product_price - $discount_value;
				}
			}
		}

		return $custom_price;
	}

	/**
	 * Get selected product options.
	 *
	 * @param int   $checkout_id    Checkout id..
	 * @param array $saved_product_options Saved product options.
	 *
	 * @return array
	 */
	public function get_selected_product_options_data( $checkout_id = '', $saved_product_options = array() ) {

		if ( empty( $checkout_id ) ) {

			global $post;

			$checkout_id = $post->ID;
		}

		$checkout_products = wcf()->utils->get_selected_checkout_products( $checkout_id );

		if ( ! is_array( $saved_product_options ) ) {
			$saved_product_options = array();
		}

		if ( is_array( $checkout_products ) ) {

			foreach ( $checkout_products as $key => $value ) {

				if ( empty( $value['product'] ) ) {
					unset( $checkout_products[ $key ] );
					continue;
				}

				$unique_id = isset( $value['unique_id'] ) ? $value['unique_id'] : '';

				if ( isset( $saved_product_options[ $unique_id ] ) ) {
					$checkout_products[ $key ] = wp_parse_args( $saved_product_options[ $unique_id ], $checkout_products[ $key ] );
				} else {
					$default_data = array(
						'product_name'     => '',
						'product_subtext'  => '',
						'enable_highlight' => 'no',
						'highlight_text'   => '',
						'add_to_cart'      => 'yes',
					);

					$checkout_products[ $key ] = wp_parse_args( $default_data, $checkout_products[ $key ] );
				}
			}
		}

		return $checkout_products;
	}

	/**
	 * Get selected options, here get default variable variation product id based on default logic.
	 *
	 * @param int $product_id product id.
	 *
	 * @return array
	 */
	public function get_variable_variation_product_id( $product_id ) {

		$product = wc_get_product( $product_id );

		if ( $product && $product->is_type( 'variable' ) ) {

			$default_attributes = $product->get_default_attributes();

			if ( ! empty( $default_attributes ) ) {

				foreach ( $product->get_children() as $c_in => $variation_id ) {

					if ( 0 === $c_in ) {
						$product_id = $variation_id;
					}

					$single_variation = new WC_Product_Variation( $variation_id );

					if ( $default_attributes == $single_variation->get_attributes() ) {

						$product_id = $variation_id;
						break;
					}
				}
			} else {

				$product_childrens = $product->get_children();

				if ( is_array( $product_childrens ) ) {

					foreach ( $product_childrens  as $c_in => $c_id ) {

						$_child_product = wc_get_product( $c_id );

						if ( $_child_product->is_in_stock() && 'publish' === $_child_product->get_status() ) {
							$product_id = $c_id;
							break;
						}
					}
				}
			}
		}

		return $product_id;
	}
}
