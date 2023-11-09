<?php
/**
 * Flow
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Automattic\WooCommerce\Utilities\OrderUtil;
/**
 * Analytics reports class.
 */
class Cartflows_Pro_Analytics_Reports {

	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 * Flow orders
	 *
	 * @var array flow_orders
	 */
	private static $flow_orders = array();

	/**
	 * Flow gross sell
	 *
	 * @var int flow_gross
	 */
	private static $flow_gross = 0;

	/**
	 * Flow visits
	 *
	 * @var array flow_visits
	 */
	private static $flow_visits = array();

	/**
	 * Steps data
	 *
	 * @var array step_data
	 */
	private static $step_data = array();

	/**
	 * Earnings for flow
	 *
	 * @var array flow_earnings
	 */
	private static $flow_earnings = array();

	/**
	 * Report interval
	 *
	 * @var int report_interval
	 */
	private static $report_interval = 30;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor function that initializes required actions and hooks
	 */
	public function __construct() {

		add_filter( 'cartflows_home_page_analytics', array( $this, 'get_home_page_analytics_data' ), 10, 3 );
	}


	/**
	 * Get home page analytics.
	 *
	 * @param array  $analytics_data analytics.
	 * @param string $start_date start date.
	 * @param string $end_date end date.
	 */
	public function get_home_page_analytics_data( $analytics_data, $start_date, $end_date ) {

		$orders           = $this->get_orders_by_all_flows( $start_date, $end_date );
		$gross_sale       = 0;
		$order_count      = 0;
		$total_bump_offer = 0;
		$cartflows_offer  = 0;

		if ( is_array( $orders ) && ! empty( $orders ) ) {

			foreach ( $orders as $order ) {

				$order_id = $order->ID;
				$order    = wc_get_order( $order_id );

				$user_id = $order->get_user_id();

				// skip the orders which are placed by the user whose user role is Administrator.
				if ( $user_id && user_can( $user_id, 'cartflows_manage_flows_steps' ) ) {
					continue;
				}

				$order_total = $order->get_total();
				$order_count++;

				if ( ! $order->has_status( 'cancelled' ) ) {
					$gross_sale += (float) $order_total;
				}

				$bump_product_id = $order->get_meta( '_wcf_bump_product' );
				$multiple_obs    = $order->get_meta( '_wcf_bump_products' );

				$separate_offer_order = $order->get_meta( '_cartflows_parent_flow_id' );

				// If Separate order for upsell/downsell is disabled i:e merge in parent order.
				if ( empty( $separate_offer_order ) ) {

					foreach ( $order->get_items() as $item_id => $item_data ) {

						$item_product_id = $item_data->get_product_id();
						$item_total      = $item_data->get_total();

						$is_upsell   = wc_get_order_item_meta( $item_id, '_cartflows_upsell', true );
						$is_downsell = wc_get_order_item_meta( $item_id, '_cartflows_downsell', true );

						// Old order bump.
						if ( $item_product_id == $bump_product_id ) {
							$total_bump_offer += $item_total;
						}

						// Upsell or Downsell.
						if ( 'yes' === $is_upsell || 'yes' === $is_downsell ) {

							$cartflows_offer += number_format( (float) $item_total, 2, '.', '' );
						}
					}

					// Multiple order bump.
					if ( is_array( $multiple_obs ) && ! empty( $multiple_obs ) ) {

						foreach ( $multiple_obs as $key => $data ) {
							$total_bump_offer += number_format( $data['price'], wc_get_price_decimals(), '.', '' );
						}
					}
				} else {
					// If separate order for upsell/downsell is enabled.
					$is_offer = $order->get_meta( '_cartflows_offer' );

					if ( 'yes' === $is_offer ) {

						$cartflows_offer += number_format( (float) $order_total, 2, '.', '' );
					}
				}
			}

			/* Get the Flow IDs. */
			$flow_ids = array_column( $orders, 'meta_value' );

			/* Calculate the Visits of those flows. */
			$visits = $this->fetch_visits_of_all_flows( $flow_ids, $start_date, $end_date );

			$analytics_data['total_revenue']        = number_format( (float) $gross_sale, 2, '.', '' );
			$analytics_data['total_offers_revenue'] = number_format( (float) $cartflows_offer, 2, '.', '' );
			$analytics_data['total_bump_revenue']   = number_format( (float) $total_bump_offer, 2, '.', '' );
			$analytics_data['total_visits']         = $visits;
			$analytics_data['total_orders']         = $order_count;
		}

		return $analytics_data;

	}

