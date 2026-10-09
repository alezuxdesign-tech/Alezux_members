<?php
namespace Alezux_Members\Modules\Listing;

use Alezux_Members\Core\Module_Base;
use Alezux_Members\Modules\Notifications\Notifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Listing extends Module_Base {

	public function init() {
		// Crear tabla de base de datos si no existe
		$this->maybe_create_table();

		// Registrar Categoría de Elementor (si es necesario) o usar existentes
		// add_action( 'elementor/elements/categories_registered', [ $this, 'register_elementor_category' ] );

		// Registrar Widgets de Elementor
		add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widgets' ] );

		// AJAX para creación y edición de tareas (Admin)
		add_action( 'wp_ajax_alezux_listing_add_task', [ $this, 'ajax_add_task' ] );
		add_action( 'wp_ajax_alezux_listing_edit_task', [ $this, 'ajax_edit_task' ] );
		add_action( 'wp_ajax_alezux_listing_delete_task', [ $this, 'ajax_delete_task' ] );
		
		// AJAX para ver tareas creadas (Admin)
		add_action( 'wp_ajax_alezux_listing_get_tasks_admin', [ $this, 'ajax_get_tasks_admin' ] );

		// AJAX para usuarios (Completar/ver tareas)
		add_action( 'wp_ajax_alezux_listing_complete_task', [ $this, 'ajax_complete_task' ] );
		add_action( 'wp_ajax_alezux_listing_get_tasks_user', [ $this, 'ajax_get_tasks_user' ] );

		// Registrar scripts y estilos
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ] );
	}

	public function register_elementor_widgets( $widgets_manager ) {
		require_once __DIR__ . '/widgets/Listing_Admin_Widget.php';
		require_once __DIR__ . '/widgets/Listing_User_Widget.php';

		$widgets_manager->register( new \Alezux_Members\Modules\Listing\Widgets\Listing_Admin_Widget() );
		$widgets_manager->register( new \Alezux_Members\Modules\Listing\Widgets\Listing_User_Widget() );
	}

	public function register_assets() {
		// Estilos Admin
		wp_register_style( 'alezux-listing-admin-css', $this->get_asset_url( 'assets/css/listing-admin.css' ), [], time() );
		// Scripts Admin
		wp_register_script( 'alezux-listing-admin-js', $this->get_asset_url( 'assets/js/listing-admin.js' ), [ 'jquery' ], time(), true );
		wp_localize_script( 'alezux-listing-admin-js', 'alezux_listing_vars', [ 'ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'alezux_listing_nonce' ) ] );

		// Estilos User
		wp_register_style( 'alezux-listing-user-css', $this->get_asset_url( 'assets/css/listing-user.css' ), [], time() );
		// Scripts User
		wp_register_script( 'alezux-listing-user-js', $this->get_asset_url( 'assets/js/listing-user.js' ), [ 'jquery' ], time(), true );
		wp_localize_script( 'alezux-listing-user-js', 'alezux_listing_vars', [ 'ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'alezux_listing_nonce' ) ] );
	}

	private function maybe_create_table() {
		global $wpdb;
		$tasks_table = $wpdb->prefix . 'alezux_listing_tasks';
		$user_tasks_table = $wpdb->prefix . 'alezux_listing_user_tasks';
		
		$installed_ver = get_option( 'alezux_listing_db_version' );
		$version = '1.0.0';

		if ( $installed_ver !== $version ) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $tasks_table (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				title varchar(255) NOT NULL,
				description text DEFAULT NULL,
				created_by bigint(20) NOT NULL,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;";

			$sql2 = "CREATE TABLE $user_tasks_table (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				task_id mediumint(9) NOT NULL,
				user_id bigint(20) NOT NULL,
				status varchar(50) DEFAULT 'completed',
				completed_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				UNIQUE KEY task_user (task_id, user_id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			dbDelta( $sql2 );

			update_option( 'alezux_listing_db_version', $version );
		}
	}

	public function ajax_add_task() {
		check_ajax_referer( 'alezux_listing_nonce', 'nonce' );

		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( [ 'message' => 'No tienes permisos.' ] );
		}

		$title = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '';

		if ( empty( $title ) ) {
			wp_send_json_error( [ 'message' => 'El título de la tarea es obligatorio.' ] );
		}

		global $wpdb;
		$tasks_table = $wpdb->prefix . 'alezux_listing_tasks';

		$result = $wpdb->insert(
			$tasks_table,
			[
				'title'       => $title,
				'description' => $description,
				'created_by'  => get_current_user_id()
			],
			[ '%s', '%s', '%d' ]
		);

		if ( $result ) {
			wp_send_json_success( [ 'message' => 'Tarea creada exitosamente.' ] );
		} else {
			wp_send_json_error( [ 'message' => 'Error al guardar la tarea en la base de datos.' ] );
		}
	}

	public function ajax_edit_task() {
		check_ajax_referer( 'alezux_listing_nonce', 'nonce' );

		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( [ 'message' => 'No tienes permisos.' ] );
		}

		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$title = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '';

		if ( ! $id || empty( $title ) ) {
			wp_send_json_error( [ 'message' => 'ID de tarea y título son obligatorios.' ] );
		}

		global $wpdb;
		$tasks_table = $wpdb->prefix . 'alezux_listing_tasks';

		$updated = $wpdb->update(
			$tasks_table,
			[
				'title'       => $title,
				'description' => $description
			],
			[ 'id' => $id ],
			[ '%s', '%s' ],
			[ '%d' ]
		);

		if ( $updated !== false ) {
			wp_send_json_success( [ 'message' => 'Tarea actualizada exitosamente.' ] );
		} else {
			wp_send_json_error( [ 'message' => 'Error al actualizar la tarea en la base de datos.' ] );
		}
	}

	public function ajax_delete_task() {
		check_ajax_referer( 'alezux_listing_nonce', 'nonce' );

		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( [ 'message' => 'No tienes permisos.' ] );
		}

		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( [ 'message' => 'ID de tarea inválido.' ] );
		}

		global $wpdb;
		$tasks_table = $wpdb->prefix . 'alezux_listing_tasks';
		// Opcional: Eliminar en cascada los registros de usuarios que completaron la tarea
		$user_tasks_table = $wpdb->prefix . 'alezux_listing_user_tasks';

		$deleted = $wpdb->delete( $tasks_table, [ 'id' => $id ], [ '%d' ] );

		if ( $deleted ) {
			$wpdb->delete( $user_tasks_table, [ 'task_id' => $id ], [ '%d' ] );
			wp_send_json_success( [ 'message' => 'Tarea eliminada exitosamente.' ] );
		} else {
			wp_send_json_error( [ 'message' => 'No se pudo eliminar la tarea.' ] );
		}
	}

	public function ajax_get_tasks_admin() {
		check_ajax_referer( 'alezux_listing_nonce', 'nonce' );

		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( [ 'message' => 'No tienes permisos.' ] );
		}

		global $wpdb;
		$tasks_table = $wpdb->prefix . 'alezux_listing_tasks';
		
		$tasks = $wpdb->get_results( "SELECT * FROM $tasks_table ORDER BY created_at DESC" );
		
		$user_tasks_table = $wpdb->prefix . 'alezux_listing_user_tasks';
		$users_table = $wpdb->users;

		// Opcional: Formatear fecha
		foreach( $tasks as $task ) {
			$task->formatted_date = date_i18n( get_option( 'date_format' ), strtotime( $task->created_at ) );
			
			// Usuarios que completaron esta tarea
			$completed_sql = $wpdb->prepare( "
				SELECT u.display_name, u.user_email 
				FROM $user_tasks_table ut
				INNER JOIN $users_table u ON ut.user_id = u.ID
				WHERE ut.task_id = %d AND ut.status = 'completed'
				ORDER BY ut.completed_at DESC
			", $task->id );
			
			$task->completed_by = $wpdb->get_results( $completed_sql );
		}

		wp_send_json_success( $tasks );
	}
	
	public function ajax_complete_task() {
		check_ajax_referer( 'alezux_listing_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( [ 'message' => 'Debes iniciar sesión.' ] );
		}

		$task_id = isset( $_POST['task_id'] ) ? intval( $_POST['task_id'] ) : 0;

		if ( ! $task_id ) {
			wp_send_json_error( [ 'message' => 'ID de tarea inválido.' ] );
		}

		$user_id = get_current_user_id();
		global $wpdb;
		$user_tasks_table = $wpdb->prefix . 'alezux_listing_user_tasks';

		$result = $wpdb->insert(
			$user_tasks_table,
			[
				'task_id' => $task_id,
				'user_id' => $user_id,
				'status'  => 'completed'
			],
			[ '%d', '%d', '%s' ]
		);

		if ( $result ) {
			// Notificar al admin por correo o modulo notification
			$this->notify_admin_task_completed( $task_id, $user_id );
			wp_send_json_success( [ 'message' => '¡Tarea completada!' ] );
		} else {
			wp_send_json_error( [ 'message' => 'Error al completar la tarea (posiblemente ya la completaste).' ] );
		}
	}

	public function ajax_get_tasks_user() {
		check_ajax_referer( 'alezux_listing_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( [ 'message' => 'Debes iniciar sesión.' ] );
		}

		$user_id = get_current_user_id();
		global $wpdb;
		$tasks_table = $wpdb->prefix . 'alezux_listing_tasks';
		$user_tasks_table = $wpdb->prefix . 'alezux_listing_user_tasks';

		// Obtener TODAS las tareas y marcar las que el usuario ya completó
		$sql = $wpdb->prepare( "
			SELECT t.*, 
                   CASE WHEN ut.id IS NOT NULL THEN 'completed' ELSE 'pending' END as user_status
			FROM $tasks_table t
			LEFT JOIN $user_tasks_table ut ON t.id = ut.task_id AND ut.user_id = %d
			ORDER BY t.created_at DESC
		", $user_id );

		$tasks = $wpdb->get_results( $sql );

		wp_send_json_success( $tasks );
	}

	private function notify_admin_task_completed( $task_id, $user_id ) {
		global $wpdb;
		$tasks_table = $wpdb->prefix . 'alezux_listing_tasks';
		$task = $wpdb->get_row( $wpdb->prepare( "SELECT title FROM $tasks_table WHERE id = %d", $task_id ) );
		$user_info = get_userdata( $user_id );

		if ( ! $task || ! $user_info ) return;

		// 1. Notificación por Correo (Siempre)
		$admin_email = get_option( 'admin_email' );
		$subject = 'Nueva Tarea Completada por: ' . $user_info->display_name;
		$message = "El usuario {$user_info->display_name} ({$user_info->user_email}) ha completado la tarea: \"{$task->title}\".\n\nSaludos,\nAlezux Members.";

		wp_mail( $admin_email, $subject, $message );

		// 2. Integración con Módulo de Notificaciones Local (Opcional)
		if ( class_exists( '\Alezux_Members\Modules\Notifications\Notifications' ) ) {
			\Alezux_Members\Modules\Notifications\Notifications::add_notification( 
				'Tarea Completada', 
				"{$user_info->display_name} completó: " . wp_trim_words( $task->title, 5 ), 
				'#', 
				'', 
				[ 1 ] // Asumimos notificar al Admin principal con ID 1
			);
		}
	}

}
