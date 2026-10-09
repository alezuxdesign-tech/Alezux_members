<?php
namespace Alezux_Members\Modules\Estudiantes\Widgets;

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

class Estudiantes_CSV_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux-estudiantes-csv';
	}

	public function get_title() {
		return esc_html__( 'Registro Masivo CSV', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-file-download';
	}

	public function get_categories() {
		return [ 'alezux-estudiantes' ];
	}

	public function get_script_depends() {
		return [ 'alezux-estudiantes-csv-js' ];
	}

	public function get_style_depends() {
		return [ 'alezux-estudiantes-csv-css' ];
	}

	protected function register_controls() {

		// --- ETIQUETAS ---
		$this->start_controls_section(
			'section_content_labels',
			[
				'label' => esc_html__( 'Textos y Etiquetas', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title_text',
			[
				'label' => esc_html__( 'Título Principal', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Importar Estudiantes (CSV)', 'alezux-members' ),
			]
		);

		$this->add_control(
			'label_course',
			[
				'label' => esc_html__( 'Etiqueta Curso', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Asignar Curso a Todos', 'alezux-members' ),
			]
		);

		// Upload Box Texts
		$this->add_control(
			'upload_title',
			[
				'label' => esc_html__( 'Texto Subida (Título)', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Arrastra tu archivo CSV o haz click aquí', 'alezux-members' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'upload_subtitle',
			[
				'label' => esc_html__( 'Texto Subida (Subtítulo)', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Formato: Nombre, Apellido, Email', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

		// --- ESTILO: UPLOAD BOX ---
		$this->start_controls_section(
			'section_style_upload',
			[
				'label' => esc_html__( 'Caja Subida', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'upload_bg_color',
			[
				'label' => esc_html__( 'Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-csv-upload-box' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'upload_border',
				'selector' => '{{WRAPPER}} .alezux-csv-upload-box',
			]
		);

		$this->add_control(
			'upload_text_color',
			[
				'label' => esc_html__( 'Color Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-csv-upload-box' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-csv-upload-box i' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'upload_hover_bg_color',
			[
				'label' => esc_html__( 'Fondo (Hover/Drag)', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-csv-upload-box:hover' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .alezux-csv-upload-box.dragover' => 'background-color: {{VALUE}}; border-color: #fff;',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		// --- ESTILO: PROGRESO ---
		$this->start_controls_section(
			'section_style_progress',
			[
				'label' => esc_html__( 'Barra de Progreso', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'progress_bar_color',
			[
				'label' => esc_html__( 'Color Barra', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#6c5ce7',
				'selectors' => [
					'{{WRAPPER}} .alezux-progress-fill' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'progress_bg_color',
			[
				'label' => esc_html__( 'Color Fondo Barra', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#333',
				'selectors' => [
					'{{WRAPPER}} .alezux-progress-bar' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		// Get Courses
		$courses = get_posts( [
			'post_type' => 'sfwd-courses',
			'numberposts' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		] );

		?>
		<div class="alezux-csv-wrapper">
			<h3 class="alezux-csv-title"><?php echo esc_html( $settings['title_text'] ); ?></h3>

			<div class="alezux-form-group">
				<label class="alezux-form-label"><?php echo esc_html( $settings['label_course'] ); ?></label>
				<select id="alezux-csv-course-select" class="alezux-form-control">
					<option value=""><?php esc_html_e( '-- Seleccionar Curso (Opcional) --', 'alezux-members' ); ?></option>
					<?php foreach ( $courses as $course ) : ?>
						<option value="<?php echo esc_attr( $course->ID ); ?>"><?php echo esc_html( $course->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="alezux-form-group">
				<label class="alezux-form-label"><?php esc_html_e( 'Asignar Plan a Todos', 'alezux-members' ); ?></label>
				<select id="alezux-csv-plan-select" class="alezux-form-control">
					<option value=""><?php esc_html_e( '-- Seleccionar Plan (Opcional) --', 'alezux-members' ); ?></option>
					<?php 
					global $wpdb;
					$plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
					$wpdb->suppress_errors(true);
					$plans = $wpdb->get_results("SELECT id, name FROM $plans_table ORDER BY name ASC");
					$wpdb->suppress_errors(false);
					if ( $plans ) :
						foreach ( $plans as $plan ) : ?>
							<option value="<?php echo esc_attr( $plan->id ); ?>"><?php echo esc_html( $plan->name ); ?></option>
						<?php endforeach;
					endif; ?>
				</select>
			</div>

			<div class="alezux-csv-upload-box" id="alezux-csv-dropzone">
				<input type="file" id="alezux-csv-file-input" accept=".csv" style="display:none;">
				<div class="alezux-upload-content">
					<i class="fas fa-cloud-upload-alt" style="font-size: 40px; margin-bottom: 15px;"></i>
					<h4 class="alezux-upload-title"><?php echo esc_html( $settings['upload_title'] ); ?></h4>
					<p class="alezux-upload-subtitle"><?php echo esc_html( $settings['upload_subtitle'] ); ?></p>
				</div>
			</div>

			<!-- UI Progreso (Oculto inicialmente) -->
			<div id="alezux-csv-progress-container" style="display:none; margin-top: 20px;">
				<div class="alezux-progress-info" style="display:flex; justify-content:space-between; margin-bottom:5px; font-size:13px; color:#888;">
					<span class="status-text">Procesando cola...</span>
					<span class="status-count">0/0</span>
				</div>
				<div class="alezux-progress-bar" style="height: 10px; border-radius: 5px; overflow: hidden; background: #333;">
					<div class="alezux-progress-fill" style="width: 0%; height: 100%; background: #6c5ce7; transition: width 0.3s ease;"></div>
				</div>
				<div id="alezux-csv-report" style="margin-top: 15px; font-size: 13px; color: #ccc; max-height: 150px; overflow-y: auto;"></div>
			</div>

		</div>
		<?php
	}
}
