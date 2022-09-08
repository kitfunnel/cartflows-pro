<?php
/**
 * Checkbox selector
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="wcf-item-selector wcf-item-multiple-sel">	
	<input class="wcf-multiple-sel" type="checkbox"
	name="wcf-multiple-sel"
	value="<?php echo $rc_product_id; ?>" <?php echo $checked; ?> >
</div>
