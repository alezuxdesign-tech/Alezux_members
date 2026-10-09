<?php
namespace Alezux_Members\Modules\Finanzas\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Finance_Dashboard {

    public static function init() {
        // \add_action( 'admin_menu', [ __CLASS__, 'add_menu_page' ] );
        \add_action( 'admin_post_alezux_manual_payment', [ __CLASS__, 'handle_manual_payment' ] );
        \add_action( 'admin_post_alezux_simulate_webhook', [ __CLASS__, 'handle_simulate_webhook' ] );
    }

    public static function add_menu_page() {
        // Desactivado por solicitud del usuario (Prefiere Widgets)
        // Reactivado temporalmente para Pruebas
        \add_submenu_page(
            'alezux-members',
            'Ventas y Suscripciones',
            'Ventas',
            'manage_options',
            'alezux-finanzas-sales',
            [ __CLASS__, 'render_dashboard' ]
        );
    }

    public static function render_dashboard() {
        $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'sales';
        ?>
        <div class="wrap">
            <h1>Finanzas: Ventas y Suscripciones</h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=alezux-finanzas-sales&tab=sales" class="nav-tab <?php echo $active_tab == 'sales' ? 'nav-tab-active' : ''; ?>">Ventas (Transacciones)</a>
                <a href="?page=alezux-finanzas-sales&tab=subscriptions" class="nav-tab <?php echo $active_tab == 'subscriptions' ? 'nav-tab-active' : ''; ?>">Suscripciones Activas</a>
                <a href="?page=alezux-finanzas-sales&tab=manual" class="nav-tab <?php echo $active_tab == 'manual' ? 'nav-tab-active' : ''; ?>">Registro Manual</a>
                <a href="?page=alezux-finanzas-sales&tab=simulation" class="nav-tab <?php echo $active_tab == 'simulation' ? 'nav-tab-active' : ''; ?>" style="color:#e91e63;">⚡ Simulación (Test)</a>
            </h2>

            <div class="alezux-tab-content">
                <?php
                if ( isset( $_GET['sim_result'] ) ) {
                    $res = $_GET['sim_result'];
                    if ( $res == 'success' ) {
                        echo '<div class="notice notice-success inline"><p>✅ <strong>Simulación Exitosa:</strong> El webhook fue enviado recuperado y procesado (HTTP 200).</p></div>';
                    } else {
                        echo '<div class="notice notice-error inline"><p>❌ <strong>Error en Simulación:</strong> Revisa el log de errores.</p></div>';
                    }
                }

                switch ( $active_tab ) {
                    case 'sales':
                        self::render_sales_tab();
                        break;
                    case 'subscriptions':
                        self::render_subscriptions_tab();
                        break;
                    case 'manual':
                        self::render_manual_entry_tab();
                        break;
                    case 'simulation':
                        self::render_simulation_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <style>
            .alezux-tab-content { margin-top: 20px; background: #fff; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
            .form-table th { width: 200px; }
        </style>
        <?php
    }

    private static function render_sales_tab() {
        global $wpdb;
        $table = $wpdb->prefix . 'alezux_finanzas_transactions';
        // Simple query, limit 50 for now
        $results = $wpdb->get_results( "SELECT * FROM $table ORDER BY created_at DESC LIMIT 50" );
        
        echo '<h3>Últimas 50 Transacciones</h3>';
        if ( empty( $results ) ) {
            echo '<p>No hay transacciones registradas.</p>';
            return;
        }

        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Usuario</th><th>Método</th><th>Monto</th><th>Ref</th><th>Estado</th><th>Fecha</th></tr></thead>';
        echo '<tbody>';
        foreach ( $results as $row ) {
            $user_info = get_userdata( $row->user_id );
            $user_name = $user_info ? $user_info->user_login : 'ID: ' . $row->user_id;
            echo '<tr>';
            echo '<td>' . $row->id . '</td>';
            echo '<td>' . $user_name . '</td>';
            echo '<td>' . ucfirst( $row->method ) . '</td>';
            echo '<td>' . $row->amount . ' ' . $row->currency . '</td>';
            echo '<td>' . $row->transaction_ref . '</td>';
            echo '<td>' . $row->status . '</td>';
            echo '<td>' . $row->created_at . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    private static function render_subscriptions_tab() {
        global $wpdb;
        $subs_table = $wpdb->prefix . 'alezux_finanzas_subscriptions';
        $plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
        
        $results = $wpdb->get_results( "
            SELECT s.*, p.name as plan_name 
            FROM $subs_table s 
            LEFT JOIN $plans_table p ON s.plan_id = p.id 
            ORDER BY s.id DESC LIMIT 50" 
        );

        echo '<h3>Estado de Suscripciones</h3>';
        if ( empty( $results ) ) {
            echo '<p>No hay suscripciones activas.</p>';
            return;
        }

        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Usuario</th><th>Plan</th><th>Estado</th><th>Cuotas Pagadas</th><th>Próximo Pago</th><th>Stripe ID</th></tr></thead>';
        echo '<tbody>';
        foreach ( $results as $row ) {
            $user_info = get_userdata( $row->user_id );
            $user_name = $user_info ? $user_info->user_login : 'ID: ' . $row->user_id;

            $status_colors = [
                'active' => 'green',
                'past_due' => 'red',
                'completed' => 'blue',
                'pending' => 'orange'
            ];
            $color = isset( $status_colors[ $row->status ] ) ? $status_colors[ $row->status ] : 'black';

            echo '<tr>';
            echo '<td>' . $row->id . '</td>';
            echo '<td>' . $user_name . '</td>';
            echo '<td>' . $row->plan_name . '</td>';
            echo '<td style="color:' . $color . '; font-weight:bold;">' . ucfirst( $row->status ) . '</td>';
            echo '<td>' . $row->quotas_paid . '</td>';
            echo '<td>' . $row->next_payment_date . '</td>';
            echo '<td>' . ( $row->stripe_subscription_id ?: 'Manual' ) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    private static function render_manual_entry_tab() {
        // Obtener Planes para el select
        global $wpdb;
        $plans = $wpdb->get_results( "SELECT id, name, quota_amount FROM {$wpdb->prefix}alezux_finanzas_plans" );
        
        ?>
        <h3>Registrar Pago Manual</h3>
        <p>Utiliza este formulario para registrar pagos hechos por Transferencia, Efectivo u otros medios externos.</p>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="alezux_manual_payment">
            <?php wp_nonce_field( 'alezux_manual_payment_action', 'alezux_nonce' ); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="user_email">Email del Usuario *</label></th>
                    <td>
                        <input type="email" name="user_email" id="user_email" class="regular-text" required placeholder="email@usuario.com">
                        <p class="description">Si el usuario no existe, se creará uno nuevo.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="plan_id">Plan *</label></th>
                    <td>
                        <select name="plan_id" id="plan_id" required>
                            <option value="">Selecciona un Plan...</option>
                            <?php foreach ( $plans as $plan ) : ?>
                                <option value="<?php echo $plan->id; ?>" data-amount="<?php echo $plan->quota_amount; ?>">
                                    <?php echo $plan->name; ?> ($<?php echo $plan->quota_amount; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="amount">Monto Pagado *</label></th>
                    <td>
                        <input type="number" step="0.01" name="amount" id="amount" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="payment_method">Método de Pago</label></th>
                    <td>
                        <select name="payment_method" id="payment_method">
                            <option value="transferencia">Transferencia Bancaria</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="otro">Otro</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="reference">Referencia / Notas</label></th>
                    <td>
                        <input type="text" name="reference" id="reference" class="regular-text" placeholder="#Comprobante 1234">
                    </td>
                </tr>
            </table>
            
            <?php submit_button( 'Registrar Pago' ); ?>
        </form>

        <script>
            // Pequeño script para auto-rellenar monto
            document.getElementById('plan_id').addEventListener('change', function() {
                var selected = this.options[this.selectedIndex];
                var amount = selected.getAttribute('data-amount');
                if (amount) {
                    document.getElementById('amount').value = amount;
                }
            });
        </script>
        <?php
    }

    private static function render_simulation_tab() {
        ?>
        <h3>⚡ Simulador de Webhook Stripe</h3>
        <p>Esta herramienta simula que Stripe ha enviado una confirmación de pago exitosa (checkout.session.completed).</p>
        <p>Utilízalo para verificar que:</p>
        <ol>
            <li>Se crea el usuario (si no existe).</li>
            <li>Se matricula en el curso y se activa el plan.</li>
            <li>Se envía el correo de bienvenida.</li>
        </ol>

        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="alezux_simulate_webhook">
            <?php wp_nonce_field( 'alezux_simulate_action', 'alezux_sim_nonce' ); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="sim_email">Email de Prueba *</label></th>
                    <td>
                        <input type="email" name="sim_email" id="sim_email" class="regular-text" value="stikecool@gmail.com" required>
                    </td>
                </tr>
            </table>
            <br>
            <button type="submit" class="button button-primary button-large" style="background:#e91e63;border-color:#c2185b;">🚀 Disparar Pago Simulado</button>
        </form>
        <?php
    }

    public static function handle_manual_payment() {
        if ( ! isset( $_POST['alezux_nonce'] ) || ! wp_verify_nonce( $_POST['alezux_nonce'], 'alezux_manual_payment_action' ) ) {
            wp_die( 'Seguridad inválida.' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'No tienes permisos.' );
        }

        global $wpdb;
        $email = sanitize_email( $_POST['user_email'] );
        $plan_id = intval( $_POST['plan_id'] );
        $amount = floatval( $_POST['amount'] );
        $method = sanitize_text_field( $_POST['payment_method'] );
        $ref = sanitize_text_field( $_POST['reference'] );

        // 1. Obtener o Crear Usuario
        $user = get_user_by( 'email', $email );
        if ( ! $user ) {
            $username = sanitize_user( current( explode( '@', $email ) ) );
            $password = wp_generate_password();
            $user_id = wp_create_user( $username, $password, $email );
            if ( is_wp_error( $user_id ) ) {
                wp_die( 'Error al crear usuario: ' . $user_id->get_error_message() );
            }
            $user = get_user_by( 'id', $user_id );
            // TODO: Enviar email bienvenida
        }
        $user_id = $user->ID;

        // 2. Buscar Plan y Suscripción Existente
        $subs_table = $wpdb->prefix . 'alezux_finanzas_subscriptions';
        $plans_table = $wpdb->prefix . 'alezux_finanzas_plans';
        
        $plan = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $plans_table WHERE id = %d", $plan_id ) );
        if ( ! $plan ) wp_die( 'Plan inválido' );

        $subscription = $wpdb->get_row( $wpdb->prepare( 
            "SELECT * FROM $subs_table WHERE user_id = %d AND plan_id = %d LIMIT 1", 
            $user_id, $plan_id 
        ) );

        $subscription_id = 0;
        $current_quota = 0;

        if ( ! $subscription ) {
            // Crear nueva suscripción manual
            $wpdb->insert( $subs_table, [
                'user_id' => $user_id,
                'plan_id' => $plan_id,
                'status' => 'active',
                'quotas_paid' => 1,
                'last_payment_date' => current_time( 'mysql' ),
                'next_payment_date' => date( 'Y-m-d H:i:s', strtotime( '+1 month' ) )
            ] );
            $subscription_id = $wpdb->insert_id;
            $current_quota = 1;

            // Matricular en LearnDash
            if ( function_exists( 'ld_update_course_access' ) ) {
                ld_update_course_access( $user_id, $plan->course_id );
            }

            // Marketing Hook (Quota 1)
            do_action( 'alezux_finance_payment_received', $user_id, $plan_id, 1 );

        } else {
            // Actualizar existente
            $subscription_id = $subscription->id;
            $current_quota = $subscription->quotas_paid + 1;
            
            $status = 'active';
            if ( $current_quota >= $plan->total_quotas ) {
                $status = 'completed';
            }

            $wpdb->update( $subs_table, [
                'quotas_paid' => $current_quota,
                'status' => $status,
                'last_payment_date' => current_time( 'mysql' ),
                'next_payment_date' => date( 'Y-m-d H:i:s', strtotime( '+1 month' ) )
            ], [ 'id' => $subscription_id ] );

            // Marketing Hook (Quota Recurrente)
            do_action( 'alezux_finance_payment_received', $user_id, $plan_id, $current_quota );
        }

        // 3. Registrar Transacción
        $trans_table = $wpdb->prefix . 'alezux_finanzas_transactions';
        $wpdb->insert( $trans_table, [
            'subscription_id' => $subscription_id,
            'user_id' => $user_id, // Agregue user_id a la tabla transactions en mi mente pero en el schema Install dice que lo tiene? Checked: SI.
            'amount' => $amount,
            'method' => $method,
            'transaction_ref' => $ref,
            'status' => 'succeeded'
        ] );

        // Redireccionar con éxito
        wp_redirect( admin_url( 'admin.php?page=alezux-finanzas-sales&message=success_manual_pay' ) );
        exit;
    }

    public static function handle_simulate_webhook() {
        if ( ! isset( $_POST['alezux_sim_nonce'] ) || ! wp_verify_nonce( $_POST['alezux_sim_nonce'], 'alezux_simulate_action' ) ) {
            wp_die( 'Seguridad inválida.' );
        }

        $email = sanitize_email( $_POST['sim_email'] );
        
        global $wpdb;
        $plan = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}alezux_finanzas_plans LIMIT 1" );
        
        // Si no hay plan, creamos uno dummy rápido
        if ( ! $plan ) {
            $wpdb->insert( $wpdb->prefix . 'alezux_finanzas_plans', [
                'name' => 'Plan Simulado',
                'course_id' => 0,
                'stripe_product_id' => 'prod_mock',
                'stripe_price_id' => 'price_mock',
                'total_quotas' => 4,
                'quota_amount' => 50.00
            ] );
            $plan_id = $wpdb->insert_id;
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
        $response = wp_remote_post( $url, [
            'body' => json_encode( $payload ),
            'headers' => [ 'Content-Type' => 'application/json' ],
            'timeout' => 5,
            'sslverify' => false // Localhost suele fallar SSL
        ] );

        if ( is_wp_error( $response ) ) {
            wp_die( 'Error enviando webhook: ' . $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $result = ($code === 200) ? 'success' : 'failed';

        wp_redirect( admin_url( 'admin.php?page=alezux-finanzas-sales&tab=simulation&sim_result=' . $result ) );
        exit;
    }
}
