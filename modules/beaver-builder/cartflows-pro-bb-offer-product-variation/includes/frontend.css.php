<?php
/**
 * BB Offer Product Variation Module front-end CSS php file.
 *
 * @package BB Offer Product Variation Module
 */

global $post;

?>

.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-variation .wcf-embeded-product-variation-wrap .variations {
	max-width: <?php echo $settings->width . '%'; ?>;
}

.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-variation label,
.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-variation .out-of-stock {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->label_color ); ?>;
}

.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-variation .wcf-embeded-product-variation-wrap .variations .value select{
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->text_color ); ?>;
}

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'typography',
			'selector'     => ".fl-node-$id .cartflows-pro-bb__offer-product-variation, .fl-node-$id .wcf-embeded-product-variation-wrap .variations .value select, .fl-node-$id .cartflows-pro-bb__offer-product-variation .label label",
		)
	);
}
?>

.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-variation {
	text-align: <?php echo $settings->align; ?>;
}