		/**
		 * Get orders data for flow.
		 *
		 * @since 1.6.15
		 * @param string $start_date start date.
		 * @param string $end_date end date.
		 *
		 * @return int
		 */
	public function get_orders_by_all_flows( $start_date, $end_date ) {

		global $wpdb;

		if ( $this->is_custom_order_table_enabled() ) {
			// HPOS usage is enabled.

			$conditions = array(
				'tb1.type' => 'shop_order',
			);

			$order_date_key   = 'date_created_gmt';
			$order_status_key = 'status';
			$order_id_key     = 'order_id';
			$order_table      = $wpdb->prefix . 'wc_orders';
			$order_meta_table = $wpdb->prefix . 'wc_orders_meta';

		} else {
			// TraditionalCPT-based orders are in use.

			$conditions       = array(
				'tb1.post_type' => 'shop_order',
			);
			$order_date_key   = 'post_date';
			$order_status_key = 'post_status';
			$order_id_key     = 'post_id';
			$order_table      = $wpdb->prefix . 'posts';
			$order_meta_table = $wpdb->prefix . 'postmeta';

		}

		$where = $this->get_items_query_where( $conditions );

		$where .= ' AND ( tb1.' . $order_date_key . " BETWEEN IF (tb2.meta_key='wcf-analytics-reset-date'>'" . $start_date . "', tb2.meta_key, '" . $start_date . "')  AND '" . $end_date . "' )";
		$where .= " AND ( ( tb2.meta_key = '_wcf_flow_id' ) OR ( tb2.meta_key = '_cartflows_parent_flow_id' ) )";
		$where .= ' AND tb1.' . $order_status_key . " IN ( 'wc-completed', 'wc-processing', 'wc-cancelled' )";

		$query = 'SELECT tb1.ID, DATE( tb1.' . $order_status_key . ' ) date, tb2.meta_value FROM ' . $order_table . ' tb1
		INNER JOIN ' . $order_meta_table . ' tb2
		ON tb1.ID = tb2.' . $order_id_key . '
		' . $where;

		// @codingStandardsIgnoreStart.
		return $wpdb->get_results( $query );
		// @codingStandardsIgnoreEnd.

	}

	/**
	 * Fetch total visits.
	 *
	 * @param integer $flow_ids flows id.
	 * @param string  $start_date start date.
	 * @param string  $end_date end date.
	 *
	 * @return array|object|null
	 */
	public function fetch_visits_of_all_flows( $flow_ids, $start_date, $end_date ) {

		global $wpdb;

		$visit_db      = $wpdb->prefix . CARTFLOWS_PRO_VISITS_TABLE;
		$visit_meta_db = $wpdb->prefix . CARTFLOWS_PRO_VISITS_META_TABLE;

		//phpcs:disable WordPress.DB.PreparedSQL
		$query = $wpdb->prepare(
			"SELECT
			 COUNT( DISTINCT( $visit_db.id ) ) AS total_visits
			 FROM $visit_db INNER JOIN $visit_meta_db ON $visit_db.id = $visit_meta_db.visit_id
			 WHERE ( date_visited BETWEEN %s AND %s )
			 GROUP BY step_id
			 ORDER BY NULL",
			$start_date,
			$end_date
		);

		// Query is prepared above.
		$visits = $wpdb->get_results( $query );//phpcs:ignore WordPress.DB.DirectDatabaseQuery

		//phpcs:enable WordPress.DB.PreparedSQL
		$total_visits = 0;

		foreach ( $visits as $visit ) {
			$total_visits += $visit->total_visits;
		}

		return $total_visits;
	}

