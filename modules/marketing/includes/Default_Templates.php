<?php
namespace Alezux_Members\Modules\Marketing\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Default_Templates {

	public static function get( $type ) {
		$common_css = '
			<style>
				body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
				.container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
				.header { text-align: center; margin-bottom: 30px; }
				.header img { max-width: 150px; }
				.btn { display: inline-block; padding: 12px 24px; background: #0073aa; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold; }
				.footer { margin-top: 30px; font-size: 12px; color: #888; text-align: center; }
			</style>
		';
		
		$header = '
			<!DOCTYPE html>
			<html>
			<head>' . $common_css . '</head>
			<body>
				<div class="container">
					<div class="header">
						<img src="{{logo_url}}" alt="{{site_name}}">
					</div>
		';

		$footer = '
					<div class="footer">
						<p>&copy; {{year}} {{site_name}}. Todos los derechos reservados.</p>
					</div>
				</div>
			</body>
			</html>
		';

		switch ( $type ) {
			case 'student_welcome':
				return [
					'subject' => 'Bienvenido a {{site_name}} - Tus Credenciales',
					'content' => $header . '
						<h2>¡Hola {{user.first_name}}!</h2>
						<p>Tu cuenta ha sido creada exitosamente. Estamos emocionados de tenerte aquí.</p>
						<p>Aquí tienes tus datos de acceso:</p>
						<div style="background: #f9f9f9; padding: 15px; border-left: 4px solid #0073aa; margin: 20px 0;">
							<p><strong>Usuario:</strong> {{user.username}}</p>
							<p><strong>Contraseña:</strong> {{password}}</p>
							<p><strong>Curso:</strong> {{course_title}}</p>
						</div>
						<p style="text-align: center;">
							<a href="{{login_url}}" class="btn">Ingresar a la Plataforma</a>
						</p>
					' . $footer
				];

			case 'user_recover_password':
				return [
					'subject' => 'Recuperación de Contraseña - {{site_name}}',
					'content' => $header . '
						<h2>Recuperar Contraseña</h2>
						<p>Hola {{user.name}},</p>
						<p>Hemos recibido una solicitud para restablecer tu contraseña. Si no fuiste tú, ignora este mensaje.</p>
						<p style="text-align: center;">
							<a href="{{reset_link}}" class="btn">Restablecer Contraseña</a>
						</p>
					' . $footer
				];
			
			case 'admin_reset_password':
				return [
					'subject' => 'Tu contraseña ha sido restablecida',
					'content' => $header . '
						<h2>Nueva Contraseña</h2>
						<p>Hola {{user.name}},</p>
						<p>Un administrador ha actualizado tus credenciales de acceso.</p>
						<div style="background: #f9f9f9; padding: 15px; margin: 20px 0;">
							<p><strong>Nueva Contraseña:</strong> {{new_password}}</p>
						</div>
						<p>Te recomendamos cambiarla después de iniciar sesión.</p>
						<p style="text-align: center;">
							<a href="{{login_url}}" class="btn">Ingresar Ahora</a>
						</p>
					' . $footer
				];

			case 'payment_success':
				return [
					'subject' => 'Confirmación de Pago - {{plan.name}}',
					'content' => $header . '
						<h2>¡Pago Recibido!</h2>
						<p>Hola {{user.first_name}},</p>
						<p>Hemos procesado tu pago correctamente.</p>
						<ul>
							<li><strong>Plan:</strong> {{plan.name}}</li>
							<li><strong>Monto:</strong> {{payment.amount}} {{payment.currency}}</li>
							<li><strong>Referencia:</strong> {{payment.ref}}</li>
						</ul>
						<p>Gracias por tu confianza.</p>
					' . $footer
				];
				
			case 'payment_failed':
				return [
					'subject' => 'Acción Requerida: Pago Fallido',
					'content' => $header . '
						<h2>Problema con tu pago</h2>
						<p>Hola {{user.first_name}},</p>
						<p>Intentamos procesar la renovación de tu suscripción <strong>{{plan.name}}</strong> pero la transacción falló.</p>
						<p>Por favor, actualiza tu método de pago para evitar la interrupción del servicio.</p>
						<p style="text-align: center;">
							<a href="{{home_url}}/perfil" class="btn">Actualizar Pago</a>
						</p>
					' . $footer
				];

			case 'achievement_assigned':
				return [
					'subject' => '¡Ganaste un nuevo Logro!',
					'content' => $header . '
						<h2>¡Felicidades, {{user.first_name}}!</h2>
						<p>Has desbloqueado un nuevo logro en {{site_name}}:</p>
						<div style="text-align: center; margin: 20px 0;">
							<img src="{{achievement.image}}" style="width: 100px; height: 100px;" alt="Logro">
							<h3>{{achievement.title}}</h3>
							<p>{{achievement.message}}</p>
						</div>
						<p style="text-align: center;">
							<a href="{{home_url}}/logros" class="btn">Ver Mis Logros</a>
						</p>
					' . $footer
				];

			case 'course_available':
				return [
					'subject' => '¡Nuevo Curso Lanzado: {{course_name}}!',
					'content' => $header . '
						<h2>¡Tenemos novedades para ti!</h2>
						<p>Hola {{user.name}},</p>
						<p>Estamos muy emocionados de presentarte el nuevo contenido disponible en la plataforma:</p>
						<div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;">
							{{courses_list}}
						</div>
						<p>Esperamos que este contenido te ayude a seguir creciendo profesionalmente.</p>
						<p style="text-align: center;">
							<a href="{{home_url}}" class="btn">Explorar Contenido</a>
						</p>
					' . $footer
				];

			case 'lesson_available':
				return [
					'subject' => 'Novedades en tu curso: {{course_name}}',
					'content' => $header . '
						<h2>¡Hay nuevas lecciones disponibles!</h2>
						<p>Hola {{user.name}},</p>
						<p>Se ha añadido nuevo contenido al curso <strong>{{course_name}}</strong> en el que estás inscrito:</p>
						<div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;">
							<ul style="margin: 0; padding-left: 20px;">
								{{lessons_list}}
							</ul>
						</div>
						<p>¡No pierdas el ritmo y continúa con tu formación!</p>
						<p style="text-align: center;">
							<a href="{{home_url}}" class="btn">Ir al Curso</a>
						</p>
					' . $footer
				];
			
			default:
				return [
					'subject' => 'Notificación de {{site_name}}',
					'content' => $header . '<p>Tienes una nueva notificación.</p>' . $footer
				];
		}
	}
}
