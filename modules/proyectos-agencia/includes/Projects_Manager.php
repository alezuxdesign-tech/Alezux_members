<?php
namespace Alezux_Members\Modules\Proyectos_Agencia\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Projects_Manager {
    
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'alezux_agency_projects';
    }

    /**
     * Create a new project
     */
    public function create_project( $client_id, $manager_id, $status = 'start', $initial_data = [] ) {
        global $wpdb;
        
        $data = [
            'client_id' => $client_id,
            'manager_id' => $manager_id,
            'status' => $status,
            'current_step' => 'briefing',
            'project_data' => json_encode($initial_data), 
            'created_at' => current_time( 'mysql' )
        ];

        $format = [ '%d', '%d', '%s', '%s', '%s', '%s' ];

        $inserted = $wpdb->insert( $this->table_name, $data, $format );

        if ( $inserted ) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Get all projects (optionally filtered by status or user)
     */
    public function get_projects( $args = [] ) {
        global $wpdb;
        
        $sql = "SELECT * FROM {$this->table_name}";
        $where = [];
        $params = [];

        if ( ! empty( $args['status'] ) ) {
            $where[] = "status = %s";
            $params[] = $args['status'];
        }

        if ( ! empty( $args['client_id'] ) ) {
             $where[] = "client_id = %d";
             $params[] = $args['client_id'];
        }
        
        if ( ! empty( $args['manager_id'] ) ) {
             $where[] = "manager_id = %d";
             $params[] = $args['manager_id'];
        }

        if ( ! empty( $where ) ) {
            $sql .= " WHERE " . implode( ' AND ', $where );
        }
        
        $sql .= " ORDER BY created_at DESC";

        if ( ! empty( $params ) ) {
            $prepared_sql = $wpdb->prepare( $sql, $params );
            return $wpdb->get_results( $prepared_sql );
        }

        return $wpdb->get_results( $sql );
    }

    /**
     * Get a single project by ID
     */
    public function get_project( $id ) {
        global $wpdb;
        $sql = $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id = %d", $id );
        return $wpdb->get_row( $sql );
    }

    /**
     * Update project status
     */
    public function update_status( $id, $new_status ) {
        global $wpdb;
        return $wpdb->update( 
            $this->table_name, 
            [ 'status' => $new_status ], 
            [ 'id' => $id ], 
            [ '%s' ], 
            [ '%d' ] 
        );
    }
    
    /**
     * Update project current step
     */
    public function update_step( $id, $new_step ) {
        global $wpdb;
        return $wpdb->update( 
            $this->table_name, 
            [ 'current_step' => $new_step ], 
            [ 'id' => $id ], 
            [ '%s' ], 
            [ '%d' ] 
        );
    }
    
    /**
     * Update project data (JSON)
     */
    public function update_project_data( $id, $new_data_array ) {
        global $wpdb;
        
        // Get current data first to merge
        $current = $this->get_project($id);
        if (!$current) return false;
        
        $current_data = json_decode($current->project_data, true);
        if (!is_array($current_data)) $current_data = [];
        
        // Merge new data (deep merge via array_replace_recursive if needed, but simple merge for now)
        $updated_data = array_merge($current_data, $new_data_array);
        
        return $wpdb->update( 
            $this->table_name, 
            [ 'project_data' => json_encode($updated_data) ], 
            [ 'id' => $id ], 
            [ '%s' ], 
            [ '%d' ] 
        );
    }
    
    /**
     * Delete project
     */
    public function delete_project( $id ) {
        global $wpdb;
        return $wpdb->delete( 
            $this->table_name, 
            [ 'id' => $id ], 
            [ '%d' ] 
        );
    }
}
