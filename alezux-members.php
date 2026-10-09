<?php
/**
 * Plugin Name: Alezux Members
 * Description: Sistema integral y modular "Lego-style" para membresías, academias online, control de estudiantes, finanzas, marketing y panel de administración moderno impulsado por Arc UI.
 * Version: 2.0.0
 * Author: Alezux
 * Text Domain: alezux-members
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Definir constantes del plugin
define( 'ALEZUX_MEMBERS_VERSION', '2.0.0' );
define( 'ALEZUX_MEMBERS_FILE', __FILE__ );
define( 'ALEZUX_MEMBERS_PATH', plugin_dir_path( __FILE__ ) );
define( 'ALEZUX_MEMBERS_URL', plugin_dir_url( __FILE__ ) );
define( 'ALEZUX_MEMBERS_MODULES_PATH', ALEZUX_MEMBERS_PATH . 'modules/' );

// Autoloader para clases del Core
spl_autoload_register( function ( $class ) {
	$prefix = 'Alezux_Members\\Core\\';
	$base_dir = ALEZUX_MEMBERS_PATH . 'core/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

// Inicializar el plugin
function alezux_members_init() {
	// 1. Inicializar API REST para el Dashboard Arc UI
	if ( class_exists( 'Alezux_Members\\Core\\Admin_Api' ) ) {
		$admin_api = new \Alezux_Members\Core\Admin_Api();
		$admin_api->init();
	}

	// 2. Inicializar Dashboard si estamos en admin
	if ( is_admin() && class_exists( 'Alezux_Members\\Core\\Admin_Dashboard' ) ) {
		$dashboard = new \Alezux_Members\Core\Admin_Dashboard();
		$dashboard->init();
	}

	// 3. Inicializar el Plugin Loader y Módulos Lego
	if ( class_exists( 'Alezux_Members\\Core\\Plugin_Loader' ) ) {
		$loader = new \Alezux_Members\Core\Plugin_Loader();
		$loader->run();
	}
}
add_action( 'plugins_loaded', 'alezux_members_init' );

/**
 * Encolar estilos globales del plugin e inyectar variables dinámicas.
 */
function alezux_members_enqueue_global_assets() {
	wp_enqueue_style( 
		'alezux-members-global', 
		ALEZUX_MEMBERS_URL . 'assets/css/global.css', 
		[], 
		ALEZUX_MEMBERS_VERSION 
	);

	// Obtener colores personalizados
	$primary       = get_option( 'alezux_primary_color', '#7747ff' );
	$primary_hover = get_option( 'alezux_primary_hover', '#5528ce' );
	$bg_base       = get_option( 'alezux_bg_base', '#0f0f0f' );
	$bg_card       = get_option( 'alezux_bg_card', '#1a1a1a' );
	$border_radius = get_option( 'alezux_border_radius', '50px' );
	$border_color  = get_option( 'alezux_border_color', '#333333' );
	$box_shadow    = get_option( 'alezux_box_shadow', '0 10px 30px rgba(0, 0, 0, 0.3)' );

	// CSS Dinámico para sobrescribir variables
	$custom_css = "
		:root {
			--alezux-primary: {$primary};
			--alezux-primary-hover: {$primary_hover};
			--alezux-bg-base: {$bg_base};
			--alezux-bg-card: {$bg_card};
			--alezux-border-radius: {$border_radius};
			--alezux-border-color: {$border_color};
			--alezux-box-shadow: {$box_shadow};
		}
	";
	
	wp_add_inline_style( 'alezux-members-global', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'alezux_members_enqueue_global_assets' );
add_action( 'elementor/frontend/after_enqueue_styles', 'alezux_members_enqueue_global_assets' );

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

    $css_classes = get_option( 'alezux_admin_only_css_classes', '' );
    
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
            echo '<style id="alezux-admin-only-css">';
            echo $final_selector . ' { display: none !important; }';
            echo '</style>';
        }
    }
} );

/**
 * Registrar categorías personalizadas en Elementor
 */
add_action( 'elementor/elements/categories_registered', function( $elements_manager ) {
	$categories = [
		'alezux-auth'        => [
			'title' => esc_html__( 'Alezux: Autenticación', 'alezux-members' ),
			'icon'  => 'eicon-lock-user',
		],
		'alezux-perfil'      => [
			'title' => esc_html__( 'Alezux: Perfil & Config', 'alezux-members' ),
			'icon'  => 'eicon-user-circle-o',
		],
		'alezux-finanzas'    => [
			'title' => esc_html__( 'Alezux: Finanzas', 'alezux-members' ),
			'icon'  => 'eicon-price-table',
		],
		'alezux-estudiantes' => [
			'title' => esc_html__( 'Alezux: Estudiantes', 'alezux-members' ),
			'icon'  => 'eicon-person',
		],
		'alezux-lms'         => [
			'title' => esc_html__( 'Alezux: LMS & Contenido', 'alezux-members' ),
			'icon'  => 'eicon-edu-cap',
		],
		'alezux-otros'       => [
			'title' => esc_html__( 'Alezux: Utilidades', 'alezux-members' ),
			'icon'  => 'eicon-tools',
		],
	];

	foreach ( $categories as $id => $config ) {
		$elements_manager->add_category( $id, $config );
	}
} );

/**
 * Ocultar la barra de administración de WordPress para estudiantes / no administradores
 */
add_filter( 'show_admin_bar', function( $show ) {
    if ( ! current_user_can( 'edit_posts' ) ) {
        return false;
    }
    return $show;
} );
