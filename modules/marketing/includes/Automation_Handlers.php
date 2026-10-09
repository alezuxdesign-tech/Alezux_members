<?php
namespace Alezux_Members\Modules\Marketing\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Automation_Handlers {

	public function __construct() {
		// Capturar cuando se publica un curso o lección
		add_action( 'transition_post_status', [ $this, 'capture_new_content' ], 10, 3 );
		
		// Hook para procesar la cola (llamado por Cron)
		add_action( 'alezux_marketing_process_queue', [ $this, 'process_notification_queue' ] );
	}

	/**
	 * Detecta la publicación de nuevo contenido y lo añade a la cola de espera.
	 */
	public function capture_new_content( $new_status, $old_status, $post ) {
		// Solo nos interesa cuando pasa a 'publish' desde cualquier otro estado (excepto si ya era publish)
		if ( 'publish' !== $new_status || 'publish' === $old_status ) {
			return;
		}

		$allowed_types = [ 'sfwd-courses', 'sfwd-lessons' ];
		if ( ! in_array( $post->post_type, $allowed_types ) ) {
			return;
		}

		$log_file = ALEZUX_MEMBERS_PATH . 'debug_status.txt';
		file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] MARKETING: Detectado post publicado ({$post->post_type}): {$post->post_title}\n", FILE_APPEND );

		// 1. Obtener la cola actual
		$queue = get_option( 'alezux_marketing_pending_notifs', [ 'courses' => [], 'lessons' => [] ] );

		if ( 'sfwd-courses' === $post->post_type ) {
			// Añadir curso si no está ya
			if ( ! in_array( $post->ID, $queue['courses'] ) ) {
				$queue['courses'][] = $post->ID;
			}
		} elseif ( 'sfwd-lessons' === $post->post_type ) {
			// Obtener curso padre
			$course_id = 0;
			if ( function_exists( 'learndash_get_course_id' ) ) {
				$course_id = learndash_get_course_id( $post->ID );
			}
			
			if ( $course_id ) {
				if ( ! isset( $queue['lessons'][ $course_id ] ) ) {
					$queue['lessons'][ $course_id ] = [];
				}
				if ( ! in_array( $post->ID, $queue['lessons'][ $course_id ] ) ) {
					$queue['lessons'][ $course_id ][] = $post->ID;
				}
			}
		}

		// 2. Guardar la cola actualizada
		update_option( 'alezux_marketing_pending_notifs', $queue );

		// 3. (Re)programar el evento para dentro de 10 minutos
		// Cancelamos si ya hay uno programado para que el "reloj" vuelva a empezar
		wp_clear_scheduled_hook( 'alezux_marketing_process_queue' );
		wp_schedule_single_event( time() + 600, 'alezux_marketing_process_queue' );
		
		file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] MARKETING: Evento agendado para dentro de 10 minutos.\n", FILE_APPEND );
	}

	/**
	 * Procesa la cola y envía los correos consolidados.
	 */
	public function process_notification_queue() {
		$log_file = ALEZUX_MEMBERS_PATH . 'debug_status.txt';
		file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] MARKETING CRON: Iniciando procesado de cola...\n", FILE_APPEND );

		$queue = get_option( 'alezux_marketing_pending_notifs' );
		if ( empty( $queue ) || ( empty( $queue['courses'] ) && empty( $queue['lessons'] ) ) ) {
			file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] MARKETING CRON: Cola vacía. Nada que enviar.\n", FILE_APPEND );
			return;
		}

		// Limpiar la cola antes de procesar para evitar duplicados si algo falla
		update_option( 'alezux_marketing_pending_notifs', [ 'courses' => [], 'lessons' => [] ] );

		$marketing = \Alezux_Members\Modules\Marketing\Marketing::get_instance();
		$engine = $marketing->get_engine();

		// --- 1. PROCESAR CURSOS (A TODOS LOS USUARIOS) ---
		if ( ! empty( $queue['courses'] ) ) {
			$this->send_courses_notification( $queue['courses'], $engine );
		}

		// --- 2. PROCESAR LECCIONES (SOLO A INSCRITOS POR CURSO) ---
		if ( ! empty( $queue['lessons'] ) ) {
			foreach ( $queue['lessons'] as $course_id => $lesson_ids ) {
				$this->send_lessons_notification( $course_id, $lesson_ids, $engine );
			}
		}

		file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] MARKETING CRON: Fin del procesado.\n", FILE_APPEND );
	}

	private function send_courses_notification( $course_ids, $engine ) {
		$log_file = ALEZUX_MEMBERS_PATH . 'debug_status.txt';
		
		// Construir lista de cursos
		$courses_list = '<ul style="margin: 0; padding-left: 20px;">';
		$last_course_name = '';
		foreach ( $course_ids as $id ) {
			$title = get_the_title( $id );
			$url = get_permalink( $id );
			$courses_list .= "<li><a href='{$url}'>{$title}</a></li>";
			$last_course_name = $title;
		}
		$courses_list .= '</ul>';

		// Obtener todos los usuarios (Rol student y subscriber o todos según petición)
		$users = get_users( [ 'fields' => [ 'ID', 'user_email', 'display_name' ] ] );
		
		file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] MARKETING: Enviando aviso de curso nuevo a " . count($users) . " usuarios.\n", FILE_APPEND );

		foreach ( $users as $user ) {
			$u_obj = new \WP_User( $user->ID );
			$data = [
				'user' => $u_obj,
				'course_name' => $last_course_name, // fallback para variables simples
				'courses_list' => $courses_list
			];
			$engine->send_email( 'course_available', $user->user_email, $data );
		}
	}

	private function send_lessons_notification( $course_id, $lesson_ids, $engine ) {
		$log_file = ALEZUX_MEMBERS_PATH . 'debug_status.txt';
		$course_name = get_the_title( $course_id );
		
		// Construir lista de lecciones
		$lessons_list = '';
		foreach ( $lesson_ids as $id ) {
			$title = get_the_title( $id );
			$url = get_permalink( $id );
			$lessons_list .= "<li><a href='{$url}'>{$title}</a></li>";
		}

		if ( ! function_exists( 'learndash_get_users_for_course' ) ) {
			file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] MARKETING ERROR: LearnDash no activo para obtener alumnos.\n", FILE_APPEND );
			return;
		}

		// Obtener alumnos inscritos
		$student_ids = learndash_get_users_for_course( $course_id );
		
		file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] MARKETING: Enviando aviso de lecciones ({$course_name}) a " . count($student_ids) . " alumnos inscritos.\n", FILE_APPEND );

		foreach ( $student_ids as $uid ) {
			$u_obj = get_userdata( $uid );
			if ( ! $u_obj ) continue;

			$data = [
				'user' => $u_obj,
				'course_name' => $course_name,
				'lessons_list' => $lessons_list
			];
			$engine->send_email( 'lesson_available', $u_obj->user_email, $data );
		}
	}
}
