<?php
namespace Alezux_Members\Modules\Logros\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Recent_Logros_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux_recent_logros';
	}

	public function get_title() {
		return esc_html__( 'Últimos Logros', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	public function get_categories() {
		return [ 'alezux-lms' ];
	}

	public function get_style_depends() {
		return [ 'alezux-recent-logros-css' ];
	}

	public function get_script_depends() {
		return [ 'alezux-recent-logros-js' ];
	}

	protected function register_controls() {
		// --- CONTENT SECTION ---
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Contenido', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'limit',
			[
				'label' => esc_html__( 'Límite de Logros', 'alezux-members' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 20,
				'step' => 1,
				'default' => 5,
			]
		);

		$this->add_control(
			'logros_page_url',
			[
				'label' => esc_html__( 'URL Página de Logros', 'alezux-members' ),
				'type' => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://tu-sitio.com/logros', 'alezux-members' ),
				'default' => [
					'url' => '#',
				],
			]
		);

		$this->end_controls_section();

		// --- STYLE SECTION: ITEM ---
		$this->start_controls_section(
			'style_section_item',
			[
				'label' => esc_html__( 'Item (Contenedor)', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'items_gap',
			[
				'label' => esc_html__( 'Espacio entre items', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 15,
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-recent-logros-widget' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'item_padding',
			[
				'label' => esc_html__( 'Relleno (Padding)', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .aleuz-logro-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'item_background',
				'label' => esc_html__( 'Fondo', 'alezux-members' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .aleuz-logro-item',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'item_border',
				'label' => esc_html__( 'Borde', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .aleuz-logro-item',
			]
		);

		$this->add_control(
			'item_border_radius',
			[
				'label' => esc_html__( 'Radio de Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .aleuz-logro-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'item_box_shadow',
				'label' => esc_html__( 'Sombra', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .aleuz-logro-item',
			]
		);

		$this->end_controls_section();

		// --- STYLE SECTION: AVATAR ---
		$this->start_controls_section(
			'style_section_avatar',
			[
				'label' => esc_html__( 'Avatar', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'avatar_size',
			[
				'label' => esc_html__( 'Tamaño', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 20,
						'max' => 150,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-logro-avatar img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'avatar_spacing',
			[
				'label' => esc_html__( 'Espacio derecho', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .aleuz-logro-item' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'avatar_border_radius',
			[
				'label' => esc_html__( 'Radio de Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-logro-avatar img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// --- STYLE SECTION: CONTENT ---
		$this->start_controls_section(
			'style_section_content',
			[
				'label' => esc_html__( 'Textos', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		// NOMBRE
		$this->add_control(
			'heading_name',
			[
				'label' => esc_html__( 'Nombre del Estudiante', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'name_color',
			[
				'label' => esc_html__( 'Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-logro-name' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'name_typography',
				'label' => esc_html__( 'Tipografía', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-logro-name',
			]
		);

		// FECHA
		$this->add_control(
			'heading_date',
			[
				'label' => esc_html__( 'Fecha / Tiempo', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'date_color',
			[
				'label' => esc_html__( 'Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-logro-time' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'date_typography',
				'label' => esc_html__( 'Tipografía', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-logro-time',
			]
		);

		// MENSAJE
		$this->add_control(
			'heading_message',
			[
				'label' => esc_html__( 'Mensaje', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'message_color',
			[
				'label' => esc_html__( 'Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-logro-message' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'message_typography',
				'label' => esc_html__( 'Tipografía', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-logro-message',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$limit = $settings['limit'];
		$logros_url = ! empty( $settings['logros_page_url']['url'] ) ? $settings['logros_page_url']['url'] : '#';

		global $wpdb;
		$table_name = $wpdb->prefix . 'alezux_achievements';

		// Consulta segura a la base de datos
		// Lógica de filtrado por usuario/curso
		$current_user_id = get_current_user_id();
		$where_clauses = [];
		$params = [];

		// 1. Logros asignados directamente al estudiante
		if ( $current_user_id ) {
			$where_clauses[] = "student_id = %d";
			$params[] = $current_user_id;
		}

		// 2. Logros de cursos donde el estudiante está inscrito
		if ( $current_user_id && function_exists( 'learndash_user_get_enrolled_courses' ) ) {
			$enrolled_courses = learndash_user_get_enrolled_courses( $current_user_id );
			if ( ! empty( $enrolled_courses ) ) {
				// Sanitize IDs for IN clause
				$course_ids = array_map( 'intval', $enrolled_courses );
				$course_ids_str = implode( ',', $course_ids );
				$where_clauses[] = "course_id IN ($course_ids_str)";
			}
		}

		if ( empty( $where_clauses ) ) {
			// Si no hay condiciones (ej. no logueado o sin cursos), no mostrar nada
			echo '<div class="alezux-no-logros">' . esc_html__( 'No hay logros recientes.', 'alezux-members' ) . '</div>';
			return;
		}

		$where_sql = implode( ' OR ', $where_clauses );
		
		// Añadir límite al final de los parámetros
		$params[] = $limit;

		// Consulta segura a la base de datos
		$sql = "SELECT * FROM $table_name WHERE ($where_sql) ORDER BY created_at DESC LIMIT %d";
		$results = $wpdb->get_results( $wpdb->prepare( $sql, $params ) );

		if ( empty( $results ) ) {
			echo '<div class="alezux-no-logros">' . esc_html__( 'No hay logros recientes.', 'alezux-members' ) . '</div>';
			return;
		}

		echo '<div class="alezux-recent-logros-widget">';

		foreach ( $results as $row ) {
			$student_id = $row->student_id;
			$user_info = ! empty( $student_id ) ? get_userdata( $student_id ) : false;
			
			if ( $user_info ) {
				// Caso 1: Logro asignado a un estudiante existente
				$student_name = $user_info->display_name;
				$avatar_url = get_avatar_url( $student_id, ['size' => 150] );
			} else {
				// Caso 2: Sin estudiante asignado (o usuario borrado) -> Mostrar info del Sitio/Admin
				$student_name = get_bloginfo( 'name' );
				// Usamos el avatar del email del administrador principal para cumplir "foto de perfil del administrador"
				$avatar_url = get_avatar_url( get_option( 'admin_email' ), ['size' => 150] );
			}

			// Calcular tiempo transcurrido
			
			// Calcular tiempo transcurrido
			$time_ago = human_time_diff( strtotime( $row->created_at ), current_time( 'timestamp' ) ) . ' ' . esc_html__( 'atrás', 'alezux-members' );
			
            // Formatear mensaje corto
            $message_preview = mb_strimwidth( strip_tags( $row->message ), 0, 100, '...' );

			?>
			<a href="<?php echo esc_url( $logros_url ); ?>" class="aleuz-logro-item">
				<div class="alezux-logro-avatar">
					<img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $student_name ); ?>">
				</div>
				<div class="alezux-logro-content">
					<div class="alezux-logro-header">
						<span class="alezux-logro-name"><?php echo esc_html( $student_name ); ?></span>
						<span class="alezux-logro-time"><?php echo esc_html( $time_ago ); ?></span>
					</div>
					<div class="alezux-logro-body">
						<p class="alezux-logro-message"><?php echo esc_html( $message_preview ); ?></p>
					</div>
				</div>
			</a>
			<?php
		}

		echo '</div>';
	}
}
