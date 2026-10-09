<?php
namespace Alezux_Members\Modules\Finanzas\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Alezux_Members\Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class User_Financial_Profile extends Widget_Base {

	public function get_name() {
		return 'alezux_user_financial_profile';
	}

	public function get_title() {
		return esc_html__( 'Perfil Financiero de Usuario', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-price-table';
	}

	public function get_categories() {
		return [ 'alezux-lms' ];
	}

	public function get_script_depends() {
		return [ 'alezux-finanzas-frontend' ];
	}

	public function get_style_depends() {
		return [ 'alezux-finanzas-tables-css' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Contenido', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title_status',
			[
				'label'   => esc_html__( 'Título Estado de Cuenta', 'alezux-members' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Estado de Cuenta', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'content_section_advanced',
			[
				'label' => esc_html__( 'Opciones Avanzadas', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'preview_modal_editor',
			[
				'label' => esc_html__( 'Previsualizar Modal (Editor)', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Sí', 'alezux-members' ),
				'label_off' => esc_html__( 'No', 'alezux-members' ),
				'return_value' => 'yes',
				'default' => '',
				'description' => esc_html__( 'Activa esto para ver el modal de historial de pagos con datos de ejemplo mientras editas.', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

		// -- ESTILOS --
        // 1. DISEÑO DE LA TABLA (Tabla y Encabezados)
        $this->start_controls_section(
            'style_section_table',
            [
                'label' => esc_html__('Diseño de la Tabla', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'table_container_heading',
            [
                'label' => esc_html__('Contenedor & Cuerpo', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'table_background',
                'label' => esc_html__('Fondo Tabla', 'alezux-members'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .alezux-finanzas-app, {{WRAPPER}} .alezux-table-wrapper',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'table_border',
                'label' => esc_html__('Borde Tabla', 'alezux-members'),
                'selector' => '{{WRAPPER}} .alezux-finanzas-app',
            ]
        );

        $this->add_control(
            'table_radius',
            [
                'label' => esc_html__('Radio de Borde', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .alezux-finanzas-app' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->add_control(
            'table_row_bg_general',
            [
                'label' => esc_html__('Fondo Filas (General)', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                 'selectors' => [
                    '{{WRAPPER}} .alezux-finanzas-table tbody tr' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .alezux-finanzas-table tbody td' => 'background-color: {{VALUE}};',
                ],
            ]
        );

         $this->add_control(
            'table_row_bg',
            [
                'label' => esc_html__('Fondo Filas (Alterno)', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                 'selectors' => [
                    '{{WRAPPER}} .alezux-finanzas-table tbody tr:nth-child(even)' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'table_header_heading',
            [
                'label' => esc_html__('Encabezados (Títulos)', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'header_bg_color',
            [
                'label' => esc_html__('Color Fondo Encabezado', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-finanzas-table thead th' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'header_text_color',
            [
                'label' => esc_html__('Color Texto Encabezado', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-finanzas-table thead th' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'header_typography',
                'selector' => '{{WRAPPER}} .alezux-finanzas-table thead th',
            ]
        );

        $this->end_controls_section();

        // 2. TEXTOS (Plan, Monto, Fechas)
        $this->start_controls_section(
            'style_section_texts',
            [
                'label' => esc_html__('Textos (Plan, Monto, Etc)', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control('heading_plan_text', ['type' => Controls_Manager::HEADING, 'label' => 'Nombre del Plan']);
        $this->add_control(
            'plan_name_color',
            [
                'label' => esc_html__('Color', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => ['{{WRAPPER}} .col-plan-name' => 'color: {{VALUE}};'],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'plan_name_typo',
                'selector' => '{{WRAPPER}} .col-plan-name',
            ]
        );

        $this->add_control('heading_amount_text', ['type' => Controls_Manager::HEADING, 'label' => 'Monto', 'separator' => 'before']);
        $this->add_control(
            'amount_color',
            [
                'label' => esc_html__('Color', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => ['{{WRAPPER}} .col-amount' => 'color: {{VALUE}};'],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'amount_typo',
                'selector' => '{{WRAPPER}} .col-amount',
            ]
        );

        $this->end_controls_section();

        // 3. ESTADO (Badges)
        $this->start_controls_section(
            'style_section_badges',
            [
                'label' => esc_html__('Estado (Badges)', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'badge_typography',
                'selector' => '{{WRAPPER}} .alezux-status-badge',
            ]
        );

        $this->add_control(
            'badge_radius',
             [
                'label' => esc_html__('Redondeo', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .alezux-status-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'badge_padding',
             [
                'label' => esc_html__('Relleno', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .alezux-status-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        // Colores por estado
        $this->add_control('heading_badge_active', ['type' => Controls_Manager::HEADING, 'label' => 'Activo', 'separator' => 'before']);
        $this->add_control('badge_active_text', ['label' => 'Texto', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-active' => 'color: {{VALUE}};']]);
        $this->add_control('badge_active_bg', ['label' => 'Fondo', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-active' => 'background-color: {{VALUE}};']]);

        $this->add_control('heading_badge_completed', ['type' => Controls_Manager::HEADING, 'label' => 'Completado', 'separator' => 'before']);
        $this->add_control('badge_completed_text', ['label' => 'Texto', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-completed' => 'color: {{VALUE}};']]);
        $this->add_control('badge_completed_bg', ['label' => 'Fondo', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-completed' => 'background-color: {{VALUE}};']]);

        $this->add_control('heading_badge_past_due', ['type' => Controls_Manager::HEADING, 'label' => 'Vencido', 'separator' => 'before']);
        $this->add_control('badge_past_due_text', ['label' => 'Texto', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-past_due' => 'color: {{VALUE}};']]);
        $this->add_control('badge_past_due_bg', ['label' => 'Fondo', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-past_due' => 'background-color: {{VALUE}};']]);

        $this->end_controls_section();

        // 4. PROGRESO
         $this->start_controls_section(
            'style_section_progress',
            [
                'label' => esc_html__('Progreso', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'progress_label_typography',
                'label' => 'Tipografía Etiquetas',
                'selector' => '{{WRAPPER}} .progress-Label span',
            ]
        );
        
        $this->add_control(
            'progress_label_color',
            [
                'label' => esc_html__('Color Etiquetas', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .progress-Label span' => 'color: {{VALUE}};',
                ],
            ]
        );

         $this->add_responsive_control(
            'progress_height',
            [
                'label' => esc_html__('Altura Barra', 'alezux-members'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 2,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .alezux-progress-bar-bg' => 'height: {{SIZE}}{{UNIT}};',
                     '{{WRAPPER}} .alezux-progress-bar-fill' => 'height: 100%;',
                ],
            ]
        );

        $this->add_control(
            'progress_radius',
            [
                'label' => esc_html__('Redondeo Barra', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .alezux-progress-bar-bg' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .alezux-progress-bar-fill' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'progress_bg_heading',
            [
                'label' => esc_html__('Fondo de la Barra (Contenedor)', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'progress_container_bg',
                'label' => esc_html__('Fondo Contenedor', 'alezux-members'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .alezux-progress-bar-bg',
            ]
        );

        $this->add_control(
            'progress_fill_heading',
            [
                'label' => esc_html__('Relleno de la Barra (Progreso)', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'progress_fill_bg',
                'label' => esc_html__('Fondo Relleno', 'alezux-members'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .alezux-progress-bar-fill',
            ]
        );

        $this->end_controls_section();


        // 5. ACCIONES (BOTONES)
        $this->start_controls_section(
            'style_section_actions',
            [
                'label' => esc_html__('Acciones (Botones)', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control('heading_btn_pay', ['type' => Controls_Manager::HEADING, 'label' => 'Botón Pagar']);
        
        $this->add_control(
            'btn_pay_bg_color',
            ['label' => 'Fondo', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .alezux-btn-pay' => 'background-color: {{VALUE}};']]
        );
        $this->add_control(
            'btn_pay_text_color',
            ['label' => 'Texto', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .alezux-btn-pay' => 'color: {{VALUE}};']]
        );

        $this->add_control('heading_btn_report', ['type' => Controls_Manager::HEADING, 'label' => 'Botón Ver Pagos', 'separator' => 'before']);
        
        $this->add_control(
            'btn_report_bg_color',
            ['label' => 'Fondo', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .alezux-btn-report' => 'background-color: {{VALUE}};']]
        );
        $this->add_control(
            'btn_report_text_color',
            ['label' => 'Texto', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .alezux-btn-report' => 'color: {{VALUE}};']]
        );

        $this->end_controls_section();

        // 6. ESTILOS DEL MODAL DE REPORTE
        $this->start_controls_section(
            'style_section_modal',
            [
                'label' => esc_html__('Estilos Modal Reporte', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'modal_overlay_bg',
            [
                'label' => esc_html__('Fondo Overlay', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => ['#alezux-report-modal-{{ID}}' => 'background-color: {{VALUE}} !important;'],
            ]
        );

        $this->add_control(
            'modal_content_heading',
            [
                'label' => esc_html__('Contenedor Modal', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'modal_bg_color',
            [
                'label' => esc_html__('Color Fondo', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => ['#alezux-report-modal-{{ID}} .alezux-modal-content' => 'background-color: {{VALUE}} !important;'],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'modal_border',
                'selector' => '#alezux-report-modal-{{ID}} .alezux-modal-content',
            ]
        );

        $this->add_control(
            'modal_title_heading',
            [
                'label' => esc_html__('Título & Subtítulo', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'modal_title_color',
            [
                'label' => esc_html__('Color Título', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => ['#alezux-report-modal-{{ID}} h3' => 'color: {{VALUE}};'],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'modal_title_typography',
                'label' => 'Tipografía Título',
                'selector' => '#alezux-report-modal-{{ID}} h3',
            ]
        );

        $this->add_control(
            'modal_subtitle_color',
            [
                'label' => esc_html__('Color Subtítulo', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => ['#alezux-report-modal-{{ID}} .alezux-modal-subtitle' => 'color: {{VALUE}};'],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'modal_subtitle_typography',
                'label' => 'Tipografía Subtítulo',
                'selector' => '#alezux-report-modal-{{ID}} .alezux-modal-subtitle',
            ]
        );

        $this->add_control(
            'modal_close_heading',
            [
                'label' => esc_html__('Botón Cerrar', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

         $this->add_control(
            'modal_close_color',
            [
                'label' => esc_html__('Color Icono', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => ['#alezux-report-modal-{{ID}} .alezux-report-close-action' => 'color: {{VALUE}};'],
            ]
        );

        $this->add_control(
            'modal_close_bg_color',
            [
                'label' => esc_html__('Color Fondo', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => ['#alezux-report-modal-{{ID}} .alezux-report-close-action' => 'background-color: {{VALUE}};'],
            ]
        );

        $this->add_control(
            'modal_close_radius',
            [
                'label' => esc_html__('Redondeo', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '#alezux-report-modal-{{ID}} .alezux-report-close-action' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'modal_card_heading',
            [
                'label' => esc_html__('Tarjetas de Pago', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'card_bg_color',
            [
                'label' => esc_html__('Fondo Tarjeta', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => ['#alezux-report-modal-{{ID}} .payment-card' => 'background-color: {{VALUE}} !important;'],
            ]
        );

        $this->add_control(
            'card_border_color',
            [
                'label' => esc_html__('Borde Tarjeta', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => ['#alezux-report-modal-{{ID}} .payment-card' => 'border-color: {{VALUE}} !important;'],
            ]
        );
        
        $this->add_control(
            'card_badge_bg',
            [
                'label' => esc_html__('Fondo Badge (Pago #)', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => ['#alezux-report-modal-{{ID}} .payment-card-badge' => 'background-color: {{VALUE}} !important;'],
            ]
        );

        $this->add_control(
            'card_badge_text',
            [
                'label' => esc_html__('Texto Badge (Pago #)', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => ['#alezux-report-modal-{{ID}} .payment-card-badge' => 'color: {{VALUE}} !important;'],
            ]
        );

        $this->add_control(
            'card_amount_color',
            [
                'label' => esc_html__('Color Monto', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => ['#alezux-report-modal-{{ID}} .payment-amount' => 'color: {{VALUE}} !important;'],
            ]
        );

        $this->add_control(
            'card_date_color',
            [
                'label' => esc_html__('Color Fecha', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => ['#alezux-report-modal-{{ID}} .payment-date' => 'color: {{VALUE}} !important;'],
            ]
        );

        $this->add_control(
            'card_ref_color',
            [
                'label' => esc_html__('Color Referencia', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => ['#alezux-report-modal-{{ID}} .payment-ref' => 'color: {{VALUE}} !important;'],
            ]
        );

        $this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
        
        // Verificar login
        if ( ! is_user_logged_in() ) {
            echo '<div class="alezux-alert alezux-alert-warning">Debes iniciar sesión para ver tu estado financiero.</div>';
            return;
        }

		?>
		<div class="alezux-financial-widget" 
             x-data="alezuxFinancialProfile()" 
             x-init="initData()">
			
            <!-- ACTIVIDAD FINANCIERA UNIFICADA -->
            <div class="alezux-finanzas-app">
                <div class="alezux-financial-section mb-8">
                    <h3 class="alezux-financial-title text-xl font-bold mb-4"><?php echo esc_html( $settings['title_status'] ); ?></h3>
                    
                    <div x-show="loading" class="alezux-loading p-4 text-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Cargando información...
                    </div>

                    <div x-show="!loading && subscriptions.length === 0" class="p-4 bg-gray-50 rounded text-center text-gray-500">
                        No tienes suscripciones activas.
                    </div>

                    <div x-show="!loading && subscriptions.length > 0" class="alezux-table-wrapper">
                        <table class="alezux-finanzas-table w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th scope="col" class="col-plan-name">Plan Académico</th>
                                    <th scope="col" class="col-amount">Monto</th>
                                    <th scope="col" class="col-status">Estado</th>
                                    <th scope="col" class="col-progress">Progreso</th>
                                    <th scope="col" class="col-due">Vencimiento</th>
                                    <th scope="col" class="col-actions">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="sub in subscriptions" :key="'sub-'+sub.id">
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <!-- PLAN NAME -->
                                        <td class="col-plan-name">
                                            <div class="font-bold plan-title" x-text="sub.plan_name"></div>
                                            <div class="text-xs text-gray-500 plan-meta" x-text="sub.plan_quotas + ' CUOTAS'"></div>
                                        </td>
                                        
                                        <!-- AMOUNT -->
                                        <td class="col-amount font-semibold" x-text="sub.formatted_price"></td>
                                        
                                        <!-- STATUS (BADGE) -->
                                        <td class="col-status">
                                            <span class="alezux-status-badge"
                                                  :class="'status-' + sub.status"
                                                  x-text="translateStatus(sub.status)">
                                            </span>
                                        </td>
                                        
                                        <!-- PROGRESS BAR -->
                                        <td class="col-progress">
                                            <div class="progress-Label flex justify-between text-xs mb-1">
                                                <span x-text="sub.progress_text + ' PAGADOS'"></span>
                                                <span x-text="sub.progress_percent + '%'"></span>
                                            </div>
                                            <div class="alezux-progress-bar-bg w-full bg-gray-200 rounded-full h-2">
                                                <div class="alezux-progress-bar-fill bg-green-500 h-2 rounded-full" :style="'width: ' + sub.progress_percent + '%'"></div>
                                            </div>
                                        </td>
                                        
                                        <!-- DUE DATE -->
                                        <td class="col-due">
                                            <div x-show="sub.status === 'active' || sub.status === 'past_due'">
                                                <div class="text-xs text-gray-500">Próxima Cuota:</div>
                                                <div class="font-medium" x-text="sub.next_payment_date_formatted"></div>
                                            </div>
                                            <div x-show="sub.status === 'completed'">
                                                <span class="text-green-600 font-bold text-xs"><i class="fas fa-check"></i> Finalizado</span>
                                            </div>
                                        </td>
                                        
                                        <!-- ACTIONS -->
                                        <td class="col-actions">
                                            <div class="flex flex-col gap-2">
                                                <!-- Pay Button -->
                                                <template x-if="sub.can_pay_manually">
                                                    <button @click="confirmPayment(sub.id)" 
                                                            class="alezux-btn-pay text-white px-3 py-1.5 text-xs rounded hover:opacity-90 transition-opacity whitespace-nowrap w-full">
                                                        <i class="fas fa-dollar-sign mr-1"></i> Pagar Cuota
                                                    </button>
                                                </template>

                                                <!-- Report Button -->
                                                <button @click="openReportModal(sub.id, sub.plan_name)" 
                                                        class="alezux-btn-report text-gray-700 bg-gray-200 px-3 py-1.5 text-xs rounded hover:opacity-90 transition-opacity whitespace-nowrap w-full">
                                                    <i class="fas fa-list-alt mr-1"></i> Ver Pagos
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- MODAL DE PAGO / CONFIRMACIÓN -->
            <div id="alezux-profile-modal-<?php echo $this->get_id(); ?>" 
                 class="alezux-modal-overlay"
                 style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100vh !important; z-index: 2147483647 !important; display: none; align-items: center !important; justify-content: center !important; background-color: rgba(0,0,0,0.5) !important; opacity: 1 !important; visibility: visible !important;">
                
                <div id="alezux-modal-backdrop-<?php echo $this->get_id(); ?>" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0;"></div>

                <div class="alezux-modal-content"
                     style="background-color: #ffffff !important; color: #1f2937 !important; border-radius: 0.5rem !important; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important; max-width: 28rem !important; width: 100% !important; margin: 1rem !important; position: relative !important; padding: 1.5rem !important; z-index: 1000000 !important;">
                    
                    <div style="text-align: center !important;">
                        <div id="alezux-modal-icon-container-<?php echo $this->get_id(); ?>"
                             style="margin-left: auto; margin-right: auto; display: flex; align-items: center; justify-content: center; height: 3rem; width: 3rem; border-radius: 9999px; margin-bottom: 1rem;">
                            <i id="alezux-modal-icon-<?php echo $this->get_id(); ?>" class="fas text-xl"></i>
                        </div>
                        <h3 id="alezux-modal-title-<?php echo $this->get_id(); ?>" style="font-size: 1.125rem; line-height: 1.75rem; font-weight: 500; color: #111827 !important; margin-top: 0;"></h3>
                        <div style="margin-top: 0.5rem;">
                            <p id="alezux-modal-message-<?php echo $this->get_id(); ?>" style="font-size: 0.875rem; line-height: 1.25rem; color: #6b7280 !important;"></p>
                        </div>
                    </div>

                    <div style="margin-top: 1.25rem; display: grid; gap: 0.75rem; grid-template-columns: repeat(1, minmax(0, 1fr));">
                        <button type="button" 
                                id="alezux-modal-confirm-btn-<?php echo $this->get_id(); ?>"
                                style="width: 100%; display: inline-flex; justify-content: center; border-radius: 0.375rem; border: 1px solid transparent; padding: 0.5rem 1rem; font-size: 1rem; line-height: 1.5rem; font-weight: 500; color: #ffffff !important; cursor: pointer; transition: background-color 0.15s ease-in-out;">
                            <span id="alezux-modal-confirm-text-<?php echo $this->get_id(); ?>"></span>
                        </button>
                        
                        <button type="button" 
                                id="alezux-modal-cancel-btn-<?php echo $this->get_id(); ?>"
                                style="margin-top: 0.75rem; width: 100%; display: inline-flex; justify-content: center; border-radius: 0.375rem; border: 1px solid #d1d5db; padding: 0.5rem 1rem; font-size: 1rem; line-height: 1.5rem; font-weight: 500; color: #374151 !important; background-color: #ffffff !important; cursor: pointer; transition: background-color 0.15s ease-in-out;">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>

            <!-- MODAL DE REPORTE DE PAGOS -->
            <div id="alezux-report-modal-<?php echo $this->get_id(); ?>" 
                 class="alezux-modal-overlay"
                 style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100vh !important; z-index: 2147483647 !important; display: none; align-items: center !important; justify-content: center !important; background-color: rgba(0,0,0,0.5) !important; opacity: 1 !important; visibility: visible !important;">
                
                <div id="alezux-report-backdrop-<?php echo $this->get_id(); ?>" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0;"></div>

                <div class="alezux-modal-content"
                     style="background-color: #ffffff; color: #1f2937; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); max-width: 40rem !important; width: 100% !important; margin: 1rem !important; position: relative !important; padding: 2rem !important; z-index: 1000000 !important; max-height: 80vh; overflow-y: auto;">
                    
                    <button id="alezux-report-close-btn-<?php echo $this->get_id(); ?>" 
                            type="button"
                            class="alezux-report-close-action" 
                            style="position: absolute; top: 1.5rem; right: 1.5rem; width: 2.5rem; height: 2.5rem; display: flex; align-items: center; justify-content: center; cursor: pointer; border: none; font-size: 1.25rem; z-index: 50; color: inherit;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" style="width: 1.25rem; height: 1.25rem;">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <div class="mb-8">
                        <h3 class="font-bold" style="margin-bottom: 2px !important; line-height: 1.2;">Historial de pago</h3>
                        <div id="alezux-report-subtitle-<?php echo $this->get_id(); ?>" class="alezux-modal-subtitle" style="line-height: 1.4;"></div>
                    </div>

                    <div id="alezux-report-content-<?php echo $this->get_id(); ?>" class="space-y-4">
                        <!-- Content injected via JS -->
                    </div>
                    
                    <div id="alezux-report-empty-<?php echo $this->get_id(); ?>" style="display:none;" class="text-center text-gray-500 py-4">
                        No hay pagos registrados para este plan.
                    </div>

                </div>
            </div>

		</div>

        <script>
        function alezuxFinancialProfile() {
            return {
                loading: true,
                subscriptions: [],
                transactions: [],
                
                // Modal State
                showModal: false,
                modalTitle: '',
                modalMessage: '',
                modalType: 'info', // info, success, error
                modalConfirmText: 'Aceptar',
                showCancelButton: false,
                onConfirmAction: null,

                onConfirmAction: null,
                isPreview: false,

                initData() {
                    // Check for Preview Mode
                    this.isPreview = <?php echo ( \Elementor\Plugin::$instance->editor->is_edit_mode() && 'yes' === $settings['preview_modal_editor'] ) ? 'true' : 'false'; ?>;

                    if (this.isPreview) {
                        this.loading = false;
                        // Fake Data for Preview
                        this.subscriptions = [
                            { id: 1, plan_name: 'Plan Demo 1', formatted_price: '$100.00', status: 'active', progress_text: '1/3', progress_percent: 33, next_payment_date_formatted: '15 Feb, 2026' }
                        ];
                        this.transactions = [
                             { subscription_id: 1, status: 'succeeded', status_label: 'Exitoso', formatted_amount: '$100.00', date_formatted: 'Feb 01, 2026 10:00am', transaction_ref: 'pi_demo_123', date: '2026-02-01 10:00:00' },
                             { subscription_id: 1, status: 'succeeded', status_label: 'Exitoso', formatted_amount: '$100.00', date_formatted: 'Jan 01, 2026 10:00am', transaction_ref: 'pi_demo_456', date: '2026-01-01 10:00:00' }
                        ];
                        // Auto-open modal in preview
                        setTimeout(() => this.openReportModal(1, 'Plan Demo 1 (Previsualización)'), 500);
                        return;
                    }
                    // Move Modals to Body
                    const modalId = 'alezux-profile-modal-<?php echo $this->get_id(); ?>';
                    const reportModalId = 'alezux-report-modal-<?php echo $this->get_id(); ?>';
                    
                    [modalId, reportModalId].forEach(id => {
                        const el = document.getElementById(id);
                        if (el && el.parentElement !== document.body) {
                            try { document.body.appendChild(el); } catch(e) {}
                        }
                    });

                    // Setup Report Modal Listeners
                    const closeBtn = document.getElementById('alezux-report-close-btn-<?php echo $this->get_id(); ?>');
                    if (closeBtn) {
                        closeBtn.onclick = (e) => {
                            if(e) { e.preventDefault(); e.stopPropagation(); }
                            this.closeReportModal();
                        };
                    }
                    
                    const backdrop = document.getElementById('alezux-report-backdrop-<?php echo $this->get_id(); ?>');
                    if (backdrop) {
                        backdrop.onclick = (e) => {
                             if(e) { e.preventDefault(); e.stopPropagation(); }
                             this.closeReportModal();
                        };
                    }

                    const formData = new FormData();
                    formData.append('action', 'alezux_get_my_financial_data');
                    formData.append('nonce', '<?php echo wp_create_nonce( "alezux_finanzas_nonce" ); ?>');

                    fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.loading = false;
                        if (data.success) {
                            this.subscriptions = data.data.subscriptions;
                            this.transactions = data.data.transactions;
                        } else {
                            console.error('Error fetching financial data:', data);
                        }
                    })
                    .catch(error => {
                        this.loading = false;
                        console.error('Fetch error:', error);
                    });
                },

                translateStatus(status) {
                    const statuses = {
                        'active': 'Activo',
                        'past_due': 'Pago Pendiente',
                        'unpaid': 'Impago',
                        'canceled': 'Cancelado',
                        'incomplete': 'Incompleto',
                        'incomplete_expired': 'Expirado',
                        'trialing': 'Prueba',
                        'completed': 'Completado'
                    };
                    return statuses[status] || status;
                },
                
                openReportModal(subscriptionId, planName) {
                    const reportModal = document.getElementById('alezux-report-modal-<?php echo $this->get_id(); ?>');
                    if (!reportModal) {
                        console.error('Modal element not found');
                        return;
                    }
                    // Use querySelector for safer scoping if IDs are duplicated or lost
                    const contentDiv = reportModal.querySelector('.space-y-4'); // Targets the content div
                    const emptyDiv = document.getElementById('alezux-report-empty-<?php echo $this->get_id(); ?>');
                    
                    // Specific fix for Subtitle Element
                    let subtitle = document.getElementById('alezux-report-subtitle-<?php echo $this->get_id(); ?>');
                    if (!subtitle) {
                         // Fallback using class inside specific modal
                         subtitle = reportModal.querySelector('.alezux-modal-subtitle');
                    }
                    
                    if(subtitle) subtitle.innerText = planName;
                    if(contentDiv) contentDiv.innerHTML = ''; // Clear previous

                    // Filter transactions for this subscription logic
                    // Note: The backend 'alezux_finanzas_transactions' table usually has subscription_id or plan_id
                    // We need to filter based on what we have. Let's assume 'subscription_id' is available in transaction object.
                    // If not (e.g. older transactions), we might need to rely on plan matching or similar.
                    // For now, let's filter by subscription_id if present, or plan_id fallback.
                    
                    const filteredTrans = this.transactions.filter(t => 
                        parseInt(t.subscription_id) === parseInt(subscriptionId) || 
                        ( !t.subscription_id && parseInt(t.plan_id) === parseInt(this.subscriptions.find(s=>s.id==subscriptionId).plan_id) )
                    );

                    if (filteredTrans.length === 0) {
                        emptyDiv.style.display = 'block';
                    } else {
                        emptyDiv.style.display = 'none';
                        // Build HTML List (Card Style)
                        let html = '<div class="space-y-3">';
                        
                        // Sort by date ASCENDING (Oldest First)
                        filteredTrans.sort((a,b) => new Date(a.date) - new Date(b.date));

                        filteredTrans.forEach((t, index) => {
                            // index 0 is the oldest now.
                            const paymentNumber = index + 1;
                            
                            // Status Styles
                            let statusTextClass = 'text-green-400';
                            if(t.status === 'failed') statusTextClass = 'text-red-400';
                            if(t.status === 'pending') statusTextClass = 'text-yellow-400';

                            html += `
                            <div class="payment-card" style="background-color: #0f0f1a; border-radius: 12px; padding: 16px; display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; border: 1px solid #2d2d42;">
                                <!-- Left Side -->
                                <div>
                                    <div class="payment-card-badge" style="background-color: #2d1b4e; color: #a5b4fc; display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem; margin-bottom: 8px;">
                                        Estado pago #${paymentNumber}
                                    </div>
                                    <h4 style="font-size: 1.5rem; font-weight: bold; margin: 0; line-height: 1.2; color: #fff;" class="${statusTextClass}">
                                        ${t.status_label || t.status}
                                    </h4>
                                    <div class="payment-ref" style="font-size: 0.75rem; color: #6b7280; margin-top: 4px;">
                                        Ref: ${t.transaction_ref || 'N/A'}
                                    </div>
                                </div>

                                <!-- Right Side -->
                                <div style="text-align: right;">
                                    <div class="payment-date" style="font-size: 0.875rem; color: #e5e7eb; margin-bottom: 4px;">
                                        ${t.date_formatted}
                                    </div>
                                    <div class="payment-amount" style="font-size: 1.5rem; font-weight: bold; color: #fff;">
                                        ${t.formatted_amount}
                                    </div>
                                </div>
                            </div>`;
                        });
                        html += '</div>';
                        contentDiv.innerHTML = html;
                    }

                    // Show Modal
                    reportModal.style.display = 'flex';
                    // Force refresh
                    reportModal.offsetHeight;
                    
                    // Fallback Visibility
                    setTimeout(() => {
                        if(getComputedStyle(reportModal).display === 'none') {
                             reportModal.style.setProperty('display', 'flex', 'important');
                        }
                    }, 50);
                },

                closeReportModal() {
                    const reportModal = document.getElementById('alezux-report-modal-<?php echo $this->get_id(); ?>');
                    if (reportModal) {
                        reportModal.style.setProperty('display', 'none', 'important');
                    }
                },

                // Modal Logic - Pure Vanilla JS
                openModal(title, message, type = 'info', confirmText = 'Aceptar', showCancel = false, onConfirm = null) {
                    const modalId = 'alezux-profile-modal-<?php echo $this->get_id(); ?>';
                    const modalEl = document.getElementById(modalId);
                    
                    if (!modalEl) return;

                    // Update Content
                    document.getElementById('alezux-modal-title-<?php echo $this->get_id(); ?>').innerText = title;
                    document.getElementById('alezux-modal-message-<?php echo $this->get_id(); ?>').innerText = message;
                    document.getElementById('alezux-modal-confirm-text-<?php echo $this->get_id(); ?>').innerText = confirmText;

                    // Update Style based on Type
                    const iconContainer = document.getElementById('alezux-modal-icon-container-<?php echo $this->get_id(); ?>');
                    const icon = document.getElementById('alezux-modal-icon-<?php echo $this->get_id(); ?>');
                    const confirmBtn = document.getElementById('alezux-modal-confirm-btn-<?php echo $this->get_id(); ?>');

                    // Reset classes
                    icon.className = 'fas text-xl';

                    if (type === 'error') {
                        iconContainer.style.backgroundColor = '#fee2e2';
                        icon.classList.add('fa-exclamation-triangle');
                        icon.style.color = '#dc2626';
                        confirmBtn.style.backgroundColor = '#dc2626';
                    } else if (type === 'success') {
                        iconContainer.style.backgroundColor = '#dcfce7';
                        icon.classList.add('fa-check');
                        icon.style.color = '#16a34a';
                        confirmBtn.style.backgroundColor = '#16a34a';
                    } else {
                        iconContainer.style.backgroundColor = '#dbeafe';
                        icon.classList.add('fa-info-circle');
                        icon.style.color = '#2563eb';
                        confirmBtn.style.backgroundColor = '#2563eb';
                    }

                    // Show/Hide Cancel Button
                    const cancelBtn = document.getElementById('alezux-modal-cancel-btn-<?php echo $this->get_id(); ?>');
                    cancelBtn.style.setProperty('display', showCancel ? 'inline-flex' : 'none', 'important');

                    // Assign Click Handlers
                    this._onConfirm = onConfirm;
                    
                    // Simple confirm handler
                    confirmBtn.onclick = () => {
                        if (this._onConfirm) {
                            this._onConfirm();
                        } else {
                            this.closeModal();
                        }
                    };

                    // Cancel Handlers
                    cancelBtn.onclick = () => this.closeModal();
                    document.getElementById('alezux-modal-backdrop-<?php echo $this->get_id(); ?>').onclick = () => {
                        if (showCancel) this.closeModal();
                    };

                    // Show Modal
                    modalEl.style.display = 'flex';
                    // Force repaint just in case
                    modalEl.offsetHeight; 
                    
                    // Double check
                    setTimeout(() => {
                        if(getComputedStyle(modalEl).display === 'none') {
                             modalEl.style.setProperty('display', 'flex', 'important');
                        }
                    }, 50);
                },

                closeModal() {
                    const modalId = 'alezux-profile-modal-<?php echo $this->get_id(); ?>';
                    const modalEl = document.getElementById(modalId);
                    if (modalEl) {
                        modalEl.style.setProperty('display', 'none', 'important');
                    }
                    this._onConfirm = null;
                },

                confirmPayment(subscriptionId) {
                    this.openModal(
                        'Confirmar Pago',
                        '¿Deseas generar el enlace de pago para la próxima cuota pendiente?',
                        'info',
                        'Sí, Pagar',
                        true,
                        () => this.processPayment(subscriptionId)
                    );
                },

                processPayment(subscriptionId) {
                    this.loading = true;
                    this.closeModal(); // Close confirmation modal
                    
                    const formData = new FormData();
                    formData.append('action', 'alezux_create_installment_checkout');
                    formData.append('subscription_id', subscriptionId);
                    formData.append('nonce', '<?php echo wp_create_nonce( "alezux_finanzas_nonce" ); ?>');

                    fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        // this.loading = false; // Don't stop loading if redirecting
                        if (data.success) {
                            window.location.href = data.data.url;
                        } else {
                            this.loading = false;
                            this.openModal('Error', data.data || 'Ocurrió un error desconocido.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.loading = false;
                        this.openModal('Error de Conexión', 'No se pudo conectar con el servidor.', 'error');
                    });
                }
            }
        }
        </script>
		<?php
	}
}
