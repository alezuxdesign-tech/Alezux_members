<?php
namespace Alezux_Members\Modules\Proyectos_Agencia\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Alezux_Members\Modules\Proyectos_Agencia\Includes\Projects_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Project_Client_View_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux_project_client_view';
	}

	public function get_title() {
		return 'Vista Cliente (Mi Proyecto)';
	}

	public function get_icon() {
		return 'eicon-time-line';
	}

	public function get_categories() {
		return [ 'alezux_members' ];
	}
    
    public function get_style_depends() {
		return [ 'alezux-client-view-css' ];
	}

	public function get_script_depends() {
		return [ 'alezux-client-app-js' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => 'Configuración',
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'no_project_message',
			[
				'label' => 'Mensaje sin proyecto',
				'type' => Controls_Manager::TEXTAREA,
				'default' => 'No tienes un proyecto activo en este momento.',
			]
		);

		$this->add_control(
			'preview_step',
			[
				'label' => 'Previsualizar Paso (Solo Editor)',
				'type' => Controls_Manager::SELECT,
				'options' => [
					'briefing' => '1. Briefing Inicial',
					'identity' => '2. Propuesta de Identidad',
					'web_design' => '3. Diseño UI/UX',
					'development' => '4. Desarrollo & Montaje',
					'delivery' => '5. Entrega Final',
				],
				'default' => 'briefing',
				'description' => 'Elige qué paso quieres previsualizar dentro del editor de Elementor para diseñar sus contenidos.',
			]
		);

		$this->end_controls_section();

		// ==========================
		// STYLE TAB: Contenedor
		// ==========================
		$this->start_controls_section(
			'style_container_section',
			[
				'label' => 'Contenedor',
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'container_background',
				'label' => 'Fondo General',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .alezux-client-project',
			]
		);

		$this->add_control(
			'force_bg_general',
			[
				'label' => 'Color de Fondo (Forzado)',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-client-project' => 'background-color: {{VALUE}} !important; background-image: none !important;',
				],
				'description' => 'Usa esto si el Fondo General no se aplica correctamente debido al tema.',
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => 'Relleno (Padding)',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-client-project' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_border_radius',
			[
				'label' => 'Radio de Borde',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-client-project' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'container_box_shadow',
				'label' => 'Sombra de Caja',
				'selector' => '{{WRAPPER}} .alezux-client-project',
			]
		);

		$this->add_control(
			'content_area_heading',
			[
				'label' => 'Caja de Contenido (Tarjeta Interna)',
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'content_background',
				'label' => 'Fondo del Contenido',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .project-content-area, {{WRAPPER}} .step-content',
			]
		);

		$this->add_control(
			'force_bg_content',
			[
				'label' => 'Color Fondo Contenido (Forzado)',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .project-content-area' => 'background-color: {{VALUE}} !important; background-image: none !important;',
					'{{WRAPPER}} .step-content' => 'background-color: {{VALUE}} !important; background-image: none !important;',
				],
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label' => 'Relleno del Contenido (Padding)',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .project-content-area, {{WRAPPER}} .step-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_border_radius',
			[
				'label' => 'Radio de Borde Contenido',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .project-content-area, {{WRAPPER}} .step-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================
		// STYLE TAB: Tipografía y Textos
		// ==========================
		$this->start_controls_section(
			'style_typography_section',
			[
				'label' => 'Tipografía y Textos',
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_title_style',
			[
				'label' => 'Títulos Principales (H3)',
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => 'Color de Título',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .step-content h3' => 'color: {{VALUE}};',
					'{{WRAPPER}} .delivery-hero h2' => 'color: {{VALUE}};',
					'{{WRAPPER}} h4' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .step-content h3, {{WRAPPER}} .delivery-hero h2, {{WRAPPER}} h4',
			]
		);

		$this->add_control(
			'heading_text_style',
			[
				'label' => 'Textos Generales (P)',
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => 'Color de Texto',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} p' => 'color: {{VALUE}};',
					'{{WRAPPER}} .delivery-folder li' => 'color: {{VALUE}};',
					'{{WRAPPER}} .credentials-box p' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'text_typography',
				'selector' => '{{WRAPPER}} p, {{WRAPPER}} .delivery-folder li, {{WRAPPER}} .credentials-box p',
			]
		);

		$this->add_control(
			'heading_label_style',
			[
				'label' => 'Etiquetas (Labels)',
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => 'Color de Etiqueta',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} label' => 'color: {{VALUE}};',
					'{{WRAPPER}} .step-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'label_typography',
				'selector' => '{{WRAPPER}} label',
			]
		);

		$this->end_controls_section();

		// ==========================
		// STYLE TAB: Línea de Tiempo (Pasos)
		// ==========================
		$this->start_controls_section(
			'style_timeline_section',
			[
				'label' => 'Línea de Tiempo',
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);
		
		$this->add_control(
			'timeline_line_color',
			[
				'label' => 'Color de Línea Conectora',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .step-line' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_timeline_style' );

		// Inactivo
		$this->start_controls_tab(
			'tab_timeline_inactive',
			[
				'label' => 'Inactivo',
			]
		);

		$this->add_control(
			'step_inactive_bg_color',
			[
				'label' => 'Color de Fondo',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .timeline-step:not(.active):not(.completed) .step-circle' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'step_inactive_text_color',
			[
				'label' => 'Color de Número/Icono',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .timeline-step:not(.active):not(.completed) .step-circle' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'step_inactive_label_color',
			[
				'label' => 'Color de Etiqueta',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .timeline-step:not(.active):not(.completed) .step-label' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		// Activo
		$this->start_controls_tab(
			'tab_timeline_active',
			[
				'label' => 'Activo',
			]
		);

		$this->add_control(
			'step_active_bg_color',
			[
				'label' => 'Color de Fondo',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .timeline-step.active .step-circle' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'step_active_text_color',
			[
				'label' => 'Color de Número',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .timeline-step.active .step-circle' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'step_active_label_color',
			[
				'label' => 'Color de Etiqueta',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .timeline-step.active .step-label' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		// Completado
		$this->start_controls_tab(
			'tab_timeline_completed',
			[
				'label' => 'Completado',
			]
		);

		$this->add_control(
			'step_completed_bg_color',
			[
				'label' => 'Color de Fondo',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .timeline-step.completed .step-circle' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'step_completed_icon_color',
			[
				'label' => 'Color de Icono',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .timeline-step.completed .step-circle' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'step_completed_label_color',
			[
				'label' => 'Color de Etiqueta',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .timeline-step.completed .step-label' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// ==========================
		// STYLE TAB: Formularios (Inputs)
		// ==========================
		$this->start_controls_section(
			'style_form_section',
			[
				'label' => 'Campos de Entrada (Inputs)',
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'input_bg_color',
			[
				'label' => 'Color de Fondo',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-input' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_text_color',
			[
				'label' => 'Color de Texto',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-input' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'input_border',
				'label' => 'Borde',
				'selector' => '{{WRAPPER}} .alezux-input',
			]
		);

		$this->add_responsive_control(
			'input_border_radius',
			[
				'label' => 'Radio de Borde',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_responsive_control(
			'input_padding',
			[
				'label' => 'Relleno (Padding)',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'input_focus_heading',
			[
				'label' => 'Estado: Foco (Activo)',
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'input_focus_border_color',
			[
				'label' => 'Color de Borde al Enfocar',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-input:focus' => 'border-color: {{VALUE}}; outline: none;',
				],
			]
		);

		$this->end_controls_section();

		// ==========================
		// STYLE TAB: Botones
		// ==========================
		$this->start_controls_section(
			'style_buttons_section',
			[
				'label' => 'Botones Generales',
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .alezux-btn',
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label' => 'Radio de Borde',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_responsive_control(
			'button_padding',
			[
				'label' => 'Relleno (Padding)',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		// Normal
		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => 'Normal',
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => 'Color de Texto Primary',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn.alezux-btn-primary' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'button_bg_color',
			[
				'label' => 'Color de Fondo Primary',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn.alezux-btn-primary' => 'background-color: {{VALUE}} !important;',
				],
			]
		);
		
		$this->add_control(
			'button_sec_text_color',
			[
				'label' => 'Color de Texto Secondary',
				'type' => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .alezux-btn.alezux-btn-secondary' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'button_sec_bg_color',
			[
				'label' => 'Color de Fondo Secondary',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn.alezux-btn-secondary' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		// Hover
		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => 'Hover',
			]
		);

		$this->add_control(
			'button_hover_text_color',
			[
				'label' => 'Color de Texto Primary',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn.alezux-btn-primary:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'button_hover_bg_color',
			[
				'label' => 'Color de Fondo Primary',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn.alezux-btn-primary:hover' => 'background-color: {{VALUE}} !important;',
				],
			]
		);
		
		$this->add_control(
			'button_sec_hover_text_color',
			[
				'label' => 'Color de Texto Secondary',
				'type' => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .alezux-btn.alezux-btn-secondary:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'button_sec_hover_bg_color',
			[
				'label' => 'Color de Fondo Secondary',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn.alezux-btn-secondary:hover' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// ==========================
		// STYLE TAB: Alertas y Estados
		// ==========================
		$this->start_controls_section(
			'style_alerts_section',
			[
				'label' => 'Alertas y Estados',
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		// Control Info / Warning
		$this->add_control(
			'alert_info_heading',
			[
				'label' => 'Alertas de Información / Aviso',
				'type' => Controls_Manager::HEADING,
			]
		);
		$this->add_control(
			'alert_info_bg',
			[
				'label' => 'Color de Fondo',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-alert-info' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .alezux-alert-warning' => 'background-color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'alert_info_color',
			[
				'label' => 'Color de Texto',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-alert-info' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-alert-warning' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'alert_info_border',
				'label' => 'Borde',
				'selector' => '{{WRAPPER}} .alezux-alert-info, {{WRAPPER}} .alezux-alert-warning',
			]
		);
		
		// Control Success
		$this->add_control(
			'alert_success_heading',
			[
				'label' => 'Alertas de Éxito',
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
		$this->add_control(
			'alert_success_bg',
			[
				'label' => 'Color de Fondo',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-alert-success' => 'background-color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'alert_success_color',
			[
				'label' => 'Color de Texto',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-alert-success' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'alert_success_border',
				'label' => 'Borde',
				'selector' => '{{WRAPPER}} .alezux-alert-success',
			]
		);

		$this->add_responsive_control(
			'alert_radius',
			[
				'label' => 'Radio de Borde Común',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .alezux-alert-info, {{WRAPPER}} .alezux-alert-warning, {{WRAPPER}} .alezux-alert-success' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'alert_padding',
			[
				'label' => 'Relleno Común (Padding)',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-alert-info, {{WRAPPER}} .alezux-alert-warning, {{WRAPPER}} .alezux-alert-success' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================
		// STYLE TAB: Tarjetas de Archivos
		// ==========================
		$this->start_controls_section(
			'style_file_cards_section',
			[
				'label' => 'Tarjetas de Archivos',
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'file_card_bg',
			[
				'label' => 'Color de Fondo',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .file-card' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'file_card_text_color',
			[
				'label' => 'Color de Texto Principal',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .file-card span' => 'color: {{VALUE}};',
				],
			]
		);

		 $this->add_control(
			'file_card_icon_color',
			[
				'label' => 'Color del Icono',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .file-card i:first-child' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'file_card_border',
				'label' => 'Borde',
				'selector' => '{{WRAPPER}} .file-card',
			]
		);

		$this->add_responsive_control(
			'file_card_radius',
			[
				'label' => 'Radio de Borde',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .file-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'file_card_padding',
			[
				'label' => 'Relleno (Padding)',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .file-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);
		
		$this->end_controls_section();

		// ==========================
		// STYLE TAB: Entrega Final
		// ==========================
		$this->start_controls_section(
			'style_delivery_section',
			[
				'label' => 'Entrega Final (Paso 5)',
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'delivery_hero_heading',
			[
				'label' => 'Cabecera (Regalo)',
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'delivery_hero_bg',
			[
				'label' => 'Fondo de Cabecera',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .delivery-hero' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'delivery_hero_icon_color',
			[
				'label' => 'Color del Icono Regalo',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .delivery-hero i' => 'color: {{VALUE}} !important;',
				],
			]
		);

		 $this->add_responsive_control(
			'delivery_hero_radius',
			[
				'label' => 'Radio de Borde Cabecera',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .delivery-hero' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'delivery_hero_padding',
			[
				'label' => 'Padding Cabecera',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .delivery-hero' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'delivery_folders_heading',
			[
				'label' => 'Carpetas / Cajas de Recursos',
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'delivery_folder_bg',
			[
				'label' => 'Color de Fondo',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .delivery-folder' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .credentials-box' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'delivery_folder_border',
				'label' => 'Borde',
				'selector' => '{{WRAPPER}} .delivery-folder, {{WRAPPER}} .credentials-box',
			]
		);

		 $this->add_responsive_control(
			'delivery_folder_radius',
			[
				'label' => 'Radio de Borde',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .delivery-folder, {{WRAPPER}} .credentials-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'delivery_folder_padding',
			[
				'label' => 'Padding',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .delivery-folder, {{WRAPPER}} .credentials-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
        $settings = $this->get_settings_for_display();
        $is_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();

        if ( ! is_user_logged_in() && ! $is_edit_mode ) {
            echo '<div class="alezux-alert-info">Debes iniciar sesión para ver tu proyecto.</div>';
            return;
        }

        if ( $is_edit_mode ) {
            // Datos ficticios para el editor
            $current_step = isset($settings['preview_step']) ? $settings['preview_step'] : 'briefing';
            $project_id = 'demo-123';
            $data = [
                'briefing' => [
                    'has_logo' => 'yes',
                    'logo_url' => '#'
                ],
                'identity' => [
                    'status' => 'pending', 
                    'proposal_files' => ['https://via.placeholder.com/800x600?text=Proposicion+1', 'https://via.placeholder.com/800x600?text=Proposicion+2']
                ],
                'web_design' => [
                    'figma_url' => 'https://figma.com/demo'
                ],
                'development' => [
                    'staging_url' => 'https://staging.ejemplotest.com'
                ],
                'delivery' => [
                    'logos' => ['https://via.placeholder.com/150?text=Logo.zip'],
                    'video_links' => ['https://youtube.com/demo1', 'https://youtube.com/demo2'],
                    'credentials' => [
                        'login_url' => 'https://ejemplo.com/wp-admin',
                        'user' => 'admin_test',
                        'password' => 'Pass1234!'
                    ]
                ]
            ];
        } else {
            // Datos reales para el front-end
            $user_id = get_current_user_id();
            $manager = new Projects_Manager();
            
            // Buscar proyecto para el cliente
            $projects = $manager->get_projects( [ 'client_id' => $user_id ] );
            
            if ( empty( $projects ) ) {
                echo '<div class="alezux-alert-info">' . esc_html( $settings['no_project_message'] ) . '</div>';
                return;
            }

            // Conseguir proyecto más reciente
            $project = $projects[0];
            $project_id = $project->id;
            $data = json_decode( $project->project_data, true ) ?: [];
            $current_step = $project->current_step;
        }

        // Map steps to order
        $steps = [
            'briefing' => 'Briefing',
            'identity' => 'Identidad (Logo)',
            'web_design' => 'Diseño Web',
            'development' => 'Desarrollo',
            'delivery' => 'Entrega'
        ];
        
        $steps_keys = array_keys($steps);
        $current_index = array_search($current_step, $steps_keys);

        // Render Timeline
		?>
		<div class="alezux-client-project" id="alezux-client-app" data-id="<?php echo esc_attr($project_id); ?>">
            
            <!-- Timeline Header -->
            <div class="project-timeline">
                <?php foreach($steps as $key => $label): 
                    $step_idx = array_search($key, $steps_keys);
                    $status_class = '';
                    if ($step_idx < $current_index) $status_class = 'completed';
                    elseif ($step_idx === $current_index) $status_class = 'active';
                ?>
                <div class="timeline-step <?php echo $status_class; ?>" data-step-target="step-<?php echo esc_attr(str_replace('_', '-', $key)); ?>" style="cursor: pointer;">
                    <div class="step-circle">
                        <span><?php echo $step_idx + 1; ?></span>
                        <?php if($status_class == 'completed'): ?>
                            <div class="step-completed-badge" style="position: absolute; bottom: 0; right: 0; background: #28a745; color: white; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; font-size: 10px; border: 2px solid #fff;"><i class="fas fa-check"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="step-label"><?php echo $label; ?></div>
                    <?php if($step_idx < count($steps)-1): ?><div class="step-line"></div><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Content Area -->
            <div class="project-content-area">
                
                <!-- STEP 1: BRIEFING -->
                <div class="step-content <?php echo ($current_step === 'briefing') ? 'active' : ''; ?>" id="step-briefing" style="<?php echo ($current_step !== 'briefing') ? 'display: none;' : ''; ?>">
                    <h3>Briefing Inicial</h3>
                    <p>Cuéntanos sobre tu visión para el sitio web y tu marca.</p>
                    
                    <form id="briefing-form" enctype="multipart/form-data">
                        <div class="alezux-form-group">
                            <label>Referencias de Sitios Web (URLs y qué te gusta):</label>
                            <textarea class="alezux-input" name="web_preferences"><?php echo isset($data['briefing']['web_preferences']) ? esc_textarea($data['briefing']['web_preferences']) : ''; ?></textarea>
                        </div>
                        
                        <div class="alezux-form-group">
                            <label>¿Ya tienes Logo?</label>
                            <select class="alezux-input" name="has_logo" id="has-logo-select">
                                <option value="no" <?php selected( ($data['briefing']['has_logo'] ?? ''), 'no' ); ?>>No, necesito uno</option>
                                <option value="yes" <?php selected( ($data['briefing']['has_logo'] ?? ''), 'yes' ); ?>>Sí, ya tengo</option>
                            </select>
                        </div>
                        
                        <div class="alezux-form-group" id="logo-upload-group" style="display: <?php echo ($data['briefing']['has_logo'] ?? '') === 'yes' ? 'block' : 'none'; ?>;">
                            <label>Sube tu Logo (PNG, SVG, JPG):</label>
                            <input type="file" class="alezux-input" name="logo_file" accept="image/*">
                            <?php if(!empty($data['briefing']['logo_url'])): ?>
                                <p>Logo actual: <a href="<?php echo esc_url($data['briefing']['logo_url']); ?>" target="_blank">Ver Logo</a></p>
                            <?php endif; ?>
                        </div>

                         <div class="alezux-section-title" style="margin-top:30px; margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:10px;">
                            <h4>Datos Legales y de Contacto</h4>
                        </div>

                        <div class="alezux-grid-2">
                             <div class="alezux-form-group">
                                <label>1. Nombre completo o Razón Social</label>
                                <input type="text" class="alezux-input" name="legal_name" value="<?php echo esc_attr($data['briefing']['legal_name'] ?? ''); ?>">
                            </div>
                            <div class="alezux-form-group">
                                <label>2. CIF / NIF / NIT</label>
                                <input type="text" class="alezux-input" name="legal_id" value="<?php echo esc_attr($data['briefing']['legal_id'] ?? ''); ?>">
                            </div>
                            <div class="alezux-form-group">
                                <label>3. Dirección fiscal completa</label>
                                <input type="text" class="alezux-input" name="legal_address" value="<?php echo esc_attr($data['briefing']['legal_address'] ?? ''); ?>">
                            </div>
                            <div class="alezux-form-group">
                                <label>4. Teléfono de contacto</label>
                                <input type="text" class="alezux-input" name="legal_phone" value="<?php echo esc_attr($data['briefing']['legal_phone'] ?? ''); ?>">
                            </div>
                             <div class="alezux-form-group">
                                <label>5. Correo electrónico de contacto</label>
                                <input type="email" class="alezux-input" name="legal_email" value="<?php echo esc_attr($data['briefing']['legal_email'] ?? ''); ?>">
                            </div>
                            <div class="alezux-form-group">
                                <label>6. Correo privacidad (si es diferente)</label>
                                <input type="email" class="alezux-input" name="privacy_email" value="<?php echo esc_attr($data['briefing']['privacy_email'] ?? ''); ?>">
                            </div>
                            <div class="alezux-form-group">
                                <label>7. URL del sitio web</label>
                                <input type="text" class="alezux-input" name="legal_url" value="<?php echo esc_attr($data['briefing']['legal_url'] ?? ''); ?>">
                            </div>
                            <div class="alezux-form-group">
                                <label>8. Nombre comercial o de marca</label>
                                <input type="text" class="alezux-input" name="brand_name" value="<?php echo esc_attr($data['briefing']['brand_name'] ?? ''); ?>">
                            </div>
                             <div class="alezux-form-group">
                                <label>9. Actividad del negocio</label>
                                <input type="text" class="alezux-input" name="business_activity" value="<?php echo esc_attr($data['briefing']['business_activity'] ?? ''); ?>">
                            </div>
                            <div class="alezux-form-group">
                                <label>10. Registro Mercantil</label>
                                <input type="text" class="alezux-input" name="legal_registry" value="<?php echo esc_attr($data['briefing']['legal_registry'] ?? ''); ?>">
                            </div>
                            <div class="alezux-form-group">
                                <label>11. Correo DPO (si aplica)</label>
                                <input type="email" class="alezux-input" name="dpo_email" value="<?php echo esc_attr($data['briefing']['dpo_email'] ?? ''); ?>">
                            </div>
                             <div class="alezux-form-group">
                                <label>12. Sectores de actividad (Marketing)</label>
                                <input type="text" class="alezux-input" name="marketing_sectors" value="<?php echo esc_attr($data['briefing']['marketing_sectors'] ?? ''); ?>">
                            </div>
                            <div class="alezux-form-group">
                                <label>13. Jurisdicción (Ciudad/País)</label>
                                <input type="text" class="alezux-input" name="jurisdiction" value="<?php echo esc_attr($data['briefing']['jurisdiction'] ?? ''); ?>">
                            </div>
                             <div class="alezux-form-group">
                                <label>14. WhatsApp para contrataciones</label>
                                <input type="text" class="alezux-input" name="whatsapp" value="<?php echo esc_attr($data['briefing']['whatsapp'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-actions" style="margin-top:30px;">
                            <button type="button" class="alezux-btn alezux-btn-secondary" id="btn-full-control" style="display:none;">¡Todo en sus manos!</button>
                            <button type="submit" class="alezux-btn alezux-btn-primary" id="save-briefing-btn">Guardar y Enviar</button>
                        </div>
                    </form>
                    
                    <script>
                        jQuery(document).ready(function($){
                            $('#has-logo-select').change(function(){
                                if($(this).val() == 'yes') {
                                    $('#logo-upload-group').slideDown();
                                } else {
                                    $('#logo-upload-group').slideUp();
                                }
                            });
                        });
                    </script>
                </div>

                <!-- STEP 2: IDENTITY -->
                <div class="step-content <?php echo ($current_step === 'identity') ? 'active' : ''; ?>" id="step-identity" style="<?php echo ($current_step !== 'identity') ? 'display: none;' : ''; ?>">
                    <h3>Propuesta de Identidad</h3>
                    
                    <?php 
                    $files = isset($data['identity']['proposal_files']) ? $data['identity']['proposal_files'] : [];
                    $status = isset($data['identity']['status']) ? $data['identity']['status'] : '';
                    
                    if(empty($files)): ?>
                        <div class="alezux-alert-warning">
                            <i class="fas fa-paint-brush"></i> Estamos trabajando en tus propuestas de diseño. Te notificaremos pronto.
                        </div>
                    <?php else: ?>
                        
                        <?php if($status === 'changes_requested'): ?>
                            <div class="alezux-alert-info">Has solicitado cambios. Estamos trabajando en ello.</div>
                        <?php elseif($status === 'approved'): ?>
                             <div class="alezux-alert-success">¡Has aprobado esta etapa! Pasaremos al desarrollo web pronto.</div>
                        <?php else: ?>
                             <div class="alezux-alert-info">Por favor revisa las propuestas y danos tu feedback.</div>
                        <?php endif; ?>

                        <div class="files-grid">
                            <?php foreach($files as $index => $file): 
                                $clean_url = strtok($file, '?');
                                $ext = strtolower(pathinfo($clean_url, PATHINFO_EXTENSION));
                                $is_img = in_array($ext, ['png', 'jpg', 'jpeg', 'svg', 'webp', 'gif']);
                            ?>
                                <div class="file-card file-delivery-card">
                                    <a href="<?php echo esc_url($file); ?>" target="_blank" class="file-preview-link">
                                        <?php if($is_img): ?>
                                            <img src="<?php echo esc_url($file); ?>" alt="Propuesta">
                                        <?php else: ?>
                                            <div class="file-preview-icon">
                                                <i class="fas fa-file-pdf"></i>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                    <div class="file-info">
                                        <span class="file-name">Propuesta <?php echo $index + 1; ?></span>
                                        <div class="file-actions">
                                            <a href="<?php echo esc_url($file); ?>" target="_blank" class="alezux-btn alezux-btn-secondary">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                            <a href="<?php echo esc_url($file); ?>" class="alezux-btn alezux-btn-primary" download>
                                                <i class="fas fa-download"></i> Bajar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if($status !== 'approved'): ?>
                        <div class="action-buttons">
                            <button class="alezux-btn alezux-btn-success" data-action="approve" data-step="identity">
                                <i class="fas fa-check-circle"></i> Aprobar Diseño
                            </button>
                            <button class="alezux-btn alezux-btn-danger" onclick="jQuery('#feedback-identity').slideToggle()">
                                <i class="fas fa-edit"></i> Sugerir Cambios
                            </button>
                        </div>
                        
                        <div id="feedback-identity" style="display:none; margin-top:20px; background:#f9f9f9; padding:15px; border-radius:8px;">
                            <label>Describe los cambios que te gustaría ver:</label>
                            <textarea class="alezux-input" id="identity-feedback-text" rows="4" placeholder="Ej: Me gustaría un color azul más oscuro en el logo..."></textarea>
                            <div style="text-align:right; margin-top:10px;">
                                <button class="alezux-btn alezux-btn-primary" data-action="changes" data-step="identity">Enviar Sugerencias</button>
                            </div>
                        </div>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
                
                <!-- STEP 3: WEB DESIGN -->
                <div class="step-content <?php echo ($current_step === 'web_design') ? 'active' : ''; ?>" id="step-web-design" style="<?php echo ($current_step !== 'web_design') ? 'display: none;' : ''; ?>">
                    <h3>Diseño UI/UX (Figma)</h3>
                     <?php 
                    $url = isset($data['web_design']['figma_url']) ? $data['web_design']['figma_url'] : '';
                    if(empty($url)): ?>
                        <div class="alezux-alert-warning">Estamos trabajando en tu diseño.</div>
                    <?php else: ?>
                        <p>Revisa el prototipo interactivo en el siguiente enlace:</p>
                        <a href="<?php echo esc_url($url); ?>" target="_blank" class="alezux-btn alezux-btn-secondary">Ver Prototipo en Figma</a>
                        
                        <div class="action-buttons" style="margin-top:20px;">
                            <button class="alezux-btn alezux-btn-success" data-action="approve" data-step="web_design">Aprobar Diseño Web</button>
                            <button class="alezux-btn alezux-btn-danger" onclick="$('#feedback-web').toggle()">Solicitar Cambios</button>
                        </div>
                         <div id="feedback-web" style="display:none; margin-top:15px;">
                            <textarea class="alezux-input" id="web-feedback-text" placeholder="Describe los cambios..."></textarea>
                            <button class="alezux-btn alezux-btn-primary" data-action="changes" data-step="web_design">Enviar Solicitud</button>
                        </div>
                    <?php endif; ?>
                </div>

                 <!-- STEP 4: DEVELOPMENT -->
                <div class="step-content <?php echo ($current_step === 'development') ? 'active' : ''; ?>" id="step-development" style="<?php echo ($current_step !== 'development') ? 'display: none;' : ''; ?>">
                    <h3>Desarrollo & Montaje</h3>
                       <?php 
                    $url = isset($data['development']['staging_url']) ? $data['development']['staging_url'] : '';
                    if(empty($url)): ?>
                        <div class="alezux-alert-warning">Estamos programando tu sitio web.</div>
                    <?php else: ?>
                        <p>Tu sitio está montado en un servidor de pruebas:</p>
                        <a href="<?php echo esc_url($url); ?>" target="_blank" class="alezux-btn alezux-btn-secondary">Ver Sitio Web (Staging)</a>
                         <div class="action-buttons" style="margin-top:20px;">
                            <button class="alezux-btn alezux-btn-success" data-action="approve" data-step="development">Aprobar y Finalizar</button>
                            <button class="alezux-btn alezux-btn-warning" onclick="$('#call-booking').toggle()">Agendar Revisión / Cambios</button>
                        </div>
                         <div id="call-booking" style="display:none; margin-top:15px;">
                            <p>Para cambios en esta etapa, es mejor agendar una llamada rápida.</p>
                            <a href="#" class="alezux-btn alezux-btn-primary">Calendly (Ejemplo)</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- STEP 5: DELIVERY -->
                <div class="step-content <?php echo ($current_step === 'delivery') ? 'active' : ''; ?>" id="step-delivery" style="<?php echo ($current_step !== 'delivery') ? 'display: none;' : ''; ?>">
                    <div class="delivery-hero">
                        <i class="fas fa-gift" style="font-size: 50px; color:#28a745;"></i>
                        <h2>¡Tu Proyecto está Listo!</h2>
                        <p>Aquí tienes todos los recursos de tu nueva marca y sitio web.</p>
                    </div>
                    
                    <?php 
                    $categories = [
                        'logos' => ['title' => 'Logotipado', 'icon' => 'fa-folder-open'],
                        'fonts' => ['title' => 'Fuentes / Tipografías', 'icon' => 'fa-font'],
                        'brand' => ['title' => 'Identidad de Marca', 'icon' => 'fa-id-card'],
                        'web' => ['title' => 'Recursos Web', 'icon' => 'fa-globe'],
                        'manual' => ['title' => 'Manual de Marca / Guías', 'icon' => 'fa-book']
                    ];

                    foreach($categories as $key => $cat):
                        $assets = isset($data['delivery'][$key]) ? $data['delivery'][$key] : [];
                        if(!empty($assets)): ?>
                            <div class="delivery-folder">
                                <h4><i class="fas <?php echo $cat['icon']; ?>"></i> <?php echo $cat['title']; ?></h4>
                                <div class="files-grid">
                                    <?php foreach($assets as $asset): 
                                        $clean_url = strtok($asset, '?');
                                        $ext = strtolower(pathinfo($clean_url, PATHINFO_EXTENSION));
                                        $is_img = in_array($ext, ['png', 'jpg', 'jpeg', 'svg', 'webp', 'gif']);
                                        ?>
                                        <div class="file-card file-delivery-card">
                                            <a href="<?php echo esc_url($asset); ?>" target="_blank" class="file-preview-link">
                                                <?php if($is_img): ?>
                                                    <img src="<?php echo esc_url($asset); ?>" alt="Preview">
                                                <?php else: ?>
                                                    <div class="file-preview-icon">
                                                        <i class="fas <?php echo ($ext === 'zip' || $ext === 'rar') ? 'fa-file-archive' : 'fa-file-alt'; ?>"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </a>
                                            <div class="file-info">
                                                <span class="file-name"><?php echo esc_html(basename($clean_url)); ?></span>
                                                <div class="file-actions">
                                                    <a href="<?php echo esc_url($asset); ?>" target="_blank" class="alezux-btn alezux-btn-secondary">
                                                        <i class="fas fa-eye"></i> Ver
                                                    </a>
                                                    <a href="<?php echo esc_url($asset); ?>" class="alezux-btn alezux-btn-primary" download>
                                                        <i class="fas fa-download"></i> Bajar
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <div class="delivery-folder">
                        <h4><i class="fas fa-key"></i> Credenciales</h4>
                        <div class="credentials-box">
                            <?php $creds = isset($data['delivery']['credentials']) ? $data['delivery']['credentials'] : []; ?>
                            <p><strong>URL Admin:</strong> <?php echo !empty($creds['login_url']) ? '<a href="'.esc_url($creds['login_url']).'" target="_blank">'.esc_html($creds['login_url']).'</a>' : 'N/A'; ?></p>
                            <p><strong>Usuario:</strong> <?php echo esc_html($creds['user'] ?? 'N/A'); ?></p>
                            <p><strong>Contraseña:</strong> <?php echo !empty($creds['password']) ? '<code>' . esc_html($creds['password']) . '</code>' : 'N/A'; ?></p>
                        </div>
                    </div>

                    <div class="delivery-folder">
                        <h4><i class="fas fa-video"></i> Video Tutoriales</h4>
                        <div class="files-grid">
                            <?php 
                             $videos = isset($data['delivery']['video_links']) ? $data['delivery']['video_links'] : [];
                             if(!empty($videos)):
                                foreach($videos as $video): ?>
                                <div class="file-card video-card">
                                    <div class="video-preview-icon">
                                        <i class="fas fa-play-circle"></i>
                                    </div>
                                    <div class="file-info">
                                        <span class="file-name">Video Tutorial</span>
                                        <div class="file-actions">
                                            <a href="<?php echo esc_url($video); ?>" target="_blank" class="alezux-btn alezux-btn-primary">
                                                <i class="fas fa-play"></i> Ver Video
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; 
                             else: ?>
                                <p>No hay tutoriales disponibles aún.</p>
                             <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
		</div>
		<?php
	}
}
