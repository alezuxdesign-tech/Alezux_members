<?php
namespace Alezux_Members\Modules\Marketing\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Marketing_Config_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux_marketing_config';
	}

	public function get_title() {
		return __( 'Alezux Marketing Manager', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-envelope';
	}

	public function get_categories() {
		return [ 'alezux-admin' ];
	}

	public function get_script_depends() {
		return [ 'alezux-marketing-admin-js' ];
	}

	public function get_style_depends() {
		return [ 'alezux-marketing-admin-css', 'alezux-tables-css' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Modo Editor (Preview)', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_editor_modal_template',
			[
				'label' => __( 'Mostrar Modal Edición', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Sí', 'alezux-members' ),
				'label_off' => __( 'No', 'alezux-members' ),
				'return_value' => 'yes',
				'default' => '',
				'description' => 'Activa esto para ver y diseñar la ventana modal de edición de plantilla.',
			]
		);

		$this->add_control(
			'show_editor_modal_settings',
			[
				'label' => __( 'Mostrar Modal Ajustes', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Sí', 'alezux-members' ),
				'label_off' => __( 'No', 'alezux-members' ),
				'return_value' => 'yes',
				'default' => '',
				'description' => 'Activa esto para ver y diseñar la ventana modal de configuración.',
			]
		);

		$this->end_controls_section();

		// 1. DISEÑO DE LA TABLA (Tabla y Encabezados)
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
                'selector' => '{{WRAPPER}} .alezux-marketing-wrapper',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'table_border',
                'label' => esc_html__('Borde Tabla', 'alezux-members'),
                'selector' => '{{WRAPPER}} .alezux-marketing-wrapper',
            ]
        );

        $this->add_control(
            'table_radius',
            [
                'label' => esc_html__('Radio de Borde', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .alezux-marketing-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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


        // 2. Celdas (Texto y Tipografía General)
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

        $this->end_controls_section();


        // 3a. CONTADOR ENVIADOS
        $this->start_controls_section(
            'style_section_count_badge',
            [
                'label' => esc_html__('Contador de Envíos', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'count_badge_typography',
                'selector' => '{{WRAPPER}} .alezux-count-badge',
            ]
        );

        $this->add_control(
            'count_badge_color',
            [
                'label' => esc_html__('Color Texto', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-count-badge' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'count_badge_bg_color',
            [
                'label' => esc_html__('Color Fondo', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-count-badge' => 'background-color: {{VALUE}};',
                ],
            ]
        );

         $this->add_control(
            'count_badge_radius',
            [
                'label' => esc_html__('Redondeo', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .alezux-count-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'count_badge_padding',
             [
                'label' => esc_html__('Relleno', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .alezux-count-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();


        // 3. ESTADO (Badges)
        $this->start_controls_section(
            'style_section_badges',
            [
                'label' => esc_html__('Estado (Badges)', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'badge_typography',
                'selector' => '{{WRAPPER}} .status-badge',
            ]
        );

        $this->add_control(
            'badge_radius',
             [
                'label' => esc_html__('Redondeo', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .status-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'badge_padding',
             [
                'label' => esc_html__('Relleno', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .status-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control('badge_active_text', ['label' => 'Texto (Activo)', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-active' => 'color: {{VALUE}};']]);
        $this->add_control('badge_active_bg', ['label' => 'Fondo (Activo)', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-active' => 'background-color: {{VALUE}};']]);

        $this->add_control('badge_inactive_text', ['label' => 'Texto (Inactivo)', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-inactive' => 'color: {{VALUE}};']]);
        $this->add_control('badge_inactive_bg', ['label' => 'Fondo (Inactivo)', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-inactive' => 'background-color: {{VALUE}};']]);

        $this->end_controls_section();

        // 4. MODALES
        $this->start_controls_section(
            'style_section_modals',
            [
                'label' => esc_html__('Ventanas Emergentes (Modales)', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'modal_overlay_bg',
            [
                'label' => esc_html__('Color Fondo Overlay', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-modal' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'modal_content_bg',
            [
                'label' => esc_html__('Color Fondo Contenido', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-modal-content' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'modal_border',
                'selector' => '{{WRAPPER}} .alezux-modal-content',
            ]
        );

        $this->add_control(
            'modal_radius',
            [
                'label' => esc_html__('Redondeo', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .alezux-modal-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'modal_shadow',
                'selector' => '{{WRAPPER}} .alezux-modal-content',
            ]
        );

        $this->add_control(
            'modal_title_color',
            [
                'label' => esc_html__('Color Título', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-modal-content h3' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'modal_title_typography',
                'selector' => '{{WRAPPER}} .alezux-modal-content h3',
            ]
        );

         $this->add_control(
            'modal_label_color',
            [
                'label' => esc_html__('Color Etiquetas (Labels)', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-modal-content label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // 5. BOTONES (General)
		$this->start_controls_section(
			'style_section_buttons_general',
			[
				'label' => esc_html__( 'Botones (General)', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_buttons_general' );

		$this->start_controls_tab(
			'tab_buttons_general_normal',
			[
				'label' => esc_html__( 'Normal', 'alezux-members' ),
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'buttons_typography',
				'selector' => '{{WRAPPER}} .alezux-marketing-btn',
			]
		);

		$this->add_control(
			'buttons_text_color',
			[
				'label'     => esc_html__( 'Color de Texto', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-marketing-btn' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'buttons_background',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .alezux-marketing-btn',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_buttons_general_hover',
			[
				'label' => esc_html__( 'Hover', 'alezux-members' ),
			]
		);

		$this->add_control(
			'buttons_text_color_hover',
			[
				'label'     => esc_html__( 'Color de Texto', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-marketing-btn:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'buttons_background_hover',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .alezux-marketing-btn:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();
		
		$this->add_control(
			'buttons_border_radius',
			[
				'label'      => esc_html__( 'Radio del Borde', 'alezux-members' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .alezux-marketing-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);
        
        $this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'buttons_box_shadow',
				'selector' => '{{WRAPPER}} .alezux-marketing-btn',
			]
		);

        $this->add_responsive_control(
			'buttons_padding',
			[
				'label'      => esc_html__( 'Relleno', 'alezux-members' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .alezux-marketing-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

        // 6. BOTÓN EDITAR
        $this->start_controls_section(
			'style_section_btn_edit',
			[
				'label' => esc_html__( 'Botón Editar', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
        
        $this->add_control(
            'btn_edit_override',
            [
                'label' => esc_html__( 'Personalizar Específicamente', 'alezux-members' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Sí', 'alezux-members' ),
                'label_off' => esc_html__( 'No', 'alezux-members' ),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'btn_edit_background',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .alezux-marketing-btn.edit-template-btn',
                'condition' => [ 'btn_edit_override' => 'yes' ]
			]
		);
        
        $this->add_control(
			'btn_edit_color',
			[
				'label'     => esc_html__( 'Color de Texto', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-marketing-btn.edit-template-btn' => 'color: {{VALUE}}',
				],
                'condition' => [ 'btn_edit_override' => 'yes' ]
			]
		);

        $this->end_controls_section();

        // 7. BOTÓN HISTORIAL
        $this->start_controls_section(
			'style_section_btn_history',
			[
				'label' => esc_html__( 'Botón Historial', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

         $this->add_control(
            'btn_history_override',
            [
                'label' => esc_html__( 'Personalizar Específicamente', 'alezux-members' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Sí', 'alezux-members' ),
                'label_off' => esc_html__( 'No', 'alezux-members' ),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'btn_history_background',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .alezux-marketing-btn.history-btn',
                'condition' => [ 'btn_history_override' => 'yes' ]
			]
		);

         $this->add_control(
			'btn_history_color',
			[
				'label'     => esc_html__( 'Color de Texto', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-marketing-btn.history-btn' => 'color: {{VALUE}}',
				],
                'condition' => [ 'btn_history_override' => 'yes' ]
			]
		);

        $this->end_controls_section();

        // 8. BOTÓN PRUEBA
        $this->start_controls_section(
			'style_section_btn_test',
			[
				'label' => esc_html__( 'Botón Prueba', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

         $this->add_control(
            'btn_test_override',
            [
                'label' => esc_html__( 'Personalizar Específicamente', 'alezux-members' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Sí', 'alezux-members' ),
                'label_off' => esc_html__( 'No', 'alezux-members' ),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'btn_test_background',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .alezux-marketing-btn.send-test-email-btn',
                'condition' => [ 'btn_test_override' => 'yes' ]
			]
		);

         $this->add_control(
			'btn_test_color',
			[
				'label'     => esc_html__( 'Color de Texto', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-marketing-btn.send-test-email-btn' => 'color: {{VALUE}}',
				],
                 'condition' => [ 'btn_test_override' => 'yes' ]
			]
		);

        $this->end_controls_section();
	}

	protected function render() {
		$is_edit = \Elementor\Plugin::instance()->editor->is_edit_mode();
		
		if ( ! current_user_can( 'administrator' ) && ! $is_edit ) {
			echo '<p>Acceso restringido.</p>';
			return;
		}

		$settings = $this->get_settings_for_display();
		$show_edit_modal = $is_edit && 'yes' === $settings['show_editor_modal_template'];
		$show_settings_modal = $is_edit && 'yes' === $settings['show_editor_modal_settings'];
		
		// Data attr to prevent JS overwrite in editor
		// FIX: We WANT JS to overwrite in editor for preview.
		$wrapper_attrs = $is_edit ? ' data-is-editor="yes"' : '';
		?>
		<div class="alezux-finanzas-app alezux-marketing-app"<?php echo $wrapper_attrs; ?>>
			
			<!-- Cabecera Estándar -->
			<div class="alezux-table-header">
				<div class="alezux-header-left">
					<h3 class="alezux-table-title">Gestor de Correos</h3>
					<p class="alezux-table-desc">Administra las plantillas de email del sistema.</p>
				</div>

				<div class="alezux-header-right alezux-filters-inline">
					<div class="alezux-filter-item">
						<button id="btn-marketing-settings" class="alezux-marketing-btn primary">
							<i class="fa fa-cog"></i> Configuración General
						</button>
					</div>
				</div>
			</div>

			<!-- Tabla Container -->
			<div class="alezux-table-wrapper">
				<table class="alezux-finanzas-table marketing-templates-table" id="marketing-templates-table">
					<thead>
						<tr>
							<th style="width: 35%;">Detalles del Correo</th>
							<th style="width: 25%;">Asunto</th>
							<th style="width: 10%;">Enviados</th>
							<th style="width: 10%;">Estado</th>
							<th style="width: 20%;">Acciones</th>
						</tr>
					</thead>
					<tbody>
						<!-- AJAX Loaded (Even in Editor) -->
						<tr><td colspan="5" style="text-align:center; padding: 20px;">Cargando plantillas...</td></tr>
					</tbody>
				</table>
			</div>

			<?php if ( $is_edit ) : ?>
				<div style="margin-top:20px; padding:10px; border:1px dashed #666; color:#aaa; font-size:12px;">
					<strong>Modo Editor:</strong> Los modales no son visibles aquí por defecto. Usa los controles "Modo Editor (Preview)" para mostrarlos y diseñarlos.
				</div>
			<?php endif; ?>

			<!-- MODAL TEMPLATE EDITOR -->
			<div id="marketing-template-modal" class="alezux-modal" style="<?php echo $show_edit_modal ? 'display:flex !important;' : 'display:none;'; ?>">
				<div class="alezux-modal-content" style="max-width: 800px;">
					<span class="alezux-close-modal">&times;</span>
					<h3 id="modal-title">Editar Plantilla (Preview)</h3>
					
					<form id="marketing-template-form" onsubmit="return false;">
						<input type="hidden" id="tpl-type" name="type" value="dummy">
						
						<div class="form-group">
							<label>Asunto:</label>
							<input type="text" id="tpl-subject" name="subject" class="alezux-input" value="Asunto de Ejemplo" required>
							<div id="tpl-variables-hint" style="margin-top: 5px; font-size: 11px; color: #666; background: #f0f0f1; padding: 5px; border-radius: 4px;">
                                <strong>Variables disponibles:</strong> <span id="vars-list">Cargando...</span>
                            </div>
						</div>

						<div class="form-group">
							<label>Contenido:</label>
                            
    						<div class="editor-mode-switcher" style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                            <span id="mode-status-text" style="font-weight:bold; font-size:13px; color:#444;">Editor HTML</span>
                            <label class="switch">
                                <input type="checkbox" id="toggle-preview-mode">
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div id="tab-content-edit">
							    <textarea id="tpl-content" name="content" rows="15" class="alezux-input" style="font-family: monospace;">&lt;h1&gt;Hola Mundo&lt;/h1&gt;</textarea>
							    <small>Pega aquí tu código HTML. La etiqueta &lt;body&gt; es opcional.</small>
                            </div>

                            <div id="tab-content-preview" style="display:none;">
                                <div id="email-preview-frame" class="preview-container">
                                    <!-- Preview rendered here -->
                                </div>
                            </div>
						</div>

						<div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-top: 15px;">
							<label>Activo:</label>
							<label class="switch">
								<input type="checkbox" id="tpl-active" name="is_active" checked>
								<span class="slider round"></span>
							</label>
						</div>

						<div class="form-actions" style="margin-top: 20px; text-align: right;">
							<button type="submit" class="alezux-marketing-btn primary">Guardar Plantilla</button>
						</div>
					</form>
				</div>
			</div>

			<div id="marketing-settings-modal" class="alezux-modal" style="<?php echo $show_settings_modal ? 'display:flex !important;' : 'display:none;'; ?>">
				<div class="alezux-modal-content">
					<span class="alezux-close-modal">&times;</span>
					<h3>Configuración General (Preview)</h3>
					
					<form id="marketing-settings-form" onsubmit="return false;">
						<div class="form-group">
							<label>Nombre del Remitente (From Name):</label>
							<input type="text" id="set-from-name" name="from_name" class="alezux-input" value="Mi Escuela">
						</div>

						<div class="form-group">
							<label>Email del Remitente (From Email):</label>
							<input type="email" id="set-from-email" name="from_email" class="alezux-input" value="info@escuela.com">
						</div>

						<div class="form-group">
						<label>URL del Logo (Variable {{logo_url}}):</label>
                        <!-- UPLOAD BOX -->
                        <div class="alezux-upload-box" id="logo-upload-trigger">
                            <input type="hidden" id="set-logo-url" name="logo_url">
                            <!-- Hidden File Input for Native Upload -->
                            <input type="file" id="logo-file-input" accept="image/*" style="display:none;">
                            
                            <div id="logo-preview-area" style="display:none;">
                                <img src="" class="alezux-preview-image" id="logo-preview-img">
                                <span class="remove-image-link" id="remove-logo">Eliminar imagen</span>
                            </div>

                            <div id="logo-upload-placeholder">
                                <span class="alezux-upload-icon"><i class="fa fa-cloud-upload"></i></span>
                                <span class="alezux-upload-title">Subir logo</span>
                                <span class="alezux-upload-desc">JPEG, PNG, máximo 50 MB.</span>
                                <button type="button" class="alezux-upload-btn-styled">Subir imagen</button>
                            </div>
                        </div>
                        <p style="font-size:12px; color:#888; margin-top:8px; line-height:1.4;">
                            <strong>Nota:</strong> Este logo se usará en el cuerpo del correo. La foto de perfil del remitente que aparece en Gmail/Outlook se gestiona a través de <a href="https://gravatar.com" target="_blank" style="color:#2271b1; text-decoration:underline;">Gravatar</a> o configuraciones de BIMI en tu dominio.
                        </p>
					</div>

					<!-- Configuración SMTP -->
					<div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee;">
						<h4 style="margin-top: 0; margin-bottom: 15px; color: inherit;">Configuración SMTP (Opcional)</h4>
						
						<div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
							<label style="margin-bottom: 0;">Habilitar SMTP:</label>
							<label class="switch">
								<input type="checkbox" id="set-smtp-enabled" name="smtp_enabled">
								<span class="slider round"></span>
							</label>
						</div>

						<div id="smtp-fields-wrapper" style="display: none;">
							<div class="form-group">
								<label>Servidor SMTP (Host):</label>
								<input type="text" id="set-smtp-host" name="smtp_host" class="alezux-input" placeholder="smtp.ejemplo.com">
							</div>

							<div style="display: flex; gap: 15px; margin-bottom: 15px;">
								<div class="form-group" style="flex: 1; margin-bottom: 0;">
									<label>Puerto SMTP:</label>
									<input type="number" id="set-smtp-port" name="smtp_port" class="alezux-input" placeholder="587" value="587">
								</div>

								<div class="form-group" style="flex: 1; margin-bottom: 0;">
									<label>Seguridad de conexión:</label>
									<select id="set-smtp-secure" name="smtp_secure" class="alezux-input" style="height: 38px; padding: 8px;">
										<option value="tls">TLS</option>
										<option value="ssl">SSL</option>
										<option value="none">Ninguno</option>
									</select>
								</div>
							</div>

							<div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
								<label style="margin-bottom: 0;">¿Requiere Autenticación?:</label>
								<label class="switch">
									<input type="checkbox" id="set-smtp-auth" name="smtp_auth" checked>
									<span class="slider round"></span>
								</label>
							</div>

							<div id="smtp-auth-fields">
								<div class="form-group">
									<label>Usuario SMTP (Username):</label>
									<input type="text" id="set-smtp-username" name="smtp_username" class="alezux-input" placeholder="correo@ejemplo.com">
								</div>

								<div class="form-group">
									<label>Contraseña SMTP (Password):</label>
									<div style="position: relative; display: flex; align-items: center;">
										<input type="password" id="set-smtp-password" name="smtp_password" class="alezux-input" style="padding-right: 40px;">
										<span id="toggle-smtp-pass" style="position: absolute; right: 12px; cursor: pointer; color: #888;">
											<i class="fa fa-eye"></i>
										</span>
									</div>
								</div>
							</div>

							<div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-top: 15px;">
								<label style="margin-bottom: 0;">Omitir verificación SSL (Desarrollo/Local):</label>
								<label class="switch">
									<input type="checkbox" id="set-smtp-skip-ssl" name="smtp_skip_ssl">
									<span class="slider round"></span>
								</label>
							</div>
						</div>
					</div>

					<div class="form-actions" style="margin-top: 20px; text-align: right;">
						<button type="submit" class="alezux-marketing-btn primary">Guardar Configuración</button>
					</div>
					</form>
				</div>
			</div>

            <!-- MODAL HISTORY -->
            <div id="marketing-history-modal" class="alezux-modal" style="display:none;">
                <div class="alezux-modal-content" style="max-width: 600px;">
                    <span class="alezux-close-modal">&times;</span>
                    <h3 id="history-modal-title">Historial de Envíos</h3>
                    <div class="alezux-table-wrapper" style="max-height: 400px; overflow-y: auto; margin-top: 15px;">
                        <table class="alezux-finanzas-table" id="history-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Destinatario</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- JS Populated -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

		<!-- GENERIC MESSAGE MODAL -->
		<div id="alezux-message-modal" class="alezux-modal" style="display:none; z-index: 99999;">
			<div class="alezux-modal-content" style="max-width: 400px; text-align: center;">
				<span class="alezux-close-modal">&times;</span>
				<div style="margin-bottom: 15px;">
					<i id="msg-modal-icon" class="fa fa-info-circle" style="font-size: 40px; color: #2271b1;"></i>
				</div>
				<h3 id="msg-modal-title" style="margin-top:0;">Mensaje</h3>
				<p id="msg-modal-content" style="margin: 20px 0; font-size: 15px; color: #555; line-height: 1.5;"></p>
				<button type="button" class="alezux-marketing-btn primary" id="msg-modal-btn">Entendido</button>
			</div>
		</div>

		</div>
		<?php
	}
}
