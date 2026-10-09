<?php
namespace Alezux_Members\Modules\Finanzas\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plans_List_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux_plans_manager';
	}

	public function get_title() {
		return esc_html__( 'Gestor de Planes (Alezux)', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-price-table';
	}

	public function get_categories() {
		return [ 'alezux-finanzas' ];
	}

	public function get_script_depends() {
		return [ 'alezux-plans-manager-js' ];
	}

    public function get_style_depends() {
		return [ 'alezux-finanzas-tables-css' ]; 
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Configuración', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);
        
        $this->add_control(
			'view_mode',
			[
				'label' => esc_html__( 'Vista Inicial', 'alezux-members' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'plans',
				'options' => [
					'plans' => esc_html__( 'Gestor de Planes', 'alezux-members' ),
				],
			]
		);

        $this->add_control(
			'table_title',
			[
				'label' => esc_html__( 'Título de la Tabla', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Planes', 'alezux-members' ),
                'placeholder' => esc_html__( 'Escribe un título', 'alezux-members' ),
			]
		);

        $this->add_control(
			'table_description',
			[
				'label' => esc_html__( 'Descripción', 'alezux-members' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Administra los planes de suscripción.', 'alezux-members' ),
                'placeholder' => esc_html__( 'Escribe una descripción', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

        // 1. DISEÑO DE LA TABLA
        $this->start_controls_section(
            'style_section_table',
            [
                'label' => esc_html__('Diseño de la Tabla', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

         $this->add_control(
            'table_container_heading',
            [
                'label' => esc_html__('Contenedor & Cuerpo', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'table_background',
                'label' => esc_html__('Fondo Tabla', 'alezux-members'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .alezux-plans-app, {{WRAPPER}} .alezux-table-wrapper',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'table_border',
                'label' => esc_html__('Borde Tabla', 'alezux-members'),
                'selector' => '{{WRAPPER}} .alezux-plans-app',
            ]
        );

        $this->add_control(
            'table_radius',
            [
                'label' => esc_html__('Radio de Borde', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .alezux-plans-app' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'table_row_bg_general',
            [
                'label' => esc_html__('Fondo Filas (General)', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                 'selectors' => [
                    '{{WRAPPER}} .alezux-finanzas-table tbody tr' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .alezux-finanzas-table tbody td' => 'background-color: {{VALUE}};',
                ],
            ]
        );

         $this->add_control(
            'table_row_bg',
            [
                'label' => esc_html__('Fondo Filas (Alterno)', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                 'selectors' => [
                    '{{WRAPPER}} .alezux-finanzas-table tbody tr:nth-child(even)' => 'background-color: {{VALUE}};',
                ],
            ]
        );

         $this->add_control(
            'table_header_heading',
            [
                'label' => esc_html__('Encabezados (Títulos)', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'header_bg_color',
            [
                'label' => esc_html__('Color Fondo Encabezado', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-finanzas-table th' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'header_text_color',
            [
                'label' => esc_html__('Color Texto Encabezado', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-finanzas-table th' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'header_typography',
                'selector' => '{{WRAPPER}} .alezux-finanzas-table th',
            ]
        );

        $this->end_controls_section();

        // 2. CELDAS
         $this->start_controls_section(
            'style_section_cells',
            [
                'label' => esc_html__('Celdas (TD)', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

         $this->add_control(
            'cell_text_color',
            [
                'label' => esc_html__('Color Texto Celdas', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-finanzas-table td' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'cell_typography',
                'selector' => '{{WRAPPER}} .alezux-finanzas-table td',
            ]
        );

        $this->add_control(
            'plan_name_color',
            [
                'label' => esc_html__('Color Nombre Plan (Negrita)', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-finanzas-table td strong' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();


        // 3. ACCIONES (BOTONES)
        $this->start_controls_section(
            'style_section_actions',
            [
                'label' => esc_html__('Acciones (Botones)', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs( 'tabs_button_style' );

        $this->start_controls_tab(
            'tab_button_normal',
            [
                'label' => esc_html__( 'Normal', 'alezux-members' ),
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .page-btn', // Matches btn-copy-link etc
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => esc_html__( 'Color Texto/Icono', 'alezux-members' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .page-btn' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'button_background',
                'label' => esc_html__( 'Fondo', 'alezux-members' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .page-btn',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .page-btn',
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => esc_html__( 'Radio Borde', 'alezux-members' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .page-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

         $this->add_control(
            'button_padding',
            [
                'label' => esc_html__( 'Relleno', 'alezux-members' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .page-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_button_hover',
            [
                'label' => esc_html__( 'Hover', 'alezux-members' ),
            ]
        );

        $this->add_control(
            'button_hover_color',
            [
                'label' => esc_html__( 'Color Texto/Icono', 'alezux-members' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .page-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'button_background_hover',
                'label' => esc_html__( 'Fondo', 'alezux-members' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .page-btn:hover',
            ]
        );

        $this->add_control(
            'button_hover_border_color',
            [
                'label' => esc_html__( 'Color Borde', 'alezux-members' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .page-btn:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();


        // 4. FOOTER & PAGINACION
        $this->start_controls_section(
            'style_section_footer',
            [
                'label' => esc_html__('Footer & Paginación', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'footer_bg_color',
            [
                'label' => esc_html__('Color Fondo Footer', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-table-footer' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'footer_text_color',
            [
                'label' => esc_html__('Color Texto Footer', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-table-footer' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .alezux-footer-filter label' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .alezux-pagination .p-info' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'pg_btn_color',
            [
                'label' => esc_html__('Color Texto Pag.', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-pagination .page-btn' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pg_btn_bg_color',
            [
                'label' => esc_html__('Fondo Pag.', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-pagination .page-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );
         $this->add_control(
            'pg_btn_active_color',
            [
                'label' => esc_html__('Color Texto (Activo)', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-pagination .page-btn.active' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .alezux-pagination .page-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'pg_btn_active_bg_color',
            [
                'label' => esc_html__('Fondo (Activo)', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-pagination .page-btn.active' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .alezux-pagination .page-btn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );


        $this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
        
        // Obtener cursos para filtro (Select options)
        global $wpdb;
        $t_plans = $wpdb->prefix . 'alezux_finanzas_plans';
        
        // Check table exists logic
        $courses_ids = [];
        if ( $wpdb->get_var("SHOW TABLES LIKE '$t_plans'") === $t_plans ) {
            $courses_ids = $wpdb->get_col("SELECT DISTINCT course_id FROM $t_plans");
        }

        $courses = [];
        if ( ! empty( $courses_ids ) ) {
            foreach( $courses_ids as $cid ) {
                $c_title = get_the_title( $cid );
                if( $c_title ) $courses[ $cid ] = $c_title;
            }
        }

        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
        ?>
        <div class="alezux-finanzas-app alezux-plans-app">
            <!-- Header: Title + Controls -->
            <div class="alezux-table-header">
                <div class="alezux-header-left">
                    <h3 class="alezux-table-title"><?php echo esc_html($settings['table_title']); ?></h3>
                    <?php if ( ! empty( $settings['table_description'] ) ) : ?>
                        <p class="alezux-table-desc"><?php echo esc_html($settings['table_description']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="alezux-header-right">
                    <div class="alezux-filters-inline">
                         <!-- Search -->
                         <div class="alezux-filter-item search-item">
                            <div class="alezux-search-wrapper">
                                <span class="dashicons dashicons-search"></span>
                                <input type="text" id="alezux-plans-search" class="alezux-table-search-input" placeholder="Buscar por nombre...">
                            </div>
                         </div>
                         
                         <!-- Course Filter (Inline) -->
                         <div class="alezux-filter-item">
                             <select id="alezux-plans-course">
                                 <option value="0">Todos los Cursos</option>
                                 <?php foreach($courses as $id => $title): ?>
                                    <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($title); ?></option>
                                 <?php endforeach; ?>
                             </select>
                         </div>
                    </div>
                </div>
            </div>

            <div class="alezux-loading" <?php if($is_editor) echo 'style="display:none;"'; ?>>
                <i class="eicon-loading eicon-animation-spin"></i> Cargando planes...
            </div>
            
            <div class="alezux-table-wrapper">
            <table class="alezux-finanzas-table">
                <thead>
                    <tr>

                        <th>Nombre del Plan</th>
                        <th>Curso Asociado</th>
                        <th>Precio</th>
                        <th>Cuotas</th>
                        <th>Frecuencia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( $is_editor ) : ?>
                        <!-- Dummy Data for Editor -->
                         <tr>

                            <td><strong>Plan Básico</strong></td>
                            <td>Curso Introducción</td>
                            <td>$20.00</td>
                            <td>1</td>
                            <td>Mensual</td>
                            <td>
                                <button class="page-btn btn-copy-link" title="Copiar Link"><i class="eicon-link"></i> Link</button>
                                <button class="page-btn btn-edit-plan" title="Editar Plan"><i class="eicon-pencil"></i></button>
                                <button class="page-btn btn-delete-plan" style="color:#d9534f; border-color:#d9534f;"><i class="eicon-trash"></i></button>
                            </td>
                        </tr>
                        <tr>

                            <td><strong>Plan Completo</strong></td>
                            <td>Curso Avanzado</td>
                            <td>$99.00</td>
                            <td>3</td>
                            <td>Mensual</td>
                            <td>
                                <button class="page-btn btn-copy-link" title="Copiar Link"><i class="eicon-link"></i> Link</button>
                                <button class="page-btn btn-edit-plan" title="Editar Plan"><i class="eicon-pencil"></i></button>
                                <button class="page-btn btn-delete-plan" style="color:#d9534f; border-color:#d9534f;"><i class="eicon-trash"></i></button>
                            </td>
                        </tr>
                         <tr>

                            <td><strong>Membresía VIP</strong></td>
                            <td>Todos los Cursos</td>
                            <td>$150.00</td>
                            <td>1</td>
                            <td>Semestral</td>
                            <td>
                                <button class="page-btn btn-copy-link" title="Copiar Link"><i class="eicon-link"></i> Link</button>
                                <button class="page-btn btn-edit-plan" title="Editar Plan"><i class="eicon-pencil"></i></button>
                                <button class="page-btn btn-delete-plan" style="color:#d9534f; border-color:#d9534f;"><i class="eicon-trash"></i></button>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>

            <!-- Footer: Pagination + Rows Filter -->
            <div class="alezux-table-footer">
                <div class="alezux-pagination">
                     <?php if ( $is_editor ) : ?>
                         <button class="page-btn active">1</button>
                         <button class="page-btn">2</button>
                         <button class="page-btn">3</button>
                    <?php endif; ?>
                </div>
                
                <div class="alezux-footer-filter">
                    <label>Filas:</label>
                    <select id="alezux-limit-select">
                        <option value="10">10</option>
                        <option value="20" selected>20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

        <!-- MODAL DE EDICIÓN (Oculto) -->
        <div id="alezux-edit-plan-modal" class="alezux-modal-overlay" style="display:none;">
            <div class="alezux-modal-content">
                <div class="alezux-modal-header">
                    <h3>Editar Plan</h3>
                    <button class="alezux-close-modal">&times;</button>
                </div>
                <div class="alezux-modal-body">
                    <form id="alezux-edit-plan-form">
                        <input type="hidden" name="plan_id" id="edit-plan-id">
                        
                        <div class="alezux-modal-row">
                             <div class="alezux-form-group">
                                <label>Nombre del Plan</label>
                                <input type="text" name="plan_name" id="edit-plan-name" required>
                            </div>
                        </div>

                        <div class="alezux-modal-row">
                             <div class="alezux-form-group">
                                <label>Enlace de Venta Manual / WhatsApp (Opcional)</label>
                                <input type="url" name="whatsapp_number" id="edit-plan-whatsapp" placeholder="Ej: https://wa.link/abcd">
                            </div>
                        </div>

                         <div class="alezux-modal-row">
                             <div class="alezux-form-group">
                                <label>Curso Asociado (Solo Lectura)</label>
                                <input type="text" id="edit-plan-course" disabled class="alezux-disabled-input">
                            </div>
                        </div>

                        <div class="alezux-modal-row">
                             <div class="alezux-form-group half">
                                <label>Precio (Solo Lectura)</label>
                                <input type="text" id="edit-plan-price" disabled class="alezux-disabled-input">
                            </div>
                            <div class="alezux-form-group half">
                                <label>Cuotas (Solo Lectura)</label>
                                <input type="text" id="edit-plan-quotas" disabled class="alezux-disabled-input">
                            </div>
                        </div>

                         <div class="alezux-form-group">
                            <label>Reglas de Acceso (Modificable)</label>
                            <div id="edit-plan-rules-container" class="alezux-rules-container">
                                <!-- Cargado via JS -->
                                <div class="alezux-spinner">Cargando reglas...</div>
                            </div>
                        </div>

                        <div class="alezux-modal-actions">
                            <button type="button" class="alezux-btn alezux-btn-cancel alezux-close-modal">Cancelar</button>
                            <button type="submit" class="alezux-btn alezux-btn-save">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <style>
            /* Modal Styles */
            .alezux-modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
                z-index: 9999;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .alezux-modal-content {
                background: #1a1a1a;
                border: 1px solid #333;
                border-radius: 12px;
                width: 90%;
                max-width: 600px;
                padding: 0;
                box-shadow: 0 10px 30px rgba(0,0,0,0.5);
                display: flex;
                flex-direction: column;
                max-height: 90vh;
            }
            .alezux-modal-header {
                padding: 20px;
                border-bottom: 1px solid #333;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .alezux-modal-header h3 { margin: 0; color: #fff; }
            .alezux-close-modal {
                background: none;
                border: none;
                color: #aaa;
                font-size: 24px;
                cursor: pointer;
            }
            .alezux-modal-body {
                padding: 20px;
                overflow-y: auto;
            }
            .alezux-form-group { margin-bottom: 15px; }
            .alezux-form-group label { display: block; margin-bottom: 5px; color: #ccc; font-size: 0.9em; }
            .alezux-form-group input {
                width: 100%;
                padding: 10px;
                background: #252525;
                border: 1px solid #444;
                color: #fff;
                border-radius: 6px;
            }
            .alezux-disabled-input { background: #151515 !important; color: #777 !important; border-color: #222 !important; }
            .alezux-modal-row { display: flex; gap: 15px; }
            .alezux-form-group.half { flex: 1; }
            
            .alezux-modal-actions {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                margin-top: 20px;
            }
            .alezux-btn { padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; font-weight: bold; }
            .alezux-btn-cancel { background: #333; color: #fff; }
            .alezux-btn-save { background: #6c5ce7; color: #fff; }
            .alezux-btn-save:hover { background: #5a4ad1; }
            
            .alezux-rules-table { width: 100%; border-collapse: collapse; font-size: 0.9em; }
            .alezux-rules-table th, .alezux-rules-table td { padding: 8px; border-bottom: 1px solid #333; text-align: left; color:#ddd; }
            .alezux-quota-select { background: #222; color: #fff; border: 1px solid #444; padding: 5px; border-radius: 4px; }
        </style>

            </div>

        </div>
        <?php
	}
}
