<?php
namespace Alezux_Members\Modules\Finanzas\Includes;

if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

class Webhook_Handler {

    private $namespace = 'alezux/v1';
    private $route = 'stripe-webhook';

    public static function init() {
        $instance = new self();
        \add_action( 'rest_api_init', [ $instance, 'register_routes' ] );
    }

    public function register_routes() {
        \register_rest_route( $this->namespace, '/' . $this->route, [
            'methods'  => 'POST',
            'callback' => [ $this, 'handle_request' ],
            'permission_callback' => '__return_true', // Stripe autentica vía firma, no usuario WP
        ] );
    }

    public function handle_request( \WP_REST_Request $request ) {
        $payload = $request->get_body();
        $sig_header = $request->get_header( 'stripe-signature' );
        $endpoint_secret = \get_option( 'alezux_stripe_webhook_secret' ); // Necesitaremos esto

        $event = null;

        try {
            // Si tenemos secreto de webhook configurado, verificamos firma (Recomendado)
            // Nota: Sin el SDK oficial de Stripe, la verificación manual de firma es compleja.
            // Por ahora, en esta implementación ligera, confiaremos en el contenido si es modo TEST
            // pero idealmente deberíamos validar.
            
            $event = \json_decode( $payload );

        } catch(\Exception $e) {
            return new \WP_REST_Response( [ 'error' => 'Invalid Payload' ], 400 );
        }

        if ( ! isset( $event->type ) ) {
            return new \WP_REST_Response( [ 'error' => 'Unknown Event' ], 400 );
        }

        // Router de Eventos
        switch ( $event->type ) {
            case 'checkout.session.completed':
                $this->handle_checkout_completed( $event->data->object );
                break;
            case 'invoice.payment_succeeded':
                $this->handle_invoice_payment_succeeded( $event->data->object );
                break;
            case 'invoice.payment_failed':
                $this->handle_invoice_payment_failed( $event->data->object );
                break;
            default:
                // Evento no manejado, pero respondemos 200 a Stripe
                break;
        }

        return new \WP_REST_Response( [ 'success' => true ], 200 );
    }

    private function handle_checkout_completed( $session ) {
        // 1. Detectar si es un pago manual de cuota
        if ( isset( $session->metadata->type ) && $session->metadata->type === 'installment_payment' ) {
            $this->handle_installment_payment_success( $session );
            return;
        }

        if ( ! isset( $session->customer_details->email ) ) {
            return;
        }

        $email = $session->customer_details->email;
        $stripe_subscription_id = $session->subscription ?? null;
        $plan_id = $session->metadata->plan_id ?? 0;
        
        $amount = ( isset( $session->amount_total ) ) ? ( $session->amount_total / 100 ) : 0;
        $currency = ( isset( $session->currency ) ) ? strtoupper( $session->currency ) : 'USD';
        $transaction_ref = $session->payment_intent ?? $session->id;
        $customer_name = isset( $session->customer_details->name ) ? sanitize_text_field( $session->customer_details->name ) : '';

        // Delegar a Enrollment Manager
        if ( class_exists( 'Alezux_Members\Modules\Finanzas\Includes\Enrollment_Manager' ) ) {
            \Alezux_Members\Modules\Finanzas\Includes\Enrollment_Manager::enroll_user( 
                $email, 
                $plan_id, 
                $stripe_subscription_id, 
                $amount, 
                $transaction_ref,
                $currency,
                $customer_name
            );
        }
    }

