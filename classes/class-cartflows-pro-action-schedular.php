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

		add_action( 'cartflows_migrate_order_bumps', array( $this, 'old_order_bump_migration' ) );

		add_action( 'cartflows_beta_migrate_order_bump_format', array( $this, 'beta_order_bump_migration' ) );

		add_action( 'cartflows_migrate_pre_checkout_offer_styles', array( $this, 'pre_checkout_offer_style_migration' ) );
	}

	/**
	 * Update beta order bump saving format.
	 */
	public function beta_order_bump_migration() {

		$page = get_option( 'wcf-beta-order-bump-page', 1 );

		$checkout_steps_args = array(
			'post_type'      => CARTFLOWS_STEP_POST_TYPE,
			'post_status'    => array( 'publish', 'pending', 'draft', 'private' ),
			'posts_per_page' => 20,
			'meta_query'  => array( //phpcs:ignore
				array(
					'key'     => 'wcf-step-type	',
					'value'   => 'checkout',
					'compare' => '===',
				),
				array(
					'key'     => 'wcf-order-bumps',
					'compare' => 'EXISTS',
				),
			),
			'paged'          => $page,
		);

		$result = new WP_Query( $checkout_steps_args );

		if ( $result->have_posts() && $result->max_num_pages <= $page ) {

			$checkout_steps = $result->posts;

			foreach ( $checkout_steps as $index => $checkout ) {

				$checkout_id = intval( $checkout->ID );

				$old_ob_format = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bumps' );

				if ( is_array( $old_ob_format ) && ! empty( $old_ob_format ) ) {

					$new_ob_format = array();

					foreach ( $old_ob_format as $ob_id => $ob_data ) {
						array_push( $new_ob_format, $ob_data );
					}

					update_post_meta( $checkout_id, 'wcf-order-bumps', $new_ob_format );
				}
			}

			$page++;

			update_option( 'wcf-beta-order-bump-page', $page );
			as_enqueue_async_action( 'cartflows_beta_migrate_ob_format' );

		} else {
			delete_option( 'wcf-beta-order-bump-page' );
		}

	}

	/**
	 * Update order bump process.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function old_order_bump_migration() {

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
				'meta_query'  => array( //phpcs:ignore
					array(
						'key'     => 'wcf-step-type	',
						'value'   => 'checkout',
						'compare' => '===',
					),
					array(
						'key'     => 'wcf-order-bump',
						'compare' => '===',
						'value'   => 'yes',
					),
					array(
						'key'     => 'wcf-order-bumps',
						'compare' => 'NOT EXISTS',
						'value'   => '',
					),
				),
			)
		);

		wcf()->logger->migration_log( 'Checkout pages count: ' . count( $checkout_steps ) );

		$default_meta    = Cartflows_Pro_Checkout_Default_Meta::get_instance()->order_bump_default_meta();
		$ob_default_meta = array();
		foreach ( $default_meta as $key => $value ) {
			$ob_default_meta[ $key ] = $value['default'];
		}

		if ( ! empty( $checkout_steps ) ) {
			foreach ( $checkout_steps as $index => $checkout ) {

				$checkout_id = intval( $checkout->ID );
				$this->migrate_order_bump_data( $checkout_id, $ob_default_meta );

				$count++;
			}
		}

		// If batch size is equal to count means there might be some post remaing to process so schedule the action again.
		if ( $batch_size === $count ) {
			if ( function_exists( 'as_enqueue_async_action' ) ) {
				as_enqueue_async_action( 'cartflows_migrate_order_bumps' );

				wcf()->logger->migration_log( 'New action is scheduled for processing remaining checkout pages.' );
			}
		} else {
			delete_option( 'wcf_order_bump_migrated' );

			update_option( 'wcf_migration_complete_status', 'done' );
			update_option( 'cartflows-assets-version', time() );
			if ( class_exists( '\Elementor\Plugin' ) ) {
				\Elementor\Plugin::$instance->files_manager->clear_cache();
			}

			wcf()->logger->migration_log( 'CartFlows Order Bump migration is completed.' );
		}

		wcf()->logger->migration_log( 'End-' . __CLASS__ . '::' . __FUNCTION__ );
		wcf()->logger->migration_log( '===============================================================' );
	}

	/**
	 * Update order bump process.
	 *
	 * @param int   $checkout_id checkout id.
	 * @param array $ob_default_meta default ob meta.
	 *
	 * @return void
	 */
	public function migrate_order_bump_data( $checkout_id, $ob_default_meta ) {

		$unique_key = substr( md5( wp_rand() ), 2, 3 );

		if ( ! empty( $unique_key ) ) {

			wcf()->logger->migration_log( 'Checkout ID: ' . $checkout_id . ' | ' . 'Unique key: ' . $unique_key ); // phpcs:ignore
			$product       = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-product' );
			$product_image = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-image' );

			$hide_image_mobile = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-show-bump-image-mobile' );
			$show_image_mobile = 'yes' === $hide_image_mobile ? 'no' : $hide_image_mobile;

			$order_bumps       = array();
			$page_builder_data = array();
			$old_ob            = array(
				// Set New Keys.
				'id'                => $unique_key,
				'title'             => __( 'No title', 'cartflows-pro' ),
				'status'            => true,
				'width'             => '100',
				'enable_show_image' => ! empty( $product_image ) ? 'yes' : 'no',

				// Set Old Keys.
				'product'           => isset( $product[0] ) ? $product[0] : '',
				'product_image'     => $product_image,
				'quantity'          => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-product-quantity' ),
				'discount_type'     => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-discount' ),
				'discount_value'    => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-discount-value' ),
				'discount_coupon'   => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-discount-coupon' ),
				'product_img_obj'   => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-image-obj' ),
				'checkbox_label'    => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-label' ),
				'title_text'        => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-title' ),
				'hl_text'           => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-hl-text' ),
				'desc_text'         => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-desc' ),
				'replace_product'   => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-replace' ),
				'next_step'         => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-ob-yes-next-step' ),
				'position'          => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-position' ),
				'show_image_mobile' => $show_image_mobile,

				'style'             => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-style' ),
				'border_color'      => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-border-color' ),
				'border_style'      => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-border-style' ),
				'bg_color'          => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-bg-color' ),
				'label_color'       => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-label-color' ),
				'label_bg_color'    => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-label-bg-color' ),
				'desc_text_color'   => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-desc-text-color' ),
				'hl_text_color'     => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-hl-text-color' ),
				'show_arrow'        => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-show-bump-arrow' ),
				'show_animation'    => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-show-bump-animate-arrow' ),
			);

			// Update data with default meta data.
			$old_ob = wp_parse_args( $old_ob, $ob_default_meta );

			if ( class_exists( '\Elementor\Plugin' ) && 'builder' === get_post_meta( $checkout_id, '_elementor_edit_mode', true ) ) {

				wcf()->logger->migration_log( 'Page Builder: Elementor ' ); // phpcs:ignore

				$elementor_data = \Elementor\Plugin::$instance->documents->get( $checkout_id )->get_elements_data();

				if ( is_array( $elementor_data ) && ! empty( $elementor_data ) ) {

					$element = $this->elementor_find_element_recursive( $elementor_data );

					if ( ! empty( $element ) ) {

						$settings = $element['settings'];

						$page_builder_data = array(
							'style'           => $settings['order_bump_skin'],
							'show_arrow'      => $settings['order_bump_checkbox_arrow'],
							'show_animation'  => $settings['order_bump_checkbox_arrow_animation'],
							'bg_color'        => $settings['order_bump_bg_color'],
							'label_color'     => $settings['order_bump_label_color'],
							'label_bg_color'  => $settings['order_bump_label_bg_color'],
							'hl_text_color'   => $settings['order_bump_hl_text_color'],
							'desc_text_color' => $settings['order_bump_desc_color'],
							'border_color'    => $settings['order_bump_border_color'],
							'border_style'    => $settings['order_bump_border_style'],
						);

						wcf()->logger->migration_log( 'Data Received.' );
						wcf()->logger->migration_log( print_r( $page_builder_data, true ) ); // phpcs:ignore

					}
				}
			} elseif ( class_exists( 'FLBuilderModel' ) && '1' === get_post_meta( $checkout_id, '_fl_builder_enabled', true ) ) {

				wcf()->logger->migration_log( 'Page Builder: Beaver Builder ' ); // phpcs:ignore

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
							'style'           => $settings->order_bump_skin,
							'show_arrow'      => $settings->order_bump_checkbox_arrow,
							'show_animation'  => $settings->order_bump_checkbox_arrow_animation,
							'bg_color'        => ! empty( $settings->order_bump_bg_color ) ? $css_prefix . $settings->order_bump_bg_color : '',
							'label_color'     => ! empty( $settings->order_bump_label_color ) ? $css_prefix . $settings->order_bump_label_color : '',
							'label_bg_color'  => ! empty( $settings->order_bump_label_bg_color ) ? $css_prefix . $settings->order_bump_label_bg_color : '',
							'hl_text_color'   => ! empty( $settings->order_bump_hl_text_color ) ? $css_prefix . $settings->order_bump_hl_text_color : '',
							'desc_text_color' => ! empty( $settings->order_bump_desc_color ) ? $css_prefix . $settings->order_bump_desc_color : '',
							'border_color'    => ! empty( $settings->order_bump_border_color ) ? $css_prefix . $settings->order_bump_border_color : '',
							'border_style'    => $settings->order_bump_border_style,
						);

						wcf()->logger->migration_log( 'Data Received.' );
						wcf()->logger->migration_log( print_r( $page_builder_data, true ) ); // phpcs:ignore
					}
				}
			} else {

				wcf()->logger->migration_log( 'Page Builder: Gutenberg ' ); // phpcs:ignore

				$current_post = get_post( $checkout_id );
				$blocks       = parse_blocks( $current_post->post_content );

				if ( is_array( $blocks ) && ! empty( $blocks ) ) {

					$data = $this->gutenberg_find_block_recursive( $blocks );

					if ( ! empty( $data ) ) {

						$settings = $data['attrs'];

						$page_builder_data = array(
							'style'           => $settings['orderBumpSkin'],
							'show_arrow'      => $settings['orderBumpCheckboxArrow'],
							'show_animation'  => $settings['orderBumpCheckboxArrowAnimation'],
							'bg_color'        => $settings['orderbumpcontentBgColor'],
							'label_color'     => $settings['orderbumpTextColor'],
							'label_bg_color'  => $settings['orderbumpTextBgColor'],
							'hl_text_color'   => $settings['orderbumpcontentTextColor'],
							'desc_text_color' => $settings['orderbumpcontentTextColor'],
							'border_color'    => $settings['orderbumpBorderColor'],
							'border_style'    => $settings['orderbumpBorderStyle'],
						);

						wcf()->logger->migration_log( 'Data Received.' );
						wcf()->logger->migration_log( print_r( $page_builder_data, true ) ); // phpcs:ignore
					}
				}
			}

			// Remove empty, null, undefined values.
			$page_builder_data = array_filter( $page_builder_data, 'strlen' );

			// Override the CSS to plage builder CSS.
			$old_ob = wp_parse_args( $page_builder_data, $old_ob );

			array_push( $order_bumps, $old_ob );

			update_post_meta( $checkout_id, 'wcf-order-bumps', $order_bumps );
			update_post_meta( $checkout_id, 'wcf-order-bump-migrated', 'yes' );
		}
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
				'meta_query'  => array( //phpcs:ignore
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

			wcf()->logger->migration_log( 'Checkout ID: ' . $checkout_id ); // phpcs:ignore

			if ( class_exists( '\Elementor\Plugin' ) && 'builder' === get_post_meta( $checkout_id, '_elementor_edit_mode', true ) ) {

				wcf()->logger->migration_log( 'Page Builder: Elementor ' ); // phpcs:ignore

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
						wcf()->logger->migration_log( print_r( $page_builder_data, true ) ); // phpcs:ignore

					}
				}
			} elseif ( class_exists( 'FLBuilderModel' ) && '1' === get_post_meta( $checkout_id, '_fl_builder_enabled', true ) ) {

				wcf()->logger->migration_log( 'Page Builder: Beaver Builder ' ); // phpcs:ignore

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
						wcf()->logger->migration_log( print_r( $page_builder_data, true ) ); // phpcs:ignore
					}
				}
			} else {

				wcf()->logger->migration_log( 'Page Builder: Gutenberg ' ); // phpcs:ignore

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
						wcf()->logger->migration_log( print_r( $page_builder_data, true ) ); // phpcs:ignore
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
