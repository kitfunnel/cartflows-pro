<?php
/**
 * License debug log metabox HTML markup.
 *
 * @package CartFlows
 */

if ( 'true' === $debug_on ) { // If start.

	// Get license class instance.
	$cartflows_license_instance = CartFlows_Pro_Licence::get_instance();

	// Get license Key.
	$data        = get_option( 'wc_am_client_cartflows_api_key', array() );
	$license_key = isset( $data['api_key'] ) ? $data['api_key'] : '';

	// Prepare license args.
	$args = array(
		'request'     => 'update',
		'slug'        => CARTFLOWS_PRO_SLUG,
		'plugin_name' => CARTFLOWS_PRO_PRODUCT_TITLE,
		'version'     => $cartflows_license_instance->wc_am_software_version,
		'product_id'  => $cartflows_license_instance->product_id,
		'api_key'     => $license_key,
		'instance'    => $cartflows_license_instance->wc_am_instance_id,
	);

	// Prepare Update Call URL.
	$target_url = esc_url_raw( add_query_arg( 'wc-api', 'wc-am-api', CARTFLOWS_SERVER_URL ) . '&' . http_build_query( $args ) );

	$request = wp_safe_remote_post( $target_url, array( 'timeout' => 15 ) );

	if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
		return false;
	}

	// Response after update call.
	$response = json_decode( wp_remote_retrieve_body( $request ) );

	// phpcs:ignore
	?>

	<!-- CartFlows Pro license debug log -->
	<div class="general-settingss-form postbox">
		<h2 class="hndle wcf-normal-cusror ui-sortable-handle">
			<span><?php esc_html_e( 'License debug log', 'cartflows-pro' ); ?></span>
		</h2>
		<div class="inside">
			<form method="post" class="wrap wcf-clear" action="" >
				<div class="form-wrap">
					<div class="wcf-license-row wcf-license-agrs">
						<p><b><u><?php esc_html_e( 'License Arguments:', 'cartflows-pro' ); ?></u></b></p>
						<?php
							echo '<pre>';
							print_r( $args ); // phpcs:ignore
							echo '</pre>';
						?>
					</div>

					<hr>

					<div class="wcf-license-row wcf-license-call">
						<p><b><u><?php esc_html_e( 'License Call:', 'cartflows-pro' ); ?></u></b></p>
						<a href="<?php echo $target_url; ?>" target="_blank" style="overflow-wrap: break-word;"><?php echo $target_url; ?></a>
					</div>

					<hr>

					<div class="wcf-license-row wcf-license-response" style="overflow-wrap: break-word;">
						<p><b><u><?php esc_html_e( 'License API Response:', 'cartflows-pro' ); ?></u></b></p>
						<?php
							echo "<pre style='white-space: pre-wrap;'>";
							print_r( $response ); // phpcs:ignore
							echo '</pre>';
						?>
					</div>
				</div>
			</form>
		</div>
	</div>
	<!-- CartFlows Pro license debug log -->
	<?php
} // If close.
