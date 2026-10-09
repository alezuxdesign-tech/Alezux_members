<?php
namespace Alezux_Members\Modules\Lesson_Navigator;

use Alezux_Members\Core\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Lesson_Navigator extends Module_Base {

	public function init() {
		// Encolar estilos específicos del módulo
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		
		// Registrar Widgets de Elementor
		add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widgets' ] );
	}

	public function enqueue_assets() {
		wp_enqueue_style( 
			'alezux-lesson-navigator-css', 
			$this->get_asset_url( 'assets/css/lesson-navigator.css' ), 
			[], 
			time() // Forzar recarga para debugging
		);
	}

	public function register_elementor_widgets( $widgets_manager ) {
		require_once __DIR__ . '/widgets/Lesson_Navigator_Widget.php';
		$widgets_manager->register( new \Alezux_Members\Modules\Lesson_Navigator\Widgets\Lesson_Navigator_Widget() );
	}

}
