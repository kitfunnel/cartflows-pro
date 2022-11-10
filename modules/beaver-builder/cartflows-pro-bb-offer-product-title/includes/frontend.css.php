<?php
/**
 * BB Offer Product Title Module front-end CSS php file.
 *
 * @package BB Offer Product Title Module
 */

global $post;

?>

.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-title {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->text_color ); ?>;
}

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'typography',
			'selector'     => ".fl-node-$id .cartflows-pro-bb__offer-product-title",
		)
	);
}
?>