	/**
	 * Visits map.
	 *
	 * @param int   $flow_id flow id.
	 * @param array $visits visits data.
	 * @param array $earning earning data.
	 * @return array
	 */
	public function visits_map( $flow_id, $visits, $earning ) {

		$visits_map = array();

		foreach ( $visits as $v_in => $v_data ) {

			$step_id                = $v_data->step_id;
			$v_data_array           = (array) $v_data;
			$visits_map[ $step_id ] = $v_data_array;
			$step_type              = wcf()->utils->get_step_type( $step_id );

			$visits_map[ $step_id ]['revenue']         = 0;
			$visits_map[ $step_id ]['title']           = get_the_title( $step_id );
			$visits_map[ $step_id ]['note']            = get_post_meta( $step_id, 'wcf-step-note', true );
			$visits_map[ $step_id ]['conversion_rate'] = 0;

			// Set conversion rate.
			$conversions  = intval( $v_data_array['conversions'] );
			$total_visits = intval( $v_data_array['total_visits'] );

			if ( $total_visits > 0 ) {

				$conversion_rate = $conversions / intval( $v_data_array['total_visits'] ) * 100;

				$visits_map[ $step_id ]['conversion_rate'] = number_format( (float) $conversion_rate, 2, '.', '' );
			}

			switch ( $step_type ) {

				case 'checkout':
					$visits_map[ $step_id ]['revenue'] = 0;

					if ( isset( $earning['checkout'][ $step_id ] ) ) {
						$visits_map[ $step_id ]['revenue'] = $earning['checkout'][ $step_id ];
					}
					break;
				case 'upsell':
				case 'downsell':
					$visits_map[ $step_id ]['revenue'] = 0;

					if ( isset( $earning['offer'][ $step_id ] ) ) {
						$visits_map[ $step_id ]['revenue'] = $earning['offer'][ $step_id ];
					}
					break;
			}

			$visits_map[ $step_id ]['revenue'] = number_format( (float) $visits_map[ $step_id ]['revenue'], 2, '.', '' );
		}

		$all_steps = wcf()->flow->get_steps( $flow_id );

		foreach ( $all_steps as $in => $step_data ) {

			$step_id = $step_data['id'];

			if ( isset( $visits_map[ $step_id ] ) ) {

				$all_steps[ $in ]['visits'] = $visits_map[ $step_id ];

				if ( isset( $step_data['ab-test'] ) ) {

					$ab_total_visits  = 0;
					$ab_unique_visits = 0;
					$ab_conversions   = 0;
					$ab_revenue       = 0;

					// If ab test true but ab test ui is off and variations are empty.
					if ( isset( $step_data['ab-test-variations'] ) && ! empty( $step_data['ab-test-variations'] ) ) {

						$variations = $step_data['ab-test-variations'];

						foreach ( $variations as $v_in => $v_data ) {

							$v_id = $v_data['id'];

							if ( isset( $visits_map[ $v_id ] ) ) {

								$all_steps[ $in ]['visits-ab'][ $v_id ] = $visits_map[ $v_id ];

								$ab_total_visits  = $ab_total_visits + intval( $visits_map[ $v_id ]['total_visits'] );
								$ab_unique_visits = $ab_unique_visits + intval( $visits_map[ $v_id ]['unique_visits'] );
								$ab_conversions   = $ab_conversions + intval( $visits_map[ $v_id ]['conversions'] );
								$ab_revenue       = $ab_revenue + $visits_map[ $v_id ]['revenue'];

							}
						}
					} else {
						$ab_total_visits  = $all_steps[ $in ]['visits']['total_visits'];
						$ab_unique_visits = $all_steps[ $in ]['visits']['unique_visits'];
						$ab_conversions   = $all_steps[ $in ]['visits']['conversions'];
						$ab_revenue       = $all_steps[ $in ]['visits']['revenue'];

						$all_steps[ $in ]['visits-ab'][ $step_id ] = $visits_map[ $step_id ];
					}

					if ( isset( $step_data['ab-test-archived-variations'] ) && ! empty( $step_data['ab-test-archived-variations'] ) ) {

						/* Add archived variations */
						$archived_variations = $step_data['ab-test-archived-variations'];

						foreach ( $archived_variations as $v_in => $v_data ) {

							$v_id = $v_data['id'];

							if ( isset( $visits_map[ $v_id ] ) ) {

								$all_steps[ $in ]['visits-ab-archived'][ $v_id ]          = $visits_map[ $v_id ];
								$all_steps[ $in ]['visits-ab-archived'][ $v_id ]['title'] = $v_data['title'];

								if ( $v_data['deleted'] ) {
									$all_steps[ $in ]['visits-ab-archived'][ $v_id ]['archived_date'] = '(Deleted on ' . $v_data['date'] . ')';
								} else {
									$all_steps[ $in ]['visits-ab-archived'][ $v_id ]['archived_date'] = '(Archived on ' . $v_data['date'] . ')';
								}

								$all_steps[ $in ]['visits-ab-archived'][ $v_id ]['note'] = isset( $v_data['note'] ) ? $v_data['note'] : '';

								$ab_total_visits  = $ab_total_visits + intval( $visits_map[ $v_id ]['total_visits'] );
								$ab_unique_visits = $ab_unique_visits + intval( $visits_map[ $v_id ]['unique_visits'] );
								$ab_conversions   = $ab_conversions + intval( $visits_map[ $v_id ]['conversions'] );
								$ab_revenue       = $ab_revenue + $visits_map[ $v_id ]['revenue'];
							}
						}
					}

					// Add total count to main step.
					$all_steps[ $in ]['visits']['total_visits']  = $ab_total_visits;
					$all_steps[ $in ]['visits']['unique_visits'] = $ab_unique_visits;
					$all_steps[ $in ]['visits']['conversions']   = $ab_conversions;
					$all_steps[ $in ]['visits']['revenue']       = number_format( (float) $ab_revenue, 2, '.', '' );

					// Calculate total conversion count and set to main step.
					$total_conversion_rate = 0;

					if ( $ab_total_visits > 0 ) {
						$total_conversion_rate = $ab_conversions / $ab_total_visits * 100;
						$total_conversion_rate = number_format( (float) $total_conversion_rate, 2, '.', '' );
					}

					$all_steps[ $in ]['visits']['conversion_rate'] = $total_conversion_rate;
				}
			}
		}

		return $all_steps;
	}

