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

		if ( 'billing' === $type ) {
			$name = 'wcf_field_order_billing[' . $key . ']';
		} else {
			$name = 'wcf_field_order_shipping[' . $key . ']';
		}

		$is_checkbox    = false;
		$is_radiobutton = false;
		$is_require     = false;
		$is_select      = false;
		$display        = 'none';

		$type = $field_args['type'];

		if ( 'checkbox' == $field_args['type'] ) {
			$is_checkbox = true;
		}

		if ( 'radio' == $field_args['type'] ) {
			$is_radiobutton = true;
		}

		if ( 'yes' == $field_args['required'] ) {
			$is_require = true;
		}

		if ( 'yes' == $field_args['optimized'] ) {
			$is_optimized = true;
		}

		if ( 'select' == $field_args['type'] ) {
			$is_select = true;
			$display   = 'block';
		}

		$new_field['required']  = 'yes' === $new_field['required'] ? 'yes' : 'no';
		$new_field['optimized'] = 'yes' === $new_field['optimized'] ? 'yes' : 'no';

		$new_field['field_options'] = array(
			'enable-field'  => array(
				'type'  => 'checkbox',
				'label' => __( 'Enable Field', 'cartflows-pro' ),
				'name'  => $name . '[enabled]',
				'value' => $field_args['enabled'],
			),
			'select-width'  => array(
				'type'    => 'select',
				'label'   => __( 'Field Width', 'cartflows-pro' ),
				'name'    => $name . '[width]',
				'value'   => $field_args['width'],
				'options' => array(
					array(
						'value' => '33',
						'label' => esc_html__( '33%', 'cartflows-pro' ),
					),
					array(
						'value' => '50',
						'label' => esc_html__( '50%', 'cartflows-pro' ),
					),
					array(
						'value' => '100',
						'label' => esc_html__( '100%', 'cartflows-pro' ),
					),
				),

			),
			'field-label'   => array(
				'type'  => 'text',
				'label' => __( 'Field Label', 'cartflows-pro' ),
				'name'  => $name . '[label]',
				'value' => $field_args['label'],
			),
			'field-name'    => array(
				'type'     => 'text',
				'label'    => __( 'Field ID', 'cartflows-pro' ),
				'name'     => $name . '[key]',
				'value'    => $field_args['key'],
				'readonly' => true,
			),

			'field-default' => $is_checkbox ?
				array(
					'type'    => 'select',
					'label'   => __( 'Default', 'cartflows-pro' ),
					'name'    => $name . '[default]',
					'value'   => $field_args['default'],
					'options' => array(
						array(
							'value' => '1',
							'label' => esc_html__( 'Checked', 'cartflows-pro' ),
						),
						array(
							'value' => '0',
							'label' => esc_html__( 'Un-Checked', 'cartflows-pro' ),
						),
					),
				) :

				array(
					'type'  => 'text',
					'label' => __( 'Default', 'cartflows-pro' ),
					'name'  => $name . '[default]',
					'value' => $field_args['default'],
				),
		);

		if ( $is_select ) {

			$new_field['field_options']['select-options'] = array(
				'type'  => 'text',
				'label' => __( 'Options', 'cartflows-pro' ),
				'name'  => $name . '[options]',
				'value' => $field_args['options'],
			);
		}

		if ( $is_radiobutton ) {

			$new_field['field_options']['radio-options'] = array(
				'type'  => 'text',
				'label' => __( 'Options', 'cartflows-pro' ),
				'name'  => $name . '[options]',
				'value' => $field_args['options'],
			);
		}

		if ( ! in_array( $type, array( 'checkbox', 'select', 'radio' ), true ) ) {
			$new_field['field_options']['field-placeholder'] = array(
				'type'  => 'text',
				'label' => __( 'Placeholder', 'cartflows-pro' ),
				'name'  => $name . '[placeholder]',
				'value' => $field_args['placeholder'],
			);
		}

		$new_field['field_options']['show-in-email'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Show In Email', 'cartflows-pro' ),
			'name'  => $name . '[show_in_email]',
			'value' => $field_args['show_in_email'],
		);

		$new_field['field_options']['required-field'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Required', 'cartflows-pro' ),
			'name'  => $name . '[required]',
			'value' => $field_args['required'],
		);

		$new_field['field_options']['collapsed-field'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Collapsible', 'cartflows-pro' ),
			'name'  => $name . '[optimized]',
			'value' => $field_args['optimized'],
		);

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

		$name = 'wcf-optin-fields-billing[' . $key . ']';

		$is_checkbox    = false;
		$is_radiobutton = false;
		$is_require     = false;
		$is_select      = false;
		$display        = 'none';

		$type = $field_args['type'];

		if ( 'checkbox' == $field_args['type'] ) {
			$is_checkbox = true;
		}

		if ( 'radio' == $field_args['type'] ) {
			$is_radiobutton = true;
		}

		if ( 'yes' == $field_args['required'] ) {
			$is_require = true;
		}

		if ( 'yes' == $field_args['optimized'] ) {
			$is_optimized = true;
		}

		if ( 'select' == $field_args['type'] ) {
			$is_select = true;
			$display   = 'block';
		}

		$new_field['required']  = 'yes' === $new_field['required'] ? 'yes' : 'no';
		$new_field['optimized'] = 'yes' === $new_field['optimized'] ? 'yes' : 'no';

		$new_field['field_options'] = array(
			'enable-field'  => array(
				'type'  => 'checkbox',
				'label' => __( 'Enable Field', 'cartflows-pro' ),
				'name'  => $name . '[enabled]',
				'value' => $field_args['enabled'],
			),
			'select-width'  => array(
				'type'    => 'select',
				'label'   => __( 'Field Width', 'cartflows-pro' ),
				'name'    => $name . '[width]',
				'value'   => $field_args['width'],
				'options' => array(
					array(
						'value' => '33',
						'label' => esc_html__( '33%', 'cartflows-pro' ),
					),
					array(
						'value' => '50',
						'label' => esc_html__( '50%', 'cartflows-pro' ),
					),
					array(
						'value' => '100',
						'label' => esc_html__( '100%', 'cartflows-pro' ),
					),
				),

			),
			'field-label'   => array(
				'type'  => 'text',
				'label' => __( 'Field Label', 'cartflows-pro' ),
				'name'  => $name . '[label]',
				'value' => $field_args['label'],
			),
			'field-name'    => array(
				'type'     => 'text',
				'label'    => __( 'Field ID', 'cartflows-pro' ),
				'name'     => $name . '[key]',
				'value'    => $field_args['key'],
				'readonly' => true,
			),

			'field-default' => $is_checkbox ?
				array(
					'type'    => 'select',
					'label'   => __( 'Default', 'cartflows-pro' ),
					'name'    => $name . '[default]',
					'value'   => $field_args['default'],
					'options' => array(
						array(
							'value' => '1',
							'label' => esc_html__( 'Checked', 'cartflows-pro' ),
						),
						array(
							'value' => '0',
							'label' => esc_html__( 'Un-Checked', 'cartflows-pro' ),
						),
					),
				) :

				array(
					'type'  => 'text',
					'label' => __( 'Default', 'cartflows-pro' ),
					'name'  => $name . '[default]',
					'value' => $field_args['default'],
				),
		);

		if ( $is_select ) {

			$new_field['field_options']['select-options'] = array(
				'type'  => 'text',
				'label' => __( 'Options', 'cartflows-pro' ),
				'name'  => $name . '[options]',
				'value' => $field_args['options'],
			);
		}

		if ( $is_radiobutton ) {

			$new_field['field_options']['radio-options'] = array(
				'type'  => 'text',
				'label' => __( 'Options', 'cartflows-pro' ),
				'name'  => $name . '[options]',
				'value' => $field_args['options'],
			);
		}

		if ( ! in_array( $type, array( 'checkbox', 'select', 'radio' ), true ) ) {
			$new_field['field_options']['field-placeholder'] = array(
				'type'  => 'text',
				'label' => __( 'Placeholder', 'cartflows-pro' ),
				'name'  => $name . '[placeholder]',
				'value' => $field_args['placeholder'],
			);
		}

		$new_field['field_options']['show-in-email'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Show In Email', 'cartflows-pro' ),
			'name'  => $name . '[show_in_email]',
			'value' => $field_args['show_in_email'],
		);

		$new_field['field_options']['required-field'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Required', 'cartflows-pro' ),
			'name'  => $name . '[required]',
			'value' => $field_args['required'],
		);

		return $new_field;
	}
}
