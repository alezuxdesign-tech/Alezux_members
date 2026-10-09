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

class Sales_History_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux_sales_history';
	}

	public function get_title() {
		return esc_html__( 'Historial de Ventas (Alezux)', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-price-table';
	}

	public function get_categories() {
		return [ 'alezux-finanzas' ];
	}

    public function get_style_depends() {
		return [ 'alezux-finanzas-tables-css' ];
	}

    public function get_script_depends() {
		return [ 'alezux-sales-history-js' ];
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
			'table_title',
			[
				'label' => esc_html__( 'Título de la Tabla', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Historial de Ventas', 'alezux-members' ),
                'placeholder' => esc_html__( 'Escribe un título', 'alezux-members' ),
			]
		);

        $this->add_control(
			'table_description',
			[
				'label' => esc_html__( 'Descripción', 'alezux-members' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Registro de todas las transacciones.', 'alezux-members' ),
                'placeholder' => esc_html__( 'Escribe una descripción', 'alezux-members' ),
			]
		);

        $this->add_control(
			'limit',
			[
				'label' => esc_html__( 'Límite de Registros', 'alezux-members' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 5,
				'max' => 100,
				'step' => 5,
				'default' => 20,
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
                'selector' => '{{WRAPPER}} .alezux-sales-app, {{WRAPPER}} .alezux-table-wrapper',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'table_border',
                'label' => esc_html__('Borde Tabla', 'alezux-members'),
                'selector' => '{{WRAPPER}} .alezux-sales-app',
            ]
        );

        $this->add_control(
            'table_radius',
            [
                'label' => esc_html__('Radio de Borde', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .alezux-sales-app' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

         $this->add_control(
            'amount_text_color',
            [
                'label' => esc_html__('Color Texto Monto (Negrita)', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-finanzas-table td strong' => 'color: {{VALUE}};',
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
                'selector' => '{{WRAPPER}} .alezux-status-badge',
            ]
        );

        $this->add_control(
            'badge_radius',
             [
                'label' => esc_html__('Redondeo', 'alezux-members'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .alezux-status-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .alezux-status-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        // Colores por estado (sales statuses: succeeded, pending, failed, refunded)
        $this->add_control('heading_badge_succeeded', ['type' => Controls_Manager::HEADING, 'label' => 'Completado (Succeeded)', 'separator' => 'before']);
        $this->add_control('badge_succeeded_text', ['label' => 'Texto', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-succeeded' => 'color: {{VALUE}};']]);
        $this->add_control('badge_succeeded_bg', ['label' => 'Fondo', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-succeeded' => 'background-color: {{VALUE}};']]);

        $this->add_control('heading_badge_pending', ['type' => Controls_Manager::HEADING, 'label' => 'Pendiente (Pending)', 'separator' => 'before']);
        $this->add_control('badge_pending_text', ['label' => 'Texto', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-pending' => 'color: {{VALUE}};']]);
        $this->add_control('badge_pending_bg', ['label' => 'Fondo', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-pending' => 'background-color: {{VALUE}};']]);

        $this->add_control('heading_badge_failed', ['type' => Controls_Manager::HEADING, 'label' => 'Fallido (Failed)', 'separator' => 'before']);
        $this->add_control('badge_failed_text', ['label' => 'Texto', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-failed' => 'color: {{VALUE}};']]);
        $this->add_control('badge_failed_bg', ['label' => 'Fondo', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-failed' => 'background-color: {{VALUE}};']]);

        $this->add_control('heading_badge_refunded', ['type' => Controls_Manager::HEADING, 'label' => 'Reembolsado (Refunded)', 'separator' => 'before']);
        $this->add_control('badge_refunded_text', ['label' => 'Texto', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-refunded' => 'color: {{VALUE}};']]);
        $this->add_control('badge_refunded_bg', ['label' => 'Fondo', 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .status-refunded' => 'background-color: {{VALUE}};']]);

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
            'footer_btn_heading',
            [
                'label' => esc_html__('Botones Paginación', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'pg_btn_color',
            [
                'label' => esc_html__('Color Texto Botón', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .page-btn' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pg_btn_bg_color',
            [
                'label' => esc_html__('Fondo Botón', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .page-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );

         $this->add_control(
            'pg_btn_active_color',
            [
                'label' => esc_html__('Color Texto (Activo)', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .page-btn.active' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .page-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pg_btn_active_bg_color',
            [
                'label' => esc_html__('Fondo (Activo)', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .page-btn.active' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .page-btn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // 5. ESTILOS DE FILTROS & DATEPICKER
        $this->start_controls_section(
            'style_section_filters',
            [
                'label' => esc_html__('Estilo Filtros & Calendario', 'alezux-members'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'filters_inputs_heading',
            [
                'label' => esc_html__('Inputs & Selects (General)', 'alezux-members'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'input_bg_color',
            [
                'label' => esc_html__('Fondo Input', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-table-search-input' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} select' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_text_color',
            [
                'label' => esc_html__('Color Texto', 'alezux-members'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .alezux-table-search-input' => 'color: {{VALUE}};',
                    '{{WRAPPER}} select' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'input_border',
                'label' => esc_html__('Borde Input', 'alezux-members'),
                'selector' => '{{WRAPPER}} .alezux-table-search-input, {{WRAPPER}} select',
            ]
        );

        $this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings_for_display();
        
        // Obtener cursos para el filtro
        global $wpdb;
        $t_plans = $wpdb->prefix . 'alezux_finanzas_plans';
        
        // Safety check if table exists to avoid errors in editor if DB is partial
        if ( $wpdb->get_var("SHOW TABLES LIKE '$t_plans'") === $t_plans ) {
            $courses_ids = $wpdb->get_col("SELECT DISTINCT course_id FROM $t_plans");
        } else {
             $courses_ids = [];
        }

        $courses = [];
        if ( ! empty( $courses_ids ) ) {
            foreach( $courses_ids as $cid ) {
                $c_title = get_the_title( $cid );
                if( $c_title ) {
                    $courses[ $cid ] = $c_title;
                }
            }
        }

        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
        ?>
        <div class="alezux-finanzas-app alezux-sales-app">
            
            <!-- Cabecera Estándar -->
            <div class="alezux-table-header">
                <div class="alezux-header-left">
                    <h3 class="alezux-table-title"><?php echo esc_html($settings['table_title']); ?></h3>
                    <?php if ( ! empty( $settings['table_description'] ) ) : ?>
                        <p class="alezux-table-desc"><?php echo esc_html($settings['table_description']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="alezux-header-right alezux-filters-inline">
                     <!-- Search -->
                    <div class="alezux-filter-item search-item">
                         <div class="alezux-search-wrapper">
                            <span class="dashicons dashicons-search"></span>
                            <input type="text" id="alezux-sales-search" class="alezux-table-search-input" placeholder="Buscar transacción...">
                         </div>
                    </div>

                    <!-- Course Filter -->
                    <div class="alezux-filter-item">
                        <select id="alezux-filter-course">
                            <option value="0">Todos los Cursos</option>
                            <?php foreach($courses as $id => $title): ?>
                                <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>



                    <!-- Status Filter -->
                    <div class="alezux-filter-item">
                        <select id="alezux-filter-status">
                                <option value="">Todos los Estados</option>
                                <option value="succeeded">Completado</option>
                                <option value="pending">Pendiente</option>
                                <option value="failed">Fallido</option>
                                <option value="refunded">Reembolsado</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div class="alezux-loading" <?php if($is_editor) echo 'style="display:none;"'; ?>>
                <i class="eicon-loading eicon-animation-spin"></i> Cargando transacciones...
            </div>

            <!-- Tabla Container -->
            <div class="alezux-table-wrapper">
                <table class="alezux-finanzas-table">
                    <thead>
                        <tr>

                            <th>Alumno</th>
                            <th>Método</th>
                            <th>Monto</th>
                            <th>Curso / Plan</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( $is_editor ) : ?>
                            <!-- Dummy Data for Editor -->
                            <tr>

                                <td>Juan Pérez</td>
                                <td><span style="font-family: 'Font Awesome 5 Brands'; font-weight: 400;">&#xf429;</span> Stripe</td>
                                <td><strong>$50.00</strong></td>
                                <td>Curso React<br><small class="text-muted">1/3 Cuotas</small></td>
                                <td><span class="alezux-status-badge status-succeeded">succeeded</span></td>
                                <td>01 Nov 2025</td>
                            </tr>
                            <tr>

                                <td>Maria Garcia</td>
                                <td>PayPal</td>
                                <td><strong>$120.00</strong></td>
                                <td>Membresía Anual<br><small class="text-muted">Pago Único</small></td>
                                <td><span class="alezux-status-badge status-pending">pending</span></td>
                                <td>02 Nov 2025</td>
                            </tr>
                            <tr>

                                <td>Carlos Diaz</td>
                                <td><span style="font-family: 'Font Awesome 5 Brands'; font-weight: 400;">&#xf429;</span> Stripe</td>
                                <td><strong>$50.00</strong></td>
                                <td>Curso React<br><small class="text-muted">2/3 Cuotas</small></td>
                                <td><span class="alezux-status-badge status-failed">failed</span></td>
                                <td>03 Nov 2025</td>
                            </tr>
                             <tr>

                                <td>Ana Lopez</td>
                                <td>Manual</td>
                                <td><strong>$25.00</strong></td>
                                <td>Taller UI/UX<br><small class="text-muted">Pago Único</small></td>
                                <td><span class="alezux-status-badge status-refunded">refunded</span></td>
                                <td>04 Nov 2025</td>
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
            </div>

        </div>
        <?php
	}
}
