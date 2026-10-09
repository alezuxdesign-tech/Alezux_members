<?php
namespace Alezux_Members\Modules\Proyectos_Agencia\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Ajax_Handler {

	public static function init() {
		$actions = [
			'alezux_agency_get_projects',
			'alezux_agency_create_project',
			'alezux_agency_update_project_status',
            'alezux_agency_search_users',
            'alezux_agency_get_project_details',
            'alezux_agency_update_project_data',
            'alezux_agency_update_project_step',
            'alezux_agency_client_save_briefing',
            'alezux_agency_client_send_feedback',
            'alezux_agency_upload_file',
            'alezux_agency_delete_project'
		];

		foreach ( $actions as $action ) {
			add_action( "wp_ajax_$action", [ __CLASS__, $action ] );
		}
	}

    public static function alezux_agency_delete_project() {
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( 'No autorizado' );
        
        $project_id = intval( $_POST['project_id'] );
        if ( ! $project_id ) wp_send_json_error( 'ID faltante' );
        
        $manager = new Projects_Manager();
        $result = $manager->delete_project( $project_id );
        
        if ( $result !== false ) {
            wp_send_json_success( 'Proyecto eliminado.' );
        } else {
            wp_send_json_error( 'Error al eliminar.' );
        }
    }

    // ... (existing functions)

    public static function alezux_agency_update_project_step() {
        self::check_permissions();
        
        $project_id = intval( $_POST['project_id'] );
        $new_step = sanitize_text_field( $_POST['step'] );
        
        if ( ! $project_id || ! $new_step ) {
            wp_send_json_error( 'Datos incompletos' );
        }
        
        $manager = new Projects_Manager();
        $updated = $manager->update_step( $project_id, $new_step );
        
        if ( $updated !== false ) {
            wp_send_json_success( 'Paso actualizado' );
        } else {
            wp_send_json_error( 'Error al actualizar paso' );
        }
    }
    
    public static function alezux_agency_update_project_data() {
        self::check_permissions();
        
        $project_id = intval( $_POST['project_id'] );
        $data_json = stripslashes( $_POST['project_data'] ); // JSON string from frontend
        $data_array = json_decode( $data_json, true );
        
        if ( ! $project_id || ! is_array( $data_array ) ) {
            wp_send_json_error( 'Datos inválidos' );
        }
        
        $manager = new Projects_Manager();
        $updated = $manager->update_project_data( $project_id, $data_array );
        
        if ( $updated !== false ) {
            wp_send_json_success( 'Datos guardados' );
        } else {
            wp_send_json_error( 'Error al guardar (o sin cambios)' );
        }
    }

    public static function alezux_agency_get_projects() {
        self::check_permissions();

        $manager = new Projects_Manager();
        $projects = $manager->get_projects();
        
        // Format for frontend
        $formatted = [];
        foreach($projects as $p) {
            $client = get_userdata($p->client_id);
            $client_name = $client ? $client->display_name : 'Usuario Eliminado';
            $client_avatar = get_avatar_url($p->client_id);
            
            $data = json_decode($p->project_data, true) ?: [];
            
            $formatted[] = [
                'id' => $p->id,
                'title' => $client_name,
                'client_name' => $client_name,
                'client_avatar' => $client_avatar,
                'status' => $p->status,
                'step' => $p->current_step,
                'start_date' => $data['start_date'] ?? '',
                'end_date' => $data['end_date'] ?? ''
            ];
        }

        wp_send_json_success( $formatted );
    }

    public static function alezux_agency_update_project_status() {
         self::check_permissions();
         
         $project_id = intval( $_POST['project_id'] );
         $new_status = sanitize_text_field( $_POST['status'] );
         
         if ( ! $project_id || ! $new_status ) {
             wp_send_json_error( 'Datos incompletos.' );
         }
         
         $manager = new Projects_Manager();
         $result = $manager->update_status( $project_id, $new_status );
         
         if ( $result !== false ) {
             wp_send_json_success( 'Estado actualizado.' );
         } else {
             wp_send_json_error( 'Error al actualizar DB.' );
         }
    }
    
