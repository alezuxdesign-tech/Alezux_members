<?php
namespace Alezux_Members\Modules\Finanzas\Includes;

if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax_Handler {

	public static function init() {
		\add_action( 'wp_ajax_alezux_get_course_modules', [ __CLASS__, 'get_course_modules' ] );
		\add_action( 'wp_ajax_alezux_create_stripe_plan', [ __CLASS__, 'create_stripe_plan' ] );
        \add_action( 'wp_ajax_alezux_get_sales_history', [ __CLASS__, 'get_sales_history' ] );
        \add_action( 'wp_ajax_alezux_get_plans_list', [ __CLASS__, 'get_plans_list' ] );
        \add_action( 'wp_ajax_alezux_get_subscriptions_list', [ __CLASS__, 'get_subscriptions_list' ] );
        \add_action( 'wp_ajax_alezux_delete_plan', [ __CLASS__, 'delete_plan' ] );
        \add_action( 'wp_ajax_alezux_manual_subs_payment', [ __CLASS__, 'manual_subscription_payment' ] );
        \add_action( 'wp_ajax_alezux_get_finance_kpis', [ __CLASS__, 'get_finance_kpis' ] );
        \add_action( 'wp_ajax_alezux_get_plan_details', [ __CLASS__, 'get_plan_details' ] ); // NUEVO
        \add_action( 'wp_ajax_alezux_update_plan', [ __CLASS__, 'update_plan' ] ); // NUEVO
        \add_action( 'wp_ajax_alezux_get_my_financial_data', [ __CLASS__, 'get_my_financial_data' ] );
        \add_action( 'wp_ajax_alezux_create_installment_checkout', [ __CLASS__, 'create_installment_checkout' ] );
        \add_action( 'wp_ajax_alezux_get_sales_stats', [ __CLASS__, 'get_sales_stats' ] ); // Migrated from Finanzas.php
	}

	public static function get_course_modules() {
		\check_ajax_referer( 'alezux_finanzas_nonce', 'nonce' );

		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_send_json_error( 'Permisos insuficientes.' );
		}

		$course_id = \intval( $_POST['course_id'] );
		
		if ( ! $course_id ) {
			\wp_send_json_error( 'ID de curso inválido.' );
		}

		// Obtener módulos/lecciones del curso usando funciones de LearnDash
        // Nota: Asumimos que LearnDash está activo. Si no, usamos posts normales.
        $modules = [];
        
        if ( \function_exists( 'learndash_get_course_steps' ) ) {
            $steps = \learndash_get_course_steps( $course_id );
            foreach ( $steps as $step_id ) {
                $post = \get_post( $step_id );
                // Solo incluimos lecciones (sfwd-lessons), los tópicos se omiten
                if ( $post->post_type === 'sfwd-lessons' ) {
                    // OMITIR Separadores visuales
                    if ( strpos( $post->post_title, '[Separador' ) !== false ) {
                        continue;
                    }

                    $modules[] = [
                        'id' => $post->ID,
                        'title' => $post->post_title,
                        'type' => $post->post_type
                    ];
                }
            }
        } else {
            // Fallback si función LD no existe (raro)
            $modules[] = ['id' => 0, 'title' => 'LearnDash no detectado', 'type' => 'error'];
        }

		\wp_send_json_success( $modules );
	}

	public static function create_stripe_plan() {
		\check_ajax_referer( 'alezux_finanzas_nonce', 'nonce' );

        if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_send_json_error( 'Permisos insuficientes.' );
		}

        $plan_name = \sanitize_text_field( $_POST['plan_name'] );
        $course_id = \intval( $_POST['course_id'] );
        $quota_amount = \floatval( $_POST['quota_amount'] );
        $total_quotas = \intval( $_POST['total_quotas'] );
        $rules = isset($_POST['rules']) ? $_POST['rules'] : [];
        $whatsapp_number = isset($_POST['whatsapp_number']) ? \esc_url_raw($_POST['whatsapp_number']) : '';

        // Determinar intervalo
        $interval = $_POST['frequency'] ?? 'month';
        if ( $total_quotas == 1 ) {
            $interval = 'contado'; // Lógica especial para pago único
        }

        $is_internal = isset($_POST['is_internal']) && $_POST['is_internal'] == '1';
        $stripe_prod_id = null;
        $stripe_price_id = null;

        if ( ! $is_internal ) {
            // Integración con Stripe
            $stripe = \Alezux_Members\Modules\Finanzas\Includes\Stripe_API::get_instance();
            
            $stripe_result = $stripe->create_plan( $plan_name, $quota_amount, $interval );

            if ( \is_wp_error( $stripe_result ) ) {
                 \wp_send_json_error( 'Error de Stripe: ' . $stripe_result->get_error_message() );
            }

            $stripe_prod_id = $stripe_result['product_id'];
            $stripe_price_id = $stripe_result['price_id'];
        } else {
            // Plan interno: generar IDs falsos para que el resto del sistema no se rompa si requiere un string
            $stripe_prod_id = 'internal_prod_' . uniqid();
            $stripe_price_id = 'internal_price_' . uniqid();
        }

        // Guardar en DB
        global $wpdb;
        $table_plans = $wpdb->prefix . 'alezux_finanzas_plans';
        
        // Generar Token Único
        $token = bin2hex( random_bytes( 16 ) );

        $wpdb->insert( 
            $table_plans, 
            [ 
                'name' => $plan_name, 
                'course_id' => $course_id,
                'stripe_product_id' => $stripe_prod_id,
                'stripe_price_id' => $stripe_price_id,
                'total_quotas' => $total_quotas,
                'quota_amount' => $quota_amount,
                'frequency'    => $interval,
                'token'        => $token,
                'whatsapp_number' => $whatsapp_number,
                'access_rules' => \json_encode( $rules ),
            ] 
        );

        $plan_id = $wpdb->insert_id;

        if ( $plan_id ) {
            $msg = $is_internal ? 'Plan interno creado correctamente en WordPress.' : 'Plan creado correctamente en Stripe y WordPress.';
            \wp_send_json_success( [ 'plan_id' => $plan_id, 'message' => $msg ] );
        } else {
            \wp_send_json_error( 'Error al guardar en base de datos local.' );
        }
	}


    public static function get_sales_history() {
        \check_ajax_referer( 'alezux_finanzas_nonce', 'nonce' );

        if ( ! \current_user_can( 'manage_options' ) ) {
            \wp_send_json_error( 'Permisos insuficientes.' );
        }

        global $wpdb;

        // Parametros DataTables / Custom
        $page = isset($_POST['page']) ? \intval($_POST['page']) : 1;
        $limit = isset($_POST['limit']) ? \intval($_POST['limit']) : 10;
        $search = isset($_POST['search']) ? \sanitize_text_field($_POST['search']) : '';
        $filter_course = isset($_POST['filter_course']) ? \intval($_POST['filter_course']) : 0;
        $filter_status = isset($_POST['filter_status']) ? \sanitize_text_field($_POST['filter_status']) : '';
        $start_date = isset($_POST['start_date']) ? \sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? \sanitize_text_field($_POST['end_date']) : '';
        
        $offset = ($page - 1) * $limit;

        // Tablas
        $t_trans = $wpdb->prefix . 'alezux_finanzas_transactions';
        $t_plans = $wpdb->prefix . 'alezux_finanzas_plans';
        $t_subs = $wpdb->prefix . 'alezux_finanzas_subscriptions';
        $t_users = $wpdb->users;
        
        // Query Base
        $sql = "SELECT SQL_CALC_FOUND_ROWS 
                    t.*, 
                    u.display_name as user_name, 
                    u.user_email, 
                    p.name as plan_name, 
                    p.total_quotas, 
                    p.frequency,
                    s.quotas_paid as sub_quotas_paid,
                    s.status as sub_status
                FROM $t_trans t
                LEFT JOIN $t_users u ON t.user_id = u.ID
                LEFT JOIN $t_plans p ON t.plan_id = p.id
                LEFT JOIN $t_subs s ON t.subscription_id = s.id
                WHERE 1=1";

        $args = [];

        // Filtros
        if ( ! empty($search) ) {
            $sql .= " AND (u.display_name LIKE %s OR u.user_email LIKE %s OR t.transaction_ref LIKE %s)";
            $args[] = '%' . $wpdb->esc_like($search) . '%';
            $args[] = '%' . $wpdb->esc_like($search) . '%';
            $args[] = '%' . $wpdb->esc_like($search) . '%';
        }

        if ( $filter_course > 0 ) {
            // Nota: El filtro pide por curso, pero transaction tiene plan_id. Join con plan tiene course_id.
            $sql .= " AND p.course_id = %d";
            $args[] = $filter_course;
        }
        
        if ( ! empty($filter_status) ) {
            $sql .= " AND t.status = %s";
            $args[] = $filter_status;
        }

        if ( ! empty($start_date) && ! empty($end_date) ) {
            $sql .= " AND DATE(t.created_at) BETWEEN %s AND %s";
            $args[] = $start_date;
            $args[] = $end_date;
        } elseif ( ! empty($start_date) ) {
            // Caso borde: solo fecha inicio seleccionada (flatpickr a veces envia solo start si no se completa rango)
            $sql .= " AND DATE(t.created_at) >= %s";
            $args[] = $start_date;
        }

        // Orden y Paginación
        $sql .= " ORDER BY t.created_at DESC";
        $sql .= " LIMIT %d OFFSET %d";
        $args[] = $limit;
        $args[] = $offset;

        $results = $wpdb->get_results( $wpdb->prepare( $sql, $args ) );
        $total_rows = $wpdb->get_var( "SELECT FOUND_ROWS()" );

        // Formatear resultados
        $data = [];
        foreach ($results as $row) {
            
            // Logica "Recurrente (1/5)" vs "Contado"
            $payment_desc = 'Pago Único';
            if ( $row->total_quotas > 1 ) {
                 // Intentar calcular cuota actual aproximada: 
                 // Si sub_quotas_paid es 3, y esta es la ultima transaccion, quizas es la 3.
                 // Simplificación: Mostrar X/Total basado en subscription actual 
                 // OMEJOR: Contado vs Suscripción
                 $payment_desc = "Recurrente ({$row->sub_quotas_paid}/{$row->total_quotas})";
            } elseif ( $row->total_quotas == 1 ) {
                 $payment_desc = 'De Contado';
            }

            $data[] = [
                'id' => $row->id,
                'student' => $row->user_name ? $row->user_name . ' (' . $row->user_email . ')' : 'Usuario Eliminado',
                'method' => $row->method ? ucfirst($row->method) : 'Sistema',
                'amount' => $row->amount . ' ' . $row->currency,
                'course' => $row->plan_name ? $row->plan_name : 'Plan Eliminado',
                'quotas_desc' => $payment_desc,
                'status' => $row->status,
                'date' => date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $row->created_at ) ),
                'ref' => $row->transaction_ref
            ];
        }

        \wp_send_json_success( [
            'rows' => $data,
            'total' => (int)$total_rows,
            'pages' => ceil($total_rows / $limit),
            'current_page' => $page
        ] );
    }

    public static function get_plans_list() {
        \check_ajax_referer( 'alezux_finanzas_nonce', 'nonce' );

        if ( ! \current_user_can( 'manage_options' ) ) {
            \wp_send_json_error( 'Permisos insuficientes.' );
        }

        global $wpdb;
    $search = isset($_POST['search']) ? \sanitize_text_field($_POST['search']) : '';
    $course_id = isset($_POST['course_id']) ? \intval($_POST['course_id']) : 0;
    
    // Pagination params
    $limit = isset($_POST['limit']) ? \intval($_POST['limit']) : 20;
    $paged = isset($_POST['paged']) ? \intval($_POST['paged']) : 1;
    if ($limit < 1) $limit = 20;
    if ($paged < 1) $paged = 1;
    $offset = ($paged - 1) * $limit;
    
    $t_plans = $wpdb->prefix . 'alezux_finanzas_plans';
    
    // Base SQL
    $where_sql = " WHERE 1=1";
    $args = [];

    if ( ! empty( $search ) ) {
        $where_sql .= " AND p.name LIKE %s";
        $args[] = '%' . $wpdb->esc_like($search) . '%';
    }

    if ( $course_id > 0 ) {
        $where_sql .= " AND p.course_id = %d";
        $args[] = $course_id;
    }

    // Get Total Count
    $count_sql = "SELECT COUNT(*) FROM $t_plans p $where_sql";
    if ( ! empty( $args ) ) {
        $total_plans = $wpdb->get_var( $wpdb->prepare( $count_sql, $args ) );
    } else {
        $total_plans = $wpdb->get_var( $count_sql );
    }
    
    // Get Rows
    $sql = "SELECT p.* FROM $t_plans p $where_sql ORDER BY p.id DESC LIMIT %d OFFSET %d";
    $args[] = $limit;
    $args[] = $offset;

    // Execute query with args
    $results = $wpdb->get_results( $wpdb->prepare( $sql, $args ) );

    $data = [];
        foreach ( $results as $row ) {
            $course_title = get_the_title( $row->course_id );
            
            // AUTO-MIGRACIÓN: Si no tiene token, generarlo y guardarlo ahora mismo.
            $token = $row->token;
            if ( empty( $token ) ) {
                $token = bin2hex( random_bytes( 16 ) );
                $wpdb->update( 
                    $t_plans, 
                    [ 'token' => $token ], 
                    [ 'id' => $row->id ] 
                );
            }

            // Generar Link Directo Seguro
            $buy_link = home_url( '/?alezux_buy_token=' . $token );

            $data[] = [
                'id' => $row->id,
                'name' => $row->name,
                'course' => $course_title ? $course_title : '(Curso Eliminado)',
                'price' => $row->quota_amount,
                'quotas' => $row->total_quotas,
                'frequency' => $row->frequency,
                'buy_link' => $buy_link
            ];
        }

        \wp_send_json_success( $data );
    }

    public static function delete_plan() {
        \check_ajax_referer( 'alezux_finanzas_nonce', 'nonce' );

        if ( ! \current_user_can( 'manage_options' ) ) {
            \wp_send_json_error( 'Permisos insuficientes.' );
        }

        $id = isset($_POST['id']) ? \intval($_POST['id']) : 0;
        if ( !$id ) \wp_send_json_error('ID Inválido');

        global $wpdb;
        $t_plans = $wpdb->prefix . 'alezux_finanzas_plans';
        
        $deleted = $wpdb->delete( $t_plans, ['id' => $id] );

        if ( $deleted ) {
            \wp_send_json_success( 'Plan eliminado.' );
        } else {
            \wp_send_json_error( 'No se pudo eliminar el plan o no existe.' );
        }
    }

    public static function get_subscriptions_list() {
        \check_ajax_referer( 'alezux_finanzas_nonce', 'nonce' );

        if ( ! \current_user_can( 'manage_options' ) ) {
            \wp_send_json_error( 'Permisos insuficientes.' );
        }

        global $wpdb;
    $search = isset($_POST['search']) ? \sanitize_text_field($_POST['search']) : '';
    
    // Pagination
    $limit = isset($_POST['limit']) ? \intval($_POST['limit']) : 20;
    $paged = isset($_POST['paged']) ? \intval($_POST['paged']) : 1;
    if ($limit < 1) $limit = 20;
    if ($paged < 1) $paged = 1;
    $offset = ($paged - 1) * $limit;

    $t_subs = $wpdb->prefix . 'alezux_finanzas_subscriptions';
    $t_plans = $wpdb->prefix . 'alezux_finanzas_plans';
    $t_users = $wpdb->users;
    
    $where_sql = " WHERE 1=1";
    $args = [];

    if ( ! empty( $search ) ) {
        $where_sql .= " AND (u.display_name LIKE %s OR u.user_email LIKE %s)";
        $args[] = '%' . $wpdb->esc_like($search) . '%';
        $args[] = '%' . $wpdb->esc_like($search) . '%';
    }

    // Get Total Count
    $count_sql = "SELECT COUNT(*) FROM $t_subs s
            LEFT JOIN $t_users u ON s.user_id = u.ID
            LEFT JOIN $t_plans p ON s.plan_id = p.id
            $where_sql";
    
    if ( ! empty( $args ) ) {
        $total_subs = $wpdb->get_var( $wpdb->prepare( $count_sql, $args ) );
    } else {
        $total_subs = $wpdb->get_var( $count_sql );
    }

    // Get Rows
    $sql = "SELECT s.*, u.display_name, u.user_email, p.name as plan_name, p.total_quotas, p.quota_amount 
            FROM $t_subs s
            LEFT JOIN $t_users u ON s.user_id = u.ID
            LEFT JOIN $t_plans p ON s.plan_id = p.id
            $where_sql
            ORDER BY s.created_at DESC
            LIMIT %d OFFSET %d";
    
    $args[] = $limit;
    $args[] = $offset;

    if ( ! empty( $args ) ) {
        $results = $wpdb->get_results( $wpdb->prepare( $sql, $args ) );
    } else {
        $results = $wpdb->get_results( $sql ); // Fallback (should have args always due to limit)
    }

    $data = [];
    foreach ( $results as $row ) {
        // Calcular próximo pago
        $next_payment = '-';
        if ( $row->status === 'active' && $row->next_payment_date ) {
            $next_payment = \date_i18n( \get_option( 'date_format' ), \strtotime( $row->next_payment_date ) );
            
            // Si está vencido
            if ( \strtotime( $row->next_payment_date ) < \time() ) {
                $next_payment .= ' <span style="color:red;">(Atrasado)</span>';
            }
        } elseif ( $row->status === 'completed' ) {
            $next_payment = 'Pagado Totalmente';
        }

        // Avatar
        $avatar_url = \get_avatar_url( $row->user_id, ['size' => 48] );
        
        // Porcentaje
        $percent = 0;
        if ( $row->total_quotas > 0 ) {
            $percent = \round( ( $row->quotas_paid / $row->total_quotas ) * 100 );
        }

        $data[] = [
            'id' => $row->id,
            'student' => $row->display_name ? $row->display_name : 'Usuario Eliminado',
            'student_email' => $row->user_email, // Raw email
            'student_avatar' => $avatar_url,
            'plan' => $row->plan_name,
            'total_quotas' => $row->total_quotas, // Para mostrar "3 CUOTAS"
            'amount' => '$' . \number_format($row->quota_amount, 2, ',', '.'), // Formato correcto moneda
            'raw_amount' => $row->quota_amount,
            'status' => $row->status,
            'quotas_paid' => $row->quotas_paid,
            'percent' => $percent,
            'next_payment' => $next_payment,
            'next_payment_raw' => $row->next_payment_date,
            'stripe_id' => $row->stripe_subscription_id
        ];
    }

    \wp_send_json_success( [
        'rows' => $data,
        'total_pages' => \ceil( $total_subs / $limit ),
        'current_page' => $paged,
        'total_subs' => $total_subs
    ] );
}

    public static function manual_subscription_payment() {
        \check_ajax_referer( 'alezux_finanzas_nonce', 'nonce' );

        if ( ! \current_user_can( 'manage_options' ) ) {
            \wp_send_json_error( 'Permisos insuficientes.' );
        }

        $sub_id = isset($_POST['subscription_id']) ? \intval($_POST['subscription_id']) : 0;
        $amount = isset($_POST['amount']) ? \floatval($_POST['amount']) : 0;
        $note = isset($_POST['note']) ? \sanitize_textarea_field($_POST['note']) : '';

        if ( !$sub_id || !$amount ) {
            \wp_send_json_error( 'Datos incompletos.' );
        }

        global $wpdb;
        $t_subs = $wpdb->prefix . 'alezux_finanzas_subscriptions';
        $t_sales = $wpdb->prefix . 'alezux_finanzas_sales_history';
        $t_plans = $wpdb->prefix . 'alezux_finanzas_plans';

        // Obtener suscripción actual
        $sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t_subs WHERE id = %d", $sub_id ) );

        if ( !$sub ) {
            \wp_send_json_error( 'Suscripción no encontrada.' );
        }

        // Obtener detalles del plan para saber total de cuotas
        $plan = $wpdb->get_row( $wpdb->prepare( "SELECT total_quotas FROM $t_plans WHERE id = %d", $sub->plan_id ) );
        $total_quotas = $plan ? $plan->total_quotas : 0;

        // 1. Insertar en Historial de Ventas
        $wpdb->insert(
            $t_sales,
            [
                'user_id' => $sub->user_id,
                'plan_id' => $sub->plan_id,
                'amount' => $amount,
                'currency' => 'USD', // Asumido
                'status' => 'succeeded',
                'payment_method' => 'manual', // Importante para diferenciar
                'stripe_payment_id' => 'MANUAL_' . \time(), // ID ficticio único
                'created_at' => \current_time( 'mysql' )
            ]
        );

        // 2. Actualizar Suscripción
        $current_quotas_paid = (int) $sub->quotas_paid;
        $new_quotas_paid = $current_quotas_paid + 1;
        $new_status = $sub->status;

        // Si estaba atrasado, lo reactivamos (asumiendo que pagó lo pendiente)
        if ( $sub->status === 'past_due' || $sub->status === 'canceled' ) {
            $new_status = 'active';
        }

        // Verificar si completó el plan
        if ( $new_quotas_paid >= $total_quotas ) {
            $new_status = 'completed';
        }

        // Actualizar fecha de update y quotas
        $result = $wpdb->update(
            $t_subs,
            [
                'quotas_paid' => $new_quotas_paid,
                'status' => $new_status
            ],
            [ 'id' => $sub_id ]
        );

        // Opcional: Agregar nota interna (si tuviéramos tabla de notas) o log
        
        \wp_send_json_success( 'Pago registrado y suscripción actualizada.' );
    }

    public static function get_finance_kpis() {
        \check_ajax_referer( 'alezux_finanzas_nonce', 'nonce' );

        // Obtener parametros de filtro
        $start = isset($_POST['start_date']) ? \sanitize_text_field($_POST['start_date']) : \date('Y-m-01');
        $end = isset($_POST['end_date']) ? \sanitize_text_field($_POST['end_date']) : \date('Y-m-t');

        // Calcular usando helpers
        $revenue = \Alezux_Members\Modules\Finanzas\Includes\Shortcodes::calculate_revenue( $start, $end );
        $projected = \Alezux_Members\Modules\Finanzas\Includes\Shortcodes::calculate_projected( $start, $end );
        
        // Devolver respuesta JSON
        \wp_send_json_success( [
            'revenue_period' => $revenue,
            'projected_period' => $projected,
            'formatted' => [
                'revenue_period' => '$' . \number_format( $revenue, 2 ),
                'projected_period' => '$' . \number_format( $projected, 2 )
            ]
        ] );
    }
    public static function get_plan_details() {
        \check_ajax_referer( 'alezux_finanzas_nonce', 'nonce' );

        if ( ! \current_user_can( 'manage_options' ) ) {
            \wp_send_json_error( 'Permisos insuficientes.' );
        }

        $plan_id = isset($_POST['plan_id']) ? \intval($_POST['plan_id']) : 0;
        
        if ( ! $plan_id ) {
            \wp_send_json_error( 'ID de plan inválido.' );
        }

        global $wpdb;
        $table_plans = $wpdb->prefix . 'alezux_finanzas_plans';
        $plan = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_plans WHERE id = %d", $plan_id ) );

        if ( ! $plan ) {
            \wp_send_json_error( 'El plan no existe.' );
        }

        // Decodificar reglas
        $plan->access_rules = \json_decode( $plan->access_rules );

        // Obtener nombre del curso para mostrarlo
        if ( $plan->course_id ) {
            $plan->course_name = \get_the_title( $plan->course_id );
        } else {
             $plan->course_name = '(Curso Eliminado)';
        }

        \wp_send_json_success( $plan );
    }

    public static function update_plan() {
        \check_ajax_referer( 'alezux_finanzas_nonce', 'nonce' );

        if ( ! \current_user_can( 'manage_options' ) ) {
            \wp_send_json_error( 'Permisos insuficientes.' );
        }

        $plan_id = isset($_POST['plan_id']) ? \intval($_POST['plan_id']) : 0;
        $plan_name = isset($_POST['plan_name']) ? \sanitize_text_field($_POST['plan_name']) : '';
        $rules = isset($_POST['rules']) ? $_POST['rules'] : [];
        $whatsapp_number = isset($_POST['whatsapp_number']) ? \esc_url_raw($_POST['whatsapp_number']) : '';

        if ( ! $plan_id || empty($plan_name) ) {
            \wp_send_json_error( 'Datos incompletos.' );
        }

        global $wpdb;
        $table_plans = $wpdb->prefix . 'alezux_finanzas_plans';

        $updated = $wpdb->update( 
            $table_plans, 
            [ 
                'name' => $plan_name, 
                'whatsapp_number' => $whatsapp_number,
                'access_rules' => \json_encode( $rules )
            ], 
            [ 'id' => $plan_id ] 
        );

        if ( $updated !== false ) {
            \wp_send_json_success( 'Plan actualizado correctamente.' );
        } else {
             \wp_send_json_error( 'No se pudo actualizar o no hubo cambios.' );
        }
    }

    /**
     * Obtiene datos financieros del usuario actual para el perfil.
     */
    public static function get_my_financial_data() {
        \check_ajax_referer( 'alezux_finanzas_nonce', 'nonce' );

        if ( ! \is_user_logged_in() ) {
            \wp_send_json_error( 'Debes iniciar sesión.' );
        }

        \nocache_headers(); // Prevenir caché
        $user_id = \get_current_user_id();
        
        if ( ! $user_id ) {
             \wp_send_json_error( 'Usuario no identificado.' );
        }
        
        global $wpdb;

        // 1. Obtener Suscripciones (Estado de Cuenta)
        $table_subs = $wpdb->prefix . 'alezux_finanzas_subscriptions';
        $table_plans = $wpdb->prefix . 'alezux_finanzas_plans';
        
        $subscriptions = $wpdb->get_results( $wpdb->prepare(
            "SELECT 
                s.*, 
                p.name as plan_name,
                p.quota_amount as plan_price,
                p.frequency as plan_cycle,
                p.total_quotas as plan_quotas
             FROM $table_subs s
             LEFT JOIN $table_plans p ON s.plan_id = p.id
             WHERE s.user_id = %d
             ORDER BY s.created_at DESC", 
            $user_id
        ) );

        // Procesar suscripciones para el frontend
        foreach ( $subscriptions as $sub ) {
            // Formateo básico de moneda (Asumimos USD por defecto ya que no se guarda en plan)
            $currency_symbol = '$'; 
            // Si en el futuro agregamos currency a planes:
            if ( isset($sub->plan_currency) && $sub->plan_currency === 'EUR' ) {
                $currency_symbol = '€';
            }

            $sub->formatted_price = $currency_symbol . \number_format( (float)$sub->plan_price, 2 );
            $sub->plan_cycle = ($sub->plan_cycle === 'month') ? 'Mensual' : ( ($sub->plan_cycle === 'year') ? 'Anual' : $sub->plan_cycle );
            
            $sub->next_payment_date_formatted = $sub->next_payment_date ? \date_i18n( \get_option( 'date_format' ), \strtotime( $sub->next_payment_date ) ) : '-';
            
            // Lógica para mostrar botón de pago
            // Si tiene cuotas pendientes y no está cancelado/completado
            $sub->is_completed = ($sub->status === 'completed');
            $sub->is_active = ($sub->status === 'active' || $sub->status === 'past_due');
            
            // Calcular progreso
            $total_quotas = (int)$sub->plan_quotas;
            $paid_quotas = (int)$sub->quotas_paid;
            
            // Evitar división por cero
            if ($total_quotas > 0) {
                $sub->progress_percent = \min( 100, \round( ($paid_quotas / $total_quotas) * 100 ) );
            } else {
                $sub->progress_percent = 100; // Si no hay cuotas definidas, asumimos pago único/completo
            }
            
            $sub->progress_text = "{$paid_quotas} / {$total_quotas}";
            
            // Flag para el botón "Pagar cuota manual"
            // Se habilita si está activo, faltan cuotas, y no es una suscripción automática de Stripe que esté al día
            // (Simplificación: si el usuario quiere pagar manual, permitimos generar link de la siguiente cuota)
            $sub->can_pay_manually = ( $sub->is_active && $paid_quotas < $total_quotas );
        }

        // 2. Obtener Historial de Transacciones
        $table_trans = $wpdb->prefix . 'alezux_finanzas_transactions';
        $transactions = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_trans WHERE user_id = %d ORDER BY created_at DESC LIMIT 50",
            $user_id
        ) );

        foreach ( $transactions as $trans ) {
            $currency_symbol = ($trans->currency === 'EUR') ? '€' : '$';
            $trans->formatted_amount = $currency_symbol . \number_format( (float)$trans->amount, 2 );
            $trans->date_formatted = \date_i18n( \get_option( 'date_format' ) . ' ' . \get_option( 'time_format' ), \strtotime( $trans->created_at ) );
            
            // Traducir estado si es necesario
            $status_labels = [
                'succeeded' => 'Exitoso',
                'pending'   => 'Pendiente',
                'failed'    => 'Fallido'
            ];
            $trans->status_label = isset($status_labels[$trans->status]) ? $status_labels[$trans->status] : $trans->status;
        }

        \wp_send_json_success( [
            'subscriptions' => $subscriptions,
            'transactions' => $transactions
        ] );
    }

    /**
     * Genera una sesión de pago para una cuota específica.
     */
    public static function create_installment_checkout() {
        \check_ajax_referer( 'alezux_finanzas_nonce', 'nonce' );

        if ( ! \is_user_logged_in() ) {
            \wp_send_json_error( 'Debes iniciar sesión.' );
        }

        $user_id = \get_current_user_id();
        $subscription_id = isset( $_POST['subscription_id'] ) ? \intval( $_POST['subscription_id'] ) : 0;

        if ( ! $subscription_id ) {
            \wp_send_json_error( 'ID de suscripción inválido.' );
        }

        global $wpdb;
        $table_subs = $wpdb->prefix . 'alezux_finanzas_subscriptions';
        $table_plans = $wpdb->prefix . 'alezux_finanzas_plans';

        // Obtener suscripción y validar propiedad
        $subscription = $wpdb->get_row( $wpdb->prepare(
            "SELECT 
                s.*, 
                p.name as plan_name, 
                p.quota_amount as plan_price,
                p.frequency as plan_cycle,
                p.total_quotas
             FROM $table_subs s
             LEFT JOIN $table_plans p ON s.plan_id = p.id
             WHERE s.id = %d AND s.user_id = %d",
            $subscription_id,
            $user_id
        ) );

        if ( ! $subscription ) {
            \wp_send_json_error( 'Suscripción no encontrada o acceso denegado.' );
        }

        // Validar si es pagable (no completada)
        if ( $subscription->status === 'completed' ) {
            \wp_send_json_error( 'Esta suscripción ya está completada (Estado: Completed).' );
        }
        
        if ( $subscription->quotas_paid >= $subscription->total_quotas ) {
             // Debug info
             $debug_info = " (Pagadas: {$subscription->quotas_paid}, Total: {$subscription->total_quotas})";
            \wp_send_json_error( 'Ya has pagado todas las cuotas de esta suscripción.' . $debug_info );
        }

        // Crear sesión de Stripe
        $stripe = \Alezux_Members\Modules\Finanzas\Includes\Stripe_API::get_instance();
        
        $current_user = \wp_get_current_user();
        $customer_email = $current_user->user_email;

        // URLs
        // Importante: Diferenciar el retorno para no activar 'enroll_user' global
        $success_url = \home_url( '/?alezux_payment_success=true&session_id={CHECKOUT_SESSION_ID}&type=installment' );
        $cancel_url  = \home_url( '/perfil/' ); // O URL anterior

        // Datos del precio (Ad-hoc)
        // Asumimos USD ya que currency no está en la tabla de planes en este momento.
        $currency = 'usd'; 
        
        // Si plan_price es null (plan borrado), intentar usar un fallback o error
        $price_val = $subscription->plan_price ? (float)$subscription->plan_price : 0;
        
        
        if ( $price_val <= 0 ) {

             \wp_send_json_error( 'Error: No se puede determinar el monto de la cuota (Plan eliminado o inválido).' );
        }

        $amount_cents = \round( $price_val * 100 );
        $next_quota = $subscription->quotas_paid + 1;
        
        $line_items = [[
            'price_data' => [
                'currency' => $currency,
                'product_data' => [
                    'name' => "Pago de Cuota #{$next_quota} - " . $subscription->plan_name,
                    'description' => "Pago manual para completar suscripción #{$subscription->id}"
                ],
                'unit_amount' => $amount_cents,
            ],
            'quantity' => 1,
        ]];

        $metadata = [
            'type' => 'installment_payment',
            'subscription_local_id' => $subscription->id,
            'plan_id' => $subscription->plan_id,
            'user_id' => $user_id
        ];

        $session = $stripe->create_checkout_session(
            $line_items,
            $success_url,
            $cancel_url,
            $customer_email,
            'payment', // Modo pago único
            $metadata
        );

        if ( \is_wp_error( $session ) ) {
            \wp_send_json_error( 'Error Stripe: ' . $session->get_error_message() );
        }

        if ( isset( $session->url ) ) {
            \wp_send_json_success( [ 'url' => $session->url ] );
        } else {
            \wp_send_json_error( 'No se pudo generar el link de pago.' );
        }
    }

    /**
     * AJAX: Get Sales Stats for Dashboard (KPIs + Chart)
     * Migrated from Finanzas.php and fixed to use correct table
     */
    public static function get_sales_stats() {
        \check_ajax_referer('alezux_finanzas_nonce', 'nonce');
        
        if (!\is_user_logged_in()) {
            \wp_send_json_error(['message' => 'Unauthorized']);
        }

        global $wpdb;
        // CORRECTED TABLE NAME
        $table_name = $wpdb->prefix . 'alezux_finanzas_transactions'; 

        // Get Filters
        $date_from = isset($_POST['date_from']) ? \sanitize_text_field($_POST['date_from']) : '';
        $date_to   = isset($_POST['date_to']) ? \sanitize_text_field($_POST['date_to']) : '';
        
        // Base Query Condition
        // In transactions table, status is typically 'succeeded' or 'completed' depending on logic.
        // sales-history.js uses 'succeeded' for badges.
        $where_clauses = ["status = 'succeeded'"]; 
        
        if (!empty($date_from) && !empty($date_to)) {
            $where_clauses[] = "created_at BETWEEN '$date_from 00:00:00' AND '$date_to 23:59:59'";
        } elseif (!empty($date_from)) {
             $where_clauses[] = "DATE(created_at) = '$date_from'";
        }

        $where_sql = implode(' AND ', $where_clauses);

        // 1. Calculate Stats
        $stat_type = isset($_POST['stat_type']) ? \sanitize_text_field($_POST['stat_type']) : 'all';
        $response = [];

        if ($stat_type === 'month_revenue' || $stat_type === 'all') {
            // Default to THIS MONTH relative to WP Timezone
            $month_start = \current_time('Y-m-01 00:00:00');
            $month_end   = \current_time('Y-m-t 23:59:59');

            if (empty($date_from)) {
                 $sql_month = "SELECT SUM(amount) FROM $table_name WHERE status = 'succeeded' AND created_at BETWEEN '$month_start' AND '$month_end'";
                 $response['month_revenue'] = $wpdb->get_var($sql_month) ?: 0;
            } else {
                 // If filtered by user, use the filtered period
                 $sql_filtered = "SELECT SUM(amount) FROM $table_name WHERE status = 'succeeded' AND $where_sql";
                 $response['month_revenue'] = $wpdb->get_var($sql_filtered) ?: 0;
            }
        }

        if ($stat_type === 'today_revenue' || $stat_type === 'all') {
             // Default to TODAY relative to WP Timezone
             $today_start = \current_time('Y-m-d 00:00:00');
             $today_end   = \current_time('Y-m-d 23:59:59');
             
             $sql_today = "SELECT SUM(amount) FROM $table_name WHERE status = 'succeeded' AND created_at BETWEEN '$today_start' AND '$today_end'";
             $response['today_revenue'] = $wpdb->get_var($sql_today) ?: 0;
        }

        // 2. Chart Data (Breakdown by Method)
        if ($stat_type === 'chart_data' || $stat_type === 'all') {
            // Check if column is 'payment_method' or 'method'
            // Database_Installer says: method VARCHAR(50)
            $col_method = 'method'; 
            
            $sql_chart = "SELECT $col_method, SUM(amount) as total FROM $table_name WHERE $where_sql GROUP BY $col_method";
            $results = $wpdb->get_results($sql_chart);
            
            $chart_data = [
                'stripe' => 0,
                'manual' => 0,
                'paypal' => 0, 
                'other' => 0
            ];

            foreach($results as $row) {
                $method = \strtolower($row->$col_method);
                if(\strpos($method, 'stripe') !== false) $chart_data['stripe'] += $row->total;
                elseif(\strpos($method, 'manual') !== false) $chart_data['manual'] += $row->total;
                elseif(\strpos($method, 'paypal') !== false) $chart_data['paypal'] += $row->total;
                else $chart_data['other'] += $row->total;
            }
            $response['chart_data'] = $chart_data;
        }

        \wp_send_json_success($response);
    }
}

