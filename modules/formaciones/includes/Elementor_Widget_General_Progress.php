<?php
namespace Alezux_Members\Modules\Formaciones\Includes;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget de Elementor: Progreso General (Semicírculo y Lista)
 */
class Elementor_Widget_General_Progress extends Widget_Base {

	public function get_name() {
		return 'alezux_general_progress';
	}

	public function get_title() {
		return __( 'Alezux Progreso General', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-gauge';
	}

	public function get_categories() {
		return [ 'alezux-lms' ];
	}

	protected function register_controls() {
		// --- SECCIÓN: CONTENIDO GENERAL ---
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Contenido', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_chart',
			[
				'label' => __( 'Mostrar Gráfico', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'chart_style',
			[
				'label' => __( 'Estilo de Gráfico', 'alezux-members' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'segmented',
				'options' => [
					'segmented' => __( 'Segmentado (Ticks)', 'alezux-members' ),
					'solid'     => __( 'Sólido (Arco)', 'alezux-members' ),
				],
				'condition' => [ 'show_chart' => 'yes' ],
			]
		);

		$this->add_control(
			'chart_label',
			[
				'label' => __( 'Etiqueta del Gráfico', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Avance total', 'alezux-members' ),
				'condition' => [ 'show_chart' => 'yes' ],
			]
		);

		$this->add_control(
			'show_list',
			[
				'label' => __( 'Mostrar Lista de Cursos', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

		// --- SECCIÓN: ESTILO - GRÁFICO ---
		$this->start_controls_section(
			'section_style_chart',
			[
				'label' => __( 'Estilo Gráfico', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_chart' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'chart_size',
			[
				'label' => __( 'Tamaño del Gráfico (px)', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 150,
						'max' => 600,
					],
				],
				'default' => [
					'size' => 350,
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-general-chart-container' => 'width: {{SIZE}}px;', 
				],
			]
		);
		
		$this->add_control(
			'chart_ticks_count',
			[
				'label' => __( 'Cantidad de Segmentos', 'alezux-members' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 10,
				'max' => 60,
				'step' => 1,
				'default' => 30,
				'condition' => [ 'chart_style' => 'segmented' ],
			]
		);
		
		$this->add_responsive_control(
			'chart_bar_width',
			[
				'label' => __( 'Grosor de Barra', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [ 'min' => 5, 'max' => 50 ],
				],
				'default' => [ 'size' => 30 ],
				'condition' => [ 'chart_style' => 'solid' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-chart-bg' => 'stroke-width: {{SIZE}}px;',
					'{{WRAPPER}} .alezux-chart-progress' => 'stroke-width: {{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			'chart_track_color',
			[
				'label' => __( 'Color Fondo / Inactivos', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .alezux-chart-bg' => 'stroke: {{VALUE}};',
					'{{WRAPPER}} .alezux-chart-tick-inactive' => 'stroke: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'chart_enable_glow',
			[
				'label' => __( 'Activar Glow (Barras)', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'chart_fill_color_start',
			[
				'label' => __( 'Color Activos / Progreso', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#FFB800', 
				'selectors' => [
					'{{WRAPPER}} .alezux-chart-progress' => 'stroke: {{VALUE}} !important;',
					'{{WRAPPER}} .alezux-chart-tick-active' => 'stroke: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'chart_percent_color',
			[
				'label' => __( 'Color Porcentaje', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffb800',
				'selectors' => [
					'{{WRAPPER}} .alezux-chart-percent' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'chart_percent_glow_show',
			[
				'label' => __( 'Activar Glow (Texto)', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'chart_percent_typography',
				'label' => __( 'Tipografía Porcentaje', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-chart-percent',
			]
		);

		$this->add_control(
			'chart_label_color',
			[
				'label' => __( 'Color Etiqueta', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .alezux-chart-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'chart_label_typography',
				'label' => __( 'Tipografía Etiqueta', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-chart-label',
			]
		);

		$this->end_controls_section();

		// --- SECCIÓN: ESTILO - LISTA DE CURSOS ---
		$this->start_controls_section(
			'section_style_list',
			[
				'label' => __( 'Estilo Lista de Cursos', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_list' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'list_gap',
			[
				'label' => __( 'Espacio entre items', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .alezux-course-item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'list_title_color',
			[
				'label' => __( 'Color Título Curso', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .alezux-course-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'list_title_typography',
				'label' => __( 'Tipografía Título', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-course-title',
			]
		);

		$this->add_control(
			'list_percent_color',
			[
				'label' => __( 'Color Porcentaje Individual', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#f1c40f',
				'selectors' => [
					'{{WRAPPER}} .alezux-course-percent' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'list_percent_typography',
				'label' => __( 'Tipografía Porcentaje', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-course-percent',
			]
		);

		$this->add_control(
			'list_divider_color',
			[
				'label' => __( 'Color Divisor', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#444444',
				'selectors' => [
					'{{WRAPPER}} .alezux-course-divider' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		if ( ! is_user_logged_in() ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="alezux-msg" style="color:#fff;">' . __( 'Debes iniciar sesión para ver el progreso real.', 'alezux-members' ) . '</div>';
				
				// Mock data for editor
				$courses_data = [
					[ 'title' => 'Curso Demo 1', 'percentage' => 45 ],
					[ 'title' => 'Curso Demo 2', 'percentage' => 80 ],
					[ 'title' => 'Curso Demo 3', 'percentage' => 20 ],
				];
				$total_courses = 3;
				$total_progress_sum = 145;
				$average_progress = 48; // Mock avg
			} else {
				return;
			}
		} else {
			$user_id = get_current_user_id();
			$enrolled_courses = learndash_user_get_enrolled_courses( $user_id, [], true ); // Return IDs only
			
			$courses_data = [];
			$total_progress_sum = 0;
			$total_courses = count( $enrolled_courses );

			foreach ( $enrolled_courses as $course_id ) {
				$progress = learndash_course_progress( [
					'user_id'   => $user_id,
					'course_id' => $course_id,
					'array'     => true,
				] );
				
				$percentage = isset( $progress['percentage'] ) ? intval( $progress['percentage'] ) : 0;
				$total_progress_sum += $percentage;
				
				$courses_data[] = [
					'title'      => get_the_title( $course_id ),
					'percentage' => $percentage,
					'permalink'  => get_permalink( $course_id ),
				];
			}
			// Calculate Global Average
			$average_progress = ( $total_courses > 0 ) ? round( $total_progress_sum / $total_courses ) : 0;
		}


		// --- RENDER ---
		?>
		<div class="alezux-general-progress-wrapper">
			
			<?php if ( 'yes' === $settings['show_chart'] ) : ?>
				<?php 
					// Chart Logic
					$unique_id = $this->get_id();
					$chart_style = isset($settings['chart_style']) ? $settings['chart_style'] : 'segmented';
					
					$cx = 250;
					$cy = 240; 
					$r = 180;
					
					$active_color = $settings['chart_fill_color_start'];
					$inactive_color = $settings['chart_track_color'];
					$enable_glow_chart = isset($settings['chart_enable_glow']) ? $settings['chart_enable_glow'] : 'yes';

					// Helper for Solid Arc
					$describe_arc = function($x, $y, $radius, $start_angle, $end_angle) {
						$polar_to_cartesian = function($cx, $cy, $rad, $deg) {
							$radians = $deg * M_PI / 180.0;
							return [
								'x' => $cx + ($rad * cos($radians)),
								'y' => $cy + ($rad * sin($radians))
							];
						};
						
						$start = $polar_to_cartesian($x, $y, $radius, $end_angle);
						$end = $polar_to_cartesian($x, $y, $radius, $start_angle);
						$large_arc_flag = ($end_angle - $start_angle) <= 180 ? "0" : "1";
						
						return "M " . $start['x'] . " " . $start['y'] . " A $radius $radius 0 $large_arc_flag 0 " . $end['x'] . " " . $end['y'];
					};
				?>
				<div class="alezux-general-chart-container">
					<svg class="alezux-general-chart-svg" viewBox="0 0 500 300" preserveAspectRatio="xMidYMax meet">
						<defs>
							<filter id="glow-<?php echo esc_attr($unique_id); ?>" x="-150%" y="-150%" width="400%" height="400%">
								<feGaussianBlur stdDeviation="6" result="coloredBlur"/>
								<feMerge>
									<feMergeNode in="coloredBlur"/>
									<feMergeNode in="SourceGraphic"/>
								</feMerge>
							</filter>
						</defs>
						
						<?php if ( 'segmented' === $chart_style ) : 
							// Segmented/Ticks Style
							$ticks_count = isset($settings['chart_ticks_count']) ? intval($settings['chart_ticks_count']) : 30;
							$ticks_active = round( ($average_progress / 100) * $ticks_count );
							$tick_length = 45; 
							
							$start_angle = -175;
							$end_angle = -5;
							$total_angle = $end_angle - $start_angle; 
							$step_angle = $total_angle / ($ticks_count - 1); 
						?>
							<g class="alezux-chart-ticks">
								<?php for ($i = 0; $i < $ticks_count; $i++) : 
									$is_active = $i < $ticks_active;
									$angle_deg = $start_angle + ($i * $step_angle);
									$angle_rad = deg2rad($angle_deg);
									
									$x1 = $cx + ($r * cos($angle_rad));
									$y1 = $cy + ($r * sin($angle_rad));
									$x2 = $cx + ( ($r - $tick_length) * cos($angle_rad));
									$y2 = $cy + ( ($r - $tick_length) * sin($angle_rad));
									
									$color = $is_active ? $active_color : $inactive_color;
									$filter = ($is_active && 'yes' === $enable_glow_chart) ? "url(#glow-{$unique_id})" : "none";
								?>
									<line 
										class="<?php echo $is_active ? 'alezux-chart-tick-active' : 'alezux-chart-tick-inactive'; ?>"
										x1="<?php echo $x1; ?>" y1="<?php echo $y1; ?>" 
										x2="<?php echo $x2; ?>" y2="<?php echo $y2; ?>" 
										style="filter: <?php echo $filter; ?>;"
									/>
								<?php endfor; ?>
							</g>
						
						<?php else : 
							// Solid Style
							$bar_width = isset($settings['chart_bar_width']['size']) ? $settings['chart_bar_width']['size'] : 30;
							
							// Background Path (180 to 360)
							$bg_path = $describe_arc($cx, $cy, $r, 180, 360);
							
							// Progress Path
							$progress_end_angle = 180 + (180 * ($average_progress / 100));
							// Clamp progress angle
							if ($progress_end_angle > 360) $progress_end_angle = 360;
							if ($progress_end_angle < 180) $progress_end_angle = 180;

							$progress_path = $describe_arc($cx, $cy, $r, 180, $progress_end_angle);
							
							// Inner Dotted Line
							$inner_r = $r - ($bar_width/2) - 25; 
							$dotted_path = $describe_arc($cx, $cy, $inner_r, 180, 360);
						?>
							<path class="alezux-chart-bg" d="<?php echo $bg_path; ?>" fill="none" stroke-linecap="round" />
							<?php if ( intval($average_progress) >= 1 ) : ?>
								<path class="alezux-chart-progress" d="<?php echo $progress_path; ?>" fill="none" stroke-linecap="round" style="<?php echo ('yes' === $enable_glow_chart) ? "filter: url(#glow-{$unique_id});" : ""; ?>" />
							<?php endif; ?>
							<?php if ( $inner_r > 0 ): ?>
								<path d="<?php echo $dotted_path; ?>" fill="none" stroke="<?php echo esc_attr($inactive_color); ?>" stroke-width="2" stroke-dasharray="1 8" stroke-linecap="round" opacity="0.5" />
							<?php endif; ?>
						<?php endif; ?>
					</svg>
					
					<div class="alezux-chart-content">
						<?php
							$percent_style = '';
							if ( isset($settings['chart_percent_glow_show']) && 'yes' === $settings['chart_percent_glow_show'] ) {
								$glow_color = isset($settings['chart_percent_color']) ? $settings['chart_percent_color'] : '#ffb800';
								$percent_style = 'text-shadow: 0 0 15px ' . $glow_color . ';';
							}
						?>
						<div class="alezux-chart-percent" style="<?php echo esc_attr($percent_style); ?>"><?php echo esc_html( $average_progress ); ?>%</div>
						<?php if ( $settings['chart_label'] ) : ?>
							<div class="alezux-chart-label"><?php echo esc_html( $settings['chart_label'] ); ?></div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( 'yes' === $settings['show_list'] ) : ?>
				<div class="alezux-general-list">
					<?php foreach ( $courses_data as $course ) : ?>
						<div class="alezux-course-item">
							<div class="alezux-course-info">
								<a href="<?php echo isset($course['permalink']) ? esc_url($course['permalink']) : '#'; ?>" class="alezux-course-title">
									<?php echo esc_html( $course['title'] ); ?>
								</a>
								<span class="alezux-course-percent"><?php echo esc_html( $course['percentage'] ); ?>%</span>
							</div>
							<div class="alezux-course-divider">
								<div class="alezux-course-divider-fill" style="width: <?php echo esc_attr( $course['percentage'] ); ?>%; background-color: <?php echo esc_attr( $settings['list_percent_color'] ); ?>;"></div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</div>
		
		<style>
			.alezux-general-progress-wrapper {
				display: flex;
				flex-direction: column;
				align-items: center;
				font-family: 'Roboto', sans-serif; 
			}
			.alezux-general-chart-container {
				position: relative;
				display: flex;
				justify-content: center;
				margin-bottom: 20px;
				width: 100%; 
			}
			.alezux-general-chart-svg {
				overflow: visible; 
				height: auto;
			}
			
			.alezux-chart-content {
				position: absolute;
				bottom: 0;
				left: 0;
				right: 0;
				text-align: center;
				display: flex;
				flex-direction: column;
				justify-content: flex-end;
				pointer-events: none;
				padding-bottom: 30px; /* Space for bottom margin */
			}
			.alezux-chart-percent {
				font-size: 60px;
				font-weight: 700;
				line-height: 1;
				margin-bottom: 0px;
				transition: all 0.3s;
			}
			.alezux-chart-label {
				font-size: 16px;
				font-weight: 500;
				opacity: 0.9;
				letter-spacing: 0.5px;
			}
			
			/* List Styles */
			.alezux-general-list {
				width: 100%;
			}
			.alezux-course-item {
				margin-bottom: 15px;
			}
			.alezux-course-info {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 8px;
			}
			.alezux-course-title {
				text-decoration: none;
				font-weight: 500;
				flex-grow: 1;
				margin-right: 15px;
				white-space: nowrap;
				overflow: hidden;
				text-overflow: ellipsis;
				font-size: 15px;
			}
			.alezux-course-percent {
				font-weight: 700;
				font-size: 14px;
			}
			.alezux-course-divider {
				height: 2px;
				width: 100%;
				position: relative;
				border-radius: 2px;
				overflow: hidden;
			}
			.alezux-course-divider-fill {
				height: 100%;
				position: absolute;
				left: 0;
				top: 0;
				border-radius: 2px;
				box-shadow: 0 0 8px currentColor; /* simple glow matching color */
			}
		</style>
		<?php
	}
}
