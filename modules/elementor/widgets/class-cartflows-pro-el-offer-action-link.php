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
use Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Offer Yes No Button Widget
 *
 * @since x.x.x
 */
class Cartflows_Pro_Offer_Action_Link extends Widget_Base {

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
		return 'offer-yes-no-link';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @since x.x.x
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Offer Yes/No Link', 'cartflows-pro' );
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
		return 'wcf-pro-el-icon-yes-no-link';
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
		return array( 'cartflows', 'offer', 'product', 'offer link', 'yes', 'no' );
	}

	/**
	 * Register Offer Yes/No Link controls controls.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function register_controls() {

		// Content Tab.
		$this->register_link_content_controls();

		// Style Tab.
		$this->register_link_style_controls();

	}

	/**
	 * Register Offer Yes/No Link General Controls.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function register_link_content_controls() {

		$this->start_controls_section(
			'link_content_fields',
			array(
				'label' => __( 'General', 'cartflows-pro' ),
			)
		);

		$this->add_control(
			'offer_action',
			array(
				'label'   => __( 'Offer Action', 'cartflows-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'accept_offer',
				'options' => array(
					'accept_offer' => __( 'Accept Offer', 'cartflows-pro' ),
					'reject_offer' => __( 'Reject Offer', 'cartflows-pro' ),
				),
			)
		);

		$this->add_control(
			'link_text',
			array(
				'label'   => __( 'Link Text', 'cartflows-pro' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Offer Action', 'cartflows-pro' ),
			)
		);

		$this->add_control(
			'icon',
			array(
				'label' => __( 'Icon', 'cartflows-pro' ),
				'type'  => Controls_Manager::ICONS,
			)
		);

		$this->add_control(
			'icon_position',
			array(
				'label'     => __( 'Icon Position', 'cartflows-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'before_link',
				'options'   => array(
					'before_link' => __( 'Before Link', 'cartflows-pro' ),
					'after_link'  => __( 'After Link', 'cartflows-pro' ),
				),
				'condition' => array(
					'icon[value]!' => '',
				),
			)
		);

		$this->add_control(
			'icon_spacing',
			array(
				'label'     => __( 'Icon Spacing', 'cartflows-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .cartflows-pro-elementor__offer-yes-no-link .cartflows-pro-elementor__before_link' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .cartflows-pro-elementor__offer-yes-no-link .cartflows-pro-elementor__after_link' => 'margin-left: {{SIZE}}{{UNIT}};',
				),

				'condition' => array(
					'icon[value]!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'     => __( 'Icon Size', 'cartflows-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .cartflows-pro-elementor__offer-yes-no-link i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .cartflows-pro-elementor__offer-yes-no-link svg' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'icon[value]!' => '',
				),
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Register Offer Yes/No Link Styling Controls.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function register_link_style_controls() {

		$this->start_controls_section(
			'offer_yes_no_button_styling',
			array(
				'label' => __( 'General', 'cartflows-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
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

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'link_typography',
				'label'    => __( 'Link Typography', 'cartflows-pro' ),
				'scheme'   => Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .cartflows-pro-elementor__offer-yes-no-link-text-wrap',
			)
		);

		$this->start_controls_tabs( 'offer_yes_no_link_style' );

			$this->start_controls_tab(
				'offer_yes_no_link_normal',
				array(
					'label' => __( 'Normal', 'cartflows-pro' ),
				)
			);

				$this->add_control(
					'link_color',
					array(
						'label'     => __( 'Link Color', 'cartflows-pro' ),
						'type'      => Controls_Manager::COLOR,
						'default'   => '',
						'selectors' => array(
							'{{WRAPPER}} a.cartflows-pro-elementor__offer-yes-no-link-text-wrap' => 'color: {{VALUE}};',
						),
					)
				);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'offer_yes_no_link_hover',
				array(
					'label' => __( 'Hover', 'cartflows-pro' ),
				)
			);

				$this->add_control(
					'hover_link_color',
					array(
						'label'     => __( 'Hover Link Color', 'cartflows-pro' ),
						'type'      => Controls_Manager::COLOR,
						'default'   => '',
						'selectors' => array(
							'{{WRAPPER}} a.cartflows-pro-elementor__offer-yes-no-link-text-wrap:hover' => 'color: {{VALUE}};',
						),
					)
				);

			$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}

	/**
	 * Render Offer Yes/No Link icon.
	 *
	 * @since x.x.x
	 * @param string $position Icon position.
	 * @param array  $settings settings.
	 * @access protected
	 */
	protected function render_link_icon( $position, $settings ) {

		$icon                = esc_attr( $settings['icon']['value'] );
		$icon_position_class = 'cartflows-pro-elementor__' . $settings['icon_position'];

		if ( '' !== $icon && $position === $settings['icon_position'] ) {
			?>
			<span class="cartflows-pro-elementor__offer-yes-no-link-icon-wrap <?php echo esc_attr( $icon_position_class ); ?>">
				<?php Icons_Manager::render_icon( $settings['icon'], array( 'aria-hidden' => 'true' ) ); ?>
			</span>
			<?php
		}

	}

	/**
	 * Render Yes No Link output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function render() {
		$settings  = $this->get_settings_for_display();
		$link_text = esc_html( $settings['link_text'] );
		$step_type = get_post_meta( get_the_ID(), 'wcf-step-type', true );
		if ( 'upsell' === $step_type || 'downsell' === $step_type ) {
			$accept_offer_link = 'upsell' === $step_type ? '?class=wcf-up-offer-yes' : '?class=wcf-down-offer-yes';
			$reject_offer_link = 'upsell' === $step_type ? '?class=wcf-up-offer-no' : '?class=wcf-down-offer-no';
		}
		$action_link = 'accept_offer' === $settings['offer_action'] ? $accept_offer_link : $reject_offer_link;
		?>

		<div class="cartflows-pro-elementor__offer-yes-no-link">
			<a href="<?php echo esc_url( $action_link ); ?>" class="cartflows-pro-elementor__offer-yes-no-link-text-wrap">
				<?php $this->render_link_icon( 'before_link', $settings ); ?>
					<span class="cartflows-pro-elementor__offer-yes-no-link-text"><?php echo esc_html( $link_text ); ?></span>
				<?php $this->render_link_icon( 'after_link', $settings ); ?>
			</a>
		</div>

		<?php

	}

	/**
	 * Render Menu Offer Yes No Link output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function render_offer_action_link() {
		?>

		<#
			function render_icon(position) {

				var iconHTML = elementor.helpers.renderIcon( view, settings.icon, {}, 'i' , 'object' );
				var icon_position_class = 'cartflows-pro-elementor__'+settings.icon_position;
				if( '' !== settings.icon.value && position === settings.icon_position ){ #>
					<span class="cartflows-pro-elementor__offer-yes-no-link-icon-wrap {{icon_position_class}}">{{ iconHTML.value }}</span>
				<#}
			}
		#>

		<div class="cartflows-pro-elementor__offer-yes-no-link">
			<a href="#" class="cartflows-pro-elementor__offer-yes-no-link-text-wrap">
				<# render_icon('before_link') #>
					<span class="cartflows-pro-elementor__offer-yes-no-link-text">
						{{settings.link_text}}
					</span>
				<# render_icon('after_link') #>
			</a>

		</div>

		<?php
	}

	/**
	 * Render Offer Yes No Link output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * Remove this after Elementor v3.3.0
	 *
	 * @since x.x.x
	 * @access protected
	 */
	protected function content_template() {
		$this->render_offer_action_link();
	}
}
