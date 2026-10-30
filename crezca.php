<?php
/**
 * Plugin Name: Crezca
 * Description: Sistema integral y modular para gestión de academias, membresías, cursos en línea, estudiantes, finanzas, marketing y panel de administración moderno impulsado por Arc UI.
 * Version: 2.1.0
 * Author: Crezca
 * Text Domain: crezca
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Desactivar pantalla generica de WordPress y exponer error exacto
add_filter( 'wp_fatal_error_handler_enabled', '__return_false' );
register_shutdown_function( function() {
	$err = error_get_last();
	if ( $err && in_array( $err['type'], [ E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR ], true ) ) {
		@file_put_contents( __DIR__ . '/debug_error.log', date('c') . ' - ' . print_r($err, true) . "\n", FILE_APPEND );
		echo '<div style="background:#fee2e2;color:#991b1b;padding:24px;margin:20px;border:2px solid #ef4444;border-radius:8px;font-family:monospace;font-size:14px;z-index:999999;position:relative;">';
		echo '<h3 style="margin-top:0;color:#b91c1c;">⚠️ Error Fatal PHP Detectado</h3>';
		echo '<strong>Mensaje:</strong> ' . htmlspecialchars( $err['message'] ) . '<br>';
		echo '<strong>Archivo:</strong> ' . htmlspecialchars( $err['file'] ) . '<br>';
		echo '<strong>Línea:</strong> ' . (int) $err['line'] . '<br>';
		echo '</div>';
	}
} );

// Definir constantes del plugin Crezca
define( 'CREZCA_VERSION', '2.1.0' );
define( 'CREZCA_FILE', __FILE__ );
define( 'CREZCA_PATH', plugin_dir_path( __FILE__ ) );
define( 'CREZCA_URL', plugin_dir_url( __FILE__ ) );
define( 'CREZCA_MODULES_PATH', CREZCA_PATH . 'modules/' );

// Aliases de constantes para retrocompatibilidad
if ( ! defined( 'ALEZUX_MEMBERS_VERSION' ) ) { define( 'ALEZUX_MEMBERS_VERSION', CREZCA_VERSION ); }
if ( ! defined( 'ALEZUX_MEMBERS_PATH' ) ) { define( 'ALEZUX_MEMBERS_PATH', CREZCA_PATH ); }
if ( ! defined( 'ALEZUX_MEMBERS_URL' ) ) { define( 'ALEZUX_MEMBERS_URL', CREZCA_URL ); }
if ( ! defined( 'ALEZUX_MEMBERS_MODULES_PATH' ) ) { define( 'ALEZUX_MEMBERS_MODULES_PATH', CREZCA_MODULES_PATH ); }

// Autoloader para clases del Core (soporta namespace Crezca\Core\ y retrocompatibilidad)
spl_autoload_register( function ( $class ) {
	$prefixes = [
		'Crezca\\Core\\'        => CREZCA_PATH . 'core/',
		'Alezux_Members\\Core\\' => CREZCA_PATH . 'core/',
	];

	foreach ( $prefixes as $prefix => $base_dir ) {
		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) === 0 ) {
			$relative_class = substr( $class, $len );
			$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;
				return;
			}
		}
	}
} );

// Inicializar el plugin Crezca
function crezca_init() {
	// 1. Inicializar API REST para el Dashboard Arc UI
	if ( class_exists( 'Crezca\\Core\\Admin_Api' ) ) {
		$admin_api = new \Crezca\Core\Admin_Api();
		$admin_api->init();
	} elseif ( class_exists( 'Alezux_Members\\Core\\Admin_Api' ) ) {
		$admin_api = new \Alezux_Members\Core\Admin_Api();
		$admin_api->init();
	}

	// 2. Inicializar Dashboard si estamos en admin
	if ( is_admin() ) {
		if ( class_exists( 'Crezca\\Core\\Admin_Dashboard' ) ) {
			$dashboard = new \Crezca\Core\Admin_Dashboard();
			$dashboard->init();
		} elseif ( class_exists( 'Alezux_Members\\Core\\Admin_Dashboard' ) ) {
			$dashboard = new \Alezux_Members\Core\Admin_Dashboard();
			$dashboard->init();
		}
	}

	// 3. Inicializar el Plugin Loader y Módulos
	if ( class_exists( 'Crezca\\Core\\Plugin_Loader' ) ) {
		$loader = new \Crezca\Core\Plugin_Loader();
		$loader->run();
	} elseif ( class_exists( 'Alezux_Members\\Core\\Plugin_Loader' ) ) {
		$loader = new \Alezux_Members\Core\Plugin_Loader();
		$loader->run();
	}

	// 4. Inicializar el motor LMS propio (Dual-Engine / Abstraction Layer)
	if ( class_exists( 'Crezca\\Core\\LMS_Engine' ) ) {
		\Crezca\Core\LMS_Engine::get_instance()->init();
	} elseif ( class_exists( 'Alezux_Members\\Core\\LMS_Engine' ) ) {
		\Alezux_Members\Core\LMS_Engine::get_instance()->init();
	}
}
add_action( 'plugins_loaded', 'crezca_init' );

/**
 * Registrar el rol nativo de Docente / Profesor en WordPress
 */
