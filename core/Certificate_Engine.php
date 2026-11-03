<?php
namespace Alezux_Members\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Certificate_Engine
 * Motor de Generación, Emisión y Verificación Pública de Certificados Oficiales.
 */
class Certificate_Engine {

	private static $instance = null;

	const TABLE_NAME     = 'crezca_certificates';
	const OPTION_SETTINGS = 'crezca_certificate_settings';

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init() {
		add_action( 'init', [ $this, 'maybe_create_tables' ], 25 );
		add_action( 'template_redirect', [ $this, 'handle_public_verification' ], 1 );
	}

	/**
	 * Asegurar existencia de la tabla de certificados en la base de datos
	 */
	public function maybe_create_tables() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		$installed_ver = get_option( 'crezca_certificates_db_ver', '0' );
		if ( '1.0.0' !== $installed_ver || $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				cert_code varchar(64) NOT NULL,
				user_id bigint(20) unsigned NOT NULL,
				course_id bigint(20) unsigned NOT NULL,
				student_name varchar(255) NOT NULL,
				course_title varchar(255) NOT NULL,
				instructor_name varchar(255) DEFAULT '',
				instructor_signature text DEFAULT '',
				issued_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				UNIQUE KEY cert_code (cert_code),
				KEY user_course (user_id, course_id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			update_option( 'crezca_certificates_db_ver', '1.0.0' );
		}
	}

	/**
	 * Obtener la configuración general de certificados
	 */
	public static function get_settings() {
		$defaults = [
			'academy_title'        => get_option( 'crezca_academy_name', get_bloginfo( 'name' ) ),
			'issuer_name'          => 'Dirección Académica',
			'issuer_role'          => 'Instructor Principal & Fundador',
			'signature_image_url'  => '',
			'seal_image_url'       => '',
			'primary_color'        => '#7747ff',
			'auto_issue_on_finish' => true,
		];

		$saved = get_option( self::OPTION_SETTINGS, [] );
		return wp_parse_args( is_array( $saved ) ? $saved : [], $defaults );
	}

	/**
	 * Guardar la configuración de certificados
	 */
	public static function save_settings( $data ) {
		$current = self::get_settings();

		if ( isset( $data['academy_title'] ) ) {
			$current['academy_title'] = sanitize_text_field( $data['academy_title'] );
		}
		if ( isset( $data['issuer_name'] ) ) {
			$current['issuer_name'] = sanitize_text_field( $data['issuer_name'] );
		}
		if ( isset( $data['issuer_role'] ) ) {
			$current['issuer_role'] = sanitize_text_field( $data['issuer_role'] );
		}
		if ( isset( $data['signature_image_url'] ) ) {
			$current['signature_image_url'] = esc_url_raw( $data['signature_image_url'] );
		}
		if ( isset( $data['seal_image_url'] ) ) {
			$current['seal_image_url'] = esc_url_raw( $data['seal_image_url'] );
		}
		if ( isset( $data['primary_color'] ) ) {
			$current['primary_color'] = sanitize_hex_color( $data['primary_color'] ) ?: '#7747ff';
		}
		if ( isset( $data['auto_issue_on_finish'] ) ) {
			$current['auto_issue_on_finish'] = (bool) $data['auto_issue_on_finish'];
		}

		update_option( self::OPTION_SETTINGS, $current );
		return $current;
	}

	/**
	 * Generar un código único de certificado (ej: CRZ-CERT-A8B9C1)
	 */
	public static function generate_cert_code() {
		$characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
		$random = '';
		for ( $i = 0; $i < 8; $i++ ) {
			$random .= $characters[ wp_rand( 0, strlen( $characters ) - 1 ) ];
		}
		return 'CRZ-CERT-' . substr( $random, 0, 4 ) . '-' . substr( $random, 4, 4 );
	}

	/**
	 * Emitir un certificado para un estudiante en un curso
	 * 
	 * @param int $user_id ID del estudiante
	 * @param int $course_id ID del curso
	 * @param bool $force Si es true, emite incluso si ya existía uno
	 */
	public static function issue_certificate( $user_id, $course_id, $force = false ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		self::get_instance()->maybe_create_tables();

		$user_id   = (int) $user_id;
		$course_id = (int) $course_id;

		if ( ! $user_id || ! $course_id ) {
			return new \WP_Error( 'invalid_data', 'Usuario o curso inválido para emitir certificado.' );
		}

		// Comprobar si ya existe
		if ( ! $force ) {
			$existing = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $table WHERE user_id = %d AND course_id = %d ORDER BY id DESC LIMIT 1",
				$user_id,
				$course_id
			) );

