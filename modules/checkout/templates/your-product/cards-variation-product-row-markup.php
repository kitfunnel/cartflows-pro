<?php
/**
 * Cards - Variation ptoduct row for cards template.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

echo wp_kses_post( $highlight_data['html_markup'] );

?>
<div class="wcf-item">
	<?php

	if ( 'force-all' === $selection_type && 'inline' === $type ) {
		include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/your-product/item-variation-inline-selector.php';
	}

	if ( 'single-selection' === $selection_type || 'multiple-selection' === $selection_type ) {

		if ( 'single-selection' === $selection_type ) { // single selection.
			include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/your-product/item-radio-selector.php';
		} else { // multiple selection.
			include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/your-product/item-checkbox-selector.php';
		}
	}

	require CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/your-product/item-image.php';
	?>
	<div class="wcf-item-content-options">
		<?php
		require CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/your-product/item-title.php';
		if ( $data['variable'] && isset( $type ) && 'popup' === $type ) {
			include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/your-product/item-variation-popup-selector.php';
		}
		require CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/your-product/item-subtext.php';

		require CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/your-product/item-quantity.php';

		require CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/your-product/item-price.php';
		?>
	</div>
</div>
<?php
