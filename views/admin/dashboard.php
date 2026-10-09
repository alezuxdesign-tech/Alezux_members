<?php
/**
 * Vista del Dashboard de Administración
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_arc_bundle = file_exists( ALEZUX_MEMBERS_PATH . 'assets/dist/alezux-dashboard.js' );
?>

<div id="crezca-admin-root">
  <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;background:#090a0f;color:#94a3b8;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;user-select:none;">
    <div style="width:42px;height:42px;border:3px solid rgba(119,71,255,0.2);border-top-color:#7747ff;border-radius:50%;animation:crezca-spin 0.8s linear infinite;margin-bottom:16px;"></div>
    <div style="font-size:16px;font-weight:700;color:#f8fafc;letter-spacing:-0.01em;">Crezca</div>
    <div style="font-size:12px;color:#64748b;margin-top:4px;">Cargando panel de administración...</div>
    <style>@keyframes crezca-spin { to { transform: rotate(360deg); } }</style>
  </div>
</div>

<?php if ( ! $has_arc_bundle ) : ?>
<style>
	/* Estilos específicos para el Dashboard que sobrescriben WP Admin */

	#wpcontent {
		padding-left: 0 !important;
		background-color: #0f0f0f !important; /* Fallback */
		background-color: var(--alezux-bg-base, #0f0f0f) !important;
	}
	.alezux-dashboard-wrapper {
		margin: 20px;
		max-width: 1200px;
		font-family: 'Inter', system-ui, -apple-system, sans-serif;
		padding-bottom: 50px; 
		display: flex;
		flex-direction: column;
	}

	/* Resetear el footer de WordPress para que no se superponga */
	#wpfooter {
		position: relative !important;
		margin-top: 50px !important;
		padding: 20px 0 !important;
		clear: both !important;
		background: transparent !important;
		color: #666 !important;
	}

	#wpcontent {
		height: auto !important;
		min-height: 100vh !important;
		display: flex;
		flex-direction: column;
	}
	.alezux-tabs {
		display: flex;
		gap: 10px;
		margin-bottom: 20px;
		border-bottom: 1px solid #333;
		padding-bottom: 10px;
	}
	.alezux-tab-link {
		padding: 10px 25px;
		background: transparent;
		color: #888;
		border: 1px solid transparent;
		cursor: pointer;
		font-weight: 600;
		border-radius: 50px;
		text-decoration: none;
		transition: all 0.3s ease;
		outline: none !important;
		box-shadow: none !important;
	}
	.alezux-tab-link:hover {
		color: #fff;
		background: #222;
	}
	.alezux-tab-link.active {
		background: var(--alezux-primary, #6c5ce7);
		color: white;
	}
	
	.alezux-tab-panel {
		/* El control de display se hará via JS inline style para asegurar prioridad */
		animation: fadeIn 0.3s ease;
		padding-bottom: 80px; 
	}
	
	@keyframes fadeIn {
		from { opacity: 0; transform: translateY(5px); }
		to { opacity: 1; transform: translateY(0); }
	}

	.alezux-form-group {
		margin-bottom: 25px;
	}
	.alezux-form-label {
		display: block;
		margin-bottom: 10px;
		color: #eee;
		font-weight: 500;
	}
	.alezux-color-input {
		height: 40px;
		width: 100px;
		border: none;
		background: transparent;
		cursor: pointer;
		padding: 0;
	}
	
	/* Estilos de Tarjetas */
	.alezux-card {
		background: #1a1a1a;
		padding: 30px;
		border-radius: 16px;
		box-shadow: 0 4px 20px rgba(0,0,0,0.2);
		border: 1px solid #333;
		margin-bottom: 20px;
	}
	.alezux-title {
		color: #ffffff;
		margin-top: 0;
		margin-bottom: 20px;
		font-weight: 700;
		font-size: 1.5em;
	}
	.alezux-text {
		color: #ccc;
		margin-bottom: 20px;
		line-height: 1.6;
		font-size: 14px;
	}

	/* Shortcodes Grid & Items - UPDATED */
	.alezux-shortcodes-list {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
		gap: 20px;
		margin-top: 20px;
	}
	.alezux-shortcode-item {
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		padding: 25px;
		background: #252525;
		border-radius: 12px;
		border: 1px solid #444;
		transition: all 0.2s ease;
		height: 100%;
		box-sizing: border-box;
	}
	.alezux-shortcode-item:hover {
		transform: translateY(-5px);
		border-color: var(--alezux-primary, #6c5ce7);
		background: #2a2a2a;
		box-shadow: 0 10px 30px rgba(0,0,0,0.3);
	}
	.alezux-shortcode-header {
		display: flex; 
		align-items: center; 
		justify-content: space-between;
		gap: 10px; 
		margin-bottom: 15px;
		flex-wrap: wrap;
	}
	.alezux-shortcode-tag {
		font-family: 'Courier New', monospace;
		background: #111;
		color: #fab1a0;
		padding: 6px 10px;
		border-radius: 6px;
		font-size: 14px;
		border: 1px solid #333;
		display: inline-block;
		word-break: break-all;
	}
	.alezux-module-badge {
		font-size: 10px; 
		text-transform: uppercase; 
		letter-spacing: 1px; 
		color: #777; 
		border: 1px solid #333; 
		padding: 3px 8px; 
		border-radius: 100px;
		white-space: nowrap;
	}
	.alezux-copy-btn {
		background: #333;
		color: white;
		border: none;
		padding: 10px 20px;
		border-radius: 8px;
		cursor: pointer;
		font-size: 14px;
		transition: all 0.2s ease;
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 8px;
		font-weight: 500;
		width: 100%;
		margin-top: auto; /* Push to bottom */
	}
	.alezux-copy-btn:hover {
		background: var(--alezux-primary, #6c5ce7);
	}
	.alezux-copy-btn.copied {
		background: #00b894;
		color: black;
	}
</style>

<?php
// Recopilar Shortcodes de módulos activos
$shortcodes = [];

// 1. FINANZAS
// (Legacy shortcodes removed)

// 1.1 FINANZAS - DASHBOARD
$shortcodes[] = [
    'tag' => 'alezux_sales_stats type="month"',
    'module' => 'FINANZAS',
    'description' => 'KPI: Ingresos Mes Actual (Dashboard).'
];
$shortcodes[] = [
    'tag' => 'alezux_sales_stats type="today"',
    'module' => 'FINANZAS',
    'description' => 'KPI: Ingresos Hoy (Dashboard).'
];


// 2. ESTUDIANTES
$shortcodes[] = [
    'tag'         => 'alezux_estudiantes_total',
    'module'      => 'ESTUDIANTES',
    'description' => 'Muestra el número total de estudiantes registrados.'
];
$shortcodes[] = [
    'tag'         => 'alezux_estudiantes_nuevos_mes',
    'module'      => 'ESTUDIANTES',
    'description' => 'Muestra nuevos estudiantes registrados este mes.'
];
$shortcodes[] = [
    'tag'         => 'alezux_student_momentum',
    'module'      => 'ESTUDIANTES',
    'description' => 'Indicador de progreso de estudio semanal vs semana pasada.'
];

// 3. FORMACIONES
$shortcodes[] = [
    'tag'         => 'alezux_course_whatsapp',
    'module'      => 'FORMACIONES',
    'description' => 'Muestra el enlace de WhatsApp configurado en el curso.'
];
$shortcodes[] = [
    'tag'         => 'alezux_course_slack',
    'module'      => 'FORMACIONES',
    'description' => 'Muestra el enlace de Slack configurado en el curso.'
];
$shortcodes[] = [
    'tag'         => 'alezux_course_zoom',
    'module'      => 'FORMACIONES',
    'description' => 'Muestra el enlace de Zoom configurado en el curso.'
];
$shortcodes[] = [
    'tag'         => 'alezux_resume_topic_name',
    'module'      => 'FORMACIONES',
    'description' => 'Muestra el nombre del tema donde quedó el estudiante.'
];
$shortcodes[] = [
    'tag'         => 'alezux_resume_topic_link',
    'module'      => 'FORMACIONES',
    'description' => 'Muestra el enlace del tema donde quedó el estudiante.'
];
$shortcodes[] = [
    'tag'         => 'alezux_course_count',
    'module'      => 'FORMACIONES',
    'description' => 'Muestra el número total de cursos publicados.'
];

// 4. CONFIG / AUTH
$shortcodes[] = [
    'tag'         => 'alezux_login_alerts',
    'module'      => 'CONFIG',
    'description' => 'Muestra mensajes de alerta en el login (error/éxito).'
];
$shortcodes[] = [
    'tag'         => 'alezux_logout_url',
    'module'      => 'CONFIG',
    'description' => 'Genera URL de cierre de sesión. Usar en href de botones.'
];

// 5. ADMIN DASHBOARD (KPIs)
$shortcodes[] = [
    'tag' => 'alezux_admin_total_students',
    'module' => 'ADMIN',
    'description' => 'KPI: Número total de estudiantes.'
];
$shortcodes[] = [
    'tag' => 'alezux_admin_new_students',
    'module' => 'ADMIN',
    'description' => 'KPI: Estudiantes nuevos este mes.'
];
$shortcodes[] = [
    'tag' => 'alezux_admin_online_users',
    'module' => 'ADMIN',
    'description' => 'KPI: Usuarios online (últimos 5 min).'
];
?>

<div class="alezux-dashboard-wrapper">
	<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px;">
		<h1 class="alezux-title" style="margin: 0; font-size: 28px;">
			🚀 Alezux Members 
			<span style="font-size: 12px; background: #333; padding: 4px 8px; border-radius: 4px; vertical-align: middle; margin-left: 10px;">v<?php echo ALEZUX_MEMBERS_VERSION; ?></span>
		</h1>
	</div>
	
	<?php if ( isset( $_GET['status'] ) && 'success' === $_GET['status'] ) : ?>
		<div class="alezux-notice" style="background: rgba(0, 184, 148, 0.1); border: 1px solid #00b894; color: #00b894; padding: 15px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
			<span class="dashicons dashicons-yes"></span>
			<strong>¡Configuración guardada correctamente!</strong>
		</div>
	<?php endif; ?>

	<?php if ( isset( $_GET['status'] ) && 'notification_sent' === $_GET['status'] ) : ?>
		<div class="alezux-notice" style="background: rgba(0, 184, 148, 0.1); border: 1px solid #00b894; color: #00b894; padding: 15px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
			<span class="dashicons dashicons-megaphone"></span>
			<strong>¡Notificación de prueba enviada correctamente!</strong>
		</div>
	<?php endif; ?>

	<!-- TABS NAVIGATION -->
	<div class="alezux-tabs">
		<a href="javascript:void(0);" onclick="openAlezuxTab(event, 'tab-settings')" class="alezux-tab-link active" id="link-tab-settings">Configuración Global</a>
		<a href="javascript:void(0);" onclick="openAlezuxTab(event, 'tab-permissions')" class="alezux-tab-link" id="link-tab-permissions">Permisos</a>
		<a href="javascript:void(0);" onclick="openAlezuxTab(event, 'tab-shortcodes')" class="alezux-tab-link" id="link-tab-shortcodes">Shortcodes</a>
		<a href="javascript:void(0);" onclick="openAlezuxTab(event, 'tab-notifications')" class="alezux-tab-link" id="link-tab-notifications">Notificaciones</a>
		<a href="javascript:void(0);" onclick="openAlezuxTab(event, 'tab-finanzas')" class="alezux-tab-link" id="link-tab-finanzas">Finanzas</a>
	</div>

	<!-- TAB 1: CONFIGURACION -->
	<div id="tab-settings" class="alezux-tab-panel" style="display: block;">
		<div class="alezux-card">
			<h2 class="alezux-title">🎨 Personalización Visual</h2>
			<p class="alezux-text">Define los colores maestros de tu plataforma. Todos los bloques se actualizarán automáticamente.</p>
			
			<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST">
				<input type="hidden" name="action" value="alezux_save_settings">
				<?php wp_nonce_field( 'alezux_save_settings_action', 'alezux_settings_nonce' ); ?>
				
				<div class="alezux-form-group">
					<label class="alezux-form-label">Color Primario (Accent)</label>
					<div style="display: flex; gap: 15px; align-items: center;">
						<div>
							<input type="color" name="alezux_primary_color" class="alezux-color-input" value="<?php echo esc_attr( $settings['primary_color'] ); ?>">
							<span style="font-size: 12px; color: #777; display: block; text-align: center;">Normal</span>
						</div>
						<div>
							<input type="color" name="alezux_primary_hover" class="alezux-color-input" value="<?php echo esc_attr( $settings['primary_hover'] ); ?>">
							<span style="font-size: 12px; color: #777; display: block; text-align: center;">Hover</span>
						</div>
					</div>
				</div>

				<div style="border-top: 1px solid #333; margin: 25px 0;"></div>

				<div class="alezux-form-group">
					<h3 class="alezux-title" style="font-size: 18px; margin-bottom: 20px;">📐 Apariencia de Componentes</h3>
					
					<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
						<div>
							<label class="alezux-form-label">Radio del Borde</label>
							<input type="text" name="alezux_border_radius" 
								   style="background: #252525; border: 1px solid #444; color: white; padding: 10px; border-radius: 8px; width: 100%; box-sizing: border-box;"
								   value="<?php echo esc_attr( $settings['border_radius'] ); ?>" placeholder="50px">
						</div>
						
						<div>
							<label class="alezux-form-label">Color del Borde</label>
							<input type="color" name="alezux_border_color" class="alezux-color-input" value="<?php echo esc_attr( $settings['border_color'] ); ?>">
						</div>
					</div>

					<div style="margin-top: 20px;">
						<label class="alezux-form-label">Sombra (Box Shadow)</label>
						<input type="text" name="alezux_box_shadow" 
							   style="background: #252525; border: 1px solid #444; color: white; padding: 10px; border-radius: 8px; width: 100%; box-sizing: border-box;"
							   value="<?php echo esc_attr( $settings['box_shadow'] ); ?>" placeholder="0 10px 30px rgba(0, 0, 0, 0.3)">
					</div>
				</div>

				<div style="border-top: 1px solid #333; margin: 25px 0;"></div>

				<div class="alezux-form-group">
					<h3 class="alezux-title" style="font-size: 18px; margin-bottom: 20px;">🌑 Modo Oscuro / Fondo</h3>
					<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px;">
						<div>
							<label class="alezux-form-label">Fondo Principal</label>
							<input type="color" name="alezux_bg_base" class="alezux-color-input" value="<?php echo esc_attr( $settings['bg_base'] ); ?>">
						</div>
						<div>
							<label class="alezux-form-label">Fondo Tarjetas</label>
							<input type="color" name="alezux_bg_card" class="alezux-color-input" value="<?php echo esc_attr( $settings['bg_card'] ); ?>">
						</div>
					</div>
				</div>

				<div style="margin-top: 40px; text-align: right;">
					<button type="submit" class="button button-primary" 
							style="background: var(--alezux-primary, #6c5ce7); border-color: var(--alezux-primary, #6c5ce7); padding: 5px 30px; font-size: 16px; font-weight: 600; height: auto; line-height: 2;">
						Guardar Cambios
					</button>
				</div>
			</form>
		</div>

		<!-- IMPORTADOR DE CURSOS (NUEVO) -->
		<div class="alezux-card">
			<h2 class="alezux-title">📥 Importador de Cursos (LearnDash)</h2>
			<p class="alezux-text">Este botón leerá automáticamente el archivo <code>JSON CURSO LEARDASH.json</code> que se encuentra dentro de la carpeta principal del plugin y creará los Cursos, Lecciones y Temas con sus respectivos videos en tu base de datos de WordPress.</p>
			
			<?php if ( isset( $_GET['status'] ) && 'import_success' === $_GET['status'] ) : ?>
				<div style="background: #27ae60; color: white; padding: 10px; border-radius: 6px; margin-bottom: 20px; display: inline-block;">
					✅ <strong>¡Éxito!</strong> Todos los cursos se han importado correctamente. Verifica en la sección de LearnDash LMS.
				</div>
			<?php endif; ?>

			<?php if ( isset( $_GET['status'] ) && 'cleanup_success' === $_GET['status'] ) : ?>
				<div style="background: #c0392b; color: white; padding: 10px; border-radius: 6px; margin-bottom: 20px; display: inline-block;">
					🗑️ <strong>Limpieza Completada:</strong> Se han borrado todos los cursos, lecciones y temas creados anteriormente.
				</div>
			<?php endif; ?>

			<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST" style="display:inline-block; margin-right: 15px; vertical-align: top;">
				<input type="hidden" name="action" value="alezux_import_json">
				<?php wp_nonce_field( 'alezux_import_json_action', 'alezux_import_nonce' ); ?>
				
				<div style="margin-top: 10px;">
					<button type="submit" class="button button-primary" onclick="return confirm('¿Estás seguro de querer importar el JSON ahora? Esto creará cursos reales en tu sitio.');"
							style="background: #e67e22; border-color: #d35400; padding: 5px 30px; font-size: 16px; font-weight: 600; height: auto; line-height: 2;">
						🚀 Importar Cursos ahora
					</button>
				</div>
			</form>

			<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST" style="display:inline-block; vertical-align: top;">
				<input type="hidden" name="action" value="alezux_cleanup_ld">
				<?php wp_nonce_field( 'alezux_cleanup_action', 'alezux_cleanup_nonce' ); ?>
				
				<div style="margin-top: 10px; display: flex; align-items: center; gap: 10px;">
					<select name="cleanup_course_id" style="background: #252525; border: 1px solid #444; color: white; padding: 5px 10px; border-radius: 6px; height: 42px;">
						<option value="all">Eliminar TODOS los cursos</option>
						<?php 
						$ld_courses = get_posts(['post_type' => 'sfwd-courses', 'posts_per_page' => -1]);
						foreach ($ld_courses as $ldc) {
							echo '<option value="' . esc_attr($ldc->ID) . '">' . esc_html($ldc->post_title) . '</option>';
						}
						?>
					</select>

					<button type="submit" class="button button-primary" onclick="return confirm('⚠️ ADVERTENCIA: Esta acción es irreversible. ¿Estás seguro de continuar?');"
							style="background: #c0392b; border-color: #a93226; padding: 5px 30px; font-size: 16px; font-weight: 600; height: auto; line-height: 2;">
						🗑️ Borrar Curso(s)
					</button>
				</div>
			</form>
		</div>

		<!-- GENERADOR DE IMÁGENES (NUEVO) -->
		<div class="alezux-card">
			<h2 class="alezux-title">🎨 Generador de Imágenes de Módulos</h2>
			<p class="alezux-text">Esto leerá todas tus lecciones y les generará y asignará automáticamente una imagen destacada profesional con el diseño "Módulo X", basándose en los separadores.</p>
			
			<?php if ( isset( $_GET['status'] ) && 'images_success' === $_GET['status'] ) : ?>
				<div style="background: #0984e3; color: white; padding: 10px; border-radius: 6px; margin-bottom: 20px; display: inline-block;">
					🖼️ <strong>¡Generación Exitosa!</strong> Todas las lecciones que no tenían imagen ahora tienen su diseño generado y asignado.
				</div>
			<?php endif; ?>

			<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST">
				<input type="hidden" name="action" value="alezux_generate_images">
				<?php wp_nonce_field( 'alezux_generate_images_action', 'alezux_generate_images_nonce' ); ?>
				
				<div style="margin-top: 10px; display: flex; align-items: center; gap: 10px;">
					<select name="image_course_id" style="background: #252525; border: 1px solid #444; color: white; padding: 5px 10px; border-radius: 6px; height: 42px;">
						<option value="all">Todos los cursos</option>
						<?php 
						foreach ($ld_courses as $ldc) {
							echo '<option value="' . esc_attr($ldc->ID) . '">' . esc_html($ldc->post_title) . '</option>';
						}
						?>
					</select>

					<button type="submit" class="button button-primary" onclick="return confirm('Este proceso puede tardar unos minutos si son muchas lecciones. Si se corta, puedes volver a presionarlo y continuará donde se quedó. ¿Generar imágenes?');"
							style="background: #0984e3; border-color: #74b9ff; padding: 5px 30px; font-size: 16px; font-weight: 600; height: auto; line-height: 2;">
						🖌️ Generar Imágenes Faltantes
					</button>
				</div>
			</form>
		</div>
	</div>

	<!-- TAB PERMISSIONS (NUEVO) -->
	<div id="tab-permissions" class="alezux-tab-panel" style="display: none;">
		<div class="alezux-card">
			<h2 class="alezux-title">🔒 Control de Acceso y Permisos</h2>
			<p class="alezux-text">Gestiona qué páginas son visibles y configura las redirecciones de autenticación.</p>
			
			<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST">
				<input type="hidden" name="action" value="alezux_save_settings">
                <input type="hidden" name="alezux_saving_tab" value="permissions">
				<?php wp_nonce_field( 'alezux_save_settings_action', 'alezux_settings_nonce' ); ?>
				
                <!-- AUTH MOVIDO AQUÍ -->
				<div class="alezux-form-group">
					<h3 class="alezux-title" style="font-size: 18px; margin-bottom: 20px; color: #ff9f43;">🚪 Autenticación Personalizada</h3>
					<div>
						<label class="alezux-form-label">Página de Login Personalizada</label>
						<?php 
						wp_dropdown_pages([
							'name'              => 'alezux_login_page_id',
							'selected'          => get_option('alezux_login_page_id'),
							'show_option_none'  => 'Usar WordPress por defecto',
							'option_none_value' => '0',
							'class'             => 'alezux-select-input',
							'style'             => 'background: #252525; border: 1px solid #444; color: white; padding: 10px; border-radius: 8px; width: 100%; box-sizing: border-box;'
						]); 
						?>
						<p style="font-size: 12px; color: #777; margin-top: 5px;">Las peticiones a /wp-login.php serán redirigidas aquí.</p>
					</div>

					<div style="margin-top: 15px;">
						<label class="alezux-form-label">Página de Restablecer Contraseña</label>
						<?php 
						wp_dropdown_pages([
							'name'              => 'alezux_reset_page_id',
							'selected'          => get_option('alezux_reset_page_id'),
							'show_option_none'  => 'Usar misma del Login',
							'option_none_value' => '0',
							'class'             => 'alezux-select-input',
							'style'             => 'background: #252525; border: 1px solid #444; color: white; padding: 10px; border-radius: 8px; width: 100%; box-sizing: border-box;'
						]); 
						?>
						<p style="font-size: 12px; color: #777; margin-top: 5px;">El correo de recuperación apuntará aquí.</p>
					</div>
				</div>

				<div style="border-top: 1px solid #333; margin: 25px 0;"></div>

                <!-- ADMIN ONLY CSS -->
                <div class="alezux-form-group">
					<h3 class="alezux-title" style="font-size: 18px; margin-bottom: 20px; color: #a29bfe;">👁️ Visibilidad por CSS (Admin Only)</h3>
					<label class="alezux-form-label">Clases CSS Ocultas para No-Admins</label>
					<textarea name="alezux_admin_only_css_classes" rows="4" 
							  style="background: #252525; border: 1px solid #444; color: white; padding: 10px; border-radius: 8px; width: 100%; box-sizing: border-box;"
							  placeholder="admin-only, staff-secret, my-custom-class"><?php echo esc_textarea( $admin_only_css_classes ); ?></textarea>
					<p style="font-size: 12px; color: #777; margin-top: 5px;">
                        Escribe las clases CSS separadas por comas. A cualquier usuario que <strong>NO</strong> sea administrador se le aplicará <code>display: none !important;</code> a estos elementos.
                    </p>
				</div>

				<div style="border-top: 1px solid #333; margin: 25px 0;"></div>

                <!-- PÁGINAS RESTRINGIDAS -->
                <div class="alezux-form-group">
					<h3 class="alezux-title" style="font-size: 18px; margin-bottom: 20px; color: #e55039;">🚫 Páginas Restringidas (Solo Admin)</h3>
					<p class="alezux-text" style="font-size: 13px; margin-bottom: 15px;">Selecciona las páginas que solo podrán ser vistas por Administradores.</p>
                    
                    <!-- Search Input -->
                    <input type="text" id="alezux-page-search" placeholder="🔍 Buscar página..." onkeyup="filterPages('alezux-page-search', 'alezux-pages-list')" style="background: #252525; border: 1px solid #444; color: white; padding: 10px; border-radius: 8px; width: 100%; box-sizing: border-box; margin-bottom: 15px;">

                    <div id="alezux-pages-list" style="max-height: 400px; overflow-y: auto; background: #222; border: 1px solid #444; border-radius: 8px; padding: 15px; display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 10px;">
                        <?php if ( ! empty( $all_pages ) ) : ?>
                            <?php foreach ( $all_pages as $page ) : ?>
                                <?php 
                                    $is_checked = in_array( $page->ID, $restricted_pages ) ? 'checked' : ''; 
                                    $page_title = ! empty( $page->post_title ) ? $page->post_title : '(Sin Título)';
                                ?>
                                <label class="alezux-page-item" style="display: flex; align-items: center; padding: 10px; background: #2a2a2a; border: 1px solid #333; border-radius: 6px; cursor: pointer; transition: all 0.2s ease;">
                                    <input type="checkbox" name="alezux_restricted_pages[]" value="<?php echo esc_attr( $page->ID ); ?>" <?php echo $is_checked; ?> style="margin-right: 12px; width: 18px; height: 18px; accent-color: #e55039;">
                                    <div style="flex-grow: 1;">
                                        <span class="page-title" style="display: block; font-weight: 500; font-size: 14px; color: #eee;"><?php echo esc_html( $page_title ); ?></span>
                                        <span style="display: block; color: #777; font-size: 11px;">ID: <?php echo $page->ID; ?> &bull; <?php echo $page->post_status; ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p style="color: #888;">No hay páginas creadas.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="border-top: 1px solid #333; margin: 25px 0;"></div>

                <!-- PÁGINAS PRIVADAS (NUEVO) -->
                <div class="alezux-form-group">
					<h3 class="alezux-title" style="font-size: 18px; margin-bottom: 20px; color: #6c5ce7;">🔐 Páginas Privadas (Solo Logueados)</h3>
					<p class="alezux-text" style="font-size: 13px; margin-bottom: 15px;">Selecciona las páginas que requieren que el usuario haya iniciado sesión para verlas. Si no está logueado, será enviado al inicio.</p>
                    
                    <!-- Search Input -->
                    <input type="text" id="alezux-private-search" placeholder="🔍 Buscar página..." onkeyup="filterPages('alezux-private-search', 'alezux-private-list')" style="background: #252525; border: 1px solid #444; color: white; padding: 10px; border-radius: 8px; width: 100%; box-sizing: border-box; margin-bottom: 15px;">

                    <div id="alezux-private-list" style="max-height: 400px; overflow-y: auto; background: #222; border: 1px solid #444; border-radius: 8px; padding: 15px; display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 10px;">
                        <?php if ( ! empty( $all_pages ) ) : ?>
                            <?php foreach ( $all_pages as $page ) : ?>
                                <?php 
                                    $is_private_checked = in_array( $page->ID, $private_pages ) ? 'checked' : ''; 
                                    $page_title = ! empty( $page->post_title ) ? $page->post_title : '(Sin Título)';
                                ?>
                                <label class="alezux-page-item priv-item" style="display: flex; align-items: center; padding: 10px; background: #2a2a2a; border: 1px solid #333; border-radius: 6px; cursor: pointer; transition: all 0.2s ease;">
                                    <input type="checkbox" name="alezux_private_pages[]" value="<?php echo esc_attr( $page->ID ); ?>" <?php echo $is_private_checked; ?> style="margin-right: 12px; width: 18px; height: 18px; accent-color: #6c5ce7;">
                                    <div style="flex-grow: 1;">
                                        <span class="page-title" style="display: block; font-weight: 500; font-size: 14px; color: #eee;"><?php echo esc_html( $page_title ); ?></span>
                                        <span style="display: block; color: #777; font-size: 11px;">ID: <?php echo $page->ID; ?> &bull; <?php echo $page->post_status; ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p style="color: #888;">No hay páginas creadas.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <style>
                    .alezux-page-item.priv-item:has(input:checked) {
                        border-color: #6c5ce7 !important;
                        background: rgba(108, 92, 231, 0.1) !important;
                    }
                </style>

                <style>
                    .alezux-page-item:hover {
                        border-color: #666 !important;
                        background: #333 !important;
                    }
                    .alezux-page-item:has(input:checked) {
                        border-color: #e55039 !important;
                        background: rgba(229, 80, 57, 0.1) !important;
                    }
                </style>
                <script>
                    function filterPages() {
                        var input = document.getElementById("alezux-page-search");
                        var filter = input.value.toUpperCase();
                        var container = document.getElementById("alezux-pages-list");
                        var labels = container.getElementsByTagName("label");

                        for (var i = 0; i < labels.length; i++) {
                            var span = labels[i].getElementsByClassName("page-title")[0];
                            if (span) {
                                var txtValue = span.textContent || span.innerText;
                                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                                    labels[i].style.display = "flex";
                                } else {
                                    labels[i].style.display = "none";
                                }
                            }       
                        }
                    }
                </script>

				<div style="margin-top: 40px; text-align: right;">
					<button type="submit" class="button button-primary" 
							style="background: var(--alezux-primary, #6c5ce7); border-color: var(--alezux-primary, #6c5ce7); padding: 5px 30px; font-size: 16px; font-weight: 600; height: auto; line-height: 2;">
						Guardar Permisos
					</button>
				</div>
			</form>
		</div>
	</div>

	<!-- TAB 2: SHORTCODES -->
	<div id="tab-shortcodes" class="alezux-tab-panel" style="display: none;">
		<div class="alezux-card" style="background: transparent; border: none; box-shadow: none; padding: 0;">
			<div style="background: #1a1a1a; padding: 30px; border-radius: 16px; border: 1px solid #333; margin-bottom: 20px;">
				<h2 class="alezux-title">🧩 Librería de Shortcodes</h2>
				<p class="alezux-text">Utiliza estos códigos cortos para insertar los módulos de Alezux en tus páginas de Elementor o editor de bloques.</p>
			</div>
			
			<?php if ( empty( $shortcodes ) ) : ?>
				<div style="padding: 40px; text-align: center; border: 1px dashed #444; border-radius: 10px; color: #666; background: #1a1a1a;">
					<span class="dashicons dashicons-info" style="font-size: 30px; width: 30px; height: 30px; margin-bottom: 10px;"></span>
					<p>No se encontraron shortcodes registrados.</p>
				</div>
			<?php else : ?>
                <!-- Filtro de Módulos -->
                <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <label style="color: #aaa; font-size: 13px;">Filtrar por Módulo:</label>
                    <select id="alezux-shortcode-filter" onchange="filterShortcodes()" style="background: #252525; border: 1px solid #444; color: white; padding: 6px 12px; border-radius: 6px;">
                        <option value="all">Todos</option>
                        <?php 
                        $modules = array_unique( array_column( $shortcodes, 'module' ) );
                        foreach ( $modules as $mod ) : ?>
                            <option value="<?php echo esc_attr( $mod ); ?>"><?php echo esc_html( $mod ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

				<div class="alezux-table-wrapper" style="background: #1a1a1a; border-radius: 12px; padding: 0;">
                    <table class="alezux-finanzas-table" id="alezux-shortcodes-table" style="border: none;">
                        <thead>
                            <tr>
                                <th style="width: 250px;">Shortcode</th>
                                <th style="width: 100px;">Módulo</th>
                                <th>Descripción</th>
                                <th style="width: 100px; text-align: right;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $shortcodes as $sc ) : ?>
                                <tr data-module="<?php echo esc_attr( $sc['module'] ); ?>">
                                    <td>
                                        <span class="alezux-shortcode-tag" style="margin: 0;">[<?php echo esc_html( $sc['tag'] ); ?>]</span>
                                    </td>
                                    <td>
                                        <span class="alezux-status-badge status-completed" style="font-size: 10px;"><?php echo esc_html( $sc['module'] ); ?></span>
                                    </td>
                                    <td style="color: #aaa; font-size: 13px;">
                                        <?php echo esc_html( $sc['description'] ); ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <button class="alezux-copy-action-btn" onclick="copyToClipboard(this, '[<?php echo esc_js( $sc['tag'] ); ?>]')" style="padding: 6px 12px; font-size: 11px;">
                                            <span class="dashicons dashicons-admin-page" style="font-size: 14px; width: 14px; height: 14px; line-height: 1.2;"></span>
                                            <span class="btn-text">Copiar</span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
				</div>
                
                <!-- Estilos de tabla inline para asegurar compatibilidad si no carga el CSS externo -->
                <style>
                    .alezux-finanzas-table th { background: rgba(26, 32, 44, 0.5); padding: 12px 15px; text-align: left; color: #a1a1aa; font-weight: 600; text-transform: uppercase; font-size: 11px; border-bottom: 1px solid #333; }
                    .alezux-finanzas-table td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #222; color: #fff; }
                    .alezux-finanzas-table tr:last-child td { border-bottom: none; }
                    
                    /* Custom Copy Button Style to avoid conflicts */
                    .alezux-copy-action-btn {
                        background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
                        border: none;
                        color: #fff;
                        padding: 8px 16px;
                        border-radius: 6px;
                        font-size: 12px;
                        font-weight: 600;
                        cursor: pointer;
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        transition: opacity 0.2s;
                        box-shadow: 0 4px 6px rgba(108, 92, 231, 0.3);
                        text-decoration: none;
                    }
                    .alezux-copy-action-btn:hover { opacity: 0.9; }
                    .alezux-copy-action-btn.copied { background: #00b894; }
                </style>
			<?php endif; ?>
		</div>
	</div>

	<!-- TAB 3: NOTIFICACIONES -->
	<div id="tab-notifications" class="alezux-tab-panel" style="display: none;">
		<div class="alezux-card">
			<h2 class="alezux-title">🔔 Enviar Notificación</h2>
			<p class="alezux-text">Envía notificaciones de prueba o avisos importantes a los usuarios directamente desde aquí.</p>

			<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST">
				<input type="hidden" name="action" value="alezux_send_test_notification">
				<?php wp_nonce_field( 'alezux_send_test_notification_action', 'alezux_notification_nonce' ); ?>

				<div class="alezux-form-group">
					<label class="alezux-form-label">Título</label>
					<input type="text" name="notification_title" 
						   style="background: #252525; border: 1px solid #444; color: white; padding: 10px; border-radius: 8px; width: 100%; box-sizing: border-box;"
						   value="Aviso Importante" required>
				</div>

				<div class="alezux-form-group">
					<label class="alezux-form-label">Mensaje</label>
					<textarea name="notification_message" rows="4" 
							  style="background: #252525; border: 1px solid #444; color: white; padding: 10px; border-radius: 8px; width: 100%; box-sizing: border-box;"
							  required>Hola, esta es una notificación enviada desde el panel de administración.</textarea>
				</div>

				<div class="alezux-form-group">
					<label class="alezux-form-label">ID de Usuario Destino (Opcional - Dejar vacío para enviar al usuario actual)</label>
					<input type="number" name="target_user_id" 
						   style="background: #252525; border: 1px solid #444; color: white; padding: 10px; border-radius: 8px; width: 100%; box-sizing: border-box;"
						   placeholder="Ej: 1">
					<p style="font-size: 12px; color: #777; margin-top: 5px;">Si se deja vacío, se enviará solo a ti mismo como prueba.</p>
				</div>

				<div style="margin-top: 20px; text-align: right;">
					<button type="submit" class="button button-primary" 
							style="background: var(--alezux-primary, #6c5ce7); border-color: var(--alezux-primary, #6c5ce7); padding: 5px 30px; font-size: 16px; font-weight: 600; height: auto; line-height: 2;">
						Enviar Notificación de Prueba
					</button>
				</div>
			</form>
		</div>
	</div>

	<!-- TAB 4: FINANZAS -->
	<div id="tab-finanzas" class="alezux-tab-panel" style="display: none;">
		<div class="alezux-card">
			<h2 class="alezux-title">💳 Configuración de Pagos</h2>
			<p class="alezux-text">Gestiona las credenciales de Stripe para permitir la creación de planes de pago y suscripciones.</p>

			<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST">
				<input type="hidden" name="action" value="alezux_save_settings">
				<?php wp_nonce_field( 'alezux_save_settings_action', 'alezux_settings_nonce' ); ?>
				
				<div class="alezux-form-group">
					<label class="alezux-form-label">Stripe Public Key</label>
					<input type="text" name="alezux_stripe_public_key" 
						   style="background: #252525; border: 1px solid #444; color: white; padding: 10px; border-radius: 8px; width: 100%; box-sizing: border-box;"
						   value="<?php echo esc_attr( get_option('alezux_stripe_public_key') ); ?>" placeholder="pk_test_...">
				</div>

				<div class="alezux-form-group">
					<label class="alezux-form-label">Stripe Secret Key</label>
					<input type="password" name="alezux_stripe_secret_key" 
						   style="background: #252525; border: 1px solid #444; color: white; padding: 10px; border-radius: 8px; width: 100%; box-sizing: border-box;"
						   value="<?php echo esc_attr( get_option('alezux_stripe_secret_key') ); ?>" placeholder="sk_test_...">
				</div>

				<div class="alezux-form-group">
					<label class="alezux-form-label">Webhook Signing Secret (Opcional por ahora)</label>
					<input type="password" name="alezux_stripe_webhook_secret" 
						   style="background: #252525; border: 1px solid #444; color: white; padding: 10px; border-radius: 8px; width: 100%; box-sizing: border-box;"
						   value="<?php echo esc_attr( get_option('alezux_stripe_webhook_secret') ); ?>" placeholder="whsec_...">
                    <p style="font-size:12px; color:#aaa; margin-top:5px;">Obtén esto en Stripe Dashboard > Developers > Webhooks tras añadir el endpoint.</p>
				</div>

				<div style="margin-top: 20px; text-align: right;">
					<button type="submit" class="button button-primary" 
							style="background: var(--alezux-primary, #6c5ce7); border-color: var(--alezux-primary, #6c5ce7); padding: 5px 30px; font-size: 16px; font-weight: 600; height: auto; line-height: 2;">
						Guardar Credenciales
					</button>
				</div>
			</form>

			<hr style="margin: 30px 0; border: 0; border-top: 1px solid #333;">

			<!-- Simulación Check -->
			<div style="background: rgba(233, 30, 99, 0.1); border: 1px solid #e91e63; border-radius: 12px; padding: 25px;">
				<h2 class="alezux-title" style="color: #e91e63; font-size: 20px;">⚡ Simulador de Webhook (Test)</h2>
				<p class="alezux-text">Esta herramienta simula una respuesta de pago exitosa desde Stripe. Úsala para verificar que tu sistema crea usuarios y envía correos correctamente.</p>
				
				<?php if ( isset( $_GET['sim_result'] ) ) : ?>
					<?php if ( $_GET['sim_result'] == 'success' ) : ?>
						<div style="background: #27ae60; color: white; padding: 10px; border-radius: 6px; margin-bottom: 20px; display: inline-block;">
							✅ <strong>Prueba Exitosa:</strong> El webhook simulado fue procesado correctamente.
						</div>
					<?php else : ?>
						<div style="background: #c0392b; color: white; padding: 10px; border-radius: 6px; margin-bottom: 20px; display: inline-block;">
							❌ <strong>Error:</strong> Hubo un problema procesando el webhook. Revisa los logs de error.
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST" style="margin-top: 20px;">
					<input type="hidden" name="action" value="alezux_simulate_webhook">
					<?php wp_nonce_field( 'alezux_simulate_action', 'alezux_sim_nonce' ); ?>
					
					<div class="alezux-form-group">
						<label class="alezux-form-label">Email de Prueba</label>
						<input type="email" name="sim_email" 
							   style="background: #252525; border: 1px solid #444; color: white; padding: 10px; border-radius: 8px; width: 100%; max-width: 400px; box-sizing: border-box;"
							   value="stikecool@gmail.com" required>
					</div>

					<button type="submit" class="button button-primary" 
							style="background: #e91e63; border-color: #c2185b; padding: 5px 30px; font-size: 16px; font-weight: 600; height: auto; line-height: 2;">
						🚀 Disparar Pago Simulado
					</button>
					<p style="font-size: 12px; color: #777; margin-top: 10px;">Esto es solo una simulación interna, no contactará a Stripe ni realizará cargos reales.</p>
				</form>
			</div>
		</div>
	</div>

		</div>
	</div>

</div>

<script>
// Funciones globales para evitar problemas de scope
function openAlezuxTab(evt, tabName) {
	evt.preventDefault();
	
	// 1. Ocultar todos los paneles
	var tabcontent = document.getElementsByClassName("alezux-tab-panel");
	for (var i = 0; i < tabcontent.length; i++) {
		tabcontent[i].style.display = "none";
	}

	// 2. Desactivar todos los links
	var tablinks = document.getElementsByClassName("alezux-tab-link");
	for (var i = 0; i < tablinks.length; i++) {
		tablinks[i].className = tablinks[i].className.replace(" active", "");
	}

	// 3. Mostrar el panel actual y activar el link actual
	document.getElementById(tabName).style.display = "block";
	evt.currentTarget.className += " active";
}

function copyToClipboard(btnElement, text) {
	navigator.clipboard.writeText(text).then(() => {
		// Feedback visual
		btnElement.classList.add('copied');
		var textSpan = btnElement.querySelector('.btn-text');
		var originalText = textSpan.innerText;
		textSpan.innerText = '¡Copiado!';
		
		setTimeout(() => {
			btnElement.classList.remove('copied');
			textSpan.innerText = originalText; // Restaurar texto original (usualmente 'Copiar')
			if(originalText !== 'Copiar') textSpan.innerText = 'Copiar'; // Fallback por si acaso
		}, 2000);
	}).catch(err => {
		console.error('Error al copiar:', err);
		alert('No se pudo copiar el texto automáticamente.');
	});
}

function filterShortcodes() {
    var filter = document.getElementById("alezux-shortcode-filter").value;
    var table = document.getElementById("alezux-shortcodes-table");
    var tr = table.getElementsByTagName("tr");

    for (var i = 0; i < tr.length; i++) {
        // Ignorar encabezado
        if (tr[i].getElementsByTagName("th").length > 0) continue;

        var module = tr[i].getAttribute("data-module");
        if (filter === "all" || module === filter) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
}
</script>
<?php endif; ?>
