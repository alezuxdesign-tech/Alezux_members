<?php
namespace Alezux_Members\Modules\Notifications\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Notifications_Widget extends Widget_Base {

	public function get_name() {
		return 'notifications';
	}

	public function get_title() {
		return __( 'Notificaciones', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-flash';
	}

	public function get_categories() {
		return [ 'alezux-lms' ];
	}

	public function get_style_depends() {
		return [ 'alezux-notifications-css' ];
	}

	public function get_script_depends() {
		return [ 'alezux-notifications-js' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Contenido', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title',
			[
				'label' => __( 'Título', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Notificaciones', 'alezux-members' ),
			]
		);

		$this->add_control(
			'notification_icon',
			[
				'label' => __( 'Ícono', 'alezux-members' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-bell',
					'library' => 'fa-solid',
				],
			]
		);

		$this->end_controls_section();

		// Labels Section
		$this->start_controls_section(
			'section_labels',
			[
				'label' => __( 'Etiquetas', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'label_mark_all_read',
			[
				'label' => __( 'Marcar todo como leído', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Marcar todo como leído', 'alezux-members' ),
			]
		);



		$this->add_control(
			'label_loading',
			[
				'label' => __( 'Texto Cargando', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Cargando...', 'alezux-members' ),
			]
		);

		$this->add_control(
			'label_no_notifications',
			[
				'label' => __( 'Texto Sin Notificaciones', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Sin notificaciones', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

		// Style Section
		$this->start_controls_section(
			'section_style_icon',
			[
				'label' => __( 'Ícono', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label'     => __( 'Color', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-bell-icon i' => 'color: {{VALUE}}',
					'{{WRAPPER}} .alezux-bell-icon svg' => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'icon_bg_color',
			[
				'label'     => __( 'Color de Fondo', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-bell-icon' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label'      => __( 'Tamaño', 'alezux-members' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .alezux-bell-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .alezux-bell-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .alezux-bell-icon img' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_padding',
			[
				'label'      => __( 'Relleno (Padding)', 'alezux-members' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .alezux-bell-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'icon_border_radius',
			[
				'label'      => __( 'Radio del Borde', 'alezux-members' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .alezux-bell-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'badge_bg_color',
			[
				'label'     => __( 'Color Fondo de Alerta', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-notification-badge' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
				],
				'condition' => [
					'notification_icon[value]!' => '',
				],
			]
		);

		$this->add_control(
			'badge_text_color',
			[
				'label'     => __( 'Color Texto de Alerta', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-notification-badge' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();

		// Dropdown Style Section
		$this->start_controls_section(
			'section_style_dropdown',
			[
				'label' => __( 'Dropdown / Panel', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'dropdown_bg_color',
			[
				'label'     => __( 'Color de Fondo', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-notifications-dropdown' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .alezux-dropdown-header' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .alezux-notifications-list' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'accordion_accent_color',
			[
				'label'     => __( 'Color de Acento', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-tab.active' => 'color: {{VALUE}}; border-bottom-color: {{VALUE}};',
					'{{WRAPPER}} .alezux-tab.active .badge-count' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .alezux-notification-item.unread::after' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_heading',
			[
				'label' => __( 'Items de Notificación', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'item_bg_color',
			[
				'label'     => __( 'Color Fondo Item', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-notification-item' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_hover_bg_color',
			[
				'label'     => __( 'Color Fondo Item (Hover)', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-notification-item:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'unread_heading',
			[
				'label' => __( 'Notificaciones No Leídas', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'unread_bg_color',
			[
				'label'     => __( 'Color Fondo No Leído', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-notification-item.unread' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'unread_dot_color',
			[
				'label'     => __( 'Color Punto de Alerta', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-notification-item.unread::after' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_typography',
			[
				'label' => __( 'Tipografía y Colores', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		// Header Title
		$this->add_control(
			'heading_title_style',
			[
				'label' => __( 'Título', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => __( 'Color', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-dropdown-header h3' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .alezux-dropdown-header h3',
			]
		);

		// Mark All Read
		$this->add_control(
			'heading_mark_read_style',
			[
				'label' => __( 'Marcar todo como leído', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'mark_read_color',
			[
				'label'     => __( 'Color', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-mark-all-read' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'mark_read_typography',
				'selector' => '{{WRAPPER}} .alezux-mark-all-read',
			]
		);

		// Notification Item Title
		$this->add_control(
			'heading_notif_title_style',
			[
				'label' => __( 'Título de Notificación', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'notif_title_color',
			[
				'label'     => __( 'Color', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .notif-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'notif_title_typography',
				'selector' => '{{WRAPPER}} .notif-title',
			]
		);

		// Notification Item Message
		$this->add_control(
			'heading_notif_message_style',
			[
				'label' => __( 'Mensaje de Notificación', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'notif_message_color',
			[
				'label'     => __( 'Color', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .notif-message' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'notif_message_typography',
				'selector' => '{{WRAPPER}} .notif-message',
			]
		);

		// Notification Meta
		$this->add_control(
			'heading_notif_meta_style',
			[
				'label' => __( 'Fecha/Meta de Notificación', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'notif_meta_color',
			[
				'label'     => __( 'Color', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .notif-meta' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'notif_meta_typography',
				'selector' => '{{WRAPPER}} .notif-meta',
			]
		);

		// Loading / Empty
		$this->add_control(
			'heading_status_text_style',
			[
				'label' => __( 'Textos de Estado (Cargando/Vacío)', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'status_text_color',
			[
				'label'     => __( 'Color', 'alezux-members' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-loading' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-no-notifications' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'status_text_typography',
				'selector' => '{{WRAPPER}} .alezux-loading, {{WRAPPER}} .alezux-no-notifications',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<div class="alezux-notifications-widget">
			<div class="alezux-bell-icon">
				<?php \Elementor\Icons_Manager::render_icon( $settings['notification_icon'], [ 'aria-hidden' => 'true' ] ); ?>
				<span class="alezux-notification-badge" style="display:none;">0</span>
			</div>
			
			<div class="alezux-notifications-dropdown">
				<div class="alezux-dropdown-header">
					<h3><?php echo esc_html( $settings['title'] ); ?></h3>
					<span class="alezux-mark-all-read"><?php echo esc_html( $settings['label_mark_all_read'] ); ?></span>
				</div>
				
				<div class="alezux-notifications-list" id="alezux-notif-list-inbox">
					<!-- Notifications loaded via AJAX -->
					<div class="alezux-loading"><?php echo esc_html( $settings['label_loading'] ); ?></div>
				</div>
			</div>
		</div>
		<?php
	}
}
