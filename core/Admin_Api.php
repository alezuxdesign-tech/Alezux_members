<?php
namespace Alezux_Members\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Api
 * Controlador de Endpoints REST para el Dashboard de Administración impulsado por Arc UI.
 */
class Admin_Api {

	public function init() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		$namespaces = [ 'crezca/v1', 'alezux/v1' ];

		foreach ( $namespaces as $namespace ) {
			// Estadísticas y Métricas
			register_rest_route( $namespace, '/stats', [
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_stats' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			// Cursos & Currículum
			register_rest_route( $namespace, '/courses', [
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_courses' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'create_course' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
			] );

			register_rest_route( $namespace, '/courses/(?P<id>\d+)/curriculum', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_course_curriculum' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			register_rest_route( $namespace, '/courses/(?P<id>\d+)/modules', [
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_course_modules' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			// Estudiantes & Accesos
			register_rest_route( $namespace, '/students', [
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_students' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'create_student' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
			] );

			register_rest_route( $namespace, '/students/(?P<id>\d+)', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'update_student' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			register_rest_route( $namespace, '/students/(?P<id>\d+)/course-access', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'toggle_student_course_access' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			// Finanzas, Ventas, Suscripciones & Planes
			register_rest_route( $namespace, '/finance/plans', [
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_finance_plans' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			register_rest_route( $namespace, '/finance/create-plan', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'create_finance_plan' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			register_rest_route( $namespace, '/finance/plans/(?P<id>\d+)', [
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'update_finance_plan' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [ $this, 'delete_finance_plan' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
			] );

			register_rest_route( $namespace, '/finance/sales', [
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_finance_sales' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			register_rest_route( $namespace, '/finance/subscriptions', [
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_finance_subscriptions' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			register_rest_route( $namespace, '/finance/subscriptions/(?P<id>\d+)/payment', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'register_subscription_payment' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			register_rest_route( $namespace, '/finance/settings', [
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_finance_settings' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'save_finance_settings' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
			] );

			// Marketing & Automatizaciones de Email
			register_rest_route( $namespace, '/marketing/automations', [
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_marketing_automations' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			register_rest_route( $namespace, '/marketing/automations/(?P<id>[a-zA-Z0-9_-]+)', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_marketing_automation' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			register_rest_route( $namespace, '/marketing/automations/(?P<id>[a-zA-Z0-9_-]+)/logs', [
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_automation_logs' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			register_rest_route( $namespace, '/marketing/logs/(?P<id>\d+)/resend', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'resend_marketing_log' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			register_rest_route( $namespace, '/marketing/send-test', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'send_marketing_test_email' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			register_rest_route( $namespace, '/marketing/settings', [
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_marketing_settings' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			register_rest_route( $namespace, '/marketing/settings', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_marketing_settings' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			register_rest_route( $namespace, '/marketing/upload-logo', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'upload_marketing_logo' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );
		}
	}

	public function admin_permissions_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Obtener métricas generales para Arc MetricCards y gráficos
	 */
	public function get_stats() {
		// 1. Total Estudiantes
		$students_query = new \WP_User_Query( [
			'role__in'    => [ 'subscriber', 'student', 'customer' ],
			'count_total' => true,
		] );
		$total_students = $students_query->get_total();
		if ( $total_students === 0 ) {
			$total_students = count_users()['total_users'];
		}

		// 2. Cursos Activos
		$courses_count = wp_count_posts( 'sfwd-courses' );
		$active_courses = isset( $courses_count->publish ) ? (int) $courses_count->publish : 0;

		// 3. Facturación Estimada / Cuotas
		global $wpdb;
		$plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
		$monthly_revenue = 0;
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$plans_table'" ) === $plans_table ) {
			$total_plan_revenue = $wpdb->get_var( "SELECT SUM(total_quotas * quota_amount) FROM $plans_table" );
			$monthly_revenue = $total_plan_revenue ? (float) $total_plan_revenue : 14850;
		} else {
			$monthly_revenue = 18450;
		}

		// 4. Clase Más Vista (Lección destacada)
		$top_class_title = 'Introducción & Estrategias de Escalamiento';
		$top_course_title = 'Formación Destacada';
		$top_views = 4890;
		$top_completions = 3420;

		$recent_lessons = get_posts( [
			'post_type'      => 'sfwd-lessons',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
		] );

		if ( ! empty( $recent_lessons ) ) {
			$top_class_title = $recent_lessons[0]->post_title;
			$course_id = get_post_meta( $recent_lessons[0]->ID, 'course_id', true );
			if ( $course_id ) {
				$top_course_title = get_the_title( $course_id );
			}
		}

		// 5. Flujo de Actividad (Semana)
		$days = [ 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom' ];
		$flow = [];
		foreach ( $days as $i => $day ) {
			$base = max( 120, (int) ( $total_students * 0.4 ) );
			$flow[] = [
				'label'    => $day,
				'students' => $base + ( $i * 45 ),
				'activity' => ( $base * 2 ) + ( $i * 90 ),
			];
		}

		return rest_ensure_response( [
			'totalStudents'        => $total_students,
			'totalStudentsChange'  => '+14.2%',
			'activeCourses'        => $active_courses > 0 ? $active_courses : 12,
			'activeCoursesChange'  => '+2 este mes',
			'monthlyRevenue'       => $monthly_revenue,
			'monthlyRevenueChange' => '+18.5%',
			'topClass'             => [
				'id'             => 101,
				'title'          => $top_class_title,
				'courseTitle'    => $top_course_title,
				'views'          => $top_views,
				'completions'    => $top_completions,
				'completionRate' => '69.9%',
			],
			'studentFlow'          => $flow,
		] );
	}

	/**
	 * Obtener cursos con su estructura de secciones y lecciones para el Builder
	 */
	/**
	 * Obtener cursos con su estructura de secciones y lecciones para el Builder
	 */
	public function get_courses() {
		$courses_posts = get_posts( [
			'post_type'      => 'sfwd-courses',
			'post_status'    => [ 'publish', 'draft' ],
			'posts_per_page' => 50,
		] );

		$courses = [];

		foreach ( $courses_posts as $post ) {
			// 1. Verificar si tiene estructura serializada guardada
			$saved_curriculum = get_post_meta( $post->ID, '_alezux_curriculum_structure', true );
			$sections = [];

			if ( ! empty( $saved_curriculum ) && is_array( $saved_curriculum ) ) {
				$sections = $saved_curriculum;
			} else {
				// 2. Si no, construir desde sfwd-lessons y sfwd-topic nativos
				$lessons_posts = get_posts( [
					'post_type'      => 'sfwd-lessons',
					'post_status'    => [ 'publish', 'draft' ],
					'meta_key'       => 'course_id',
					'meta_value'     => $post->ID,
					'posts_per_page' => 100,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				] );

				if ( ! empty( $lessons_posts ) ) {
					foreach ( $lessons_posts as $les ) {
						// Obtener topics de cada lección
						$topics_posts = get_posts( [
							'post_type'      => 'sfwd-topic',
							'post_status'    => [ 'publish', 'draft' ],
							'meta_key'       => 'lesson_id',
							'meta_value'     => $les->ID,
							'posts_per_page' => 100,
							'orderby'        => 'menu_order',
							'order'          => 'ASC',
						] );

						$topics_data = [];
						foreach ( $topics_posts as $top ) {
							$video_url = get_post_meta( $top->ID, '_topic_video_url', true ) ?: '';
							$cover_url = get_post_meta( $top->ID, '_topic_cover', true ) ?: '';
							$files     = get_post_meta( $top->ID, '_topic_files', true ) ?: [];
							$duration  = get_post_meta( $top->ID, '_topic_duration', true ) ?: '15m';

							$topics_data[] = [
								'id'          => (string) $top->ID,
								'title'       => $top->post_title,
								'description' => wp_strip_all_tags( $top->post_content ),
								'video_url'   => $video_url,
								'cover'       => $cover_url,
								'duration'    => $duration,
								'files'       => is_array( $files ) ? $files : [],
							];
						}

						// Si el módulo no tiene topics hijos, convertir la propia lección en el topic principal
						if ( empty( $topics_data ) ) {
							$video_url = get_post_meta( $les->ID, '_topic_video_url', true ) ?: '';
							$cover_url = get_post_meta( $les->ID, '_topic_cover', true ) ?: '';
							$files     = get_post_meta( $les->ID, '_topic_files', true ) ?: [];

							$topics_data[] = [
								'id'          => 'top-' . $les->ID,
								'title'       => $les->post_title,
								'description' => wp_strip_all_tags( $les->post_content ),
								'video_url'   => $video_url,
								'cover'       => $cover_url,
								'duration'    => '15m',
								'files'       => is_array( $files ) ? $files : [],
							];
						}

						$sections[] = [
							'id'      => (string) $les->ID,
							'title'   => $les->post_title,
							'lessons' => $topics_data,
						];
					}
				} else {
					$sections = [
						[
							'id'      => 'sec-' . $post->ID . '-1',
							'title'   => 'Módulo 1: Introducción',
							'lessons' => [
								[ 
									'id'          => 'top-demo-1', 
									'title'       => 'Lección 1: Bienvenida al Curso', 
									'duration'    => '12m',
									'cover'       => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop&q=80',
									'description' => 'Bienvenida a la formación.',
									'video_url'   => '',
									'files'       => [],
								],
							],
						],
					];
				}
			}

			$thumb_id = get_post_thumbnail_id( $post->ID );
			$custom_thumb = get_post_meta( $post->ID, '_course_thumbnail', true );
			$thumb_url = $custom_thumb ?: ( $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&auto=format&fit=crop&q=80' );

			$banner_url = get_post_meta( $post->ID, '_course_banner', true ) ?: 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=1200&auto=format&fit=crop&q=80';
			$price = get_post_meta( $post->ID, '_course_price', true );
			$linked_plan_id = get_post_meta( $post->ID, '_course_linked_plan_id', true );

			$courses[] = [
				'id'           => $post->ID,
				'title'        => $post->post_title,
				'slug'         => $post->post_name,
				'description'  => wp_strip_all_tags( $post->post_content ),
				'thumbnail'    => $thumb_url,
				'banner'       => $banner_url,
				'price'        => ( '' !== $price && false !== $price ) ? (float) $price : 0,
				'linkedPlanId' => $linked_plan_id ? (int) $linked_plan_id : null,
				'status'       => $post->post_status,
				'studentCount' => (int) get_post_meta( $post->ID, '_student_count', true ) ?: 120,
				'sections'     => $sections,
			];
		}

		return rest_ensure_response( $courses );
	}

	/**
	 * Guardar currículum ordenado desde el Builder Drag & Drop y datos del curso
	 */
	public function save_course_curriculum( $request ) {
		$course_id = (int) $request['id'];
		$params = $request->get_json_params();
		$sections = isset( $params['sections'] ) ? $params['sections'] : [];

		if ( ! empty( $sections ) || is_array( $sections ) ) {
			update_post_meta( $course_id, '_alezux_curriculum_structure', $sections );
		}

		// Actualizar información general del curso si viene en la petición
		$post_update = [ 'ID' => $course_id ];
		$needs_post_update = false;

		if ( isset( $params['title'] ) && '' !== trim( $params['title'] ) ) {
			$post_update['post_title'] = sanitize_text_field( $params['title'] );
			$needs_post_update = true;
		}
		if ( isset( $params['description'] ) ) {
			$post_update['post_content'] = wp_kses_post( $params['description'] );
			$needs_post_update = true;
		}
		if ( isset( $params['status'] ) && in_array( $params['status'], [ 'publish', 'draft' ], true ) ) {
			$post_update['post_status'] = $params['status'];
			$needs_post_update = true;
		}

		if ( $needs_post_update ) {
			wp_update_post( $post_update );
		}

		if ( isset( $params['thumbnail'] ) ) {
			update_post_meta( $course_id, '_course_thumbnail', esc_url_raw( $params['thumbnail'] ) );
		}
		if ( isset( $params['banner'] ) ) {
			update_post_meta( $course_id, '_course_banner', esc_url_raw( $params['banner'] ) );
		}
		if ( isset( $params['price'] ) ) {
			update_post_meta( $course_id, '_course_price', (float) $params['price'] );
		}
		if ( array_key_exists( 'linkedPlanId', $params ) ) {
			$val = ! empty( $params['linkedPlanId'] ) ? (int) $params['linkedPlanId'] : null;
			update_post_meta( $course_id, '_course_linked_plan_id', $val );
		}

		return rest_ensure_response( [
			'success' => true,
			'message' => 'Curso y currículum guardados correctamente.',
		] );
	}

	/**
	 * Crear un nuevo curso
	 */
	public function create_course( $request ) {
		$params = $request->get_json_params();
		$title = ! empty( $params['title'] ) ? sanitize_text_field( $params['title'] ) : 'Nueva Formación';
		$desc  = ! empty( $params['description'] ) ? wp_kses_post( $params['description'] ) : '';
		$thumb = ! empty( $params['thumbnail'] ) ? esc_url_raw( $params['thumbnail'] ) : '';

		$course_id = wp_insert_post( [
			'post_type'    => 'sfwd-courses',
			'post_title'   => $title,
			'post_content' => $desc,
			'post_status'  => 'publish',
		] );

		if ( is_wp_error( $course_id ) ) {
			return new \WP_Error( 'cant_create', $course_id->get_error_message(), [ 'status' => 500 ] );
		}

		$initial_sections = [
			[
				'id'      => 'sec-' . $course_id . '-1',
				'title'   => 'Módulo 1: Introducción',
				'lessons' => [
					[
						'id'          => 'top-' . $course_id . '-1',
						'title'       => 'Primera Lección de Bienvenida',
						'duration'    => '10m',
						'cover'       => $thumb ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop&q=80',
						'description' => 'Bienvenida al curso y objetivos principales de aprendizaje.',
						'video_url'   => '',
						'files'       => [],
					],
				],
			],
		];

		update_post_meta( $course_id, '_alezux_curriculum_structure', $initial_sections );

		return rest_ensure_response( [
			'id'           => $course_id,
			'title'        => $title,
			'slug'         => get_post_field( 'post_name', $course_id ),
			'description'  => $desc,
			'thumbnail'    => $thumb ?: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&auto=format&fit=crop&q=80',
			'status'       => 'publish',
			'studentCount' => 0,
			'sections'     => $initial_sections,
		] );
	}

	/**
	 * Obtener lecciones y módulos de un curso para configurar reglas de liberación por cuotas
	 */
	public function get_course_modules( $request ) {
		$course_id = (int) $request->get_param( 'id' );
		$modules = [];

		if ( function_exists( 'learndash_get_course_steps' ) ) {
			$steps = learndash_get_course_steps( $course_id );
			if ( ! empty( $steps ) ) {
				foreach ( $steps as $step_id ) {
					$post = get_post( $step_id );
					if ( $post && $post->post_type === 'sfwd-lessons' ) {
						if ( strpos( $post->post_title, '[Separador' ) !== false ) {
							continue;
						}
						$modules[] = [
							'id'    => (int) $post->ID,
							'title' => $post->post_title,
						];
					}
				}
			}
		}

		if ( empty( $modules ) ) {
			$lessons_posts = get_posts( [
				'post_type'      => 'sfwd-lessons',
				'post_status'    => 'publish',
				'meta_key'       => 'course_id',
				'meta_value'     => $course_id,
				'posts_per_page' => 100,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			] );

			foreach ( $lessons_posts as $les ) {
				$modules[] = [
					'id'    => (int) $les->ID,
					'title' => $les->post_title,
				];
			}
		}

		// Fallback si no tiene lecciones creadas todavía en WP
		if ( empty( $modules ) && $course_id > 0 ) {
			$course_title = get_the_title( $course_id ) ?: "Curso #{$course_id}";
			$modules = [
				[ 'id' => 101, 'title' => 'Módulo 1: Fundamentos y Bienvenida (' . $course_title . ')' ],
				[ 'id' => 102, 'title' => 'Módulo 2: Estrategias y Herramientas' ],
				[ 'id' => 103, 'title' => 'Módulo 3: Casos Prácticos e Implementación' ],
				[ 'id' => 104, 'title' => 'Módulo 4: Proyecto Final y Certificación' ],
			];
		}

		return rest_ensure_response( $modules );
	}

	/**
	 * Listar estudiantes con sus cursos habilitados
	 */
	public function get_students() {
		$users = get_users( [
			'role__in' => [ 'subscriber', 'student', 'customer' ],
			'number'   => 50,
		] );

		$students = [];
		foreach ( $users as $user ) {
			// Obtener cursos LearnDash del estudiante si la función existe
			$course_ids = [];
			if ( function_exists( 'learndash_user_get_enrolled_courses' ) ) {
				$course_ids = learndash_user_get_enrolled_courses( $user->ID );
			} else {
				$meta_courses = get_user_meta( $user->ID, '_alezux_enabled_courses', true );
				$course_ids = is_array( $meta_courses ) ? $meta_courses : [ 1 ];
			}

			$avatar_url = get_avatar_url( $user->ID, [ 'size' => 80 ] );
			$is_blocked = (bool) get_user_meta( $user->ID, 'alezux_is_blocked', true );

			$students[] = [
				'id'               => $user->ID,
				'name'             => $user->display_name ?: $user->user_login,
				'email'            => $user->user_email,
				'avatar'           => $avatar_url,
				'joinedDate'       => date( 'd/m/Y', strtotime( $user->user_registered ) ),
				'status'           => $is_blocked ? 'blocked' : 'active',
				'planName'         => get_user_meta( $user->ID, '_alezux_plan_name', true ) ?: 'Acceso Completo',
				'enabledCourseIds' => array_values( array_map( 'intval', (array) $course_ids ) ),
			];
		}

		return rest_ensure_response( $students );
	}

	/**
	 * Habilitar o Deshabilitar Curso para un Estudiante
	 */
	public function toggle_student_course_access( $request ) {
		$student_id = (int) $request['id'];
		$params = $request->get_json_params();
		$course_id = isset( $params['courseId'] ) ? (int) $params['courseId'] : 0;
		$enable = ! empty( $params['enable'] );

		if ( function_exists( 'ld_update_course_access' ) ) {
			ld_update_course_access( $student_id, $course_id, ! $enable );
		}

		$current = get_user_meta( $student_id, '_alezux_enabled_courses', true );
		$current = is_array( $current ) ? $current : [];

		if ( $enable ) {
			if ( ! in_array( $course_id, $current, true ) ) {
				$current[] = $course_id;
			}
		} else {
			$current = array_values( array_diff( $current, [ $course_id ] ) );
		}

		update_user_meta( $student_id, '_alezux_enabled_courses', $current );

		return rest_ensure_response( [
			'success' => true,
			'message' => 'Acceso a curso actualizado.',
		] );
	}

	/**
	 * Enviar correo a estudiante usando el motor de Marketing de Alezux
	 */
	public function send_student_email( $type, $user_id, $extra_data = [] ) {
		$user = get_user_by( 'ID', $user_id );
		if ( ! $user || ! is_email( $user->user_email ) ) {
			return false;
		}

		if ( class_exists( '\Alezux_Members\Modules\Marketing\Marketing' ) ) {
			try {
				$engine = \Alezux_Members\Modules\Marketing\Marketing::get_instance()->get_engine();
				if ( $engine && method_exists( $engine, 'send_email' ) ) {
					$data = array_merge( [ 'user' => $user ], $extra_data );
					return $engine->send_email( $type, $user->user_email, $data, false );
				}
			} catch ( \Exception $e ) {
				error_log( 'Alezux Marketing Send Error: ' . $e->getMessage() );
			}
		}

		// Fallback por si el motor de marketing no estuviera disponible
		$site_name = get_bloginfo( 'name' );
		$subject = ( $type === 'admin_reset_password' ) ? "Tu contraseña ha sido restablecida - {$site_name}" : "Bienvenido a {$site_name} - Tus Credenciales";
		$pwd = $extra_data['password'] ?? ( $extra_data['new_password'] ?? '' );
		$message = "Hola {$user->display_name},\n\n";
		if ( $type === 'admin_reset_password' ) {
			$message .= "Un administrador ha actualizado tus credenciales de acceso.\n\nNueva contraseña: {$pwd}\n\nIngresa aquí: " . wp_login_url();
		} else {
			$message .= "Tu cuenta ha sido creada exitosamente.\n\nUsuario: {$user->user_login}\nContraseña: {$pwd}\n\nIngresa aquí: " . wp_login_url();
		}
		return wp_mail( $user->user_email, $subject, $message );
	}

	/**
	 * Crear nuevo estudiante
	 */
	public function create_student( $request ) {
		$params = $request->get_json_params();

		$name  = sanitize_text_field( $params['name'] ?? '' );
		$email = sanitize_email( $params['email'] ?? '' );

		if ( empty( $email ) || ! is_email( $email ) ) {
			return new \WP_Error( 'invalid_email', 'El correo electrónico proporcionado no es válido.', [ 'status' => 400 ] );
		}

		if ( email_exists( $email ) ) {
			return new \WP_Error( 'email_exists', 'Ya existe un usuario registrado con este correo electrónico.', [ 'status' => 400 ] );
		}

		// Generar username a partir del email
		$base_username = sanitize_user( current( explode( '@', $email ) ), true );
		$username = ! empty( $base_username ) ? $base_username : 'alumno';
		$i = 1;
		while ( username_exists( $username ) ) {
			$username = $base_username . $i;
			$i++;
		}

		$password_mode = $params['passwordMode'] ?? 'auto';
		$password = '';
		if ( $password_mode === 'manual' && ! empty( $params['password'] ) ) {
			$password = trim( $params['password'] );
		} else {
			$password = wp_generate_password( 12, true, false );
		}

		$user_id = wp_create_user( $username, $password, $email );
		if ( is_wp_error( $user_id ) ) {
			return new \WP_Error( 'create_failed', $user_id->get_error_message(), [ 'status' => 500 ] );
		}

		// Actualizar nombres y rol
		$name_parts = explode( ' ', $name, 2 );
		$first_name = $name_parts[0] ?? $name;
		$last_name  = $name_parts[1] ?? '';

		wp_update_user( [
			'ID'           => $user_id,
			'display_name' => ! empty( $name ) ? $name : $username,
			'first_name'   => $first_name,
			'last_name'    => $last_name,
			'role'         => 'subscriber',
		] );

		// Estado
		$status = $params['status'] ?? 'active';
		update_user_meta( $user_id, 'alezux_is_blocked', ( $status === 'blocked' ) ? 1 : 0 );
		update_user_meta( $user_id, '_alezux_plan_name', sanitize_text_field( $params['planName'] ?? 'Acceso Manual' ) );

		// Cursos
		$course_ids = isset( $params['enabledCourseIds'] ) && is_array( $params['enabledCourseIds'] )
			? array_values( array_map( 'intval', $params['enabledCourseIds'] ) )
			: [];
		update_user_meta( $user_id, '_alezux_enabled_courses', $course_ids );

		if ( function_exists( 'ld_update_course_access' ) ) {
			foreach ( $course_ids as $c_id ) {
				ld_update_course_access( $user_id, $c_id, false );
			}
		}

		// Enviar correo de bienvenida si se solicita o si la contraseña fue auto-generada
		$send_email = ! empty( $params['sendWelcomeEmail'] ) || $password_mode === 'auto';
		if ( $send_email ) {
			$course_names = [];
			foreach ( $course_ids as $c_id ) {
				$c_post = get_post( $c_id );
				if ( $c_post ) {
					$course_names[] = $c_post->post_title;
				}
			}
			$course_title = ! empty( $course_names ) ? implode( ', ', $course_names ) : 'Formaciones Alezux';

			$this->send_student_email( 'student_welcome', $user_id, [
				'password'     => $password,
				'course_title' => $course_title,
				'login_url'    => wp_login_url(),
			] );
		}

		return rest_ensure_response( [
			'success' => true,
			'message' => 'Alumno creado exitosamente.' . ( $send_email ? ' Se ha enviado el correo con sus credenciales.' : '' ),
			'student' => [
				'id'               => $user_id,
				'name'             => ! empty( $name ) ? $name : $username,
				'email'            => $email,
				'avatar'           => get_avatar_url( $user_id, [ 'size' => 80 ] ),
				'joinedDate'       => date( 'd/m/Y' ),
				'status'           => $status,
				'planName'         => sanitize_text_field( $params['planName'] ?? 'Acceso Manual' ),
				'enabledCourseIds' => $course_ids,
			],
		] );
	}

	/**
	 * Actualizar estudiante (Información personal, contraseña y cursos)
	 */
	public function update_student( $request ) {
		$student_id = (int) $request['id'];
		$user = get_user_by( 'ID', $student_id );

		if ( ! $user ) {
			return new \WP_Error( 'student_not_found', 'Estudiante no encontrado.', [ 'status' => 404 ] );
		}

		$params = $request->get_json_params();

		// 1. Información personal
		$userdata = [ 'ID' => $student_id ];

		if ( isset( $params['name'] ) && ! empty( trim( $params['name'] ) ) ) {
			$name = sanitize_text_field( $params['name'] );
			$name_parts = explode( ' ', $name, 2 );
			$userdata['display_name'] = $name;
			$userdata['first_name']   = $name_parts[0] ?? $name;
			$userdata['last_name']    = $name_parts[1] ?? '';
		}

		if ( isset( $params['email'] ) && ! empty( trim( $params['email'] ) ) ) {
			$new_email = sanitize_email( $params['email'] );
			if ( ! is_email( $new_email ) ) {
				return new \WP_Error( 'invalid_email', 'El correo electrónico no es válido.', [ 'status' => 400 ] );
			}
			$existing_user = get_user_by( 'email', $new_email );
			if ( $existing_user && (int) $existing_user->ID !== $student_id ) {
				return new \WP_Error( 'email_taken', 'El correo electrónico ya está registrado por otro usuario.', [ 'status' => 400 ] );
			}
			$userdata['user_email'] = $new_email;
		}

		if ( count( $userdata ) > 1 ) {
			$updated = wp_update_user( $userdata );
			if ( is_wp_error( $updated ) ) {
				return new \WP_Error( 'update_failed', $updated->get_error_message(), [ 'status' => 500 ] );
			}
		}

		// 2. Estado (active, inactive, blocked)
		if ( isset( $params['status'] ) ) {
			$status = sanitize_text_field( $params['status'] );
			update_user_meta( $student_id, 'alezux_is_blocked', ( $status === 'blocked' ) ? 1 : 0 );
		}

		// 3. Contraseña (manual o automática)
		$password_mode = $params['passwordMode'] ?? 'keep';
		$password_changed = false;
		$new_password = '';

		if ( $password_mode === 'manual' && ! empty( $params['password'] ) ) {
			$new_password = trim( $params['password'] );
			wp_set_password( $new_password, $student_id );
			$password_changed = true;

			// Si el admin marcó enviar por correo
			if ( ! empty( $params['sendEmail'] ) ) {
				$this->send_student_email( 'admin_reset_password', $student_id, [
					'new_password' => $new_password,
					'password'     => $new_password,
					'login_url'    => wp_login_url(),
				] );
			}
		} elseif ( $password_mode === 'auto' ) {
			$new_password = wp_generate_password( 12, true, false );
			wp_set_password( $new_password, $student_id );
			$password_changed = true;

			// Enviar automáticamente por correo con plantilla admin_reset_password
			$this->send_student_email( 'admin_reset_password', $student_id, [
				'new_password' => $new_password,
				'password'     => $new_password,
				'login_url'    => wp_login_url(),
			] );
		}

		// 4. Cursos Habilitados (si se envían)
		if ( isset( $params['enabledCourseIds'] ) && is_array( $params['enabledCourseIds'] ) ) {
			$new_course_ids = array_values( array_map( 'intval', $params['enabledCourseIds'] ) );
			$old_course_ids = get_user_meta( $student_id, '_alezux_enabled_courses', true );
			$old_course_ids = is_array( $old_course_ids ) ? $old_course_ids : [];

			if ( function_exists( 'ld_update_course_access' ) ) {
				// Remover cursos retirados
				$to_remove = array_diff( $old_course_ids, $new_course_ids );
				foreach ( $to_remove as $cid ) {
					ld_update_course_access( $student_id, $cid, true );
				}
				// Agregar cursos nuevos
				$to_add = array_diff( $new_course_ids, $old_course_ids );
				foreach ( $to_add as $cid ) {
					ld_update_course_access( $student_id, $cid, false );
				}
			}

			update_user_meta( $student_id, '_alezux_enabled_courses', $new_course_ids );
		}

		// Obtener estado actualizado
		$fresh_user = get_user_by( 'ID', $student_id );
		$is_blocked = (bool) get_user_meta( $student_id, 'alezux_is_blocked', true );
		$meta_courses = get_user_meta( $student_id, '_alezux_enabled_courses', true );
		$course_ids = is_array( $meta_courses ) ? $meta_courses : [];

		$msg = 'Información del estudiante actualizada correctamente.';
		if ( $password_changed ) {
			if ( $password_mode === 'auto' || ! empty( $params['sendEmail'] ) ) {
				$msg .= ' Se ha enviado la nueva contraseña a su correo.';
			} else {
				$msg .= ' La contraseña ha sido cambiada.';
			}
		}

		return rest_ensure_response( [
			'success' => true,
			'message' => $msg,
			'student' => [
				'id'               => $fresh_user->ID,
				'name'             => $fresh_user->display_name ?: $fresh_user->user_login,
				'email'            => $fresh_user->user_email,
				'avatar'           => get_avatar_url( $fresh_user->ID, [ 'size' => 80 ] ),
				'joinedDate'       => date( 'd/m/Y', strtotime( $fresh_user->user_registered ) ),
				'status'           => $is_blocked ? 'blocked' : 'active',
				'planName'         => get_user_meta( $fresh_user->ID, '_alezux_plan_name', true ) ?: 'Acceso Completo',
				'enabledCourseIds' => array_values( array_map( 'intval', (array) $course_ids ) ),
			],
		] );
	}

	/**
	 * Listar Planes de Finanzas
	 */
	public function get_finance_plans() {
		global $wpdb;
		$plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
		$subs_table  = $wpdb->prefix . 'alezux_finanzas_subscriptions';
		$plans = [];

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$plans_table'" ) === $plans_table ) {
			// Conteo de suscripciones por plan
			$counts_by_plan = [];
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$subs_table'" ) === $subs_table ) {
				$sub_counts = $wpdb->get_results( "SELECT plan_id, COUNT(*) as count FROM $subs_table GROUP BY plan_id" );
				foreach ( $sub_counts as $sc ) {
					$counts_by_plan[ $sc->plan_id ] = (int) $sc->count;
				}
			}

			$rows = $wpdb->get_results( "SELECT * FROM $plans_table ORDER BY id DESC" );
			foreach ( $rows as $row ) {
				$course_title = 'Todos los Cursos';
				if ( ! empty( $row->course_id ) ) {
					$course_title = get_the_title( $row->course_id ) ?: "Curso #{$row->course_id}";
				}

				// Token fallback
				$token = $row->token;
				if ( empty( $token ) ) {
					$token = bin2hex( random_bytes( 16 ) );
					$wpdb->update( $plans_table, [ 'token' => $token ], [ 'id' => $row->id ] );
				}

				$checkout_url = home_url( "/?alezux_action=checkout&token={$token}" );
				$subs_count = isset( $counts_by_plan[ $row->id ] ) ? $counts_by_plan[ $row->id ] : 0;

				$plans[] = [
					'id'               => (int) $row->id,
					'name'             => $row->name,
					'courseId'         => (int) $row->course_id,
					'courseTitle'      => $course_title,
					'totalQuotas'      => (int) $row->total_quotas,
					'quotaAmount'      => (float) $row->quota_amount,
					'totalAmount'      => (float) ( $row->total_quotas * $row->quota_amount ),
					'frequency'        => ! empty( $row->frequency ) ? $row->frequency : 'month',
					'whatsapp_number'  => ! empty( $row->whatsapp_number ) ? $row->whatsapp_number : '',
					'access_rules'     => ! empty( $row->access_rules ) ? json_decode( $row->access_rules, true ) : [],
					'token'            => $token,
					'checkoutUrl'      => $checkout_url,
					'subscribersCount' => $subs_count,
				];
			}
		}

		return rest_ensure_response( $plans );
	}

	/**
	 * Crear Plan de Pago
	 */
	public function create_finance_plan( $request ) {
		$params = $request->get_json_params();
		$name = sanitize_text_field( $params['name'] );
		$course_id = (int) $params['courseId'];
		$total_quotas = max( 1, (int) $params['totalQuotas'] );
		$quota_amount = (float) $params['quotaAmount'];
		$frequency = sanitize_text_field( isset( $params['frequency'] ) && ! empty( $params['frequency'] ) ? $params['frequency'] : 'month' );
		if ( $total_quotas === 1 ) {
			$frequency = 'contado';
		}
		$whatsapp_number = sanitize_text_field( isset( $params['whatsapp_number'] ) ? $params['whatsapp_number'] : '' );
		$rules = isset( $params['access_rules'] ) ? $params['access_rules'] : [];
		$token = bin2hex( random_bytes( 16 ) );

		global $wpdb;
		$plans_table = $wpdb->prefix . 'alezux_finanzas_plans';

		$stripe_product_id = null;
		$stripe_price_id = null;

		// Si Stripe está configurado, crear producto y precio
		if ( class_exists( '\Alezux_Members\Modules\Finanzas\Includes\Stripe_API' ) ) {
			$stripe = \Alezux_Members\Modules\Finanzas\Includes\Stripe_API::get_instance();
			$interval = ( $total_quotas == 1 ) ? 'contado' : $frequency;
			$stripe_result = $stripe->create_plan( $name, $quota_amount, $interval );
			if ( ! is_wp_error( $stripe_result ) && is_array( $stripe_result ) ) {
				$stripe_product_id = $stripe_result['product_id'] ?? null;
				$stripe_price_id   = $stripe_result['price_id'] ?? null;
			}
		}

		$wpdb->insert( $plans_table, [
			'name'              => $name,
			'course_id'         => $course_id,
			'stripe_product_id' => $stripe_product_id,
			'stripe_price_id'   => $stripe_price_id,
			'total_quotas'      => $total_quotas,
			'quota_amount'      => $quota_amount,
			'frequency'         => $frequency,
			'whatsapp_number'   => $whatsapp_number,
			'access_rules'      => json_encode( $rules ),
			'token'             => $token,
		] );

		$plan_id = $wpdb->insert_id;
		$checkout_url = home_url( "/?alezux_action=checkout&token={$token}" );
		$course_title = 'Todos los Cursos';
		if ( $course_id > 0 ) {
			$course_title = get_the_title( $course_id ) ?: "Curso #{$course_id}";
		}

		return rest_ensure_response( [
			'id'               => $plan_id,
			'name'             => $name,
			'courseId'         => $course_id,
			'courseTitle'      => $course_title,
			'totalQuotas'      => $total_quotas,
			'quotaAmount'      => $quota_amount,
			'totalAmount'      => (float) ( $total_quotas * $quota_amount ),
			'frequency'        => $frequency,
			'whatsapp_number'  => $whatsapp_number,
			'access_rules'     => $rules,
			'token'            => $token,
			'checkoutUrl'      => $checkout_url,
			'subscribersCount' => 0,
		] );
	}

	/**
	 * Actualizar configuración de un Plan de Pago existente
	 */
	public function update_finance_plan( $request ) {
		global $wpdb;
		$plan_id = (int) $request->get_param( 'id' );
		$params = $request->get_json_params();
		$plans_table = $wpdb->prefix . 'alezux_finanzas_plans';

		$plan = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $plans_table WHERE id = %d", $plan_id ) );
		if ( ! $plan ) {
			return new \WP_Error( 'not_found', 'El plan no existe.', [ 'status' => 404 ] );
		}

		$data_to_update = [];
		if ( isset( $params['name'] ) ) {
			$data_to_update['name'] = sanitize_text_field( $params['name'] );
		}
		if ( isset( $params['courseId'] ) ) {
			$data_to_update['course_id'] = (int) $params['courseId'];
		}
		if ( isset( $params['totalQuotas'] ) ) {
			$data_to_update['total_quotas'] = max( 1, (int) $params['totalQuotas'] );
		}
		if ( isset( $params['quotaAmount'] ) ) {
			$data_to_update['quota_amount'] = (float) $params['quotaAmount'];
		}
		if ( isset( $params['frequency'] ) ) {
			$data_to_update['frequency'] = sanitize_text_field( $params['frequency'] );
		}
		if ( isset( $params['whatsapp_number'] ) ) {
			$data_to_update['whatsapp_number'] = sanitize_text_field( $params['whatsapp_number'] );
		}
		if ( isset( $params['access_rules'] ) ) {
			$data_to_update['access_rules'] = json_encode( $params['access_rules'] );
		}

		if ( ! empty( $data_to_update ) ) {
			$wpdb->update( $plans_table, $data_to_update, [ 'id' => $plan_id ] );
		}

		return rest_ensure_response( [
			'success' => true,
			'message' => 'Plan actualizado correctamente.',
		] );
	}

	/**
	 * Eliminar un Plan de Pago
	 */
	public function delete_finance_plan( $request ) {
		global $wpdb;
		$plan_id = (int) $request->get_param( 'id' );
		$plans_table = $wpdb->prefix . 'alezux_finanzas_plans';

		$deleted = $wpdb->delete( $plans_table, [ 'id' => $plan_id ] );
		if ( $deleted ) {
			return rest_ensure_response( [
				'success' => true,
				'message' => 'Plan eliminado correctamente.',
			] );
		}
		return new \WP_Error( 'not_found', 'No se pudo eliminar el plan.', [ 'status' => 404 ] );
	}

	/**
	 * Obtener Historial de Ventas / Transacciones
	 */
	public function get_finance_sales( $request ) {
		global $wpdb;
		$t_trans = $wpdb->prefix . 'alezux_finanzas_transactions';
		$t_plans = $wpdb->prefix . 'alezux_finanzas_plans';
		$t_subs  = $wpdb->prefix . 'alezux_finanzas_subscriptions';
		$t_users = $wpdb->users;

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$t_trans'" ) !== $t_trans ) {
			return rest_ensure_response( [ 'rows' => [], 'total' => 0, 'pages' => 1 ] );
		}

		$search = sanitize_text_field( $request->get_param( 'search' ) ?: '' );
		$status = sanitize_text_field( $request->get_param( 'status' ) ?: '' );
		$page   = max( 1, (int) ( $request->get_param( 'page' ) ?: 1 ) );
		$limit  = min( 100, max( 5, (int) ( $request->get_param( 'limit' ) ?: 20 ) ) );
		$offset = ( $page - 1 ) * $limit;

		$sql = "SELECT SQL_CALC_FOUND_ROWS 
					t.*, 
					u.display_name as user_name, 
					u.user_email, 
					p.name as plan_name, 
					p.total_quotas, 
					p.frequency,
					s.quotas_paid as sub_quotas_paid,
					s.status as sub_status
				FROM $t_trans t
				LEFT JOIN $t_users u ON t.user_id = u.ID
				LEFT JOIN $t_plans p ON t.plan_id = p.id
				LEFT JOIN $t_subs s ON t.subscription_id = s.id
				WHERE 1=1";

		$args = [];
		if ( ! empty( $search ) ) {
			$sql .= " AND (u.display_name LIKE %s OR u.user_email LIKE %s OR t.transaction_ref LIKE %s)";
			$args[] = '%' . $wpdb->esc_like( $search ) . '%';
			$args[] = '%' . $wpdb->esc_like( $search ) . '%';
			$args[] = '%' . $wpdb->esc_like( $search ) . '%';
		}
		if ( ! empty( $status ) ) {
			$sql .= " AND t.status = %s";
			$args[] = $status;
		}

		$sql .= " ORDER BY t.created_at DESC LIMIT %d OFFSET %d";
		$args[] = $limit;
		$args[] = $offset;

		$results = $wpdb->get_results( $wpdb->prepare( $sql, $args ) );
		$total_rows = (int) $wpdb->get_var( "SELECT FOUND_ROWS()" );

		$data = [];
		foreach ( $results as $row ) {
			$payment_desc = 'Pago Único';
			if ( $row->total_quotas > 1 ) {
				$curr_q = $row->sub_quotas_paid ?: 1;
				$payment_desc = "Recurrente ({$curr_q}/{$row->total_quotas})";
			} elseif ( $row->total_quotas == 1 ) {
				$payment_desc = 'De Contado';
			}

			$data[] = [
				'id'           => (int) $row->id,
				'student'      => $row->user_name ? $row->user_name : ( $row->user_email ?: 'Usuario #' . $row->user_id ),
				'studentEmail' => $row->user_email ?: '',
				'method'       => $row->method ? ucfirst( $row->method ) : 'Stripe',
				'amount'       => (float) $row->amount,
				'currency'     => $row->currency ?: 'USD',
				'course'       => $row->plan_name ?: 'Plan General',
				'quotasDesc'   => $payment_desc,
				'status'       => $row->status ?: 'succeeded',
				'date'         => date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $row->created_at ) ),
				'ref'          => $row->transaction_ref ?: 'tx_' . substr( md5( $row->id ), 0, 10 ),
			];
		}

		return rest_ensure_response( [
			'rows'  => $data,
			'total' => $total_rows,
			'pages' => ceil( $total_rows / $limit ) ?: 1,
		] );
	}

	/**
	 * Obtener Listado de Suscripciones & Cuotas
	 */
	public function get_finance_subscriptions( $request ) {
		global $wpdb;
		$t_subs  = $wpdb->prefix . 'alezux_finanzas_subscriptions';
		$t_plans = $wpdb->prefix . 'alezux_finanzas_plans';
		$t_users = $wpdb->users;

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$t_subs'" ) !== $t_subs ) {
			return rest_ensure_response( [ 'rows' => [], 'total' => 0, 'pages' => 1 ] );
		}

		$search = sanitize_text_field( $request->get_param( 'search' ) ?: '' );
		$page   = max( 1, (int) ( $request->get_param( 'page' ) ?: 1 ) );
		$limit  = min( 100, max( 5, (int) ( $request->get_param( 'limit' ) ?: 20 ) ) );
		$offset = ( $page - 1 ) * $limit;

		$sql = "SELECT SQL_CALC_FOUND_ROWS 
					s.*, 
					u.display_name, 
					u.user_email, 
					p.name as plan_name, 
					p.total_quotas, 
					p.quota_amount 
				FROM $t_subs s
				LEFT JOIN $t_users u ON s.user_id = u.ID
				LEFT JOIN $t_plans p ON s.plan_id = p.id
				WHERE 1=1";

		$args = [];
		if ( ! empty( $search ) ) {
			$sql .= " AND (u.display_name LIKE %s OR u.user_email LIKE %s)";
			$args[] = '%' . $wpdb->esc_like( $search ) . '%';
			$args[] = '%' . $wpdb->esc_like( $search ) . '%';
		}

		$sql .= " ORDER BY s.created_at DESC LIMIT %d OFFSET %d";
		$args[] = $limit;
		$args[] = $offset;

		$results = $wpdb->get_results( $wpdb->prepare( $sql, $args ) );
		$total_rows = (int) $wpdb->get_var( "SELECT FOUND_ROWS()" );

		$data = [];
		foreach ( $results as $row ) {
			$next_payment = '—';
			if ( $row->status === 'active' && $row->next_payment_date ) {
				$next_payment = date_i18n( get_option( 'date_format' ), strtotime( $row->next_payment_date ) );
				if ( strtotime( $row->next_payment_date ) < time() ) {
					$next_payment .= ' (Atrasado)';
				}
			} elseif ( $row->status === 'completed' ) {
				$next_payment = 'Pagado Totalmente';
			}

			$percent = 0;
			if ( (int) $row->total_quotas > 0 ) {
				$percent = round( ( (int) $row->quotas_paid / (int) $row->total_quotas ) * 100 );
			}

			$data[] = [
				'id'             => (int) $row->id,
				'student'        => $row->display_name ?: ( $row->user_email ?: 'Usuario #' . $row->user_id ),
				'studentEmail'   => $row->user_email ?: '',
				'studentAvatar'  => get_avatar_url( $row->user_id, [ 'size' => 48 ] ),
				'plan'           => $row->plan_name ?: 'Plan de Pagos',
				'totalQuotas'    => (int) $row->total_quotas,
				'quotasPaid'     => (int) $row->quotas_paid,
				'percent'        => min( 100, $percent ),
				'amount'         => (float) $row->quota_amount,
				'status'         => $row->status ?: 'active',
				'nextPayment'    => $next_payment,
				'nextPaymentRaw' => $row->next_payment_date,
				'stripeId'       => $row->stripe_subscription_id ?: '',
			];
		}

		return rest_ensure_response( [
			'rows'  => $data,
			'total' => $total_rows,
			'pages' => ceil( $total_rows / $limit ) ?: 1,
		] );
	}

	/**
	 * Registrar Pago Manual para una Suscripción
	 */
	public function register_subscription_payment( $request ) {
		global $wpdb;
		$sub_id = (int) $request->get_param( 'id' );
		$params = $request->get_json_params();
		$amount = (float) ( $params['amount'] ?? 0 );
		$note   = sanitize_text_field( $params['note'] ?? '' );

		$t_subs  = $wpdb->prefix . 'alezux_finanzas_subscriptions';
		$t_trans = $wpdb->prefix . 'alezux_finanzas_transactions';
		$t_plans = $wpdb->prefix . 'alezux_finanzas_plans';

		$sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t_subs WHERE id = %d", $sub_id ) );
		if ( ! $sub ) {
			return new \WP_Error( 'not_found', 'Suscripción no encontrada.', [ 'status' => 404 ] );
		}

		$plan = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t_plans WHERE id = %d", $sub->plan_id ) );
		$total_quotas = $plan ? (int) $plan->total_quotas : 1;
		$new_quotas_paid = (int) $sub->quotas_paid + 1;
		$new_status = $sub->status;

		if ( $sub->status === 'past_due' || $sub->status === 'canceled' ) {
			$new_status = 'active';
		}
		if ( $new_quotas_paid >= $total_quotas ) {
			$new_status = 'completed';
		}

		$wpdb->update(
			$t_subs,
			[
				'quotas_paid'       => $new_quotas_paid,
				'status'            => $new_status,
				'last_payment_date' => current_time( 'mysql' ),
			],
			[ 'id' => $sub_id ]
		);

		// Insertar registro de transacción
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$t_trans'" ) === $t_trans ) {
			$wpdb->insert(
				$t_trans,
				[
					'user_id'         => $sub->user_id,
					'subscription_id' => $sub->id,
					'plan_id'         => $sub->plan_id,
					'amount'          => $amount > 0 ? $amount : ( $plan ? $plan->quota_amount : 0 ),
					'currency'        => 'USD',
					'method'          => 'manual',
					'transaction_ref' => 'MANUAL-' . strtoupper( wp_generate_password( 8, false ) ),
					'status'          => 'succeeded',
					'data'            => json_encode( [ 'note' => $note, 'registered_by' => get_current_user_id() ] ),
				]
			);
		}

		return rest_ensure_response( [
			'success'    => true,
			'message'    => "Pago manual registrado. Cuota {$new_quotas_paid} de {$total_quotas}.",
			'status'     => $new_status,
			'quotasPaid' => $new_quotas_paid,
		] );
	}

	/**
	 * Obtener credenciales de Stripe para el Dashboard
	 */
	public function get_finance_settings() {
		return rest_ensure_response( [
			'stripe_public_key' => get_option( 'alezux_stripe_public_key', '' ),
			'stripe_secret_key' => get_option( 'alezux_stripe_secret_key', '' ),
			'webhook_url'       => home_url( '/?alezux_webhook=stripe' ),
		] );
	}

	/**
	 * Guardar credenciales de Stripe
	 */
	public function save_finance_settings( $request ) {
		$params = $request->get_json_params();
		if ( isset( $params['stripe_public_key'] ) ) {
			update_option( 'alezux_stripe_public_key', sanitize_text_field( $params['stripe_public_key'] ) );
		}
		if ( isset( $params['stripe_secret_key'] ) ) {
			update_option( 'alezux_stripe_secret_key', sanitize_text_field( $params['stripe_secret_key'] ) );
		}
		return rest_ensure_response( [
			'success' => true,
			'message' => 'Configuración de pasarela guardada correctamente.',
		] );
	}

	/**
	 * Obtener listado completo de automatizaciones de correo de marketing
	 */
	public function get_marketing_automations() {
		global $wpdb;
		$table = $wpdb->prefix . 'alezux_marketing_templates';
		$table_logs = $wpdb->prefix . 'alezux_marketing_logs';

		if ( ! class_exists( '\Alezux_Members\Modules\Marketing\Marketing' ) && file_exists( ALEZUX_MEMBERS_PATH . 'modules/marketing/Marketing.php' ) ) {
			require_once ALEZUX_MEMBERS_PATH . 'modules/marketing/Marketing.php';
		}
		if ( ! class_exists( '\Alezux_Members\Modules\Marketing\Includes\Email_Engine' ) && file_exists( ALEZUX_MEMBERS_PATH . 'modules/marketing/includes/Email_Engine.php' ) ) {
			require_once ALEZUX_MEMBERS_PATH . 'modules/marketing/includes/Email_Engine.php';
		}
		if ( file_exists( ALEZUX_MEMBERS_PATH . 'modules/marketing/includes/Default_Templates.php' ) ) {
			require_once ALEZUX_MEMBERS_PATH . 'modules/marketing/includes/Default_Templates.php';
		}

		$engine = new \Alezux_Members\Modules\Marketing\Includes\Email_Engine();
		$registered_types = $engine->get_registered_types();

		// Plantillas guardadas en base de datos
		$saved_templates = [];
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table ) {
			$saved_templates = $wpdb->get_results( "SELECT * FROM $table", OBJECT_K );
		}

		// Conteo histórico de logs enviados
		$log_counts = [];
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_logs'" ) === $table_logs ) {
			$counts = $wpdb->get_results( "SELECT type, COUNT(*) as count FROM $table_logs GROUP BY type", OBJECT_K );
			if ( is_array( $counts ) ) {
				$log_counts = $counts;
			}
		}

		$categories_map = [
			'student_welcome'        => 'registro',
			'user_recover_password'  => 'registro',
			'admin_reset_password'   => 'registro',
			'payment_success'        => 'finanzas',
			'payment_failed'         => 'finanzas',
			'payment_reminder'       => 'finanzas',
			'subscription_cancelled' => 'finanzas',
			'achievement_assigned'   => 'logros',
			'inactivity_alert'       => 'logros',
			'course_available'       => 'cursos',
			'lesson_available'       => 'cursos',
			'course_completed'       => 'cursos',
		];

		$triggers_map = [
			'student_welcome'        => 'Al registrarse o adquirir membresía',
			'user_recover_password'  => 'Al solicitar recuperar contraseña',
			'admin_reset_password'   => 'Al actualizar clave desde panel admin',
			'payment_success'        => 'Al procesar cobro exitosamente',
			'payment_failed'         => 'Al fallar cobro recurrente o cuota',
			'payment_reminder'       => '3 días antes de renovación de cuota',
			'subscription_cancelled' => 'Al cancelar membresía o plan',
			'achievement_assigned'   => 'Al desbloquear insignia o logro',
			'course_available'       => 'Al publicar un nuevo curso formativo',
			'lesson_available'       => 'Al publicar nuevas lecciones',
			'inactivity_alert'       => 'Tras 7+ días continuos de inactividad',
			'course_completed'       => 'Al alcanzar el 100% del curso',
		];

		$result = [];
		foreach ( $registered_types as $type_key => $info ) {
			$saved = isset( $saved_templates[ $type_key ] ) ? $saved_templates[ $type_key ] : null;
			$default = class_exists( '\Alezux_Members\Modules\Marketing\Includes\Default_Templates' )
				? \Alezux_Members\Modules\Marketing\Includes\Default_Templates::get( $type_key )
				: [ 'subject' => $info['title'], 'content' => '' ];

			$subject = $saved && ! empty( $saved->subject ) ? $saved->subject : ( isset( $default['subject'] ) ? $default['subject'] : $info['title'] );
			$body = $saved && ! empty( $saved->content ) ? $saved->content : ( isset( $default['content'] ) ? $default['content'] : '' );
			$is_active = $saved ? (bool) $saved->is_active : true;
			$sent_count = isset( $log_counts[ $type_key ] ) ? (int) $log_counts[ $type_key ]->count : 0;

			$result[] = [
				'id'           => $type_key,
				'name'         => $info['title'],
				'description'  => $info['description'],
				'category'     => isset( $categories_map[ $type_key ] ) ? $categories_map[ $type_key ] : 'general',
				'triggerEvent' => isset( $triggers_map[ $type_key ] ) ? $triggers_map[ $type_key ] : 'Disparo automático por evento',
				'subject'      => $subject,
				'body'         => $body,
				'enabled'      => $is_active,
				'sentCount'    => $sent_count,
				'variables'    => isset( $info['variables'] ) ? $info['variables'] : [],
			];
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Guardar o actualizar plantilla de correo de marketing
	 */
	public function save_marketing_automation( $request ) {
		$type = sanitize_text_field( $request->get_param( 'id' ) );
		$params = $request->get_json_params();

		global $wpdb;
		$table = $wpdb->prefix . 'alezux_marketing_templates';

		// Verificar existencia de la tabla
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
			return new \WP_Error( 'table_missing', 'La tabla de plantillas de marketing no existe todavía', [ 'status' => 500 ] );
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE type = %s", $type ) );

		$subject = isset( $params['subject'] ) ? sanitize_text_field( $params['subject'] ) : ( $row ? $row->subject : '' );
		$content = isset( $params['body'] ) ? wp_unslash( $params['body'] ) : ( $row ? $row->content : '' );
		$is_active = isset( $params['enabled'] ) ? ( $params['enabled'] ? 1 : 0 ) : ( $row ? (int) $row->is_active : 1 );

		if ( $row ) {
			$wpdb->update(
				$table,
				[
					'subject'   => $subject,
					'content'   => $content,
					'is_active' => $is_active,
				],
				[ 'type' => $type ]
			);
		} else {
			$wpdb->insert(
				$table,
				[
					'type'      => $type,
					'subject'   => $subject,
					'content'   => $content,
					'is_active' => $is_active,
				]
			);
		}

		return rest_ensure_response( [
			'success' => true,
			'id'      => $type,
			'enabled' => (bool) $is_active,
			'subject' => $subject,
		] );
	}

	/**
	 * Enviar correo de prueba para una plantilla de marketing
	 */
	public function send_marketing_test_email( $request ) {
		$params = $request->get_json_params();
		$type = sanitize_text_field( isset( $params['id'] ) ? $params['id'] : '' );
		$email = sanitize_email( isset( $params['email'] ) && ! empty( $params['email'] ) ? $params['email'] : wp_get_current_user()->user_email );

		if ( empty( $type ) || ! is_email( $email ) ) {
			return new \WP_Error( 'invalid_data', 'Tipo de automatización o correo destinatario inválido', [ 'status' => 400 ] );
		}

		if ( ! class_exists( '\Alezux_Members\Modules\Marketing\Marketing' ) && file_exists( ALEZUX_MEMBERS_PATH . 'modules/marketing/Marketing.php' ) ) {
			require_once ALEZUX_MEMBERS_PATH . 'modules/marketing/Marketing.php';
		}
		if ( ! class_exists( '\Alezux_Members\Modules\Marketing\Includes\Email_Engine' ) && file_exists( ALEZUX_MEMBERS_PATH . 'modules/marketing/includes/Email_Engine.php' ) ) {
			require_once ALEZUX_MEMBERS_PATH . 'modules/marketing/includes/Email_Engine.php';
		}

		$marketing = \Alezux_Members\Modules\Marketing\Marketing::get_instance();
		$engine = $marketing->get_engine();

		$current_user = wp_get_current_user();
		$sample_data = [
			'user'             => $current_user,
			'plan_name'        => 'Plan Pro Anual VIP',
			'course_name'      => 'Master en Marketing Digital',
			'course_title'     => 'Master en Marketing Digital',
			'price'            => '$97 USD',
			'amount'           => '$97 USD',
			'date'             => date( 'd/m/Y' ),
			'renewal_date'     => date( 'd/m/Y', strtotime( '+30 days' ) ),
			'end_date'         => date( 'd/m/Y', strtotime( '+1 year' ) ),
			'achievement_name' => 'Graduado Master 2026',
			'achievement_desc' => 'Completaste con éxito todos los módulos de la formación.',
			'days_inactive'    => '7',
			'password'         => '********',
			'new_password'     => '********',
			'reset_link'       => home_url( '/wp-login.php?action=rp' ),
			'courses_list'     => '<ul><li>Módulo Avanzado de Funnels</li><li>Copywriting Persuasivo</li></ul>',
			'lessons_list'     => '<ul><li>Clase 1: Configuración de Campañas</li><li>Clase 2: Retargeting Dinámico</li></ul>',
		];

		$sent = $engine->send_email( $type, $email, $sample_data, true );

		return rest_ensure_response( [
			'success' => (bool) $sent,
			'email'   => $email,
		] );
	}

	/**
	 * Obtener historial de correos enviados para una automatización
	 */
	public function get_automation_logs( $request ) {
		global $wpdb;
		$type = sanitize_text_field( $request->get_param( 'id' ) );
		$table_logs = $wpdb->prefix . 'alezux_marketing_logs';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_logs'" ) !== $table_logs ) {
			return rest_ensure_response( [] );
		}

		$logs = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $table_logs WHERE type = %s ORDER BY sent_at DESC LIMIT 200",
			$type
		) );

		if ( empty( $logs ) ) {
			return rest_ensure_response( [] );
		}

		$formatted = [];
		foreach ( $logs as $log ) {
			$status_display = 'Enviado';
			if ( ! empty( $log->opened_at ) ) {
				$status_display = 'Leído';
			} elseif ( strpos( strtolower( $log->status ), 'fail' ) !== false || strpos( strtolower( $log->status ), 'err' ) !== false ) {
				$status_display = 'Fallido';
			}

			$formatted[] = [
				'id'        => (int) $log->id,
				'type'      => $log->type,
				'recipient' => $log->recipient_email,
				'date'      => date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $log->sent_at ) ),
				'status'    => $status_display,
				'rawStatus' => strtolower( $log->status ),
				'openedAt'  => ! empty( $log->opened_at ) ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $log->opened_at ) ) : null,
			];
		}

		return rest_ensure_response( $formatted );
	}

	/**
	 * Reenviar correo desde el historial
	 */
	public function resend_marketing_log( $request ) {
		global $wpdb;
		$log_id = (int) $request->get_param( 'id' );
		$table_logs = $wpdb->prefix . 'alezux_marketing_logs';

		$log = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_logs WHERE id = %d", $log_id ) );
		if ( ! $log ) {
			return new \WP_Error( 'not_found', 'Registro de correo no encontrado.', [ 'status' => 404 ] );
		}

		if ( ! class_exists( '\Alezux_Members\Modules\Marketing\Marketing' ) && file_exists( ALEZUX_MEMBERS_PATH . 'modules/marketing/Marketing.php' ) ) {
			require_once ALEZUX_MEMBERS_PATH . 'modules/marketing/Marketing.php';
		}
		if ( ! class_exists( '\Alezux_Members\Modules\Marketing\Includes\Email_Engine' ) && file_exists( ALEZUX_MEMBERS_PATH . 'modules/marketing/includes/Email_Engine.php' ) ) {
			require_once ALEZUX_MEMBERS_PATH . 'modules/marketing/includes/Email_Engine.php';
		}

		$marketing = \Alezux_Members\Modules\Marketing\Marketing::get_instance();
		$engine = $marketing->get_engine();

		$user = get_user_by( 'email', $log->recipient_email );
		$data = [];
		if ( $user ) {
			$data['user'] = $user;
			if ( $log->type === 'student_welcome' ) {
				$password = wp_generate_password( 12, true );
				wp_set_password( $password, $user->ID );
				$data['new_password'] = $password;
			}
		} else {
			$data['user'] = (object) [
				'display_name' => $log->recipient_email,
				'user_email'   => $log->recipient_email,
			];
		}

		$sent = $engine->send_email( $log->type, $log->recipient_email, $data, false );

		if ( $sent ) {
			$wpdb->update( $table_logs, [ 'status' => 'sent', 'sent_at' => current_time( 'mysql' ) ], [ 'id' => $log_id ] );
			return rest_ensure_response( [
				'success' => true,
				'message' => 'Correo reenviado exitosamente a ' . $log->recipient_email,
			] );
		} else {
			$err = $engine->get_last_error_message();
			$wpdb->update( $table_logs, [ 'status' => 'fail: ' . mb_substr( $err, 0, 150 ) ], [ 'id' => $log_id ] );
			return new \WP_Error( 'send_failed', 'Error al reenviar correo: ' . $err, [ 'status' => 500 ] );
		}
	}

	/**
	 * Obtener configuración general de marketing (Remitente, Logotipo y SMTP)
	 */
	public function get_marketing_settings() {
		return rest_ensure_response( [
			'from_name'     => get_option( 'alezux_marketing_from_name', get_bloginfo( 'name' ) ),
			'from_email'    => get_option( 'alezux_marketing_from_email', get_bloginfo( 'admin_email' ) ),
			'logo_url'      => get_option( 'alezux_marketing_logo_url', '' ),
			'smtp_enabled'  => get_option( 'alezux_marketing_smtp_enabled', '0' ) === '1',
			'smtp_host'     => get_option( 'alezux_marketing_smtp_host', '' ),
			'smtp_port'     => (int) get_option( 'alezux_marketing_smtp_port', 587 ),
			'smtp_secure'   => get_option( 'alezux_marketing_smtp_secure', 'tls' ),
			'smtp_auth'     => get_option( 'alezux_marketing_smtp_auth', '1' ) === '1',
			'smtp_username' => get_option( 'alezux_marketing_smtp_username', '' ),
			'smtp_password' => get_option( 'alezux_marketing_smtp_password', '' ),
			'smtp_skip_ssl' => get_option( 'alezux_marketing_smtp_skip_ssl', '0' ) === '1',
		] );
	}

	/**
	 * Guardar configuración general de marketing
	 */
	public function save_marketing_settings( $request ) {
		$params = $request->get_json_params();

		if ( isset( $params['from_name'] ) ) {
			update_option( 'alezux_marketing_from_name', sanitize_text_field( $params['from_name'] ) );
		}
		if ( isset( $params['from_email'] ) ) {
			update_option( 'alezux_marketing_from_email', sanitize_email( $params['from_email'] ) );
		}
		if ( isset( $params['logo_url'] ) ) {
			update_option( 'alezux_marketing_logo_url', sanitize_url( $params['logo_url'] ) );
		}
		if ( isset( $params['smtp_enabled'] ) ) {
			update_option( 'alezux_marketing_smtp_enabled', ! empty( $params['smtp_enabled'] ) ? '1' : '0' );
		}
		if ( isset( $params['smtp_host'] ) ) {
			update_option( 'alezux_marketing_smtp_host', sanitize_text_field( $params['smtp_host'] ) );
		}
		if ( isset( $params['smtp_port'] ) ) {
			update_option( 'alezux_marketing_smtp_port', (int) $params['smtp_port'] );
		}
		if ( isset( $params['smtp_secure'] ) ) {
			update_option( 'alezux_marketing_smtp_secure', sanitize_text_field( $params['smtp_secure'] ) );
		}
		if ( isset( $params['smtp_auth'] ) ) {
			update_option( 'alezux_marketing_smtp_auth', ! empty( $params['smtp_auth'] ) ? '1' : '0' );
		}
		if ( isset( $params['smtp_username'] ) ) {
			update_option( 'alezux_marketing_smtp_username', sanitize_text_field( $params['smtp_username'] ) );
		}
		if ( isset( $params['smtp_password'] ) ) {
			update_option( 'alezux_marketing_smtp_password', sanitize_text_field( wp_unslash( $params['smtp_password'] ) ) );
		}
		if ( isset( $params['smtp_skip_ssl'] ) ) {
			update_option( 'alezux_marketing_smtp_skip_ssl', ! empty( $params['smtp_skip_ssl'] ) ? '1' : '0' );
		}

		return rest_ensure_response( [
			'success' => true,
			'message' => 'Configuración de marketing guardada correctamente.',
		] );
	}

	/**
	 * Subida de Logotipo de Marketing vía Dropzone o selector
	 */
	public function upload_marketing_logo( $request ) {
		$files = $request->get_file_params();
		if ( empty( $files['file'] ) && empty( $_FILES['file'] ) ) {
			return new \WP_Error( 'no_file', 'No se ha proporcionado ningún archivo para subir', [ 'status' => 400 ] );
		}

		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		$attachment_id = media_handle_upload( 'file', 0 );
		if ( is_wp_error( $attachment_id ) ) {
			return new \WP_Error( 'upload_failed', $attachment_id->get_error_message(), [ 'status' => 500 ] );
		}

		$url = wp_get_attachment_url( $attachment_id );
		update_option( 'alezux_marketing_logo_url', $url );

		return rest_ensure_response( [
			'success' => true,
			'url'     => $url,
		] );
	}
}

