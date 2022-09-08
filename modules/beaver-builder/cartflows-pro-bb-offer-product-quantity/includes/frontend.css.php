<?php
/**
 * BB Offer Product Quantity Module front-end CSS php file.
 *
 * @package BB offer-product-quantity
 */

global $post;

?>

.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-quantity .wcf-embeded-product-quantity-wrap .quantity {
	max-width: <?php echo $settings->width . '%'; ?>;
}

.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-quantity .wcf-embeded-product-quantity-wrap label {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->label_color ); ?>;
}

.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-quantity .wcf-embeded-product-quantity-wrap .quantity input[type=number]{
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->text_color ); ?>;
}

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'typography',
			'selector'     => ".fl-node-$id .cartflows-pro-bb__offer-product-quantity, .fl-node-$id .wcf-embeded-product-quantity-wrap .quantity input[type=number].input-text, .fl-node-$id .cartflows-pro-bb__offer-product-quantity .quantity label",
		)
	);
}
?>

.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-quantity {
	text-align: <?php echo $settings->align; ?>;
}
