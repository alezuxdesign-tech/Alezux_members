<?php
namespace Alezux_Members\Modules\Finanzas;

use Alezux_Members\Core\Module_Base;

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Finanzas extends Module_Base {

	public function init() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	private function define_constants() {
		if ( ! \defined( 'ALEZUX_FINANZAS_PATH' ) ) {
			\define( 'ALEZUX_FINANZAS_PATH', \plugin_dir_path( __FILE__ ) );
		}
		if ( ! \defined( 'ALEZUX_FINANZAS_URL' ) ) {
			\define( 'ALEZUX_FINANZAS_URL', \plugin_dir_url( __FILE__ ) );
		}
	}

	private function includes() {
		// La lógica de inclusión se ha movido a init_hooks para asegurar el orden de carga
	}

	private function init_hooks() {
		// Incluir archivos necesarios
		require_once ALEZUX_FINANZAS_PATH . 'includes/Database_Installer.php';
		require_once ALEZUX_FINANZAS_PATH . 'includes/Ajax_Handler.php';
		require_once ALEZUX_FINANZAS_PATH . 'includes/Stripe_API.php';
		require_once ALEZUX_FINANZAS_PATH . 'includes/Admin_Settings.php';
        // require_once ALEZUX_FINANZAS_PATH . 'includes/Webhook_Handler.php'; // Dejamos este activo por si acaso, no lo toqué
        require_once ALEZUX_FINANZAS_PATH . 'includes/Webhook_Handler.php'; 
        require_once ALEZUX_FINANZAS_PATH . 'includes/Access_Control.php'; // REACTIVATED
        require_once ALEZUX_FINANZAS_PATH . 'includes/Webhook_Handler.php'; 
        require_once ALEZUX_FINANZAS_PATH . 'includes/Access_Control.php'; // REACTIVATED
        require_once ALEZUX_FINANZAS_PATH . 'includes/Enrollment_Manager.php'; // REACTIVATED
        require_once ALEZUX_FINANZAS_PATH . 'includes/Shortcodes.php'; // NUEVO: Shortcodes KPIs

		// Inicializar manejadores
		\Alezux_Members\Modules\Finanzas\Includes\Ajax_Handler::init();
        \Alezux_Members\Modules\Finanzas\Includes\Admin_Settings::init();
        \Alezux_Members\Modules\Finanzas\Includes\Webhook_Handler::init();
        
        // Inicializar Control de Acceso (Hooks LearnDash)
        if ( class_exists( 'Alezux_Members\Modules\Finanzas\Includes\Access_Control' ) ) {
            \Alezux_Members\Modules\Finanzas\Includes\Access_Control::init();
        }
        
        // Inicializar Shortcodes
         if ( class_exists( 'Alezux_Members\Modules\Finanzas\Includes\Shortcodes' ) ) {
            \Alezux_Members\Modules\Finanzas\Includes\Shortcodes::init();
        }

        // Dashboard legacy (opcional, mantener por si acaso se necesita lógica interna)
        // require_once ALEZUX_FINANZAS_PATH . 'includes/Finance_Dashboard.php';
        // \Alezux_Members\Modules\Finanzas\Includes\Finance_Dashboard::init();
 
 		// Hooks
 		\add_action( 'admin_init', array( __NAMESPACE__ . '\\Includes\\Database_Installer', 'check_updates' ) );
        \add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
        \add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_widget_styles' ] );
        \add_action( 'template_redirect', [ $this, 'handle_checkout_redirect' ] );
        \add_action( 'template_redirect', [ $this, 'handle_payment_return' ] ); // Nuevo hook
        \add_action( 'template_redirect', [ $this, 'handle_payment_return' ] ); // Nuevo hook
        // \add_action( 'wp_ajax_alezux_get_sales_stats', [ $this, 'get_sales_stats' ] ); // MOVED TO Ajax_Handler.php
	}

    /**
     * Maneja la redirección al Checkout de Stripe cuando se detecta ?alezux_action=checkout
     */
    public function handle_checkout_redirect() {
        if ( isset( $_GET['alezux_action'] ) && $_GET['alezux_action'] === 'checkout' ) {
            $plan_id = 0;
            
            // 1. Buscar Plan ID
            if ( ! empty( $_GET['token'] ) ) {
                global $wpdb;
                $table_plans = $wpdb->prefix . 'alezux_finanzas_plans';
                $token = sanitize_text_field( $_GET['token'] );
                $plan_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_plans WHERE token = %s", $token ) );
            } elseif ( ! empty( $_GET['plan_id'] ) ) {
                $plan_id = intval( $_GET['plan_id'] );
            }

            if ( ! $plan_id ) {
                wp_die( 'Error: Enlace de pago inválido (Token no encontrado o ID faltante).', 'Error de Checkout' );
            }

            // 2. Obtener datos del plan
            global $wpdb;
            $table_plans = $wpdb->prefix . 'alezux_finanzas_plans';
            $plan = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_plans WHERE id = %d", $plan_id ) );

            if ( ! $plan || empty( $plan->stripe_price_id ) ) {
                wp_die( 'Error: Plan no encontrado o configuración inválida.', 'Error de Checkout' );
            }

            // 2. Crear sesión de Stripe
            $users_access = \Alezux_Members\Modules\Finanzas\Includes\Stripe_API::get_instance();
            
            // URLs de retorno
            $current_url = home_url( add_query_arg( [], $GLOBALS['wp']->request ) ); // URL actual base (aproximada) o home
            // Mejor redirigir a una página de "Gracias" o al dashboard. Por defecto al home + status
            $success_url = home_url( '/?alezux_payment_success=true&session_id={CHECKOUT_SESSION_ID}' );
            $cancel_url  = home_url( '/?alezux_payment_canceled=true' );

            // Si el usuario está logueado, pasamos su email para autocompletar en Stripe
            $customer_email = null;
            if ( is_user_logged_in() ) {
                $current_user = wp_get_current_user();
                $customer_email = $current_user->user_email;
            }

            // Determinar modo (payment vs subscription) basado en frecuencia o cuotas
            $mode = ( ( isset( $plan->frequency ) && $plan->frequency === 'contado' ) || $plan->total_quotas == 1 ) ? 'payment' : 'subscription';

            // Generar metadata
            $metadata = [ 'plan_id' => $plan_id ];

            $session = $users_access->create_checkout_session( 
                $plan->stripe_price_id, 
                $success_url, 
                $cancel_url, 
                $customer_email,
                $mode,
                $metadata
            );

            if ( is_wp_error( $session ) ) {
                wp_die( 'Error de Stripe: ' . $session->get_error_message() );
            }

            // 3. Redirigir a Stripe
            if ( isset( $session->url ) ) {
                wp_redirect( $session->url );
                exit;
            }
        }

        // Manejar Link de Pago Directo por Token (?alezux_buy_token=xyz)
        if ( isset( $_GET['alezux_buy_token'] ) ) {
            $token = sanitize_text_field( $_GET['alezux_buy_token'] );
            global $wpdb;
            $table_plans = $wpdb->prefix . 'alezux_finanzas_plans';
            $plan_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_plans WHERE token = %s", $token ) );
            
            if ( $plan_id ) {
                $this->process_direct_buy_link( $plan_id );
            }
        }

        // Manejar Link de Pago Directo Legacy (?alezux_buy_plan=123)
        if ( isset( $_GET['alezux_buy_plan'] ) ) {
            $plan_id = intval( $_GET['alezux_buy_plan'] );
            if ( $plan_id > 0 ) {
                $this->process_direct_buy_link( $plan_id );
            }
        }

        // Manejar Retorno de Stripe
        if ( isset( $_GET['session_id'] ) && ! empty( $_GET['session_id'] ) ) {
            $this->handle_payment_return();
        }
    }

    private function process_direct_buy_link( $plan_id ) {
        // Obtener datos del plan de la DB
        global $wpdb;
        $table_plans = $wpdb->prefix . 'alezux_finanzas_plans';
        $plan = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_plans WHERE id = %d", $plan_id ) );

        if ( ! $plan ) {
            wp_die( 'El plan especificado no existe.' );
        }

        // Crear Sesión de Stripe
        $stripe = \Alezux_Members\Modules\Finanzas\Includes\Stripe_API::get_instance();
        
        // URLs de éxito y cancelación
        $success_url = home_url( '/?alezux_payment_success=true&session_id={CHECKOUT_SESSION_ID}' );
        $cancel_url  = home_url( '/' ); // O alguna página específica

        // Metadata necesaria
        $metadata = [
            'plan_id' => $plan_id,
            'source'  => 'direct_link'
        ];

        // Determinar modo (payment vs subscription) basado en frecuencia o cuotas
        $mode = ( ( isset( $plan->frequency ) && $plan->frequency === 'contado' ) || $plan->total_quotas == 1 ) ? 'payment' : 'subscription';

        // Crear sesión
        $session = $stripe->create_checkout_session(
            $plan->stripe_price_id,
            $success_url,
            $cancel_url,
            null, // No customer email for direct link unless user is logged in
            $mode,
            $metadata
        );

        if ( is_wp_error( $session ) ) {
            wp_die( 'Error conectando con Stripe: ' . $session->get_error_message() );
        }

        // Redirigir a Stripe
        if ( isset( $session->url ) ) {
            wp_redirect( $session->url );
            exit;
        } else {
            wp_die( 'No se pudo generar la URL de pago.' );
        }
    }
    /**
     * Maneja el retorno exitoso desde Stripe para matriculación inmediata.
     */
    public function handle_payment_return() {
        if ( isset( $_GET['alezux_payment_success'] ) && isset( $_GET['session_id'] ) ) {
            $session_id = sanitize_text_field( $_GET['session_id'] );
            
            $stripe = \Alezux_Members\Modules\Finanzas\Includes\Stripe_API::get_instance();
            $session = $stripe->get_checkout_session( $session_id );

            if ( is_wp_error( $session ) ) {
               wp_die( 'Error verificando pago: ' . $session->get_error_message() );
            }

            if ( $session->payment_status !== 'paid' ) {
                wp_die( 'El pago no se ha completado o está pendiente de confirmación.' );
            }

            // Si es pago de cuota manual, redirigir al perfil
            if ( isset( $session->metadata->type ) && $session->metadata->type === 'installment_payment' ) {
                 wp_redirect( home_url( '/perfil/?payment_success=true' ) );
                 exit;
            }

            // Recopilar datos
            $email = $session->customer_details->email;
            $plan_id = $session->metadata->plan_id ?? 0;
            $amount = ( isset( $session->amount_total ) ) ? ( $session->amount_total / 100 ) : 0;
            $currency = ( isset( $session->currency ) ) ? strtoupper( $session->currency ) : 'USD';
            $transaction_ref = $session->payment_intent ?? $session->id;
            $stripe_subscription_id = $session->subscription ?? null;
            $customer_name = isset( $session->customer_details->name ) ? sanitize_text_field( $session->customer_details->name ) : '';

             // Delegar a Enrollment Manager
            if ( class_exists( 'Alezux_Members\Modules\Finanzas\Includes\Enrollment_Manager' ) ) {
                $user_id = \Alezux_Members\Modules\Finanzas\Includes\Enrollment_Manager::enroll_user( 
                    $email, 
                    $plan_id, 
                    $stripe_subscription_id, 
                    $amount, 
                    $transaction_ref,
                    $currency,
                    $customer_name
                );

                if ( $user_id ) {
                    // Auto-Login si no está logueado (Mejorado)
                    if ( ! is_user_logged_in() ) {
                         $user = get_user_by( 'id', $user_id );
                         if ( $user ) {
                             wp_clear_auth_cookie();
                             wp_set_current_user( $user_id );
                             wp_set_auth_cookie( $user_id );
                             do_action( 'wp_login', $user->user_login, $user );
                         }
                    }

                    // Redirigir al curso o dashboard
                    // Obtener course_id del plan para redirigir
                    global $wpdb;
                    $table_plans = $wpdb->prefix . 'alezux_finanzas_plans';
                    $course_id = $wpdb->get_var( $wpdb->prepare( "SELECT course_id FROM $table_plans WHERE id = %d", $plan_id ) );

                    if ( $course_id ) {
                        $course_url = get_permalink( $course_id );
                        if ( $course_url ) {
                            nocache_headers(); // Evitar cache de redireccion
                            wp_redirect( $course_url );
                            exit;
                        }
                    }
                    
                    // Fallback home
                    wp_redirect( home_url() );
                    exit;
                }
            } else {
                 wp_die( 'Error interno: Enrollment Manager no cargado.' );
            }
        }
    }

	public function register_widgets( $widgets_manager ) {
        if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}
        
        // Incluir archivos de widgets aquí para asegurar que Elementor está cargado
        require_once ALEZUX_FINANZAS_PATH . 'widgets/Create_Plan_Widget.php';
        require_once ALEZUX_FINANZAS_PATH . 'widgets/Sales_History_Widget.php';
        require_once ALEZUX_FINANZAS_PATH . 'widgets/Subscriptions_List_Widget.php';
        require_once ALEZUX_FINANZAS_PATH . 'widgets/Plans_List_Widget.php';
        require_once ALEZUX_FINANZAS_PATH . 'widgets/Manual_Payment_Widget.php';
        require_once ALEZUX_FINANZAS_PATH . 'widgets/Financial_Performance_Widget.php';
        require_once ALEZUX_FINANZAS_PATH . 'widgets/Filter_Date_Widget.php'; // NUEVO
        require_once ALEZUX_FINANZAS_PATH . 'widgets/User_Financial_Profile.php';
        require_once ALEZUX_FINANZAS_PATH . 'widgets/User_Financial_Profile.php'; // NUEVO: Perfil Usuario
        require_once ALEZUX_FINANZAS_PATH . 'widgets/Sales_Stats_Widget.php';
        require_once ALEZUX_FINANZAS_PATH . 'widgets/Sales_Chart_Widget.php';
        require_once ALEZUX_FINANZAS_PATH . 'widgets/Date_Range_Filter_Widget.php';

        // Registrar Widgets
        if ( class_exists( 'Alezux_Members\Modules\Finanzas\Widgets\Create_Plan_Widget' ) ) {
            $widgets_manager->register( new Widgets\Create_Plan_Widget() );
        }
        
        if ( class_exists( 'Alezux_Members\Modules\Finanzas\Widgets\Sales_History_Widget' ) ) {
            $widgets_manager->register( new Widgets\Sales_History_Widget() );
        }
        
        if ( class_exists( 'Alezux_Members\Modules\Finanzas\Widgets\Subscriptions_List_Widget' ) ) {
            $widgets_manager->register( new Widgets\Subscriptions_List_Widget() );
        }

        if ( class_exists( 'Alezux_Members\Modules\Finanzas\Widgets\Plans_List_Widget' ) ) {
            $widgets_manager->register( new Widgets\Plans_List_Widget() );
        }
        
        if ( class_exists( 'Alezux_Members\Modules\Finanzas\Widgets\Manual_Payment_Widget' ) ) {
            $widgets_manager->register( new Widgets\Manual_Payment_Widget() );
        }

        if ( class_exists( 'Alezux_Members\Modules\Finanzas\Widgets\Financial_Performance_Widget' ) ) {
            $widgets_manager->register( new Widgets\Financial_Performance_Widget() );
        }
        
        if ( class_exists( 'Alezux_Members\Modules\Finanzas\Widgets\Filter_Date_Widget' ) ) {
            $widgets_manager->register( new Widgets\Filter_Date_Widget() );
        }

        if ( class_exists( 'Alezux_Members\Modules\Finanzas\Widgets\User_Financial_Profile' ) ) {
            $widgets_manager->register( new Widgets\User_Financial_Profile() );
        }

        if ( class_exists( 'Alezux_Members\Modules\Finanzas\Widgets\User_Financial_Profile' ) ) {
            $widgets_manager->register( new Widgets\User_Financial_Profile() );
        }

        /* 
        // Deshabilitado por solicitud del cliente (Imagen 2 - Eliminar)
        if ( class_exists( 'Alezux_Members\Modules\Finanzas\Widgets\Sales_Stats_Widget' ) ) {
            $widgets_manager->register( new Widgets\Sales_Stats_Widget() );
        }
        */

        if ( class_exists( 'Alezux_Members\Modules\Finanzas\Widgets\Sales_Chart_Widget' ) ) {
            $widgets_manager->register( new Widgets\Sales_Chart_Widget() );
        }

        if ( class_exists( 'Alezux_Members\Modules\Finanzas\Widgets\Date_Range_Filter_Widget' ) ) {
            $widgets_manager->register( new Widgets\Date_Range_Filter_Widget() );
        }
	}

    public function enqueue_widget_styles() {
        $version = '1.2.06'; // Bump version to force cache clear

        // Unified Table Styles
        // Register Flatpickr (Remote CDN)
        wp_register_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', [], '4.6.13');
        wp_register_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', ['jquery'], '4.6.13', true);
        wp_register_script('flatpickr-es-js', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js', ['flatpickr-js'], '4.6.13', true);

        // Alpine.js & Frontend
        wp_register_script('alpinejs', 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js', [], '3.0.0', true);
        wp_register_script('alezux-finanzas-frontend', false, ['alpinejs', 'jquery'], $version, true);

        // Register Alpine.js (Remote CDN)
        wp_register_script('alpinejs', 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js', [], '3.0.0', true);

        // Register Frontend Script for Financial Profile (depende de Alpine)
        // Aunque el código principal está inline en el widget, registramos el handle para que Elementor lo maneje.
        wp_register_script('alezux-finanzas-frontend', false, ['alpinejs', 'jquery'], $version, true);


        if ( file_exists( ALEZUX_FINANZAS_PATH . 'assets/css/alezux-tables.css' ) ) {
             wp_register_style( 'alezux-finanzas-tables-css', ALEZUX_FINANZAS_URL . 'assets/css/alezux-tables.css', ['flatpickr-css'], $version );
        }
        
        if ( file_exists( ALEZUX_FINANZAS_PATH . 'assets/js/sales-history.js' ) ) {
             wp_register_script( 'alezux-sales-history-js', ALEZUX_FINANZAS_URL . 'assets/js/sales-history.js', ['jquery', 'flatpickr-js', 'flatpickr-es-js'], $version, true );
             
             wp_localize_script( 'alezux-sales-history-js', 'alezux_finanzas_vars', [
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce( 'alezux_finanzas_nonce' ),
                'is_logged_in' => is_user_logged_in()
            ] );
        }
        
        if ( file_exists( ALEZUX_FINANZAS_PATH . 'assets/js/plans-manager.js' ) ) {
             wp_register_script( 'alezux-plans-manager-js', ALEZUX_FINANZAS_URL . 'assets/js/plans-manager.js', ['jquery'], $version, true );
              wp_localize_script( 'alezux-plans-manager-js', 'alezux_finanzas_vars', [
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce( 'alezux_finanzas_nonce' ),
                'is_logged_in' => is_user_logged_in()
            ] );
        }
        
        if ( file_exists( ALEZUX_FINANZAS_PATH . 'assets/js/subscriptions-list.js' ) ) {
             wp_register_script( 'alezux-subs-list-js', ALEZUX_FINANZAS_URL . 'assets/js/subscriptions-list.js', ['jquery'], $version, true );
              wp_localize_script( 'alezux-subs-list-js', 'alezux_finanzas_vars', [
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce( 'alezux_finanzas_nonce' ),
                'is_logged_in' => is_user_logged_in()
            ] );
        }
        
        if ( file_exists( ALEZUX_FINANZAS_PATH . 'assets/css/manual-payment.css' ) ) {
            wp_register_style( 'alezux-manual-payment-css', ALEZUX_FINANZAS_URL . 'assets/css/manual-payment.css', [], '1.0.0' );
        }
        
        // Script Dashboard Financiero (KPIs + Filtros)
        if ( file_exists( ALEZUX_FINANZAS_PATH . 'assets/js/finance-dashboard.js' ) ) {
             wp_register_script( 'alezux-finance-dashboard-js', ALEZUX_FINANZAS_URL . 'assets/js/finance-dashboard.js', ['jquery', 'flatpickr-js', 'flatpickr-es-js'], '1.0.0', true );
             
             wp_localize_script( 'alezux-finance-dashboard-js', 'alezux_finanzas_vars', [
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce( 'alezux_finanzas_nonce' )
            ] );
             
             // Enqueue globalmente o solo donde esté el shortcode? Como es para el dashboard, mejor global si el modulo está activo.
             wp_enqueue_script('alezux-finance-dashboard-js');
             
             // Asegurar estilos base flatpickr y tablas tambien
             wp_enqueue_style('flatpickr-css');
             wp_enqueue_style('alezux-finanzas-tables-css');
        }

        // Register Chart.js (Remote CDN)
        wp_register_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], '4.4.0', true );

        // 4. NEW Sales Dashboard Universal Logic
        // Load on Admin Dashboard OR if widgets are present (Frontend)
        if ( is_admin() ) {
            $screen = get_current_screen();
            $is_dashboard = $screen ? $screen->id === 'dashboard' || $screen->id === 'toplevel_page_alezux-dashboard' : false;
        } else {
            $is_dashboard = false;
        }

        if ( file_exists( ALEZUX_FINANZAS_PATH . 'assets/js/sales-dashboard.js' ) ) {
            wp_register_script( 'alezux-sales-dashboard-js', ALEZUX_FINANZAS_URL . 'assets/js/sales-dashboard.js', ['jquery', 'flatpickr-js', 'flatpickr-es-js', 'chart-js'], $version, true );
             wp_localize_script( 'alezux-sales-dashboard-js', 'alezux_dashboard_vars', [
                 'ajax_url'     => admin_url( 'admin-ajax.php' ),
                 'nonce'        => wp_create_nonce( 'alezux_finanzas_nonce' ),
                 'is_logged_in' => is_user_logged_in(),
                 'server_date'  => current_time( 'Y-m-d' ), // Pass server date for strict validation
             ] );
             
             // Enqueue if we are on Admin Dashboard OR if Elementor Editor is active and class exists
             $is_elementor_editor = false;
             if ( did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' ) ) {
                 if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                     $is_elementor_editor = true;
                 }
             }

             if ( $is_dashboard || $is_elementor_editor ) {
                   wp_enqueue_script('alezux-sales-dashboard-js');
             }
        }
    }

    /**
     * AJAX: Get Sales Stats for Dashboard (KPIs + Chart)
     */
    /*
     * AJAX: Get Sales Stats for Dashboard (KPIs + Chart)
     * MOVED TO: Includes\Ajax_Handler::get_sales_stats
     */
    /*
    public function get_sales_stats() {
        // Legacy code removed to prevent duplicates.
    }
    */
}
