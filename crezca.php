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
 * Ocultar la barra de administración de WordPress para estudiantes / no administradores
 */
add_filter( 'show_admin_bar', function( $show ) {
    if ( ! current_user_can( 'edit_posts' ) ) {
        return false;
    }
    return $show;
} );
