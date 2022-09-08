<?php
/**
 * Title and Subtext
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<?php
if ( ! empty( $data['product_subtext'] ) ) {
	?>
<div class="wcf-item-subtext">
	<?php

	$to_replace = array(
		'{{quantity}}',
		'{{discount_value}}',
		'{{discount_percent}}',
	);

	$with_replace = array(
		'<span class="wcf-display-quantity">' . $price_sel_data['quantity'] . '</span>',
		'<span class="wcf-display-discount-value">' . $price_sel_data['save_value'] . '</span>',
		'<span class="wcf-display-discount-percent">' . $price_sel_data['save_percent'] . '</span>',
	);

	$subtext = str_replace( $to_replace, $with_replace, $data['product_subtext'] );

	echo $subtext;
	?>
	</div>
<?php } ?>
