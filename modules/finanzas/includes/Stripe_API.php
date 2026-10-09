<?php
namespace Alezux_Members\Modules\Finanzas\Includes;

if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase para manejar la integración con Stripe.
 * Utiliza cURL directo para no depender de librerías externas pesadas si es algo simple,
 * o el SDK oficial si decidiéramos incluirlo. Por ahora haremos una implementación ligera.
 */
class Stripe_API {

    private $secret_key;
    private $api_url = 'https://api.stripe.com/v1/';

    private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    public function __construct() {
        // Obtener keys de las opciones (o harcoded temporalmente para pruebas)
        $this->secret_key = \get_option( 'alezux_stripe_secret_key', '' );
    }

    /**
     * Crea un producto y un precio en Stripe.
     * Retorna array con IDs o false si falla.
     */
    public function create_plan( $name, $amount_usd, $interval = 'month' ) {
        if ( empty( $this->secret_key ) ) {
            return new \WP_Error( 'stripe_error', 'Falta la Secret Key de Stripe.' );
        }

        // 1. Crear Producto
        $product_data = [
            'name' => $name,
            'type' => 'service',
        ];

        $product_response = $this->request( 'products', $product_data );

        if ( is_wp_error( $product_response ) ) {
            return $product_response;
        }

        $product_id = $product_response->id;

        // 2. Crear Precio Recurrente
        $price_data = [
            'unit_amount' => $amount_usd * 100, // Stripe usa centavos
            'currency' => 'usd',
            'product' => $product_id,
        ];

        if ( $interval === 'contado' ) {
             // Pago único (sin recurring)
             // No hacemos nada extra, es un precio one-time
        } else {
            // Recurrente
            $price_data['recurring'] = [
                'interval' => $interval // 'month', 'week'
            ];
        }

        $price_response = $this->request( 'prices', $price_data );

        if ( is_wp_error( $price_response ) ) {
            return $price_response;
        }

        return [
            'product_id' => $product_id,
            'price_id' => $price_response->id,
        ];
    }

    public function create_checkout_session( $line_items_or_price_id, $success_url, $cancel_url, $customer_email = null, $mode = 'subscription', $metadata = [] ) {
        if ( empty( $this->secret_key ) ) {
            return new \WP_Error( 'stripe_error', 'Falta la Secret Key de Stripe.' );
        }

        $line_items = [];
        if ( is_array( $line_items_or_price_id ) ) {
            // Pasamos array completo de line_items (para precios ad-hoc o múltiples)
            $line_items = $line_items_or_price_id;
        } else {
            // Comportamiento Legacy: string ID
            $line_items = [
                [
                    'price' => $line_items_or_price_id,
                    'quantity' => 1,
                ],
            ];
        }

        $payload = [
            'success_url' => $success_url,
            'cancel_url' => $cancel_url,
            'line_items' => $line_items,
            'mode' => $mode,
        ];

        if ( ! empty( $metadata ) ) {
            $payload['metadata'] = $metadata;
        }

        if ( ! empty( $customer_email ) ) {
            $payload['customer_email'] = $customer_email;
        }

        return $this->request( 'checkout/sessions', $payload );
    }

    /**
     * Obtiene una sesión de checkout por ID.
     */
    public function get_checkout_session( $session_id ) {
        if ( empty( $this->secret_key ) ) {
            return new \WP_Error( 'stripe_error', 'Falta la Secret Key de Stripe.' );
        }

        return $this->request( 'checkout/sessions/' . $session_id, [], 'GET' );
    }

    /**
     * Realiza una petición a la API de Stripe.
     */
    private function request( $endpoint, $data = [], $method = 'POST' ) {
        $url = $this->api_url . $endpoint;

        $args = [
            'method' => $method,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => $data,
            'timeout' => 45,
        ];

        $response = \wp_remote_request( $url, $args );

        if ( \is_wp_error( $response ) ) {
            return $response;
        }

        $body = \wp_remote_retrieve_body( $response );
        $code = \wp_remote_retrieve_response_code( $response );
        $json = \json_decode( $body );

        if ( $code >= 400 ) {
            $msg = isset( $json->error->message ) ? $json->error->message : 'Error desconocido de Stripe';
            // Agregar más info para debug si es posible
            if ( isset( $json->error->param ) ) {
                $msg .= ' (Campo: ' . $json->error->param . ')';
            }
            return new \WP_Error( 'stripe_api_error', $msg );
        }

        return $json;
    }
}
