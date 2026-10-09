<?php
namespace Alezux_Members\Modules\Finanzas\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget de Elementor: Filtro de Fecha Financiero (AJAX)
 */
class Filter_Date_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux_finance_date_filter';
	}

	public function get_title() {
		return __( 'Alezux Filtro Fechas', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return [ 'alezux-lms' ];
	}

	protected function register_controls() {
		// --- SECCIÓN: CONTENIDO ---
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Configuración', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'placeholder',
			[
				'label' => __( 'Texto Placeholder', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Seleccionar Rango...', 'alezux-members' ),
			]
		);

        $this->add_control(
			'default_range',
			[
				'label' => __( 'Rango por Defecto', 'alezux-members' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'month',
                'options' => [
                    'none' => 'Vacio',
                    'today' => 'Hoy',
                    'week' => 'Esta Semana',
                    'month' => 'Este Mes',
                ]
			]
		);

		$this->end_controls_section();

		// --- SECCIÓN: ESTILO ---
		$this->start_controls_section(
			'section_style_input',
			[
				'label' => __( 'Input', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
        
        $this->add_control(
			'input_bg_color',
			[
				'label' => __( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .alezux-date-filter-input' => 'background-color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'input_text_color',
			[
				'label' => __( 'Color de Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#333333',
				'selectors' => [
					'{{WRAPPER}} .alezux-date-filter-input' => 'color: {{VALUE}};',
				],
			]
		);

        $this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'input_typography',
				'selector' => '{{WRAPPER}} .alezux-date-filter-input',
			]
		);

        $this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'input_border',
				'selector' => '{{WRAPPER}} .alezux-date-filter-input',
			]
		);

        $this->add_control(
			'input_border_radius',
			[
				'label' => __( 'Radio del Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-date-filter-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->add_control(
			'input_padding',
			[
				'label' => __( 'Relleno', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-date-filter-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->add_control(
			'icon_color',
			[
				'label' => __( 'Color Icono', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#666',
				'selectors' => [
					'{{WRAPPER}} .alezux-input-icon' => 'color: {{VALUE}};',
				],
			]
		);

        $this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
        
        // Calcular fechas por defecto para data attributes
        $start_def = '';
        $end_def = '';
        
        if ( $settings['default_range'] === 'month' ) {
            $start_def = date('Y-m-01');
            $end_def = date('Y-m-t');
        } elseif ( $settings['default_range'] === 'week' ) {
            $monday = strtotime('last monday', strtotime('tomorrow'));
            $start_def = date('Y-m-d', $monday);
            $end_def = date('Y-m-d', strtotime('next sunday', $monday));
        } elseif ( $settings['default_range'] === 'today' ) {
            $start_def = date('Y-m-d');
            $end_def = date('Y-m-d');
        }

		?>
		<div class="alezux-date-filter-wrapper">
             <div class="alezux-input-container">
                 <input type="text" class="alezux-date-filter-input" 
                        placeholder="<?php echo esc_attr( $settings['placeholder'] ); ?>" 
                        data-start="<?php echo esc_attr($start_def); ?>"
                        data-end="<?php echo esc_attr($end_def); ?>"
                        readonly>
                 <span class="alezux-input-icon eicon-calendar"></span>
             </div>
		</div>

        <style>
            .alezux-date-filter-wrapper {
                position: relative;
                width: 100%;
            }
            .alezux-input-container {
                position: relative;
                display: flex;
                align-items: center;
            }
            .alezux-date-filter-input {
                width: 100%;
                border: 1px solid #ddd;
                background: #fff;
                padding: 10px 15px 10px 40px; /* Space for icon */
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.3s;
            }
            .alezux-input-icon {
                position: absolute;
                left: 15px;
                pointer-events: none;
                font-size: 16px;
            }
        </style>
        
        <?php
        // Enqueue script if not already done via module central
        // We will do it centrally in Finanzas.php, but need localized vars here? 
        // No, global script is better.
	}
}
