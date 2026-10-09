<?php
namespace Alezux_Members\Modules\Finanzas\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Enrollment_Manager {

    /**
     * Procesa la inscripción de un usuario tras un pago exitoso.
     *
     * @param string $email Email del usuario (Stripe Customer Email)
     * @param int $plan_id ID del Plan interno
     * @param string $stripe_sub_id ID de suscripción de Stripe (opcional)
     * @param float $amount Monto pagado
     * @param string $transaction_ref ID de referencia (PaymentIntent o Session ID)
     * @return int|false ID del usuario o false si falla
     */
    public static function enroll_user( $email, $plan_id, $stripe_sub_id = null, $amount = 0.0, $transaction_ref = '', $currency = 'USD', $full_name = '', $payment_method = 'stripe', $manual_password = '' ) {
        global $wpdb;
        
        error_log( "Alezux Enrollment: Iniciando para $email - Plan $plan_id - Ref $transaction_ref - Method $payment_method" );

        // 1. Gestionar Usuario (Buscar o Crear)
        $user = get_user_by( 'email', $email );
        $is_new_user = false;

        if ( ! $user ) {
            $username = sanitize_user( current( explode( '@', $email ) ) );
            // Asegurar username único
            if ( username_exists( $username ) ) {
                $username .= '_' . rand( 100, 999 );
            }
            $password = ! empty( $manual_password ) ? $manual_password : wp_generate_password();
            $user_id = wp_create_user( $username, $password, $email );
            
            if ( is_wp_error( $user_id ) ) {
                error_log( 'Alezux Error: No se pudo crear usuario para ' . $email . ' - ' . $user_id->get_error_message() );
                return false;
            }

            $user = get_user_by( 'id', $user_id );
            
            // Asignar rol por defecto
            $user->set_role( 'subscriber' ); 
            
            // Actualizar Nombre y Apellido si vienen de Stripe
            if ( ! empty( $full_name ) ) {
                $parts = explode( ' ', trim( $full_name ), 2 );
                $first_name = $parts[0];
                $last_name  = isset( $parts[1] ) ? $parts[1] : '';

                wp_update_user( [
                    'ID' => $user_id,
                    'first_name' => $first_name,
                    'last_name'  => $last_name,
                    'display_name' => $full_name
                ] );
            }
            
            $is_new_user = true;
            
            // Enviar correo de Bienvenida (Módulo Marketing)
            if ( class_exists( '\Alezux_Members\Modules\Marketing\Marketing' ) ) {
                // Obtener título del curso para el correo
                $course_title = 'la plataforma';
                if ( isset( $plan_id ) ) {
                    global $wpdb;
                    $p_table = $wpdb->prefix . 'alezux_finanzas_plans';
                    // Fix: Table does not have course_name column. Fetch course_id instead.
                    $p_data = $wpdb->get_row( $wpdb->prepare( "SELECT name, course_id FROM $p_table WHERE id = %d", $plan_id ) );
                    
                    if ( $p_data ) {
                         if ( ! empty( $p_data->course_id ) ) {
                             $c_title = get_the_title( $p_data->course_id );
                             if ( $c_title ) {
                                 $course_title = $c_title;
                             } else {
                                 $course_title = $p_data->name; // Fallback to Plan Name
                             }
                         } else {
                             $course_title = $p_data->name; // Fallback to Plan Name
                         }
                    }
                }

                \Alezux_Members\Modules\Marketing\Marketing::get_instance()->get_engine()->send_email(
                    'student_welcome',
                    $email,
                    [
                        'user' => $user,
                        'password' => $password,
                        'course_title' => $course_title,
                        'login_url' => wp_login_url()
                    ]
                );
                
                error_log( "Alezux: Correo de bienvenida enviado a $email" );
            } else {
                // Fallback si Marketing no está activo
                wp_new_user_notification( $user_id, null, 'user' ); 
            }
        }

        $user_id = $user->ID;

        // ACTUALIZACIÓN: Actualizar nombre siempre si viene de Stripe/Input, incluso para usuarios existentes
        // Esto corrige el caso donde un usuario existente con datos antiguos/erroneos compra de nuevo con nombre correcto
        if ( ! empty( $full_name ) ) {
            $parts = explode( ' ', trim( $full_name ), 2 );
            $first_name = $parts[0];
            $last_name  = isset( $parts[1] ) ? $parts[1] : '';

            // Opcional: Podríamos verificar si ya tiene nombre para no sobrescribir, 
            // pero si viene del checkout reciente, asumimos que es el dato más actual.
            wp_update_user( [
                'ID' => $user_id,
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'display_name' => $full_name
            ] );
            error_log( "Alezux: Nombre actualizado para usuario $email ($user_id): $full_name" );
        }

        // 2. Obtener Info del Plan Local
        $plan_table = $wpdb->prefix . 'alezux_finanzas_plans';
        $plan = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $plan_table WHERE id = %d", $plan_id ) );

        if ( ! $plan ) {
            error_log( 'Alezux Error: Plan no encontrado ID ' . $plan_id );
            // Fallback para evitar error fatal si no hay plan, pero continuamos
             $is_one_time = ( isset( $amount ) && $amount > 0 ); // Adivinar
        } else {
             $is_one_time = ( ( isset( $plan->frequency ) && $plan->frequency === 'contado' ) || $plan->total_quotas == 1 );
        }

        // 3. Matricular en LearnDash
        if ( $plan && function_exists( 'ld_update_course_access' ) ) {
            // Verificar si el curso es valido
            if ( $plan->course_id > 0 ) {
                ld_update_course_access( $user_id, $plan->course_id );
                error_log( "Alezux: Usuario $user_id matriculado exitosamente en curso {$plan->course_id} via LD." );
            } else {
                 error_log( "Alezux Warning: El plan {$plan_id} no tiene un course_id valido ({$plan->course_id}). No se pudo matricular." );
            }
        } elseif ( ! function_exists( 'ld_update_course_access' ) ) {
            error_log( "Alezux Error: Función 'ld_update_course_access' no existe. LearnDash no está activo?" );
        }

        // 4. Registrar Suscripción/Compra
        $subs_table = $wpdb->prefix . 'alezux_finanzas_subscriptions';
        
        // Verificar duplicados activos
        $existing_sub = $wpdb->get_var( $wpdb->prepare( 
            "SELECT id FROM $subs_table WHERE user_id = %d AND plan_id = %d AND status = 'active'", 
            $user_id, $plan_id 
        ) );

        $subscription_id = 0;

        if ( ! $existing_sub ) {
            
            $status = $is_one_time ? 'completed' : 'active';
            $quotas_paid = $is_one_time ? ( $plan->total_quotas ?? 1 ) : 1; 

            // Si es manual y no one_time, igual lo marcamos activo.
            // Ojo: Si es manual recurrente, la lógica de next_payment_date manual es compleja.
            // Por ahora asumimos manual = pagó esta cuota.

            $wpdb->insert( $subs_table, [
                'user_id' => $user_id,
                'plan_id' => $plan_id,
                'stripe_subscription_id' => $stripe_sub_id, 
                'status' => $status,
                'quotas_paid' => $quotas_paid,
                'last_payment_date' => current_time( 'mysql' ),
                'next_payment_date' => ( $stripe_sub_id && ! $is_one_time ) ? date( 'Y-m-d H:i:s', strtotime( '+1 month' ) ) : null
            ] );
            $subscription_id = $wpdb->insert_id;
        } else {
            $subscription_id = $existing_sub;
        }

        // 5. Registrar Transacción
        $trans_table = $wpdb->prefix . 'alezux_finanzas_transactions';
        
        // Evitar duplicar transacción si ya existe con esa referencia
        $existing_trans = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $trans_table WHERE transaction_ref = %s", $transaction_ref ) );
        
        if ( ! $existing_trans ) {
            $inserted = $wpdb->insert( $trans_table, [
                'subscription_id' => $subscription_id,
                'plan_id' => $plan_id, // Redundante pero útil
                'user_id' => $user_id,
                'amount' => $amount,
                'currency' => $currency,
                'method' => $payment_method, // USAR PARAMETRO
                'transaction_ref' => $transaction_ref,
                'status' => 'succeeded',
                'created_at' => current_time( 'mysql' )
            ] );
            
            if ( $inserted ) {
                error_log( "Alezux Transaction: Insertada transacción $transaction_ref para usuario $user_id" );
                // IMPORTANTE: Disparar evento para Marketing solo si es nueva transacción
                do_action( 'alezux_finance_payment_received', $user_id, $plan_id, 1 );

                // Enviar correo de Confirmación de Pago (A todos)
                if ( class_exists( '\Alezux_Members\Modules\Marketing\Marketing' ) ) {
                    $u = get_user_by( 'id', $user_id );
                    $model_plan = [ 'name' => 'Producto / Servicio' ]; 
                    
                    // Re-query plan name just in case local $plan var isn't fully robust or to be safe
                    global $wpdb;
                    $p_table_2 = $wpdb->prefix . 'alezux_finanzas_plans';
                    $p_info = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM $p_table_2 WHERE id = %d", $plan_id ) );
                    if ( $p_info ) {
                        $model_plan['name'] = $p_info->name;
                    }

                    \Alezux_Members\Modules\Marketing\Marketing::get_instance()->get_engine()->send_email(
                        'payment_success',
                        $email,
                        [
                            'user' => $u,
                            'plan' => $model_plan,
                            'payment' => [
                                'amount' => number_format( $amount, 2 ),
                                'currency' => $currency,
                                'ref' => $transaction_ref
                            ]
                        ]
                    );
                    error_log( "Alezux: Correo de confirmación de pago enviado a $email" );
                }

            } else {
                error_log( "Alezux Error: Fallo al insertar transacción. DB Error: " . $wpdb->last_error );
            }
        } else {
            error_log( "Alezux Info: Transacción $transaction_ref ya existía. Omitiendo insert." );
            // Si la transacción ya existía, asumimos que el correo ya se envió antes para no duplicar.
        }

        return $user_id;
    }
}
