<?php
/**
 * Frontend view
 *
 * @package action-link
 */

?>
<div class="cartflows-pro-bb__action-link">
	<a href="<?php echo esc_url( $module->get_link() ); ?>" class="cartflows-pro-bb__action-link" >

		<?php
		if ( ! empty( $settings->icon ) && ( 'before' == $settings->icon_position || ! isset( $settings->icon_position ) ) ) :
			?>

			<i class="cartflows-pro-bb__action-link-icon cartflows-pro-bb__action-link-icon-before fa <?php echo esc_attr( $settings->icon ); ?>" aria-hidden="true"></i>

		<?php endif; ?>

		<?php if ( ! empty( $settings->text ) ) : ?>
			<span class="cartflows-pro-bb__action-link-text"><?php echo esc_html( $settings->text ); ?></span>
		<?php endif; ?>

		<?php
		if ( ! empty( $settings->icon ) && 'after' == $settings->icon_position ) :
			?>
			<i class="cartflows-pro-bb__action-link-icon cartflows-pro-bb__action-link-icon-after fa <?php echo esc_attr( $settings->icon ); ?>"></i>
		<?php endif; ?>


	</a>
</div>
