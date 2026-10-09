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

class Sales_Stats_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux_sales_stats';
	}

	public function get_title() {
		return esc_html__( 'Estadística de Ventas (KPI)', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-counter-circle';
	}

	public function get_categories() {
		return [ 'alezux-finanzas' ];
	}

    public function get_style_depends() {
		return [ 'alezux-finanzas-tables-css' ]; // Reusing table CSS for consistent fonts/colors if needed
	}

    public function get_script_depends() {
		return [ 'alezux-sales-dashboard-js' ]; // New shared JS for dashboard widgets
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
			'stat_type',
			[
				'label' => esc_html__( 'Tipo de Estadística', 'alezux-members' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'month_revenue',
				'options' => [
					'month_revenue' => esc_html__( 'Ingresos Mes Actual', 'alezux-members' ),
					'today_revenue' => esc_html__( 'Ingresos Hoy', 'alezux-members' ),
				],
			]
		);

        $this->add_control(
			'custom_label',
			[
				'label' => esc_html__( 'Etiqueta Personalizada Override', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
                'description' => esc_html__('Dejar vacío para usar la etiqueta por defecto del tipo seleccionado.', 'alezux-members'),
			]
		);

        $this->add_control(
			'icon',
			[
				'label' => esc_html__( 'Icono', 'alezux-members' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-dollar-sign',
					'library' => 'fa-solid',
				],
			]
		);

		$this->end_controls_section();

        // ESTILOS
        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__('Diseño', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'card_bg_color',
            [
                'label' => esc_html__('Color de Fondo', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-kpi-card' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'card_border',
                'selector' => '{{WRAPPER}} .alezux-kpi-card',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_shadow',
                'selector' => '{{WRAPPER}} .alezux-kpi-card',
            ]
        );

        $this->add_control(
            'heading_texts',
            [
                'label' => esc_html__('Textos', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'value_color',
            [
                'label' => esc_html__('Color Valor', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-kpi-value' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'value_typography',
                'label' => 'Tipografía Valor',
                'selector' => '{{WRAPPER}} .alezux-kpi-value',
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label' => esc_html__('Color Etiqueta', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-kpi-label' => 'color: {{VALUE}};',
                ],
            ]
        );

         $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'label' => 'Tipografía Etiqueta',
                'selector' => '{{WRAPPER}} .alezux-kpi-label',
            ]
        );

         $this->add_control(
            'heading_icon',
            [
                'label' => esc_html__('Icono', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => esc_html__('Color Icono', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-kpi-icon i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .alezux-kpi-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_bg_color',
            [
                'label' => esc_html__('Fondo Icono', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-kpi-icon' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings_for_display();
        $stat_type = $settings['stat_type'];
        
        $default_label = ($stat_type === 'today_revenue') ? 'Ingresos Hoy' : 'Ingresos Mes';
        $label = !empty($settings['custom_label']) ? $settings['custom_label'] : $default_label;
        
        $this->add_render_attribute('wrapper', 'class', 'alezux-kpi-card');
        $this->add_render_attribute('wrapper', 'data-stat-type', $stat_type);
        
        ?>
        <div <?php echo $this->get_render_attribute_string('wrapper'); ?>>
            <div class="alezux-kpi-content">
                <div class="alezux-kpi-info">
                    <span class="alezux-kpi-label"><?php echo esc_html($label); ?></span>
                    <!-- Value will be populated via JS -->
                    <h3 class="alezux-kpi-value">
                        <span class="alezux-kpi-number">--</span>
                    </h3>
                </div>
                <div class="alezux-kpi-icon">
                    <?php \Elementor\Icons_Manager::render_icon( $settings['icon'], [ 'aria-hidden' => 'true' ] ); ?>
                </div>
            </div>
             <!-- Loading Overlay -->
             <div class="alezux-kpi-loading" style="display:none;">
                <i class="eicon-loading eicon-animation-spin"></i>
            </div>
        </div>
        
        <style>
            .alezux-kpi-card {
                padding: 20px;
                background: #1a1a1a;
                border: 1px solid #333;
                border-radius: 12px;
                position: relative;
                overflow: hidden;
            }
            .alezux-kpi-content {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 15px;
            }
            .alezux-kpi-info {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }
            .alezux-kpi-label {
                font-size: 14px;
                color: #888;
                font-weight: 500;
            }
            .alezux-kpi-value {
                font-size: 28px;
                color: #fff;
                margin: 0;
                font-weight: 700;
            }
            .alezux-kpi-icon {
                width: 50px;
                height: 50px;
                background: rgba(108, 92, 231, 0.1);
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                color: #6c5ce7;
            }
             .alezux-kpi-loading {
                position: absolute;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 20px;
                border-radius: 12px;
            }
        </style>
        <?php
	}
}
