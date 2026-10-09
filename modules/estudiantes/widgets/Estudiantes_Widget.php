<?php
namespace Alezux_Members\Modules\Estudiantes\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Estudiantes_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux_estudiantes_table';
	}

	public function get_title() {
		return \esc_html__( 'Tabla de Estudiantes', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-table';
	}

	public function get_categories() {
		return [ 'alezux-estudiantes' ];
	}

	public function get_style_depends() {
		return [ 'alezux-estudiantes-css' ];
	}

	public function get_script_depends() {
		return [ 'alezux-estudiantes-js' ];
	}

	protected function _register_controls() {
		// --- Sección Contenido Cabecera ---
		$this->start_controls_section(
			'section_content_header',
			[
				'label' => \esc_html__( 'Cabecera', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'header_title',
			[
				'label'       => \esc_html__( 'Título', 'alezux-members' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => \esc_html__( 'Centro de Mando Académico', 'alezux-members' ),
				'placeholder' => \esc_html__( 'Escribe el título aquí', 'alezux-members' ),
			]
		);

		$this->add_control(
			'header_description',
			[
				'label'       => \esc_html__( 'Descripción', 'alezux-members' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => \esc_html__( 'Gestión de accesos, datos personales y seguridad.', 'alezux-members' ),
				'placeholder' => \esc_html__( 'Escribe la descripción aquí', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

		// --- Sección Estilo Cabecera ---
		$this->start_controls_section(
			'section_style_header',
			[
				'label' => \esc_html__( 'Estilo Cabecera', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'header_bg_color',
			[
				'label'     => \esc_html__( 'Color Fondo Cabecera', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-estudiantes-header' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'header_title_color',
			[
				'label'     => \esc_html__( 'Color Título', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-table-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'header_title_typography',
				'label'    => \esc_html__( 'Tipografía Título', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-table-title',
			]
		);

		$this->add_control(
			'header_desc_color',
			[
				'label'     => \esc_html__( 'Color Descripción', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-table-desc' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'header_desc_typography',
				'label'    => esc_html__( 'Tipografía Descripción', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-table-desc',
			]
		);
		
		$this->add_control(
			'heading_search_style',
			[
				'label'     => \esc_html__( 'Buscador', 'alezux-members' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'search_bg_color',
			[
				'label'     => \esc_html__( 'Fondo Buscador', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-table-search-input' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'search_text_color',
			[
				'label'     => \esc_html__( 'Color Texto Buscador', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-table-search-input' => 'color: {{VALUE}};',
				],
			]
		);
		
		$this->add_control(
			'search_border_color',
			[
				'label'     => \esc_html__( 'Borde Buscador', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-table-search-input' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// --- Sección de Estilo de la Tabla ---
		$this->start_controls_section(
			'section_style_table',
			[
				'label' => \esc_html__( 'Tabla', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'table_header_bg',
			[
				'label'     => \esc_html__( 'Fondo Cabecera', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-finanzas-table thead tr' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'table_header_color',
			[
				'label'     => \esc_html__( 'Color Texto Cabecera', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-finanzas-table thead th' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'table_header_typography',
				'label'    => \esc_html__( 'Tipografía Cabecera', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-finanzas-table thead th',
			]
		);

		$this->add_control(
			'table_row_even_bg',
			[
				'label'     => \esc_html__( 'Fondo Filas Pares', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-finanzas-table tbody tr:nth-child(even)' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'table_row_odd_bg',
			[
				'label'     => \esc_html__( 'Fondo Filas Impares', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-finanzas-table tbody tr:nth-child(odd)' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_table_text_style',
			[
				'label'     => \esc_html__( 'Texto y Contenido', 'alezux-members' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'table_text_color',
			[
				'label'     => \esc_html__( 'Color Texto General', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-finanzas-table tbody td' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'table_text_typography',
				'label'    => \esc_html__( 'Tipografía General', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-finanzas-table tbody td',
			]
		);

		$this->add_control(
			'table_name_color',
			[
				'label'     => \esc_html__( 'Color Nombre Estudiante', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .student-name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'table_name_typography',
				'label'    => \esc_html__( 'Tipografía Nombre', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .student-name',
			]
		);

		$this->add_control(
			'table_username_color',
			[
				'label'     => \esc_html__( 'Color Usuario (@handle)', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .student-email' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'table_username_typography',
				'label'    => \esc_html__( 'Tipografía Usuario', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .student-email',
			]
		);

		$this->add_control(
			'heading_table_cells',
			[
				'label'     => \esc_html__( 'Celdas y Bordes', 'alezux-members' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'cell_padding',
			[
				'label'      => \esc_html__( 'Padding Celdas', 'alezux-members' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .alezux-finanzas-table th, {{WRAPPER}} .alezux-finanzas-table td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'table_border',
				'label'    => \esc_html__( 'Borde Tabla', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-finanzas-app',
			]
		);

		$this->add_control(
			'heading_avatar_style',
			[
				'label'     => \esc_html__( 'Avatar', 'alezux-members' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'avatar_size',
			[
				'label' => \esc_html__( 'Tamaño Avatar', 'alezux-members' ),
				'type'  => Controls_Manager::SLIDER,
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

		$this->add_control(
			'heading_status_style',
			[
				'label'     => \esc_html__( 'Estados', 'alezux-members' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'status_active_color',
			[
				'label'     => \esc_html__( 'Color Activo (Texto)', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .status-active' => 'color: {{VALUE}}; border-color: {{VALUE}};',
				],
			]
		);
		
		$this->add_control(
			'status_active_bg',
			[
				'label'     => \esc_html__( 'Color Activo (Fondo)', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .status-active' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'status_inactive_color',
			[
				'label'     => \esc_html__( 'Color Inactivo (Texto)', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .status-inactive' => 'color: {{VALUE}}; border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'status_inactive_bg',
			[
				'label'     => \esc_html__( 'Color Inactivo (Fondo)', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .status-inactive' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================================
		// ESTILO PÍLDORA TIEMPO DE ESTUDIO
		// ==========================================================
		$this->start_controls_section(
			'section_style_time_pill',
			[
				'label' => \esc_html__( 'Tiempo de Estudio (Píldora)', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'time_pill_bg',
			[
				'label'     => \esc_html__( 'Fondo Píldora', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-time-pill' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'time_pill_border',
				'label'    => \esc_html__( 'Borde Píldora', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-time-pill',
			]
		);

		$this->add_control(
			'time_pill_radius',
			[
				'label'      => \esc_html__( 'Radio Borde', 'alezux-members' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .alezux-time-pill' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// ICONO
		$this->add_control(
			'heading_time_icon',
			[
				'label'     => \esc_html__( 'Icono', 'alezux-members' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'time_icon_color',
			[
				'label'     => \esc_html__( 'Color Icono', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-time-icon' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'time_icon_bg',
			[
				'label'     => \esc_html__( 'Fondo Icono', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-time-icon' => 'background-color: {{VALUE}};',
				],
			]
		);

		// ICONO PERSONALIZADO (IMAGEN)
		$this->add_control(
			'time_pill_custom_icon',
			[
				'label'   => \esc_html__( 'Icono Personalizado (Imagen)', 'alezux-members' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => '',
				],
			]
		);

		$this->add_control(
			'time_icon_image_size',
			[
				'label' => \esc_html__( 'Tamaño Imagen', 'alezux-members' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-time-icon img' => 'width: {{SIZE}}{{UNIT}}; height: auto;',
				],
				'condition' => [
					'time_pill_custom_icon[url]!' => '',
				],
			]
		);

		// TEXTO ETIQUETA
		$this->add_control(
			'heading_time_label',
			[
				'label'     => \esc_html__( 'Etiqueta (TIEMPO TOTAL)', 'alezux-members' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'time_label_color',
			[
				'label'     => \esc_html__( 'Color Etiqueta', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-time-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'time_label_typography',
				'label'    => \esc_html__( 'Tipografía Etiqueta', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-time-label',
			]
		);

		// TEXTO VALOR
		$this->add_control(
			'heading_time_value',
			[
				'label'     => \esc_html__( 'Valor (0h 0m)', 'alezux-members' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'time_value_color',
			[
				'label'     => \esc_html__( 'Color Valor', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-time-value' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'time_value_typography',
				'label'    => \esc_html__( 'Tipografía Valor', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-time-value',
			]
		);

		$this->end_controls_section();

		// --- Sección Estilo Barra de Progreso ---
		$this->start_controls_section(
			'section_style_progress',
			[
				'label' => \esc_html__( 'Barra de Progreso', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'progress_container_heading',
			[
				'label'     => \esc_html__( 'Contenedor', 'alezux-members' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'progress_container_height',
			[
				'label' => \esc_html__( 'Alto', 'alezux-members' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [ 'min' => 2, 'max' => 20 ],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-progress-wrapper' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'progress_container_bg',
			[
				'label'     => \esc_html__( 'Color Fondo', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-progress-wrapper' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'progress_container_radius',
			[
				'label'      => \esc_html__( 'Radio Borde', 'alezux-members' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .alezux-progress-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'progress_bar_heading',
			[
				'label'     => \esc_html__( 'Barra de Relleno', 'alezux-members' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'progress_fill_color',
			[
				'label'     => \esc_html__( 'Color Relleno', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-progress-bar' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'progress_fill_radius',
			[
				'label'      => \esc_html__( 'Radio Borde Relleno', 'alezux-members' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .alezux-progress-bar' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'progress_text_heading',
			[
				'label'     => \esc_html__( 'Texto (% Completado)', 'alezux-members' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'progress_text_color',
			[
				'label'     => \esc_html__( 'Color Texto', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-progress-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'progress_text_typography',
				'selector' => '{{WRAPPER}} .alezux-progress-text',
			]
		);

		$this->end_controls_section();

		// --- Sección Botón Gestionar ---
		$this->start_controls_section(
			'section_style_btn',
			[
				'label' => \esc_html__( 'Botón Gestionar', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'btn_bg_color',
			[
				'label'     => \esc_html__( 'Color Fondo', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-action-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_text_color',
			[
				'label'     => \esc_html__( 'Color Texto', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-action-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'btn_border',
				'selector' => '{{WRAPPER}} .alezux-action-btn',
			]
		);

		$this->add_control(
			'btn_border_radius',
			[
				'label'      => \esc_html__( 'Radio de Borde', 'alezux-members' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .alezux-action-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// --- SECCIÓN ESTILO MODAL: GENERAL ---
		$this->start_controls_section(
			'section_style_modal',
			[
				'label' => \esc_html__( 'Modal: General y Header', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'modal_overlay_bg',
			[
				'label'     => \esc_html__( 'Fondo Overlay', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.alezux-management-modal-overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'modal_bg_color',
			[
				'label'     => \esc_html__( 'Fondo Modal', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.alezux-management-modal, .alezux-alert-modal' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'modal_border',
				'label'    => \esc_html__( 'Borde Modal', 'alezux-members' ),
				'selector' => '.alezux-management-modal, .alezux-alert-modal',
			]
		);
		
		$this->add_control(
			'modal_radius',
			[
				'label'      => \esc_html__( 'Radio Borde', 'alezux-members' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'.alezux-management-modal, .alezux-alert-modal' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'modal_shadow',
				'selector' => '.alezux-management-modal, .alezux-alert-modal',
			]
		);

		$this->add_control(
			'heading_modal_header',
			[
				'label'     => \esc_html__( 'Cabecera Modal', 'alezux-members' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'modal_header_bg',
			[
				'label'     => \esc_html__( 'Fondo Header', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.alezux-modal-header' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'modal_title_color',
			[
				'label'     => \esc_html__( 'Color Título', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.alezux-modal-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'modal_title_typo',
				'selector' => '.alezux-modal-title',
			]
		);

		$this->start_controls_tabs( 'tabs_modal_close_style' );
		
		$this->start_controls_tab(
			'tab_modal_close_normal',
			[
				'label' => \esc_html__( 'Normal', 'alezux-members' ),
			]
		);

		$this->add_control(
			'modal_close_color',
			[
				'label'     => \esc_html__( 'Color Icono', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.alezux-modal-close' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'modal_close_bg_color',
			[
				'label'     => \esc_html__( 'Color Fondo', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.alezux-modal-close' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_modal_close_hover',
			[
				'label' => \esc_html__( 'Hover', 'alezux-members' ),
			]
		);

		$this->add_control(
			'modal_close_color_hover',
			[
				'label'     => \esc_html__( 'Color Icono', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.alezux-modal-close:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'modal_close_bg_color_hover',
			[
				'label'     => \esc_html__( 'Color Fondo', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.alezux-modal-close:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// --- SECCIÓN ESTILO MODAL: FORMULARIOS ---
		$this->start_controls_section(
			'section_style_modal_form',
			[
				'label' => \esc_html__( 'Modal: Etiquetas e Inputs', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'modal_label_color',
			[
				'label'     => \esc_html__( 'Color Etiquetas', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.alezux-form-label, .alezux-section-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'modal_label_typo',
				'label'    => \esc_html__( 'Tipografía Etiquetas', 'alezux-members' ),
				'selector' => '.alezux-form-label, .alezux-section-title',
			]
		);

		$this->add_control(
			'heading_modal_inputs',
			[
				'label'     => \esc_html__( 'Campos de Texto', 'alezux-members' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'modal_input_typo',
				'selector' => '.alezux-form-control',
			]
		);

		$this->add_control(
			'modal_input_bg',
			[
				'label'     => \esc_html__( 'Fondo Input', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.alezux-form-control' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'modal_input_color',
			[
				'label'     => \esc_html__( 'Color Texto Input', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.alezux-form-control' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'modal_input_border_color',
			[
				'label'     => \esc_html__( 'Color Borde Input', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.alezux-form-control' => 'border-color: {{VALUE}};',
				],
			]
		);
		
		$this->add_control(
			'modal_input_radius',
			[
				'label'      => \esc_html__( 'Radio Borde Input', 'alezux-members' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'.alezux-form-control' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'modal_input_padding',
			[
				'label'      => \esc_html__( 'Padding Input', 'alezux-members' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'.alezux-form-control' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// --- SECCIÓN ESTILO MODAL: BOTONES ---
		$this->start_controls_section(
			'section_style_modal_buttons',
			[
				'label' => \esc_html__( 'Modal: Botones', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		// --- Botón Primario ---
		$this->add_control(
			'heading_btn_primary',
			[
				'label'     => \esc_html__( 'Botón Guardar (Primario)', 'alezux-members' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'modal_btn_primary_typo',
				'selector' => '.alezux-btn-primary',
			]
		);

		$this->start_controls_tabs( 'tabs_btn_primary' );

		$this->start_controls_tab( 'tab_btn_primary_normal', [ 'label' => \esc_html__( 'Normal', 'alezux-members' ) ] );

		$this->add_control(
			'modal_btn_primary_text',
			[
				'label'     => \esc_html__( 'Texto', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '.alezux-btn-primary' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'modal_btn_primary_bg',
			[
				'label'     => \esc_html__( 'Fondo', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '.alezux-btn-primary' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_btn_primary_hover', [ 'label' => \esc_html__( 'Hover', 'alezux-members' ) ] );

		$this->add_control(
			'modal_btn_primary_text_hover',
			[
				'label'     => \esc_html__( 'Texto', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '.alezux-btn-primary:hover' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'modal_btn_primary_bg_hover',
			[
				'label'     => \esc_html__( 'Fondo', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '.alezux-btn-primary:hover' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// --- Botón Warning ---
		$this->add_control(
			'heading_btn_warning',
			[
				'label'     => \esc_html__( 'Botón Reset Password (Warning)', 'alezux-members' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'modal_btn_warning_typo',
				'selector' => '.alezux-btn-warning',
			]
		);

		$this->start_controls_tabs( 'tabs_btn_warning' );

		$this->start_controls_tab( 'tab_btn_warning_normal', [ 'label' => \esc_html__( 'Normal', 'alezux-members' ) ] );

		$this->add_control(
			'modal_btn_warning_text',
			[
				'label'     => \esc_html__( 'Texto', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '.alezux-btn-warning' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'modal_btn_warning_bg',
			[
				'label'     => \esc_html__( 'Fondo', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '.alezux-btn-warning' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_btn_warning_hover', [ 'label' => \esc_html__( 'Hover', 'alezux-members' ) ] );

		$this->add_control(
			'modal_btn_warning_text_hover',
			[
				'label'     => \esc_html__( 'Texto', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '.alezux-btn-warning:hover' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'modal_btn_warning_bg_hover',
			[
				'label'     => \esc_html__( 'Fondo', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '.alezux-btn-warning:hover' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// --- Botón Danger ---
		$this->add_control(
			'heading_btn_danger',
			[
				'label'     => \esc_html__( 'Botón Bloquear (Peligro)', 'alezux-members' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'modal_btn_danger_typo',
				'selector' => '.alezux-btn-danger',
			]
		);

		$this->start_controls_tabs( 'tabs_btn_danger' );

		$this->start_controls_tab( 'tab_btn_danger_normal', [ 'label' => \esc_html__( 'Normal', 'alezux-members' ) ] );

		$this->add_control(
			'modal_btn_danger_text',
			[
				'label'     => \esc_html__( 'Texto', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '.alezux-btn-danger' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'modal_btn_danger_bg',
			[
				'label'     => \esc_html__( 'Fondo', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '.alezux-btn-danger' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_btn_danger_hover', [ 'label' => \esc_html__( 'Hover', 'alezux-members' ) ] );

		$this->add_control(
			'modal_btn_danger_text_hover',
			[
				'label'     => \esc_html__( 'Texto', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '.alezux-btn-danger:hover' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'modal_btn_danger_bg_hover',
			[
				'label'     => \esc_html__( 'Fondo', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '.alezux-btn-danger:hover' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$limit = 10;

		// Obtener estudiantes
		$args = [
			'role__in'    => [ 'subscriber', 'student' ],
			'number'      => $limit,
			'count_total' => true,
		];

		$user_query = new \WP_User_Query( $args );
		$students = $user_query->get_results();
		$total_users = $user_query->get_total();

		// Fallback si no hay roles específicos
		if ( empty( $students ) && $total_users === 0 ) {
			$args = [ 
				'number'      => $limit,
				'count_total' => true,
			]; 
			$user_query = new \WP_User_Query( $args );
			$students = $user_query->get_results();
			$total_users = $user_query->get_total();
		}

		$total_pages = ceil( $total_users / $limit );

		// Custom Icon logic
		$custom_icon_url = ! empty( $settings['time_pill_custom_icon']['url'] ) ? $settings['time_pill_custom_icon']['url'] : '';

		?>

		<div class="alezux-finanzas-app alezux-estudiantes-wrapper" data-limit="<?php echo \esc_attr( $limit ); ?>" data-time-icon="<?php echo \esc_url( $custom_icon_url ); ?>">
			<!-- Header -->
			<div class="alezux-table-header alezux-estudiantes-header">
				<div class="alezux-header-left">
					<h2 class="alezux-table-title alezux-estudiantes-title"><?php echo \esc_html( $settings['header_title'] ); ?></h2>
					<p class="alezux-table-desc alezux-estudiantes-desc"><?php echo \esc_html( $settings['header_description'] ); ?></p>
				</div>
                <div class="alezux-header-right">
                    <div class="alezux-filters-wrapper" style="display:flex; gap:10px; align-items:center; margin-right: 15px;">
                        <!-- Filtro Curso -->
                        <?php 
                        $courses = get_posts([
                             'post_type' => 'sfwd-courses',
                             'numberposts' => -1,
                             'post_status' => 'publish',
                             'orderby' => 'title',
                             'order' => 'ASC'
                        ]);
                        ?>
                        <div class="alezux-select-wrapper">
                            <select id="filter-course" class="alezux-filter-select alezux-form-control" style="height:40px; padding: 0 30px 0 10px;">
                                <option value=""><?php esc_html_e( 'Todos los cursos', 'alezux-members' ); ?></option>
                                <?php if($courses): foreach($courses as $course): ?>
                                    <option value="<?php echo esc_attr($course->ID); ?>"><?php echo esc_html($course->post_title); ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <!-- Filtro Estado -->
                        <div class="alezux-select-wrapper">
                            <select id="filter-status" class="alezux-filter-select alezux-form-control" style="height:40px; padding: 0 30px 0 10px;">
                                <option value=""><?php esc_html_e( 'Todos los estados', 'alezux-members' ); ?></option>
                                <option value="active"><?php esc_html_e( 'Activo', 'alezux-members' ); ?></option>
                                <option value="blocked"><?php esc_html_e( 'Bloqueado', 'alezux-members' ); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="alezux-search-wrapper">
                       <span class="dashicons dashicons-search search-icon"></span>
                       <input type="text" class="alezux-table-search-input" placeholder="<?php esc_attr_e( 'Buscar por nombre o email...', 'alezux-members' ); ?>">
                       <span class="dashicons dashicons-no-alt alezux-clear-icon"></span>
                   </div>
                </div>
			</div>

			<!-- Barra de Acciones Masivas (Oculta por defecto) -->
			<div class="alezux-bulk-actions-bar" style="display: none; background: var(--alezux-bg-card, #1a1a1a); padding: 15px 20px; border-bottom: 1px solid var(--alezux-border-color, #333); display: flex; align-items: center; justify-content: space-between; gap: 15px;">
				<div class="alezux-bulk-left" style="display: flex; align-items: center; gap: 15px;">
					<span class="alezux-bulk-count" style="font-weight: bold; color: var(--alezux-primary, #6c5ce7);"><span id="bulk-selected-count">0</span> seleccionados</span>
					<?php 
					global $wpdb;
					$plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
					$plans = [];
					if ( $wpdb->get_var( "SHOW TABLES LIKE '$plans_table'" ) == $plans_table ) {
						$plans = $wpdb->get_results( "SELECT id, name FROM $plans_table ORDER BY name ASC" );
					}
					?>
					<select id="bulk-plan-select" class="alezux-form-control" style="width: 200px;">
						<option value=""><?php esc_html_e( 'Ningún plan (Sólo re-enviar)', 'alezux-members' ); ?></option>
						<?php if ( ! empty( $plans ) ) : foreach ( $plans as $plan ) : ?>
							<option value="<?php echo esc_attr( $plan->id ); ?>"><?php echo esc_html( $plan->name ); ?></option>
						<?php endforeach; endif; ?>
					</select>
					<button id="btn-bulk-process" class="alezux-action-btn" style="background: var(--alezux-primary, #6c5ce7);">
						<i class="fas fa-paper-plane"></i> Procesar y Enviar
					</button>
				</div>
				<div class="alezux-bulk-right" style="flex: 1; max-width: 300px; display: none;" id="bulk-progress-container">
					<div class="alezux-progress-wrapper" style="margin: 0;">
						<div class="progress-Label" style="font-size: 11px; margin-bottom: 5px; display: flex; justify-content: space-between;">
							<span id="bulk-progress-text">Procesando...</span>
							<span id="bulk-progress-percent">0%</span>
						</div>
						<div class="alezux-progress-bar-bg" style="height: 6px;">
							<div id="bulk-progress-fill" class="alezux-progress-bar-fill" style="width: 0%;"></div>
						</div>
					</div>
				</div>
			</div>

			<!-- Table -->
			<div class="alezux-table-wrapper">
				<table class="alezux-finanzas-table alezux-estudiantes-table">
					<thead>
						<tr>
							<th style="width: 40px; text-align: center;"><input type="checkbox" id="selectAllStudents"></th>
							<th style="width: 280px;"><?php \esc_html_e( 'ESTUDIANTE', 'alezux-members' ); ?></th>
							<th><?php \esc_html_e( 'CORREO', 'alezux-members' ); ?></th>
							<th style="width: 180px;"><?php \esc_html_e( 'PROGRESO academico', 'alezux-members' ); ?></th>
							<th style="width: 120px;"><?php \esc_html_e( 'ESTADO', 'alezux-members' ); ?></th>
							<th style="width: 100px; text-align: right;"><?php \esc_html_e( 'ACCIONES', 'alezux-members' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $students ) ) : ?>
							<?php foreach ( $students as $student ) : 
								$avatar_url = \get_avatar_url( $student->ID );
								$name = $student->display_name;
								$email = $student->user_email;
								
								// Calcular Progreso
								$avg_progress = 0;
								if ( function_exists( 'learndash_user_get_enrolled_courses' ) && function_exists( 'learndash_course_get_user_progress' ) ) {
									$user_courses = \learndash_user_get_enrolled_courses( $student->ID );
									if ( ! empty( $user_courses ) ) {
										$total_progress = 0;
										foreach ( $user_courses as $course_id ) {
											$progress = \learndash_course_get_user_progress( $student->ID, $course_id );
											$percentage = isset( $progress['percentage'] ) ? intval( $progress['percentage'] ) : 0;
											$total_progress += $percentage;
										}
										$avg_progress = intval( $total_progress / count( $user_courses ) );
									}
								}

								$is_blocked = (bool) \get_user_meta( $student->ID, 'alezux_is_blocked', true );
								if ( $is_blocked ) {
									$status_label = \esc_html__( 'Bloqueado', 'alezux-members' );
									$status_class = 'status-failed';
								} else {
									$status_label = \esc_html__( 'Activo', 'alezux-members' );
									$status_class = 'status-active';
								}
							?>
							<tr>
								<td style="text-align: center;">
									<input type="checkbox" class="student-checkbox" value="<?php echo \esc_attr( $student->ID ); ?>">
								</td>
								<td>
									<div class="alezux-student-info">
										<img src="<?php echo \esc_url( $avatar_url ); ?>" alt="<?php echo \esc_attr( $name ); ?>" class="alezux-student-avatar">
										<div class="alezux-student-text">
											<span class="student-name"><?php echo \esc_html( $name ); ?></span>
											<span class="student-email"><?php echo '@' . \esc_html( $student->user_nicename ); ?></span>
										</div>
									</div>
								</td>
								<td>
									<span class="student-email"><?php echo \esc_html( $email ); ?></span>
								</td>
								<td>
									<div class="alezux-progress-wrapper">
										<div class="progress-Label">
											<span><?php echo \esc_html( $avg_progress ); ?>%</span>
											<span><?php \esc_html_e( 'Completado', 'alezux-members' ); ?></span>
										</div>
										<div class="alezux-progress-bar-bg">
											<div class="alezux-progress-bar-fill" style="width: <?php echo \esc_attr( $avg_progress ); ?>%;"></div>
										</div>
									</div>
								</td>
								<td>
									<span class="alezux-status-badge <?php echo \esc_attr( $status_class ); ?>">
										<span class="alezux-status-dot"></span>
										<?php echo \esc_html( $status_label ); ?>
									</span>
								</td>
								<td style="text-align: right;">
									<button class="alezux-action-btn" data-student-id="<?php echo \esc_attr( $student->ID ); ?>">
										<i class="fas fa-cog"></i> <?php \esc_html_e( 'Gestionar', 'alezux-members' ); ?>
									</button>
								</td>
							</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="5" style="text-align:center; padding: 40px;">
									<?php \esc_html_e( 'No se encontraron estudiantes.', 'alezux-members' ); ?>
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
			
			<!-- Footer: Pagination & Limit -->
			<div class="alezux-table-footer">
				<div class="alezux-pagination alezux-estudiantes-pagination" 
					 data-total-pages="<?php echo \esc_attr( $total_pages ); ?>" 
					 data-current-page="1">
				<!-- Pagination rendered via JS -->
				</div>

				<div class="alezux-row-limit-wrapper">
					<span class="alezux-row-limit-label"><?php \esc_html_e( 'Filas:', 'alezux-members' ); ?></span>
					<select class="alezux-row-limit-select">
						<option value="10" <?php \selected( $limit, 10 ); ?>>10</option>
						<option value="20" <?php \selected( $limit, 20 ); ?>>20</option>
						<option value="50" <?php \selected( $limit, 50 ); ?>>50</option>
						<option value="100" <?php \selected( $limit, 100 ); ?>>100</option>
						<option value="200" <?php \selected( $limit, 200 ); ?>>200</option>
					</select>
				</div>
			</div>

            <!-- MODAL DE GESTIÓN (Hidden default) -->
            <div id="alezux-management-modal-overlay" class="alezux-management-modal-overlay" style="display:none;">
                <div class="alezux-management-modal">
                    <div class="alezux-modal-header">
                        <h3 class="alezux-modal-title"><?php \esc_html_e( 'Gestionar Estudiante', 'alezux-members' ); ?></h3>
                        <button id="alezux-modal-close" class="alezux-modal-close">&times;</button>
                    </div>
                    <div class="alezux-modal-body">
                        <!-- Loading State -->
                        <div id="alezux-modal-loading" style="text-align:center; padding: 40px;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 30px; color: #6366f1;"></i>
                        </div>

                        <!-- Content State -->
                        <div id="alezux-modal-content" style="display:none;">
                            <input type="hidden" id="alezux-manage-user-id" value="">

                            <!-- 1. Editar Datos -->
                            <div class="alezux-section-title"><?php \esc_html_e( 'Información Personal', 'alezux-members' ); ?></div>
                            <div class="alezux-manage-form-grid">
                                <div>
                                    <label class="alezux-form-label"><?php \esc_html_e( 'Nombre', 'alezux-members' ); ?></label>
                                    <input type="text" id="manage-first-name" class="alezux-form-control">
                                </div>
                                <div>
                                    <label class="alezux-form-label"><?php \esc_html_e( 'Apellido', 'alezux-members' ); ?></label>
                                    <input type="text" id="manage-last-name" class="alezux-form-control">
                                </div>
                                <div class="alezux-full-width">
                                    <label class="alezux-form-label"><?php \esc_html_e( 'Correo Electrónico', 'alezux-members' ); ?></label>
                                    <input type="email" id="manage-email" class="alezux-form-control">
                                </div>
                                <div class="alezux-full-width" style="margin-top:10px;">
                                    <button class="alezux-btn alezux-btn-primary" id="btn-save-student-data">
                                        <?php \esc_html_e( 'Guardar Cambios', 'alezux-members' ); ?> <i class="fas fa-spinner alezux-spinner"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- 2. Seguridad (Password & Block) -->
                            <div class="alezux-section-title"><?php \esc_html_e( 'Seguridad y Acceso', 'alezux-members' ); ?></div>
                            <div class="alezux-manage-form-grid" style="align-items:end;">
                                <div class="alezux-full-width" style="display:flex; gap:10px; align-items:flex-end;">
                                    <div style="flex:1;">
                                        <label class="alezux-form-label"><?php \esc_html_e( 'Cambiar Contraseña Manual', 'alezux-members' ); ?></label>
                                        <input type="text" id="manage-password" class="alezux-form-control" placeholder="Dejar en blanco para no cambiar">
                                    </div>
                                </div>
                                <div style="margin-top:5px;">
                                    <button class="alezux-btn alezux-btn-warning alezux-btn-block" id="btn-reset-password">
                                        <i class="fas fa-envelope"></i> <?php \esc_html_e( 'Generar y Enviar por Correo', 'alezux-members' ); ?>
                                    </button>
                                </div>
                                <div style="margin-top:5px;">
                                    <button class="alezux-btn alezux-btn-danger alezux-btn-block" id="btn-block-user">
                                        <i class="fas fa-ban"></i> <span id="lbl-block-user"><?php \esc_html_e( 'Bloquear Acceso Academia', 'alezux-members' ); ?></span>
                                    </button>
                                </div>
                            </div>

                            <!-- 3. Activos -->
                            <div class="alezux-section-title"><?php \esc_html_e( 'Cursos Activos', 'alezux-members' ); ?></div>
                            <ul id="list-enrolled-courses" class="alezux-course-list">
                                <!-- Populated via JS -->
                            </ul>
                            <div id="no-enrolled-msg" style="color:#666; font-size:13px; font-style:italic; display:none;"><?php \esc_html_e( 'No tiene cursos activos.', 'alezux-members' ); ?></div>

                            <div class="alezux-section-title"><?php \esc_html_e( 'Planes Activos (Acceso a Módulos)', 'alezux-members' ); ?></div>
                            <ul id="list-enrolled-plans" class="alezux-course-list" style="margin-bottom: 20px;">
                                <!-- Populated via JS -->
                            </ul>
                            <div id="no-enrolled-plans-msg" style="color:#666; font-size:13px; font-style:italic; display:none; margin-bottom: 20px;"><?php \esc_html_e( 'No tiene planes activos.', 'alezux-members' ); ?></div>

                            <!-- 4. Conceder Acceso -->
                            <div class="alezux-section-title"><?php \esc_html_e( 'Conceder Nuevo Acceso', 'alezux-members' ); ?></div>
                            <div class="alezux-grant-access-form" style="display:flex; flex-direction:column; gap:15px; padding:15px; border:1px solid #333; border-radius:6px; background: rgba(0,0,0,0.2);">
                                <div>
                                    <label class="alezux-form-label" style="margin-bottom: 5px; display:block;"><?php \esc_html_e( 'Seleccionar Curso', 'alezux-members' ); ?></label>
                                    <select id="select-grant-course" class="alezux-form-control">
                                        <option value=""><?php \esc_html_e( 'Seleccione un curso...', 'alezux-members' ); ?></option>
                                    </select>
                                </div>
                                <div>
                                    <label class="alezux-form-label" style="margin-bottom: 5px; display:block;"><?php \esc_html_e( 'Seleccionar Plan (Local/Stripe)', 'alezux-members' ); ?></label>
                                    <select id="select-grant-plan" class="alezux-form-control" disabled>
                                        <option value=""><?php \esc_html_e( 'Primero seleccione un curso...', 'alezux-members' ); ?></option>
                                    </select>
                                </div>
                                <button class="alezux-btn alezux-btn-primary" id="btn-grant-course-plan" style="margin-top: 5px;" disabled>
                                    <i class="fas fa-plus-circle"></i> <?php \esc_html_e( 'Conceder Acceso al Estudiante', 'alezux-members' ); ?>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

			<!-- SYSTEM ALERT MODAL -->
            <div id="alezux-alert-modal-overlay" class="alezux-management-modal-overlay" style="display:none; z-index: 10001;">
                <div class="alezux-alert-modal">
                    <div class="alezux-alert-icon" id="alezux-alert-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h3 id="alezux-alert-title">Título Alerta</h3>
                    <p id="alezux-alert-message">Mensaje de alerta</p>
                    <div class="alezux-alert-actions">
                        <button id="alezux-alert-cancel" class="alezux-btn alezux-btn-secondary" style="display:none;"><?php \esc_html_e( 'Cancelar', 'alezux-members' ); ?></button>
                        <button id="alezux-alert-confirm" class="alezux-btn alezux-btn-primary"><?php \esc_html_e( 'Aceptar', 'alezux-members' ); ?></button>
                    </div>
                </div>
            </div>

		</div>
		<?php
	}
}