function crezca_register_instructor_role() {
	if ( ! get_role( 'crezca_instructor' ) ) {
		add_role( 'crezca_instructor', 'Docente Crezca', [
			'read'         => true,
			'upload_files' => true,
			'edit_posts'   => true,
		] );
	}
}
add_action( 'init', 'crezca_register_instructor_role' );

/**
 * Determinar si un usuario es Docente / Instructor en la plataforma
 */
function crezca_is_user_instructor( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	if ( ! $user_id ) {
		return false;
	}
	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return false;
	}
	$roles = (array) $user->roles;
	if ( in_array( 'crezca_instructor', $roles, true ) || in_array( 'instructor', $roles, true ) ) {
		return true;
	}
	$global_instructors = get_option( '_alezux_global_instructors', [] );
	if ( is_array( $global_instructors ) ) {
		foreach ( $global_instructors as $gi ) {
			if ( ! empty( $gi['userId'] ) && (int) $gi['userId'] === (int) $user_id ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Encolar estilos globales del plugin
 */
function crezca_enqueue_global_assets() {
	wp_enqueue_style( 
		'crezca-global', 
		CREZCA_URL . 'assets/css/global.css', 
		[], 
		CREZCA_VERSION 
	);

	$primary       = get_option( 'crezca_primary_color', get_option( 'alezux_primary_color', '#7747ff' ) );
	$primary_hover = get_option( 'crezca_primary_hover', get_option( 'alezux_primary_hover', '#5528ce' ) );
	$bg_base       = get_option( 'crezca_bg_base', get_option( 'alezux_bg_base', '#0f0f0f' ) );
	$bg_card       = get_option( 'crezca_bg_card', get_option( 'alezux_bg_card', '#1a1a1a' ) );
	$border_radius = get_option( 'crezca_border_radius', get_option( 'alezux_border_radius', '50px' ) );
	$border_color  = get_option( 'crezca_border_color', get_option( 'alezux_border_color', '#333333' ) );
	$box_shadow    = get_option( 'crezca_box_shadow', get_option( 'alezux_box_shadow', '0 10px 30px rgba(0, 0, 0, 0.3)' ) );

	$custom_css = "
		:root {
			--crezca-primary: {$primary};
			--crezca-primary-hover: {$primary_hover};
			--crezca-bg-base: {$bg_base};
			--crezca-bg-card: {$bg_card};
			--crezca-border-radius: {$border_radius};
			--crezca-border-color: {$border_color};
			--crezca-box-shadow: {$box_shadow};
			--alezux-primary: {$primary};
			--alezux-primary-hover: {$primary_hover};
			--alezux-bg-base: {$bg_base};
			--alezux-bg-card: {$bg_card};
		}
	";
	
	wp_add_inline_style( 'crezca-global', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'crezca_enqueue_global_assets' );
add_action( 'elementor/frontend/after_enqueue_styles', 'crezca_enqueue_global_assets' );

/**
 * Forzar carga de FontAwesome en el frontend para widgets de Elementor
 */
add_action( 'elementor/frontend/after_enqueue_styles', function() {
	wp_enqueue_style( 'elementor-icons-shared-0' );
	wp_enqueue_style( 'elementor-icons-fa-solid' );
	wp_enqueue_style( 'elementor-icons-fa-regular' );
	wp_enqueue_style( 'elementor-icons-fa-brands' );
} );

/**
 * Inyectar CSS de 'Solo Admin' en el Frontend
 */
add_action( 'wp_head', function() {
    if ( current_user_can( 'administrator' ) ) {
        return;
    }

    $css_classes = get_option( 'crezca_admin_only_css_classes', get_option( 'alezux_admin_only_css_classes', '' ) );
    
    if ( ! empty( $css_classes ) ) {
        $classes_array = explode( ',', $css_classes );
        $selector_parts = [];

        foreach ( $classes_array as $class ) {
            $class = trim( $class );
            if ( ! empty( $class ) ) {
                if ( strpos( $class, '.' ) === 0 ) {
                    $selector_parts[] = $class;
                } else {
                    $selector_parts[] = '.' . $class;
                }
            }
        }

        if ( ! empty( $selector_parts ) ) {
            $final_selector = implode( ', ', $selector_parts );
            echo '<style id="crezca-admin-only-css">';
            echo $final_selector . ' { display: none !important; }';
            echo '</style>';
        }
    }
} );

/**
 * Categorías en Elementor
 */
add_action( 'elementor/elements/categories_registered', function( $elements_manager ) {
	$categories = [
		'crezca-auth'        => [
			'title' => esc_html__( 'Crezca: Autenticación', 'crezca' ),
			'icon'  => 'eicon-lock-user',
		],
		'crezca-perfil'      => [
			'title' => esc_html__( 'Crezca: Perfil & Config', 'crezca' ),
			'icon'  => 'eicon-user-circle-o',
		],
		'crezca-finanzas'    => [
			'title' => esc_html__( 'Crezca: Finanzas', 'crezca' ),
			'icon'  => 'eicon-price-table',
		],
		'crezca-estudiantes' => [
			'title' => esc_html__( 'Crezca: Estudiantes', 'crezca' ),
			'icon'  => 'eicon-person',
		],
		'crezca-lms'         => [
			'title' => esc_html__( 'Crezca: LMS & Contenido', 'crezca' ),
			'icon'  => 'eicon-edu-cap',
		],
		'crezca-otros'       => [
			'title' => esc_html__( 'Crezca: Utilidades', 'crezca' ),
			'icon'  => 'eicon-tools',
		],
	];

	foreach ( $categories as $id => $config ) {
		$elements_manager->add_category( $id, $config );
	}
} );

/**
 * Ocultar la barra de administración de WordPress en páginas de Crezca / Alezux y para estudiantes
 */
add_filter( 'show_admin_bar', function( $show ) {
    if ( function_exists( 'is_admin' ) && is_admin() && isset( $_GET['page'] ) && in_array( $_GET['page'], [ 'crezca', 'alezux-members' ], true ) ) {
        return false;
    }
    if ( function_exists( 'current_user_can' ) && ! current_user_can( 'edit_posts' ) ) {
        return false;
    }
    return $show;
} );

/**
 * Obtener configuración de login y autenticación personalizada
 */
function crezca_get_auth_settings() {
	$defaults = [
		'logo_url'               => get_option( 'alezux_academy_logo', '' ),
		'logo_width'             => 160,
		'bg_type'                => 'gradient', // 'gradient' | 'solid' | 'image'
		'bg_color'               => '#090d16',
		'bg_gradient_start'      => '#1d68ed',
		'bg_gradient_end'        => '#0b3294',
		'bg_image_url'           => '',
		'accent_color'           => '#1d68ed',
		'hero_badge_enabled'     => true,
		'hero_badge_text'        => 'Join Us to Build 🤩',
		'hero_title'             => 'Start your Journey',
		'hero_subtitle'          => 'Follow these simple steps to set up your account and start learning today.',
		'steps_enabled'          => true,
		'step_1_num'             => '1',
		'step_1_text'            => "Register your\naccount",
		'step_2_num'             => '2',
		'step_2_text'            => "Set up your profile\ninformation",
		'step_3_num'             => '3',
		'step_3_text'            => "Access your courses\ninstantly",
		'login_title'            => 'Welcome Back',
		'login_subtitle'         => 'Inicia sesión en tu cuenta para continuar',
		'allow_registration'     => false,
		'terms_url'              => get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/terminos-y-condiciones' ),
		'privacy_url'            => get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/politica-de-privacidad' ),
		'custom_css'             => '',
		'sync_academy_colors'    => true,
		'student_default_view'   => 'courses', // 'courses' | 'dashboard'
		'admin_default_redirect' => 'academy', // 'academy' | 'wp'
	];

	$saved = get_option( 'crezca_auth_settings', [] );
	if ( ! is_array( $saved ) ) {
		$saved = [];
	}

	return wp_parse_args( $saved, $defaults );
}

/**
 * Obtener la URL de la página de Login personalizada de Crezca
 */
function crezca_get_login_page_url() {
	$login_page_id = (int) get_option( 'alezux_login_page_id' );
	if ( $login_page_id && get_post_status( $login_page_id ) === 'publish' ) {
		return get_permalink( $login_page_id );
	}

	$slugs = [ 'login', 'iniciar-sesion', 'acceso', 'auth', 'ingreso' ];
	foreach ( $slugs as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page && $page->post_status === 'publish' ) {
			update_option( 'alezux_login_page_id', (int) $page->ID );
			return get_permalink( $page->ID );
		}
	}

	// Auto-provisionar la página de Login si aún no existe
	if ( function_exists( 'wp_insert_post' ) ) {
		$new_page_id = wp_insert_post( [
			'post_title'     => 'Iniciar Sesión',
			'post_name'      => 'login',
			'post_content'   => '',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		] );

		if ( $new_page_id && ! is_wp_error( $new_page_id ) ) {
			update_post_meta( $new_page_id, '_wp_page_template', 'elementor_canvas' );
			update_option( 'alezux_login_page_id', (int) $new_page_id );
			return get_permalink( $new_page_id );
		}
	}

	return home_url( '/login/' );
}

/**
 * Obtener la URL del Campus Virtual / Portal del Estudiante
 */
function crezca_get_student_portal_url() {
	$student_page_id = (int) get_option( 'alezux_student_dashboard_page_id' );
	if ( $student_page_id && get_post_status( $student_page_id ) === 'publish' ) {
		return get_permalink( $student_page_id );
	}

	global $wpdb;
	$found_page_id = $wpdb->get_var( "
		SELECT ID FROM {$wpdb->posts}
		WHERE post_type = 'page'
		  AND post_status = 'publish'
		  AND (post_content LIKE '%[crezca_aula%' OR post_content LIKE '%[alezux_aula%')
		ORDER BY ID ASC LIMIT 1
	" );

	if ( $found_page_id ) {
		update_option( 'alezux_student_dashboard_page_id', (int) $found_page_id );
		return get_permalink( $found_page_id );
	}

	$slugs = [ 'campus', 'aula', 'cursos', 'estudiantes', 'mi-cuenta' ];
	foreach ( $slugs as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page && $page->post_status === 'publish' ) {
			if ( strpos( $page->post_content, 'crezca_aula' ) === false && strpos( $page->post_content, 'alezux_aula' ) === false ) {
				wp_update_post( [
					'ID'           => $page->ID,
					'post_content' => '[crezca_aula]',
				] );
			}
			update_option( 'alezux_student_dashboard_page_id', (int) $page->ID );
			return get_permalink( $page->ID );
		}
	}

	// Auto-provisionar la página del Campus Virtual si aún no existe
	if ( function_exists( 'wp_insert_post' ) ) {
		$new_page_id = wp_insert_post( [
			'post_title'     => 'Campus Virtual',
			'post_name'      => 'campus',
			'post_content'   => '[crezca_aula]',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		] );

		if ( $new_page_id && ! is_wp_error( $new_page_id ) ) {
			update_post_meta( $new_page_id, '_wp_page_template', 'elementor_canvas' );
			update_option( 'alezux_student_dashboard_page_id', (int) $new_page_id );
			return get_permalink( $new_page_id );
		}
	}

	return home_url( '/campus/' );
}

/**
 * Shortcode [crezca_aula] / [alezux_aula] para renderizar el Campus Virtual de Estudiantes
 */
function crezca_render_student_portal_shortcode( $atts = [] ) {
    add_filter( 'show_admin_bar', '__return_false' );
	$dist_css = CREZCA_PATH . 'assets/dist/alezux-dashboard.css';
	$dist_js  = CREZCA_PATH . 'assets/dist/alezux-dashboard.js';
	
	if ( ! file_exists( $dist_js ) ) {
		return '<div style="padding:40px;text-align:center;color:#94a3b8;background:#090a0f;">Cargando aula virtual de estudiantes...</div>';
	}
	
	$js_ver  = filemtime( $dist_js );
	$css_ver = file_exists( $dist_css ) ? filemtime( $dist_css ) : time();
	
	wp_enqueue_style( 'crezca-inter-font', 'https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap', [], null );
	wp_enqueue_style( 'crezca-dashboard-style', CREZCA_URL . 'assets/dist/alezux-dashboard.css', [ 'crezca-inter-font' ], $css_ver );
	wp_enqueue_script( 'crezca-dashboard-script', CREZCA_URL . 'assets/dist/alezux-dashboard.js', [], $js_ver, true );
	
	$current_user  = wp_get_current_user();
	$is_blocked    = ( $current_user && $current_user->ID ) ? (bool) get_user_meta( $current_user->ID, 'alezux_is_blocked', true ) : false;
	$academy_name  = get_option( 'alezux_academy_name', 'Academia' );
	$academy_logo  = get_option( 'alezux_academy_logo', '' );
	$support_wa    = get_option( 'alezux_support_whatsapp', '' );
	$support_email = get_option( 'admin_email', '' );
	$accent        = get_option( 'alezux_accent', 'violet' );
	$theme         = get_option( 'alezux_theme', 'dark' );
	$auth_settings = crezca_get_auth_settings();
	$is_admin      = current_user_can( 'manage_options' );
	$is_instructor = ! $is_admin && crezca_is_user_instructor( $current_user ? $current_user->ID : 0 );
	$student_view  = ! empty( $auth_settings['student_default_view'] ) ? $auth_settings['student_default_view'] : 'courses';

	$accent_colors = [
		'violet'  => '#7747ff',
		'emerald' => '#10b981',
		'blue'    => '#3b82f6',
		'amber'   => '#f59e0b',
		'rose'    => '#f43f5e',
		'cyan'    => '#06b6d4',
	];
	$accent_color = isset( $accent_colors[ $accent ] ) ? $accent_colors[ $accent ] : '#7747ff';
	$is_light     = ( 'light' === $theme );
	$bg_color     = $is_light ? '#f8fafc' : '#090a0f';
	$text_color   = $is_light ? '#0f172a' : '#f8fafc';
	$muted_color  = $is_light ? '#64748b' : '#94a3b8';

	$data = [
		'root_url'             => esc_url_raw( rest_url( 'crezca/v1/' ) ),
		'legacy_root_url'      => esc_url_raw( rest_url( 'alezux/v1/' ) ),
		'nonce'                => wp_create_nonce( 'wp_rest' ),
		'is_student_mode'      => true,
		'is_logged_in'         => is_user_logged_in(),
		'user_id'              => $current_user ? $current_user->ID : 0,
		'is_admin'             => $is_admin,
		'is_instructor'        => $is_instructor,
		'user_role'            => $is_admin ? 'admin' : ( $is_instructor ? 'instructor' : 'student' ),
		'is_blocked'           => $is_blocked,
		'support_whatsapp'     => $support_wa,
		'support_email'        => $support_email,
		'initial_tab'          => $student_view,
		'student_default_view' => $student_view,
		'academy_name'         => $academy_name,
		'academy_logo'         => $academy_logo,
		'accent'               => $accent,
		'theme'                => $theme,
		'wp_admin_url'         => $is_admin ? esc_url_raw( admin_url( 'index.php' ) ) : '',
		'login_url'            => wp_login_url( get_permalink() ),
		'logout_url'           => home_url( '/?crezca_logout=1' ),
	];

	ob_start();
	?>
	<script id="crezca-student-portal-data">
		window.crezca_student_data = <?php echo json_encode( $data ); ?>;
		window.crezca_admin_data = window.crezca_admin_data || window.crezca_student_data;
	</script>
	<div id="crezca-student-root" data-mode="student" style="min-height:100vh;background:<?php echo esc_attr( $bg_color ); ?>;"></div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'crezca_aula', 'crezca_render_student_portal_shortcode' );
add_shortcode( 'alezux_aula', 'crezca_render_student_portal_shortcode' );

/**
 * Endpoint de cierre de sesión limpio para estudiantes y usuarios de Crezca
 * Bypasea cualquier pantalla o aviso nativo de confirmación de WordPress
 */
add_action( 'init', 'crezca_clean_logout_handler' );
function crezca_clean_logout_handler() {
	if ( isset( $_GET['crezca_logout'] ) || ( isset( $_GET['action'] ) && $_GET['action'] === 'crezca_logout' ) ) {
		wp_logout();
		$login_url = crezca_get_login_page_url();
		wp_safe_redirect( add_query_arg( 'loggedout', 'true', $login_url ) );
		exit;
	}
}

/**
 * Intercepta cualquier llamada nativa a wp-login.php?action=logout para cerrar sesión directo
 * y evitar la pantalla de confirmación "¿Realmente deseas cerrar sesión?" de WordPress.
 */
add_action( 'login_init', 'crezca_intercept_wp_logout_prompt', 1 );
function crezca_intercept_wp_logout_prompt() {
	if ( isset( $_GET['action'] ) && $_GET['action'] === 'logout' ) {
		wp_logout();
		$login_url = crezca_get_login_page_url();
		wp_safe_redirect( add_query_arg( 'loggedout', 'true', $login_url ) );
		exit;
	}
}

/**
 * Intercepta accesos a ?tab=courses en la raíz o páginas del tema para redirigir al Campus Virtual
 */
add_action( 'template_redirect', 'crezca_redirect_student_tabs_to_campus', 5 );
function crezca_redirect_student_tabs_to_campus() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}

	if ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], [ 'courses', 'dashboard', 'community', 'profile' ], true ) ) {
		// Si no está autenticado, redirigir a login directamente
		if ( ! is_user_logged_in() ) {
			$login_url     = crezca_get_login_page_url();
			$portal_url    = crezca_get_student_portal_url();
			$redirect_dest = add_query_arg( 'tab', sanitize_text_field( $_GET['tab'] ), $portal_url );
			wp_safe_redirect( add_query_arg( 'redirect_to', urlencode( $redirect_dest ), $login_url ) );
			exit;
		}

		$portal_url     = crezca_get_student_portal_url();
		$portal_page_id = (int) get_option( 'alezux_student_dashboard_page_id' );

		// Si no estamos en la página del portal del estudiante, redirigir
		if ( ! is_page( $portal_page_id ) ) {
			$target = add_query_arg( 'tab', sanitize_text_field( $_GET['tab'] ), $portal_url );
			$current_req = ( is_ssl() ? 'https://' : 'http://' ) . ( isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '' ) . ( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' );
			if ( untrailingslashit( $current_req ) !== untrailingslashit( $target ) ) {
				wp_safe_redirect( $target );
				exit;
			}
		}
	}
}

