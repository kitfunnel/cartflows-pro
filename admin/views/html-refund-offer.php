<?php
/**
 * Offer items HTML for meta box.
 *
 * @package CartFlows
 */

defined( 'ABSPATH' ) || exit;


global $post;

// Required Variables.
$products = array();
$order_id = $post ? $post->ID : 0;

// Reason for ignoring the rule: We are including this file on admin action in add_meta_box function callback.
if ( ! $order_id && isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$order_id = intval( wp_unslash( $_GET['id'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

$order_obj    = wc_get_order( $order_id );
$order_items  = $order_obj->get_items( 'line_item' );
$shipping_fee = 0;

$order_obj->calculate_totals();

foreach ( $order_items as $key => $value ) {
	$is_upsell   = wc_get_order_item_meta( $key, '_cartflows_upsell', true );
	$is_downsell = wc_get_order_item_meta( $key, '_cartflows_downsell', true );
	$step_id     = wc_get_order_item_meta( $key, '_cartflows_step_id', true );
	$is_refunded = wc_get_order_item_meta( $key, '_cartflows_refunded', true );


	if ( 'yes' == $is_upsell || 'yes' == $is_downsell ) {

		if ( 'yes' == $is_upsell ) {
			$offer_type     = 'Upsell';
			$transaction_id = wc_get_order_item_meta( $key, '_cartflows_offer_txn_id', true );
			$shipping_fee   = wc_get_order_item_meta( $key, '_cartflows_offer_shipping_fee', true );
		} elseif ( 'yes' == $is_downsell ) {
			$offer_type     = 'Downsell';
			$transaction_id = wc_get_order_item_meta( $key, '_cartflows_offer_txn_id', true );
			$shipping_fee   = wc_get_order_item_meta( $key, '_cartflows_offer_shipping_fee', true );
		}

		$products[ $key ] = array(
			'order_id'                 => $value['order_id'],
			'step_id'                  => $step_id,
			'offer_type'               => $offer_type,
			'order_item_id'            => $key,
			'offer_product_id'         => $value['product_id'],
			'offer_product_name'       => get_the_title( $value['product_id'] ),
			'offer_product_total'      => $value->get_total(),
			'offer_product_qty'        => $value->get_quantity(),
			'offer_product_item_total' => 0,
			'offer_product_item_tax'   => 0,
			'transaction_id'           => $transaction_id,
			'is_refunded'              => 'yes' === $is_refunded ? true : false,
			'shipping_fee'             => $shipping_fee,
		);

		if ( get_option( 'woocommerce_calc_taxes' ) ) {
			$products[ $key ]['offer_product_total']      = $products[ $key ]['offer_product_total'] + $value->get_total_tax();
			$products[ $key ]['offer_product_item_total'] = $value->get_total();
			$products[ $key ]['offer_product_item_tax']   = $products[ $key ]['offer_product_item_tax'] + $value->get_total_tax();
		}

		if ( isset( $shipping_fee ) && ! empty( $shipping_fee ) && $shipping_fee > 0 ) {
			$products[ $key ]['offer_product_total']      = $products[ $key ]['offer_product_total'] + $shipping_fee;
			$products[ $key ]['offer_product_item_total'] = $value->get_total();
			$products[ $key ]['offer_product_item_tax']   = $products[ $key ]['offer_product_item_tax'] + $shipping_fee;
		}
	}
}

// @codingStandardsIgnoreLine WordPress.Security.EscapeOutput.UnsafePrintingFunction
?>

<div class="woocommerce_order_items_wrapper wc-order-items-editable cartflows-offer-refund-wrap">
	<?php if ( count( $order_items ) > 0 ) { ?>

		<table cellpadding="0" cellspacing="0" class="cartflows_offer_items">
			<thead>
			<tr>
				<th colspan="2"><?php esc_html_e( 'Product Name', 'cartflows-pro' ); ?></th>
				<th ><?php esc_html_e( 'Offer Type', 'cartflows-pro' ); ?></th>
				<th ><?php esc_html_e( 'Quantity', 'cartflows-pro' ); ?></th>
				<th ><?php esc_html_e( 'Total', 'cartflows-pro' ); ?></th>
				<th width="1%"><?php esc_html_e( 'Action', 'cartflows-pro' ); ?></th>
			</tr>
			</thead>
			<tbody id="order_line_items" class="order_line_items">
			<?php
			foreach ( $products as $key => $product_details ) {

				$offer_type     = $product_details['offer_type'];
				$product_id     = $product_details['offer_product_id'];
				$product_name   = $product_details['offer_product_name'];
				$product_qty    = $product_details['offer_product_qty'];
				$is_refunded    = $product_details['is_refunded'];
				$product_amount = wc_price( $product_details['offer_product_total'] );
				$product        = wc_get_product( $product_id );
				$thumbnail      = $product->get_image( 'thumbnail', array( 'title' => '' ) );

				?>
				<tr class="item">

					<td class="thumb">
						<div class="wc-order-item-thumbnail"><?php echo wp_kses_post( $thumbnail ); ?></div>
					</td>

					<td class="name">
						<a data-product_id="<?php echo esc_attr( $product_id ); ?>" href="<?php echo esc_url( get_edit_post_link( $product_id ) ); ?>" class="wc-order-item-name" target="_blank"><?php echo esc_html( $product_name ); ?></a>
					</td>

					<td class="type">
						<div class="view">
							<span class="offer_type"><?php echo esc_html( $offer_type ); ?></span>
						</div>
					</td>

					<td class="quantity" width="1%">
						<div class="view">
							<span class="quantity"><?php echo esc_html( $product_qty ); ?></span>
						</div>
					</td>

					<td class="line_cost" width="1%">
						<div class="view">
							<strong><?php echo wp_kses_post( $product_amount ); ?> </strong>
							<?php
								echo wp_kses_post(
									wc_help_tip(
									/* translators: %1$s: item total, %2$s: tax, %3$s: product total */
										sprintf( __( '<div class="amount_distribution"><span class="">Item Prices: %1$s</span><br/><span class="">Tax & Other: %2$s</span><br><span class="">Total: %3$s</span></div>', 'cartflows-pro' ), wc_price( $product_details['offer_product_item_total'] ), wc_price( $product_details['offer_product_item_tax'] ), wc_price( $product_details['offer_product_total'] ) )
									)
								);
							?>
						</div>
					</td>

					<td width="1%">
						<div class="view">
						<?php
						if ( ! $is_refunded ) {
							$button_markup = '<button type="button" class="button wcf-offer-refund"
					                data-item-name="' . $product_details['offer_product_name'] . '"
					                data-item-amount="' . $product_details['offer_product_total'] . '"
					                data-item-quantity="' . $product_details['offer_product_qty'] . '"
					                data-item-id="' . $product_details['order_item_id'] . '"
					                data-item-type="' . $product_details['offer_type'] . '"
					                data-order-id="' . $product_details['order_id'] . '"
					                data-transaction-id="' . $product_details['transaction_id'] . '"
					                data-step-id="' . $product_details['step_id'] . '">' . esc_html__( 'Refund', 'cartflows-pro' ) . '</button>';
						} else {

							$button_markup = '<button type="button" class="button disabled">' . esc_html__( 'Refunded', 'cartflows-pro' ) . '</button>';
						}

						echo wp_kses_post( $button_markup );
						?>
						</div>
					</td>
				</tr>
				<?php
			}
			?>

			<input type="hidden" value="<?php echo esc_attr( $order_id ); ?>" name="order_id">
			<input type="hidden" value="<?php echo esc_attr( wp_create_nonce( 'wcf_admin_refund_offer_nonce' ) ); ?>" name="wcf_admin_refund_offer_nonce">
			</tbody>
		</table>

		<div class="ref_note">
			<?php esc_html_e( 'Use WooCommerce\'s refund feature to refund the main order. You can refund upsell/downsell offers from this section. Always refund upsell/downsell offers first & then refund the main order.', 'cartflows-pro' ); ?>
		</div>

		<?php
	} else {
		echo "<p class='wcf-no-refund-offer'>" . esc_html_e( 'Refunds are not available for any offer(s) against this order.', 'cartflows-pro' ) . '</p>';
	}
	?>
</div>
