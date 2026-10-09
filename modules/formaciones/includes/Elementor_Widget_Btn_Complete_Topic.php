<?php
namespace Alezux_Members\Modules\Formaciones\Includes;

use Alezux_Members\Core\Elementor_Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elementor_Widget_Btn_Complete_Topic extends Elementor_Widget_Base {

	public function get_name() {
		return 'alezux_btn_complete_topic';
	}

	public function get_title() {
		return __( 'Botón Completar Topic', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-check-circle-o';
	}

	public function get_categories() {
		return [ 'alezux-lms' ];
	}

	protected function register_widget_controls() {

		// --- Sección Contenido ---
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Contenido', 'alezux-members' ),
			]
		);

		$this->add_control(
			'text_incomplete',
			[
				'label' => __( 'Texto (Incompleto)', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Marcar como Completado', 'alezux-members' ),
				'placeholder' => __( 'Marcar como Completado', 'alezux-members' ),
			]
		);

		$this->add_control(
			'icon_incomplete',
			[
				'label' => __( 'Icono (Incompleto)', 'alezux-members' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'far fa-circle',
					'library' => 'fa-regular',
				],
			]
		);

		$this->add_control(
			'text_complete',
			[
				'label' => __( 'Texto (Completado)', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Completado', 'alezux-members' ),
				'placeholder' => __( 'Completado', 'alezux-members' ),
			]
		);

		$this->add_control(
			'icon_complete',
			[
				'label' => __( 'Icono (Completado)', 'alezux-members' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-check-circle',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'icon_align',
			[
				'label' => __( 'Posición del Icono', 'alezux-members' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Izquierda', 'alezux-members' ),
						'icon' => 'eicon-h-align-left',
					],
					'right' => [
						'title' => __( 'Derecha', 'alezux-members' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'default' => 'left',
				'toggle' => false,
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
					'{{WRAPPER}} .alezux-btn-complete-topic .elementor-button-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .alezux-btn-complete-topic .elementor-button-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Alineación', 'alezux-members' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left'    => [
						'title' => __( 'Izquierda', 'alezux-members' ),
						'icon' => 'eicon-text-align-left',
					],
					'center'  => [
						'title' => __( 'Centro', 'alezux-members' ),
						'icon' => 'eicon-text-align-center',
					],
					'right'   => [
						'title' => __( 'Derecha', 'alezux-members' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => __( 'Justificado', 'alezux-members' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'prefix_class' => 'elementor%s-align-',
				'default' => '',
			]
		);

		$this->end_controls_section();

		// --- Sección Estilo ---
		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Botón', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'selector' => '{{WRAPPER}} .alezux-btn-complete-topic',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		// Normal State (Incomplete)
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
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-complete-topic' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-btn-complete-topic svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_background',
				'label' => __( 'Fondo', 'alezux-members' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ], // Usually buttons don't have bg images
				'selector' => '{{WRAPPER}} .alezux-btn-complete-topic',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'selector' => '{{WRAPPER}} .alezux-btn-complete-topic',
			]
		);

		$this->end_controls_tab();

		// Hover State
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
					'{{WRAPPER}} .alezux-btn-complete-topic:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-btn-complete-topic:hover svg' => 'fill: {{VALUE}};',
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
				'selector' => '{{WRAPPER}} .alezux-btn-complete-topic:hover',
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label' => __( 'Color de Borde', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-complete-topic:hover' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'button_border_border!' => '',
				],
			]
		);

		$this->end_controls_tab();

		// Completed State
		$this->start_controls_tab(
			'tab_button_completed',
			[
				'label' => __( 'Completado', 'alezux-members' ),
			]
		);

		$this->add_control(
			'button_completed_text_color',
			[
				'label' => __( 'Color de Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-complete-topic.is-completed' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-btn-complete-topic.is-completed svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_completed_background',
				'label' => __( 'Fondo', 'alezux-members' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ],
				'selector' => '{{WRAPPER}} .alezux-btn-complete-topic.is-completed',
			]
		);

		$this->add_control(
			'button_completed_border_color',
			[
				'label' => __( 'Color de Borde', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-complete-topic.is-completed' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'button_border_border!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'button_border_radius',
			[
				'label' => __( 'Radio de Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-complete-topic' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => __( 'Relleno', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-complete-topic' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$post_id  = get_the_ID();
		
		if ( ! $post_id ) {
			return;
		}

		// Comprobar si el usuario está logueado
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Check content and user context safely
		$user_id = get_current_user_id();
		$is_completed = false;
        
        // --- 1. Obtener Contexto del Curso ---
        $course_id = 0;
        if ( function_exists( 'learndash_get_course_id' ) ) {
            $course_id = learndash_get_course_id( $post_id );
        }
        if ( ! $course_id ) {
            $course_id = get_post_meta( $post_id, 'course_id', true );
        }
		
		// --- 2. Verificar Completado (Lógica Robusta) ---
		if ( function_exists( 'learndash_is_target_complete' ) ) {
            // Pasamos null como tercer argumento para forzar chequeo sin caché si es posible, aunque la función no siempre lo soporta
			$is_completed = learndash_is_target_complete( $post_id, $user_id );
		}
        
        // Fallback: Si learndash dice false, validamos contra la DB directamente para evitar falso negativo visual
        if ( ! $is_completed ) {
            global $wpdb;
            $activity_type = 'topic';
            $pt = get_post_type($post_id);
            if('sfwd-lessons' === $pt) $activity_type = 'lesson';
            
            // Verificación directa en tabla de actividad
            $row = $wpdb->get_row( $wpdb->prepare(
                "SELECT activity_id FROM {$wpdb->prefix}learndash_user_activity 
                WHERE user_id = %d 
                AND post_id = %d 
                AND activity_type = %s 
                AND (activity_status = 1 OR activity_completed > 0)",
                $user_id,
                $post_id,
                $activity_type
            ) );
            
            if ( $row ) {
                $is_completed = true;
            }
        }
        
        // --- 3. Calcular Siguiente Paso (Redirección) ---
        $next_url = '';
        
        if ( $course_id ) {
            // Por defecto, si es el último, volvemos al curso
            $next_url = get_permalink( $course_id );
            
            // Intentar buscar el siguiente paso lógicamente (Topics de la lección actual o siguiente lección)
            if ( function_exists( 'learndash_get_next_lesson_redirect' ) ) {
                // Esta función de LD suele devolver el siguiente paso global
                $next_step_link = learndash_next_post_link( '', true, $post_id ); 
                // Nota: learndash_next_post_link devuelve HTML <a>, necesitamos la URL.
                // Usamos learndash_get_next_step_id mejor
            }

            // Lógica manual confiable para Topics
            $lesson_id = learndash_get_lesson_id( $post_id );
            if ( $lesson_id ) { 
                // Estamos en un Topic o Lección
                $course_steps = learndash_get_course_steps( $course_id );
                
                // Encontrar índice actual
                $current_step_key = array_search( $post_id, $course_steps );
                
                if ( $current_step_key !== false && isset( $course_steps[ $current_step_key + 1 ] ) ) {
                    // Hay un siguiente paso (Topic o Lección)
                    $next_step_id = $course_steps[ $current_step_key + 1 ];
                    $next_url = get_permalink( $next_step_id );
                } else {
                    // No hay siguiente paso, es el último del curso -> URL Curso (ya seteado por defecto)
                    // Opcional: Podríamos redirigir a un "Quiz" si existe.
                }
            }
        }

    	$this->add_render_attribute( 'button', 'class', [ 'alezux-btn-complete-topic', 'elementor-button' ] );
		$this->add_render_attribute( 'button', 'role', 'button' );
		$this->add_render_attribute( 'button', 'data-post-id', $post_id );
		
        // Solo generamos nonce si NO está completado, para evitar acciones de desmarcado via AJAX desde este botón
        if ( ! $is_completed ) {
		    $this->add_render_attribute( 'button', 'data-nonce', wp_create_nonce( 'alezux_toggle_complete_' . $post_id ) );
        }
        
        // Añadir URL de redirección
        if ( $next_url ) {
            $this->add_render_attribute( 'button', 'data-next-url', $next_url );
        }

		if ( $is_completed ) {
			$this->add_render_attribute( 'button', 'class', 'is-completed' );
            // Añadimos clase disabled-action para estilos si se quiere
            $this->add_render_attribute( 'button', 'class', 'action-next-only' );
		}

		$icon_html_incomplete = '';
		$icon_html_complete = '';

		// Prepare Icons
		if ( ! empty( $settings['icon_incomplete']['value'] ) ) {
			ob_start();
			Icons_Manager::render_icon( $settings['icon_incomplete'], [ 'aria-hidden' => 'true' ] );
			$icon_html_incomplete = ob_get_clean();
		}

		if ( ! empty( $settings['icon_complete']['value'] ) ) {
			ob_start();
			Icons_Manager::render_icon( $settings['icon_complete'], [ 'aria-hidden' => 'true' ] );
			$icon_html_complete = ob_get_clean();
		}

		// Icon alignment classes
		$icon_class_align = 'elementor-button-icon elementor-button-icon-' . $settings['icon_align'];

		// Output structure
		?>
		<div class="alezux-elementor-btn-wrapper">
			<a <?php echo $this->get_render_attribute_string( 'button' ); ?>>
				<span class="alezux-btn-content-wrapper">
					<!-- State: Incomplete -->
					<span class="alezux-btn-state state-incomplete" <?php echo $is_completed ? 'style="display:none;"' : ''; ?>>
						<?php if ( 'left' === $settings['icon_align'] && $icon_html_incomplete ) : ?>
							<span class="<?php echo esc_attr( $icon_class_align ); ?>"><?php echo $icon_html_incomplete; ?></span>
						<?php endif; ?>
						
						<span class="elementor-button-text"><?php echo esc_html( $settings['text_incomplete'] ); ?></span>

						<?php if ( 'right' === $settings['icon_align'] && $icon_html_incomplete ) : ?>
							<span class="<?php echo esc_attr( $icon_class_align ); ?>"><?php echo $icon_html_incomplete; ?></span>
						<?php endif; ?>
					</span>

					<!-- State: Complete -->
					<span class="alezux-btn-state state-completed" <?php echo ! $is_completed ? 'style="display:none;"' : ''; ?>>
						<?php if ( 'left' === $settings['icon_align'] && $icon_html_complete ) : ?>
							<span class="<?php echo esc_attr( $icon_class_align ); ?>"><?php echo $icon_html_complete; ?></span>
						<?php endif; ?>
						
						<span class="elementor-button-text"><?php echo esc_html( $settings['text_complete'] ); ?></span>

						<?php if ( 'right' === $settings['icon_align'] && $icon_html_complete ) : ?>
							<span class="<?php echo esc_attr( $icon_class_align ); ?>"><?php echo $icon_html_complete; ?></span>
						<?php endif; ?>
					</span>
					
					<!-- Loading spinner (optional, styled in CSS) -->
					<span class="alezux-btn-loader" style="display:none;"><i class="fas fa-spinner fa-spin"></i></span>
				</span>
			</a>
		</div>
		<?php
	}
}
