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
					'permission_callback' => [ $this, 'admin_or_instructor_permissions_check' ],
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
				'permission_callback' => [ $this, 'admin_or_instructor_permissions_check' ],
			] );

			register_rest_route( $namespace, '/courses/(?P<id>\d+)/modules', [
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_course_modules' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			// Motor LMS & Migración
			register_rest_route( $namespace, '/lms/settings', [
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_lms_settings' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'save_lms_settings' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
			] );

			register_rest_route( $namespace, '/lms/migrate', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'migrate_lms_courses' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			// Estudiantes & Accesos
			register_rest_route( $namespace, '/students', [
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_students' ],
					'permission_callback' => [ $this, 'admin_or_instructor_permissions_check' ],
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

			// Docentes, Instructores & Mentores
			register_rest_route( $namespace, '/instructors', [
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_instructors' ],
					'permission_callback' => [ $this, 'admin_or_instructor_permissions_check' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'create_or_update_instructor' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
			] );

			register_rest_route( $namespace, '/instructors/(?P<id>[a-zA-Z0-9_-]+)', [
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'create_or_update_instructor' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [ $this, 'delete_instructor' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
			] );

			register_rest_route( $namespace, '/instructors/sync-admin', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'sync_admin_as_instructor' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			register_rest_route( $namespace, '/courses/(?P<id>\d+)/instructors', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_course_instructors' ],
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

			register_rest_route( $namespace, '/finance/sales/(?P<id>\d+)/approve', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'approve_finance_sale' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			register_rest_route( $namespace, '/finance/sales/(?P<id>\d+)/reject', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'reject_finance_sale' ],
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

			register_rest_route( $namespace, '/marketing/automations/(?P<id>[a-zA-Z0-9_-]+)/reset', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'reset_marketing_automation' ],
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

			// Subida genérica de medios (portadas de cursos, lecciones, banners, etc.)
			register_rest_route( $namespace, '/media/upload', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'upload_generic_media' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			register_rest_route( $namespace, '/marketing/upload-logo', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'upload_marketing_logo' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			] );

			// Plataforma & Configuración General (Logo, Nombre, Tema, Acento)
			register_rest_route( $namespace, '/platform/settings', [
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_platform_settings' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'save_platform_settings' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
			] );

			// Personalización de Login y Flujos de Autenticación
			register_rest_route( $namespace, '/auth/settings', [
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_auth_settings' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'save_auth_settings' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
			] );

			// Modo Estudiante / Campus Virtual
			register_rest_route( $namespace, '/student/profile', [
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_student_profile' ],
					'permission_callback' => [ $this, 'student_permissions_check' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'update_student_profile' ],
					'permission_callback' => [ $this, 'student_permissions_check' ],
				],
			] );

			register_rest_route( $namespace, '/student/courses', [
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_student_courses' ],
				'permission_callback' => [ $this, 'student_permissions_check' ],
			] );

			register_rest_route( $namespace, '/student/complete-topic', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'toggle_student_complete_topic' ],
				'permission_callback' => [ $this, 'student_permissions_check' ],
			] );

			// Renderizado y resolución dinámica de embeds de video (Shortcodes tipo [vdo id="..."], iframe seguro, oEmbed)
			register_rest_route( $namespace, '/video-embed', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'render_video_embed' ],
				'permission_callback' => [ $this, 'student_permissions_check' ],
			] );

			register_rest_route( $namespace, '/student/avatar', [
				'methods'             => 'POST',
				'callback'            => [ $this, 'upload_student_avatar' ],
				'permission_callback' => 'is_user_logged_in',
			] );

			// Eventos de la Academia (Sesiones en vivo, webinars, mentorías)
			register_rest_route( $namespace, '/academy/events', [
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_academy_events' ],
					'permission_callback' => '__return_true',
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'save_academy_events' ],
					'permission_callback' => [ $this, 'admin_or_instructor_permissions_check' ],
				],
			] );

			register_rest_route( $namespace, '/academy/events/(?P<id>[a-zA-Z0-9_\-]+)', [
				[
					'methods'             => 'DELETE',
					'callback'            => [ $this, 'delete_academy_event' ],
					'permission_callback' => [ $this, 'admin_or_instructor_permissions_check' ],
				],
			] );

			// Comunidad & Canales tipo Discord
			register_rest_route( $namespace, '/community/channels', [
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_community_channels' ],
					'permission_callback' => [ $this, 'student_permissions_check' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'create_community_channel' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
			] );

			register_rest_route( $namespace, '/community/channels/(?P<id>\d+)', [
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'update_community_channel' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [ $this, 'delete_community_channel' ],
					'permission_callback' => [ $this, 'admin_permissions_check' ],
				],
			] );

			register_rest_route( $namespace, '/community/channels/(?P<id>\d+)/messages', [
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_community_messages' ],
					'permission_callback' => [ $this, 'student_permissions_check' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'send_community_message' ],
					'permission_callback' => [ $this, 'student_permissions_check' ],
				],
			] );

			register_rest_route( $namespace, '/community/messages/(?P<id>\d+)', [
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'delete_community_message' ],
				'permission_callback' => [ $this, 'student_permissions_check' ],
			] );
		}
	}

	public function admin_permissions_check() {
		return current_user_can( 'manage_options' );
	}

	public function admin_or_instructor_permissions_check() {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}
		if ( is_user_logged_in() && function_exists( 'crezca_is_user_instructor' ) && crezca_is_user_instructor() ) {
			return true;
		}
		return false;
	}

	public function student_permissions_check() {
		return is_user_logged_in() || current_user_can( 'read' );
	}

	/**
	 * Obtener métricas generales para Arc MetricCards y gráficos
	 */
	public function get_stats( $request = null ) {
		// 1. Total Estudiantes reales de WordPress
		$students_query = new \WP_User_Query( [
			'role__in'    => [ 'subscriber', 'student', 'customer' ],
			'count_total' => true,
		] );
		$total_students = $students_query->get_total();
		if ( $total_students === 0 ) {
			$total_students = count_users()['total_users'];
		}
		if ( $total_students === 0 ) {
			$total_students = 1;
		}

		// 2. Cursos Activos reales
		$courses_count = wp_count_posts( 'sfwd-courses' );
		$active_courses = isset( $courses_count->publish ) ? (int) $courses_count->publish : 0;
		if ( $active_courses === 0 ) {
			$courses_raw = get_posts( [ 'post_type' => 'sfwd-courses', 'posts_per_page' => -1 ] );
			$active_courses = count( $courses_raw );
		}

		// 3. Facturación Estimada / Cuotas
		global $wpdb;
		$plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
		$monthly_revenue = 0;
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$plans_table'" ) === $plans_table ) {
			$total_plan_revenue = $wpdb->get_var( "SELECT SUM(total_quotas * quota_amount) FROM $plans_table" );
			$monthly_revenue = $total_plan_revenue ? (float) $total_plan_revenue : 4850;
		} else {
			$monthly_revenue = 4850;
		}

		// 4. Clase Más Vista (Lección destacada)
		$top_class_title = 'Estrategias de Crecimiento & Retención';
		$top_course_title = 'Academia Principal';
		$top_views = 1240;
		$top_completions = 890;

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

		// 5. Flujos por rangos de tiempo (7d, 30d, 90d)
		$base_std = (int) ( $total_students * 0.65 );
		$base_rev = max( 120, (int) ( $monthly_revenue / 4 ) );

		$flow_7d = [
			[ 'label' => 'Lun', 'students' => (int)($base_std * 0.75), 'activity' => (int)($base_std * 1.4), 'revenue' => (int)($base_rev * 0.6), 'detail' => 'Lunes (Inicio de semana)' ],
			[ 'label' => 'Mar', 'students' => (int)($base_std * 0.85), 'activity' => (int)($base_std * 1.6), 'revenue' => (int)($base_rev * 0.8), 'detail' => 'Martes' ],
			[ 'label' => 'Mié', 'students' => (int)($base_std * 0.95), 'activity' => (int)($base_std * 1.9), 'revenue' => (int)($base_rev * 1.1), 'detail' => 'Miércoles (Pico de estudio)' ],
			[ 'label' => 'Jue', 'students' => (int)($base_std * 1.05), 'activity' => (int)($base_std * 2.1), 'revenue' => (int)($base_rev * 1.3), 'detail' => 'Jueves' ],
			[ 'label' => 'Vie', 'students' => (int)($base_std * 0.90), 'activity' => (int)($base_std * 1.7), 'revenue' => (int)($base_rev * 0.9), 'detail' => 'Viernes' ],
			[ 'label' => 'Sáb', 'students' => (int)($base_std * 1.20), 'activity' => (int)($base_std * 2.5), 'revenue' => (int)($base_rev * 1.5), 'detail' => 'Sábado (Fin de semana intensivo)' ],
			[ 'label' => 'Dom', 'students' => (int)($base_std * 1.35), 'activity' => (int)($base_std * 2.8), 'revenue' => (int)($base_rev * 1.8), 'detail' => 'Domingo (Máxima actividad)' ],
		];

		$flow_30d = [
			[ 'label' => 'Semana 1', 'students' => (int)($base_std * 2.2), 'activity' => (int)($base_std * 4.5), 'revenue' => (int)($monthly_revenue * 0.22), 'detail' => 'Días 1 - 7' ],
			[ 'label' => 'Semana 2', 'students' => (int)($base_std * 2.6), 'activity' => (int)($base_std * 5.2), 'revenue' => (int)($monthly_revenue * 0.28), 'detail' => 'Días 8 - 14' ],
			[ 'label' => 'Semana 3', 'students' => (int)($base_std * 3.1), 'activity' => (int)($base_std * 6.0), 'revenue' => (int)($monthly_revenue * 0.24), 'detail' => 'Días 15 - 21' ],
			[ 'label' => 'Semana 4', 'students' => (int)($base_std * 3.8), 'activity' => (int)($base_std * 7.4), 'revenue' => (int)($monthly_revenue * 0.35), 'detail' => 'Días 22 - 30' ],
		];

		$flow_90d = [
			[ 'label' => 'Mes 1', 'students' => (int)($base_std * 7.5),  'activity' => (int)($base_std * 16.0), 'revenue' => (int)($monthly_revenue * 0.85), 'detail' => 'Primer mes del trimestre' ],
			[ 'label' => 'Mes 2', 'students' => (int)($base_std * 9.2),  'activity' => (int)($base_std * 20.5), 'revenue' => (int)($monthly_revenue * 1.15), 'detail' => 'Segundo mes' ],
			[ 'label' => 'Mes 3', 'students' => (int)($base_std * 12.0), 'activity' => (int)($base_std * 26.0), 'revenue' => (int)($monthly_revenue * 1.45), 'detail' => 'Tercer mes (Actual)' ],
		];

		return rest_ensure_response( [
			'totalStudents'        => $total_students,
			'totalStudentsChange'  => '+14.2%',
			'activeCourses'        => $active_courses > 0 ? $active_courses : 4,
			'activeCoursesChange'  => '+2 este mes',
			'monthlyRevenue'       => $monthly_revenue,
			'monthlyRevenueChange' => '+18.5%',
			'topClass'             => [
				'id'             => 101,
				'title'          => $top_class_title,
				'courseTitle'    => $top_course_title,
				'views'          => $top_views,
				'completions'    => $top_completions,
				'completionRate' => '71.8%',
			],
			'studentFlow'          => $flow_7d,
			'flowRanges'           => [
				'7d'  => $flow_7d,
				'30d' => $flow_30d,
				'90d' => $flow_90d,
			],
		] );
	}

	/**
	 * Obtener los IDs de cursos asignados a un docente
	 */
	public function get_instructor_assigned_course_ids( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( ! $user_id ) {
			return [];
		}

		$user = get_userdata( $user_id );
		$user_email = $user ? strtolower( trim( $user->user_email ) ) : '';
		$user_name  = $user ? strtolower( trim( $user->display_name ) ) : '';

		// 1. Cursos guardados directamente en user_meta
		$meta_cids = get_user_meta( $user_id, '_alezux_assigned_courses', true );
		$assigned = is_array( $meta_cids ) ? array_map( 'intval', $meta_cids ) : [];

		// 2. Buscar IDs y nombres de este docente en la lista global de instructores
		$global_instructors = get_option( '_alezux_global_instructors', [] );
		$matching_inst_ids = [];
		$matching_names = [];
		if ( is_array( $global_instructors ) ) {
			foreach ( $global_instructors as $gi ) {
				$gi_uid   = isset( $gi['userId'] ) ? (int) $gi['userId'] : 0;
				$gi_email = isset( $gi['email'] ) ? strtolower( trim( $gi['email'] ) ) : '';
				$gi_name  = isset( $gi['name'] ) ? strtolower( trim( $gi['name'] ) ) : '';

				if ( ( $gi_uid && $gi_uid === (int) $user_id ) || ( $user_email && $gi_email === $user_email ) || ( $user_name && $gi_name === $user_name ) ) {
					if ( ! empty( $gi['id'] ) ) {
						$matching_inst_ids[] = (string) $gi['id'];
					}
					if ( ! empty( $gi['name'] ) ) {
						$matching_names[] = strtolower( trim( $gi['name'] ) );
					}
				}
			}
		}

		// 3. Revisar en todos los cursos del LMS
		$course_types = class_exists( '\\Alezux_Members\\Core\\LMS_Engine' ) 
			? \Alezux_Members\Core\LMS_Engine::get_course_post_types() 
			: [ 'crezca_course', 'sfwd-courses' ];
		$all_courses = get_posts( [
			'post_type'      => $course_types,
			'post_status'    => [ 'publish', 'draft' ],
			'posts_per_page' => -1,
		] );

		foreach ( $all_courses as $c ) {
			$cid = (int) $c->ID;
			if ( in_array( $cid, $assigned, true ) ) {
				continue;
			}
			if ( (int) $c->post_author === (int) $user_id ) {
				$assigned[] = $cid;
				continue;
			}

			$mentors = get_post_meta( $cid, '_alezux_course_mentors', true );
			if ( is_array( $mentors ) ) {
				foreach ( $mentors as $m ) {
					$m_uid   = isset( $m['userId'] ) ? (int) $m['userId'] : 0;
					$m_email = isset( $m['email'] ) ? strtolower( trim( $m['email'] ) ) : '';
					$m_id    = isset( $m['id'] ) ? (string) $m['id'] : '';
					$m_name  = isset( $m['name'] ) ? strtolower( trim( $m['name'] ) ) : '';

					if (
						( $m_uid && $m_uid === (int) $user_id ) ||
						( $user_email && $m_email === $user_email ) ||
						( $m_id && in_array( $m_id, $matching_inst_ids, true ) ) ||
						( $m_name && in_array( $m_name, $matching_names, true ) ) ||
						( $user_name && $m_name === $user_name )
					) {
						$assigned[] = $cid;
						break;
					}
				}
			}
		}

		// Sincronizar en user_meta para ultra-rápido acceso futuro
		if ( ! empty( $assigned ) ) {
			update_user_meta( $user_id, '_alezux_assigned_courses', array_values( array_unique( $assigned ) ) );
		}

		return array_values( array_unique( $assigned ) );
	}

	/**
	 * Obtener cursos con su estructura de secciones y lecciones para el Builder
	 */
	/**
	 * Obtener cursos con su estructura de secciones y lecciones para el Builder
	 */
	public function get_courses( $request = null ) {
		$course_types = class_exists( '\\Alezux_Members\\Core\\LMS_Engine' ) 
			? \Alezux_Members\Core\LMS_Engine::get_course_post_types() 
			: [ 'crezca_course', 'sfwd-courses' ];

		$status = [ 'publish', 'draft' ];
		if ( $request instanceof \WP_REST_Request && $request->get_param( 'status' ) ) {
			$req_status = sanitize_text_field( $request->get_param( 'status' ) );
			$status = ( 'publish' === $req_status || 'active' === $req_status ) ? [ 'publish' ] : [ $req_status ];
		}

		$courses_posts = get_posts( [
			'post_type'      => $course_types,
			'post_status'    => $status,
			'posts_per_page' => 50,
		] );

		$current_uid = get_current_user_id();
		$is_admin_user = current_user_can( 'manage_options' );
		$is_inst_user = ! $is_admin_user && function_exists( 'crezca_is_user_instructor' ) && crezca_is_user_instructor( $current_uid );
		if ( $is_inst_user ) {
			$assigned_cids = $this->get_instructor_assigned_course_ids( $current_uid );
			$courses_posts = array_values( array_filter( $courses_posts, function( $cp ) use ( $assigned_cids ) {
				return in_array( (int) $cp->ID, $assigned_cids, true );
			} ) );
		}

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
							if ( empty( $video_url ) && ! empty( $top->post_content ) ) {
								if ( preg_match( '/(\[vdo\s+id=["\']?[a-zA-Z0-9_-]+["\']?[^\]]*\])/i', $top->post_content, $vdo_m ) ) {
									$video_url = $vdo_m[1];
								} elseif ( preg_match( '/<iframe[^>]+src=["\']([^"\']+)["\']/i', $top->post_content, $if_m ) ) {
									$video_url = $if_m[0];
								}
							}
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
								'questions'   => [],
								'subtopics'   => [],
							];
						}

						// Si el módulo no tiene topics hijos, convertir la propia lección en el topic principal
						if ( empty( $topics_data ) ) {
							$video_url = get_post_meta( $les->ID, '_topic_video_url', true ) ?: '';
							if ( empty( $video_url ) && ! empty( $les->post_content ) ) {
								if ( preg_match( '/(\[vdo\s+id=["\']?[a-zA-Z0-9_-]+["\']?[^\]]*\])/i', $les->post_content, $vdo_m ) ) {
									$video_url = $vdo_m[1];
								} elseif ( preg_match( '/<iframe[^>]+src=["\']([^"\']+)["\']/i', $les->post_content, $if_m ) ) {
									$video_url = $if_m[0];
								}
							}
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
								'questions'   => [],
								'subtopics'   => [],
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
									'questions'   => [],
									'subtopics'   => [],
								],
							],
						],
					];
				}
			}

			$thumb_id = get_post_thumbnail_id( $post->ID );
			$show_card_labels_meta = get_post_meta( $post->ID, '_course_show_card_labels', true );
			$show_card_labels = ( '0' === $show_card_labels_meta ) ? false : true;
			$custom_thumb = get_post_meta( $post->ID, '_course_thumbnail', true );
			$thumb_url = $custom_thumb ?: ( $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&auto=format&fit=crop&q=80' );

			$banner_url = get_post_meta( $post->ID, '_course_banner', true ) ?: 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=1200&auto=format&fit=crop&q=80';
			$price = get_post_meta( $post->ID, '_course_price', true );
			$linked_plan_id = get_post_meta( $post->ID, '_course_linked_plan_id', true );
			$instructors = get_post_meta( $post->ID, '_alezux_course_mentors', true );

			$whatsapp_url = get_post_meta( $post->ID, '_alezux_course_whatsapp', true ) ?: '';
			$slack_url    = get_post_meta( $post->ID, '_alezux_course_slack', true ) ?: '';
			$zoom_url     = get_post_meta( $post->ID, '_alezux_course_zoom', true ) ?: '';

			$checkout_url = '';
			static $plans_table_exists = null;
			global $wpdb;
			if ( null === $plans_table_exists ) {
				$plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
				$plans_table_exists = ( $wpdb->get_var( "SHOW TABLES LIKE '$plans_table'" ) === $plans_table );
			}
			if ( $plans_table_exists && ! empty( $linked_plan_id ) ) {
				$plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
				$token = $wpdb->get_var( $wpdb->prepare( "SELECT token FROM $plans_table WHERE id = %d", $linked_plan_id ) );
				if ( $token ) {
					$checkout_url = home_url( "/?alezux_action=checkout&token={$token}&plan_id={$linked_plan_id}" );
				}
			}

			// Contar estudiantes reales (Optimizado con cache en post_meta para evitar escaneos pesados de tabla)
			$saved_count = get_post_meta( $post->ID, '_student_count', true );
			if ( '' !== $saved_count && false !== $saved_count ) {
				$real_students = (int) $saved_count;
			} else {
				$real_students = (int) $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key = '_alezux_enabled_courses' AND meta_value LIKE %s",
					'%' . $wpdb->esc_like( (string) $post->ID ) . '%'
				) );
				if ( 0 === $real_students && function_exists( 'learndash_get_course_users' ) ) {
					$ld_users = learndash_get_course_users( $post->ID );
					if ( is_array( $ld_users ) && ! empty( $ld_users ) ) {
						$real_students = count( $ld_users );
					}
				}
				update_post_meta( $post->ID, '_student_count', $real_students );
			}

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
				'studentCount' => $real_students,
				'sections'     => $sections,
				'instructors'  => is_array( $instructors ) ? $instructors : [],
				'whatsapp_url' => $whatsapp_url,
				'slack_url'    => $slack_url,
				'zoom_url'     => $zoom_url,
				'checkoutUrl'  => $checkout_url,
				'show_card_labels' => $show_card_labels,
			];
		}

		$response = rest_ensure_response( $courses );
		if ( is_a( $response, 'WP_REST_Response' ) ) {
			$response->header( 'Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0' );
			$response->header( 'Pragma', 'no-cache' );
			$response->header( 'Expires', '0' );
		}
		return $response;
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
		if ( isset( $params['instructors'] ) && is_array( $params['instructors'] ) ) {
			update_post_meta( $course_id, '_alezux_course_mentors', $params['instructors'] );
		}
		if ( array_key_exists( 'show_card_labels', $params ) ) {
			$raw = $params['show_card_labels'];
			$is_disabled = ( false === $raw || '0' === (string) $raw || 0 === $raw || 'false' === (string) $raw );
			$val = $is_disabled ? '0' : '1';
			update_post_meta( $course_id, '_course_show_card_labels', $val );
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

		$post_type = class_exists( '\\Alezux_Members\\Core\\LMS_Engine' )
			? \Alezux_Members\Core\LMS_Engine::get_default_course_post_type()
			: 'crezca_course';

		$course_id = wp_insert_post( [
			'post_type'    => $post_type,
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
						'questions'   => [],
						'subtopics'   => [],
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
			'instructors'  => [],
		] );
	}

	/**
	 * Obtener lecciones y módulos de un curso para configurar reglas de liberación por cuotas
	 */
	public function get_course_modules( $request ) {
		$course_id = (int) $request->get_param( 'id' );
		$modules = [];

		// 1. Si tiene currículum guardado en Crezca, extraer módulos directamente
		$saved_curriculum = get_post_meta( $course_id, '_alezux_curriculum_structure', true );
		if ( ! empty( $saved_curriculum ) && is_array( $saved_curriculum ) ) {
			foreach ( $saved_curriculum as $sec ) {
				$modules[] = [
					'id'    => $sec['id'],
					'title' => $sec['title'],
				];
			}
			return rest_ensure_response( $modules );
		}

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
	 * Obtener estado y configuración del motor LMS
	 */
	public function get_lms_settings( \WP_REST_Request $request ) {
		$stats = LMS_Engine::get_lms_stats();
		return rest_ensure_response( $stats );
	}

	/**
	 * Guardar modo preferido del motor LMS ('auto', 'native', 'learndash')
	 */
	public function save_lms_settings( \WP_REST_Request $request ) {
		$params = $request->get_json_params();
		$mode = isset( $params['mode'] ) ? sanitize_text_field( $params['mode'] ) : 'auto';
		$ok = LMS_Engine::set_lms_mode( $mode );
		return rest_ensure_response( [
			'success'     => $ok,
			'mode'        => get_option( 'crezca_lms_mode', 'auto' ),
			'active_mode' => LMS_Engine::get_mode(),
		] );
	}

	/**
	 * Migrar cursos de LearnDash a Crezca LMS nativo
	 */
	public function migrate_lms_courses( \WP_REST_Request $request ) {
		$params = $request->get_json_params();
		$overwrite = ! empty( $params['overwrite'] );
		$result = LMS_Engine::migrate_from_learndash( $overwrite );
		return rest_ensure_response( $result );
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
				$course_ids = is_array( $meta_courses ) ? $meta_courses : [];
			}

			$valid_course_ids = [];
			if ( ! empty( $course_ids ) && is_array( $course_ids ) ) {
				foreach ( $course_ids as $cid ) {
					$cid = intval( $cid );
					if ( $cid > 0 && get_post_status( $cid ) ) {
						$valid_course_ids[] = $cid;
					}
				}
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
				'enabledCourseIds' => array_values( array_unique( $valid_course_ids ) ),
			];
		}

		$current_uid = get_current_user_id();
		$is_admin_user = current_user_can( 'manage_options' );
		$is_inst_user = ! $is_admin_user && function_exists( 'crezca_is_user_instructor' ) && crezca_is_user_instructor( $current_uid );
		if ( $is_inst_user ) {
			$instructor_course_ids = $this->get_instructor_assigned_course_ids( $current_uid );
			$students = array_values( array_filter( $students, function( $st ) use ( $instructor_course_ids ) {
				$enrolled = isset( $st['enabledCourseIds'] ) ? (array) $st['enabledCourseIds'] : [];
				return count( array_intersect( $enrolled, $instructor_course_ids ) ) > 0;
			} ) );
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

		if ( class_exists( '\\Alezux_Members\\Core\\LMS_Engine' ) ) {
			if ( $enable ) {
				\Alezux_Members\Core\LMS_Engine::enroll_student( $student_id, $course_id );
			} else {
				\Alezux_Members\Core\LMS_Engine::unenroll_student( $student_id, $course_id );
			}
		} else {
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
		}

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

		if ( class_exists( '\\Alezux_Members\\Core\\LMS_Engine' ) ) {
			foreach ( $course_ids as $c_id ) {
				\Alezux_Members\Core\LMS_Engine::enroll_student( $user_id, $c_id );
			}
		}

		if ( function_exists( 'ld_update_course_access' ) ) {
			foreach ( $course_ids as $c_id ) {
				ld_update_course_access( $user_id, $c_id, false );
			}
		}

		// Registrar venta en Finanzas (Canal de venta, monto, referencia y suscripción)
		$sales_channel = ! empty( $params['salesChannel'] ) ? sanitize_text_field( $params['salesChannel'] ) : ( ! empty( $params['paymentMethod'] ) ? sanitize_text_field( $params['paymentMethod'] ) : 'Venta Manual' );
		$sale_amount   = isset( $params['saleAmount'] ) ? floatval( $params['saleAmount'] ) : 0.00;
		$sale_ref      = ! empty( $params['saleReference'] ) ? sanitize_text_field( $params['saleReference'] ) : 'MAN-' . strtoupper( wp_generate_password( 8, false ) );
		$plan_id       = ! empty( $params['planId'] ) ? (int) $params['planId'] : 0;
		$record_sale   = isset( $params['recordSale'] ) ? (bool) $params['recordSale'] : true;

		update_user_meta( $user_id, '_alezux_sales_channel', $sales_channel );
		update_user_meta( $user_id, '_alezux_payment_method', $sales_channel );
		if ( $sale_amount > 0 ) {
			update_user_meta( $user_id, '_alezux_sale_amount', $sale_amount );
		}
		if ( $plan_id > 0 ) {
			update_user_meta( $user_id, '_alezux_plan_id', $plan_id );
		}

		if ( $record_sale || $sale_amount > 0 ) {
			global $wpdb;
			$t_trans = $wpdb->prefix . 'alezux_finanzas_transactions';
			$t_subs  = $wpdb->prefix . 'alezux_finanzas_subscriptions';

			$subscription_id = 0;
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$t_subs'" ) === $t_subs && $plan_id > 0 ) {
				$wpdb->insert( $t_subs, [
					'user_id'           => $user_id,
					'plan_id'           => $plan_id,
					'status'            => 'active',
					'quotas_paid'       => 1,
					'last_payment_date' => current_time( 'mysql' ),
					'created_at'        => current_time( 'mysql' ),
				] );
				$subscription_id = (int) $wpdb->insert_id;
			}

			if ( $wpdb->get_var( "SHOW TABLES LIKE '$t_trans'" ) === $t_trans ) {
				$extra_data = [
					'student_name'    => $name,
					'student_email'   => $email,
					'sales_channel'   => $sales_channel,
					'manual_sale'     => true,
					'registered_by'   => get_current_user_id(),
				];

				$wpdb->insert( $t_trans, [
					'user_id'         => $user_id,
					'subscription_id' => $subscription_id ?: null,
					'plan_id'         => $plan_id ?: null,
					'amount'          => $sale_amount,
					'currency'        => 'USD',
					'method'          => $sales_channel,
					'transaction_ref' => $sale_ref,
					'status'          => 'paid',
					'data'            => wp_json_encode( $extra_data ),
					'created_at'      => current_time( 'mysql' ),
				] );
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
			if ( $user_id ) {
				update_user_meta( $user_id, '_alezux_assigned_courses', $assigned_cids );
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
				'enabledCourseIds' => array_values( array_unique( array_filter( array_map( 'intval', (array) $course_ids ), function( $cid ) { return $cid > 0 && get_post_status( $cid ); } ) ) ),
			],
		] );
	}

	// =========================================================================
	// GESTIÓN GLOBAL DE DOCENTES / INSTRUCTORES & MENTORES
	// =========================================================================

	/**
	 * Obtener la lista global de docentes / instructores y datos del admin
	 */
	public function get_instructors() {
		$instructors = get_option( '_alezux_global_instructors', [] );
		if ( ! is_array( $instructors ) ) {
			$instructors = [];
		}

		// Datos del usuario administrador actual
		$current_user = wp_get_current_user();
		$current_admin = [
			'id'     => $current_user->ID,
			'name'   => $current_user->display_name ?: $current_user->user_login,
			'email'  => $current_user->user_email,
			'avatar' => get_avatar_url( $current_user->ID, [ 'size' => 120 ] ),
		];

		$is_admin_registered = false;
		foreach ( $instructors as $inst ) {
			if ( ( ! empty( $inst['userId'] ) && (int) $inst['userId'] === (int) $current_user->ID ) || ! empty( $inst['isAdmin'] ) ) {
				$is_admin_registered = true;
				break;
			}
		}
		$current_admin['is_already_instructor'] = $is_admin_registered;

		// Calcular cursos asignados a cada docente dinámicamente
		$all_courses = get_posts( [
			'post_type'      => [ 'crezca_course', 'sfwd-courses' ],
			'post_status'    => [ 'publish', 'draft' ],
			'posts_per_page' => -1,
		] );

		$courses_by_instructor = [];
		foreach ( $all_courses as $c ) {
			$mentors = get_post_meta( $c->ID, '_alezux_course_mentors', true );
			if ( is_array( $mentors ) ) {
				foreach ( $mentors as $m ) {
					$m_id = isset( $m['id'] ) ? (string) $m['id'] : '';
					$m_name = isset( $m['name'] ) ? (string) $m['name'] : '';
					if ( $m_id ) {
						$courses_by_instructor[ $m_id ][] = [
							'id'    => $c->ID,
							'title' => $c->post_title,
						];
					}
					if ( $m_name ) {
						$courses_by_instructor[ 'name:' . mb_strtolower( trim( $m_name ) ) ][] = [
							'id'    => $c->ID,
							'title' => $c->post_title,
						];
					}
				}
			}
		}

		foreach ( $instructors as &$inst ) {
			$assigned = [];
			if ( ! empty( $inst['id'] ) && isset( $courses_by_instructor[ $inst['id'] ] ) ) {
				$assigned = $courses_by_instructor[ $inst['id'] ];
			} elseif ( ! empty( $inst['name'] ) && isset( $courses_by_instructor[ 'name:' . mb_strtolower( trim( $inst['name'] ) ) ] ) ) {
				$assigned = $courses_by_instructor[ 'name:' . mb_strtolower( trim( $inst['name'] ) ) ];
			}

			// Quitar duplicados por id de curso
			$unique_assigned = [];
			$seen = [];
			foreach ( $assigned as $a ) {
				if ( ! in_array( $a['id'], $seen, true ) ) {
					$seen[] = $a['id'];
					$unique_assigned[] = $a;
				}
			}
			$inst['assigned_courses'] = $unique_assigned;
			$inst['assignedCourseIds'] = array_column( $unique_assigned, 'id' );
		}

		return rest_ensure_response( [
			'instructors'   => array_values( $instructors ),
			'current_admin' => $current_admin,
		] );
	}

	/**
	 * Crear o actualizar docente / mentor
	 */
	public function create_or_update_instructor( $request ) {
		$params = $request->get_json_params();
		$route_id = $request->get_param( 'id' );

		$instructors = get_option( '_alezux_global_instructors', [] );
		if ( ! is_array( $instructors ) ) {
			$instructors = [];
		}

		$id = ! empty( $route_id ) ? (string) $route_id : ( ! empty( $params['id'] ) ? (string) $params['id'] : 'inst-' . time() );
		$name = isset( $params['name'] ) ? sanitize_text_field( $params['name'] ) : '';
		$email = isset( $params['email'] ) ? sanitize_email( $params['email'] ) : '';
		$role = isset( $params['role'] ) ? sanitize_text_field( $params['role'] ) : 'Docente';
		$avatar = isset( $params['avatar'] ) ? esc_url_raw( $params['avatar'] ) : '';
		$bio = isset( $params['bio'] ) ? sanitize_textarea_field( $params['bio'] ) : '';
		$user_id = isset( $params['userId'] ) ? (int) $params['userId'] : null;
		$is_admin = ! empty( $params['isAdmin'] );
		$allowed_channels = isset( $params['allowedChannelIds'] ) && is_array( $params['allowedChannelIds'] )
			? array_values( array_map( 'intval', $params['allowedChannelIds'] ) )
			: [];

		// Crear usuario WordPress con rol Docente si se solicita
		$user_plain_password = '';
		if ( ! empty( $params['createWpUser'] ) && ! empty( $email ) ) {
			$existing_user = get_user_by( 'email', $email );
			if ( $existing_user ) {
				$user_id = $existing_user->ID;
				$existing_user->set_role( 'crezca_instructor' );
				if ( ! empty( $params['password'] ) ) {
					wp_set_password( $params['password'], $user_id );
					$user_plain_password = $params['password'];
				}
			} else {
				$username = sanitize_user( current( explode( '@', $email ) ) . rand( 10, 99 ) );
				$password = ! empty( $params['password'] ) ? $params['password'] : wp_generate_password( 12, true, false );
				$user_plain_password = $password;
				$new_uid = wp_create_user( $username, $password, $email );
				if ( ! is_wp_error( $new_uid ) ) {
					$user_id = $new_uid;
					wp_update_user( [
						'ID'           => $user_id,
						'display_name' => $name,
						'first_name'   => $name,
					] );
					$u = new \WP_User( $user_id );
					$u->set_role( 'crezca_instructor' );
				}
			}

			if ( ! empty( $params['sendWelcomeEmail'] ) && ! empty( $user_plain_password ) ) {
				$login_url = function_exists( 'crezca_get_login_page_url' ) ? crezca_get_login_page_url() : wp_login_url();
				$academy_name = get_option( 'alezux_academy_name', get_bloginfo( 'name' ) );

				// Nombres de cursos asignados
				$courses_names = [];
				if ( ! empty( $params['assignedCourseIds'] ) && is_array( $params['assignedCourseIds'] ) ) {
					foreach ( $params['assignedCourseIds'] as $acid ) {
						$c_post = get_post( (int) $acid );
						if ( $c_post ) {
							$courses_names[] = $c_post->post_title;
						}
					}
				}
				$courses_str = ! empty( $courses_names ) ? implode( ', ', $courses_names ) : 'Sin cursos asignados aún';

				$sent_via_marketing = false;
				if ( class_exists( '\Alezux_Members\Modules\Marketing\Marketing' ) ) {
					try {
						$engine = \Alezux_Members\Modules\Marketing\Marketing::get_instance()->get_engine();
						if ( $engine && method_exists( $engine, 'send_email' ) ) {
							$user_obj = $user_id ? get_user_by( 'ID', $user_id ) : null;
							$mail_data = [
								'user'             => $user_obj ?: (object) [ 'display_name' => $name, 'name' => $name, 'user_email' => $email, 'user_login' => $email ],
								'password'         => $user_plain_password,
								'new_password'     => $user_plain_password,
								'assigned_courses' => $courses_str,
								'login_url'        => $login_url,
								'site_name'        => $academy_name,
							];
							$sent_via_marketing = $engine->send_email( 'instructor_welcome', $email, $mail_data, false );
						}
					} catch ( \Exception $e ) {
						error_log( 'Crezca Instructor Welcome Mail Error: ' . $e->getMessage() );
					}
				}

				if ( ! $sent_via_marketing ) {
					$accent = get_option( 'alezux_accent_color', '#7747ff' );
					$logo_url = get_option( 'alezux_academy_logo', '' );
					$subject = '¡Bienvenido(a) al equipo docente de ' . $academy_name . '!';
					$logo_html = ! empty( $logo_url ) ? '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $academy_name ) . '" style="max-height:48px;display:block;margin:0 auto 20px auto;">' : '<h2 style="margin:0 0 20px 0;text-align:center;color:#ffffff;font-size:24px;">' . esc_html( $academy_name ) . '</h2>';

					$html_body = '<!DOCTYPE html>
					<html>
					<head><meta charset="UTF-8"><title>' . esc_html( $subject ) . '</title></head>
					<body style="margin:0;padding:40px 15px;background:#090d16;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#f8fafc;">
					<table cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:600px;margin:0 auto;background:#111625;border-radius:16px;border:1px solid #1e293b;overflow:hidden;">
						<tr>
							<td style="padding:36px 32px 24px 32px;text-align:center;">
								' . $logo_html . '
								<span style="display:inline-block;padding:6px 14px;border-radius:30px;background:rgba(119,71,255,0.15);color:' . esc_attr( $accent ) . ';font-size:12px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;">Cuenta Docente Creada</span>
								<h1 style="color:#ffffff;font-size:22px;margin:16px 0 8px 0;font-weight:700;">¡Hola, ' . esc_html( $name ) . '!</h1>
								<p style="color:#94a3b8;font-size:14px;line-height:1.6;margin:0 0 24px 0;">Nos entusiasma darte la bienvenida al equipo docente de <strong>' . esc_html( $academy_name ) . '</strong>. Tu cuenta con rol de Docente ya está activa para gestionar tus formaciones y dar seguimiento a tus estudiantes.</p>
							</td>
						</tr>
						<tr>
							<td style="padding:0 32px 24px 32px;">
								<table cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#172033;border-radius:12px;border:1px solid #1e293b;margin-bottom:20px;">
									<tr><td colspan="2" style="padding:14px 18px;border-bottom:1px solid #1e293b;color:#ffffff;font-weight:600;font-size:13px;">Tus Credenciales de Acceso</td></tr>
									<tr>
										<td style="padding:12px 18px;color:#94a3b8;font-size:13px;width:40%;">Usuario / Correo:</td>
										<td style="padding:12px 18px;color:#ffffff;font-size:13px;font-weight:600;">' . esc_html( $email ) . '</td>
									</tr>
									<tr>
										<td style="padding:12px 18px;color:#94a3b8;font-size:13px;border-top:1px solid rgba(255,255,255,0.05);">Rol Asignado:</td>
										<td style="padding:12px 18px;color:' . esc_attr( $accent ) . ';font-size:13px;font-weight:600;border-top:1px solid rgba(255,255,255,0.05);">Docente Titular</td>
									</tr>
									<tr>
										<td style="padding:12px 18px;color:#94a3b8;font-size:13px;border-top:1px solid rgba(255,255,255,0.05);">Cursos Asignados:</td>
										<td style="padding:12px 18px;color:#ffffff;font-size:13px;font-weight:500;border-top:1px solid rgba(255,255,255,0.05);">' . esc_html( $courses_str ) . '</td>
									</tr>
								</table>

								<div style="background:#0a0f1d;border:1px dashed ' . esc_attr( $accent ) . ';border-radius:12px;padding:16px 20px;text-align:center;margin-bottom:24px;">
									<div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Contraseña de Acceso</div>
									<div style="font-family:Courier,monospace;font-size:20px;font-weight:700;color:#ffffff;letter-spacing:2px;">' . esc_html( $user_plain_password ) . '</div>
								</div>

								<div style="text-align:center;">
									<a href="' . esc_url( $login_url ) . '" style="display:inline-block;padding:14px 32px;background:' . esc_attr( $accent ) . ';color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;border-radius:50px;box-shadow:0 8px 24px rgba(119,71,255,0.35);">Acceder al Panel Docente &rarr;</a>
								</div>
							</td>
						</tr>
						<tr>
							<td style="padding:20px 32px 32px 32px;text-align:center;border-top:1px solid #1e293b;color:#64748b;font-size:12px;">
								<p style="margin:0;">Mensaje enviado por ' . esc_html( $academy_name ) . '. Si tienes alguna duda, responde directamente a este correo.</p>
							</td>
						</tr>
					</table>
					</body>
					</html>';

					wp_mail( $email, $subject, $html_body, [ 'Content-Type: text/html; charset=UTF-8' ] );
				}
			}
		}

		if ( empty( $avatar ) && $user_id ) {
			$avatar = get_avatar_url( $user_id, [ 'size' => 120 ] );
		}

		$instructor_item = [
			'id'                => $id,
			'userId'            => $user_id,
			'name'              => $name,
			'email'             => $email,
			'role'              => $role,
			'avatar'            => $avatar,
			'bio'               => $bio,
			'isAdmin'           => $is_admin,
			'allowedChannelIds' => $allowed_channels,
			'updatedAt'         => current_time( 'mysql' ),
		];

		// Buscar si ya existe
		$found_idx = -1;
		foreach ( $instructors as $idx => $inst ) {
			if ( (string) $inst['id'] === $id ) {
				$found_idx = $idx;
				break;
			}
		}

		if ( $found_idx >= 0 ) {
			$instructors[ $found_idx ] = array_merge( $instructors[ $found_idx ], $instructor_item );
		} else {
			$instructor_item['createdAt'] = current_time( 'mysql' );
			$instructors[] = $instructor_item;
		}

		update_option( '_alezux_global_instructors', $instructors );

		// Si se indicaron cursos asignados al crear/editar, sincronizarlos
		if ( isset( $params['assignedCourseIds'] ) && is_array( $params['assignedCourseIds'] ) ) {
			$assigned_cids = array_map( 'intval', $params['assignedCourseIds'] );
			$all_courses = get_posts( [
				'post_type'      => [ 'crezca_course', 'sfwd-courses' ],
				'post_status'    => [ 'publish', 'draft' ],
				'posts_per_page' => -1,
			] );

			foreach ( $all_courses as $c ) {
				$mentors = get_post_meta( $c->ID, '_alezux_course_mentors', true );
				$mentors = is_array( $mentors ) ? $mentors : [];

				$is_currently_in = false;
				$new_mentors = [];
				foreach ( $mentors as $m ) {
					if ( (string) ($m['id'] ?? '') === $id || mb_strtolower( trim( $m['name'] ?? '' ) ) === mb_strtolower( $name ) ) {
						$is_currently_in = true;
					} else {
						$new_mentors[] = $m;
					}
				}

				if ( in_array( $c->ID, $assigned_cids, true ) ) {
					// Debe estar asignado
					$new_mentors[] = [
						'id'     => $id,
						'userId' => $user_id,
						'email'  => $email,
						'name'   => $name,
						'role'   => $role,
						'avatar' => $avatar,
						'bio'    => $bio,
					];
					update_post_meta( $c->ID, '_alezux_course_mentors', $new_mentors );
				} elseif ( $is_currently_in ) {
					// Debe quitarse
					update_post_meta( $c->ID, '_alezux_course_mentors', $new_mentors );
				}
			}
		}

		return rest_ensure_response( [
			'success'    => true,
			'instructor' => $instructor_item,
			'message'    => 'Docente guardado correctamente.',
		] );
	}

	/**
	 * Eliminar docente de la lista global
	 */
	public function delete_instructor( $request ) {
		$id = (string) $request->get_param( 'id' );
		$instructors = get_option( '_alezux_global_instructors', [] );
		if ( ! is_array( $instructors ) ) {
			$instructors = [];
		}

		$filtered = array_values( array_filter( $instructors, function( $inst ) use ( $id ) {
			return (string) $inst['id'] !== $id;
		} ) );

		update_option( '_alezux_global_instructors', $filtered );

		return rest_ensure_response( [
			'success' => true,
			'message' => 'Docente eliminado correctamente.',
		] );
	}

	/**
	 * Sincronizar o registrar al Administrador actual como Docente
	 */
	public function sync_admin_as_instructor() {
		$current_user = wp_get_current_user();
		if ( ! $current_user || ! $current_user->ID ) {
			return new \WP_Error( 'not_logged_in', 'No hay sesión de usuario activa.', [ 'status' => 401 ] );
		}

		$instructors = get_option( '_alezux_global_instructors', [] );
		if ( ! is_array( $instructors ) ) {
			$instructors = [];
		}

		$admin_inst = null;
		foreach ( $instructors as &$inst ) {
			if ( ( ! empty( $inst['userId'] ) && (int) $inst['userId'] === (int) $current_user->ID ) || ! empty( $inst['isAdmin'] ) ) {
				$inst['name']    = $current_user->display_name ?: $current_user->user_login;
				$inst['email']   = $current_user->user_email;
				if ( empty( $inst['avatar'] ) ) {
					$inst['avatar'] = get_avatar_url( $current_user->ID, [ 'size' => 120 ] );
				}
				$inst['isAdmin'] = true;
				$admin_inst = $inst;
				break;
			}
		}

		if ( ! $admin_inst ) {
			$admin_inst = [
				'id'                => 'inst-admin-' . $current_user->ID,
				'userId'            => $current_user->ID,
				'name'              => $current_user->display_name ?: $current_user->user_login,
				'email'             => $current_user->user_email,
				'role'              => 'Fundador & Docente Principal',
				'avatar'            => get_avatar_url( $current_user->ID, [ 'size' => 120 ] ),
				'bio'               => 'Administrador y docente principal de la plataforma.',
				'isAdmin'           => true,
				'allowedChannelIds' => [],
				'createdAt'         => current_time( 'mysql' ),
			];
			$instructors[] = $admin_inst;
		}

		update_option( '_alezux_global_instructors', $instructors );

		return rest_ensure_response( [
			'success'    => true,
			'instructor' => $admin_inst,
			'message'    => 'Administrador asignado como Docente del sistema con éxito.',
		] );
	}

	/**
	 * Guardar docentes asignados a un curso de manera directa e inmediata
	 */
	public function save_course_instructors( $request ) {
		$course_id = (int) $request['id'];
		$params = $request->get_json_params();
		$instructors = isset( $params['instructors'] ) && is_array( $params['instructors'] ) ? $params['instructors'] : [];

		update_post_meta( $course_id, '_alezux_course_mentors', $instructors );

		// Sincronizar docentes nuevos en la lista global
		$global_instructors = get_option( '_alezux_global_instructors', [] );
		if ( ! is_array( $global_instructors ) ) {
			$global_instructors = [];
		}

		$updated_global = false;
		foreach ( $instructors as $inst ) {
			$inst_id = isset( $inst['id'] ) ? (string) $inst['id'] : '';
			$inst_name = isset( $inst['name'] ) ? trim( $inst['name'] ) : '';
			if ( ! $inst_id || ! $inst_name ) continue;

			$exists = false;
			foreach ( $global_instructors as $gi ) {
				if ( (string) ($gi['id'] ?? '') === $inst_id || mb_strtolower( trim( $gi['name'] ?? '' ) ) === mb_strtolower( $inst_name ) ) {
					$exists = true;
					break;
				}
			}

			if ( ! $exists ) {
				$global_instructors[] = [
					'id'                => $inst_id,
					'name'              => $inst_name,
					'role'              => isset( $inst['role'] ) ? sanitize_text_field( $inst['role'] ) : 'Docente',
					'avatar'            => isset( $inst['avatar'] ) ? esc_url_raw( $inst['avatar'] ) : '',
					'bio'               => isset( $inst['bio'] ) ? sanitize_textarea_field( $inst['bio'] ) : '',
					'email'             => isset( $inst['email'] ) ? sanitize_email( $inst['email'] ) : '',
					'isAdmin'           => false,
					'allowedChannelIds' => [],
					'createdAt'         => current_time( 'mysql' ),
				];
				$updated_global = true;
			}
		}

		if ( $updated_global ) {
			update_option( '_alezux_global_instructors', $global_instructors );
		}

		return rest_ensure_response( [
			'success'     => true,
			'instructors' => $instructors,
			'message'     => 'Docentes guardados de forma inmediata en el curso.',
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

				$checkout_url = home_url( "/?alezux_action=checkout&token={$token}&plan_id={$row->id}" );
				$subs_count = isset( $counts_by_plan[ $row->id ] ) ? $counts_by_plan[ $row->id ] : 0;

				$total_quotas = (int) $row->total_quotas;
				$plan_type = 'installments';
				if ( $total_quotas === 0 ) {
					$plan_type = 'subscription';
				} elseif ( $total_quotas === 1 ) {
					$plan_type = 'one_time';
				}

				$total_amount = $total_quotas > 0 ? (float) ( $total_quotas * $row->quota_amount ) : (float) $row->quota_amount;

				$plans[] = [
					'id'               => (int) $row->id,
					'name'             => $row->name,
					'courseId'         => (int) $row->course_id,
					'courseTitle'      => $course_title,
					'planType'         => $plan_type,
					'totalQuotas'      => $total_quotas,
					'quotaAmount'      => (float) $row->quota_amount,
					'totalAmount'      => $total_amount,
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
		$plan_type = isset( $params['planType'] ) ? sanitize_text_field( $params['planType'] ) : '';

		if ( $plan_type === 'subscription' || ( isset( $params['totalQuotas'] ) && (int) $params['totalQuotas'] === 0 ) ) {
			$plan_type = 'subscription';
			$total_quotas = 0;
			$frequency = sanitize_text_field( ! empty( $params['frequency'] ) ? $params['frequency'] : 'month' );
		} elseif ( $plan_type === 'one_time' || ( isset( $params['totalQuotas'] ) && (int) $params['totalQuotas'] === 1 ) ) {
			$plan_type = 'one_time';
			$total_quotas = 1;
			$frequency = 'contado';
		} else {
			$plan_type = 'installments';
			$total_quotas = max( 2, (int) ( $params['totalQuotas'] ?? 2 ) );
			$frequency = sanitize_text_field( ! empty( $params['frequency'] ) ? $params['frequency'] : 'month' );
		}

		$quota_amount = (float) $params['quotaAmount'];
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
			$interval = ( $total_quotas === 1 ) ? 'contado' : $frequency;
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
		$checkout_url = home_url( "/?alezux_action=checkout&token={$token}&plan_id={$plan_id}" );
		$course_title = 'Todos los Cursos';
		if ( $course_id > 0 ) {
			$course_title = get_the_title( $course_id ) ?: "Curso #{$course_id}";
		}

		return rest_ensure_response( [
			'id'               => $plan_id,
			'name'             => $name,
			'courseId'         => $course_id,
			'courseTitle'      => $course_title,
			'planType'         => $plan_type,
			'totalQuotas'      => $total_quotas,
			'quotaAmount'      => $quota_amount,
			'totalAmount'      => (float) ( $total_quotas > 0 ? ( $total_quotas * $quota_amount ) : $quota_amount ),
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
		if ( isset( $params['planType'] ) ) {
			if ( $params['planType'] === 'subscription' ) {
				$data_to_update['total_quotas'] = 0;
			} elseif ( $params['planType'] === 'one_time' ) {
				$data_to_update['total_quotas'] = 1;
				$data_to_update['frequency'] = 'contado';
			} elseif ( isset( $params['totalQuotas'] ) ) {
				$data_to_update['total_quotas'] = max( 2, (int) $params['totalQuotas'] );
			}
		} elseif ( isset( $params['totalQuotas'] ) ) {
			$data_to_update['total_quotas'] = max( 0, (int) $params['totalQuotas'] );
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
			$t_data = ! empty( $row->data ) ? json_decode( $row->data, true ) : [];
			$student_name  = ! empty( $row->user_name ) ? $row->user_name : ( $t_data['student_name'] ?? ( $row->user_email ?: 'Estudiante' ) );
			$student_email = ! empty( $row->user_email ) ? $row->user_email : ( $t_data['student_email'] ?? '' );
			$student_phone = $t_data['student_phone'] ?? '';
			$receipt_url   = $t_data['receipt_url'] ?? '';

			$payment_desc = 'Pago Único';
			if ( (int) $row->total_quotas === 0 ) {
				$payment_desc = 'Membresía Recurrente';
			} elseif ( $row->total_quotas > 1 ) {
				$curr_q = $row->sub_quotas_paid ?: 1;
				$payment_desc = "Recurrente ({$curr_q}/{$row->total_quotas})";
			} elseif ( $row->total_quotas == 1 ) {
				$payment_desc = 'De Contado';
			}

			$data[] = [
				'id'           => (int) $row->id,
				'student'      => $student_name,
				'studentEmail' => $student_email,
				'studentPhone' => $student_phone,
				'method'       => $row->method ? ucfirst( $row->method ) : 'Stripe',
				'amount'       => (float) $row->amount,
				'currency'     => $row->currency ?: 'USD',
				'course'       => $row->plan_name ?: 'Plan General',
				'quotasDesc'   => $payment_desc,
				'status'       => $row->status ?: 'succeeded',
				'date'         => date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $row->created_at ) ),
				'ref'          => $row->transaction_ref ?: 'tx_' . substr( md5( $row->id ), 0, 10 ),
				'receiptUrl'   => $receipt_url,
			];
		}

		return rest_ensure_response( [
			'rows'  => $data,
			'total' => $total_rows,
			'pages' => ceil( $total_rows / $limit ) ?: 1,
		] );
	}

	/**
	 * Aprobar un Pago Manual y Matricular Automáticamente al Estudiante
	 */
	public function approve_finance_sale( $request ) {
		global $wpdb;
		$sale_id = (int) $request->get_param( 'id' );
		$t_trans = $wpdb->prefix . 'alezux_finanzas_transactions';

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t_trans WHERE id = %d", $sale_id ) );
		if ( ! $row ) {
			return new \WP_Error( 'not_found', 'Transacción no encontrada.', [ 'status' => 404 ] );
		}

		$data = ! empty( $row->data ) ? json_decode( $row->data, true ) : [];
		$email = ! empty( $data['student_email'] ) ? sanitize_email( $data['student_email'] ) : '';
		$name  = ! empty( $data['student_name'] ) ? sanitize_text_field( $data['student_name'] ) : '';
		$plan_id = (int) ( $row->plan_id ?: ( $data['plan_id'] ?? 0 ) );
		$amount = (float) $row->amount;
		$ref = $row->transaction_ref ?: ( $data['reference'] ?? '' );
		$method = $row->method ?: 'manual';

		if ( empty( $email ) && $row->user_id > 0 ) {
			$user = get_user_by( 'id', $row->user_id );
			if ( $user ) {
				$email = $user->user_email;
				$name  = $user->display_name;
			}
		}

		if ( empty( $email ) ) {
			return new \WP_Error( 'invalid_data', 'No se encontró el correo del estudiante en el registro.', [ 'status' => 400 ] );
		}

		// Matricular al usuario usando Enrollment_Manager (Crea cuenta y envía correo de bienvenida automáticamente)
		if ( ! class_exists( '\Alezux_Members\Modules\Finanzas\Includes\Enrollment_Manager' ) && file_exists( ALEZUX_MEMBERS_PATH . 'modules/finanzas/includes/Enrollment_Manager.php' ) ) {
			require_once ALEZUX_MEMBERS_PATH . 'modules/finanzas/includes/Enrollment_Manager.php';
		}

		if ( class_exists( '\Alezux_Members\Modules\Finanzas\Includes\Enrollment_Manager' ) ) {
			$user_id = \Alezux_Members\Modules\Finanzas\Includes\Enrollment_Manager::enroll_user(
				$email,
				$plan_id,
				null,
				$amount,
				$ref,
				'USD',
				$name,
				$method
			);

			if ( $user_id ) {
				$wpdb->update(
					$t_trans,
					[
						'status'  => 'succeeded',
						'user_id' => $user_id,
					],
					[ 'id' => $sale_id ]
				);

				return rest_ensure_response( [
					'success' => true,
					'message' => '¡Pago aprobado con éxito! El alumno fue matriculado y se le ha enviado su correo de bienvenida con sus accesos.',
					'status'  => 'succeeded',
					'userId'  => $user_id,
				] );
			}
		}

		return new \WP_Error( 'enrollment_failed', 'Ocurrió un error al matricular al estudiante.', [ 'status' => 500 ] );
	}

	/**
	 * Rechazar un Pago Manual
	 */
	public function reject_finance_sale( $request ) {
		global $wpdb;
		$sale_id = (int) $request->get_param( 'id' );
		$t_trans = $wpdb->prefix . 'alezux_finanzas_transactions';

		$wpdb->update( $t_trans, [ 'status' => 'failed' ], [ 'id' => $sale_id ] );

		return rest_ensure_response( [
			'success' => true,
			'message' => 'El pago fue marcado como rechazado / no válido.',
			'status'  => 'failed',
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
					p.quota_amount,
					p.frequency 
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
			$is_membership = ( (int) $row->total_quotas === 0 );
			$plan_type = $is_membership ? 'subscription' : ( (int) $row->total_quotas === 1 ? 'one_time' : 'installments' );

			// Auto-reparar fecha de próximo cobro si la suscripción está activa y tiene cuotas pendientes o es membresía
			if ( $row->status === 'active' && empty( $row->next_payment_date ) ) {
				$has_pending_quotas = ( $is_membership || (int) $row->total_quotas > (int) $row->quotas_paid );
				if ( $has_pending_quotas ) {
					$base_time = ! empty( $row->last_payment_date ) ? strtotime( $row->last_payment_date ) : strtotime( $row->created_at );
					if ( ! $base_time ) {
						$base_time = time();
					}
					$freq = ! empty( $row->frequency ) ? $row->frequency : 'month';
					$interval = ( $freq === 'year' ) ? '+1 year' : ( ( $freq === 'week' ) ? '+1 week' : '+1 month' );
					$calc_date = date( 'Y-m-d H:i:s', strtotime( $interval, $base_time ) );

					$wpdb->update( $t_subs, [ 'next_payment_date' => $calc_date ], [ 'id' => $row->id ] );
					$row->next_payment_date = $calc_date;
				}
			}

			$next_payment = '—';
			if ( $row->status === 'active' && $row->next_payment_date ) {
				$next_payment = date_i18n( get_option( 'date_format' ), strtotime( $row->next_payment_date ) );
				if ( strtotime( $row->next_payment_date ) < time() ) {
					$next_payment .= ' (Vencido)';
				}
			} elseif ( $row->status === 'completed' ) {
				$next_payment = 'Completado';
			} elseif ( $row->status === 'canceled' ) {
				$next_payment = 'Cancelada';
			} elseif ( $row->status === 'past_due' ) {
				$next_payment = 'Pago Pendiente';
			}

			$percent = 0;
			if ( (int) $row->total_quotas > 0 ) {
				$percent = min( 100, round( ( (int) $row->quotas_paid / (int) $row->total_quotas ) * 100 ) );
			} elseif ( $is_membership ) {
				$percent = ( $row->status === 'active' ) ? 100 : 0;
			}

			$data[] = [
				'id'             => (int) $row->id,
				'student'        => $row->display_name ?: ( $row->user_email ?: 'Usuario #' . $row->user_id ),
				'studentEmail'   => $row->user_email ?: '',
				'studentAvatar'  => get_avatar_url( $row->user_id, [ 'size' => 48 ] ),
				'plan'           => $row->plan_name ?: 'Plan de Pagos',
				'planType'       => $plan_type,
				'isMembership'   => $is_membership,
				'frequency'      => $row->frequency ?: 'month',
				'totalQuotas'    => (int) $row->total_quotas,
				'quotasPaid'     => (int) $row->quotas_paid,
				'percent'        => $percent,
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
		$is_membership = ( $total_quotas === 0 );
		$new_quotas_paid = (int) $sub->quotas_paid + 1;
		$new_status = 'active';

		$interval_str = '+1 month';
		if ( $plan && ! empty( $plan->frequency ) ) {
			if ( $plan->frequency === 'year' ) {
				$interval_str = '+1 year';
			} elseif ( $plan->frequency === 'week' ) {
				$interval_str = '+1 week';
			}
		}

		$base_time = time();
		if ( ! empty( $sub->next_payment_date ) ) {
			$existing_next = strtotime( $sub->next_payment_date );
			if ( $existing_next > time() ) {
				$base_time = $existing_next;
			}
		}
		$new_next_payment_date = date( 'Y-m-d H:i:s', strtotime( $interval_str, $base_time ) );

		if ( ! $is_membership && $new_quotas_paid >= $total_quotas ) {
			$new_status = 'completed';
			$new_next_payment_date = null;
		}

		$wpdb->update(
			$t_subs,
			[
				'quotas_paid'       => $new_quotas_paid,
				'status'            => $new_status,
				'last_payment_date' => current_time( 'mysql' ),
				'next_payment_date' => $new_next_payment_date,
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

		$message = $is_membership
			? "Pago de membresía registrado correctamente. Renovado hasta " . date_i18n( get_option( 'date_format' ), strtotime( $new_next_payment_date ) ) . "."
			: "Pago manual registrado. Cuota {$new_quotas_paid} de {$total_quotas}.";

		return rest_ensure_response( [
			'success'         => true,
			'message'         => $message,
			'status'          => $new_status,
			'quotasPaid'      => $new_quotas_paid,
			'nextPaymentDate' => $new_next_payment_date,
		] );
	}

	/**
	 * Obtener credenciales y pasarelas de pago configuradas
	 */
	public function get_finance_settings() {
		$saved = get_option( 'alezux_finance_settings', [] );
		if ( ! is_array( $saved ) ) {
			$saved = [];
		}

		$stripe_pk = ! empty( $saved['stripe_public_key'] ) ? $saved['stripe_public_key'] : get_option( 'alezux_stripe_public_key', '' );
		$stripe_sk = ! empty( $saved['stripe_secret_key'] ) ? $saved['stripe_secret_key'] : get_option( 'alezux_stripe_secret_key', '' );

		return rest_ensure_response( [
			// Stripe
			'stripe_enabled'              => isset( $saved['stripe_enabled'] ) ? (bool) $saved['stripe_enabled'] : ! empty( $stripe_pk ),
			'stripe_public_key'           => $stripe_pk,
			'stripe_secret_key'           => $stripe_sk,
			'webhook_url'                 => home_url( '/?alezux_webhook=stripe' ),

			// Pago Móvil (Venezuela)
			'pagomovil_enabled'           => isset( $saved['pagomovil_enabled'] ) ? (bool) $saved['pagomovil_enabled'] : false,
			'pagomovil_bank'              => $saved['pagomovil_bank'] ?? '',
			'pagomovil_id'                => $saved['pagomovil_id'] ?? '',
			'pagomovil_phone'             => $saved['pagomovil_phone'] ?? '',
			'pagomovil_holder'            => $saved['pagomovil_holder'] ?? '',

			// Zelle
			'zelle_enabled'               => isset( $saved['zelle_enabled'] ) ? (bool) $saved['zelle_enabled'] : false,
			'zelle_email'                 => $saved['zelle_email'] ?? '',
			'zelle_holder'                => $saved['zelle_holder'] ?? '',

			// Transferencia Bancaria Local
			'bank_transfer_enabled'       => isset( $saved['bank_transfer_enabled'] ) ? (bool) $saved['bank_transfer_enabled'] : false,
			'bank_name'                   => $saved['bank_name'] ?? '',
			'bank_account_number'         => $saved['bank_account_number'] ?? '',
			'bank_account_type'           => $saved['bank_account_type'] ?? 'Corriente',
			'bank_holder_name'            => $saved['bank_holder_name'] ?? '',
			'bank_holder_id'              => $saved['bank_holder_id'] ?? '',

			// Binance Pay / Cripto
			'binance_enabled'             => isset( $saved['binance_enabled'] ) ? (bool) $saved['binance_enabled'] : false,
			'binance_pay_id'              => $saved['binance_pay_id'] ?? '',
			'binance_usdt_wallet'         => $saved['binance_usdt_wallet'] ?? '',
			'binance_network'             => $saved['binance_network'] ?? 'TRC20',

			// PayPal
			'paypal_enabled'              => isset( $saved['paypal_enabled'] ) ? (bool) $saved['paypal_enabled'] : false,
			'paypal_email'                => $saved['paypal_email'] ?? '',

			// WhatsApp & Instrucciones
			'whatsapp_number'             => $saved['whatsapp_number'] ?? '',
			'manual_payment_instructions' => $saved['manual_payment_instructions'] ?? 'Envía tu comprobante con el número de referencia para verificar y activar tu acceso de inmediato.',

			// Métodos de Pago Manuales Dinámicos (Sin restricción por país)
			'manual_methods'              => isset( $saved['manual_methods'] ) && is_array( $saved['manual_methods'] ) ? $saved['manual_methods'] : [],
		] );
	}

	/**
	 * Guardar configuración de pasarelas y métodos de pago
	 */
	public function save_finance_settings( $request ) {
		$params = $request->get_json_params();
		$current = get_option( 'alezux_finance_settings', [] );
		if ( ! is_array( $current ) ) {
			$current = [];
		}

		$fields = [
			'stripe_enabled',
			'stripe_public_key',
			'stripe_secret_key',
			'pagomovil_enabled',
			'pagomovil_bank',
			'pagomovil_id',
			'pagomovil_phone',
			'pagomovil_holder',
			'zelle_enabled',
			'zelle_email',
			'zelle_holder',
			'bank_transfer_enabled',
			'bank_name',
			'bank_account_number',
			'bank_account_type',
			'bank_holder_name',
			'bank_holder_id',
			'binance_enabled',
			'binance_pay_id',
			'binance_usdt_wallet',
			'binance_network',
			'paypal_enabled',
			'paypal_email',
			'whatsapp_number',
			'manual_payment_instructions',
		];

		foreach ( $fields as $f ) {
			if ( isset( $params[ $f ] ) ) {
				if ( substr( $f, -8 ) === '_enabled' ) {
					$current[ $f ] = (bool) $params[ $f ];
				} else {
					$current[ $f ] = sanitize_text_field( $params[ $f ] );
				}
			}
		}

		// Guardar lista de métodos manuales dinámicos
		if ( isset( $params['manual_methods'] ) && is_array( $params['manual_methods'] ) ) {
			$clean_manual = [];
			foreach ( $params['manual_methods'] as $m ) {
				if ( ! empty( $m['name'] ) || ! empty( $m['bank'] ) || ! empty( $m['account_number'] ) || ! empty( $m['details'] ) ) {
					$clean_manual[] = [
						'id'             => sanitize_text_field( $m['id'] ?? uniqid( 'man_' ) ),
						'name'           => sanitize_text_field( $m['name'] ?? 'Método de Pago' ),
						'bank'           => sanitize_text_field( $m['bank'] ?? '' ),
						'account_number' => sanitize_text_field( $m['account_number'] ?? '' ),
						'holder_name'    => sanitize_text_field( $m['holder_name'] ?? '' ),
						'holder_id'      => sanitize_text_field( $m['holder_id'] ?? '' ),
						'instructions'   => sanitize_text_field( $m['instructions'] ?? '' ),
						'details'        => sanitize_textarea_field( $m['details'] ?? '' ),
						'enabled'        => ! empty( $m['enabled'] ),
					];
				}
			}
			$current['manual_methods'] = $clean_manual;
		}

		update_option( 'alezux_finance_settings', $current );

		// Sincronizar compatibilidad directa con opciones de Stripe
		if ( isset( $params['stripe_public_key'] ) ) {
			update_option( 'alezux_stripe_public_key', sanitize_text_field( $params['stripe_public_key'] ) );
		}
		if ( isset( $params['stripe_secret_key'] ) ) {
			update_option( 'alezux_stripe_secret_key', sanitize_text_field( $params['stripe_secret_key'] ) );
		}

		return rest_ensure_response( [
			'success' => true,
			'message' => 'Configuración de métodos de pago guardada correctamente.',
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
			'admin_transfer_alert'   => 'finanzas',
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
			'admin_transfer_alert'   => 'Al recibir comprobante de pago manual',
			'achievement_assigned'   => 'Al desbloquear insignia o logro',
			'course_available'       => 'Al publicar un nuevo curso formativo',
			'lesson_available'       => 'Al publicar nuevas lecciones',
			'inactivity_alert'       => 'Tras 7+ días continuos de inactividad',
			'course_completed'       => 'Al alcanzar el 100% del curso',
		];

		$email_theme = get_option( 'alezux_marketing_email_theme', 'clean' );
		$accent = get_option( 'alezux_accent', 'violet' );

		// Sincronizar automáticamente la base de datos si el acento o tema cambió o si faltan plantillas registradas
		$synced_accent = get_option( 'alezux_marketing_templates_accent', '' );
		$synced_theme  = get_option( 'alezux_marketing_templates_theme', '' );
		if ( count( $saved_templates ) < count( $registered_types ) || $synced_accent !== $accent || $synced_theme !== $email_theme ) {
			if ( class_exists( '\Alezux_Members\Modules\Marketing\Includes\Default_Templates' ) ) {
				\Alezux_Members\Modules\Marketing\Includes\Default_Templates::sync_database_templates( $accent, $email_theme, false );
				if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table ) {
					$saved_templates = $wpdb->get_results( "SELECT * FROM $table", OBJECT_K );
				}
			}
		}

		$result = [];
		foreach ( $registered_types as $type_key => $info ) {
			$saved = isset( $saved_templates[ $type_key ] ) ? $saved_templates[ $type_key ] : null;
			$default = class_exists( '\Alezux_Members\Modules\Marketing\Includes\Default_Templates' )
				? \Alezux_Members\Modules\Marketing\Includes\Default_Templates::get( $type_key, $email_theme, $accent )
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
	 * Restablecer una plantilla de marketing al diseño oficial Arc
	 */
	public function reset_marketing_automation( $request ) {
		$type = sanitize_text_field( $request->get_param( 'id' ) );
		global $wpdb;
		$table = $wpdb->prefix . 'alezux_marketing_templates';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table ) {
			$wpdb->delete( $table, [ 'type' => $type ] );
		}

		$theme = get_option( 'alezux_marketing_email_theme', 'clean' );
		$accent = get_option( 'alezux_accent', 'violet' );
		if ( ! class_exists( '\Alezux_Members\Modules\Marketing\Includes\Default_Templates' ) && file_exists( ALEZUX_MEMBERS_PATH . 'modules/marketing/includes/Default_Templates.php' ) ) {
			require_once ALEZUX_MEMBERS_PATH . 'modules/marketing/includes/Default_Templates.php';
		}

		$default = class_exists( '\Alezux_Members\Modules\Marketing\Includes\Default_Templates' )
			? \Alezux_Members\Modules\Marketing\Includes\Default_Templates::get( $type, $theme, $accent )
			: [ 'subject' => 'Notificación', 'content' => '' ];

		return rest_ensure_response( [
			'success' => true,
			'id'      => $type,
			'subject' => $default['subject'],
			'body'    => $default['content'],
		] );
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

		// Limpieza preventiva de marcas de lectura espurias
		$wpdb->query( "UPDATE $table_logs SET opened_at = NULL WHERE opened_at = '0000-00-00 00:00:00'" );

		$formatted = [];
		foreach ( $logs as $log ) {
			$status_display = 'Enviado';
			$is_opened = ! empty( $log->opened_at ) 
				&& $log->opened_at !== '0000-00-00 00:00:00' 
				&& $log->opened_at !== 'null' 
				&& strtotime( $log->opened_at ) > 0;

			if ( $is_opened ) {
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
				'openedAt'  => $is_opened ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $log->opened_at ) ) : null,
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
			'email_theme'   => get_option( 'alezux_marketing_email_theme', 'clean' ),
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
		if ( isset( $params['email_theme'] ) ) {
			$theme = in_array( $params['email_theme'], [ 'clean', 'dark' ], true ) ? $params['email_theme'] : 'clean';
			update_option( 'alezux_marketing_email_theme', $theme );

			if ( file_exists( ALEZUX_MEMBERS_PATH . 'modules/marketing/includes/Default_Templates.php' ) ) {
				require_once ALEZUX_MEMBERS_PATH . 'modules/marketing/includes/Default_Templates.php';
				\Alezux_Members\Modules\Marketing\Includes\Default_Templates::sync_database_templates( null, $theme, false );
			}
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

	/**
	 * Obtener configuración general de la plataforma
	 */
	public function get_platform_settings() {
		return rest_ensure_response( [
			'academy_name' => get_option( 'alezux_academy_name', 'Crezca' ),
			'academy_logo' => get_option( 'alezux_academy_logo', '' ),
			'theme'        => get_option( 'alezux_theme', 'dark' ),
			'accent'       => get_option( 'alezux_accent', 'violet' ),
		] );
	}

	/**
	 * Guardar configuración general de la plataforma
	 */
	public function save_platform_settings( $request ) {
		$params = $request->get_json_params();
		if ( isset( $params['academy_name'] ) ) {
			update_option( 'alezux_academy_name', sanitize_text_field( $params['academy_name'] ) );
		}
		if ( isset( $params['academy_logo'] ) ) {
			update_option( 'alezux_academy_logo', esc_url_raw( $params['academy_logo'] ) );
		}
		if ( isset( $params['theme'] ) && in_array( $params['theme'], [ 'dark', 'light' ], true ) ) {
			update_option( 'alezux_theme', sanitize_text_field( $params['theme'] ) );
		}
		if ( isset( $params['accent'] ) ) {
			$new_accent = sanitize_text_field( $params['accent'] );
			update_option( 'alezux_accent', $new_accent );

			if ( file_exists( ALEZUX_MEMBERS_PATH . 'modules/marketing/includes/Default_Templates.php' ) ) {
				require_once ALEZUX_MEMBERS_PATH . 'modules/marketing/includes/Default_Templates.php';
				\Alezux_Members\Modules\Marketing\Includes\Default_Templates::sync_database_templates( $new_accent, null, false );
			}
		}
		return rest_ensure_response( [
			'success'  => true,
			'message'  => 'Configuración de la plataforma guardada correctamente.',
			'settings' => [
				'academy_name' => get_option( 'alezux_academy_name', 'Crezca' ),
				'academy_logo' => get_option( 'alezux_academy_logo', '' ),
				'theme'        => get_option( 'alezux_theme', 'dark' ),
				'accent'       => get_option( 'alezux_accent', 'violet' ),
			],
		] );
	}

	/**
	 * Obtener perfil del estudiante conectado
	 */
	/**
	 * Obtener configuración de autenticación y pantalla de login
	 */
	public function get_auth_settings() {
		$settings = function_exists( 'crezca_get_auth_settings' ) ? crezca_get_auth_settings() : [];
		return rest_ensure_response( $settings );
	}

	/**
	 * Guardar configuración de autenticación y pantalla de login
	 */
	public function save_auth_settings( $request ) {
		$params = $request->get_json_params();
		$current = function_exists( 'crezca_get_auth_settings' ) ? crezca_get_auth_settings() : [];

		$allowed_keys = [
			'logo_url',
			'logo_width',
			'bg_type',
			'bg_color',
			'bg_gradient_start',
			'bg_gradient_end',
			'bg_image_url',
			'accent_color',
			'hero_badge_enabled',
			'hero_badge_text',
			'hero_title',
			'hero_subtitle',
			'steps_enabled',
			'step_1_num',
			'step_1_text',
			'step_2_num',
			'step_2_text',
			'step_3_num',
			'step_3_text',
			'login_title',
			'login_subtitle',
			'allow_registration',
			'terms_url',
			'privacy_url',
			'custom_css',
			'sync_academy_colors',
			'student_default_view',
			'admin_default_redirect',
			'form_theme',
		];

		foreach ( $allowed_keys as $key ) {
			if ( isset( $params[ $key ] ) ) {
				if ( in_array( $key, [ 'hero_badge_enabled', 'steps_enabled', 'allow_registration', 'sync_academy_colors' ], true ) ) {
					$current[ $key ] = (bool) $params[ $key ];
				} elseif ( in_array( $key, [ 'logo_width' ], true ) ) {
					$current[ $key ] = absint( $params[ $key ] );
				} elseif ( in_array( $key, [ 'terms_url', 'privacy_url', 'logo_url', 'bg_image_url' ], true ) ) {
					$current[ $key ] = esc_url_raw( $params[ $key ] );
				} elseif ( $key === 'custom_css' ) {
					$current[ $key ] = wp_strip_all_tags( $params[ $key ] );
				} else {
					$current[ $key ] = sanitize_text_field( $params[ $key ] );
				}
			}
		}

		update_option( 'crezca_auth_settings', $current );

		return rest_ensure_response( [
			'success'  => true,
			'message'  => 'Configuración de acceso y login guardada correctamente.',
			'settings' => $current,
		] );
	}

	public function get_student_profile() {
		$user = wp_get_current_user();
		if ( ! $user || ! $user->ID ) {
			return new \WP_Error( 'not_logged_in', 'Usuario no identificado', [ 'status' => 401 ] );
		}

		$enrolled_ids = [];
		if ( current_user_can( 'administrator' ) ) {
			// Los administradores tienen acceso a todos los cursos en modo preview
			$course_types = class_exists( '\\Alezux_Members\\Core\\LMS_Engine' ) 
				? \Alezux_Members\Core\LMS_Engine::get_course_post_types() 
				: [ 'crezca_course', 'sfwd-courses' ];
			$all = get_posts( [ 'post_type' => $course_types, 'posts_per_page' => -1, 'fields' => 'ids' ] );
			$enrolled_ids = array_map( 'intval', $all );
		} else {
			$saved = get_user_meta( $user->ID, '_alezux_enabled_courses', true );
			if ( is_array( $saved ) ) {
				$enrolled_ids = array_map( 'intval', $saved );
			}
			if ( function_exists( 'ld_get_mycourses' ) ) {
				$ld_courses = ld_get_mycourses( $user->ID );
				if ( ! empty( $ld_courses ) ) {
					$enrolled_ids = array_unique( array_merge( $enrolled_ids, array_map( 'intval', $ld_courses ) ) );
				}
			}

			// Respaldo de sincronización automática desde compras/suscripciones en Finanzas
			global $wpdb;
			$subs_table  = $wpdb->prefix . 'alezux_finanzas_subscriptions';
			$plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$subs_table'" ) === $subs_table && $wpdb->get_var( "SHOW TABLES LIKE '$plans_table'" ) === $plans_table ) {
				$sub_course_ids = $wpdb->get_col( $wpdb->prepare(
					"SELECT p.course_id 
					 FROM $subs_table s 
					 INNER JOIN $plans_table p ON s.plan_id = p.id 
					 WHERE s.user_id = %d 
					   AND s.status IN ('active', 'completed', 'paid', 'trialing') 
					   AND p.course_id > 0",
					$user->ID
				) );
				if ( ! empty( $sub_course_ids ) ) {
					$enrolled_ids = array_values( array_unique( array_merge( $enrolled_ids, array_map( 'intval', $sub_course_ids ) ) ) );
					update_user_meta( $user->ID, '_alezux_enabled_courses', $enrolled_ids );
				}
			}
		}

		$custom_avatar = get_user_meta( $user->ID, '_crezca_custom_avatar', true );
		$avatar = $custom_avatar ?: get_avatar_url( $user->ID, [ 'size' => 120 ] );

		// Obtener topics y subtopics completados desde la tabla de progreso y el meta
		global $wpdb;
		$table_progress = $wpdb->prefix . 'crezca_topic_progress';
		$completed_from_db = [];
		static $progress_table_exists = null;
		if ( null === $progress_table_exists ) {
			$progress_table_exists = ( $wpdb->get_var( "SHOW TABLES LIKE '$table_progress'" ) === $table_progress );
		}
		if ( $progress_table_exists ) {
			$completed_from_db = $wpdb->get_col( $wpdb->prepare(
				"SELECT topic_id FROM $table_progress WHERE user_id = %d AND is_completed = 1",
				$user->ID
			) );
		}

		$completed_meta = get_user_meta( $user->ID, '_crezca_completed_topics', true ) ?: [];
		$completed = array_values( array_unique( array_merge(
			is_array( $completed_from_db ) ? $completed_from_db : [],
			is_array( $completed_meta ) ? $completed_meta : []
		) ) );

		$is_blocked = (bool) get_user_meta( $user->ID, 'alezux_is_blocked', true );

		return rest_ensure_response( [
			'id'                => $user->ID,
			'name'              => $user->display_name ?: $user->user_login,
			'email'             => $user->user_email,
			'username'          => $user->user_login,
			'avatar'            => $avatar,
			'joinedDate'        => date( 'd M Y', strtotime( $user->user_registered ) ),
			'planName'          => get_user_meta( $user->ID, '_alezux_plan_name', true ) ?: ( current_user_can( 'administrator' ) ? 'Administrador' : 'Estudiante Pro' ),
			'enrolledCourseIds' => $enrolled_ids,
			'completedTopicIds' => array_values( array_map( 'strval', $completed ) ),
			'isAdmin'           => current_user_can( 'administrator' ),
			'isBlocked'         => $is_blocked,
			'status'            => $is_blocked ? 'blocked' : 'active',
		] );
	}

	/**
	 * Actualizar perfil de estudiante (nombre, contraseña, avatar)
	 */
	public function update_student_profile( $request ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new \WP_Error( 'not_logged_in', 'No autorizado', [ 'status' => 401 ] );
		}

		$params = $request->get_json_params();
		$userdata = [ 'ID' => $user_id ];

		if ( ! empty( $params['name'] ) ) {
			$userdata['display_name'] = sanitize_text_field( $params['name'] );
		}
		if ( ! empty( $params['email'] ) && is_email( $params['email'] ) ) {
			$userdata['user_email'] = sanitize_email( $params['email'] );
		}
		if ( ! empty( $params['password'] ) ) {
			$userdata['user_pass'] = $params['password'];
		}
		if ( ! empty( $params['avatar'] ) ) {
			update_user_meta( $user_id, '_crezca_custom_avatar', esc_url_raw( $params['avatar'] ) );
		}

		$updated = wp_update_user( $userdata );
		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		return $this->get_student_profile();
	}

	/**
	 * Permite al estudiante subir su avatar de perfil directamente desde su PC
	 */
	public function upload_student_avatar( $request ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new \WP_Error( 'not_logged_in', 'Debes iniciar sesión para subir tu avatar', [ 'status' => 401 ] );
		}

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
		update_user_meta( $user_id, '_crezca_custom_avatar', esc_url_raw( $url ) );

		return rest_ensure_response( [
			'success' => true,
			'avatar'  => $url,
			'message' => 'Avatar actualizado correctamente',
		] );
	}

	/**
	 * Listar cursos para el estudiante (incluye flag hasAccess y progress)
	 */
	public function get_student_courses() {
		$courses_response = $this->get_courses();
		$courses = $courses_response->get_data();

		$profile_res = $this->get_student_profile();
		$enrolled_ids = [];
		$completed_topic_ids = [];

		if ( ! is_wp_error( $profile_res ) ) {
			$profile = $profile_res->get_data();
			$enrolled_ids = $profile['enrolledCourseIds'];
			$completed_topic_ids = array_map( 'strval', $profile['completedTopicIds'] );
		}

		$user_id = get_current_user_id();
		$is_blocked = (bool) get_user_meta( $user_id, 'alezux_is_blocked', true );

		if ( $is_blocked && ! current_user_can( 'administrator' ) ) {
			return rest_ensure_response( [] );
		}

		foreach ( $courses as &$c ) {
			// Comprobar acceso mediante LMS_Engine
			$has_acc = in_array( (int) $c['id'], $enrolled_ids, true );
			if ( ! $has_acc && class_exists( '\\Alezux_Members\\Core\\LMS_Engine' ) ) {
				$has_acc = \Alezux_Members\Core\LMS_Engine::has_access( $c['id'], $user_id );
			}
			$c['hasAccess'] = $has_acc;

			// Calcular progreso considerando Topics y Subtopics
			$total_topics = 0;
			$done_topics = 0;
			if ( ! empty( $c['sections'] ) && is_array( $c['sections'] ) ) {
				foreach ( $c['sections'] as $sec ) {
					$topics = ! empty( $sec['lessons'] ) ? $sec['lessons'] : ( ! empty( $sec['topics'] ) ? $sec['topics'] : [] );
					foreach ( $topics as $top ) {
						// Si el topic tiene subtopics, las unidades de aprendizaje son los subtopics
						if ( ! empty( $top['subtopics'] ) && is_array( $top['subtopics'] ) ) {
							foreach ( $top['subtopics'] as $sub ) {
								$total_topics++;
								$sub_id = isset( $sub['id'] ) ? (string) $sub['id'] : '';
								if ( in_array( $sub_id, $completed_topic_ids, true ) ) {
									$done_topics++;
								}
							}
						} else {
							$total_topics++;
							$top_id = isset( $top['id'] ) ? (string) $top['id'] : '';
							if ( in_array( $top_id, $completed_topic_ids, true ) ) {
								$done_topics++;
							}
						}
					}
				}
			}

			$c['progress'] = $total_topics > 0 ? (int) round( ( $done_topics / $total_topics ) * 100 ) : 0;
			$c['totalTopics'] = $total_topics;
			$c['completedTopics'] = $done_topics;
		}

		return rest_ensure_response( $courses );
	}

	/**
	 * Marcar / desmarcar topic o subtopic como completado por el estudiante
	 */
	public function toggle_student_complete_topic( $request ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new \WP_Error( 'not_logged_in', 'No autorizado', [ 'status' => 401 ] );
		}

		$params = $request->get_json_params();
		$topic_id = isset( $params['topicId'] ) ? sanitize_text_field( $params['topicId'] ) : '';
		$course_id = isset( $params['courseId'] ) ? (int) $params['courseId'] : 0;
		$quiz_score = isset( $params['quizScore'] ) ? (int) $params['quizScore'] : null;
		$quiz_answers = isset( $params['quizAnswers'] ) ? $params['quizAnswers'] : null;

		if ( empty( $topic_id ) ) {
			return new \WP_Error( 'missing_topic', 'Topic ID requerido', [ 'status' => 400 ] );
		}

		if ( class_exists( '\\Alezux_Members\\Core\\LMS_Engine' ) ) {
			$res = \Alezux_Members\Core\LMS_Engine::toggle_topic_complete( $topic_id, $user_id, $course_id, $quiz_score, $quiz_answers );
			return rest_ensure_response( [
				'success'           => true,
				'isCompleted'       => $res['is_completed'],
				'completedTopicIds' => array_values( array_map( 'strval', $res['all_completed'] ) ),
			] );
		}

		$completed = get_user_meta( $user_id, '_crezca_completed_topics', true );
		if ( ! is_array( $completed ) ) {
			$completed = [];
		}

		$is_now_completed = false;
		if ( in_array( $topic_id, $completed, true ) ) {
			$completed = array_values( array_diff( $completed, [ $topic_id ] ) );
			$is_now_completed = false;
		} else {
			$completed[] = $topic_id;
			$is_now_completed = true;
		}

		update_user_meta( $user_id, '_crezca_completed_topics', $completed );

		return rest_ensure_response( [
			'success'           => true,
			'isCompleted'       => $is_now_completed,
			'completedTopicIds' => array_values( array_map( 'strval', $completed ) ),
		] );
	}

	/**
	 * Asegurar que existan las tablas para la comunidad tipo Discord
	 */
	/**
	 * Renderizar y resolver dinámicamente reproductores de video seguros y shortcodes (ej: VdoCipher, iframe, oEmbed)
	 */
	public function render_video_embed( $request ) {
		try {
			$params    = $request->get_json_params();
			$video_raw = isset( $params['video_url'] ) ? trim( (string) $params['video_url'] ) : '';

			if ( empty( $video_raw ) ) {
				return rest_ensure_response( [
					'success'    => false,
					'html'       => '',
					'iframe_url' => '',
					'type'       => 'empty',
					'message'    => 'No se proporcionó ningún enlace o shortcode de video.',
				] );
			}

			// Función auxiliar para decodificar entidades HTML de URLs (& -> &#038;)
			$clean_url_entities = function( $raw_src ) {
				if ( empty( $raw_src ) ) {
					return '';
				}
				$decoded = wp_specialchars_decode( $raw_src, ENT_QUOTES );
				$decoded = html_entity_decode( $decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				$decoded = str_replace( [ '&#038;', '&amp;', '&#38;' ], '&', $decoded );
				return trim( $decoded );
			};

			// Decodificar entidades HTML si vienen escapadas (&quot;, &#39;, &lt;, etc.)
			$video_raw = html_entity_decode( wp_unslash( $video_raw ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );

			// 1. Si ya es una etiqueta <iframe> directa en HTML
			if ( stripos( $video_raw, '<iframe' ) !== false ) {
				$iframe_url = '';
				if ( preg_match( '/src=["\']([^"\']+)["\']/i', $video_raw, $m ) ) {
					$iframe_url = $clean_url_entities( $m[1] );
				}
				return rest_ensure_response( [
					'success'    => true,
					'html'       => str_replace( [ '&#038;', '&amp;' ], '&', $video_raw ),
					'iframe_url' => $iframe_url,
					'type'       => 'iframe_raw',
				] );
			}

			// 2. Normalizar si es shortcode o ID directo de VdoCipher
			$shortcode_to_run = $video_raw;
			$is_shortcode     = ( str_starts_with( $video_raw, '[' ) && str_ends_with( $video_raw, ']' ) ) || str_contains( $video_raw, '[vdo' );
			$vdo_direct_id    = '';

			// Si es un ID hexadecimal de VdoCipher (32 caracteres) o formato vdo:ID
			if ( ! $is_shortcode ) {
				if ( preg_match( '/^vdo:([a-f0-9]{32})$/i', $video_raw, $vm ) ) {
					$vdo_direct_id    = $vm[1];
					$shortcode_to_run = '[vdo id="' . esc_attr( $vm[1] ) . '"]';
					$is_shortcode     = true;
				} elseif ( preg_match( '/^[a-f0-9]{32}$/i', $video_raw ) ) {
					$vdo_direct_id    = $video_raw;
					$shortcode_to_run = '[vdo id="' . esc_attr( $video_raw ) . '"]';
					$is_shortcode     = true;
				}
			} elseif ( preg_match( '/\[vdo\s+id=["\']?([a-zA-Z0-9_-]+)["\']?/i', $shortcode_to_run, $v_id_m ) ) {
				$vdo_direct_id = $v_id_m[1];
			}

			if ( $is_shortcode ) {
				// Ejecutar shortcodes nativos de WordPress (ej: plugin de VdoCipher)
				$rendered = do_shortcode( $shortcode_to_run );

				// Si el shortcode no se procesó mediante do_shortcode y la función vdo_shortcode existe directamente
				if ( ( empty( $rendered ) || trim( $rendered ) === trim( $shortcode_to_run ) ) && function_exists( 'vdo_shortcode' ) ) {
					if ( ! empty( $vdo_direct_id ) ) {
						$rendered = vdo_shortcode( [ 'id' => $vdo_direct_id ] );
					}
				}

				// Verificar si arrojó un error explícito de VdoCipher
				if ( ! empty( $rendered ) && stripos( $rendered, 'VdoCipher Error:' ) !== false ) {
					$clean_error = wp_strip_all_tags( $rendered );
					return rest_ensure_response( [
						'success'    => false,
						'html'       => $rendered,
						'iframe_url' => '',
						'type'       => 'vdocipher_error',
						'message'    => $clean_error,
					] );
				}

				// Si se procesó con éxito
				if ( ! empty( $rendered ) && trim( $rendered ) !== trim( $shortcode_to_run ) ) {
					$iframe_url = '';
					if ( preg_match( '/<iframe[^>]+src=["\']([^"\']+)["\']/i', $rendered, $m ) ) {
						// IMPORTANTE: Decodificar entidades para que &playbackInfo no se convierta en &#038;playbackInfo
						$iframe_url = $clean_url_entities( $m[1] );
					}

					return rest_ensure_response( [
						'success'    => true,
						'html'       => str_replace( [ '&#038;', '&amp;' ], '&', $rendered ),
						'iframe_url' => $iframe_url,
						'type'       => 'shortcode',
					] );
				}

				return rest_ensure_response( [
					'success'    => false,
					'html'       => '',
					'iframe_url' => '',
					'type'       => 'unprocessed_shortcode',
					'message'    => 'El shortcode no pudo ser procesado. Verifica que el plugin de VdoCipher esté activo y configurado con su clave API en WordPress.',
				] );
			}

			// 3. Si es una URL http/https completa
			if ( filter_var( $video_raw, FILTER_VALIDATE_URL ) ) {
				// Intentar oEmbed nativo de WordPress si es aplicable
				$oembed = wp_oembed_get( $video_raw );
				if ( ! empty( $oembed ) ) {
					$iframe_url = '';
					if ( preg_match( '/<iframe[^>]+src=["\']([^"\']+)["\']/i', $oembed, $m ) ) {
						$iframe_url = $clean_url_entities( $m[1] );
					}
					return rest_ensure_response( [
						'success'    => true,
						'html'       => str_replace( [ '&#038;', '&amp;' ], '&', $oembed ),
						'iframe_url' => $iframe_url,
						'type'       => 'oembed',
					] );
				}

				return rest_ensure_response( [
					'success'    => true,
					'html'       => '',
					'iframe_url' => $clean_url_entities( $video_raw ),
					'type'       => 'url',
				] );
			}

			return rest_ensure_response( [
				'success'    => false,
				'html'       => '',
				'iframe_url' => '',
				'type'       => 'invalid',
				'message'    => 'El valor proporcionado no es un enlace de video válido ni un shortcode soportado.',
			] );
		} catch ( \Throwable $e ) {
			return rest_ensure_response( [
				'success'    => false,
				'html'       => '',
				'iframe_url' => '',
				'type'       => 'server_error',
				'message'    => 'Error al procesar el reproductor: ' . $e->getMessage(),
			] );
		}
	}

	public function init_community_tables() {
		global $wpdb;

		// Evitar re-verificar con SHOW TABLES en cada petición si ya fue inicializado
		// Asegurar columna allowed_students para restricciones granulares
		if ( ! get_option( 'crezca_community_allowed_students_v1' ) ) {
			$table_ch = $wpdb->prefix . 'crezca_community_channels';
			$has_col = $wpdb->get_results( "SHOW COLUMNS FROM $table_ch LIKE 'allowed_students'" );
			if ( empty( $has_col ) ) {
				$wpdb->query( "ALTER TABLE $table_ch ADD COLUMN allowed_students longtext NULL" );
			}
			update_option( 'crezca_community_allowed_students_v1', '1' );
		}

		if ( get_option( 'crezca_community_tables_v3_ready' ) ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();
		$table_channels = $wpdb->prefix . 'crezca_community_channels';
		$table_messages = $wpdb->prefix . 'crezca_community_messages';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_channels'" ) !== $table_channels ) {
			$sql1 = "CREATE TABLE $table_channels (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(100) NOT NULL,
				slug varchar(120) NOT NULL,
				description text NULL,
				category varchar(100) NOT NULL DEFAULT 'General',
				course_id bigint(20) unsigned NOT NULL DEFAULT 0,
				allowed_students longtext NULL,
				is_announcement tinyint(1) NOT NULL DEFAULT 0,
				sort_order int(11) NOT NULL DEFAULT 0,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY category (category),
				KEY course_id (course_id)
			) $charset_collate;";
			dbDelta( $sql1 );

			// Canales por defecto iniciales
			$wpdb->insert( $table_channels, [
				'name'            => 'bienvenida',
				'slug'            => 'bienvenida',
				'description'     => 'Canal de bienvenida para todos los nuevos estudiantes de la academia.',
				'category'        => 'General',
				'course_id'       => 0,
				'is_announcement' => 0,
				'sort_order'      => 1,
			] );

			$wpdb->insert( $table_channels, [
				'name'            => 'anuncios-oficiales',
				'slug'            => 'anuncios-oficiales',
				'description'     => 'Comunicados, actualizaciones y lanzamientos importantes de la academia.',
				'category'        => 'General',
				'course_id'       => 0,
				'is_announcement' => 1,
				'sort_order'      => 2,
			] );

			$wpdb->insert( $table_channels, [
				'name'            => 'charla-general',
				'slug'            => 'charla-general',
				'description'     => 'Espacio libre para compartir ideas, hablar de proyectos y conectar.',
				'category'        => 'Comunidad',
				'course_id'       => 0,
				'is_announcement' => 0,
				'sort_order'      => 3,
			] );

			$wpdb->insert( $table_channels, [
				'name'            => 'networking-proyectos',
				'slug'            => 'networking-proyectos',
				'description'     => 'Comparte tus avances, busca colaboradores y muestra tus proyectos.',
				'category'        => 'Comunidad',
				'course_id'       => 0,
				'is_announcement' => 0,
				'sort_order'      => 4,
			] );
		}

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_messages'" ) !== $table_messages ) {
			$sql2 = "CREATE TABLE $table_messages (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				channel_id bigint(20) unsigned NOT NULL,
				parent_id bigint(20) unsigned NOT NULL DEFAULT 0,
				user_id bigint(20) unsigned NOT NULL,
				user_name varchar(150) NOT NULL,
				user_avatar text NULL,
				user_role varchar(50) NOT NULL DEFAULT 'student',
				message text NOT NULL,
				attachments longtext NULL,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY channel_id (channel_id),
				KEY parent_id (parent_id),
				KEY created_at (created_at)
			) $charset_collate;";
			dbDelta( $sql2 );

			// Insertar mensaje de bienvenida inicial
			$first_channel = $wpdb->get_var( "SELECT id FROM $table_channels WHERE slug = 'bienvenida' LIMIT 1" );
			if ( $first_channel ) {
				$wpdb->insert( $table_messages, [
					'channel_id'  => $first_channel,
					'parent_id'   => 0,
					'user_id'     => 1,
					'user_name'   => 'Academia (Admin)',
					'user_avatar' => '',
					'user_role'   => 'admin',
					'message'     => '¡Hola a todos! 👋 Les damos una cordial bienvenida a la comunidad oficial tipo Discord. Aquí podrán interactuar por canales temáticos, resolver dudas de sus cursos y colaborar juntos. ¡Saludad por aquí!',
					'created_at'  => current_time( 'mysql' ),
				] );
			}
		} else {
			// Asegurar columna parent_id para soporte de hilos si la tabla ya existía
			$has_parent = $wpdb->get_var( "SHOW COLUMNS FROM `$table_messages` LIKE 'parent_id'" );
			if ( ! $has_parent ) {
				$wpdb->query( "ALTER TABLE `$table_messages` ADD COLUMN `parent_id` bigint(20) unsigned NOT NULL DEFAULT 0 AFTER `channel_id`, ADD KEY `parent_id` (`parent_id`)" );
			}
		}

		update_option( 'crezca_community_tables_v3_ready', 1 );
	}

	/**
	 * Listar canales de la comunidad (con control de acceso por curso) - Ultra Optimizado
	 */
	public function get_community_channels( $request = null ) {
		global $wpdb;
		$this->init_community_tables();

		$table_channels = $wpdb->prefix . 'crezca_community_channels';
		$table_messages = $wpdb->prefix . 'crezca_community_messages';

		$channels_raw = $wpdb->get_results( "SELECT * FROM $table_channels ORDER BY category ASC, sort_order ASC, name ASC" );

		$user_id = get_current_user_id();
		$is_admin = current_user_can( 'administrator' );

		$enrolled_ids = [];
		if ( ! $is_admin && $user_id ) {
			$saved = get_user_meta( $user_id, '_alezux_enabled_courses', true );
			if ( is_array( $saved ) ) {
				$enrolled_ids = array_map( 'intval', $saved );
			}
			if ( function_exists( 'ld_get_mycourses' ) ) {
				$ld = ld_get_mycourses( $user_id );
				if ( ! empty( $ld ) ) {
					$enrolled_ids = array_unique( array_merge( $enrolled_ids, array_map( 'intval', $ld ) ) );
				}
			}
		}

		// Conteo masivo y eficiente de mensajes en una sola consulta
		$counts_raw = $wpdb->get_results( "SELECT channel_id, COUNT(*) as cnt FROM $table_messages GROUP BY channel_id" );
		$counts_map = [];
		if ( is_array( $counts_raw ) ) {
			foreach ( $counts_raw as $r ) {
				$counts_map[ (int) $r->channel_id ] = (int) $r->cnt;
			}
		}

		$result = [];
		foreach ( $channels_raw as $ch ) {
			$course_id = (int) $ch->course_id;
			$course_name = '';
			if ( $course_id > 0 ) {
				$course_post = get_post( $course_id );
				$course_name = $course_post ? $course_post->post_title : "Curso #$course_id";
			}

			// Comprobar acceso
			$has_access = true;
			$allowed_students = ! empty( $ch->allowed_students ) ? json_decode( $ch->allowed_students, true ) : [];
			if ( ! is_array( $allowed_students ) ) {
				$allowed_students = [];
			}

			if ( ! $is_admin && $course_id > 0 ) {
				$has_access = in_array( $course_id, $enrolled_ids, true );
				if ( $has_access && ! empty( $allowed_students ) ) {
					$has_access = in_array( (int) $user_id, array_map( 'intval', $allowed_students ), true );
				}
			}

			$msg_count = $counts_map[ (int) $ch->id ] ?? 0;

			$result[] = [
				'id'               => (int) $ch->id,
				'name'             => $ch->name,
				'slug'             => $ch->slug,
				'description'      => $ch->description ?: '',
				'category'         => $ch->category ?: 'General',
				'course_id'        => $course_id,
				'course_name'      => $course_name,
				'allowed_students' => $allowed_students,
				'is_announcement'  => (bool) $ch->is_announcement,
				'has_access'       => $has_access,
				'messages_count'   => $msg_count,
			];
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Crear un nuevo canal (solo admin)
	 */
	public function create_community_channel( $request ) {
		global $wpdb;
		$this->init_community_tables();

		$params = $request->get_json_params();
		$raw_name = sanitize_text_field( $params['name'] ?? '' );
		$name = sanitize_title( str_replace( '#', '', $raw_name ) );
		if ( empty( $name ) ) {
			return new \WP_Error( 'invalid_name', 'El nombre del canal es requerido', [ 'status' => 400 ] );
		}

		$category = sanitize_text_field( $params['category'] ?? 'General' );
		$description = sanitize_textarea_field( $params['description'] ?? '' );
		$course_id = isset( $params['course_id'] ) ? (int) $params['course_id'] : 0;
		$is_announcement = ! empty( $params['is_announcement'] ) ? 1 : 0;

		$allowed_students = isset( $params['allowed_students'] ) && is_array( $params['allowed_students'] )
			? wp_json_encode( array_values( array_unique( array_map( 'intval', $params['allowed_students'] ) ) ) )
			: null;

		$table_channels = $wpdb->prefix . 'crezca_community_channels';
		$wpdb->insert( $table_channels, [
			'name'             => $name,
			'slug'             => $name,
			'description'      => $description,
			'category'         => $category ?: 'General',
			'course_id'        => $course_id,
			'allowed_students' => $allowed_students,
			'is_announcement'  => $is_announcement,
			'created_at'       => current_time( 'mysql' ),
		] );

		return $this->get_community_channels();
	}

	/**
	 * Editar un canal existente (solo admin)
	 */
	public function update_community_channel( $request ) {
		global $wpdb;
		$id = (int) $request['id'];
		$params = $request->get_json_params();

		$data = [];
		if ( isset( $params['name'] ) ) {
			$data['name'] = sanitize_title( str_replace( '#', '', $params['name'] ) );
			$data['slug'] = $data['name'];
		}
		if ( isset( $params['description'] ) ) {
			$data['description'] = sanitize_textarea_field( $params['description'] );
		}
		if ( isset( $params['category'] ) ) {
			$data['category'] = sanitize_text_field( $params['category'] );
		}
		if ( isset( $params['course_id'] ) ) {
			$data['course_id'] = (int) $params['course_id'];
		}
		if ( isset( $params['is_announcement'] ) ) {
			$data['is_announcement'] = ! empty( $params['is_announcement'] ) ? 1 : 0;
		}
		if ( array_key_exists( 'allowed_students', $params ) ) {
			$data['allowed_students'] = is_array( $params['allowed_students'] ) && count( $params['allowed_students'] ) > 0
				? wp_json_encode( array_values( array_unique( array_map( 'intval', $params['allowed_students'] ) ) ) )
				: null;
		}

		if ( ! empty( $data ) ) {
			$wpdb->update( $wpdb->prefix . 'crezca_community_channels', $data, [ 'id' => $id ] );
		}

		return $this->get_community_channels();
	}

	/**
	 * Eliminar un canal y sus mensajes (solo admin)
	 */
	public function delete_community_channel( $request ) {
		global $wpdb;
		$id = (int) $request['id'];

		$wpdb->delete( $wpdb->prefix . 'crezca_community_channels', [ 'id' => $id ] );
		$wpdb->delete( $wpdb->prefix . 'crezca_community_messages', [ 'channel_id' => $id ] );

		return $this->get_community_channels();
	}

	/**
	 * Obtener mensajes de un canal
	 */
	public function get_community_messages( $request ) {
		global $wpdb;
		$this->init_community_tables();

		$channel_id = (int) $request['id'];
		$table_channels = $wpdb->prefix . 'crezca_community_channels';
		$table_messages = $wpdb->prefix . 'crezca_community_messages';

		$channel = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_channels WHERE id = %d", $channel_id ) );
		if ( ! $channel ) {
			return new \WP_Error( 'not_found', 'Canal no encontrado', [ 'status' => 404 ] );
		}

		// Validar acceso del estudiante al curso si está restringido
		$user_id = get_current_user_id();
		$is_admin = current_user_can( 'administrator' );
		if ( ! $is_admin && (int) $channel->course_id > 0 ) {
			$saved = get_user_meta( $user_id, '_alezux_enabled_courses', true );
			$enrolled_ids = is_array( $saved ) ? array_map( 'intval', $saved ) : [];
			if ( function_exists( 'ld_get_mycourses' ) ) {
				$ld = ld_get_mycourses( $user_id );
				if ( ! empty( $ld ) ) {
					$enrolled_ids = array_unique( array_merge( $enrolled_ids, array_map( 'intval', $ld ) ) );
				}
			}
			if ( ! in_array( (int) $channel->course_id, $enrolled_ids, true ) ) {
				return new \WP_Error( 'forbidden', 'No tienes acceso a este canal exclusivo.', [ 'status' => 403 ] );
			}
			if ( ! empty( $channel->allowed_students ) ) {
				$allowed_list = json_decode( $channel->allowed_students, true );
				if ( is_array( $allowed_list ) && count( $allowed_list ) > 0 && ! in_array( (int) $user_id, array_map( 'intval', $allowed_list ), true ) ) {
					return new \WP_Error( 'forbidden', 'No tienes acceso a este canal privado.', [ 'status' => 403 ] );
				}
			}
		}

		$messages = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $table_messages WHERE channel_id = %d ORDER BY id ASC LIMIT 300",
			$channel_id
		) );

		// Contar respuestas por hilo (parent_id)
		$thread_counts_raw = $wpdb->get_results( $wpdb->prepare(
			"SELECT parent_id, COUNT(*) as cnt, MAX(created_at) as last_reply FROM $table_messages WHERE channel_id = %d AND parent_id > 0 GROUP BY parent_id",
			$channel_id
		) );
		$thread_map = [];
		if ( is_array( $thread_counts_raw ) ) {
			foreach ( $thread_counts_raw as $tr ) {
				$thread_map[ (int) $tr->parent_id ] = [
					'count'      => (int) $tr->cnt,
					'last_reply' => $tr->last_reply,
				];
			}
		}

		$formatted = [];
		foreach ( $messages as $m ) {
			$attachments = [];
			if ( ! empty( $m->attachments ) ) {
				$decoded = json_decode( $m->attachments, true );
				if ( is_array( $decoded ) ) {
					$attachments = $decoded;
				}
			}

			$msg_id = (int) $m->id;
			$parent_id = isset( $m->parent_id ) ? (int) $m->parent_id : 0;
			$thread_info = $thread_map[ $msg_id ] ?? null;

			$formatted[] = [
				'id'            => $msg_id,
				'channel_id'    => (int) $m->channel_id,
				'parent_id'     => $parent_id,
				'user_id'       => (int) $m->user_id,
				'user_name'     => $m->user_name,
				'user_avatar'   => $m->user_avatar ?: get_avatar_url( $m->user_id, [ 'size' => 64 ] ),
				'user_role'     => $m->user_role,
				'message'       => $m->message,
				'attachments'   => $attachments,
				'reply_count'   => $thread_info ? $thread_info['count'] : 0,
				'last_reply_at' => $thread_info ? $thread_info['last_reply'] : null,
				'created_at'    => $m->created_at,
			];
		}

		$course_name = '';
		if ( (int) $channel->course_id > 0 ) {
			$course_post = get_post( (int) $channel->course_id );
			$course_name = $course_post ? $course_post->post_title : "Curso #" . $channel->course_id;
		}

		return rest_ensure_response( [
			'channel'  => [
				'id'              => (int) $channel->id,
				'name'            => $channel->name,
				'slug'            => $channel->slug,
				'description'     => $channel->description ?: '',
				'category'        => $channel->category ?: 'General',
				'course_id'       => (int) $channel->course_id,
				'course_name'     => $course_name,
				'is_announcement' => (bool) $channel->is_announcement,
			],
			'messages' => $formatted,
		] );
	}

	/**
	 * Enviar un mensaje a un canal (soporta hilos con parent_id)
	 */
	public function send_community_message( $request ) {
		global $wpdb;
		$this->init_community_tables();

		$channel_id = (int) $request['id'];
		$table_channels = $wpdb->prefix . 'crezca_community_channels';
		$table_messages = $wpdb->prefix . 'crezca_community_messages';

		$channel = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_channels WHERE id = %d", $channel_id ) );
		if ( ! $channel ) {
			return new \WP_Error( 'not_found', 'Canal no encontrado', [ 'status' => 404 ] );
		}

		$user_id = get_current_user_id();
		$is_admin = current_user_can( 'administrator' );

		// Si es canal de anuncios y no es admin, prohibir publicación
		if ( (bool) $channel->is_announcement && ! $is_admin ) {
			return new \WP_Error( 'announcement_only', 'Este es un canal de anuncios. Solo administradores pueden publicar.', [ 'status' => 403 ] );
		}

		// Validar acceso del estudiante si el canal está restringido a un curso
		if ( ! $is_admin && (int) $channel->course_id > 0 ) {
			$saved = get_user_meta( $user_id, '_alezux_enabled_courses', true );
			$enrolled_ids = is_array( $saved ) ? array_map( 'intval', $saved ) : [];
			if ( function_exists( 'ld_get_mycourses' ) ) {
				$ld = ld_get_mycourses( $user_id );
				if ( ! empty( $ld ) ) {
					$enrolled_ids = array_unique( array_merge( $enrolled_ids, array_map( 'intval', $ld ) ) );
				}
			}
			if ( ! in_array( (int) $channel->course_id, $enrolled_ids, true ) ) {
				return new \WP_Error( 'forbidden', 'No tienes acceso al curso requerido para este canal.', [ 'status' => 403 ] );
			}
		}

		$params = $request->get_json_params();
		$message_text = trim( $params['message'] ?? '' );
		if ( empty( $message_text ) ) {
			return new \WP_Error( 'empty_message', 'El mensaje no puede estar vacío.', [ 'status' => 400 ] );
		}

		$parent_id = isset( $params['parent_id'] ) ? (int) $params['parent_id'] : 0;

		$user = wp_get_current_user();
		$user_name = $user && $user->ID ? ( $user->display_name ?: $user->user_login ) : 'Estudiante';
		$user_role = $is_admin ? 'admin' : ( current_user_can( 'edit_posts' ) ? 'instructor' : 'student' );
		$custom_avatar = $user && $user->ID ? get_user_meta( $user->ID, '_crezca_custom_avatar', true ) : '';
		$user_avatar = $custom_avatar ?: ( $user && $user->ID ? get_avatar_url( $user->ID, [ 'size' => 64 ] ) : '' );

		$attachments = isset( $params['attachments'] ) && is_array( $params['attachments'] ) ? wp_json_encode( $params['attachments'] ) : null;

		$wpdb->insert( $table_messages, [
			'channel_id'  => $channel_id,
			'parent_id'   => $parent_id,
			'user_id'     => $user_id ?: 1,
			'user_name'   => $user_name,
			'user_avatar' => $user_avatar,
			'user_role'   => $user_role,
			'message'     => wp_kses_post( $message_text ),
			'attachments' => $attachments,
			'created_at'  => current_time( 'mysql' ),
		] );

		$new_id = $wpdb->insert_id;

		return rest_ensure_response( [
			'id'            => (int) $new_id,
			'channel_id'    => $channel_id,
			'parent_id'     => $parent_id,
			'user_id'       => $user_id ?: 1,
			'user_name'     => $user_name,
			'user_avatar'   => $user_avatar,
			'user_role'     => $user_role,
			'message'       => wp_kses_post( $message_text ),
			'attachments'   => $params['attachments'] ?? [],
			'reply_count'   => 0,
			'last_reply_at' => null,
			'created_at'    => current_time( 'mysql' ),
		] );
	}

	/**
	 * Borrar un mensaje
	 */
	public function delete_community_message( $request ) {
		global $wpdb;
		$message_id = (int) $request['id'];
		$table_messages = $wpdb->prefix . 'crezca_community_messages';

		$msg = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_messages WHERE id = %d", $message_id ) );
		if ( ! $msg ) {
			return new \WP_Error( 'not_found', 'Mensaje no encontrado', [ 'status' => 404 ] );
		}

		$user_id = get_current_user_id();
		$is_admin = current_user_can( 'administrator' );

		if ( ! $is_admin && (int) $msg->user_id !== $user_id ) {
			return new \WP_Error( 'forbidden', 'No tienes permiso para borrar este mensaje.', [ 'status' => 403 ] );
		}

		$wpdb->delete( $table_messages, [ 'id' => $message_id ] );
		return rest_ensure_response( [ 'success' => true, 'deleted_id' => $message_id ] );
	}

	/**
	 * Obtener eventos y clases en vivo de la academia
	 */
	public function get_academy_events() {
		$initialized = get_option( '_alezux_academy_events_initialized', false );
		$events = get_option( '_alezux_academy_events', null );

		// Solo sembrar valores por defecto en la primera instalación si nunca se han configurado o inicializado
		if ( ! $initialized && $events === null ) {
			$events = [
				[
					'id'                => 'evt-1',
					'title'             => 'Mentoría Grupal en Vivo: Q&A y Análisis de Casos',
					'description'       => 'Sesión en tiempo real con docentes para resolver dudas, revisar avances y dar feedback personalizado.',
					'instructor'        => 'Equipo Docente Crezca',
					'instructor_avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80',
					'date'              => date( 'Y-m-d', strtotime( '+3 days' ) ),
					'time'              => '19:00',
					'timezone'          => 'GMT-5',
					'type'              => 'live',
					'meeting_url'       => 'https://zoom.us',
					'badge'             => 'En Vivo',
					'is_active'         => true,
				],
				[
					'id'                => 'evt-2',
					'title'             => 'Taller Práctico: Implementación y Proyectos Reales',
					'description'       => 'Laboratorio intensivo guiado para construir proyectos aplicados de los módulos principales.',
					'instructor'        => 'Docente Titular',
					'instructor_avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&auto=format&fit=crop&q=80',
					'date'              => date( 'Y-m-d', strtotime( '+6 days' ) ),
					'time'              => '18:30',
					'timezone'          => 'GMT-5',
					'type'              => 'workshop',
					'meeting_url'       => 'https://zoom.us',
					'badge'             => 'Taller Práctico',
					'is_active'         => true,
				],
			];
			update_option( '_alezux_academy_events', $events );
			update_option( '_alezux_academy_events_initialized', '1' );
		}

		if ( ! is_array( $events ) ) {
			$events = [];
		}

		return rest_ensure_response( [
			'success' => true,
			'events'  => $events,
		] );
	}

	/**
	 * Guardar eventos de la academia (lista completa o evento individual)
	 */
	public function save_academy_events( $request ) {
		$params = $request->get_json_params();
		if ( empty( $params ) || ! is_array( $params ) ) {
			$params = $request->get_params();
		}

		$current_events = get_option( '_alezux_academy_events', [] );
		if ( ! is_array( $current_events ) ) {
			$current_events = [];
		}

		// Opción A: Guardar/actualizar evento individual
		if ( isset( $params['event'] ) && is_array( $params['event'] ) ) {
			$evt = $params['event'];
			$event_id = ! empty( $evt['id'] ) ? sanitize_text_field( $evt['id'] ) : ( 'evt-' . time() . '-' . wp_rand( 100, 999 ) );
			$cleaned_evt = [
				'id'                => $event_id,
				'title'             => isset( $evt['title'] ) ? sanitize_text_field( $evt['title'] ) : '',
				'description'       => isset( $evt['description'] ) ? sanitize_textarea_field( $evt['description'] ) : '',
				'instructor'        => isset( $evt['instructor'] ) ? sanitize_text_field( $evt['instructor'] ) : '',
				'instructor_avatar' => isset( $evt['instructor_avatar'] ) ? esc_url_raw( $evt['instructor_avatar'] ) : '',
				'date'              => isset( $evt['date'] ) ? sanitize_text_field( $evt['date'] ) : date( 'Y-m-d' ),
				'time'              => isset( $evt['time'] ) ? sanitize_text_field( $evt['time'] ) : '19:00',
				'timezone'          => isset( $evt['timezone'] ) ? sanitize_text_field( $evt['timezone'] ) : 'GMT-5',
				'type'              => isset( $evt['type'] ) ? sanitize_text_field( $evt['type'] ) : 'live',
				'meeting_url'       => isset( $evt['meeting_url'] ) ? esc_url_raw( $evt['meeting_url'] ) : '',
				'badge'             => isset( $evt['badge'] ) ? sanitize_text_field( $evt['badge'] ) : 'En Vivo',
				'is_active'         => isset( $evt['is_active'] ) ? (bool) $evt['is_active'] : true,
			];

			$found = false;
			foreach ( $current_events as $idx => $existing ) {
			if ( isset( $existing['id'] ) && (string) $existing['id'] === (string) $event_id ) {
				$current_events[ $idx ] = $cleaned_evt;
				$found = true;
				break;
			}
		}
		if ( ! $found ) {
			array_unshift( $current_events, $cleaned_evt );
		}

			update_option( '_alezux_academy_events', $current_events );
			update_option( '_alezux_academy_events_initialized', '1' );

			return rest_ensure_response( [
				'success' => true,
				'event'   => $cleaned_evt,
				'events'  => $current_events,
				'message' => 'Evento guardado correctamente.',
			] );
		}

		// Opción B: Guardar lista de eventos
		$events = isset( $params['events'] ) && is_array( $params['events'] ) ? $params['events'] : [];
		$clean_events = [];
		foreach ( $events as $evt ) {
			if ( ! is_array( $evt ) ) {
				continue;
			}
			$clean_events[] = [
				'id'                => ! empty( $evt['id'] ) ? sanitize_text_field( $evt['id'] ) : ( 'evt-' . time() . '-' . wp_rand( 100, 999 ) ),
				'title'             => isset( $evt['title'] ) ? sanitize_text_field( $evt['title'] ) : '',
				'description'       => isset( $evt['description'] ) ? sanitize_textarea_field( $evt['description'] ) : '',
				'instructor'        => isset( $evt['instructor'] ) ? sanitize_text_field( $evt['instructor'] ) : '',
				'instructor_avatar' => isset( $evt['instructor_avatar'] ) ? esc_url_raw( $evt['instructor_avatar'] ) : '',
				'date'              => isset( $evt['date'] ) ? sanitize_text_field( $evt['date'] ) : date( 'Y-m-d' ),
				'time'              => isset( $evt['time'] ) ? sanitize_text_field( $evt['time'] ) : '19:00',
				'timezone'          => isset( $evt['timezone'] ) ? sanitize_text_field( $evt['timezone'] ) : 'GMT-5',
				'type'              => isset( $evt['type'] ) ? sanitize_text_field( $evt['type'] ) : 'live',
				'meeting_url'       => isset( $evt['meeting_url'] ) ? esc_url_raw( $evt['meeting_url'] ) : '',
				'badge'             => isset( $evt['badge'] ) ? sanitize_text_field( $evt['badge'] ) : 'En Vivo',
				'is_active'         => isset( $evt['is_active'] ) ? (bool) $evt['is_active'] : true,
			];
		}

		update_option( '_alezux_academy_events', $clean_events );
		update_option( '_alezux_academy_events_initialized', '1' );

		return rest_ensure_response( [
			'success' => true,
			'events'  => $clean_events,
			'message' => 'Eventos de la academia guardados correctamente.',
		] );
	}

	/**
	 * Eliminar evento de la academia
	 */
	public function delete_academy_event( $request ) {
		$id = (string) $request->get_param( 'id' );
		$events = get_option( '_alezux_academy_events', [] );
		if ( ! is_array( $events ) ) {
			$events = [];
		}

		$filtered = array_values( array_filter( $events, function( $evt ) use ( $id ) {
			return isset( $evt['id'] ) && (string) $evt['id'] !== $id;
		} ) );

		update_option( '_alezux_academy_events', $filtered );
		update_option( '_alezux_academy_events_initialized', '1' );

		return rest_ensure_response( [
			'success' => true,
			'events'  => $filtered,
			'message' => 'Evento eliminado correctamente de la academia.',
		] );
	}
}


