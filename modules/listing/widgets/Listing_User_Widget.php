<?php
namespace Alezux_Members\Modules\Listing\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Listing_User_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux_listing_user';
	}

	public function get_title() {
		return esc_html__( 'Listing Tareas (Estudiante)', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-checkbox';
	}

	public function get_categories() {
		return [ 'alezux-estudiantes' ]; // Categoría para elementos del estudiante
	}

	public function get_style_depends() {
		return [ 'alezux-listing-user-css' ];
	}

	public function get_script_depends() {
		return [ 'alezux-listing-user-js' ];
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
				'label' => esc_html__( 'Título', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Tus Tareas Pendientes', 'alezux-members' ),
			]
		);

		$this->add_control(
			'header_description',
			[
				'label' => esc_html__( 'Descripción', 'alezux-members' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Completa los siguientes pasos asignados por el administrador para avanzar en tu progreso.', 'alezux-members' ),
			]
		);

		$this->add_control(
			'empty_message',
			[
				'label' => esc_html__( 'Mensaje sin Tareas', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( '¡Felicidades! Has completado todas tus tareas.', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

		// Controles de Editor Módulo
		$this->start_controls_section(
			'editor_options_section',
			[
				'label' => esc_html__( 'Opciones de Editor', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_dummy_data',
			[
				'label' => esc_html__( 'Ver Datos de Prueba', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Sí', 'alezux-members' ),
				'label_off' => esc_html__( 'No', 'alezux-members' ),
				'return_value' => 'yes',
				'default' => 'no',
				'description' => esc_html__( 'Muestra tareas falsas en el editor para previsualizar estilos.', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

		// Style Tab - Contenedores
		$this->start_controls_section(
			'style_section',
			[
				'label' => esc_html__( 'Fondos y Contenedor', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'bg_color',
			[
				'label' => esc_html__( 'Color de Fondo Global', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-listing-user' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_bg_color',
			[
				'label' => esc_html__( 'Fondo de Tarjeta (Tarea)', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-user-task-item' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_border_color',
			[
				'label' => esc_html__( 'Color Borde Tarjeta', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-user-task-item' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => esc_html__( 'Padding Global', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-listing-user' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_padding',
			[
				'label' => esc_html__( 'Padding Tarjeta (Tarea)', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-user-task-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_radius',
			[
				'label' => esc_html__( 'Bordes Redondos Tarjeta', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-user-task-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Style Tab - Textos y Tipografías
		$this->start_controls_section(
			'typography_section',
			[
				'label' => esc_html__( 'Textos y Tipografías', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_color_title',
			[
				'label' => esc_html__( 'Color Título Principal', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-listing-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => esc_html__( 'Tipografía Título Principal', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-listing-title',
			]
		);

		$this->add_control(
			'text_color_desc',
			[
				'label' => esc_html__( 'Color Descripción P.', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-listing-desc' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'desc_typography',
				'label' => esc_html__( 'Tipografía Desc. Principal', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-listing-desc',
			]
		);

		$this->add_control(
			'task_title_color',
			[
				'label' => esc_html__( 'Color Título de Tarea', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .user-task-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'task_title_typography',
				'label' => esc_html__( 'Tipografía Tít. Tarea', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .user-task-title',
			]
		);

		$this->add_control(
			'task_desc_color',
			[
				'label' => esc_html__( 'Color Desc. Tarea', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .user-task-desc' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'task_desc_typography',
				'label' => esc_html__( 'Tipografía Desc. Tarea', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .user-task-desc',
			]
		);

		$this->end_controls_section();

		// Style Tab - Botón
		$this->start_controls_section(
			'button_section',
			[
				'label' => esc_html__( 'Botón "Completar"', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'btn_color_idle',
			[
				'label' => esc_html__( 'Color del Botón', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-complete' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .alezux-btn-complete:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'btn_padding',
			[
				'label' => esc_html__( 'Padding', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-complete' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'btn_typography',
				'label' => esc_html__( 'Tipografía', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-btn-complete',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$is_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();

		$dummy_tasks = '';
		if ( $is_edit_mode && 'yes' === $settings['enable_dummy_data'] ) {
			$dummy_tasks_array = [
				[
					'id' => 101,
					'title' => 'Mirar Clase Introductoria',
					'description' => 'Accede al bloque principal y revisa el tutorial para conocer cómo manejar esta plataforma de ahora en más.',
				],
				[
					'id' => 102,
					'title' => 'Completar Perfil',
					'description' => 'Añade tu foto y datos que nos puedan ser útiles en tu ficha técnica.',
				],
			];
			$dummy_tasks = wp_json_encode( $dummy_tasks_array );
		}

		if ( ! is_user_logged_in() && ! $is_edit_mode ) {
			echo '<div class="alezux-notice">Inicia sesión para ver tus tareas.</div>';
			return;
		}

		?>
		<div class="alezux-listing-user" data-empty-msg="<?php echo esc_attr( $settings['empty_message'] ); ?>" data-dummy-tasks="<?php echo esc_attr( $dummy_tasks ); ?>">
			<div class="alezux-listing-header">
				<h2 class="alezux-listing-title"><?php echo esc_html( $settings['header_title'] ); ?></h2>
				<p class="alezux-listing-desc"><?php echo esc_html( $settings['header_description'] ); ?></p>
			</div>

			<div class="alezux-user-tasks-wrapper">
				<div id="alezux-user-tasks-list" class="alezux-user-tasks-list">
					<div class="alezux-loading-tasks">
						<i class="fas fa-circle-notch fa-spin"></i> Buscando tus tareas...
					</div>
				</div>
				<div id="alezux-user-msg" class="alezux-user-msg" style="display: none;"></div>
			</div>
		</div>
		<?php
	}
}
