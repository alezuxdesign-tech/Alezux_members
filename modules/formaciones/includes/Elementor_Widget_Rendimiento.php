<?php
namespace Alezux_Members\Modules\Formaciones\Includes;

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
 * Widget de Elementor: Rendimiento (Analítica de Tiempo)
 */
class Elementor_Widget_Rendimiento extends Widget_Base {

	public function get_name() {
		return 'alezux_rendimiento';
	}

	public function get_title() {
		return __( 'Alezux Rendimiento', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-time-line';
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
				'default' => __( 'Time Spent Analytic', 'alezux-members' ),
                'placeholder' => __( 'Time Spent Analytic', 'alezux-members' ),
			]
		);

        // Labels for Tabs
        $this->add_control(
			'tab_daily_label',
			[
				'label' => __( 'Etiqueta Semanal (7 Días)', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Weekly', 'alezux-members' ),
			]
		);
        $this->add_control(
			'tab_weekly_label',
			[
				'label' => __( 'Etiqueta Mensual (30 Días)', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Monthly', 'alezux-members' ),
			]
		);
        $this->add_control(
			'tab_monthly_label',
			[
				'label' => __( 'Etiqueta Anual (12 Meses)', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Yearly', 'alezux-members' ),
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

        $this->add_responsive_control(
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

        $this->add_responsive_control(
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

        $this->add_responsive_control(
			'tabs_margin',
			[
				'label' => __( 'Margen', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-rendimiento-tabs' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
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

        $this->add_responsive_control(
			'tabs_padding',
			[
				'label' => __( 'Relleno (Padding) Interno', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-tab-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

        $this->add_responsive_control(
            'chart_height',
            [
                'label' => __( 'Altura del Gráfico', 'alezux-members' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 600,
                        'step' => 1,
                    ],
                    'vh' => [
                        'min' => 10,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 250,
                ],
                'selectors' => [
                    '{{WRAPPER}} .alezux-rendimiento-chart-area' => 'height: {{SIZE}}{{UNIT}};',
                ],
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
        
        $user_id = get_current_user_id();
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
        
        // --- DATA FETCHING ---
        // --- DATA FETCHING (SQL) ---
        $log = [];
        if ( is_user_logged_in() ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'alezux_study_log';
            
            // Fix Timezone: Use WP Local Time as base
            $wp_local_timestamp = current_time('timestamp');
            // Fetch 1 year of data for all views
            $one_year_ago = date('Y-m-d', strtotime('-1 year', $wp_local_timestamp));
            
            // Query: Sum seconds per day for this user, from 1 year ago until now
            $results = $wpdb->get_results( $wpdb->prepare(
                "SELECT date, SUM(seconds) as total_seconds 
                 FROM $table_name 
                 WHERE user_id = %d 
                 AND date >= %s 
                 GROUP BY date",
                $user_id,
                $one_year_ago
            ) );

            if ( $results ) {
                foreach ( $results as $row ) {
                    $log[ $row->date ] = (int) $row->total_seconds;
                }
            }
        } else if ( $is_editor ) {
            // Mock data for editor
            $today = date('Y-m-d', current_time('timestamp'));
            $log = [
                date('Y-m-d', strtotime('-1 day', current_time('timestamp'))) => 3600,
                date('Y-m-d', strtotime('-2 days', current_time('timestamp'))) => 7200,
                date('Y-m-d', strtotime('-3 days', current_time('timestamp'))) => 1800,
                $today => 5400,
            ];
        }

        $wp_local_timestamp = current_time('timestamp'); // Re-fetch to be sure available in this scope

        // --- DATA PROCESSING FUNCTIONS ---
        // Helper to process daily bars (used for Weekly and Monthly)
        $process_daily_data = function($days_count) use ($log, $wp_local_timestamp) {
            $data = [];
            $max_val = 0;
            for ($i = $days_count - 1; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-$i days", $wp_local_timestamp));
                $val = isset($log[$d]) ? $log[$d] : 0;
                if ($val > $max_val) $max_val = $val;
                
                $day_label = date_i18n('D', strtotime($d)); // Localized day name
                // For Monthly (30 days), modify label slightly later or here
                
                $data[] = [
                    'val' => $val,
                    'label' => $day_label,
                    'full_date' => date_i18n(get_option('date_format'), strtotime($d)),
                    'date' => $d,
                    'is_today' => ($i === 0)
                ];
            }
            return ['data' => $data, 'max' => $max_val];
        };

        // 1. Weekly Data (Calendar Week: Mon-Sun)
        $weekly_data = [];
        $max_weekly = 0;
        
        // Find Monday of the current week (based on WP Local Time)
        // 'w' 0 (Sun) - 6 (Sat). We want Monday (1) to be start.
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
                // Force label to be Mon, Tue, etc.
                'label' => date_i18n('l', $day_ts), // Full day name as requested "Lunes", "Martes" -> 'l' gives full name. User said "Lunes...Domingo"
                'full_date' => date_i18n(get_option('date_format'), $day_ts),
                'date' => $d,
                'is_today' => $is_today
            ];
        }

        // Calculate Real Max for Best logic (Weekly)
        $real_max_weekly = 0;
        foreach ($weekly_data as $d) {
            if ($d['val'] > $real_max_weekly) $real_max_weekly = $d['val'];
        }
        if ($max_weekly == 0) $max_weekly = 3600;
        $max_weekly = ceil($max_weekly / 1800) * 1800;

        // 2. Monthly Data (Calendar Month: 1st to End of Month)
        // User requested to see all days of the current month (e.g., Jan 1 to Jan 31).
        $monthly_data = [];
        $max_monthly = 0;
        
        $current_month_start_ts = strtotime(date('Y-m-01', $wp_local_timestamp));
        $days_in_month = (int)date('t', $wp_local_timestamp); // e.g., 31
        
        for ($i = 0; $i < $days_in_month; $i++) {
            $day_ts = strtotime("+$i days", $current_month_start_ts);
            $d = date('Y-m-d', $day_ts);
            
            $val = isset($log[$d]) ? $log[$d] : 0;
            if ($val > $max_monthly) $max_monthly = $val;
            
            $day_number = (int)date('j', $day_ts);
            $is_today = ($d === date('Y-m-d', $wp_local_timestamp));
            
            // Label Logic: Show every 5th day AND the last day? 
            // Or if user wants "all numbers" but said "depending on the day". 
            // 31 labels might be too tight. Let's try showing every 2nd or 3rd day, or keep 5th but ensure last day is shown.
            // Let's stick to every 3rd day to be more granular but not crowded, or 5th. 
            // "me sale los numeros hasta el dia 30" -> implies they saw labels. 
            // Let's show: 1, 5, 10, 15, 20, 25, 30...
            $label = '';
            if ($day_number === 1 || $day_number % 5 === 0 || $day_number === $days_in_month) { // Show 1, 5, 10, 15... and last day
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

        if ($max_monthly == 0) $max_monthly = 3600;
        $max_monthly = ceil($max_monthly / 1800) * 1800;

        // 3. Yearly Data (Calendar Year: Jan-Dec)
        $yearly_data = [];
        $max_yearly = 0;
        $current_year = date('Y', $wp_local_timestamp);

        for ($m = 1; $m <= 12; $m++) {
            $month_start = "$current_year-" . sprintf('%02d', $m) . "-01";
            $month_end = date('Y-m-t', strtotime($month_start));
            $month_val = 0;
            
            // Sum all days in this month from $log
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
                'label' => date_i18n('F', strtotime($month_start)), // Full month name "Enero", "Febrero"
                'full_date' => date_i18n('F Y', strtotime($month_start)),
                'is_today' => $is_current_month
            ];
        }
        if ($max_yearly == 0) $max_yearly = 3600;
        $max_yearly = ceil($max_yearly / 3600) * 3600; // Round to nearest hour for yearly

        // Helper render function to avoid code duplication
        $render_chart = function($id, $data_set, $max_val, $is_active = false) {
             $style = $is_active ? '' : 'display:none;';
             ?>
             <div class="alezux-rendimiento-chart-area" id="<?php echo esc_attr($id); ?>" style="<?php echo $style; ?>">
                <!-- Y Axis -->
                <div class="alezux-axis-y">
                    <?php 
                        $steps = 4;
                        for ($i = $steps; $i >= 0; $i--) {
                            $seconds = ($max_val / $steps) * $i;
                            echo '<span>' . gmdate("H:i", $seconds) . '</span>';
                        }
                    ?>
                </div>

                <!-- Grid & Bars -->
                <div class="alezux-chart-plot">
                    <?php 
                        // Grid Lines
                        for ($i = 0; $i <= $steps; $i++) {
                            $top_pct = ($i / $steps) * 100;
                            echo '<div class="alezux-chart-grid-line" style="top: ' . $top_pct . '%;"></div>';
                        }
                    ?>
                    
                    <div class="alezux-bars-container">
                        <?php foreach($data_set as $data): 
                            $height_pct = ($data['val'] / $max_val) * 100;
                            $formatted_time = ($data['val'] >= 3600) ? floor($data['val']/3600).'h '.floor(($data['val']%3600)/60).'m' : gmdate("i:s", $data['val']);
                        ?>
                            <div class="alezux-bar-column">
                                <div class="alezux-bar-item <?php echo isset($data['is_today']) && $data['is_today'] ? 'is-today' : ''; ?>" style="height: <?php echo $height_pct; ?>%;">
                                    <div class="alezux-bar-fill"></div>
                                    <div class="alezux-bar-tooltip">
                                        <div class="tooltip-time"><?php echo $formatted_time; ?></div>
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
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="7" y1="17" x2="17" y2="7"></line>
                    <polyline points="7 7 17 7 17 17"></polyline>
                </svg>
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
                min-width: 0; /* Permite que el contenedor se encoja y habilite el scroll interno */
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
                overflow-x: auto;
                overflow-y: hidden;
                scrollbar-width: none; /* Firefox */
                -ms-overflow-style: none; /* IE and Edge */
                -webkit-overflow-scrolling: touch; /* Scroll fluido en dispositivos móviles / iOS */
            }
            .alezux-bars-container::-webkit-scrollbar {
                display: none; /* Chrome, Safari and Opera */
            }
            
            .alezux-bar-column {
                flex: 1;
                min-width: 65px; /* Aumentado para dar más espacio a los nombres de los meses */
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: flex-end;
                align-items: center;
                position: relative;
                padding: 0 5px; /* Añade un poco de respiro entre columnas */
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
            
            .alezux-bar-active-color-placeholder {} /* Dummy for finding linter position if needed */
            
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
