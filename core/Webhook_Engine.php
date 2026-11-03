<?php
namespace Alezux_Members\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Webhook_Engine
 * Motor centralizado para Webhooks Salientes (Outgoing) y Entrantes (Incoming).
 * Permite la integración en tiempo real con Zapier, Make, n8n, Meta, CRMs y plataformas de pago.
 */
class Webhook_Engine {

	private static $instance = null;

	const OPTION_WEBHOOKS      = 'crezca_outgoing_webhooks';
	const OPTION_LOGS          = 'crezca_webhook_logs';
	const OPTION_INCOMING_KEY  = 'crezca_incoming_webhook_api_key';

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init() {
		// Asegurar que exista una API Key para webhooks entrantes
		if ( ! get_option( self::OPTION_INCOMING_KEY ) ) {
			update_option( self::OPTION_INCOMING_KEY, 'crz_sec_' . bin2hex( random_bytes( 16 ) ) );
		}
	}

	/**
	 * Obtener la API Key para webhooks entrantes
	 */
	public static function get_incoming_key() {
		$key = get_option( self::OPTION_INCOMING_KEY );
		if ( empty( $key ) ) {
			$key = 'crz_sec_' . bin2hex( random_bytes( 16 ) );
			update_option( self::OPTION_INCOMING_KEY, $key );
		}
		return $key;
	}

	/**
	 * Regenerar la API Key para webhooks entrantes
	 */
	public static function regenerate_incoming_key() {
		$new_key = 'crz_sec_' . bin2hex( random_bytes( 16 ) );
		update_option( self::OPTION_INCOMING_KEY, $new_key );
		return $new_key;
	}

	/**
	 * Obtener la lista de webhooks salientes configurados
	 */
	public static function get_webhooks() {
		$webhooks = get_option( self::OPTION_WEBHOOKS, [] );
		return is_array( $webhooks ) ? $webhooks : [];
	}

	/**
	 * Guardar o actualizar un webhook saliente
	 */
	public static function save_webhook( $data ) {
		$webhooks = self::get_webhooks();
		$id = ! empty( $data['id'] ) ? sanitize_text_field( $data['id'] ) : 'wh_' . bin2hex( random_bytes( 8 ) );

		$name   = ! empty( $data['name'] ) ? sanitize_text_field( $data['name'] ) : 'Webhook Externo';
		$url    = ! empty( $data['url'] ) ? esc_url_raw( trim( $data['url'] ) ) : '';
		$secret = ! empty( $data['secret'] ) ? sanitize_text_field( $data['secret'] ) : 'whsec_' . bin2hex( random_bytes( 12 ) );
		$status = ( isset( $data['status'] ) && $data['status'] === 'paused' ) ? 'paused' : 'active';
		$events = ! empty( $data['events'] ) && is_array( $data['events'] ) ? array_map( 'sanitize_text_field', $data['events'] ) : [ '*' ];

		if ( empty( $url ) ) {
			return new \WP_Error( 'invalid_url', 'La URL del webhook es requerida.' );
		}

		$item = [
			'id'         => $id,
			'name'       => $name,
			'url'        => $url,
			'secret'     => $secret,
			'status'     => $status,
			'events'     => $events,
			'updated_at' => current_time( 'mysql' ),
		];

		$found = false;
		foreach ( $webhooks as $idx => $wh ) {
			if ( isset( $wh['id'] ) && $wh['id'] === $id ) {
				$item['created_at'] = $wh['created_at'] ?? current_time( 'mysql' );
				$webhooks[ $idx ] = $item;
				$found = true;
				break;
			}
		}

		if ( ! $found ) {
			$item['created_at'] = current_time( 'mysql' );
			$webhooks[] = $item;
		}

		update_option( self::OPTION_WEBHOOKS, array_values( $webhooks ) );
		return $item;
	}

	/**
	 * Eliminar un webhook por ID
	 */
	public static function delete_webhook( $id ) {
		$webhooks = self::get_webhooks();
		$filtered = array_filter( $webhooks, function( $wh ) use ( $id ) {
			return isset( $wh['id'] ) && $wh['id'] !== $id;
		} );
		update_option( self::OPTION_WEBHOOKS, array_values( $filtered ) );
		return true;
	}

