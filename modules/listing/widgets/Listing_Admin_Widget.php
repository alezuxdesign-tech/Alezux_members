<?php
namespace Alezux_Members\Modules\Listing\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Listing_Admin_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux_listing_admin';
	}

	public function get_title() {
		return esc_html__( 'Listing Admin', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-bullet-list';
	}

	public function get_categories() {
		return [ 'alezux-admin' ]; // Usar la categoría común para admins
	}

	public function get_style_depends() {
		return [ 'alezux-listing-admin-css' ];
	}

	public function get_script_depends() {
		return [ 'alezux-listing-admin-js' ];
	}

	protected function register_controls() {
		// Content Tab
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Contenido', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'header_title',
			[
				'label' => esc_html__( 'Título del Gestor', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Gestor de Tareas', 'alezux-members' ),
			]
		);

		$this->add_control(
			'header_description',
			[
				'label' => esc_html__( 'Descripción', 'alezux-members' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Crea y administra las tareas requeridas para los estudiantes.', 'alezux-members' ),
			]
		);

		$this->add_control(
			'edit_icon',
			[
				'label' => esc_html__( 'Ícono del Botón: Editar', 'alezux-members' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-pen',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'delete_icon',
			[
				'label' => esc_html__( 'Ícono del Botón: Eliminar', 'alezux-members' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-trash',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'edit_close_icon',
			[
				'label' => esc_html__( 'Ícono de Cerrar Modal', 'alezux-members' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-times',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'history_icon',
			[
				'label' => esc_html__( 'Ícono del Botón: Historial', 'alezux-members' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-users',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'preview_dummy_data',
			[
				'label' => esc_html__( 'Ver Datos de Prueba', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Sí', 'alezux-members' ),
				'label_off' => esc_html__( 'No', 'alezux-members' ),
				'return_value' => 'yes',
				'default' => '',
				'description' => esc_html__( 'Mostrar tareas falsas en el editor para previsualizar estilos de tipografía.', 'alezux-members' ),
			]
		);

		$this->add_control(
			'preview_show_modal',
			[
				'label' => esc_html__( 'Forzar Modal Editar Abierto', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Sí', 'alezux-members' ),
				'label_off' => esc_html__( 'No', 'alezux-members' ),
				'return_value' => 'yes',
				'default' => '',
				'description' => esc_html__( 'Forza que el Modal de Edición siempre esté abierto dentro de Elementor para estilizar sus textos y botones.', 'alezux-members' ),
			]
		);

		$this->add_control(
			'preview_show_history_modal',
			[
				'label' => esc_html__( 'Forzar Modal Historial Abierto', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Sí', 'alezux-members' ),
				'label_off' => esc_html__( 'No', 'alezux-members' ),
				'return_value' => 'yes',
				'default' => '',
				'description' => esc_html__( 'Forza que el Modal de Historial siempre esté abierto dentro de Elementor para estilizar la tabla.', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

		// Style Tab
		$this->start_controls_section(
			'style_section',
			[
				'label' => esc_html__( 'Estilos Generales', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'bg_color',
			[
				'label' => esc_html__( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-listing-admin' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => esc_html__( 'Color de Texto Principal', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-listing-title' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-task-item-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'accent_color',
			[
				'label' => esc_html__( 'Color de Acento', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-primary' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// Tipografía Section
		$this->start_controls_section(
			'typography_section',
			[
				'label' => esc_html__( 'Tipografías', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => esc_html__( 'Título Principal', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-listing-title',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'desc_typography',
				'label' => esc_html__( 'Descripción Principal', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-listing-desc',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'task_title_typography',
				'label' => esc_html__( 'Títulos de Tareas', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .task-title, {{WRAPPER}} .alezux-tasks-subtitle',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'task_desc_typography',
				'label' => esc_html__( 'Descripción de Tareas', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .task-desc',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'meta_typography',
				'label' => esc_html__( 'Textos Meta (Fecha)', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .task-meta',
			]
		);

		$this->end_controls_section();

		// === SECCIÓN BOTÓN EDITAR ===
		$this->start_controls_section(
			'edit_button_section',
			[
				'label' => esc_html__( 'Botón Editar', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'edit_icon_color',
			[
				'label' => esc_html__( 'Color de Ícono', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-edit-task svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .btn-edit-task i' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'edit_icon_color_hover',
			[
				'label' => esc_html__( 'Color Hover', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-edit-task:hover svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .btn-edit-task:hover i' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'edit_btn_bg',
			[
				'label' => esc_html__( 'Color Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-edit-task' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'edit_btn_bg_hover',
			[
				'label' => esc_html__( 'Color Fondo Hover', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-edit-task:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'edit_icon_size',
			[
				'label' => esc_html__( 'Tamaño Ícono', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 60,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .btn-edit-task i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .btn-edit-task svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'edit_btn_padding',
			[
				'label' => esc_html__( 'Padding', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .btn-edit-task' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'edit_btn_border_radius',
			[
				'label' => esc_html__( 'Redondeo de Bordes', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .btn-edit-task' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// === SECCIÓN BOTÓN HISTORIAL ===
		$this->start_controls_section(
			'history_button_section',
			[
				'label' => esc_html__( 'Botón Historial', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'history_icon_color',
			[
				'label' => esc_html__( 'Color de Ícono', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-history-task svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .btn-history-task i' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'history_icon_color_hover',
			[
				'label' => esc_html__( 'Color Hover', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-history-task:hover svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .btn-history-task:hover i' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'history_btn_bg',
			[
				'label' => esc_html__( 'Color Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-history-task' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'history_btn_bg_hover',
			[
				'label' => esc_html__( 'Color Fondo Hover', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-history-task:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'history_icon_size',
			[
				'label' => esc_html__( 'Tamaño Ícono', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 60,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .btn-history-task i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .btn-history-task svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'history_btn_padding',
			[
				'label' => esc_html__( 'Padding', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .btn-history-task' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'history_btn_border_radius',
			[
				'label' => esc_html__( 'Redondeo de Bordes', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .btn-history-task' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// === SECCIÓN BOTÓN ELIMINAR ===
		$this->start_controls_section(
			'delete_button_section',
			[
				'label' => esc_html__( 'Botón Eliminar', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'delete_icon_color',
			[
				'label' => esc_html__( 'Color de Ícono', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-delete-task svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .btn-delete-task i' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'delete_icon_color_hover',
			[
				'label' => esc_html__( 'Color Hover', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-delete-task:hover svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .btn-delete-task:hover i' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'delete_btn_bg',
			[
				'label' => esc_html__( 'Color Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-delete-task' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'delete_btn_bg_hover',
			[
				'label' => esc_html__( 'Color Fondo Hover', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn-delete-task:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'delete_icon_size',
			[
				'label' => esc_html__( 'Tamaño Ícono', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 60,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .btn-delete-task i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .btn-delete-task svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'delete_btn_padding',
			[
				'label' => esc_html__( 'Padding', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .btn-delete-task' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'delete_btn_border_radius',
			[
				'label' => esc_html__( 'Redondeo de Bordes', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .btn-delete-task' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// === SECCIÓN BOTÓN CERRAR ===
		$this->start_controls_section(
			'close_button_section',
			[
				'label' => esc_html__( 'Botón Cerrar (X)', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'close_icon_color',
			[
				'label' => esc_html__( 'Color de Cerrar', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-listing-modal-close' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-listing-modal-close svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'close_icon_color_hover',
			[
				'label' => esc_html__( 'Color de Cerrar Hover', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-listing-modal-close:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-listing-modal-close:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'close_icon_size',
			[
				'label' => esc_html__( 'Tamaño de Cerrar', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 60,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-listing-modal-close i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .alezux-listing-modal-close svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_box_size',
			[
				'label' => esc_html__( 'Caja Atrás (Fondo)', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 20,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Botones Section
		$this->start_controls_section(
			'buttons_style_section',
			[
				'label' => esc_html__( 'Botones', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);
		
		$this->add_control(
			'button_text_color',
			[
				'label' => esc_html__( 'Color de Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'label' => esc_html__( 'Tipografía', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-btn',
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__( 'Padding', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// === SECCIÓN MODAL HISTORIAL STYLES ===
		$this->start_controls_section(
			'history_modal_styles',
			[
				'label' => esc_html__( 'Estilos Modal Historial', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'history_modal_title_color',
			[
				'label' => esc_html__( 'Color Título (Historial de Tarea)', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-history-task-modal h3' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'history_modal_title_typography',
				'label' => esc_html__( 'Tipografía Título', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-history-task-modal h3',
			]
		);

		$this->add_control(
			'history_modal_taskname_color',
			[
				'label' => esc_html__( 'Color Nombre de Tarea', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .history-task-name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'history_modal_taskname_typography',
				'label' => esc_html__( 'Tipografía Nombre de Tarea', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .history-task-name',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( ! current_user_can( 'administrator' ) ) {
			echo '<div class="alezux-error-notice">Solo administradores pueden ver este gestor de tareas.</div>';
			return;
		}

		$edit_icon_html = '<i class="fas fa-pen"></i>';
		if ( ! empty( $settings['edit_icon']['value'] ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $settings['edit_icon'], [ 'aria-hidden' => 'true' ] );
			$edit_icon_html = ob_get_clean();
		}

		$delete_icon_html = '<i class="fas fa-trash"></i>';
		if ( ! empty( $settings['delete_icon']['value'] ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $settings['delete_icon'], [ 'aria-hidden' => 'true' ] );
			$delete_icon_html = ob_get_clean();
		}

		$edit_close_icon_html = '<i class="fas fa-times"></i>';
		if ( ! empty( $settings['edit_close_icon']['value'] ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $settings['edit_close_icon'], [ 'aria-hidden' => 'true' ] );
			$edit_close_icon_html = ob_get_clean();
		}

		$history_icon_html = '<i class="fas fa-users"></i>';
		if ( ! empty( $settings['history_icon']['value'] ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $settings['history_icon'], [ 'aria-hidden' => 'true' ] );
			$history_icon_html = ob_get_clean();
		}

		?>
		<div class="alezux-listing-admin" data-icon-edit="<?php echo esc_attr( $edit_icon_html ); ?>" data-icon-delete="<?php echo esc_attr( $delete_icon_html ); ?>" data-icon-history="<?php echo esc_attr( $history_icon_html ); ?>">
			<div class="alezux-listing-header">
				<h2 class="alezux-listing-title"><?php echo esc_html( $settings['header_title'] ); ?></h2>
				<p class="alezux-listing-desc"><?php echo esc_html( $settings['header_description'] ); ?></p>
			</div>

			<div class="alezux-listing-form-wrapper">
				<form id="alezux-add-task-form" class="alezux-form">
					<div class="alezux-form-group">
						<label for="task_title">Nombre de la Tarea</label>
						<input type="text" id="task_title" name="task_title" class="alezux-input" placeholder="Ej. Completar evaluación inicial" required>
					</div>
					<div class="alezux-form-group">
						<label for="task_description">Descripción (Opcional)</label>
						<textarea id="task_description" name="task_description" class="alezux-input" rows="3" placeholder="Detalles extra sobre la tarea..."></textarea>
					</div>
					<button type="submit" class="alezux-btn alezux-btn-primary" id="alezux-submit-task-btn">
						<span class="btn-text">Crear Tarea</span>
						<span class="btn-loading" style="display: none; padding-left: 10px;">Guardando...</span>
					</button>
					<div id="alezux-task-form-msg" class="alezux-form-msg"></div>
				</form>
			</div>

			<div class="alezux-listing-tasks-wrapper">
				<h3 class="alezux-tasks-subtitle">Tareas Creadas</h3>
				<div id="alezux-admin-tasks-list" class="alezux-tasks-list">
					<?php
					$is_elementor_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
					
					if ( $is_elementor_editor && 'yes' === $settings['preview_dummy_data'] ) {
						$dummy_tasks = [
							[
								'id' => 1,
								'title' => 'Conectar Meta Business Manager',
								'description' => 'El estudiante debe dar acceso de anunciante a nuestra cuenta publicitaria.',
								'created_at' => wp_date( 'Y-m-d H:i:s' ),
								'completed_by' => [
									[ 'display_name' => 'Juan Pérez', 'user_email' => 'juan@prueba.com' ],
									[ 'display_name' => 'María Gómez', 'user_email' => 'maria@ejemplo.com' ]
								]
							],
							[
								'id' => 2,
								'title' => 'Llenar Onboarding Formulario',
								'description' => 'Recopilar todos los datos del negocio del cliente, URLs y accesos básicos antes de agendar llamada inicial.',
								'created_at' => wp_date( 'Y-m-d H:i:s', strtotime('-2 days') ),
								'completed_by' => []
							]
						];

						foreach ( $dummy_tasks as $task ) {
							$date_format = date_i18n( get_option('date_format'), strtotime($task['created_at']) );
							$completed_json = wp_json_encode( $task['completed_by'] );

							echo '<div class="alezux-task-item" data-id="' . esc_attr($task['id']) . '" data-completed="' . esc_attr( $completed_json ) . '">
								<div class="task-info">
									<h4 class="task-title">' . esc_html($task['title']) . '</h4>
									<p class="task-desc">' . esc_html($task['description']) . '</p>
									<span class="task-meta"><i class="far fa-calendar-alt"></i> ' . esc_html($date_format) . '</span>
								</div>
								<div class="task-actions">
									<span class="alezux-btn-icon btn-history-task" role="button" tabindex="0" title="Historial">' . $history_icon_html . '</span>
									<span class="alezux-btn-icon btn-edit-task" role="button" tabindex="0" title="Editar">' . $edit_icon_html . '</span>
									<span class="alezux-btn-icon btn-delete-task" role="button" tabindex="0" style="color: #ff4757;" title="Eliminar">' . $delete_icon_html . '</span>
								</div>
							</div>';
						}
					} else {
						echo '<div class="alezux-loading-tasks"><i class="fas fa-circle-notch fa-spin"></i> Cargando tareas...</div>';
					}
					?>
				</div>
			</div>

			<!-- Modal Edit Task -->
			<?php
			$modal_style = 'display: none;';
			$modal_class = 'alezux-listing-modal-overlay alezux-edit-task-modal';
			
			if ( $is_elementor_editor && 'yes' === $settings['preview_show_modal'] ) {
				// Estilos en línea para forzar display relativo y prevenir que el overlay ocupe 100vh tapando Elementor
				$modal_style = 'display: flex; opacity: 1; position: relative !important; z-index: 10 !important; max-height: 500px; padding: 20px; background: transparent;';
				$modal_class .= ' editor-preview-active'; 
			}
			?>
			<div class="<?php echo esc_attr( $modal_class ); ?>" style="<?php echo esc_attr( $modal_style ); ?>">
				<div class="alezux-listing-modal-content">
					<div class="alezux-listing-modal-header">
						<h3>Editar Tarea</h3>
						<span class="alezux-listing-modal-close"><?php echo $edit_close_icon_html; ?></span>
					</div>
					<div class="alezux-listing-modal-body">
						<form class="alezux-form alezux-edit-task-form">
							<input type="hidden" class="edit_task_id" name="edit_task_id">
							<div class="alezux-form-group">
								<label>Nombre de la Tarea</label>
								<input type="text" name="edit_task_title" class="alezux-input edit_task_title" required>
							</div>
							<div class="alezux-form-group">
								<label>Descripción</label>
								<textarea name="edit_task_description" class="alezux-input edit_task_description" rows="3"></textarea>
							</div>
							<button type="submit" class="alezux-btn alezux-btn-primary alezux-submit-edit-task-btn">
								<span class="btn-text">Guardar Cambios</span>
								<span class="btn-loading" style="display: none; padding-left: 10px;">Guardando...</span>
							</button>
						</form>
					</div>
				</div>
			</div>

			<!-- Modal Historial de Usuarios (Semejante a Tablas Finanzas) -->
			<?php
			$history_modal_style = 'display: none;';
			$history_modal_class = 'alezux-listing-modal-overlay alezux-history-task-modal';

			if ( $is_elementor_editor && 'yes' === $settings['preview_show_history_modal'] ) {
				$history_modal_style = 'display: flex; opacity: 1; position: relative !important; z-index: 10 !important; max-height: 600px; padding: 20px; background: transparent;';
				$history_modal_class .= ' editor-preview-active'; 
			}
			?>
			<div class="<?php echo esc_attr( $history_modal_class ); ?>" style="<?php echo esc_attr( $history_modal_style ); ?>">
				<div class="alezux-listing-modal-content" style="max-width: 600px;">
					<div class="alezux-listing-modal-header">
						<div class="header-main">
							<h3>Historial de Tarea</h3>
							<small class="history-task-name">Cargando...</small>
						</div>
						<span class="alezux-listing-modal-close"><?php echo $edit_close_icon_html; ?></span>
					</div>
					<div class="alezux-listing-modal-body">
						<div class="alezux-table-wrapper">
							<table class="alezux-listing-table">
								<thead>
									<tr>
										<th>ESTUDIANTE / USUARIO</th>
										<th>CORREO ELECTRÓNICO</th>
									</tr>
								</thead>
								<tbody class="history-table-body">
									<?php 
									if ( $is_elementor_editor && 'yes' === $settings['preview_show_history_modal'] ) {
										echo '<tr>
												<td>Juan Pérez</td>
												<td>juan@prueba.com</td>
											  </tr>
											  <tr>
												<td>María Gómez</td>
												<td>maria@ejemplo.com</td>
											  </tr>';
									} else {
										echo '<tr><td colspan="2" style="text-align: center; color: #a0a0a0; padding: 20px;">No hay usuarios.</td></tr>';
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

		</div>
		<?php
	}
}
