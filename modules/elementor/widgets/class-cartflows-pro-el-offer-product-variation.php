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

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Offer Product Variation Widget
 *
 * @since x.x.x
 */
class Cartflows_Pro_Offer_Product_Variation extends Widget_Base {

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
		return 'offer-product-variation';
	}

	/**
	 * Retrieve the widget Variation.
	 *
	 * @since x.x.x
	 *
	 * @access public
	 *
	 * @return string Widget Variation.
	 */
	public function get_title() {
		return __( 'Offer Product Variation', 'cartflows-pro' );
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
		return 'wcf-pro-el-icon-offer-product-variations';
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
		return array( 'cartflows', 'offer', 'product', 'variation' );
	}

	/**
	 * Register Offer Product Variation controls.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function register_controls() {

		// Style Tab.
		$this->register_product_variation_style_controls();

	}

	/**
	 * Register Offer Product Variation Style Controls.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function register_product_variation_style_controls() {

		$this->start_controls_section(
			'offer_product_variation_styling',
			array(
				'label' => __( 'Offer Product Variation', 'cartflows-pro' ),
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
				'prefix_class' => 'cartflows-elementor__offer-product-variation_align%s-',
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
					'{{WRAPPER}} .cartflows-pro-elementor__offer-product-variation .wcf-embeded-product-variation-wrap .variations' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'label_color',
			array(
				'label'     => __( 'Label Color', 'cartflows-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .cartflows-pro-elementor__offer-product-variation label' => 'color: {{VALUE}};',
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
					'{{WRAPPER}} .cartflows-pro-elementor__offer-product-variation .wcf-embeded-product-variation-wrap .variations .value select' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'label'    => __( 'Typography', 'cartflows-pro' ),
				'scheme'   => Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .cartflows-pro-elementor__offer-product-variation,
				{{WRAPPER}} .cartflows-pro-elementor__offer-product-variation .wcf-embeded-product-variation-wrap .variations .value select,
				{{WRAPPER}} .cartflows-pro-elementor__offer-product-variation .label label',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'text_shadow',
				'label'    => __( 'Text Shadow', 'cartflows-pro' ),
				'selector' => '{{WRAPPER}} .cartflows-pro-elementor__offer-product-variation, {{WRAPPER}} .cartflows-pro-elementor__offer-product-variation .wcf-embeded-product-variation-wrap .variations .value select,
				{{WRAPPER}} .cartflows-pro-elementor__offer-product-variation label',
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Render Offer Product Variation output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		?>

		<div class = "cartflows-pro-elementor__offer-product-variation">
			<?php echo do_shortcode( '[cartflows_offer_product_variation]' ); ?>
		</div>

		<?php
	}

	/**
	 * Render Offer Product Variation output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function render_offer_product_variation() {
		?>

		<div class = "cartflows-pro-elementor__offer-product-variation">
			<?php echo do_shortcode( '[cartflows_offer_product_variation]' ); ?>
		</div>

		<?php
	}

	/**
	 * Render Offer Product Variation output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * Remove this after Elementor v3.3.0
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function content_template() {
		$this->render_offer_product_variation();
	}
}
