<?php
namespace Alezux_Members\Modules\Menu_Admin;

use Alezux_Members\Core\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Menu_Admin extends Module_Base {

	public function init() {
		// Registrar Widgets de Elementor
		add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widgets' ] );
	}

	public function register_elementor_widgets( $widgets_manager ) {
		require_once __DIR__ . '/widgets/Menu_Admin_Widget.php';
		$widgets_manager->register( new \Alezux_Members\Modules\Menu_Admin\Widgets\Menu_Admin_Widget() );

		require_once __DIR__ . '/widgets/Menu_User_Widget.php';
		$widgets_manager->register( new \Alezux_Members\Modules\Menu_Admin\Widgets\Menu_User_Widget() );
	}

}