    public static function alezux_agency_create_project() {
        self::check_permissions();
        
        $client_id = intval( $_POST['client_id'] );
        $title = sanitize_text_field( $_POST['title'] ); // Not stored in DB yet as column, but maybe in JSON
        $start_date = sanitize_text_field( $_POST['start_date'] );
        $end_date = sanitize_text_field( $_POST['end_date'] );

        if ( ! $client_id ) {
            wp_send_json_error( 'Debes seleccionar un cliente.' );
        }
        
        $manager_id = get_current_user_id();
        
        $initial_data = [
            'project_title' => $title,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];

        $manager = new Projects_Manager();
        $new_id = $manager->create_project( $client_id, $manager_id, 'start', $initial_data );
        
        if ( $new_id ) {
            wp_send_json_success( ['id' => $new_id, 'message' => 'Proyecto creado correctamente.'] );
        } else {
            wp_send_json_error( 'Error al crear proyecto.' );
        }
    }

    public static function alezux_agency_search_users() {
        self::check_permissions();
        
        $term = sanitize_text_field( $_GET['term'] ?? '' );
        
        $args = [
            'number' => 20,
            'fields' => ['ID', 'display_name', 'user_email']
        ];

        if ( ! empty( $term ) ) {
            $args['search'] = '*' . $term . '*';
        }
        
        $users = get_users($args);
        
        $results = [];
        foreach($users as $u) {
            $results[] = [
                'id' => $u->ID,
                'text' => $u->display_name . ' (' . $u->user_email . ')'
            ];
        }
        
        wp_send_json_success( $results );
    }

    public static function alezux_agency_get_project_details() {
        self::check_permissions();
        
        $project_id = intval( $_POST['project_id'] );
        if ( ! $project_id ) wp_send_json_error( 'ID faltante' );
        
        $manager = new Projects_Manager();
        $project = $manager->get_project( $project_id );
        
        if ( ! $project ) wp_send_json_error( 'Proyecto no encontrado' );
        
        $client = get_userdata( $project->client_id );
        $project->client_name = $client ? $client->display_name : 'N/A';
        $project->client_email = $client ? $client->user_email : 'N/A';
        $project->data = json_decode( $project->project_data, true );
        
        wp_send_json_success( $project );
    }



    public static function alezux_agency_client_save_briefing() {
        if ( ! is_user_logged_in() ) wp_send_json_error( 'No autorizado' );
        
        $project_id = intval( $_POST['project_id'] );
        if ( ! self::verify_project_ownership( $project_id ) ) {
            wp_send_json_error( 'No tienes permiso para editar este proyecto.' );
        }

        // Get current data to preserve existing logo if not replaced
        $manager = new Projects_Manager();
        $project = $manager->get_project( $project_id );
        $current_data = json_decode( $project->project_data, true ) ?: [];
        $existing_briefing = $current_data['briefing'] ?? [];

        $web_prefs = sanitize_textarea_field( $_POST['web_preferences'] );
        $has_logo = sanitize_text_field( $_POST['has_logo'] );
        
        // Legal Fields
        $legal_fields = [
            'legal_name', 'legal_id', 'legal_address', 'legal_phone', 
            'legal_email', 'privacy_email', 'legal_url', 'brand_name', 
            'business_activity', 'legal_registry', 'dpo_email', 
            'marketing_sectors', 'jurisdiction', 'whatsapp'
        ];
        
        $legal_inputs = [];
        foreach($legal_fields as $field) {
            $legal_inputs[$field] = sanitize_text_field( $_POST[$field] ?? '' );
        }

        // Handle Logo Upload
        $logo_url = $existing_briefing['logo_url'] ?? '';
        
        if ( ! empty( $_FILES['logo_file']['name'] ) ) {
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );
            
            $attachment_id = media_handle_upload( 'logo_file', 0 );
            
            if ( ! is_wp_error( $attachment_id ) ) {
                $logo_url = wp_get_attachment_url( $attachment_id );
            }
        }

