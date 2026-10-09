<?php
namespace Alezux_Members\Modules\Marketing\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Engine {

	private $last_error = null;

	public function __construct() {
		// Control de remitente y formato
		add_filter( 'wp_mail_from', [ $this, 'custom_mail_from' ] );
		add_filter( 'wp_mail_from_name', [ $this, 'custom_mail_from_name' ] );
		add_filter( 'wp_mail_content_type', [ $this, 'set_html_content_type' ] );

		// Captura de errores fatales de envío
		add_action( 'wp_mail_failed', [ $this, 'log_mail_errors' ] );

		// Configurar SMTP si está habilitado
		add_action( 'phpmailer_init', [ $this, 'configure_smtp' ] );
	}

	/**
	 * Log de errores detallado cuando wp_mail falla
	 */
	public function log_mail_errors( $wp_error ) {
		$this->last_error = $wp_error;

		$log_file = ALEZUX_MEMBERS_PATH . 'alezux-mail-errors.log';
		$timestamp = date('Y-m-d H:i:s');
		$error_msg = $wp_error->get_error_message();
		$error_data = json_encode( $wp_error->get_error_data(), JSON_PRETTY_PRINT );

		$log_entry = "[$timestamp] ERROR ENVÍO: $error_msg\nDATOS: $error_data\n" . str_repeat('-', 50) . "\n";
		file_put_contents( $log_file, $log_entry, FILE_APPEND );
	}

	/**
	 * Retorna el último mensaje de error capturado.
	 * 
	 * @return string
	 */
	public function get_last_error_message() {
		if ( is_wp_error( $this->last_error ) ) {
			return $this->last_error->get_error_message();
		}
		return '';
	}

	public function get_registered_types() {
		$types = [
			'student_welcome' => [
				'title'       => 'Registro - Bienvenida',
				'description' => 'Se envía automáticamente cuando un estudiante se registra exitosamente en la plataforma.',
				'variables'   => [ '{{user.name}}', '{{user.username}}', '{{user.email}}', '{{password}}', '{{course_title}}', '{{login_url}}', '{{site_name}}', '{{logo_url}}' ]
			],
			'user_recover_password' => [
				'title'       => 'Seguridad - Recuperar Contraseña',
				'description' => 'Se envía cuando un usuario solicita restablecer su contraseña desde el formulario de acceso.',
				'variables'   => [ '{{user.name}}', '{{reset_link}}', '{{site_name}}', '{{logo_url}}' ]
			],
			'admin_reset_password' => [
				'title'       => 'Seguridad - Reset por Admin',
				'description' => 'Se envía cuando un administrador restablece manualmente la contraseña de un usuario.',
				'variables'   => [ '{{user.name}}', '{{new_password}}', '{{password}}', '{{login_url}}', '{{site_name}}', '{{logo_url}}' ]
			],
			'payment_success' => [
				'title'       => 'Finanzas - Pago Exitoso',
				'description' => 'Se envía al usuario confirmando que su pago se ha procesado correctamente.',
				'variables'   => [ '{{user.name}}', '{{plan_name}}', '{{price}}', '{{date}}', '{{amount}}', '{{site_name}}', '{{logo_url}}' ]
			],
			'payment_failed' => [
				'title'       => 'Finanzas - Pago Fallido',
				'description' => 'Se envía cuando un intento de pago o renovación falla.',
				'variables'   => [ '{{user.name}}', '{{plan_name}}', '{{attempt_date}}', '{{retry_url}}', '{{site_name}}', '{{logo_url}}' ]
			],
			'payment_reminder' => [
				'title'       => 'Finanzas - Recordatorio Renovación',
				'description' => 'Se envía días antes de que una suscripción se renueve automáticamente.',
				'variables'   => [ '{{user.name}}', '{{plan_name}}', '{{renewal_date}}', '{{price}}', '{{site_name}}', '{{logo_url}}' ]
			],
			'subscription_cancelled' => [
				'title'       => 'Finanzas - Suscripción Cancelada',
				'description' => 'Se envía cuando una suscripción es cancelada (por el usuario o admin).',
				'variables'   => [ '{{user.name}}', '{{plan_name}}', '{{end_date}}', '{{site_name}}', '{{logo_url}}' ]
			],
			'achievement_assigned' => [
				'title'       => 'Logros - Nuevo Logro Desbloqueado',
				'description' => 'Se envía cuando un estudiante desbloquea un nuevo logro o insignia.',
				'variables'   => [ '{{user.name}}', '{{achievement_name}}', '{{achievement_desc}}', '{{site_name}}', '{{logo_url}}' ]
			],
			'course_available' => [
				'title'       => 'Cursos - Nuevo Curso Disponible',
				'description' => 'Notificación a todos los usuarios cuando se publica un nuevo curso.',
				'variables'   => [ '{{user.name}}', '{{course_name}}', '{{courses_list}}', '{{site_name}}', '{{logo_url}}' ]
			],
			'lesson_available' => [
				'title'       => 'Cursos - Nuevas Lecciones Disponibles',
				'description' => 'Notificación a los alumnos inscritos cuando se añaden lecciones a un curso.',
				'variables'   => [ '{{user.name}}', '{{course_name}}', '{{lessons_list}}', '{{site_name}}', '{{logo_url}}' ]
			],
			'inactivity_alert' => [
				'title'       => 'Retención - Alerta de Inactividad',
				'description' => 'Se envía automáticamente si el estudiante no ingresa por varios días.',
				'variables'   => [ '{{user.name}}', '{{days_inactive}}', '{{login_url}}', '{{site_name}}', '{{logo_url}}' ]
			],
		];

		return apply_filters( 'alezux_marketing_email_types', $types );
	}