	/**
	 * Obtener los registros de auditoría de envíos recientes
	 */
	public static function get_logs( $limit = 50 ) {
		$logs = get_option( self::OPTION_LOGS, [] );
		if ( ! is_array( $logs ) ) {
			$logs = [];
		}
		return array_slice( $logs, 0, $limit );
	}

	/**
	 * Registrar un log de entrega
	 */
	private static function record_log( $log_entry ) {
		$logs = get_option( self::OPTION_LOGS, [] );
		if ( ! is_array( $logs ) ) {
			$logs = [];
		}
		array_unshift( $logs, $log_entry );
		if ( count( $logs ) > 60 ) {
			$logs = array_slice( $logs, 0, 60 );
		}
		update_option( self::OPTION_LOGS, $logs, false );
	}

	/**
	 * Disparar evento a todos los webhooks suscritos
	 * 
	 * @param string $event Nombre del evento (ej: 'course.completed', 'lesson.completed', 'certificate.issued')
	 * @param array $payload Datos del evento
	 * @return int Cantidad de webhooks a los que se envió
	 */
	public static function dispatch( $event, $payload = [] ) {
		$webhooks = self::get_webhooks();
		if ( empty( $webhooks ) ) {
			return 0;
		}

		$dispatched_count = 0;
		$timestamp        = time();
		$academy_name     = get_option( 'crezca_academy_name', get_bloginfo( 'name' ) );

		$full_payload = [
			'event'        => $event,
			'timestamp'    => $timestamp,
			'academy_name' => $academy_name,
			'site_url'     => home_url(),
			'data'         => $payload,
		];

		$json_body = wp_json_encode( $full_payload );

		foreach ( $webhooks as $wh ) {
			if ( empty( $wh['status'] ) || $wh['status'] !== 'active' ) {
				continue;
			}
			$subscribed_events = (array) ( $wh['events'] ?? [] );
			if ( ! in_array( '*', $subscribed_events, true ) && ! in_array( $event, $subscribed_events, true ) ) {
				continue;
			}

			$secret = $wh['secret'] ?? '';
			$signature = hash_hmac( 'sha256', $json_body, $secret );

			$start_time = microtime( true );

			$args = [
				'method'      => 'POST',
				'timeout'     => 10,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking'    => true,
				'headers'     => [
					'Content-Type'        => 'application/json',
					'User-Agent'          => 'Crezca-Webhook-Engine/2.1.0',
					'X-Crezca-Event'      => $event,
					'X-Crezca-Signature'  => $signature,
					'X-Crezca-Timestamp'  => (string) $timestamp,
				],
				'body'        => $json_body,
			];

			$response = wp_remote_post( $wh['url'], $args );
			$duration = round( ( microtime( true ) - $start_time ) * 1000 );

			$status_code = 0;
			$response_body = '';

			if ( is_wp_error( $response ) ) {
				$status_code   = 500;
				$response_body = $response->get_error_message();
			} else {
				$status_code   = wp_remote_retrieve_response_code( $response );
				$response_body = substr( wp_remote_retrieve_body( $response ), 0, 500 );
			}

			self::record_log( [
				'id'           => 'log_' . bin2hex( random_bytes( 6 ) ),
				'webhook_id'   => $wh['id'],
				'webhook_name' => $wh['name'],
				'event'        => $event,
				'url'          => $wh['url'],
				'status_code'  => $status_code,
				'response'     => $response_body,
				'duration_ms'  => $duration,
				'payload'      => $full_payload,
				'created_at'   => current_time( 'mysql' ),
			] );

			$dispatched_count++;
		}

		return $dispatched_count;
	}

