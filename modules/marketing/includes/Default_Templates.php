<?php
namespace Alezux_Members\Modules\Marketing\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Default_Templates {

	/**
	 * Obtiene el juego de colores de acento calibrado para accesibilidad y contraste WCAG AA.
	 *
	 * @param string $accent  Identificador del preset ('violet', 'blue', 'green', 'amber', 'coral') o código HEX.
	 * @param bool   $is_dark Modo oscuro activo.
	 * @return array{accent: string, button_bg: string, button_text: string, badge_bg: string, badge_text: string, badge_border: string, shadow: string}
	 */
	public static function get_accent_palette( $accent = 'violet', $is_dark = false ) {
		$accent = strtolower( trim( (string) $accent ) );

		// Paletas predefinidas con contraste calibrado para fondo claro y oscuro
		$palettes = [
			'violet' => [
				'accent'         => '#7747ff',
				'accent_dark'    => '#5b2bd9',
				'accent_light'   => '#a78bff',
				'button_bg'      => '#7747ff',
				'button_text'    => '#ffffff',
				'badge_bg_light' => '#f3f0ff',
				'badge_tx_light' => '#5825d6',
				'badge_bd_light' => '#ddd6fe',
				'badge_bg_dark'  => 'rgba(119, 71, 255, 0.18)',
				'badge_tx_dark'  => '#c7b7ff',
				'badge_bd_dark'  => 'rgba(119, 71, 255, 0.32)',
				'shadow'         => 'rgba(119, 71, 255, 0.35)',
			],
			'blue' => [
				'accent'         => '#0562ef',
				'accent_dark'    => '#0345b5',
				'accent_light'   => '#60a5fa',
				'button_bg'      => '#0562ef',
				'button_text'    => '#ffffff',
				'badge_bg_light' => '#eff6ff',
				'badge_tx_light' => '#0345b5',
				'badge_bd_light' => '#bfdbfe',
				'badge_bg_dark'  => 'rgba(5, 98, 239, 0.18)',
				'badge_tx_dark'  => '#93c5fd',
				'badge_bd_dark'  => 'rgba(5, 98, 239, 0.32)',
				'shadow'         => 'rgba(5, 98, 239, 0.35)',
			],
			'green' => [
				// Calibrado a #059669 para asegurar contraste > 4.5:1 con texto blanco en fondo claro
				'accent'         => '#059669',
				'accent_dark'    => '#047857',
				'accent_light'   => '#34d399',
				'button_bg'      => '#059669',
				'button_text'    => '#ffffff',
				'badge_bg_light' => '#ecfdf5',
				'badge_tx_light' => '#065f46',
				'badge_bd_light' => '#a7f3d0',
				'badge_bg_dark'  => 'rgba(16, 185, 129, 0.18)',
				'badge_tx_dark'  => '#6ee7b7',
				'badge_bd_dark'  => 'rgba(16, 185, 129, 0.32)',
				'shadow'         => 'rgba(5, 150, 105, 0.35)',
			],
			'amber' => [
				// Calibrado a #d97706 para garantizar legibilidad óptima sobre fondo claro
				'accent'         => '#d97706',
				'accent_dark'    => '#b45309',
				'accent_light'   => '#fbbf24',
				'button_bg'      => '#d97706',
				'button_text'    => '#ffffff',
				'badge_bg_light' => '#fffbeb',
				'badge_tx_light' => '#92400e',
				'badge_bd_light' => '#fde68a',
				'badge_bg_dark'  => 'rgba(245, 158, 11, 0.18)',
				'badge_tx_dark'  => '#fcd34d',
				'badge_bd_dark'  => 'rgba(245, 158, 11, 0.32)',
				'shadow'         => 'rgba(217, 119, 6, 0.35)',
			],
			'coral' => [
				// Calibrado a #e0483d para tono coral de alto contraste
				'accent'         => '#e0483d',
				'accent_dark'    => '#b91c1c',
				'accent_light'   => '#fca5a5',
				'button_bg'      => '#e0483d',
				'button_text'    => '#ffffff',
				'badge_bg_light' => '#fef2f2',
				'badge_tx_light' => '#991b1b',
				'badge_bd_light' => '#fecaca',
				'badge_bg_dark'  => 'rgba(241, 95, 85, 0.18)',
				'badge_tx_dark'  => '#fca5a5',
				'badge_bd_dark'  => 'rgba(241, 95, 85, 0.32)',
				'shadow'         => 'rgba(224, 72, 61, 0.35)',
			],
			'neutral' => [
				'accent'         => '#3f3f46',
				'accent_dark'    => '#27272a',
				'accent_light'   => '#a1a1aa',
				'button_bg'      => '#3f3f46',
				'button_text'    => '#ffffff',
				'badge_bg_light' => '#f4f4f5',
				'badge_tx_light' => '#27272a',
				'badge_bd_light' => '#e4e4e7',
				'badge_bg_dark'  => 'rgba(113, 113, 122, 0.18)',
				'badge_tx_dark'  => '#d4d4d8',
				'badge_bd_dark'  => 'rgba(113, 113, 122, 0.32)',
				'shadow'         => 'rgba(63, 63, 70, 0.35)',
			],
		];

		if ( isset( $palettes[ $accent ] ) ) {
			$p = $palettes[ $accent ];
		} else {
			// Manejo de código HEX personalizado con cálculo de luminancia
			$hex = ltrim( $accent, '#' );
			if ( strlen( $hex ) === 3 ) {
				$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
			}
			if ( preg_match( '/^[0-9a-f]{6}$/i', $hex ) ) {
				$r = hexdec( substr( $hex, 0, 2 ) );
				$g = hexdec( substr( $hex, 2, 2 ) );
				$b = hexdec( substr( $hex, 4, 2 ) );

				// Luminancia relativa
				$lum = ( 0.299 * $r + 0.587 * $g + 0.114 * $b ) / 255;

				// Si el color es demasiado claro para texto blanco en botones, oscurecerlo
				$btn_r = $lum > 0.55 ? (int)( $r * 0.72 ) : $r;
				$btn_g = $lum > 0.55 ? (int)( $g * 0.72 ) : $g;
				$btn_b = $lum > 0.55 ? (int)( $b * 0.72 ) : $b;
				$button_bg = sprintf( '#%02x%02x%02x', $btn_r, $btn_g, $btn_b );

				$dark_r = (int)( $r * 0.65 );
				$dark_g = (int)( $g * 0.65 );
				$dark_b = (int)( $b * 0.65 );
				$text_dark = sprintf( '#%02x%02x%02x', $dark_r, $dark_g, $dark_b );

				$p = [
					'accent'         => '#' . $hex,
					'accent_dark'    => $text_dark,
					'accent_light'   => '#' . $hex,
					'button_bg'      => $button_bg,
					'button_text'    => '#ffffff',
					'badge_bg_light' => 'rgba(' . $r . ',' . $g . ',' . $b . ',0.1)',
					'badge_tx_light' => $text_dark,
					'badge_bd_light' => 'rgba(' . $r . ',' . $g . ',' . $b . ',0.25)',
					'badge_bg_dark'  => 'rgba(' . $r . ',' . $g . ',' . $b . ',0.2)',
					'badge_tx_dark'  => '#' . $hex,
					'badge_bd_dark'  => 'rgba(' . $r . ',' . $g . ',' . $b . ',0.35)',
					'shadow'         => 'rgba(' . $r . ',' . $g . ',' . $b . ',0.35)',
				];
			} else {
				$p = $palettes['violet'];
			}
		}

		return [
			'accent'       => $is_dark ? $p['accent_light'] : $p['accent'],
			'button_bg'    => $p['button_bg'],
			'button_text'  => $p['button_text'],
			'badge_bg'     => $is_dark ? $p['badge_bg_dark'] : $p['badge_bg_light'],
			'badge_text'   => $is_dark ? $p['badge_tx_dark'] : $p['badge_tx_light'],
			'badge_border' => $is_dark ? $p['badge_bd_dark'] : $p['badge_bd_light'],
			'shadow'       => $p['shadow'],
		];
	}