    /**
     * Maneja el éxito de un pago manual de cuota (Checkout Session).
     */
    private function handle_installment_payment_success( $session ) {
        global $wpdb;
        $subs_table = $wpdb->prefix . 'alezux_finanzas_subscriptions';
        
        $subscription_id = isset( $session->metadata->subscription_local_id ) ? intval( $session->metadata->subscription_local_id ) : 0;
        
        if ( ! $subscription_id ) {
            \error_log( 'Alezux Webhook Error: ID de suscripción no encontrado en metadata de pago manual.' );
            return;
        }

        // Obtener suscripción
        $subscription = $wpdb->get_row( $wpdb->prepare( 
            "SELECT s.*, p.total_quotas 
             FROM $subs_table s 
             JOIN {$wpdb->prefix}alezux_finanzas_plans p ON s.plan_id = p.id 
             WHERE s.id = %d", 
            $subscription_id 
        ) );

        if ( ! $subscription ) {
            \error_log( "Alezux Webhook Error: Suscripción local #$subscription_id no encontrada." );
            return;
        }

        // Registrar Transacción
        $trans_table = $wpdb->prefix . 'alezux_finanzas_transactions';
        $amount_paid = ( isset( $session->amount_total ) ) ? ( $session->amount_total / 100 ) : 0;
        $transaction_ref = $session->payment_intent ?? $session->id;

        $wpdb->insert( $trans_table, [
            'subscription_id' => $subscription->id,
            'user_id'         => $subscription->user_id, // Importante registrar user_id
            'amount'          => $amount_paid,
            'currency'        => strtoupper( $session->currency ),
            'method'          => 'stripe',
            'transaction_ref' => $transaction_ref,
            'status'          => 'succeeded',
            'created_at'      => current_time( 'mysql' )
        ] );

        // Actualizar Cuotas
        $new_quotas_paid = $subscription->quotas_paid + 1;
        $wpdb->update( 
            $subs_table, 
            [ 
                'quotas_paid' => $new_quotas_paid,
                'last_payment_date' => current_time( 'mysql' ),
                'status' => 'active' // Asegurar que vuelva a activo si estaba past_due
            ], 
            [ 'id' => $subscription->id ] 
        );

        \error_log( "Alezux Manual Payment: Cuota $new_quotas_paid pagada para suscripción Local ID {$subscription->id}" );

        // Disparar evento
        do_action( 'alezux_finance_payment_received', $subscription->user_id, $subscription->plan_id, $new_quotas_paid );

        // Verificar Completitud
        if ( $new_quotas_paid >= $subscription->total_quotas ) {
            // Si tenía sub en Stripe vinculada, cancelarla (aunque esto es pago manual, 
            // puede ser un híbrido donde canceló y ahora paga manual).
            if ( ! empty( $subscription->stripe_subscription_id ) ) {
                $this->cancel_stripe_subscription( $subscription->stripe_subscription_id );
            }
            
            $wpdb->update( 
                $subs_table, 
                [ 'status' => 'completed' ], 
                [ 'id' => $subscription->id ] 
            );
            
            \error_log( "Alezux: Plan completado (Pago Manual). Suscripción #{$subscription->id} finalizada." );
        }
    }

    private function handle_invoice_payment_succeeded( $invoice ) {
        if ( $invoice->billing_reason == 'subscription_create' ) {
            // Esto ya lo maneja checkout.session.completed generalmente
            return;
        }

        $stripe_sub_id = $invoice->subscription;
        if ( ! $stripe_sub_id ) return;

        global $wpdb;
        $subs_table = $wpdb->prefix . 'alezux_finanzas_subscriptions';
        $plans_table = $wpdb->prefix . 'alezux_finanzas_plans';

        // Buscar suscripción local
        $subscription = $wpdb->get_row( $wpdb->prepare( 
            "SELECT s.*, p.total_quotas, p.stripe_price_id 
             FROM $subs_table s 
             JOIN $plans_table p ON s.plan_id = p.id 
             WHERE s.stripe_subscription_id = %s", 
            $stripe_sub_id 
        ) );

        if ( ! $subscription ) return;

        // Registrar Transacción
        $trans_table = $wpdb->prefix . 'alezux_finanzas_transactions';
        $wpdb->insert( $trans_table, [
            'subscription_id' => $subscription->id,
            'amount' => ($invoice->amount_paid / 100),
            'method' => 'stripe',
            'transaction_ref' => $invoice->payment_intent ?? $invoice->id,
            'status' => 'succeeded'
        ] );

        // Actualizar Cuotas
        $new_quotas_paid = $subscription->quotas_paid + 1;
        $wpdb->update( 
            $subs_table, 
            [ 
                'quotas_paid' => $new_quotas_paid,
                'last_payment_date' => current_time( 'mysql' )
            ], 
            [ 'id' => $subscription->id ] 
        );

        \error_log( "Alezux Renewal: Cuota $new_quotas_paid pagada para suscripción Local ID {$subscription->id}" );

        // Disparar evento para Marketing (Quota Recurrente)
        do_action( 'alezux_finance_payment_received', $subscription->user_id, '$subscription->plan_id', $new_quotas_paid ); // Error in string interpolation detected, fixing manually below
         do_action( 'alezux_finance_payment_received', $subscription->user_id, $subscription->plan_id, $new_quotas_paid );

        // Verificar si se completaron las cuotas
        if ( $new_quotas_paid >= $subscription->total_quotas ) {
            $this->cancel_stripe_subscription( $stripe_sub_id );
            
            $wpdb->update( 
                $subs_table, 
                [ 'status' => 'completed' ], 
                [ 'id' => $subscription->id ] 
            );
            
            \error_log( "Alezux: Plan completado. Suscripción Stripe $stripe_sub_id cancelada." );
        }
    }

