<?php
namespace Alezux_Members\Modules\Config\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Alezux_Members\Modules\Config\Includes\Admin_Dashboard_Stats;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elementor_Widget_Admin_Ingresos extends Widget_Base {

	public function get_name() {
		return 'alezux_admin_revenue';
	}

	public function get_title() {
		return __( 'Admin: Ingresos (7 Días)', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-bar-chart';
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
				'default' => __( 'Ingresos Recientes', 'alezux-members' ),
			]
		);
        

        
        $this->add_control(
			'simulate_data',
			[
				'label' => __( 'Simular datos (Demo)', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'description' => 'Activa esto para ver el gráfico con datos falsos si aún no tienes ventas.',
				'default' => 'no',
			]
		);

        // Labels for Tabs
        $this->add_control(
			'tab_daily_label',
			[
				'label' => __( 'Etiqueta Semanal', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Semanal', 'alezux-members' ),
			]
		);
        $this->add_control(
			'tab_weekly_label',
			[
				'label' => __( 'Etiqueta Mensual', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Mensual', 'alezux-members' ),
			]
		);
        $this->add_control(
			'tab_monthly_label',
			[
				'label' => __( 'Etiqueta Anual', 'alezux-members' ),
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

        // --- SECCIÓN: ESTILO - GRÁFICO (CHART) ---
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
					'{{WRAPPER}} .alezux-axis-y span, {{WRAPPER}} .alezux-axis-xspan' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .alezux-chart-grid-line' => 'border-top-color: {{VALUE}}; opacity: 0.2;',
                    '{{WRAPPER}} .alezux-axis-x-label' => 'color: {{VALUE}};',
				],
			]
		);

        $this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
        
        // --- DATA FETCHING ---
        $log = [];
        
        // Fetch 1 year of data directly
        $db_data = Admin_Dashboard_Stats::get_revenue_log(366); // 1 year + extra margin
        
        if ( ! empty($db_data) ) {
            foreach($db_data as $row) {
                // $row is object {date: 'Y-m-d', total: 123.45}
                $date = is_object($row) ? $row->date : $row['date'];
                $total = is_object($row) ? $row->total : $row['total'];
                $log[$date] = (float)$total;
            }
        }

        // Demo Data
        if ( 'yes' === $settings['simulate_data'] || (empty($log) && \Elementor\Plugin::$instance->editor->is_edit_mode()) ) {
            $today_ts = current_time('timestamp');
             for ($i = 365; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-$i days", $today_ts));
                if (!isset($log[$d])) {
                    // Random sales pattern: some zeros, some values
                    if (rand(0,10) > 3) $log[$d] = rand(50, 500) + (rand(0,99)/100);
                }
             }
        }
        
        $wp_local_timestamp = current_time('timestamp'); 
        $currency_symbol = '$'; // TODO: Get from settings if needed

        // --- DATA PROCESSING ---

        // 1. Weekly Data (Calendar Week: Mon-Sun)
        $weekly_data = [];
        $max_weekly = 0;
        $total_weekly = 0;
        
        // Find Monday of the current week (based on WP Local Time)
        $current_w = date('w', $wp_local_timestamp);
        $offset_to_monday = ($current_w == 0) ? 6 : $current_w - 1;
        $monday_timestamp = strtotime("-$offset_to_monday days", $wp_local_timestamp);

        for ($i = 0; $i < 7; $i++) {
            $day_ts = strtotime("+$i days", $monday_timestamp);
            $d = date('Y-m-d', $day_ts);
            
            $val = isset($log[$d]) ? $log[$d] : 0;
            if ($val > $max_weekly) $max_weekly = $val;
            $total_weekly += $val;
            
            $is_today = ($d === date('Y-m-d', $wp_local_timestamp));
            
            $weekly_data[] = [
                'val' => $val,
                'label' => date_i18n('l', $day_ts), // Full day name
                'full_date' => date_i18n(get_option('date_format'), $day_ts),
                'is_today' => $is_today
            ];
        }
        if ($max_weekly == 0) $max_weekly = 100;
        // Scale nice numbers
        $magnitude = pow(10, floor(log10($max_weekly)));
        $max_weekly = ceil($max_weekly / ($magnitude/2)) * ($magnitude/2);


        // 2. Monthly Data (Calendar Month: 1st to End of Month)
        $monthly_data = [];
        $max_monthly = 0;
        $total_monthly = 0;
        
        $current_month_start_ts = strtotime(date('Y-m-01', $wp_local_timestamp));
        $days_in_month = (int)date('t', $wp_local_timestamp); 
        
        for ($i = 0; $i < $days_in_month; $i++) {
            $day_ts = strtotime("+$i days", $current_month_start_ts);
            $d = date('Y-m-d', $day_ts);
            
            $val = isset($log[$d]) ? $log[$d] : 0;
            if ($val > $max_monthly) $max_monthly = $val;
            $total_monthly += $val;
            
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
                'is_today' => $is_today
            ];
        }
        if ($max_monthly == 0) $max_monthly = 100;
        $magnitude = pow(10, floor(log10($max_monthly)));
        $max_monthly = ceil($max_monthly / ($magnitude/2)) * ($magnitude/2);


        // 3. Yearly Data (Calendar Year: Jan-Dec)
        $yearly_data = [];
        $max_yearly = 0;
        $total_yearly = 0;
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
            $total_yearly += $month_val;
            
            $is_current_month = ($m === (int)date('n', $wp_local_timestamp));

            $yearly_data[] = [
                'val' => $month_val,
                'label' => date_i18n('F', strtotime($month_start)), // Full month name
                'full_date' => date_i18n('F Y', strtotime($month_start)),
                'is_today' => $is_current_month
            ];
        }
        if ($max_yearly == 0) $max_yearly = 100;
        $magnitude = pow(10, floor(log10($max_yearly)));
        $max_yearly = ceil($max_yearly / ($magnitude/2)) * ($magnitude/2);


