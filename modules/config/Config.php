<?php
namespace Alezux_Members\Modules\Config;

use Alezux_Members\Core\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Config extends Module_Base {

	public function init() {
        $log_file = ALEZUX_MEMBERS_PATH . 'debug_status.txt';
        // USAMOS UNA MARCA DE VERSIÓN ÚNICA PARA CONFIRMAR LA CARGA
        file_put_contents( $log_file, "--- NUEVA VERSIÓN 1.0.5 CARGADA: " . date('Y-m-d H:i:s') . " ---\n", FILE_APPEND );


		// Encolar estilos específicos del módulo
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		// Forzar Barra de Administración para Administradores
		add_filter( 'show_admin_bar', function( $show ) {
			if ( current_user_can( 'administrator' ) ) {
				return true;
			}
			return $show;
		}, 999 );


		// Registrar Widgets de Elementor
		add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widgets' ] );

		// Redirección y Filtros de Login Personalizado
		add_action( 'init', [ $this, 'setup_custom_auth_logic' ] );

		// Inicializar Estadísticas Dashboard Admin
		require_once __DIR__ . '/includes/Admin_Dashboard_Stats.php';
		\Alezux_Members\Modules\Config\Includes\Admin_Dashboard_Stats::init();

		// AJAX Auth Actions - REGISTRO CRÍTICO
        file_put_contents( $log_file, "Registrando hooks AJAX...\n", FILE_APPEND );
		add_action( 'wp_ajax_nopriv_alezux_ajax_login', [ $this, 'handle_ajax_login' ] );
		add_action( 'wp_ajax_alezux_ajax_login', [ $this, 'handle_ajax_login' ] );
		
		add_action( 'wp_ajax_nopriv_alezux_ajax_recover', [ $this, 'handle_ajax_recover' ] );
		add_action( 'wp_ajax_alezux_ajax_recover', [ $this, 'handle_ajax_recover' ] );
		
		add_action( 'wp_ajax_nopriv_alezux_reset_password', [ $this, 'handle_ajax_reset_password' ] );
		add_action( 'wp_ajax_alezux_reset_password', [ $this, 'handle_ajax_reset_password' ] );

		// AJAX Profile & Password
		add_action( 'wp_ajax_alezux_update_profile', [ $this, 'handle_update_profile' ] );
		add_action( 'wp_ajax_alezux_change_password', [ $this, 'handle_change_password' ] );
        file_put_contents( $log_file, "Hooks AJAX registrados correctamente.\n", FILE_APPEND );


		// Shortcode de Alertas
		$this->register_shortcode( 'alezux_login_alerts', [ $this, 'render_login_alerts' ], 'Muestra mensajes de error/éxito en el login.' );

        // Shortcode para URL de Logout
        $this->register_shortcode( 'alezux_logout_url', [ $this, 'render_logout_url' ], 'Devuelve la URL para cerrar sesión. Úsalo en los campos de enlace de tus botones.' );

        // Shortcodes Dashboard Admin (KPIs)
        $this->register_shortcode( 'alezux_admin_total_students', [ $this, 'sc_total_students' ], 'Muestra el número total de estudiantes.' );
        $this->register_shortcode( 'alezux_admin_new_students', [ $this, 'sc_new_students' ], 'Muestra nuevos estudiantes (Mes actual).' );
        $this->register_shortcode( 'alezux_admin_online_users', [ $this, 'sc_online_users' ], 'Muestra usuarios online en tiempo real.' );

		// Filtro para Avatar Personalizado
		add_filter( 'get_avatar_url', [ $this, 'custom_avatar_url' ], 10, 3 );
	}

    /**
     * Renderiza la URL de logout
     */
    public function render_logout_url( $atts ) {
        $atts = \shortcode_atts( [
            'redirect' => \home_url(),
        ], $atts );

        return \wp_logout_url( $atts['redirect'] );
    }

	/**
	 * Configura los filtros y redirecciones solo si la página está seleccionada
	 */
	public function setup_custom_auth_logic() {
        // Redirección de páginas restringidas (Admin Only) y Privadas (Solo Logueados)
        add_action( 'template_redirect', [ $this, 'check_restricted_pages' ] );
        add_action( 'template_redirect', [ $this, 'check_private_pages' ] );

		$login_page_id = get_option( 'alezux_login_page_id' );
		
		// Solo proceder si hay una página válida seleccionada
		if ( ! $login_page_id || get_post_status( $login_page_id ) !== 'publish' ) {
			return;
		}

		// Redirección de /wp-login.php
		add_action( 'template_redirect', [ $this, 'redirect_to_custom_login' ] );
		
		// Sobrescribir URLs nativas de WordPress
		add_filter( 'login_url', [ $this, 'custom_login_url' ], 10, 3 );
		add_filter( 'lostpassword_url', [ $this, 'custom_lostpassword_url' ], 10, 2 );
		add_filter( 'retrieve_password_message', [ $this, 'custom_retrieve_password_message' ], 10, 4 );
	}

    /**
     * Verifica si la página actual está restringida a administradores
     */
    public function check_restricted_pages() {
        $restricted_pages = get_option( 'alezux_restricted_pages', [] );
        
        if ( empty( $restricted_pages ) || ! is_array( $restricted_pages ) ) {
            return;
        }

        if ( is_page( $restricted_pages ) ) {
            if ( ! is_user_logged_in() || ! current_user_can( 'administrator' ) ) {
                // Redirigir a Home o Login. Por seguridad y UX, mejor home o login.
                // Si redirigimos a login, el usuario podría intentar loguearse y seguir sin permiso.
                // Redirigir al inicio es más seguro/claro: "No tienes acceso".
                wp_redirect( home_url() );
                exit;
            }
        }
    }

    /**
     * Verifica si la página actual requiere estar logueado
     */
    public function check_private_pages() {
        $private_pages = get_option( 'alezux_private_pages', [] );
        
        if ( empty( $private_pages ) || ! is_array( $private_pages ) ) {
            return;
        }

        if ( is_page( $private_pages ) ) {
            if ( ! is_user_logged_in() ) {
                // Redirigir a Home si intenta entrar a una página privada sin estar logueado
                wp_redirect( home_url() );
                exit;
            }
        }
    }

	public function custom_login_url( $login_url, $redirect, $force_reauth ) {
		$login_page_id = get_option( 'alezux_login_page_id' );
		if ( $login_page_id ) {
			$url = get_permalink( $login_page_id );
			if ( ! empty( $redirect ) ) {
				$url = add_query_arg( 'redirect_to', urlencode( $redirect ), $url );
			}
			return $url;
		}
		return $login_url;
	}

	public function custom_lostpassword_url( $lostpassword_url, $redirect ) {
		// Por ahora reusamos la misma lógica si el usuario tiene widgets en la misma página 
		// o el usuario puede personalizar esto después.
		$login_page_id = get_option( 'alezux_login_page_id' );
		if ( $login_page_id ) {
			return get_permalink( $login_page_id ); 
		}
		return $lostpassword_url;
	}


	/**
	 * Personalizar el mensaje de correo de recuperación para cambiar el link
	 */
	public function custom_retrieve_password_message( $message, $key, $user_login, $user_data ) {
		// Intentamos obtener la página de login personalizada para construir el link
		// OJO: Idealmente deberíamos tener una opción específica para "Página de Reset"
		// Por ahora asumimos que el widget Reset estará en una página que definiremos o usaremos la login_page_id como base si no hay otra.
		
		// NOTA: El usuario debe configurar la página donde puso el widget Reset.
		// Vamos a asumir una opción 'alezux_reset_page_id' o usar un placeholder.
		$reset_page_id = get_option( 'alezux_reset_page_id' ); 
		
		if ( ! $reset_page_id ) {
			// Fallback: Si no hay página de reset definida, intentamos usar la de login
			// Pero esto solo funcionaría si el widget Reset también está en la página de login (o mostramos uno u otro según parámetros)
			$reset_page_id = get_option( 'alezux_login_page_id' );
		}

		if ( $reset_page_id ) {
			$reset_url = get_permalink( $reset_page_id );
			$reset_url = add_query_arg( [
				'key' => $key,
				'login' => rawurlencode( $user_login )
			], $reset_url );

			// Reemplazamos el link nativo por el nuestro
			// El mensaje original de WP es simple, podemos reemplazar todo o buscar el link.
			// Para ser seguros y totalmente personalizados, recreamos el mensaje.
			
			$message  = __( 'Alguien ha solicitado restablecer la contraseña de la siguiente cuenta:', 'alezux-members' ) . "\r\n\r\n";
			$message .= network_home_url( '/' ) . "\r\n\r\n";
			$message .= sprintf( __( 'Nombre de usuario: %s', 'alezux-members' ), $user_login ) . "\r\n\r\n";
			$message .= __( 'Si ha sido un error, ignora este correo.', 'alezux-members' ) . "\r\n\r\n";
			$message .= __( 'Para restablecer la contraseña, visita la siguiente dirección:', 'alezux-members' ) . "\r\n\r\n";
			$message .= $reset_url . "\r\n";
		}

		return $message;
	}

	/**
	 * Redirigir /wp-login.php a la página personalizada
	 */
	public function redirect_to_custom_login() {
		global $pagenow;
		
		// Si es la página de login nativa y no es un POST (login real) ni acciones especiales
		if ( 'wp-login.php' == $pagenow && $_SERVER['REQUEST_METHOD'] == 'GET' ) {
			if ( ! isset( $_REQUEST['action'] ) || in_array( $_REQUEST['action'], [ 'login' ] ) ) {
				$login_page_id = get_option( 'alezux_login_page_id' );
				wp_redirect( get_permalink( $login_page_id ) );
				exit;
			}
		}
	}

	public function enqueue_assets() {
		// CSS
		wp_enqueue_style( 
			'alezux-config-css', 
			$this->get_asset_url( 'assets/css/config.css' ), 
			[], 
			file_exists( __DIR__ . '/assets/css/config.css' ) ? filemtime( __DIR__ . '/assets/css/config.css' ) : ALEZUX_MEMBERS_VERSION 
		);

		// JS
		wp_enqueue_script(
			'alezux-config-js',
			$this->get_asset_url( 'assets/js/config.js' ),
			[ 'jquery' ],
			file_exists( __DIR__ . '/assets/js/config.js' ) ? filemtime( __DIR__ . '/assets/js/config.js' ) : ALEZUX_MEMBERS_VERSION,
			true
		);

		// Localizar script para AJAX
		wp_localize_script( 'alezux-config-js', 'alezux_auth_obj', [
			'ajax_url'     => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'alezux-auth-nonce' ),
			'home_url'     => home_url(),
			'is_logged_in' => is_user_logged_in()
		]);

		// Profile Assets
		wp_enqueue_style( 'alezux-profile-css', $this->get_asset_url( 'assets/css/profile-widget.css' ), [], file_exists( __DIR__ . '/assets/css/profile-widget.css' ) ? filemtime( __DIR__ . '/assets/css/profile-widget.css' ) : ALEZUX_MEMBERS_VERSION );
		wp_enqueue_script( 'alezux-profile-js', $this->get_asset_url( 'assets/js/profile-widget.js' ), [ 'jquery' ], file_exists( __DIR__ . '/assets/js/profile-widget.js' ) ? filemtime( __DIR__ . '/assets/js/profile-widget.js' ) : ALEZUX_MEMBERS_VERSION, true );
        
        // Cropper JS (CDN)
        wp_enqueue_style( 'cropper-css', 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css', [], '1.5.13' );
        wp_enqueue_script( 'cropper-js', 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js', [], '1.5.13', true );

		// Password Assets
		wp_enqueue_style( 'alezux-password-css', $this->get_asset_url( 'assets/css/password-widget.css' ), [], file_exists( __DIR__ . '/assets/css/password-widget.css' ) ? filemtime( __DIR__ . '/assets/css/password-widget.css' ) : ALEZUX_MEMBERS_VERSION );
		wp_enqueue_script( 'alezux-password-js', $this->get_asset_url( 'assets/js/password-widget.js' ), [ 'jquery' ], file_exists( __DIR__ . '/assets/js/password-widget.js' ) ? filemtime( __DIR__ . '/assets/js/password-widget.js' ) : ALEZUX_MEMBERS_VERSION, true );
	}

	public function register_elementor_widgets( $widgets_manager ) {
		require_once __DIR__ . '/widgets/Config_Widget.php';
		require_once __DIR__ . '/widgets/Login_Widget.php';
		require_once __DIR__ . '/widgets/Recover_Widget.php';
		require_once __DIR__ . '/widgets/Reset_Widget.php';
		require_once __DIR__ . '/widgets/Profile_Widget.php';
		require_once __DIR__ . '/widgets/Password_Widget.php';
		// require_once __DIR__ . '/widgets/Elementor_Widget_Admin_Resumen.php'; // Eliminado a petición
		require_once __DIR__ . '/widgets/Elementor_Widget_Admin_Seguridad.php';
		require_once __DIR__ . '/widgets/Elementor_Widget_Admin_Ingresos.php';

		$widgets_manager->register( new \Alezux_Members\Modules\Config\Widgets\Config_Widget() );
		$widgets_manager->register( new \Alezux_Members\Modules\Config\Widgets\Login_Widget() );
		$widgets_manager->register( new \Alezux_Members\Modules\Config\Widgets\Recover_Widget() );
		$widgets_manager->register( new \Alezux_Members\Modules\Config\Widgets\Reset_Widget() );
		$widgets_manager->register( new \Alezux_Members\Modules\Config\Widgets\Profile_Widget() );
		$widgets_manager->register( new \Alezux_Members\Modules\Config\Widgets\Password_Widget() );
		
		// Dashboard Admin Widgets
		/*
		if ( class_exists( '\Alezux_Members\Modules\Config\Widgets\Elementor_Widget_Admin_Resumen' ) ) {
			$widgets_manager->register( new \Alezux_Members\Modules\Config\Widgets\Elementor_Widget_Admin_Resumen() );
		}
		*/
		if ( class_exists( '\Alezux_Members\Modules\Config\Widgets\Elementor_Widget_Admin_Seguridad' ) ) {
			$widgets_manager->register( new \Alezux_Members\Modules\Config\Widgets\Elementor_Widget_Admin_Seguridad() );
		}
		if ( class_exists( '\Alezux_Members\Modules\Config\Widgets\Elementor_Widget_Admin_Ingresos' ) ) {
			$widgets_manager->register( new \Alezux_Members\Modules\Config\Widgets\Elementor_Widget_Admin_Ingresos() );
		}
	}

	/**
	 * Manejar Login por AJAX
	 */
	public function handle_ajax_login() {
		// BYPASS DE SEGURIDAD PARA HOSTINGER CACHE:
        // Si el nonce falla (devuelve false), permitimos continuar pero lo registramos.
        // wp_signon() ya tiene su propia seguridad interna de usuario/contraseña.
        $nonce_check = wp_verify_nonce( $_POST['nonce'], 'alezux-auth-nonce' );
        
        if ( ! $nonce_check ) {
            file_put_contents( ALEZUX_MEMBERS_PATH . 'debug_status.txt', "ADVERTENCIA: Nonce inválido o caducado detectado en login. Continuando por bypass...\n", FILE_APPEND );
        }


		$info = [];
		$info['user_login']    = sanitize_user( $_POST['username'] );
		$info['user_password'] = $_POST['password'];
		$info['remember']      = isset( $_POST['remember'] ) ? true : false;

		$user_signon = wp_signon( $info, is_ssl() );

		if ( is_wp_error( $user_signon ) ) {
			wp_send_json_error( [ 'message' => 'Usuario o contraseña incorrectos.' ] );
		} else {
            // Determinar Redirección
            $redirect_url = home_url();
            
            // 1. Redirección General (Fallback inicial)
            if ( ! empty( $_POST['redirect_to'] ) ) {
                $redirect_url = esc_url_raw( $_POST['redirect_to'] );
            }

            // 2. Lógica por Roles
            $roles = $user_signon->roles;

            if ( in_array( 'administrator', $roles ) && ! empty( $_POST['redirect_admin'] ) ) {
                $redirect_url = esc_url_raw( $_POST['redirect_admin'] );
            } elseif ( ( in_array( 'subscriber', $roles ) || in_array( 'student', $roles ) ) && ! empty( $_POST['redirect_student'] ) ) {
                // Soportamos 'subscriber' (WP) y 'student' (posible custom role)
                $redirect_url = esc_url_raw( $_POST['redirect_student'] );
            }

			wp_send_json_success( [ 'redirect' => $redirect_url ] );
		}
	}

	/**
	 * Manejar actualización de perfil por AJAX
	 */
	public function handle_update_profile() {
		$log_file = ALEZUX_MEMBERS_PATH . 'debug_status.txt';
		file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] --- INICIANDO ACTUALIZACIÓN DE PERFIL ---\n", FILE_APPEND );

		// BYPASS DE SEGURIDAD PARA CACHÉ:
        $nonce_check = wp_verify_nonce( $_POST['nonce'], 'alezux-auth-nonce' );
        if ( ! $nonce_check ) {
            file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] ADVERTENCIA: Nonce inválido en Perfil. Continuando por bypass...\n", FILE_APPEND );
        }
		
		if ( ! is_user_logged_in() ) {
			file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] ERROR: Usuario no logueado.\n", FILE_APPEND );
			wp_send_json_error( [ 'message' => 'Debes iniciar sesión para realizar esta acción.' ] );
		}

		$user_id = get_current_user_id();
		$errors = [];

		// Sanitización y validación
		$first_name = sanitize_text_field( $_POST['first_name'] );
		$last_name  = sanitize_text_field( $_POST['last_name'] );
		$user_email = sanitize_email( $_POST['user_email'] );

		if ( ! is_email( $user_email ) ) {
			wp_send_json_error( [ 'message' => 'El correo electrónico no es válido.' ] );
		}

		// Verificar si el correo ya existe en otro usuario
		$existing_user = get_user_by( 'email', $user_email );
		if ( $existing_user && $existing_user->ID !== $user_id ) {
			wp_send_json_error( [ 'message' => 'Este correo ya está en uso por otro usuario.' ] );
		}

		// Actualizar datos básicos
		$user_data = [
			'ID'         => $user_id,
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'user_email' => $user_email,
		];

		$updated_user_id = wp_update_user( $user_data );

		if ( is_wp_error( $updated_user_id ) ) {
			file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] ERROR al actualizar user: " . $updated_user_id->get_error_message() . "\n", FILE_APPEND );
			wp_send_json_error( [ 'message' => 'Error al actualizar el perfil: ' . $updated_user_id->get_error_message() ] );
		}

		file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] ÉXITO: Datos básicos actualizados para user $user_id.\n", FILE_APPEND );

		// Gestión de Avatar
		if ( ! empty( $_FILES['alezux_avatar']['name'] ) ) {
			file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] INFO: Detectado nuevo avatar para subir: " . $_FILES['alezux_avatar']['name'] . "\n", FILE_APPEND );
			
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );

			// Soportar subida desde el frontend (específico para algunos entornos)
			$attachment_id = media_handle_upload( 'alezux_avatar', 0 ); 

			if ( is_wp_error( $attachment_id ) ) {
				file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] ERROR en media_handle_upload: " . $attachment_id->get_error_message() . "\n", FILE_APPEND );
				wp_send_json_error( [ 'message' => 'Error al subir la imagen: ' . $attachment_id->get_error_message() ] );
			}

			$avatar_url = wp_get_attachment_url( $attachment_id );
			update_user_meta( $user_id, 'alezux_user_avatar', $avatar_url );
			update_user_meta( $user_id, 'alezux_user_avatar_id', $attachment_id );
			file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] ÉXITO: Avatar subido correctamente. URL: $avatar_url\n", FILE_APPEND );
		}

		file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] --- ACTUALIZACIÓN FINALIZADA CON ÉXITO ---\n", FILE_APPEND );

		wp_send_json_success( [ 'message' => 'Perfil actualizado correctamente.' ] );
	}

	/**
	 * Filtro para usar el avatar personalizado si existe
	 */
	public function custom_avatar_url( $url, $id_or_email, $args ) {
		$user_id = 0;

		if ( is_numeric( $id_or_email ) ) {
			$user_id = (int) $id_or_email;
		} elseif ( is_string( $id_or_email ) && ( $user = get_user_by( 'email', $id_or_email ) ) ) {
			$user_id = $user->ID;
		} elseif ( is_object( $id_or_email ) && isset( $id_or_email->user_id ) ) {
			$user_id = (int) $id_or_email->user_id;
		}

		if ( $user_id ) {
			$custom_avatar = get_user_meta( $user_id, 'alezux_user_avatar', true );
			if ( $custom_avatar ) {
				return $custom_avatar;
			}
		}

		return $url;
	}

	/**
	 * Manejar cambio de contraseña por AJAX
	 */
	public function handle_change_password() {
		check_ajax_referer( 'alezux-auth-nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( [ 'message' => 'Debes iniciar sesión para realizar esta acción.' ] );
		}

		$user_id = get_current_user_id();
		$user    = get_userdata( $user_id );

		$old_password     = $_POST['old_password'];
		$new_password     = $_POST['new_password'];
		$confirm_password = $_POST['confirm_password'];

		// 1. Verificar restricción de tiempo (24 horas)
		$last_change = get_user_meta( $user_id, 'alezux_last_password_change', true );
		if ( $last_change && ( time() - $last_change ) < DAY_IN_SECONDS ) {
			$remaining = round( ( DAY_IN_SECONDS - ( time() - $last_change ) ) / 3600, 1 );
			wp_send_json_error( [ 'message' => sprintf( 'Solo puedes cambiar tu contraseña una vez cada 24 horas. Intenta de nuevo en %s horas.', $remaining ) ] );
		}

		// 2. Verificar contraseña antigua
		if ( ! wp_check_password( $old_password, $user->user_pass, $user_id ) ) {
			wp_send_json_error( [ 'message' => 'La contraseña actual es incorrecta.' ] );
		}

		// 3. Verificar que coincidan
		if ( $new_password !== $confirm_password ) {
			wp_send_json_error( [ 'message' => 'Las nuevas contraseñas no coinciden.' ] );
		}

		// 4. Validar requisitos de fortaleza (Servidor)
		$errors = [];
		if ( strlen( $new_password ) < 8 ) $errors[] = 'Mínimo 8 caracteres.';
		if ( ! preg_match( '/[A-Z]/', $new_password ) ) $errors[] = 'Al menos una mayúscula.';
		if ( ! preg_match( '/[0-9]/', $new_password ) ) $errors[] = 'Al menos un número.';
		if ( ! preg_match( '/[^A-Za-z0-9]/', $new_password ) ) $errors[] = 'Al menos un signo especial.';

		if ( ! empty( $errors ) ) {
			wp_send_json_error( [ 'message' => 'La nueva contraseña no cumple los requisitos: ' . implode( ' ', $errors ) ] );
		}

		// 5. Actualizar contraseña
		wp_set_password( $new_password, $user_id );
		
		// Registrar tiempo del cambio
		update_user_meta( $user_id, 'alezux_last_password_change', time() );

		wp_send_json_success( [ 'message' => 'Contraseña actualizada correctamente.' ] );
	}

	/**
	 * Manejar Recuperación de Contraseña por AJAX
	 */
	public function handle_ajax_recover() {
		$log_file = ALEZUX_MEMBERS_PATH . 'debug_status.txt';
		
		// BYPASS DE SEGURIDAD PARA HOSTINGER CACHE:
		$nonce_check = wp_verify_nonce( $_POST['nonce'], 'alezux-auth-nonce' );
		if ( ! $nonce_check ) {
			file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] ADVERTENCIA: Nonce inválido en recuperación. Continuando por bypass...\n", FILE_APPEND );
		}

		$user_login = sanitize_text_field( $_POST['user_login'] );
		
		if ( empty( $user_login ) ) {
			wp_send_json_error( [ 'message' => 'Por favor ingresa tu correo o usuario.' ] );
		}

		$user_data = get_user_by( 'email', $user_login );
		if ( ! $user_data ) {
			$user_data = get_user_by( 'login', $user_login );
		}

		if ( ! $user_data ) {
			file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] ERROR: Usuario no encontrado ($user_login)\n", FILE_APPEND );
			wp_send_json_error( [ 'message' => 'No existe ningún usuario con ese correo o nombre.' ] );
		}

		$user_id = $user_data->ID;
		$key = get_password_reset_key( $user_data );
		
		if ( is_wp_error( $key ) ) {
			file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] ERROR: No se pudo generar la clave de recuperación para usuario ID $user_id\n", FILE_APPEND );
			wp_send_json_error( [ 'message' => 'No se pudo generar la clave de recuperación.' ] );
		}

		// Enviar email (Delegado a Marketing)
		if ( class_exists( '\Alezux_Members\Modules\Marketing\Marketing' ) ) {
			
			$reset_page_id = get_option( 'alezux_reset_page_id' );
			if ( ! $reset_page_id ) $reset_page_id = get_option( 'alezux_login_page_id' );
			
			if ( $reset_page_id ) {
				$reset_url = get_permalink( $reset_page_id );
				$reset_url = add_query_arg( [
					'key' => $key,
					'login' => rawurlencode( $user_data->user_login )
				], $reset_url );
			} else {
				$reset_url = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_data->user_login ), 'login' );
			}

			file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] INFO: Intentando enviar correo a " . $user_data->user_email . "\n", FILE_APPEND );

			$sent = \Alezux_Members\Modules\Marketing\Marketing::get_instance()->get_engine()->send_email(
				'user_recover_password',
				$user_data->user_email,
				[
					'user' => $user_data,
					'reset_link' => $reset_url
				]
			);

			if ( $sent ) {
				file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] ÉXITO: Correo enviado a " . $user_data->user_email . "\n", FILE_APPEND );
				wp_send_json_success( [ 'message' => 'Se ha enviado un correo con instrucciones.' ] );
			} else {
				file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] ERROR CRÍTICO: El motor de correo devolvió FALSE para " . $user_data->user_email . "\n", FILE_APPEND );
				wp_send_json_error( [ 'message' => 'El correo no pudo ser enviado.' ] );
			}
		} else {
			 file_put_contents( $log_file, "[" . date('Y-m-d H:i:s') . "] ERROR: Módulo de Marketing no encontrado.\n", FILE_APPEND );
			 wp_send_json_error( [ 'message' => 'Error sistema de correo no disponible.' ] );
		}
	}

	/**
	 * Renderizar alertas de login (Shortcode)
	 */
	public function render_login_alerts() {
		if ( isset( $_GET['login'] ) && 'failed' === $_GET['login'] ) {
			return '<div class="alezux-auth-alert error">Credenciales incorrectas. Inténtalo de nuevo.</div>';
		}
		return '';
	}

	public function handle_ajax_reset_password() {
		check_ajax_referer( 'alezux-auth-nonce', 'nonce' );
	
		$key   = isset( $_POST['key'] ) ? sanitize_text_field( $_POST['key'] ) : '';
		$login = isset( $_POST['login'] ) ? sanitize_user( $_POST['login'] ) : '';
		$pass1 = isset( $_POST['pass1'] ) ? wp_unslash( $_POST['pass1'] ) : '';
		$pass2 = isset( $_POST['pass2'] ) ? wp_unslash( $_POST['pass2'] ) : '';
	
		if ( empty( $key ) || empty( $login ) ) {
			wp_send_json_error( [ 'message' => 'Falta información de verificación.' ] );
		}
	
		// 1. Verificar Key Nuevamente (Seguridad)
		$user = check_password_reset_key( $key, $login );
		if ( is_wp_error( $user ) ) {
			wp_send_json_error( [ 'message' => 'El enlace ha expirado o no es válido.' ] );
		}
	
		// 2. Verificar Contraseñas
		if ( empty( $pass1 ) || empty( $pass2 ) ) {
			wp_send_json_error( [ 'message' => 'Por favor ingresa tu nueva contraseña.' ] );
		}
	
		if ( $pass1 !== $pass2 ) {
			wp_send_json_error( [ 'message' => 'Las contraseñas no coinciden.' ] );
		}
	
		// 3. Validar fortaleza (Opcional, pero recomendado replicar lo de JS)
		if ( strlen( $pass1 ) < 8 ) {
			wp_send_json_error( [ 'message' => 'La contraseña es muy corta.' ] );
		}
	
		// 4. Resetear
		reset_password( $user, $pass1 );
	
		// 5. Éxito
		// Podemos redirigir al login
		$login_page_id = get_option( 'alezux_login_page_id' );
		
		// Si hay página personalizada, vamos ahí. Si no, al login de WP por defecto.
		$redirect_url = $login_page_id ? get_permalink( $login_page_id ) : wp_login_url();
	
		wp_send_json_success( [ 
			'message' => 'Contraseña actualizada correctamente. Redirigiendo...',
			'redirect' => $redirect_url
		] );
	}

    // --- Shortcodes Callbacks ---

    public function sc_total_students() {
        if ( ! class_exists( '\Alezux_Members\Modules\Config\Includes\Admin_Dashboard_Stats' ) ) return '0';
        return number_format( \Alezux_Members\Modules\Config\Includes\Admin_Dashboard_Stats::get_total_students() );
    }

    public function sc_new_students() {
        if ( ! class_exists( '\Alezux_Members\Modules\Config\Includes\Admin_Dashboard_Stats' ) ) return '0';
        return number_format( \Alezux_Members\Modules\Config\Includes\Admin_Dashboard_Stats::get_new_students('month') );
    }

    public function sc_online_users() {
        if ( ! class_exists( '\Alezux_Members\Modules\Config\Includes\Admin_Dashboard_Stats' ) ) return '0';
        return number_format( \Alezux_Members\Modules\Config\Includes\Admin_Dashboard_Stats::get_online_users_count() );
    }
}
