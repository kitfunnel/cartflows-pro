<?php
/**
 * BB Next Step link Module front-end CSS php file.
 *
 * @package BB Next Step link Module
 */

// Escaping not required as it is CSS file and we are following beaver builder format for it.
//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
global $post;

?>

.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-desc {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->text_color ); ?>;
}

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'typography',
			'selector'     => ".fl-node-$id .cartflows-pro-bb__offer-product-desc",
		)
	);
}
?>
