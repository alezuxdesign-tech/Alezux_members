<?php
namespace Alezux_Members\Modules\Demo_Block\Widgets;

use Alezux_Members\Core\Elementor_Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Demo_Block_Widget extends Elementor_Widget_Base {

	public function get_name() {
		return 'alezux_demo_block';
	}

	public function get_title() {
		return esc_html__( 'Demo Block Lego', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-code';
	}

	public function get_categories() {
		return [ 'alezux-lms' ];
	}

	protected function register_widget_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Contenido', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title_text',
			[
				'label' => esc_html__( 'Título', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Título por defecto', 'alezux-members' ),
				'placeholder' => esc_html__( 'Escribe tu título aquí', 'alezux-members' ),
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		echo '<div class="alezux-widget-container">';
		echo '<h3 class="alezux-demo-title">' . esc_html( $settings['title_text'] ) . '</h3>';
		echo '<p>Renderizado desde Elementor Widget (Lego Style)</p>';
		echo '</div>';
	}
}
