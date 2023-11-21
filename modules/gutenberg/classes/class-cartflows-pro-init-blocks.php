<?php
/**
 * Cartflows Pro Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.6.13
 * @package Cartflows Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cartflows_Pro_Init_Blocks.
 *
 * @package Cartflows Pro
 */
class Cartflows_Pro_Init_Blocks {

	/**
	 * Member Variable
	 *
	 * @var instance
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
	 * Constructor
	 */
	public function __construct() {

		// Hook: Frontend assets.
		add_action( 'enqueue_block_assets', array( $this, 'block_assets' ) );

		// Hook: Block assets.
		add_action( 'enqueue_block_editor_assets', array( $this, 'editor_assets' ) );

		if ( version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ) {
			add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );
		} else {
			add_filter( 'block_categories', array( $this, 'register_block_category' ), 10, 2 );
		}

		add_action( 'wp_ajax_wpcfp_offer_product_title_shortcode', array( $this, 'offer_product_title_shortcode' ) );
		add_action( 'wp_ajax_wpcfp_offer_product_description_shortcode', array( $this, 'offer_product_description_shortcode' ) );
		add_action( 'wp_ajax_wpcfp_offer_product_price_shortcode', array( $this, 'offer_product_price_shortcode' ) );
		add_action( 'wp_ajax_wpcfp_offer_product_quantity_shortcode', array( $this, 'offer_product_quantity_shortcode' ) );
		add_action( 'wp_ajax_wpcfp_offer_product_variation_shortcode', array( $this, 'offer_product_variation_shortcode' ) );
		add_action( 'wp_ajax_wpcfp_offer_product_image_shortcode', array( $this, 'offer_product_image_shortcode' ) );

		// Add GB editor compatibility.
		add_action( 'cartflows_gutenberg_editor_compatibility', array( $this, 'gutenberg_editor_compatibility' ) );