	/**
	 * Renderiza un icono vectorial Lucide SVG para correos electrónicos.
	 *
	 * @param string $name  Nombre del icono (sparkles, key, lock, shield-check, check-circle, credit-card, alert-circle, calendar, file-text, award, book-open, play-circle, zap, graduation-cap).
	 * @param string $color Color del trazo (stroke).
	 * @param int    $size  Tamaño en píxeles.
	 * @return string Código SVG inline seguro para email.
	 */
	public static function lucide_icon( $name, $color = '#7747ff', $size = 14 ) {
		$icons = [
			'sparkles'       => '<path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/><path d="M5 3v4"/><path d="M7 5H3"/><path d="M19 17v4"/><path d="M21 19h-4"/>',
			'key'            => '<path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4"/><path d="m21 2-9.6 9.6"/><circle cx="7.5" cy="15.5" r="5.5"/>',
			'lock'           => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
			'shield-check'   => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
			'check-circle'   => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
			'credit-card'    => '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
			'alert-circle'   => '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>',
			'calendar'       => '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>',
			'file-text'      => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>',
			'award'          => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
			'trophy'         => '<path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>',
			'book-open'      => '<path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>',
			'play-circle'    => '<circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/>',
			'zap'            => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
			'graduation-cap' => '<path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>',
			'info'           => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
		];

		if ( ! isset( $icons[ $name ] ) ) {
			return '';
		}

		return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: -2px; margin-right: 6px;">' . $icons[ $name ] . '</svg>';
	}

