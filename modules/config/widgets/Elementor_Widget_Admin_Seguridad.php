<?php
namespace Alezux_Members\Modules\Config\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Alezux_Members\Modules\Config\Includes\Admin_Dashboard_Stats;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elementor_Widget_Admin_Seguridad extends Widget_Base {

	public function get_name() {
		return 'alezux_admin_security';
	}

	public function get_title() {
		return __( 'Admin: Seguridad', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-lock-user';
	}

	public function get_categories() {
		return [ 'alezux-lms' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Configuración', 'alezux-members' ),
			]
		);
        
        $this->add_control(
			'safe_message',
			[
				'label' => __( 'Mensaje (Seguro)', 'alezux-members' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => __( 'Todo en orden. No se han detectado intentos de acceso inusuales hoy.', 'alezux-members' ),
			]
		);
        
        $this->add_control(
			'alert_message',
			[
				'label' => __( 'Mensaje (Alerta)', 'alezux-members' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => __( 'Atención: Se han detectado {count} intentos de inicio de sesión fallidos hoy.', 'alezux-members' ),
                'description' => 'Usa {count} para mostrar el número.'
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Estilos', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        // Estilos para estado SEGURO
        $this->add_control(
			'heading_safe',
			[
				'label' => __( 'Estado: Seguro', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'safe_color',
			[
				'label' => __( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => 'rgba(76, 175, 80, 0.1)',
			]
		);
        
        $this->add_control(
			'safe_text_color',
			[
				'label' => __( 'Color Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#4CAF50',
			]
		);

        // Estilos para estado ALERTA
         $this->add_control(
			'heading_alert',
			[
				'label' => __( 'Estado: Alerta', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        $this->add_control(
			'alert_color',
			[
				'label' => __( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => 'rgba(244, 67, 54, 0.1)',
			]
		);
        
        $this->add_control(
			'alert_text_color',
			[
				'label' => __( 'Color Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#F44336',
			]
		);


		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
        $failed_logins = Admin_Dashboard_Stats::get_failed_logins_today();
        
        $is_alert = $failed_logins > 0;
        
        if ( $is_alert ) {
            $bg_color   = $settings['alert_color'];
            $text_color = $settings['alert_text_color'];
            $icon       = 'eicon-warning';
            $message    = str_replace( '{count}', $failed_logins, $settings['alert_message'] );
            $border_color = '#F44336';
        } else {
            $bg_color   = $settings['safe_color'];
            $text_color = $settings['safe_text_color'];
            $icon       = 'eicon-check-circle-o';
            $message    = $settings['safe_message'];
            $border_color = '#4CAF50';
        }

		?>
		<div class="alezux-security-status">
            <div class="security-icon">
                <i class="<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></i>
            </div>
            <div class="security-content">
                <h4 class="security-title"><?php echo $is_alert ? 'Alerta de Seguridad' : 'Sistema Seguro'; ?></h4>
                <p class="security-desc"><?php echo esc_html( $message ); ?></p>
            </div>
            <?php if ( $is_alert ) : ?>
             <div class="security-action">
                <a href="<?php echo admin_url('users.php'); ?>" class="check-logs-btn">Revisar</a>
            </div>
            <?php endif; ?>
		</div>
        
        <style>
            .alezux-security-status {
                display: flex;
                align-items: center;
                gap: 20px;
                padding: 20px 25px;
                background: <?php echo $bg_color; ?>;
                border: 1px solid <?php echo $border_color; ?>;
                border-radius: 12px;
                color: <?php echo $text_color; ?>;
                backdrop-filter: blur(5px);
            }
            .security-icon {
                font-size: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .security-content {
                flex-grow: 1;
            }
            .security-title {
                margin: 0 0 5px;
                font-size: 18px;
                font-weight: 700;
                color: inherit;
            }
            .security-desc {
                margin: 0;
                font-size: 14px;
                opacity: 0.9;
                color: inherit;
            }
            .check-logs-btn {
                background: <?php echo $text_color; ?>;
                color: #fff;
                padding: 8px 16px;
                border-radius: 6px;
                text-decoration: none;
                font-size: 13px;
                font-weight: 600;
                transition: opacity 0.3s;
            }
            .check-logs-btn:hover {
                opacity: 0.8;
                color: #fff;
            }
        </style>
		<?php
	}
}
