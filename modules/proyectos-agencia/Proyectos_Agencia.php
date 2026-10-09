<?php
namespace Alezux_Members\Modules\Proyectos_Agencia;

use Alezux_Members\Core\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Proyectos_Agencia extends Module_Base {

	public function init() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
        
        // Init Components
        \Alezux_Members\Modules\Proyectos_Agencia\Includes\Ajax_Handler::init();
	}

	private function define_constants() {
		if ( ! defined( 'ALEZUX_PROYECTOS_AGENCIA_PATH' ) ) {
			define( 'ALEZUX_PROYECTOS_AGENCIA_PATH', plugin_dir_path( __FILE__ ) );
		}
		if ( ! defined( 'ALEZUX_PROYECTOS_AGENCIA_URL' ) ) {
			define( 'ALEZUX_PROYECTOS_AGENCIA_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	private function includes() {
		require_once ALEZUX_PROYECTOS_AGENCIA_PATH . 'includes/Database_Installer.php';
        require_once ALEZUX_PROYECTOS_AGENCIA_PATH . 'includes/Projects_Manager.php';
		require_once ALEZUX_PROYECTOS_AGENCIA_PATH . 'includes/Ajax_Handler.php';
	}

	private function init_hooks() {
		\add_action( 'admin_init', array( __NAMESPACE__ . '\\Includes\\Database_Installer', 'check_updates' ) );
        \add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
        \add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        \add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] ); // Also enqueue in backend if needed
	}
    
    public function register_widgets( $widgets_manager ) {
        // error_log('Proyectos_Agencia: Attempting to register widgets.');
        if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

        require_once ALEZUX_PROYECTOS_AGENCIA_PATH . 'widgets/Project_Manager_Admin_Widget.php';
        require_once ALEZUX_PROYECTOS_AGENCIA_PATH . 'widgets/Project_Client_View_Widget.php';
        
        if ( class_exists( '\Alezux_Members\Modules\Proyectos_Agencia\Widgets\Project_Manager_Admin_Widget' ) ) {
            $widgets_manager->register( new \Alezux_Members\Modules\Proyectos_Agencia\Widgets\Project_Manager_Admin_Widget() );
            // error_log('Proyectos_Agencia: Registered Admin Widget.');
        } else {
             // error_log('Proyectos_Agencia: Admin Widget Class NOT found.');
        }

        if ( class_exists( '\Alezux_Members\Modules\Proyectos_Agencia\Widgets\Project_Client_View_Widget' ) ) {
            $widgets_manager->register( new \Alezux_Members\Modules\Proyectos_Agencia\Widgets\Project_Client_View_Widget() );
             // error_log('Proyectos_Agencia: Registered Client Widget.');
        }
    }
    
    public function enqueue_scripts() {
        // Enqueue WP Media no es necesario si usamos selector nativo
        // wp_enqueue_media();

        // CSS
        wp_register_style( 'alezux-kanban-css', ALEZUX_PROYECTOS_AGENCIA_URL . 'assets/css/kanban.css', [], '1.1.8' );
        wp_register_style( 'alezux-client-view-css', ALEZUX_PROYECTOS_AGENCIA_URL . 'assets/css/client-view.css', [], '1.2.0' );
        // jQuery UI CSS for Datepicker
        wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css', [], '1.12.1' );
        
        // JS - Admin
        wp_register_script( 'alezux-kanban-js', ALEZUX_PROYECTOS_AGENCIA_URL . 'assets/js/kanban-app.js', ['jquery', 'jquery-ui-sortable', 'jquery-ui-datepicker', 'jquery-ui-resizable'], '1.1.8', true );
        
        wp_localize_script( 'alezux-kanban-js', 'alezux_agency_vars', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'alezux_agency_nonce' )
        ] );

        // JS - Client
         wp_register_script( 'alezux-client-app-js', ALEZUX_PROYECTOS_AGENCIA_URL . 'assets/js/client-app.js', ['jquery'], '1.1.5', true );
          wp_localize_script( 'alezux-client-app-js', 'alezux_agency_vars', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'alezux_agency_nonce' )
        ] );

        // Enqueue styles
        if ( current_user_can( 'edit_posts' ) ) {
             wp_enqueue_style( 'jquery-ui' );
        }

        // Enqueue styles globally for now, or check for widget presence (optimized)
        // For simplicity in this phase, we enqueue if the class exists or globally if preferred.
        // Better: Let Elementor widgets enqueue them via get_style_depends / get_script_depends 
        // BUT Module_Base standard often enqueues in a hook.
        // Let's rely on the widgets to request them? 
        // Actually, existing widgets often use `wp_enqueue_scripts` hook in the module class.
        // I'll enqueue them here based on roles or page presence would be better, but simple enqueue is fine for MVP.
        
        // As logic suggests, we should only enqueue if the widget is used. 
        // But since I can't easily detect that before render in all cases:
        // Admin Widget CSS/JS -> Only for Admins?
        if ( current_user_can( 'edit_posts' ) ) {
             // wp_enqueue_media(); 
        }
        
    }
}