	/**
	 * Fetch total visits.
	 *
	 * @param integer $flow_id flow_id.
	 * @param string  $action action to verify nonce.
	 * @return array|object|null
	 */
	public function fetch_visits( $flow_id, $action ) {

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( $action, 'security', false ) ) {
			$response_data = array( 'message' => __( 'Nonce verification failed.', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		global $wpdb;

		$visit_db      = $wpdb->prefix . CARTFLOWS_PRO_VISITS_TABLE;
		$visit_meta_db = $wpdb->prefix . CARTFLOWS_PRO_VISITS_META_TABLE;

		$start_date = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '';
		$end_date   = isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '';
		$start_date = $start_date ? $start_date : gmdate( 'Y-m-d' );
		$end_date   = $end_date ? $end_date : gmdate( 'Y-m-d' );
		$start_date = gmdate( 'Y-m-d H:i:s', strtotime( $start_date . '00:00:00' ) );
		$end_date   = gmdate( 'Y-m-d H:i:s', strtotime( $end_date . '23:59:59' ) );

		// Need to look into date format later.
		$analytics_reset_date = wcf()->options->get_flow_meta_value( $flow_id, 'wcf-analytics-reset-date' );

		if ( $analytics_reset_date > $start_date ) {
			$start_date = $analytics_reset_date;
		}

		$steps     = wcf()->flow->get_steps( $flow_id );
		$all_steps = array();

		foreach ( $steps as $s_key => $s_data ) {

			if ( isset( $s_data['ab-test'] ) ) {

				if ( isset( $s_data['ab-test-variations'] ) && ! empty( $s_data['ab-test-variations'] ) ) {

					foreach ( $s_data['ab-test-variations'] as $v_key => $v_data ) {

						$all_steps[] = $v_data['id'];
					}
				} else {
					$all_steps[] = $s_data['id'];
				}

				if ( isset( $s_data['ab-test-archived-variations'] ) && ! empty( $s_data['ab-test-archived-variations'] ) ) {

					foreach ( $s_data['ab-test-archived-variations'] as $av_key => $av_data ) {
						$all_steps[] = $av_data['id'];
					}
				}
			} else {
				$all_steps[] = $s_data['id'];
			}
		}

		$step_ids = implode( ', ', $all_steps );

		// phpcs:disable WordPress.DB.PreparedSQL
		$query = $wpdb->prepare(
			"SELECT step_id,
			 COUNT( DISTINCT( $visit_db.id ) ) AS total_visits,
			 COUNT( DISTINCT( CASE WHEN visit_type = 'new'
			 THEN $visit_db.id ELSE NULL END ) ) AS unique_visits,
			 COUNT( CASE WHEN $visit_meta_db.meta_key = 'conversion'
			 AND $visit_meta_db.meta_value = 'yes'
			 THEN step_id ELSE NULL END ) AS conversions
			 FROM $visit_db INNER JOIN $visit_meta_db ON $visit_db.id = $visit_meta_db.visit_id
			 WHERE step_id IN ( $step_ids )
			 AND ( date_visited BETWEEN %s AND %s )
			 GROUP BY step_id
			 ORDER BY NULL",
			$start_date,
			$end_date
		);

		$visits = $wpdb->get_results( $query ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery

		// phpcs:enable WordPress.DB.PreparedSQL
		$visited_steps     = wp_list_pluck( (array) $visits, 'step_id' );
		$non_visited_steps = array_diff( $all_steps, $visited_steps );

		// Non visited steps.
		if ( $non_visited_steps ) {

			$non_visit = array(
				'step_id'       => 0,
				'total_visits'  => 0,
				'unique_visits' => 0,
				'conversions'   => 0,
				'revenue'       => 0,
			);

			foreach ( $non_visited_steps as $non_visited_step ) {

				$non_visit['step_id'] = $non_visited_step;
				array_push( $visits, (object) $non_visit );

			}
		}

		$step_ids_array = wp_list_pluck( (array) $steps, 'id' );
		usort(
			$visits,
			function ( $a, $b ) use ( $all_steps ) {
				return array_search( intval( $a->step_id ), $all_steps, true ) - array_search( intval( $b->step_id ), $all_steps, true );

			}
		);

		// phpcs:enable
		return $visits;
	}

	/**
	 * Calculate earning.
	 *
	 * @param integer $flow_id flow_id.
	 * @param string  $action action to verify nonce.
	 * @return array
	 */
	public function get_earnings( $flow_id, $action ) {

		$orders                   = $this->get_orders_by_flow( $flow_id, $action );
		$gross_sale               = 0;
		$checkout_total           = 0;
		$avg_order_value          = 0;
		$total_bump_offer_earning = 0;
		$checkout_earnings        = array();
		$offer_earnings           = array();
		$order_count              = 0;

		if ( ! empty( $orders ) ) {

			foreach ( $orders as $order ) {

				$order_id = $order->ID;

				$order   = wc_get_order( $order_id );
				$user_id = $order->get_user_id();

				// skip the orders which are placed by the user whose user role is Administrator.
				if ( $user_id && user_can( $user_id, 'cartflows_manage_flows_steps' ) ) {
					continue;
				}

				$order_total = $order->get_total();
				if ( ! $order->has_status( 'cancelled' ) ) {
					$gross_sale    += (float) $order_total;
					$checkout_total = (float) $order_total;
				}
				$bump_product_id      = $order->get_meta( '_wcf_bump_product' );
				$multiple_obs         = $order->get_meta( '_wcf_bump_products' );
				$separate_offer_order = $order->get_meta( '_cartflows_parent_flow_id' );
				$checkout_id          = $order->get_meta( '_wcf_checkout_id' );

				if ( empty( $separate_offer_order ) ) {

					// We are doing this for main order and not for the other order such as Upsell/Downsells.
					$order_count++;

					foreach ( $order->get_items() as $item_id => $item_data ) {

						$item_product_id = $item_data->get_product_id();
						$item_total      = $item_data->get_total();
						$is_upsell       = wc_get_order_item_meta( $item_id, '_cartflows_upsell', true );
						$is_downsell     = wc_get_order_item_meta( $item_id, '_cartflows_downsell', true );
						$offer_step_id   = wc_get_order_item_meta( $item_id, '_cartflows_step_id', true );

						if ( 'yes' === $is_upsell ) {
							$checkout_total -= $item_total;

							if ( ! isset( $offer_earnings[ $offer_step_id ] ) ) {
								$offer_earnings[ $offer_step_id ] = 0;
							}
							$offer_earnings[ $offer_step_id ] += number_format( (float) $item_total, 2, '.', '' );
						}

						if ( 'yes' === $is_downsell ) {
							$checkout_total -= $item_total;

							if ( ! isset( $offer_earnings[ $offer_step_id ] ) ) {
								$offer_earnings[ $offer_step_id ] = 0;
							}

							$offer_earnings[ $offer_step_id ] += number_format( (float) $item_total, 2, '.', '' );
						}

						if ( $item_product_id == $bump_product_id ) {
							$total_bump_offer_earning += $item_total;
							$checkout_total           -= $item_total;
						}
					}
					// Multiple order bump.
					if ( is_array( $multiple_obs ) && ! empty( $multiple_obs ) ) {

						foreach ( $multiple_obs as $key => $data ) {
							$total_bump_offer_earning += number_format( $data['price'], wc_get_price_decimals(), '.', '' );
						}
					}
				} else {

					// Calculate the current upsell/downsell's earnings for the same order.
					$is_offer      = $order->get_meta( '_cartflows_offer' );
					$offer_step_id = $order->get_meta( '_cartflows_offer_step_id', true );

					if ( 'yes' === $is_offer ) {
						$checkout_total -= $order_total;

						if ( ! isset( $offer_earnings[ $offer_step_id ] ) ) {
							$offer_earnings[ $offer_step_id ] = 0;
						}

						$offer_earnings[ $offer_step_id ] += number_format( (float) $order_total, 2, '.', '' );
					}
				}

				if ( ! isset( $checkout_earnings[ $checkout_id ] ) ) {
					$checkout_earnings[ $checkout_id ] = 0;
				}

				$checkout_earnings[ $checkout_id ] = $checkout_earnings[ $checkout_id ] + $checkout_total;
			}

			if ( 0 !== $order_count ) {
				$avg_order_value = $gross_sale / $order_count;
			}
		}

		$all_earning_data = array(
			'order_count'     => $order_count,
			'avg_order_value' => number_format( (float) $avg_order_value, 2, '.', '' ),
			'gross_sale'      => number_format( (float) $gross_sale, 2, '.', '' ),
			'checkout_sale'   => number_format( (float) $checkout_total, 2, '.', '' ),
			'offer'           => $offer_earnings,
			'checkout'        => $checkout_earnings,
			'bump_offer'      => number_format( (float) $total_bump_offer_earning, 2, '.', '' ),
		);

		return $all_earning_data;
	}

	/**
	 * Prepare where items for query.
	 *
	 * @param array $conditions conditions to prepare WHERE query.
	 * @return string
	 */
	protected function get_items_query_where( $conditions ) {

		global $wpdb;

		$where_conditions = array();
		$where_values     = array();

		foreach ( $conditions as $key => $condition ) {

			if ( false !== stripos( $key, 'IN' ) ) {
				$where_conditions[] = $key . '( %s )';
			} else {
				$where_conditions[] = $key . '= %s';
			}

			$where_values[] = $condition;
		}

		if ( ! empty( $where_conditions ) ) {
			// @codingStandardsIgnoreStart
			return $wpdb->prepare( 'WHERE 1 = 1 AND ' . implode( ' AND ', $where_conditions ), $where_values );
			// @codingStandardsIgnoreEnd
		} else {
			return '';
		}
	}


	/**
	 * Get orders data for flow.
	 *
	 * @param int    $flow_id flow id.
	 * @param string $action action to verify.
	 * @return int
	 */
	public function get_orders_by_flow( $flow_id, $action ) {

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( $action, 'security', false ) ) {
			$response_data = array( 'message' => __( 'Nonce verification failed.', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		global $wpdb;

		$start_date = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '';
		$end_date   = isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '';
		$start_date = $start_date ? $start_date : gmdate( 'Y-m-d' );
		$end_date   = $end_date ? $end_date : gmdate( 'Y-m-d' );
		$start_date = gmdate( 'Y-m-d H:i:s', strtotime( $start_date . '00:00:00' ) );
		$end_date   = gmdate( 'Y-m-d H:i:s', strtotime( $end_date . '23:59:59' ) );

		$analytics_reset_date = wcf()->options->get_flow_meta_value( $flow_id, 'wcf-analytics-reset-date' );

		if ( $analytics_reset_date > $start_date ) {
			$start_date = $analytics_reset_date;
		}

		if ( $this->is_custom_order_table_enabled() ) {
			// HPOS usage is enabled.

			$conditions       = array(
				'tb1.type' => 'shop_order',
			);
			$order_date_key   = 'date_created_gmt';
			$order_status_key = 'status';
			$order_id_key     = 'order_id';
			$order_table      = $wpdb->prefix . 'wc_orders';
			$order_meta_table = $wpdb->prefix . 'wc_orders_meta';

		} else {
			// Traditional CPT-based orders are in use.

			$conditions       = array(
				'tb1.post_type' => 'shop_order',
			);
			$order_date_key   = 'post_date';
			$order_status_key = 'post_status';
			$order_id_key     = 'post_id';
			$order_table      = $wpdb->prefix . 'posts';
			$order_meta_table = $wpdb->prefix . 'postmeta';
		}

		$where = $this->get_items_query_where( $conditions );

		$where .= ' AND ( tb1.' . $order_date_key . " BETWEEN '" . $start_date . "' AND '" . $end_date . "' )";
		$where .= " AND ( ( tb2.meta_key = '_wcf_flow_id' AND tb2.meta_value = $flow_id ) OR ( tb2.meta_key = '_cartflows_parent_flow_id' AND tb2.meta_value = $flow_id ) )";
		$where .= ' AND tb1.' . $order_status_key . " IN ( 'wc-completed', 'wc-processing', 'wc-cancelled' )";

		$query = 'SELECT tb1.ID, DATE( tb1.' . $order_date_key . ' ) date FROM ' . $order_table . ' tb1
		INNER JOIN ' . $order_meta_table . ' tb2
		ON tb1.ID = tb2.' . $order_id_key . '
		' . $where;

		// @codingStandardsIgnoreStart
		$orders = $wpdb->get_results( $query );
		// @codingStandardsIgnoreEnd

		self::$flow_orders = $orders;

		return $orders;
	}

	/**
	 * Check if custom order table enabled.
	 *
	 * @return bool
	 */
	public function is_custom_order_table_enabled() {

		return class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ? true : false;
	}
}

Cartflows_Pro_Analytics_Reports::get_instance();
