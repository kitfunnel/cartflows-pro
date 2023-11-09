<?php
/**
 * Your product section html
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
//phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.SelfOutsideClass
?>

<div class="wcf-product-option-wrap wcf-yp-skin-<?php echo esc_attr( self::$product_option_data['selected_skin'] ); ?> wcf-product-option-<?php echo esc_attr( self::$product_option_data['position'] ); ?>">
	<h3 id="your_products_heading"> <?php echo esc_html( $this->product_option_title() ); ?> </h3>
	<div class="wcf-qty-options">

	<?php
	if ( 'classic' === self::$product_option_data['selected_skin'] ) {

		include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/your-product/product-table-titles.php';
	}

	foreach ( $products as $p_id => $p_data ) {

		$current_product = wc_get_product( $p_data['product_id'] );

		// Reason for PHPCS ignore: The data is being escaped inside the functions and the templates used below.
		if ( 'force-all' === $type ) {
			echo $this->force_all_product_markup( $current_product, $p_data ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( 'single-selection' === $type ) {
			echo $this->single_sel_product_markup( $current_product, $p_data ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo $this->multiple_sel_product_markup( $current_product, $p_data ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
	?>
	</div>
</div>
