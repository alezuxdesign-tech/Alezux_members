<?php
/**
 * ALEZUX MEMBERS - CUSTOM AUTHENTICATION TEMPLATE
 * Replaces standard wp-login.php with high-fidelity Image 2 design.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$site_name       = get_bloginfo( 'name' );
$site_desc       = get_bloginfo( 'description' );
$custom_logo_id  = get_theme_mod( 'custom_logo' );
$logo_img        = $custom_logo_id ? wp_get_attachment_image_url( $custom_logo_id, 'medium' ) : '';
$site_icon       = get_site_icon_url( 64 );

$redirect_to     = ! empty( $_REQUEST['redirect_to'] ) ? esc_url_raw( $_REQUEST['redirect_to'] ) : home_url();
$action          = isset( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : 'login';
$reset_key       = isset( $_REQUEST['key'] ) ? sanitize_text_field( $_REQUEST['key'] ) : '';
$reset_login     = isset( $_REQUEST['login'] ) ? sanitize_user( $_REQUEST['login'] ) : '';

$module_dir      = dirname( __DIR__ );
$css_url         = ! empty( $css_url ) ? $css_url : plugins_url( 'assets/css/custom-auth.css', $module_dir . '/Config.php' );
$js_url          = ! empty( $js_url ) ? $js_url : plugins_url( 'assets/js/custom-auth.js', $module_dir . '/Config.php' );
$css_ver         = file_exists( $module_dir . '/assets/css/custom-auth.css' ) ? filemtime( $module_dir . '/assets/css/custom-auth.css' ) : ALEZUX_MEMBERS_VERSION;
$js_ver          = file_exists( $module_dir . '/assets/js/custom-auth.js' ) ? filemtime( $module_dir . '/assets/js/custom-auth.js' ) : ALEZUX_MEMBERS_VERSION;

$privacy_url     = get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/politica-de-privacidad' );
$terms_url       = home_url( '/terminos-y-condiciones' );
$standalone      = isset( $standalone ) ? (bool) $standalone : true;

$is_register     = ( $action === 'register' || ( function_exists( 'is_page' ) && is_page( [ 'registro', 'register' ] ) ) );
$is_recover      = ( in_array( $action, [ 'lostpassword', 'recover' ], true ) );
$is_reset        = ( in_array( $action, [ 'rp', 'resetpass' ], true ) || ( ! empty( $reset_key ) && ! empty( $reset_login ) ) );
$is_login        = ( ! $is_register && ! $is_recover && ! $is_reset );

if ( $standalone ) : ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo esc_html( $site_name ); ?> &bull; <?php esc_html_e( 'Acceso', 'alezux-members' ); ?></title>
  
  <?php if ( $site_icon ) : ?>
    <link rel="icon" href="<?php echo esc_url( $site_icon ); ?>" sizes="32x32">
  <?php endif; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="<?php echo esc_url( $css_url ); ?>?v=<?php echo esc_attr( $css_ver ); ?>">
</head>
<body class="alezux-auth-body">
<?php else : ?>
<div class="alezux-auth-body">
<?php endif; ?>

<div class="alezux-auth-wrapper">
  <div class="alezux-auth-card">
    
    <!-- ======================================================== -->
    <!-- PANEL IZQUIERDO: HERO VIBRANT BLUE (DISEÑO IMAGEN 2)     -->
    <!-- ======================================================== -->
    <div class="alezux-hero-panel">
      <!-- Marca / Logo -->
      <a href="<?php echo esc_url( home_url() ); ?>" class="hero-brand">
        <?php if ( $logo_img ) : ?>
          <img src="<?php echo esc_url( $logo_img ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" style="max-height: 36px; width: auto; object-fit: contain;">
        <?php elseif ( $site_icon ) : ?>
          <img src="<?php echo esc_url( $site_icon ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" style="width: 30px; height: 30px; border-radius: 8px;">
          <span><?php echo esc_html( $site_name ); ?></span>
        <?php else : ?>
          <!-- Logo hexagonal moderno de nodo como en Imagen 2 -->
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <circle cx="12" cy="12" r="4"></circle>
            <line x1="4.93" y1="4.93" x2="9.17" y2="9.17"></line>
            <line x1="14.83" y1="14.83" x2="19.07" y2="19.07"></line>
            <line x1="14.83" y1="9.17" x2="19.07" y2="4.93"></line>
            <line x1="4.93" y1="19.07" x2="9.17" y2="14.83"></line>
          </svg>
          <span><?php echo esc_html( $site_name ); ?></span>
        <?php endif; ?>
      </a>

      <!-- Texto Hero Central -->
      <div class="hero-middle">
        <div class="hero-pill">
          <span>Join Us to Build 🤩</span>
        </div>
        <h1 class="hero-title">Start your Journey</h1>
        <p class="hero-subtitle">
          Follow these simple steps to set up your account and start learning today.
        </p>
      </div>

      <!-- Grid de 3 Tarjetas de Pasos -->
      <div class="hero-cards-grid">
        <div class="hero-step-card active">
          <div class="card-badge">1</div>
          <div class="card-text">Register your<br>account</div>
        </div>

        <div class="hero-step-card glass">
          <div class="card-badge">2</div>
          <div class="card-text">Set up your profile<br>information</div>
        </div>

        <div class="hero-step-card glass">
          <div class="card-badge">3</div>
          <div class="card-text">Access your courses<br>instantly</div>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- PANEL DERECHO: FORMULARIOS (WHITE SURFACE - IMAGEN 2)    -->
    <!-- ======================================================== -->
    <div class="alezux-form-panel">
      
      <!-- ---------------------------------------------------- -->
      <!-- VISTA 1: INICIO DE SESIÓN (WELCOME BACK)             -->
      <!-- ---------------------------------------------------- -->
      <div id="alezux-view-login" class="auth-view <?php echo $is_login ? '' : 'is-hidden'; ?>">
        <div class="form-header">
          <h2 class="form-title">Welcome Back</h2>
          <p class="form-subtitle">Inicia sesión en tu cuenta para continuar</p>
        </div>

        <div class="auth-alert-container"></div>

        <form id="alezux-login-form" autocomplete="on">
          <input type="hidden" id="alezux-login-redirect" value="<?php echo esc_attr( $redirect_to ); ?>">

          <div class="auth-field">
            <label class="field-label" for="alezux-login-username">Correo o Usuario</label>
            <div class="input-wrapper">
              <input 
                type="text" 
                id="alezux-login-username" 
                class="auth-input" 
                placeholder="tu@correo.com o usuario" 
                required 
                autocomplete="username"
              >
            </div>
          </div>

          <div class="auth-field">
            <label class="field-label" for="alezux-login-password">Contraseña</label>
            <div class="input-wrapper">
              <input 
                type="password" 
                id="alezux-login-password" 
                class="auth-input" 
                placeholder="••••••••••••" 
                required 
                autocomplete="current-password"
              >
              <button type="button" class="toggle-pwd-btn" aria-label="Mostrar contraseña">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="7" r="3"></circle>
                </svg>
              </button>
            </div>
          </div>

          <div class="auth-options-row">
            <label class="remember-label">
              <input type="checkbox" id="alezux-login-remember" value="1" checked>
              <span>Recordarme</span>
            </label>
            <a href="#recover" class="auth-link" data-auth-target="recover">¿Olvidaste tu contraseña?</a>
          </div>

          <button type="submit" class="auth-submit-btn">
            <span class="auth-spinner"></span>
            <span class="btn-text">Iniciar Sesión</span>
          </button>

          <div class="auth-switch-row">
            ¿No tienes una cuenta?
            <a href="#register" class="auth-link" data-auth-target="register">Regístrate</a>
          </div>
        </form>
      </div>

      <!-- ---------------------------------------------------- -->
      <!-- VISTA 2: REGISTRO DE CUENTA (JOIN US - IMAGEN 2)     -->
      <!-- ---------------------------------------------------- -->
      <div id="alezux-view-register" class="auth-view <?php echo $is_register ? '' : 'is-hidden'; ?>">
        <div class="form-header">
          <h2 class="form-title">Join Us</h2>
          <p class="form-subtitle">Enter your personal details to get started</p>
        </div>

        <div class="auth-alert-container"></div>

        <form id="alezux-register-form" autocomplete="off">
          <input type="hidden" id="alezux-register-redirect" value="<?php echo esc_attr( $redirect_to ); ?>">

          <div class="auth-field">
            <label class="field-label" for="alezux-register-email">Correo Electrónico</label>
            <div class="input-wrapper">
              <input 
                type="email" 
                id="alezux-register-email" 
                class="auth-input" 
                placeholder="tu@correo.com" 
                required 
                autocomplete="email"
              >
            </div>
          </div>

          <!-- Fila de 2 Columnas: Nombre Completo & Usuario (Imagen 2) -->
          <div class="auth-row-2col">
            <div class="auth-field">
              <label class="field-label" for="alezux-register-name">Full Name</label>
              <div class="input-wrapper">
                <input 
                  type="text" 
                  id="alezux-register-name" 
                  class="auth-input" 
                  placeholder="Juliette Karapetyan" 
                  required
                >
              </div>
            </div>

            <div class="auth-field">
              <label class="field-label" for="alezux-register-username">Username</label>
              <div class="input-wrapper">
                <input 
                  type="text" 
                  id="alezux-register-username" 
                  class="auth-input" 
                  placeholder="julietux"
                >
                <span class="input-check-icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                </span>
              </div>
            </div>
          </div>

          <div class="auth-field">
            <label class="field-label" for="alezux-register-password">Password</label>
            <div class="input-wrapper">
              <input 
                type="password" 
                id="alezux-register-password" 
                class="auth-input" 
                placeholder="••••••••••••" 
                required 
                autocomplete="new-password"
              >
              <button type="button" class="toggle-pwd-btn" aria-label="Mostrar contraseña">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
              </button>
            </div>
            <p class="field-helper-text">
              Mínimo 8 caracteres, mayúsculas, minúsculas, números y símbolos.
            </p>
            <div class="password-meter-bar">
              <div id="alezux-reg-meter-fill" class="password-meter-fill"></div>
            </div>
          </div>

          <button type="submit" class="auth-submit-btn" style="margin-top: 10px;">
            <span class="auth-spinner"></span>
            <span class="btn-text">Continue</span>
          </button>

          <div class="auth-switch-row">
            Already have an account?
            <a href="#login" class="auth-link" data-auth-target="login">Log in</a>
          </div>
        </form>
      </div>

      <!-- ---------------------------------------------------- -->
      <!-- VISTA 3: RECUPERACIÓN DE CONTRASEÑA                  -->
      <!-- ---------------------------------------------------- -->
      <div id="alezux-view-recover" class="auth-view <?php echo $is_recover ? '' : 'is-hidden'; ?>">
        <div class="form-header">
          <h2 class="form-title">Recuperar Acceso</h2>
          <p class="form-subtitle">Ingresa tu correo o usuario y te enviaremos las instrucciones.</p>
        </div>

        <div class="auth-alert-container"></div>

        <form id="alezux-recover-form">
          <div class="auth-field">
            <label class="field-label" for="alezux-recover-login">Correo Electrónico o Usuario</label>
            <div class="input-wrapper">
              <input 
                type="text" 
                id="alezux-recover-login" 
                class="auth-input" 
                placeholder="tu@correo.com o usuario" 
                required
              >
            </div>
          </div>

          <button type="submit" class="auth-submit-btn" style="margin-top: 14px;">
            <span class="auth-spinner"></span>
            <span class="btn-text">Enviar Enlace</span>
          </button>

          <div class="auth-switch-row">
            ¿Recordaste tu contraseña?
            <a href="#login" class="auth-link" data-auth-target="login">Volver a iniciar sesión</a>
          </div>
        </form>
      </div>

      <!-- ---------------------------------------------------- -->
      <!-- VISTA 4: RESTABLECER CONTRASEÑA (RP / RESETPASS)     -->
      <!-- ---------------------------------------------------- -->
      <div id="alezux-view-reset" class="auth-view <?php echo $is_reset ? '' : 'is-hidden'; ?>">
        <div class="form-header">
          <h2 class="form-title">Nueva Contraseña</h2>
          <p class="form-subtitle">Crea una nueva contraseña segura para tu cuenta.</p>
        </div>

        <div class="auth-alert-container"></div>

        <form id="alezux-reset-form">
          <input type="hidden" id="alezux-reset-key" value="<?php echo esc_attr( $reset_key ); ?>">
          <input type="hidden" id="alezux-reset-user" value="<?php echo esc_attr( $reset_login ); ?>">

          <div class="auth-field">
            <label class="field-label" for="alezux-reset-password">Nueva Contraseña</label>
            <div class="input-wrapper">
              <input 
                type="password" 
                id="alezux-reset-password" 
                class="auth-input" 
                placeholder="Mínimo 8 caracteres" 
                required 
                autocomplete="new-password"
              >
              <button type="button" class="toggle-pwd-btn" aria-label="Mostrar contraseña">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
              </button>
            </div>
            <div class="password-meter-bar">
              <div id="alezux-reset-meter-fill" class="password-meter-fill"></div>
            </div>
          </div>

          <div class="auth-field">
            <label class="field-label" for="alezux-reset-confirm">Confirmar Nueva Contraseña</label>
            <div class="input-wrapper">
              <input 
                type="password" 
                id="alezux-reset-confirm" 
                class="auth-input" 
                placeholder="Repite la contraseña" 
                required 
                autocomplete="new-password"
              >
              <button type="button" class="toggle-pwd-btn" aria-label="Mostrar contraseña">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
              </button>
            </div>
          </div>

          <button type="submit" class="auth-submit-btn" style="margin-top: 14px;">
            <span class="auth-spinner"></span>
            <span class="btn-text">Guardar Contraseña</span>
          </button>

          <div class="auth-switch-row">
            <a href="#login" class="auth-link" data-auth-target="login">Volver a iniciar sesión</a>
          </div>
        </form>
      </div>

      <!-- Footer con Términos -->
      <div class="auth-terms-footer">
        Al registrarte o continuar, confirmas que has leído y aceptas los 
        <a href="<?php echo esc_url( $terms_url ); ?>" target="_blank">Términos de Servicio</a> y la 
        <a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank">Política de Privacidad</a>.
      </div>

    </div>
  </div>
</div>

<script>
  window.AlezuxAuthConfig = {
    ajaxUrl: <?php echo json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
    nonce: <?php echo json_encode( wp_create_nonce( 'alezux-auth-nonce' ) ); ?>,
    homeUrl: <?php echo json_encode( home_url() ); ?>,
    defaultRedirect: <?php echo json_encode( $redirect_to ); ?>
  };
</script>
<script src="<?php echo esc_url( $js_url ); ?>?v=<?php echo esc_attr( $js_ver ); ?>"></script>

<?php if ( $standalone ) : ?>
</body>
</html>
<?php else : ?>
</div>
<?php endif; ?>
