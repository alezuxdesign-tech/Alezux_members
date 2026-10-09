<?php
namespace Alezux_Members\Modules\Finanzas\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sales_Chart_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux_sales_chart';
	}

	public function get_title() {
		return esc_html__( 'Gráfico de Ventas', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-chart-pie';
	}

	public function get_categories() {
		return [ 'alezux-finanzas' ];
	}

    public function get_script_depends() {
		return [ 'chart-js', 'alezux-sales-dashboard-js' ]; // Dependencies: Chart.js + Dashboard Logic
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Configuración', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

        $this->add_control(
			'chart_type',
			[
				'label' => esc_html__( 'Tipo de Gráfico', 'alezux-members' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'doughnut',
				'options' => [
					'pie' => esc_html__( 'Torta (Pie)', 'alezux-members' ),
					'doughnut' => esc_html__( 'Dona (Doughnut)', 'alezux-members' ),
                    'bar' => esc_html__( 'Barras (Bar)', 'alezux-members' ),
				],
			]
		);

         $this->add_control(
			'chart_title',
			[
				'label' => esc_html__( 'Título del Gráfico', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Ingresos por Método', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

        // ESTILOS DE COLORES
        $this->start_controls_section(
            'style_section_colors',
            [
                'label' => esc_html__('Colores del Gráfico', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'stripe_color',
            [
                'label' => esc_html__('Color Stripe', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6772e5',
            ]
        );

        $this->add_control(
            'manual_color',
            [
                'label' => esc_html__('Color Manual', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'default' => '#2ecc71',
            ]
        );
        
        $this->add_control(
            'paypal_color',
            [
                'label' => esc_html__('Color PayPal', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'default' => '#003087',
            ]
        );

        $this->end_controls_section();

        // ESTILOS CONTENEDOR
        $this->start_controls_section(
            'style_section_container',
            [
                'label' => esc_html__('Diseño Contenedor', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

         $this->add_control(
            'container_bg',
            [
                'label' => esc_html__('Fondo', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-chart-container' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'container_border',
                'selector' => '{{WRAPPER}} .alezux-chart-container',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'container_shadow',
                'selector' => '{{WRAPPER}} .alezux-chart-container',
            ]
        );
         
         $this->add_control(
            'title_color',
            [
                'label' => esc_html__('Color Título', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-chart-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .alezux-chart-title',
            ]
        );

        $this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings_for_display();
        
        $this->add_render_attribute('wrapper', 'class', 'alezux-chart-card');
        $this->add_render_attribute('wrapper', 'data-chart-type', $settings['chart_type']);
        
        // Pass colors to JS via data attributes
        $this->add_render_attribute('wrapper', 'data-color-stripe', $settings['stripe_color']);
        $this->add_render_attribute('wrapper', 'data-color-manual', $settings['manual_color']);
        $this->add_render_attribute('wrapper', 'data-color-paypal', $settings['paypal_color']);

        ?>
        <div class="alezux-chart-container">
            <?php if(!empty($settings['chart_title'])): ?>
                <h3 class="alezux-chart-title"><?php echo esc_html($settings['chart_title']); ?></h3>
            <?php endif; ?>
            
            <div class="alezux-chart-wrapper" style="position: relative; height:300px; width:100%;">
                <canvas id="alezuxSalesChart-<?php echo $this->get_id(); ?>" <?php echo $this->get_render_attribute_string('wrapper'); ?>></canvas>
                 
                 <!-- Loading Overlay -->
                <div class="alezux-chart-loading">
                    <i class="eicon-loading eicon-animation-spin"></i>
                </div>
            </div>
        </div>
        
        <style>
            .alezux-chart-container {
                padding: 20px;
                background: #1a1a1a;
                border: 1px solid #333;
                border-radius: 12px;
            }
            .alezux-chart-title {
                margin: 0 0 20px 0;
                font-size: 18px;
                color: #fff;
                font-weight: 600;
            }
            .alezux-chart-loading {
                position: absolute;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(26,26,26,0.7);
                display: flex; /* Flex but dependent on parent wrapper logic to show/hide */
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 30px;
                border-radius: 12px;
                z-index: 10;
            }
        </style>
        <?php
	}
}
