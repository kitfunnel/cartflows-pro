<?php
/**
 * Your product template for variation products.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
//phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.SelfOutsideClass
if ( $single_variation && $single_variation->is_in_stock() && 'publish' === $single_variation->get_status() ) {

	$highlight_data = $this->get_product_highlight_data( $data );
	$parent_id      = $current_product->get_id();
	$variation_id   = $single_variation->get_id();
	$checked        = ( isset( self::$cart_products[ $variation_id ] ) && isset( self::$cart_items[ $data['cart_item_key'] ] ) ) ? 'checked' : '';
	$price_data     = $this->your_product_price( $current_product, $data, $single_variation );
	$price_sel_data = $price_data['sel_data'];
	$rc_product_obj = $single_variation;
	$rc_product_id  = $variation_id;
	$rc_sel_data    = wp_json_encode( $price_sel_data );
	$rc_sel_data    = is_string( $rc_sel_data ) ? $rc_sel_data : '';
	?>
	<div class="wcf-qty-row wcf-qty-row-<?php echo esc_attr( $variation_id ); ?> <?php echo esc_attr( $highlight_data['parent_class'] ); ?>" data-options="<?php echo htmlspecialchars( $rc_sel_data, ENT_COMPAT, 'utf-8' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
		<?php

			$selected_skin = self::$product_option_data['selected_skin'];

			$template_file = CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/your-product/' . $selected_skin . '-normal-product-row-markup.php';

		if ( file_exists( $template_file ) ) {
			include $template_file;
		}
		?>
	</div>
	<?php
}
