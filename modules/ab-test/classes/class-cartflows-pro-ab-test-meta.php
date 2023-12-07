<?php
/**
 * Base Offer meta.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Meta Boxes setup
 */
class Cartflows_Pro_Ab_Test_Meta {


	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		add_filter( 'cartflows_admin_js_localize', array( $this, 'localize_vars' ) );

		/* Localized vars for CartFlows New UI */
		add_filter( 'cartflows_admin_localized_vars', array( $this, 'localize_vars' ) );

		/* Ajax Calls */
		add_action( 'wp_ajax_cartflows_delete_ab_test_step', array( $this, 'delete_ab_test_variation' ) );
		add_action( 'wp_ajax_cartflows_archive_ab_test_step', array( $this, 'archive_ab_test_variation' ) );
		add_action( 'wp_ajax_cartflows_create_ab_test_variation', array( $this, 'create_ab_test_variation' ) );
		add_action( 'wp_ajax_cartflows_start_ab_test', array( $this, 'start_ab_test' ) );
		add_action( 'wp_ajax_cartflows_declare_ab_test_winner', array( $this, 'declare_ab_test_winner' ) );
		add_action( 'wp_ajax_cartflows_clone_ab_test_step', array( $this, 'clone_ab_test_variation' ) );

		add_action( 'wp_ajax_cartflows_restore_archive_ab_test_variation', array( $this, 'restore_archive_ab_test_variation' ) );
		add_action( 'wp_ajax_cartflows_delete_archive_ab_test_variation', array( $this, 'delete_archive_ab_test_variation' ) );

	}

	/**
	 * Localize variables in admin
	 *
	 * @param array $vars variables.
	 */
	public function localize_vars( $vars ) {

		$ajax_actions = array(
			'wcf_create_ab_test_variation',
			'wcf_start_ab_test',
			'wcf_declare_ab_test_winner',
			'wcf_save_ab_test_settings',
			'wcf_delete_ab_test_step',
			'wcf_archive_ab_test_step',
			'wcf_clone_ab_test_step',
			'wcf_restore_archive_ab_test_variation',
			'wcf_delete_archive_ab_test_variation',
		);

		foreach ( $ajax_actions as $action ) {

			$vars[ $action . '_nonce' ] = wp_create_nonce( str_replace( '_', '-', $action ) );
		}

		return $vars;
	}

	/**
	 * Delete variation and archive it.
	 *
	 * @param int  $flow_id Flow id.
	 * @param int  $step_id Step id.
	 * @param bool $delete_data Delete step and it's meta.
	 */
	public function delete_variation( $flow_id, $step_id, $delete_data = false ) {

		$flow_steps   = get_post_meta( $flow_id, 'wcf-steps', true );
		$control_step = get_post_meta( $step_id, 'wcf-control-step', true );
		$control_step = empty( $control_step ) ? $step_id : intval( $control_step );

		if ( is_array( $flow_steps ) && ! empty( $flow_steps ) ) {
			foreach ( $flow_steps as $index => $data ) {

				if ( intval( $data['id'] ) === $control_step ) {

					$all_variations      = $flow_steps[ $index ]['ab-test-variations'];
					$archived_variations = $flow_steps[ $index ]['ab-test-archived-variations'];

					if ( ! is_array( $archived_variations ) ) {
						$archived_variations = array();
					}

					if ( $control_step === $step_id && isset( $all_variations[1] ) ) {

						$step_to_update = $all_variations[1]['id'];

						$flow_steps[ $index ] = array(
							'id'    => intval( $step_to_update ),
							'title' => get_the_title( $step_to_update ),
							'type'  => get_post_meta( $step_to_update, 'wcf-step-type', true ),
						);

						foreach ( $all_variations as $v_index => $v_data ) {

							if ( $step_to_update !== $v_data['id'] ) {
								update_post_meta( $v_data['id'], 'wcf-control-step', $step_to_update );
							}
						}

						// Update the control step id in archived variations so if anyone visit it it will redirect to control step.
						foreach ( $archived_variations as $av_index => $av_data ) {

							if ( $step_to_update !== $av_data['id'] ) {
								update_post_meta( $av_data['id'], 'wcf-control-step', $step_to_update );
							}
						}

						delete_post_meta( $step_to_update, 'wcf-control-step' );

						do_action( 'cartflows_ab_test_update_control', $flow_id, $flow_steps[ $index ] );
					}

					// It mean this step is not available for ab test process until it is restored.
					delete_post_meta( $step_id, 'wcf-ab-test' );

					$current_time = current_time( 'Y-m-d H:i:s' );
					/* Add to archived list */
					$step_to_archive_data = array(
						'id'       => $step_id,
						'title'    => get_the_title( $step_id ),
						'note'     => get_post_meta( $step_id, 'wcf-step-note', true ),
						'deleted'  => $delete_data,
						'raw_date' => $current_time,
						'date'     => gmdate( 'M d, Y', strtotime( $current_time ) ),
					);

					if ( $delete_data ) {
						$archived_variations[] = $step_to_archive_data;
					} else {
						array_unshift( $archived_variations, $step_to_archive_data );
					}

					/* Delete the item from variation */
					if ( count( $all_variations ) > 1 ) {

						foreach ( $all_variations as $v_index => $v_data ) {

							if ( $step_id === $v_data['id'] ) {
								array_splice( $all_variations, $v_index, 1 );
							}
						}

						/* Update traffic after variation deleted */
						$traffic = array( 50, 50 );

						foreach ( $all_variations as $v_index => $v_data ) {

							if ( isset( $traffic[ $v_index ] ) ) {
								$all_variations[ $v_index ]['traffic'] = intval( $traffic[ $v_index ] );
							} else {
								$all_variations[ $v_index ]['traffic'] = 0;
							}
						}
					}

					$flow_steps[ $index ]['ab-test']                     = true;
					$flow_steps[ $index ]['ab-test-ui']                  = true;
					$flow_steps[ $index ]['ab-test-start']               = false;
					$flow_steps[ $index ]['ab-test-variations']          = $all_variations;
					$flow_steps[ $index ]['ab-test-archived-variations'] = $archived_variations;

					break;
				}
			}

			/* Set index order properly */
			$flow_steps = array_merge( $flow_steps );

			/* Update latest data */
			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

			if ( $delete_data ) {
				/* Delete step */
				wp_delete_post( $step_id, true );
			}
		}
	}

	/**
	 * Delete ab test delete.
	 *
	 * @return void
	 */
	public function delete_ab_test_variation() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-delete-ab-test-step', 'security' );

		$flow_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;


		$result = array(
			'status' => false,
			'reload' => false,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Step not deleted for flow - %s', 'cartflows-pro' ), $flow_id ),
		);

		if ( ! $flow_id || ! $step_id ) {
			wp_send_json( $result );
		}

		if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) || CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json_error( $result );
		}

		$this->delete_variation( $flow_id, $step_id, true );

		$result = array(
			'status' => true,
			'reload' => true,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Step deleted for flow - %s', 'cartflows-pro' ), $flow_id ),
		);

		wp_send_json( $result );
	}

	/**
	 * Delete ab test delete.
	 *
	 * @return void
	 */
	public function archive_ab_test_variation() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-archive-ab-test-step', 'security' );

		if ( isset( $_POST['post_id'] ) && isset( $_POST['step_id'] ) ) {
			$flow_id = intval( $_POST['post_id'] );
			$step_id = intval( $_POST['step_id'] );
		}

		$result = array(
			'status' => false,
			'reload' => false,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Can\'t archive this step - %1$s. Funnel - %2$s', 'cartflows-pro' ), $step_id, $flow_id ),
		);

		if ( ! $flow_id || ! $step_id ) {
			wp_send_json( $result );
		}

		$this->delete_variation( $flow_id, $step_id, false );

		$result = array(
			'status' => true,
			'reload' => true,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Step - %1$s archived. Funnel - %2$s', 'cartflows-pro' ), $step_id, $flow_id ),
		);

		wp_send_json( $result );
	}

	/**
	 * Create variation for current step
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function create_ab_test_variation() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-create-ab-test-variation', 'security' );

		$flow_id = isset( $_POST['flow_id'] ) ? intval( $_POST['flow_id'] ) : 0;
		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;

		$result = array(
			'status' => false,
			/* translators: %s step id */
			'text'   => sprintf( __( 'Can\'t create a variation for this step - %s', 'cartflows-pro' ), $step_id ),
		);

		if ( ! $flow_id || ! $step_id ) {
			wp_send_json( $result );
		}

		if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) || CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json_error( $result );
		}

		/* Enable abtest for step */
		update_post_meta( $step_id, 'wcf-ab-test', true );

		// Step - Clone step as a variation.
		$new_step_id = wcf_pro()->utils->clone_step( $step_id );

		if ( $new_step_id ) {

			// Step - Add control step as parent.
			update_post_meta( $new_step_id, 'wcf-control-step', $step_id );

			// Flow - Add ab test variations array.
			$show_variations = array(
				array(
					'id'      => $step_id,
					'traffic' => 50,
				),
				array(
					'id'      => $new_step_id,
					'traffic' => 50,
				),
			);

			$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

			if ( is_array( $flow_steps ) && ! empty( $flow_steps ) ) {
				foreach ( $flow_steps as $index => $step_data ) {

					if ( $step_data['id'] === $step_id ) {
						$flow_steps[ $index ]['ab-test']            = true;
						$flow_steps[ $index ]['ab-test-ui']         = true;
						$flow_steps[ $index ]['ab-test-variations'] = $show_variations;
						$flow_steps[ $index ]['ab-test-start']      = false;

						$archived_variations = array();

						if ( isset( $flow_steps[ $index ]['ab-test-archived-variations'] ) ) {
							$archived_variations = $flow_steps[ $index ]['ab-test-archived-variations'];
						}

						$flow_steps[ $index ]['ab-test-archived-variations'] = $archived_variations;
					}
				}

				update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

				$result = array(
					'status' => true,
					/* translators: %s flow id */
					'text'   => sprintf( __( 'Variation created for step - %s', 'cartflows-pro' ), $step_id ),
				);
			}
		}

		wp_send_json( $result );
	}

	/**
	 * Start split test for current variation
	 *
	 * @since 1.6.13
	 *
	 * @return void
	 */
	public function start_ab_test() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-start-ab-test', 'security' );

		$flow_id = isset( $_POST['flow_id'] ) ? intval( $_POST['flow_id'] ) : 0;
		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;

		$result = array(
			'status' => false,
			'text'   => __( 'Can\'t start a split test', 'cartflows-pro' ),
		);

		if ( ! $flow_id || ! $step_id ) {
			wp_send_json( $result );
		}

		if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) || CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json_error( $result );
		}

		$success_text = __( 'Stop Split Test', 'cartflows-pro' );
		$start_test   = true;
		$flow_steps   = get_post_meta( $flow_id, 'wcf-steps', true );

		if ( is_array( $flow_steps ) && ! empty( $flow_steps ) ) {
			foreach ( $flow_steps as $index => $step_data ) {

				if ( intval( $step_data['id'] ) === $step_id ) {

					if ( isset( $flow_steps[ $index ]['ab-test-start'] ) ) {

						if ( $flow_steps[ $index ]['ab-test-start'] ) {
							$flow_steps[ $index ]['ab-test-start'] = false;
							$start_test                            = false;
							$success_text                          = __( 'Start Split Test', 'cartflows-pro' );
						} else {
							$flow_steps[ $index ]['ab-test-start'] = true;
							$start_test                            = true;
						}
					} else {
						$flow_steps[ $index ]['ab-test-start'] = true;
						$start_test                            = true;
					}
				}
			}

			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

			$result = array(
				'status' => true,
				'start'  => $start_test,
				'text'   => $success_text,
			);
		}

		wp_send_json( $result );
	}

	/**
	 * Declare ab test winner
	 *
	 * @since 1.6.13
	 *
	 * @return void
	 */
	public function declare_ab_test_winner() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-declare-ab-test-winner', 'security' );

		$flow_id = isset( $_POST['flow_id'] ) ? intval( $_POST['flow_id'] ) : 0;
		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;

		$result = array(
			'status' => false,
			/* translators: %s step id */
			'text'   => sprintf( __( 'Can\'t update the winner for this step - %s', 'cartflows-pro' ), $step_id ),
		);

		if ( ! $flow_id || ! $step_id ) {
			wp_send_json( $result );
		}

		if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) || CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json_error( $result );
		}

		$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

		if ( ! is_array( $flow_steps ) ) {
			wp_send_json( $result );
		}

		$control_step = get_post_meta( $step_id, 'wcf-control-step', true );
		$control_step = empty( $control_step ) ? $step_id : intval( $control_step );

		foreach ( $flow_steps as $index => $data ) {

			if ( intval( $data['id'] ) === $control_step ) {

				$all_variations      = $flow_steps[ $index ]['ab-test-variations'];
				$archived_variations = $flow_steps[ $index ]['ab-test-archived-variations'];

				if ( ! is_array( $archived_variations ) ) {
					$archived_variations = array();
				}

				/* Update winner step */
				$flow_steps[ $index ] = array(
					'id'    => intval( $step_id ),
					'title' => get_the_title( $step_id ),
					'type'  => get_post_meta( $step_id, 'wcf-step-type', true ),
				);

				/* Remove step ab test trace */
				delete_post_meta( $step_id, 'wcf-ab-test' );
				delete_post_meta( $step_id, 'wcf-control-step' );

				do_action( 'cartflows_ab_test_update_control', $flow_id, $flow_steps[ $index ] );
				/* Update winner step in already archived variations. So if anyone visited so will reirect to winner step */
				foreach ( $archived_variations as $av_index => $av_data ) {

					if ( $step_id !== $av_data['id'] ) {
						update_post_meta( $av_data['id'], 'wcf-winner-step', $step_id );
					}
				}

				/* Archive all rest of variation */
				$current_time = current_time( 'Y-m-d H:i:s' );

				/* Add rest of variation to archived list */
				foreach ( $all_variations as $v_index => $v_data ) {

					if ( $step_id !== $v_data['id'] ) {

						$step_to_archive_data = array(
							'id'       => $v_data['id'],
							'title'    => get_the_title( $v_data['id'] ),
							'note'     => get_post_meta( $v_data['id'], 'wcf-step-note', true ),
							'deleted'  => false,
							'raw_date' => $current_time,
							'date'     => gmdate( 'M d, Y', strtotime( $current_time ) ),
						);

						array_unshift( $archived_variations, $step_to_archive_data );

						update_post_meta( $v_data['id'], 'wcf-winner-step', $step_id );
						delete_post_meta( $v_data['id'], 'wcf-ab-test' );

					}
				}

				/* Keep ab step data for analytics */
				$flow_steps[ $index ]['ab-test']                     = true;
				$flow_steps[ $index ]['ab-test-ui']                  = false;
				$flow_steps[ $index ]['ab-test-start']               = false;
				$flow_steps[ $index ]['ab-test-variations']          = array();
				$flow_steps[ $index ]['ab-test-archived-variations'] = $archived_variations;

				break;
			}
		}

		/* Set index order properly */
		$flow_steps = array_merge( $flow_steps );

		/* Update latest data */
		update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

		$result = array(
			'status' => true,
			/* translators: %s step id */
			'text'   => sprintf( __( 'Winner updated for this step - %s', 'cartflows-pro' ), $step_id ),
		);

		wp_send_json( $result );
	}

	/**
	 * Create variation for current step
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function clone_ab_test_variation() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-clone-ab-test-step', 'security' );

		if ( isset( $_POST['post_id'] ) && isset( $_POST['step_id'] ) && isset( $_POST['control_id'] ) ) {
			$flow_id    = intval( $_POST['post_id'] );
			$step_id    = intval( $_POST['step_id'] );
			$control_id = intval( $_POST['control_id'] );
		}

		$result = array(
			'status' => false,
			'reload' => true,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Can\'t clone this step - %1$s. Funnel - %2$s', 'cartflows-pro' ), $step_id, $flow_id ),
		);

		if ( ! $flow_id || ! $step_id || ! $control_id ) {
			wp_send_json( $result );
		}

		if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) || CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json_error( $result );
		}

		// Step - Clone step as a variation.
		$new_step_id = wcf_pro()->utils->clone_step( $step_id );

		if ( $new_step_id ) {

			// Step - Add control step as parent.
			update_post_meta( $new_step_id, 'wcf-control-step', $control_id );

			$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

			if ( is_array( $flow_steps ) && ! empty( $flow_steps ) ) {
				foreach ( $flow_steps as $index => $step_data ) {

					if ( $step_data['id'] === $control_id ) {

						$variations = $flow_steps[ $index ]['ab-test-variations'];

						if ( ! is_array( $variations ) ) {
							$variations = array();
						}

						// Flow - Add ab test variations array.
						$variations[] = array(
							'id'      => $new_step_id,
							'traffic' => 0,
						);

						$flow_steps[ $index ]['ab-test-variations'] = $variations;
					}
				}

				update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

				$result = array(
					'status' => true,
					'reload' => true,
					/* translators: %s flow id */
					'text'   => sprintf( __( 'Step - %1$s archived. Funnel - %2$s', 'cartflows-pro' ), $step_id, $flow_id ),
				);
			}
		}

		wp_send_json( $result );
	}



	/**
	 * Restore archived variation.
	 *
	 * @return void
	 */
	public function restore_archive_ab_test_variation() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-restore-archive-ab-test-variation', 'security' );

		if ( isset( $_POST['flow_id'] ) && isset( $_POST['step_id'] ) && isset( $_POST['control_id'] ) ) {
			$flow_id      = isset( $_POST['flow_id'] ) ? intval( $_POST['flow_id'] ) : 0;
			$step_id      = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
			$control_step = isset( $_POST['control_id'] ) ? intval( $_POST['control_id'] ) : 0;
		}

		$result = array(
			'status' => false,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Can\'t restore this variation - %1$s. Funnel - %2$s', 'cartflows-pro' ), $step_id, $flow_id ),
		);

		if ( ! $flow_id || ! $step_id || ! $control_step ) {
			wp_send_json( $result );
		}

		if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) || CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json_error( $result );
		}

		$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

		// Update control id to step.
		update_post_meta( $step_id, 'wcf-control-step', $control_step );

		// It mean this step is not archived and in process of ab test.
		update_post_meta( $step_id, 'wcf-ab-test', true );

		if ( is_array( $flow_steps ) && ! empty( $flow_steps ) ) {
			foreach ( $flow_steps as $index => $data ) {

				if ( intval( $data['id'] ) === $control_step ) {

					$all_variations      = $flow_steps[ $index ]['ab-test-variations'];
					$archived_variations = $flow_steps[ $index ]['ab-test-archived-variations'];

					/* Add to variation list list */
					$new_variation = array(
						'id'      => $step_id,
						'traffic' => 0,
					);

					$all_variations[] = $new_variation;

					/* Delete the item from archived list */
					foreach ( $archived_variations as $v_index => $v_data ) {

						if ( $step_id === $v_data['id'] ) {
							array_splice( $archived_variations, $v_index, 1 );
							delete_post_meta( $v_data['id'], 'wcf-winner-step' );
							break;
						}
					}

					$flow_steps[ $index ]['ab-test-variations']          = $all_variations;
					$flow_steps[ $index ]['ab-test-archived-variations'] = $archived_variations;

					break;
				}
			}

			/* Set index order properly */
			$flow_steps = array_merge( $flow_steps );

			/* Update latest data */
			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

			$result = array(
				'status' => true,
				/* translators: %s flow id */
				'text'   => sprintf( __( 'Variation - %1$s restored. Funnel  - %2$s', 'cartflows-pro' ), $step_id, $flow_id ),
			);
		}

		wp_send_json( $result );
	}

	/**
	 * Delete archived variation.
	 *
	 * @return void
	 */
	public function delete_archive_ab_test_variation() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-delete-archive-ab-test-variation', 'security' );

		if ( isset( $_POST['flow_id'] ) && isset( $_POST['step_id'] ) && isset( $_POST['control_id'] ) ) {
			$flow_id      = intval( $_POST['flow_id'] );
			$step_id      = intval( $_POST['step_id'] );
			$control_step = intval( $_POST['control_id'] );
		}

		$result = array(
			'status' => false,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Can\'t delete this variation - %1$s. Funnel - %2$s', 'cartflows-pro' ), $step_id, $flow_id ),
		);

		if ( ! $flow_id || ! $step_id || ! $control_step ) {
			wp_send_json( $result );
		}

		if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) || CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			wp_send_json_error( $result );
		}

		$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

		if ( is_array( $flow_steps ) && ! empty( $flow_steps ) ) {
			foreach ( $flow_steps as $index => $data ) {

				if ( intval( $data['id'] ) === $control_step ) {

					$archived_variations = $flow_steps[ $index ]['ab-test-archived-variations'];

					/* Mark as deleted, update time in the archived list */
					foreach ( $archived_variations as $v_index => $v_data ) {

						if ( $step_id === $v_data['id'] ) {
							unset( $archived_variations[ $v_index ] );
							break;
						}
					}

					$flow_steps[ $index ]['ab-test-archived-variations'] = array_values( $archived_variations );
					break;
				}
			}

			/* Set index order properly */
			$flow_steps = array_merge( $flow_steps );

			/* Update latest data */
			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

			/* Delete step */
			wp_delete_post( $step_id, true );

			$result = array(
				'status' => true,
				/* translators: %s flow id */
				'text'   => sprintf( __( 'Variation - %1$s deleted. Funnel  - %2$s', 'cartflows-pro' ), $step_id, $flow_id ),
			);
		}
		wp_send_json( $result );
	}
}


/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Ab_Test_Meta::get_instance();