		add_filter( 'cartflows_gutenberg_blocks_attributes', array( $this, 'add_pro_checkout_form_attributes' ), 10, 1 );
	}

	/**
	 * Update attrinutes.
	 *       *
	 *
	 * @param array $attr attr List.
	 */
	public function add_pro_checkout_form_attributes( $attr ) {

		$product_border_attribute    = Cartflows_Pro_Gb_Helper::generate_border_attribute( 'product' );
		$product_hl_border_attribute = Cartflows_Pro_Gb_Helper::generate_border_attribute( 'productHighlight' );

		$attr['wcfb/checkout-form']['attributes'] = array_merge( $attr['wcfb/checkout-form']['attributes'], $product_border_attribute, $product_hl_border_attribute );

		return $attr;
	}

	/**
	 * Gutenberg editor compatibility.
	 *
	 * @param int $step_id Step id.
	 *
	 * @since 1.6.13
	 */
	public function gutenberg_editor_compatibility( $step_id ) {

		if ( wcf()->utils->check_is_offer_page( $step_id ) ) {
			add_filter( 'cartflows_is_offer_type', '__return_true' );
		}
	}

	/**
	 * Renders the Offer Product Title shortcode.
	 *
	 * @since 1.6.13
	 */
	public function offer_product_title_shortcode() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cartflows-pro' ) ) );
		}

		check_ajax_referer( 'wpcfp_ajax_nonce', 'nonce' );

		$data['html'] = do_shortcode( '[cartflows_offer_product_title]' );

		wp_send_json_success( $data );
	}

	/**
	 * Renders the Offer Product Description shortcode.
	 *
	 * @since 1.6.13
	 */
	public function offer_product_description_shortcode() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cartflows-pro' ) ) );
		}

		check_ajax_referer( 'wpcfp_ajax_nonce', 'nonce' );

		if ( isset( $_POST['shortDescription'] ) && 'true' === $_POST['shortDescription'] ) {
			$data['html'] = do_shortcode( '[cartflows_offer_product_short_desc]' );

		} else {
			$data['html'] = do_shortcode( '[cartflows_offer_product_desc]' );
		}

		wp_send_json_success( $data );
	}


	/**
	 * Renders the Offer Product Price shortcode.
	 *
	 * @since 1.6.13
	 */
	public function offer_product_price_shortcode() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cartflows-pro' ) ) );
		}

		check_ajax_referer( 'wpcfp_ajax_nonce', 'nonce' );

		$data['html'] = do_shortcode( '[cartflows_offer_product_price]' );

		wp_send_json_success( $data );
	}

	/**
	 * Renders the Offer Product Quantity shortcode.
	 *
	 * @since 1.6.13
	 */
	public function offer_product_quantity_shortcode() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cartflows-pro' ) ) );
		}

		check_ajax_referer( 'wpcfp_ajax_nonce', 'nonce' );

		$data['html'] = do_shortcode( '[cartflows_offer_product_quantity]' );

		wp_send_json_success( $data );
	}

	/**
	 * Renders the Offer Product Variation shortcode.
	 *
	 * @since 1.6.13
	 */
	public function offer_product_variation_shortcode() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cartflows-pro' ) ) );
		}

		check_ajax_referer( 'wpcfp_ajax_nonce', 'nonce' );

		$data['html'] = do_shortcode( '[cartflows_offer_product_variation]' );

		wp_send_json_success( $data );
	}

	/**
	 * Renders the Offer Product Image shortcode.
	 *
	 * @since 1.6.13
	 */
	public function offer_product_image_shortcode() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cartflows-pro' ) ) );
		}

		check_ajax_referer( 'wpcfp_ajax_nonce', 'nonce' );

		$data['html'] = do_shortcode( '[cartflows_offer_product_image]' );

		wp_send_json_success( $data );
	}

	/**
	 * Enqueue Gutenberg block assets for both frontend + backend.
	 *
	 * @since 1.6.13
	 */
	public function block_assets() {

		$post = get_post();

		if ( isset( $post ) && CARTFLOWS_STEP_POST_TYPE === $post->post_type ) {

			/**
			 * Filters the post to build stylesheet for.
			 *
			 * @param \WP_Post $post The global post.
			 */
			$post = apply_filters( 'cfp_post_for_stylesheet', $post );

			// Register block styles for both frontend + backend.
			wp_enqueue_style(
				'CFP_block-cfp-style-css', // Handle.
				CARTFLOWS_PRO_URL . 'modules/gutenberg/build/style-blocks.css',
				is_admin() ? array( 'wp-editor' ) : null, // Dependency to include the CSS after it.
				CARTFLOWS_PRO_VER // filemtime( plugin_dir_path( __DIR__ ) . 'modules/gutenberg/dist/blocks.style.build.css' ) // Version: File modification time.
			);

			$blocks       = Cartflows_Pro_Block_Config::get_block_attributes();
			$block_assets = Cartflows_Pro_Block_Config::get_block_assets();

			foreach ( $blocks as $slug => $value ) {
				$_slug = str_replace( 'wcfpb/', '', $slug );

				$js_assets = ( isset( $blocks[ $slug ]['js_assets'] ) ) ? $blocks[ $slug ]['js_assets'] : array();

				$css_assets = ( isset( $blocks[ $slug ]['css_assets'] ) ) ? $blocks[ $slug ]['css_assets'] : array();

				foreach ( $js_assets as $asset_handle => $val ) {
					// Scripts.
					wp_register_script(
						$val, // Handle.
						$block_assets[ $val ]['src'],
						$block_assets[ $val ]['dep'],
						CARTFLOWS_PRO_VER,
						true
					);

					$skip_editor = isset( $block_assets[ $val ]['skipEditor'] ) ? $block_assets[ $val ]['skipEditor'] : false;

					if ( is_admin() && false === $skip_editor ) {
						wp_enqueue_script( $val );
					}
				}
			}
		}

	}

	/**
	 * Enqueue Gutenberg block assets for backend.
	 *
	 * @since 1.6.13
	 */
	public function editor_assets() {

		$post_id   = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_type = get_post_type( $post_id );

		if ( CARTFLOWS_STEP_POST_TYPE === $post_type ) {

			$wpcfp_ajax_nonce       = wp_create_nonce( 'wpcfp_ajax_nonce' );
			$step_type              = wcf()->utils->get_step_type( $post_id );
			$enable_product_options = wcf()->options->get_checkout_meta_value( $post_id, 'wcf-enable-product-options' );
			$enable_order_bump      = wcf()->options->get_checkout_meta_value( $post_id, 'wcf-order-bump' );

			wp_enqueue_style(
				'wcf-pro-flexslider',
				wcf_pro()->utils->get_css_url( 'flexslider' ),
				array(),
				CARTFLOWS_PRO_VER,
				false
			);
			wp_enqueue_script(
				'flexslider',
				plugins_url() . '/woocommerce/assets/js/flexslider/jquery.flexslider.js',
				array( 'jquery' ),
				CARTFLOWS_PRO_VER,
				false
			);

			$script_dep_path = CARTFLOWS_PRO_DIR . 'modules/gutenberg/build/blocks.asset.php';
			$script_info     = file_exists( $script_dep_path )
				? include $script_dep_path
				: array(
					'dependencies' => array(),
					'version'      => CARTFLOWS_VER,
				);
			$script_dep      = array_merge( $script_info['dependencies'], array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ) );
			$script_ver      = $script_info['version'];

			// Register block editor script for backend.
			wp_register_script(
				'CFP_block-cfp-block-js', // Handle.
				CARTFLOWS_PRO_URL . 'modules/gutenberg/build/blocks.js',
				$script_dep, // Dependencies, defined above.
				$script_ver, // Version: filemtime — Gets file modification.
				true // Enqueue the script in the footer.
			);

			wp_set_script_translations( 'CFP_block-cfp-block-js', 'cartflows-pro' );

			// Register block editor styles for backend.
			wp_register_style(
				'CFP_block-cfp-block-editor-css', // Handle.
				CARTFLOWS_PRO_URL . 'modules/gutenberg/build/blocks.style.css',
				array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
				CARTFLOWS_PRO_VER // filemtime( plugin_dir_path( __DIR__ ) . 'modules/gutenberg/dist/blocks.editor.build.css' ) // Version: File modification time.
			);

			// Enqueue frontend CSS in editor.
			wp_enqueue_style( 'CFP_block-cartflows-frotend-style', CARTFLOWS_PRO_URL . 'assets/css/frontend.css', array( 'wp-edit-blocks', 'wp-editor' ), CARTFLOWS_PRO_VER );
			wp_enqueue_style( 'cartflows-pro-optin-style', wcf_pro()->utils->get_css_url( 'optin-styles' ), array( 'wp-edit-blocks', 'wp-editor' ), CARTFLOWS_PRO_VER );
			wp_enqueue_style( 'cartflows-pro-checkout-style', wcf_pro()->utils->get_css_url( 'checkout-styles' ), array( 'wp-edit-blocks', 'wp-editor' ), CARTFLOWS_PRO_VER );
			wp_enqueue_style( 'cartflows-pro-multistep-checkout-style', wcf_pro()->utils->get_css_url( 'multistep-checkout' ), array( 'wp-edit-blocks', 'wp-editor' ), CARTFLOWS_PRO_VER );
			wp_enqueue_script( 'wcf-editor-helper', CARTFLOWS_PRO_URL . 'modules/gutenberg/assets/js/editor-helper.js', array( 'wp-editor', 'jquery' ), CARTFLOWS_PRO_VER, true );

			// WP Localized globals. Use dynamic PHP stuff in JavaScript via `cfpGlobal` object.
			wp_localize_script(
				'CFP_block-cfp-block-js',
				'cfp_blocks_info', // Array containing dynamic data for a JS Global.
				array(
					'pluginDirPath'             => plugin_dir_path( __DIR__ ),
					'pluginDirUrl'              => plugin_dir_url( __DIR__ ),
					'category'                  => 'cartflows-pro',
					'ajax_url'                  => admin_url( 'admin-ajax.php' ),
					'wpcfp_ajax_nonce'          => $wpcfp_ajax_nonce,
					'blocks'                    => Cartflows_Pro_Block_Config::get_block_attributes(),
					'block_assets'              => Cartflows_Pro_Block_Config::get_block_assets(),
					'tablet_breakpoint'         => CFP_TABLET_BREAKPOINT,
					'mobile_breakpoint'         => CFP_MOBILE_BREAKPOINT,
					'step_type'                 => $step_type,
					'ID'                        => $post_id,
					'enable_product_options'    => $enable_product_options,
					'enable_order_bump'         => $enable_order_bump,
					'is_woo_active'             => wcf()->is_woo_active,
					'multistep_buttons_strings' => array(
						'billing'  => __( 'Continue to shipping', 'cartflows-pro' ),
						'shipping' => __( 'Continue to payment', 'cartflows-pro' ),
					),
				// Add more data here that you want to access from `cfpGlobal` object.
				)
			);

			/**
			 * Register Gutenberg block on server-side.
			 *
			 * Register the block on server-side to ensure that the block
			 * scripts and styles for both frontend and backend are
			 * enqueued when the editor loads.
			 *
			 * @link https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type#enqueuing-block-scripts
			 * @since 1.6.13
			 */
			register_block_type(
				'wcfpb/offer-yes-no-button',
				array(
					// Enqueue blocks.build.js in the editor only.
					'editor_script' => 'CFP_block-cfp-block-js',
					// Enqueue blocks.editor.build.css in the editor only.
					'style'         => 'CFP_block-cfp-block-editor-css',
					// Enqueue blocks.commoneditorstyle.build.css in the editor only.
					'editor_style'  => 'CFP_block-common-editor-css',
				)
			);
		}
	}

	/**
	 * Gutenberg block category for Cartflows Pro.
	 *
	 * @param array  $categories Block categories.
	 * @param object $post Post object.
	 * @since 1.6.13
	 */
	public function register_block_category( $categories, $post ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'cartflows-pro',
					'title' => __( 'Cartflows Pro', 'cartflows-pro' ),
				),
			)
		);
	}

}

/**
 *  Prepare if class 'Cartflows_Pro_Init_Blocks' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Init_Blocks::get_instance();
