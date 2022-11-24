<?php
/**
 * BB Next Step link Module front-end CSS php file.
 *
 * @package BB Next Step link Module
 */

global $post;

?>

.fl-node-<?php echo $id; ?> .cartflows-pro-bb__action-link,
.fl-node-<?php echo $id; ?> .cartflows-pro-bb__action-link i,
.fl-node-<?php echo $id; ?> .cartflows-pro-bb__action-link .cartflows-pro-bb__action-link-text {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->text_color ); ?>;
}

.fl-node-<?php echo $id; ?> .cartflows-pro-bb__action-link:hover,
.fl-node-<?php echo $id; ?> .cartflows-pro-bb__action-link:hover i,
.fl-node-<?php echo $id; ?> .cartflows-pro-bb__action-link:hover .cartflows-pro-bb__action-link-text {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->text_hover_color ); ?>;
}

<?php
if ( class_exists( 'FLBuilderCSS' ) ) {
	FLBuilderCSS::typography_field_rule(
		array(
			'settings'     => $settings,
			'setting_name' => 'link_typography',
			'selector'     => ".fl-node-$id .cartflows-pro-bb__action-link",
		)
	);
}
?>

<?php if ( 'before' == $settings->icon_position && ! empty( $settings->icon ) ) { ?>
	.fl-node-<?php echo $id; ?> .cartflows-pro-bb__action-link-icon-before {
		margin-right: <?php echo $settings->icon_spacing; ?>px;
	}
<?php } elseif ( 'after' == $settings->icon_position && ! empty( $settings->icon ) ) { ?>
	.fl-node-<?php echo $id; ?> .cartflows-pro-bb__action-link-icon-after {
		margin-left: <?php echo $settings->icon_spacing; ?>px;
	}
<?php } ?>
