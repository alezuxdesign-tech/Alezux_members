<?php
namespace Alezux_Members\Modules\Notifications;

use Alezux_Members\Core\Module_Base;
require_once __DIR__ . '/includes/Notifications_DB.php';
use Alezux_Members\Modules\Notifications\Includes\Notifications_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Notifications extends Module_Base {

	public function init() {
		// Initialize DB if needed (ideally this should be on plugin activation, but for this modular setup we check here)
		// To avoid running dbDelta every time, we could check for an option, but for now we'll rely on dbDelta's safety or check manually.
		// For performance, let's only run it if a specific option is missing or mismatched.
		if ( get_option( 'alezux_notifications_db_version' ) !== '1.0' ) {
			Notifications_DB::create_table();
			update_option( 'alezux_notifications_db_version', '1.0' );
		}

		// Encolar assets
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		// Widget de Elementor
		add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widgets' ] );

		// AJAX Endpoints
		add_action( 'wp_ajax_alezux_get_notifications', [ $this, 'ajax_get_notifications' ] );
		add_action( 'wp_ajax_alezux_mark_read', [ $this, 'ajax_mark_read' ] );
		add_action( 'wp_ajax_alezux_mark_all_read', [ $this, 'ajax_mark_all_read' ] );

		// LearnDash Hooks
		add_action( 'transition_post_status', [ $this, 'on_course_publish' ], 10, 3 );
	}

	public function enqueue_assets() {
		// CSS
		wp_enqueue_style( 
			'alezux-notifications-css', 
			$this->get_asset_url( 'assets/css/notifications.css' ), 
			[], 
			time() 
		);

		// JS
		wp_register_script( 
			'alezux-notifications-js', 
			$this->get_asset_url( 'assets/js/notifications.js' ), 
			[ 'jquery' ], 
			time(), 
			true 
		);

		wp_localize_script( 'alezux-notifications-js', 'alezux_notifications_obj', [ // Standardizing localized object name if possible, or use module specific
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'alezux_notifications_nonce' ),
			'is_logged_in' => is_user_logged_in(),
		] );

		wp_enqueue_script( 'alezux-notifications-js' );
	}

	public function register_elementor_widgets( $widgets_manager ) {
		require_once __DIR__ . '/widgets/Notifications_Widget.php';
		$widgets_manager->register( new \Alezux_Members\Modules\Notifications\Widgets\Notifications_Widget() );
	}

	/**
	 * Helper to add a notification
	 * @param string $title
	 * @param string $message
	 * @param string $link
	 * @param string $avatar_url
	 * @param mixed $target_users 'all' or user_id or array of user_ids
	 */
	public static function add_notification( $title, $message, $link = '#', $avatar_url = '', $target_users = 'all' ) {
		if ( $target_users === 'all' ) {
			$users = get_users( [ 'fields' => 'ID' ] );
		} elseif ( is_array( $target_users ) ) {
			$users = $target_users;
		} else {
			$users = [ $target_users ];
		}

		foreach ( $users as $user_id ) {
			Notifications_DB::insert( $user_id, $title, $message, $link, $avatar_url );
		}
	}

	/**
	 * AJAX: Get Notifications
	 */
	public function ajax_get_notifications() {
		check_ajax_referer( 'alezux_notifications_nonce', 'nonce' );
		
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'User not logged in' );
		}

		$user_id = get_current_user_id();
		$notifications = Notifications_DB::get_user_notifications( $user_id, 20, 0, true );
		$unread_count = Notifications_DB::get_unread_count( $user_id );

		// Pre-process for frontend (e.g. human readable time)
		foreach ( $notifications as &$note ) {
			$note->time_ago = human_time_diff( strtotime( $note->created_at ), current_time( 'timestamp' ) ) . ' ago';
		}

		wp_send_json_success( [
			'notifications' => $notifications,
			'unread_count'  => $unread_count
		] );
	}

	/**
	 * AJAX: Mark single read
	 */
	public function ajax_mark_read() {
		check_ajax_referer( 'alezux_notifications_nonce', 'nonce' );
		$id = intval( $_POST['id'] );
		$user_id = get_current_user_id();

		Notifications_DB::mark_as_read( $id, $user_id );
		wp_send_json_success();
	}

	/**
	 * AJAX: Mark all read
	 */
	public function ajax_mark_all_read() {
		check_ajax_referer( 'alezux_notifications_nonce', 'nonce' );
		$user_id = get_current_user_id();

		Notifications_DB::mark_all_as_read( $user_id );
		wp_send_json_success();
	}

	/**
	 * Hook: New Course Published
	 * @param string $new_status
	 * @param string $old_status
	 * @param \WP_Post $post
	 */
	public function on_course_publish( $new_status, $old_status, $post ) {
		// Verify if it's a course and strictly publishing for the first time (or scheduled to publish)
		// Usually old_status != publish implies it's new to the public.
		if ( 'sfwd-courses' !== $post->post_type ) {
			return;
		}

		if ( 'publish' !== $new_status || 'publish' === $old_status ) {
			return;
		}

		// Avoid notifying if it's an autosave or revision just in case
        if ( wp_is_post_revision( $post->ID ) || wp_is_post_autosave( $post->ID ) ) {
            return;
        }

		// Prepare Notification Data
		$title = '¡Nuevo Curso Disponible!';
		$message = 'Se ha publicado el curso: ' . $post->post_title;
		$link = get_permalink( $post->ID );
		
		// Try to get course featured image
		$avatar_url = 'icon:course'; // Default to course icon
		if ( has_post_thumbnail( $post->ID ) ) {
			$avatar_url = get_the_post_thumbnail_url( $post->ID, 'thumbnail' ); 
		}

		// Send to ALL users
		self::add_notification( $title, $message, $link, $avatar_url, 'all' );
	}
}
