<?php
// @codingStandardsIgnoreStart

$image_available = !empty( $bump_order_image ) ? true : false;

?>
<div class="wcf-bump-order-wrap wcf-bump-order-<?php echo $ob_id; ?> wcf-bump-order-style-3 wcf-<?php echo $position; ?> wcf-ob-column-<?php echo $bump_layout_width; ?>">

	<?php $this->get_order_bump_hidden_data( $product_id, $order_bump_checked ); ?>

	<!-- wcf-bump-order-content -->
	<div class="wcf-bump-order-content wcf-bump-order-image-<?php echo $bump_image_position; ?>">

	<?php
		if( !empty( $bump_image_position ) && 'right' !== $bump_image_position ){
	?>
		<!-- wcf-bump-order-field-wrap -->
		<div class="wcf-bump-order-field-wrap">
		<?php
		// If the image is set then show this section.
		if( $image_available && 'top' !== $bump_image_position ){  ?>
					<div class="wcf-bump-order-action">
						<?php if( isset( $bump_order_blinking_arrow ) ){ echo $bump_order_blinking_arrow;} ?>
						<input type="checkbox" id="wcf-bump-order-cb" class="wcf-bump-order-cb"  name="<?php echo 'wcf-bump-order-cb-'. $ob_id ?>" value="<?php echo $ob_id; ?>" <?php checked( $order_bump_checked, true, true ); ?> data-ob_data="<?php echo htmlentities( $bump_offer_data) ?>">
					</div>
			<?php } ?>

			<?php if( !empty( $bump_order_image ) ){ ?>
				<div class="wcf-bump-order-image">
					<img src="<?php if( isset( $bump_order_image ) ){ echo esc_url( $bump_order_image ); }?>" class="wcf-image" />
				</div>
			<?php } ?>

			<div class="wcf-bump-order-text">
					<div class="wcf-bump-order-label">
						<?php if( !$image_available ) { ?>
								<?php if( isset( $bump_order_blinking_arrow ) ){ echo $bump_order_blinking_arrow;} ?>
								<input type="checkbox" id="wcf-bump-order-cb" class="wcf-bump-order-cb"  name="<?php echo 'wcf-bump-order-cb-'. $ob_id ?>" value="<?php echo $ob_id; ?>" <?php checked( $order_bump_checked, true, true ); ?> data-ob_data="<?php echo htmlentities( $bump_offer_data) ?>">

								<?php if( !empty( $order_bump_title ) ){ ?>
										<label><?php echo wp_kses_post( $order_bump_title ); ?></label>
								<?php } ?>

						<?php
							} else if( $image_available && 'top' === $bump_image_position )  {
						?>
						<!-- If the image is set and image position is top then show this section. -->

						<div class="wcf-bump-order-action">
							<?php if( isset( $bump_order_blinking_arrow ) ){ echo $bump_order_blinking_arrow;} ?>
							<input type="checkbox" id="wcf-bump-order-cb" class="wcf-bump-order-cb"  name="<?php echo 'wcf-bump-order-cb-'. $ob_id ?>" value="<?php echo $ob_id; ?>" <?php checked( $order_bump_checked, true, true ); ?> data-ob_data="<?php echo htmlentities( $bump_offer_data) ?>">
						<?php if( !empty( $order_bump_title ) ){ ?>
								<label><?php echo wp_kses_post( $order_bump_title ); ?></label>
							<?php }?>

						</div>

						<?php }else{

							if( !empty( $order_bump_title ) ){ ?>
								<label><?php echo wp_kses_post( $order_bump_title ); ?></label>
							<?php }
						} ?>


					</div>

				<?php if( !empty( $order_bump_desc ) ) { ?>
					<div class="wcf-bump-order-desc"><?php echo wp_kses_post( $order_bump_desc ); ?></div>
				<?php } ?>
				<?php if( 'yes' === $display_quantity_field ) { ?>
					<div class="wcf-ob-qty-selection-wrap">
						<span class="wcf-ob-qty-selection-btn wcf-ob-qty-decrement wcf-ob-qty-change-icon">-</span>
						<input type="number" class="wcf-order-bump-quantity-updater" name="wcf_order_bump_quantity" placeholder="1" value="<?php echo esc_html( $order_bump_product_quantity );?>" min="1" />
						<span class="wcf-ob-qty-selection-btn wcf-ob-qty-increment wcf-ob-qty-change-icon">+</span>
					</div>
				<?php } ?>
			</div>



		</div>
	<?php
		}else if( !empty( $bump_image_position ) && 'right' === $bump_image_position ){

	?>
		<!-- wcf-bump-order-field-wrap -->
		<div class="wcf-bump-order-field-wrap">

			<div class="wcf-bump-order-text">
				<div class="wcf-bump-order-label">
		<?php if( !$image_available ) { ?>
					<?php if( isset( $bump_order_blinking_arrow ) ){ echo $bump_order_blinking_arrow;} ?>
						<input type="checkbox" id="wcf-bump-order-cb" class="wcf-bump-order-cb"  name="<?php echo 'wcf-bump-order-cb-'. $ob_id ?>" value="<?php echo $ob_id; ?>" <?php checked( $order_bump_checked, true, true ); ?> data-ob_data="<?php echo htmlentities( $bump_offer_data) ?>">
					<?php }?>
	<?php if( !empty( $order_bump_title ) ){ ?>
						<label><?php echo wp_kses_post( $order_bump_title ); ?></label>
					<?php }?>
				</div>

	<?php if( !empty( $order_bump_desc ) ) { ?>
					<div class="wcf-bump-order-desc"><?php echo wp_kses_post( $order_bump_desc ); ?></div>
				<?php } ?>
	<?php if( 'yes' === $display_quantity_field ) { ?>
					<div class="wcf-ob-qty-selection-wrap">
						<span class="wcf-ob-qty-selection-btn wcf-ob-qty-decrement wcf-ob-qty-change-icon">-</span>
						<input type="number" class="wcf-order-bump-quantity-updater" name="wcf_order_bump_quantity" placeholder="1" value="<?php echo esc_html( $order_bump_product_quantity );?>" min="1" />
						<span class="wcf-ob-qty-selection-btn wcf-ob-qty-increment wcf-ob-qty-change-icon">+</span>
					</div>
				<?php } ?>
			</div>

	<?php if( !empty( $bump_order_image ) ){ ?>
			<div class="wcf-bump-order-image">
				<img src="<?php if( isset( $bump_order_image ) ){ echo esc_url( $bump_order_image ); }?>" class="wcf-image" />
			</div>
	<?php } ?>

	<?php
		// If the image is set then show this section.
		if( $image_available ){  ?>
					<div class="wcf-bump-order-action">
						<input type="checkbox" id="wcf-bump-order-cb" class="wcf-bump-order-cb"  name="<?php echo 'wcf-bump-order-cb-'. $ob_id ?>" value="<?php echo $ob_id; ?>" <?php checked( $order_bump_checked, true, true ); ?> data-ob_data="<?php echo htmlentities( $bump_offer_data) ?>">
						<?php if( isset( $bump_order_blinking_arrow ) ){ echo $bump_order_blinking_arrow;} ?>
					</div>
			<?php } ?>

		</div>
	<?php
		}
	?>

	</div>
</div>
 <!-- Main Div Close -->
<?php
	// @codingStandardsIgnoreEnd