	public function custom_mail_from( $original_email ) {
		$custom = get_option( 'alezux_marketing_from_email' );
		
		if ( ! empty( $custom ) && is_email( $custom ) ) {
			return $custom;
		}

		// Si no hay configurado, forzar un correo del dominio actual para evitar bloqueos
		$domain = parse_url( home_url(), PHP_URL_HOST );
		if ( $domain ) {
			// Muchos hostings requieren que el "From" sea del mismo dominio
			return 'no-reply@' . str_replace('www.', '', $domain);
		}

		return $original_email;
	}

	public function custom_mail_from_name( $original_name ) {
		$custom = get_option( 'alezux_marketing_from_name' );
		if ( ! empty( $custom ) ) {
			return $custom;
		}
		return $original_name;
	}
	
	public function set_html_content_type() {
		return 'text/html';
	}

	/**
	 * Configura PHPMailer para usar SMTP si está habilitado en los ajustes.
	 * 
	 * Si la opción 'smtp_enabled' no está activa, no hace nada y WordPress usa
	 * su envío por defecto (nativa de PHP u otro plugin de SMTP).
	 * 
	 * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer Instancia de PHPMailer
	 */
	public function configure_smtp( $phpmailer ) {
		$enabled = get_option( 'alezux_marketing_smtp_enabled', '0' );
		
		// Si no está habilitado, retornar de inmediato para usar el envío por defecto de WordPress
		if ( '1' !== $enabled ) {
			return;
		}

		$host = get_option( 'alezux_marketing_smtp_host' );
		if ( empty( $host ) ) {
			return; // Si no hay Host configurado, no hacer nada
		}

		// Configurar PHPMailer para usar SMTP
		$phpmailer->isSMTP();
		$phpmailer->Host = $host;
		$phpmailer->Port = (int) get_option( 'alezux_marketing_smtp_port', 587 );

		// Autenticación
		$auth = get_option( 'alezux_marketing_smtp_auth', '1' );
		if ( '1' === $auth ) {
			$phpmailer->SMTPAuth = true;
			$phpmailer->Username = get_option( 'alezux_marketing_smtp_username', '' );
			$phpmailer->Password = get_option( 'alezux_marketing_smtp_password', '' );
		} else {
			$phpmailer->SMTPAuth = false;
		}

		// Tipo de cifrado (Seguridad)
		$secure = get_option( 'alezux_marketing_smtp_secure', 'tls' );
		if ( 'ssl' === $secure ) {
			$phpmailer->SMTPSecure = 'ssl';
		} elseif ( 'tls' === $secure ) {
			$phpmailer->SMTPSecure = 'tls';
		} else {
			$phpmailer->SMTPSecure = '';
			// Si no hay seguridad, desactivar el AutoTLS por si acaso
			$phpmailer->SMTPAutoTLS = false;
		}

		// Omitir verificación de certificado SSL (útil en desarrollo local o servidores autofirmados)
		$skip_ssl = get_option( 'alezux_marketing_smtp_skip_ssl', '0' );
		if ( '1' === $skip_ssl ) {
			$phpmailer->SMTPOptions = [
				'ssl' => [
					'verify_peer'       => false,
					'verify_peer_name'  => false,
					'allow_self_signed' => true
				]
			];
		}
	}

