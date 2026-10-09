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

class Date_Range_Filter_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux_date_range_filter';
	}

	public function get_title() {
		return esc_html__( 'Filtro de Fecha (Global)', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return [ 'alezux-finanzas' ];
	}

    public function get_script_depends() {
		return [ 'flatpickr-js', 'flatpickr-es-js', 'alezux-sales-dashboard-js' ]; 
	}
    
    public function get_style_depends() {
		return [ 'flatpickr-css' ]; 
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
			'placeholder',
			[
				'label' => esc_html__( 'Texto Placeholder', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Seleccionar Rango...', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

        // ESTILOS INPUT
        $this->start_controls_section(
            'style_section_input',
            [
                'label' => esc_html__('Diseño Input', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'input_bg_color',
            [
                'label' => esc_html__('Fondo Input', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-date-global-input' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_text_color',
            [
                'label' => esc_html__('Color Texto', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-date-global-input' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'input_border',
                'selector' => '{{WRAPPER}} .alezux-date-global-input',
            ]
        );

        $this->add_control(
            'input_padding',
            [
                'label' => esc_html__('Relleno', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .alezux-date-global-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings_for_display();
        
        ?>
        <div class="alezux-date-filter-wrapper">
             <div class="alezux-input-icon-wrapper">
                <span class="dashicons dashicons-calendar-alt"></span>
                <input type="text" class="alezux-date-global-input" placeholder="<?php echo esc_attr($settings['placeholder']); ?>" readonly>
                <span class="dashicons dashicons-dismiss alezux-clear-global-date" title="Limpiar" style="display:none; cursor:pointer;"></span>
             </div>
        </div>
        
        <style>
            .alezux-date-filter-wrapper {
                display: inline-block;
                width: 100%;
            }
            .alezux-input-icon-wrapper {
                position: relative;
                display: flex;
                align-items: center;
            }
            .alezux-input-icon-wrapper .dashicons-calendar-alt {
                position: absolute;
                left: 10px;
                color: #888;
                pointer-events: none;
            }
            .alezux-input-icon-wrapper .dashicons-dismiss {
                position: absolute;
                right: 10px;
                color: #888;
            }
            .alezux-date-global-input {
                width: 100%;
                padding: 10px 10px 10px 35px; /* Space for icon */
                border: 1px solid #444;
                border-radius: 6px;
                background: #1a1a1a;
                color: #fff;
                cursor: pointer;
            }
            
            /* Flatpickr Customization for this widget */
            .flatpickr-calendar {
                z-index: 99999 !important; /* Ensure it floats above everything */
                font-size: 13px !important; /* Compact font */
                /* Width and height removed to let Flatpickr calculate natural size */
            }
        </style>
        <?php
	}
}
