<?php
/**
 * Variation inline selector
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="wcf-item-selector wcf-item-var-sel">
	<input class="wcf-var-sel" id="wcf-item-product-<?php echo esc_attr( $rc_product_id ); ?>"  type="radio"
	name="wcf-var-sel[<?php echo esc_attr( $parent_id ); ?>]" value="<?php echo esc_attr( $rc_product_id ); ?>"
	<?php echo esc_attr( $checked ); ?>>
	<label class="wcf-item-product-label" for="wcf-item-product-<?php echo esc_attr( $rc_product_id ); ?>" ></label>
</div>
