<?php
namespace Alezux_Members\Modules\Marketing;

use Alezux_Members\Core\Module_Base;
use Alezux_Members\Modules\Marketing\Includes\Email_Engine;
use Alezux_Members\Modules\Marketing\Includes\Cron_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Marketing extends Module_Base {

	private static $instance = null;
	private $email_engine;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init() {
		// Crear tabla DB si no existe
		$this->maybe_create_table();

		// Inicializar Engine
		require_once __DIR__ . '/includes/Email_Engine.php';
		$this->email_engine = new Email_Engine();

		// Inicializar Cron
		require_once __DIR__ . '/includes/Cron_Handler.php';
		new Cron_Handler();

		// Inicializar Automatizaciones (Nuevos cursos/lecciones)
		require_once __DIR__ . '/includes/Automation_Handlers.php';
		new \Alezux_Members\Modules\Marketing\Includes\Automation_Handlers();

		// Registrar Widgets Elementor
		add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widgets' ] );

		// Encolar assets admin
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		// Encolar assets frontend si el widget se usa en frontend
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		// AJAX Actions
		add_action( 'wp_ajax_alezux_marketing_get_templates', [ $this, 'ajax_get_templates' ] );
		add_action( 'wp_ajax_alezux_marketing_get_template', [ $this, 'ajax_get_template' ] );
		add_action( 'wp_ajax_alezux_marketing_save_template', [ $this, 'ajax_save_template' ] );
		add_action( 'wp_ajax_alezux_marketing_save_settings', [ $this, 'ajax_save_settings' ] );
		add_action( 'wp_ajax_alezux_marketing_get_settings', [ $this, 'ajax_get_settings' ] );
		add_action( 'wp_ajax_alezux_marketing_upload_logo', [ $this, 'ajax_upload_logo' ] ); 
		add_action( 'wp_ajax_alezux_marketing_send_test_email', [ $this, 'ajax_send_test_email' ] );
		add_action( 'wp_ajax_alezux_marketing_get_logs', [ $this, 'ajax_get_email_logs' ] );
		add_action( 'wp_ajax_alezux_marketing_resend_log', [ $this, 'ajax_resend_log' ] );
		
		// Tracking Pixel Listener
		add_action( 'init', [ $this, 'handle_tracking_pixel' ] );

	}

	public function get_engine() {
		if ( ! $this->email_engine ) {
			if ( ! class_exists( '\Alezux_Members\Modules\Marketing\Includes\Email_Engine' ) ) {
				require_once __DIR__ . '/includes/Email_Engine.php';
			}
			$this->email_engine = new \Alezux_Members\Modules\Marketing\Includes\Email_Engine();
		}
		return $this->email_engine;
	}

	public function register_elementor_widgets( $widgets_manager ) {
		require_once __DIR__ . '/widgets/Marketing_Config_Widget.php';
		$widgets_manager->register( new \Alezux_Members\Modules\Marketing\Widgets\Marketing_Config_Widget() );
	}

	public function enqueue_assets() {
		// Reusamos estilos de tabla globales. Calculamos URL relativa a modules/finanzas
		// __DIR__ = modules/marketing. Ups lvl to modules -> finanzas
		$custom_url = plugin_dir_url( dirname( __DIR__ ) . '/finanzas/dummy.php' ); 
		
		// Encolar media uploader para selector de imágenes
		wp_enqueue_media();

		wp_enqueue_style( 
			'alezux-tables-css', 
			$custom_url . 'assets/css/alezux-tables.css', 
			[], 
			'1.0.5' 
		);
		
		// Estilos propios del módulo
		wp_enqueue_style( 
			'alezux-marketing-admin-css', 
			plugin_dir_url( __FILE__ ) . 'assets/css/marketing-admin.css', 
			[], 
			time() 
		);

		// JS
		wp_register_script(
			'alezux-marketing-admin-js',
			plugin_dir_url( __FILE__ ) . 'assets/js/marketing-admin.js',
			[ 'jquery', 'elementor-frontend' ], 
			time(),
			true
		);

		wp_localize_script( 'alezux-marketing-admin-js', 'alezux_marketing_vars', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'alezux_marketing_nonce' ),
			'logo_url' => get_option( 'alezux_marketing_logo_url', '' ),
			'from_email' => get_option( 'alezux_marketing_from_email', '' ),
		] );
	}

	private function maybe_create_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'alezux_marketing_templates';
		
		$installed_ver = get_option( 'alezux_marketing_db_version' );
		$version = '1.0.1'; // Bump to force update

		if ( $installed_ver !== $version ) {
			$charset_collate = $wpdb->get_charset_collate();

			// Table: Templates
			$sql_templates = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				type varchar(50) NOT NULL UNIQUE,
				subject text NOT NULL,
				content longtext NOT NULL,
				is_active tinyint(1) DEFAULT 1,
				updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;";

			// Table: Logs
			$table_logs = $wpdb->prefix . 'alezux_marketing_logs';
			$sql_logs = "CREATE TABLE $table_logs (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				type varchar(50) NOT NULL,
				recipient_email varchar(100) NOT NULL,
				user_id bigint(20) DEFAULT 0,
				status varchar(20) DEFAULT 'sent',
				sent_at datetime DEFAULT CURRENT_TIMESTAMP,
				opened_at datetime DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY type (type),
				KEY user_id (user_id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql_templates );
			dbDelta( $sql_logs );

			update_option( 'alezux_marketing_db_version', $version );
		}
	}

	public function handle_tracking_pixel() {
		if ( isset( $_GET['alezux_track_email'] ) ) {
			$log_id = intval( $_GET['alezux_track_email'] );
			if ( $log_id > 0 ) {
				global $wpdb;
				$table = $wpdb->prefix . 'alezux_marketing_logs';
				$wpdb->query( $wpdb->prepare( 
					"UPDATE $table SET opened_at = %s WHERE id = %d AND opened_at IS NULL", 
					current_time( 'mysql' ), 
					$log_id 
				) );
			}

			// Return transparent 1x1 GIF
			header( 'Content-Type: image/gif' );
			header( 'Cache-Control: no-cache, no-store, must-revalidate' );
			echo base64_decode( 'R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==' );
			exit;
		}
	}

	// --- AJAX HANDLERS ---

	public function ajax_get_templates() {
		try {
			check_ajax_referer( 'alezux_marketing_nonce', 'nonce' );
			if ( ! current_user_can( 'administrator' ) ) throw new \Exception( 'Forbidden' );

			global $wpdb;
			$table = $wpdb->prefix . 'alezux_marketing_templates';
			
			// Ensure engine is loaded
			if ( ! $this->email_engine ) {
				require_once __DIR__ . '/includes/Email_Engine.php';
				$this->email_engine = new Email_Engine();
			}

			// 1. Obtener tipos registrados
			$registered_types = $this->email_engine->get_registered_types();
			
			// 2. Obtener configuraciones guardadas
			$saved_templates = $wpdb->get_results( "SELECT * FROM $table", OBJECT_K ); 
			
			// 3. Obtener contadores de logs (KPIs)
			$table_logs = $wpdb->prefix . 'alezux_marketing_logs';
			// Check if logs table exists to avoid errors on fresh update before DB delta runs? 
			// maybe_create_table runs on init, so it should be there.
			$log_counts = $wpdb->get_results( "SELECT type, COUNT(*) as count FROM $table_logs GROUP BY type", OBJECT_K );

			// Safety check if table missing or error
			if ( ! is_array( $saved_templates ) ) {
				$saved_templates = [];
				if ( $wpdb->last_error ) error_log( 'Alezux Marketing DB Error: ' . $wpdb->last_error );
			}
			
			// Reorganizar key por type (ya hecho con OBJECT_K)
			$saved_by_type = $saved_templates;

			// Require Default Templates for fallbacks
			require_once __DIR__ . '/includes/Default_Templates.php';

			$data = [];
			foreach ( $registered_types as $key => $info ) {
				$s = isset( $saved_by_type[$key] ) ? $saved_by_type[$key] : null;
				$sent_count = isset( $log_counts[$key] ) ? $log_counts[$key]->count : 0;
				
				$subject_display = '(Sin Asunto)';
				if ( $s ) {
					$subject_display = $s->subject;
				} else {
					// Fetch default
					$def = \Alezux_Members\Modules\Marketing\Includes\Default_Templates::get( $key );
					$subject_display = isset( $def['subject'] ) ? $def['subject'] : '(Por defecto)'; 
				}

				$data[] = [
					'type'        => $key,
					'title'       => $info['title'],
					'description' => $info['description'],
					'variables'   => $info['variables'],
					'subject'     => $subject_display,
					'is_active'   => $s ? (bool)$s->is_active : true, // Default active
					'has_custom'  => (bool)$s,
					'sent_count'  => $sent_count
				];
			}

			wp_send_json_success( $data );

		} catch ( \Exception $e ) {
			error_log( 'Alezux Marketing Error (get_templates): ' . $e->getMessage() );
			wp_send_json_error( $e->getMessage() );
		} catch ( \Error $e ) {
			error_log( 'Alezux Marketing Fatal Error (get_templates): ' . $e->getMessage() );
			wp_send_json_error( 'Server Error: ' . $e->getMessage() );
		}
	}

	public function ajax_get_template() {
		try {
			check_ajax_referer( 'alezux_marketing_nonce', 'nonce' );
			if ( ! current_user_can( 'administrator' ) ) throw new \Exception( 'Forbidden' );

			$type = sanitize_text_field( $_POST['type'] );
			
			global $wpdb;
			$table = $wpdb->prefix . 'alezux_marketing_templates';
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE type = %s", $type ) );

			if ( ! $this->email_engine ) {
				require_once __DIR__ . '/includes/Email_Engine.php';
				$this->email_engine = new Email_Engine();
			}
			$definitions = $this->email_engine->get_registered_types();
			$vars_list   = isset( $definitions[$type]['variables'] ) ? $definitions[$type]['variables'] : [];

			if ( ! $row ) {
				// Return default content if not saved yet
				require_once __DIR__ . '/includes/Default_Templates.php';
				$defaults = \Alezux_Members\Modules\Marketing\Includes\Default_Templates::get( $type );
				
				wp_send_json_success( [
					'type'    => $type,
					'subject' => $defaults['subject'],
					'content' => $defaults['content'],
					'is_active' => 1,
					'variables' => $vars_list
				] );
			} else {
				$row->variables = $vars_list;
				wp_send_json_success( $row );
			}
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	public function ajax_save_template() {
		try {
			check_ajax_referer( 'alezux_marketing_nonce', 'nonce' );
			if ( ! current_user_can( 'administrator' ) ) throw new \Exception( 'Forbidden' );

			$type    = sanitize_text_field( $_POST['type'] );
			$subject = sanitize_text_field( $_POST['subject'] );
			// Allow full HTML including <style>, <html>, etc. for email templates.
			// Since this is restricted to 'administrator', we trust the input.
			// wp_kses_post strips essential email tags.
			$content = ! empty( $_POST['content'] ) ? stripslashes( $_POST['content'] ) : ''; // Handle magic quotes if needed, though usually wp sets up environment. -> actually just $_POST is slashessed by WP? No, use wp_unslash.
			$content = wp_unslash( $content ); 
			
			$is_active = isset( $_POST['is_active'] ) ? 1 : 0;

			global $wpdb;
			$table = $wpdb->prefix . 'alezux_marketing_templates';

			// Check if exists
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE type = %s", $type ) );

			if ( $exists ) {
				$wpdb->update( $table, 
					[ 'subject' => $subject, 'content' => $content, 'is_active' => $is_active ], 
					[ 'type' => $type ] 
				);
			} else {
				$wpdb->insert( $table, 
					[ 'type' => $type, 'subject' => $subject, 'content' => $content, 'is_active' => $is_active ] 
				);
			}

			wp_send_json_success( [ 'message' => 'Plantilla guardada.' ] );
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	public function ajax_save_settings() {
		check_ajax_referer( 'alezux_marketing_nonce', 'nonce' );
		if ( ! current_user_can( 'administrator' ) ) wp_send_json_error( 'Forbidden' );

		update_option( 'alezux_marketing_from_name', sanitize_text_field( $_POST['from_name'] ) );
		update_option( 'alezux_marketing_from_email', sanitize_email( $_POST['from_email'] ) );
		update_option( 'alezux_marketing_logo_url', sanitize_url( $_POST['logo_url'] ) );

		// Nuevas opciones SMTP
		update_option( 'alezux_marketing_smtp_enabled', isset( $_POST['smtp_enabled'] ) ? '1' : '0' );
		update_option( 'alezux_marketing_smtp_host', sanitize_text_field( $_POST['smtp_host'] ) );
		update_option( 'alezux_marketing_smtp_port', intval( $_POST['smtp_port'] ) );
		update_option( 'alezux_marketing_smtp_secure', sanitize_text_field( $_POST['smtp_secure'] ) );
		update_option( 'alezux_marketing_smtp_auth', isset( $_POST['smtp_auth'] ) ? '1' : '0' );
		update_option( 'alezux_marketing_smtp_username', sanitize_text_field( $_POST['smtp_username'] ) );
		update_option( 'alezux_marketing_smtp_password', sanitize_text_field( wp_unslash( $_POST['smtp_password'] ) ) );
		update_option( 'alezux_marketing_smtp_skip_ssl', isset( $_POST['smtp_skip_ssl'] ) ? '1' : '0' );

		wp_send_json_success( [ 'message' => 'Configuración guardada.' ] );
	}

	public function ajax_get_settings() {
		check_ajax_referer( 'alezux_marketing_nonce', 'nonce' );
		if ( ! current_user_can( 'administrator' ) ) wp_send_json_error( 'Forbidden' );

		$data = [
			'from_name'  => get_option( 'alezux_marketing_from_name', get_bloginfo( 'name' ) ),
			'from_email' => get_option( 'alezux_marketing_from_email', get_bloginfo( 'admin_email' ) ),
			'logo_url'   => get_option( 'alezux_marketing_logo_url', '' ),
			
			// Nuevas opciones SMTP
			'smtp_enabled'  => get_option( 'alezux_marketing_smtp_enabled', '0' ),
			'smtp_host'     => get_option( 'alezux_marketing_smtp_host', '' ),
			'smtp_port'     => get_option( 'alezux_marketing_smtp_port', '587' ),
			'smtp_secure'   => get_option( 'alezux_marketing_smtp_secure', 'tls' ),
			'smtp_auth'     => get_option( 'alezux_marketing_smtp_auth', '1' ),
			'smtp_username' => get_option( 'alezux_marketing_smtp_username', '' ),
			'smtp_password' => get_option( 'alezux_marketing_smtp_password', '' ),
			'smtp_skip_ssl' => get_option( 'alezux_marketing_smtp_skip_ssl', '0' ),
		];

		wp_send_json_success( $data );
	}
	public function ajax_upload_logo() {
		try {
			check_ajax_referer( 'alezux_marketing_nonce', 'nonce' );
			if ( ! current_user_can( 'administrator' ) ) throw new \Exception( 'Forbidden' );

			if ( empty( $_FILES['file'] ) ) {
				throw new \Exception( 'No se ha subido ningún archivo.' );
			}

			// Load WP Media functions
			if ( ! function_exists( 'media_handle_upload' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
			}

			$attachment_id = media_handle_upload( 'file', 0 );

			if ( is_wp_error( $attachment_id ) ) {
				throw new \Exception( $attachment_id->get_error_message() );
			}

			$url = wp_get_attachment_url( $attachment_id );
			
			wp_send_json_success( [ 'url' => $url ] );

		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	public function ajax_send_test_email() {
		try {
			check_ajax_referer( 'alezux_marketing_nonce', 'nonce' );
			if ( ! current_user_can( 'administrator' ) ) throw new \Exception( 'Forbidden' );

			$type  = sanitize_text_field( $_POST['type'] );
			$email = sanitize_email( $_POST['email'] );

			if ( ! is_email( $email ) ) {
				throw new \Exception( 'Email inválido.' );
			}

			// Ensure engine is loaded
			if ( ! $this->email_engine ) {
				require_once __DIR__ . '/includes/Email_Engine.php';
				$this->email_engine = new Email_Engine();
			}

			// Mock data for test
			$test_user = wp_get_current_user();
			$dummy_data = [
				'user' => $test_user,
				'plan_name' => 'Plan de Prueba',
				'price' => '$99.00',
				'date' => date('d/m/Y'),
				'amount' => '$99.00',
				'course_name' => 'Curso Demo',
				'reset_link' => site_url('/wp-login.php?action=rp'),
				'new_password' => '****',
				'retry_url' => site_url('/checkout?retry=1'),
				'renewal_date' => date('d/m/Y', strtotime('+1 year')),
				'end_date' => date('d/m/Y'),
				'achievement_name' => 'Primeros Pasos',
				'achievement_desc' => 'Has completado tu primera lección.',
				'course_url' => site_url('/cursos/demo'),
				'days_inactive' => '5'
			];

			// Pass is_test = true to avoid logging test emails to DB
			$log_file = ALEZUX_MEMBERS_PATH . 'debug_status.txt';
			file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] MARKETING: Iniciando envío de prueba a $email (Tipo: $type)\n", FILE_APPEND );
			
			$start = microtime( true );
			$sent = $this->email_engine->send_email( $type, $email, $dummy_data, true );
			$end = microtime( true );
			$diff = round( $end - $start, 4 );

			if ( $sent ) {
				file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] MARKETING: Éxito en envío de prueba ({$diff}s)\n", FILE_APPEND );
				wp_send_json_success( [ 'message' => 'Correo de prueba enviado a ' . $email . " ({$diff}s)" ] );
			} else {
				file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] MARKETING: Fallo en envío de prueba ({$diff}s)\n", FILE_APPEND );
				
				$detailed_error = $this->email_engine->get_last_error_message();
				$error_msg = 'wp_mail devolvió false.';
				if ( ! empty( $detailed_error ) ) {
					$error_msg .= ' Detalle: ' . $detailed_error;
				} else {
					$error_msg .= ' Revisa logs del servidor o prueba activando "Omitir verificación SSL".';
				}
				throw new \Exception( $error_msg );
			}

		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	public function ajax_get_email_logs() {
		try {
			check_ajax_referer( 'alezux_marketing_nonce', 'nonce' );
			if ( ! current_user_can( 'administrator' ) ) throw new \Exception( 'Forbidden' );

			$type = sanitize_text_field( $_POST['type'] );
			global $wpdb;
			$table_logs = $wpdb->prefix . 'alezux_marketing_logs';
			
			// Get last 2000 logs for this type
			$logs = $wpdb->get_results( $wpdb->prepare( 
				"SELECT * FROM $table_logs WHERE type = %s ORDER BY sent_at DESC LIMIT 2000", 
				$type 
			) );

			if ( empty($logs) ) {
				wp_send_json_success( [] );
				return;
			}
			
			// Format for display
			$formatted = [];
			foreach($logs as $log) {
				$status_display = ucfirst( $log->status );
				if ( ! empty( $log->opened_at ) ) {
					$status_display = 'Leído';
				}

				$formatted[] = [
					'id' => $log->id,
					'recipient' => $log->recipient_email,
					'date' => date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $log->sent_at ) ),
					'status' => $status_display,
					'raw_status' => strtolower( $log->status )
				];
			}

			wp_send_json_success( $formatted );

		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	public function ajax_resend_log() {
		try {
			check_ajax_referer( 'alezux_marketing_nonce', 'nonce' );
			if ( ! current_user_can( 'administrator' ) ) throw new \Exception( 'Forbidden' );

			$log_id = intval( $_POST['log_id'] );
			global $wpdb;
			$table_logs = $wpdb->prefix . 'alezux_marketing_logs';

			$log = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_logs WHERE id = %d", $log_id ) );
			if ( ! $log ) throw new \Exception( 'Registro no encontrado.' );

			if ( ! $this->email_engine ) {
				require_once __DIR__ . '/includes/Email_Engine.php';
				$this->email_engine = new Email_Engine();
			}

			$user = get_user_by( 'email', $log->recipient_email );
			if ( ! $user ) throw new \Exception( 'Usuario no encontrado.' );

			// Variables for the template
			$data = [
				'user' => $user
			];

			// Especial case for student_welcome (we must generate a new password)
			if ( $log->type === 'student_welcome' ) {
				$password = wp_generate_password( 12, true );
				wp_set_password( $password, $user->ID );
				$data['new_password'] = $password;
			}

			// Intentar reenvío
			$sent = $this->email_engine->send_email( $log->type, $log->recipient_email, $data, false );

			if ( $sent ) {
				// Actualizar log a 'sent'
				$wpdb->update( $table_logs, [ 'status' => 'sent' ], [ 'id' => $log_id ] );
				wp_send_json_success( [ 'message' => 'Correo reenviado correctamente.' ] );
			} else {
				$error_msg = $this->email_engine->get_last_error_message();
				// Actualizar log con el nuevo error
				$wpdb->update( $table_logs, [ 'status' => 'fail: ' . mb_substr( $error_msg, 0, 150 ) ], [ 'id' => $log_id ] );
				throw new \Exception( 'Error al enviar: ' . $error_msg );
			}

		} catch ( \Exception $e ) {
			wp_send_json_error( [ 'message' => $e->getMessage() ] );
		}
	}
}
