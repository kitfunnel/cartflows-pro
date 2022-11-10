<?php
/**
 * Get current step data - factory.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Step factory
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Step_Factory extends Cartflows_Step_Factory {

	/**
	 * Check for offer page
	 *
	 * @return bool
	 */
	public function is_offer_page() {

		$step_type = $this->get_step_type();

		if ( 'upsell' === $step_type || 'downsell' === $step_type ) {

			return true;
		}

		return false;
	}

	/**
	 * Get next step id according to condition.
	 *
	 * @since 1.6.13
	 *
	 * @return bool|int
	 */
	public function get_next_step_id() {

		$next_step_id = false;

		$flow_id = $this->get_flow_id();

		if ( $flow_id ) {

			$flow_steps   = $this->get_flow_steps();
			$control_step = $this->get_control_step();

			if ( is_array( $flow_steps ) ) {

				foreach ( $flow_steps as $index => $data ) {

					if ( intval( $data['id'] ) === $control_step ) {

						$next_step_index = $index + 1;

						if ( isset( $flow_steps[ $next_step_index ] ) ) {

							$next_step_id = intval( $flow_steps[ $next_step_index ]['id'] );
						}

						break;
					}
				}
			}
		}

		return $next_step_id;
	}

	/**
	 * Get thank you page ID.
	 *
	 * @since 1.0.0
	 *
	 * @return bool|int
	 */
	public function get_thankyou_page_id() {

		$steps               = $this->get_flow_steps();
		$thankyou_step_id    = false;
		$thankyou_step_index = false;

		if ( empty( $steps ) ) {
			return $thankyou_step_id;
		}

		foreach ( $steps as $i => $step ) {

			if ( 'thankyou' === $step['type'] ) {

				$thankyou_step_id = intval( $step['id'] );
				break;
			}
		}

		return $thankyou_step_id;
	}

	/**
	 * Get prev control id according to condition.
	 *
	 * @since 1.6.13
	 *
	 * @return bool|int
	 */
	public function get_prev_control_id() {

		$prev_step_id = false;

		$flow_id = $this->get_flow_id();

		if ( $flow_id ) {

			$flow_steps   = $this->get_flow_steps();
			$control_step = $this->get_control_step();

			if ( is_array( $flow_steps ) ) {

				foreach ( $flow_steps as $index => $data ) {

					if ( intval( $data['id'] ) === $control_step ) {

						$prev_step_index = $index - 1;

						if ( isset( $flow_steps[ $prev_step_index ] ) ) {

							$prev_step_id = intval( $flow_steps[ $prev_step_index ]['id'] );
						}

						break;
					}
				}
			}
		}

		return $prev_step_id;
	}
}
