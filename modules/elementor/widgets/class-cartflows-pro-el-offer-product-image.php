<?php
/**
 * Elementor Classes.
 *
 * @package cartflows
 */

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Group_Control_Border;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Offer Product Image Widget
 *
 * @since x.x.x
 */
class Cartflows_Pro_Offer_Product_Image extends Widget_Base {

	/**
	 * Module should load or not.
	 *
	 * @since x.x.x
	 * @access public
	 * @param string $step_type Current step type.
	 *
	 * @return bool true|false.
	 */
	public static function is_enable( $step_type ) {

		if ( ( 'upsell' === $step_type || 'downsell' === $step_type ) && wcf()->is_woo_active ) {
			return true;
		}
		return false;
	}

	/**
	 * Retrieve the widget name.
	 *
	 * @since x.x.x
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'offer-product-image';
	}

	/**
	 * Retrieve the widget Image.
	 *
	 * @since x.x.x
	 *
	 * @access public
	 *
	 * @return string Widget Image.
	 */
	public function get_title() {
		return __( 'Offer Product Image', 'cartflows-pro' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @since x.x.x
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'wcf-pro-el-icon-offer-product-image';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @since x.x.x
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'cartflows-widgets' );
	}

	/**
	 * Retrieve Widget Keywords.
	 *
	 * @since x.x.x
	 * @access public
	 *
	 * @return string Widget keywords.
	 */
	public function get_keywords() {
		return array( 'cartflows', 'offer', 'product', 'image' );
	}

	/**
	 * Register Offer Product Image controls.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function register_controls() {

		// Style Tab.
		$this->register_product_image_style_controls();
		$this->register_product_thumbnails_style_controls();

	}

	/**
	 * Register Offer Product Image Style Controls.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function register_product_image_style_controls() {

		$this->start_controls_section(
			'offer_product_image_styling',
			array(
				'label' => __( 'Offer Product Image', 'cartflows-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'align',
			array(
				'label'     => __( 'Alignment', 'cartflows-pro' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'cartflows-pro' ),
						'icon'  => 'fa fa-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'cartflows-pro' ),
						'icon'  => 'fa fa-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'cartflows-pro' ),
						'icon'  => 'fa fa-align-right',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .woocommerce-product-gallery .woocommerce-product-gallery__image' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'image_bottom_spacing',
			array(
				'label'     => __( 'Image bottom Spacing', 'cartflows-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 500,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .cartflows-pro-elementor__offer-product-image .woocommerce-product-gallery .woocommerce-product-gallery__wrapper' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'label'       => __( 'Image Border', 'cartflows-pro' ),
				'show_label'  => true,
				'label_block' => true,
				'name'        => 'image_border',
				'selector'    => '{{WRAPPER}} .cartflows-pro-elementor__offer-product-image .woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image img',
			)
		);

		$this->add_control(
			'image_border_radius',
			array(
				'label'      => __( 'Image Rounded Corners', 'cartflows-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .cartflows-pro-elementor__offer-product-image .woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Register Offer Product Image Style Controls.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function register_product_thumbnails_style_controls() {

		$this->start_controls_section(
			'offer_thumbnails_styling',
			array(
				'label' => __( 'Thumbnails', 'cartflows-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'thumbnail_spacing',
			array(
				'label'     => __( 'Spacing between Thumbnails', 'cartflows-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .cartflows-pro-elementor__offer-product-image .woocommerce-product-gallery ol li:not(:last-child)' => 'margin: 0px {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0px;',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'label'       => __( 'Thumbnail Border', 'cartflows-pro' ),
				'show_label'  => true,
				'label_block' => true,
				'name'        => 'thumbnail_border',
				'selector'    => '{{WRAPPER}} .cartflows-pro-elementor__offer-product-image .woocommerce-product-gallery ol li img',
			)
		);

		$this->add_control(
			'thumbnail_border_radius',
			array(
				'label'      => __( 'Thumbnail Rounded Corners', 'cartflows-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .cartflows-pro-elementor__offer-product-image .woocommerce-product-gallery ol li img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Render Offer Product Image output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		?>

		<div class = "cartflows-pro-elementor__offer-product-image">
			<?php echo do_shortcode( '[cartflows_offer_product_image]' ); ?>
		</div>

		<?php
	}

	/**
	 * Render Offer Product Image output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function render_offer_product_image() {

		?>

		<div class = "cartflows-pro-elementor__offer-product-image">
			<?php echo do_shortcode( '[cartflows_offer_product_image]' ); ?>
		</div>
		<!-- ******************** For Image Slider ******************** -->
		<!-- <# elementorFrontend.hooks.doAction( 'frontend/element_ready/cfpro-image.default' ); #> -->
		<?php
	}

	/**
	 * Render Offer Product Image output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * Remove this after Elementor v3.3.0
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function content_template() {
		$this->render_offer_product_image();
	}
}
