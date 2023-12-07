<?php
/**
 *  BB Offer product image Module front-end CSS php file.
 *
 * @package cartflows-pro
 */

// Escaping not required as it is CSS file and we are following beaver builder format for it.
//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
global $post;

?>

.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-image .woocommerce-product-gallery .woocommerce-product-gallery__wrapper {
	margin-bottom: <?php echo $settings->image_spacing . 'px'; ?>;
}

.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-image .woocommerce-product-gallery .woocommerce-product-gallery__image {
	text-align: <?php echo $settings->align; ?>;
}

.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-image .woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image img {
	border-radius: <?php echo ( '' != $settings->image_border_radius ) ? $settings->image_border_radius : '0'; ?>px;
}

<?php if ( 'none' != $settings->image_border_style ) { ?>
	.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-image .woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image img {
		border-style: <?php echo ( '' != $settings->image_border_style ) ? $settings->image_border_style : 'solid'; ?>;
		border-width: <?php echo ( '' != $settings->image_border_width ) ? $settings->image_border_width : '0'; ?>px;
		border-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->image_border_color ); ?>;
	}

<?php } ?>

<?php // Thumbnails style. ?>

.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-image .woocommerce-product-gallery ol li:not(:last-child) {
	margin-bottom: <?php echo $settings->thumbnails_spacing . 'px'; ?>;
	margin-right: <?php echo $settings->thumbnails_spacing . 'px'; ?>;
	margin-top: 0px;
	margin-left: 0px;
}

.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-image .woocommerce-product-gallery ol li img {
	border-radius: <?php echo ( '' != $settings->thumbnails_border_radius ) ? $settings->thumbnails_border_radius : '0'; ?>px;
}

<?php if ( 'none' != $settings->thumbnails_border_style ) { ?>
	.fl-node-<?php echo $id; ?> .cartflows-pro-bb__offer-product-image .woocommerce-product-gallery ol li img {
		border-style: <?php echo ( '' != $settings->thumbnails_border_style ) ? $settings->thumbnails_border_style : 'solid'; ?>;
		border-width: <?php echo ( '' != $settings->thumbnails_border_width ) ? $settings->thumbnails_border_width : '0'; ?>px;
		border-color: <?php echo FLBuilderColor::hex_or_rgb( $settings->thumbnails_border_color ); ?>;
	}

<?php } ?>
