<?php
/**
 * CartFlows Admin Helper.
 *
 * @package CartFlows
 */

namespace CartflowsProAdmin\AdminCore\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class AdminHelper.
 */
class AdminHelper {

	/**
	 * Get flow meta options.
	 *
	 * @param int    $ob_id post id.
	 * @param string $title order bump title.
	 * @param array  $order_bumps all order bumps.
	 *
	 * @return array.
	 */
	public static function add_default_order_bump_data( $ob_id, $title, $order_bumps ) {

		$default_meta = \Cartflows_Pro_Checkout_Default_Meta::get_instance()->order_bump_default_meta();

		foreach ( $default_meta as $key => $value ) {
			$new_ob[ $key ] = $value['default'];
		}

		// Update the dynamic values.
		$new_ob['id']    = $ob_id;
		$new_ob['title'] = $title;

		array_push( $order_bumps, $new_ob );

		return $order_bumps;
	}

	/**
	 * Regenerate CSS for speicfic step.
	 *
	 * @param int $step_id step id.
	 */
	public static function clear_current_step_css( $step_id ) {

		update_post_meta( $step_id, 'wcf-pro-dynamic-css-version', time() );
	}

	/**
	 * Prepare new custom meta fields
	 *
	 * @param array  $new_field new field.
	 * @param string $post_id post id.
	 * @param string $type type.
	 */
	public static function prepare_checkout_field_settings( $new_field, $post_id, $type ) {

		$key           = $new_field['key'];
		$checkout_meta = \Cartflows_Checkout_Meta_Data::get_instance();
		$field_args    = $checkout_meta->prepare_field_arguments( $key, $new_field, $post_id, $type );

		$new_field = \Cartflows_Helper::get_instance()->prepare_custom_field_settings( $new_field, $key, $field_args, $type, 'checkout' );
		return $new_field;
	}

	/**
	 * Prepare new custom meta fields
	 *
	 * @param array  $new_field new field.
	 * @param string $post_id post id.
	 * @param string $type post id.
	 */
	public static function prepare_optin_field_settings( $new_field, $post_id, $type ) {

		$key        = $new_field['key'];
		$optin_meta = \Cartflows_Optin_Meta_Data::get_instance();
		$field_args = $optin_meta->prepare_field_arguments( $key, $new_field, $post_id, $type );

		$new_field = \Cartflows_Helper::get_instance()->prepare_custom_field_settings( $new_field, $key, $field_args, $type, 'optin' );

		return $new_field;
	}
}
