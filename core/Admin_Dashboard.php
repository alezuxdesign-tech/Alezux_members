<?php
namespace Alezux_Members\Core;

use Alezux_Members\Modules\Notifications\Notifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Dashboard {

	public function init() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ], 5 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'admin_post_alezux_save_settings', [ $this, 'save_settings' ] );
		add_action( 'admin_post_alezux_send_test_notification', [ $this, 'send_test_notification' ] );
        add_action( 'admin_post_alezux_simulate_webhook', [ $this, 'handle_simulate_webhook' ] );
		add_action( 'admin_post_alezux_import_json', [ $this, 'run_json_import' ] );
		add_action( 'admin_post_alezux_cleanup_ld', [ $this, 'run_cleanup' ] );
		add_action( 'admin_post_alezux_generate_images', [ $this, 'run_image_generation' ] ); // NUEVO
		// Configuración de modo ventana independiente / app completa y enlace del menú
		add_action( 'admin_head', [ $this, 'setup_admin_window_mode' ] );
	}

	public function setup_admin_window_mode() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$is_crezca_page = ( $screen && ( 'toplevel_page_crezca' === $screen->id || 'toplevel_page_alezux-members' === $screen->id ) )
			|| ( isset( $_GET['page'] ) && ( 'crezca' === $_GET['page'] || 'alezux-members' === $_GET['page'] ) );
		?>
		<style>
			/* Icono de Crezca en el menú lateral */
			#adminmenu #toplevel_page_alezux-members .wp-menu-image img,
			#adminmenu #toplevel_page_crezca .wp-menu-image img {
				max-width: 20px;
				max-height: 20px;
				width: 20px;
				height: auto;
				padding-top: 8px;
				opacity: 0.9;
			}
			#adminmenu #toplevel_page_alezux-members:hover .wp-menu-image img,
			#adminmenu #toplevel_page_crezca:hover .wp-menu-image img {
				opacity: 1;
			}

			<?php if ( $is_crezca_page ) : ?>
			/* ===================================================
			   MODO APP INDEPENDIENTE (VENTANA COMPLETA LIMPIA)
			   Elimina todo el entorno/chrome de WordPress
			   =================================================== */
			html.wp-toolbar,
			body.toplevel_page_crezca,
			body.toplevel_page_alezux-members {
				padding: 0 !important;
				margin: 0 !important;
				overflow: hidden !important;
				height: 100vh !important;
				max-height: 100vh !important;
				background-color: var(--background, #090a0f) !important;
			}
			#wpadminbar {
				display: none !important;
			}
			#adminmenumain,
			#adminmenuback,
			#adminmenuwrap,
			#adminmenu {
				display: none !important;
				width: 0 !important;
				height: 0 !important;
			}
			#wpwrap {
				height: 100vh !important;
				max-height: 100vh !important;
				overflow: hidden !important;
				margin: 0 !important;
				padding: 0 !important;
			}
			#wpcontent {
				margin-left: 0 !important;
				padding: 0 !important;
				background-color: var(--background, #090a0f) !important;
				height: 100vh !important;
				max-height: 100vh !important;
				overflow: hidden !important;
			}
			#wpbody {
				padding-top: 0 !important;
				height: 100vh !important;
				max-height: 100vh !important;
				overflow: hidden !important;
			}
			#wpbody-content {
				padding-bottom: 0 !important;
				float: none !important;
				height: 100vh !important;
				max-height: 100vh !important;
				overflow: hidden !important;
			}
			#wpfooter {
				display: none !important;
				height: 0 !important;
				padding: 0 !important;
				margin: 0 !important;
			}
			/* Ocultar avisos, barras y banners de WordPress y plugins */
			.notice,
			.update-nag,
			.updated,
			.error,
			.is-dismissible,
			#screen-meta-links,
			#screen-meta {
				display: none !important;
			}
			/* El contenedor de React toma exactamente 100vh sin desborde exterior */
			#crezca-admin-root {
				width: 100% !important;
				height: 100vh !important;
				max-height: 100vh !important;
				margin: 0 !important;
				padding: 0 !important;
				overflow: hidden !important;
			}
			#alezux-admin-root {
				display: none !important;
			}
			/* Desactivar fondos blancos impuestos por wp-admin/css/forms.css */
			#crezca-admin-root input,
			#crezca-admin-root select,
			#crezca-admin-root textarea {
				background-color: var(--surface, #1e1e24) !important;
				color: var(--foreground, #f3f4f6) !important;
				border: 1px solid var(--border, #2d2d39) !important;
				box-shadow: none !important;
			}
			#crezca-admin-root select option {
				background-color: #1e1e24 !important;
				color: #f3f4f6 !important;
			}
			<?php endif; ?>
		</style>

		<script>
		// Abrir Crezca en una nueva ventana/pestaña al hacer clic desde el menú lateral
		document.addEventListener('DOMContentLoaded', function() {
			var crezcaMenuLinks = document.querySelectorAll('#adminmenu a[href*="page=crezca"], #adminmenu a[href*="page=alezux-members"]');
			crezcaMenuLinks.forEach(function(link) {
				link.setAttribute('target', '_blank');
				link.setAttribute('rel', 'noopener noreferrer');
				link.setAttribute('title', 'Abrir Crezca en una nueva ventana');
			});
		});
		</script>
		<?php
	}

	public function add_admin_menu() {
		$academy_name = get_option( 'alezux_academy_name', 'Crezca' );
		$academy_logo = get_option( 'alezux_academy_logo', '' );
		$menu_icon    = ! empty( $academy_logo ) ? $academy_logo : ALEZUX_MEMBERS_URL . 'modules/demo-block/assets/css/img/LOGO.svg';

		add_menu_page(
			$academy_name,
			$academy_name,
			'manage_options',
			'crezca',
			[ $this, 'render_dashboard' ],
			$menu_icon,
			2
		);

		// Submenú explícito para el Dashboard
		add_submenu_page(
			'crezca',
			'Dashboard',
			'Dashboard',
			'manage_options',
			'crezca',
			[ $this, 'render_dashboard' ]
		);
	}

	public function enqueue_admin_assets( $hook ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$is_crezca_page = ( 'toplevel_page_crezca' === $hook || 'toplevel_page_alezux-members' === $hook )
			|| ( $screen && ( 'toplevel_page_crezca' === $screen->id || 'toplevel_page_alezux-members' === $screen->id || 'crezca' === $screen->id ) )
			|| ( isset( $_GET['page'] ) && in_array( $_GET['page'], [ 'crezca', 'alezux-members', 'crezca-dashboard' ], true ) );

		if ( ! $is_crezca_page ) {
			return;
		}
		
		// Encolar estilos globales también en el admin para nuestra página
		wp_enqueue_style( 
			'crezca-global', 
			ALEZUX_MEMBERS_URL . 'assets/css/global.css', 
			[], 
			ALEZUX_MEMBERS_VERSION 
		);

		$dist_css = ALEZUX_MEMBERS_PATH . 'assets/dist/alezux-dashboard.css';
		$dist_js  = ALEZUX_MEMBERS_PATH . 'assets/dist/alezux-dashboard.js';

		$css_ver = file_exists( $dist_css ) ? filemtime( $dist_css ) : ALEZUX_MEMBERS_VERSION;
		$js_ver  = file_exists( $dist_js ) ? filemtime( $dist_js ) : ALEZUX_MEMBERS_VERSION;

		if ( file_exists( $dist_css ) ) {
			wp_enqueue_style(
				'crezca-admin-arc-css',
				ALEZUX_MEMBERS_URL . 'assets/dist/alezux-dashboard.css',
				[],
				$css_ver
			);
		}

		if ( file_exists( $dist_js ) ) {
			wp_enqueue_script(
				'crezca-admin-arc-js',
				ALEZUX_MEMBERS_URL . 'assets/dist/alezux-dashboard.js',
				[],
				$js_ver,
				true
			);

			$admin_data = [
				'root_url'        => esc_url_raw( rest_url( 'crezca/v1/' ) ),
				'legacy_root_url' => esc_url_raw( rest_url( 'alezux/v1/' ) ),
				'wp_admin_url'    => esc_url_raw( admin_url( 'index.php' ) ),
				'nonce'           => wp_create_nonce( 'wp_rest' ),
				'ajax_url'        => admin_url( 'admin-ajax.php' ),
				'academy_name'    => get_option( 'alezux_academy_name', 'Crezca' ),
				'academy_logo'    => get_option( 'alezux_academy_logo', '' ),
				'theme'           => get_option( 'alezux_theme', 'dark' ),
				'accent'          => get_option( 'alezux_accent', 'violet' ),
			];

			wp_localize_script( 'crezca-admin-arc-js', 'crezca_admin_data', $admin_data );
			wp_localize_script( 'crezca-admin-arc-js', 'alezux_admin_data', $admin_data );
		}
	}




	public function render_dashboard() {
		// Salvaguarda: garantizar que los assets de Crezca estén encolados
		$this->enqueue_admin_assets( 'toplevel_page_crezca' );

		// Obtener opciones guardadas
		$settings = [
			'primary_color' => get_option( 'alezux_primary_color', '#6c5ce7' ),
			'primary_hover' => get_option( 'alezux_primary_hover', '#5649c0' ),
			'bg_base'       => get_option( 'alezux_bg_base', '#0f0f0f' ),
			'bg_card'       => get_option( 'alezux_bg_card', '#1a1a1a' ),
			'border_radius' => get_option( 'alezux_border_radius', '50px' ),
			'border_color'  => get_option( 'alezux_border_color', '#333333' ),
			'box_shadow'    => get_option( 'alezux_box_shadow', '0 10px 30px rgba(0, 0, 0, 0.3)' ),
		];

		// Obtener shortcodes registrados desde Module_Base
		$shortcodes = Module_Base::get_registered_shortcodes();

        // Obtener todas las páginas para el selector de permisos
        $all_pages        = get_pages();
        $restricted_pages = get_option( 'alezux_restricted_pages', [] );
        $private_pages    = get_option( 'alezux_private_pages', [] );
        $admin_only_css_classes = get_option( 'alezux_admin_only_css_classes', '' );

		include ALEZUX_MEMBERS_PATH . 'views/admin/dashboard.php';
	}

	public function save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'No tienes permisos.' );
		}

		check_admin_referer( 'alezux_save_settings_action', 'alezux_settings_nonce' );

		// Guardar colores y estilos
		if ( isset( $_POST['alezux_primary_color'] ) ) {
			update_option( 'alezux_primary_color', sanitize_hex_color( $_POST['alezux_primary_color'] ) );
		}
		if ( isset( $_POST['alezux_primary_hover'] ) ) {
			update_option( 'alezux_primary_hover', sanitize_hex_color( $_POST['alezux_primary_hover'] ) );
		}
		if ( isset( $_POST['alezux_bg_base'] ) ) {
			update_option( 'alezux_bg_base', sanitize_hex_color( $_POST['alezux_bg_base'] ) );
		}
		if ( isset( $_POST['alezux_bg_card'] ) ) {
			update_option( 'alezux_bg_card', sanitize_hex_color( $_POST['alezux_bg_card'] ) );
		}
		if ( isset( $_POST['alezux_border_radius'] ) ) {
			update_option( 'alezux_border_radius', sanitize_text_field( $_POST['alezux_border_radius'] ) );
		}
		if ( isset( $_POST['alezux_border_color'] ) ) {
			update_option( 'alezux_border_color', sanitize_hex_color( $_POST['alezux_border_color'] ) );
		}
		if ( isset( $_POST['alezux_box_shadow'] ) ) {
			update_option( 'alezux_box_shadow', sanitize_text_field( $_POST['alezux_box_shadow'] ) );
		}

		// Guardar Keys de Stripe
		if ( isset( $_POST['alezux_stripe_public_key'] ) ) {
			update_option( 'alezux_stripe_public_key', sanitize_text_field( $_POST['alezux_stripe_public_key'] ) );
		}
		if ( isset( $_POST['alezux_stripe_secret_key'] ) ) {
			update_option( 'alezux_stripe_secret_key', sanitize_text_field( $_POST['alezux_stripe_secret_key'] ) );
		}
        if ( isset( $_POST['alezux_stripe_webhook_secret'] ) ) {
			update_option( 'alezux_stripe_webhook_secret', sanitize_text_field( $_POST['alezux_stripe_webhook_secret'] ) );
		}

        // Auth Pages
		if ( isset( $_POST['alezux_login_page_id'] ) ) {
			update_option( 'alezux_login_page_id', intval( $_POST['alezux_login_page_id'] ) );
		}
		if ( isset( $_POST['alezux_reset_page_id'] ) ) {
			update_option( 'alezux_reset_page_id', intval( $_POST['alezux_reset_page_id'] ) );
		}

        // Restricted & Private Pages
        if ( isset( $_POST['alezux_saving_tab'] ) && 'permissions' === $_POST['alezux_saving_tab'] ) {
            // Restricted
            $restricted = isset( $_POST['alezux_restricted_pages'] ) ? array_map( 'intval', $_POST['alezux_restricted_pages'] ) : [];
            update_option( 'alezux_restricted_pages', $restricted );

            // Private
            $private = isset( $_POST['alezux_private_pages'] ) ? array_map( 'intval', $_POST['alezux_private_pages'] ) : [];
            update_option( 'alezux_private_pages', $private );
        }

        // Admin Only CSS Classes
        if ( isset( $_POST['alezux_admin_only_css_classes'] ) ) {
            // Sanitize textarea but keep commas/spaces
            $css_classes = sanitize_textarea_field( $_POST['alezux_admin_only_css_classes'] );
            update_option( 'alezux_admin_only_css_classes', $css_classes );
        }

		wp_redirect( admin_url( 'admin.php?page=crezca&status=success' ) );
		exit;
	}

	public function send_test_notification() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'No tienes permisos.' );
		}

		check_admin_referer( 'alezux_send_test_notification_action', 'alezux_notification_nonce' );

		$title      = isset( $_POST['notification_title'] ) ? sanitize_text_field( $_POST['notification_title'] ) : 'Notificación de Prueba';
		$message    = isset( $_POST['notification_message'] ) ? sanitize_textarea_field( $_POST['notification_message'] ) : 'Este es un mensaje de prueba.';
		$avatar_url = isset( $_POST['notification_avatar'] ) ? esc_url_raw( $_POST['notification_avatar'] ) : '';
		$target_user_id = isset( $_POST['target_user_id'] ) ? intval( $_POST['target_user_id'] ) : 0;
		
		// Si no se especifica usuario, enviamos al actual para la prueba (o a todos si se implementara logicamente así, pero por seguridad en prueba mejor al actual si está vacío)
		if ( empty( $target_user_id ) ) {
			$target_user_id = get_current_user_id();
		}

		// Usar la clase Notifications para enviar
		// Nota: add_notification espera $target_users como 'all', ID o array de IDs.
		Notifications::add_notification( 
			$title, 
			$message, 
			'#', 
			$avatar_url, 
			$target_user_id 
		);

		wp_redirect( admin_url( 'admin.php?page=crezca&status=notification_sent' ) );
		exit;
	}

    public function handle_simulate_webhook() {
        if ( ! isset( $_POST['alezux_sim_nonce'] ) || ! wp_verify_nonce( $_POST['alezux_sim_nonce'], 'alezux_simulate_action' ) ) {
            wp_die( 'Seguridad inválida.' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'No tienes permisos.' );
        }

        $email = sanitize_email( $_POST['sim_email'] );
        
        global $wpdb;
        $plan = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}alezux_finanzas_plans LIMIT 1" );
        
        // Si no hay plan, creamos uno dummy rápido
        if ( ! $plan ) {
            $table_plans = $wpdb->prefix . 'alezux_finanzas_plans';
            // Verificar si la tabla existe antes de insertar, aunque debería
            if($wpdb->get_var("SHOW TABLES LIKE '$table_plans'") == $table_plans) {
                $wpdb->insert( $table_plans, [
                    'name' => 'Plan Simulado',
                    'course_id' => 0,
                    'stripe_product_id' => 'prod_mock',
                    'stripe_price_id' => 'price_mock',
                    'total_quotas' => 4,
                    'quota_amount' => 50.00
                ] );
                $plan_id = $wpdb->insert_id;
            } else {
                wp_die('Error: La tabla de planes no existe. Reinstala el módulo de Finanzas.');
            }
        } else {
            $plan_id = $plan->id;
        }

        // Payload Mock
        $payload = [
            'id' => 'evt_sim_' . time(),
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_sim_' . time(),
                    'object' => 'checkout.session',
                    'customer_details' => [ 'email' => $email ],
                    'subscription' => 'sub_sim_' . time(),
                    'metadata' => [ 'plan_id' => $plan_id ],
                    'amount_total' => 5000,
                    'payment_intent' => 'pi_sim_' . time()
                ]
            ]
        ];

        // Enviar al endpoint local
        $url = get_rest_url( null, 'alezux/v1/stripe-webhook' );
        
        // Self request
        $response = wp_remote_post( $url, [
            'body' => json_encode( $payload ),
            'headers' => [ 'Content-Type' => 'application/json' ],
            'timeout' => 5,
            'sslverify' => false 
        ] );

        $result = 'failed';
        if ( ! is_wp_error( $response ) ) {
            $code = wp_remote_retrieve_response_code( $response );
            if ( $code === 200 ) {
                $result = 'success';
            } else {
                // Loguear error para debug
                error_log('Simulacion Fallida HTTP Code: ' . $code . ' Body: ' . wp_remote_retrieve_body($response));
            }
        } else {
             error_log('Simulacion Fallida WP Error: ' . $response->get_error_message());
        }

        // Redirigir de vuelta a settings
        $redirect_url = add_query_arg( 
            [ 'page' => 'crezca', 'sim_result' => $result ], 
            admin_url( 'admin.php' ) 
        );
        
        wp_redirect( $redirect_url );
        exit;
    }

	public function run_json_import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'No tienes permisos.' );
		}

		check_admin_referer( 'alezux_import_json_action', 'alezux_import_nonce' );

		$json_file = ALEZUX_MEMBERS_PATH . 'JSON CURSO LEARDASH.json';

		if ( ! file_exists( $json_file ) ) {
			wp_die( 'Error: No se encontró el archivo JSON CURSO LEARDASH.json en la carpeta del plugin.' );
		}

		// Prevenir Timeout de PHP y permitir que siga corriendo en segundo plano
		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}
		ignore_user_abort( true );

		$debug_log = ALEZUX_MEMBERS_PATH . 'import_debug.log';
		file_put_contents($debug_log, "Iniciando importación...\n", FILE_APPEND);

		// Sanitizar y parsear el JSON
		$json_data = file_get_contents( $json_file );
		$courses_data = json_decode( $json_data, true );

		if ( ! is_array( $courses_data ) ) {
			file_put_contents($debug_log, "Error: JSON Invalido.\n", FILE_APPEND);
			wp_die( 'Error: El JSON no tiene un formato válido.' );
		}

		file_put_contents($debug_log, "Cursos a importar: " . count($courses_data) . "\n", FILE_APPEND);

		foreach ( $courses_data as $course_data ) {
			$course_title = sanitize_text_field( $course_data['curso'] );
			
			file_put_contents($debug_log, "Procesando curso: {$course_title}\n", FILE_APPEND);

			// Buscar o Crear el Curso
			$existing_courses = get_posts([
				'post_type' => 'sfwd-courses',
				'title' => $course_title,
				'post_status' => ['publish', 'pending', 'draft', 'private'],
				'posts_per_page' => 1
			]);
			
			if ( ! empty($existing_courses) ) {
				$course_post = $existing_courses[0];
				$course_id = $course_post->ID;
				// Si está en draft, lo publicamos
				if ( $course_post->post_status !== 'publish' ) {
					wp_update_post( ['ID' => $course_id, 'post_status' => 'publish'] );
				}
				file_put_contents($debug_log, "Curso ya existe con ID: {$course_id} (Status: {$course_post->post_status})\n", FILE_APPEND);
			} else {
				// Si está en trash o no existe, crear uno nuevo
				$course_id = wp_insert_post( [
					'post_type'   => 'sfwd-courses',
					'post_title'  => $course_title,
					'post_status' => 'publish',
				] );
				
				if (is_wp_error($course_id)) {
					file_put_contents($debug_log, "Error insertando curso: " . $course_id->get_error_message() . "\n", FILE_APPEND);
				} else {
					file_put_contents($debug_log, "Nuevo curso creado con ID: {$course_id}\n", FILE_APPEND);
				}
			}

			if ( ! empty( $course_data['lessons'] ) && is_array( $course_data['lessons'] ) ) {
				$lesson_order = 1;
				foreach ( $course_data['lessons'] as $lesson_data ) {
					$raw_lesson_title = sanitize_text_field( $lesson_data['lesson_name'] );
					
					// Si es un separador, el usuario pidió encerrarlo en corchetes []
					if ( stripos( $raw_lesson_title, 'Separador' ) !== false ) {
						$lesson_title = '[' . $raw_lesson_title . ']';
					} else {
						$lesson_title = $raw_lesson_title;
					}

					// Verificar si la lección ya existe por título exacto
					$existing_lessons = get_posts([
						'post_type' => 'sfwd-lessons',
						'title' => $lesson_title,
						'post_status' => ['publish', 'pending', 'draft', 'private'],
						'meta_key' => 'course_id',
						'meta_value' => $course_id,
						'posts_per_page' => 1
					]);

					$lesson_id = 0;
					
					if ( ! empty($existing_lessons) ) {
						$lesson_post = $existing_lessons[0];
						$lesson_id = $lesson_post->ID;
						if ( $lesson_post->post_status !== 'publish' ) {
							wp_update_post( ['ID' => $lesson_id, 'post_status' => 'publish'] );
						}
					}

					if ( ! $lesson_id ) {
						$lesson_id = wp_insert_post( [
							'post_type'   => 'sfwd-lessons',
							'post_title'  => $lesson_title,
							'post_status' => 'publish',
							'menu_order'  => $lesson_order
						] );
						
						if ( $lesson_id && ! is_wp_error( $lesson_id ) ) {
							update_post_meta( $lesson_id, 'course_id', $course_id );
							update_post_meta( $lesson_id, '_sfwd-lessons', [ 'sfwd-lessons_course' => $course_id ] );
						}
					}

					if ( $lesson_id && ! is_wp_error( $lesson_id ) ) {
						if ( ! empty( $lesson_data['topics'] ) && is_array( $lesson_data['topics'] ) ) {
							$topic_order = 1;
							foreach ( $lesson_data['topics'] as $topic_data ) {
								$topic_title = sanitize_text_field( $topic_data['topic_name'] );
								$video_id    = sanitize_text_field( $topic_data['video_id'] );
								
								// Verificar si el Topic ya existe por título exacto
								$existing_topics = get_posts([
									'post_type' => 'sfwd-topic',
									'title' => $topic_title,
									'post_status' => ['publish', 'pending', 'draft', 'private'],
									'meta_key' => 'lesson_id',
									'meta_value' => $lesson_id,
									'posts_per_page' => 1
								]);

								$topic_id = 0;
								
								if ( ! empty($existing_topics) ) {
									$topic_post = $existing_topics[0];
									$topic_id = $topic_post->ID;
									if ( $topic_post->post_status !== 'publish' ) {
										wp_update_post( ['ID' => $topic_id, 'post_status' => 'publish'] );
									}
								}

								if ( ! $topic_id ) {
									$topic_content = '[vdo id="' . $video_id . '"]';

									$topic_id = wp_insert_post( [
										'post_type'    => 'sfwd-topic',
										'post_title'   => $topic_title,
										'post_content' => $topic_content,
										'post_status'  => 'publish',
										'menu_order'   => $topic_order
									] );

									if ( $topic_id && ! is_wp_error( $topic_id ) ) {
										update_post_meta( $topic_id, 'course_id', $course_id );
										update_post_meta( $topic_id, 'lesson_id', $lesson_id );
										update_post_meta( $topic_id, '_sfwd-topic', [
											'sfwd-topic_course' => $course_id,
											'sfwd-topic_lesson' => $lesson_id
										]);
									}
								}
								$topic_order++;
							}
						}
					}
					$lesson_order++;
				}
			}
		}

		wp_redirect( admin_url( 'admin.php?page=crezca&status=import_success' ) );
		exit;
	}

	public function run_cleanup() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'No tienes permisos.' );
		}
		check_admin_referer( 'alezux_cleanup_action', 'alezux_cleanup_nonce' );

		// Prevenir Timeout de PHP
		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}
		ignore_user_abort( true );

		$course_id = isset($_POST['cleanup_course_id']) ? sanitize_text_field($_POST['cleanup_course_id']) : 'all';

		if ($course_id === 'all') {
			$post_types = ['sfwd-courses', 'sfwd-lessons', 'sfwd-topic'];
			foreach ( $post_types as $pt ) {
				$posts = get_posts([
					'post_type' => $pt,
					'posts_per_page' => -1,
					'post_status' => ['publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash']
				]);
				foreach ( $posts as $p ) {
					// Si es lección o curso, intentar borrar la imagen destacada generada
					$thumb_id = get_post_thumbnail_id( $p->ID );
					if ( $thumb_id ) {
						wp_delete_attachment( $thumb_id, true );
					}
					// Borrar el post
					wp_delete_post( $p->ID, true );
				}
			}
		} else {
			// Borrar un curso específico y sus descendientes
			$cid = intval($course_id);
			if ($cid > 0) {
				// Borrar el curso
				wp_delete_post($cid, true);
				
				// Borrar lecciones de ese curso
				$lessons = get_posts([
					'post_type' => 'sfwd-lessons',
					'posts_per_page' => -1,
					'meta_key' => 'course_id',
					'meta_value' => $cid,
					'post_status' => ['publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash']
				]);
				foreach ($lessons as $l) {
					$thumb_id = get_post_thumbnail_id( $l->ID );
					if ( $thumb_id ) {
						wp_delete_attachment( $thumb_id, true );
					}
					wp_delete_post($l->ID, true);
				}

				// Borrar topics de ese curso
				$topics = get_posts([
					'post_type' => 'sfwd-topic',
					'posts_per_page' => -1,
					'meta_key' => 'course_id',
					'meta_value' => $cid,
					'post_status' => ['publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash']
				]);
				foreach ($topics as $t) {
					wp_delete_post($t->ID, true);
				}
			}
		}

		wp_redirect( admin_url( 'admin.php?page=crezca&status=cleanup_success' ) );
		exit;
	}

	public function run_image_generation() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'No tienes permisos.' );
		}
		check_admin_referer( 'alezux_generate_images_action', 'alezux_generate_images_nonce' );

		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}
		ignore_user_abort( true );

		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		// Obtener todos los cursos o el curso específico
		$course_filter = isset($_POST['image_course_id']) ? sanitize_text_field($_POST['image_course_id']) : 'all';
		$args = [ 'post_type' => 'sfwd-courses', 'posts_per_page' => -1, 'post_status' => 'publish' ];
		if ($course_filter !== 'all') {
			$args['p'] = intval($course_filter);
		}
		$courses = get_posts($args);

		foreach ( $courses as $course ) {
			$lessons = get_posts([
				'post_type'      => 'sfwd-lessons',
				'posts_per_page' => -1,
				'meta_key'       => 'course_id',
				'meta_value'     => $course->ID,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'post_status'    => 'publish'
			]);

			$bg_templates = [
				ALEZUX_MEMBERS_PATH . 'assets/bg_yellow.jpg',
				ALEZUX_MEMBERS_PATH . 'assets/bg_blue.jpg',
				ALEZUX_MEMBERS_PATH . 'assets/bg_orange.jpg',
			];
			$current_palette_index = -1; // Comienza en -1 para que el primer separador lo ponga en 0
			$current_separator_title = 'Plataforma';
			$module_counter = 0;

			foreach ( $lessons as $lesson ) {
				$title = $lesson->post_title;
				
				if ( stripos( $title, 'Separador' ) !== false ) {
					$module_counter = 0;
					if (preg_match('/Titulo:\s*([^)]+)/i', $title, $matches)) {
					    $current_separator_title = trim($matches[1]);
					} else {
					    $current_separator_title = str_replace(['[', ']'], '', $title);
					}
					$current_palette_index = ($current_palette_index + 1) % count($bg_templates);
					continue;
				}

				$module_counter++;

				if ( has_post_thumbnail( $lesson->ID ) ) {
					continue;
				}

				// Generar Imagen GD
				// Cargar plantilla generada por IA
				$width = 600;
				$height = 900;
				
				$template_path = $bg_templates[$current_palette_index];
				if (file_exists($template_path)) {
					$raw_image = imagecreatefromjpeg($template_path);
					$image = imagecreatetruecolor($width, $height);
					imagecopyresampled($image, $raw_image, 0, 0, 0, 0, $width, $height, imagesx($raw_image), imagesy($raw_image));
					imagedestroy($raw_image);
				} else {
					$image = imagecreatetruecolor($width, $height);
					$bg = imagecolorallocate($image, 30, 30, 30);
					imagefilledrectangle($image, 0, 0, $width, $height, $bg);
				}

				imagealphablending($image, true);
				imagesavealpha($image, true);

				// Borde de la tarjeta
				$border_color = imagecolorallocatealpha($image, 255, 255, 255, 90);
				imageline($image, 0, 0, $width, 0, $border_color);
				imageline($image, 0, 0, 0, $height, $border_color);
				imageline($image, $width-1, 0, $width-1, $height, $border_color);
				imageline($image, 0, $height-1, $width, $height-1, $border_color);

				$white = imagecolorallocate($image, 255, 255, 255);
				$black = imagecolorallocate($image, 0, 0, 0);

				// Dibujar Pastilla sin solapamientos
				$radius = 20;
				$badge_x = 40;
				$badge_y = 40;
				$badge_w = 140;
				$badge_h = 40;
				$badge_bg = imagecolorallocatealpha($image, 255, 255, 255, 95); 
				
				// Relleno central
				imagefilledrectangle($image, $badge_x+$radius, $badge_y, $badge_x+$badge_w-$radius, $badge_y+$badge_h, $badge_bg);
				// Semicírculos (evitando dibujar sobre el rectángulo)
				imagefilledarc($image, $badge_x+$radius, $badge_y+$radius, $radius*2, $radius*2, 90, 270, $badge_bg, IMG_ARC_PIE);
				imagefilledarc($image, $badge_x+$badge_w-$radius, $badge_y+$radius, $radius*2, $radius*2, 270, 90, $badge_bg, IMG_ARC_PIE);

				$font_bold = ALEZUX_MEMBERS_PATH . 'assets/fonts/Roboto-Bold.ttf';
				$font_regular = ALEZUX_MEMBERS_PATH . 'assets/fonts/Roboto-Regular.ttf';
				$mod_text = "Modulo " . $module_counter;

				if ( file_exists($font_bold) ) {
					$bbox = imagettfbbox(16, 0, $font_bold, $mod_text);
					$text_w = $bbox[2] - $bbox[0];
					$text_h = $bbox[1] - $bbox[7];
					$text_x = $badge_x + ($badge_w - $text_w) / 2;
					$text_y = $badge_y + ($badge_h + $text_h) / 2 - 2; 
					imagettftext( $image, 16, 0, $text_x, $text_y, $black, $font_bold, $mod_text );

					$words = explode(' ', $title);
					$lines = [];
					$current_line = '';
					foreach ($words as $word) {
						$test_line = $current_line . $word . ' ';
						$bbox = imagettfbbox(38, 0, $font_bold, $test_line);
						if ($bbox[2] > ($width - 80)) {
							$lines[] = trim($current_line);
							$current_line = $word . ' ';
						} else {
							$current_line = $test_line;
						}
					}
					$lines[] = trim($current_line);
					
					$margin_bottom = 60;
					$line_height = 55;
					$total_title_height = count($lines) * $line_height;
					
					$current_y = $height - $margin_bottom - $total_title_height + 40;
					foreach ($lines as $line) {
						imagettftext( $image, 38, 0, 40, $current_y, $white, $font_bold, $line );
						$current_y += $line_height;
					}

					$sep_y = $height - $margin_bottom - $total_title_height - 10;
					imagettftext( $image, 16, 0, 40, $sep_y, $white, $font_bold, mb_strtoupper($current_separator_title, 'UTF-8') );

				} else {
					imagestring( $image, 5, $badge_x + 20, $badge_y + 15, $mod_text, $black );
					imagestring( $image, 5, 40, $height - 100, substr($title, 0, 40), $white );
				}

				// Guardar en wp-content/uploads
				$upload_dir = wp_upload_dir();
				$filename = sanitize_title($title) . '-' . time() . '.jpg';
				$filepath = $upload_dir['path'] . '/' . $filename;
				
				imagejpeg( $image, $filepath, 90 );
				imagedestroy( $image );

				// Insertar en Media Library
				$filetype = wp_check_filetype( basename( $filepath ), null );
				$attachment = [
					'guid'           => $upload_dir['url'] . '/' . basename( $filepath ), 
					'post_mime_type' => $filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filepath ) ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				];
				$attach_id = wp_insert_attachment( $attachment, $filepath, $lesson->ID );
				$attach_data = wp_generate_attachment_metadata( $attach_id, $filepath );
				wp_update_attachment_metadata( $attach_id, $attach_data );
				
				// Asignar Thumbnail
				set_post_thumbnail( $lesson->ID, $attach_id );
			}
		}

		wp_redirect( admin_url( 'admin.php?page=crezca&status=images_success' ) );
		exit;
	}
}
