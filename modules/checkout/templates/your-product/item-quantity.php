<?php
/**
 * Quantity option
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
//phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.SelfOutsideClass
$quantity_hidden = '';
if ( 'yes' !== self::$is_quantity ) {
	$quantity_hidden = 'wcf-qty-hidden';
}
$title_attr           = '';
$product              = wc_get_product( $data['product_id'] );
$is_sold_individually = $product->is_sold_individually() ? 'true' : 'false';

if ( 'true' === $is_sold_individually ) {
	$title_attr = __( 'This product is set to purchase only 1 item per order.', 'cartflows-pro' );
}

?>
<div class="wcf-qty  <?php echo esc_attr( $quantity_hidden ); ?>">
	<div class="wcf-qty-selection-wrap">
		<span class="wcf-qty-selection-btn wcf-qty-decrement wcf-qty-change-icon" title="<?php echo esc_attr( $title_attr ); ?>">&minus;</span>
		<input autocomplete="off" type="number" value="<?php echo esc_attr( $data['default_quantity'] ); ?>" step="<?php echo esc_attr( $data['default_quantity'] ); ?>" min="<?php echo esc_attr( $data['default_quantity'] ); ?>" name="wcf_qty_selection" class="wcf-qty-selection" placeholder="1" data-sale-limit="<?php echo esc_attr( $is_sold_individually ); ?>" title="<?php echo esc_attr( $title_attr ); ?>" readonly="<?php echo esc_attr( $is_sold_individually ); ?>" >
		<span class="wcf-qty-selection-btn wcf-qty-increment wcf-qty-change-icon" title="<?php echo esc_attr( $title_attr ); ?>">&plus;</span>
	</div>
</div>
