<?php
namespace Alezux_Members\Modules\Lesson_Navigator\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Lesson_Navigator_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux-lesson-navigator';
	}

	public function get_title() {
		return esc_html__( 'Navegador de Lecciones (Módulos)', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-exchange';
	}

	public function get_categories() {
		return [ 'alezux-lms' ];
	}

	public function get_style_depends() {
		return [ 'alezux-lesson-navigator-css' ];
	}

	protected function register_controls() {
		
		// --- CONTENIDO ---
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Contenido', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'prev_text',
			[
				'label' => esc_html__( 'Texto Botón Anterior', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Módulo Anterior', 'alezux-members' ),
			]
		);

		$this->add_control(
			'next_text',
			[
				'label' => esc_html__( 'Texto Botón Siguiente', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Siguiente Módulo', 'alezux-members' ),
			]
		);

        $this->add_control(
			'show_lesson_title',
			[
				'label' => esc_html__( 'Mostrar Nombre del Módulo', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Sí', 'alezux-members' ),
				'label_off' => esc_html__( 'No', 'alezux-members' ),
				'default' => 'yes',
                'description' => esc_html__( 'Muestra el nombre de la lección destino debajo del texto del botón.', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

		// --- ESTILO ---
		$this->start_controls_section(
			'section_style_buttons',
			[
				'label' => esc_html__( 'Botones', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'button_bg_color',
			[
				'label' => esc_html__( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-ln-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_hover_color',
			[
				'label' => esc_html__( 'Color de Fondo (Hover)', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-ln-btn:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => esc_html__( 'Color del Texto Principal', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-ln-btn-label' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-ln-btn i' => 'color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'button_subtext_color',
			[
				'label' => esc_html__( 'Color del Nombre del Módulo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-ln-btn-title' => 'color: {{VALUE}};',
				],
                'condition' => [ 'show_lesson_title' => 'yes' ]
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'label' => esc_html__( 'Tipografía Principal', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-ln-btn-label',
			]
		);

        $this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_sub_typography',
				'label' => esc_html__( 'Tipografía Nombre Módulo', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-ln-btn-title',
                'condition' => [ 'show_lesson_title' => 'yes' ]
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__( 'Relleno (Padding)', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-ln-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label' => esc_html__( 'Radio del Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-ln-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$current_id = get_the_ID();
		
		if ( ! function_exists( 'learndash_get_course_id' ) ) {
			echo '<p>LearnDash no está activo.</p>';
			return;
		}

		$course_id = learndash_get_course_id( $current_id );
		$current_lesson_id = learndash_get_lesson_id( $current_id );

		if ( empty( $course_id ) || empty( $current_lesson_id ) ) {
			// Si no estamos en un contexto de curso, mostrar vista previa estática para Elementor
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				$this->render_preview( $settings );
			}
			return;
		}

		$lesson_ids = learndash_course_get_steps_by_type( $course_id, 'sfwd-lessons' );
		if ( empty( $lesson_ids ) ) {
			return;
		}

		$current_index = array_search( $current_lesson_id, $lesson_ids );
		
		$prev_data = false;
		$next_data = false;

		// Buscar Lección Anterior válida
		if ( $current_index !== false && $current_index > 0 ) {
			for ( $i = $current_index - 1; $i >= 0; $i-- ) {
				$prev_id = $lesson_ids[ $i ];
				$prev_data = $this->get_valid_lesson_data( $prev_id, $course_id );
				if ( $prev_data ) {
					break;
				}
			}
		}

		// Buscar Lección Siguiente válida
		if ( $current_index !== false && $current_index < count( $lesson_ids ) - 1 ) {
			for ( $i = $current_index + 1; $i < count( $lesson_ids ); $i++ ) {
				$next_id = $lesson_ids[ $i ];
				$next_data = $this->get_valid_lesson_data( $next_id, $course_id );
				if ( $next_data ) {
					break;
				}
			}
		}

		// Si no hay ninguno, no renderizar contenedor
		if ( ! $prev_data && ! $next_data ) {
			return;
		}

		$view_path = plugin_dir_path( __FILE__ ) . '../views/lesson-navigator.php';
		
		if ( file_exists( $view_path ) ) {
			include $view_path;
		}
	}

	/**
	 * Extrae el enlace del primer topic de una lección y su título limpio.
	 * Retorna false si es un separador o si no tiene topics.
	 */
	private function get_valid_lesson_data( $lesson_id, $course_id ) {
		$title = get_the_title( $lesson_id );
		$decoded_title = html_entity_decode( $title, ENT_QUOTES | ENT_HTML5 );
		
		// Ignorar si es un separador
		if ( preg_match( '/\[\s*Separador/iu', $decoded_title ) ) {
			return false;
		}

		// Obtener Topics de esta lección
		$topics = learndash_course_get_children_of_step( $course_id, $lesson_id, 'sfwd-topic' );
		if ( empty( $topics ) ) {
			return false;
		}

		// Tomar el primer Topic
		$first_topic_id = $topics[0];
		$url = learndash_get_step_permalink( $first_topic_id, $course_id );

		return [
			'url'   => $url,
			'title' => $title,
		];
	}

	private function render_preview( $settings ) {
		$prev_data = [
			'url' => '#',
			'title' => 'Nombre del Módulo Anterior',
		];
		$next_data = [
			'url' => '#',
			'title' => 'Nombre del Siguiente Módulo',
		];
		$view_path = plugin_dir_path( __FILE__ ) . '../views/lesson-navigator.php';
		if ( file_exists( $view_path ) ) {
			include $view_path;
		}
	}
}