	/**
	 * Obtiene una plantilla de correo por tipo, adaptada al tema Arc y color de acento configurado.
	 *
	 * @param string      $type   Identificador de la plantilla.
	 * @param string|null $theme  'clean' o 'dark'.
	 * @param string|null $accent Color de acento de la plataforma ('violet', 'blue', 'green', 'amber', 'coral' o HEX).
	 * @return array{subject: string, content: string}
	 */
	public static function get( $type, $theme = null, $accent = null ) {
		if ( empty( $theme ) ) {
			$theme = function_exists( 'get_option' ) ? get_option( 'alezux_marketing_email_theme', 'clean' ) : 'clean';
		}
		if ( ! in_array( $theme, [ 'clean', 'dark' ], true ) ) {
			$theme = 'clean';
		}

		if ( empty( $accent ) ) {
			$accent = function_exists( 'get_option' ) ? get_option( 'alezux_accent', 'violet' ) : 'violet';
		}

		$is_dark = ( 'dark' === $theme );
		$accent_data = self::get_accent_palette( $accent, $is_dark );

		$colors = [
			'body_bg'        => $is_dark ? '#0c0d14' : '#f3f4f8',
			'card_bg'        => $is_dark ? '#141523' : '#ffffff',
			'card_border'    => $is_dark ? '#222538' : '#e6e8ee',
			'text_primary'   => $is_dark ? '#f8fafc' : '#0f172a',
			'text_secondary' => $is_dark ? '#94a3b8' : '#475569',
			'text_muted'     => $is_dark ? '#64748b' : '#94a3b8',
			'box_bg'         => $is_dark ? '#191b2c' : '#f8fafc',
			'box_border'     => $is_dark ? '#292c44' : '#e2e8f0',
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

		// Helpers de componentes Arc con Lucide Icons y colores calibrados
		$badge = function( $text, $icon_name = '', $custom_bg = null, $custom_color = null, $custom_border = null ) use ( $colors, $accent_data ) {
			$bg = $custom_bg ? $custom_bg : $accent_data['badge_bg'];
			$color = $custom_color ? $custom_color : $accent_data['badge_text'];
			$border = $custom_border ? $custom_border : $accent_data['badge_border'];
			$icon_html = $icon_name ? self::lucide_icon( $icon_name, $color, 14 ) : '';
			return '<div style="margin-bottom: 18px;"><span style="display: inline-block; background-color: ' . $bg . '; color: ' . $color . '; border: 1px solid ' . $border . '; border-radius: 9999px; padding: 5px 14px; font-size: 12px; font-weight: 600; letter-spacing: 0.02em; line-height: 1;">' . $icon_html . $text . '</span></div>';
		};

		$title = function( $text ) use ( $colors ) {
			return '<h1 style="margin: 0 0 14px 0; font-size: 24px; font-weight: 700; color: ' . $colors['text_primary'] . '; line-height: 1.25; letter-spacing: -0.02em;">' . $text . '</h1>';
		};

		$paragraph = function( $text ) use ( $colors ) {
			return '<p style="margin: 0 0 16px 0; font-size: 15px; color: ' . $colors['text_secondary'] . '; line-height: 1.65;">' . $text . '</p>';
		};

		$button = function( $text, $url, $custom_bg = null ) use ( $colors, $accent_data ) {
			$bg = $custom_bg ? $custom_bg : $accent_data['button_bg'];
			$color = $accent_data['button_text'];
			$shadow = $accent_data['shadow'];
			return '
				<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin: 28px auto 12px auto;">
					<tr>
						<td align="center" style="border-radius: 9999px; background-color: ' . $bg . '; box-shadow: 0 4px 14px ' . $shadow . ';">
							<a href="' . $url . '" class="arc-btn" style="background-color: ' . $bg . '; color: ' . $color . ' !important; font-size: 14px; font-weight: 600; text-decoration: none; padding: 13px 32px; border-radius: 9999px; display: inline-block; letter-spacing: 0.01em;">
								' . $text . '
							</a>
						</td>
					</tr>
				</table>
			';
		};

		$box_start = function( $title_text = '', $icon_name = '' ) use ( $colors, $accent_data ) {
			$header_html = '';
			if ( ! empty( $title_text ) ) {
				$icon_html = $icon_name ? self::lucide_icon( $icon_name, $accent_data['accent'], 14 ) : '';
				$header_html = '<div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: ' . $accent_data['accent'] . '; margin-bottom: 12px; line-height: 1;">' . $icon_html . $title_text . '</div>';
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

		$row = function( $label, $value, $is_code = false, $is_last = false ) use ( $colors, $accent_data ) {
			$val_html = $value;
			if ( $is_code ) {
				$val_html = '<span style="display: inline-block; background-color: ' . $colors['code_bg'] . '; border: 1px solid ' . $colors['code_border'] . '; padding: 3px 10px; border-radius: 6px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 13px; font-weight: 700; color: ' . $accent_data['accent'] . ';">' . $value . '</span>';
			}
			$border_bottom = $is_last ? '' : 'border-bottom: 1px solid ' . $colors['divider'] . ';';
			return '
				<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0; padding: 0;">
					<tr>
						<td class="arc-data-td" style="padding: 9px 0; font-size: 13px; font-weight: 500; color: ' . $colors['text_muted'] . '; width: 38%; vertical-align: middle; ' . $border_bottom . '">
							' . $label . '
						</td>
						<td class="arc-data-td" style="padding: 9px 0; font-size: 14px; font-weight: 600; color: ' . $colors['text_primary'] . '; width: 62%; vertical-align: middle; text-align: right; ' . $border_bottom . '">
							' . $val_html . '
						</td>
					</tr>
				</table>
			';
		};

		$password_card = function( $password_var ) use ( $colors, $accent_data ) {
			return '
				<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 18px 0; background-color: ' . $colors['code_bg'] . '; border: 1.5px dashed ' . $accent_data['accent'] . '; border-radius: 14px; overflow: hidden;">
					<tr>
						<td align="center" style="padding: 20px 18px; text-align: center;">
							<div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: ' . $colors['text_muted'] . '; margin-bottom: 8px;">
								🔑 Contraseña de Acceso
							</div>
							<div style="display: inline-block; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 19px; font-weight: 800; letter-spacing: 2px; color: ' . $accent_data['accent'] . '; padding: 9px 22px; background-color: ' . $colors['card_bg'] . '; border: 1px solid ' . $colors['card_border'] . '; border-radius: 8px; -webkit-user-select: all; -moz-user-select: all; user-select: all; cursor: pointer;" title="Toca o haz doble clic para seleccionar toda la contraseña">
								' . $password_var . '
							</div>
							<div style="font-size: 11px; color: ' . $colors['text_muted'] . '; margin-top: 10px; line-height: 1.4;">
								📋 <em>Toca o haz doble clic sobre la contraseña para seleccionarla y copiarla</em>
							</div>
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
						$badge( 'Cuenta Creada Exitosamente', 'sparkles' ) .
						$title( '¡Hola, {{user.first_name}}!' ) .
						$paragraph( 'Nos emociona darte la bienvenida a <strong>{{site_name}}</strong>. Tu acceso a la plataforma ya se encuentra activo y tienes todo preparado para empezar a aprender.' ) .
						$box_start( 'Tus Credenciales de Acceso', 'key' ) .
						$row( 'Usuario / Correo', '{{user.username}}' ) .
						$row( 'Curso o Membresía', '{{course_title}}', false, true ) .
						$box_end() .
						$password_card( '{{password}}' ) .
						$paragraph( 'Por motivos de seguridad, te sugerimos actualizar tu contraseña después de tu primer inicio de sesión.' ) .
						$button( 'Acceder al Campus Virtual &rarr;', '{{login_url}}' ) .
						$footer
				];

			case 'user_recover_password':
				return [
					'subject' => 'Recuperación de Contraseña - {{site_name}}',
					'content' => $header .
						$badge( 'Seguridad de la Cuenta', 'lock' ) .
						$title( '¿Olvidaste tu contraseña?' ) .
						$paragraph( 'Hola <strong>{{user.name}}</strong>, hemos recibido una solicitud para restablecer la contraseña asociada a tu cuenta en <strong>{{site_name}}</strong>.' ) .
						$paragraph( 'Para crear una nueva contraseña y recuperar tu acceso, haz clic en el botón siguiente:' ) .
						$button( 'Restablecer Mi Contraseña &rarr;', '{{reset_link}}' ) .
						$box_start( 'Aviso de Seguridad', 'info' ) .
						$paragraph( 'Si no solicitaste este cambio, puedes ignorar este mensaje con tranquilidad. Tu contraseña actual continuará siendo totalmente segura.' ) .
						$box_end() .
						$footer
				];

			case 'admin_reset_password':
				return [
					'subject' => 'Tus credenciales de acceso han sido actualizadas',
					'content' => $header .
						$badge( 'Actualización de Seguridad', 'shield-check' ) .
						$title( 'Nueva contraseña asignada' ) .
						$paragraph( 'Hola <strong>{{user.name}}</strong>, un administrador de la plataforma ha restablecido las credenciales de tu cuenta.' ) .
						$box_start( 'Nuevos Datos de Acceso', 'key' ) .
						$row( 'Usuario', '{{user.email}}', false, true ) .
						$box_end() .
						$password_card( '{{new_password}}' ) .
						$paragraph( 'Te recomendamos cambiar esta contraseña desde los ajustes de tu perfil una vez que inicies sesión.' ) .
						$button( 'Ingresar al Campus &rarr;', '{{login_url}}' ) .
						$footer
				];

			case 'admin_transfer_alert':
				return [
					'subject' => '🔔 Nuevo pago por transferencia recibido: {{plan_name}} - {{student_name}}',
					'content' => $header .
						$badge( 'Comprobante de Pago Recibido', 'alert-circle', ( $is_dark ? 'rgba(245,158,11,0.18)' : '#fffbeb' ), ( $is_dark ? '#fcd34d' : '#d97706' ), ( $is_dark ? 'rgba(245,158,11,0.3)' : '#fde68a' ) ) .
						$title( 'Comprobante de pago pendiente de revisión' ) .
						$paragraph( 'Un alumno ha enviado un comprobante de pago por transferencia bancaria o pago manual a través del checkout de la academia. A continuación tienes los detalles para verificar la acreditación y habilitar su acceso:' ) .
						$box_start( 'Detalles del Alumno y Pago', 'credit-card' ) .
						$row( 'Alumno', '{{student_name}}' ) .
						$row( 'Correo Electrónico', '{{student_email}}' ) .
						$row( 'Teléfono / WhatsApp', '{{student_phone}}' ) .
						$row( 'Plan / Formación', '{{plan_name}}' ) .
						$row( 'Monto a Validar', '{{amount}}' ) .
						$row( 'Método de Pago', '{{payment_method}}' ) .
						$row( 'Nº Referencia', '{{reference}}', true ) .
						$row( 'Comprobante Adjunto', '<a href="{{receipt_url}}" target="_blank" style="color: ' . $accent_data['accent'] . '; font-weight: 700; text-decoration: underline;">Ver archivo adjunto</a>', false, true ) .
						$box_end() .
						$paragraph( 'Ingresa al módulo de Finanzas en el panel de administración para validar la transacción y activar al estudiante con un solo clic:' ) .
						$button( 'Revisar y Activar en Finanzas &rarr;', '{{admin_url}}' ) .
						$footer
				];

			case 'payment_success':
				return [
					'subject' => '¡Pago Confirmado! - {{plan_name}}',
					'content' => $header .
						$badge( 'Pago Procesado con Éxito', 'check-circle', ( $is_dark ? 'rgba(16,185,129,0.18)' : '#ecfdf5' ), ( $is_dark ? '#6ee7b7' : '#059669' ), ( $is_dark ? 'rgba(16,185,129,0.3)' : '#a7f3d0' ) ) .
						$title( '¡Gracias por tu compra, {{user.first_name}}!' ) .
						$paragraph( 'Hemos procesado correctamente tu pago para la suscripción <strong>{{plan_name}}</strong>. Tu acceso a los materiales y lecciones ya está disponible.' ) .
						$box_start( 'Comprobante de Transacción', 'credit-card' ) .
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
						$badge( 'Alerta de Pago', 'alert-circle', ( $is_dark ? 'rgba(239,68,68,0.18)' : '#fef2f2' ), ( $is_dark ? '#fca5a5' : '#dc2626' ), ( $is_dark ? 'rgba(239,68,68,0.3)' : '#fecaca' ) ) .
						$title( 'No pudimos procesar tu pago' ) .
						$paragraph( 'Hola <strong>{{user.first_name}}</strong>, intentamos procesar la renovación o cuota correspondiente a tu plan <strong>{{plan_name}}</strong>, pero la transacción no pudo completarse.' ) .
						$box_start( 'Detalles del Intento', 'credit-card' ) .
						$row( 'Plan', '{{plan_name}}' ) .
						$row( 'Fecha del intento', '{{attempt_date}}' ) .
						$row( 'Estado', '<span style="color: #ef4444; font-weight: 700;">Rechazado</span>', false, true ) .
						$box_end() .
						$paragraph( 'Por favor actualiza tu método de pago para evitar la interrupción de tu acceso al campus y a tus clases.' ) .
						$button( 'Actualizar Método de Pago &rarr;', '{{retry_url}}', '#e0483d' ) .
						$footer
				];

			case 'payment_reminder':
				return [
					'subject' => 'Recordatorio: Próxima cuota de {{plan_name}} - {{site_name}}',
					'content' => $header .
						$badge( 'Aviso de Renovación', 'calendar' ) .
						$title( 'Próxima cuota programada' ) .
						$paragraph( 'Hola <strong>{{user.name}}</strong>, te recordamos que en los próximos días se procesará automáticamente la siguiente cuota de tu membresía <strong>{{plan_name}}</strong>.' ) .
						$box_start( 'Resumen del Cobro', 'credit-card' ) .
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
						$badge( 'Notificación de Suscripción', 'file-text' ) .
						$title( 'Suscripción cancelada' ) .
						$paragraph( 'Hola <strong>{{user.name}}</strong>, confirmamos que la solicitud de cancelación para tu membresía <strong>{{plan_name}}</strong> ha sido procesada.' ) .
						$box_start( 'Vigencia de Acceso', 'calendar' ) .
						$row( 'Plan', '{{plan_name}}' ) .
						$row( 'Acceso activo hasta', '{{end_date}}', false, true ) .
						$box_end() .
						$paragraph( 'Podrás seguir accediendo a todos los cursos y contenidos hasta la fecha indicada. Nuestras puertas siempre estarán abiertas para cuando desees regresar.' ) .
						$button( 'Ir a la Plataforma &rarr;', '{{home_url}}' ) .
						$footer
				];

			case 'achievement_assigned':
				return [
					'subject' => '¡Ganaste un nuevo logro! {{achievement_name}}',
					'content' => $header .
						$badge( 'Nuevo Logro Desbloqueado', 'award' ) .
						$title( '¡Felicidades, {{user.first_name}}!' ) .
						$paragraph( 'Tu constancia y disciplina en la academia están dando frutos extraordinarios. Has obtenido una nueva insignia oficial:' ) .
						$box_start( 'Detalles del Logro', 'trophy' ) .
						'<div style="text-align: center; padding: 12px 0;">
							<div style="display: inline-block; width: 72px; height: 72px; line-height: 72px; border-radius: 50%; background-color: ' . $accent_data['badge_bg'] . '; border: 2px solid ' . $accent_data['badge_border'] . '; margin-bottom: 12px;">
								' . self::lucide_icon( 'award', $accent_data['accent'], 36 ) . '
							</div>
							<h3 style="margin: 0 0 6px 0; font-size: 18px; font-weight: 700; color: ' . $colors['text_primary'] . ';">{{achievement_name}}</h3>
							<p style="margin: 0; font-size: 14px; color: ' . $colors['text_secondary'] . '; line-height: 1.5;">{{achievement_desc}}</p>
						</div>' .
						$box_end() .
						$button( 'Ver Mis Logros &rarr;', '{{home_url}}/logros' ) .
						$footer
				];

			case 'course_available':
				return [
					'subject' => 'Nuevo Curso Lanzado: {{course_name}}',
					'content' => $header .
						$badge( 'Nuevo Lanzamiento', 'book-open' ) .
						$title( '¡Tenemos novedades para ti!' ) .
						$paragraph( 'Hola <strong>{{user.name}}</strong>, nos emociona anunciarte que hay nuevo contenido formativo disponible en <strong>{{site_name}}</strong>:' ) .
						$box_start( 'Nuevo Contenido Formativo', 'book-open' ) .
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
						$badge( 'Clases Nuevas', 'play-circle' ) .
						$title( '¡Hay nuevas lecciones listas!' ) .
						$paragraph( 'Hola <strong>{{user.name}}</strong>, se han incorporado nuevas lecciones al curso <strong>{{course_name}}</strong> en el cual estás inscrito:' ) .
						$box_start( 'Lecciones Publicadas', 'play-circle' ) .
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
						$badge( 'Impulsa tu Progreso', 'zap' ) .
						$title( '¡Hola, {{user.name}}!' ) .
						$paragraph( 'Hemos notado que llevas <strong>{{days_inactive}} días</strong> sin ingresar a la plataforma.' ) .
						$paragraph( 'Recuerda que la consistencia es el factor determinante para dominar cualquier habilidad. Tienes lecciones pendientes que te esperan para continuar avanzando.' ) .
						$box_start( 'Compromiso de Aprendizaje', 'zap' ) .
						'<p style="margin: 0; font-size: 14px; color: ' . $accent_data['accent'] . '; font-weight: 600; text-align: center;">Dedícale 15 minutos hoy y mantén tu impulso de aprendizaje.</p>' .
						$box_end() .
						$button( 'Continuar Aprendiendo &rarr;', '{{login_url}}' ) .
						$footer
				];

			case 'course_completed':
				return [
					'subject' => '¡Felicitaciones por graduarte de {{course_title}}!',
					'content' => $header .
						$badge( 'Graduación Completada', 'graduation-cap' ) .
						$title( '¡Lo lograste, {{user.name}}!' ) .
						$paragraph( '¡Has alcanzado el 100% de progreso en el programa <strong>{{course_title}}</strong>!' ) .
						$paragraph( 'Tu constancia y dedicación han dado resultados admirables. Tu certificado oficial de finalización ya se encuentra generado y disponible para ser compartido en tu perfil profesional.' ) .
						$box_start( 'Certificación Oficial', 'award' ) .
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
						$badge( 'Notificación', 'info' ) .
						$title( 'Aviso Importante' ) .
						$paragraph( 'Tienes una nueva notificación de tu academia <strong>{{site_name}}</strong>.' ) .
						$button( 'Ir a la Plataforma &rarr;', '{{home_url}}' ) .
						$footer
				];
		}
	}

	/**
	 * Reemplaza de forma segura la paleta de acento en un código HTML existente.
	 * Preserva los textos personalizados del usuario mientras actualiza los colores de botones,
	 * insignias, iconos Lucide y sombras al acento calibrado con WCAG.
	 *
	 * @param string $html       Contenido HTML del correo.
	 * @param string $new_accent Identificador del preset o código HEX.
	 * @param bool   $is_dark    Si el modo oscuro está activo.
	 * @return string HTML con los estilos de acento actualizados.
	 */
	public static function replace_accent_in_html( $html, $new_accent, $is_dark = false ) {
		if ( empty( $html ) ) {
			return $html;
		}

		$palette = self::get_accent_palette( $new_accent, $is_dark );

		// Lista de colores de botón anteriores que deben actualizarse
		$old_button_bgs = [
			'#7747ff', '#6366f1', '#5b2bd9', '#4f46e5', '#4338ca',
			'#0562ef', '#0345b5', '#2563eb', '#1d4ed8',
			'#059669', '#0db879', '#047857', '#10b981',
			'#d97706', '#f3ad20', '#b45309', '#f59e0b',
			'#e0483d', '#f15f55', '#b91c1c', '#ef4444',
			'#3f3f46', '#71717a', '#27272a',
			'#0073aa',
		];

		// Fondos de badge a reemplazar
		$old_badge_bgs = [
			'#f3f0ff', '#eef2ff', 'rgba(119, 71, 255, 0.18)', 'rgba(99, 102, 241, 0.18)', 'rgba(119,71,255,0.18)',
			'#eff6ff', 'rgba(5, 98, 239, 0.18)', 'rgba(37, 99, 235, 0.18)', 'rgba(5,98,239,0.18)',
			'#ecfdf5', 'rgba(16, 185, 129, 0.18)', 'rgba(5, 150, 105, 0.18)', 'rgba(16,185,129,0.18)',
			'#fffbeb', 'rgba(245, 158, 11, 0.18)', 'rgba(217, 119, 6, 0.18)', 'rgba(245,158,11,0.18)',
			'#fef2f2', 'rgba(241, 95, 85, 0.18)', 'rgba(224, 72, 61, 0.18)', 'rgba(241,95,85,0.18)',
			'#f4f4f5', 'rgba(113, 113, 122, 0.18)', 'rgba(113,113,122,0.18)',
		];

		// Bordes de badge a reemplazar
		$old_badge_bds = [
			'#ddd6fe', '#c7d2fe', 'rgba(119, 71, 255, 0.32)', 'rgba(99, 102, 241, 0.32)', 'rgba(119,71,255,0.32)',
			'#bfdbfe', 'rgba(5, 98, 239, 0.32)', 'rgba(37, 99, 235, 0.32)', 'rgba(5,98,239,0.32)',
			'#a7f3d0', 'rgba(16, 185, 129, 0.32)', 'rgba(5, 150, 105, 0.32)', 'rgba(16,185,129,0.32)',
			'#fde68a', 'rgba(245, 158, 11, 0.32)', 'rgba(217, 119, 6, 0.32)', 'rgba(245,158,11,0.32)',
			'#fecaca', 'rgba(241, 95, 85, 0.32)', 'rgba(224, 72, 61, 0.32)', 'rgba(241,95,85,0.32)',
			'#e4e4e7', 'rgba(113, 113, 122, 0.32)', 'rgba(113,113,122,0.32)',
		];

		// Textos de badge / trazos de iconos anteriores
		$old_badge_txs = [
			'#7747ff', '#5b2bd9', '#a78bff',
			'#5825d6', '#4338ca', '#c7b7ff', '#818cf8',
			'#0562ef', '#0345b5', '#93c5fd', '#1d4ed8', '#60a5fa',
			'#059669', '#0db879', '#047857', '#10b981', '#6ee7b7', '#065f46', '#34d399',
			'#d97706', '#f3ad20', '#b45309', '#f59e0b', '#fcd34d', '#92400e', '#fbbf24',
			'#e0483d', '#f15f55', '#b91c1c', '#ef4444', '#fca5a5', '#991b1b',
			'#3f3f46', '#71717a', '#27272a', '#d4d4d8', '#a1a1aa',
			'#0073aa',
		];

		// Sombras de botones anteriores
		$old_shadows = [
			'rgba(119, 71, 255, 0.35)', 'rgba(99, 102, 241, 0.35)', 'rgba(119,71,255,0.35)',
			'rgba(5, 98, 239, 0.35)', 'rgba(37, 99, 235, 0.35)', 'rgba(5,98,239,0.35)',
			'rgba(5, 150, 105, 0.35)', 'rgba(16, 185, 129, 0.35)', 'rgba(5,150,105,0.35)',
			'rgba(217, 119, 6, 0.35)', 'rgba(245, 158, 11, 0.35)', 'rgba(217,119,6,0.35)',
			'rgba(224, 72, 61, 0.35)', 'rgba(241, 95, 85, 0.35)', 'rgba(224,72,61,0.35)',
			'rgba(63, 63, 70, 0.35)', 'rgba(63,63,70,0.35)',
		];

		// 1. Reemplazar botones
		foreach ( $old_button_bgs as $old_bg ) {
			if ( strtolower( $old_bg ) !== strtolower( $palette['button_bg'] ) ) {
				$html = str_ireplace( 'background-color: ' . $old_bg, 'background-color: ' . $palette['button_bg'], $html );
				$html = str_ireplace( 'background:' . $old_bg, 'background:' . $palette['button_bg'], $html );
				$html = str_ireplace( 'background: ' . $old_bg, 'background: ' . $palette['button_bg'], $html );
			}
		}

		// 2. Reemplazar fondos de badge
		foreach ( $old_badge_bgs as $old_bg ) {
			if ( strtolower( $old_bg ) !== strtolower( $palette['badge_bg'] ) ) {
				$html = str_ireplace( 'background-color: ' . $old_bg, 'background-color: ' . $palette['badge_bg'], $html );
				$html = str_ireplace( 'background:' . $old_bg, 'background:' . $palette['badge_bg'], $html );
			}
		}

		// 3. Reemplazar bordes de badge
		foreach ( $old_badge_bds as $old_bd ) {
			if ( strtolower( $old_bd ) !== strtolower( $palette['badge_border'] ) ) {
				$html = str_ireplace( 'border: 1px solid ' . $old_bd, 'border: 1px solid ' . $palette['badge_border'], $html );
				$html = str_ireplace( 'border:1px solid ' . $old_bd, 'border:1px solid ' . $palette['badge_border'], $html );
			}
		}

		// 4. Reemplazar textos de badge, acentos e iconos SVG
		foreach ( $old_badge_txs as $old_tx ) {
			if ( strtolower( $old_tx ) !== strtolower( $palette['accent'] ) ) {
				$html = str_ireplace( 'color: ' . $old_tx, 'color: ' . $palette['accent'], $html );
				$html = str_ireplace( 'color:' . $old_tx, 'color:' . $palette['accent'], $html );
				$html = str_ireplace( 'stroke="' . $old_tx . '"', 'stroke="' . $palette['accent'] . '"', $html );
				$html = str_ireplace( 'border: 1.5px dashed ' . $old_tx, 'border: 1.5px dashed ' . $palette['accent'], $html );
				$html = str_ireplace( 'border: 1px dashed ' . $old_tx, 'border: 1px dashed ' . $palette['accent'], $html );
			}
		}

		// 5. Reemplazar sombras de botones
		foreach ( $old_shadows as $old_sh ) {
			if ( strtolower( $old_sh ) !== strtolower( $palette['shadow'] ) ) {
				$html = str_ireplace( 'box-shadow: 0 4px 14px ' . $old_sh, 'box-shadow: 0 4px 14px ' . $palette['shadow'], $html );
				$html = str_ireplace( 'box-shadow:0 4px 14px ' . $old_sh, 'box-shadow: 0 4px 14px ' . $palette['shadow'], $html );
			}
		}

		return $html;
	}

	/**
	 * Sincroniza y actualiza todas las plantillas en la base de datos con el acento y tema activos.
	 *
	 * @param string|null $accent           Acento a aplicar (o null para leer el de la opción).
	 * @param string|null $theme            Tema 'clean' o 'dark' (o null para leer el de la opción).
	 * @param bool        $force_regenerate Si es true, sobrescribe el contenido completo con el canonical de Default_Templates.
	 * @return bool
	 */
	public static function sync_database_templates( $accent = null, $theme = null, $force_regenerate = false ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'alezux_marketing_templates';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
			return false;
		}

		if ( empty( $accent ) ) {
			$accent = get_option( 'alezux_accent', 'violet' );
		}
		if ( empty( $theme ) ) {
			$theme = get_option( 'alezux_marketing_email_theme', 'clean' );
		}

		if ( ! class_exists( '\Alezux_Members\Modules\Marketing\Includes\Email_Engine' ) && file_exists( dirname( __FILE__ ) . '/Email_Engine.php' ) ) {
			require_once dirname( __FILE__ ) . '/Email_Engine.php';
		}

		$engine = new \Alezux_Members\Modules\Marketing\Includes\Email_Engine();
		$registered_types = $engine->get_registered_types();

		$existing_rows = $wpdb->get_results( "SELECT id, type, subject, content FROM $table_name", OBJECT_K );
		$is_dark = ( 'dark' === $theme );

		foreach ( $registered_types as $type_key => $info ) {
			$canonical = self::get( $type_key, $theme, $accent );
			if ( empty( $canonical['content'] ) ) {
				continue;
			}

			if ( isset( $existing_rows[ $type_key ] ) ) {
				$row = $existing_rows[ $type_key ];
				$subject = ! empty( $row->subject ) ? $row->subject : $canonical['subject'];

				// Si el contenido actual no tiene la estructura Arc o iconos Lucide, o se fuerza:
				$has_new_pwd = in_array( $type_key, [ 'student_welcome', 'admin_reset_password' ], true ) ? strpos( $row->content, 'Contraseña de Acceso' ) !== false : true;
				if ( $force_regenerate || ! $has_new_pwd || strpos( $row->content, 'arc-wrapper' ) === false || strpos( $row->content, '<svg' ) === false ) {
					$new_content = $canonical['content'];
				} else {
					// Actualizar la paleta de acento en el HTML existente respetando textos personalizados
					$new_content = self::replace_accent_in_html( $row->content, $accent, $is_dark );
				}

				$wpdb->update(
					$table_name,
					[
						'content' => $new_content,
						'subject' => $subject,
					],
					[ 'id' => $row->id ]
				);
			} else {
				$wpdb->insert(
					$table_name,
					[
						'type'      => $type_key,
						'subject'   => $canonical['subject'],
						'content'   => $canonical['content'],
						'is_active' => 1,
					]
				);
			}
		}

		update_option( 'alezux_marketing_templates_accent', $accent );
		update_option( 'alezux_marketing_templates_theme', $theme );

		return true;
	}
}