	/**
	 * Enviar un evento de prueba hacia un webhook específico
	 */
	public static function test_webhook( $id ) {
		$webhooks = self::get_webhooks();
		$target   = null;
		foreach ( $webhooks as $wh ) {
			if ( isset( $wh['id'] ) && $wh['id'] === $id ) {
				$target = $wh;
				break;
			}
		}

		if ( ! $target ) {
			return new \WP_Error( 'not_found', 'Webhook no encontrado.' );
		}

		$event        = 'ping.test';
		$timestamp    = time();
		$academy_name = get_option( 'crezca_academy_name', get_bloginfo( 'name' ) );

		$test_payload = [
			'event'        => $event,
			'timestamp'    => $timestamp,
			'academy_name' => $academy_name,
			'site_url'     => home_url(),
			'message'      => '¡Prueba exitosa de conexión desde Crezca LMS!',
			'data'         => [
				'test'      => true,
				'ping_id'   => 'ping_' . bin2hex( random_bytes( 4 ) ),
				'sent_at'   => current_time( 'mysql' ),
			],
		];

		$json_body = wp_json_encode( $test_payload );
		$secret    = $target['secret'] ?? '';
		$signature = hash_hmac( 'sha256', $json_body, $secret );

		$start_time = microtime( true );

		$response = wp_remote_post( $target['url'], [
			'method'      => 'POST',
			'timeout'     => 10,
			'headers'     => [
				'Content-Type'       => 'application/json',
				'User-Agent'         => 'Crezca-Webhook-Engine/2.1.0',
				'X-Crezca-Event'     => $event,
				'X-Crezca-Signature' => $signature,
				'X-Crezca-Timestamp' => (string) $timestamp,
			],
			'body'        => $json_body,
		] );

		$duration = round( ( microtime( true ) - $start_time ) * 1000 );

		if ( is_wp_error( $response ) ) {
			$status_code   = 500;
			$response_body = $response->get_error_message();
		} else {
			$status_code   = wp_remote_retrieve_response_code( $response );
			$response_body = substr( wp_remote_retrieve_body( $response ), 0, 500 );
		}

		self::record_log( [
			'id'           => 'log_' . bin2hex( random_bytes( 6 ) ),
			'webhook_id'   => $target['id'],
			'webhook_name' => $target['name'],
			'event'        => $event,
			'url'          => $target['url'],
			'status_code'  => $status_code,
			'response'     => $response_body,
			'duration_ms'  => $duration,
			'payload'      => $test_payload,
			'created_at'   => current_time( 'mysql' ),
		] );

		return [
			'success'     => ( $status_code >= 200 && $status_code < 300 ),
			'status_code' => $status_code,
			'response'    => $response_body,
			'duration_ms' => $duration,
		];
	}

