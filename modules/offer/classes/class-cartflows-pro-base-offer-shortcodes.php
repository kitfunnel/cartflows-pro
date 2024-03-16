<?php
/**
 * Logger.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Base_Offer_Shortcodes {


	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var object _product
	 */
	private static $main_product = null;

	/**
	 * Member Variable
	 *
	 * @var object product_obj
	 */
	private static $main_product_data = null;

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

		/* Variation Product Shortcode */
		add_shortcode( 'cartflows_offer_product_title', array( $this, 'product_title' ) );
		add_shortcode( 'cartflows_offer_product_desc', array( $this, 'product_desc' ) );
		add_shortcode( 'cartflows_offer_product_short_desc', array( $this, 'product_short_desc' ) );
		add_shortcode( 'cartflows_offer_product_price', array( $this, 'product_price' ) );
		add_shortcode( 'cartflows_offer_product_price_diff', array( $this, 'product_diff_price' ) );

		add_shortcode( 'cartflows_offer_product_image', array( $this, 'product_image' ) );

		add_shortcode( 'cartflows_offer_product_variation', array( $this, 'variation_selector' ) );
		add_shortcode( 'cartflows_offer_product_quantity', array( $this, 'quantity_selector' ) );

		/* Offer Shortcode */
		add_shortcode( 'cartflows_offer_link_yes', array( $this, 'offer_link_yes_markup' ) );
		add_shortcode( 'cartflows_offer_link_no', array( $this, 'offer_link_no_markup' ) );

		/* Load woo templates from plugin */
		add_filter( 'woocommerce_locate_template', array( $this, 'override_woo_variable_template' ), 20, 3 );
	}


	/**
	 * Product selection options
	 */
	public function product_title() {

		if ( _is_wcf_base_offer_type() ) {

			$product = $this->get_offer_product( 'object' );

			if ( ! is_object( $product ) || null === $product ) {
				return;
			}

			return $product->get_title();
		}

	}

	/**
	 * Product selection options
	 */
	public function product_desc() {

		if ( _is_wcf_base_offer_type() ) {

			$product = $this->get_offer_product( 'object' );

			if ( ! is_object( $product ) || null === $product ) {
				return;
			}

			return $product->get_description();
		}
	}

	/**
	 * Product selection options
	 */
	public function product_short_desc() {

		if ( _is_wcf_base_offer_type() ) {

			$product = $this->get_offer_product( 'object' );

			if ( ! is_object( $product ) || null === $product ) {
				return;
			}

			return $product->get_short_description();
		}
	}

	/**
	 * Product selection options
	 */
	public function product_price() {

		if ( _is_wcf_base_offer_type() ) {

			global $post;

			$output = '';

			$context = 'raw';
			$product = $this->get_offer_product( 'object' );

			add_filter( 'woocommerce_price_trim_zeros', '__return_true' );

			if ( ! is_object( $product ) || null === $product ) {
				return;
			}

			$post_id = false;

			if ( $post ) {
				$post_id = $post->ID;
			} elseif ( is_admin() && isset( $_POST['id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
				$post_id = intval( $_POST['id'] ); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			}

			if ( ! $post_id ) {
				return;
			}

			$offer_product = wcf_pro()->utils->get_offer_data( $post_id );

			if ( empty( $offer_product ) ) {
				return;
			}

			$price_args = array(
				'decimals' => wc_get_price_decimals(),
			);

			$output .= '<span class="wcf-offer-price">';

			$output .= '<span class="wcf-offer-price-inner" style="display:none;">';

			$display_price          = $offer_product['display_price'];
			$display_original_price = $offer_product['display_original_price'];
			$shipping_fee           = $offer_product['shipping_fee_tax'];

			if ( $offer_product['original_price'] !== $offer_product['unit_price'] ) {
				$output .= '<span class="wcf-regular-price del">' . wc_price( $display_original_price, $price_args ) . '</span>';
				$output .= '<span class="wcf-discount-price">' . wc_price( $display_price, $price_args ) . '</span>';
			} else {
				if ( 'display' === $context && ( wcf_pro()->is_wcs_active && in_array( $product->get_type(), array( 'subscription', 'variable-subscription', 'subscription_variation' ), true ) ) ) {
					$output .= '<span class="wcf-regular-price">' . WC_Subscriptions_Product::get_price_string( $product ) . '</span>';
				} else {
					$output .= '<span class="wcf-regular-price">' . wc_price( $display_price, $price_args ) . '</span>';
				}
			}

			if ( 0 < $shipping_fee ) {
				$output .= '<span class="wcf-offer-shipping-fee">' . __( 'Shipping: ', 'cartflows-pro' );
				$output .= wc_price( $shipping_fee, $price_args );
				$output .= __( ' via Flat rate', 'cartflows-pro' ) . '</span>';

			}

				$output .= '</span>';

			if ( $product->is_type( 'variable' ) ) {
				$output .= '<span class="wcf-variable-price-range" style="display:none;">' . $product->get_price_html() . '</span>';
			}

			$output .= '</span>';

			remove_filter( 'woocommerce_price_trim_zeros', '__return_true' );

			return $output;
		}
	}

	/**
	 * Product selection options
	 */
	public function product_diff_price() {

		if ( _is_wcf_base_offer_type() ) {

			global $post;

			$output  = '';
			$product = $this->get_offer_product( 'object' );

			if ( ! is_object( $product ) || null === $product ) {
				return;
			}
			// We are executing the shortcode here.So need for nonce verification.
			$order_id  = isset( $_GET['wcf-order'] ) ? intval( $_GET['wcf-order'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order_key = isset( $_GET['wcf-key'] ) ? sanitize_text_field( wp_unslash( $_GET['wcf-key'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( empty( $order_key ) || empty( $order_id ) ) {
				return;
			}

			$order         = wc_get_order( $order_id );
			$order_total   = $order->get_total();
			$offer_product = wcf_pro()->utils->get_offer_data( $post->ID );
			$product_price = $offer_product['unit_price'];
			$diff_amt      = floatval( $product_price ) - floatval( $order_total );
			$price_args    = array( 'decimals' => 0 );

			if ( $diff_amt > 0 ) {
				$output = wc_price( $diff_amt, $price_args );
			}

			return $output;
		}
	}

	/**
	 * Product selection options
	 */
	public function product_image() {

		if ( _is_wcf_base_offer_type() ) {

			$product = $this->get_offer_product( 'object' );

			if ( ! is_object( $product ) || null === $product ) {
				return;
			}

			/* Enqueue slider script */
			wp_enqueue_style(
				'wcf-pro-flexslider',
				wcf_pro()->utils->get_css_url( 'flexslider' ),
				array(),
				CARTFLOWS_PRO_VER
			);
			wp_enqueue_script( 'wc-add-to-cart-variation' );
			wp_enqueue_script( 'flexslider' );

			if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
				return;
			}

			$columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
			$post_thumbnail_id = $product->get_image_id();
			$attachment_ids    = $product->get_gallery_image_ids();
			$wrapper_classes   = apply_filters(
				'woocommerce_single_product_image_gallery_classes',
				array(
					'woocommerce-product-gallery',
					'woocommerce-product-gallery--' . ( $product->get_image_id() ? 'with-images' : 'without-images' ),
					'woocommerce-product-gallery--columns-' . absint( $columns ),
					'images',
				)
			);

			ob_start();
			?>
			<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>" styles="opacity: 0; transition: opacity .25s ease-in-out;">
				<figure class="woocommerce-product-gallery__wrapper slides">
					<?php
					if ( $product->get_image_id() ) {
						$html = wc_get_gallery_image_html( $post_thumbnail_id, true );
					} else {
						$html  = '<div class="woocommerce-product-gallery__image--placeholder">';
						$html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'cartflows-pro' ) );
						$html .= '</div>';
					}

					echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped

					if ( $attachment_ids && $product->get_image_id() ) {
						foreach ( $attachment_ids as $attachment_id ) {
							echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', wc_get_gallery_image_html( $attachment_id ), $attachment_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
						}
					}
					?>
				</figure>
			</div>
			<?php
			return ob_get_clean();
		}
	}

	/**
	 * Product selection options
	 */
	public function variation_selector() {

		if ( _is_wcf_base_offer_type() ) {

			global $post, $product;

			$product_obj = $this->get_offer_product( 'object' );

			if ( ! is_object( $product_obj ) || empty( $product_obj ) ) {
				return;
			}

			if ( ! $product_obj->is_type( 'variable' ) ) {
				return;
			}

			$product = $product_obj;

			ob_start();
			?>
			<div class="wcf-embeded-product-variation-wrap wcf-offer-product-variation">
				<?php
					woocommerce_template_single_add_to_cart();
				?>
			</div>
			<?php

			$product = null;

			return ob_get_clean();
		}
	}

	/**
	 * Product selection options
	 */
	public function quantity_selector() {

		if ( _is_wcf_base_offer_type() ) {

			global $post, $product;

			$products = $this->get_offer_product( 'data' );

			if ( ! is_array( $products ) || empty( $products ) ) {
				return;
			}

			$step_id = 0;

			if ( $post ) {
				$step_id = $post->ID;
			} elseif ( is_admin() && isset( $_POST['id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
				$step_id = intval( $_POST['id'] ); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			}

			$prdouct_id = $products['id'];

			ob_start();
			?>
			<div class="wcf-embeded-product-quantity-wrap wcf-offer-product-quantity">
				<?php

				$product = wc_get_product( $prdouct_id );

				$get_product_quantity = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-quantity' );

				if ( ! empty( $get_product_quantity ) ) {

					$product_quantity = $get_product_quantity;

				} else {
					$product_quantity = apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product );
				}

				woocommerce_quantity_input(
					array(
						'product_name' => '',
						'min_value'    => $product_quantity,
						'max_value'    => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
						'input_value'  => isset( $_POST['quantity'] ) ? wc_stock_amount( intval( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), //phpcs:ignore WordPress.Security.NonceVerification.Missing
					)
				);

				?>
			</div>
			<?php

			$product = null;

			return ob_get_clean();
		}
	}

	/**
	 * Get all selected products
	 *
	 * @param string $context context of data.
	 */
	public function get_offer_product( $context = 'data' ) {

		if ( null === self::$main_product || null === self::$main_product_data ) {

			global $post;

			$step_id = 0;

			if ( $post ) {
				$step_id = $post->ID;
			} elseif ( is_admin() && isset( $_POST['id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
				$step_id = intval( $_POST['id'] ); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			}

			$product_data = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-product' );
			$products     = array();

			if ( is_array( $product_data ) ) {

				foreach ( $product_data as $p_index => $p_data ) {

					if ( ! isset( $product_data[ $p_index ] ) ) {
						continue;
					}

					$products = array(
						'id'           => $product_data[ $p_index ],
						'variable'     => false,
						'variation'    => false,
						'variation_id' => $product_data[ $p_index ],

					);

					$_product = wc_get_product( $product_data[ $p_index ] );

					if ( ! empty( $_product ) ) {

						if ( $_product->is_type( 'variable' ) ) {
							$products['variable']     = true;
							$products['variation_id'] = wcf_pro()->utils->get_variable_variation_product_id( $product_data[ $p_index ] );

						}

						if ( $_product->is_type( 'variation' ) ) {

							$products['id']        = $_product->get_parent_id();
							$products['variation'] = true;
						}

						self::$main_product      = $_product;
						self::$main_product_data = $products;
					}
				}
			}
		}

		if ( 'object' === $context ) {
			return self::$main_product;
		} else {
			return self::$main_product_data;
		}
	}

	/**
	 * Offer shortcode markup
	 *
	 * @param array $atts attributes.
	 * @return string
	 */
	public function offer_link_yes_markup( $atts ) {

		$order_id = ( isset( $_GET['wcf-order'] ) ) ? intval( $_GET['wcf-order'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$output   = '#';

		if ( _is_wcf_base_offer_type() ) {

			$step_id = _get_wcf_base_offer_id();

			if ( $step_id ) {

				$action = 'yes';

				$template_type = get_post_meta( $step_id, 'wcf-step-type', true );

				$attr = array(
					'class' => 'wcf-' . $template_type . '-offer-' . $action,
				);

				$order = wc_get_order( $order_id );

				if ( $order ) {

					$payment_method = $order->get_payment_method();

					$gateways = array( 'paypal', 'ppec_paypal' );
					$gateways = apply_filters( 'cartflows_offer_supported_payment_gateway_slugs', $gateways );
					if ( ( in_array( $payment_method, $gateways, true ) ) && ! wcf_pro()->utils->is_reference_transaction() ) {

						$attr['skip'] = 'cartflows-skip';
					}
				}

				$attr_string = '?';

				foreach ( $attr as $key => $value ) {
					$attr_string .= $key . '=' . $value . '&';
				}

				$output = rtrim( $attr_string, '&' );
			}
		}

		return $output;
	}

	/**
	 * Offer shortcode markup
	 *
	 * @param array $atts attributes.
	 * @return string
	 */
	public function offer_link_no_markup( $atts ) {

		$output = '#';

		if ( _is_wcf_base_offer_type() ) {

			$step_id = _get_wcf_base_offer_id();

			if ( $step_id ) {

				$action = 'no';

				$template_type = get_post_meta( $step_id, 'wcf-step-type', true );

				$attr = array(
					'class' => 'wcf-' . $template_type . '-offer-' . $action,
				);

				$attr_string = '?';

				foreach ( $attr as $key => $value ) {
					$attr_string .= $key . '=' . $value . '&';
				}

				$output = rtrim( $attr_string, '&' );
			}
		}

		return $output;
	}

	/**
	 * Override woo templates.
	 *
	 * @param string $template new  Template full path.
	 * @param string $template_name Template name.
	 * @param string $template_path Template Path.
	 * @since 1.1.5
	 * @return string.
	 */
	public function override_woo_variable_template( $template, $template_name, $template_path ) {

		if ( _is_wcf_base_offer_type() ) {

			global $woocommerce;

			$_template = $template;

			$plugin_path = CARTFLOWS_PRO_DIR . 'woocommerce/templates/';

			if ( file_exists( $plugin_path . $template_name ) ) {
				$template = $plugin_path . $template_name;
			}

			if ( ! $template ) {
				$template = $_template;
			}
		}

		return $template;
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Base_Offer_Shortcodes::get_instance();
