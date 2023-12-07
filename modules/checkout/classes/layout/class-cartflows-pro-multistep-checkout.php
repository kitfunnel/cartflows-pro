<?php
/**
 * Multistep Checkout.
 *
 * @package CartFlows Checkout
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Multistep Checkout
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Multistep_Checkout {


	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

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
	 *  Constructor
	 */
	public function __construct() {

		// Load Multistep Layout Checkout Customization Actions.
		add_action( 'cartflows_checkout_form_before', array( $this, 'multistep_checkout_layout_actions' ), 10, 1 );

		add_filter( 'woocommerce_order_button_html', array( $this, 'add_back_nav_button_for_payment' ), 9 );

		// Update the cart total price to display on button and on the mobile order view section.
		add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'update_order_review_template' ), 99999, 1 );

		add_filter( 'cartflows_checkout_form_layout', array( $this, 'add_class_for_multistep_checkout' ), 10, 1 );

		add_filter( 'global_cartflows_js_localize', array( $this, 'localize_vars_for_multistep_layout' ) );

		add_filter( 'cartflows_get_order_review_template_path', array( $this, 'update_path_for_order_review_template' ), 10, 2 );

		add_filter( 'woocommerce_checkout_fields', array( $this, 'unset_fields_for_multistep_checkout' ), 10, 1 );
	}

	/**
	 * Add localize variables.
	 *
	 * @since 1.1.5
	 * @param array $localize settings.
	 * @return array $localize settings.
	 */
	public function localize_vars_for_multistep_layout( $localize ) {

		$checkout_id = _get_wcf_checkout_id();

		if ( ! $this->is_multistep_checkout_layout( $checkout_id ) ) {
			return $localize;
		}

		$show_order_field     = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-additional-fields' );
		$is_hide_shipping_tab = ! WC()->cart->needs_shipping() && 'no' === $show_order_field;
		$button_text          = __( 'Continue to shipping', 'cartflows-pro' );
		if ( ! WC()->cart->needs_shipping() || wc_ship_to_billing_address_only() ) {
			$button_text = __( 'Continue to order notes', 'cartflows-pro' );
		}

		if ( $is_hide_shipping_tab ) {
			$button_text = __( 'Continue to payment', 'cartflows-pro' );
		}

		$localize['is_hide_shipping_tab'] = $is_hide_shipping_tab;

		$localize['multistep_buttons_strings'] = array(
			'billing'           => $button_text,
			'shipping'          => __( 'Continue to payment', 'cartflows-pro' ),
			'is_guest_checkout' => 'yes' === get_option( 'woocommerce_enable_guest_checkout', false ),
		);

		return $localize;
	}

	/**
	 * Include custom class for multistep checkout.
	 *
	 * @param string $checkout_layout layout type.
	 */
	public function add_class_for_multistep_checkout( $checkout_layout ) {

		return 'multistep-checkout' === $checkout_layout ? 'modern-checkout wcf-modern-skin-multistep wcf-billing' : $checkout_layout;

	}

	/**
	 * Add order review template path for multistep checkout.
	 *
	 * @param string $path template path.
	 * @param string $checkout_layout layout type.
	 */
	public function update_path_for_order_review_template( $path, $checkout_layout ) {

		return 'multistep-checkout' === $checkout_layout ? CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/checkout/multistep-review-order.php' : $path;

	}

	/**
	 * Multistep Checkout Layout Actions.
	 *
	 * @param int $checkout_id checkout ID.
	 */
	public function multistep_checkout_layout_actions( $checkout_id ) {

		if ( $this->is_multistep_checkout_layout( $checkout_id ) ) {

			add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'add_multistep_checkout_breadcrumb' ), 10, 1 );

			add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'multistep_customer_info_parent_wrapper_start' ), 20, 1 );

			add_action( 'woocommerce_checkout_billing', array( $this, 'add_custom_billing_email_field' ), 9, 1 );

			add_action( 'woocommerce_review_order_before_payment', array( $this, 'display_custom_payment_heading' ), 12 );

			remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );

			add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'add_navigation_buttons' ), 99, 1 );

			// Add the collapsable order review section for mobile view at the top of Checkout form.
			add_action( 'woocommerce_before_checkout_form', array( $this, 'add_custom_collapsed_order_review_table' ), 8 );

			// Re-arrange the position of payment section only for multistep checkout.
			remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
			add_action( 'cartflows_checkout_after_multistep_checkout_layout', 'woocommerce_checkout_payment', 21 );


			add_filter( 'woocommerce_order_button_html', array( $this, 'add_back_nav_button_for_payment' ), 9 );

			add_action( 'woocommerce_before_checkout_shipping_fields', array( $this, 'add_shipping_section' ), 1 );

			remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );
			add_action( 'woocommerce_checkout_order_review', array( $this, 'custom_order_review_template' ), 9 );
		}

	}

	/**
	 * Update cart total on button and order review mobile sction.
	 *
	 * @param string $fragments shipping message.
	 *
	 * @return array $fragments updated Woo fragments.
	 */
	public function update_order_review_template( $fragments ) {

		$checkout_id = _get_wcf_checkout_id();

		if ( ! $checkout_id ) {

			$checkout_id = isset( $_GET['wcf_checkout_id'] ) && ! empty( $_GET['wcf_checkout_id'] ) ? intval( wp_unslash( $_GET['wcf_checkout_id'] ) ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ( empty( $checkout_id ) || ! $this->is_multistep_checkout_layout( $checkout_id ) ) {
			return $fragments;
		}

		ob_start();

		$this->custom_order_review_template();
		$wcf_multistep_order_review = ob_get_clean();

		ob_start();
		wc_cart_totals_shipping_html();
		$wcf_shipping_method_html = ob_get_clean();

		$fragments['.woocommerce-checkout-review-order-table'] = $wcf_multistep_order_review;
		$fragments['.woocommerce-shipping-totals']             = $wcf_shipping_method_html;

		if ( wp_doing_ajax() && isset( $_GET['wc-ajax'] ) && 'update_order_review' === $_GET['wc-ajax'] ) {  //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			ob_start();

			$this->custom_contact_section();
			$custom_contact_section = ob_get_clean();

			ob_start();

			$this->custom_address_section();
			$custom_address_section = ob_get_clean();

			$fragments['.wcf-shipping-step-review-details'] = $custom_contact_section;
			$fragments['.wcf-payment-step-review-details']  = $custom_address_section;
		}

		return $fragments;
	}

	/**
	 * Return true if checkout layout skin is conditional checkout.
	 *
	 * @param int $checkout_id Checkout ID.
	 *
	 * @return bool
	 */
	public function is_multistep_checkout_layout( $checkout_id = '' ) {
		global $post;

		$is_multistep_checkout = false;

		if ( ! empty( $post ) && empty( $checkout_id ) ) {
			$checkout_id = $post->ID;
		}

		if ( ! empty( $checkout_id ) ) {
			// Get checkout layout skin.
			$checkout_layout = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-layout' );

			if ( 'multistep-checkout' === $checkout_layout ) {
				$is_multistep_checkout = true;
			}
		}

		return $is_multistep_checkout;
	}

	/**
	 * Include custom order review template.
	 */
	public function custom_order_review_template() {
		include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/checkout/multistep-review-order.php';
	}

	/**
	 * Add custom shipping method secition.
	 */
	public function add_shipping_section() {

		ob_start();
		$this->custom_contact_section();
		?>
		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) { ?>
			<div class="wcf-multistep-shipping-methods">
				<h3><?php echo esc_html__( 'Shipping method', 'cartflows-pro' ); ?></h3>
				<div class="wcf-multistep-shipping-table">
					<table>
						<thead>
							<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
							<?php wc_cart_totals_shipping_html(); ?>
							<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
						</thead>
					</table>
				</div>
			</div>
			<?php
		}

		ob_end_flush();
	}

	/**
	 * Add custom contact section for shipping step.
	 */
	public function custom_contact_section() {

		$post_data = array();

		if ( isset( $_POST ) && ! empty( $_POST['post_data'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing

			$post_raw_data = isset( $_POST['post_data'] ) ? wp_unslash( $_POST['post_data'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			parse_str( $post_raw_data, $post_data );
		}

		$billing_email = is_array( $post_data ) && ! empty( $post_data ) ? $post_data['billing_email'] : ''; //phpcs:ignore WordPress.Security.NonceVerification.Missing

		ob_start();
		?>
		<div class="wcf-shipping-step-review-details">
			<ul class="wcf-review-details-wrapper">
				<li>
					<div class="wcf-review-details-inner">
						<div role="rowheader" class="wcf-review-detail-label"><?php echo esc_html__( 'Contact', 'cartflows-pro' ); ?></div>
						<div class="wcf-review-detail-content contact-details"><?php echo esc_html( $billing_email ); ?></div>
					</div>
					<div role="cell" class="wcf-review-detail-link">
						<a href="javascript:" data-target="billing" class="wcf-step-link"><?php echo esc_html__( 'Change', 'cartflows-pro' ); ?></a>
					</div>
				</li>
			</ul>
		</div>

		<?php

		ob_end_flush();
	}

	/**
	 * Add Customer Information Section.
	 *
	 * @param int $checkout_id checkout ID.
	 *
	 * @return void
	 */
	public function multistep_customer_info_parent_wrapper_start( $checkout_id ) {

		if ( ! $checkout_id ) {
			$checkout_id = _get_wcf_checkout_id();
		}

		do_action( 'cartflows_checkout_before_multistep_checkout_layout', $checkout_id );

		echo '<div class="wcf-customer-info-main-wrapper">';
	}

	/**
	 * Add Customer Information Section.
	 *
	 * @param int $checkout_id checkout ID.
	 *
	 * @return void
	 */
	public function add_multistep_checkout_breadcrumb( $checkout_id ) {

		if ( ! $checkout_id ) {
			$checkout_id = _get_wcf_checkout_id();
		}

		$breadcrumb_text = __( 'Shipping', 'cartflows-pro' );

		if ( ! WC()->cart->needs_shipping() || wc_ship_to_billing_address_only() ) {
			$breadcrumb_text = __( 'Order notes', 'cartflows-pro' );
		}

		$show_order_field = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-additional-fields' );

		if ( ! WC()->cart->needs_shipping() && 'no' === $show_order_field ) {
			echo '<div class="wcf-multistep-checkout-breadcrumb">
					<span class="wcf-checkout-breadcrumb-list">
						<li class="wcf-checkout-breadcrumb information-step wcf-current-step"><a href="" data-tab="billing" class="wcf-current-step">' . esc_html__( 'Information', 'cartflows-pro' ) . '</a></li>
						<li class="wcf-checkout-breadcrumb payment-step"><a href="" data-tab="payment">' . esc_html__( 'Payment', 'cartflows-pro' ) . '</a></li>
					</span>
				</div>';

		} else {
			echo '<div class="wcf-multistep-checkout-breadcrumb">
						<span class="wcf-checkout-breadcrumb-list">
							<li class="wcf-checkout-breadcrumb information-step wcf-current-step"><a href="" data-tab="billing" class="wcf-current-step">' . esc_html__( 'Information', 'cartflows-pro' ) . '</a></li>
							<li class="wcf-checkout-breadcrumb shipping-step"><a href="" data-tab="shipping">' . esc_html( $breadcrumb_text ) . '</a></li>
							<li class="wcf-checkout-breadcrumb payment-step"><a href="" data-tab="payment">' . esc_html__( 'Payment', 'cartflows-pro' ) . '</a></li>
						</span>
				</div>';
		}
	}

	/**
	 * Add back button for payment step.
	 *
	 * @param string $html Button HTML.
	 */
	public function add_back_nav_button_for_payment( $html ) {

		$checkout_id = wcf()->utils->get_checkout_id_from_post_data();

		if ( ! $checkout_id ) {
			$checkout_id = _get_wcf_checkout_id();
		}

		if ( ! $this->is_multistep_checkout_layout( $checkout_id ) || ( wp_doing_ajax() && isset( $_REQUEST['action'] ) && 'update_order_review' !== $_REQUEST['action'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $html;
		}

		$show_order_field = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-additional-fields' );

		$back_button = '<a href="" class="wcf-multistep-nav-back-btn" data-target="shipping">' . __( '« Back to shipping', 'cartflows-pro' ) . '</a>';

		if ( ! WC()->cart->needs_shipping() || wc_ship_to_billing_address_only() ) {
			$back_button = '<a href="" class="wcf-multistep-nav-back-btn" data-target="shipping">' . __( '« Back to order notes', 'cartflows-pro' ) . '</a>';
		}

		if ( ! WC()->cart->needs_shipping() && 'no' === $show_order_field ) {
			$back_button = '<a href="" class="wcf-multistep-nav-back-btn" data-target="billing">' . __( '« Back to information', 'cartflows-pro' ) . '</a>';
		}

		return "<div class='wcf-multistep-buttons-wrapper'>" . $back_button . $html . '</div>';
	}


	/**
	 * Add bottom navigation buttons.
	 *
	 * @param int $checkout_id checkout ID.
	 */
	public function add_navigation_buttons( $checkout_id ) {

		if ( ! $checkout_id ) {
			$checkout_id = _get_wcf_checkout_id();
		}

		do_action( 'cartflows_checkout_after_multistep_checkout_layout', $checkout_id );

		$second_step_btn_text = __( 'Continue to shipping', 'cartflows-pro' );

		if ( ! WC()->cart->needs_shipping() || wc_ship_to_billing_address_only() ) {
			$second_step_btn_text = __( 'Continue to order notes', 'cartflows-pro' );
		}
		$data_target      = 'shipping';
		$show_order_field = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-additional-fields' );

		if ( ! WC()->cart->needs_shipping() && 'no' === $show_order_field ) {
			$second_step_btn_text = __( 'Continue to payment', 'cartflows-pro' );
			$data_target          = 'payment';
		}

		echo "<div class='wcf-multistep-nav-btn-group'>
				<a href='' class='wcf-multistep-nav-back-btn' data-target=''>" . esc_html__( '« Back to information', 'cartflows-pro' ) . "</a>
				<span class='wcf-multistep-nav-next-btn' data-target='" . esc_attr( $data_target ) . "'>" . esc_html( $second_step_btn_text ) . '</span>
			</div></div>';

	}

	/**
	 * Add Custom Email Field.
	 *
	 * @return void
	 */
	public function add_custom_billing_email_field() {

		$checkout_id = _get_wcf_checkout_id();

		if ( ! $checkout_id ) {
			$checkout_id = isset( $_GET['wcf_checkout_id'] ) && ! empty( $_GET['wcf_checkout_id'] ) ? intval( wp_unslash( $_GET['wcf_checkout_id'] ) ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$default = '';

		if ( ( function_exists( 'is_auto_prefill_checkout_fields_enabled' ) && is_auto_prefill_checkout_fields_enabled() ) && ( isset( $_GET['billing_email'] ) && ! empty( $_GET['billing_email'] ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$default = sanitize_email( wp_unslash( $_GET['billing_email'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$lost_password_url  = esc_url( wp_lostpassword_url() );
		$current_user_name  = wp_get_current_user()->display_name;
		$current_user_email = wp_get_current_user()->user_email;
		$is_allow_login     = 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' );
		$fields_skins       = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-fields-skins' );
		$required_mark      = 'modern-label' === $fields_skins ? '&#42;' : '';

		?>
			<div class="wcf-customer-info" id="customer_info">
				<div class="wcf-customer-info__notice"></div>
				<div class="woocommerce-billing-fields-custom">
					<h3><?php echo wp_kses_post( apply_filters( 'cartflows_woo_customer_info_text', esc_html__( 'Customer information', 'cartflows-pro' ) ) ); ?>
						<?php if ( ! is_user_logged_in() && $is_allow_login ) { ?>
							<div class="woocommerce-billing-fields__customer-login-label"><?php /* translators: %1$s: Link HTML start, %2$s Link HTML End */ echo wp_kses_post( sprintf( __( 'Already have an account? %1$1s Log in%2$2s', 'cartflows-pro' ), '<a href="javascript:" class="wcf-customer-login-url">', '</a>' ) ); ?></div>
						<?php } ?>
					</h3>
					<div class="woocommerce-billing-fields__customer-info-wrapper">
					<?php
					if ( ! is_user_logged_in() ) {

							woocommerce_form_field(
								'billing_email',
								array(
									'type'         => 'email',
									'class'        => array( 'form-row-fill' ),
									'required'     => true,
									'label'        => __( 'Email Address', 'cartflows-pro' ),
									'default'      => $default,
									/* translators: %s: asterisk mark */
									'placeholder'  => sprintf( __( 'Email Address %s', 'cartflows-pro' ), $required_mark ),
									'autocomplete' => 'email username',
								)
							);

						if ( 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) {
							?>
								<div class="wcf-customer-login-section">
								<?php
									woocommerce_form_field(
										'billing_password',
										array(
											'type'        => 'password',
											'class'       => array( 'form-row-fill', 'wcf-password-field' ),
											'required'    => true,
											'label'       => __( 'Password', 'cartflows-pro' ),
											/* translators: %s: asterisk mark */
											'placeholder' => sprintf( __( 'Password %s', 'cartflows-pro' ), $required_mark ),
										)
									);
								?>
								<div class="wcf-customer-login-actions">
							<?php
									echo "<input type='button' name='wcf-customer-login-btn' class='button wcf-customer-login-section__login-button' id='wcf-customer-login-section__login-button' value='" . esc_attr( __( 'Login', 'cartflows-pro' ) ) . "'>";
									echo "<a href='" . esc_url( $lost_password_url ) . "' class='wcf-customer-login-lost-password-url'>" . esc_html( __( 'Lost your password?', 'cartflows-pro' ) ) . '</a>';
							?>
								</div>
							<?php
							if ( 'yes' === get_option( 'woocommerce_enable_guest_checkout', false ) ) {
								echo "<p class='wcf-login-section-message'>" . esc_html( __( 'Login is optional, you can continue with your order below.', 'cartflows-pro' ) ) . '</p>';
							}
							?>
								</div>
						<?php } ?>
						<?php
						if ( 'yes' === get_option( 'woocommerce_enable_signup_and_login_from_checkout' ) ) {
							?>
								<div class="wcf-create-account-section" hidden>
								<?php if ( 'yes' === get_option( 'woocommerce_enable_guest_checkout' ) ) { ?>
										<p class="form-row form-row-wide create-account">
											<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
												<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e( 'Create an account?', 'cartflows-pro' ); ?></span>
											</label>
										</p>
									<?php } ?>
									<div class="create-account">
									<?php

									if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) {
										woocommerce_form_field(
											'account_username',
											array(
												'type'     => 'text',
												'class'    => array( 'form-row-fill' ),
												'id'       => 'account_username',
												'required' => true,
												'label'    => __( 'Account username', 'cartflows-pro' ),
												/* translators: %s: asterisk mark */
												'placeholder' => sprintf( __( 'Account username %s', 'cartflows-pro' ), $required_mark ),
											)
										);
									}
									if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) {
										woocommerce_form_field(
											'account_password',
											array(
												'type'     => 'password',
												'id'       => 'account_password',
												'class'    => array( 'form-row-fill' ),
												'required' => true,
												'label'    => __( 'Create account password', 'cartflows-pro' ),
												/* translators: %s: asterisk mark */
												'placeholder' => sprintf( __( 'Create account password %s', 'cartflows-pro' ), $required_mark ),
											)
										);
									}
									?>
									</div>
								</div>
						<?php } ?>
					<?php } else { ?>
								<div class="wcf-logged-in-customer-info"> <?php /* translators: %1$s: username, %2$s emailid */ echo wp_kses_post( apply_filters( 'cartflows_logged_in_customer_info_text', sprintf( __( ' Welcome Back %1$s ( %2$s )', 'cartflows-pro' ), esc_attr( $current_user_name ), esc_attr( $current_user_email ) ) ) ); ?>
									<div><input type="hidden" class="wcf-email-address" id="billing_email" name="billing_email" value="<?php echo esc_attr( $current_user_email ); ?>"/></div>
								</div>
					<?php } ?>
					</div>
				</div>
			</div>
		<?php
	}

	/**
	 * Display Payment heading field after coupon code fields.
	 */
	public function display_custom_payment_heading() {

		ob_start();
		$this->custom_address_section()
		?>
		<div class="wcf-payment-option-heading">
			<h3 id="payment_options_heading"><?php echo wp_kses_post( apply_filters( 'cartflows_woo_payment_text', esc_html__( 'Payment', 'cartflows-pro' ) ) ); ?></h3>
		</div>
		<?php
		echo wp_kses_post( ob_get_clean() );
	}

	/**
	 * Display Payment heading field after coupon code fields.
	 */
	public function custom_address_section() {

		$post_data = array();

		if ( isset( $_POST ) && ! empty( $_POST['post_data'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			$post_raw_data = isset( $_POST['post_data'] ) ? wp_unslash( $_POST['post_data'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			parse_str( $post_raw_data, $post_data );
		}

		$billing_email = '';
		$ship_to       = '';
		$output        = '';

		$shipping_data_target = ! WC()->cart->needs_shipping() || wc_ship_to_billing_address_only() ? 'billing' : 'shipping';

		if ( ! empty( $post_data ) && ! empty( $post_data ) ) {

			$countries = WC()->countries->countries;

			$billing_email = $post_data['billing_email'];

			if ( ! empty( $post_data['shipping_first_name'] ) ) {
				$state   = isset( $post_data['shipping_state'] ) ? $post_data['shipping_state'] : '';
				$country = isset( $post_data['shipping_country'] ) ? $post_data['shipping_country'] : '';

				if ( ! empty( $state ) && ! empty( $country ) ) {
					$state = WC()->countries->states[ $country ][ $state ];
				}

				$address = array(
					$post_data['shipping_first_name'] . ' ' . $post_data['shipping_last_name'],
					! empty( $post_data['shipping_address_1'] ) ? $post_data['shipping_address_1'] : '',
					! empty( $post_data['shipping_address_2'] ) ? $post_data['shipping_address_2'] : '',
					! empty( $post_data['shipping_city'] ) ? $post_data['shipping_city'] : '',
					! empty( $post_data['shipping_postcode'] ) ? $post_data['shipping_postcode'] : '',
					! empty( $state ) ? $state : '',
					! empty( $country ) ? $countries[ $country ] : '',
				);
			} else {
				$state   = isset( $post_data['billing_state'] ) ? $post_data['billing_state'] : '';
				$country = isset( $post_data['billing_country'] ) ? $post_data['billing_country'] : '';

				if ( ! empty( $state ) && ! empty( $country ) ) {
					$state = WC()->countries->states[ $country ][ $state ];
				}

				$address = array(
					$post_data['billing_first_name'] . ' ' . $post_data['billing_last_name'],
					! empty( $post_data['billing_address_1'] ) ? $post_data['billing_address_1'] : '',
					! empty( $post_data['billing_address_2'] ) ? $post_data['billing_address_2'] : '',
					! empty( $post_data['billing_city'] ) ? $post_data['billing_city'] : '',
					! empty( $post_data['billing_postcode'] ) ? $post_data['billing_postcode'] : '',
					! empty( $state ) ? $state : '',
					! empty( $country ) ? $countries[ $country ] : '',
				);
			}

			$ship_to = implode( ', ', array_filter( $address ) );

		}

		ob_start();
		?>
		<div class="wcf-payment-step-review-details">
			<ul class="wcf-review-details-wrapper">
				<li>
					<div class="wcf-review-details-inner">
						<div role="rowheader" class="wcf-review-detail-label"><?php echo esc_html__( 'Contact', 'cartflows-pro' ); ?></div>
						<div class="wcf-review-detail-content contact-details"><?php echo esc_html( $billing_email ); ?></div>
					</div>
					<div role="cell" class="wcf-review-detail-link">
						<a href="javascript:" data-target="billing" class="wcf-step-link"><?php echo esc_html__( 'Change', 'cartflows-pro' ); ?></a>
					</div>
				</li>

				<li>
					<div class="wcf-review-details-inner">
						<div role="rowheader" class="wcf-review-detail-label"><?php echo esc_html__( 'Address', 'cartflows-pro' ); ?></div>
						<div class="wcf-review-detail-content shipping-details"><?php echo esc_html( $ship_to ); ?></div>
					</div>
					<div role="cell" class="wcf-review-detail-link">
						<a href="javascript:" data-target=<?php echo esc_attr( $shipping_data_target ); ?> class="wcf-step-link"><?php echo esc_html__( 'Change', 'cartflows-pro' ); ?></a>
					</div>
				</li>
			</ul>
		</div>

		<?php
		$output .= ob_get_clean();

		echo wp_kses_post( $output );
	}

	/**
	 * Prefill the checkout fields if available in url.
	 *
	 * @param array $checkout_fields checkout fields array.
	 */
	public function unset_fields_for_multistep_checkout( $checkout_fields ) {

		$checkout_id = _get_wcf_checkout_id();

		if ( ! $checkout_id ) {
			$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
		}

		if ( ! $this->is_multistep_checkout_layout( $checkout_id ) ) {
			return $checkout_fields;
		}
		// No nonce verification required as it is called on woocommerce action.
		if ( isset( $_GET['wc-ajax'] ) && 'checkout' === $_GET['wc-ajax'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $checkout_fields;
		}

		// Unset defalut billing email from Billing Details.
		unset( $checkout_fields['billing']['billing_email'] );
		unset( $checkout_fields['account']['account_username'] );
		unset( $checkout_fields['account']['account_password'] );

		return $checkout_fields;
	}

	/**
	 * Customized order review section used to display in modern checkout responsive devices.
	 *
	 * @return void
	 */
	public function add_custom_collapsed_order_review_table() {

		include CARTFLOWS_CHECKOUT_DIR . 'templates/checkout/collapsed-order-summary.php';
	}

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Multistep_Checkout::get_instance();
