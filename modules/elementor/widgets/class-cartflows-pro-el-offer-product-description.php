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
 * Offer Product Description Widget
 *
 * @since x.x.x
 */
class Cartflows_Pro_Offer_Product_Description extends Widget_Base {

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
		return 'offer-product-description';
	}

	/**
	 * Retrieve the widget description.
	 *
	 * @since x.x.x
	 *
	 * @access public
	 *
	 * @return string Widget description.
	 */
	public function get_title() {
		return __( 'Offer Product Description', 'cartflows-pro' );
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
		return 'wcf-pro-el-icon-offer-product-description';
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
		return array( 'cartflows', 'offer', 'product', 'description', 'short description' );
	}

	/**
	 * Register Offer Product Description controls.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function register_controls() {

		// Style Tab.
		$this->register_product_description_style_controls();

	}

	/**
	 * Register Offer Product Description Styling Controls.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function register_product_description_style_controls() {

		$this->start_controls_section(
			'offer_product_description_styling',
			array(
				'label' => __( 'Offer Product Description', 'cartflows-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'short_description',
			array(
				'label'        => __( 'Short Description', 'cartflows-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'cartflows-pro' ),
				'label_off'    => __( 'Hide', 'cartflows-pro' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'        => __( 'Alignment', 'cartflows-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'left'    => array(
						'title' => __( 'Left', 'cartflows-pro' ),
						'icon'  => 'fa fa-align-left',
					),
					'center'  => array(
						'title' => __( 'Center', 'cartflows-pro' ),
						'icon'  => 'fa fa-align-center',
					),
					'right'   => array(
						'title' => __( 'Right', 'cartflows-pro' ),
						'icon'  => 'fa fa-align-right',
					),
					'justify' => array(
						'title' => __( 'Justify', 'cartflows-pro' ),
						'icon'  => 'fa fa-align-justify',
					),
				),
				'default'      => 'left',
				'prefix_class' => 'elementor%s-align-',
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'Text Color', 'cartflows-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .cartflows-pro-elementor__offer-product-description' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'label'    => __( 'Typography', 'cartflows-pro' ),
				'scheme'   => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .cartflows-pro-elementor__offer-product-description',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'text_shadow',
				'label'    => __( 'Text Shadow', 'cartflows-pro' ),
				'selector' => '{{WRAPPER}} .cartflows-pro-elementor__offer-product-description',
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Render Offer Product Description output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		?>

		<div class = "cartflows-pro-elementor__offer-product-description">
			<?php
			if ( 'yes' === $settings['short_description'] ) {
				echo do_shortcode( '[cartflows_offer_product_short_desc]' );
			} else {
				echo do_shortcode( '[cartflows_offer_product_desc]' );
			}
			?>
		</div>

		<?php
	}

	/**
	 * Render Offer Product Description output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function render_offer_product_description() {
		?>

		<div class = "cartflows-pro-elementor__offer-product-description">
			<# if( 'yes' === settings.short_description ) { #>
				<?php echo do_shortcode( '[cartflows_offer_product_short_desc]' ); ?>
			<# } else { #>
				<?php echo do_shortcode( '[cartflows_offer_product_desc]' ); ?>
			<# } #>
		</div>

		<?php
	}

	/**
	 * Render Offer Product Description output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * Remove this after Elementor v3.3.0
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function content_template() {
		$this->render_offer_product_description();
	}
}
