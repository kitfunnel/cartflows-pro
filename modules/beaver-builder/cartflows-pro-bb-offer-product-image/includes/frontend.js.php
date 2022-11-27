<?php
/**
 *  Offer Product Image Module front-end JS php file
 *
 *  @package Offer Product Image Module
 */

?>

(function($) {
	$(function() {
		new Cartflows_Pro_BBProductImage ({
			id: '<?php echo esc_attr( $id ); ?>',
			isBuilderActive: <?php echo esc_attr( FLBuilderModel::is_builder_active() ? 'true' : 'false' ); ?>,
			settings: <?php echo wp_json_encode( $settings ); ?>
		});
	});

})(jQuery);

<?php // File end. ?>
