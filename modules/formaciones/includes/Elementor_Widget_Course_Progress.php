<?php
namespace Alezux_Members\Modules\Formaciones\Includes;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elementor_Widget_Course_Progress extends Widget_Base {

	public function get_name() {
		return 'alezux_course_progress';
	}

	public function get_title() {
		return __( 'Alezux Course Progress', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-skill-bar';
	}

	public function get_categories() {
		return [ 'alezux-lms' ];
	}

	protected function register_controls() {
		// --- SECTION: CONTENT ---
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_title',
			[
				'label' => __( 'Show Title', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'alezux-members' ),
				'label_off' => __( 'Hide', 'alezux-members' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

		// --- SECTION: STYLE - TITLE ---
		$this->start_controls_section(
			'section_style_title',
			[
				'label' => __( 'Title', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => __( 'Text Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-progress-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .alezux-progress-title',
			]
		);

		$this->add_control(
			'title_spacing',
			[
				'label' => __( 'Bottom Spacing', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-progress-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// --- SECTION: STYLE - PROGRESS BAR ---
		$this->start_controls_section(
			'section_style_bar',
			[
				'label' => __( 'Progress Bar', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'bar_height',
			[
				'label' => __( 'Height', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-progress-bar-bg' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .alezux-progress-bar-fill' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'bar_bg_color',
			[
				'label' => __( 'Background Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-progress-bar-bg' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'bar_fill_background',
				'label' => __( 'Fill Color (Gradient/Solid)', 'alezux-members' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .alezux-progress-bar-fill',
			]
		);

		$this->add_control(
			'bar_border_radius',
			[
				'label' => __( 'Border Radius', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-progress-bar-bg' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .alezux-progress-bar-fill' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
		
		// --- SECTION: STYLE - PERCENTAGE ---
		$this->start_controls_section(
			'section_style_percentage',
			[
				'label' => __( 'Percentage Text', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		
		$this->add_control(
			'percentage_color',
			[
				'label' => __( 'Text Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-progress-percentage' => 'color: {{VALUE}}',
				],
			]
		);
		
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'percentage_typography',
				'selector' => '{{WRAPPER}} .alezux-progress-percentage',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		// 1. Get Course ID
		$course_id = learndash_get_course_id();
		if ( ! $course_id ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="alezux-editor-placeholder">' . __( 'Please place this widget inside a LearnDash Course, Lesson, or Topic.', 'alezux-members' ) . '</div>';
			}
			return;
		}

		// 2. Get Progress
		$user_id = get_current_user_id();
		$progress = learndash_course_progress( [
			'user_id'   => $user_id,
			'course_id' => $course_id,
			'array'     => true,
		] );

		$percentage = isset( $progress['percentage'] ) ? intval( $progress['percentage'] ) : 0;
		$course_title = get_the_title( $course_id );

		// 3. Render HTML
		?>
		<div class="alezux-course-progress-widget">
			<?php if ( 'yes' === $settings['show_title'] ) : ?>
				<div class="alezux-progress-header">
					<h3 class="alezux-progress-title"><?php echo esc_html( $course_title ); ?></h3>
					<span class="alezux-progress-percentage"><?php echo esc_html( $percentage ); ?>%</span>
				</div>
			<?php endif; ?>

			<div class="alezux-progress-bar-bg">
				<div class="alezux-progress-bar-fill" style="width: <?php echo esc_attr( $percentage ); ?>%;"></div>
			</div>
			
			<?php if ( 'yes' !== $settings['show_title'] ) : // If title is hidden, show percentage elsewhere? No per request description but maybe useful 
			?>
				<!-- Percentage is currently part of the header flex in common designs, if header hidden, percentage hidden based on logic above. Let's keep it simple. -->
			<?php endif; ?>
		</div>
		<style>
			.alezux-progress-header {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 10px;
			}
			.alezux-progress-title {
				margin: 0;
			}
			.alezux-progress-bar-bg {
				width: 100%;
				background-color: #eee;
				height: 10px;
				overflow: hidden;
			}
			.alezux-progress-bar-fill {
				height: 100%;
				background-color: #0073aa;
				transition: width 0.3s ease;
			}
		</style>
		<?php
	}
}
