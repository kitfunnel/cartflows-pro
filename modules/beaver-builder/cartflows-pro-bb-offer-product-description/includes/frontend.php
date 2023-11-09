<?php
/**
 * Frontend view
 *
 * @package offer-product-desc
 */

?>
<div class="cartflows-pro-bb__offer-product-desc">
	<?php
	if ( 'yes' === $settings->short_description ) {
		echo do_shortcode( '[cartflows_offer_product_short_desc]' );
	} else {
		echo do_shortcode( '[cartflows_offer_product_desc]' );
	}
	?>
</div>