/**
 * Sobrescribe la plantilla del Campus Virtual para renderizado Canvas a pantalla completa sin interferencias del tema
 */
add_filter( 'template_include', 'crezca_student_portal_canvas_template', 9999 );
function crezca_student_portal_canvas_template( $template ) {
	if ( is_admin() || ( class_exists( '\\Elementor\\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) || isset( $_GET['elementor-preview'] ) ) {
		return $template;
	}

	$portal_page_id = (int) get_option( 'alezux_student_dashboard_page_id' );
	global $post;

	$is_portal = false;
	if ( $portal_page_id > 0 && is_page( $portal_page_id ) ) {
		$is_portal = true;
	} elseif ( is_a( $post, 'WP_Post' ) && ( has_shortcode( $post->post_content, 'crezca_aula' ) || has_shortcode( $post->post_content, 'alezux_aula' ) ) ) {
		$is_portal = true;
	} elseif ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], [ 'courses', 'dashboard', 'community', 'profile' ], true ) ) {
		$is_portal = true;
	}

	if ( $is_portal ) {
		// Si es acceso no autenticado al aula, redirigir al login personalizado
		if ( ! is_user_logged_in() ) {
			$login_url     = crezca_get_login_page_url();
			$current_url   = ( is_ssl() ? 'https://' : 'http://' ) . ( isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '' ) . ( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' );
			wp_safe_redirect( add_query_arg( 'redirect_to', urlencode( $current_url ), $login_url ) );
			exit;
		}
		add_filter( 'show_admin_bar', '__return_false' );

		$theme        = get_option( 'alezux_theme', 'dark' );
		$is_light     = ( 'light' === $theme );
		$bg_color     = $is_light ? '#f8fafc' : '#090a0f';
		$text_color   = $is_light ? '#0f172a' : '#f8fafc';
		$academy_name = get_option( 'alezux_academy_name', 'Campus Virtual' );

		header( 'Content-Type: text/html; charset=UTF-8' );
		?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> style="margin-top: 0 !important; background: <?php echo esc_attr( $bg_color ); ?>;">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title><?php echo esc_html( $academy_name ); ?> - Campus Virtual</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
<?php wp_head(); ?>
<style>
	html, body, #crezca-student-root, #crezca-student-root * {
		font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
	}
	html, body {
		margin: 0 !important;
		padding: 0 !important;
		width: 100% !important;
		min-height: 100vh !important;
		overflow-x: hidden !important;
		background: <?php echo esc_attr( $bg_color ); ?> !important;
		color: <?php echo esc_attr( $text_color ); ?> !important;
	}
	#wpadminbar { display: none !important; }
	html { margin-top: 0 !important; }
</style>
</head>
<body class="crezca-campus-portal <?php echo $is_light ? 'theme-light' : 'theme-dark'; ?>">
	<?php echo crezca_render_student_portal_shortcode(); ?>
	<?php wp_footer(); ?>
</body>
</html>
		<?php
		exit;
	}

	return $template;
}
