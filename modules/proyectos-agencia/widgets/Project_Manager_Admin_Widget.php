<?php
namespace Alezux_Members\Modules\Proyectos_Agencia\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Project_Manager_Admin_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux_project_manager_admin';
	}

	public function get_title() {
		return 'Gestor de Proyectos (Admin)';
	}

	public function get_icon() {
		return 'eicon-kanban';
	}

	public function get_categories() {
		return [ 'alezux_members' ];
	}
    
    public function get_style_depends() {
		return [ 'alezux-kanban-css' ];
	}

	public function get_script_depends() {
		return [ 'alezux-kanban-js' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => 'Configuración',
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'admin_roles',
			[
				'label' => 'Roles Permitidos',
				'type' => Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => [
					'administrator' => 'Administrador',
					'editor' => 'Editor',
                    'author' => 'Autor' 
				],
				'default' => [ 'administrator' ],
                'description' => 'Selecciona qué roles pueden ver y gestionar este tablero.'
			]
		);

        $this->add_control(
			'board_title',
			[
				'label' => 'Título del Tablero',
				'type' => Controls_Manager::TEXT,
				'default' => 'Gestor de Proyectos',
			]
		);

        $this->add_control(
			'button_text',
			[
				'label' => 'Texto del Botón',
				'type' => Controls_Manager::TEXT,
				'default' => 'Nuevo Proyecto',
			]
		);

		$this->end_controls_section();

        // Section: Column Titles
        $this->start_controls_section(
			'columns_section',
			[
				'label' => 'Títulos de Columnas',
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

        $this->add_control(
			'col_start_title',
			[
				'label' => 'Por Comenzar',
				'type' => Controls_Manager::TEXT,
				'default' => 'Por Comenzar',
			]
		);

        $this->add_control(
			'col_process_title',
			[
				'label' => 'En Proceso',
				'type' => Controls_Manager::TEXT,
				'default' => 'En Proceso',
			]
		);

        $this->add_control(
			'col_review_title',
			[
				'label' => 'En Revisión',
				'type' => Controls_Manager::TEXT,
				'default' => 'En Revisión',
			]
		);

        $this->add_control(
			'col_approved_title',
			[
				'label' => 'Aprobado',
				'type' => Controls_Manager::TEXT,
				'default' => 'Aprobado',
			]
		);

        $this->add_control(
			'col_delivered_title',
			[
				'label' => 'Entregado',
				'type' => Controls_Manager::TEXT,
				'default' => 'Entregado',
			]
		);

        $this->end_controls_section();

        // Section: Board Header
        $this->start_controls_section(
			'board_header_style',
			[
				'label' => 'Estilo del Encabezado',
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => 'Tipografía Título',
				'selector' => '{{WRAPPER}} .kanban-header h2',
			]
		);

        $this->add_control(
			'title_color',
			[
				'label' => 'Color del Título',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .kanban-header h2' => 'color: {{VALUE}};',
				],
			]
		);

        $this->end_controls_section();

        // Section: Kanban Board Style
        $this->start_controls_section(
			'board_style_section',
			[
				'label' => 'Estilo del Tablero',
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
			'board_bg',
			[
				'label' => 'Fondo del Tablero',
				'type' => Controls_Manager::COLOR,
				'default' => '#f4f6f9',
				'selectors' => [
					'{{WRAPPER}} .alezux-kanban-board' => 'background: {{VALUE}} !important;',
				],
			]
		);

        $this->add_responsive_control(
			'board_padding',
			[
				'label' => 'Padding',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-kanban-board' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->add_control(
			'board_radius',
			[
				'label' => 'Border Radius',
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 50 ],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-kanban-board' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

        $this->end_controls_section();

        // Section: Buttons Style
        $this->start_controls_section(
			'buttons_style_section',
			[
				'label' => 'Estilo del Botón',
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->start_controls_tabs( 'btn_tabs' );

        $this->start_controls_tab(
            'btn_normal',
            [
                'label' => 'Normal',
            ]
        );

        $this->add_control(
			'btn_bg',
			[
				'label' => 'Fondo',
				'type' => Controls_Manager::COLOR,
				'default' => '#007bff',
                'selectors' => [
                    '{{WRAPPER}} #add-project-btn' => 'background: {{VALUE}};',
                ],
			]
		);

        $this->add_control(
			'btn_text_color',
			[
				'label' => 'Color de Texto',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #add-project-btn' => 'color: {{VALUE}};',
				],
			]
		);

        $this->end_controls_tab();

        $this->start_controls_tab(
            'btn_hover',
            [
                'label' => 'Hover',
            ]
        );

        $this->add_control(
			'btn_bg_hover',
			[
				'label' => 'Fondo (Hover)',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #add-project-btn:hover' => 'background: {{VALUE}};',
				],
			]
		);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'btn_typography',
				'label' => 'Tipografía Botón',
				'selector' => '{{WRAPPER}} #add-project-btn',
			]
		);

        $this->add_responsive_control(
			'btn_padding',
			[
				'label' => 'Padding',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} #add-project-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->end_controls_section();

        // Section: Column Styles
        $this->start_controls_section(
			'columns_style_section',
			[
				'label' => 'Estilo de Columnas',
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'col_typography',
				'label' => 'Tipografía Columnas',
				'selector' => '{{WRAPPER}} .kanban-column-header',
			]
		);

        $this->add_control(
			'col_container_bg',
			[
				'label' => 'Fondo de la Columna de Estado',
				'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .kanban-column' => 'background-color: {{VALUE}};',
				],
			]
		);

        $this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'col_container_border',
				'label' => 'Borde',
				'selector' => '{{WRAPPER}} .kanban-column',
			]
		);

        $this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'col_container_shadow',
				'label' => 'Sombra de Caja',
				'selector' => '{{WRAPPER}} .kanban-column',
			]
		);

        $this->add_responsive_control(
			'col_container_radius',
			[
				'label' => 'Radio de Borde (Columna)',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .kanban-column' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->add_responsive_control(
			'col_container_padding',
			[
				'label' => 'Padding de Columna',
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .kanban-column' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->add_control(
			'card_style_heading',
			[
				'label' => 'Tarjetas de Proyecto Internas',
				'type' => Controls_Manager::HEADING,
                'separator' => 'before',
			]
		);

        $this->add_control(
			'col_card_bg',
			[
				'label' => 'Fondo de Tarjetas',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .kanban-card' => 'background: {{VALUE}};',
				],
			]
		);

        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'card_title_typography',
				'label' => 'Tipografía Tarjeta',
				'selector' => '{{WRAPPER}} .kanban-card-title',
			]
		);

        $this->add_control(
			'col_start_color',
			[
				'label' => 'Color: Por Comenzar',
				'type' => Controls_Manager::COLOR,
				'default' => '#6c757d',
                'selectors' => [
                    '{{WRAPPER}} .kanban-column-header.start' => 'border-bottom-color: {{VALUE}}; color: {{VALUE}};',
                    '{{WRAPPER}} .kanban-column[data-status="start"] .kanban-card' => 'border-left-color: {{VALUE}};',
                ],
			]
		);

        $this->add_control(
			'col_process_color',
			[
				'label' => 'Color: En Proceso',
				'type' => Controls_Manager::COLOR,
				'default' => '#007bff',
                'selectors' => [
                    '{{WRAPPER}} .kanban-column-header.process' => 'border-bottom-color: {{VALUE}}; color: {{VALUE}};',
                    '{{WRAPPER}} .kanban-column[data-status="process"] .kanban-card' => 'border-left-color: {{VALUE}};',
                ],
			]
		);

        $this->add_control(
			'col_review_color',
			[
				'label' => 'Color: En Revisión',
				'type' => Controls_Manager::COLOR,
				'default' => '#fd7e14',
                'selectors' => [
                    '{{WRAPPER}} .kanban-column-header.review' => 'border-bottom-color: {{VALUE}}; color: {{VALUE}};',
                    '{{WRAPPER}} .kanban-column[data-status="review"] .kanban-card' => 'border-left-color: {{VALUE}};',
                ],
			]
		);

        $this->add_control(
			'col_approved_color',
			[
				'label' => 'Color: Aprobado',
				'type' => Controls_Manager::COLOR,
				'default' => '#28a745',
                'selectors' => [
                    '{{WRAPPER}} .kanban-column-header.approved' => 'border-bottom-color: {{VALUE}}; color: {{VALUE}};',
                    '{{WRAPPER}} .kanban-column[data-status="approved"] .kanban-card' => 'border-left-color: {{VALUE}};',
                ],
			]
		);

        $this->add_control(
			'col_delivered_color',
			[
				'label' => 'Color: Entregado',
				'type' => Controls_Manager::COLOR,
				'default' => '#6610f2',
                'selectors' => [
                    '{{WRAPPER}} .kanban-column-header.delivered' => 'border-bottom-color: {{VALUE}}; color: {{VALUE}};',
                    '{{WRAPPER}} .kanban-column[data-status="delivered"] .kanban-card' => 'border-left-color: {{VALUE}};',
                ],
			]
		);

		$this->end_controls_section();

        // Section: Modal Style
        $this->start_controls_section(
			'modal_style_section',
			[
				'label' => 'Estilo de Modales',
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
			'modal_bg',
			[
				'label' => 'Fondo del Modal',
				'type' => Controls_Manager::COLOR,
				'default' => '#fefefe',
				'selectors' => [
					'{{WRAPPER}} .alezux-modal-content' => 'background-color: {{VALUE}};',
				],
			]
		);

        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'modal_title_typography',
				'label' => 'Tipografía Títulos Modal',
				'selector' => '{{WRAPPER}} .alezux-modal-content h3',
			]
		);

        $this->add_control(
			'modal_overlay_color',
			[
				'label' => 'Color Overlay',
				'type' => Controls_Manager::COLOR,
				'default' => 'rgba(0,0,0,0.5)',
				'selectors' => [
					'{{WRAPPER}} .alezux-modal' => 'background-color: {{VALUE}};',
				],
			]
		);

        $this->end_controls_section();

        // Section: Step Content & Stepper Style
        $this->start_controls_section(
			'step_style_section',
			[
				'label' => 'Estilo Contenido de Pasos',
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
			'step_content_bg',
			[
				'label' => 'Fondo del Contenedor de Pasos',
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .step-content' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'step_title_typography',
				'label' => 'Tipografía Títulos (h4)',
				'selector' => '{{WRAPPER}} .step-content h4',
			]
		);

        $this->add_control(
			'step_title_color',
			[
				'label' => 'Color Títulos (h4)',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .step-content h4' => 'color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'stepper_bg',
			[
				'label' => 'Fondo del Stepper (Barra)',
				'type' => Controls_Manager::COLOR,
				'default' => '#f8f9fa',
				'selectors' => [
					'{{WRAPPER}} .alezux-stepper' => 'background-color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

        $this->start_controls_tabs( 'stepper_tabs_style' );

        $this->start_controls_tab(
            'step_item_normal',
            [
                'label' => 'Normal',
            ]
        );

        $this->add_control(
			'step_item_bg',
			[
				'label' => 'Fondo Pestaña',
				'type' => Controls_Manager::COLOR,
				'default' => '#e9ecef',
				'selectors' => [
					'{{WRAPPER}} .step-item' => 'background-color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'step_item_color',
			[
				'label' => 'Color Texto',
				'type' => Controls_Manager::COLOR,
				'default' => '#6c757d',
				'selectors' => [
					'{{WRAPPER}} .step-item' => 'color: {{VALUE}};',
				],
			]
		);

        $this->end_controls_tab();

        $this->start_controls_tab(
            'step_item_active',
            [
                'label' => 'Activa',
            ]
        );

        $this->add_control(
			'step_item_active_bg',
			[
				'label' => 'Fondo Activa',
				'type' => Controls_Manager::COLOR,
				'default' => '#007bff',
				'selectors' => [
					'{{WRAPPER}} .step-item.active' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

        $this->add_control(
			'step_item_active_color',
			[
				'label' => 'Color Texto Activa',
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .step-item.active' => 'color: {{VALUE}} !important;',
				],
			]
		);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
			'dates_header_bg',
			[
				'label' => 'Fondo Franja de Fechas',
				'type' => Controls_Manager::COLOR,
				'default' => '#fdfdfd',
				'selectors' => [
					'{{WRAPPER}} .modal-project-dates' => 'background-color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

        $this->end_controls_section();

	}

	protected function render() {
        // Verificar permisos
        $settings = $this->get_settings_for_display();
        $allowed_roles = $settings['admin_roles'];
        $current_user = wp_get_current_user();
        $has_permission = false;

        foreach ( $allowed_roles as $role ) {
            if ( in_array( $role, (array) $current_user->roles ) ) {
                $has_permission = true;
                break;
            }
        }

        if ( ! $has_permission && ! current_user_can( 'administrator' ) ) {
            echo '<div class="alezux-alert-error">No tienes permiso para ver este contenido.</div>';
            return;
        }

        // Renderizar el Kanban Board Container
		?>
		<div class="alezux-kanban-board" id="alezux-kanban-app">
            <div class="kanban-header">
                <h2><?php echo esc_html($settings['board_title']); ?></h2>
                 <button id="add-project-btn" class="alezux-btn alezux-btn-primary">
                    <i class="fas fa-plus"></i> <?php echo esc_html($settings['button_text']); ?>
                </button>
            </div>
            
            <div class="kanban-container">
                <!-- Column: Por Comenzar -->
                <div class="kanban-column" data-status="start">
                    <div class="kanban-column-header start"><?php echo esc_html($settings['col_start_title']); ?></div>
                    <div class="kanban-column-body" id="col-start">
                        <!-- Items inject via JS -->
                        <div class="kanban-loading">Cargando...</div>
                    </div>
                </div>

                <!-- Column: En Proceso -->
                <div class="kanban-column" data-status="process">
                    <div class="kanban-column-header process"><?php echo esc_html($settings['col_process_title']); ?></div>
                    <div class="kanban-column-body" id="col-process"></div>
                </div>

                 <!-- Column: En Revisión -->
                <div class="kanban-column" data-status="review">
                    <div class="kanban-column-header review"><?php echo esc_html($settings['col_review_title']); ?></div>
                    <div class="kanban-column-body" id="col-review"></div>
                </div>

                <!-- Column: Aprobado -->
                <div class="kanban-column" data-status="approved">
                    <div class="kanban-column-header approved"><?php echo esc_html($settings['col_approved_title']); ?></div>
                    <div class="kanban-column-body" id="col-approved"></div>
                </div>

                <!-- Column: Entregado -->
                <div class="kanban-column" data-status="delivered">
                    <div class="kanban-column-header delivered"><?php echo esc_html($settings['col_delivered_title']); ?></div>
                    <div class="kanban-column-body" id="col-delivered"></div>
                </div>
            </div>
		</div>

        <!-- Project Details Modal (Hidden by default) -->
        <div id="project-modal" class="alezux-modal" style="display:none;">
            <div class="alezux-modal-content">
                <span class="close-modal close-details">&times;</span>
                <h3 id="modal-project-title">Detalles del Proyecto</h3>
                <!-- Dynamic Content Here -->
                <div id="modal-body-content"></div>
            </div>
        </div>

        <!-- New Project Modal (Hidden by default) -->
        <div id="new-project-modal" class="alezux-modal" style="display:none;">
            <div class="alezux-modal-content">
                <span class="close-modal close-new">&times;</span>
                <h3>Crear Nuevo Proyecto</h3>
                <form id="new-project-form">
                    <div class="alezux-form-group">
                        <label>Nombre del Proyecto</label>
                        <input type="text" id="new-project-name" class="alezux-input" required placeholder="Ej: Rediseño Web X">
                    </div>
                    
                    <div class="alezux-form-group">
                        <label>Cliente (Usuario WP)</label>
                        <select id="new-project-client" class="alezux-input" style="width:100%;" required>
                            <option value="">Buscar usuario...</option>
                        </select>
                    </div>

                    <div class="alezux-form-group-row" style="display:flex; gap:15px;">
                        <div class="alezux-form-group" style="flex:1;">
                            <label>Fecha Inicio</label>
                            <input type="text" id="new-project-start" class="alezux-input datepicker" required>
                        </div>
                        <div class="alezux-form-group" style="flex:1;">
                            <label>Fecha Fin</label>
                            <input type="text" id="new-project-end" class="alezux-input datepicker" required>
                        </div>
                    </div>

                    <div class="modal-footer" style="margin-top:20px;">
                        <button type="submit" class="alezux-btn alezux-btn-primary">Crear Proyecto</button>
                    </div>
                </form>
            </div>
        </div>
		<?php
	}
}
