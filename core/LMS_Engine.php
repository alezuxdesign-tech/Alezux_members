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
	 * Post types de cursos admitidos para consultas según el modo elegido
	 */
	public static function get_course_post_types() {
		$mode = get_option( 'crezca_lms_mode', 'auto' );
		if ( 'native' === $mode ) {
			return [ 'crezca_course' ];
		}
		if ( 'learndash' === $mode && ( self::is_learndash_active() || post_type_exists( 'sfwd-courses' ) ) ) {
			return [ 'sfwd-courses' ];
		}
		// 'auto': incluye crezca_course y sfwd-courses si existe
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
	 * Obtener mapa bidireccional entre IDs de cursos de LearnDash y Crezca LMS Nativo
	 */
	public static function get_course_id_map() {
		global $wpdb;
		$results = $wpdb->get_results(
			"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_crezca_migrated_from_ld_id'"
		);

		$ld_to_native = [];
		$native_to_ld = [];

		if ( ! empty( $results ) ) {
			foreach ( $results as $row ) {
				$native_id = (int) $row->post_id;
				$ld_id     = (int) $row->meta_value;
				if ( $native_id > 0 && $ld_id > 0 ) {
					$ld_to_native[ $ld_id ]     = $native_id;
					$native_to_ld[ $native_id ] = $ld_id;
				}
			}
		}

		return [
			'ld_to_native' => $ld_to_native,
			'native_to_ld' => $native_to_ld,
		];
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
		if ( ! $course_id ) {
			return false;
		}

		// 1. Verificación nativa directa: lista de cursos habilitados del estudiante
		$enabled_courses = get_user_meta( $user_id, '_alezux_enabled_courses', true );
		$enabled_array   = is_array( $enabled_courses ) ? array_map( 'intval', $enabled_courses ) : [];
		if ( in_array( $course_id, $enabled_array, true ) ) {
			return true;
		}

		// 2. Si el curso es nativo y fue migrado de LearnDash, comprobar acceso al curso original
		$orig_ld_id = (int) get_post_meta( $course_id, '_crezca_migrated_from_ld_id', true );
		if ( $orig_ld_id > 0 ) {
			if ( in_array( $orig_ld_id, $enabled_array, true ) ) {
				self::enroll_student( $user_id, $course_id );
				return true;
			}
			if ( self::is_learndash_active() ) {
				if ( ( function_exists( 'sfwd_lms_has_access' ) && sfwd_lms_has_access( $orig_ld_id, $user_id ) )
					|| ( function_exists( 'ld_course_check_user_access' ) && ld_course_check_user_access( $orig_ld_id, $user_id ) ) ) {
					self::enroll_student( $user_id, $course_id );
					return true;
				}
			}
		}

		// 3. Si el curso es LearnDash y fue migrado a nativo, comprobar acceso al curso nativo
		$post_type = get_post_type( $course_id );
		if ( 'sfwd-courses' === $post_type ) {
			global $wpdb;
			$native_id = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_crezca_migrated_from_ld_id' AND meta_value = %d LIMIT 1",
				$course_id
			) );
			if ( $native_id > 0 && in_array( $native_id, $enabled_array, true ) ) {
				return true;
			}
			if ( self::is_learndash_active() ) {
				if ( ( function_exists( 'sfwd_lms_has_access' ) && sfwd_lms_has_access( $course_id, $user_id ) )
					|| ( function_exists( 'ld_course_check_user_access' ) && ld_course_check_user_access( $course_id, $user_id ) ) ) {
					return true;
				}
			}
		}

		// 4. Verificación con el módulo Finanzas (planes de suscripción o pago único)
		global $wpdb;
		$plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
		$subscriptions_table = $wpdb->prefix . 'alezux_finanzas_subscriptions';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$plans_table'" ) === $plans_table ) {
			$check_ids = [ $course_id ];
			if ( ! empty( $orig_ld_id ) ) {
				$check_ids[] = $orig_ld_id;
			}
			foreach ( $check_ids as $cid ) {
				$plan_id = (int) get_post_meta( $cid, '_course_linked_plan_id', true );
				if ( ! $plan_id ) {
					$plan_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $plans_table WHERE course_id = %d LIMIT 1", $cid ) );
				}

				if ( $plan_id && $wpdb->get_var( "SHOW TABLES LIKE '$subscriptions_table'" ) === $subscriptions_table ) {
					$has_active_sub = $wpdb->get_var( $wpdb->prepare(
						"SELECT id FROM $subscriptions_table 
						 WHERE user_id = %d AND plan_id = %d AND status IN ('active', 'trialing', 'paid', 'completed') 
						 LIMIT 1",
						$user_id,
						$plan_id
					) );
					if ( ! empty( $has_active_sub ) ) {
						self::enroll_student( $user_id, $course_id );
						return true;
					}
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
		$courses = is_array( $courses ) ? array_map( 'intval', $courses ) : [];
		if ( ! in_array( $course_id, $courses, true ) ) {
			$courses[] = $course_id;
			update_user_meta( $user_id, '_alezux_enabled_courses', array_values( array_unique( $courses ) ) );
		}

		// 2. Si LearnDash está activo, matricularlo también en LearnDash
		if ( self::is_learndash_active() && function_exists( 'ld_update_course_access' ) ) {
			ld_update_course_access( $user_id, $course_id, false );
			$orig_ld_id = (int) get_post_meta( $course_id, '_crezca_migrated_from_ld_id', true );
			if ( $orig_ld_id > 0 ) {
				ld_update_course_access( $user_id, $orig_ld_id, false );
			}
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

	/**
	 * Obtener diagnóstico y estadísticas actuales de ambos motores LMS
	 */
	public static function get_lms_stats() {
		$ld_courses = get_posts([
			'post_type'      => 'sfwd-courses',
			'post_status'    => [ 'publish', 'draft', 'private' ],
			'posts_per_page' => -1,
			'fields'         => 'ids',
		]);
		$native_courses = get_posts([
			'post_type'      => 'crezca_course',
			'post_status'    => [ 'publish', 'draft', 'private' ],
			'posts_per_page' => -1,
			'fields'         => 'ids',
		]);

		return [
			'mode'                    => get_option( 'crezca_lms_mode', 'auto' ),
			'active_mode'             => self::get_mode(),
			'is_learndash_active'     => self::is_learndash_active(),
			'learndash_courses_count' => count( $ld_courses ),
			'native_courses_count'    => count( $native_courses ),
		];
	}

	/**
	 * Cambiar modo del motor LMS ('auto', 'native', 'learndash')
	 */
	public static function set_lms_mode( $mode ) {
		if ( in_array( $mode, [ 'auto', 'native', 'learndash' ], true ) ) {
			update_option( 'crezca_lms_mode', $mode );
			return true;
		}
		return false;
	}

	/**
	 * Migrar todos los cursos de LearnDash a cursos nativos de Crezca
	 */
	public static function migrate_from_learndash( $overwrite = false ) {
		$ld_courses = get_posts([
			'post_type'      => 'sfwd-courses',
			'post_status'    => [ 'publish', 'draft', 'private' ],
			'posts_per_page' => -1,
		]);

		if ( empty( $ld_courses ) ) {
			return [
				'success'          => false,
				'message'          => __( 'No se encontraron cursos de LearnDash (sfwd-courses) para migrar.', 'crezca' ),
				'migrated_courses' => 0,
			];
		}

		$migrated_courses_count  = 0;
		$migrated_sections_count = 0;
		$migrated_lessons_count  = 0;
		$migrated_students_count = 0;
		$details = [];

		foreach ( $ld_courses as $ld_course ) {
			$ld_id = $ld_course->ID;

			// Verificar si ya fue migrado previamente
			$existing_native_id = 0;
			$existing_query = get_posts([
				'post_type'      => 'crezca_course',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_key'       => '_crezca_migrated_from_ld_id',
				'meta_value'     => $ld_id,
				'fields'         => 'ids',
			]);

			if ( ! empty( $existing_query ) ) {
				$existing_native_id = (int) $existing_query[0];
				if ( ! $overwrite ) {
					$details[] = [
						'ld_id'     => $ld_id,
						'title'     => $ld_course->post_title,
						'status'    => 'skipped',
						'native_id' => $existing_native_id,
						'message'   => __( 'Ya fue migrado previamente.', 'crezca' ),
					];
					continue;
				}
			}

			// 1. Obtener o construir estructura curricular en formato JSON de Crezca
			$curriculum = get_post_meta( $ld_id, '_alezux_curriculum_structure', true );
			if ( empty( $curriculum ) || ! is_array( $curriculum ) ) {
				$curriculum = self::build_curriculum_from_learndash_course( $ld_id );
			}

			// Contar secciones y lecciones para el reporte
			if ( is_array( $curriculum ) ) {
				$migrated_sections_count += count( $curriculum );
				foreach ( $curriculum as $sec ) {
					$lessons = isset( $sec['lessons'] ) ? $sec['lessons'] : ( isset( $sec['topics'] ) ? $sec['topics'] : [] );
					$migrated_lessons_count += count( $lessons );
				}
			}

			// 2. Crear o actualizar curso nativo crezca_course
			$post_data = [
				'post_title'   => $ld_course->post_title,
				'post_content' => $ld_course->post_content,
				'post_status'  => $ld_course->post_status,
				'post_type'    => 'crezca_course',
			];

			if ( $existing_native_id ) {
				$post_data['ID'] = $existing_native_id;
				$native_id = wp_update_post( $post_data );
			} else {
				$native_id = wp_insert_post( $post_data );
			}

			if ( is_wp_error( $native_id ) || ! $native_id ) {
				continue;
			}

			// 3. Migrar metadatos
			update_post_meta( $native_id, '_crezca_migrated_from_ld_id', $ld_id );
			update_post_meta( $native_id, '_alezux_curriculum_structure', $curriculum );

			// Portada / Thumbnail
			$thumb_id = get_post_thumbnail_id( $ld_id );
			if ( $thumb_id ) {
				set_post_thumbnail( $native_id, $thumb_id );
			}

			// Precios y planes
			$price = get_post_meta( $ld_id, '_alezux_course_price', true );
			if ( '' !== $price && false !== $price ) {
				update_post_meta( $native_id, '_alezux_course_price', $price );
			}
			$plan_id = get_post_meta( $ld_id, '_alezux_course_plan_id', true );
			if ( $plan_id ) {
				update_post_meta( $native_id, '_alezux_course_plan_id', $plan_id );
			}

			// Docentes / Mentores
			$mentors = get_post_meta( $ld_id, '_alezux_course_mentors', true );
			if ( ! empty( $mentors ) ) {
				update_post_meta( $native_id, '_alezux_course_mentors', $mentors );
			}

			// 4. Migrar estudiantes matriculados
			$enrolled_users = self::get_learndash_enrolled_users( $ld_id );
			$course_students_count = 0;
			foreach ( $enrolled_users as $user_id ) {
				self::enroll_student( $user_id, $native_id );
				$course_students_count++;
			}
			$migrated_students_count += $course_students_count;

			// 5. Migrar progreso de los estudiantes a la tabla wp_crezca_topic_progress
			self::migrate_learndash_course_progress( $ld_id, $native_id, $curriculum, $enrolled_users );

			$migrated_courses_count++;
			$details[] = [
				'ld_id'     => $ld_id,
				'native_id' => $native_id,
				'title'     => $ld_course->post_title,
				'status'    => 'migrated',
				'students'  => $course_students_count,
			];
		}

		// Cambiar modo a 'native' para que Crezca asuma el control completo
		update_option( 'crezca_lms_mode', 'native' );

		// Sincronización exhaustiva de matrículas de todos los cursos migrados
		$sync_result = self::sync_all_migrated_course_enrollments();
		if ( ! empty( $sync_result['synced_students'] ) ) {
			$migrated_students_count = max( $migrated_students_count, (int) $sync_result['synced_students'] );
		}

		return [
			'success'                 => true,
			'migrated_courses'        => $migrated_courses_count,
			'migrated_sections'       => $migrated_sections_count,
			'migrated_lessons'        => $migrated_lessons_count,
			'migrated_students'       => $migrated_students_count,
			'details'                 => $details,
			'message'                 => sprintf(
				__( '¡Migración completada con éxito! Se migraron %d cursos, %d lecciones y %d accesos de estudiantes a Crezca LMS.', 'crezca' ),
				$migrated_courses_count,
				$migrated_lessons_count,
				$migrated_students_count
			),
		];
	}

	/**
	 * Construir estructura curricular a partir de las lecciones y temas nativos de LearnDash
	 */
	public static function build_curriculum_from_learndash_course( $course_id ) {
		$curriculum = [];
		$lessons = [];

		if ( function_exists( 'learndash_get_course_lessons_list' ) ) {
			$lessons_raw = learndash_get_course_lessons_list( $course_id, null, [ 'num' => -1 ] );
			if ( is_array( $lessons_raw ) ) {
				foreach ( $lessons_raw as $l ) {
					if ( isset( $l['post'] ) && is_object( $l['post'] ) ) {
						$lessons[] = $l['post'];
					}
				}
			}
		}

		if ( empty( $lessons ) ) {
			$lessons = get_posts([
				'post_type'      => 'sfwd-lessons',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'meta_query'     => [
					[
						'key'     => 'course_id',
						'value'   => $course_id,
						'compare' => '=',
					],
				],
			]);
		}

		$formatted_topics = [];
		foreach ( $lessons as $l_idx => $lesson ) {
			$l_id = is_object( $lesson ) ? $lesson->ID : (int) $lesson;
			$l_post = get_post( $l_id );
			if ( ! $l_post ) continue;

			// Video de la lección
			$video_url = get_post_meta( $l_id, '_lesson_video_url', true );
			if ( empty( $video_url ) ) {
				$video_url = get_post_meta( $l_id, '_alezux_video_url', true );
			}
			if ( empty( $video_url ) ) {
				$ld_settings = get_post_meta( $l_id, '_sfwd-lessons', true );
				if ( is_array( $ld_settings ) && ! empty( $ld_settings['sfwd-lessons_lesson_video_url'] ) ) {
					$video_url = $ld_settings['sfwd-lessons_lesson_video_url'];
				}
			}
			if ( empty( $video_url ) && ! empty( $l_post->post_content ) ) {
				if ( preg_match( '/(\[vdo\s+id=["\']?[a-zA-Z0-9_-]+["\']?[^\]]*\])/i', $l_post->post_content, $vdo_m ) ) {
					$video_url = $vdo_m[1];
				} elseif ( preg_match( '/<iframe[^>]+src=["\']([^"\']+)["\']/i', $l_post->post_content, $if_m ) ) {
					$video_url = $if_m[0];
				}
			}

			// Topics de esta lección (se convierten en subtopics en Crezca)
			$subtopics = [];
			$topics_raw = [];
			if ( function_exists( 'learndash_get_topic_list' ) ) {
				$topics_raw = learndash_get_topic_list( $l_id, $course_id );
			}
			if ( empty( $topics_raw ) ) {
				$topics_raw = get_posts([
					'post_type'      => 'sfwd-topic',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
					'meta_query'     => [
						[
							'key'     => 'lesson_id',
							'value'   => $l_id,
							'compare' => '=',
						],
					],
				]);
			}

			if ( ! empty( $topics_raw ) ) {
				foreach ( $topics_raw as $t_idx => $top ) {
					$t_id = is_object( $top ) ? $top->ID : (int) $top;
					$t_post = get_post( $t_id );
					if ( ! $t_post ) continue;

					$sub_video = get_post_meta( $t_id, '_topic_video_url', true );
					if ( empty( $sub_video ) ) {
						$sub_video = get_post_meta( $t_id, '_alezux_video_url', true );
					}
					if ( empty( $sub_video ) ) {
						$t_settings = get_post_meta( $t_id, '_sfwd-topic', true );
						if ( is_array( $t_settings ) && ! empty( $t_settings['sfwd-topic_lesson_video_url'] ) ) {
							$sub_video = $t_settings['sfwd-topic_lesson_video_url'];
						}
					}
					if ( empty( $sub_video ) && ! empty( $t_post->post_content ) ) {
						if ( preg_match( '/(\[vdo\s+id=["\']?[a-zA-Z0-9_-]+["\']?[^\]]*\])/i', $t_post->post_content, $vdo_m ) ) {
							$sub_video = $vdo_m[1];
						} elseif ( preg_match( '/<iframe[^>]+src=["\']([^"\']+)["\']/i', $t_post->post_content, $if_m ) ) {
							$sub_video = $if_m[0];
						}
					}

					$subtopics[] = [
						'id'          => 'sub_' . $t_id,
						'title'       => $t_post->post_title,
						'duration'    => '10m',
						'video_url'   => $sub_video ? $sub_video : '',
						'description' => wp_strip_all_tags( $t_post->post_content ),
						'files'       => [],
						'questions'   => [],
					];
				}
			}

			$formatted_topics[] = [
				'id'          => 'top_' . $l_id,
				'title'       => $l_post->post_title,
				'duration'    => '15m',
				'video_url'   => $video_url ? $video_url : '',
				'description' => wp_strip_all_tags( $l_post->post_content ),
				'files'       => [],
				'questions'   => [],
				'subtopics'   => $subtopics,
			];
		}

		$curriculum[] = [
			'id'          => 'sec_main',
			'title'       => __( 'Módulo Principal', 'crezca' ),
			'description' => '',
			'lessons'     => $formatted_topics,
		];

		return $curriculum;
	}

	/**
	 * Obtener IDs de usuarios inscritos en un curso de LearnDash
	 */
	public static function get_learndash_enrolled_users( $course_id ) {
		global $wpdb;
		$user_ids  = [];
		$course_id = (int) $course_id;
		if ( ! $course_id ) {
			return [];
		}

		// 1. LearnDash función oficial: learndash_get_users_for_course
		if ( function_exists( 'learndash_get_users_for_course' ) ) {
			$ld_users = learndash_get_users_for_course( $course_id );
			if ( is_array( $ld_users ) ) {
				foreach ( $ld_users as $u ) {
					$user_ids[] = is_object( $u ) ? (int) $u->ID : (int) $u;
				}
			}
		}

		// 2. LearnDash función alternativa: learndash_get_course_users
		if ( function_exists( 'learndash_get_course_users' ) ) {
			$ld_users = learndash_get_course_users( $course_id );
			if ( is_array( $ld_users ) ) {
				foreach ( $ld_users as $u ) {
					$user_ids[] = is_object( $u ) ? (int) $u->ID : (int) $u;
				}
			}
		}

		// 3. Post meta nativo de LearnDash: course_access_list (lista separada por comas)
		$access_list = get_post_meta( $course_id, 'course_access_list', true );
		if ( ! empty( $access_list ) ) {
			if ( is_string( $access_list ) ) {
				$parsed = array_filter( array_map( 'intval', explode( ',', $access_list ) ) );
				$user_ids = array_merge( $user_ids, $parsed );
			} elseif ( is_array( $access_list ) ) {
				$user_ids = array_merge( $user_ids, array_map( 'intval', $access_list ) );
			}
		}

		// 4. Grupos de LearnDash que tienen este curso asignado
		if ( function_exists( 'learndash_get_course_groups' ) && function_exists( 'learndash_get_groups_user_ids' ) ) {
			$groups = learndash_get_course_groups( $course_id );
			if ( is_array( $groups ) ) {
				foreach ( $groups as $gid ) {
					$grp_users = learndash_get_groups_user_ids( (int) $gid );
					if ( is_array( $grp_users ) ) {
						$user_ids = array_merge( $user_ids, array_map( 'intval', $grp_users ) );
					}
				}
			}
		}

		// 5. User meta nativo de LearnDash: course_{id}_access_from
		$ld_access_users = $wpdb->get_col( $wpdb->prepare(
			"SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s",
			'course_' . $course_id . '_access_from'
		) );
		if ( ! empty( $ld_access_users ) ) {
			$user_ids = array_merge( $user_ids, array_map( 'intval', $ld_access_users ) );
		}

		// 6. User meta _alezux_enabled_courses que contenga course_id
		$alezux_metas = $wpdb->get_results(
			"SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = '_alezux_enabled_courses'"
		);
		if ( ! empty( $alezux_metas ) ) {
			foreach ( $alezux_metas as $row ) {
				$val = maybe_unserialize( $row->meta_value );
				if ( is_array( $val ) && in_array( $course_id, array_map( 'intval', $val ), true ) ) {
					$user_ids[] = (int) $row->user_id;
				}
			}
		}

		// 7. Usuarios con progreso registrado en este curso
		$ld_progress_users = $wpdb->get_col( $wpdb->prepare(
			"SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '_sfwd-course_progress' AND meta_value LIKE %s",
			'%' . $wpdb->esc_like( (string) $course_id ) . '%'
		) );
		if ( ! empty( $ld_progress_users ) ) {
			$user_ids = array_merge( $user_ids, array_map( 'intval', $ld_progress_users ) );
		}

		// 8. Chequeo por función sfwd_lms_has_access o ld_course_check_user_access si la lista sigue vacía
		if ( empty( $user_ids ) && ( function_exists( 'sfwd_lms_has_access' ) || function_exists( 'ld_course_check_user_access' ) ) ) {
			$all_users = $wpdb->get_col( "SELECT ID FROM {$wpdb->users} LIMIT 500" );
			foreach ( $all_users as $uid ) {
				$uid = (int) $uid;
				if ( ( function_exists( 'sfwd_lms_has_access' ) && sfwd_lms_has_access( $course_id, $uid ) )
					|| ( function_exists( 'ld_course_check_user_access' ) && ld_course_check_user_access( $course_id, $uid ) ) ) {
					$user_ids[] = $uid;
				}
			}
		}

		return array_values( array_unique( array_filter( array_map( 'intval', $user_ids ) ) ) );
	}

	/**
	 * Sincronizar masivamente todas las matrículas entre cursos de LearnDash y Crezca LMS Nativo
	 */
	public static function sync_all_migrated_course_enrollments() {
		global $wpdb;

		$migrated_courses = $wpdb->get_results(
			"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_crezca_migrated_from_ld_id'"
		);

		if ( empty( $migrated_courses ) ) {
			return [
				'success'         => true,
				'synced_courses'  => 0,
				'synced_students' => 0,
				'message'         => 'No hay cursos migrados registrados para sincronizar.',
			];
		}

		$total_synced_courses = 0;
		$unique_synced_students = [];

		foreach ( $migrated_courses as $mc ) {
			$native_id = (int) $mc->post_id;
			$ld_id     = (int) $mc->meta_value;

			if ( ! $native_id || ! $ld_id || ! get_post_status( $native_id ) ) {
				continue;
			}

			// 1. Alumnos provenientes de LearnDash
			$ld_users = self::get_learndash_enrolled_users( $ld_id );

			// 2. Alumnos con curso nativo en _alezux_enabled_courses
			$native_users = [];
			$all_enabled = $wpdb->get_results( "SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = '_alezux_enabled_courses'" );
			foreach ( $all_enabled as $row ) {
				$val = maybe_unserialize( $row->meta_value );
				if ( is_array( $val ) && in_array( $native_id, array_map( 'intval', $val ), true ) ) {
					$native_users[] = (int) $row->user_id;
				}
			}

			// 3. Alumnos con compras en Finanzas vinculadas a este curso
			$finance_users = [];
			$plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
			$subs_table  = $wpdb->prefix . 'alezux_finanzas_subscriptions';
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$plans_table'" ) === $plans_table && $wpdb->get_var( "SHOW TABLES LIKE '$subs_table'" ) === $subs_table ) {
				$fu = $wpdb->get_col( $wpdb->prepare(
					"SELECT s.user_id 
					 FROM $subs_table s 
					 INNER JOIN $plans_table p ON s.plan_id = p.id 
					 WHERE p.course_id IN (%d, %d) AND s.status IN ('active', 'completed', 'paid', 'trialing')",
					$native_id,
					$ld_id
				) );
				if ( ! empty( $fu ) ) {
					$finance_users = array_map( 'intval', $fu );
				}
			}

			// Combinar todos los estudiantes detectados
			$combined_students = array_values( array_unique( array_merge( $ld_users, $native_users, $finance_users ) ) );

			// Matricularlos en el curso nativo y mantener sincronizado LD
			foreach ( $combined_students as $uid ) {
				self::enroll_student( $uid, $native_id );
				$unique_synced_students[ $uid ] = true;
			}

			$total_synced_courses++;
		}

		return [
			'success'         => true,
			'synced_courses'  => $total_synced_courses,
			'synced_students' => count( $unique_synced_students ),
			'message'         => sprintf(
				'Se sincronizaron con éxito %d cursos y %d estudiantes en Crezca LMS.',
				$total_synced_courses,
				count( $unique_synced_students )
			),
		];
	}

	/**
	 * Migrar el historial de lecciones completadas a la tabla wp_crezca_topic_progress
	 */
	public static function migrate_learndash_course_progress( $ld_course_id, $native_course_id, $curriculum, $user_ids ) {
		global $wpdb;
		$table_progress = $wpdb->prefix . 'crezca_topic_progress';
		$has_table = ( $wpdb->get_var( "SHOW TABLES LIKE '$table_progress'" ) === $table_progress );

		// Construir mapa de temas y subtemas del curso
		$topic_ids_in_course = [];
		if ( is_array( $curriculum ) ) {
			foreach ( $curriculum as $sec ) {
				$lessons = isset( $sec['lessons'] ) ? $sec['lessons'] : ( isset( $sec['topics'] ) ? $sec['topics'] : [] );
				foreach ( $lessons as $top ) {
					if ( isset( $top['id'] ) ) {
						$topic_ids_in_course[] = (string) $top['id'];
					}
					if ( ! empty( $top['subtopics'] ) && is_array( $top['subtopics'] ) ) {
						foreach ( $top['subtopics'] as $sub ) {
							if ( isset( $sub['id'] ) ) {
								$topic_ids_in_course[] = (string) $sub['id'];
							}
						}
					}
				}
			}
		}

		foreach ( $user_ids as $u_id ) {
			$completed_meta = get_user_meta( $u_id, '_crezca_completed_topics', true );
			if ( ! is_array( $completed_meta ) ) {
				$completed_meta = [];
			}

			// Leer progreso de LearnDash si existe
			$ld_prog = get_user_meta( $u_id, '_sfwd-course_progress', true );
			if ( is_array( $ld_prog ) && isset( $ld_prog[ $ld_course_id ] ) ) {
				$course_p = $ld_prog[ $ld_course_id ];
				// Lecciones completadas en LearnDash
				if ( isset( $course_p['lessons'] ) && is_array( $course_p['lessons'] ) ) {
					foreach ( $course_p['lessons'] as $les_id => $status ) {
						if ( $status ) {
							$topic_key = 'top_' . $les_id;
							// Guardar en tabla
							if ( $has_table ) {
								$wpdb->replace(
									$table_progress,
									[
										'user_id'      => $u_id,
										'course_id'    => $native_course_id,
										'topic_id'     => $topic_key,
										'is_completed' => 1,
										'completed_at' => current_time( 'mysql' ),
									],
									[ '%d', '%d', '%s', '%d', '%s' ]
								);
							}
							if ( ! in_array( $topic_key, $completed_meta, true ) ) {
								$completed_meta[] = $topic_key;
							}
						}
					}
				}
				// Topics/temas completados en LearnDash
				if ( isset( $course_p['topics'] ) && is_array( $course_p['topics'] ) ) {
					foreach ( $course_p['topics'] as $top_id => $status ) {
						if ( $status ) {
							$sub_key = 'sub_' . $top_id;
							if ( $has_table ) {
								$wpdb->replace(
									$table_progress,
									[
										'user_id'      => $u_id,
										'course_id'    => $native_course_id,
										'topic_id'     => $sub_key,
										'is_completed' => 1,
										'completed_at' => current_time( 'mysql' ),
									],
									[ '%d', '%d', '%s', '%d', '%s' ]
								);
							}
							if ( ! in_array( $sub_key, $completed_meta, true ) ) {
								$completed_meta[] = $sub_key;
							}
						}
					}
				}
			}

			update_user_meta( $u_id, '_crezca_completed_topics', $completed_meta );
		}
	}
}

// Alias para retrocompatibilidad
class_alias( 'Alezux_Members\\Core\\LMS_Engine', 'Crezca\\Core\\LMS_Engine' );
