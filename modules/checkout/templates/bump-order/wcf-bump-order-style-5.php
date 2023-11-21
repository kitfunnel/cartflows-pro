<?php
// @codingStandardsIgnoreStart

$image_available = !empty( $bump_order_image ) ? true : false;

?>
<div class="wcf-bump-order-wrap wcf-bump-order-<?php echo $ob_id; ?> wcf-bump-order-style-5 wcf-<?php echo $position; ?> wcf-ob-column-<?php echo $bump_layout_width; ?>">

	<?php $this->get_order_bump_hidden_data( $product_id, $order_bump_checked ); ?>

	<!-- wcf-bump-order-content -->
	<div class="wcf-bump-order-content">
		<!-- wcf-bump-order-field-wrap -->
		<div class="wcf-bump-order-field-wrap">

			<div class="wcf-bump-order-info wcf-bump-order-image-<?php echo $bump_image_position; ?>">

				<?php
					if( !empty( $bump_image_position ) && 'right' === $bump_image_position ){
				?>
					<!-- Order bump description & action section -->
					<div class="wcf-bump-order-text">
						<div class="wcf-bump-order-label">
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

				<?php if ( 'checkbox' === $order_bump_action_element ) { ?>
							<div class="wcf-bump-order-action">
								<?php if( isset( $bump_order_blinking_arrow ) ){ echo $bump_order_blinking_arrow;} ?>
								<input type="checkbox" id="wcf-bump-order-cb" class="wcf-bump-order-cb"  name="<?php echo 'wcf-bump-order-cb-'. $ob_id ?>" value="<?php echo $ob_id; ?>" <?php checked( $order_bump_checked, true, true ); ?> data-ob_data="<?php echo htmlentities( $bump_offer_data) ?>">
								<?php if( !empty( $order_bump_label ) ){ ?>
										<label><?php echo wp_kses_post( $order_bump_label ); ?></label>
								<?php }?>
							</div>
						<?php } ?>

				<?php if ( 'button' === $order_bump_action_element ) { ?>
							<div class="wcf-bump-order-action wcf-ob-action-button">
							<input type="checkbox" id="wcf-bump-order-cb" class="wcf-bump-order-cb"  name="<?php echo 'wcf-bump-order-cb-'. $ob_id ?>" value="<?php echo $ob_id; ?>" <?php checked( $order_bump_checked, true, true ); ?> data-ob_data="<?php echo htmlentities( $bump_offer_data) ?>" data-add="<?php esc_attr_e( 'Add', 'cartflows-pro' ) ?>" data-remove="<?php esc_attr_e( 'Remove', 'cartflows-pro' ) ?>">
								<?php if( $order_bump_checked ){ ?>
									<span class="wcf-bump-order-cb-button wcf-bump-remove-from-cart"  data-removing="<?php esc_attr_e( 'Removing', 'cartflows-pro' ) ?>"><?php echo esc_html__( 'Remove', 'cartflows-pro' ) ?></span>
								<?php } else { ?>
									<span class="wcf-bump-order-cb-button wcf-bump-add-to-cart" data-adding="<?php esc_attr_e( 'Adding', 'cartflows-pro' ) ?>"><?php echo esc_html__( 'Add', 'cartflows-pro' ) ?></span>
								<?php } ?>
							</div>
						<?php } ?>
					</div>

				<?php if( !empty( $bump_order_image ) ){ ?>
						<!-- Order bump image section -->
						<div class="wcf-bump-order-image">
							<img src="<?php if( isset( $bump_order_image ) ){ echo esc_url( $bump_order_image ); }?>" alt='' class="wcf-image" />
						</div>
					<?php } ?>
				<?php
					}else{

				?>
				<?php if( !empty( $bump_order_image ) ){ ?>
					<!-- Order bump image section -->
					<div class="wcf-bump-order-image">
						<img src="<?php if( isset( $bump_order_image ) ){ echo esc_url( $bump_order_image ); }?>" class="wcf-image" />
					</div>
				<?php } ?>

				<!-- Order bump description & action section -->
				<div class="wcf-bump-order-text">
					<div class="wcf-bump-order-label">
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

				<?php if ( 'checkbox' === $order_bump_action_element ) { ?>
						<div class="wcf-bump-order-action">
							<?php if( isset( $bump_order_blinking_arrow ) ){ echo $bump_order_blinking_arrow;} ?>
							<input type="checkbox" id="wcf-bump-order-cb" class="wcf-bump-order-cb"  name="<?php echo 'wcf-bump-order-cb-'. $ob_id ?>" value="<?php echo $ob_id; ?>" <?php checked( $order_bump_checked, true, true ); ?> data-ob_data="<?php echo htmlentities( $bump_offer_data) ?>">
							<?php if( !empty( $order_bump_label ) ){ ?>
									<label><?php echo wp_kses_post( $order_bump_label ); ?></label>
							<?php }?>
						</div>
					<?php } ?>

				<?php if ( 'button' === $order_bump_action_element ) { ?>
						<div class="wcf-bump-order-action wcf-ob-action-button">
							<input type="checkbox" id="wcf-bump-order-cb" class="wcf-bump-order-cb"  name="<?php echo 'wcf-bump-order-cb-'. $ob_id ?>" value="<?php echo $ob_id; ?>" <?php checked( $order_bump_checked, true, true ); ?> data-ob_data="<?php echo htmlentities( $bump_offer_data) ?>" data-add="<?php esc_attr_e( 'Add', 'cartflows-pro' ) ?>" data-remove="<?php esc_attr_e( 'Remove', 'cartflows-pro' ) ?>">
							<?php if( $order_bump_checked ){ ?>
									<span class="wcf-bump-order-cb-button wcf-bump-remove-from-cart"  data-removing="<?php esc_attr_e( 'Removing', 'cartflows-pro' ) ?>"><?php echo esc_html__( 'Remove', 'cartflows-pro' ) ?></span>
								<?php } else { ?>
									<span class="wcf-bump-order-cb-button wcf-bump-add-to-cart" data-adding="<?php esc_attr_e( 'Adding', 'cartflows-pro' ) ?>"><?php echo esc_html__( 'Add', 'cartflows-pro' ) ?></span>
								<?php } ?>
						</div>
					<?php } ?>

				</div>
			<?php
				}
			?>

			</div>

		</div>

	</div>
</div>
 <!-- Main Div Close -->
<?php
	// @codingStandardsIgnoreEnd
