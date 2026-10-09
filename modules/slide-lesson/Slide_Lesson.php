<?php
namespace Alezux_Members\Modules\Slide_Lesson;

use Alezux_Members\Core\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Slide_Lesson extends Module_Base {

	public function init() {
		// Encolar estilos específicos del módulo
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		
		// Registrar Widgets de Elementor
		add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widgets' ] );
	}

	public function enqueue_assets() {
		// CSS
		wp_enqueue_style( 
			'alezux-slide-lesson-css', 
			$this->get_asset_url( 'assets/css/slide-lesson.css' ), 
			[], 
			time() // Forzar recarga para debugging
		);

		// JS
		wp_register_script( 
			'alezux-slide-lesson-js', 
			$this->get_asset_url( 'assets/js/slide-lesson.js' ), 
			[], 
			time(), // Forzar recarga para debugging
			true 
		);
	}

	public function register_elementor_widgets( $widgets_manager ) {
		require_once __DIR__ . '/widgets/Slide_Lesson_Widget.php';
		$widgets_manager->register( new \Alezux_Members\Modules\Slide_Lesson\Widgets\Slide_Lesson_Widget() );
	}


}
