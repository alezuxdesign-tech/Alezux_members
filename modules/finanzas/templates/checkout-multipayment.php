<?php
/**
 * Plantilla de Checkout Multipasarela - Alezux Members (Estilo Arc UI)
 *
 * Basado en la interfaz moderna de pago con radio cards interactivas,
 * resumen lateral de compra, iconos oficiales Lucide y tokens Arc UI.
 *
 * @var object $plan
 * @var array  $settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$site_name = get_bloginfo( 'name' );
$site_logo = get_site_icon_url( 64 );

// 1. Información del curso
$course_id = (int) ( $plan->course_id ?? 0 );
$course_title = 'Membresía All-Access (Todos los Cursos)';
$course_image = '';
$course_desc  = 'Acceso completo a todos los cursos y lecciones formativas de la academia.';

if ( $course_id > 0 ) {
	$course_post = get_post( $course_id );
	if ( $course_post ) {
		$course_title = get_the_title( $course_id );
		$course_image = get_the_post_thumbnail_url( $course_id, 'medium_large' );
		$excerpt      = wp_strip_all_tags( get_the_excerpt( $course_id ) );
		if ( ! empty( $excerpt ) ) {
			$course_desc = $excerpt;
		}
	}
}

// 2. Datos y cálculos del plan
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
	$plan_type_label = 'Pago Único Completo';
	$billing_summary = 'Acceso de por vida · Pago único';
	$price_badge     = '$' . $amount_formatted . ' USD';
	$total_pay_today = '$' . $amount_formatted . ' USD';
} else {
	$total_plan_calc = number_format( $total_quotas * $quota_amount, 2, '.', ',' );
	$plan_type_label = 'Financiación en ' . $total_quotas . ' Cuotas';
	$billing_summary = 'Cuota 1 de ' . $total_quotas . ' (Total: $' . $total_plan_calc . ' USD)';
	$price_badge     = '$' . $amount_formatted . ' USD / cuota';
	$total_pay_today = '$' . $amount_formatted . ' USD';
}

// 3. WhatsApp para comprobantes
$whatsapp_raw   = ! empty( $plan->whatsapp_number ) ? $plan->whatsapp_number : ( $settings['whatsapp_number'] ?? '' );
$whatsapp_clean = preg_replace( '/[^0-9]/', '', $whatsapp_raw );
$whatsapp_msg   = '¡Hola! Acabo de realizar el pago de $' . $amount_formatted . ' USD para el plan "' . $plan->name . '" (' . $course_title . '). Adjunto mi comprobante para activar mi acceso a la academia. Mi correo es: ';
$whatsapp_url   = ! empty( $whatsapp_clean ) ? 'https://wa.me/' . $whatsapp_clean . '?text=' . rawurlencode( $whatsapp_msg ) : '#';

$instructions = ! empty( $settings['manual_payment_instructions'] )
	? $settings['manual_payment_instructions']
	: 'Transfiere el monto exacto y luego confirma tu comprobante por WhatsApp para activar tu acceso.';

// 4. Métodos de Pago Activos
$methods = [];

// Stripe (Tarjeta)
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
		'description' => 'Pago 100% cifrado y seguro a través de Stripe Checkout. Tu acceso a las lecciones y módulos se activará de forma instantánea al completar la transacción.',
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
		'notice'     => 'Realiza el pago móvil por el monto equivalente en bolívares a la tasa oficial del BCV.',
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
		'notice'     => 'En el concepto o nota de Zelle coloca únicamente tu nombre o tu correo electrónico.',
	];
}

// Transferencia Bancaria
if ( ! empty( $settings['bank_transfer_enabled'] ) ) {
	$bt_bank   = $settings['bank_name'] ?? '';
	$bt_acc    = $settings['bank_account_number'] ?? '';
	$bt_type   = $settings['bank_account_type'] ?? 'Corriente';
	$bt_holder = $settings['bank_holder_name'] ?? '';
	$bt_id     = $settings['bank_holder_id'] ?? '';

	$methods['bank_transfer'] = [
		'id'         => 'bank_transfer',
		'title'      => 'Transferencia Bancaria',
		'subtitle'   => $bt_bank ? $bt_bank : 'Cuenta Bancaria Local',
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

// Método seleccionado por defecto (el primero de la lista)
$active_method_id = ! empty( $methods ) ? array_key_first( $methods ) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Checkout — <?php echo esc_html( $plan->name ); ?> | <?php echo esc_html( $site_name ); ?></title>
	<?php if ( $site_logo ) : ?>
		<link rel="icon" href="<?php echo esc_url( $site_logo ); ?>" type="image/x-icon">
	<?php endif; ?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	
	<style>
		/* ========================================================
		   TOKENS DE DISEÑO ARC UI (Dark Fintech Edition)
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
			
			/* Acento vibrante estilo fintech (verde lima/neón como en la referencia) */
			--arc-accent: #bef264;
			--arc-accent-dim: rgba(190, 242, 100, 0.12);
			--arc-accent-border: #a3e635;
			--arc-accent-foreground: #09090b;

			--arc-text-primary: #f4f4f5;
			--arc-text-secondary: #a1a1aa;
			--arc-text-muted: #71717a;
			
			--arc-whatsapp: #25D366;
			--arc-whatsapp-hover: #22c05b;

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

		.checkout-layout {
			max-width: 1080px;
			margin: 0 auto;
			padding: 40px 24px 80px;
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

		@media (max-width: 920px) {
			.checkout-grid {
				grid-template-columns: 1fr;
				gap: 36px;
			}
		}

		/* ========================================================
		   COLUMNA IZQUIERDA: MÉTODOS DE PAGO
		   ======================================================== */
		.payment-column {
			display: flex;
			flex-direction: column;
			gap: 24px;
		}

		.checkout-top-nav {
			display: flex;
			align-items: center;
			gap: 14px;
		}

		.arc-back-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 36px;
			height: 36px;
			border-radius: 10px;
			background: var(--arc-surface);
			border: 1px solid var(--arc-border);
			color: var(--arc-text-secondary);
			text-decoration: none;
			transition: var(--transition);
		}

		.arc-back-btn:hover {
			color: var(--arc-text-primary);
			border-color: var(--arc-border-strong);
			background: var(--arc-surface-subtle);
		}

		.checkout-heading {
			font-size: 1.75rem;
			font-weight: 700;
			letter-spacing: -0.03em;
		}

		.section-header {
			display: flex;
			flex-direction: column;
			gap: 4px;
		}

		.section-title {
			font-size: 1.125rem;
			font-weight: 600;
			letter-spacing: -0.01em;
		}

		.section-subtitle {
			font-size: 0.875rem;
			color: var(--arc-text-muted);
		}

		/* Radio Cards List */
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

		/* Estado Seleccionado: Borde de Acento Lime / Arc UI */
		.method-card.is-active {
			border-color: var(--arc-accent-border);
			background: var(--arc-surface);
			box-shadow: 0 0 0 1px var(--arc-accent-border), 0 8px 24px rgba(0, 0, 0, 0.35);
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

		/* Radio Custom Circle */
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
		.brand-pill.pp { background: #003087; color: #0079C1; font-weight: 900; background: #fff; border: none; }
		.brand-pill.crypto { background: #f59e0b; color: #000; border: none; }

		/* Contenido Desplegado del Card Seleccionado */
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
		   COLUMNA DERECHA: RESUMEN DE COMPRA (SUMMARY)
		   ======================================================== */
		.summary-column {
			background: var(--arc-surface);
			border: 1px solid var(--arc-border);
			border-radius: 24px;
			padding: 28px;
			display: flex;
			flex-direction: column;
			gap: 24px;
			position: sticky;
			top: 24px;
		}

		.summary-title {
			font-size: 1.25rem;
			font-weight: 700;
			letter-spacing: -0.02em;
		}

		/* Tarjeta interna del producto */
		.product-card {
			background: var(--arc-surface-subtle);
			border: 1px solid var(--arc-border);
			border-radius: 16px;
			padding: 14px;
			display: flex;
			flex-direction: column;
			gap: 14px;
		}

		.product-thumb {
			width: 100%;
			height: 180px;
			object-fit: cover;
			border-radius: 12px;
			border: 1px solid var(--arc-border);
		}

		.product-info-row {
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			gap: 12px;
		}

		.product-meta {
			display: flex;
			flex-direction: column;
			gap: 4px;
		}

		.product-title {
			font-size: 1rem;
			font-weight: 700;
			color: var(--arc-text-primary);
			line-height: 1.35;
		}

		.product-badge {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			font-size: 0.78rem;
			color: var(--arc-text-secondary);
		}

		.product-badge::before {
			content: "";
			width: 6px;
			height: 6px;
			border-radius: 50%;
			background: var(--arc-accent);
		}

		.product-price {
			font-size: 1.05rem;
			font-weight: 700;
			color: var(--arc-text-primary);
			white-space: nowrap;
		}

		/* Features List (Estilo Referencia) */
		.summary-features {
			display: flex;
			flex-direction: column;
			gap: 12px;
		}

		.feature-item {
			display: flex;
			align-items: center;
			gap: 10px;
			font-size: 0.85rem;
			color: var(--arc-text-secondary);
		}

		.feature-item svg {
			color: var(--arc-accent);
			flex-shrink: 0;
		}

		.summary-divider {
			height: 1px;
			background: var(--arc-border);
		}

		/* Desglose de Precios */
		.breakdown-table {
			display: flex;
			flex-direction: column;
			gap: 10px;
		}

		.breakdown-row {
			display: flex;
			justify-content: space-between;
			align-items: center;
			font-size: 0.875rem;
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
			margin-top: 6px;
			padding-top: 14px;
			border-top: 1px solid var(--arc-border);
		}

		.total-label {
			font-size: 1rem;
			font-weight: 600;
			color: var(--arc-text-primary);
		}

		.total-amount {
			font-size: 1.6rem;
			font-weight: 800;
			letter-spacing: -0.03em;
			color: #fff;
			font-variant-numeric: tabular-nums;
		}

		/* Instrucciones Generales */
		.instructions-card {
			background: var(--arc-surface-subtle);
			border: 1px solid var(--arc-border);
			border-radius: 12px;
			padding: 12px 14px;
			font-size: 0.82rem;
			color: var(--arc-text-secondary);
			line-height: 1.45;
		}

		/* Footer */
		.checkout-page-footer {
			margin-top: 60px;
			text-align: center;
			font-size: 0.82rem;
			color: var(--arc-text-muted);
			display: flex;
			flex-direction: column;
			gap: 8px;
			align-items: center;
		}

		.footer-back-link {
			color: var(--arc-text-secondary);
			text-decoration: none;
			transition: var(--transition);
		}

		.footer-back-link:hover {
			color: var(--arc-text-primary);
		}
	</style>
</head>
<body>

	<div class="checkout-layout">
		
		<div class="checkout-grid">

			<!-- ========================================================
			     COLUMNA IZQUIERDA: SELECCIÓN DE MÉTODO DE PAGO
			     ======================================================== -->
			<div class="payment-column">
				
				<!-- Navegación y Título de Cabecera -->
				<div class="checkout-top-nav">
					<a href="<?php echo esc_url( home_url() ); ?>" class="arc-back-btn" title="Volver a la academia">
						<!-- Lucide ArrowLeft -->
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
					</a>
					<h1 class="checkout-heading">Checkout</h1>
				</div>

				<div class="section-header">
					<h2 class="section-title">Método de pago</h2>
					<p class="section-subtitle">Elige tu forma de pago preferida para formalizar tu inscripción.</p>
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

					<!-- Botón de Acción Principal Dinámico (Lime / Accent Button) -->
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
							<span>Pago Seguro · Encriptado SSL · Verificado</span>
						</div>
					</div>

				<?php else : ?>
					<div class="product-card" style="text-align: center; padding: 32px 20px;">
						<p style="color: var(--arc-text-secondary); margin-bottom: 12px;">Actualmente no hay pasarelas de pago configuradas.</p>
						<a href="<?php echo esc_url( $whatsapp_url ); ?>" class="arc-btn-primary" target="_blank" rel="noopener noreferrer">
							<span>Contactar vía WhatsApp</span>
						</a>
					</div>
				<?php endif; ?>

			</div>

			<!-- ========================================================
			     COLUMNA DERECHA: RESUMEN DE COMPRA (SUMMARY)
			     ======================================================== -->
			<aside class="summary-column">
				
				<h2 class="summary-title">Resumen de compra</h2>

				<!-- Tarjeta interna del curso / producto -->
				<div class="product-card">
					<?php if ( $course_image ) : ?>
						<img src="<?php echo esc_url( $course_image ); ?>" alt="<?php echo esc_attr( $course_title ); ?>" class="product-thumb">
					<?php endif; ?>
					
					<div class="product-info-row">
						<div class="product-meta">
							<span class="product-title"><?php echo esc_html( $course_title ); ?></span>
							<span class="product-badge"><?php echo esc_html( $plan_type_label ); ?></span>
						</div>
						<div class="product-price">
							<?php echo $price_badge; ?>
						</div>
					</div>
				</div>

				<!-- Beneficios incluidos con Iconos Lucide -->
				<div class="summary-features">
					<div class="feature-item">
						<!-- Lucide GraduationCap -->
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/></svg>
						<span>Acceso inmediato al aula virtual</span>
					</div>

					<div class="feature-item">
						<!-- Lucide Zap -->
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
						<span>Módulos y lecciones en video HD</span>
					</div>

					<div class="feature-item">
						<!-- Lucide MessageCircle -->
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
						<span>Soporte directo por WhatsApp</span>
					</div>

					<div class="feature-item">
						<!-- Lucide ShieldCheck -->
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
						<span>Garantía de activación inmediata</span>
					</div>
				</div>

				<div class="summary-divider"></div>

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

				<!-- Instrucciones de Confirmación -->
				<div class="instructions-card">
					<p><?php echo nl2br( esc_html( $instructions ) ); ?></p>
				</div>

			</aside>

		</div>

		<!-- Footer -->
		<footer class="checkout-page-footer">
			<a href="<?php echo esc_url( home_url() ); ?>" class="footer-back-link">← Volver a <?php echo esc_html( $site_name ); ?></a>
			<span>&copy; <?php echo date( 'Y' ); ?> <?php echo esc_html( $site_name ); ?>. Todos los derechos reservados.</span>
		</footer>

	</div>

	<!-- ========================================================
	     SCRIPT INTERACTIVO CON RADIO ACCORDION & COPIADO
	     ======================================================== -->
	<script>
		// Datos dinámicos de los métodos para actualizar el botón principal
		var methodsData = <?php echo json_encode( $methods ); ?>;

		function selectMethod(methodKey) {
			// Remover clase activa de todos los cards
			document.querySelectorAll('.method-card').forEach(function(card) {
				card.classList.remove('is-active');
			});

			// Activar card seleccionado
			var activeCard = document.getElementById('card-' + methodKey);
			if (activeCard) {
				activeCard.classList.add('is-active');
			}

			// Actualizar botón principal CTA
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