	/**
	 * Procesar webhook entrante (Incoming Webhook)
	 * Llamado por plataformas externas (Zapier, Make, Hotmart, ThriveCart, ClickFunnels, Stripe)
	 */
	public static function process_incoming( $request ) {
		$provided_key = $request->get_header( 'x-crezca-api-key' );
		if ( empty( $provided_key ) ) {
			$provided_key = $request->get_param( 'api_key' );
		}

		$configured_key = self::get_incoming_key();

		if ( empty( $provided_key ) || ! hash_equals( (string) $configured_key, (string) $provided_key ) ) {
			return new \WP_Error( 'unauthorized', 'Clave API de Webhook inválida o ausente.', [ 'status' => 401 ] );
		}

		$params = $request->get_json_params();
		if ( empty( $params ) || ! is_array( $params ) ) {
			$params = $request->get_params();
		}

		$action = isset( $params['action'] ) ? sanitize_key( $params['action'] ) : '';
		$email  = isset( $params['email'] ) ? sanitize_email( $params['email'] ) : '';

		if ( empty( $action ) ) {
			return new \WP_Error( 'missing_action', 'El parámetro "action" es requerido.', [ 'status' => 400 ] );
		}

		switch ( $action ) {
			case 'enroll_student':
			case 'add_access':
				if ( empty( $email ) ) {
					return new \WP_Error( 'missing_email', 'El correo "email" es requerido.', [ 'status' => 400 ] );
				}

				$course_id = isset( $params['course_id'] ) ? (int) $params['course_id'] : 0;
				$name      = isset( $params['name'] ) ? sanitize_text_field( $params['name'] ) : '';

				$user = get_user_by( 'email', $email );
				$is_new = false;
				if ( ! $user ) {
					$username = sanitize_user( current( explode( '@', $email ) ) );
					if ( username_exists( $username ) ) {
						$username .= '_' . wp_rand( 100, 999 );
					}
					$password = wp_generate_password( 12, true );
					$user_id = wp_create_user( $username, $password, $email );
					if ( is_wp_error( $user_id ) ) {
						return $user_id;
					}

					$is_new = true;
					if ( ! empty( $name ) ) {
						$parts = explode( ' ', $name, 2 );
						wp_update_user( [
							'ID'         => $user_id,
							'first_name' => $parts[0] ?? '',
							'last_name'  => $parts[1] ?? '',
							'display_name' => $name,
						] );
					}

					// Disparar evento de registro
					self::dispatch( 'student.registered', [
						'user_id' => $user_id,
						'email'   => $email,
						'name'    => $name ?: $username,
					] );
				} else {
					$user_id = $user->ID;
				}

				// Asignar curso si se especificó
				if ( $course_id > 0 ) {
					if ( class_exists( '\\Alezux_Members\\Core\\LMS_Engine' ) ) {
						\Alezux_Members\Core\LMS_Engine::enroll_user( $user_id, $course_id );
					} else {
						$enrolled = get_user_meta( $user_id, '_crezca_enrolled_courses', true );
						if ( ! is_array( $enrolled ) ) {
							$enrolled = [];
						}
						if ( ! in_array( $course_id, $enrolled, true ) ) {
							$enrolled[] = $course_id;
							update_user_meta( $user_id, '_crezca_enrolled_courses', $enrolled );
						}
					}

					self::dispatch( 'course.enrolled', [
						'user_id'   => $user_id,
						'course_id' => $course_id,
						'email'     => $email,
					] );
				}

				return rest_ensure_response( [
					'success'  => true,
					'message'  => 'Estudiante procesado y matriculado con éxito.',
					'user_id'  => $user_id,
					'is_new'   => $is_new,
					'course_id'=> $course_id,
				] );

			case 'revoke_access':
				if ( empty( $email ) ) {
					return new \WP_Error( 'missing_email', 'El correo "email" es requerido.', [ 'status' => 400 ] );
				}
				$course_id = isset( $params['course_id'] ) ? (int) $params['course_id'] : 0;
				$user = get_user_by( 'email', $email );
				if ( ! $user ) {
					return new \WP_Error( 'user_not_found', 'No existe estudiante con ese correo.', [ 'status' => 404 ] );
				}

				if ( $course_id > 0 ) {
					if ( class_exists( '\\Alezux_Members\\Core\\LMS_Engine' ) ) {
						\Alezux_Members\Core\LMS_Engine::unenroll_user( $user->ID, $course_id );
					} else {
						$enrolled = get_user_meta( $user->ID, '_crezca_enrolled_courses', true );
						if ( is_array( $enrolled ) ) {
							$enrolled = array_values( array_diff( $enrolled, [ $course_id ] ) );
							update_user_meta( $user->ID, '_crezca_enrolled_courses', $enrolled );
						}
					}
				}

				return rest_ensure_response( [
					'success'   => true,
					'message'   => 'Acceso revocado correctamente.',
					'user_id'   => $user->ID,
					'course_id' => $course_id,
				] );

			case 'award_xp':
				$points = isset( $params['points'] ) ? (int) $params['points'] : 0;
				$reason = isset( $params['reason'] ) ? sanitize_text_field( $params['reason'] ) : 'Bonus externo';
				$user   = get_user_by( 'email', $email );
				if ( ! $user ) {
					return new \WP_Error( 'user_not_found', 'Estudiante no encontrado.', [ 'status' => 404 ] );
				}

				if ( class_exists( '\\Alezux_Members\\Core\\Ranking_Engine' ) ) {
					$new_xp = \Alezux_Members\Core\Ranking_Engine::add_xp( $user->ID, $points, 'external_bonus', $reason );
					return rest_ensure_response( [
						'success'  => true,
						'message'  => "Se otorgaron {$points} XP al estudiante.",
						'total_xp' => $new_xp,
					] );
				}

				return rest_ensure_response( [ 'success' => true, 'message' => 'Ranking_Engine no disponible.' ] );

			default:
				return new \WP_Error( 'invalid_action', "Acción no reconocida: {$action}", [ 'status' => 400 ] );
		}
	}
}
