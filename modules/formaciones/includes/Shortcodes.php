<?php
namespace Alezux_Members\Modules\Formaciones\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shortcodes
 * Maneja los shortcodes del módulo de formaciones.
 */
class Shortcodes {

	public function get_definitions() {
		return [

			'alezux_course_whatsapp' => [
				'callback'    => [ $this, 'render_whatsapp_link' ],
				'description' => \__( 'Muestra el enlace de WhatsApp configurado en el curso.', 'alezux-members' ),
			],
			'alezux_course_slack' => [
				'callback'    => [ $this, 'render_slack_link' ],
				'description' => \__( 'Muestra el enlace de Slack configurado en el curso.', 'alezux-members' ),
			],
			'alezux_course_zoom' => [
				'callback'    => [ $this, 'render_zoom_link' ],
				'description' => \__( 'Muestra el enlace de Zoom configurado en el curso.', 'alezux-members' ),
			],
			'alezux_resume_topic_name' => [
				'callback'    => [ $this, 'render_resume_topic_name' ],
				'description' => \__( 'Muestra el nombre del tema donde quedó el estudiante.', 'alezux-members' ),
			],
			'alezux_resume_topic_link' => [
				'callback'    => [ $this, 'render_resume_topic_link' ],
				'description' => \__( 'Muestra el enlace del tema donde quedó el estudiante.', 'alezux-members' ),
			],
			'alezux_course_count' => [
				'callback'    => [ $this, 'render_course_count' ],
				'description' => \__( 'Muestra el número total de cursos publicados.', 'alezux-members' ),
			],
		];
	}



	/**
	 * Shortcode: [alezux_course_whatsapp]
	 * Retorna el enlace de Whatsapp del curso actual.
	 */
	public function render_whatsapp_link( $atts ) {
		$course_id = \learndash_get_course_id();
		if ( ! $course_id ) return '';

		$link = \get_post_meta( $course_id, '_alezux_course_whatsapp', true );
		return \esc_url( $link );
	}

	/**
	 * Shortcode: [alezux_course_slack]
	 * Retorna el enlace de Slack del curso actual.
	 */
	public function render_slack_link( $atts ) {
		$course_id = \learndash_get_course_id();
		if ( ! $course_id ) return '';

		$link = \get_post_meta( $course_id, '_alezux_course_slack', true );
		return \esc_url( $link );
	}

	/**
	 * Shortcode: [alezux_course_zoom]
	 * Retorna el enlace de Zoom del curso actual.
	 */
	public function render_zoom_link( $atts ) {
		$course_id = \learndash_get_course_id();
		if ( ! $course_id ) return '';

		$link = \get_post_meta( $course_id, '_alezux_course_zoom', true );
		return \esc_url( $link );
	}

	/**
	 * Helper: Obtiene el ID del último paso (lección/tema) visitado por el usuario de forma global.
	 */
	private function get_global_last_step( $user_id ) {
		global $wpdb;
		$activity_table = $wpdb->prefix . 'learndash_user_activity';

		// Verificar si la tabla existe por seguridad (aunque debería en LD)
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$activity_table'" ) != $activity_table ) {
			return 0;
		}

		// Consultar la actividad más reciente de tipo 'lesson' o 'topic'
		$query = $wpdb->prepare(
			"SELECT post_id 
			 FROM $activity_table 
			 WHERE user_id = %d 
			 AND activity_type IN ('lesson', 'topic') 
			 ORDER BY activity_updated DESC 
			 LIMIT 1",
			$user_id
		);

		$last_step_id = $wpdb->get_var( $query );

		return $last_step_id ? \intval( $last_step_id ) : 0;
	}

	/**
	 * Shortcode: [alezux_resume_topic_name]
	 * Retorna el nombre del último paso visitado global o por curso.
	 * Atributos:
	 *  - course_id: (int) ID del curso específico.
	 *  - global: (string) "yes" para forzar búsqueda global ignorando el contexto actual.
	 */
	public function render_resume_topic_name( $atts ) {
		if ( ! \is_user_logged_in() ) {
			return '';
		}

		$atts = \shortcode_atts( [
			'course_id' => 0,
			'global'    => 'no', // 'yes' para forzar global
		], $atts );

		$user_id = \get_current_user_id();
		$step_id = 0;
		$force_global = ( 'yes' === $atts['global'] );

		// 1. Determinar el Course ID objetivo (solo si no forzamos global)
		$target_course_id = 0;
		if ( ! $force_global ) {
			if ( $atts['course_id'] ) {
				$target_course_id = \intval( $atts['course_id'] );
			} else {
				$target_course_id = \learndash_get_course_id();
			}
		}

		if ( $target_course_id ) {
			// Lógica específica del curso
			$step_id = \learndash_course_get_last_step( $target_course_id, $user_id );
			
			// Fallback al primer paso si no hay progreso en ESTE curso
			if ( ! $step_id ) {
				$course_steps = \learndash_course_get_steps_by_type( $target_course_id, 'sfwd-lessons' );
				if ( ! empty( $course_steps ) ) {
					$step_id = $course_steps[0];
				}
			}
		} else {
			// 2. Si NO hay contexto de curso, buscar GLOBALMENTE el último visitado
			$step_id = $this->get_global_last_step( $user_id );
		}

		if ( ! $step_id ) {
			return '';
		}

		return \get_the_title( $step_id );
	}

	/**
	 * Shortcode: [alezux_resume_topic_link]
	 * Retorna el enlace del último paso visitado global o por curso.
	 */
	public function render_resume_topic_link( $atts ) {
		if ( ! \is_user_logged_in() ) {
			return '';
		}

		$atts = \shortcode_atts( [
			'course_id' => 0,
			'global'    => 'no',
		], $atts );

		$user_id = \get_current_user_id();
		$step_id = 0;
		$force_global = ( 'yes' === $atts['global'] );

		// 1. Determinar el Course ID objetivo (solo si no forzamos global)
		$target_course_id = 0;
		if ( ! $force_global ) {
			if ( $atts['course_id'] ) {
				$target_course_id = \intval( $atts['course_id'] );
			} else {
				$target_course_id = \learndash_get_course_id();
			}
		}

		if ( $target_course_id ) {
			$step_id = \learndash_course_get_last_step( $target_course_id, $user_id );
			
			if ( ! $step_id ) {
				$course_steps = \learndash_course_get_steps_by_type( $target_course_id, 'sfwd-lessons' );
				if ( ! empty( $course_steps ) ) {
					$step_id = $course_steps[0];
				}
			}
		} else {
			// 2. Global
			$step_id = $this->get_global_last_step( $user_id );
		}

		if ( ! $step_id ) {
			return '';
		}

		return \get_permalink( $step_id );
	}

	/**
	 * Shortcode: [alezux_course_count]
	 * Retorna el número total de cursos publicados.
	 */
	public function render_course_count( $atts ) {
		$count_posts = \wp_count_posts( 'sfwd-courses' );
		if ( isset( $count_posts->publish ) ) {
			return $count_posts->publish;
		}
		return '0';
	}
}
