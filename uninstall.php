<?php
/**
 * CartFlows Pro
 * Delete Plugin Data.
 *
 * @package CartFlows Pro
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


global $wpdb;

$is_delete = get_option( 'cartflows_pro_delete_plugin_data', false );

if ( 'enable' === $is_delete ) {

	$options = array(
		'cartflows-pro-version',
		'_cartflows_offer_global_settings',
		'wc_am_client_cartflows_instance',
		'wc_am_client_cartflows_deactivate_checkbox',
		'wc_am_client_cartflows_activated',
		'wc_am_client_cartflows_api_key',
		'wc_am_client_cartflows',
		'wc_am_client_cartflows_activated',
		'cartflows_database_tables_created',
		'cartflows_pro_delete_plugin_data',
	);

	foreach ( $options as $index => $key ) {
		delete_option( $key );
	}

	wp_clear_scheduled_hook( 'carflows_schedule_normalize_order_status' );

	// phpcs:disable
	
	$wpdb->get_results( "DROP TABLE IF EXISTS {$wpdb->prefix}cartflows_visits_meta" ); // db call ok; no-cache ok.

	$wpdb->get_results( "DROP TABLE IF EXISTS {$wpdb->prefix}cartflows_visits" ); // db call ok; no-cache ok.

}

