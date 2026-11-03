<?php
namespace Alezux_Members\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ranking_Engine
 * Motor de Gamificación, Niveles de Experiencia (XP) y Tablas de Clasificación (Leaderboards).
 */
class Ranking_Engine {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init() {
		add_action( 'init', [ $this, 'maybe_create_tables' ], 25 );
	}

	/**
	 * Asegurar existencia de la tabla de registros de XP
	 */
	public function maybe_create_tables() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'crezca_xp_logs';

		$installed_ver = get_option( 'crezca_xp_db_version', '0' );
		if ( '1.0.0' !== $installed_ver || $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				points int(11) NOT NULL DEFAULT 0,
				action_type varchar(50) NOT NULL,
				action_detail varchar(255) DEFAULT '',
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY user_id (user_id),
				KEY created_at (created_at),
				KEY action_type (action_type)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			update_option( 'crezca_xp_db_version', '1.0.0' );
		}
	}

	/**
	 * Definición de niveles y rangos por XP
	 */
	public static function get_level_info( $xp ) {
		$xp = max( 0, (int) $xp );

		$levels = [
			[ 'level' => 1, 'name' => 'Novato',       'min' => 0,    'max' => 99,   'badge' => '🌱', 'color' => '#94a3b8' ],
			[ 'level' => 2, 'name' => 'Explorador',   'min' => 100,  'max' => 299,  'badge' => '🧭', 'color' => '#38bdf8' ],
			[ 'level' => 3, 'name' => 'Aprendiz',     'min' => 300,  'max' => 599,  'badge' => '⚡', 'color' => '#a855f7' ],
			[ 'level' => 4, 'name' => 'Especialista', 'min' => 600,  'max' => 999,  'badge' => '🛡️', 'color' => '#3b82f6' ],
			[ 'level' => 5, 'name' => 'Maestro',      'min' => 1000, 'max' => 1999, 'badge' => '⚔️', 'color' => '#f59e0b' ],
			[ 'level' => 6, 'name' => 'Leyenda',      'min' => 2000, 'max' => 999999, 'badge' => '👑', 'color' => '#ec4899' ],
		];

		$current = $levels[0];
		$next    = $levels[1];

		foreach ( $levels as $idx => $lvl ) {
			if ( $xp >= $lvl['min'] && $xp <= $lvl['max'] ) {
				$current = $lvl;
				$next    = isset( $levels[ $idx + 1 ] ) ? $levels[ $idx + 1 ] : null;
				break;
			}
		}

		$progress_pct = 100;
		$points_to_next = 0;

		if ( $next ) {
			$range = $current['max'] - $current['min'] + 1;
			$gained = $xp - $current['min'];
			$progress_pct = min( 100, max( 0, (int) round( ( $gained / $range ) * 100 ) ) );
			$points_to_next = max( 0, ( $next['min'] - $xp ) );
		}

		return [
			'level'          => $current['level'],
			'name'           => $current['name'],
			'badge'          => $current['badge'],
			'color'          => $current['color'],
			'current_xp'     => $xp,
			'progress_pct'   => $progress_pct,
			'points_to_next' => $points_to_next,
			'next_level'     => $next ? $next['name'] : null,
		];
	}

	/**
	 * Sumar o restar puntos de experiencia a un usuario
	 */
	public static function add_xp( $user_id, $points, $action_type = 'general', $action_detail = '' ) {
		$user_id = (int) $user_id;
		$points  = (int) $points;
		if ( ! $user_id || 0 === $points ) {
			return 0;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'crezca_xp_logs';

		// Asegurar tabla
		self::get_instance()->maybe_create_tables();

		$wpdb->insert(
			$table_name,
			[
				'user_id'       => $user_id,
				'points'        => $points,
				'action_type'   => sanitize_key( $action_type ),
				'action_detail' => sanitize_text_field( $action_detail ),
				'created_at'    => current_time( 'mysql' ),
			],
			[ '%d', '%d', '%s', '%s', '%s' ]
		);

		// Actualizar total en usermeta para lecturas instantáneas
		$current_total = (int) get_user_meta( $user_id, '_crezca_total_xp', true );
		$new_total = max( 0, $current_total + $points );
		update_user_meta( $user_id, '_crezca_total_xp', $new_total );

		// Si subió de nivel y tenemos notificaciones
		$prev_lvl = self::get_level_info( $current_total );
		$new_lvl  = self::get_level_info( $new_total );
		if ( $new_lvl['level'] > $prev_lvl['level'] ) {
			if ( class_exists( '\\Alezux_Members\\Modules\\Notifications\\Notifications' ) ) {
				\Alezux_Members\Modules\Notifications\Notifications::add_notification(
					"¡Subiste de Nivel!",
					"¡Felicidades! Alcanzaste el Nivel {$new_lvl['level']}: {$new_lvl['name']} {$new_lvl['badge']}",
					'#',
					'',
					[ $user_id ]
				);
			}
		}

		return $new_total;
	}

	/**
	 * Obtener total acumulado de XP de un usuario
	 */
	public static function get_user_xp( $user_id ) {
		$xp = get_user_meta( (int) $user_id, '_crezca_total_xp', true );
		if ( '' === $xp ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'crezca_xp_logs';
			$sum = (int) $wpdb->get_var( $wpdb->prepare( "SELECT SUM(points) FROM $table_name WHERE user_id = %d", $user_id ) );
			$xp = max( 0, $sum );
			update_user_meta( (int) $user_id, '_crezca_total_xp', $xp );
		}
		return (int) $xp;
	}

	/**
	 * Obtener la tabla de clasificación (Leaderboard) con soporte de filtros temporales
	 * 
	 * @param string $period 'all', 'month', 'week'
	 * @param int $limit Número de alumnos a mostrar (ej: 20)
	 * @param int $current_user_id ID del estudiante logueado
	 */
	public static function get_leaderboard( $period = 'all', $limit = 20, $current_user_id = 0 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'crezca_xp_logs';
		$limit = max( 5, min( 100, (int) $limit ) );

		self::get_instance()->maybe_create_tables();

		$date_clause = '';
		if ( 'week' === $period ) {
			$date_clause = "AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
		} elseif ( 'month' === $period ) {
			$date_clause = "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
		}

		// Consulta para obtener la tabla con el total del periodo
		$query = "SELECT user_id, SUM(points) as total_points, MAX(created_at) as last_active
			FROM $table_name
			WHERE points > 0 $date_clause
			GROUP BY user_id
			ORDER BY total_points DESC, last_active DESC
			LIMIT %d";

		$results = $wpdb->get_results( $wpdb->prepare( $query, $limit ) );

		// Si no hay suficientes registros en el periodo histórico, calcular con usermeta o fallback
		if ( empty( $results ) && 'all' === $period ) {
			$users_with_meta = get_users( [
				'meta_key' => '_crezca_total_xp',
				'orderby'  => 'meta_value_num',
				'order'    => 'DESC',
				'number'   => $limit,
			] );

			$results = [];
			foreach ( $users_with_meta as $u ) {
				$xp = (int) get_user_meta( $u->ID, '_crezca_total_xp', true );
				if ( $xp > 0 ) {
					$results[] = (object) [
						'user_id'      => $u->ID,
						'total_points' => $xp,
						'last_active'  => current_time( 'mysql' ),
					];
				}
			}
		}

		$leaderboard = [];
		$rank = 1;
		$user_in_list = false;

		foreach ( $results as $row ) {
			$user = get_userdata( $row->user_id );
			if ( ! $user ) {
				continue;
			}

			// Si es admin puro y se prefiere ocultar de los alumnos en competencia:
			// solo mostramos si tiene XP acumulada
			$is_admin = in_array( 'administrator', (array) $user->roles, true );
			$avatar_meta = get_user_meta( $user->ID, '_crezca_custom_avatar_url', true );
			$avatar = ! empty( $avatar_meta ) ? $avatar_meta : get_avatar_url( $user->ID, [ 'size' => 96, 'default' => 'identicon' ] );

			$lvl_info = self::get_level_info( (int) $row->total_points );

			$display_name = trim( $user->display_name ?: $user->user_login );

			$is_current = ( (int) $current_user_id === (int) $user->ID );
			if ( $is_current ) {
				$user_in_list = true;
			}

			$leaderboard[] = [
				'rank'         => $rank,
				'user_id'      => (int) $user->ID,
				'name'         => $display_name,
				'username'     => $user->user_login,
				'avatar'       => $avatar,
				'xp'           => (int) $row->total_points,
				'level'        => $lvl_info['level'],
				'level_name'   => $lvl_info['name'],
				'badge'        => $lvl_info['badge'],
				'badge_color'  => $lvl_info['color'],
				'is_current'   => $is_current,
			];

			$rank++;
		}

		// Datos del usuario actual (si está logueado y no entró en el top)
		$current_user_summary = null;
		if ( $current_user_id > 0 ) {
			$user_xp = self::get_user_xp( $current_user_id );
			$lvl_info = self::get_level_info( $user_xp );
			$current_user_rank = null;

			// Buscar si está en la lista obtenida
			foreach ( $leaderboard as $item ) {
				if ( $item['user_id'] === (int) $current_user_id ) {
					$current_user_rank = $item['rank'];
					break;
				}
			}

			// Si no está en el top listado, calcular su posición exacta
			if ( null === $current_user_rank ) {
				$above_count = (int) $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(DISTINCT user_id) FROM $table_name WHERE points > 0 $date_clause GROUP BY user_id HAVING SUM(points) > %d",
					$user_xp
				) );
				$current_user_rank = $above_count + 1;
			}

			// Calcular cuántos puntos le faltan para alcanzar al puesto anterior
			$points_to_higher = 0;
			if ( $current_user_rank > 1 && ! empty( $leaderboard ) ) {
				foreach ( $leaderboard as $item ) {
					if ( $item['rank'] === $current_user_rank - 1 ) {
						$points_to_higher = max( 0, ( $item['xp'] - $user_xp ) + 5 );
						break;
					}
				}
			}

			$current_user_summary = [
				'rank'             => $current_user_rank,
				'xp'               => $user_xp,
				'level'            => $lvl_info['level'],
				'level_name'       => $lvl_info['name'],
				'badge'            => $lvl_info['badge'],
				'badge_color'      => $lvl_info['color'],
				'progress_pct'     => $lvl_info['progress_pct'],
				'points_to_next'   => $lvl_info['points_to_next'],
				'points_to_higher' => $points_to_higher,
			];
		}

		// Estadísticas generales de la academia
		$total_students_with_xp = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT user_id) FROM $table_name" );
		$total_xp_awarded       = (int) $wpdb->get_var( "SELECT SUM(points) FROM $table_name" );

		return [
			'period'       => $period,
			'leaderboard'  => $leaderboard,
			'currentUser'  => $current_user_summary,
			'stats'        => [
				'total_ranked_students' => max( count( $leaderboard ), $total_students_with_xp ),
				'total_xp_awarded'      => $total_xp_awarded,
			],
		];
	}
}
