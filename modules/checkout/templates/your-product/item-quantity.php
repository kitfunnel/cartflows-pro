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
?>
<div class="wcf-qty  <?php echo esc_attr( $quantity_hidden ); ?>">
	<div class="wcf-qty-selection-wrap">
		<span class="wcf-qty-selection-btn wcf-qty-decrement wcf-qty-change-icon">&minus;</span>
		<input autocomplete="off" type="number" value="<?php echo esc_attr( $data['default_quantity'] ); ?>" step="<?php echo esc_attr( $data['default_quantity'] ); ?>" min="<?php echo esc_attr( $data['default_quantity'] ); ?>" name="wcf_qty_selection" class="wcf-qty-selection" placeholder="1">
		<span class="wcf-qty-selection-btn wcf-qty-increment wcf-qty-change-icon">&plus;</span>
	</div>
</div>
