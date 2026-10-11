<?php
namespace Alezux_Members\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LMS_Engine
 * Motor central y unificado de LMS (Dual-Engine / Abstraction Layer).
 * Permite operar de forma 100% nativa e independiente sin dependencias externas,
 * manteniendo compatibilidad transparente si LearnDash está instalado.
 */
class LMS_Engine {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init() {
		// 1. Registrar Custom Post Type nativo para cursos
		add_action( 'init', [ $this, 'register_post_types' ] );

		// 2. Asegurar existencia de tablas de base de datos
		add_action( 'admin_init', [ $this, 'init_database_tables' ] );
		// También en instalación o carga si la tabla no existe
		add_action( 'init', [ $this, 'check_and_create_tables' ], 20 );
	}

	/**
	 * Verifica si LearnDash LMS está presente y activo
	 */
	public static function is_learndash_active() {
		return defined( 'LEARNDASH_VERSION' ) || class_exists( 'SFWD_LMS' );
	}

	/**
	 * Modo activo del LMS: 'auto' (detecta LD), 'native' (forzar propio) o 'learndash'
	 */
	public static function get_mode() {
		$mode = get_option( 'crezca_lms_mode', 'auto' );
		if ( 'auto' === $mode ) {
			return self::is_learndash_active() ? 'learndash' : 'native';
		}
		return $mode;
	}

	/**
	 * Post types de cursos admitidos para consultas (soporta cursos nativos y de LearnDash a la vez)
	 */
	public static function get_course_post_types() {
		$types = [ 'crezca_course' ];
		if ( self::is_learndash_active() || post_type_exists( 'sfwd-courses' ) ) {
			$types[] = 'sfwd-courses';
		}
		return $types;
	}

	/**
	 * Post type por defecto para la creación de nuevos cursos
	 */
	public static function get_default_course_post_type() {
		$mode = self::get_mode();
		if ( 'learndash' === $mode && self::is_learndash_active() ) {
			return 'sfwd-courses';
		}
		return 'crezca_course';
	}

	/**
	 * Registrar el CPT nativo 'crezca_course' en WordPress
	 */
	public function register_post_types() {
		$labels = [
			'name'               => _x( 'Cursos', 'post type general name', 'crezca' ),
			'singular_name'      => _x( 'Curso', 'post type singular name', 'crezca' ),
			'menu_name'          => _x( 'Cursos Crezca', 'admin menu', 'crezca' ),
			'name_admin_bar'     => _x( 'Curso Crezca', 'add new on admin bar', 'crezca' ),
			'add_new'            => _x( 'Añadir Nuevo', 'course', 'crezca' ),
			'add_new_item'       => __( 'Añadir Nuevo Curso', 'crezca' ),
			'new_item'           => __( 'Nuevo Curso', 'crezca' ),
			'edit_item'          => __( 'Editar Curso', 'crezca' ),
			'view_item'          => __( 'Ver Curso', 'crezca' ),
			'all_items'          => __( 'Todos los Cursos', 'crezca' ),
			'search_items'       => __( 'Buscar Cursos', 'crezca' ),
			'parent_item_colon'  => __( 'Cursos Superiores:', 'crezca' ),
			'not_found'          => __( 'No se encontraron cursos.', 'crezca' ),
			'not_found_in_trash' => __( 'No se encontraron cursos en la papelera.', 'crezca' ),
		];

		$args = [
			'labels'             => $labels,
			'description'        => __( 'Cursos y formaciones de la academia Crezca.', 'crezca' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false, // Se gestionan de forma moderna mediante el Dashboard Arc UI
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'cursos', 'with_front' => false ],
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon'          => 'dashicons-welcome-learn-more',
			'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt', 'author' ],
			'show_in_rest'       => true,
		];

