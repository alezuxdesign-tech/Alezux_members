<?php
namespace Alezux_Members\Modules\Estudiantes;

use Alezux_Members\Core\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Estudiantes extends Module_Base {

	public function init() {
		// Registrar Shortcodes

		\add_shortcode( 'alezux_estudiantes_total', [ $this, 'shortcode_total_students' ] );
		\add_shortcode( 'alezux_estudiantes_nuevos_mes', [ $this, 'shortcode_new_students_month' ] );
		\add_shortcode( 'alezux_student_momentum', [ $this, 'shortcode_study_momentum' ] );

		// Registrar Widgets de Elementor
		\add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widgets' ] );
		
		// Registrar scripts y estilos
		\add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		\add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ] );

		// AJAX Endpoints
		\add_action( 'wp_ajax_alezux_search_students', [ $this, 'ajax_search_students' ] );
		\add_action( 'wp_ajax_alezux_register_student', [ $this, 'ajax_register_student' ] );
		\add_action( 'wp_ajax_alezux_register_batch_csv', [ $this, 'ajax_register_batch_csv' ] );
		// New Management Hooks
		\add_action( 'wp_ajax_alezux_get_student_details', [ $this, 'ajax_get_student_details' ] );
		\add_action( 'wp_ajax_alezux_update_student', [ $this, 'ajax_update_student' ] );
		\add_action( 'wp_ajax_alezux_reset_password', [ $this, 'ajax_reset_password' ] );
		\add_action( 'wp_ajax_alezux_update_course_access', [ $this, 'ajax_update_course_access' ] );
		\add_action( 'wp_ajax_alezux_update_plan_access', [ $this, 'ajax_update_plan_access' ] );
		\add_action( 'wp_ajax_alezux_toggle_block_user', [ $this, 'ajax_toggle_block_user' ] );
		\add_action( 'wp_ajax_alezux_bulk_student_process', [ $this, 'ajax_bulk_student_process' ] );
	}

	public function register_assets() {
		// Estilos
		\wp_enqueue_style( 'alezux-tables-css', \plugin_dir_url( \dirname( __FILE__ ) ) . 'finanzas/assets/css/alezux-tables.css', [], '1.0.5' );
		\wp_enqueue_style( 'alezux-estudiantes-css', \plugin_dir_url( __FILE__ ) . 'assets/css/estudiantes.css', [], '1.3.7' );
		\wp_register_style( 'alezux-estudiantes-register-css', \plugin_dir_url( __FILE__ ) . 'assets/css/estudiantes-register.css', [], '1.1.6' );
		\wp_register_style( 'alezux-estudiantes-csv-css', \plugin_dir_url( __FILE__ ) . 'assets/css/estudiantes-csv.css', [], '1.1.0' ); // Se registra pero no se encola globalmente

		// Scripts
		\wp_enqueue_script( 'alezux-estudiantes-js', \plugin_dir_url( __FILE__ ) . 'assets/js/estudiantes.js', [ 'jquery' ], '1.4.0', true );
		\wp_register_script( 'alezux-estudiantes-register-js', \plugin_dir_url( __FILE__ ) . 'assets/js/estudiantes-register.js', [ 'jquery' ], '1.1.6', true );
		\wp_register_script( 'alezux-estudiantes-csv-js', \plugin_dir_url( __FILE__ ) . 'assets/js/estudiantes-csv.js', [ 'jquery' ], '1.1.0', true );

		// Localize Scripts (Variables comunes)
		$vars = [
			'ajax_url' => \admin_url( 'admin-ajax.php' ),
			'nonce'    => \wp_create_nonce( 'alezux_estudiantes_nonce' ),
		];

		\wp_localize_script( 'alezux-estudiantes-js', 'alezux_estudiantes_vars', $vars );
		\wp_localize_script( 'alezux-estudiantes-register-js', 'alezux_estudiantes_vars', $vars );
		\wp_localize_script( 'alezux-estudiantes-csv-js', 'alezux_estudiantes_vars', $vars );
	}

	public function register_elementor_widgets( $widgets_manager ) {
		// Include widget files
		if ( file_exists( __DIR__ . '/widgets/Estudiantes_Widget.php' ) ) {
			require_once __DIR__ . '/widgets/Estudiantes_Widget.php';
			$widgets_manager->register( new \Alezux_Members\Modules\Estudiantes\Widgets\Estudiantes_Widget() );
		}
		
		if ( file_exists( __DIR__ . '/widgets/Estudiantes_Register_Widget.php' ) ) {
			require_once __DIR__ . '/widgets/Estudiantes_Register_Widget.php';
			$widgets_manager->register( new \Alezux_Members\Modules\Estudiantes\Widgets\Estudiantes_Register_Widget() );
		}

		if ( file_exists( __DIR__ . '/widgets/Estudiantes_CSV_Widget.php' ) ) {
			require_once __DIR__ . '/widgets/Estudiantes_CSV_Widget.php';
			$widgets_manager->register( new \Alezux_Members\Modules\Estudiantes\Widgets\Estudiantes_CSV_Widget() );
		}

		if ( file_exists( __DIR__ . '/widgets/Elementor_Widget_Estudiantes_Cursos_Grid.php' ) ) {
			require_once __DIR__ . '/widgets/Elementor_Widget_Estudiantes_Cursos_Grid.php';
			$widgets_manager->register( new \Alezux_Members\Modules\Estudiantes\Widgets\Elementor_Widget_Estudiantes_Cursos_Grid() );
		}

		if ( file_exists( __DIR__ . '/widgets/Elementor_Widget_Student_Heatmap.php' ) ) {
			require_once __DIR__ . '/widgets/Elementor_Widget_Student_Heatmap.php';
			$widgets_manager->register( new \Alezux_Members\Modules\Estudiantes\Widgets\Elementor_Widget_Student_Heatmap() );
		}
	}

	public function shortcode_total_students() {
		$args = [
			'role__in'    => [ 'subscriber', 'student' ],
			'number'      => 1, // Limit fetching
			'count_total' => true,
		];
		$user_query = new \WP_User_Query( $args );
		return $user_query->get_total();
	}

	public function shortcode_new_students_month() {
		$args = array(
			'role__in'    => array( 'subscriber', 'student' ),
			'date_query'  => array(
				array( 'after' => 'first day of this month' )
			)
		);
		$user_query = new \WP_User_Query( $args );
		return $user_query->get_total();
	}

	/**
	 * AJAX Handler: Buscar estudiantes
	 */
	public function ajax_search_students() {
        // ... (existing code)
		\check_ajax_referer( 'alezux_estudiantes_nonce', 'nonce' );

		if ( ! \current_user_can( 'edit_users' ) ) {
			\wp_send_json_error( [ 'message' => 'No tienes permisos.' ] );
		}

		$search    = isset( $_POST['search'] ) ? \sanitize_text_field( $_POST['search'] ) : '';
		$page      = isset( $_POST['page'] ) ? \intval( $_POST['page'] ) : 1;
		$limit     = isset( $_POST['limit'] ) ? \intval( $_POST['limit'] ) : 10;
		$course_id = isset( $_POST['course_id'] ) ? \intval( $_POST['course_id'] ) : 0;
		$status    = isset( $_POST['status'] ) ? \sanitize_text_field( $_POST['status'] ) : '';

		$offset = ( $page - 1 ) * $limit;

		$args = [
			'role__in'       => [ 'subscriber', 'student' ],
			'number'         => $limit,
			'offset'         => $offset,
			'search'         => '*' . $search . '*',
			'search_columns' => [ 'user_login', 'user_email', 'display_name' ],
			'count_total'    => true, // Asegurar que cuente el total
            'meta_query'     => [],
		];

        // Filtro por Curso (LearnDash)
        if ( $course_id > 0 ) {
            // Opción A: Usar función nativa si existe (más seguro)
            // Opción B: Query manual a usermeta '_sfwd_course_progress' o acceso
            
            // Vamos a obtener IDs de usuarios con acceso a ese curso
            // Esto puede ser pesado si hay muchos usuarios, pero es lo más fiable con LD
            // Una alternativa mejor es query directa a usermeta si sabemos la key
            // Key habitual: 'course_{id}_access_from' (timestamp) o simplemente enrollment.
            // Para simplificar y rendimiento, buscaremos usuarios que tengan meta key de progreso o acceso
            
            // NOTA: LearnDash guarda el acceso de varias formas. La más directa para query es user_meta
            // course_X_access_from
            
            $args['meta_query'][] = [
                'key'     => 'course_' . $course_id . '_access_from',
                'compare' => 'EXISTS'
            ];
        }

        // Filtro por Estado (Bloqueado / Activo)
        if ( ! empty( $status ) ) {
            if ( $status === 'blocked' ) {
                $args['meta_query'][] = [
                    'key'   => 'alezux_is_blocked',
                    'value' => '1',
                    'compare' => '='
                ];
            } elseif ( $status === 'active' ) {
                $args['meta_query'][] = [
                    'relation' => 'OR',
                    [
                        'key'     => 'alezux_is_blocked',
                        'compare' => 'NOT EXISTS'
                    ],
                    [
                        'key'     => 'alezux_is_blocked',
                        'value'   => '1',
                        'compare' => '!='
                    ]
                ];
            }
        }

		// Si string vacío, devuelve lista inicial sin filtro de búsqueda
		if ( empty( $search ) ) {
			unset( $args['search'] );
		}

		$user_query = new \WP_User_Query( $args );
		$students = $user_query->get_results();
		$total_users = $user_query->get_total();
		$total_pages = ceil( $total_users / $limit );

		    $data = [];
    foreach ( $students as $student ) {
        // Calculate Average Progress
        $progress = 0;
        $total_courses = 0;
        $user_progress = \get_user_meta( $student->ID, '_sfwd_course_progress', true );
        
        if ( is_array( $user_progress ) ) {
            foreach ( $user_progress as $course_id => $p_data ) {
                if ( ! empty( $p_data['total'] ) && $p_data['total'] > 0 ) {
                    $completed = isset( $p_data['completed'] ) ? intval( $p_data['completed'] ) : 0;
                    $total = intval( $p_data['total'] );
                    $percentage = ($completed / $total) * 100;
                    $progress += $percentage;
                    $total_courses++;
                }
            }
        }
        
        $avg_progress = ($total_courses > 0) ? round( $progress / $total_courses ) : 0;

        $data[] = [
            'id'           => $student->ID,
            'name'         => $student->display_name,
            'username'     => $student->user_nicename,
            'email'        => $student->user_email,
            'avatar_url'   => \get_avatar_url( $student->ID ),
            'status_label' => (bool) \get_user_meta( $student->ID, 'alezux_is_blocked', true ) ? 'Bloqueado' : 'OK',
            'status_class' => (bool) \get_user_meta( $student->ID, 'alezux_is_blocked', true ) ? 'status-inactive' : 'status-active',
            'progress'     => $avg_progress,
        ];
    }

		\wp_send_json_success( [
			'students'     => $data,
			'total_pages'  => $total_pages,
			'current_page' => $page,
			'total_users'  => $total_users
		] );
	}

	/**
	 * AJAX Handler: Registro Manual de 1 Estudiante
	 */
	/**
	 * AJAX Handler: Registro Manual de 1 Estudiante (Integrado con Finanzas)
	 */
	public function ajax_register_student() {
		\check_ajax_referer( 'alezux_estudiantes_nonce', 'nonce' );

		if ( ! \current_user_can( 'edit_users' ) ) {
			\wp_send_json_error( [ 'message' => 'No tienes permisos.' ] );
		}

		$first_name = isset( $_POST['first_name'] ) ? \sanitize_text_field( $_POST['first_name'] ) : '';
		$last_name  = isset( $_POST['last_name'] ) ? \sanitize_text_field( $_POST['last_name'] ) : '';
		$email      = isset( $_POST['email'] ) ? \sanitize_email( $_POST['email'] ) : '';
		
        // Nuevos campos
        $plan_id           = isset( $_POST['plan_id'] ) ? \intval( $_POST['plan_id'] ) : 0;
        $payment_method    = isset( $_POST['payment_method'] ) ? \sanitize_text_field( $_POST['payment_method'] ) : 'manual';
        $payment_reference = isset( $_POST['payment_reference'] ) ? \sanitize_text_field( $_POST['payment_reference'] ) : 'Manual-' . time();
        $password          = isset( $_POST['password'] ) ? \sanitize_text_field( $_POST['password'] ) : '';
        
        // Si viene course_id legacy, lo ignoramos o tratamos de mapear (pero el widget nuevo manda plan_id)

		if ( empty( $email ) || ! \is_email( $email ) ) {
			\wp_send_json_error( [ 'message' => 'Email inválido.' ] );
		}
        
        if ( empty( $plan_id ) ) {
            \wp_send_json_error( [ 'message' => 'Debes seleccionar un Plan.' ] );
        }

		// Verificamos si email o usuario ya existen ANTES de intentar registrar
        // NOTA: Enrollment_Manager maneja usuarios existentes, pero para registro manual nuevo
        // a veces queremos avisar si ya existe. Enrollment Manager lo matricula igual.
        // Vamos a permitir registrar/matricular usuarios existentes.

        // Validar si Finanzas está activo y cargar Enrollment Manager
        if ( ! class_exists( 'Alezux_Members\Modules\Finanzas\Includes\Enrollment_Manager' ) ) {
             \wp_send_json_error( [ 'message' => 'Error Interno: Módulo de Finanzas no cargado. No se puede procesar el registro.' ] );
        }

        // Obtener precio del plan para la transacción
        global $wpdb;
        $table_plans = $wpdb->prefix . 'alezux_finanzas_plans';
        // FIX: La columna es 'quota_amount', no 'price'. Currency no existe en tabla, asumimos USD.
        $plan = $wpdb->get_row( $wpdb->prepare( "SELECT quota_amount FROM $table_plans WHERE id = %d", $plan_id ) );
        
        if ( ! $plan ) {
            \wp_send_json_error( [ 'message' => 'El plan seleccionado no existe.' ] );
        }
        
        $amount = floatval( $plan->quota_amount );
        $currency = 'USD'; // Default currency as it is not in the table
        $full_name = trim( $first_name . ' ' . $last_name );

        // Ejecutar Inscripción vía Finanzas
        // Esto crea usuario (si no existe), inscribe en curso (si plan tiene curso), 
        // crea suscripción y registra transacción.
        $user_id = \Alezux_Members\Modules\Finanzas\Includes\Enrollment_Manager::enroll_user(
            $email,
            $plan_id,
            null, // No Stripe Sub ID
            $amount,
            $payment_reference, // Ref transacción
            $currency,
            $full_name,
            $payment_method, // Nuevo param: manual_cash, manual_transfer, etc
            $password
        );

		if ( ! $user_id ) {
			\wp_send_json_error( [ 'message' => 'Error al procesar la inscripción.' ] );
		}

		\wp_send_json_success( [ 'message' => 'Estudiante registrado y plan asignado correctamente.' ] );
	}

	/**
	 * AJAX Handler: Registro Batch CSV (Procesa lote pequeño)
	 */
	public function ajax_register_batch_csv() {
		\check_ajax_referer( 'alezux_estudiantes_nonce', 'nonce' );

		if ( ! \current_user_can( 'edit_users' ) ) {
			\wp_send_json_error( [ 'message' => 'No tienes permisos.' ] );
		}

		$students  = isset( $_POST['students'] ) ? $_POST['students'] : []; // Array de {name, email, ...}
		$course_id = isset( $_POST['course_id'] ) ? \intval( $_POST['course_id'] ) : 0;
		$plan_id   = isset( $_POST['plan_id'] ) ? \intval( $_POST['plan_id'] ) : 0;

		if ( empty( $students ) || ! \is_array( $students ) ) {
			\wp_send_json_error( [ 'message' => 'No hay datos para procesar.' ] );
		}

		$success_count = 0;
		$errors = [];

		foreach ( $students as $student_data ) {
			// Mapeo básico CSV (asumimos frontend envía claves normalizadas)
			$data = [
				'first_name' => isset( $student_data['first_name'] ) ? \sanitize_text_field( $student_data['first_name'] ) : '',
				'last_name'  => isset( $student_data['last_name'] ) ? \sanitize_text_field( $student_data['last_name'] ) : '',
				'email'      => isset( $student_data['email'] ) ? \sanitize_email( $student_data['email'] ) : '',
				'course_id'  => $course_id,
				'plan_id'    => $plan_id,
			];

			if ( empty( $data['email'] ) ) continue;

			$res = $this->register_single_student( $data );
			if ( ! \is_wp_error( $res ) ) {
				$success_count++;
			} else {
				$errors[] = $data['email'] . ': ' . $res->get_error_message();
			}
		}

		\wp_send_json_success( [
			'processed' => count( $students ),
			'success'   => $success_count,
			'errors'    => $errors,
		] );
	}

	/**
	 * Lógica centralizada para registrar/matricular un estudiante
	 */
	private function register_single_student( $data ) {
		$email      = $data['email'];
		$first_name = $data['first_name'];
		$last_name  = $data['last_name'];
		$course_id  = $data['course_id'];
		$plan_id    = isset( $data['plan_id'] ) ? $data['plan_id'] : 0;

		$user = \get_user_by( 'email', $email );
		$is_new_user = false;
		$password = '';

		if ( ! $user ) {
			// Crear usuario
			$password = \wp_generate_password( 12, true );
			$username = \sanitize_user( current( explode( '@', $email ) ), true );
			
			// Asegurar username único
			$base_username = $username;
			$i = 1;
			while ( \username_exists( $username ) ) {
				$username = $base_username . $i;
				$i++;
			}

			$user_id = \wp_create_user( $username, $password, $email );

			if ( \is_wp_error( $user_id ) ) {
				return $user_id;
			}

			$is_new_user = true;
			$user = \get_user_by( 'id', $user_id );
		} else {
			// Usuario existe, le regeneramos contraseña para enviársela y asegurar acceso
			$user_id = $user->ID;
			$password = \wp_generate_password( 12, true );
			\wp_set_password( $password, $user_id );
		}

		// Actualizar datos del usuario (completar al 100%)
		$update_data = [ 'ID' => $user_id ];
		if ( ! empty( $first_name ) ) {
			$update_data['first_name'] = $first_name;
		}
		if ( ! empty( $last_name ) ) {
			$update_data['last_name'] = $last_name;
		}
		
		// Aseguramos que tenga el rol
		if ( $is_new_user ) {
			$update_data['role'] = 'subscriber';
		}
		
		\wp_update_user( $update_data );

		// Asignar Curso LearnDash directo
		if ( $course_id > 0 && function_exists( 'ld_update_course_access' ) ) {
			\ld_update_course_access( $user_id, $course_id, false ); // false = add access
		}

		// Asignar Plan Financiero (que a su vez puede dar acceso a otro curso)
		if ( $plan_id > 0 ) {
			global $wpdb;
			$subs_table = $wpdb->prefix . 'alezux_finanzas_subscriptions';
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$subs_table'" ) == $subs_table ) {
				$existing_sub = $wpdb->get_row( $wpdb->prepare( 
					"SELECT id, status FROM $subs_table WHERE user_id = %d AND plan_id = %d", 
					$user_id, $plan_id 
				) );

				if ( $existing_sub ) {
					if ( $existing_sub->status !== 'active' && $existing_sub->status !== 'completed' ) {
						$wpdb->update( $subs_table, [ 'status' => 'active' ], [ 'id' => $existing_sub->id ] );
					}
				} else {
					$wpdb->insert( $subs_table, [
						'user_id' => $user_id,
						'plan_id' => $plan_id,
						'status' => 'active',
						'quotas_paid' => 1,
						'last_payment_date' => current_time( 'mysql' )
					] );
				}
				
				// Dar acceso al curso asociado al plan, si lo hay
				$plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
				$plan_course_id = $wpdb->get_var( $wpdb->prepare( "SELECT course_id FROM $plans_table WHERE id = %d", $plan_id ) );
				if ( $plan_course_id && $plan_course_id > 0 && function_exists( 'ld_update_course_access' ) ) {
					\ld_update_course_access( $user_id, $plan_course_id, false );
				}
			}
		}

		// Enviar Email de credenciales siempre (para asegurar la entrega a todos)
		$email_sent = $this->send_new_user_email( $user, $password, $course_id, $plan_id );
		
		if ( ! $email_sent ) {
			$engine = \Alezux_Members\Modules\Marketing\Marketing::get_instance()->get_engine();
			$err_msg = $engine->get_last_error_message();
			return new \WP_Error( 'email_failed', 'Usuario registrado, pero el correo falló: ' . $err_msg );
		}

		return true;
	}

	/**
	 * Enviar Correo con Credenciales (Delegado a Marketing)
	 */
	private function send_new_user_email( $user, $password, $course_id, $plan_id = 0 ) {
		if ( ! class_exists( '\Alezux_Members\Modules\Marketing\Marketing' ) ) {
			return false; // Fallback o error log
		}

		$course_title = '';
		if ( $course_id ) {
			$course = \get_post( $course_id );
			if ( $course ) $course_title = $course->post_title;
		} elseif ( $plan_id ) {
			global $wpdb;
			$p_table = $wpdb->prefix . 'alezux_finanzas_plans';
			$p_data = $wpdb->get_row( $wpdb->prepare( "SELECT name, course_id FROM $p_table WHERE id = %d", $plan_id ) );
			if ( $p_data ) {
				if ( ! empty( $p_data->course_id ) ) {
					$c_title = \get_the_title( $p_data->course_id );
					$course_title = $c_title ? $c_title : $p_data->name;
				} else {
					$course_title = $p_data->name;
				}
			}
		}

		return \Alezux_Members\Modules\Marketing\Marketing::get_instance()->get_engine()->send_email( 
			'student_welcome', 
			$user->user_email, 
			[
				'user' => $user,
				'password' => $password,
				'course_title' => $course_title,
				'login_url' => \wp_login_url()
			]
		);
	}

	/**
	 * AJAX Handler: Obtener detalles del estudiante para el Modal
	 */
	public function ajax_get_student_details() {
		\check_ajax_referer( 'alezux_estudiantes_nonce', 'nonce' );
		
		if ( ! \current_user_can( 'edit_users' ) ) {
			\wp_send_json_error( [ 'message' => 'No autorizado' ] );
		}

		$user_id = isset( $_POST['user_id'] ) ? \intval( $_POST['user_id'] ) : 0;
		$user = \get_user_by( 'id', $user_id );

		if ( ! $user ) {
			\wp_send_json_error( [ 'message' => 'Usuario no encontrado' ] );
		}

		// 1. Datos Básicos
		$data = [
			'id'         => $user->ID,
			'first_name' => $user->first_name,
			'last_name'  => $user->last_name,
			'email'      => $user->user_email,
			'is_blocked' => (bool) \get_user_meta( $user->ID, 'alezux_is_blocked', true ),
		];

		// 2. Cursos (LearnDash)
		$enrolled = [];
		$available = [];

		// Obtener todos los cursos
		$all_courses = \get_posts( [
			'post_type'      => 'sfwd-courses',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		] );

		if ( $all_courses ) {
			foreach ( $all_courses as $course ) {
				$has_access = false;
				if ( function_exists( 'sfwd_lms_has_access' ) ) {
					$has_access = \sfwd_lms_has_access( $course->ID, $user->ID );
				}

				$course_info = [
					'id'    => $course->ID,
					'title' => $course->post_title,
                    'study_seconds' => 0,
                    'study_time_formatted' => '0h 0m'
				];

                // Fetch Study Time from DB
                global $wpdb;
                $table_name = $wpdb->prefix . 'alezux_study_log';
                // Check if table exists first to avoid errors
                if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
                    $seconds = $wpdb->get_var( $wpdb->prepare(
                        "SELECT SUM(seconds) FROM $table_name WHERE user_id = %d AND course_id = %d",
                        $user->ID,
                        $course->ID
                    ) );
                    
                    if ( $seconds ) {
                        $course_info['study_seconds'] = intval( $seconds );
                        $hours = floor( $seconds / 3600 );
                        $mins = floor( ($seconds % 3600) / 60 );
                        $course_info['study_time_formatted'] = "{$hours}h {$mins}m";
                    }
                }

				if ( $has_access ) {
					$enrolled[] = $course_info;
				} else {
					$available[] = $course_info;
				}
			}
		}

		$data['enrolled_courses']  = $enrolled;
		$data['available_courses'] = $available;

		// 3. Planes (Alezux Finanzas)
		$enrolled_plans = [];
		$available_plans = [];
        
        global $wpdb;
        $plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
        $subs_table = $wpdb->prefix . 'alezux_finanzas_subscriptions';

        $wpdb->suppress_errors(true);
        
        // Get all plans
        $all_plans = $wpdb->get_results( "SELECT id, name, course_id FROM $plans_table ORDER BY name ASC" );
        
        // Get user subscriptions
        $user_subs = $wpdb->get_results( $wpdb->prepare( 
            "SELECT plan_id FROM $subs_table WHERE user_id = %d AND status IN ('active', 'completed', 'trialing')", 
            $user->ID 
        ) );

        $wpdb->suppress_errors(false);
        
        $active_plan_ids = [];
        if ( $user_subs ) {
            foreach ( $user_subs as $sub ) {
                $active_plan_ids[] = intval( $sub->plan_id );
            }
        }
        
        if ( $all_plans ) {
            foreach ( $all_plans as $plan ) {
                $plan_info = [
                    'id' => $plan->id,
                    'title' => $plan->name,
                    'course_id' => intval( $plan->course_id )
                ];
                
                if ( in_array( intval( $plan->id ), $active_plan_ids ) ) {
                    $enrolled_plans[] = $plan_info;
                } else {
                    $available_plans[] = $plan_info;
                }
            }
        }
        
        $data['enrolled_plans'] = $enrolled_plans;
        $data['available_plans'] = $available_plans;

		\wp_send_json_success( $data );
	}

	/**
	 * AJAX Handler: Actualizar datos del estudiante
	 */
	public function ajax_update_student() {
		\check_ajax_referer( 'alezux_estudiantes_nonce', 'nonce' );

		if ( ! \current_user_can( 'edit_users' ) ) {
			\wp_send_json_error( [ 'message' => 'No autorizado' ] );
		}

		$user_id    = isset( $_POST['user_id'] ) ? \intval( $_POST['user_id'] ) : 0;
		$first_name = isset( $_POST['first_name'] ) ? \sanitize_text_field( $_POST['first_name'] ) : '';
		$last_name  = isset( $_POST['last_name'] ) ? \sanitize_text_field( $_POST['last_name'] ) : '';
		$email      = isset( $_POST['email'] ) ? \sanitize_email( $_POST['email'] ) : '';
		$password   = isset( $_POST['password'] ) ? \sanitize_text_field( $_POST['password'] ) : '';

		if ( ! \get_user_by( 'id', $user_id ) ) {
			\wp_send_json_error( [ 'message' => 'Usuario inválido' ] );
		}

		// Verificar email duplicado (si cambió)
		$user = \get_user_by( 'id', $user_id );
		if ( $email !== $user->user_email && \email_exists( $email ) ) {
			\wp_send_json_error( [ 'message' => 'El correo ya está en uso por otro usuario.' ] );
		}

		$user_data = [
			'ID'         => $user_id,
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'user_email' => $email,
		];

		if ( ! empty( $password ) ) {
			$user_data['user_pass'] = $password;
		}

		$updated = \wp_update_user( $user_data );

		if ( \is_wp_error( $updated ) ) {
			\wp_send_json_error( [ 'message' => $updated->get_error_message() ] );
		}

		\wp_send_json_success( [ 'message' => 'Datos actualizados correctamente.' ] );
	}

	/**
	 * AJAX Handler: Reset Password
	 */
	public function ajax_reset_password() {
		\check_ajax_referer( 'alezux_estudiantes_nonce', 'nonce' );
		if ( ! \current_user_can( 'edit_users' ) ) \wp_send_json_error( [ 'message' => 'No autorizado' ] );

		$user_id = isset( $_POST['user_id'] ) ? \intval( $_POST['user_id'] ) : 0;
		$user = \get_user_by( 'id', $user_id );

		if ( ! $user ) \wp_send_json_error( [ 'message' => 'Usuario no encontrado' ] );

		// Generar password
		$new_pass = \wp_generate_password( 12, true );
		\wp_set_password( $new_pass, $user_id );

		// Enviar email (Delegado a Marketing)
		if ( class_exists( '\Alezux_Members\Modules\Marketing\Marketing' ) ) {
			$sent = \Alezux_Members\Modules\Marketing\Marketing::get_instance()->get_engine()->send_email(
				'admin_reset_password',
				$user->user_email,
				[
					'user' => $user,
					'new_password' => $new_pass, // Variable oficial
					'password'     => $new_pass, // Alias por compatibilidad
					'login_url'    => \wp_login_url()
				]
			);

			if ( ! $sent ) {
				\wp_send_json_error( [ 'message' => 'Contraseña cambiada, pero falló el envío del correo electrónico. Por favor, verifica la configuración de correo.' ] );
			}
		} else {
			\wp_send_json_error( [ 'message' => 'Módulo de Marketing no activo. La contraseña se cambió pero no se pudo enviar el correo.' ] );
		}

		\wp_send_json_success( [ 'message' => 'Contraseña restablecida y enviada por correo correctamente.' ] );
	}

	/**
	 * AJAX Handler: Bloquear / Desbloquear
	 */
	public function ajax_toggle_block_user() {
		\check_ajax_referer( 'alezux_estudiantes_nonce', 'nonce' );
		if ( ! \current_user_can( 'edit_users' ) ) \wp_send_json_error( [ 'message' => 'No autorizado' ] );

		$user_id = isset( $_POST['user_id'] ) ? \intval( $_POST['user_id'] ) : 0;
		$action  = isset( $_POST['block_action'] ) ? \sanitize_text_field( $_POST['block_action'] ) : ''; // 'block' or 'unblock'

		if ( ! \get_user_by( 'id', $user_id ) ) \wp_send_json_error( [ 'message' => 'Usuario inválido' ] );

		if ( $action === 'block' ) {
			\update_user_meta( $user_id, 'alezux_is_blocked', 1 );
			// Opcional: Podrías cambiar el rol a 'subscriber' sin caps o similar, pero meta es mas seguro para lógica custom
			\wp_send_json_success( [ 'message' => 'Usuario bloqueado.' ] );
		} else {
			\delete_user_meta( $user_id, 'alezux_is_blocked' );
			\wp_send_json_success( [ 'message' => 'Usuario desbloqueado.' ] );
		}
	}

	/**
	 * AJAX Handler: Actualizar acceso cursos
	 */
	public function ajax_update_course_access() {
		\check_ajax_referer( 'alezux_estudiantes_nonce', 'nonce' );
		if ( ! \current_user_can( 'edit_users' ) ) \wp_send_json_error( [ 'message' => 'No autorizado' ] );

		$user_id   = isset( $_POST['user_id'] ) ? \intval( $_POST['user_id'] ) : 0;
		$course_id = isset( $_POST['course_id'] ) ? \intval( $_POST['course_id'] ) : 0;
		$action    = isset( $_POST['access_action'] ) ? \sanitize_text_field( $_POST['access_action'] ) : ''; // 'add' or 'remove'

		if ( ! function_exists( 'ld_update_course_access' ) ) {
			\wp_send_json_error( [ 'message' => 'LearnDash no activo' ] );
		}

		if ( $action === 'add' ) {
			\ld_update_course_access( $user_id, $course_id, false ); // false = add
			\wp_send_json_success( [ 'message' => 'Acceso concedido.' ] );
		} else {
			\ld_update_course_access( $user_id, $course_id, true ); // true = remove
			\wp_send_json_success( [ 'message' => 'Acceso revocado.' ] );
		}
	}

	/**
	 * AJAX Handler: Actualizar acceso a planes
	 */
	public function ajax_update_plan_access() {
		\check_ajax_referer( 'alezux_estudiantes_nonce', 'nonce' );
		if ( ! \current_user_can( 'edit_users' ) ) \wp_send_json_error( [ 'message' => 'No autorizado' ] );

		$user_id = isset( $_POST['user_id'] ) ? \intval( $_POST['user_id'] ) : 0;
		$plan_id = isset( $_POST['plan_id'] ) ? \intval( $_POST['plan_id'] ) : 0;
		$action  = isset( $_POST['access_action'] ) ? \sanitize_text_field( $_POST['access_action'] ) : ''; // 'add' or 'remove'

        if ( ! $user_id || ! $plan_id ) {
            \wp_send_json_error( [ 'message' => 'Datos inválidos' ] );
        }

        global $wpdb;
        $subs_table = $wpdb->prefix . 'alezux_finanzas_subscriptions';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$subs_table'" ) != $subs_table ) {
            \wp_send_json_error( [ 'message' => 'Módulo de finanzas no activo' ] );
        }

		if ( $action === 'add' ) {
            // Check if exists
            $existing_sub = $wpdb->get_row( $wpdb->prepare( 
                "SELECT id, status FROM $subs_table WHERE user_id = %d AND plan_id = %d", 
                $user_id, $plan_id 
            ) );

            if ( $existing_sub ) {
                if ( $existing_sub->status !== 'active' && $existing_sub->status !== 'completed' ) {
                    $wpdb->update( $subs_table, [ 'status' => 'active' ], [ 'id' => $existing_sub->id ] );
                }
            } else {
                $wpdb->insert( $subs_table, [
                    'user_id' => $user_id,
                    'plan_id' => $plan_id,
                    'status' => 'active',
                    'quotas_paid' => 1,
                    'last_payment_date' => current_time( 'mysql' )
                ] );
            }
            
            // Give LearnDash course access if plan has course
            $plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
            $plan_course_id = $wpdb->get_var( $wpdb->prepare( "SELECT course_id FROM $plans_table WHERE id = %d", $plan_id ) );
            if ( $plan_course_id && $plan_course_id > 0 && function_exists( 'ld_update_course_access' ) ) {
                \ld_update_course_access( $user_id, $plan_course_id, false ); // Add access
            }

			\wp_send_json_success( [ 'message' => 'Plan concedido correctamente.' ] );
		} else {
            // Remove
            $wpdb->query( $wpdb->prepare( 
                "UPDATE $subs_table SET status = 'cancelled' WHERE user_id = %d AND plan_id = %d", 
                $user_id, $plan_id 
            ) );
            
            // Optional: remove course access
            $plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
            $plan_course_id = $wpdb->get_var( $wpdb->prepare( "SELECT course_id FROM $plans_table WHERE id = %d", $plan_id ) );
            if ( $plan_course_id && $plan_course_id > 0 && function_exists( 'ld_update_course_access' ) ) {
                \ld_update_course_access( $user_id, $plan_course_id, true ); // Remove access
            }

			\wp_send_json_success( [ 'message' => 'Plan revocado correctamente.' ] );
		}
	}

	/**
	 * AJAX Handler: Bulk process students (Assign Plan and Resend Credentials)
	 */
	public function ajax_bulk_student_process() {
		\check_ajax_referer( 'alezux_estudiantes_nonce', 'nonce' );
		if ( ! \current_user_can( 'edit_users' ) ) \wp_send_json_error( [ 'message' => 'No autorizado' ] );

		$user_id = isset( $_POST['user_id'] ) ? \intval( $_POST['user_id'] ) : 0;
		$plan_id = isset( $_POST['plan_id'] ) ? \intval( $_POST['plan_id'] ) : 0;

		$user = \get_user_by( 'id', $user_id );
		if ( ! $user ) {
			\wp_send_json_error( [ 'message' => 'Estudiante no encontrado.' ] );
		}

		global $wpdb;
		$course_id = 0;

		// Asignar Plan si se especificó
		if ( $plan_id > 0 ) {
			$subs_table = $wpdb->prefix . 'alezux_finanzas_subscriptions';
			
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$subs_table'" ) == $subs_table ) {
				$existing_sub = $wpdb->get_row( $wpdb->prepare( 
					"SELECT id, status FROM $subs_table WHERE user_id = %d AND plan_id = %d", 
					$user_id, $plan_id 
				) );

				if ( $existing_sub ) {
					if ( $existing_sub->status !== 'active' && $existing_sub->status !== 'completed' ) {
						$wpdb->update( $subs_table, [ 'status' => 'active' ], [ 'id' => $existing_sub->id ] );
					}
				} else {
					$wpdb->insert( $subs_table, [
						'user_id' => $user_id,
						'plan_id' => $plan_id,
						'status' => 'active',
						'quotas_paid' => 1,
						'last_payment_date' => current_time( 'mysql' )
					] );
				}
				
				$plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
				$course_id = $wpdb->get_var( $wpdb->prepare( "SELECT course_id FROM $plans_table WHERE id = %d", $plan_id ) );
				if ( $course_id && $course_id > 0 && function_exists( 'ld_update_course_access' ) ) {
					\ld_update_course_access( $user_id, $course_id, false );
				}
			}
		}

		// Generar nueva contraseña y enviar credenciales
		$password = \wp_generate_password( 12, true );
		\wp_set_password( $password, $user_id );

		$email_sent = $this->send_new_user_email( $user, $password, $course_id, $plan_id );

		if ( ! $email_sent ) {
			$engine = \Alezux_Members\Modules\Marketing\Marketing::get_instance()->get_engine();
			$err_msg = $engine->get_last_error_message();
			\wp_send_json_error( [ 'message' => 'Contraseña actualizada, pero falló el correo: ' . $err_msg ] );
		}

		\wp_send_json_success( [ 'message' => 'Procesado correctamente.' ] );
	}

	public function shortcode_study_momentum() {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$user_id = get_current_user_id();
		global $wpdb;
		$table_name = $wpdb->prefix . 'alezux_study_log';

		// Verificar si tabla existe (por si acaso Modulo Formaciones no activo)
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
			return '';
		}

		// Fechas (Semana = Lunes a Domingo)
		// Semana Actual
		$this_week_start = date( 'Y-m-d', strtotime( 'monday this week' ) );
		$this_week_end   = date( 'Y-m-d', strtotime( 'sunday this week' ) );

		// Semana Pasada
		$last_week_start = date( 'Y-m-d', strtotime( 'monday last week' ) );
		$last_week_end   = date( 'Y-m-d', strtotime( 'sunday last week' ) );

		// Query Current Week
		$current_seconds = $wpdb->get_var( $wpdb->prepare(
			"SELECT SUM(seconds) FROM $table_name WHERE user_id = %d AND date BETWEEN %s AND %s",
			$user_id, $this_week_start, $this_week_end
		) );
		$current_seconds = intval( $current_seconds );

		// Query Last Week
		$last_seconds = $wpdb->get_var( $wpdb->prepare(
			"SELECT SUM(seconds) FROM $table_name WHERE user_id = %d AND date BETWEEN %s AND %s",
			$user_id, $last_week_start, $last_week_end
		) );
		$last_seconds = intval( $last_seconds );

		// Cálculos
		if ( $last_seconds > 0 ) {
			if ( $current_seconds >= $last_seconds ) {
				// Crecimiento
				$diff = $current_seconds - $last_seconds;
				$percentage = round( ( $diff / $last_seconds ) * 100 );
				$html = sprintf( 
					'<span class="alezux-momentum positive">🚀 %s%% %s</span>', 
					$percentage, 
					__( 'más que la semana pasada', 'alezux-members' ) 
				);
			} else {
				// Decrecimiento
				$diff = $last_seconds - $current_seconds;
				$percentage = round( ( $diff / $last_seconds ) * 100 );
				$html = sprintf( 
					'<span class="alezux-momentum negative">📉 %s%% %s</span>', 
					$percentage, 
					__( 'menos que la semana pasada', 'alezux-members' ) 
				);
			}
		} elseif ( $current_seconds > 0 ) {
			// Pasó de nada a algo
			$html = '<span class="alezux-momentum positive">🔥 ' . __( '¡Has comenzado con fuerza esta semana!', 'alezux-members' ) . '</span>';
		} else {
			// Sin actividad relevante
			$html = '<span class="alezux-momentum neutral">' . __( 'Sin actividad reciente', 'alezux-members' ) . '</span>';
		}

		return $html;
	}
}
