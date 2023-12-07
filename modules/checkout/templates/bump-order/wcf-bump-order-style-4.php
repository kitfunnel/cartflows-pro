<?php
// @codingStandardsIgnoreStart
?>
<div class="wcf-bump-order-wrap wcf-bump-order-<?php echo $ob_id; ?> wcf-bump-order-style-4 wcf-<?php echo $position; ?> wcf-ob-column-<?php echo $bump_layout_width; ?>">

	<?php $this->get_order_bump_hidden_data( $product_id, $order_bump_checked ); ?>

	<!-- wcf-bump-order-content -->
	<div class="wcf-bump-order-content wcf-bump-order-image-<?php echo $bump_image_position; ?>">
		<!-- wcf-bump-order-field-wrap -->
		<div class="wcf-bump-order-field-wrap">
			<?php
				if( !empty( $bump_image_position ) && 'right' !== $bump_image_position ){
			?>

				<?php if( !empty( $bump_order_image ) ){ ?>
					<div class="wcf-bump-order-image">
						<img src="<?php if( isset( $bump_order_image ) ){ echo esc_url( $bump_order_image ); }?>" class="wcf-image" />
					</div>
				<?php } ?>

				<div class="wcf-bump-order-text">
					<div class="wcf-bump-order-info">

			<?php if( !empty( $order_bump_title ) ) { ?>
							<div class="wcf-bump-order-label">
								<label><?php echo wp_kses_post( $order_bump_title ); ?></label>
							</div>
						<?php }?>

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

				<div class="wcf-bump-order-action">
					<input type="checkbox" id="wcf-bump-order-cb" class="wcf-bump-order-cb"  name="<?php echo 'wcf-bump-order-cb-'. $ob_id ?>" value="<?php echo $ob_id; ?>" <?php checked( $order_bump_checked, true, true ); ?> data-ob_data="<?php echo htmlentities( $bump_offer_data) ?>" data-add="<?php esc_attr_e( 'Add', 'cartflows-pro' ) ?>" data-remove="<?php esc_attr_e( 'Remove', 'cartflows-pro' ) ?>">

			<?php if( $order_bump_checked ){ ?>
						<span class="wcf-bump-order-cb-button wcf-bump-remove-from-cart" data-removing="<?php esc_attr_e( 'Removing', 'cartflows-pro' ) ?>"><?php echo esc_html__( 'Remove', 'cartflows-pro' ) ?></span>
					<?php } else { ?>
						<span class="wcf-bump-order-cb-button wcf-bump-add-to-cart" data-adding="<?php esc_attr_e( 'Adding', 'cartflows-pro' ) ?>"><?php echo esc_html__( 'Add', 'cartflows-pro' ) ?></span>
					<?php } ?>
				</div>

			<?php
				}else if( !empty( $bump_image_position ) && 'right' === $bump_image_position ){
			?>

				<div class="wcf-bump-order-action">
					<input type="checkbox" id="wcf-bump-order-cb" class="wcf-bump-order-cb"  name="<?php echo 'wcf-bump-order-cb-'. $ob_id ?>" value="<?php echo $ob_id; ?>" <?php checked( $order_bump_checked, true, true ); ?> data-ob_data="<?php echo htmlentities( $bump_offer_data) ?>" data-add="<?php esc_attr_e( 'Add', 'cartflows-pro' ) ?>" data-remove="<?php esc_attr_e( 'Remove', 'cartflows-pro' ) ?>">
			<?php if( $order_bump_checked ){ ?>
						<span class="wcf-bump-order-cb-button wcf-bump-remove-from-cart" data-removing="<?php esc_attr_e( 'Removing', 'cartflows-pro' ) ?>"><?php echo esc_html__( 'Remove', 'cartflows-pro' ) ?></span>
					<?php } else { ?>
						<span class="wcf-bump-order-cb-button wcf-bump-add-to-cart" data-adding="<?php esc_attr_e( 'Adding', 'cartflows-pro' ) ?>"><?php echo esc_html__( 'Add', 'cartflows-pro' ) ?></span>
					<?php } ?>
				</div>

				<div class="wcf-bump-order-text">
					<div class="wcf-bump-order-info">

			<?php if( !empty( $order_bump_title ) ) { ?>
							<div class="wcf-bump-order-label">
								<label><?php echo wp_kses_post( $order_bump_title ); ?></label>
							</div>
						<?php }?>

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

			<?php if( !empty( $bump_order_image ) ){ ?>
					<div class="wcf-bump-order-image">
						<img src="<?php if( isset( $bump_order_image ) ){ echo esc_url( $bump_order_image ); }?>" class="wcf-image" />
					</div>
				<?php } ?>

			<?php
				}
			?>

		</div>

	</div>
</div>
 <!-- Main Div Close -->
<?php
	// @codingStandardsIgnoreEnd
