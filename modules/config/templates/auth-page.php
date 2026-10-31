<?php
/**
 * ALEZUX / CREZCA - CUSTOM AUTHENTICATION TEMPLATE
 * Replaces standard wp-login.php with high-fidelity Image 2 design.
 * 100% personalizable desde el panel de control.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$auth_settings   = function_exists( 'crezca_get_auth_settings' ) ? crezca_get_auth_settings() : [];

$site_name       = get_bloginfo( 'name' );
$site_desc       = get_bloginfo( 'description' );
$custom_logo_id  = get_theme_mod( 'custom_logo' );
$logo_img        = ! empty( $auth_settings['logo_url'] ) ? $auth_settings['logo_url'] : ( $custom_logo_id ? wp_get_attachment_image_url( $custom_logo_id, 'medium' ) : get_option( 'alezux_academy_logo', '' ) );
$logo_width      = ! empty( $auth_settings['logo_width'] ) ? intval( $auth_settings['logo_width'] ) : 160;
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

$privacy_url     = ! empty( $auth_settings['privacy_url'] ) ? $auth_settings['privacy_url'] : ( get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/politica-de-privacidad' ) );
$terms_url       = ! empty( $auth_settings['terms_url'] ) ? $auth_settings['terms_url'] : home_url( '/terminos-y-condiciones' );
$standalone      = isset( $standalone ) ? (bool) $standalone : true;

$form_theme      = ! empty( $auth_settings['form_theme'] ) ? $auth_settings['form_theme'] : 'light';
$allow_register  = isset( $auth_settings['allow_registration'] ) ? (bool) $auth_settings['allow_registration'] : true;
$login_title     = ! empty( $auth_settings['login_title'] ) ? $auth_settings['login_title'] : 'Welcome Back';
$login_subtitle  = ! empty( $auth_settings['login_subtitle'] ) ? $auth_settings['login_subtitle'] : 'Inicia sesión en tu cuenta para continuar';
$hero_title      = ! empty( $auth_settings['hero_title'] ) ? $auth_settings['hero_title'] : 'Start your Journey';
$hero_subtitle   = ! empty( $auth_settings['hero_subtitle'] ) ? $auth_settings['hero_subtitle'] : 'Follow these simple steps to set up your account and start learning today.';
$hero_badge      = ! empty( $auth_settings['hero_badge_text'] ) ? $auth_settings['hero_badge_text'] : 'Join Us to Build 🤩';
$badge_enabled   = isset( $auth_settings['hero_badge_enabled'] ) ? (bool) $auth_settings['hero_badge_enabled'] : true;
$steps_enabled   = isset( $auth_settings['steps_enabled'] ) ? (bool) $auth_settings['steps_enabled'] : true;

$sync_colors    = ! isset( $auth_settings['sync_academy_colors'] ) || ! empty( $auth_settings['sync_academy_colors'] );
$academy_accent = get_option( 'alezux_accent', 'violet' );

$accent_presets = [
	'dark'    => [ 'primary' => '#3b82f6', 'gradient_start' => '#1f2937', 'gradient_end' => '#090d16' ],
	'violet'  => [ 'primary' => '#7747ff', 'gradient_start' => '#7747ff', 'gradient_end' => '#1a1038' ],
	'blue'    => [ 'primary' => '#0562ef', 'gradient_start' => '#0562ef', 'gradient_end' => '#041635' ],
	'ocean'   => [ 'primary' => '#0284c7', 'gradient_start' => '#0284c7', 'gradient_end' => '#041d2f' ],
	'sky'     => [ 'primary' => '#0ea5e9', 'gradient_start' => '#0ea5e9', 'gradient_end' => '#042435' ],
	'teal'    => [ 'primary' => '#0d9488', 'gradient_start' => '#0d9488', 'gradient_end' => '#042421' ],
	'emerald' => [ 'primary' => '#0db879', 'gradient_start' => '#0db879', 'gradient_end' => '#04281b' ],
	'green'   => [ 'primary' => '#0db879', 'gradient_start' => '#0db879', 'gradient_end' => '#04281b' ],
	'amber'   => [ 'primary' => '#f3ad20', 'gradient_start' => '#f3ad20', 'gradient_end' => '#35260c' ],
	'coral'   => [ 'primary' => '#f15f55', 'gradient_start' => '#f15f55', 'gradient_end' => '#351513' ],
	'neutral' => [ 'primary' => '#6366f1', 'gradient_start' => '#3f3f46', 'gradient_end' => '#141416' ],
];

// Helper para calcular un color final de degradado profundo complementario para cualquier HEX
$calc_dark_end = function( $hex ) {
	$clean = ltrim( $hex, '#' );
	if ( strlen( $clean ) === 6 ) {
		$r = hexdec( substr( $clean, 0, 2 ) );
		$g = hexdec( substr( $clean, 2, 2 ) );
		$b = hexdec( substr( $clean, 4, 2 ) );
		$end_r = max( 4, (int) round( $r * 0.22 ) );
		$end_g = max( 6, (int) round( $g * 0.22 ) );
		$end_b = max( 14, (int) round( $b * 0.22 ) );
		return sprintf( '#%02x%02x%02x', $end_r, $end_g, $end_b );
	}
	return '#090d16';
};

$hex_to_preset = [
	'#18181b' => 'dark',
	'#7747ff' => 'violet',
	'#0562ef' => 'blue',
	'#0284c7' => 'ocean',
	'#0ea5e9' => 'sky',
	'#0d9488' => 'teal',
	'#0db879' => 'emerald',
	'#f3ad20' => 'amber',
	'#f15f55' => 'coral',
];

if ( $sync_colors ) {
	$matched_key = isset( $accent_presets[ $academy_accent ] ) ? $academy_accent : ( isset( $hex_to_preset[ strtolower( $academy_accent ) ] ) ? $hex_to_preset[ strtolower( $academy_accent ) ] : null );

	if ( $matched_key && isset( $accent_presets[ $matched_key ] ) ) {
		$login_primary = $accent_presets[ $matched_key ]['primary'];
		$hero_gradient = "linear-gradient(135deg, {$accent_presets[$matched_key]['gradient_start']} 0%, {$accent_presets[$matched_key]['gradient_end']} 100%)";
	} elseif ( str_starts_with( $academy_accent, '#' ) ) {
		$login_primary = $academy_accent;
		$deep_end = $calc_dark_end( $academy_accent );
		$hero_gradient = "linear-gradient(135deg, {$academy_accent} 0%, {$deep_end} 100%)";
	} else {
		$login_primary = '#7747ff';
		$hero_gradient = 'linear-gradient(135deg, #7747ff 0%, #1a1038 100%)';
	}
} else {
	$login_primary = ! empty( $auth_settings['accent_color'] ) ? $auth_settings['accent_color'] : '#1d68ed';
	$start = ! empty( $auth_settings['bg_gradient_start'] ) ? $auth_settings['bg_gradient_start'] : '#1d68ed';
	$end   = ! empty( $auth_settings['bg_gradient_end'] ) ? $auth_settings['bg_gradient_end'] : '#0b3294';
	$hero_gradient = "linear-gradient(135deg, {$start} 0%, {$end} 100%)";
}

// Helpers para RGB y tono hover del color de acento
$calc_rgb = function( $hex ) {
	$clean = ltrim( $hex, '#' );
	if ( strlen( $clean ) === 6 ) {
		$r = hexdec( substr( $clean, 0, 2 ) );
		$g = hexdec( substr( $clean, 2, 2 ) );
		$b = hexdec( substr( $clean, 4, 2 ) );
		return "{$r}, {$g}, {$b}";
	}
	return '29, 99, 237';
};

$calc_hover = function( $hex ) {
	$clean = ltrim( $hex, '#' );
	if ( strlen( $clean ) === 6 ) {
		$r = hexdec( substr( $clean, 0, 2 ) );
		$g = hexdec( substr( $clean, 2, 2 ) );
		$b = hexdec( substr( $clean, 4, 2 ) );
		$hr = max( 0, (int) round( $r * 0.85 ) );
		$hg = max( 0, (int) round( $g * 0.85 ) );
		$hb = max( 0, (int) round( $b * 0.85 ) );
		return sprintf( '#%02x%02x%02x', $hr, $hg, $hb );
	}
	return '#1e40af';
};

$login_rgb   = $calc_rgb( $login_primary );
$login_hover = $calc_hover( $login_primary );

// Cálculo de contraste WCAG para el texto de botones dinámicos (idéntico al sistema global)
$calc_contrast_foreground = function( $hex ) {
	$clean = ltrim( $hex, '#' );
	if ( strlen( $clean ) === 3 ) {
		$clean = $clean[0] . $clean[0] . $clean[1] . $clean[1] . $clean[2] . $clean[2];
	}
	if ( strlen( $clean ) === 6 ) {
		$r = hexdec( substr( $clean, 0, 2 ) ) / 255;
		$g = hexdec( substr( $clean, 2, 2 ) ) / 255;
		$b = hexdec( substr( $clean, 4, 2 ) ) / 255;

		$calc_c = function( $v ) {
			return $v <= 0.03928 ? $v / 12.92 : pow( ( $v + 0.055 ) / 1.055, 2.4 );
		};

		$lum = $calc_c( $r ) * 0.2126 + $calc_c( $g ) * 0.7152 + $calc_c( $b ) * 0.0722;
		return $lum > 0.44 ? '#090a0f' : '#ffffff';
	}
	return '#ffffff';
};

$btn_text_color = $calc_contrast_foreground( $login_primary );

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

  <style id="crezca-auth-dynamic-styles">
    :root {
      --auth-primary: <?php echo esc_attr( $login_primary ); ?>;
      --auth-primary-hover: <?php echo esc_attr( $login_hover ); ?>;
      --auth-primary-rgb: <?php echo esc_attr( $login_rgb ); ?>;
      --auth-input-focus: <?php echo esc_attr( $login_primary ); ?>;
      --auth-btn-text: <?php echo esc_attr( $btn_text_color ); ?>;
    }
    .auth-submit-btn {
      background: <?php echo esc_attr( $login_primary ); ?> !important;
      color: <?php echo esc_attr( $btn_text_color ); ?> !important;
      box-shadow: 0 4px 14px rgba(<?php echo esc_attr( $login_rgb ); ?>, 0.35) !important;
    }
    .auth-submit-btn:hover {
      background: <?php echo esc_attr( $login_hover ); ?> !important;
      color: <?php echo esc_attr( $btn_text_color ); ?> !important;
      box-shadow: 0 6px 20px rgba(<?php echo esc_attr( $login_rgb ); ?>, 0.45) !important;
    }
    .auth-submit-btn .auth-spinner {
      border-color: <?php echo $btn_text_color === '#ffffff' ? 'rgba(255, 255, 255, 0.3)' : 'rgba(9, 10, 15, 0.3)'; ?> !important;
      border-top-color: <?php echo esc_attr( $btn_text_color ); ?> !important;
    }
    .auth-link {
      color: <?php echo esc_attr( $login_primary ); ?> !important;
    }
    .auth-link:hover {
      color: <?php echo esc_attr( $login_hover ); ?> !important;
    }
    .remember-label input[type="checkbox"] {
      accent-color: <?php echo esc_attr( $login_primary ); ?> !important;
    }
    .auth-input:focus {
      border-color: <?php echo esc_attr( $login_primary ); ?> !important;
      box-shadow: 0 0 0 3px rgba(<?php echo esc_attr( $login_rgb ); ?>, 0.16) !important;
    }
    .hero-step-card.active .card-badge {
      background: <?php echo esc_attr( $login_primary ); ?> !important;
    }
    <?php if ( ! $sync_colors && ! empty( $auth_settings['bg_type'] ) && $auth_settings['bg_type'] === 'solid' && ! empty( $auth_settings['bg_color'] ) ) : ?>
      .alezux-hero-panel {
        background: <?php echo esc_attr( $auth_settings['bg_color'] ); ?> !important;
      }
    <?php elseif ( ! $sync_colors && ! empty( $auth_settings['bg_type'] ) && $auth_settings['bg_type'] === 'image' && ! empty( $auth_settings['bg_image_url'] ) ) : ?>
      .alezux-hero-panel {
        background-image: linear-gradient(rgba(15, 23, 42, 0.72), rgba(15, 23, 42, 0.85)), url('<?php echo esc_url( $auth_settings['bg_image_url'] ); ?>') !important;
        background-size: cover !important;
        background-position: center !important;
      }
    <?php else : ?>
      .alezux-hero-panel {
        background: <?php echo $hero_gradient; ?> !important;
      }
    <?php endif; ?>
    <?php if ( ! empty( $auth_settings['custom_css'] ) ) : ?>
      <?php echo wp_strip_all_tags( $auth_settings['custom_css'] ); ?>
    <?php endif; ?>
  </style>
</head>
<body class="alezux-auth-body <?php echo $form_theme === 'dark' ? 'theme-dark' : 'theme-light'; ?>">
<?php else : ?>
<div class="alezux-auth-body <?php echo $form_theme === 'dark' ? 'theme-dark' : 'theme-light'; ?>">
<?php endif; ?>

<div class="alezux-auth-wrapper">
  <div class="alezux-auth-card <?php echo $form_theme === 'dark' ? 'theme-dark' : 'theme-light'; ?>">
    
    <!-- ======================================================== -->
    <!-- PANEL IZQUIERDO: HERO PERSONALIZABLE (SOLO DESKTOP)      -->
    <!-- ======================================================== -->
    <div class="alezux-hero-panel">
      <!-- Marca / Logo -->
      <a href="<?php echo esc_url( home_url() ); ?>" class="hero-brand">
        <?php if ( $logo_img ) : ?>
          <img src="<?php echo esc_url( $logo_img ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" style="max-height: 40px; max-width: <?php echo esc_attr( $logo_width ); ?>px; width: auto; object-fit: contain;">
        <?php elseif ( $site_icon ) : ?>
          <img src="<?php echo esc_url( $site_icon ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" style="width: 32px; height: 32px; border-radius: 8px;">
          <span><?php echo esc_html( $site_name ); ?></span>
        <?php else : ?>
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
        <?php if ( $badge_enabled && ! empty( $hero_badge ) ) : ?>
          <div class="hero-pill">
            <span><?php echo esc_html( $hero_badge ); ?></span>
          </div>
        <?php endif; ?>
        <h1 class="hero-title"><?php echo esc_html( $hero_title ); ?></h1>
        <p class="hero-subtitle">
          <?php echo esc_html( $hero_subtitle ); ?>
        </p>
      </div>

      <!-- Grid de 3 Tarjetas de Pasos / Beneficios -->
      <?php if ( $steps_enabled ) : ?>
        <div class="hero-cards-grid">
          <div class="hero-step-card active">
            <div class="card-badge"><?php echo esc_html( ! empty( $auth_settings['step_1_num'] ) ? $auth_settings['step_1_num'] : '1' ); ?></div>
            <div class="card-text"><?php echo nl2br( esc_html( ! empty( $auth_settings['step_1_text'] ) ? $auth_settings['step_1_text'] : "Register your\naccount" ) ); ?></div>
          </div>

          <div class="hero-step-card glass">
            <div class="card-badge"><?php echo esc_html( ! empty( $auth_settings['step_2_num'] ) ? $auth_settings['step_2_num'] : '2' ); ?></div>
            <div class="card-text"><?php echo nl2br( esc_html( ! empty( $auth_settings['step_2_text'] ) ? $auth_settings['step_2_text'] : "Set up your profile\ninformation" ) ); ?></div>
          </div>

          <div class="hero-step-card glass">
            <div class="card-badge"><?php echo esc_html( ! empty( $auth_settings['step_3_num'] ) ? $auth_settings['step_3_num'] : '3' ); ?></div>
            <div class="card-text"><?php echo nl2br( esc_html( ! empty( $auth_settings['step_3_text'] ) ? $auth_settings['step_3_text'] : "Access your courses\ninstantly" ) ); ?></div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- ======================================================== -->
    <!-- PANEL DERECHO: FORMULARIOS (WHITE SURFACE)               -->
    <!-- ======================================================== -->
    <div class="alezux-form-panel <?php echo $form_theme === 'dark' ? 'theme-dark' : 'theme-light'; ?>">

      <!-- Cabecera Móvil: Logo arriba solo en teléfonos a petición del usuario -->
      <div class="mobile-brand-header">
        <a href="<?php echo esc_url( home_url() ); ?>" class="mobile-brand-link">
          <?php if ( $logo_img ) : ?>
            <img src="<?php echo esc_url( $logo_img ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" style="max-height: 48px; max-width: <?php echo esc_attr( $logo_width ); ?>px; width: auto; object-fit: contain;">
          <?php elseif ( $site_icon ) : ?>
            <img src="<?php echo esc_url( $site_icon ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" style="width: 34px; height: 34px; border-radius: 8px;">
            <span><?php echo esc_html( $site_name ); ?></span>
          <?php else : ?>
            <span><?php echo esc_html( $site_name ); ?></span>
          <?php endif; ?>
        </a>
      </div>
      
      <!-- ---------------------------------------------------- -->
      <!-- VISTA 1: INICIO DE SESIÓN                            -->
      <!-- ---------------------------------------------------- -->
      <div id="alezux-view-login" class="auth-view <?php echo $is_login ? '' : 'is-hidden'; ?>">
        <div class="form-header">
          <h2 class="form-title"><?php echo esc_html( $login_title ); ?></h2>
          <p class="form-subtitle"><?php echo esc_html( $login_subtitle ); ?></p>
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
        </form>
      </div>

      <!-- ---------------------------------------------------- -->
      <!-- VISTA 2: REGISTRO DE CUENTA                          -->
      <!-- ---------------------------------------------------- -->
      <div id="alezux-view-register" class="auth-view <?php echo ( $is_register && $allow_register ) ? '' : 'is-hidden'; ?>">
        <div class="form-header">
          <h2 class="form-title">Crear Cuenta</h2>
          <p class="form-subtitle">Ingresa tus datos personales para unirte a la academia</p>
        </div>

        <div class="auth-alert-container"></div>

        <?php if ( ! $allow_register ) : ?>
          <div style="background: rgba(243, 173, 32, 0.12); border: 1px solid rgba(243, 173, 32, 0.3); color: #d97706; padding: 14px; border-radius: 10px; font-size: 13px; text-align: center; margin-bottom: 20px;">
            El registro público está desactivado. Para obtener acceso, comunícate con la academia.
          </div>
        <?php else : ?>
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

            <div class="auth-row-2col">
              <div class="auth-field">
                <label class="field-label" for="alezux-register-fullname">Nombre Completo</label>
                <div class="input-wrapper">
                  <input 
                    type="text" 
                    id="alezux-register-fullname" 
                    class="auth-input" 
                    placeholder="Ej. Ana Pérez" 
                    required 
                    autocomplete="name"
                  >
                </div>
              </div>

              <div class="auth-field">
                <label class="field-label" for="alezux-register-username">Nombre de Usuario</label>
                <div class="input-wrapper">
                  <input 
                    type="text" 
                    id="alezux-register-username" 
                    class="auth-input" 
                    placeholder="usuario123" 
                    required 
                    autocomplete="username"
                  >
                </div>
              </div>
            </div>

            <div class="auth-field">
              <label class="field-label" for="alezux-register-password">Contraseña</label>
              <div class="input-wrapper">
                <input 
                  type="password" 
                  id="alezux-register-password" 
                  class="auth-input" 
                  placeholder="Mínimo 8 caracteres" 
                  required 
                  autocomplete="new-password"
                >
                <button type="button" class="toggle-pwd-btn" aria-label="Mostrar contraseña">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="7" r="3"></circle>
                  </svg>
                </button>
              </div>
            </div>

            <button type="submit" class="auth-submit-btn" style="margin-top: 14px;">
              <span class="auth-spinner"></span>
              <span class="btn-text">Crear Cuenta</span>
            </button>

            <div class="auth-switch-row">
              ¿Ya tienes una cuenta?
              <a href="#login" class="auth-link" data-auth-target="login">Iniciar sesión</a>
            </div>
          </form>
        <?php endif; ?>
      </div>

      <!-- ---------------------------------------------------- -->
      <!-- VISTA 3: RECUPERACIÓN DE CONTRASEÑA                  -->
      <!-- ---------------------------------------------------- -->
      <div id="alezux-view-recover" class="auth-view <?php echo $is_recover ? '' : 'is-hidden'; ?>">
        <div class="form-header">
          <h2 class="form-title">Recuperar Contraseña</h2>
          <p class="form-subtitle">Ingresa tu correo o usuario y te enviaremos un enlace de restablecimiento.</p>
        </div>

        <div class="auth-alert-container"></div>

        <form id="alezux-recover-form">
          <div class="auth-field">
            <label class="field-label" for="alezux-recover-user">Correo o Usuario</label>
            <div class="input-wrapper">
              <input 
                type="text" 
                id="alezux-recover-user" 
                name="user_login"
                class="auth-input" 
                placeholder="tu@correo.com o usuario" 
                required
                autocomplete="username"
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
      <!-- VISTA 4: RESTABLECER CONTRASEÑA                      -->
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
                  <circle cx="12" cy="7" r="3"></circle>
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
                  <circle cx="12" cy="7" r="3"></circle>
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
        Al continuar, confirmas que has leído y aceptas los 
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
