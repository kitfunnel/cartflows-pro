<?php
/**
 * Radio selector
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="wcf-item-selector wcf-item-single-sel">
	<input class="wcf-single-sel" type="radio"
	id="wcf-item-product-<?php echo $rc_product_id; ?>"
	name="wcf-single-sel" 
	value="<?php echo $rc_product_id; ?>"
	<?php echo $checked; ?>>

	<label class="wcf-item-product-label" for="wcf-item-product-<?php echo $rc_product_id; ?>"></label>
</div>
