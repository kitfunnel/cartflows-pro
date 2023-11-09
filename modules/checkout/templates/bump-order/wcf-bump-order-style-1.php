<?php
// @codingStandardsIgnoreStart

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="wcf-bump-order-wrap wcf-bump-order-<?php echo $ob_id ; ?> wcf-bump-order-style-1 wcf-<?php echo $position; ?> wcf-ob-column-<?php echo $bump_layout_width; ?>">


	<?php $this->get_order_bump_hidden_data( $product_id, $order_bump_checked ); ?>

	<!-- wcf-bump-order-content -->
	<div class="wcf-bump-order-content wcf-bump-order-image-<?php echo $bump_image_position; ?>">
		<!-- wcf-bump-order-field-wrap -->
		<div class="wcf-bump-order-field-wrap">

		<?php
			if( !empty( $bump_image_position ) && 'right' !== $bump_image_position ){
		?>
			<label>
			<?php if( isset( $bump_order_blinking_arrow ) ){ echo $bump_order_blinking_arrow;} ?>
				<input type="checkbox" id="wcf-bump-order-cb" class="wcf-bump-order-cb"  name="<?php echo 'wcf-bump-order-cb-'. $ob_id ?>" value="<?php echo $ob_id; ?>" <?php checked( $order_bump_checked, true, true ); ?> data-ob_data="<?php echo htmlentities( $bump_offer_data) ?>">
				<span class="wcf-bump-order-label"><?php echo wp_kses_post( $order_bump_label ); ?>

				</span>
			</label>
		<?php
			}else if( ! empty( $bump_image_position ) && 'right' === $bump_image_position ){
		?>
			<label>
				<span class="wcf-bump-order-label"><?php echo wp_kses_post( $order_bump_label ); ?>
				<input type="checkbox" id="wcf-bump-order-cb" class="wcf-bump-order-cb"  name="<?php echo 'wcf-bump-order-cb-'. $ob_id ?>" value="<?php echo $ob_id; ?>" <?php checked( $order_bump_checked, true, true ); ?> data-ob_data="<?php echo htmlentities( $bump_offer_data) ?>">
		<?php if( isset( $bump_order_blinking_arrow ) ){ echo $bump_order_blinking_arrow;} ?>
				</span>
			</label>
		<?php
			}
		?>
		</div>
		<!-- wcf-bump-order-field-wrap -->


		<!-- wcf-content-wrap -->
		<div class="wcf-content-container">
			<?php
				if( !empty( $bump_image_position ) && 'right' !== $bump_image_position ){
			?>
				<?php
				if( isset( $bump_order_image ) && ! empty( $bump_order_image ) ){
				?>
					<!-- Left side box -->
					<div class="wcf-bump-order-offer-content-left wcf-bump-order-image">
						<img src="<?php if( isset( $bump_order_image ) ){ echo esc_url( $bump_order_image );} ?>" class="wcf-image" />
					</div>
					<!-- Left side box  -->
				<?php
					}
				?>
				<!-- Right side box  -->
				<div class="wcf-bump-order-offer-content-right">

					<!-- Offer box  -->
					<div class="wcf-bump-order-offer">
						<span class="wcf-bump-order-bump-highlight"><?php echo wp_kses_post( $order_bump_hl_text ); ?>
						</span>
					</div>
					<!-- Offer box  -->

					<!-- wcf-bump-order-desc -->
					<div class="wcf-bump-order-desc">
				<?php echo wp_kses_post( $order_bump_desc ); ?>
				<?php if( 'yes' === $display_quantity_field ) { ?>
					<div class="wcf-ob-qty-selection-wrap">
						<span class="wcf-ob-qty-selection-btn wcf-ob-qty-decrement wcf-ob-qty-change-icon">-</span>
						<input type="number" class="wcf-order-bump-quantity-updater" name="wcf_order_bump_quantity" placeholder="1" value="<?php echo esc_html( $order_bump_product_quantity );?>" min="1" />
						<span class="wcf-ob-qty-selection-btn wcf-ob-qty-increment wcf-ob-qty-change-icon">+</span>
					</div>
				<?php } ?>
					</div>
					<!-- wcf-bump-order-desc -->
				</div>
				<!-- Right side box  -->

			<?php }else{
			?>
				<!-- Right side box  -->
				<div class="wcf-bump-order-offer-content-right">

					<!-- Offer box  -->
					<div class="wcf-bump-order-offer">
						<span class="wcf-bump-order-bump-highlight"><?php echo wp_kses_post( $order_bump_hl_text ); ?>
						</span>
					</div>
					<!-- Offer box  -->

					<!-- wcf-bump-order-desc -->
					<div class="wcf-bump-order-desc">
						<?php echo wp_kses_post( $order_bump_desc ); ?>
					</div>
					<!-- wcf-bump-order-desc -->
				</div>
				<!-- Right side box  -->

				<?php
					if( isset( $bump_order_image ) && ! empty( $bump_order_image ) ){
				?>
					<!-- Left side box -->
					<div class="wcf-bump-order-offer-content-left wcf-bump-order-image">
						<img src="<?php if( isset( $bump_order_image ) ){ echo esc_url( $bump_order_image );} ?>" class="wcf-image" />
					</div>
					<!-- Left side box  -->
				<?php
					}
				?>

			<?php
				}
			?>
		</div>
		<!-- wcf-content-wrap -->
	</div>
	<!-- wcf-bump-order-content -->

</div>
 <!-- Main Div Close -->
<?php
	// @codingStandardsIgnoreEnd
