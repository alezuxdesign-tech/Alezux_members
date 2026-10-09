<?php
namespace Alezux_Members\Modules\Smtp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Módulo para configurar el envío de correos vía SMTP con Hostinger
 */
class Smtp {

	public function init() {
		// Enganchar a phpmailer_init para sobrescribir la configuración de envío
		add_action( 'phpmailer_init', [ $this, 'configure_smtp' ], 999 );
		
		// Forzar el remitente por defecto para todos los correos de WordPress
		add_filter( 'wp_mail_from', [ $this, 'set_wp_mail_from' ] );
		add_filter( 'wp_mail_from_name', [ $this, 'set_wp_mail_from_name' ] );
	}

	/**
	 * Configura el objeto PHPMailer con las credenciales de Hostinger.
	 *
	 * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer
	 */
	public function configure_smtp( $phpmailer ) {
		$phpmailer->isSMTP();
		
		// Configuración de Hostinger
		$phpmailer->Host       = 'smtp.hostinger.com';
		$phpmailer->SMTPAuth   = true;
		$phpmailer->Port       = 465;
		$phpmailer->SMTPSecure = 'ssl'; // Hostinger requiere SSL para el puerto 465
		
		// Credenciales proporcionadas
		$phpmailer->Username   = 'academia@cdibusinessschool.com';
		$phpmailer->Password   = 'Wordpressacademia123#';
		
		// Forzar remitente en el propio objeto phpmailer
		$phpmailer->From       = 'academia@cdibusinessschool.com';
		$phpmailer->FromName   = 'CDI Business School';
	}

	/**
	 * Sobrescribe el correo remitente por defecto de WordPress
	 */
	public function set_wp_mail_from( $original_email_address ) {
		return 'academia@cdibusinessschool.com';
	}

	/**
	 * Sobrescribe el nombre del remitente por defecto de WordPress
	 */
	public function set_wp_mail_from_name( $original_email_from ) {
		return 'CDI Business School';
	}
}
