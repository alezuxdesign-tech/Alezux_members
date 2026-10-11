<?php
namespace Alezux_Members\Modules\Marketing\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Default_Templates {

	/**
	 * Obtiene una plantilla de correo por tipo, adaptada al tema Arc seleccionado.
	 *
	 * @param string      $type  Identificador de la plantilla.
	 * @param string|null $theme 'clean' (híbrido/claro) o 'dark' (oscuro premium). Por defecto lee el ajuste del sitio.
	 * @return array{subject: string, content: string}
	 */
	public static function get( $type, $theme = null ) {
		if ( empty( $theme ) ) {
			$theme = function_exists( 'get_option' ) ? get_option( 'alezux_marketing_email_theme', 'clean' ) : 'clean';
		}
		if ( ! in_array( $theme, [ 'clean', 'dark' ], true ) ) {
			$theme = 'clean';
		}

		$is_dark = ( 'dark' === $theme );

		$colors = [
			'body_bg'        => $is_dark ? '#0c0d14' : '#f3f4f8',
			'card_bg'        => $is_dark ? '#141523' : '#ffffff',
			'card_border'    => $is_dark ? '#222538' : '#e6e8ee',
			'text_primary'   => $is_dark ? '#f8fafc' : '#0f172a',
			'text_secondary' => $is_dark ? '#94a3b8' : '#475569',
			'text_muted'     => $is_dark ? '#64748b' : '#94a3b8',
			'box_bg'         => $is_dark ? '#191b2c' : '#f8fafc',
			'box_border'     => $is_dark ? '#292c44' : '#e2e8f0',
			'accent'         => '#7747ff',
			'badge_bg'       => $is_dark ? 'rgba(119,71,255,0.18)' : '#f0ebff',
			'badge_text'     => $is_dark ? '#c7b7ff' : '#6d28d9',
			'badge_border'   => $is_dark ? 'rgba(119,71,255,0.3)' : '#ddd6fe',
			'code_bg'        => $is_dark ? '#10111a' : '#f1f3f9',
			'code_border'    => $is_dark ? '#282b42' : '#d5daf0',
			'footer_text'    => $is_dark ? '#6b7280' : '#8e8ea0',
			'divider'        => $is_dark ? '#202235' : '#edf0f6',
		];

		$header = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{{site_name}}</title>
<style>
  body { margin: 0; padding: 0; background-color: ' . $colors['body_bg'] . '; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Inter", Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
  table { border-collapse: separate; }
  a { text-decoration: none; }
  @media only screen and (max-width: 620px) {
    .arc-wrapper { padding: 16px 8px !important; }
    .arc-card { padding: 26px 20px !important; border-radius: 16px !important; }
    .arc-btn { display: block !important; width: 100% !important; box-sizing: border-box !important; text-align: center !important; }
    .arc-data-td { display: block !important; width: 100% !important; padding: 6px 0 !important; }
  }
</style>
</head>
<body style="margin: 0; padding: 0; background-color: ' . $colors['body_bg'] . '; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Inter\', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: ' . $colors['body_bg'] . '; width: 100%; margin: 0; padding: 36px 12px;" class="arc-wrapper">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 580px; width: 100%; margin: 0 auto;">
          <!-- Cabecera Logotipo Arc -->
          <tr>
            <td align="center" style="padding-bottom: 24px;">
              <a href="{{home_url}}" style="text-decoration: none; display: inline-block;">
                <img src="{{logo_url}}" alt="{{site_name}}" style="max-height: 44px; max-width: 200px; height: auto; border: 0; display: block; margin: 0 auto; outline: none;" />
              </a>
            </td>
          </tr>
          <!-- Tarjeta Principal Arc -->
          <tr>
            <td class="arc-card" style="background-color: ' . $colors['card_bg'] . '; border: 1px solid ' . $colors['card_border'] . '; border-radius: 20px; padding: 40px 36px; box-shadow: ' . ( $is_dark ? '0 12px 40px rgba(0,0,0,0.45)' : '0 10px 30px rgba(0,0,0,0.04)' ) . ';">
';

		$footer = '
            </td>
          </tr>
          <!-- Pie de Página Arc -->
          <tr>
            <td align="center" style="padding-top: 28px; padding-bottom: 20px;">
              <p style="margin: 0 0 6px; font-size: 12px; font-weight: 500; color: ' . $colors['footer_text'] . '; line-height: 1.5;">
                &copy; {{year}} <strong>{{site_name}}</strong>. Todos los derechos reservados.
              </p>
              <p style="margin: 0; font-size: 11px; color: ' . $colors['footer_text'] . '; line-height: 1.5; opacity: 0.85;">
                Has recibido este correo electrónico porque eres miembro registrado en nuestra plataforma.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
';

		// Helpers de componentes Arc para el cuerpo
		$badge = function( $text, $custom_bg = null, $custom_color = null, $custom_border = null ) use ( $colors ) {
			$bg = $custom_bg ? $custom_bg : $colors['badge_bg'];
			$color = $custom_color ? $custom_color : $colors['badge_text'];
			$border = $custom_border ? $custom_border : $colors['badge_border'];
			return '<div style="margin-bottom: 18px;"><span style="display: inline-block; background-color: ' . $bg . '; color: ' . $color . '; border: 1px solid ' . $border . '; border-radius: 9999px; padding: 4px 14px; font-size: 12px; font-weight: 600; letter-spacing: 0.02em;">' . $text . '</span></div>';
		};

		$title = function( $text ) use ( $colors ) {
			return '<h1 style="margin: 0 0 14px 0; font-size: 24px; font-weight: 700; color: ' . $colors['text_primary'] . '; line-height: 1.25; letter-spacing: -0.02em;">' . $text . '</h1>';
		};

		$paragraph = function( $text ) use ( $colors ) {
			return '<p style="margin: 0 0 16px 0; font-size: 15px; color: ' . $colors['text_secondary'] . '; line-height: 1.65;">' . $text . '</p>';
		};

		$button = function( $text, $url, $custom_accent = null ) use ( $colors ) {
			$bg = $custom_accent ? $custom_accent : $colors['accent'];
			return '
				<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin: 28px auto 12px auto;">
					<tr>
						<td align="center" style="border-radius: 9999px; background-color: ' . $bg . '; box-shadow: 0 4px 14px rgba(119,71,255,0.35);">
							<a href="' . $url . '" class="arc-btn" style="background-color: ' . $bg . '; color: #ffffff !important; font-size: 14px; font-weight: 600; text-decoration: none; padding: 13px 32px; border-radius: 9999px; display: inline-block; letter-spacing: 0.01em;">
								' . $text . '
							</a>
						</td>
					</tr>
				</table>
			';
		};

		$box_start = function( $title_text = '' ) use ( $colors ) {
			$header_html = '';
			if ( ! empty( $title_text ) ) {
				$header_html = '<div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: ' . $colors['accent'] . '; margin-bottom: 12px;">' . $title_text . '</div>';
			}
			return '
				<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: ' . $colors['box_bg'] . '; border: 1px solid ' . $colors['box_border'] . '; border-radius: 14px; margin: 22px 0; overflow: hidden;">
					<tr>
						<td style="padding: 20px 22px;">
							' . $header_html;
		};

		$box_end = function() {
			return '
						</td>
					</tr>
				</table>
			';
		};

		$row = function( $label, $value, $is_code = false, $is_last = false ) use ( $colors ) {
			$val_html = $value;
			if ( $is_code ) {
				$val_html = '<span style="display: inline-block; background-color: ' . $colors['code_bg'] . '; border: 1px solid ' . $colors['code_border'] . '; padding: 3px 10px; border-radius: 6px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 13px; font-weight: 700; color: ' . $colors['accent'] . ';">' . $value . '</span>';
			}
			$border_bottom = $is_last ? '' : 'border-bottom: 1px solid ' . $colors['divider'] . ';';
			return '
				<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0; padding: 0;">
					<tr>
						<td class="arc-data-td" style="padding: 9px 0; font-size: 13px; font-weight: 500; color: ' . $colors['text_muted'] . '; width: 36%; vertical-align: middle; ' . $border_bottom . '">
							' . $label . '
						</td>
						<td class="arc-data-td" style="padding: 9px 0; font-size: 14px; font-weight: 600; color: ' . $colors['text_primary'] . '; width: 64%; vertical-align: middle; text-align: right; ' . $border_bottom . '">
							' . $val_html . '
						</td>
					</tr>
				</table>
			';
		};

		switch ( $type ) {
			case 'student_welcome':
				return [
					'subject' => '¡Bienvenido a {{site_name}}! Tus credenciales de acceso',
					'content' => $header .
						$badge( '🎉 ¡Cuenta Creada Exitosamente!' ) .
						$title( '¡Hola, {{user.first_name}}!' ) .
						$paragraph( 'Nos emociona darte la bienvenida a <strong>{{site_name}}</strong>. Tu acceso a la plataforma ya se encuentra activo y tienes todo preparado para empezar a aprender.' ) .
						$box_start( '🔑 Tus Credenciales de Acceso' ) .
						$row( 'Usuario / Correo', '{{user.username}}' ) .
						$row( 'Contraseña Temporal', '{{password}}', true ) .
						$row( 'Curso o Membresía', '{{course_title}}', false, true ) .
						$box_end() .
						$paragraph( 'Por motivos de seguridad, te sugerimos actualizar tu contraseña después de tu primer inicio de sesión.' ) .
						$button( 'Acceder al Campus Virtual &rarr;', '{{login_url}}' ) .
						$footer
				];

			case 'user_recover_password':
				return [
					'subject' => 'Recuperación de Contraseña - {{site_name}}',
					'content' => $header .
						$badge( '🔐 Seguridad de la Cuenta' ) .
						$title( '¿Olvidaste tu contraseña?' ) .
						$paragraph( 'Hola <strong>{{user.name}}</strong>, hemos recibido una solicitud para restablecer la contraseña asociada a tu cuenta en <strong>{{site_name}}</strong>.' ) .
						$paragraph( 'Para crear una nueva contraseña y recuperar tu acceso, haz clic en el botón siguiente:' ) .
						$button( 'Restablecer Mi Contraseña &rarr;', '{{reset_link}}' ) .
						$box_start() .
						$paragraph( '⚠️ Si no solicitaste este cambio, puedes ignorar este mensaje con tranquilidad. Tu contraseña actual continuará siendo totalmente segura.' ) .
						$box_end() .
						$footer
				];

			case 'admin_reset_password':
				return [
					'subject' => 'Tus credenciales de acceso han sido actualizadas',
					'content' => $header .
						$badge( '🛡️ Actualización de Seguridad' ) .
						$title( 'Nueva contraseña asignada' ) .
						$paragraph( 'Hola <strong>{{user.name}}</strong>, un administrador de la plataforma ha restablecido las credenciales de tu cuenta.' ) .
						$box_start( 'Nuevos Datos de Acceso' ) .
						$row( 'Usuario', '{{user.email}}' ) .
						$row( 'Nueva Contraseña', '{{new_password}}', true, true ) .
						$box_end() .
						$paragraph( 'Te recomendamos cambiar esta contraseña desde los ajustes de tu perfil una vez que inicies sesión.' ) .
						$button( 'Ingresar al Campus &rarr;', '{{login_url}}' ) .
						$footer
				];

			case 'payment_success':
				return [
					'subject' => '¡Pago Confirmado! - {{plan_name}}',
					'content' => $header .
						$badge( '✓ Pago Procesado con Éxito', ( $is_dark ? 'rgba(16,185,129,0.18)' : '#ecfdf5' ), ( $is_dark ? '#6ee7b7' : '#059669' ), ( $is_dark ? 'rgba(16,185,129,0.3)' : '#a7f3d0' ) ) .
						$title( '¡Gracias por tu compra, {{user.first_name}}!' ) .
						$paragraph( 'Hemos procesado correctamente tu pago para la suscripción <strong>{{plan_name}}</strong>. Tu acceso a los materiales y lecciones ya está disponible.' ) .
						$box_start( 'Comprobante de Transacción' ) .
						$row( 'Plan Adquirido', '{{plan_name}}' ) .
						$row( 'Monto Pagado', '{{amount}}' ) .
						$row( 'Fecha', '{{date}}' ) .
						$row( 'Referencia', '{{payment.ref}}', true, true ) .
						$box_end() .
						$button( 'Ir a Mis Cursos &rarr;', '{{home_url}}' ) .
						$footer
				];

			case 'payment_failed':
				return [
					'subject' => 'Acción Requerida: Problema con tu pago de {{plan_name}}',
					'content' => $header .
						$badge( '⚠️ Alerta de Pago', ( $is_dark ? 'rgba(239,68,68,0.18)' : '#fef2f2' ), ( $is_dark ? '#fca5a5' : '#dc2626' ), ( $is_dark ? 'rgba(239,68,68,0.3)' : '#fecaca' ) ) .
						$title( 'No pudimos procesar tu pago' ) .
						$paragraph( 'Hola <strong>{{user.first_name}}</strong>, intentamos procesar la renovación o cuota correspondiente a tu plan <strong>{{plan_name}}</strong>, pero la transacción no pudo completarse.' ) .
						$box_start( 'Detalles del Intento' ) .
						$row( 'Plan', '{{plan_name}}' ) .
						$row( 'Fecha del intento', '{{attempt_date}}' ) .
						$row( 'Estado', '<span style="color: #ef4444; font-weight: 700;">Rechazado</span>', false, true ) .
						$box_end() .
						$paragraph( 'Por favor actualiza tu método de pago para evitar la interrupción de tu acceso al campus y a tus clases.' ) .
						$button( 'Actualizar Método de Pago &rarr;', '{{retry_url}}', '#ef4444' ) .
						$footer
				];

			case 'payment_reminder':
				return [
					'subject' => 'Recordatorio: Próxima cuota de {{plan_name}} - {{site_name}}',
					'content' => $header .
						$badge( '📅 Aviso de Renovación' ) .
						$title( 'Próxima cuota programada' ) .
						$paragraph( 'Hola <strong>{{user.name}}</strong>, te recordamos que en los próximos días se procesará automáticamente la siguiente cuota de tu membresía <strong>{{plan_name}}</strong>.' ) .
						$box_start( 'Resumen del Cobro' ) .
						$row( 'Membresía', '{{plan_name}}' ) .
						$row( 'Monto estimado', '{{price}}' ) .
						$row( 'Fecha programada', '{{renewal_date}}', false, true ) .
						$box_end() .
						$paragraph( 'Asegúrate de contar con fondos disponibles en tu método de pago registrado para continuar tu formación sin interrupciones.' ) .
						$button( 'Ver Mi Cuenta &rarr;', '{{home_url}}' ) .
						$footer
				];

			case 'subscription_cancelled':
				return [
					'subject' => 'Confirmación de Suscripción Cancelada - {{plan_name}}',
					'content' => $header .
						$badge( '📋 Notificación de Suscripción' ) .
						$title( 'Suscripción cancelada' ) .
						$paragraph( 'Hola <strong>{{user.name}}</strong>, confirmamos que la solicitud de cancelación para tu membresía <strong>{{plan_name}}</strong> ha sido procesada.' ) .
						$box_start( 'Vigencia de Acceso' ) .
						$row( 'Plan', '{{plan_name}}' ) .
						$row( 'Acceso activo hasta', '{{end_date}}', false, true ) .
						$box_end() .
						$paragraph( 'Podrás seguir accediendo a todos los cursos y contenidos hasta la fecha indicada. Nuestras puertas siempre estarán abiertas para cuando desees regresar.' ) .
						$button( 'Ir a la Plataforma &rarr;', '{{home_url}}' ) .
						$footer
				];

			case 'achievement_assigned':
				return [
					'subject' => '¡Ganaste un nuevo Logro! 🏆 {{achievement_name}}',
					'content' => $header .
						$badge( '🏆 ¡Nuevo Logro Desbloqueado!', ( $is_dark ? 'rgba(245,158,11,0.18)' : '#fffbeb' ), ( $is_dark ? '#fcd34d' : '#d97706' ), ( $is_dark ? 'rgba(245,158,11,0.3)' : '#fde68a' ) ) .
						$title( '¡Felicidades, {{user.first_name}}!' ) .
						$paragraph( 'Tu constancia y disciplina en la academia están dando frutos extraordinarios. Has obtenido una nueva insignia:' ) .
						$box_start() .
						'<div style="text-align: center; padding: 8px 0;">
							<img src="{{achievement.image}}" width="84" height="84" alt="Logro" style="border-radius: 50%; box-shadow: 0 4px 16px rgba(245,158,11,0.3); display: inline-block; margin-bottom: 12px;" />
							<h3 style="margin: 0 0 6px 0; font-size: 18px; font-weight: 700; color: ' . $colors['text_primary'] . ';">{{achievement_name}}</h3>
							<p style="margin: 0; font-size: 14px; color: ' . $colors['text_secondary'] . '; line-height: 1.5;">{{achievement_desc}}</p>
						</div>' .
						$box_end() .
						$button( 'Ver Mis Logros &rarr;', '{{home_url}}/logros' ) .
						$footer
				];

			case 'course_available':
				return [
					'subject' => '🚀 ¡Nuevo Curso Lanzado: {{course_name}}!',
					'content' => $header .
						$badge( '🚀 Nuevo Lanzamiento' ) .
						$title( '¡Tenemos novedades para ti!' ) .
						$paragraph( 'Hola <strong>{{user.name}}</strong>, nos emociona anunciarte que hay nuevo contenido formativo disponible en <strong>{{site_name}}</strong>:' ) .
						$box_start( '📚 Nuevo Contenido Formativo' ) .
						'<div style="font-size: 16px; font-weight: 700; color: ' . $colors['text_primary'] . '; margin-bottom: 8px;">{{course_name}}</div>' .
						'<div style="font-size: 14px; color: ' . $colors['text_secondary'] . '; line-height: 1.6;">{{courses_list}}</div>' .
						$box_end() .
						$paragraph( 'Aprovecha este contenido actualizado para seguir perfeccionando tus habilidades profesionales.' ) .
						$button( 'Explorar Curso Ahora &rarr;', '{{home_url}}' ) .
						$footer
				];

			case 'lesson_available':
				return [
					'subject' => 'Nuevas clases disponibles en: {{course_name}}',
					'content' => $header .
						$badge( '✨ Clases Nuevas' ) .
						$title( '¡Hay nuevas lecciones listas!' ) .
						$paragraph( 'Hola <strong>{{user.name}}</strong>, se han incorporado nuevas lecciones al curso <strong>{{course_name}}</strong> en el cual estás inscrito:' ) .
						$box_start( '📖 Lecciones Publicadas' ) .
						'<div style="font-size: 14px; color: ' . $colors['text_secondary'] . '; line-height: 1.65;">{{lessons_list}}</div>' .
						$box_end() .
						$paragraph( '¡Mantén vivo tu ritmo de estudio y no dejes pasar la oportunidad de avanzar hoy!' ) .
						$button( 'Ir al Curso &rarr;', '{{home_url}}' ) .
						$footer
				];

			case 'inactivity_alert':
				return [
					'subject' => '¡Te extrañamos en {{site_name}}! Retoma tus metas',
					'content' => $header .
						$badge( '⚡ Impulsa tu Progreso' ) .
						$title( '¡Hola, {{user.name}}!' ) .
						$paragraph( 'Hemos notado que llevas <strong>{{days_inactive}} días</strong> sin ingresar a la plataforma.' ) .
						$paragraph( 'Recuerda que la consistencia es el factor determinante para dominar cualquier habilidad. Tienes lecciones pendientes que te esperan para continuar avanzando.' ) .
						$box_start() .
						'<p style="margin: 0; font-size: 14px; color: ' . $colors['accent'] . '; font-weight: 600; text-align: center;">🎯 Dedícale 15 minutos hoy y mantén tu impulso de aprendizaje.</p>' .
						$box_end() .
						$button( 'Continuar Aprendiendo &rarr;', '{{login_url}}' ) .
						$footer
				];

			case 'course_completed':
				return [
					'subject' => '🎓 ¡Felicitaciones por graduarte de {{course_title}}!',
					'content' => $header .
						$badge( '🎓 ¡Graduación Completada!', ( $is_dark ? 'rgba(119,71,255,0.18)' : '#f0ebff' ), ( $is_dark ? '#c7b7ff' : '#7747ff' ), ( $is_dark ? 'rgba(119,71,255,0.3)' : '#ddd6fe' ) ) .
						$title( '¡Felicitaciones, {{user.name}}! 🎉' ) .
						$paragraph( '¡Has alcanzado el 100% de progreso en el programa <strong>{{course_title}}</strong>!' ) .
						$paragraph( 'Tu constancia y dedicación han dado resultados admirables. Tu certificado oficial de finalización ya se encuentra generado y disponible para ser compartido en tu perfil profesional.' ) .
						$box_start( '🏅 Certificación Oficial' ) .
						$row( 'Programa', '{{course_title}}' ) .
						$row( 'Progreso Final', '100% Completado', false, true ) .
						$box_end() .
						$button( 'Descargar Mi Certificado &rarr;', '{{login_url}}' ) .
						$footer
				];

			default:
				return [
					'subject' => 'Notificación de {{site_name}}',
					'content' => $header .
						$badge( 'Notificación' ) .
						$title( 'Aviso Importante' ) .
						$paragraph( 'Tienes una nueva notificación de tu academia <strong>{{site_name}}</strong>.' ) .
						$button( 'Ir a la Plataforma &rarr;', '{{home_url}}' ) .
						$footer
				];
		}
	}
}
