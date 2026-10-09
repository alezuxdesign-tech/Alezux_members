<?php
namespace Alezux_Members\Modules\Slide_Lesson\Widgets;


use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Slide_Lesson_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux-slide-lesson';
	}

	public function get_title() {
		return esc_html__( 'Slide Lesson', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-slides';
	}

	public function get_categories() {
		return [ 'alezux-lms' ];
	}

	public function get_script_depends() {
		return [ 'alezux-slide-lesson-js' ];
	}

	public function get_style_depends() {
		return [ 'alezux-slide-lesson-css' ];
	}

	protected function register_controls() {
		// --- Sección de Contenido ---
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Contenido', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'limit',
			[
				'label' => esc_html__( 'Límite de Lecciones', 'alezux-members' ),
				'type' => Controls_Manager::NUMBER,
				'min' => -1,
				'max' => 50,
				'step' => 1,
				'default' => -1,
				'description' => esc_html__( 'Número de lecciones a mostrar (-1 para todas).', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

		// --- Sección de Estilo: Contenedor Slider ---
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Contenedor Slider', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => esc_html__( 'Relleno (Padding)', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-lesson-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'container_background',
				'label' => esc_html__( 'Fondo', 'alezux-members' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .alezux-slide-lesson-container',
			]
		);

		$this->end_controls_section();

		// --- Sección de Estilo: Tarjeta (Card) ---
		$this->start_controls_section(
			'section_style_card',
			[
				'label' => esc_html__( 'Tarjeta de Lección', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'card_width',
			[
				'label' => esc_html__( 'Ancho de Tarjeta', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 500,
						'step' => 5,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 300,
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-item' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'card_border_radius',
			[
				'label' => esc_html__( 'Radio del Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'card_border',
				'label' => esc_html__( 'Borde', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-slide-item',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_box_shadow',
				'label' => esc_html__( 'Sombra de Caja', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-slide-item',
			]
		);
		
		$this->add_control(
			'card_bg_color',
			[
				'label' => esc_html__( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-item' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// --- Sección de Estilo: Título ---
		$this->start_controls_section(
			'section_style_title',
			[
				'label' => esc_html__( 'Título', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Color del Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => esc_html__( 'Tipografía', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-slide-title',
			]
		);
		
		$this->add_control(
			'title_bg_gradient',
			[
				'label' => esc_html__( 'Fondo (Gradiente)', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'description' => 'Color base para la superposición del titulo',
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-content' => 'background: linear-gradient(to top, {{VALUE}} 0%, rgba(0,0,0,0) 100%);',
				],
			]
		);

		$this->end_controls_section();

		// --- Sección de Estilo: Título de Grupo (Separador) ---
		$this->start_controls_section(
			'section_style_group_title',
			[
				'label' => esc_html__( 'Título de Grupo', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'group_title_color',
			[
				'label' => esc_html__( 'Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-group-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'group_title_typography',
				'label' => esc_html__( 'Tipografía', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-slide-group-title',
			]
		);

		$this->end_controls_section();


		// --- Sección de Estilo: Navegación Avanzada ---
		$this->start_controls_section(
			'section_style_nav',
			[
				'label' => esc_html__( 'Navegación', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'show_arrows',
			[
				'label' => esc_html__( 'Mostrar Flechas', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Sí', 'alezux-members' ),
				'label_off' => esc_html__( 'No', 'alezux-members' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'nav_position_mode',
			[
				'label' => esc_html__( 'Modo de Posición', 'alezux-members' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => esc_html__( 'Por Defecto', 'alezux-members' ),
					'custom'  => esc_html__( 'Personalizado (Avanzado)', 'alezux-members' ),
				],
				'condition' => [ 'show_arrows' => 'yes' ],
			]
		);

		// --- Configuración Flecha Anterior ---
		$this->add_control(
			'heading_prev_arrow',
			[
				'label' => esc_html__( 'Flecha Anterior', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ 'show_arrows' => 'yes' ],
			]
		);

		$this->add_control(
			'prev_arrow_icon',
			[
				'label' => esc_html__( 'Icono', 'alezux-members' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-chevron-left',
					'library' => 'fa-solid',
				],
				'condition' => [ 'show_arrows' => 'yes' ],
			]
		);

		// Estilos Normal/Hover (Color/Bg)
		$this->start_controls_tabs( 'tabs_prev_arrow_style', [ 'condition' => [ 'show_arrows' => 'yes' ] ] );

		$this->start_controls_tab(
			'tab_prev_arrow_normal',
			[
				'label' => esc_html__( 'Normal', 'alezux-members' ),
			]
		);

		$this->add_control(
			'prev_arrow_color',
			[
				'label' => esc_html__( 'Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav-prev' => '--alezux-nav-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'prev_arrow_bg_color',
			[
				'label' => esc_html__( 'Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav-prev' => '--alezux-nav-bg: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_prev_arrow_hover',
			[
				'label' => esc_html__( 'Hover', 'alezux-members' ),
			]
		);

		$this->add_control(
			'prev_arrow_color_hover',
			[
				'label' => esc_html__( 'Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav-prev:hover' => '--alezux-nav-color-hover: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'prev_arrow_bg_color_hover',
			[
				'label' => esc_html__( 'Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav-prev:hover' => '--alezux-nav-bg-hover: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Posicionamiento Previous - Lógica Robusta
		// Selector de Orientación
		$this->add_control(
			'prev_horizontal_orientation',
			[
				'label' => esc_html__( 'Posición Horizontal', 'alezux-members' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Izquierda', 'alezux-members' ),
						'icon' => 'eicon-h-align-left',
					],
					'right' => [
						'title' => esc_html__( 'Derecha', 'alezux-members' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'default' => 'right',
				'toggle' => false,
				'default' => 'right',
				'toggle' => false,
				'condition' => [ 
					'show_arrows' => 'yes',
					'nav_position_mode' => 'custom',
				],
			]
		);

		// Offset Izquierda (Solo visible si left)
		$this->add_responsive_control(
			'prev_offset_left',
			[
				'label' => esc_html__( 'Offset Izquierda', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [ 'min' => -200, 'max' => 500 ],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav-prev' => '--alezux-nav-left: {{SIZE}}{{UNIT}}; --alezux-nav-right: auto;',
				],
				'condition' => [ 
					'show_arrows' => 'yes',
					'prev_horizontal_orientation' => 'left',
					'nav_position_mode' => 'custom',
				],
			]
		);

		// Offset Derecha (Solo visible si right)
		$this->add_responsive_control(
			'prev_offset_right',
			[
				'label' => esc_html__( 'Offset Derecha', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 60,
					'unit' => 'px',
				],
				'range' => [
					'px' => [ 'min' => -200, 'max' => 500 ],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav-prev' => '--alezux-nav-right: {{SIZE}}{{UNIT}}; --alezux-nav-left: auto;',
				],
				'condition' => [ 
					'show_arrows' => 'yes',
					'prev_horizontal_orientation' => 'right',
					'nav_position_mode' => 'custom',
				],
			]
		);

		$this->add_control(
			'prev_vertical_orientation',
			[
				'label' => esc_html__( 'Posición Vertical', 'alezux-members' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'top' => [
						'title' => esc_html__( 'Arriba', 'alezux-members' ),
						'icon' => 'eicon-v-align-top',
					],
					'bottom' => [
						'title' => esc_html__( 'Abajo', 'alezux-members' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'default' => 'top',
				'toggle' => false,
				'condition' => [ 
					'show_arrows' => 'yes',
					'nav_position_mode' => 'custom',
				],
			]
		);

		// Offset Arriba
		$this->add_responsive_control(
			'prev_offset_top',
			[
				'label' => esc_html__( 'Offset Arriba', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => -60,
					'unit' => 'px',
				],
				'range' => [
					'px' => [ 'min' => -200, 'max' => 200 ],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav-prev' => '--alezux-nav-top: {{SIZE}}{{UNIT}}; --alezux-nav-bottom: auto;',
				],
				'condition' => [
					'show_arrows' => 'yes',
					'prev_vertical_orientation' => 'top',
					'nav_position_mode' => 'custom',
				],
			]
		);

		// Offset Abajo
		$this->add_responsive_control(
			'prev_offset_bottom',
			[
				'label' => esc_html__( 'Offset Abajo', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [ 'min' => -200, 'max' => 200 ],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav-prev' => '--alezux-nav-bottom: {{SIZE}}{{UNIT}}; --alezux-nav-top: auto;',
				],
				'condition' => [
					'show_arrows' => 'yes',
					'prev_vertical_orientation' => 'bottom',
					'nav_position_mode' => 'custom',
				],
			]
		);


		// --- Configuración Flecha Siguiente ---
		$this->add_control(
			'heading_next_arrow',
			[
				'label' => esc_html__( 'Flecha Siguiente', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ 'show_arrows' => 'yes' ],
			]
		);

		$this->add_control(
			'next_arrow_icon',
			[
				'label' => esc_html__( 'Icono', 'alezux-members' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-chevron-right',
					'library' => 'fa-solid',
				],
				'condition' => [ 'show_arrows' => 'yes' ],
			]
		);

		$this->start_controls_tabs( 'tabs_next_arrow_style', [ 'condition' => [ 'show_arrows' => 'yes' ] ] );

		$this->start_controls_tab(
			'tab_next_arrow_normal',
			[
				'label' => esc_html__( 'Normal', 'alezux-members' ),
			]
		);

		$this->add_control(
			'next_arrow_color',
			[
				'label' => esc_html__( 'Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav-next' => '--alezux-nav-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'next_arrow_bg_color',
			[
				'label' => esc_html__( 'Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav-next' => '--alezux-nav-bg: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_next_arrow_hover',
			[
				'label' => esc_html__( 'Hover', 'alezux-members' ),
			]
		);

		$this->add_control(
			'next_arrow_color_hover',
			[
				'label' => esc_html__( 'Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav-next:hover' => '--alezux-nav-color-hover: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'next_arrow_bg_color_hover',
			[
				'label' => esc_html__( 'Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav-next:hover' => '--alezux-nav-bg-hover: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Posicionamiento Next - Lógica Robusta
		$this->add_control(
			'next_horizontal_orientation',
			[
				'label' => esc_html__( 'Posición Horizontal', 'alezux-members' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Izquierda', 'alezux-members' ),
						'icon' => 'eicon-h-align-left',
					],
					'right' => [
						'title' => esc_html__( 'Derecha', 'alezux-members' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'default' => 'right',
				'toggle' => false,
				'condition' => [ 
					'show_arrows' => 'yes',
					'nav_position_mode' => 'custom',
				],
			]
		);

		// Offset Izquierda Next
		$this->add_responsive_control(
			'next_offset_left',
			[
				'label' => esc_html__( 'Offset Izquierda', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [ 'min' => -200, 'max' => 500 ],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav-next' => '--alezux-nav-left: {{SIZE}}{{UNIT}}; --alezux-nav-right: auto;',
				],
				'condition' => [ 
					'show_arrows' => 'yes',
					'next_horizontal_orientation' => 'left',
					'nav_position_mode' => 'custom',
				],
			]
		);

		// Offset Derecha Next
		$this->add_responsive_control(
			'next_offset_right',
			[
				'label' => esc_html__( 'Offset Derecha', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 10,
					'unit' => 'px',
				],
				'range' => [
					'px' => [ 'min' => -200, 'max' => 500 ],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav-next' => '--alezux-nav-right: {{SIZE}}{{UNIT}}; --alezux-nav-left: auto;',
				],
				'condition' => [ 
					'show_arrows' => 'yes',
					'next_horizontal_orientation' => 'right',
					'nav_position_mode' => 'custom',
				],
			]
		);

		$this->add_control(
			'next_vertical_orientation',
			[
				'label' => esc_html__( 'Posición Vertical', 'alezux-members' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'top' => [
						'title' => esc_html__( 'Arriba', 'alezux-members' ),
						'icon' => 'eicon-v-align-top',
					],
					'bottom' => [
						'title' => esc_html__( 'Abajo', 'alezux-members' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'default' => 'top',
				'toggle' => false,
				'condition' => [ 
					'show_arrows' => 'yes',
					'nav_position_mode' => 'custom',
				],
			]
		);

		// Offset Arriba Next
		$this->add_responsive_control(
			'next_offset_top',
			[
				'label' => esc_html__( 'Offset Arriba', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => -60,
					'unit' => 'px',
				],
				'range' => [
					'px' => [ 'min' => -200, 'max' => 200 ],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav-next' => '--alezux-nav-top: {{SIZE}}{{UNIT}}; --alezux-nav-bottom: auto;',
				],
				'condition' => [ 
					'show_arrows' => 'yes',
					'next_vertical_orientation' => 'top',
					'nav_position_mode' => 'custom',
				],

			]
		);

		// Offset Abajo Next
		$this->add_responsive_control(
			'next_offset_bottom',
			[
				'label' => esc_html__( 'Offset Abajo', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [ 'min' => -200, 'max' => 200 ],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav-next' => '--alezux-nav-bottom: {{SIZE}}{{UNIT}}; --alezux-nav-top: auto;',
				],
				'condition' => [
					'show_arrows' => 'yes',
					'next_vertical_orientation' => 'bottom',
					'nav_position_mode' => 'custom',
				],
			]
		);
		
		// Estilos comunes para las flechas (tamaño, borde)
		$this->add_control(
			'heading_nav_style',
			[
				'label' => esc_html__( 'Estilo General de Flechas', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ 'show_arrows' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'nav_size',
			[
				'label' => esc_html__( 'Tamaño del Botón', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 20,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav' => '--alezux-nav-size: {{SIZE}}{{UNIT}};',
				],
				'condition' => [ 'show_arrows' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'nav_icon_size',
			[
				'label' => esc_html__( 'Tamaño del Icono', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav' => '--alezux-nav-icon-size: {{SIZE}}{{UNIT}};',
				],
				'condition' => [ 'show_arrows' => 'yes' ],
			]
		);

		$this->add_control(
			'nav_border_radius',
			[
				'label' => esc_html__( 'Radio del Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-slide-nav' => '--alezux-nav-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [ 'show_arrows' => 'yes' ],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'nav_border',
				'label' => esc_html__( 'Borde', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-slide-nav',
				'condition' => [ 'show_arrows' => 'yes' ],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'nav_box_shadow',
				'label' => esc_html__( 'Sombra de Caja', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-slide-nav',
				'condition' => [ 'show_arrows' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$limit = $settings['limit'];

		// Lógica corregida para LearnDash
		// Obtener ID del curso actual
		$course_id = learndash_get_course_id();
		
		// Obtener las lecciones en el orden exacto del Course Builder
		if ( ! empty( $course_id ) && function_exists( 'learndash_course_get_steps_by_type' ) ) {
			$lesson_ids = learndash_course_get_steps_by_type( $course_id, 'sfwd-lessons' );
			
			if ( ! empty( $lesson_ids ) ) {
				$args = [
					'post_type'      => 'sfwd-lessons',
					'post__in'       => $lesson_ids,
					'orderby'        => 'post__in', // Mantener el orden del Builder
					'posts_per_page' => intval( $limit ),
					'post_status'    => 'publish',
				];
				$query = new \WP_Query( $args );
			} else {
				// No hay lecciones
				$query = new \WP_Query( [ 'post__in' => [0] ] ); 
			}
		} else {
			// Fallback si no hay course_id (ej. vista previa en Elementor general)
			$args = [
				'post_type'      => 'sfwd-lessons',
				'posts_per_page' => intval( $limit ),
				'post_status'    => 'publish',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			];
			if ( ! empty( $course_id ) ) {
				$args['meta_key']   = 'course_id';
				$args['meta_value'] = $course_id;
			}
			$query = new \WP_Query( $args );
		}
		$lessons = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$lesson_id = get_the_ID();
				
				// Enlace base: Lección
				$permalink = ! empty( $course_id ) ? learndash_get_step_permalink( $lesson_id, $course_id ) : get_permalink( $lesson_id );

				// Intentar obtener topics para enlazar al primero
				if ( ! empty( $course_id ) && function_exists( 'learndash_get_topic_list' ) ) {
					// query_type = post_ids optimiza si solo necesitamos IDs, pero default objects está bien para safety
					$topics = learndash_get_topic_list( $lesson_id, $course_id );
					
					if ( ! empty( $topics ) && is_array( $topics ) ) {
						// El primer elemento es el primer topic ordenado
						$first_topic = $topics[0];
						$first_topic_id = is_object( $first_topic ) ? $first_topic->ID : ( is_numeric( $first_topic ) ? $first_topic : 0 );
						
						if ( $first_topic_id ) {
							$permalink = learndash_get_step_permalink( $first_topic_id, $course_id );
						}
					}
				}
				
				$is_locked = false;
                if ( class_exists( '\Alezux_Members\Modules\Finanzas\Includes\Access_Control' ) ) {
                    $is_locked = \Alezux_Members\Modules\Finanzas\Includes\Access_Control::is_post_locked( $lesson_id );
                }

				$lessons[] = [
					'title'     => get_the_title(),
					'permalink' => $permalink,
					'image_url' => get_the_post_thumbnail_url( $lesson_id, 'full' ),
                    'is_locked' => $is_locked
				];
			}
			wp_reset_postdata();
		}

		// Agrupar lecciones por separadores
		// Regex para detectar [Separador (Titulo: ...)]
		$slide_groups = [];
		$current_group = [
			'title' => '',
			'lessons' => [],
		];

		foreach ( $lessons as $lesson ) {
			// Decodificar entidades HTML para asegurar que [ ] ( ) y caracteres especiales se lean bien
			$decoded_title = html_entity_decode( $lesson['title'], ENT_QUOTES | ENT_HTML5 );

			if ( preg_match( '/\[\s*Separador\s*\(\s*T[ií]tulo\s*:\s*(.*?)\s*\)\s*\]/iu', $decoded_title, $matches ) ) {
				// Si el grupo actual tiene lecciones, lo guardamos
				if ( ! empty( $current_group['lessons'] ) ) {
					$slide_groups[] = $current_group;
				}
				// Iniciar nuevo grupo con el título extraído
				$current_group = [
					'title'   => isset( $matches[1] ) ? trim( $matches[1] ) : '',
					'lessons' => [],
				];
			} else {
				// Es una lección normal, agregar al grupo actual
				$current_group['lessons'][] = $lesson;
			}
		}

		// Agregar el último grupo si tiene lecciones
		if ( ! empty( $current_group['lessons'] ) ) {
			$slide_groups[] = $current_group;
		}

		// Reutilizar la vista existente
		// Usar __FILE__ para evitar ambigüedades con __DIR__
		// plugin_dir_path( __FILE__ ) devuelve .../widgets/
		// Subimos un nivel para llegar a .../modules/slide-lesson/
		$view_path = plugin_dir_path( __FILE__ ) . '../views/slide-lesson.php';
		
		if ( file_exists( $view_path ) ) {
			// Wrapper específico para estilo y aislamiento
			echo '<div class="alezux-widget-wrapper">';
			include $view_path;
			echo '</div>';
		} else {
			echo 'View definition not found: ' . esc_html( $view_path );
		}
	}
}