		register_post_type( 'crezca_course', $args );
	}

	/**
	 * Crear tabla de progreso y respuestas de evaluación si no existe
	 */
	public function check_and_create_tables() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'crezca_topic_progress';
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
			$this->init_database_tables();
		}
	}

	public function init_database_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_progress = $wpdb->prefix . 'crezca_topic_progress';

		$sql = "CREATE TABLE $table_progress (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			course_id bigint(20) unsigned NOT NULL,
			topic_id varchar(100) NOT NULL,
			is_completed tinyint(1) NOT NULL DEFAULT 1,
			quiz_score int(11) NULL,
			quiz_answers longtext NULL,
			completed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY user_course_topic (user_id, course_id, topic_id),
			KEY user_course (user_id, course_id),
			KEY topic_idx (topic_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Verifica si un usuario tiene acceso a un curso
	 */
	public static function has_access( $course_id, $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		// Administradores y editores siempre tienen acceso libre
		if ( user_can( $user_id, 'manage_options' ) || user_can( $user_id, 'edit_posts' ) ) {
			return true;
		}

		$course_id = (int) $course_id;

		// 1. Si LearnDash está activo y tiene función de acceso, consultar
		if ( self::is_learndash_active() && function_exists( 'sfwd_lms_has_access' ) ) {
			$post_type = get_post_type( $course_id );
			if ( 'sfwd-courses' === $post_type && sfwd_lms_has_access( $course_id, $user_id ) ) {
				return true;
			}
		}

		// 2. Verificación nativa: lista de cursos habilitados del estudiante
		$enabled_courses = get_user_meta( $user_id, '_alezux_enabled_courses', true );
		if ( is_array( $enabled_courses ) && in_array( $course_id, array_map( 'intval', $enabled_courses ), true ) ) {
			return true;
		}

		// 3. Verificación con el módulo Finanzas (planes de suscripción o pago único)
		global $wpdb;
		$plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
		$subscriptions_table = $wpdb->prefix . 'alezux_finanzas_subscriptions';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$plans_table'" ) === $plans_table ) {
			// Buscar plan asignado al curso
			$plan_id = (int) get_post_meta( $course_id, '_course_linked_plan_id', true );
			if ( ! $plan_id ) {
				$plan_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $plans_table WHERE course_id = %d LIMIT 1", $course_id ) );
			}

			if ( $plan_id && $wpdb->get_var( "SHOW TABLES LIKE '$subscriptions_table'" ) === $subscriptions_table ) {
				$has_active_sub = $wpdb->get_var( $wpdb->prepare(
					"SELECT id FROM $subscriptions_table 
					 WHERE user_id = %d AND plan_id = %d AND status IN ('active', 'trialing', 'paid') 
					 LIMIT 1",
					$user_id,
					$plan_id
				) );
				if ( ! empty( $has_active_sub ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Inscribir a un estudiante en un curso
	 */
	public static function enroll_student( $user_id, $course_id ) {
		$user_id   = (int) $user_id;
		$course_id = (int) $course_id;

		if ( ! $user_id || ! $course_id ) {
			return false;
		}

		// 1. Guardar en meta nativo
		$courses = get_user_meta( $user_id, '_alezux_enabled_courses', true );
		if ( ! is_array( $courses ) ) {
			$courses = [];
		}
		if ( ! in_array( $course_id, $courses, true ) ) {
			$courses[] = $course_id;
			update_user_meta( $user_id, '_alezux_enabled_courses', $courses );
		}

		// 2. Si LearnDash está activo, matricularlo también en LearnDash
		if ( self::is_learndash_active() && function_exists( 'ld_update_course_access' ) ) {
			ld_update_course_access( $user_id, $course_id, false );
		}

		return true;
	}

	/**
	 * Dar de baja a un estudiante de un curso
	 */
	public static function unenroll_student( $user_id, $course_id ) {
		$user_id   = (int) $user_id;
		$course_id = (int) $course_id;

		if ( ! $user_id || ! $course_id ) {
			return false;
		}

		// 1. Remover de meta nativo
		$courses = get_user_meta( $user_id, '_alezux_enabled_courses', true );
		if ( is_array( $courses ) ) {
			$courses = array_values( array_diff( array_map( 'intval', $courses ), [ $course_id ] ) );
			update_user_meta( $user_id, '_alezux_enabled_courses', $courses );
		}

		// 2. Si LearnDash está activo, remover acceso en LearnDash
		if ( self::is_learndash_active() && function_exists( 'ld_update_course_access' ) ) {
			ld_update_course_access( $user_id, $course_id, true );
		}

		return true;
	}

	/**
	 * Comprobar si una lección, topic o subtopic está completado
	 */
	public static function is_topic_completed( $topic_id, $user_id = 0, $course_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( ! $user_id || empty( $topic_id ) ) {
			return false;
		}

		global $wpdb;
		$table_progress = $wpdb->prefix . 'crezca_topic_progress';

		// Consultar en tabla nativa
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_progress'" ) === $table_progress ) {
			$completed = $wpdb->get_var( $wpdb->prepare(
				"SELECT is_completed FROM $table_progress WHERE user_id = %d AND topic_id = %s LIMIT 1",
				$user_id,
				(string) $topic_id
			) );
			if ( null !== $completed ) {
				return (bool) $completed;
			}
		}

		// Fallback: verificar user meta
		$completed_meta = get_user_meta( $user_id, '_crezca_completed_topics', true );
		if ( is_array( $completed_meta ) && in_array( (string) $topic_id, array_map( 'strval', $completed_meta ), true ) ) {
			return true;
		}

		// Si es ID numérico y LearnDash está activo
		if ( is_numeric( $topic_id ) && self::is_learndash_active() && function_exists( 'learndash_is_target_complete' ) ) {
			return (bool) learndash_is_target_complete( (int) $topic_id, $user_id );
		}

		return false;
	}

	/**
	 * Marcar / Desmarcar como completado un topic o subtopic
	 */
	public static function toggle_topic_complete( $topic_id, $user_id = 0, $course_id = 0, $quiz_score = null, $quiz_answers = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( ! $user_id || empty( $topic_id ) ) {
			return false;
		}

		global $wpdb;
		$table_progress = $wpdb->prefix . 'crezca_topic_progress';
		$is_completed = self::is_topic_completed( $topic_id, $user_id, $course_id );
		$new_state = ! $is_completed;

		// 1. Actualizar tabla nativa de progreso
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_progress'" ) === $table_progress ) {
			if ( $new_state ) {
				$wpdb->replace(
					$table_progress,
					[
						'user_id'      => $user_id,
						'course_id'    => (int) $course_id,
						'topic_id'     => (string) $topic_id,
						'is_completed' => 1,
						'quiz_score'   => null !== $quiz_score ? (int) $quiz_score : null,
						'quiz_answers' => is_array( $quiz_answers ) ? wp_json_encode( $quiz_answers ) : null,
						'completed_at' => current_time( 'mysql' ),
					],
					[ '%d', '%d', '%s', '%d', '%d', '%s', '%s' ]
				);
			} else {
				$wpdb->delete(
					$table_progress,
					[
						'user_id'  => $user_id,
						'topic_id' => (string) $topic_id,
					],
					[ '%d', '%s' ]
				);
			}
		}

		// 2. Sincronizar user meta
		$completed_meta = get_user_meta( $user_id, '_crezca_completed_topics', true );
		if ( ! is_array( $completed_meta ) ) {
			$completed_meta = [];
		}

		$topic_str = (string) $topic_id;
		if ( $new_state ) {
			if ( ! in_array( $topic_str, $completed_meta, true ) ) {
				$completed_meta[] = $topic_str;
			}
		} else {
			$completed_meta = array_values( array_diff( $completed_meta, [ $topic_str ] ) );
		}
		update_user_meta( $user_id, '_crezca_completed_topics', $completed_meta );

		// 3. Si LearnDash está activo y es numérico, sincronizar con su API
		if ( is_numeric( $topic_id ) && self::is_learndash_active() ) {
			if ( $new_state && function_exists( 'learndash_process_mark_complete' ) ) {
				learndash_process_mark_complete( $user_id, (int) $topic_id );
			}
		}

		return [
			'is_completed' => $new_state,
			'completed_at' => current_time( 'mysql' ),
			'all_completed'=> $completed_meta,
		];
	}
}

// Alias para retrocompatibilidad
class_alias( 'Alezux_Members\\Core\\LMS_Engine', 'Crezca\\Core\\LMS_Engine' );
