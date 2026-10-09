<?php
namespace Alezux_Members\Modules\Logros\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class View_Logros_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux_view_logros';
	}

	public function get_title() {
		return \esc_html__( 'Ver Logros', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-table';
	}

	public function get_categories() {
		return [ 'alezux-lms' ];
	}

	public function get_script_depends() {
		return [ 'alezux-view-logros-js' ];
	}

	public function get_style_depends() {
		return [ 'alezux-view-logros-css' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => \esc_html__( 'Configuración', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'header_title',
			[
				'label'   => \esc_html__( 'Título de Cabecera', 'alezux-members' ),
				'type'    => Controls_Manager::TEXT,
				'default' => \esc_html__( 'Logros de Alumnos', 'alezux-members' ),
			]
		);

		$this->add_control(
			'header_description',
			[
				'label'   => \esc_html__( 'Descripción de Cabecera', 'alezux-members' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => \esc_html__( 'Historial de reconocimientos y logros otorgados.', 'alezux-members' ),
			]
		);

		$this->add_control(
			'items_per_page',
			[
				'label' => \esc_html__( 'Elementos por página', 'alezux-members' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 5,
				'max' => 100,
				'step' => 5,
				'default' => 20,
			]
		);

		$this->end_controls_section();
		
		$this->end_controls_section();

		/* -------------------------------------------------------------------------- */
		/*                               SECTION: FILTERS                             */
		/* -------------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_filters',
			[
				'label' => \esc_html__( 'Filtros (Buscador y Select)', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'filters_container_padding',
			[
				'label' => \esc_html__( 'Relleno del Contenedor', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-logros-filters' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'filters_container_bg',
			[
				'label' => \esc_html__( 'Color de Fondo Contenedor', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-logros-filters' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'filters_container_sombra',
				'selector' => '{{WRAPPER}} .alezux-logros-filters',
			]
		);

		$this->add_control(
			'heading_input_styles',
			[
				'label' => \esc_html__( 'Estilos de Inputs', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'input_typography',
				'selector' => '{{WRAPPER}} .alezux-logros-filters input, {{WRAPPER}} .alezux-logros-filters select',
			]
		);

		$this->add_control(
			'input_text_color',
			[
				'label' => \esc_html__( 'Color de Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-logros-filters input, {{WRAPPER}} .alezux-logros-filters select' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_bg_color',
			[
				'label' => \esc_html__( 'Color de Fondo Input', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-logros-filters input, {{WRAPPER}} .alezux-logros-filters select' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'input_border',
				'selector' => '{{WRAPPER}} .alezux-logros-filters input, {{WRAPPER}} .alezux-logros-filters select',
			]
		);

		$this->add_responsive_control(
			'input_border_radius',
			[
				'label' => \esc_html__( 'Radio del Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-logros-filters input, {{WRAPPER}} .alezux-logros-filters select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* -------------------------------------------------------------------------- */
		/*                               SECTION: CARD                                */
		/* -------------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_card',
			[
				'label' => \esc_html__( 'Tarjeta (Logro)', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'card_bg_color',
			[
				'label' => \esc_html__( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-logro-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label' => \esc_html__( 'Relleno (Padding)', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-logro-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_gap',
			[
				'label' => \esc_html__( 'Espacio entre columnas', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-logro-card' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'card_border',
				'selector' => '{{WRAPPER}} .alezux-logro-card',
			]
		);

		$this->add_responsive_control(
			'card_border_radius',
			[
				'label' => \esc_html__( 'Radio del Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-logro-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .alezux-logro-card',
			]
		);

		$this->end_controls_section();

		/* -------------------------------------------------------------------------- */
		/*                               SECTION: IMAGE                               */
		/* -------------------------------------------------------------------------- */

		$this->start_controls_section(
			'section_style_image',
			[
				'label' => \esc_html__( 'Imagen', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'show_image',
			[
				'label' => \esc_html__( 'Mostrar Imagen', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => \esc_html__( 'Sí', 'alezux-members' ),
				'label_off' => \esc_html__( 'No', 'alezux-members' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'image_resolution',
			[
				'label' => \esc_html__( 'Resolución de Imagen', 'alezux-members' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'medium',
				'options' => [
					'thumbnail' => \esc_html__( 'Miniatura (150x150)', 'alezux-members' ),
					'medium' => \esc_html__( 'Medio (300x300)', 'alezux-members' ),
					'large' => \esc_html__( 'Grande (1024x1024)', 'alezux-members' ),
					'full' => \esc_html__( 'Completo', 'alezux-members' ),
				],
				'condition' => [
					'show_image' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'image_width',
			[
				'label' => \esc_html__( 'Ancho', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 500,
					],
				],
			'selectors' => [
					'{{WRAPPER}} .alezux-card-image' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; flex: 0 0 {{SIZE}}{{UNIT}}; max-width: 100%;',
				],
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label' => \esc_html__( 'Radio del Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-card-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .alezux-card-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* -------------------------------------------------------------------------- */
		/*                               SECTION: CONTENT                             */
		/* -------------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_content',
			[
				'label' => \esc_html__( 'Contenido (Textos)', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		// --- COURSE BADGE ---
		$this->add_control(
			'heading_badge_style',
			[
				'label' => \esc_html__( 'Etiqueta de Curso', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'badge_typography',
				'selector' => '{{WRAPPER}} .alezux-course-badge',
			]
		);

		$this->add_control(
			'badge_color',
			[
				'label' => \esc_html__( 'Color de Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-course-badge' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'badge_bg_color',
			[
				'label' => \esc_html__( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-course-badge' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'badge_padding',
			[
				'label' => \esc_html__( 'Relleno', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-course-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_responsive_control(
			'badge_border_radius',
			[
				'label' => \esc_html__( 'Radio del Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-course-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// --- DATE ---
		$this->add_control(
			'heading_date_style',
			[
				'label' => \esc_html__( 'Fecha', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'date_typography',
				'selector' => '{{WRAPPER}} .alezux-card-date',
			]
		);

		$this->add_control(
			'date_color',
			[
				'label' => \esc_html__( 'Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-card-date' => 'color: {{VALUE}};',
				],
			]
		);

		// --- MESSAGE ---
		$this->add_control(
			'heading_message_style',
			[
				'label' => \esc_html__( 'Mensaje', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'message_typography',
				'selector' => '{{WRAPPER}} .alezux-card-message',
			]
		);

		$this->add_control(
			'message_color',
			[
				'label' => \esc_html__( 'Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-card-message' => 'color: {{VALUE}};',
				],
			]
		);

		// --- STUDENT NAME ---
		$this->add_control(
			'heading_student_style',
			[
				'label' => \esc_html__( 'Nombre Estudiante', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'student_typography',
				'selector' => '{{WRAPPER}} .alezux-student-name',
			]
		);

		$this->add_control(
			'student_color',
			[
				'label' => \esc_html__( 'Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-student-name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/* -------------------------------------------------------------------------- */
		/*                               SECTION: ACTIONS                             */
		/* -------------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_actions',
			[
				'label' => \esc_html__( 'Botones de Acción', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'actions_padding',
			[
				'label' => \esc_html__( 'Relleno', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-card-action' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_responsive_control(
			'actions_radius',
			[
				'label' => \esc_html__( 'Radio del Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-card-action' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'actions_typography',
				'selector' => '{{WRAPPER}} .alezux-btn-card-action',
			]
		);

		// EDIT BTN
		$this->add_control(
			'heading_edit_btn',
			[
				'label' => \esc_html__( 'Botón Editar', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->start_controls_tabs( 'tabs_edit_btn' );

		$this->start_controls_tab(
			'tab_edit_btn_normal',
			[
				'label' => \esc_html__( 'Normal', 'alezux-members' ),
			]
		);

		$this->add_control(
			'edit_btn_color',
			[
				'label' => \esc_html__( 'Color de Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-card-edit' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'edit_btn_bg_color',
			[
				'label' => \esc_html__( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-card-edit' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'edit_btn_border',
				'selector' => '{{WRAPPER}} .alezux-btn-card-edit',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_edit_btn_hover',
			[
				'label' => \esc_html__( 'Hover', 'alezux-members' ),
			]
		);

		$this->add_control(
			'edit_btn_hover_color',
			[
				'label' => \esc_html__( 'Color de Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-card-edit:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'edit_btn_hover_bg_color',
			[
				'label' => \esc_html__( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-card-edit:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'edit_btn_hover_border',
				'selector' => '{{WRAPPER}} .alezux-btn-card-edit:hover',
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		// DELETE BTN
		$this->add_control(
			'heading_delete_btn',
			[
				'label' => \esc_html__( 'Botón Eliminar', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->start_controls_tabs( 'tabs_delete_btn' );

		$this->start_controls_tab(
			'tab_delete_btn_normal',
			[
				'label' => \esc_html__( 'Normal', 'alezux-members' ),
			]
		);

		$this->add_control(
			'delete_btn_color',
			[
				'label' => \esc_html__( 'Color de Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-card-delete' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'delete_btn_bg_color',
			[
				'label' => \esc_html__( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-card-delete' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'delete_btn_border',
				'selector' => '{{WRAPPER}} .alezux-btn-card-delete',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_delete_btn_hover',
			[
				'label' => \esc_html__( 'Hover', 'alezux-members' ),
			]
		);

		$this->add_control(
			'delete_btn_hover_color',
			[
				'label' => \esc_html__( 'Color de Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-card-delete:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'delete_btn_hover_bg_color',
			[
				'label' => \esc_html__( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-card-delete:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'delete_btn_hover_border',
				'selector' => '{{WRAPPER}} .alezux-btn-card-delete:hover',
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

		/* -------------------------------------------------------------------------- */
		/*                               SECTION: PAGINATION                          */
		/* -------------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_pagination',
			[
				'label' => \esc_html__( 'Paginación (Cargar más)', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'pagination_align',
			[
				'label' => \esc_html__( 'Alineación', 'alezux-members' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => \esc_html__( 'Izquierda', 'alezux-members' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => \esc_html__( 'Centro', 'alezux-members' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => \esc_html__( 'Derecha', 'alezux-members' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} #alezux-logros-pagination-container' => 'text-align: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'pagination_typography',
				'selector' => '{{WRAPPER}} #alezux-load-more-logros',
			]
		);
		
		$this->add_responsive_control(
			'pagination_padding',
			[
				'label' => \esc_html__( 'Relleno', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} #alezux-load-more-logros' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_pagination_btn' );

		$this->start_controls_tab(
			'tab_pagination_normal',
			[
				'label' => \esc_html__( 'Normal', 'alezux-members' ),
			]
		);

		$this->add_control(
			'pagination_color',
			[
				'label' => \esc_html__( 'Color de Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #alezux-load-more-logros' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pagination_bg_color',
			[
				'label' => \esc_html__( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #alezux-load-more-logros' => 'background-color: {{VALUE}};',
				],
			]
		);
		
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'pagination_border',
				'selector' => '{{WRAPPER}} #alezux-load-more-logros',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_pagination_hover',
			[
				'label' => \esc_html__( 'Hover', 'alezux-members' ),
			]
		);

		$this->add_control(
			'pagination_hover_color',
			[
				'label' => \esc_html__( 'Color de Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #alezux-load-more-logros:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pagination_hover_bg_color',
			[
				'label' => \esc_html__( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #alezux-load-more-logros:hover' => 'background-color: {{VALUE}};',
				],
			]
		);
		
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'pagination_hover_border',
				'selector' => '{{WRAPPER}} #alezux-load-more-logros:hover',
			]
		);
		
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

			// ------------------------------
			// BUTTONS STYLE
			// ------------------------------
			$this->start_controls_section(
				'section_style_buttons',
				[
					'label' => \esc_html__( 'Botones Tarjeta', 'alezux-members' ),
					'tab'   => Controls_Manager::TAB_STYLE,
				]
			);

            $this->add_control(
				'heading_btn_edit',
				[
					'label' => \esc_html__( 'Editar', 'alezux-members' ),
					'type' => Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'btn_edit_typography',
					'selector' => '{{WRAPPER}} .alezux-btn-card-edit',
				]
			);

            $this->start_controls_tabs( 'tabs_btn_edit' );

            $this->start_controls_tab(
                'tab_btn_edit_normal',
                [
                    'label' => \esc_html__( 'Normal', 'alezux-members' ),
                ]
            );

            $this->add_control(
                'btn_edit_color',
                [
                    'label' => \esc_html__( 'Texto', 'alezux-members' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .alezux-btn-card-edit' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'btn_edit_bg_color',
                [
                    'label' => \esc_html__( 'Fondo', 'alezux-members' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .alezux-btn-card-edit' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->end_controls_tab();

            $this->start_controls_tab(
                'tab_btn_edit_hover',
                [
                    'label' => \esc_html__( 'Hover', 'alezux-members' ),
                ]
            );

            $this->add_control(
                'btn_edit_color_hover',
                [
                    'label' => \esc_html__( 'Texto', 'alezux-members' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .alezux-btn-card-edit:hover' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'btn_edit_bg_color_hover',
                [
                    'label' => \esc_html__( 'Fondo', 'alezux-members' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .alezux-btn-card-edit:hover' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->end_controls_tab();

            $this->end_controls_tabs();

            // DELETE BUTTON
            $this->add_control(
				'heading_btn_delete',
				[
					'label' => \esc_html__( 'Eliminar', 'alezux-members' ),
					'type' => Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

            $this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'btn_delete_typography',
					'selector' => '{{WRAPPER}} .alezux-btn-card-delete',
				]
			);

            $this->start_controls_tabs( 'tabs_btn_delete' );

            $this->start_controls_tab(
                'tab_btn_delete_normal',
                [
                    'label' => \esc_html__( 'Normal', 'alezux-members' ),
                ]
            );

            $this->add_control(
                'btn_delete_color',
                [
                    'label' => \esc_html__( 'Texto', 'alezux-members' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .alezux-btn-card-delete' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'btn_delete_bg_color',
                [
                    'label' => \esc_html__( 'Fondo', 'alezux-members' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .alezux-btn-card-delete' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->end_controls_tab();

            $this->start_controls_tab(
                'tab_btn_delete_hover',
                [
                    'label' => \esc_html__( 'Hover', 'alezux-members' ),
                ]
            );

            $this->add_control(
                'btn_delete_color_hover',
                [
                    'label' => \esc_html__( 'Texto', 'alezux-members' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .alezux-btn-card-delete:hover' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'btn_delete_bg_color_hover',
                [
                    'label' => \esc_html__( 'Fondo', 'alezux-members' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .alezux-btn-card-delete:hover' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->end_controls_tab();

            $this->end_controls_tabs();

			$this->end_controls_section();

			// ------------------------------
			// MODAL STYLE (POPUP)
			// ------------------------------
			$this->start_controls_section(
				'section_style_modal',
				[
					'label' => \esc_html__( 'Modal / Popup', 'alezux-members' ),
					'tab'   => Controls_Manager::TAB_STYLE,
				]
			);

            $this->add_control(
				'heading_modal_overlay',
				[
					'label' => \esc_html__( 'Fondo Overlay', 'alezux-members' ),
					'type' => Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

            $this->add_control(
				'modal_overlay_bg',
				[
					'label' => \esc_html__( 'Color', 'alezux-members' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .alezux-modal' => 'background-color: {{VALUE}};',
					],
				]
			);

            $this->add_control(
				'heading_modal_box',
				[
					'label' => \esc_html__( 'Caja de Contenido', 'alezux-members' ),
					'type' => Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

            $this->add_control(
				'modal_box_bg',
				[
					'label' => \esc_html__( 'Color Fondo', 'alezux-members' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .alezux-logro-form-wrapper .alezux-logro-form' => 'background-color: {{VALUE}};',
                        '{{WRAPPER}} .alezux-delete-modal-content' => 'background-color: {{VALUE}};',
					],
				]
			);

            $this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name' => 'modal_box_border',
					'selector' => '{{WRAPPER}} .alezux-logro-form-wrapper .alezux-logro-form, {{WRAPPER}} .alezux-delete-modal-content',
				]
			);

            $this->add_control(
				'modal_box_radius',
				[
					'label' => \esc_html__( 'Radio de Borde', 'alezux-members' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .alezux-logro-form-wrapper .alezux-logro-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        '{{WRAPPER}} .alezux-delete-modal-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

            $this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'modal_box_shadow',
					'selector' => '{{WRAPPER}} .alezux-logro-form-wrapper .alezux-logro-form, {{WRAPPER}} .alezux-delete-modal-content',
				]
			);

            $this->add_control(
				'modal_box_padding',
				[
					'label' => \esc_html__( 'Relleno (Padding)', 'alezux-members' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .alezux-logro-form-wrapper .alezux-logro-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        '{{WRAPPER}} .alezux-delete-modal-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

            $this->add_control(
				'heading_modal_title',
				[
					'label' => \esc_html__( 'Título', 'alezux-members' ),
					'type' => Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

            $this->add_control(
				'modal_title_color',
				[
					'label' => \esc_html__( 'Color', 'alezux-members' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .alezux-logro-form-wrapper h3' => 'color: {{VALUE}} !important;',
                        '{{WRAPPER}} .alezux-delete-modal-content h3' => 'color: {{VALUE}} !important;',
					],
				]
			);

            $this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'modal_title_typography',
					'selector' => '{{WRAPPER}} .alezux-logro-form-wrapper h3, {{WRAPPER}} .alezux-delete-modal-content h3',
				]
			);

            // LABELS
            $this->add_control(
				'heading_modal_labels',
				[
					'label' => \esc_html__( 'Etiquetas (Labels)', 'alezux-members' ),
					'type' => Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

            $this->add_control(
				'modal_label_color',
				[
					'label' => \esc_html__( 'Color Texto', 'alezux-members' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .alezux-logro-form-group label' => 'color: {{VALUE}};',
					],
				]
			);

             $this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'modal_label_typography',
					'selector' => '{{WRAPPER}} .alezux-logro-form-group label',
				]
			);


            $this->add_control(
				'heading_modal_inputs',
				[
					'label' => \esc_html__( 'Campos (Inputs)', 'alezux-members' ),
					'type' => Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

            $this->add_control(
				'modal_input_bg',
				[
					'label' => \esc_html__( 'Fondo Campo', 'alezux-members' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .alezux-logro-input' => 'background-color: {{VALUE}};',
					],
				]
			);

            $this->add_control(
				'modal_input_color',
				[
					'label' => \esc_html__( 'Color Texto', 'alezux-members' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .alezux-logro-input' => 'color: {{VALUE}};',
					],
				]
			);
            
            $this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'modal_input_typography',
					'selector' => '{{WRAPPER}} .alezux-logro-input',
				]
			);

            $this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name' => 'modal_input_border',
					'selector' => '{{WRAPPER}} .alezux-logro-input',
				]
			);

            $this->add_control(
				'modal_input_radius',
				[
					'label' => \esc_html__( 'Radio Borde', 'alezux-members' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .alezux-logro-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

            $this->add_control(
				'modal_input_padding',
				[
					'label' => \esc_html__( 'Relleno (Padding)', 'alezux-members' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .alezux-logro-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

            $this->add_control(
				'heading_modal_submit',
				[
					'label' => \esc_html__( 'Botón Guardar', 'alezux-members' ),
					'type' => Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

            $this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'modal_submit_typography',
					'selector' => '{{WRAPPER}} .alezux-logro-submit',
				]
			);

            $this->start_controls_tabs( 'tabs_modal_submit' );

            $this->start_controls_tab(
                'tab_modal_submit_normal',
                [
                    'label' => \esc_html__( 'Normal', 'alezux-members' ),
                ]
            );

            $this->add_control(
				'modal_submit_bg',
				[
					'label' => \esc_html__( 'Fondo Botón', 'alezux-members' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .alezux-logro-submit' => 'background-color: {{VALUE}};',
					],
				]
			);
            
            $this->add_control(
				'modal_submit_color',
				[
					'label' => \esc_html__( 'Texto Botón', 'alezux-members' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .alezux-logro-submit' => 'color: {{VALUE}};',
					],
				]
			);

            $this->end_controls_tab();

            $this->start_controls_tab(
                'tab_modal_submit_hover',
                [
                    'label' => \esc_html__( 'Hover', 'alezux-members' ),
                ]
            );

             $this->add_control(
				'modal_submit_bg_hover',
				[
					'label' => \esc_html__( 'Fondo Botón', 'alezux-members' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .alezux-logro-submit:hover' => 'background-color: {{VALUE}};',
					],
				]
			);
            
            $this->add_control(
				'modal_submit_color_hover',
				[
					'label' => \esc_html__( 'Texto Botón', 'alezux-members' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .alezux-logro-submit:hover' => 'color: {{VALUE}};',
					],
				]
			);

            $this->end_controls_tab();
            
            $this->end_controls_tabs();

             $this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name' => 'modal_submit_border',
					'selector' => '{{WRAPPER}} .alezux-logro-submit',
					'separator' => 'before',
				]
			);

             $this->add_control(
				'modal_submit_radius',
				[
					'label' => \esc_html__( 'Radio Borde', 'alezux-members' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .alezux-logro-submit' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

             $this->add_control(
				'modal_submit_padding',
				[
					'label' => \esc_html__( 'Relleno', 'alezux-members' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .alezux-logro-submit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

            $this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'modal_submit_shadow',
					'selector' => '{{WRAPPER}} .alezux-logro-submit',
				]
			);

            $this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$limit = ! empty( $settings['items_per_page'] ) ? $settings['items_per_page'] : 20;

		if ( \current_user_can( 'upload_files' ) ) {
			wp_enqueue_media();
		}
		// Obtener todos los cursos para el filtro (sfwd-courses es el de LearnDash)
		$courses = get_posts( [
			'post_type'      => 'sfwd-courses', 
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		] );

		?>
		<div class="alezux-finanzas-app alezux-view-logros-wrapper" data-limit="<?php echo \esc_attr( $limit ); ?>">
			<!-- Header -->
			<div class="alezux-table-header alezux-logros-header">
				<div class="alezux-header-left">
					<h2 class="alezux-table-title"><?php echo \esc_html( $settings['header_title'] ?? 'Logros de Alumnos' ); ?></h2>
					<p class="alezux-table-desc"><?php echo \esc_html( $settings['header_description'] ?? 'Historial de reconocimientos y logros otorgados.' ); ?></p>
				</div>
				<div class="alezux-header-right">
					<div class="alezux-filters-inline">
						<div class="alezux-filter-item">
							<select id="alezux-logro-course-filter">
								<option value=""><?php \esc_html_e( 'Todos los cursos', 'alezux-members' ); ?></option>
								<?php foreach ( $courses as $course ) : ?>
									<option value="<?php echo \esc_attr( $course->ID ); ?>">
										<?php echo \esc_html( $course->post_title ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="alezux-search-wrapper">
							<span class="dashicons dashicons-search search-icon"></span>
							<input type="text" id="alezux-logro-search" class="alezux-table-search-input" placeholder="<?php \esc_attr_e( 'Buscar por mensaje...', 'alezux-members' ); ?>">
							<span class="dashicons dashicons-no-alt alezux-clear-icon"></span>
						</div>
					</div>
				</div>
			</div>

			<?php
			$show_image = ( ! empty( $settings['show_image'] ) && 'yes' === $settings['show_image'] ) ? 'yes' : 'no';
			$image_size = ! empty( $settings['image_resolution'] ) ? $settings['image_resolution'] : 'medium';
			?>

			<!-- Table Wrapper -->
			<div class="alezux-table-wrapper">
				<table class="alezux-finanzas-table alezux-logros-table">
					<thead>
						<tr>
							<th style="width: 300px;"><?php \esc_html_e( 'LOGRO', 'alezux-members' ); ?></th>
							<th><?php \esc_html_e( 'ESTUDIANTE', 'alezux-members' ); ?></th>
							<th style="width: 200px;"><?php \esc_html_e( 'CURSO', 'alezux-members' ); ?></th>
							<th style="width: 150px;"><?php \esc_html_e( 'FECHA', 'alezux-members' ); ?></th>
							<th style="width: 100px; text-align: right;"><?php \esc_html_e( 'ACCIONES', 'alezux-members' ); ?></th>
						</tr>
					</thead>
					<tbody id="alezux-logros-list-container" 
						   data-show-image="<?php echo \esc_attr( $show_image ); ?>"
						   data-image-size="<?php echo \esc_attr( $image_size ); ?>">
						<!-- Filas cargadas vía AJAX -->
						<tr>
							<td colspan="5" style="text-align:center; padding: 40px;">
								<div class="alezux-loading"><?php \esc_html_e( 'Cargando registros...', 'alezux-members' ); ?></div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<!-- Footer: Pagination & Limit -->
			<div class="alezux-table-footer">
				<div id="alezux-logros-pagination-container" class="alezux-pagination alezux-logros-pagination" style="display:none;">
					<!-- La paginación será similar a estudiantes.js pero adaptada -->
					<button id="alezux-load-more-logros" class="alezux-btn alezux-btn-primary" style="display:none;">
						<?php \esc_html_e( 'Cargar más', 'alezux-members' ); ?>
					</button>
				</div>

				<div class="alezux-row-limit-wrapper">
					<span class="alezux-row-limit-label"><?php \esc_html_e( 'Filas:', 'alezux-members' ); ?></span>
					<select id="alezux-logros-limit-select" class="alezux-row-limit-select">
						<option value="10">10</option>
						<option value="20" <?php \selected( 20, $limit ); ?>>20</option>
						<option value="50">50</option>
						<option value="100">100</option>
					</select>
				</div>
			</div>

			<!-- Modal de Edición (Estructura Avanzada) -->
			<div id="alezux-logro-edit-modal" class="alezux-modal" style="display:none;">
				<div class="alezux-modal-content alezux-logro-form-wrapper" style="max-width: 600px; padding: 0;">
					<div class="alezux-logro-form" style="box-shadow: none; border: none; margin: 0;">
						<span class="alezux-modal-close" style="position: absolute; right: 20px; top: 15px; z-index: 100;">&times;</span>
						<h3 style="margin-top: 0; margin-bottom: 20px; text-align: center; color: #333;"><?php esc_html_e( 'Editar Logro', 'alezux-members' ); ?></h3>
						
						<form id="alezux-logro-edit-form">
							<input type="hidden" id="edit-logro-id" name="id">
							
							<!-- Course -->
							<div class="alezux-logro-form-group">
								<label for="edit-course-id"><?php esc_html_e( 'Curso', 'alezux-members' ); ?></label>
								<select id="edit-course-id" name="course_id" class="alezux-logro-input" required>
									<option value=""><?php esc_html_e( 'Seleccionar Curso', 'alezux-members' ); ?></option>
									<?php foreach ( $courses as $course ) : ?>
										<option value="<?php echo esc_attr( $course->ID ); ?>">
											<?php echo esc_html( $course->post_title ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>

							<!-- Student -->
							<div class="alezux-logro-form-group">
								<label for="edit-student-id"><?php esc_html_e( 'ID Estudiante', 'alezux-members' ); ?></label>
								<input type="number" id="edit-student-id" name="student_id" class="alezux-logro-input">
							</div>

							<!-- Message -->
							<div class="alezux-logro-form-group">
								<label for="edit-message"><?php esc_html_e( 'Mensaje', 'alezux-members' ); ?></label>
								<textarea id="edit-message" name="message" class="alezux-logro-input" rows="4" required></textarea>
							</div>

							<!-- Image Upload -->
							<div class="alezux-logro-form-group">
								<label style="display:block; margin-bottom:8px; font-weight:600;"><?php esc_html_e( 'Imagen', 'alezux-members' ); ?></label>
								<div class="alezux-logro-upload-container">
									<input type="hidden" id="edit-image-id" name="image_id" class="alezux-logro-image-id" value="">
									
									<div class="alezux-upload-box">
										<!-- Placeholder State -->
										<div class="alezux-upload-placeholder">
											<div class="alezux-upload-icon">
												<i class="fas fa-cloud-upload-alt" aria-hidden="true"></i>
											</div>
											<div class="alezux-upload-title">
												<?php esc_html_e( 'Elige un archivo', 'alezux-members' ); ?>
											</div>
											<div class="alezux-upload-button-wrapper">
												<span class="alezux-upload-btn-fake"><?php esc_html_e( 'Buscar Archivo', 'alezux-members' ); ?></span>
											</div>
										</div>

										<!-- Preview State -->
										<div class="alezux-upload-preview" style="display: none;">
											<img class="alezux-preview-img" src="" alt="Preview">
											<span class="alezux-remove-img" title="Eliminar"><i class="eicon-close"></i></span>
										</div>
									</div>
								</div>
							</div>

							<div class="alezux-logro-form-actions">
								<button type="submit" class="alezux-logro-submit">
									<?php esc_html_e( 'Guardar Cambios', 'alezux-members' ); ?>
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>

			<!-- Modal de Eliminación Personalizado -->
			<div id="alezux-delete-modal" class="alezux-modal" style="display:none;">
				<div class="alezux-modal-content alezux-delete-modal-content">
					<div class="alezux-delete-icon">
						<i class="fas fa-trash-alt"></i>
					</div>
					<h3><?php esc_html_e( '¿Estás seguro?', 'alezux-members' ); ?></h3>
					<p><?php esc_html_e( 'Esta acción eliminará el logro permanentemente. No se puede deshacer.', 'alezux-members' ); ?></p>
					
					<div class="alezux-delete-actions">
						<button class="alezux-btn alezux-btn-cancel alezux-modal-close-btn"><?php esc_html_e( 'Cancelar', 'alezux-members' ); ?></button>
						<button id="alezux-confirm-delete-btn" class="alezux-btn alezux-btn-danger-confirm"><?php esc_html_e( 'Sí, Eliminar', 'alezux-members' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
