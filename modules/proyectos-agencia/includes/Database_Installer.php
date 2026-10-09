<?php
namespace Alezux_Members\Modules\Proyectos_Agencia\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Database_Installer {

	const DB_VERSION = '1.0.0';
	const OPTION_NAME = 'alezux_projects_db_version';

	public static function check_updates() {
		if ( get_option( self::OPTION_NAME ) !== self::DB_VERSION ) {
			self::install();
		}
	}

	public static function install() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'alezux_agency_projects';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			client_id bigint(20) NOT NULL,
			manager_id bigint(20) NOT NULL,
            status varchar(50) DEFAULT 'start' NOT NULL,
            current_step varchar(50) DEFAULT 'briefing' NOT NULL,
            project_data longtext DEFAULT NULL, 
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
            KEY client_id (client_id),
            KEY manager_id (manager_id),
            KEY status (status)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( self::OPTION_NAME, self::DB_VERSION );
	}
}
