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
		'allow_registration'     => true,
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
 * Obtener la URL del Campus Virtual / Portal del Estudiante
 */
function crezca_get_student_portal_url() {
	$student_page_id = get_option( 'alezux_student_dashboard_page_id' );
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

	$slugs = [ 'aula', 'campus', 'cursos', 'estudiantes', 'mi-cuenta' ];
	foreach ( $slugs as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page && $page->post_status === 'publish' ) {
			update_option( 'alezux_student_dashboard_page_id', (int) $page->ID );
			return get_permalink( $page->ID );
		}
	}

	return home_url( '/' );
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
	
	wp_enqueue_style( 'crezca-dashboard-style', CREZCA_URL . 'assets/dist/alezux-dashboard.css', [], $css_ver );
	wp_enqueue_script( 'crezca-dashboard-script', CREZCA_URL . 'assets/dist/alezux-dashboard.js', [], $js_ver, true );
	
	$current_user  = wp_get_current_user();
	$academy_name  = get_option( 'alezux_academy_name', 'Crezca' );
	$academy_logo  = get_option( 'alezux_academy_logo', '' );
	$accent        = get_option( 'alezux_accent', 'violet' );
	$theme         = get_option( 'alezux_theme', 'dark' );
	$auth_settings = crezca_get_auth_settings();
	$is_admin      = current_user_can( 'manage_options' );
	$student_view  = ! empty( $auth_settings['student_default_view'] ) ? $auth_settings['student_default_view'] : 'courses';

	$data = [
		'root_url'             => esc_url_raw( rest_url( 'crezca/v1/' ) ),
		'legacy_root_url'      => esc_url_raw( rest_url( 'alezux/v1/' ) ),
		'nonce'                => wp_create_nonce( 'wp_rest' ),
		'is_student_mode'      => true,
		'is_logged_in'         => is_user_logged_in(),
		'user_id'              => $current_user ? $current_user->ID : 0,
		'is_admin'             => $is_admin,
		'initial_tab'          => $student_view,
		'student_default_view' => $student_view,
		'academy_name'         => $academy_name,
		'academy_logo'         => $academy_logo,
		'accent'               => $accent,
		'theme'                => $theme,
		'wp_admin_url'         => $is_admin ? esc_url_raw( admin_url( 'index.php' ) ) : '',
		'login_url'            => wp_login_url( get_permalink() ),
		'logout_url'           => wp_logout_url( home_url() ),
	];

	ob_start();
	?>
	<script id="crezca-student-portal-data">
		window.crezca_student_data = <?php echo json_encode( $data ); ?>;
		window.crezca_admin_data = window.crezca_admin_data || window.crezca_student_data;
	</script>
	<div id="crezca-student-root" data-mode="student" style="min-height:100vh;background:var(--background,#090a0f);"></div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'crezca_aula', 'crezca_render_student_portal_shortcode' );
add_shortcode( 'alezux_aula', 'crezca_render_student_portal_shortcode' );
