<?php
/**
 * Frontend view
 *
 * @package cartflows-pro
 */

?>
<div class="cartflows-pro-bb__offer-product-variation cartflows-bb__offer-product-variation_align-<?php echo esc_attr( $settings->align ); ?>">
	<?php
		echo do_shortcode( '[cartflows_offer_product_variation]' );
	?>
</div>
