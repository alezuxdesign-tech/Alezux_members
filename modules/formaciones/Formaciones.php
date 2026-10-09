<?php
namespace Alezux_Members\Modules\Formaciones;

use Alezux_Members\Core\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Formaciones
 * Módulo para gestionar formaciones.
 */
class Formaciones extends Module_Base {

	public function init() {
		// Cargar clases internas
		require_once __DIR__ . '/includes/Course_Meta_Fields.php';
		require_once __DIR__ . '/includes/Shortcodes.php';
		// Instanciar Meta Fields para que corran los hooks del backend
		new \Alezux_Members\Modules\Formaciones\Includes\Course_Meta_Fields();
		
		// Registrar Shortcodes (y añadirlos a la documentación)
		$shortcodes = new \Alezux_Members\Modules\Formaciones\Includes\Shortcodes();
		foreach ( $shortcodes->get_definitions() as $tag => $def ) {
			$this->register_shortcode( $tag, $def['callback'], $def['description'] );
		}

		// Encolar assets de administración
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

		// Encolar assets del frontend
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'enqueue_frontend_assets' ] );

		// Registrar Widget de Elementor
		add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widgets' ] );
		
		// Registrar Etiquetas Dinámicas de Elementor
		add_action( 'elementor/dynamic_tags/register', [ $this, 'register_dynamic_tags' ] );

		// AJAX para completar/descompletar topic
		add_action( 'wp_ajax_alezux_toggle_topic_complete', [ $this, 'handle_topic_completion' ] );
		
		// AJAX para seguimiento de tiempo
		add_action( 'wp_ajax_alezux_track_study_time', [ $this, 'handle_study_time_tracking' ] );

        // Create Table if not exists (Auto-migration)
        add_action( 'admin_init', [ $this, 'create_study_log_table' ] );

		// Control de Caché (LiteSpeed)
		add_action( 'wp', [ $this, 'control_litespeed_cache' ] );
	}

    public function create_study_log_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'alezux_study_log';
        
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            date date NOT NULL,
            seconds int(11) NOT NULL DEFAULT 0,
            course_id bigint(20) NOT NULL DEFAULT 0,
            topic_id bigint(20) NOT NULL DEFAULT 0,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_date_topic (user_id, date, topic_id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

	public function control_litespeed_cache() {
		// Solo aplicar si el usuario está logueado (LearnDash progress is user-specific)
		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( is_singular( [ 'sfwd-topic', 'sfwd-lessons', 'sfwd-courses' ] ) ) {
			// Opción A: Deshabilitar caché completamente para estas páginas
			if ( defined( 'LSCWP_V' ) ) {
				do_action( 'litespeed_disable_cache' );
				// Alternativa menos agresiva: do_action( 'litespeed_control_set_ttl', 0 );
			}
			
			// Headers estándar de no-cache para asegurar
			nocache_headers();
		}
	}

	public function enqueue_frontend_assets() {
		wp_enqueue_style(
			'alezux-formaciones-front',
			$this->get_asset_url( 'assets/css/formaciones.css' ),
			[],
			'1.0.5' . time() // Version bumped for full customization controls
		);

		wp_enqueue_script(
			'alezux-formaciones-js',
			$this->get_asset_url( 'assets/js/formaciones.js' ),
			[ 'jquery' ],
			'1.0.4' . time(),
			true
		);

        // Get Course ID (Context)
        $course_id = 0;
        if ( function_exists( 'learndash_get_course_id' ) ) {
            $course_id = learndash_get_course_id( get_the_ID() );
        }

		wp_localize_script( 'alezux-formaciones-js', 'alezux_vars', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'post_id'  => get_the_ID(),
            'course_id' => $course_id,
		] );
	}

	public function register_elementor_widgets( $widgets_manager ) {
		require_once __DIR__ . '/includes/Elementor_Widget_Formaciones_Grid.php';
		require_once __DIR__ . '/includes/Elementor_Widget_Topics.php';
		require_once __DIR__ . '/includes/Elementor_Widget_Btn_Complete_Topic.php';
		require_once __DIR__ . '/includes/Elementor_Widget_Course_Progress.php';
		require_once __DIR__ . '/includes/Elementor_Widget_General_Progress.php';
		require_once __DIR__ . '/includes/Elementor_Widget_Rendimiento.php';
		
		$widgets_manager->register( new \Alezux_Members\Modules\Formaciones\Includes\Elementor_Widget_Formaciones_Grid() );
		$widgets_manager->register( new \Alezux_Members\Modules\Formaciones\Includes\Elementor_Widget_Topics() );
		$widgets_manager->register( new \Alezux_Members\Modules\Formaciones\Includes\Elementor_Widget_Btn_Complete_Topic() );
		$widgets_manager->register( new \Alezux_Members\Modules\Formaciones\Includes\Elementor_Widget_Course_Progress() );
		$widgets_manager->register( new \Alezux_Members\Modules\Formaciones\Includes\Elementor_Widget_General_Progress() );
		$widgets_manager->register( new \Alezux_Members\Modules\Formaciones\Includes\Elementor_Widget_Rendimiento() );
	}

	public function register_dynamic_tags( $dynamic_tags ) {
		require_once __DIR__ . '/includes/Dynamic_Tag_Course_Cover.php';
		
		$dynamic_tags->register( new \Alezux_Members\Modules\Formaciones\Includes\Dynamic_Tag_Course_Cover() );
	}

	public function enqueue_admin_assets( $hook ) {
		global $post_type;
		$screen = get_current_screen();
		$screen_post_type = $screen ? $screen->post_type : '';
		
		// Verificar si es curso de LearnDash (Comprobación amplia)
		$is_course = false;
		if ( 'sfwd-courses' === $post_type ) $is_course = true;
		if ( 'sfwd-courses' === $screen_post_type ) $is_course = true;
		
		if ( ! $is_course ) {
			return;
		}

		wp_enqueue_media(); 

		wp_enqueue_style(
			'alezux-formaciones-admin',
			$this->get_asset_url( 'assets/css/admin-formaciones.css' ),
			[],
			'1.0.0' . time() // Cache bust
		);

		wp_enqueue_script(
			'alezux-formaciones-admin',
			$this->get_asset_url( 'assets/js/admin-formaciones.js' ),
			[ 'jquery' ],
			'1.0.0' . time(), // Cache bust
			true
		);

		wp_localize_script( 'alezux-formaciones-admin', 'alezux_admin_vars', [
			'debug' => false
		]);
	}
	public function handle_topic_completion() {
		try {
			// Validar nonce
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'alezux_toggle_complete_' . $_POST['post_id'] ) ) {
				throw new \Exception( 'Invalid nonce' );
			}

			if ( ! is_user_logged_in() ) {
				throw new \Exception( 'User not logged in' );
			}

			$post_id = intval( $_POST['post_id'] );
			$user_id = get_current_user_id();

			if ( ! $post_id ) {
				throw new \Exception( 'Invalid Post ID' );
			}

			global $wpdb;

			// --- 1. Determine Status (Read) ---
			$is_completed = false;

			if ( function_exists( 'learndash_is_target_complete' ) ) {
				$is_completed = learndash_is_target_complete( $post_id, $user_id );
			} 
			
			// CRITICAL FIX: Force DB Check if native is false
			if ( ! $is_completed ) {
				// Fallback: Check Post Meta directly from User Activity or Course Progress
				// Method A: Check '_sfwd_course_progress'
				$course_id = 0;
				if(function_exists('learndash_get_course_id')){
					$course_id = learndash_get_course_id( $post_id );
				} else {
					// Manual fallback for course ID
					$post_type = get_post_type($post_id);
					if('sfwd-topic' === $post_type || 'sfwd-lessons' === $post_type) {
						$course_id = get_post_meta( $post_id, 'course_id', true );
					}
				}

				if ( $course_id ) {
					$course_progress = get_user_meta( $user_id, '_sfwd_course_progress', true );
					if ( ! empty( $course_progress[$course_id] ) ) {
						// Check topics
						if ( ! empty( $course_progress[$course_id]['topics'][$post_id] ) ) {
							$is_completed = true;
						}
						// Check lessons (if it was a lesson)
						if ( ! empty( $course_progress[$course_id]['lessons'][$post_id] ) ) {
							$is_completed = true;
						}
					}
				}
				
				// Method B: Check Activity Table manually if Method A failed or as verification
				if ( ! $is_completed ) {
					$activity_type = 'topic';
					$post_type = get_post_type($post_id);
					if('sfwd-lessons' === $post_type) $activity_type = 'lesson';
					
					// CRITICAL FIX: Check for activity_status = 1 OR activity_completed > 0
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
			}

			// --- 2. Action (Write) ---
			
			// Determinar tipo activdad para escritura
			$activity_type = 'topic'; 
			$post_type = get_post_type($post_id);
			if('sfwd-lessons' === $post_type) $activity_type = 'lesson';
			if('sfwd-topic' === $post_type) $activity_type = 'topic';

			if ( $is_completed ) {
				// === UNMARK COMPLETE ===
				
				// 1. Delete from Activity Table
				// 1. Delete from Activity Table (Aggressive: Ignore activity_type, just use post_id/user_id)
				// 1. Delete from Activity Table
				// CRITICAL FIX: Include activity_type to prevent deleting wrong rows (e.g. if ID check is loose or shared)
				// Also include course_id if available for stricter check
				$delete_where = [
					'user_id' => $user_id,
					'post_id' => $post_id,
					'activity_type' => $activity_type
				];
				$delete_format = [ '%d', '%d', '%s' ];

				if ( $course_id ) {
					$delete_where['course_id'] = $course_id;
					$delete_format[] = '%d';
				}

				$wpdb->delete(
					$wpdb->prefix . 'learndash_user_activity',
					$delete_where,
					$delete_format
				);

				// 2. Clear User Meta Cache for Course Progress
				// Need Course ID
				$course_id = 0;
				if(function_exists('learndash_get_course_id')){
					$course_id = learndash_get_course_id( $post_id );
				}
                
                if ( ! $course_id ) {
					$course_id = get_post_meta( $post_id, 'course_id', true );
				}

				if ( $course_id ) {
                    error_log("ALEZUX DEBUG UNMARK: PostID: $post_id, UserID: $user_id, CourseID: $course_id, Type: $activity_type");
					$course_progress = get_user_meta( $user_id, '_sfwd_course_progress', true );
                    error_log("ALEZUX DEBUG META BEFORE: " . print_r($course_progress[$course_id] ?? 'NOT FOUND', true));
					
					// Remove from progress array if exists
					$updated = false;
					if ( isset( $course_progress[$course_id] ) ) {
						if ( 'topic' === $activity_type && isset( $course_progress[$course_id]['topics'][$post_id] ) ) {
							unset( $course_progress[$course_id]['topics'][$post_id] );
							$updated = true;
                            error_log("ALEZUX DEBUG: Unset topic $post_id");
						} elseif ( 'lesson' === $activity_type && isset( $course_progress[$course_id]['lessons'][$post_id] ) ) {
							unset( $course_progress[$course_id]['lessons'][$post_id] );
							$updated = true;
                            error_log("ALEZUX DEBUG: Unset lesson $post_id");
						}
						
						if ( $updated ) {
							update_user_meta( $user_id, '_sfwd_course_progress', $course_progress );
                            error_log("ALEZUX DEBUG: Meta Updated");
						}
					}
				}

				// Clear Caches to ensure UI updates
				clean_post_cache( $post_id );
				wp_cache_delete( 'learndash_course_progress_' . $user_id . '_' . $course_id, 'learndash' );
				wp_cache_delete( 'learndash_user_activity_' . $user_id . '_' . $post_id, 'learndash' );

				// LiteSpeed Cache Purge
				if ( defined( 'LSCWP_V' ) ) {
					do_action( 'litespeed_purge_post', $post_id );
					if ( $course_id ) {
						do_action( 'litespeed_purge_post', $course_id );
					}
				}

				wp_send_json_success( [ 'status' => 'incomplete', 'method' => 'manual_unmark' ] );

			} else {
				// === MARK COMPLETE ===
				
				if ( function_exists( 'learndash_process_mark_complete' ) ) {
					learndash_process_mark_complete( $user_id, $post_id );
					wp_send_json_success( [ 'status' => 'completed', 'method' => 'api' ] );
				} else {
					// Manual Mark Complete (Fallback)
					
					// 1. Insert into Activity Table
					$now_timestamp = time(); // Epoch
					
					// Need Course ID for activity record
					$course_id = 0;
					if(function_exists('learndash_get_course_id')){
						$course_id = learndash_get_course_id( $post_id );
					} else {
						$course_id = get_post_meta( $post_id, 'course_id', true );
					}

					$wpdb->insert(
						$wpdb->prefix . 'learndash_user_activity',
						[
							'user_id' => $user_id,
							'post_id' => $post_id,
							'course_id' => $course_id,
							'activity_type' => $activity_type,
							'activity_status' => 1, // Completed
							'activity_started' => $now_timestamp,
							'activity_completed' => $now_timestamp,
							'activity_updated' => $now_timestamp,
						],
						[ '%d', '%d', '%d', '%s', '%d', '%d', '%d', '%d' ]
					);

					// 2. Update User Meta Progress
					if ( $course_id ) {
						$course_progress = get_user_meta( $user_id, '_sfwd_course_progress', true );
						if ( ! is_array( $course_progress ) ) $course_progress = [];
						if ( ! isset( $course_progress[$course_id] ) ) $course_progress[$course_id] = [];
						
						if ( 'topic' === $activity_type ) {
							if ( ! isset( $course_progress[$course_id]['topics'] ) ) $course_progress[$course_id]['topics'] = [];
							$course_progress[$course_id]['topics'][$post_id] = 1;
						} elseif ( 'lesson' === $activity_type ) {
							if ( ! isset( $course_progress[$course_id]['lessons'] ) ) $course_progress[$course_id]['lessons'] = [];
							$course_progress[$course_id]['lessons'][$post_id] = 1;
						}
						// Also update 'total' and 'completed' counts if you want to be perfect, 
						// but usually just setting the key triggers recalculation on next full load or is enough.
						
						update_user_meta( $user_id, '_sfwd_course_progress', $course_progress );
					}

					// LiteSpeed Cache Purge
					if ( defined( 'LSCWP_V' ) ) {
						do_action( 'litespeed_purge_post', $post_id );
						if ( $course_id ) {
							do_action( 'litespeed_purge_post', $course_id );
						}
					}

					wp_send_json_success( [ 'status' => 'completed', 'method' => 'manual_fallback_write' ] );
				}
			}
		} catch ( \Exception $e ) {
			wp_send_json_error( [ 'message' => $e->getMessage() ] );
		} catch ( \Throwable $e ) {
			wp_send_json_error( [ 'message' => 'Fatal Error: ' . $e->getMessage() ] );
		}
	}

	public function handle_study_time_tracking() {
		// Verificar nonce para seguridad (opcional pero recomendado)
		// check_ajax_referer( 'alezux_study_tracker_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( [ 'message' => 'User not logged in' ] );
		}

		$user_id = get_current_user_id();
		$seconds = isset( $_POST['seconds'] ) ? intval( $_POST['seconds'] ) : 0;
        $course_id = isset( $_POST['course_id'] ) ? intval( $_POST['course_id'] ) : 0;
        $topic_id = isset( $_POST['topic_id'] ) ? intval( $_POST['topic_id'] ) : 0;
		
		// Use WordPress Local Time
		$date = current_time( 'Y-m-d' );

		if ( $seconds <= 0 ) {
			wp_send_json_success(); // No hay nada que guardar
		}

		// Debug Log
		error_log( "ALEZUX TRACKER SQL: User $user_id | Seconds: $seconds | Date: $date | Course: $course_id | Topic: $topic_id" );

        // --- SQL INSERTION ---
        global $wpdb;
        $table_name = $wpdb->prefix . 'alezux_study_log';

        // Insert or Update (accumulator)
        // If row exists for user+date+topic, add seconds to existing value
        $sql = $wpdb->prepare(
            "INSERT INTO $table_name (user_id, date, seconds, course_id, topic_id) 
             VALUES (%d, %s, %d, %d, %d) 
             ON DUPLICATE KEY UPDATE seconds = seconds + VALUES(seconds), course_id = VALUES(course_id)",
            $user_id,
            $date,
            $seconds,
            $course_id,
            $topic_id
        );

        $result = $wpdb->query( $sql );

        if ( $result === false ) {
            error_log( "ALEZUX TRACKER SQL ERROR: " . $wpdb->last_error );
            wp_send_json_error( [ 'message' => 'Database error' ] );
        }

		wp_send_json_success( [ 'logged' => $seconds, 'date_used' => $date ] );
	}

}
