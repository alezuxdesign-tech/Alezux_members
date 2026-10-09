<?php
namespace Alezux_Members\Modules\Finanzas\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcodes {

	public static function init() {
        // --- Nuevos Shortcodes Dashboard (AJAX Powered) ---
        add_shortcode( 'alezux_sales_stats', [ __CLASS__, 'render_sales_stats' ] );

	}



    /**
     * Nuevos Renders para Dashboard (AJAX)
     */
    public static function render_sales_stats( $atts ) {
        $a = shortcode_atts( [
            'type' => 'month_revenue', // month_revenue, today_revenue
            'format' => 'raw', // raw (number only) or card (full widget)
            'label' => ''
        ], $atts );

        $stat_type = esc_attr($a['type']);
        
        // Normalize shorthand types
        if ($stat_type === 'month') $stat_type = 'month_revenue';
        if ($stat_type === 'today') $stat_type = 'today_revenue';

        // RAW OUTPUT (For custom designs)
        if ( $a['format'] === 'raw' ) {
            // Output a span that JS will target to update
            return sprintf(
                '<span class="alezux-dynamic-stat-raw" data-stat-type="%s">--</span>',
                $stat_type
            );
        }

        // ... (Legacy Card Output if needed, but user asked for raw)
        $label = $a['label'];
        if(!$label) {
            $label = ($a['type'] === 'today_revenue') ? 'Ingresos Hoy' : 'Ingresos Mes';
        }

        ob_start();
        ?>
        <div class="alezux-kpi-card" data-stat-type="<?php echo $stat_type; ?>" style="padding: 20px; background: #1a1a1a; border: 1px solid #333; border-radius: 12px; position: relative; overflow: hidden; min-width: 200px; display: inline-block; margin: 10px;">
            <div class="alezux-kpi-content" style="display: flex; align-items: center; justify-content: space-between; gap: 15px;">
                <div class="alezux-kpi-info" style="display: flex; flex-direction: column; gap: 5px;">
                    <span class="alezux-kpi-label" style="font-size: 14px; color: #888; font-weight: 500;"><?php echo esc_html($label); ?></span>
                    <h3 class="alezux-kpi-value" style="font-size: 28px; color: #fff; margin: 0; font-weight: 700;">
                        <span class="alezux-kpi-number">--</span>
                    </h3>
                </div>
                <div class="alezux-kpi-icon" style="width: 50px; height: 50px; background: rgba(108, 92, 231, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #6c5ce7;">
                    <span class="dashicons dashicons-money-alt" style="font-size:24px; height:24px; width:24px; display:flex; align-items:center; justify-content:center;"></span>
                </div>
            </div>
             <div class="alezux-kpi-loading" style="display:none; position: absolute; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center;">...</div>
        </div>
        <?php
        return ob_get_clean();
    }


}
