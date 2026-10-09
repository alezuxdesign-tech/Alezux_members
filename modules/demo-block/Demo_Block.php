<?php
namespace Alezux_Members\Modules\Demo_Block;

use Alezux_Members\Core\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Demo_Block extends Module_Base {

	public function init() {
		// Encolar estilos/scripts específicos del módulo

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		
		// Registrar Widgets de Elementor
		add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widgets' ] );
	}

	public function enqueue_assets() {
		wp_enqueue_style( 
			'alezux-demo-block-css', 
			$this->get_asset_url( 'assets/css/demo-block.css' ), 
			[], 
			ALEZUX_MEMBERS_VERSION 
		);
	}

	public function register_elementor_widgets( $widgets_manager ) {
		// Requerir el archivo del widget
		require_once __DIR__ . '/widgets/Demo_Block_Widget.php';
		
		// Registrar el widget
		$widgets_manager->register( new \Alezux_Members\Modules\Demo_Block\Widgets\Demo_Block_Widget() );
	}


}
