<?php
namespace Alezux_Members\Modules\Config\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Admin_Dashboard_Stats {

	public static function init() {
		// Hook para trackear actividad de usuario (Online Status)
		add_action( 'init', [ __CLASS__, 'track_user_activity' ] );
		
		// Hook para trackear logins fallidos
		add_action( 'wp_login_failed', [ __CLASS__, 'track_failed_login' ] );
	}

	/**
	 * Obtiene el total de estudiantes (Suscriptores + Estudiantes custom role)
	 */
	public static function get_total_students() {
		$args = [
			'role__in' => [ 'subscriber', 'student', 'customer' ], // Ajustar según roles reales
			'count_total' => true,
			'fields' => 'ID' // Optimización: solo contar IDs
		];
		$user_query = new \WP_User_Query( $args );
		return $user_query->get_total();
	}

	/**
	 * Obtiene nuevos estudiantes en el periodo dudo (month, week, today)
	 */
	public static function get_new_students( $period = 'month' ) {
		$args = [
			'role__in' => [ 'subscriber', 'student', 'customer' ],
			'date_query' => [],
			'count_total' => true,
			'fields' => 'ID'
		];

		switch ( $period ) {
			case 'week':
				$args['date_query'][] = [ 'after' => '1 week ago' ];
				break;
			case 'today':
				$args['date_query'][] = [ 'after' => 'today' ];
				break;
			case 'month':
			default:
				$args['date_query'][] = [ 'after' => '1 month ago' ];
				break;
		}

		$user_query = new \WP_User_Query( $args );
		return $user_query->get_total();
	}

	/**
	 * Trackea la última actividad del usuario logueado
	 */
	public static function track_user_activity() {
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			// Guardamos un transient simple por usuario que expira en 5 mins
			// El nombre del transient incluye el ID para ser único
			set_transient( 'alezux_user_online_' . $user_id, true, 5 * MINUTE_IN_SECONDS );
			
			// También actualizamos meta para históricos si fuera necesario, pero transient es mejor para "Online Ahora"
			update_user_meta( $user_id, 'alezux_last_activity', current_time( 'mysql' ) );
			
			// LOGIC: Reset Inactivity Email Flag
			// If user returns, we want to allow sending this email again in the future if they leave again.
			delete_user_meta( $user_id, 'alezux_inactivity_email_sent' );
		}
	}

	/**
	 * Cuenta usuarios online basado en transients activos
	 * NOTA: Esta función es aproximada y depende de la limpieza de transients de WP.
	 * Para mayor precisión con muchos usuarios, se requeriría una tabla custom, 
	 * pero para < 5000 users esto es eficiente.
	 */
	public static function get_online_users_count() {
		global $wpdb;
		// Buscamos directamente en la tabla options los transients que matchen 'alezux_user_online_%'
		// Y que no hayan expirado (la opción _transient_timeout_... debe ser mayor a now)
		
		// Método más directo: Contar usuarios que han tenido actividad en los últimos 5 minutos usando user meta
		// Es más robusto que buscar en tabla options llena de basura.
		
		$time_ago = date( 'Y-m-d H:i:s', strtotime( '-5 minutes' ) );
		
		$args = [
			'meta_query' => [
				[
					'key'     => 'alezux_last_activity',
					'value'   => $time_ago,
					'compare' => '>=',
					'type'    => 'DATETIME'
				]
			],
			'fields' => 'ID',
			'count_total' => true
		];
		
		$user_query = new \WP_User_Query( $args );
		return $user_query->get_total();
	}

	/**
	 * Registra intentos de login fallidos
	 */
	public static function track_failed_login( $username ) {
		// Usamos un option para guardar un array simple de intentos diarios
		// Estructura: ['date' => 'Y-m-d', 'count' => N]
		$option_name = 'alezux_security_failed_logins';
		$stats = get_option( $option_name, [ 'date' => date( 'Y-m-d' ), 'count' => 0 ] );

		// Si cambió el día, reiniciar
		if ( $stats['date'] !== date( 'Y-m-d' ) ) {
			$stats = [ 'date' => date( 'Y-m-d' ), 'count' => 0 ];
		}

		$stats['count']++;
		update_option( $option_name, $stats );
	}

	/**
	 * Obtiene intentos fallidos de hoy
	 */
	public static function get_failed_logins_today() {
		$stats = get_option( 'alezux_security_failed_logins', [ 'date' => date( 'Y-m-d' ), 'count' => 0 ] );
		if ( $stats['date'] !== date( 'Y-m-d' ) ) {
			return 0;
		}
		return (int) $stats['count'];
	}

	/**
	 * Obtiene ingresos de los últimos 30 días (simulado si no hay módulo Finanzas, o real si existe tabla)
	 */
	public static function get_recent_revenue() {
		return self::get_revenue_log(30);
	}

    /**
     * Obtiene log de ingresos diarios para los últimos X días
     * @param int $days días hacia atrás desde hoy (default 365 para cubrir todo el año)
     * @return array Array de objetos {date: 'Y-m-d', total: 123.45}
     */
    public static function get_revenue_log($days = 365) {
        global $wpdb;
		$table_name = $wpdb->prefix . 'alezux_finanzas_transactions'; 
		
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
			return []; 
		}

		// Fecha límite respetando Timezone de WP
		$date_limit = date('Y-m-d H:i:s', strtotime("-$days days", current_time('timestamp')));

		$query = "
			SELECT DATE(created_at) as date, SUM(amount) as total
			FROM $table_name
			WHERE created_at >= '$date_limit'
			AND status = 'succeeded'
			GROUP BY DATE(created_at)
			ORDER BY date ASC
		";
		
		return $wpdb->get_results( $query, 'ARRAY_A' );
    }
}
