<?php
namespace Alezux_Members\Modules\Finanzas\Includes;

if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Settings {

    public static function init() {
        // Submenu desactivado por solicitud del usuario (Prefiere Widgets / Dashboard unificado)
        // \add_action( 'admin_menu', [ __CLASS__, 'add_settings_page' ] );
        \add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
    }

    public static function add_settings_page() {
        \add_submenu_page(
            'alezux-members', // Parent slug
            'Finanzas Config', // Page Title
            'Finanzas', // Menu Title
            'manage_options',
            'alezux-finanzas-settings',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function register_settings() {
        \register_setting( 'alezux_finanzas_group', 'alezux_stripe_public_key' );
        \register_setting( 'alezux_finanzas_group', 'alezux_stripe_secret_key' );
    }

    public static function render_page() {
        ?>
        <div class="wrap alezux-settings-wrap">
            <h1>Configuración de Finanzas & Stripe</h1>
            <form method="post" action="options.php">
                <?php \settings_fields( 'alezux_finanzas_group' ); ?>
                <?php \do_settings_sections( 'alezux_finanzas_group' ); ?>
                
                <div class="card" style="max-width: 600px; padding: 20px; margin-top: 20px;">
                    <h2>Credenciales de Stripe</h2>
                    <p class="description">Ingresa tus claves de API de Stripe (Modo Test o Live según corresponda).</p>
                    
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Publishable Key</th>
                            <td>
                                <input type="text" name="alezux_stripe_public_key" value="<?php echo \esc_attr( \get_option('alezux_stripe_public_key') ); ?>" class="regular-text" />
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Secret Key</th>
                            <td>
                                <input type="password" name="alezux_stripe_secret_key" value="<?php echo \esc_attr( \get_option('alezux_stripe_secret_key') ); ?>" class="regular-text" />
                                <p class="description">Comienza con sk_test_... o sk_live_...</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php \submit_button(); ?>
            </form>

            <hr style="margin: 30px 0;">

            <!-- Simulación Check -->
            <div class="card" style="max-width: 600px; padding: 20px; border-left: 4px solid #e91e63;">
                <h2 style="color: #e91e63;">⚡ Simulador de Webhook (Test)</h2>
                <p>Usa esta herramienta para simular un pago exitoso y verificar que:</p>
                <ol>
                    <li>Se crea el usuario y suscripción.</li>
                    <li>Se envía el email de bienvenida.</li>
                </ol>

                <?php
                if ( isset( $_GET['sim_result'] ) ) {
                    if ( $_GET['sim_result'] == 'success' ) {
                        echo '<div class="notice notice-success inline" style="margin: 10px 0;"><p>✅ <strong>Prueba Exitosa:</strong> Webhook simulado correctamente.</p></div>';
                    } else {
                        echo '<div class="notice notice-error inline" style="margin: 10px 0;"><p>❌ <strong>Error:</strong> Revisa los logs.</p></div>';
                    }
                }
                ?>

                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="alezux_simulate_webhook">
                    <?php wp_nonce_field( 'alezux_simulate_action', 'alezux_sim_nonce' ); ?>
                    
                    <p>
                        <label><strong>Email de Prueba:</strong></label><br>
                        <input type="email" name="sim_email" value="stikecool@gmail.com" class="regular-text" required>
                    </p>
                    <button type="submit" class="button button-secondary">🚀 Simular Pago Ahora</button>
                    <p class="description">Esto no tocará tu cuenta bancaria real.</p>
                </form>
            </div>
        </div>
        <?php
    }

    public static function get_registered_shortcodes() {
        return [
            [
                'tag'         => 'ax_fin_revenue_period',
                'module'      => 'FINANZAS',
                'description' => 'Muestra los ingresos del periodo seleccionado (o mes actual por defecto).'
            ],
            [
                'tag'         => 'ax_fin_pending_total',
                'module'      => 'FINANZAS',
                'description' => 'Muestra la deuda total por cobrar (Global).'
            ],
            [
                'tag'         => 'ax_fin_projected_period',
                'module'      => 'FINANZAS',
                'description' => 'Muestra la proyección de cobro para el periodo seleccionado.'
            ],
            [
                'tag'         => 'ax_fin_revenue_today',
                'module'      => 'FINANZAS',
                'description' => 'Muestra lo facturado el día de hoy (Ticker Diario).'
            ],
            // Otros shortcodes existentes (asumidos, si hay más módulos, deberían inyectarse via filter, pero por ahora estático aqui)
            [
                'tag'         => 'alezux_plans_list',
                'module'      => 'FINANZAS',
                'description' => 'Lista de planes disponibles para compra.'
            ],
            // ESTUDIANTES
            [
                'tag'         => 'alezux_estudiantes_total',
                'module'      => 'ESTUDIANTES',
                'description' => 'Muestra el número total de estudiantes registrados.'
            ],
            [
                'tag'         => 'alezux_estudiantes_nuevos_mes',
                'module'      => 'ESTUDIANTES',
                'description' => 'Muestra el número de nuevos estudiantes en el mes actual.'
            ],
            [
                'tag'         => 'alezux_student_momentum',
                'module'      => 'ESTUDIANTES',
                'description' => 'Muestra el indicador de momentum (progreso semanal vs semana pasada).'
            ],
            // CORE
             [
                'tag'         => 'alezux_profile',
                'module'      => 'CORE',
                'description' => 'Muestra el perfil del usuario con tabs (General, Password, Logros).'
            ]
        ];
    }
}
