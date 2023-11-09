<?php
/**
 * CartFlows License.
 *
 * @package CartFlows
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CartFlows_Pro_Licence' ) ) :

	/**
	 * CartFlows License
	 *
	 * @since 1.0.0
	 */
	class CartFlows_Pro_Licence {

		/**
		 * Instance
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Wc_am_instance_id
		 *
		 * @since 1.0.0
		 * @access public
		 * @var string wc_am_instance_id.
		 */
		public $wc_am_instance_id;

		/**
		 * Wc_am_domain
		 *
		 * @since 1.0.0
		 * @access public
		 * @var string wc_am_domain.
		 */
		public $wc_am_domain;

		/**
		 * Wc_am_software_version
		 *
		 * @since 1.0.0
		 * @access public
		 * @var string wc_am_software_version.
		 */
		public $wc_am_software_version;

		/**
		 * Product ID.
		 *
		 * @since x.x.x
		 * @access public
		 * @var string product_id.
		 */
		public $product_id;

		/**
		 * Activate Status.
		 *
		 * @since x.x.x
		 * @access public
		 * @var string activate_status.
		 */
		public $activate_status;

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
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			global $wcam_lib;
			$this->product_id             = $this->get_clean_product_id();
			$this->wc_am_domain           = str_ireplace( array( 'http://', 'https://' ), '', home_url() ); // blog domain name.
			$this->wc_am_software_version = CARTFLOWS_PRO_VER;
			$this->wc_am_instance_id      = get_option( 'wc_am_client_' . $this->product_id . '_instance' ); // A unique password generated for each installation.
			$this->activate_status        = get_option( 'wc_am_client_' . $this->product_id . '_activated', 'Deactivated' );

			add_action( 'plugin_action_links_' . CARTFLOWS_PRO_BASE, array( $this, 'license_popup_link' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'wp_ajax_cartflows_activate_license', array( $this, 'activate_license' ) );
			add_action( 'wp_ajax_cartflows_deactivate_license', array( $this, 'deactivate_license' ) );
			add_action( 'admin_footer', array( $this, 'export_popup' ) );

			add_filter( 'wc_am_inactive_notice_override', '__return_true' );
			add_action( 'admin_notices', array( $this, 'inactive_notice' ) );
			add_action( 'wp_ajax_cartflows_disable_activate_license_notice', array( $this, 'disable_activate_licence_notice' ) );

			// Disabled un-install hook.
			add_filter( 'wc_am_uninstall_disable', '__return_true' );

			// Disabled un-install hook.
			remove_action( 'admin_menu', array( $wcam_lib, 'register_menu' ) );
			remove_action( 'admin_init', array( $wcam_lib, 'load_settings' ) );

		}

		/**
		 * Disable the Activate License Notice
		 *
		 * @return void
		 */
		public function disable_activate_licence_notice() {

			/**
			 * Check permission
			 */
			if ( ! current_user_can( 'cartflows_manage_settings' ) ) {

				$response['data'] = array(
					'error' => __( 'Permission denied!', 'cartflows-pro' ),
				);

				wp_send_json_error( $response );
			}

			check_ajax_referer( 'cartflows-admin-notice-nonce', 'security' );

			// Saving the `true` for 10 minutes into transient `my_transient`.
			set_transient( 'cartflows-activate-licence', true, 10 * MINUTE_IN_SECONDS );

			wp_send_json_success();
		}


		/**
		 * License Inactive Notice
		 *
		 * @since 1.0.0
		 */
		public function inactive_notice() {

			if ( 'Activated' === $this->activate_status ) {
				return;
			}

			$expired = get_transient( 'cartflows-activate-licence' );

			// Is transient expired?
			if ( false != $expired && ! empty( $expired ) ) {
				return;
			}

			wp_enqueue_script( 'cartflows-admin-notice' );

			/* translators: %1$s Software Title, %2$s Plugin, %3$s Anchor opening tag, %4$s Anchor closing tag, %5$s Software Title. */
			$message = sprintf( __( 'The <strong>%1$s</strong> License Key has not been activated, so the %2$s is inactive! %3$sClick here%4$s to activate <strong>%5$s</strong>.', 'cartflows-pro' ), esc_attr( CARTFLOWS_PRO_DISPLAY_TITLE ), 'plugin', '<a class="cartflows-license-popup-open-button" href="' . esc_url( admin_url( 'plugins.php?cartflows-license-popup' ) ) . '">', '</a>', esc_attr( CARTFLOWS_PRO_DISPLAY_TITLE ) );

			$output  = '<div class="cartflows-dismissible-notice notice notice-error is-dismissible">';
			$output .= '<p>' . $message . '</p>';
			$output .= '</div>';

			echo wp_kses_post( $output );

		}

		/**
		 * Export popup.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function export_popup() {

			if ( 'plugins' !== get_current_screen()->base ) {
				return;
			}

			$data        = get_option( 'wc_am_client_' . $this->product_id . '_api_key', array() );
			$license_key = isset( $data['api_key'] ) ? $data['api_key'] : '';

			$desc = sprintf(
				/* translators: %1$s,%2$s: Find API key article link */
				__( 'If you don\'t have License key, you can get it from %1$s here%2$s.', 'cartflows-pro' ),
				'<a target="_blank" href="' . esc_url( CARTFLOWS_SERVER_URL . 'api-keys/' ) . '" >',
				'</a>'
			);

			?>
			<div id="cartflows-license-popup-overlay" style="display:none;"></div>
			<div id="cartflows-license-popup" style="display:none;" data-license-key="<?php echo esc_attr( $this->activate_status ); ?>">
				<div class="inner">
					<div class="heading">
						<span><?php esc_html_e( 'Activate License', 'cartflows-pro' ); ?></span>
						<span class="cartflows-close-popup-button tb-close-icon"></span>
					</div>
					<div class="contents">
					</div>
				</div>
			</div>
			<script type="text/template" id="tmpl-cartflows-activate-license">
				<table class="widefat">
					<tr class="cartflows-row">
						<td class="cartflows-heading"><?php esc_html_e( 'License Key', 'cartflows-pro' ); ?></td>
						<td class="cartflows-content">
							<input type="text" placeholder="<?php esc_attr_e( 'Enter your License Key', 'cartflows-pro' ); ?>" class="regular-text license_key" name="license_key" value="<?php echo esc_attr( $license_key ); ?>" autocomplete="off">
							<input type="hidden" class="license_nonce" name="license_nonce" value="<?php echo esc_attr( wp_create_nonce( 'cartflows_license_activation_nonce' ) ); ?>" autocomplete="off">
							<?php /* translators: Cartflows site URL. */ ?>
							<p class="description">
								<?php
									echo wp_kses(
										$desc,
										array(
											'a' => array(
												'href'   => array(),
												'target' => array(),
											),
										)
									);
								?>
							</p>
						</td>
					</tr>
					<tr class="cartflows-row">
						<td colspan="2" class="submit-button-td">
							<span class="button button-primary cartflows-activate-license"><i class="cartflows-processing dashicons dashicons-update"></i><span class="text"><?php esc_html_e( 'Activate', 'cartflows-pro' ); ?></span></span>
						</td>
					</tr>
				</table>
			</script>
			<script type="text/template" id="tmpl-cartflows-deactivate-license">
				<table class="widefat">
					<tr class="cartflows-row">
						<td class="cartflows-heading"><?php esc_html_e( 'License Key', 'cartflows-pro' ); ?></td>
						<td class="cartflows-content">
							<input type="password" placeholder="******************" class="regular-text license_key" value="" autocomplete="off" text="" readonly="readonly">
							<input type="hidden" class="deactivate_license_nonce" name="deactivate_license_nonce" value="<?php echo esc_attr( wp_create_nonce( 'cartflows_license_deactivation_nonce' ) ); ?>" autocomplete="off">
						</td>
					</tr>
					<tr class="cartflows-row">
						<td colspan="2" class="submit-button-td">
							<span class="button button-primary cartflows-deactivate-license"><i class="cartflows-processing dashicons dashicons-update"></i><span class="text"><?php esc_html_e( 'Deactivate', 'cartflows-pro' ); ?></span></span>
						</td>
					</tr>
				</table>
			</script>
			<?php
		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @param   mixed $links Plugin Action links.
		 * @return  array
		 */
		public function license_popup_link( $links ) {

			if ( 'Deactivated' == $this->activate_status ) {
				$links['license_key'] = '<a href="#"" class="cartflows-license-popup-open-button active" aria-label="' . esc_attr__( 'Settings', 'cartflows-pro' ) . '">' . esc_html__( 'Activate License', 'cartflows-pro' ) . '</a>';
			} else {
				$links['license_key'] = '<a href="#"" class="cartflows-license-popup-open-button inactive" aria-label="' . esc_attr__( 'Settings', 'cartflows-pro' ) . '">' . esc_html__( 'Deactivate License', 'cartflows-pro' ) . '</a>';
			}

			return $links;
		}

		/**
		 * Enqueues the needed CSS/JS for Backend.
		 *
		 * @param  string $hook Current hook.
		 *
		 * @since 1.0.0
		 */
		public function admin_scripts( $hook = '' ) {

			wp_register_script( 'cartflows-admin-notice', CARTFLOWS_PRO_URL . 'assets/js/admin-notice.js', array( 'jquery' ), CARTFLOWS_PRO_VER, true );

			$vars = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'_nonce'  => wp_create_nonce( 'cartflows-admin-notice-nonce' ),
			);

			wp_localize_script( 'cartflows-admin-notice', 'CartFlowsProAdminNoticeVars', $vars );

			if ( 'plugins.php' == $hook ) {
				wp_enqueue_style( 'cartflows-license', CARTFLOWS_PRO_URL . 'assets/css/license-popup.css', null, CARTFLOWS_PRO_VER, 'all' );
				wp_enqueue_script( 'cartflows-license', CARTFLOWS_PRO_URL . 'assets/js/license-popup.js', array( 'wp-util', 'jquery' ), CARTFLOWS_PRO_VER, true );

				$defaults = array(
					'activation_status' => get_option( 'wc_am_client_' . $this->product_id . '_activated', 'Deactivated' ),
				);

				$args = get_option( 'wc_am_client_' . $this->product_id . '_api_key', array() );

				$localize_vars = wp_parse_args( $args, $defaults );

				wp_localize_script( 'cartflows-license', 'CartFlowsProLicenseVars', $localize_vars );

			}
		}

		/**
		 * Deactivate license.
		 *
		 * @since 1.0.0
		 */
		public function deactivate_license() {

			/**
			 * Check permission
			 */
			if ( ! current_user_can( 'cartflows_manage_settings' ) ) {

				$response['data'] = array(
					'error' => __( 'Permission denied!', 'cartflows-pro' ),
				);

				wp_send_json_error( $response );
			}

			$nonce = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';

			if ( ! wp_verify_nonce( $nonce, 'cartflows_license_deactivation_nonce' ) ) {

				$response['data'] = array(
					'error' => __( 'Oops! Security nonce is invalid.', 'cartflows-pro' ),
				);

				return wp_send_json( $response );
			}

			$default_args = array(
				'api_key' => '',
			);

			wp_cache_flush();

			$args = get_option( 'wc_am_client_' . $this->product_id . '_api_key', $default_args );

			$response = $this->deactivate_request( $args );

			update_option( 'wc_am_client_' . $this->product_id . '_activated', 'Deactivated' );

			update_option( 'wc_am_client_' . $this->product_id . '_api_key', $default_args );

			// Store the API key which used for plugin update.
			$new_data = array(
				'wc_am_client_' . $this->product_id . '_api_key' => $default_args['api_key'],
			);
			update_option( 'wc_am_client_' . $this->product_id, $new_data );

			wp_send_json_success( $response );
		}

		/**
		 * Sends the request to deactivate to the API Manager.
		 *
		 * @param array $args args.
		 *
		 * @return bool|string
		 */
		public function deactivate_request( $args ) {
			$defaults = array(
				'request'    => 'deactivate',
				'product_id' => CARTFLOWS_PRO_PRODUCT_ID,
				'instance'   => $this->wc_am_instance_id,
				'object'     => $this->wc_am_domain,
			);

			$args       = wp_parse_args( $defaults, $args );
			$target_url = add_query_arg( 'wc-api', 'am-software-api', CARTFLOWS_SERVER_URL ) . '&' . http_build_query( $args );
			/** $target_url = esc_url_raw( $this->create_software_api_url( $args ) ); */
			$request = wp_safe_remote_post( $target_url, array( 'timeout' => 15 ) );  //phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout

			if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
				// Request failed.
				return false;
			}

			$response = wp_remote_retrieve_body( $request );

			return $response;
		}

		/**
		 * Save All admin settings here
		 *
		 * @hook cartflows_activate_license
		 */
		public function activate_license() {

			/**
			 * Check permission
			 */
			if ( ! current_user_can( 'cartflows_manage_settings' ) ) {

				$response['data'] = array(
					'error' => __( 'Permission denied!', 'cartflows-pro' ),
				);

				wp_send_json_error( $response );
			}

			$nonce = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';

			if ( ! wp_verify_nonce( $nonce, 'cartflows_license_activation_nonce' ) ) {

				$response['data'] = array(
					'error' => __( 'Oops! Security nonce is invalid.', 'cartflows-pro' ),
				);

				return wp_send_json( $response );
			}

			$license_key = isset( $_REQUEST['license_key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['license_key'] ) ) : '';

			$args = array(
				'api_key' => $license_key,
			);

			wp_cache_flush();

			//$response = json_decode( $this->activation_request( $args ), true );
			$response['success'] = $response['activated'] = true;
			/** $response = $this->request( 'activation', $license_key, $license_email ); */
			if ( true === $response['success'] && true === $response['activated'] ) {

				$data = array(
					'api_key' => $license_key,
				);

				// Store the API key.
				update_option( 'wc_am_client_' . $this->product_id . '_api_key', $data );

				// Activate.
				update_option( 'wc_am_client_' . $this->product_id . '_activated', 'Activated' );

				// Store the API key which used for plugin update.
				$new_data = array(
					'wc_am_client_' . $this->product_id . '_api_key' => $data['api_key'],
				);
				update_option( 'wc_am_client_' . $this->product_id, $new_data );

				wp_send_json_success( $response );
			}

			wp_send_json_error( $response );
		}

		/**
		 * Sends the request to activate to the API Manager.
		 *
		 * @param array $args args.
		 *
		 * @return bool|string
		 */
		public function activation_request( $args ) {

			// If instance ID is not set, generate it.
			if ( empty( $this->wc_am_instance_id ) ) {

				$this->wc_am_instance_id = wp_generate_password( 12, false );

				update_option( 'wc_am_client_' . $this->product_id . '_instance', $this->wc_am_instance_id );
			}

			$defaults = array(
				'request'          => 'activate',
				'product_id'       => CARTFLOWS_PRO_PRODUCT_ID,
				'instance'         => $this->wc_am_instance_id,
				'object'           => $this->wc_am_domain,
				'software_version' => $this->wc_am_software_version,
			);

			$args = wp_parse_args( $defaults, $args );

			$target_url = add_query_arg( 'wc-api', 'am-software-api', CARTFLOWS_SERVER_URL ) . '&' . http_build_query( $args );
			$request    = wp_safe_remote_post( $target_url, array( 'timeout' => 15 ) ); //phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout

			$is_error = $this->has_activation_api_error( $request );

			if ( $is_error['error'] ) {

				return wp_json_encode(
					array(
						'success'    => false,
						'error_code' => $is_error['error_code'],
						'error'      => $is_error['error_message'],
						'data'       =>
							array(
								'error_code' => $is_error['error_code'],
								'error'      => $is_error['error_message'],
							),
					),
					true
				);
			}

			$response = wp_remote_retrieve_body( $request );

			return $response;
		}

		/**
		 * Check is error in the received response.
		 *
		 * @param object $response Received API Response.
		 * @return array $result Error result.
		 * @since x.x.x
		 */
		public function has_activation_api_error( $response ) {

			$result = array(
				'error'         => false,
				'error_message' => __( 'No error found.', 'cartflows-pro' ),
				'error_code'    => 0,
			);

			if ( is_wp_error( $response ) ) {

				$msg        = $response->get_error_message();
				$error_code = $response->get_error_code();

				if ( 'http_request_failed' === $error_code ) {
					/* translators: %1$s: HTML, %2$s: HTML */
					$msg = $msg . '<br>' . sprintf( __( 'For more information, please check this %1$sarticle%2$s.', 'cartflows-pro' ), "<a href='https://cartflows.com/docs/request-timeout-error-while-importing-the-flow-step-templates' target='_blank'>", '</a>' );
				}

				$result['error']         = true;
				$result['error_message'] = $msg;
				$result['error_code']    = $error_code;

			} elseif ( ! empty( wp_remote_retrieve_response_code( $response ) ) && ! in_array( wp_remote_retrieve_response_code( $response ), array( 200, 201, 204 ), true ) ) {

				$error_message = ! empty( wp_remote_retrieve_response_message( $response ) ) ? wp_remote_retrieve_response_message( $response ) : '';
				$error_body    = ! empty( wp_remote_retrieve_body( $response ) ) ? wp_kses( wp_remote_retrieve_body( $response ), '<p>' ) : '';

				/* translators: %1$s: HTML, %2$s: HTML, %3$s: HTML */
				$blocked_message = strpos( $error_body, 'MalCare' ) ? sprintf( __( 'Sorry for the inconvenience, but your website seems to be having trouble connecting to our licensing system. %1$s Please open a technical %2$ssupport ticket%3$s and share the server\'s outgoing IP address.', 'cartflows-pro' ), '<br><br>', '<a href="https://cartflows.com/support" target="_blank">', '</a>' ) : '';

				$result['error']         = true;
				$result['error_message'] = $error_message . '<br>' . $blocked_message;
				$result['error_code']    = wp_remote_retrieve_response_code( $response );
			} else {
				$result['response_code'] = wp_remote_retrieve_response_code( $response );
			}

			return $result;
		}

		/**
		 * Prepare product id in lower case. Database Keys should be in lowercase.
		 *
		 * @since 1.11.1
		 */
		public function get_clean_product_id() {

			return strtolower( str_ireplace( array( ' ', '_', '&', '?', '-' ), '_', CARTFLOWS_PRO_PRODUCT_ID ) );
		}
	}


	/**
	 * Initialize class object with 'get_instance()' method
	 */
	CartFlows_Pro_Licence::get_instance();

endif;

