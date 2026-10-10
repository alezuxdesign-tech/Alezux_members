<?php
/**
 * Plantilla de Checkout Multipasarela - Alezux Members (Estilo Landing / Sales Checkout)
 *
 * Página independiente de alta conversión para checkout directo desde landing pages:
 * - Muestra el logo y nombre oficial de la academia.
 * - Sin botones de retorno que provoquen fuga de alumnos.
 * - Portada del curso destacada estilo ficha de ventas (hero mockup).
 * - Radio cards interactivas con tokens de diseño Arc UI.
 * - Iconos oficiales de la librería Lucide.
 *
 * @var object $plan
 * @var array  $settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Identidad de la Academia
$academy_name = get_option( 'alezux_academy_name', '' );
if ( empty( $academy_name ) ) {
	$academy_name = get_bloginfo( 'name' );
}

$academy_logo = get_option( 'alezux_academy_logo', '' );
if ( empty( $academy_logo ) ) {
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	if ( $custom_logo_id ) {
		$academy_logo = wp_get_attachment_image_url( $custom_logo_id, 'full' );
	}
}
if ( empty( $academy_logo ) ) {
	$academy_logo = get_site_icon_url( 128 );
}

// 2. Información del Curso (Estilo Página de Ventas)
$course_id    = (int) ( $plan->course_id ?? 0 );
$course_title = 'Membresía All-Access (Todos los Cursos)';
$course_image = '';
$course_desc  = 'Acceso completo a todos los cursos, herramientas y contenidos formativos de la academia.';

if ( $course_id > 0 ) {
	$course_post = get_post( $course_id );
	if ( $course_post ) {
		$course_title = get_the_title( $course_id );
		
		// 1. Meta _course_thumbnail (formato nativo de cursos Alezux)
		$course_image = get_post_meta( $course_id, '_course_thumbnail', true );

		// 2. Meta _course_banner
		if ( empty( $course_image ) ) {
			$course_image = get_post_meta( $course_id, '_course_banner', true );
		}

		// 3. Featured Image estándar de WordPress
		if ( empty( $course_image ) ) {
			$course_image = get_the_post_thumbnail_url( $course_id, 'full' );
		}
		if ( empty( $course_image ) ) {
			$thumb_id = get_post_thumbnail_id( $course_id );
			if ( $thumb_id ) {
				$course_image = wp_get_attachment_image_url( $thumb_id, 'large' );
			}
		}

		$excerpt = wp_strip_all_tags( get_the_excerpt( $course_id ) );
		if ( ! empty( $excerpt ) ) {
			$course_desc = $excerpt;
		}
	}
}

// Portada por defecto de alta calidad si no se ha subido una o es membresía completa
if ( empty( $course_image ) ) {
	$course_image = 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=1000&auto=format&fit=crop&q=80';
}

// 3. Cálculos y Modalidad del Plan
$plan_name        = esc_html( $plan->name );
$quota_amount     = (float) $plan->quota_amount;
$amount_formatted = number_format( $quota_amount, 2, '.', ',' );
$total_quotas     = (int) $plan->total_quotas;

if ( $total_quotas === 0 ) {
	$plan_type_label = 'Membresía Recurrente';
	$billing_summary = ( isset( $plan->frequency ) && $plan->frequency === 'year' ) ? 'Facturado anualmente' : 'Facturado mensualmente';
	$price_badge     = '$' . $amount_formatted . ' USD / mes';
	$total_pay_today = '$' . $amount_formatted . ' USD';
} elseif ( $total_quotas === 1 ) {
	$plan_type_label = 'Acceso Completo (Pago Único)';
	$billing_summary = 'Acceso de por vida · Pago único';
	$price_badge     = '$' . $amount_formatted . ' USD';
	$total_pay_today = '$' . $amount_formatted . ' USD';
} else {
	$total_plan_calc = number_format( $total_quotas * $quota_amount, 2, '.', ',' );
	$plan_type_label = 'Financiación en ' . $total_quotas . ' Cuotas';
	$billing_summary = 'Cuota 1 de ' . $total_quotas . ' (Total del plan: $' . $total_plan_calc . ' USD)';
	$price_badge     = '$' . $amount_formatted . ' USD / cuota';
	$total_pay_today = '$' . $amount_formatted . ' USD';
}

// 4. WhatsApp para Comprobantes
$whatsapp_raw   = ! empty( $plan->whatsapp_number ) ? $plan->whatsapp_number : ( $settings['whatsapp_number'] ?? '' );
$whatsapp_clean = preg_replace( '/[^0-9]/', '', $whatsapp_raw );
$whatsapp_msg   = '¡Hola! Acabo de realizar el pago de $' . $amount_formatted . ' USD para el plan "' . $plan->name . '" (' . $course_title . '). Adjunto mi comprobante para que activen mi acceso a la academia. Mi correo es: ';
$whatsapp_url   = ! empty( $whatsapp_clean ) ? 'https://wa.me/' . $whatsapp_clean . '?text=' . rawurlencode( $whatsapp_msg ) : '#';

$instructions = ! empty( $settings['manual_payment_instructions'] )
	? $settings['manual_payment_instructions']
	: 'Transfiere el monto exacto y luego confirma tu comprobante por WhatsApp para activar tu acceso de inmediato.';

// 5. Pasarelas y Métodos Activos
$methods = [];

// Stripe (Tarjetas)
$stripe_sec = $settings['stripe_secret_key'] ?? get_option( 'alezux_stripe_secret_key', '' );
if ( ! empty( $settings['stripe_enabled'] ) && ! empty( $stripe_sec ) && ! empty( $plan->stripe_price_id ) ) {
	$methods['stripe'] = [
		'id'          => 'stripe',
		'title'       => 'Tarjeta de Crédito / Débito',
		'subtitle'    => 'Visa, Mastercard, American Express',
		'badge_type'  => 'cards',
		'is_stripe'   => true,
		'cta_text'    => 'Pagar $' . $amount_formatted . ' USD con Tarjeta',
		'action_url'  => add_query_arg( [ 'gateway' => 'stripe' ] ),
		'description' => 'Pago 100% cifrado y seguro a través de Stripe Checkout. Tu acceso a las lecciones y módulos se activará de forma instantánea al procesar la compra.',
	];
}

// Pago Móvil (Venezuela)
if ( ! empty( $settings['pagomovil_enabled'] ) ) {
	$pm_bank   = $settings['pagomovil_bank'] ?? '';
	$pm_phone  = $settings['pagomovil_phone'] ?? '';
	$pm_id     = $settings['pagomovil_id'] ?? '';
	$pm_holder = $settings['pagomovil_holder'] ?? '';

	$pm_copy = "Pago Móvil Venezuela:\nBanco: {$pm_bank}\nTeléfono: {$pm_phone}\nC.I / RIF: {$pm_id}\nTitular: {$pm_holder}\nMonto: \${$amount_formatted} USD (a tasa oficial BCV)";

	$methods['pagomovil'] = [
		'id'         => 'pagomovil',
		'title'      => 'Pago Móvil',
		'subtitle'   => 'Bolívares (Tasa Oficial BCV)',
		'badge_type' => 'pm',
		'is_manual'  => true,
		'cta_text'   => 'Confirmar Pago por WhatsApp ($' . $amount_formatted . ' USD)',
		'action_url' => $whatsapp_url,
		'copy_data'  => $pm_copy,
		'fields'     => [
			'Banco'        => $pm_bank,
			'Teléfono'     => $pm_phone,
			'Cédula / RIF' => $pm_id,
			'Titular'      => $pm_holder,
			'Monto Ref.'   => '$' . $amount_formatted . ' USD (a tasa oficial BCV del día)',
		],
		'notice'     => 'Realiza el pago móvil en bolívares a la tasa oficial del BCV del día.',
	];
}

// Zelle
if ( ! empty( $settings['zelle_enabled'] ) ) {
	$zelle_email  = $settings['zelle_email'] ?? '';
	$zelle_holder = $settings['zelle_holder'] ?? '';

	$methods['zelle'] = [
		'id'         => 'zelle',
		'title'      => 'Zelle',
		'subtitle'   => 'Transferencia directa en USD',
		'badge_type' => 'zelle',
		'is_manual'  => true,
		'cta_text'   => 'Confirmar Pago por WhatsApp ($' . $amount_formatted . ' USD)',
		'action_url' => $whatsapp_url,
		'copy_data'  => $zelle_email,
		'copy_label' => 'Copiar Correo Zelle',
		'fields'     => [
			'Correo Zelle' => $zelle_email,
			'Titular'      => $zelle_holder,
			'Monto Exacto' => '$' . $amount_formatted . ' USD',
		],
		'notice'     => 'En el concepto o nota de Zelle, coloca únicamente tu nombre o tu correo electrónico.',
	];
}

// Transferencia Bancaria
if ( ! empty( $settings['bank_transfer_enabled'] ) ) {
	$bt_bank   = $settings['bank_name'] ?? '';
	$bt_acc    = $settings['bank_account_number'] ?? '';
	$bt_type   = $settings['bank_account_type'] ?? 'Corriente';
	$bt_holder = $settings['bank_holder_name'] ?? '';
	$bt_id     = $settings['bank_holder_id'] ?? '';

	$bt_copy = "Transferencia Bancaria:\nBanco: {$bt_bank}\nCuenta: {$bt_acc}\nTipo: {$bt_type}\nTitular: {$bt_holder}\nID/RIF: {$bt_id}";

	$methods['bank_transfer'] = [
		'id'         => 'bank_transfer',
		'title'      => 'Transferencia Bancaria',
		'subtitle'   => $bt_bank ? $bt_bank : 'Cuenta Bancaria',
		'badge_type' => 'bank',
		'is_manual'  => true,
		'cta_text'   => 'Confirmar Pago por WhatsApp ($' . $amount_formatted . ' USD)',
		'action_url' => $whatsapp_url,
		'copy_data'  => $bt_acc,
		'copy_label' => 'Copiar N° de Cuenta',
		'fields'     => [
			'Banco'          => $bt_bank,
			'N° de Cuenta'   => $bt_acc,
			'Tipo de Cuenta' => $bt_type,
			'Titular'        => $bt_holder,
			'Identificación' => $bt_id,
			'Monto'          => '$' . $amount_formatted . ' USD',
		],
	];
}

// Binance Pay / USDT
if ( ! empty( $settings['binance_enabled'] ) ) {
	$bin_id     = $settings['binance_pay_id'] ?? '';
	$bin_wallet = $settings['binance_usdt_wallet'] ?? '';
	$bin_net    = $settings['binance_network'] ?? 'TRC20';

	$bin_copy = ! empty( $bin_wallet ) ? $bin_wallet : $bin_id;

	$methods['binance'] = [
		'id'         => 'binance',
		'title'      => 'Binance Pay / USDT',
		'subtitle'   => 'Criptomonedas estables (USDT)',
		'badge_type' => 'binance',
		'is_manual'  => true,
		'cta_text'   => 'Confirmar Pago por WhatsApp (' . $amount_formatted . ' USDT)',
		'action_url' => $whatsapp_url,
		'copy_data'  => $bin_copy,
		'copy_label' => ! empty( $bin_wallet ) ? 'Copiar Billetera USDT' : 'Copiar Binance Pay ID',
		'fields'     => array_filter( [
			'Binance Pay ID' => $bin_id,
			'Red Cripto'     => $bin_net,
			'Billetera USDT' => $bin_wallet,
			'Monto a Pagar'  => $amount_formatted . ' USDT',
		] ),
		'notice'     => 'Asegúrate de enviar los fondos exclusivamente por la red ' . esc_html( $bin_net ) . '.',
	];
}

// PayPal
if ( ! empty( $settings['paypal_enabled'] ) ) {
	$paypal_raw = $settings['paypal_email'] ?? '';
	$is_pp_link = ( strpos( $paypal_raw, 'http' ) === 0 || strpos( $paypal_raw, 'paypal.me' ) !== false );
	$pp_url     = $is_pp_link ? ( strpos( $paypal_raw, 'http' ) === 0 ? $paypal_raw : 'https://' . $paypal_raw ) : '';

	$methods['paypal'] = [
		'id'         => 'paypal',
		'title'      => 'PayPal',
		'subtitle'   => 'Saldo o cuenta PayPal',
		'badge_type' => 'paypal',
		'is_manual'  => empty( $pp_url ),
		'cta_text'   => ! empty( $pp_url ) ? 'Pagar con PayPal ($' . $amount_formatted . ' USD)' : 'Confirmar Pago por WhatsApp ($' . $amount_formatted . ' USD)',
		'action_url' => ! empty( $pp_url ) ? $pp_url : $whatsapp_url,
		'copy_data'  => $paypal_raw,
		'copy_label' => 'Copiar Cuenta PayPal',
		'fields'     => [
			'Cuenta PayPal' => $paypal_raw,
			'Monto'         => '$' . $amount_formatted . ' USD',
		],
	];
}

// Método activo inicial
$active_method_id = ! empty( $methods ) ? array_key_first( $methods ) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Checkout Oficial — <?php echo esc_html( $plan->name ); ?> | <?php echo esc_html( $academy_name ); ?></title>
	<?php if ( $academy_logo ) : ?>
		<link rel="icon" href="<?php echo esc_url( $academy_logo ); ?>" type="image/x-icon">
	<?php endif; ?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	
	<style>
		/* ========================================================
		   TOKENS DE DISEÑO ARC UI (Standalone Landing Checkout)
		   ======================================================== */
		:root {
			color-scheme: dark;
			--arc-bg: #09090b;
			--arc-surface: #121215;
			--arc-surface-subtle: #18181c;
			--arc-surface-raised: #1f1f25;
			--arc-border: rgba(255, 255, 255, 0.08);
			--arc-border-strong: rgba(255, 255, 255, 0.16);
			--arc-border-input: #27272a;
			
			/* Acento vibrante estilo fintech (verde lima/neón de referencia) */
			--arc-accent: #bef264;
			--arc-accent-dim: rgba(190, 242, 100, 0.12);
			--arc-accent-border: #a3e635;
			--arc-accent-foreground: #09090b;

			--arc-text-primary: #f4f4f5;
			--arc-text-secondary: #a1a1aa;
			--arc-text-muted: #71717a;

			--radius-control: 12px;
			--radius-card: 20px;
			--radius-pill: 9999px;

			--font-sans: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
			--transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
		}

		* {
			box-sizing: border-box;
			margin: 0;
			padding: 0;
		}

		body {
			background-color: var(--arc-bg);
			color: var(--arc-text-primary);
			font-family: var(--font-sans);
			min-height: 100vh;
			display: flex;
			flex-direction: column;
			-webkit-font-smoothing: antialiased;
			line-height: 1.45;
		}

		/* ========================================================
		   CABECERA OFICIAL DE LA ACADEMIA (Sin fugas de navegación)
		   ======================================================== */
		.checkout-brand-header {
			width: 100%;
			max-width: 1120px;
			margin: 0 auto;
			padding: 32px 24px 20px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			border-bottom: 1px solid var(--arc-border);
		}

		.brand-identity {
			display: flex;
			align-items: center;
			gap: 14px;
		}

		.academy-logo-img {
			height: 38px;
			max-width: 180px;
			object-fit: contain;
			border-radius: 8px;
		}

		.academy-name-text {
			font-size: 1.15rem;
			font-weight: 700;
			letter-spacing: -0.02em;
			color: #fff;
		}

		.secure-badge-pill {
			display: inline-flex;
			align-items: center;
			gap: 7px;
			padding: 6px 14px;
			border-radius: var(--radius-pill);
			background: rgba(163, 230, 53, 0.08);
			border: 1px solid rgba(163, 230, 53, 0.25);
			color: var(--arc-accent);
			font-size: 0.78rem;
			font-weight: 600;
		}

		.secure-badge-pill svg {
			flex-shrink: 0;
		}

		/* Layout Contenedor */
		.checkout-layout {
			max-width: 1120px;
			margin: 0 auto;
			padding: 36px 24px 80px;
			width: 100%;
			flex: 1;
		}

		/* Grid Principal 2 Columnas */
		.checkout-grid {
			display: grid;
			grid-template-columns: 1.25fr 1fr;
			gap: 48px;
			align-items: start;
		}

		@media (max-width: 960px) {
			.checkout-grid {
				grid-template-columns: 1fr;
				gap: 36px;
			}
		}

		/* ========================================================
		   COLUMNA IZQUIERDA: SELECCIÓN DE MÉTODO DE PAGO
		   ======================================================== */
		.payment-column {
			display: flex;
			flex-direction: column;
			gap: 22px;
		}

		.section-header {
			display: flex;
			flex-direction: column;
			gap: 4px;
		}

		.section-title {
			font-size: 1.5rem;
			font-weight: 700;
			letter-spacing: -0.02em;
		}

		.section-subtitle {
			font-size: 0.88rem;
			color: var(--arc-text-muted);
		}

		/* Lista de Tarjetas de Pago */
		.methods-list {
			display: flex;
			flex-direction: column;
			gap: 12px;
		}

		.method-card {
			background: var(--arc-surface);
			border: 1px solid var(--arc-border);
			border-radius: var(--radius-card);
			transition: var(--transition);
			overflow: hidden;
			cursor: pointer;
		}

		.method-card:hover {
			border-color: var(--arc-border-strong);
			background: #151519;
		}

		/* Estado Activo: Borde Verde Lima / Arc UI */
		.method-card.is-active {
			border-color: var(--arc-accent-border);
			background: var(--arc-surface);
			box-shadow: 0 0 0 1px var(--arc-accent-border), 0 8px 24px rgba(0, 0, 0, 0.4);
		}

		.card-header-row {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 18px 20px;
			user-select: none;
		}

		.radio-label-group {
			display: flex;
			align-items: center;
			gap: 14px;
		}

		.arc-radio {
			width: 20px;
			height: 20px;
			border-radius: 50%;
			border: 2px solid var(--arc-text-muted);
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
			transition: var(--transition);
		}

		.method-card:hover .arc-radio {
			border-color: var(--arc-text-secondary);
		}

		.method-card.is-active .arc-radio {
			border-color: var(--arc-accent);
		}

		.arc-radio-dot {
			width: 8px;
			height: 8px;
			border-radius: 50%;
			background: var(--arc-accent);
			opacity: 0;
			transform: scale(0.5);
			transition: var(--transition);
		}

		.method-card.is-active .arc-radio-dot {
			opacity: 1;
			transform: scale(1);
		}

		.method-title-wrap {
			display: flex;
			flex-direction: column;
			gap: 2px;
		}

		.method-main-title {
			font-size: 0.95rem;
			font-weight: 600;
			color: var(--arc-text-primary);
		}

		.method-sub-title {
			font-size: 0.78rem;
			color: var(--arc-text-muted);
		}

		.brand-badges {
			display: flex;
			align-items: center;
			gap: 6px;
		}

		.brand-pill {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 4px 8px;
			border-radius: 6px;
			font-size: 0.72rem;
			font-weight: 700;
			letter-spacing: 0.02em;
			background: var(--arc-surface-subtle);
			border: 1px solid var(--arc-border);
			color: var(--arc-text-secondary);
		}

		.brand-pill.visa { background: #00579f; color: #fff; border: none; }
		.brand-pill.mc { background: #eb001b; color: #fff; border: none; }
		.brand-pill.amex { background: #007bc1; color: #fff; border: none; }
		.brand-pill.pp { background: #fff; color: #003087; font-weight: 900; border: none; }
		.brand-pill.crypto { background: #f59e0b; color: #000; border: none; }

		/* Contenido Desplegado del Card Activo */
		.card-expanded-body {
			display: none;
			padding: 0 20px 20px;
			border-top: 1px solid rgba(255, 255, 255, 0.04);
			animation: arcFadeIn 0.22s var(--transition) forwards;
		}

		.method-card.is-active .card-expanded-body {
			display: block;
			padding-top: 16px;
		}

		@keyframes arcFadeIn {
			from { opacity: 0; transform: translateY(-4px); }
			to { opacity: 1; transform: translateY(0); }
		}

		/* Cajas de Datos Arc UI (Bordes 100% Neutros) */
		.arc-data-grid {
			display: flex;
			flex-direction: column;
			gap: 8px;
			margin-bottom: 14px;
		}

		.arc-data-item {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 10px 14px;
			background: var(--arc-surface-subtle);
			border: 1px solid var(--arc-border-input);
			border-radius: var(--radius-control);
			font-size: 0.85rem;
		}

		.arc-data-key {
			color: var(--arc-text-muted);
			font-weight: 500;
		}

		.arc-data-val {
			color: var(--arc-text-primary);
			font-weight: 600;
			user-select: all;
			font-variant-numeric: tabular-nums;
		}

		.arc-notice-box {
			display: flex;
			align-items: center;
			gap: 8px;
			font-size: 0.8rem;
			color: #eab308;
			background: rgba(234, 179, 8, 0.08);
			border: 1px solid rgba(234, 179, 8, 0.2);
			border-radius: 8px;
			padding: 8px 12px;
			margin-bottom: 14px;
		}

		.arc-notice-box svg {
			flex-shrink: 0;
		}

		/* Botón de Copiar Secundario */
		.arc-btn-secondary {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			width: 100%;
			min-height: 40px;
			padding: 0 16px;
			border-radius: var(--radius-control);
			background: var(--arc-surface-raised);
			border: 1px solid var(--arc-border);
			color: var(--arc-text-primary);
			font-family: inherit;
			font-size: 0.85rem;
			font-weight: 600;
			cursor: pointer;
			transition: var(--transition);
		}

		.arc-btn-secondary:hover {
			background: #272730;
			border-color: var(--arc-border-strong);
		}

		.arc-btn-secondary.is-copied {
			color: #4ade80;
			border-color: rgba(74, 222, 128, 0.35);
			background: rgba(74, 222, 128, 0.1);
		}

		/* Botón Principal CTA (Lime / Accent de la referencia) */
		.arc-cta-container {
			margin-top: 10px;
			display: flex;
			flex-direction: column;
			gap: 12px;
			align-items: center;
		}

		.arc-btn-primary {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
			width: 100%;
			min-height: 52px;
			border-radius: var(--radius-pill);
			background: var(--arc-accent);
			color: var(--arc-accent-foreground);
			border: none;
			font-family: inherit;
			font-size: 1.05rem;
			font-weight: 700;
			letter-spacing: -0.01em;
			cursor: pointer;
			text-decoration: none;
			box-shadow: 0 8px 24px rgba(190, 242, 100, 0.25);
			transition: var(--transition);
		}

		.arc-btn-primary:hover {
			filter: brightness(1.08);
			transform: translateY(-1px);
			box-shadow: 0 12px 28px rgba(190, 242, 100, 0.35);
		}

		.arc-btn-primary:active {
			transform: translateY(0);
		}

		.secure-footer-badge {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			font-size: 0.8rem;
			color: var(--arc-text-muted);
		}

		.secure-footer-badge svg {
			color: var(--arc-text-secondary);
		}

		/* ========================================================
		   COLUMNA DERECHA: RESUMEN DEL CURSO (Sales Page Style)
		   ======================================================== */
		.summary-column {
			background: var(--arc-surface);
			border: 1px solid var(--arc-border);
			border-radius: 24px;
			padding: 24px;
			display: flex;
			flex-direction: column;
			gap: 20px;
			position: sticky;
			top: 24px;
		}

		.summary-header-row {
			display: flex;
			align-items: center;
			justify-content: space-between;
		}

		.summary-title {
			font-size: 1.15rem;
			font-weight: 700;
			letter-spacing: -0.02em;
		}

		.summary-pill {
			font-size: 0.72rem;
			font-weight: 600;
			padding: 4px 10px;
			border-radius: var(--radius-pill);
			background: rgba(255, 255, 255, 0.06);
			color: var(--arc-text-secondary);
		}

		/* Tarjeta Hero / Portada del Curso */
		.sales-product-card {
			display: flex;
			flex-direction: column;
			gap: 16px;
		}

		.course-hero-cover {
			position: relative;
			width: 100%;
			height: 200px;
			border-radius: 14px;
			overflow: hidden;
			border: 1px solid var(--arc-border);
			background: linear-gradient(135deg, #18181c 0%, #27272a 100%);
		}

		.course-cover-img {
			width: 100%;
			height: 100%;
			object-fit: cover;
			transition: transform 0.3s ease;
		}

		.course-cover-wrap:hover .course-cover-img {
			transform: scale(1.02);
		}

		.course-cover-placeholder {
			width: 100%;
			height: 100%;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			gap: 10px;
			color: var(--arc-text-muted);
			padding: 20px;
			text-align: center;
		}

		.course-cover-placeholder svg {
			color: var(--arc-accent);
		}

		.sales-cover-badge {
			position: absolute;
			top: 12px;
			left: 12px;
			background: rgba(9, 9, 11, 0.85);
			backdrop-filter: blur(8px);
			border: 1px solid rgba(255, 255, 255, 0.12);
			color: #fff;
			padding: 4px 10px;
			border-radius: 6px;
			font-size: 0.72rem;
			font-weight: 700;
			letter-spacing: 0.03em;
			text-transform: uppercase;
		}

		.sales-product-info {
			display: flex;
			flex-direction: column;
			gap: 6px;
		}

		.sales-category-tag {
			font-size: 0.75rem;
			font-weight: 600;
			color: var(--arc-accent);
			text-transform: uppercase;
			letter-spacing: 0.05em;
		}

		.sales-course-title {
			font-size: 1.15rem;
			font-weight: 800;
			color: #fff;
			line-height: 1.35;
			letter-spacing: -0.02em;
		}

		.sales-plan-name {
			font-size: 0.88rem;
			color: var(--arc-text-secondary);
			display: flex;
			align-items: center;
			gap: 8px;
			flex-wrap: wrap;
		}

		.highlight-badge {
			background: var(--arc-surface-raised);
			border: 1px solid var(--arc-border);
			padding: 2px 8px;
			border-radius: 6px;
			font-size: 0.75rem;
			color: #fff;
			font-weight: 600;
		}

		.sales-course-desc {
			font-size: 0.84rem;
			color: var(--arc-text-muted);
			line-height: 1.5;
			margin-top: 4px;
		}

		/* Value Stack / Beneficios */
		.sales-features-list {
			display: flex;
			flex-direction: column;
			gap: 10px;
			background: var(--arc-surface-subtle);
			padding: 14px;
			border-radius: 12px;
			border: 1px solid var(--arc-border);
		}

		.sales-feature-item {
			display: flex;
			align-items: center;
			gap: 10px;
			font-size: 0.82rem;
			color: var(--arc-text-secondary);
		}

		.sales-feature-item svg {
			color: var(--arc-accent);
			flex-shrink: 0;
		}

		.sales-divider {
			height: 1px;
			background: var(--arc-border);
		}

		/* Tabla de Desglose */
		.breakdown-table {
			display: flex;
			flex-direction: column;
			gap: 8px;
		}

		.breakdown-row {
			display: flex;
			justify-content: space-between;
			align-items: center;
			font-size: 0.85rem;
		}

		.breakdown-label {
			color: var(--arc-text-secondary);
		}

		.breakdown-value {
			color: var(--arc-text-primary);
			font-weight: 500;
			font-variant-numeric: tabular-nums;
		}

		.total-row {
			margin-top: 4px;
			padding-top: 12px;
			border-top: 1px solid var(--arc-border);
		}

		.total-label {
			font-size: 0.95rem;
			font-weight: 700;
			color: #fff;
		}

		.total-amount {
			font-size: 1.5rem;
			font-weight: 800;
			letter-spacing: -0.03em;
			color: #fff;
			font-variant-numeric: tabular-nums;
		}

		/* Instrucciones */
		.instructions-card {
			background: var(--arc-surface-subtle);
			border: 1px solid var(--arc-border);
			border-radius: 12px;
			padding: 12px 14px;
			font-size: 0.8rem;
			color: var(--arc-text-secondary);
			line-height: 1.45;
		}

		.instructions-title {
			display: flex;
			align-items: center;
			gap: 6px;
			font-weight: 600;
			color: var(--arc-text-primary);
			margin-bottom: 4px;
		}

		/* Footer */
		.checkout-page-footer {
			margin-top: 60px;
			text-align: center;
			font-size: 0.8rem;
			color: var(--arc-text-muted);
			display: flex;
			flex-direction: column;
			gap: 6px;
			align-items: center;
		}
	</style>
</head>
<body>

	<!-- ========================================================
	     CABECERA OFICIAL DE LA ACADEMIA (Landing Checkout)
	     ======================================================== -->
	<header class="checkout-brand-header">
		<div class="brand-identity">
			<?php if ( ! empty( $academy_logo ) ) : ?>
				<img src="<?php echo esc_url( $academy_logo ); ?>" alt="<?php echo esc_attr( $academy_name ); ?>" class="academy-logo-img">
			<?php else : ?>
				<span class="academy-name-text"><?php echo esc_html( $academy_name ); ?></span>
			<?php endif; ?>
		</div>

		<div class="secure-badge-pill">
			<!-- Lucide ShieldCheck -->
			<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
			<span>Checkout Seguro · SSL 256-bit</span>
		</div>
	</header>

	<div class="checkout-layout">
		
		<div class="checkout-grid">

			<!-- ========================================================
			     COLUMNA IZQUIERDA: FORMULARIO Y MÉTODOS DE PAGO
			     ======================================================== -->
			<div class="payment-column">
				
				<div class="section-header">
					<h1 class="section-title">Completar Inscripción</h1>
					<p class="section-subtitle">Selecciona tu método de pago preferido para formalizar tu acceso.</p>
				</div>

				<!-- Lista de Radio Cards de Pago -->
				<?php if ( ! empty( $methods ) ) : ?>
					<div class="methods-list" role="radiogroup">
						
						<?php foreach ( $methods as $key => $m ) : 
							$is_active = ( $key === $active_method_id );
						?>
							<div 
								class="method-card <?php echo $is_active ? 'is-active' : ''; ?>" 
								id="card-<?php echo esc_attr( $key ); ?>"
								onclick="selectMethod('<?php echo esc_attr( $key ); ?>')"
							>
								<!-- Fila Principal Radio + Título + Badges -->
								<div class="card-header-row">
									<div class="radio-label-group">
										<div class="arc-radio">
											<div class="arc-radio-dot"></div>
										</div>
										<div class="method-title-wrap">
											<span class="method-main-title"><?php echo esc_html( $m['title'] ); ?></span>
											<span class="method-sub-title"><?php echo esc_html( $m['subtitle'] ); ?></span>
										</div>
									</div>

									<div class="brand-badges">
										<?php if ( $m['badge_type'] === 'cards' ) : ?>
											<span class="brand-pill amex">AMEX</span>
											<span class="brand-pill mc">MC</span>
											<span class="brand-pill visa">VISA</span>
										<?php elseif ( $m['badge_type'] === 'pm' ) : ?>
											<span class="brand-pill">BCV Bs</span>
										<?php elseif ( $m['badge_type'] === 'zelle' ) : ?>
											<span class="brand-pill" style="color: #7b2cbf;">Zelle</span>
										<?php elseif ( $m['badge_type'] === 'binance' ) : ?>
											<span class="brand-pill crypto">USDT</span>
										<?php elseif ( $m['badge_type'] === 'paypal' ) : ?>
											<span class="brand-pill pp">PayPal</span>
										<?php elseif ( $m['badge_type'] === 'bank' ) : ?>
											<span class="brand-pill">Banco</span>
										<?php endif; ?>
									</div>
								</div>

								<!-- Contenido Desplegable al Seleccionar -->
								<div class="card-expanded-body">
									
									<?php if ( ! empty( $m['notice'] ) ) : ?>
										<div class="arc-notice-box">
											<!-- Lucide Info -->
											<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
											<span><?php echo esc_html( $m['notice'] ); ?></span>
										</div>
									<?php endif; ?>

									<?php if ( ! empty( $m['description'] ) ) : ?>
										<p style="font-size: 0.85rem; color: var(--arc-text-secondary); margin-bottom: 14px; line-height: 1.5;">
											<?php echo esc_html( $m['description'] ); ?>
										</p>
									<?php endif; ?>

									<?php if ( ! empty( $m['fields'] ) ) : ?>
										<div class="arc-data-grid">
											<?php foreach ( $m['fields'] as $label => $val ) : ?>
												<div class="arc-data-item">
													<span class="arc-data-key"><?php echo esc_html( $label ); ?>:</span>
													<span class="arc-data-val"><?php echo esc_html( $val ); ?></span>
												</div>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>

									<?php if ( ! empty( $m['copy_data'] ) ) : ?>
										<button 
											type="button" 
											class="arc-btn-secondary"
											data-copy="<?php echo esc_attr( $m['copy_data'] ); ?>"
											onclick="event.stopPropagation(); copyArcData(this)"
										>
											<!-- Lucide Copy -->
											<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
											<span><?php echo esc_html( $m['copy_label'] ?? 'Copiar Datos de Pago' ); ?></span>
										</button>
									<?php endif; ?>

								</div>
							</div>
						<?php endforeach; ?>

					</div>

					<!-- Botón de Acción Principal Dinámico (Lime Button) -->
					<div class="arc-cta-container">
						<a 
							href="<?php echo esc_url( $methods[$active_method_id]['action_url'] ?? '#' ); ?>" 
							id="arc-main-cta" 
							class="arc-btn-primary"
							<?php echo ( ! empty( $methods[$active_method_id]['is_manual'] ) ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
						>
							<span id="arc-cta-text"><?php echo esc_html( $methods[$active_method_id]['cta_text'] ?? 'Completar Pago' ); ?></span>
						</a>

						<div class="secure-footer-badge">
							<!-- Lucide Lock -->
							<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
							<span>Transacción Segura y Privada · Activación Inmediata</span>
						</div>
					</div>

				<?php else : ?>
					<div class="sales-product-card" style="text-align: center; padding: 32px 20px;">
						<p style="color: var(--arc-text-secondary); margin-bottom: 12px;">Actualmente no hay pasarelas de pago configuradas.</p>
						<a href="<?php echo esc_url( $whatsapp_url ); ?>" class="arc-btn-primary" target="_blank" rel="noopener noreferrer">
							<span>Contactar vía WhatsApp</span>
						</a>
					</div>
				<?php endif; ?>

			</div>

			<!-- ========================================================
			     COLUMNA DERECHA: FICHA DEL CURSO (Sales Page Style)
			     ======================================================== -->
			<aside class="summary-column">
				
				<div class="summary-header-row">
					<h2 class="summary-title">Resumen de compra</h2>
					<span class="summary-pill">Acceso Inmediato</span>
				</div>

				<div class="sales-product-card">
					<!-- Portada del Curso como Hero Mockup -->
					<div class="course-hero-cover">
						<?php if ( ! empty( $course_image ) ) : ?>
							<img src="<?php echo esc_url( $course_image ); ?>" alt="<?php echo esc_attr( $course_title ); ?>" class="course-cover-img">
						<?php else : ?>
							<div class="course-cover-placeholder">
								<!-- Lucide GraduationCap -->
								<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/></svg>
								<span style="font-weight: 600; font-size: 0.9rem; color: #fff;"><?php echo esc_html( $course_title ); ?></span>
							</div>
						<?php endif; ?>
						<span class="sales-cover-badge">Formación Certificada</span>
					</div>

					<!-- Datos del Producto -->
					<div class="sales-product-info">
						<span class="sales-category-tag">Programa Formativo Oficial</span>
						<h3 class="sales-course-title"><?php echo esc_html( $course_title ); ?></h3>
						<div class="sales-plan-name">
							<span><?php echo esc_html( $plan->name ); ?></span>
							<span class="highlight-badge"><?php echo esc_html( $plan_type_label ); ?></span>
						</div>
						
						<?php if ( ! empty( $course_desc ) ) : ?>
							<p class="sales-course-desc"><?php echo esc_html( $course_desc ); ?></p>
						<?php endif; ?>
					</div>

					<!-- Value Stack (Lo que incluye el acceso) con Iconos Lucide -->
					<div class="sales-features-list">
						<div class="sales-feature-item">
							<!-- Lucide GraduationCap -->
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/></svg>
							<span>Acceso inmediato a la plataforma educativa</span>
						</div>

						<div class="sales-feature-item">
							<!-- Lucide Zap -->
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
							<span>Módulos y lecciones en video HD actualizadas</span>
						</div>

						<div class="sales-feature-item">
							<!-- Lucide MessageCircle -->
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
							<span>Canal de soporte y atención a consultas</span>
						</div>

						<div class="sales-feature-item">
							<!-- Lucide ShieldCheck -->
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
							<span>Garantía de activación y soporte 100% oficial</span>
						</div>
					</div>

					<div class="sales-divider"></div>

					<!-- Desglose de Precios -->
					<div class="breakdown-table">
						<div class="breakdown-row">
							<span class="breakdown-label">Plan seleccionado</span>
							<span class="breakdown-value"><?php echo esc_html( $plan->name ); ?></span>
						</div>

						<div class="breakdown-row">
							<span class="breakdown-label">Modalidad</span>
							<span class="breakdown-value"><?php echo esc_html( $billing_summary ); ?></span>
						</div>

						<div class="breakdown-row total-row">
							<span class="total-label">Total a pagar hoy</span>
							<span class="total-amount"><?php echo esc_html( $total_pay_today ); ?></span>
						</div>
					</div>

					<?php if ( ! empty( $instructions ) ) : ?>
						<div class="instructions-card">
							<div class="instructions-title">
								<!-- Lucide Info -->
								<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
								<span>Instrucciones de activación</span>
							</div>
							<p><?php echo nl2br( esc_html( $instructions ) ); ?></p>
						</div>
					<?php endif; ?>

				</div>

			</aside>

		</div>

		<!-- Footer sin fugas de enlace -->
		<footer class="checkout-page-footer">
			<span>&copy; <?php echo date( 'Y' ); ?> <?php echo esc_html( $academy_name ); ?>. Todos los derechos reservados.</span>
			<span style="opacity: 0.6;">Plataforma de pagos segura y encriptada para estudiantes.</span>
		</footer>

	</div>

	<!-- Script Interactivo de Selección y Copiado -->
	<script>
		var methodsData = <?php echo json_encode( $methods ); ?>;

		function selectMethod(methodKey) {
			document.querySelectorAll('.method-card').forEach(function(card) {
				card.classList.remove('is-active');
			});

			var activeCard = document.getElementById('card-' + methodKey);
			if (activeCard) {
				activeCard.classList.add('is-active');
			}

			var data = methodsData[methodKey];
			if (data) {
				var btn = document.getElementById('arc-main-cta');
				var text = document.getElementById('arc-cta-text');
				if (btn && text) {
					btn.href = data.action_url;
					text.textContent = data.cta_text;
					if (data.is_manual || data.id === 'paypal') {
						btn.setAttribute('target', '_blank');
						btn.setAttribute('rel', 'noopener noreferrer');
					} else {
						btn.removeAttribute('target');
						btn.removeAttribute('rel');
					}
				}
			}
		}

		function copyArcData(btn) {
			var text = btn.getAttribute('data-copy');
			if (!text) return;

			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text).then(function() {
					setCopySuccess(btn);
				}).catch(function() {
					fallbackArcCopy(text, btn);
				});
			} else {
				fallbackArcCopy(text, btn);
			}
		}

		function fallbackArcCopy(text, btn) {
			var ta = document.createElement('textarea');
			ta.value = text;
			ta.style.position = 'fixed';
			ta.style.opacity = '0';
			document.body.appendChild(ta);
			ta.select();
			try {
				document.execCommand('copy');
				setCopySuccess(btn);
			} catch (e) {
				console.error(e);
			}
			document.body.removeChild(ta);
		}

		function setCopySuccess(btn) {
			var originalHTML = btn.innerHTML;
			btn.classList.add('is-copied');
			btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>¡Copiado con Éxito!</span>';
			setTimeout(function() {
				btn.classList.remove('is-copied');
				btn.innerHTML = originalHTML;
			}, 2500);
		}
	</script>
</body>
</html>