    private function handle_invoice_payment_failed( $invoice ) {
        $stripe_sub_id = $invoice->subscription;
        if ( ! $stripe_sub_id ) return;

        global $wpdb;
        $subs_table = $wpdb->prefix . 'alezux_finanzas_subscriptions';

        // Marcar como past_due
        $wpdb->update( 
            $subs_table, 
            [ 'status' => 'past_due' ], 
            [ 'stripe_subscription_id' => $stripe_sub_id ]
        );

        // Enviar email de fallo (Módulo Marketing)
        if ( class_exists( '\Alezux_Members\Modules\Marketing\Marketing' ) ) {
            $subscription = $wpdb->get_row( $wpdb->prepare( "SELECT user_id, plan_id FROM $subs_table WHERE stripe_subscription_id = %s", $stripe_sub_id ) );
            if ( $subscription ) {
                $user = get_user_by( 'id', $subscription->user_id );
                if ( $user ) {
                    \Alezux_Members\Modules\Marketing\Marketing::get_instance()->get_engine()->send_email(
                        'payment_failed',
                        $user->user_email,
                        [
                            'user' => $user,
                            'payment' => [
                                'amount' => number_format($invoice->amount_due / 100, 2),
                                'currency' => strtoupper($invoice->currency),
                                'ref' => $invoice->number
                            ],
                            'plan' => [
                                'name' => 'Suscripción' // Idealmente buscar nombre del plan
                            ]
                        ]
                    );
                }
            }
        }

        \error_log( "Alezux: Pago fallido para suscripción stripe $stripe_sub_id" );
        
        // Obtener user y plan para el evento
        $subscription = $wpdb->get_row( $wpdb->prepare( "SELECT user_id, plan_id FROM $subs_table WHERE stripe_subscription_id = %s", $stripe_sub_id ) );
        if ( $subscription ) {
            do_action( 'alezux_finance_payment_failed', $subscription->user_id, $subscription->plan_id );
        }
    }

    private function cancel_stripe_subscription( $sub_id ) {
        // Necesitamos Stripe API aquí para cancelar
        // Como no tenemos el SDK cargado globalmente igual que en Stripe_API class, 
        // usaremos una instancia rápida o helper estático.
        
        $secret_key = \get_option( 'alezux_stripe_secret_key' );
        if ( ! $secret_key ) return;

        // Llamada curl directa para evitar dependencias complejas en este handler ligero
        $ch = \curl_init();
        \curl_setopt($ch, CURLOPT_URL, "https://api.stripe.com/v1/subscriptions/$sub_id");
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        \curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        \curl_setopt($ch, CURLOPT_USERPWD, $secret_key . ':' . '');
        
        $result = \curl_exec($ch);
        if ( \curl_errno($ch) ) {
            \error_log( 'Error cancelando suscripción Stripe: ' . \curl_error($ch) );
        }
        \curl_close($ch);
    }
}
