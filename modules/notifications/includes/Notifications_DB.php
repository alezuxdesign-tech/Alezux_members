<?php
namespace Alezux_Members\Modules\Notifications\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Notifications_DB {

	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'alezux_notifications';
	}

	public static function create_table() {
		global $wpdb;
		$table_name = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			title text NOT NULL,
			message text NOT NULL,
			link text DEFAULT NULL,
			avatar_url text DEFAULT NULL,
			type varchar(50) DEFAULT 'system',
			is_read tinyint(1) DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public static function insert( $user_id, $title, $message, $link = null, $avatar_url = null, $type = 'system' ) {
		global $wpdb;
		$table_name = self::get_table_name();

		return $wpdb->insert(
			$table_name,
			[
				'user_id'    => $user_id,
				'title'      => $title,
				'message'    => $message,
				'link'       => $link,
				'avatar_url' => $avatar_url,
				'type'       => $type,
				'is_read'    => 0,
				'created_at' => current_time( 'mysql' ),
			],
			[ '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s' ]
		);
	}

	public static function get_unread_count( $user_id ) {
		global $wpdb;
		$table_name = self::get_table_name();
		return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND is_read = 0", $user_id ) );
	}

	public static function get_user_notifications( $user_id, $limit = 20, $offset = 0, $only_unread = true ) {
		global $wpdb;
		$table_name = self::get_table_name();
		
		$where_clause = "WHERE user_id = %d";
		if ( $only_unread ) {
			$where_clause .= " AND is_read = 0";
		}
		
		return $wpdb->get_results( $wpdb->prepare( 
			"SELECT * FROM $table_name $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d", 
			$user_id, $limit, $offset 
		) );
	}

	public static function mark_as_read( $notification_id, $user_id ) {
		global $wpdb;
		$table_name = self::get_table_name();
		return $wpdb->update( 
			$table_name, 
			[ 'is_read' => 1 ], 
			[ 'id' => $notification_id, 'user_id' => $user_id ], 
			[ '%d' ], 
			[ '%d', '%d' ] 
		);
	}

	public static function mark_all_as_read( $user_id ) {
		global $wpdb;
		$table_name = self::get_table_name();
		return $wpdb->update( 
			$table_name, 
			[ 'is_read' => 1 ], 
			[ 'user_id' => $user_id, 'is_read' => 0 ], 
			[ '%d' ], 
			[ '%d', '%d' ] 
		);
	}
}
