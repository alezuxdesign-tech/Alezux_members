<?php
namespace Alezux_Members\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Asegurarse de que Elementor esté activo antes de extender su clase
if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
	return;
}

/**
 * Abstract Class Elementor_Widget_Base
 * Base para todos los widgets de Elementor del plugin.
 */
abstract class Elementor_Widget_Base extends \Elementor\Widget_Base {

	public function get_categories() {
		return [ 'alezux-members' ]; // Categoría personalizada para nuestros widgets
	}

	protected function register_controls() {
		// Aquí podríamos agregar controles globales de estilo (margenes, fondos, etc.) 
		// que queremos que todos nuestros widgets tengan por defecto.
		
		$this->start_controls_section(
			'section_global_style',
			[
				'label' => esc_html__( 'Estilos Globales Alezux', 'alezux-members' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		// Ejemplo: Un control de borde predeterminado para consistencia
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'global_border',
				'selector' => '{{WRAPPER}} .alezux-widget-container',
			]
		);

		$this->end_controls_section();

		// Llamar a los controles específicos del widget hijo
		$this->register_widget_controls();
	}

	/**
	 * Método abstracto para que los hijos registren sus propios controles.
	 * Reemplaza al register_controls estándar de Elementor para inyectar nuestros globales antes.
	 */
	abstract protected function register_widget_controls();
}
