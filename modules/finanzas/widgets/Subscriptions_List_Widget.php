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

class Subscriptions_List_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux_subs_list';
	}

	public function get_title() {
		return esc_html__( 'Listado Suscripciones (Alezux)', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-person';
	}

	public function get_categories() {
		return [ 'alezux-finanzas' ];
	}

	public function get_script_depends() {
		return [ 'alezux-subs-list-js' ];
	}

    public function get_style_depends() {
		return [ 'alezux-finanzas-tables-css' ]; 
	}

	protected function register_controls() {

		// Content Section
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Configuración', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'limit',
			[
				'label'   => esc_html__( 'Límite de registros', 'alezux-members' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 100,
				'step'    => 1,
				'default' => 10,
			]
		);

        $this->add_control(
			'table_title',
			[
				'label' => esc_html__( 'Título de la Tabla', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Suscripciones', 'alezux-members' ),
                'placeholder' => esc_html__( 'Escribe un título', 'alezux-members' ),
			]
		);

        $this->add_control(
			'table_description',
			[
				'label' => esc_html__( 'Descripción', 'alezux-members' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Gestiona las suscripciones de los estudiantes.', 'alezux-members' ),
                'placeholder' => esc_html__( 'Escribe una descripción', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

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


        // 2. ESTUDIANTE
        $this->start_controls_section(
            'style_section_student',
            [
                'label' => esc_html__('Estudiante (Avatar, Nombre, Email)', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'student_avatar_heading',
            [
                'label' => esc_html__('Avatar', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_responsive_control(
            'student_avatar_size',
            [
                'label' => esc_html__('Tamaño Avatar', 'alezux-members'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range' => [
                    'px' => [
                        'min' => 20,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .alezux-student-avatar' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

         $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'student_avatar_border',
                'label' => esc_html__('Borde Avatar', 'alezux-members'),
                'selector' => '{{WRAPPER}} .alezux-student-avatar',
            ]
        );

        $this->add_control(
            'student_avatar_radius',
            [
                'label' => esc_html__('Radio Avatar', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .alezux-student-avatar' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'student_name_heading',
            [
                'label' => esc_html__('Nombre', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'student_name_color',
            [
                'label' => esc_html__('Color Nombre', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .student-name' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'student_name_typography',
                'selector' => '{{WRAPPER}} .student-name',
            ]
        );

        $this->add_control(
            'student_email_heading',
            [
                'label' => esc_html__('Email', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'student_email_color',
            [
                'label' => esc_html__('Color Email', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .student-email' => 'color: {{VALUE}}; word-break: break-all; white-space: normal; display: block; max-width: 100%;',
                    '{{WRAPPER}} .alezux-student-text' => 'min-width: 0; flex: 1;',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'student_email_typography',
                'selector' => '{{WRAPPER}} .student-email',
            ]
        );

        $this->end_controls_section();


        // 3. MONTO
        $this->start_controls_section(
            'style_section_amount',
            [
                'label' => esc_html__('Monto', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'amount_text_color',
            [
                'label' => esc_html__('Color Texto', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .col-amount strong' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .col-amount' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'amount_typography',
                'selector' => '{{WRAPPER}} .col-amount strong, {{WRAPPER}} .col-amount',
            ]
        );

        $this->end_controls_section();

        // 4. ESTADO
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

        $this->add_control('heading_badge_canceled', ['type' => Controls_Manager::HEADING, 'label' => 'Cancelado', 'separator' => 'before']);
        $this->add_control('badge_canceled_text', ['label' => 'Texto', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-canceled' => 'color: {{VALUE}};']]);
        $this->add_control('badge_canceled_bg', ['label' => 'Fondo', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-canceled' => 'background-color: {{VALUE}};']]);

        $this->end_controls_section();


        // 5. PROGRESO
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

        // 6. VENCIMIENTO
        $this->start_controls_section(
            'style_section_duedate',
            [
                'label' => esc_html__('Vencimiento', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'duedate_color',
            [
                'label' => esc_html__('Color Texto', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .col-next-payment' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .date-val' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'duedate_typography',
                'selector' => '{{WRAPPER}} .col-next-payment, {{WRAPPER}} .date-val',
            ]
        );
         $this->add_control(
            'duedate_meta_color',
            [
                'label' => esc_html__('Color Meta Info', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .date-meta' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();


        // 7. ACCIONES (BOTONES)
        $this->start_controls_section(
            'style_section_actions',
            [
                'label' => esc_html__('Acciones (Botones)', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        // Usamos el Button Widget nativo de Elementor si es posible, si no, manual
        // Manual simulation of Elementor button controls for consistency
        $this->start_controls_tabs( 'tabs_button_style' );

        $this->start_controls_tab(
            'tab_button_normal',
            [
                'label' => esc_html__( 'Normal', 'alezux-members' ),
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .alezux-btn-manual-pay',
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => esc_html__( 'Color Texto', 'alezux-members' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-btn-manual-pay' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'button_background',
                'label' => esc_html__( 'Fondo', 'alezux-members' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .alezux-btn-manual-pay',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .alezux-btn-manual-pay',
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => esc_html__( 'Radio Borde', 'alezux-members' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .alezux-btn-manual-pay' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

         $this->add_control(
            'button_padding',
            [
                'label' => esc_html__( 'Relleno', 'alezux-members' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .alezux-btn-manual-pay' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_button_hover',
            [
                'label' => esc_html__( 'Hover', 'alezux-members' ),
            ]
        );

        $this->add_control(
            'button_hover_color',
            [
                'label' => esc_html__( 'Color Texto', 'alezux-members' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-btn-manual-pay:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'button_background_hover',
                'label' => esc_html__( 'Fondo', 'alezux-members' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .alezux-btn-manual-pay:hover',
            ]
        );

        $this->add_control(
            'button_hover_border_color',
            [
                'label' => esc_html__( 'Color Borde', 'alezux-members' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-btn-manual-pay:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

         $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_box_shadow',
                'selector' => '{{WRAPPER}} .alezux-btn-manual-pay',
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();

        // ---------------------------------------------------------
        // SECTION: Popup Styles & Preview
        // ---------------------------------------------------------
        $this->start_controls_section(
            'section_popup_style',
            [
                'label' => esc_html__('Estilos Popup de Pago', 'alezux-members'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'preview_modal',
            [
                'label' => esc_html__('Ver Popup en Editor', 'alezux-members'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Sí', 'alezux-members'),
                'label_off' => esc_html__('No', 'alezux-members'),
                'return_value' => 'yes',
                'default' => '',
                'description' => esc_html__('Activa para previsualizar y editar el estilo del popup.', 'alezux-members'),
            ]
        );

        $this->add_control(
            'popup_overlay_heading',
            [
                'label' => esc_html__('Fondo Pantalla (Overlay)', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'popup_overlay_bg',
            [
                'label' => esc_html__('Color Fondo Oscuro', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-modal' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'popup_box_heading',
            [
                'label' => esc_html__('Caja del Modal', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'popup_box_bg',
            [
                'label' => esc_html__('Color Fondo Caja', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-modal-content' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'popup_box_border',
                'selector' => '{{WRAPPER}} .alezux-modal-content',
            ]
        );

        $this->add_control(
            'popup_box_radius',
            [
                'label' => esc_html__('Radio de Borde', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .alezux-modal-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

         $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'popup_box_shadow',
                'selector' => '{{WRAPPER}} .alezux-modal-content',
            ]
        );

        $this->add_control(
            'popup_box_margin',
            [
                'label' => esc_html__('Margen', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .alezux-modal-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'popup_box_padding',
            [
                'label' => esc_html__('Relleno', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .alezux-modal-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'popup_content_heading',
            [
                'label' => esc_html__('Textos y Inputs', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'popup_title_color',
            [
                'label' => esc_html__('Color Título', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-modal h3' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'popup_title_typo',
                'selector' => '{{WRAPPER}} .alezux-modal h3',
            ]
        );

        $this->add_control(
            'popup_labels_color',
            [
                'label' => esc_html__('Color Etiquetas', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-modal label' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .alezux-modal p' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'popup_input_bg',
            [
                'label' => esc_html__('Fondo Inputs', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-form-group input' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .alezux-form-group textarea' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'popup_input_color',
            [
                'label' => esc_html__('Color Texto Inputs', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-form-group input' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .alezux-form-group textarea' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'popup_input_border',
                'selector' => '{{WRAPPER}} .alezux-form-group input, {{WRAPPER}} .alezux-form-group textarea',
            ]
        );

        $this->add_control(
            'popup_btn_heading',
            [
                'label' => esc_html__('Botón Confirmar', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'popup_btn_bg',
            [
                'label' => esc_html__('Fondo Botón', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-modal .alezux-btn-primary' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'popup_btn_bg_hover',
            [
                'label' => esc_html__('Fondo Botón (Hover)', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-modal .alezux-btn-primary:hover' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'popup_btn_text_color',
            [
                'label' => esc_html__('Color Texto Botón', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-modal .alezux-btn-primary' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();    

	}

	protected function render() {
		$settings = $this->get_settings_for_display();
        ?>
        <div class="alezux-finanzas-app alezux-subs-app">
            
            <div class="alezux-table-header">
                <div class="alezux-header-left">
                    <h3 class="alezux-table-title"><?php echo esc_html($settings['table_title']); ?></h3>
                    <?php if ( ! empty( $settings['table_description'] ) ) : ?>
                        <p class="alezux-table-desc"><?php echo esc_html($settings['table_description']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="alezux-header-right">
                    <div class="alezux-filters-inline">
                         <!-- Search -->
                         <div class="alezux-filter-item search-item">
                            <div class="alezux-search-wrapper">
                                <span class="dashicons dashicons-search"></span>
                                <input type="text" id="alezux-subs-search" class="alezux-table-search-input" placeholder="<?php esc_attr_e('Buscar por estudiante o plan...', 'alezux-members'); ?>">
                            </div>
                         </div>
                    </div>
                </div>
            </div>

    <div class="alezux-loading" style="display:none;">
        <i class="eicon-loading eicon-animation-spin"></i> <?php esc_html_e('Cargando suscripciones...', 'alezux-members'); ?>
    </div>

            <div class="alezux-table-wrapper">
                <table class="alezux-finanzas-table"> 
                    <thead>
                        <tr>

                            <th class="col-student"><?php esc_html_e('ESTUDIANTE', 'alezux-members'); ?></th>
                            <th class="col-plan"><?php esc_html_e('PLAN ACADÉMICO', 'alezux-members'); ?></th>
                            <th class="col-amount"><?php esc_html_e('MONTO', 'alezux-members'); ?></th>
                            <th class="col-status"><?php esc_html_e('ESTADO', 'alezux-members'); ?></th>
                            <th class="col-progress"><?php esc_html_e('PROGRESO', 'alezux-members'); ?></th>
                            <th class="col-next-payment"><?php esc_html_e('VENCIMIENTO', 'alezux-members'); ?></th>
                            <th class="col-actions"><?php esc_html_e('ACCIONES', 'alezux-members'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- AJAX Content -->
                        <?php
                        if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                            $dummy_data = [
                                [
                                    'id' => 101,
                                    'student' => 'Estudiante Demo',
                                    'email' => 'demo@ejemplo.com',
                                    'avatar' => get_avatar_url(0),
                                    'plan' => 'Suscripción Anual',
                                    'amount' => '$120.00',
                                    'status' => 'active',
                                    'status_label' => 'Activo',
                                    'progress' => 45,
                                    'due' => date('d/m/Y', strtotime('+5 days')),
                                    'due_meta' => 'En 5 días'
                                ],
                                [
                                    'id' => 102,
                                    'student' => 'Juan Pérez',
                                    'email' => 'juan.perez@email.com',
                                    'avatar' => get_avatar_url(0),
                                    'plan' => 'Curso Intensivo',
                                    'amount' => '$299.00',
                                    'status' => 'completed',
                                    'status_label' => 'Completado',
                                    'progress' => 100,
                                    'due' => date('d/m/Y', strtotime('-1 days')),
                                    'due_meta' => 'Ayer'
                                ],
                                [
                                    'id' => 103,
                                    'student' => 'Maria Gonzalez',
                                    'email' => 'maria.g@email.com',
                                    'avatar' => get_avatar_url(0),
                                    'plan' => 'Mensualidad Básica',
                                    'amount' => '$45.00',
                                    'status' => 'past_due',
                                    'status_label' => 'Vencido',
                                    'progress' => 12,
                                    'due' => date('d/m/Y', strtotime('-1 week')),
                                    'due_meta' => 'Hace 1 semana'
                                ],
                            ];

                            foreach ($dummy_data as $item) {
                                ?>
                                <tr>

                                    <td class="col-student">
                                        <div class="alezux-student-info">
                                            <img src="<?php echo esc_url($item['avatar']); ?>" class="alezux-student-avatar" alt="">
                                            <div class="alezux-student-text">

                                                <div class="student-name"><?php echo esc_html($item['student']); ?></div>
                                                <div class="student-email"><?php echo esc_html($item['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="col-plan"><?php echo esc_html($item['plan']); ?></td>
                                    <td class="col-amount"><strong><?php echo esc_html($item['amount']); ?></strong></td>
                                    <td class="col-status">
                                        <span class="alezux-status-badge status-<?php echo esc_attr($item['status']); ?>"><?php echo esc_html($item['status_label']); ?></span>
                                    </td>
                                    <td class="col-progress">
                                        <div class="progress-Label"><span><?php echo esc_html($item['progress']); ?>%</span></div>
                                        <div class="alezux-progress-bar-bg">
                                            <div class="alezux-progress-bar-fill" style="width: <?php echo esc_attr($item['progress']); ?>%;"></div>
                                        </div>
                                    </td>
                                    <td class="col-next-payment">
                                        <div class="date-val"><?php echo esc_html($item['due']); ?></div>
                                        <div class="date-meta"><?php echo esc_html($item['due_meta']); ?></div>
                                    </td>
                                    <td class="col-actions">
                                        <button class="alezux-btn-manual-pay"><?php esc_html_e('Pagar', 'alezux-members'); ?></button>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Footer: Pagination + Rows Filter -->
            <div class="alezux-table-footer">
                <div class="alezux-pagination"></div>
                
                <div class="alezux-footer-filter">
                    <label>Filas:</label>
                    <select id="alezux-limit-select">
                        <option value="10">10</option>
                        <option value="20" selected>20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <!-- Modal de Pago Manual -->
            <div id="alezux-manual-pay-modal-<?php echo $this->get_id(); ?>" class="alezux-modal" style="<?php echo ( \Elementor\Plugin::$instance->editor->is_edit_mode() && 'yes' === $settings['preview_modal'] ) ? 'display:flex !important;' : 'display:none;'; ?>">
                <div class="alezux-modal-content">
                    <span class="alezux-close-modal">&times;</span>
                    <h3><?php esc_html_e('Registrar Pago Manual', 'alezux-members'); ?></h3>
                    <p><?php esc_html_e('Suscripción ID:', 'alezux-members'); ?> <span id="modal-sub-id"></span></p>
                    
                    <div class="alezux-form-group">
                        <label><?php esc_html_e('Monto ($)', 'alezux-members'); ?></label>
                        <input type="number" id="manual-pay-amount" step="0.01" placeholder="Ej: 50.00">
                    </div>

                    <div class="alezux-form-group">
                        <label><?php esc_html_e('Motivo / Nota', 'alezux-members'); ?></label>
                        <textarea id="manual-pay-note" placeholder="Ej: Transferencia Bancaria #1234"></textarea>
                    </div>

                    <button id="btn-confirm-manual-pay" class="alezux-btn-primary"><?php esc_html_e('Registrar Pago', 'alezux-members'); ?></button>
                </div>
            </div>

        </div>
        <?php
	}
}
