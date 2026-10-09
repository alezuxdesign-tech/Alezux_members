<?php
namespace Alezux_Members\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Plugin_Loader
 * Maneja la carga dinámica de módulos.
 */
class Plugin_Loader {

	private $modules = [];

	public function run() {
		$this->load_modules();
		$this->init_modules();
	}

	/**
	 * Escanea el directorio de módulos y carga los archivos principales.
	 */
	private function load_modules() {
        $log_file = ALEZUX_MEMBERS_PATH . 'debug_status.txt';
		if ( ! is_dir( ALEZUX_MEMBERS_MODULES_PATH ) ) {
            file_put_contents( $log_file, "ERROR: Directorio de módulos no encontrado: " . ALEZUX_MEMBERS_MODULES_PATH . "\n", FILE_APPEND );
			return;
		}

		$dirs = glob( ALEZUX_MEMBERS_MODULES_PATH . '*', GLOB_ONLYDIR );
        file_put_contents( $log_file, "Buscando módulos en: " . ALEZUX_MEMBERS_MODULES_PATH . " (Encontrados: " . count($dirs) . ")\n", FILE_APPEND );

		foreach ( $dirs as $dir ) {
			$dirname = basename( $dir );
			$class_name_suffix = str_replace( '-', '_', ucwords( $dirname, '-' ) ); 
			$file_path = $dir . '/' . $class_name_suffix . '.php';

            file_put_contents( $log_file, "Intentando cargar módulo: $dirname -> Buscando archivo: $file_path\n", FILE_APPEND );

			if ( file_exists( $file_path ) ) {
				require_once $file_path;
				$full_class_name = "\\Alezux_Members\\Modules\\{$class_name_suffix}\\{$class_name_suffix}";

				if ( class_exists( $full_class_name ) ) {
					$this->modules[ $dirname ] = new $full_class_name();
                    file_put_contents( $log_file, "ÉXITO: Módulo $dirname cargado ($full_class_name).\n", FILE_APPEND );
				} else {
                    file_put_contents( $log_file, "ERROR: La clase $full_class_name no existe en $file_path\n", FILE_APPEND );
                }
			} else {
                file_put_contents( $log_file, "ADVERTENCIA: Archivo no encontrado para el módulo $dirname en $file_path\n", FILE_APPEND );
            }
		}
	}

	/**
	 * Inicializa todos los módulos cargados.
	 */
	private function init_modules() {
		foreach ( $this->modules as $module ) {
			if ( method_exists( $module, 'init' ) ) {
				$module->init();
			}
		}
	}
}
