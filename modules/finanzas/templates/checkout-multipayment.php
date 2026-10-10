<?php
/**
 * Plantilla de Checkout Multipasarela de Alezux Members
 *
 * Variables disponibles:
 * @var object $plan
 * @var array  $settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$site_name = get_bloginfo( 'name' );
$site_logo = get_site_icon_url( 64 );

// Información del curso
$course_id = (int) ( $plan->course_id ?? 0 );
$course_title = 'Membresía All-Access (Todos los Cursos)';
$course_image = '';
$course_desc  = 'Acceso ilimitado a todos los cursos y contenidos formativos de la academia.';

if ( $course_id > 0 ) {
	$course_post = get_post( $course_id );
	if ( $course_post ) {
		$course_title = get_the_title( $course_id );
		$course_image = get_the_post_thumbnail_url( $course_id, 'medium_large' );
		$excerpt      = wp_strip_all_tags( get_the_excerpt( $course_id ) );
		if ( ! empty( $excerpt ) ) {
			$course_desc = $excerpt;
		} else {
			$course_desc = 'Accede de forma inmediata al curso y a todas sus lecciones interactivas.';
		}
	}
}

// Datos del plan
$plan_name        = esc_html( $plan->name );
$quota_amount     = (float) $plan->quota_amount;
$amount_formatted = number_format( $quota_amount, 2, '.', ',' );
$total_quotas     = (int) $plan->total_quotas;

if ( $total_quotas === 0 ) {
	$plan_badge     = 'Membresía Recurrente';
	$frequency_text = ( isset( $plan->frequency ) && $plan->frequency === 'year' ) ? 'Facturado anualmente' : 'Facturado cada mes';
	$price_headline = '$' . $amount_formatted . ' <span class="currency">USD / mes</span>';
} elseif ( $total_quotas === 1 ) {
	$plan_badge     = 'Pago Único';
	$frequency_text = 'Acceso vitalicio · Sin pagos adicionales';
	$price_headline = '$' . $amount_formatted . ' <span class="currency">USD</span>';
} else {
	$total_plan_amount = number_format( $total_quotas * $quota_amount, 2, '.', ',' );
	$plan_badge        = $total_quotas . ' Cuotas Flexibles';
	$frequency_text    = 'Cuota 1 de ' . $total_quotas . ' · Total del plan: $' . $total_plan_amount . ' USD';
	$price_headline    = '$' . $amount_formatted . ' <span class="currency">USD / cuota</span>';
}

// WhatsApp para comprobantes
$whatsapp_raw   = ! empty( $plan->whatsapp_number ) ? $plan->whatsapp_number : ( $settings['whatsapp_number'] ?? '' );
$whatsapp_clean = preg_replace( '/[^0-9]/', '', $whatsapp_raw );
$whatsapp_msg   = '¡Hola! Acabo de realizar el pago de $' . $amount_formatted . ' USD para el plan "' . $plan->name . '" (' . $course_title . '). Adjunto mi comprobante para que activen mi acceso a la academia. Mi correo es: ';
$whatsapp_link  = ! empty( $whatsapp_clean ) ? 'https://wa.me/' . $whatsapp_clean . '?text=' . rawurlencode( $whatsapp_msg ) : '';

$instructions = ! empty( $settings['manual_payment_instructions'] )
	? $settings['manual_payment_instructions']
	: 'Realiza tu transferencia o pago por el monto indicado. Al finalizar, haz clic en el botón de abajo para enviar tu comprobante por WhatsApp y activar tu acceso de inmediato.';

// Configuración de Pasarelas y Métodos Activos
$methods = [];

// 1. Pago Móvil (Venezuela)
if ( ! empty( $settings['pagomovil_enabled'] ) ) {
	$pm_bank   = $settings['pagomovil_bank'] ?? '';
	$pm_phone  = $settings['pagomovil_phone'] ?? '';
	$pm_id     = $settings['pagomovil_id'] ?? '';
	$pm_holder = $settings['pagomovil_holder'] ?? '';

	$pm_copy = "Pago Móvil Venezuela:\nBanco: {$pm_bank}\nTeléfono: {$pm_phone}\nC.I / RIF: {$pm_id}\nTitular: {$pm_holder}\nMonto: \${$amount_formatted} USD (a tasa oficial del día)";

	$methods['pagomovil'] = [
		'id'        => 'pagomovil',
		'label'     => 'Pago Móvil',
		'badge'     => 'Venezuela (Bs)',
		'icon'      => '🏦',
		'copy_text' => $pm_copy,
		'fields'    => [
			'Banco'        => $pm_bank,
			'Teléfono'     => $pm_phone,
			'Cédula / RIF' => $pm_id,
			'Titular'      => $pm_holder,
			'Monto'        => '$' . $amount_formatted . ' USD (a tasa oficial BCV)',
		],
		'notice'    => 'Realiza la conversión a bolívares a la tasa oficial del BCV del día de hoy.',
	];
}

// 2. Zelle
if ( ! empty( $settings['zelle_enabled'] ) ) {
	$zelle_email  = $settings['zelle_email'] ?? '';
	$zelle_holder = $settings['zelle_holder'] ?? '';

	$methods['zelle'] = [
		'id'         => 'zelle',
		'label'      => 'Zelle',
		'badge'      => 'USD Directo',
		'icon'       => '💵',
		'copy_text'  => $zelle_email,
		'copy_label' => 'Copiar Correo Zelle',
		'fields'     => [
			'Correo Zelle' => $zelle_email,
			'Titular'      => $zelle_holder,
			'Monto Exacto' => '$' . $amount_formatted . ' USD',
		],
		'notice'     => 'En el concepto o nota de Zelle, coloca únicamente tu nombre o correo electrónico (no coloques la palabra curso o academia).',
	];
}

// 3. Transferencia Bancaria
if ( ! empty( $settings['bank_transfer_enabled'] ) ) {
	$bt_bank   = $settings['bank_name'] ?? '';
	$bt_acc    = $settings['bank_account_number'] ?? '';
	$bt_type   = $settings['bank_account_type'] ?? 'Corriente';
	$bt_holder = $settings['bank_holder_name'] ?? '';
	$bt_id     = $settings['bank_holder_id'] ?? '';

	$bt_copy = "Transferencia Bancaria:\nBanco: {$bt_bank}\nCuenta: {$bt_acc}\nTipo: {$bt_type}\nTitular: {$bt_holder}\nID/RIF: {$bt_id}";

	$methods['bank_transfer'] = [
		'id'         => 'bank_transfer',
		'label'      => 'Transferencia',
		'badge'      => 'Cuenta Bancaria',
		'icon'       => '🏛️',
		'copy_text'  => $bt_acc,
		'copy_label' => 'Copiar Número de Cuenta',
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

// 4. Binance Pay / USDT
if ( ! empty( $settings['binance_enabled'] ) ) {
	$bin_id     = $settings['binance_pay_id'] ?? '';
	$bin_wallet = $settings['binance_usdt_wallet'] ?? '';
	$bin_net    = $settings['binance_network'] ?? 'TRC20';

	$bin_copy = ! empty( $bin_wallet ) ? $bin_wallet : $bin_id;

	$methods['binance'] = [
		'id'         => 'binance',
		'label'      => 'Binance Pay / USDT',
		'badge'      => 'Cripto',
		'icon'       => '🟡',
		'copy_text'  => $bin_copy,
		'copy_label' => ! empty( $bin_wallet ) ? 'Copiar Dirección USDT' : 'Copiar Binance Pay ID',
		'fields'     => array_filter( [
			'Binance Pay ID' => $bin_id,
			'Red de Envío'   => $bin_net,
			'Billetera USDT' => $bin_wallet,
			'Monto a Pagar'  => $amount_formatted . ' USDT',
		] ),
		'notice'     => 'Asegúrate de enviar únicamente por la red seleccionada (' . esc_html( $bin_net ) . ') para evitar pérdidas de fondos.',
	];
}

// 5. PayPal
if ( ! empty( $settings['paypal_enabled'] ) ) {
	$paypal_raw = $settings['paypal_email'] ?? '';
	$is_pp_link = ( strpos( $paypal_raw, 'http' ) === 0 || strpos( $paypal_raw, 'paypal.me' ) !== false );
	$pp_url     = $is_pp_link ? ( strpos( $paypal_raw, 'http' ) === 0 ? $paypal_raw : 'https://' . $paypal_raw ) : '';

	$methods['paypal'] = [
		'id'         => 'paypal',
		'label'      => 'PayPal',
		'badge'      => 'Saldo / Tarjeta',
		'icon'       => '🅿️',
		'copy_text'  => $paypal_raw,
		'copy_label' => 'Copiar Cuenta PayPal',
		'pay_url'    => $pp_url,
		'fields'     => [
			'Cuenta PayPal' => $paypal_raw,
			'Monto a Pagar' => '$' . $amount_formatted . ' USD',
		],
		'notice'     => 'Envía el pago indicando tu correo para que podamos verificarlo.',
	];
}

// 6. Stripe (Tarjeta de Crédito / Débito)
$stripe_sec = $settings['stripe_secret_key'] ?? get_option( 'alezux_stripe_secret_key', '' );
if ( ! empty( $settings['stripe_enabled'] ) && ! empty( $stripe_sec ) && ! empty( $plan->stripe_price_id ) ) {
	$stripe_url = add_query_arg( [ 'gateway' => 'stripe' ] );
	$methods['stripe'] = [
		'id'        => 'stripe',
		'label'     => 'Tarjeta Débito / Crédito',
		'badge'     => 'Inmediato',
		'icon'      => '💳',
		'is_stripe' => true,
		'pay_url'   => $stripe_url,
		'notice'    => 'Paga de forma 100% segura con tu tarjeta Visa, Mastercard o American Express. Tu acceso se activará automáticamente.',
	];
}

$first_tab = ! empty( $methods ) ? array_key_first( $methods ) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html( $plan->name ); ?> — Checkout Seguro | <?php echo esc_html( $site_name ); ?></title>
	<?php if ( $site_logo ) : ?>
		<link rel="icon" href="<?php echo esc_url( $site_logo ); ?>" type="image/x-icon">
	<?php endif; ?>
	<style>
		:root {
			--bg-main: #0a0e17;
			--bg-card: #111827;
			--bg-card-subtle: #1a2234;
			--border-subtle: rgba(255, 255, 255, 0.08);
			--border-focus: #3b82f6;
			--text-primary: #f8fafc;
			--text-secondary: #94a3b8;
			--text-muted: #64748b;
			--accent: #3b82f6;
			--accent-hover: #2563eb;
			--accent-glow: rgba(59, 130, 246, 0.15);
			--whatsapp: #25D366;
			--whatsapp-hover: #20ba59;
			--font: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
		}

		* {
			box-sizing: border-box;
			margin: 0;
			padding: 0;
		}

		body {
			background-color: var(--bg-main);
			color: var(--text-primary);
			font-family: var(--font);
			line-height: 1.5;
			min-height: 100vh;
			display: flex;
			flex-direction: column;
			-webkit-font-smoothing: antialiased;
		}

		.checkout-wrapper {
			max-width: 1060px;
			margin: 0 auto;
			padding: 32px 20px 60px;
			width: 100%;
			flex: 1;
		}

		/* Header */
		.checkout-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding-bottom: 24px;
			border-bottom: 1px solid var(--border-subtle);
			margin-bottom: 32px;
		}

		.brand-wrap {
			display: flex;
			align-items: center;
			gap: 12px;
			text-decoration: none;
			color: var(--text-primary);
		}

		.brand-logo {
			width: 36px;
			height: 36px;
			border-radius: 8px;
			object-fit: cover;
		}

		.brand-name {
			font-size: 1.15rem;
			font-weight: 700;
			letter-spacing: -0.02em;
		}

		.security-badge {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 6px 12px;
			border-radius: 20px;
			background: rgba(16, 185, 129, 0.1);
			color: #34d399;
			font-size: 0.8rem;
			font-weight: 600;
			border: 1px solid rgba(16, 185, 129, 0.2);
		}

		/* Grid Principal */
		.checkout-grid {
			display: grid;
			grid-template-columns: 1fr 1.35fr;
			gap: 32px;
			align-items: start;
		}

		@media (max-width: 860px) {
			.checkout-grid {
				grid-template-columns: 1fr;
			}
		}

		/* Tarjeta Columna Izquierda (Resumen) */
		.summary-card {
			background: var(--bg-card);
			border: 1px solid var(--border-subtle);
			border-radius: 16px;
			padding: 28px;
			position: sticky;
			top: 24px;
		}

		.course-preview {
			margin-bottom: 20px;
		}

		.course-thumb {
			width: 100%;
			height: 160px;
			border-radius: 10px;
			object-fit: cover;
			margin-bottom: 16px;
			border: 1px solid var(--border-subtle);
		}

		.plan-badge {
			display: inline-block;
			background: var(--accent-glow);
			color: #60a5fa;
			border: 1px solid rgba(96, 165, 250, 0.3);
			padding: 4px 10px;
			border-radius: 6px;
			font-size: 0.75rem;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.05em;
			margin-bottom: 10px;
		}

		.course-title {
			font-size: 1.3rem;
			font-weight: 700;
			line-height: 1.3;
			margin-bottom: 6px;
		}

		.plan-title {
			font-size: 1rem;
			color: var(--text-secondary);
			margin-bottom: 16px;
		}

		.divider {
			height: 1px;
			background: var(--border-subtle);
			margin: 20px 0;
		}

		.price-block {
			display: flex;
			flex-direction: column;
			gap: 4px;
			margin-bottom: 20px;
		}

		.price-label {
			font-size: 0.85rem;
			color: var(--text-muted);
			text-transform: uppercase;
			letter-spacing: 0.04em;
			font-weight: 600;
		}

		.price-amount {
			font-size: 2.2rem;
			font-weight: 800;
			letter-spacing: -0.03em;
			color: #fff;
		}

		.price-amount .currency {
			font-size: 1rem;
			font-weight: 500;
			color: var(--text-secondary);
		}

		.price-subtext {
			font-size: 0.85rem;
			color: #34d399;
			font-weight: 500;
		}

		.benefits-list {
			list-style: none;
			display: flex;
			flex-direction: column;
			gap: 10px;
			font-size: 0.9rem;
			color: var(--text-secondary);
		}

		.benefits-list li {
			display: flex;
			align-items: center;
			gap: 8px;
		}

		.benefits-list li::before {
			content: "✓";
			color: #10b981;
			font-weight: 800;
		}

		/* Tarjeta Columna Derecha (Métodos de Pago) */
		.payment-card {
			background: var(--bg-card);
			border: 1px solid var(--border-subtle);
			border-radius: 16px;
			padding: 28px;
		}

		.payment-title {
			font-size: 1.25rem;
			font-weight: 700;
			margin-bottom: 6px;
		}

		.payment-desc {
			font-size: 0.9rem;
			color: var(--text-secondary);
			margin-bottom: 24px;
		}

		/* Pestañas de Métodos */
		.tabs-container {
			display: flex;
			flex-wrap: wrap;
			gap: 8px;
			margin-bottom: 24px;
			background: var(--bg-card-subtle);
			padding: 6px;
			border-radius: 12px;
			border: 1px solid var(--border-subtle);
		}

		.tab-btn {
			flex: 1 1 auto;
			min-width: 120px;
			background: transparent;
			border: none;
			color: var(--text-secondary);
			padding: 10px 14px;
			border-radius: 8px;
			font-size: 0.85rem;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.15s ease;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
		}

		.tab-btn:hover {
			color: #fff;
			background: rgba(255, 255, 255, 0.05);
		}

		.tab-btn.active {
			background: var(--accent);
			color: #fff;
			box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
		}

		/* Contenido del Tab Activo */
		.tab-content {
			display: none;
			animation: fadeIn 0.2s ease forwards;
		}

		.tab-content.active {
			display: block;
		}

		@keyframes fadeIn {
			from { opacity: 0; transform: translateY(4px); }
			to { opacity: 1; transform: translateY(0); }
		}

		.data-box {
			background: var(--bg-card-subtle);
			border: 1px solid var(--border-subtle);
			border-radius: 12px;
			padding: 20px;
			margin-bottom: 20px;
		}

		.data-row {
			display: flex;
			justify-content: space-between;
			padding: 9px 0;
			border-bottom: 1px solid rgba(255, 255, 255, 0.04);
			font-size: 0.9rem;
		}

		.data-row:last-child {
			border-bottom: none;
			padding-bottom: 0;
		}

		.data-label {
			color: var(--text-secondary);
			font-weight: 500;
		}

		.data-value {
			color: #fff;
			font-weight: 600;
			text-align: right;
			user-select: all;
			word-break: break-all;
		}

		.method-notice {
			background: rgba(245, 158, 11, 0.1);
			border: 1px solid rgba(245, 158, 11, 0.25);
			color: #fbbf24;
			border-radius: 8px;
			padding: 10px 14px;
			font-size: 0.85rem;
			margin-bottom: 16px;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		/* Botones de Acción */
		.action-row {
			display: flex;
			gap: 12px;
			flex-wrap: wrap;
		}

		.btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			padding: 12px 20px;
			border-radius: 10px;
			font-size: 0.95rem;
			font-weight: 600;
			cursor: pointer;
			text-decoration: none;
			transition: all 0.15s ease;
			border: none;
			flex: 1 1 200px;
		}

		.btn-copy {
			background: rgba(255, 255, 255, 0.08);
			color: var(--text-primary);
			border: 1px solid var(--border-subtle);
		}

		.btn-copy:hover {
			background: rgba(255, 255, 255, 0.14);
		}

		.btn-copy.copied {
			background: rgba(16, 185, 129, 0.15);
			color: #34d399;
			border-color: rgba(16, 185, 129, 0.3);
		}

		.btn-stripe {
			background: #6366f1;
			color: #fff;
			box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
		}

		.btn-stripe:hover {
			background: #4f46e5;
		}

		.btn-paypal {
			background: #0070ba;
			color: #fff;
		}

		.btn-paypal:hover {
			background: #005ea6;
		}

		/* Sección de WhatsApp */
		.whatsapp-section {
			margin-top: 28px;
			padding-top: 24px;
			border-top: 1px solid var(--border-subtle);
		}

		.whatsapp-card {
			background: rgba(37, 211, 102, 0.07);
			border: 1px solid rgba(37, 211, 102, 0.2);
			border-radius: 12px;
			padding: 20px;
		}

		.whatsapp-header {
			display: flex;
			align-items: center;
			gap: 10px;
			margin-bottom: 8px;
		}

		.whatsapp-title {
			font-size: 1.05rem;
			font-weight: 700;
			color: #4ade80;
		}

		.whatsapp-instructions {
			font-size: 0.88rem;
			color: var(--text-secondary);
			line-height: 1.5;
			margin-bottom: 16px;
		}

		.btn-whatsapp {
			background: var(--whatsapp);
			color: #0b2612;
			font-weight: 700;
			box-shadow: 0 4px 16px rgba(37, 211, 102, 0.35);
			width: 100%;
		}

		.btn-whatsapp:hover {
			background: var(--whatsapp-hover);
			color: #051409;
		}

		/* Footer */
		.checkout-footer {
			margin-top: 40px;
			text-align: center;
			color: var(--text-muted);
			font-size: 0.85rem;
			display: flex;
			flex-direction: column;
			gap: 8px;
			align-items: center;
		}

		.back-link {
			color: var(--text-secondary);
			text-decoration: none;
			font-weight: 500;
			transition: color 0.15s;
		}

		.back-link:hover {
			color: #fff;
		}

		/* Empty State */
		.no-methods {
			text-align: center;
			padding: 32px 16px;
			color: var(--text-secondary);
		}
	</style>
