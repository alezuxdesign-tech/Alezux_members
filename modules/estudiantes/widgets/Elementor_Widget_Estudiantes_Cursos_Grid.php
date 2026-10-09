<?php
namespace Alezux_Members\Modules\Estudiantes\Widgets;

use Alezux_Members\Core\Elementor_Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Text_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elementor_Widget_Estudiantes_Cursos_Grid extends Elementor_Widget_Base {

	public function get_name() {
		return 'alezux_estudiantes_cursos_grid';
	}

	public function get_title() {
		return __( 'Grid Cursos Inscritos (Estudiantes)', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-gallery-masonry';
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
			'posts_per_page',
			[
				'label' => __( 'Cantidad de Cursos', 'alezux-members' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 6,
			]
		);

		$this->add_control(
			'button_text_access',
			[
				'label' => __( 'Texto Botón (Con Acceso)', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Continuar', 'alezux-members' ),
				'placeholder' => __( 'Continuar', 'alezux-members' ),
			]
		);

        // Eliminamos controles de compra ya que solo mostramos inscritos
		/* $this->add_control(
			'button_text_purchase',
			[
				'label' => __( 'Texto Botón (Sin Acceso)', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Comprar Curso', 'alezux-members' ),
				'placeholder' => __( 'Comprar Curso', 'alezux-members' ),
			]
		); */

		$this->add_control(
			'selected_icon',
			[
				'label' => __( 'Icono', 'alezux-members' ),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
			]
		);

		$this->add_control(
			'icon_align',
			[
				'label' => __( 'Posición del Icono', 'alezux-members' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left' => __( 'Antes', 'alezux-members' ),
					'right' => __( 'Después', 'alezux-members' ),
				],
				'condition' => [
					'selected_icon[value]!' => '',
				],
			]
		);

		$this->add_control(
			'icon_indent',
			[
				'label' => __( 'Espaciado del Icono', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .alezux-btn-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'orderby',
			[
				'label' => __( 'Ordenar por', 'alezux-members' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => [
					'date' => __( 'Fecha', 'alezux-members' ),
					'title' => __( 'Título', 'alezux-members' ),
					'rand' => __( 'Aleatorio', 'alezux-members' ),
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label' => __( 'Orden', 'alezux-members' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => [
					'ASC' => __( 'Ascendente', 'alezux-members' ),
					'DESC' => __( 'Descendente', 'alezux-members' ),
				],
			]
		);

		$this->add_control(
			'mentors_label_text',
			[
				'label' => __( 'Etiqueta Mentores', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'MENTORES', 'alezux-members' ),
				'placeholder' => __( 'Escribe aquí...', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

		// --- Sección de Estilo: Grid ---
		$this->start_controls_section(
			'section_style_grid',
			[
				'label' => __( 'Grid & Layout', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => __( 'Columnas', 'alezux-members' ),
				'type' => Controls_Manager::SELECT,
				'default' => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-formaciones-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr) !important;',
				],
			]
		);

		$this->add_responsive_control(
			'gap',
			[
				'label' => __( 'Espacio entre tarjetas', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 20,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-formaciones-grid' => 'gap: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// --- Sección de Estilo: Tarjeta ---
		$this->start_controls_section(
			'section_style_card',
			[
				'label' => __( 'Tarjeta', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'card_bg_color',
			[
				'label' => __( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#1a1a1a',
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_border_radius',
			[
				'label' => __( 'Borde Radio', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default' => [
					'top' => 50,
					'right' => 50,
					'bottom' => 50,
					'left' => 50,
					'unit' => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'card_padding',
			[
				'label' => __( 'Relleno (Padding)', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default' => [
					'top' => 0, 
					'right' => 0, 
					'bottom' => 25, 
					'left' => 0,
					'unit' => 'px', 
					'isLinked' => false,
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .alezux-formacion-card',
			]
		);

		$this->end_controls_section();

		// --- Sección de Estilo: Imagen ---
		$this->start_controls_section(
			'section_style_image',
			[
				'label' => __( 'Imagen', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name' => 'image_size',
				'default' => 'large',
				'separator' => 'none',
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label' => __( 'Borde Radio Imagen', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_responsive_control(
			'image_margin',
			[
				'label' => __( 'Margen Imagen', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-image' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// --- Sección de Estilo: Tipografía ---
		$this->start_controls_section(
			'section_style_typography',
			[
				'label' => __( 'Textos y Colores', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		// Título
		$this->add_control(
			'heading_title',
			[
				'label' => __( 'Título', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => __( 'Color Título', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .alezux-formacion-title',
			]
		);
		
		// Precio
		$this->add_control(
			'heading_price',
			[
				'label' => __( 'Precio', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'price_color',
			[
				'label' => __( 'Color Precio', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#6c5ce7', 
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-price' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'price_typography',
				'selector' => '{{WRAPPER}} .alezux-formacion-price',
			]
		);
		
        $this->end_controls_section();

		// --- Sección de Estilo: Mentores ---
		$this->start_controls_section(
			'section_style_mentors',
			[
				'label' => __( 'Mentores', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'mentors_container_gap',
			[
				'label' => __( 'Espacio Inferior', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-mentors' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'mentors_avatar_size',
			[
				'label' => __( 'Tamaño Avatar', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 20,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 30,
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-mentor-avatar' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .alezux-mentors-avatars' => 'height: {{SIZE}}{{UNIT}};', // Ensure container fits
				],
			]
		);

        $this->add_control(
			'mentors_label_heading',
			[
				'label' => __( 'Etiqueta', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'mentors_label_color',
			[
				'label' => __( 'Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#6d7688',
				'selectors' => [
					'{{WRAPPER}} .alezux-mentors-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mentors_label_typography',
				'selector' => '{{WRAPPER}} .alezux-mentors-label',
			]
		);

        $this->add_control(
			'mentors_names_heading',
			[
				'label' => __( 'Nombres', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'mentors_names_color',
			[
				'label' => __( 'Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .alezux-mentors-names' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mentors_names_typography',
				'selector' => '{{WRAPPER}} .alezux-mentors-names',
			]
		);
		
		$this->end_controls_section();

		// --- Sección de Estilo: Botón ---
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => __( 'Botón', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        // Alignment moved to Footer / Layout section


		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .alezux-formacion-button',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'button_text_shadow',
				'selector' => '{{WRAPPER}} .alezux-formacion-button',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		// Tab Normal
		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => __( 'Normal', 'alezux-members' ),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => __( 'Color de Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-button' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-formacion-button svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_background',
				'label' => __( 'Fondo', 'alezux-members' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ],
				'selector' => '{{WRAPPER}} .alezux-formacion-button',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [
						'default' => '#6c5ce7',
					],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .alezux-formacion-button',
			]
		);

		$this->end_controls_tab();

		// Tab Hover
		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => __( 'Hover', 'alezux-members' ),
			]
		);

		$this->add_control(
			'button_hover_text_color',
			[
				'label' => __( 'Color de Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-button:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-formacion-button:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_hover_background',
				'label' => __( 'Fondo', 'alezux-members' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ],
				'selector' => '{{WRAPPER}} .alezux-formacion-button:hover',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_hover_box_shadow',
				'selector' => '{{WRAPPER}} .alezux-formacion-button:hover',
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label' => __( 'Color de Borde', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-button:hover' => 'border-color: {{VALUE}}; text-decoration: none;',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'button_border_type',
			[
				'label' => __( 'Tipo de Borde', 'alezux-members' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'solid',
				'options' => [
					'none' => __( 'Ninguno', 'alezux-members' ),
					'solid' => __( 'Sólido', 'alezux-members' ),
					'double' => __( 'Doble', 'alezux-members' ),
					'dotted' => __( 'Punteado', 'alezux-members' ),
					'dashed' => __( 'Discontinuo', 'alezux-members' ),
					'groove' => __( 'Groove', 'alezux-members' ),
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-button' => 'border-style: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'button_border_width',
			[
				'label' => __( 'Ancho de Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-button' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'button_border_type!' => 'none',
				],
			]
		);

		$this->add_control(
			'button_border_color',
			[
				'label' => __( 'Color de Borde', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-button' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'button_border_type!' => 'none',
				],
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label' => __( 'Radio de Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default' => [
					'top' => 50,
					'right' => 50,
					'bottom' => 50,
					'left' => 50,
					'unit' => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => __( 'Relleno', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default' => [
					'top' => 10,
					'right' => 20,
					'bottom' => 10,
					'left' => 20,
					'unit' => 'px',
					'isLinked' => false,
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->add_responsive_control(
			'button_icon_size',
			[
				'label' => __( 'Tamaño del Icono', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-button .alezux-btn-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'selected_icon[value]!' => '',
				],
			]
		);

		$this->end_controls_section();

		// --- Sección de Estilo: Footer (Layout) ---
		$this->start_controls_section(
			'section_style_footer',
			[
				'label' => __( 'Footer / Layout', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'footer_layout_justify',
			[
				'label' => __( 'Alineación Horizontal', 'alezux-members' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => __( 'Izquierda', 'alezux-members' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Centro', 'alezux-members' ),
						'icon' => 'eicon-text-align-center',
					],
					'flex-end' => [
						'title' => __( 'Derecha', 'alezux-members' ),
						'icon' => 'eicon-text-align-right',
					],
					'space-between' => [
						'title' => __( 'Espacio entre', 'alezux-members' ),
						'icon' => 'eicon-text-align-justify',
					],
					'stretch' => [
						'title' => __( 'Estirar Botón', 'alezux-members' ), // Clearer label
						'icon' => 'eicon-arrow-expand',
					],
				],
				'default' => 'space-between',
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-footer' => 'justify-content: {{VALUE}};',
					'{{WRAPPER}} .alezux-formacion-button' => 'width: {{VALUE}} == "stretch" ? "100%" : "auto";',
				],
			]
		);

		$this->add_responsive_control(
			'footer_gap',
			[
				'label' => __( 'Espacio entre Elementos (Gap)', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-formacion-footer' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// --- Sección Estilo: Barra de Progreso ---
		$this->start_controls_section(
			'section_style_progress_bar',
			[
				'label' => __( 'Barra de Progreso', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'show_progress_bar',
			[
				'label' => __( 'Mostrar Barra de Progreso', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Sí', 'alezux-members' ),
				'label_off' => __( 'No', 'alezux-members' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'progress_bar_height',
			[
				'label' => __( 'Altura', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 2,
						'max' => 20,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 6,
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-progress-track' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'show_progress_bar' => 'yes',
				],
			]
		);

		$this->add_control(
			'progress_bar_radius',
			[
				'label' => __( 'Radio del Borde', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 3,
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-progress-track, {{WRAPPER}} .alezux-progress-fill' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'show_progress_bar' => 'yes',
				],
			]
		);

		$this->add_control(
			'progress_bar_track_color',
			[
				'label' => __( 'Color de Fondo (Pista)', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_TEXT,
				],
				'default' => '#333333',
				'selectors' => [
					'{{WRAPPER}} .alezux-progress-track' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'show_progress_bar' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'progress_bar_fill_background',
				'label' => __( 'Color de Relleno (Progreso)', 'alezux-members' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .alezux-progress-fill',
				'condition' => [
					'show_progress_bar' => 'yes',
				],
			]
		);
		
		$this->add_responsive_control(
			'progress_bar_margin',
			[
				'label' => __( 'Margen', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-progress-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [
					'top' => 15,
					'bottom' => 0,
					'left' => 0,
					'right' => 0,
					'unit' => 'px',
					'isLinked' => false,
				],
				'condition' => [
					'show_progress_bar' => 'yes',
				],
			]
		);
        
        $this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="alezux-alert">' . __( 'Debes iniciar sesión para ver tus cursos inscritos.', 'alezux-members' ) . '</div>';
			}
			return;
		}

		// Obtener cursos inscritos del usuario
		$enrolled_courses = [];
		if ( function_exists( 'learndash_user_get_enrolled_courses' ) ) {
			$enrolled_courses = learndash_user_get_enrolled_courses( $user_id );
		}

		// Si no hay cursos inscritos, no mostrar nada o mensaje
		if ( empty( $enrolled_courses ) ) {
			echo '<p>' . __( 'No estás inscrito en ningún curso.', 'alezux-members' ) . '</p>';
			return;
		}

		$args = [
			'post_type' => 'sfwd-courses', // LearnDash courses
			'post__in' => $enrolled_courses, // Filter by enrolled courses
			'posts_per_page' => $settings['posts_per_page'],
			'orderby' => $settings['orderby'],
			'order' => $settings['order'],
		];

		$query = new \WP_Query( $args );

		if ( $query->have_posts() ) {
			echo '<div class="alezux-formaciones-grid">';
			
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				
				// Obtener datos
				$price = get_post_meta( $post_id, '_alezux_course_price', true );
				$mentors = get_post_meta( $post_id, '_alezux_course_mentors', true );
				
				// Usar tamaño de imagen configurado por el usuario (o 'large' por defecto)
				$img_size = !empty($settings['image_size']) ? $settings['image_size'] : 'large';
				$image_url = get_the_post_thumbnail_url( $post_id, $img_size );
				$description = get_the_excerpt();

                // Siempre tiene acceso porque estamos filtrando por inscritos
				$has_access = true; 
				
				$button_text = $settings['button_text_access'];
                $button_link = get_the_permalink();
                
				// Renderizar Tarjeta
				?>
				<div class="alezux-formacion-card">
					<?php if ( $image_url ) : ?>
						<div class="alezux-formacion-image">
							<a href="<?php the_permalink(); ?>">
								<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title_attribute(); ?>">
							</a>
						</div>
					<?php endif; ?>
					
					<div class="alezux-formacion-content">
						
                        <a href="<?php the_permalink(); ?>" class="alezux-formacion-title-link">
							<h3 class="alezux-formacion-title"><?php the_title(); ?></h3>
						</a>

						<!-- Mentores -->
						<?php if ( ! empty( $mentors ) && is_array( $mentors ) ) : ?>
							<div class="alezux-formacion-mentors">
								<div class="alezux-mentors-avatars">
									<?php foreach ( $mentors as $mentor ) : ?>
										<?php if ( ! empty( $mentor['image'] ) ) : ?>
											<div class="alezux-mentor-avatar">
												<img src="<?php echo esc_url( $mentor['image'] ); ?>" alt="<?php echo esc_attr( $mentor['name'] ); ?>">
											</div>
										<?php endif; ?>
									<?php endforeach; ?>
								</div>
								
								<div class="alezux-mentors-info">
									<span class="alezux-mentors-label"><?php echo esc_html( $settings['mentors_label_text'] ); ?></span>
									<span class="alezux-mentors-names">
										<?php 
										$names = array_map( function( $m ) { return $m['name']; }, $mentors );
										echo esc_html( implode( ', ', $names ) ); 
										?>
									</span>
								</div>
							</div>
						<?php endif; ?>
                        
                        <?php
                        // Barra de Progreso
                        if ( 'yes' === $settings['show_progress_bar'] ) {
                            $progress = learndash_course_progress( [
                                'user_id'   => $user_id,
                                'course_id' => $post_id,
                                'array'     => true
                            ] );
                            // Asegurar que exista percentage, si no 0
                            $percentage = isset($progress['percentage']) ? intval($progress['percentage']) : 0;
                            ?>
                            <div class="alezux-progress-wrapper">
                                <div class="alezux-progress-track">
                                    <div class="alezux-progress-fill" style="width: <?php echo esc_attr( $percentage ); ?>%;"></div>
                                </div>
                                <div class="alezux-progress-text"><?php echo esc_html( $percentage ); ?>% <?php esc_html_e('Completado', 'alezux-members'); ?></div>
                            </div>
                            <?php
                        }
                        ?>

						<div class="alezux-formacion-excerpt">
							<?php echo wp_trim_words( $description, 15 ); ?>
						</div>

						<div class="alezux-formacion-footer">
							<?php if ( ! $has_access ) : ?>
								<div class="alezux-formacion-price">
									<?php echo esc_html( $price ); ?>
								</div>
							<?php endif; ?>
							<a href="<?php echo esc_url( $button_link ); ?>" class="alezux-formacion-button">
								<?php if ( ! empty( $settings['selected_icon']['value'] ) && 'left' === $settings['icon_align'] ) : ?>
									<span class="alezux-btn-icon alezux-btn-icon-left">
										<?php Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] ); ?>
									</span>
								<?php endif; ?>
								
								<span class="alezux-btn-text"><?php echo esc_html( $button_text ); ?></span>

								<?php if ( ! empty( $settings['selected_icon']['value'] ) && 'right' === $settings['icon_align'] ) : ?>
									<span class="alezux-btn-icon alezux-btn-icon-right">
										<?php Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] ); ?>
									</span>
								<?php endif; ?>
							</a>
						</div>
					</div>
				</div>
				<?php
			}
			
			echo '</div>'; // .alezux-formaciones-grid
			wp_reset_postdata();
		} else {
			echo '<p>' . __( 'No se encontraron cursos.', 'alezux-members' ) . '</p>';
		}
	}
}
