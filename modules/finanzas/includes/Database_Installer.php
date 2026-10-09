<?php
namespace Alezux_Members\Modules\Finanzas\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Database_Installer {

	const DB_VERSION = '1.3';
	const OPTION_NAME = 'alezux_finanzas_db_version';

	public static function install() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$table_plans = $wpdb->prefix . 'alezux_finanzas_plans';
		$table_subs = $wpdb->prefix . 'alezux_finanzas_subscriptions';
		$table_trans = $wpdb->prefix . 'alezux_finanzas_transactions';

		$sql_plans = "CREATE TABLE $table_plans (
			id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			name VARCHAR(255) NOT NULL,
			course_id BIGINT(20) NOT NULL,
			stripe_product_id VARCHAR(255),
			stripe_price_id VARCHAR(255),
			total_quotas INT(5) DEFAULT 1,
			quota_amount DECIMAL(10,2),
			frequency VARCHAR(50) DEFAULT 'month',
            token VARCHAR(64),
			whatsapp_number VARCHAR(255),
			access_rules LONGTEXT,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP
		) $charset_collate;";

        // Update 1.2: Add token column if not exists
        $row_check = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '$table_plans' AND COLUMN_NAME = 'token'" );
        if ( empty( $row_check ) ) {
            $wpdb->query( "ALTER TABLE $table_plans ADD COLUMN token VARCHAR(64)" );
        }

        // Update 1.3: Add whatsapp_number column if not exists
        $row_check_wa = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '$table_plans' AND COLUMN_NAME = 'whatsapp_number'" );
        if ( empty( $row_check_wa ) ) {
            $wpdb->query( "ALTER TABLE $table_plans ADD COLUMN whatsapp_number VARCHAR(255)" );
        } else {
            // Modify to 255 if it was 50
            $wpdb->query( "ALTER TABLE $table_plans MODIFY COLUMN whatsapp_number VARCHAR(255)" );
        }

        // Backfill tokens for existing plans
        $existing_plans = $wpdb->get_results( "SELECT id FROM $table_plans WHERE token IS NULL OR token = ''" );
        if ( ! empty( $existing_plans ) ) {
            foreach ( $existing_plans as $plan ) {
                $token = bin2hex( random_bytes( 16 ) );
                $wpdb->update( $table_plans, ['token' => $token], ['id' => $plan->id] );
            }
        }

		$sql_subs = "CREATE TABLE $table_subs (
			id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			user_id BIGINT(20) NOT NULL,
			plan_id BIGINT(20) NOT NULL,
			stripe_subscription_id VARCHAR(255),
			stripe_customer_id VARCHAR(255),
			status VARCHAR(50) DEFAULT 'pending',
			quotas_paid INT(5) DEFAULT 0,
			last_payment_date DATETIME,
			next_payment_date DATETIME,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP
		) $charset_collate;";

		$sql_trans = "CREATE TABLE $table_trans (
			id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			user_id BIGINT(20) NOT NULL,
			subscription_id BIGINT(20),
			plan_id BIGINT(20),
			amount DECIMAL(10,2),
			currency VARCHAR(10) DEFAULT 'USD',
			method VARCHAR(50),
			transaction_ref VARCHAR(255),
			status VARCHAR(50),
			data LONGTEXT,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_plans );
		dbDelta( $sql_subs );
		dbDelta( $sql_trans );


		// Configurar Keys iniciales si no existen (Seed)
        if ( ! get_option( 'alezux_stripe_public_key' ) ) {
            update_option( 'alezux_stripe_public_key', '' );
        }
        if ( ! get_option( 'alezux_stripe_secret_key' ) ) {
            update_option( 'alezux_stripe_secret_key', '' );
        }

		update_option( self::OPTION_NAME, self::DB_VERSION );
	}

	public static function check_updates() {
		if ( get_option( self::OPTION_NAME ) !== self::DB_VERSION ) {
			self::install();
		}
	}
}
