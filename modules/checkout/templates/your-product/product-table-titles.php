<?php
/**
 * Product table titles html
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

?>
<div class="wcf-qty-row wcf-qty-table-titles">
	<div class="wcf-qty-header wcf-item">
		<div class="wcf-field-label"><?php echo esc_html__( 'Product', 'cartflows-pro' ); ?></div>
	</div>
	<div class="wcf-qty-header wcf-qty <?php echo esc_attr( $quantity_hidden ); ?>">
		<div class="wcf-field-label"><?php echo esc_html__( 'Quantity', 'cartflows-pro' ); ?></div>
	</div>
	<div class="wcf-qty-header wcf-price">
		<div class="wcf-field-label"><?php echo esc_html__( 'Price', 'cartflows-pro' ); ?></div>
	</div>
</div>
