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
	<input class="wcf-var-sel" id="wcf-item-product-<?php echo $rc_product_id; ?>"  type="radio" 
	name="wcf-var-sel[<?php echo $parent_id; ?>]" value="<?php echo $rc_product_id; ?>" 
	<?php echo $checked; ?>>
	<label class="wcf-item-product-label" for="wcf-item-product-<?php echo $rc_product_id; ?>" ></label>
</div>