			if ( $existing ) {
				return self::format_certificate( $existing );
			}
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return new \WP_Error( 'user_not_found', 'Estudiante no encontrado en la base de datos.' );
		}

		$course_post = get_post( $course_id );
		$course_title = $course_post ? $course_post->post_title : "Curso #{$course_id}";

		$settings = self::get_settings();
		$student_name = trim( $user->display_name ?: ( $user->first_name . ' ' . $user->last_name ) );
		if ( empty( $student_name ) ) {
			$student_name = $user->user_login;
		}

		// Intentar obtener el nombre del instructor asignado al curso
		$instructor_name = $settings['issuer_name'];
		$instructor_signature = $settings['signature_image_url'];

		$course_instructors = get_post_meta( $course_id, '_crezca_course_instructors', true );
		if ( ! empty( $course_instructors ) && is_array( $course_instructors ) ) {
			$first_inst = reset( $course_instructors );
			if ( ! empty( $first_inst['name'] ) ) {
				$instructor_name = $first_inst['name'];
			}
			if ( ! empty( $first_inst['signature'] ) ) {
				$instructor_signature = $first_inst['signature'];
			}
		}

		$code = self::generate_cert_code();
		$now  = current_time( 'mysql' );

		$inserted = $wpdb->insert(
			$table,
			[
				'cert_code'            => $code,
				'user_id'              => $user_id,
				'course_id'            => $course_id,
				'student_name'         => $student_name,
				'course_title'         => $course_title,
				'instructor_name'      => $instructor_name,
				'instructor_signature' => $instructor_signature,
				'issued_at'            => $now,
				'created_at'           => $now,
			],
			[ '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s' ]
		);

		if ( ! $inserted ) {
			return new \WP_Error( 'db_error', 'No se pudo registrar el certificado en la base de datos.' );
		}

		$cert_id = $wpdb->insert_id;
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $cert_id ) );
		$formatted = self::format_certificate( $row );

		// 1. Notificación interna
		if ( class_exists( '\\Alezux_Members\\Modules\\Notifications\\Notifications' ) ) {
			\Alezux_Members\Modules\Notifications\Notifications::add_notification(
				"¡Certificado Desbloqueado! 🎓",
				"¡Felicidades {$student_name}! Has completado el curso '{$course_title}' y tu certificado oficial ya está disponible.",
				home_url( '/?crezca_verify_cert=' . $code ),
				'',
				[ $user_id ]
			);
		}

		// 2. Disparar Webhook Saliente si el motor está activo
		if ( class_exists( '\\Alezux_Members\\Core\\Webhook_Engine' ) ) {
			\Alezux_Members\Core\Webhook_Engine::dispatch( 'certificate.issued', [
				'cert_id'         => $cert_id,
				'cert_code'       => $code,
				'user_id'         => $user_id,
				'user_email'      => $user->user_email,
				'student_name'    => $student_name,
				'course_id'       => $course_id,
				'course_title'    => $course_title,
				'issued_at'       => $now,
				'verification_url'=> $formatted['verification_url'],
			] );
		}

		return $formatted;
	}

	/**
	 * Comprobar si un estudiante ha completado el 100% de las lecciones/temas de un curso
	 */
	public static function is_course_completed_by_user( $user_id, $course_id ) {
		$course_id = (int) $course_id;
		$user_id   = (int) $user_id;
		if ( ! $course_id || ! $user_id ) {
			return false;
		}

		$sections = get_post_meta( $course_id, '_alezux_curriculum_structure', true );
		if ( empty( $sections ) && class_exists( '\\Alezux_Members\\Core\\LMS_Engine' ) ) {
			$sections = \Alezux_Members\Core\LMS_Engine::build_curriculum_from_learndash_course( $course_id );
		}

		if ( empty( $sections ) || ! is_array( $sections ) ) {
			return false;
		}

		$all_topic_ids = [];
		foreach ( $sections as $sec ) {
			$topics = ! empty( $sec['lessons'] ) ? $sec['lessons'] : ( ! empty( $sec['topics'] ) ? $sec['topics'] : [] );
			foreach ( $topics as $top ) {
				if ( ! empty( $top['subtopics'] ) && is_array( $top['subtopics'] ) ) {
					foreach ( $top['subtopics'] as $sub ) {
						if ( isset( $sub['id'] ) ) {
							$all_topic_ids[] = (string) $sub['id'];
						}
					}
				} else {
					if ( isset( $top['id'] ) ) {
						$all_topic_ids[] = (string) $top['id'];
					}
				}
			}
		}

		if ( empty( $all_topic_ids ) ) {
			return false;
		}

		$completed_meta = get_user_meta( $user_id, '_crezca_completed_topics', true );
		if ( ! is_array( $completed_meta ) ) {
			$completed_meta = [];
		}

		$completed_count = 0;
		foreach ( $all_topic_ids as $tid ) {
			if ( in_array( (string) $tid, $completed_meta, true ) ) {
				$completed_count++;
			}
		}

		return ( $completed_count >= count( $all_topic_ids ) );
	}

	/**
	 * Evaluar finalización de curso, premiar con XP, webhook y emitir certificado
	 */
	public static function check_and_process_course_completion( $user_id, $course_id ) {
		$user_id   = (int) $user_id;
		$course_id = (int) $course_id;

		if ( ! self::is_course_completed_by_user( $user_id, $course_id ) ) {
			return false;
		}

		$completed_courses = get_user_meta( $user_id, '_crezca_completed_courses', true );
		if ( ! is_array( $completed_courses ) ) {
			$completed_courses = [];
		}

		$already_done = in_array( $course_id, $completed_courses, true );

		if ( ! $already_done ) {
			$completed_courses[] = $course_id;
			update_user_meta( $user_id, '_crezca_completed_courses', $completed_courses );

			// 1. Otorgar XP por finalización de curso (+150 XP)
			if ( class_exists( '\\Alezux_Members\\Core\\Ranking_Engine' ) ) {
				\Alezux_Members\Core\Ranking_Engine::add_xp( $user_id, 150, 'course_completed', "Completó el curso al 100%" );
			}

			// 2. Disparar Webhook 'course.completed'
			if ( class_exists( '\\Alezux_Members\\Core\\Webhook_Engine' ) ) {
				$u = get_userdata( $user_id );
				$post = get_post( $course_id );
				\Alezux_Members\Core\Webhook_Engine::dispatch( 'course.completed', [
					'user_id'      => $user_id,
					'email'        => $u ? $u->user_email : '',
					'student_name' => $u ? ( $u->display_name ?: $u->user_login ) : '',
					'course_id'    => $course_id,
					'course_title' => $post ? $post->post_title : '',
					'completed_at' => current_time( 'mysql' ),
				] );
			}
		}

		// 3. Emitir Certificado si auto_issue_on_finish está activo
		$settings = self::get_settings();
		if ( ! empty( $settings['auto_issue_on_finish'] ) ) {
			self::issue_certificate( $user_id, $course_id );
		}

		return true;
	}

	/**
	 * Obtener los certificados obtenidos por un estudiante
	 */
	public static function get_student_certificates( $user_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		self::get_instance()->maybe_create_tables();

		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $table WHERE user_id = %d ORDER BY issued_at DESC",
			$user_id
		) );

		if ( empty( $results ) ) {
			return [];
		}

		return array_map( [ __CLASS__, 'format_certificate' ], $results );
	}

	/**
	 * Obtener todos los certificados emitidos (para el Administrador)
	 */
	public static function get_all_certificates( $limit = 50, $search = '' ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		self::get_instance()->maybe_create_tables();

		$limit = max( 10, min( 200, (int) $limit ) );

		if ( ! empty( $search ) ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$results = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM $table WHERE student_name LIKE %s OR course_title LIKE %s OR cert_code LIKE %s ORDER BY issued_at DESC LIMIT %d",
				$like,
				$like,
				$like,
				$limit
			) );
		} else {
			$results = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM $table ORDER BY issued_at DESC LIMIT %d",
				$limit
			) );
		}

		if ( empty( $results ) ) {
			return [];
		}

		return array_map( [ __CLASS__, 'format_certificate' ], $results );
	}

	/**
	 * Obtener un certificado por su código único de verificación pública
	 */
	public static function get_certificate_by_code( $code ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		self::get_instance()->maybe_create_tables();

		$clean_code = sanitize_text_field( trim( $code ) );
		if ( empty( $clean_code ) ) {
			return null;
		}

		$row = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM $table WHERE cert_code = %s LIMIT 1",
			$clean_code
		) );

		if ( ! $row ) {
			return null;
		}

		return self::format_certificate( $row );
	}

	/**
	 * Formatear un objeto de certificado para la API y Vistas
	 */
	public static function format_certificate( $row ) {
		$settings = self::get_settings();
		$code     = $row->cert_code;

		$verify_url = home_url( '/?crezca_verify_cert=' . rawurlencode( $code ) );

		$issued_ts = ! empty( $row->issued_at ) ? strtotime( $row->issued_at ) : time();
		$formatted_date = date_i18n( 'd \d\e F \d\e Y', $issued_ts );

		// Enlace oficial para añadir a LinkedIn Licencias y Certificaciones
		$linkedin_url = 'https://www.linkedin.com/profile/add?startTask=CERTIFICATION_NAME' .
			'&name=' . rawurlencode( $row->course_title ) .
			'&organizationName=' . rawurlencode( $settings['academy_title'] ) .
			'&issueYear=' . date( 'Y', $issued_ts ) .
			'&issueMonth=' . date( 'n', $issued_ts ) .
			'&certUrl=' . rawurlencode( $verify_url ) .
			'&certId=' . rawurlencode( $code );

		return [
			'id'                   => (int) $row->id,
			'cert_code'            => $code,
			'user_id'              => (int) $row->user_id,
			'course_id'            => (int) $row->course_id,
			'student_name'         => $row->student_name,
			'course_title'         => $row->course_title,
			'instructor_name'      => $row->instructor_name ?: $settings['issuer_name'],
			'instructor_signature' => $row->instructor_signature ?: $settings['signature_image_url'],
			'academy_name'         => $settings['academy_title'],
			'issued_at'            => $row->issued_at,
			'formatted_date'       => $formatted_date,
			'verification_url'     => $verify_url,
			'linkedin_url'         => $linkedin_url,
		];
	}

	/**
	 * Interceptar petición si es una URL pública de verificación y renderizar vista oficial
	 */
	public function handle_public_verification() {
		if ( ! isset( $_GET['crezca_verify_cert'] ) && ! isset( $_GET['crezca_cert'] ) ) {
			return;
		}

		$code = isset( $_GET['crezca_verify_cert'] ) ? sanitize_text_field( $_GET['crezca_verify_cert'] ) : sanitize_text_field( $_GET['crezca_cert'] );
		$cert = self::get_certificate_by_code( $code );

		$settings = self::get_settings();
		$academy_name = $settings['academy_title'];
		$academy_logo = get_option( 'crezca_academy_logo', '' );

		status_header( 200 );
		header( 'Content-Type: text/html; charset=utf-8' );

		?>
		<!DOCTYPE html>
		<html lang="es">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php echo $cert ? 'Certificado Oficial Verificado - ' . esc_html( $cert['course_title'] ) : 'Certificado No Encontrado'; ?></title>
			<link rel="preconnect" href="https://fonts.googleapis.com">
			<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
			<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
			<style>
				* { box-sizing: border-box; margin: 0; padding: 0; }
				body {
					font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
					background: #090a0f;
					color: #f8fafc;
					min-height: 100vh;
					display: flex;
					flex-direction: column;
					align-items: center;
					justify-content: center;
					padding: 24px 16px;
					position: relative;
					overflow-x: hidden;
				}
				.bg-glow {
					position: absolute;
					width: 500px;
					height: 500px;
					background: radial-gradient(circle, rgba(119, 71, 255, 0.18) 0%, rgba(9, 10, 15, 0) 70%);
					top: 50%;
					left: 50%;
					transform: translate(-50%, -50%);
					pointer-events: none;
					z-index: 0;
				}
				.card {
					position: relative;
					z-index: 1;
					max-width: 640px;
					width: 100%;
					background: rgba(18, 20, 29, 0.85);
					border: 1px solid rgba(255, 255, 255, 0.1);
					backdrop-filter: blur(16px);
					border-radius: 20px;
					padding: 40px 32px;
					text-align: center;
					box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(119, 71, 255, 0.2);
				}
				.badge-verified {
					display: inline-flex;
					align-items: center;
					gap: 8px;
					padding: 8px 18px;
					border-radius: 9999px;
					background: rgba(13, 184, 121, 0.15);
					border: 1px solid rgba(13, 184, 121, 0.35);
					color: #34d399;
					font-size: 13px;
					font-weight: 700;
					text-transform: uppercase;
					letter-spacing: 0.05em;
					margin-bottom: 24px;
				}
				.badge-invalid {
					display: inline-flex;
					align-items: center;
					gap: 8px;
					padding: 8px 18px;
					border-radius: 9999px;
					background: rgba(239, 68, 68, 0.15);
					border: 1px solid rgba(239, 68, 68, 0.35);
					color: #f87171;
					font-size: 13px;
					font-weight: 700;
					text-transform: uppercase;
					letter-spacing: 0.05em;
					margin-bottom: 24px;
				}
				.cert-title {
					font-family: 'Playfair Display', Georgia, serif;
					font-size: 28px;
					font-weight: 700;
					color: #ffffff;
					margin-bottom: 12px;
					line-height: 1.25;
				}
				.cert-subtitle {
					font-size: 15px;
					color: #94a3b8;
					margin-bottom: 28px;
				}
				.cert-detail-box {
					background: rgba(255, 255, 255, 0.03);
					border: 1px solid rgba(255, 255, 255, 0.06);
					border-radius: 14px;
					padding: 24px;
					margin-bottom: 28px;
					text-align: left;
					display: flex;
					flex-direction: column;
					gap: 16px;
				}
				.detail-item {
					display: flex;
					justify-content: space-between;
					align-items: center;
					padding-bottom: 12px;
					border-bottom: 1px solid rgba(255, 255, 255, 0.05);
				}
				.detail-item:last-child {
					border-bottom: none;
					padding-bottom: 0;
				}
				.detail-label {
					font-size: 13px;
					color: #94a3b8;
					font-weight: 500;
				}
				.detail-value {
					font-size: 14px;
					color: #ffffff;
					font-weight: 600;
					text-align: right;
				}
				.detail-value.highlight {
					color: #a78bfa;
					font-family: monospace;
					font-size: 13px;
					letter-spacing: 0.04em;
				}
				.actions {
					display: flex;
					flex-direction: column;
					gap: 12px;
				}
				.btn {
					display: inline-flex;
					align-items: center;
					justify-content: center;
					gap: 8px;
					width: 100%;
					padding: 13px 20px;
					border-radius: 10px;
					font-size: 14px;
					font-weight: 600;
					text-decoration: none;
					cursor: pointer;
					transition: all 0.2s ease;
				}
				.btn-primary {
					background: #7747ff;
					color: #ffffff;
					border: none;
				}
				.btn-primary:hover {
					background: #6633ee;
					transform: translateY(-1px);
				}
				.btn-secondary {
					background: rgba(255, 255, 255, 0.06);
					color: #e2e8f0;
					border: 1px solid rgba(255, 255, 255, 0.1);
				}
				.btn-secondary:hover {
					background: rgba(255, 255, 255, 0.1);
				}
				.footer-note {
					margin-top: 24px;
					font-size: 12px;
					color: #64748b;
				}
			</style>
		</head>
		<body>
			<div class="bg-glow"></div>
			<div class="card">
				<?php if ( $cert ) : ?>
					<div class="badge-verified">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path></svg>
						Certificado Auténtico y Verificado
					</div>
					<h1 class="cert-title"><?php echo esc_html( $cert['course_title'] ); ?></h1>
					<p class="cert-subtitle">Acreditación oficial emitida por <strong><?php echo esc_html( $cert['academy_name'] ); ?></strong></p>

					<div class="cert-detail-box">
						<div class="detail-item">
							<span class="detail-label">Estudiante Titular</span>
							<span class="detail-value"><?php echo esc_html( $cert['student_name'] ); ?></span>
						</div>
						<div class="detail-item">
							<span class="detail-label">Fecha de Graduación</span>
							<span class="detail-value"><?php echo esc_html( $cert['formatted_date'] ); ?></span>
						</div>
						<div class="detail-item">
							<span class="detail-label">Instructor / Emisor</span>
							<span class="detail-value"><?php echo esc_html( $cert['instructor_name'] ); ?></span>
						</div>
						<div class="detail-item">
							<span class="detail-label">ID de Verificación Oficial</span>
							<span class="detail-value highlight"><?php echo esc_html( $cert['cert_code'] ); ?></span>
						</div>
					</div>

					<div class="actions">
						<a href="<?php echo esc_url( $cert['linkedin_url'] ); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 10.9v8.37H9.2V10.9H6.46M7.83 6.45a1.62 1.62 0 1 0 0 3.24 1.62 1.62 0 0 0 0-3.24z"/></svg>
							Añadir a mi perfil de LinkedIn
						</a>
						<a href="<?php echo esc_url( home_url() ); ?>" class="btn btn-secondary">
							Ir al Portal de la Academia
						</a>
					</div>
				<?php else : ?>
					<div class="badge-invalid">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
						Certificado No Encontrado
					</div>
					<h1 class="cert-title">Código Inválido o No Emitido</h1>
					<p class="cert-subtitle">El identificador <strong><?php echo esc_html( $code ); ?></strong> no coincide con ningún certificado emitido en esta academia.</p>
					<div class="actions">
						<a href="<?php echo esc_url( home_url() ); ?>" class="btn btn-secondary">Volver al Inicio</a>
					</div>
				<?php endif; ?>

				<div class="footer-note">
					Emitido por <?php echo esc_html( $academy_name ); ?> con tecnología Crezca LMS.
				</div>
			</div>
		</body>
		</html>
		<?php
		exit;
	}
}