        // Render Helpers
        $format_axis = function($amount) use ($currency_symbol) {
             if ($amount >= 1000) return $currency_symbol . number_format($amount/1000, 1) . 'k';
             return $currency_symbol . number_format($amount, 0);
        };
        $format_money = function($amount) use ($currency_symbol) {
             return $currency_symbol . number_format($amount, 2);
        };
        
        // Chart Render Function
        $render_chart = function($id, $data_set, $max_val, $is_active = false) use ($format_axis, $format_money) {
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
                        ?>
                            <div class="alezux-bar-column">
                                <div class="alezux-bar-item <?php echo isset($data['is_today']) && $data['is_today'] ? 'is-today' : ''; ?>" style="height: <?php echo $height_pct; ?>%;">
                                    <div class="alezux-bar-fill"></div>
                                    <div class="alezux-bar-tooltip">
                                        <div class="tooltip-time"><?php echo $format_money($data['val']); ?></div>
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

		?>
		<div class="alezux-rendimiento-wrapper">
            <div class="alezux-rendimiento-header">
                <div class="alezux-rendimiento-title">
                    <?php echo esc_html( $settings['title_text'] ); ?>
                </div>
                <!-- Dynamic Pill - Updated by JS ideally, but here static for active tab? 
                     Basically we need JS to update this total if we want it perfect.
                     For now, let's just show Weekly total as default, or maybe hide it?
                     The requested widget doesn't strictly have a total pill in the screenshot context, 
                     but the original had one. The Rendimiento one has an icon.
                     Let's match Rendimiento: Icon instead of Pill? 
                     User said: "igual al widget de rendimiento con la unica diferencia es que aqui muestra son datos de ventas"
                     So I'll swap the icon for the pill, but maybe make the pill dynamic via JS?
                     Or just 3 pills hidden/shown?
                -->
                 <div class="alezux-revenue-pill" id="pill-weekly">
                     <?php echo $format_money($total_weekly); ?>
                 </div>
                 <div class="alezux-revenue-pill" id="pill-monthly" style="display:none;">
                     <?php echo $format_money($total_monthly); ?>
                 </div>
                 <div class="alezux-revenue-pill" id="pill-yearly" style="display:none;">
                     <?php echo $format_money($total_yearly); ?>
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

            <script>
            jQuery(document).ready(function($) {
                // Unique IDs might be needed if multiple widgets?
                // Scope to wrapper
                $('.alezux-rendimiento-wrapper').each(function() {
                    var $wrapper = $(this);
                    
                    $wrapper.find('.alezux-tab-item').on('click', function() {
                        var tab = $(this).data('tab');
                        
                        $wrapper.find('.alezux-tab-item').removeClass('active');
                        $(this).addClass('active');
                        
                        $wrapper.find('.alezux-rendimiento-chart-area').hide();
                        $wrapper.find('#chart-' + tab).fadeIn(200);
                        
                        // Also toggle pills
                        $wrapper.find('.alezux-revenue-pill').hide();
                        $wrapper.find('#pill-' + tab).fadeIn(200);
                    });
                });
            });
            </script>
		</div>

        <style>
            /* Reutilizando estilos de Financial_Performance_Widget (Alezux Ingresos) */
            .alezux-rendimiento-wrapper {
                font-family: 'Roboto', sans-serif;
                overflow: hidden;
                /* El borde y fondo son manejados por controles Elementor, defaults aqui */
            }
            .alezux-rendimiento-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 25px;
            }
            .alezux-rendimiento-title {
                font-size: 18px;
                font-weight: 600;
            }
            .alezux-revenue-pill {
                background: rgba(86, 85, 214, 0.1);
                color: #5655d6;
                padding: 4px 12px;
                border-radius: 20px;
                font-weight: 700;
                font-size: 14px;
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
                height: 200px; /* Un poco mas compacto que el full dashboard */
                width: 100%;
            }
            .alezux-axis-y {
                width: 50px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                padding-right: 8px;
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
                padding: 0 5px;
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
                width: 50%;
                max-width: 30px;
                min-height: 4px; /* Ensure visible even if 0 */
                border-radius: 4px 4px 0 0;
                position: relative;
                transition: height 0.5s ease;
                cursor: pointer;
            }
            
            .alezux-bar-fill {
                width: 100%;
                height: 100%;
                border-radius: 4px 4px 0 0;
                opacity: 0.8;
                transition: opacity 0.3s;
                /* Background set by controls */
            }
            .alezux-bar-item:hover .alezux-bar-fill {
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
                padding: 6px 10px;
                border-radius: 6px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.15);
                white-space: nowrap;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                pointer-events: none;
                z-index: 10;
                text-align: center;
            }
            .alezux-bar-item:hover .alezux-bar-tooltip {
                opacity: 1;
                visibility: visible;
                transform: translateX(-50%) translateY(-8px);
            }
            
            .tooltip-time {
                font-weight: 700;
                font-size: 13px;
                color: #000;
                line-height: 1.2;
            }
            .tooltip-meta {
                font-size: 10px;
                color: #888;
                margin-top: 2px;
                line-height: 1.2;
            }
            .alezux-bar-tooltip::after {
                content: '';
                position: absolute;
                top: 100%;
                left: 50%;
                margin-left: -5px;
                border-width: 5px;
                border-style: solid;
                border-color: #fff transparent transparent transparent;
            }
        </style>
		<?php
	}
}