	/**
	 * Main function to send emails
	 */
	public function send_email( $type, $recipient_email, $data = [], $is_test = false ) {
		if ( ! is_email( $recipient_email ) ) return false;

		// 1. Get Template (DB or Default)
		$template = $this->get_template( $type );

		// If disabled by user setting (and NOT a test email), skip.
		if ( ! $is_test && $template && isset( $template->is_active ) && '0' == $template->is_active ) {
			return false; // Email disabled by admin
		}

		// 2. Prepare Content
		$subject = $template ? $template->subject : '';
		$content = $template ? $template->content : '';

		// Fallback if no custom template found in DB
		if ( ! $template ) {
			require_once __DIR__ . '/Default_Templates.php';
			$defaults = Default_Templates::get( $type );
			
			// Allow modules to provide their own defaults
			$defaults = apply_filters( 'alezux_marketing_default_template', $defaults, $type );

			$subject = $defaults['subject'];
			$content = $defaults['content'];
		}

		// 3. Variable Replacement
		$vars = $this->prepare_variables( $data );
		
		$subject = $this->replace_vars( $subject, $vars );
		$content = $this->replace_vars( $content, $vars );

		// 4. Tracking Logic (Create log BEFORE sending to get ID)
		$log_id = 0;
		if ( ! $is_test ) {
			$log_id = $this->create_log_entry( $type, $recipient_email, $data );
			
			// Append Tracking Pixel
			if ( $log_id > 0 ) {
				$tracking_url = home_url( '/?alezux_track_email=' . $log_id );
				$pixel = '<img src="' . esc_url( $tracking_url ) . '" alt="" width="1" height="1" style="display:none !important;" />';
				$content .= "\n" . $pixel;
			}
		}

		// 5. Inject Logic (Header/Logo)
		// Headers: Add Reply-To if configured
		$headers = [];
		$from_email = get_option( 'alezux_marketing_from_email' );
		$from_name  = get_option( 'alezux_marketing_from_name' );

		if ( ! empty( $from_email ) && is_email( $from_email ) ) {
			$name_part = ! empty( $from_name ) ? $from_name : get_bloginfo( 'name' );
			$headers[] = "Reply-To: $name_part <$from_email>";
		}

		// 6. Send
		try {
			$start_time = microtime( true );
			$sent = wp_mail( $recipient_email, $subject, $content, $headers );
			$end_time = microtime( true );
			$duration = round( $end_time - $start_time, 4 );

			// Log de rendimiento si tarda más de lo esperado
			if ( $duration > 2.0 ) {
				$this->log_mail_errors( new \WP_Error( 'mail_slow', "AVISO LENTITUD: wp_mail tardó {$duration}s en responder para $recipient_email" ) );
			}
		} catch ( \Exception $e ) {
			$sent = false;
			$this->log_mail_errors( new \WP_Error( 'mail_exception', $e->getMessage() ) );
		}

		// 7. Update Log Status
		if ( ! $is_test && $log_id > 0 ) {
			$status = $sent ? 'sent' : 'failed';
			$this->update_log_status( $log_id, $status );
			
			if ( ! $sent ) {
				// Log manual en caso de que wp_mail devuelva false sin disparar el hook (raro pero posible)
				$this->log_mail_errors( new \WP_Error( 'mail_false', "wp_mail devolvió FALSE para: $recipient_email" ) );
			}
		}

		return $sent;
	}

	private function create_log_entry( $type, $recipient_email, $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'alezux_marketing_logs';
		
		$user_id = 0;
		if ( isset( $data['user'] ) && $data['user'] instanceof \WP_User ) {
			$user_id = $data['user']->ID;
		}

		$inserted = $wpdb->insert( 
			$table, 
			[ 
				'type' => $type, 
				'recipient_email' => $recipient_email, 
				'user_id' => $user_id,
				'status' => 'sending', // Initial status
				'sent_at' => current_time( 'mysql' )
			] 
		);

		if ( ! $inserted ) {
			error_log( 'Alezux Marketing DB Error (create_log_entry): ' . $wpdb->last_error );
			return 0;
		}

		return $wpdb->insert_id;
	}

	private function update_log_status( $log_id, $status ) {
		global $wpdb;
		$table = $wpdb->prefix . 'alezux_marketing_logs';
		$wpdb->update( 
			$table, 
			[ 'status' => $status ], 
			[ 'id' => $log_id ] 
		);
	}

	private function get_template( $type ) {
		global $wpdb;
		$table = $wpdb->prefix . 'alezux_marketing_templates';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE type = %s", $type ) );
	}

	private function prepare_variables( $data ) {
		$vars = [];
		
		// Global
		$logo_url = get_option( 'alezux_marketing_logo_url' );
		$vars['{{logo_url}}'] = $logo_url ? $logo_url : '';
		$vars['{{site_name}}'] = get_bloginfo( 'name' );
		$vars['{{home_url}}'] = home_url();
		$vars['{{login_url}}'] = wp_login_url(); // Or custom
		$vars['{{year}}'] = date('Y');

		// User specific
		if ( isset( $data['user'] ) && $data['user'] instanceof \WP_User ) {
			$u = $data['user'];
			$vars['{{user.name}}'] = $u->display_name; // Or first_name
			$vars['{{user.first_name}}'] = $u->first_name;
			$vars['{{user.email}}'] = $u->user_email;
			$vars['{{user.username}}'] = $u->user_login;
		}

		// Merge extra data
		foreach ( $data as $key => $val ) {
			if ( is_string( $val ) || is_numeric( $val ) ) {
				$vars['{{' . $key . '}}'] = $val;
			}
		}

		return $vars;
	}

	private function replace_vars( $text, $vars ) {
		return str_replace( array_keys( $vars ), array_values( $vars ), $text );
	}
}
