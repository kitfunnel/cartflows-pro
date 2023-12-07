<?php
/**
 * CartFlows Pro License Debug Log HTML.
 *
 * @package CARTFLOWS
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * File.
 *
 * @file
 * Header file.
 */
?>

<div class="wcf-debug-page-content wcf-clear">
	<div class="wcf-log__section wcf-license-log__section">

		<!-- CartFlows Pro license debug log -->
			<div class="log-viewer-select">
				<h2 class="log-viewer--title">
					<span><?php esc_html_e( 'License debug log', 'cartflows-pro' ); ?></span>
				</h2>
			</div>
			<div class="log-viewer">
				<form method="post" class="wrap wcf-clear" action="" >
					<div class="form-wrap">
						<div class="wcf-license-row wcf-license-agrs">
							<p><b><u><?php esc_html_e( 'License Arguments:', 'cartflows-pro' ); ?></u></b></p>
							<?php
								// Escaping this as we intentionally want to display the license API call info for debugging purpose.
								echo '<pre>';
								wcf_print_r( $args );
								echo '</pre>';
							?>
						</div>

						<hr>

						<div class="wcf-license-row wcf-license-call">
							<p><b><u><?php esc_html_e( 'License Call:', 'cartflows-pro' ); ?></u></b></p>
							<a href="<?php echo esc_url( $target_url ); ?>" target="_blank" style="overflow-wrap: break-word;"><?php echo esc_url( $target_url ); ?></a>
						</div>

						<hr>

						<div class="wcf-license-row wcf-license-response" style="overflow-wrap: break-word;">
							<p><b><u><?php esc_html_e( 'License API Response:', 'cartflows-pro' ); ?></u></b></p>
							<?php
								// Escaping this as we intentionally want to display the license API call info for debugging purpose.
								echo "<pre style='white-space: pre-wrap;'>";
								wcf_print_r( $response );
								echo '</pre>';
							?>
						</div>
					</div>
				</form>
			</div>
		<!-- CartFlows Pro license debug log -->

	</div>
</div>
