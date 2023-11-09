<?php
/**
 * Widgets loader for Cartflows Pro.
 *
 * @package Cartflows
 * */

defined( 'ABSPATH' ) || exit;

/**
 * Set up Widgets Loader class
 */
class Cartflows_Pro_Widgets_Loader {

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
	 * Setup actions and filters.
	 *
	 * @since 1.6.13
	 */
	private function __construct() {

		if ( _is_cartflows_pluspro() ) {

			// Remove this in CartFlows major update and keep only `register`.
			$action_name = ( true === version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) ? 'register' : 'widgets_registered';

			// Register widgets.
			add_action( 'elementor/widgets/' . $action_name, array( $this, 'register_widgets' ) );

			add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'admin_enqueue_styles' ) );
		}

		$this->widget_extend_files();
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @since 1.6.13
	 * @param string $hook Current page hook.
	 * @access public
	 */
	public function admin_enqueue_styles( $hook ) {

		// Register the icons styles.
		wp_register_style(
			'cartflows-elementor-pro-icons-style',
			CARTFLOWS_PRO_URL . 'assets/elementor-assets/css/style.css',
			array(),
			CARTFLOWS_PRO_VER
		);

		wp_enqueue_style( 'cartflows-elementor-pro-icons-style' );
	}

	/**
	 * Extend widget with pro functionality.
	 *
	 * @since 1.6.13
	 */
	public function widget_extend_files() {

		require_once CARTFLOWS_PRO_DIR . 'modules/elementor/widgets/class-cartflows-pro-el-checkout-form-extend.php';
		require_once CARTFLOWS_PRO_DIR . 'modules/elementor/widgets/class-cartflows-pro-el-optin-form-extend.php';
	}

	/**
	 * Returns Script array.
	 *
	 * @return array()
	 * @since 1.6.13
	 */
	public static function get_widget_list() {

		$widget_list = array(
			'offer-action-button',
			'offer-product-description',
			'offer-action-link',
			'offer-product-title',
			'offer-product-price',
			'offer-product-quantity',
			'offer-product-variation',
			'offer-product-image',
		);

		return $widget_list;
	}

	/**
	 * Include Widgets files
	 *
	 * Load widgets files
	 *
	 * @since 1.6.13
	 * @access public
	 */
	public function include_widgets_files() {
		$widget_list = $this->get_widget_list();

		if ( ! empty( $widget_list ) ) {
			foreach ( $widget_list as $handle => $data ) {
				$file_path = CARTFLOWS_PRO_DIR . 'modules/elementor/widgets/class-cartflows-pro-el-' . $data . '.php';
				if ( file_exists( $file_path ) ) {
					require_once $file_path;
				}
			}
		}

		// Enqueue the widgets style.
		wp_enqueue_style( 'cartflows-pro-elementor-style', CARTFLOWS_PRO_URL . 'modules/elementor/widgets-css/frontend.css', array(), CARTFLOWS_PRO_VER );

		if ( \Elementor\Plugin::instance()->preview->is_preview_mode() ) {

			global $post;

			$step_type = get_post_meta( $post->ID, 'wcf-step-type', true );

			if ( 'upsell' === $step_type || 'downsell' === $step_type ) {

				wp_enqueue_style(
					'wcf-pro-flexslider',
					wcf_pro()->utils->get_css_url( 'flexslider' ),
					array(),
					CARTFLOWS_PRO_VER
				);

				wp_enqueue_script( 'flexslider' );
			}
		}
	}

	/**
	 * Register Widgets
	 *
	 * Register new Elementor widgets.
	 *
	 * @since 1.6.13
	 * @access public
	 */
	public function register_widgets() {

		global $post;

		if ( ! isset( $post ) ) {
			return;
		}

		$post_type = $post->post_type;

		$step_type = get_post_meta( $post->ID, 'wcf-step-type', true );

		if ( 'cartflows_step' === $post_type && class_exists( '\Elementor\Plugin' ) ) {

			// Its is now safe to include Widgets files.
			$this->include_widgets_files();

			$widget_list = $this->get_widget_list();

			$widget_manager = \Elementor\Plugin::$instance->widgets_manager;

			$fn_name = ( true === version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) ? 'register' : 'register_widget_type';

			foreach ( $widget_list as $widget ) {

				$widget = str_replace( '-', ' ', $widget );

				$class_name = 'Cartflows_Pro_' . str_replace( ' ', '_', ucwords( $widget ) );

				if ( $class_name::is_enable( $step_type ) ) {
					$widget_manager->{ $fn_name }( new $class_name() );
				}
			}
		}
	}
}

/**
 * Initiate the class.
 */
Cartflows_Pro_Widgets_Loader::get_instance();
