<?php
/**
 * AB test factory.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * AB test factory
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Ab_Test_Factory {

	/**
	 * Member Variable
	 *
	 * @var int step_id
	 */
	private $step_id;

	/**
	 * Member Variable
	 *
	 * @var int control_step_id
	 */
	private $control_step_id;

	/**
	 * Member Variable
	 *
	 * @var int flow_id
	 */
	private $flow_id;


	/**
	 * Member Variable
	 *
	 * @var string step_type
	 */
	private $step_type;

	/**
	 * Member Variable
	 *
	 * @var int flow_steps
	 */
	private $flow_steps;

	/**
	 * Member Variable
	 *
	 * @var int ab_test
	 */
	private $ab_test;

	/**
	 * Member Variable
	 *
	 * @var int start_ab_test
	 */
	private $start_ab_test;

	/**
	 * Member Variable
	 *
	 * @var int all_variations
	 */
	private $all_variations;

	/**
	 * Member Variable
	 *
	 * @var int show_variation_id
	 */
	private $show_variation_id;

	/**
	 *  Constructor
	 *
	 * @param int $id step id.
	 */
	public function __construct( $id = null ) {

		if ( null !== $id ) {

			$this->step_id = intval( $id );

			/* Data from step */
			$this->ab_test = get_post_meta( $this->step_id, 'wcf-ab-test', true );

			if ( $this->ab_test ) {

				$this->flow_id   = get_post_meta( $this->step_id, 'wcf-flow-id', true );
				$control_step_id = get_post_meta( $this->step_id, 'wcf-control-step', true );

				$this->control_step_id = empty( $control_step_id ) ? $this->step_id : intval( $control_step_id );
				$this->step_type       = get_post_meta( $this->step_id, 'wcf-step-type', true );

				/* Data from flow */
				$this->flow_id    = get_post_meta( $this->step_id, 'wcf-flow-id', true );
				$this->flow_steps = get_post_meta( $this->flow_id, 'wcf-steps', true );

				/* Set variaiton data */
				$this->set_ab_test();
			}
		}
	}

	/**
	 * Is ab test enable
	 */
	public function is_ab_test_enable() {

		if ( $this->ab_test && $this->start_ab_test ) {

			return true;
		}

		return false;
	}

	/**
	 * Get flow id
	 */
	public function get_flow_id() {
		return $this->flow_id;
	}

	/**
	 * Get step id
	 */
	public function get_step_id() {
		return $this->step_id;
	}

	/**
	 * Get flow steps
	 */
	public function get_flow_steps() {
		return $this->flow_steps;
	}

	/**
	 * Set flow steps map
	 */
	public function check_step_cookie() {

		$cookie_name = CARTFLOWS_AB_TEST_COOKIE . $this->control_step_id;

		if ( isset( $_COOKIE[ $cookie_name ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Set flow steps map
	 */
	public function get_step_cookie() {

		$cookie_name = CARTFLOWS_AB_TEST_COOKIE . $this->control_step_id;

		if ( isset( $_COOKIE[ $cookie_name ] ) ) {
			return intval( $_COOKIE[ $cookie_name ] ); //phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE
		}

		return 0;
	}

	/**
	 * Set visited cookie
	 */
	public function set_step_cookie() {

		$cookie_name = CARTFLOWS_AB_TEST_COOKIE . $this->control_step_id;
		$cookiepath  = $this->get_cookiepath();
		$expire_time = time() + ( 30 * 24 * 60 * MINUTE_IN_SECONDS );
		$value       = $this->show_variation_id;

		setcookie( $cookie_name, $value, $expire_time, $cookiepath, COOKIE_DOMAIN, CARTFLOWS_HTTPS );   //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie
	}

	/**
	 *  Get cookie path
	 *
	 * @return string cookiepath.
	 */
	public function get_cookiepath() {

		return COOKIEPATH ? COOKIEPATH : '/';
	}

	/**
	 * Set ab test
	 */
	public function set_ab_test() {

		foreach ( $this->flow_steps as $in => $data ) {

			if ( $this->control_step_id === $data['id'] ) {

				$this->all_variations = isset( $data['ab-test-variations'] ) ? $data['ab-test-variations'] : array();
				$this->start_ab_test  = isset( $data['ab-test-start'] ) ? $data['ab-test-start'] : false;
				break;
			}
		}
	}

	/**
	 * Run ab test
	 *
	 * @return string redirect.
	 */
	public function run_ab_test() {

		// If cookie exists get already previewed id.
		if ( $this->check_step_cookie() ) {

			$this->show_variation_id = $this->get_step_cookie();
		} else {

			$this->show_variation_id = $this->choose_display_variation();

			// Set preview variation step in cookie.
			$this->set_step_cookie();
		}

		// If show variation step is equal current step. Return.
		if ( $this->show_variation_id === $this->step_id ) {
			return;
		}

		$this->redirect_to_step( $this->show_variation_id );
	}

	/**
	 * Handle redirection to given step.
	 *
	 * @param int $step_id step id.
	 */
	public function redirect_to_step( $step_id ) {

		$url_to_redirect = get_permalink( $step_id );

		// If post exists.
		if ( $url_to_redirect ) {
			$abtest_settings = Cartflows_Pro_Helper::get_abtest_settings();
			$override        = 'enable' === $abtest_settings['override_permalink'] ? true : false;
			if ( apply_filters( 'cartflows_ab_test_override_variation_content', $override ) ) {
				$this->override_variation_content_to_control( $this->show_variation_id );
			} else {
				if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
					$query_string    = sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) );
					$url_to_redirect = add_query_arg( $query_string, '', $url_to_redirect );
				}

				// Redirect to variation id.
				wp_safe_redirect( $url_to_redirect );
				die();
			}
		}

	}

	/**
	 * Redirect to winner step.
	 *
	 * @param int $step_id step id.
	 */
	public function maybe_redirect_winner_step( $step_id ) {

		// In case if user is trying to visit old archived variation after completing A/B test.
		$is_winner_step_id = get_post_meta( $step_id, 'wcf-winner-step', true );

		$step_to_redirect = $is_winner_step_id ? $is_winner_step_id : get_post_meta( $step_id, 'wcf-control-step', true );

		if ( $step_to_redirect ) {
			$this->redirect_to_step( $step_to_redirect );
		}
	}

	/**
	 * Choose variation based on current traffic.
	 *
	 * @return int variation id.
	 */
	public function choose_display_variation() {

		$rand_num    = wp_rand( 0, 100 );
		$measurement = 0;

		foreach ( $this->all_variations as $variation ) {

			$traffic = intval( $variation['traffic'] );

			if ( ( $rand_num >= $measurement ) && ( $rand_num <= ( $measurement + $traffic ) ) ) {

				return $variation['id'];
			}

			$measurement += $traffic;
		}

		return $this->control_step_id;
	}

	/**
	 * Overrides variation content to control url
	 *
	 * @param int $variation_id chosen variation id.
	 * @return void
	 * @since X.X.X
	 */
	public function override_variation_content_to_control( $variation_id ) {
		$variant = get_post( $variation_id );

		if ( $variant && 'publish' === $variant->post_status ) {

			if ( isset( $GLOBALS['posts'][0] ) ) {
				$GLOBALS['posts'][0] = $variant; //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}

			if ( isset( $GLOBALS['wp_the_query']->post ) ) {
				$GLOBALS['wp_the_query']->post = $variant; //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}

			$GLOBALS['post'] = $variant; //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}
}
