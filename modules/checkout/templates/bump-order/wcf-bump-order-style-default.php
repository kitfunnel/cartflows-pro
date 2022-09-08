<?php
// @codingStandardsIgnoreStart

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="wcf-bump-order-wrap default">

	<?php $this->get_order_bump_hidden_data( $product_id, $order_bump_checked ); ?>

	<div class="wcf-bump-order-field-wrap wcf-bump-order-<?php echo $order_bump_data['id']; ?>">
		<label>
			<span class="dashicons dashicons-arrow-right-alt"></span>
			<input type="checkbox" class="wcf-bump-order-cb"  name="<?php echo 'wcf-bump-order-cb-'. $ob_id ?>" value="<?php echo $ob_id; ?>" id="wcf-bump-order-cb"<?php checked( $order_bump_checked, true, false ); ?> data-ob_data="<?php echo htmlentities($bump_offer_data) ?>" >
			<span class="wcf-bump-order-label"><?php echo esc_attr( $order_bump_label ); ?></span>

			<span class="dashicons dashicons-arrow-left-alt"></span>
		</label>
	</div>

	<div class="wcf-bump-order-desc">
		<span class="wcf-bump-order-bump-highlight"><?php echo wp_kses_post( $order_bump_hl_text ); ?></span>&nbsp;<?php echo wp_kses_post( $order_bump_desc ); ?>
	</div>
</div>
<?php 
	// @codingStandardsIgnoreEnd
