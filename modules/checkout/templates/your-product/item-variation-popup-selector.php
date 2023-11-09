<?php
/**
 * Variation popup selector
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="wcf-item-choose-options"><a href="#" class="wcf-variable-item-popup-text" data-product="<?php echo esc_attr( $parent_id ); ?>" data-variation="<?php echo esc_attr( $rc_product_id ); ?>">
	<?php echo esc_html( $this->variation_popup_toggle_text() ); ?></a>
</div>
