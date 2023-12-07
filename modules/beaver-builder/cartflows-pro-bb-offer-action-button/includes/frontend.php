<?php
/**
 * Frontend view
 *
 * @package action-button
 */

?>
<div class="<?php echo esc_attr( $module->get_classname() ); ?>">

	<?php
	if ( isset( $settings->threed_button_options ) && ( 'animate_top' == $settings->threed_button_options || 'animate_bottom' == $settings->threed_button_options || 'animate_left' == $settings->threed_button_options || 'animate_right' == $settings->threed_button_options ) ) {
		?>
		<div class="perspective">
		<?php
	}
	?>

		<a href="<?php echo esc_url( $module->get_link() ); ?>" class="cartflows-pro-bb__action-button cartflows-pro-bb__action-creative-button <?php echo 'cartflows-pro-bb__action-creative-' . esc_attr( $settings->style ) . '-btn'; ?> cartflows-pro-bb__button-icon-<?php echo esc_attr( $settings->verticle_align ); ?> <?php echo esc_attr( $module->get_button_style() ); ?>" role="button" >

			<div class="cartflows-pro-bb__offer-yes-no-inner-wrap">

				<?php
				if ( ! empty( $settings->icon ) && ( 'before_title_desc' == $settings->icon_position || ! isset( $settings->icon_position ) ) ) :

					$add_class_to_icon = 'cartflows-pro-bb__action-button-icon cartflows-pro-bb__action-button-icon-before-title-desc cartflows-pro-bb__action-creative-button-icon-before-title-desc';
					?>

					<i class="<?php echo esc_attr( $add_class_to_icon ); ?> fa <?php echo esc_attr( $settings->icon ); ?>" aria-hidden="true"></i>

				<?php endif; ?>

				<div class="cartflows-pro-bb__offer-yes-no-button-content-wrap">

					<div class="cartflows-pro-bb__offer-yes-no-button-title-wrap">

						<?php
						if ( ! empty( $settings->icon ) && ( 'before' == $settings->icon_position || ! isset( $settings->icon_position ) ) ) :

							$add_class_to_icon = 'cartflows-pro-bb__action-button-icon-before cartflows-pro-bb__action-creative-button-icon-before';
							?>

							<i class="<?php echo esc_attr( $add_class_to_icon ); ?> fa <?php echo esc_attr( $settings->icon ); ?>" aria-hidden="true"></i>

						<?php endif; ?>

						<?php if ( ! empty( $settings->text ) ) : ?>
							<span class="cartflows-pro-bb__action-button-text cartflows-pro-bb__action-creative-button-text"><?php echo esc_html( $settings->text ); ?></span>
						<?php endif; ?>

						<?php
						if ( ! empty( $settings->icon ) && 'after' == $settings->icon_position ) :
							$add_class_to_icon = 'cartflows-pro-bb__action-button-icon-after cartflows-pro-bb__action-creative-button-icon-after';
							?>
							<i class="cartflows-pro-bb__action-button-icon cartflows-pro-bb__action-creative-button-icon <?php echo esc_attr( $add_class_to_icon ); ?> fa <?php echo esc_attr( $settings->icon ); ?>"></i>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $settings->description ) ) : ?>
						<span class="cartflows-pro-bb__action-button-description cartflows-pro-bb__action-creative-button-description"><?php echo esc_html( $settings->description ); ?></span>
					<?php endif; ?>

				</div>

				<?php
				if ( ! empty( $settings->icon ) && ( 'after_title_desc' == $settings->icon_position || ! isset( $settings->icon_position ) ) ) :

					$add_class_to_icon = 'cartflows-pro-bb__action-button-icon cartflows-pro-bb__action-button-icon-after-title-desc cartflows-pro-bb__action-creative-button-icon-after-title-desc';
					?>

					<i class="<?php echo esc_attr( $add_class_to_icon ); ?> fa <?php echo esc_attr( $settings->icon ); ?>" aria-hidden="true"></i>

				<?php endif; ?>

			</div>

		</a>

	<?php
	if ( isset( $settings->threed_button_options ) && ( 'animate_top' == $settings->threed_button_options || 'animate_bottom' == $settings->threed_button_options || 'animate_left' == $settings->threed_button_options || 'animate_right' == $settings->threed_button_options ) ) {
		?>
		</div>
		<?php
	}
	?>
</div>
