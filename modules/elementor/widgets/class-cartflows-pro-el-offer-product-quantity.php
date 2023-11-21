<?php
/**
 * Elementor Classes.
 *
 * @package cartflows
 */

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Border;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Offer Product Quantity Widget
 *
 * @since x.x.x
 */
class Cartflows_Pro_Offer_Product_Quantity extends Widget_Base {

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
		return 'offer-product-quantity';
	}

	/**
	 * Retrieve the widget Quantity.
	 *
	 * @since x.x.x
	 *
	 * @access public
	 *
	 * @return string Widget Quantity.
	 */
	public function get_title() {
		return __( 'Offer Product Quantity', 'cartflows-pro' );
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
		return 'wcf-pro-el-icon-offer-product-quantity';
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
		return array( 'cartflows', 'offer', 'product', 'quantity' );
	}

	/**
	 * Register Offer Product Quantity controls.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function register_controls() {

		// Style Tab.
		$this->register_product_quantity_style_controls();

	}

	/**
	 * Register Offer Product Quantity Style Controls.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function register_product_quantity_style_controls() {

		$this->start_controls_section(
			'offer_product_quantity_styling',
			array(
				'label' => __( 'Offer Product Quantity', 'cartflows-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'        => __( 'Alignment', 'cartflows-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
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
				'prefix_class' => 'cartflows-elementor__offer-product-quantity_align-',
			)
		);

		$this->add_control(
			'section_order_review_width',
			array(
				'label'     => __( 'Width(%)', 'cartflows-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'%' => array(
						'max' => 100,
					),
				),
				'default'   => array(
					'unit' => '%',
				),
				'selectors' => array(
					'{{WRAPPER}} .cartflows-pro-elementor__offer-product-quantity .quantity' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'label'    => __( 'Typography', 'cartflows-pro' ),
				'scheme'   => Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .cartflows-pro-elementor__offer-product-quantity .quantity .screen-reader-text, {{WRAPPER}} .cartflows-pro-elementor__offer-product-quantity .quantity .input-text.qty.text',
			)
		);

		$this->add_control(
			'label_color',
			array(
				'label'     => __( 'Label Color', 'cartflows-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .cartflows-pro-elementor__offer-product-quantity .quantity .screen-reader-text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'Input Text Color', 'cartflows-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .cartflows-pro-elementor__offer-product-quantity .quantity .input-text.qty.text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'bg_color',
			array(
				'label'     => __( 'Background Color', 'cartflows-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .cartflows-pro-elementor__offer-product-quantity .quantity .input-text.qty.text' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'label'       => __( 'Border', 'cartflows-pro' ),
				'show_label'  => true,
				'label_block' => true,
				'name'        => 'border',
				'selector'    => '{{WRAPPER}} .cartflows-pro-elementor__offer-product-quantity .quantity .input-text.qty.text',
			)
		);

		$this->add_control(
			'border_radius',
			array(
				'label'      => __( 'Rounded Corners', 'cartflows-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .cartflows-pro-elementor__offer-product-quantity .quantity .input-text.qty.text' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'text_shadow',
				'label'    => __( 'Text Shadow', 'cartflows-pro' ),
				'selector' => '{{WRAPPER}} .cartflows-pro-elementor__offer-product-quantity .quantity .screen-reader-text, {{WRAPPER}} .cartflows-pro-elementor__offer-product-quantity .quantity .input-text.qty.text',
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Render Offer Product Quantity output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		?>

		<div class = "cartflows-pro-elementor__offer-product-quantity">
			<?php echo do_shortcode( '[cartflows_offer_product_quantity]' ); ?>
		</div>

		<?php
	}

	/**
	 * Render Offer Product Quantity output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function render_offer_product_quantity() {
		?>

		<div class = "cartflows-pro-elementor__offer-product-quantity">
			<?php echo do_shortcode( '[cartflows_offer_product_quantity]' ); ?>
		</div>

		<?php
	}

	/**
	 * Render Offer Product Quantity output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * Remove this after Elementor v3.3.0
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function content_template() {
		$this->render_offer_product_quantity();
	}
}