</head>
<body>

	<div class="checkout-wrapper">
		<!-- Header -->
		<header class="checkout-header">
			<a href="<?php echo esc_url( home_url() ); ?>" class="brand-wrap">
				<?php if ( $site_logo ) : ?>
					<img src="<?php echo esc_url( $site_logo ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" class="brand-logo">
				<?php endif; ?>
				<span class="brand-name"><?php echo esc_html( $site_name ); ?></span>
			</a>
			<div class="security-badge">
				<span>🔒</span>
				<span>Pago 100% Seguro</span>
			</div>
		</header>

		<!-- Grid Principal -->
		<div class="checkout-grid">
			
			<!-- COLUMNA IZQUIERDA: RESUMEN DEL PEDIDO -->
			<div class="summary-card">
				<div class="course-preview">
					<?php if ( $course_image ) : ?>
						<img src="<?php echo esc_url( $course_image ); ?>" alt="<?php echo esc_attr( $course_title ); ?>" class="course-thumb">
					<?php endif; ?>
					<span class="plan-badge"><?php echo esc_html( $plan_badge ); ?></span>
					<h1 class="course-title"><?php echo esc_html( $course_title ); ?></h1>
					<p class="plan-title"><?php echo esc_html( $plan->name ); ?></p>
				</div>

				<div class="divider"></div>

				<div class="price-block">
					<span class="price-label">Total a Pagar Hoy</span>
					<div class="price-amount"><?php echo $price_headline; ?></div>
					<span class="price-subtext"><?php echo esc_html( $frequency_text ); ?></span>
				</div>

				<div class="divider"></div>

				<ul class="benefits-list">
					<li>Acceso garantizado a la plataforma</li>
					<li>Módulos y lecciones formativas</li>
					<li>Activación rápida tras confirmar tu pago</li>
					<li>Soporte y atención a dudas</li>
				</ul>
			</div>

			<!-- COLUMNA DERECHA: SELECCIÓN DE MÉTODO DE PAGO -->
			<div class="payment-card">
				<h2 class="payment-title">Elige cómo pagar</h2>
				<p class="payment-desc">Selecciona la pasarela o método local que prefieras para completar tu inscripción.</p>

				<?php if ( ! empty( $methods ) ) : ?>
					<!-- Pestañas de Métodos -->
					<div class="tabs-container" role="tablist">
						<?php foreach ( $methods as $key => $m ) : ?>
							<button 
								type="button" 
								class="tab-btn <?php echo ( $key === $first_tab ) ? 'active' : ''; ?>" 
								data-target="tab-<?php echo esc_attr( $key ); ?>"
								onclick="switchTab('<?php echo esc_attr( $key ); ?>')"
							>
								<span><?php echo $m['icon']; ?></span>
								<span><?php echo esc_html( $m['label'] ); ?></span>
							</button>
						<?php endforeach; ?>
					</div>

					<!-- Contenido de cada Método -->
					<?php foreach ( $methods as $key => $m ) : ?>
						<div id="tab-<?php echo esc_attr( $key ); ?>" class="tab-content <?php echo ( $key === $first_tab ) ? 'active' : ''; ?>">
							
							<?php if ( ! empty( $m['notice'] ) ) : ?>
								<div class="method-notice">
									<span>ℹ️</span>
									<span><?php echo esc_html( $m['notice'] ); ?></span>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $m['fields'] ) ) : ?>
								<div class="data-box">
									<?php foreach ( $m['fields'] as $label => $val ) : ?>
										<div class="data-row">
											<span class="data-label"><?php echo esc_html( $label ); ?>:</span>
											<span class="data-value"><?php echo esc_html( $val ); ?></span>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<div class="action-row">
								<?php if ( ! empty( $m['copy_text'] ) ) : ?>
									<button 
										type="button" 
										class="btn btn-copy" 
										data-copy="<?php echo esc_attr( $m['copy_text'] ); ?>"
										onclick="copyDetails(this)"
									>
										<span>📋</span>
										<span><?php echo esc_html( $m['copy_label'] ?? 'Copiar Datos' ); ?></span>
									</button>
								<?php endif; ?>

								<?php if ( ! empty( $m['is_stripe'] ) && ! empty( $m['pay_url'] ) ) : ?>
									<a href="<?php echo esc_url( $m['pay_url'] ); ?>" class="btn btn-stripe">
										<span>Pagar con Tarjeta (Stripe) 💳</span>
									</a>
								<?php endif; ?>

								<?php if ( ! empty( $m['pay_url'] ) && empty( $m['is_stripe'] ) ) : ?>
									<a href="<?php echo esc_url( $m['pay_url'] ); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-paypal">
										<span>Pagar por PayPal 🅿️</span>
									</a>
								<?php endif; ?>
							</div>

						</div>
					<?php endforeach; ?>

				<?php else : ?>
					<div class="no-methods">
						<p>No hay pasarelas automáticas configuradas actualmente.</p>
						<p style="margin-top: 6px;">Por favor, contacta directamente a la academia vía WhatsApp para coordinar tu forma de pago.</p>
					</div>
				<?php endif; ?>

				<!-- SECCIÓN DE RECEPCIÓN DE COMPROBANTE WHATSAPP -->
				<div class="whatsapp-section">
					<div class="whatsapp-card">
						<div class="whatsapp-header">
							<span style="font-size: 1.4rem;">📱</span>
							<h3 class="whatsapp-title">Confirmación y Envío de Comprobante</h3>
						</div>
						<p class="whatsapp-instructions">
							<?php echo nl2br( esc_html( $instructions ) ); ?>
						</p>

						<?php if ( ! empty( $whatsapp_link ) ) : ?>
							<a href="<?php echo esc_url( $whatsapp_link ); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-whatsapp">
								<span>Enviar Comprobante por WhatsApp 💬</span>
							</a>
						<?php else : ?>
							<p style="font-size: 0.85rem; color: var(--text-muted); font-style: italic;">
								Envía tu comprobante al administrador de la academia indicando tu correo electrónico para procesar tu alta.
							</p>
						<?php endif; ?>
					</div>
				</div>

			</div>
		</div>

		<!-- Footer -->
		<footer class="checkout-footer">
			<a href="<?php echo esc_url( home_url() ); ?>" class="back-link">← Volver al sitio principal</a>
			<span>&copy; <?php echo date( 'Y' ); ?> <?php echo esc_html( $site_name ); ?>. Todos los derechos reservados.</span>
		</footer>
	</div>

	<script>
		function switchTab(methodKey) {
			// Remover clase activa de todos los botones
			document.querySelectorAll('.tab-btn').forEach(function(btn) {
				btn.classList.remove('active');
			});
			// Remover clase activa de todos los contenidos
			document.querySelectorAll('.tab-content').forEach(function(content) {
				content.classList.remove('active');
			});

			// Activar botón seleccionado
			var activeBtn = document.querySelector('[data-target="tab-' + methodKey + '"]');
			if (activeBtn) {
				activeBtn.classList.add('active');
			}

			// Activar contenido seleccionado
			var activeContent = document.getElementById('tab-' + methodKey);
			if (activeContent) {
				activeContent.classList.add('active');
			}
		}

		function copyDetails(button) {
			var text = button.getAttribute('data-copy');
			if (!text) return;

			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text).then(function() {
					showCopied(button);
				}).catch(function() {
					fallbackCopy(text, button);
				});
			} else {
				fallbackCopy(text, button);
			}
		}

		function fallbackCopy(text, button) {
			var textarea = document.createElement('textarea');
			textarea.value = text;
			textarea.style.position = 'fixed';
			textarea.style.opacity = '0';
			document.body.appendChild(textarea);
			textarea.select();
			try {
				document.execCommand('copy');
				showCopied(button);
			} catch (err) {
				console.error('Error al copiar', err);
			}
			document.body.removeChild(textarea);
		}

		function showCopied(button) {
			var originalHTML = button.innerHTML;
			button.classList.add('copied');
			button.innerHTML = '<span>✓</span><span>¡Copiado con Éxito!</span>';
			setTimeout(function() {
				button.classList.remove('copied');
				button.innerHTML = originalHTML;
			}, 2500);
		}
	</script>
</body>
</html>