        // Construct full briefing data
        $briefing_data = array_merge($existing_briefing, [
            'web_preferences' => $web_prefs,
            'has_logo' => $has_logo,
            'completed_at' => current_time('mysql'),
            'logo_url' => $logo_url
        ], $legal_inputs);

        $update_data = [
            'briefing' => $briefing_data
        ];
        
        if ( $manager->update_project_data( $project_id, $update_data ) !== false ) {
            wp_send_json_success( 'Briefing guardado correctamente.' );
        } else {
             wp_send_json_error( 'Error al guardar.' );
        }
    }
    
    public static function alezux_agency_client_send_feedback() {
         if ( ! is_user_logged_in() ) wp_send_json_error( 'No autorizado' );
         
         $project_id = intval( $_POST['project_id'] );
         $step = sanitize_text_field( $_POST['step'] ); // identity, web_design, etc
         $action = sanitize_text_field( $_POST['action_type'] ); // approve, changes
         $feedback = sanitize_textarea_field( $_POST['feedback'] );
         
         if ( ! self::verify_project_ownership( $project_id ) ) {
            wp_send_json_error( 'No tienes permiso.' );
        }
        
        $manager = new Projects_Manager();
        
        $update_data = [
            $step => [
                 'status' => ($action === 'approve') ? 'approved' : 'changes_requested',
                 'client_feedback' => $feedback,
                 'feedback_date' => current_time('mysql')
            ]
        ];
        
        if ( $manager->update_project_data( $project_id, $update_data ) !== false ) {
            // Ideally notify admin via email
            wp_send_json_success( 'Tu respuesta ha sido enviada.' );
        } else {
             wp_send_json_error( 'Error al procesar.' );
        }
    }

    private static function check_permissions() {
        if ( ! current_user_can( 'edit_posts' ) ) { 
            wp_send_json_error( 'No tienes permisos de administración.' );
            die();
        }
    }
    
    private static function verify_project_ownership( $project_id ) {
        $manager = new Projects_Manager();
        $project = $manager->get_project( $project_id );
        if ( ! $project ) return false;
        
        return ( intval( $project->client_id ) === get_current_user_id() );
    }

    public static function alezux_agency_upload_file() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'No autorizado' );
        }

        // We support both 'file' (single) and 'files' (multiple array)
        $files_to_process = [];

        if ( ! empty( $_FILES['file'] ) ) {
            $files_to_process[] = 'file';
        } elseif ( ! empty( $_FILES['files'] ) ) {
            // Re-structure the $_FILES array for easy handling
            $files = $_FILES['files'];
            foreach ( $files['name'] as $key => $value ) {
                if ( $files['name'][$key] ) {
                    $file = array(
                        'name'     => $files['name'][$key],
                        'type'     => $files['type'][$key],
                        'tmp_name' => $files['tmp_name'][$key],
                        'error'    => $files['error'][$key],
                        'size'     => $files['size'][$key]
                    );

                    $_FILES["file_$key"] = $file;
                    $files_to_process[] = "file_$key";
                }
            }
        }

        if ( empty( $files_to_process ) ) {
            wp_send_json_error( 'No se recibió ningún archivo.' );
        }

        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $results = [];
        $errors = [];

        foreach ( $files_to_process as $file_key ) {
            $attachment_id = media_handle_upload( $file_key, 0 );

            if ( is_wp_error( $attachment_id ) ) {
                $errors[] = $_FILES[$file_key]['name'] . ': ' . $attachment_id->get_error_message();
            } else {
                $results[] = [
                    'url' => wp_get_attachment_url( $attachment_id ),
                    'id'  => $attachment_id
                ];
            }
        }

        if ( empty( $results ) && ! empty( $errors ) ) {
            wp_send_json_error( implode( ', ', $errors ) );
        } else {
            wp_send_json_success( [
                'files' => $results,
                'errors' => $errors
            ] );
        }
    }
}
