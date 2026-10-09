<?php
namespace Alezux_Members\Modules\Finanzas\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget de Elementor: Rendimiento Financiero (Ingresos)
 */
class Financial_Performance_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux_financial_performance';
	}

	public function get_title() {
		return __( 'Alezux Ingresos', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-price-table';
	}

	public function get_categories() {
		return [ 'alezux-lms' ];
	}

	protected function register_controls() {
		// --- SECCIÓN: CONTENIDO ---
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Contenido', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title_text',
			[
				'label' => __( 'Título', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Ingresos', 'alezux-members' ),
                'placeholder' => __( 'Ingresos', 'alezux-members' ),
			]
		);

        // Labels for Tabs
        $this->add_control(
			'tab_daily_label',
			[
				'label' => __( 'Etiqueta Semanal (7 Días)', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Semanal', 'alezux-members' ),
			]
		);
        $this->add_control(
			'tab_weekly_label',
			[
				'label' => __( 'Etiqueta Mensual (30 Días)', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Mensual', 'alezux-members' ),
			]
		);
        $this->add_control(
			'tab_monthly_label',
			[
				'label' => __( 'Etiqueta Anual (12 Meses)', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Anual', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

		// --- SECCIÓN: ESTILO - GENERAL ---
		$this->start_controls_section(
			'section_style_general',
			[
				'label' => __( 'General', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
        
        $this->add_control(
			'container_bg_color',
			[
				'label' => __( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .alezux-rendimiento-wrapper' => 'background-color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'container_padding',
			[
				'label' => __( 'Relleno (Padding)', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-rendimiento-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
                'default' => [
                    'top' => '20',
                    'right' => '20',
                    'bottom' => '25',
                    'left' => '20',
                    'isLinked' => true,
                ],
			]
		);

        $this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'container_border',
				'label' => __( 'Borde', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-rendimiento-wrapper',
			]
		);

        $this->add_control(
			'container_border_radius',
			[
				'label' => __( 'Radio del Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-rendimiento-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
                'default' => [
                    'top' => '15',
                    'right' => '15',
                    'bottom' => '15',
                    'left' => '15',
                    'isLinked' => true,
                ],
			]
		);

        $this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'container_box_shadow',
				'label' => __( 'Sombra', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-rendimiento-wrapper',
			]
		);

        $this->end_controls_section();

        // --- SECCIÓN: ESTILO - TÍTULO ---
        $this->start_controls_section(
			'section_style_title',
			[
				'label' => __( 'Título', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
			'title_color',
			[
				'label' => __( 'Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#000000',
				'selectors' => [
					'{{WRAPPER}} .alezux-rendimiento-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .alezux-rendimiento-header svg' => 'fill: {{VALUE}}; stroke: {{VALUE}};',
				],
			]
		);

        $this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => __( 'Tipografía', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-rendimiento-title',
			]
		);

        $this->end_controls_section();

        // --- SECCIÓN: ESTILO - TABS ---
        $this->start_controls_section(
			'section_style_tabs',
			[
				'label' => __( 'Pestañas (Tabs)', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
			'tabs_bg_color',
			[
				'label' => __( 'Fondo Contenedor Tabs', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#f5f5f5',
				'selectors' => [
					'{{WRAPPER}} .alezux-rendimiento-tabs' => 'background-color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'tab_item_color',
			[
				'label' => __( 'Color Texto Inactivo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#666666',
				'selectors' => [
					'{{WRAPPER}} .alezux-tab-item' => 'color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'tab_item_active_color',
			[
				'label' => __( 'Color Texto Activo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#000000',
				'selectors' => [
					'{{WRAPPER}} .alezux-tab-item.active' => 'color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'tab_item_active_bg',
			[
				'label' => __( 'Fondo Activo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .alezux-tab-item.active' => 'background-color: {{VALUE}}; box-shadow: 0 2px 5px rgba(0,0,0,0.05);',
				],
			]
		);

        $this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'tabs_typography',
				'label' => __( 'Tipografía', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-tab-item',
			]
		);

        $this->end_controls_section();

        // --- SECCIÓN: ESTILO - GRÁFICO ---
        $this->start_controls_section(
			'section_style_chart',
			[
				'label' => __( 'Gráfico', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
			'chart_bar_color',
			[
				'label' => __( 'Color Barras', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#e0e0ff',
				'selectors' => [
					'{{WRAPPER}} .alezux-bar-fill' => 'background: linear-gradient(180deg, {{VALUE}} 0%, rgba(255,255,255,0) 100%);',
				],
			]
		);

        $this->add_control(
			'chart_bar_active_color',
			[
				'label' => __( 'Color Barra Activa / Hoy', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#5655d6',
				'selectors' => [
					'{{WRAPPER}} .alezux-bar-item.is-today .alezux-bar-fill, {{WRAPPER}} .alezux-bar-item:hover .alezux-bar-fill' => 'background: linear-gradient(180deg, {{VALUE}} 0%, rgba(255,255,255,0.2) 100%);',
				],
			]
		);

        $this->add_control(
			'chart_axis_color',
			[
				'label' => __( 'Color Ejes/Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#bfbfbf',
				'selectors' => [
					'{{WRAPPER}} .alezux-axis-y span, {{WRAPPER}} .alezux-axis-x span' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .alezux-chart-grid-line' => 'border-top-color: {{VALUE}}; opacity: 0.2;',
				],
			]
		);

        $this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
        
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
        
        // --- DATA FETCHING (SQL) ---
        $log = [];
        $currency_symbol = '$'; // Default or fetched from settings if available
        
        // Si estamos en el editor usamos mock data, si no, intentamos sacar datos reales.
        // Nota: Si el dashboard de Finanzas es global admin, tal vez no deberíamos filtrar por user_id.
        // Pero el widget original filtraba por user_id. 
        // Análisis: Finanzas suele ser para el ADMIN o para el USUARIO viendo sus compras?
        // Contexto: "mostrar aqui en finanzas los datos de los pagos". Si es un dashboard de admin, debe ver TODO.
        // Si es un "Mis Compras" de usuario, debe ver SUYOS.
        // Asumiremos ADMINISTRADOR GLOBAL viendo ingresos totales por defecto, o podemos agregar un control.
        // Pero dado que 'finanzas' suena a módulo de administración, mostraré TODOS los ingresos (Global).
        // Si el usuario quisiera "Mis gastos", sería diferente. Asumo "Dashboard de Ingresos" para el Admin.
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'alezux_finanzas_transactions';
        
        // Fix Timezone: Use WP Local Time as base
        $wp_local_timestamp = current_time('timestamp');
        // Fetch 1 year of data for all views
        $one_year_ago = date('Y-m-d', strtotime('-1 year', $wp_local_timestamp));
        
        // Query: Sum amount per day
        // Importante: status = 'succeeded'
        // Chequeamos si la tabla existe para evitar errores fatales en editor si no se ha corrido installer
        if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name ) {
             $results = $wpdb->get_results( $wpdb->prepare(
                "SELECT DATE(created_at) as date, SUM(amount) as total_amount, currency
                 FROM $table_name 
                 WHERE status = 'succeeded' 
                 AND created_at >= %s 
                 GROUP BY DATE(created_at)",
                $one_year_ago
            ) );

            if ( $results ) {
                foreach ( $results as $row ) {
                    $log[ $row->date ] = (float) $row->total_amount;
                    // Podríamos tratar de detectar divisa, pero asumiremos la del último o default.
                }
            }
        }
        
        if ( empty($log) && $is_editor ) {
            // Mock data for editor
            $today = date('Y-m-d', current_time('timestamp'));
            $log = [
                date('Y-m-d', strtotime('-1 day', current_time('timestamp'))) => 150.00,
                date('Y-m-d', strtotime('-2 days', current_time('timestamp'))) => 300.50,
                date('Y-m-d', strtotime('-3 days', current_time('timestamp'))) => 75.00,
                $today => 220.00,
            ];
        }

        $wp_local_timestamp = current_time('timestamp'); // Re-fetch to be sure available in this scope

        // --- DATA PROCESSING FUNCTIONS ---
        
        // Helper para formatear moneda
        $format_money = function($amount) use ($currency_symbol) {
             return $currency_symbol . number_format($amount, 2);
        };
        
        // Helper para formatear ejes (sin decimales para limpieza si es entero grande)
        $format_axis = function($amount) use ($currency_symbol) {
             if ($amount >= 1000) return $currency_symbol . number_format($amount/1000, 1) . 'k';
             return $currency_symbol . number_format($amount, 0);
        };

        // 1. Weekly Data (Calendar Week: Mon-Sun)
        $weekly_data = [];
        $max_weekly = 0;
        
        $current_w = date('w', $wp_local_timestamp);
        $offset_to_monday = ($current_w == 0) ? 6 : $current_w - 1;
        $monday_timestamp = strtotime("-$offset_to_monday days", $wp_local_timestamp);

        for ($i = 0; $i < 7; $i++) {
            $day_ts = strtotime("+$i days", $monday_timestamp);
            $d = date('Y-m-d', $day_ts);
            
            $val = isset($log[$d]) ? $log[$d] : 0;
            if ($val > $max_weekly) $max_weekly = $val;
            
            $is_today = ($d === date('Y-m-d', $wp_local_timestamp));
            
            $weekly_data[] = [
                'val' => $val,
                'label' => date_i18n('l', $day_ts), 
                'full_date' => date_i18n(get_option('date_format'), $day_ts),
                'date' => $d,
                'is_today' => $is_today
            ];
        }

        // Round Max for nice axis
        if ($max_weekly == 0) $max_weekly = 100;
        // Redondear al siguiente 10, 100 o 1000
        $magnitude = pow(10, floor(log10($max_weekly)));
        $max_weekly = ceil($max_weekly / ($magnitude/2)) * ($magnitude/2);


        // 2. Monthly Data (Calendar Month: 1st to End of Month)
        $monthly_data = [];
        $max_monthly = 0;
        
        $current_month_start_ts = strtotime(date('Y-m-01', $wp_local_timestamp));
        $days_in_month = (int)date('t', $wp_local_timestamp);
        
        for ($i = 0; $i < $days_in_month; $i++) {
            $day_ts = strtotime("+$i days", $current_month_start_ts);
            $d = date('Y-m-d', $day_ts);
            
            $val = isset($log[$d]) ? $log[$d] : 0;
            if ($val > $max_monthly) $max_monthly = $val;
            
            $day_number = (int)date('j', $day_ts);
            $is_today = ($d === date('Y-m-d', $wp_local_timestamp));
            
            $label = '';
            if ($day_number === 1 || $day_number % 5 === 0 || $day_number === $days_in_month) {
                $label = $day_number;
            }

            $monthly_data[] = [
                'val' => $val,
                'label' => $label,
                'full_date' => date_i18n(get_option('date_format'), $day_ts),
                'date' => $d,
                'is_today' => $is_today
            ];
        }

        if ($max_monthly == 0) $max_monthly = 100;
        $magnitude = pow(10, floor(log10($max_monthly)));
        $max_monthly = ceil($max_monthly / ($magnitude/2)) * ($magnitude/2);


        // 3. Yearly Data (Calendar Year: Jan-Dec)
        $yearly_data = [];
        $max_yearly = 0;
        $current_year = date('Y', $wp_local_timestamp);

        for ($m = 1; $m <= 12; $m++) {
            $month_start = "$current_year-" . sprintf('%02d', $m) . "-01";
            $month_end = date('Y-m-t', strtotime($month_start));
            $month_val = 0;
            
            $current_day = $month_start;
            while (strtotime($current_day) <= strtotime($month_end)) {
                 if (isset($log[$current_day])) {
                     $month_val += $log[$current_day];
                 }
                 $current_day = date('Y-m-d', strtotime($current_day . ' +1 day'));
            }

            if ($month_val > $max_yearly) $max_yearly = $month_val;
            
            $is_current_month = ($m === (int)date('n', $wp_local_timestamp));

            $yearly_data[] = [
                'val' => $month_val,
                'label' => date_i18n('F', strtotime($month_start)),
                'full_date' => date_i18n('F Y', strtotime($month_start)),
                'is_today' => $is_current_month
            ];
        }

        if ($max_yearly == 0) $max_yearly = 1000;
        $magnitude = pow(10, floor(log10($max_yearly)));
        $max_yearly = ceil($max_yearly / ($magnitude/2)) * ($magnitude/2);


        // Helper render function
        $render_chart = function($id, $data_set, $max_val, $is_active = false) use ($format_money, $format_axis) {
             $style = $is_active ? '' : 'display:none;';
             ?>
             <div class="alezux-rendimiento-chart-area" id="<?php echo esc_attr($id); ?>" style="<?php echo $style; ?>">
                <!-- Y Axis -->
                <div class="alezux-axis-y">
                    <?php 
                        $steps = 4;
                        for ($i = $steps; $i >= 0; $i--) {
                            $amount = ($max_val / $steps) * $i;
                            echo '<span>' . $format_axis($amount) . '</span>';
                        }
                    ?>
                </div>

                <!-- Grid & Bars -->
                <div class="alezux-chart-plot">
                    <?php 
                        for ($i = 0; $i <= $steps; $i++) {
                            $top_pct = ($i / $steps) * 100;
                            echo '<div class="alezux-chart-grid-line" style="top: ' . $top_pct . '%;"></div>';
                        }
                    ?>
                    
                    <div class="alezux-bars-container">
                        <?php foreach($data_set as $data): 
                            $height_pct = ($max_val > 0) ? ($data['val'] / $max_val) * 100 : 0;
                            // Format Money
                            $formatted_value = $format_money($data['val']);
                        ?>
                            <div class="alezux-bar-column">
                                <div class="alezux-bar-item <?php echo isset($data['is_today']) && $data['is_today'] ? 'is-today' : ''; ?>" style="height: <?php echo $height_pct; ?>%;">
                                    <div class="alezux-bar-fill"></div>
                                    <div class="alezux-bar-tooltip">
                                        <div class="tooltip-time"><?php echo $formatted_value; ?></div>
                                        <div class="tooltip-meta"><?php echo $data['full_date']; ?></div>
                                    </div>
                                </div>
                                <div class="alezux-axis-x-label"><?php echo $data['label']; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
             <?php
        };

        // --- RENDER HTML ---
		?>
		<div class="alezux-rendimiento-wrapper">
            <div class="alezux-rendimiento-header">
                <div class="alezux-rendimiento-title"><?php echo esc_html( $settings['title_text'] ); ?></div>
                <div class="alezux-rendimiento-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
            </div>

            <div class="alezux-rendimiento-tabs">
                <div class="alezux-tab-item active" data-tab="weekly"><?php echo esc_html( $settings['tab_daily_label'] ); ?></div>
                <div class="alezux-tab-item" data-tab="monthly"><?php echo esc_html( $settings['tab_weekly_label'] ); ?></div>
                <div class="alezux-tab-item" data-tab="yearly"><?php echo esc_html( $settings['tab_monthly_label'] ); ?></div>
            </div>

            <?php 
                $render_chart('chart-weekly', $weekly_data, $max_weekly, true);
                $render_chart('chart-monthly', $monthly_data, $max_monthly, false);
                $render_chart('chart-yearly', $yearly_data, $max_yearly, false);
            ?>
            
		</div>

        <script>
        jQuery(document).ready(function($) {
            $('.alezux-rendimiento-wrapper .alezux-tab-item').on('click', function() {
                var tab = $(this).data('tab');
                var $wrapper = $(this).closest('.alezux-rendimiento-wrapper');
                
                $wrapper.find('.alezux-tab-item').removeClass('active');
                $(this).addClass('active');
                
                $wrapper.find('.alezux-rendimiento-chart-area').hide();
                $wrapper.find('#chart-' + tab).fadeIn(200);
            });
        });
        </script>

        <style>
            .alezux-rendimiento-wrapper {
                font-family: 'Roboto', sans-serif;
                overflow: hidden;
            }
            .alezux-rendimiento-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
            }
            .alezux-rendimiento-title {
                font-size: 18px;
                font-weight: 600;
            }
            .alezux-rendimiento-tabs {
                display: flex;
                border-radius: 8px;
                padding: 4px;
                margin-bottom: 30px;
            }
            .alezux-tab-item {
                flex: 1;
                text-align: center;
                padding: 8px 12px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                border-radius: 6px;
                transition: all 0.3s;
            }
            
            /* Chart Layout */
            .alezux-rendimiento-chart-area {
                display: flex;
                height: 250px;
                width: 100%;
            }
            .alezux-axis-y {
                width: 60px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                padding-right: 10px;
                font-size: 11px;
                text-align: right;
                padding-bottom: 20px; /* Offset for x labels */
            }
            .alezux-axis-y span {
                transform: translateY(-50%);
            }
            .alezux-axis-y span:first-child { transform: translateY(0); } /* Top */
            .alezux-axis-y span:last-child { transform: translateY(0); } /* Bottom */
            
            .alezux-chart-plot {
                flex-grow: 1;
                position: relative;
                border-left: 0px solid #eee;
                border-bottom: 0px solid #eee;
                display: flex;
                align-items: flex-end;
            }
            
            .alezux-chart-grid-line {
                position: absolute;
                left: 0;
                right: 0;
                border-top: 1px dashed #ccc;
                z-index: 0;
            }
            
            .alezux-bars-container {
                display: flex;
                width: 100%;
                height: 100%;
                justify-content: space-between;
                align-items: flex-end;
                z-index: 1;
                padding: 0 10px;
            }
            
            .alezux-bar-column {
                flex: 1;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: flex-end;
                align-items: center;
                position: relative;
            }
            
            .alezux-bar-item {
                width: 60%;
                max-width: 40px;
                min-height: 4px; /* Ensure visible even if 0 */
                border-radius: 6px 6px 0 0;
                position: relative;
                transition: height 0.5s ease;
                cursor: pointer;
            }
            
            .alezux-bar-fill {
                width: 100%;
                height: 100%;
                border-radius: 6px 6px 0 0;
                opacity: 0.8;
                transition: opacity 0.3s;
            }
            .alezux-bar-item:hover .alezux-bar-fill {
                opacity: 1;
            }
            
            .alezux-bar-item.is-today .alezux-bar-fill, 
            .alezux-bar-item.is-best .alezux-bar-fill,
            .alezux-bar-item:hover .alezux-bar-fill {
                /* Color controlled by chart_bar_active_color */
                opacity: 1;
            }
            
            .alezux-axis-x-label {
                margin-top: 8px;
                font-size: 12px;
                color: #999;
                text-align: center;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 100%;
            }
            
            /* Tooltip */
            .alezux-bar-tooltip {
                position: absolute;
                bottom: 100%;
                left: 50%;
                transform: translateX(-50%) translateY(10px); /* Initially hidden lower */
                background: #fff;
                padding: 8px 12px;
                border-radius: 8px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.15);
                white-space: nowrap;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                pointer-events: none;
                z-index: 10;
                text-align: center;
                display: flex;
                flex-direction: column;
                align-items: center;
                min-width: 80px;
            }
            .alezux-bar-item.is-today .alezux-bar-tooltip,
            .alezux-bar-item:hover .alezux-bar-tooltip {
                opacity: 1;
                visibility: visible;
                transform: translateX(-50%) translateY(-10px);
            }
            
            .tooltip-icon {
                font-size: 16px;
                margin-bottom: 4px;
                line-height: 1;
            }
            .tooltip-time {
                font-weight: 700;
                font-size: 14px;
                color: #000;
                line-height: 1.2;
            }
            .tooltip-meta {
                font-size: 11px;
                color: #888;
                margin-top: 2px;
                line-height: 1.2;
            }
            .alezux-bar-tooltip::after {
                content: '';
                position: absolute;
                top: 100%;
                left: 50%;
                margin-left: -6px;
                border-width: 6px;
                border-style: solid;
                border-color: #fff transparent transparent transparent;
            }
        </style>
		<?php
	}
}
