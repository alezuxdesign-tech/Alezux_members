<?php
namespace Alezux_Members\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Class Module_Base
 * Base para todos los módulos "Lego".
 */
abstract class Module_Base {

	public function __construct() {
		// Constructor vacío por defecto
	}

	/**
	 * Punto de entrada del módulo.
	 * Debe ser implementado por cada módulo para registrar sus hooks.
	 */
	abstract public function init();

	/**
	 * Utilidad para obtener la URL de assets dentro del módulo actual.
	 * 
	 * @param string $path Ruta relativa al asset dentro de la carpeta del módulo.
	 * @return string URL completa del asset.
	 */
	protected function get_asset_url( $path ) {
		// Obtener la clase hija para saber en qué directorio estamos
		$reflector = new \ReflectionClass( $this );
		$module_dir = dirname( $reflector->getFileName() );
		
		// Convertir ruta de sistema a URL
		// Nota: Esto asume que el módulo está dentro de la carpeta de plugins
		$url = plugin_dir_url( $reflector->getFileName() ) . ltrim( $path, '/' );
		
		return $url;
	}

	/**
	 * Utilidad para renderizar una vista (template).
	 * 
	 * @param string $view_name Nombre del archivo de vista (sin .php).
	 * @param array $args Argumentos para pasar a la vista.
	 */
	protected function render_view( $view_name, $args = [] ) {
		$reflector = new \ReflectionClass( $this );
		$module_dir = dirname( $reflector->getFileName() );
		$view_path = $module_dir . '/views/' . $view_name . '.php';

		if ( file_exists( $view_path ) ) {
			extract( $args );
			include $view_path;
		} else {
			echo "<!-- Vista no encontrada: {$view_name} -->";
		}
	}

	/**
	 * Almacén estático de shortcodes para documentación.
	 */
	protected static $registered_shortcodes = [];

	/**
	 * Registrar un shortcode y documentarlo.
	 * 
	 * @param string $tag El tag del shortcode.
	 * @param callable $callback La función callback.
	 * @param string $description Descripción para el dashboard.
	 */
	protected function register_shortcode( $tag, $callback, $description = '' ) {
		add_shortcode( $tag, $callback );
		self::$registered_shortcodes[] = [
			'tag'         => $tag,
			'description' => $description,
			'module'      => ( new \ReflectionClass( $this ) )->getShortName(),
		];
	}

	public static function get_registered_shortcodes() {
		return self::$registered_shortcodes;
	}
}

