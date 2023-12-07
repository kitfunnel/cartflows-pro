<?php
/**
 * Cartflows Pro Action Schedular.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Action_Schedular.
 */
class Cartflows_Pro_Action_Schedular {

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

		add_action( 'cartflows_migrate_pre_checkout_offer_styles', array( $this, 'pre_checkout_offer_style_migration' ) );
	}

	/**
	 * Get the elementor widget data.
	 *
	 * @param array  $elements elements data.
	 * @param string $slug widget name.
	 */
	public function elementor_find_element_recursive( $elements, $slug = 'checkout-form' ) {
		foreach ( $elements as $element ) {
			if ( 'widget' === $element['elType'] && 'checkout-form' === $element['widgetType'] ) {
				return $element;
			}
			if ( ! empty( $element['elements'] ) ) {
				$element = $this->elementor_find_element_recursive( $element['elements'] );
				if ( $element ) {
					return $element;
				}
			}
		}
		return false;
	}

	/**
	 * Get the block data.
	 *
	 * @param array  $elements elements data.
	 * @param string $slug widget name.
	 */
	public function gutenberg_find_block_recursive( $elements, $slug = 'wcfb/checkout-form' ) {
		foreach ( $elements as $element ) {
			if ( 'wcfb/checkout-form' === $element['blockName'] ) {
				return $element;
			}
			if ( ! empty( $element['innerBlocks'] ) ) {
				$element = $this->gutenberg_find_block_recursive( $element['innerBlocks'] );
				if ( $element ) {
					return $element;
				}
			}
		}
		return false;
	}


	/**
	 * Get checkout steps & migrate the pre checkout offer styles.
	 */
	public function pre_checkout_offer_style_migration() {

		wcf()->logger->migration_log( '===============================================================' );
		wcf()->logger->migration_log( 'Start-' . __CLASS__ . '::' . __FUNCTION__ );

		$count      = 0;
		$batch_size = 10;

		// Get checkout steps where order bump is enabled & not migrated to new order bump.
		$checkout_steps = get_posts(
			array(
				'post_type'   => CARTFLOWS_STEP_POST_TYPE,
				'post_status' => array( 'publish', 'pending', 'draft', 'private' ),
				'numberposts' => $batch_size,
				'meta_query'  => array( //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'wcf-step-type',
						'value'   => 'checkout',
						'compare' => '===',
					),
					array(
						'key'     => 'wcf-pre-checkout-offer',
						'compare' => '===',
						'value'   => 'yes',
					),
					array(
						'key'     => 'wcf-pre-checkout-offer-migrated',
						'compare' => 'NOT EXISTS',
						'value'   => '',
					),
				),
			)
		);

		wcf()->logger->migration_log( 'Checkout pages count: ' . count( $checkout_steps ) );

		if ( ! empty( $checkout_steps ) ) {
			foreach ( $checkout_steps as $index => $checkout ) {

				$checkout_id = intval( $checkout->ID );
				$this->migrate_pre_checkout_offer_styles( $checkout_id );

				$count++;
			}
		}

		// If batch size is equal to count means there might be some post remaing to process so schedule the action again.
		if ( $batch_size === $count ) {
			if ( function_exists( 'as_enqueue_async_action' ) ) {
				as_enqueue_async_action( 'cartflows_migrate_pre_checkout_offer_styles' );

				wcf()->logger->migration_log( 'New action is scheduled for processing remaining checkout pages.' );
			}
		} else {
			delete_option( 'wcf_pre_checkout_offer_styles_migrated' );

			update_option( 'wcf_pre_checkout_offer_styles_migration_complete_status', 'done' );
			update_option( 'cartflows-assets-version', time() );
			if ( class_exists( '\Elementor\Plugin' ) ) {
				\Elementor\Plugin::$instance->files_manager->clear_cache();
			}

			wcf()->logger->migration_log( 'CartFlows pre checkout styles migration is completed.' );
		}

		wcf()->logger->migration_log( 'End-' . __CLASS__ . '::' . __FUNCTION__ );
		wcf()->logger->migration_log( '===============================================================' );
	}

		/**
		 * Update pre checkout offer process.
		 *
		 * @param int $checkout_id checkout id.
		 */
	public function migrate_pre_checkout_offer_styles( $checkout_id ) {

		if ( ! empty( $checkout_id ) ) {

			wcf()->logger->migration_log( 'Checkout ID: ' . $checkout_id );

			if ( class_exists( '\Elementor\Plugin' ) && 'builder' === get_post_meta( $checkout_id, '_elementor_edit_mode', true ) ) {

				wcf()->logger->migration_log( 'Page Builder: Elementor ' );

				$elementor_data = \Elementor\Plugin::$instance->documents->get( $checkout_id )->get_elements_data();

				if ( is_array( $elementor_data ) && ! empty( $elementor_data ) ) {

					$element = $this->elementor_find_element_recursive( $elementor_data );

					if ( ! empty( $element ) ) {

						$settings = $element['settings'];

						$page_builder_data = array(
							'wcf-pre-checkout-offer-bg-color'       => isset( $settings['pre_checkout_overlay_bg_color'] ) ? $settings['pre_checkout_overlay_bg_color'] : '',
							'wcf-pre-checkout-offer-model-bg-color' => isset( $settings['pre_checkout_bg_color'] ) ? $settings['pre_checkout_bg_color'] : '',
							'wcf-pre-checkout-offer-title-color'    => isset( $settings['pre_checkout_title_color'] ) ? $settings['pre_checkout_title_color'] : '',
							'wcf-pre-checkout-offer-subtitle-color' => isset( $settings['pre_checkout_sub_title_color'] ) ? $settings['pre_checkout_sub_title_color'] : '',
							'wcf-pre-checkout-offer-desc-color'     => isset( $settings['pre_checkout_desc_color'] ) ? $settings['pre_checkout_desc_color'] : '',

							'wcf-pre-checkout-offer-navbar-color'  => isset( $settings['global_primary_color'] ) ? $settings['global_primary_color'] : '',
							'wcf-pre-checkout-offer-button-color'  => isset( $settings['global_primary_color'] ) ? $settings['global_primary_color'] : '',
						);

						wcf()->logger->migration_log( 'Data Received.' );
						wcf()->logger->migration_log( wcf_print_r( $page_builder_data, true ) );

					}
				}
			} elseif ( class_exists( 'FLBuilderModel' ) && '1' === get_post_meta( $checkout_id, '_fl_builder_enabled', true ) ) {

				wcf()->logger->migration_log( 'Page Builder: Beaver Builder ' );

				$layout_data = FLBuilderModel::get_layout_data( 'published', $checkout_id );

				if ( ! empty( $layout_data ) ) {

					$css_prefix = '#';

					foreach ( $layout_data as $node => $data ) {
						if ( ! empty( $data->type ) && 'module' === $data->type && ! empty( $data->settings->type ) && 'cartflows-bb-checkout-form' === $data->settings->type ) {
							$settings = $data->settings;
							break;
						}
					}

					if ( ! empty( $settings ) ) {

						$page_builder_data = array(
							'wcf-pre-checkout-offer-bg-color'       => ! empty( $settings->pre_checkout_overlay_bg_color ) ? $css_prefix . $settings->pre_checkout_overlay_bg_color : '',
							'wcf-pre-checkout-offer-model-bg-color' => ! empty( $settings->pre_checkout_bg_color ) ? $css_prefix . $settings->pre_checkout_bg_color : '',
							'wcf-pre-checkout-offer-title-color'    => ! empty( $settings->pre_checkout_title_color ) ? $css_prefix . $settings->pre_checkout_title_color : '',
							'wcf-pre-checkout-offer-subtitle-color' => ! empty( $settings->pre_checkout_sub_title_color ) ? $css_prefix . $settings->pre_checkout_sub_title_color : '',
							'wcf-pre-checkout-offer-desc-color'     => ! empty( $settings->pre_checkout_desc_color ) ? $css_prefix . $settings->pre_checkout_desc_color : '',

							'wcf-pre-checkout-offer-navbar-color'  => ! empty( $settings->global_primary_color ) ? $settings->global_primary_color : '',
							'wcf-pre-checkout-offer-button-color'  => ! empty( $settings->global_primary_color ) ? $settings->global_primary_color : '',
						);

						wcf()->logger->migration_log( 'Data Received.' );
						wcf()->logger->migration_log( wcf_print_r( $page_builder_data, true ) );
					}
				}
			} else {

				wcf()->logger->migration_log( 'Page Builder: Gutenberg ' );

				$current_post = get_post( $checkout_id );
				$blocks       = parse_blocks( $current_post->post_content );

				if ( is_array( $blocks ) && ! empty( $blocks ) ) {

					$data = $this->gutenberg_find_block_recursive( $blocks );

					if ( ! empty( $data ) ) {

						$settings = $data['attrs'];

						$page_builder_data = array(
							'wcf-pre-checkout-offer-bg-color' => isset( $settings['OverlayBackgroundColor'] ) ? $settings['OverlayBackgroundColor'] : '',
							'wcf-pre-checkout-offer-model-bg-color' => isset( $settings['ModalBackgroundColor'] ) ? $settings['ModalBackgroundColor'] : '',
							'wcf-pre-checkout-offer-title-color' => isset( $settings['TitleColor'] ) ? $settings['TitleColor'] : '',
							'wcf-pre-checkout-offer-subtitle-color' => isset( $settings['SubtitleColor'] ) ? $settings['SubtitleColor'] : '',
							'wcf-pre-checkout-offer-desc-color'  => isset( $settings['DescriptionColor'] ) ? $settings['DescriptionColor'] : '',

							'wcf-pre-checkout-offer-navbar-color'  => isset( $settings['globalbgColor'] ) ? $settings['globalbgColor'] : '',
							'wcf-pre-checkout-offer-button-color'  => isset( $settings['globalbgColor'] ) ? $settings['globalbgColor'] : '',
						);

						wcf()->logger->migration_log( 'Data Received.' );
						wcf()->logger->migration_log( wcf_print_r( $page_builder_data, true ) );
					}
				}
			}

			// Remove empty, null, undefined values.
			$page_builder_data = array_filter( $page_builder_data, 'strlen' );

			foreach ( $page_builder_data as $key => $value ) {
				update_post_meta( $checkout_id, $key, $value );
			}

			update_post_meta( $checkout_id, 'wcf-pre-checkout-offer-migrated', 'yes' );
		}
	}

}


/**
 *  Prepare if class 'Cartflows_Pro_Action_Schedular' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Action_Schedular::get_instance();
