<?php
namespace Alezux_Members\Modules\Formaciones\Includes;

use Alezux_Members\Core\Elementor_Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elementor_Widget_Topics extends Elementor_Widget_Base {

	public function get_name() {
		return 'alezux_topics_list';
	}

	public function get_title() {
		return __( 'Lista de Clases (Topics)', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-bullet-list';
	}

	public function get_categories() {
		return [ 'alezux-lms' ];
	}

	protected function register_widget_controls() {
		
		// --- Sección de Contenido ---
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Contenido', 'alezux-members' ),
			]
		);

		$this->add_control(
			'show_header',
			[
				'label' => __( 'Mostrar Cabecera (Lección)', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Sí', 'alezux-members' ),
				'label_off' => __( 'No', 'alezux-members' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

		// --- Sección de Estilo: Cabecera ---
		$this->start_controls_section(
			'section_style_header',
			[
				'label' => __( 'Cabecera', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_header' => 'yes',
				],
			]
		);

		$this->add_control(
			'header_bg_color',
			[
				'label' => __( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-topics-header' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'header_title_color',
			[
				'label' => __( 'Color Título', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .alezux-topics-header-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'header_title_typography',
				'label' => __( 'Tipografía Título', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-topics-header-title',
			]
		);

		$this->add_control(
			'progress_bar_heading',
			[
				'label' => __( 'Barra de Progreso', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'progress_bar_width',
			[
				'label' => __( 'Ancho Barra (Width)', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'%' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 100,
					'unit' => '%',
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-topics-progress-bar' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'progress_bar_height',
			[
				'label' => __( 'Alto Barra', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 20,
					],
				],
				'default' => [
					'size' => 6,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-topics-progress-bar' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'show_progress_percentage',
			[
				'label' => __( 'Mostrar Porcentaje (%)', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Sí', 'alezux-members' ),
				'label_off' => __( 'No', 'alezux-members' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->add_control(
			'progress_percentage_color',
			[
				'label' => __( 'Color Porcentaje', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .alezux-topics-progress-percentage' => 'color: {{VALUE}};',
				],
				'condition' => [
					'show_progress_percentage' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'progress_percentage_typography',
				'label' => __( 'Tipografía Porcentaje', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-topics-progress-percentage',
				'condition' => [
					'show_progress_percentage' => 'yes',
				],
			]
		);

		$this->add_control(
			'progress_bar_radius',
			[
				'label' => __( 'Radio Borde', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-topics-progress-bar' => 'border-radius: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .alezux-topics-progress-bar-fill' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'progress_bar_bg_color',
			[
				'label' => __( 'Color Fondo (Contenedor)', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#333333',
				'selectors' => [
					'{{WRAPPER}} .alezux-topics-progress-bar' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'progress_bar_fill',
				'label' => __( 'Fondo Barra (Relleno)', 'alezux-members' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .alezux-topics-progress-bar-fill',
			]
		);

		$this->end_controls_section();

		// --- Sección de Estilo: Lista ---
		$this->start_controls_section(
			'section_style_list',
			[
				'label' => __( 'Lista de Topics', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'list_gap',
			[
				'label' => __( 'Espacio entre Elementos', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-topics-list' => 'gap: {{SIZE}}{{UNIT}};',
					// Fallback for older browsers if flex gap not supported, though unlikely needed
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'item_bg_color',
			[
				'label' => __( 'Color de Fondo Item', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => 'transparent',
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-item' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_bg_color_active',
			[
				'label' => __( 'Color de Fondo Activo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-item.is-active' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'separator_heading',
			[
				'label' => __( 'Separador', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'item_border_style',
			[
				'label' => __( 'Estilo de Borde', 'alezux-members' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'solid',
				'options' => [
					'none' => __( 'Ninguno', 'alezux-members' ),
					'solid' => __( 'Sólido', 'alezux-members' ),
					'double' => __( 'Doble', 'alezux-members' ),
					'dotted' => __( 'Punteado', 'alezux-members' ),
					'dashed' => __( 'Discontinuo', 'alezux-members' ),
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-item' => 'border-bottom-style: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_border_color',
			[
				'label' => __( 'Color de Separador', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#333333',
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-item' => 'border-bottom-color: {{VALUE}};',
				],
				'condition' => [
					'item_border_style!' => 'none',
				],
			]
		);

		$this->add_responsive_control(
			'item_border_width',
			[
				'label' => __( 'Ancho de Separador', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'max' => 10,
					],
				],
				'default' => [
					'size' => 1,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-item' => 'border-bottom-width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'item_border_style!' => 'none',
				],
			]
		);

		$this->add_responsive_control(
			'item_padding',
			[
				'label' => __( 'Relleno Item (Padding)', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		// --- Sección de Estilo: Indicadores ---
		$this->start_controls_section(
			'section_style_indicators',
			[
				'label' => __( 'Indicadores (Estado)', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'active_bar_heading',
			[
				'label' => __( 'Barra Activa (Izquierda)', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'active_indicator_color',
			[
				'label' => __( 'Color Barra Activa', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#00b894',
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-item.is-active::before' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'active_indicator_width',
			[
				'label' => __( 'Ancho Barra', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'max' => 10,
					],
				],
				'default' => [
					'size' => 4,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-item::before' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);
		
		$this->add_responsive_control(
			'active_indicator_height',
			[
				'label' => __( 'Alto Barra (%)', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 70,
					'unit' => '%',
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-item::before' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'active_indicator_offset',
			[
				'label' => __( 'Desplazamiento Horizontal', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => -50,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-item::before' => 'left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'active_indicator_border_radius',
			[
				'label' => __( 'Radio de Borde Barra', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-item::before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'active_indicator_border',
				'selector' => '{{WRAPPER}} .alezux-topic-item::before',
			]
		);

		$this->add_control(
			'checkmark_heading',
			[
				'label' => __( 'Icono de Estado (Check)', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		// Normal / Incomplete (Though usually hidden if not completed/active, sticking to design implies it might be visible or just completed)
		// For simplicity, let's control "Completed" and "Active" states mostly.
		
		$this->start_controls_tabs( 'tabs_checkmark_style' );

		$this->start_controls_tab(
			'tab_checkmark_completed',
			[
				'label' => __( 'Completado', 'alezux-members' ),
			]
		);

		$this->add_control(
			'checkmark_bg_completed',
			[
				'label' => __( 'Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-check.completed' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'checkmark_icon_color_completed',
			[
				'label' => __( 'Color Icono', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#1a1a1a',
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-check.completed svg' => 'stroke: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'checkmark_border_color_completed',
			[
				'label' => __( 'Borde', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-check.completed' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_checkmark_active',
			[
				'label' => __( 'Activo', 'alezux-members' ),
			]
		);

		$this->add_control(
			'checkmark_bg_active',
			[
				'label' => __( 'Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#2ecc71',
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-item.is-active .alezux-topic-check' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'checkmark_icon_color_active',
			[
				'label' => __( 'Color Icono', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-item.is-active .alezux-topic-check svg' => 'stroke: {{VALUE}};',
				],
			]
		);
		
		$this->add_control(
			'checkmark_border_color_active',
			[
				'label' => __( 'Borde', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-item.is-active .alezux-topic-check' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();
		
		$this->add_responsive_control(
			'checkmark_size',
			[
				'label' => __( 'Tamaño Total', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-check' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important; min-width: {{SIZE}}{{UNIT}} !important;',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'checkmark_icon_size',
			[
				'label' => __( 'Tamaño Icono (Interno)', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 5,
						'max' => 30,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-check svg' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important; min-width: {{SIZE}}{{UNIT}} !important; max-width: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);



		$this->end_controls_section();

		// --- Sección de Estilo: Contenido Item ---
		$this->start_controls_section(
			'section_style_item_content',
			[
				'label' => __( 'Contenido del Item', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'item_title_color',
			[
				'label' => __( 'Color Título', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'item_title_typography',
				'selector' => '{{WRAPPER}} .alezux-topic-title',
			]
		);

		$this->add_control(
			'item_author_color',
			[
				'label' => __( 'Color Autor', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#a0a0a0',
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-author' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'item_author_typography',
				'selector' => '{{WRAPPER}} .alezux-topic-author',
			]
		);

		$this->add_responsive_control(
			'image_width',
			[
				'label' => __( 'Ancho Imagen', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 20,
						'max' => 200,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-thumbnail' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'image_border_radius',
			[
				'label' => __( 'Radio Borde Imagen', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-thumbnail img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .alezux-topic-thumbnail' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_margin',
			[
				'label' => __( 'Margen Imagen', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default' => [
					'top' => 0,
					'right' => 20,
					'bottom' => 0,
					'left' => 0,
					'unit' => 'px',
					'isLinked' => false,
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-topic-thumbnail-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// --- Sección de Estilo: Contenedor Principal ---
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => __( 'Contenedor del Widget', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'container_bg_color',
			[
				'label' => __( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#121212',
				'selectors' => [
					'{{WRAPPER}} .alezux-topics-widget' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'container_border',
				'selector' => '{{WRAPPER}} .alezux-topics-widget',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'container_border_radius',
			[
				'label' => __( 'Radio de Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default' => [
					'top' => 15,
					'right' => 15,
					'bottom' => 15,
					'left' => 15,
					'unit' => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-topics-widget' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => __( 'Relleno (Padding)', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default' => [
					'top' => 20,
					'right' => 20,
					'bottom' => 20,
					'left' => 20,
					'unit' => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-topics-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'container_box_shadow',
				'selector' => '{{WRAPPER}} .alezux-topics-widget',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$current_id = get_the_ID();
		if ( ! $current_id ) {
			return;
		}

		$post_type = get_post_type( $current_id );
		
		$lesson_id = 0;
		$current_topic_id = 0;

		if ( 'sfwd-lessons' === $post_type ) {
			$lesson_id = $current_id;
		} elseif ( 'sfwd-topic' === $post_type ) {
			$current_topic_id = $current_id;
			if ( function_exists( 'learndash_get_setting' ) ) {
				$lesson_id = learndash_get_setting( $current_id, 'lesson' );
			}
			// Fallback si learndash_get_setting no devuelve el ID directamente o formato antiguo
			if ( ! $lesson_id && function_exists( 'learndash_get_course_id' ) ) {
				// Esto devuelve el curso, no la lección directamente si no está vinculada.
				// Mejor usar get_post_meta para course_id y luego buscar. 
				// Pero en LearnDash moderno 'lesson_id' suele ser meta 'lesson_id' o 'course_id'.
				$lesson_id = get_post_meta( $current_id, 'lesson_id', true );
			}
		} else {
			// Si estamos en el editor de Elementor y no es contexto de LD, mostrar placeholder
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="alezux-elementor-placeholder" style="color:white; padding: 20px; background: #333;">Este widget solo funciona en contextos de Lección o Topic de LearnDash.</div>';
				return;
			}
			return;
		}

		if ( ! $lesson_id ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="alezux-elementor-placeholder" style="color:white;">No se pudo determinar la Lección actual.</div>';
			}
			return;
		}

		// Obtener Topics
		$topics = [];
		if ( function_exists( 'learndash_get_topic_list' ) ) {
			$topics = learndash_get_topic_list( $lesson_id );
		}

		if ( empty( $topics ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="alezux-elementor-placeholder" style="color:white;">Esta lección no tiene Topics (Clases).</div>';
			}
			return; // No mostrar nada si no hay topics
		}

		// Datos de la Lección para cabecera
		$lesson_title = get_the_title( $lesson_id );
		
		// Progreso (Placeholder lógica básica, LD tiene funciones complejas para esto)
		// Para simplificar, obtenemos total y completados
		$total_topics = count( $topics );
		$completed_count = 0;
		$user_id = get_current_user_id();

		if ( $user_id && function_exists( 'learndash_is_topic_complete' ) ) {
			foreach ( $topics as $topic ) {
				if ( learndash_is_topic_complete( $user_id, $topic->ID ) ) {
					$completed_count++;
				}
			}
		}

		?>
		<div class="alezux-topics-widget">
			<?php if ( 'yes' === $settings['show_header'] ) : ?>
			<div class="alezux-topics-header">
				<div class="alezux-topics-header-content">
					<h3 class="alezux-topics-header-title"><?php echo esc_html( $lesson_title ); ?></h3>
					<?php
					$progress_percentage = 0;
					if ( $total_topics > 0 ) {
						$progress_percentage = ( $completed_count / $total_topics ) * 100;
					}
					?>
					<div class="alezux-topics-progress-bar">
						<div class="alezux-topics-progress-bar-fill" style="width: <?php echo esc_attr( $progress_percentage ); ?>%;"></div>
					</div>
					<?php if ( 'yes' === $settings['show_progress_percentage'] ) : ?>
						<div class="alezux-topics-progress-percentage">
							<?php echo esc_html( round( $progress_percentage ) . '%' ); ?>
						</div>
					<?php endif; ?>
				</div>
				<div class="alezux-topics-header-icon">
					<i class="eicon-chevron-down"></i>
				</div>
			</div>
			<?php endif; ?>

			<div class="alezux-topics-list">
				<?php foreach ( $topics as $topic ) : 
					$is_active = ( $current_topic_id === $topic->ID ) ? 'is-active' : '';
					$user_id = get_current_user_id();
					$is_completed = ( $user_id && function_exists( 'learndash_is_topic_complete' ) ) ? learndash_is_topic_complete( $user_id, $topic->ID ) : false;
					$permalink = get_permalink( $topic->ID );
					$title = get_the_title( $topic->ID );
					$author_id = $topic->post_author;
					$author_name = get_the_author_meta( 'display_name', $author_id );
					
					// Thumbnail
					$has_thumbnail = has_post_thumbnail( $topic->ID );
					$thumbnail_url = '';
					if ( $has_thumbnail ) {
						$thumbnail_url = get_the_post_thumbnail_url( $topic->ID, 'medium' );
					}
				?>
				<a href="<?php echo esc_url( $permalink ); ?>" class="alezux-topic-item <?php echo esc_attr( $is_active ); ?> <?php echo $is_completed ? 'is-completed' : ''; ?>">
					
					<div class="alezux-topic-thumbnail-wrapper">
						<!-- Checkmark Icon (Inside wrapper for absolute positioning) -->
						<div class="alezux-topic-check <?php echo $is_completed ? 'completed' : ''; ?>" style="width: 24px; height: 24px; min-width: 24px;">
							<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" style="width: 12px; height: 12px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
								<polyline points="20 6 9 17 4 12"></polyline>
							</svg>
						</div>

						<?php if ( $has_thumbnail ) : ?>
							<div class="alezux-topic-thumbnail">
								<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php echo esc_attr( $title ); ?>">
							</div>
						<?php else : ?>
							<div class="alezux-topic-thumbnail placeholder"></div>
						<?php endif; ?>
					</div>
					
					<div class="alezux-topic-info">
						<h4 class="alezux-topic-title"><?php echo esc_html( $title ); ?></h4>
						<span class="alezux-topic-author"><?php echo esc_html__( 'Autor:', 'alezux-members' ); ?> <?php echo esc_html( $author_name ); ?></span>
					</div>
				</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
