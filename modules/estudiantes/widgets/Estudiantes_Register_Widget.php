<?php
namespace Alezux_Members\Modules\Estudiantes\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Estudiantes_Register_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux-estudiantes-register';
	}

	public function get_title() {
		return esc_html__( 'Registro Manual Estudiante', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-person';
	}

	public function get_categories() {
		return [ 'alezux-estudiantes' ];
	}

	public function get_script_depends() {
		return [ 'alezux-estudiantes-register-js' ];
	}

	public function get_style_depends() {
		return [ 'alezux-estudiantes-register-css' ];
	}

	protected function register_controls() {

// --- CONTENIDO: ETIQUETAS ---
		$this->start_controls_section(
			'section_content_labels',
			[
				'label' => esc_html__( 'Etiquetas y Textos', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title_text',
			[
				'label' => esc_html__( 'Título del Formulario', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Registrar Nuevo Estudiante', 'alezux-members' ),
			]
		);

		$this->add_control(
			'label_firstname',
			[
				'label' => esc_html__( 'Label Nombre', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Nombre', 'alezux-members' ),
			]
		);

		$this->add_control(
			'label_lastname',
			[
				'label' => esc_html__( 'Label Apellido', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Apellido', 'alezux-members' ),
			]
		);

		$this->add_control(
			'label_email',
			[
				'label' => esc_html__( 'Label Email', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Correo Electrónico', 'alezux-members' ),
			]
		);

		$this->add_control(
			'label_plan',
			[
				'label' => esc_html__( 'Label Plan/Curso', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Plan / Curso a Asignar', 'alezux-members' ),
			]
		);

		$this->add_control(
			'label_payment_method',
			[
				'label' => esc_html__( 'Label Método Pago', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Método de Pago', 'alezux-members' ),
			]
		);

		$this->add_control(
			'label_payment_ref',
			[
				'label' => esc_html__( 'Label Referencia', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Referencia / Comprobante', 'alezux-members' ),
			]
		);

		$this->add_control(
			'text_button',
			[
				'label' => esc_html__( 'Texto Botón', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Registrar Estudiante', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

		// --- ESTILO: CONTENEDOR ---
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Contenedor', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'container_background',
				'selector' => '{{WRAPPER}} .alezux-register-form',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'container_border',
				'selector' => '{{WRAPPER}} .alezux-register-form',
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => esc_html__( 'Relleno', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-register-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_radius',
			[
				'label' => esc_html__( 'Radio Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-register-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// --- ESTILO: CAMPOS ---
		$this->start_controls_section(
			'section_style_inputs',
			[
				'label' => esc_html__( 'Inputs y Etiquetas', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'input_bg_color',
			[
				'label' => esc_html__( 'Fondo Input', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-form-control' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_text_color',
			[
				'label' => esc_html__( 'Texto Input', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-form-control' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'input_border',
				'selector' => '{{WRAPPER}} .alezux-form-control',
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => esc_html__( 'Color Etiquetas', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-form-label' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-register-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// --- ESTILO: BOTÓN ---
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => esc_html__( 'Botón', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .alezux-register-submit',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => esc_html__( 'Normal', 'alezux-members' ),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => esc_html__( 'Color Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-register-submit' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_color',
			[
				'label' => esc_html__( 'Color Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-register-submit' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => esc_html__( 'Hover', 'alezux-members' ),
			]
		);

		$this->add_control(
			'button_text_color_hover',
			[
				'label' => esc_html__( 'Color Texto (Hover)', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-register-submit:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_color_hover',
			[
				'label' => esc_html__( 'Color Fondo (Hover)', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-register-submit:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

        $this->end_controls_section();

		// --- ESTILO: ALERTAS / MODAL ---
		$this->start_controls_section(
			'section_style_alerts',
			[
				'label' => esc_html__( 'Alertas / Modal', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'alert_bg_color',
			[
				'label' => esc_html__( 'Color de Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '', // JS fallback: linear-gradient
				'description' => 'Deja vacío para usar el degradado violeta por defecto.',
			]
		);

		$this->add_control(
			'alert_heading_title',
			[
				'label' => esc_html__( 'Título', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'alert_title_color',
			[
				'label' => esc_html__( 'Color Título', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'alert_title_typography',
				'selector' => '.alezux-toast-title', // Selector dummy, used for PHP retrieval mostly
			]
		);

		$this->add_control(
			'alert_heading_message',
			[
				'label' => esc_html__( 'Mensaje', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'alert_message_color',
			[
				'label' => esc_html__( 'Color Mensaje', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'alert_message_typography',
				'selector' => '.alezux-toast-message',
			]
		);

		$this->add_control(
			'alert_heading_button',
			[
				'label' => esc_html__( 'Botón "Entendido"', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'alert_btn_bg_color',
			[
				'label' => esc_html__( 'Fondo Botón', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => 'rgba(255, 255, 255, 0.2)',
			]
		);

		$this->add_control(
			'alert_btn_text_color',
			[
				'label' => esc_html__( 'Color Texto Botón', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'alert_btn_typography',
				'selector' => '.alezux-toast-action-btn',
			]
		);

		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		// Obtener PLANES disponibles de Finanzas
        global $wpdb;
        $plans = [];
        $table_plans = $wpdb->prefix . 'alezux_finanzas_plans';
        
        // Verificar tabla existe antes de query (por si modulo finanzas desactivado)
        if (  $wpdb->get_var("SHOW TABLES LIKE '$table_plans'") == $table_plans ) {
            $plans = $wpdb->get_results( "SELECT * FROM $table_plans ORDER BY name ASC" );
        }

		// Prepare Alert Config for JS
		$alert_config = [
			'bgColor'    => $settings['alert_bg_color'],
			'titleColor' => $settings['alert_title_color'],
			'msgColor'   => $settings['alert_message_color'],
			'btnBg'      => $settings['alert_btn_bg_color'],
			'btnColor'   => $settings['alert_btn_text_color'],
			// Typography needs manual extraction if not using selectors, 
			// but for now we pass colors mainly. Typography is complex to pass via JSON safely for JS injection 
			// without complex parsing. We will try to rely on direct styles helper if needed or just CSS.
			// Actually, let's pass simplest typography props or rely on inline style injection in JS.
			'titleSize'  => isset($settings['alert_title_typography_font_size']['size']) ? $settings['alert_title_typography_font_size']['size'] . $settings['alert_title_typography_font_size']['unit'] : '',
			'titleWeight'=> $settings['alert_title_typography_font_weight'],
			'msgSize'    => isset($settings['alert_message_typography_font_size']['size']) ? $settings['alert_message_typography_font_size']['size'] . $settings['alert_message_typography_font_size']['unit'] : '',
			'btnSize'    => isset($settings['alert_btn_typography_font_size']['size']) ? $settings['alert_btn_typography_font_size']['size'] . $settings['alert_btn_typography_font_size']['unit'] : '',
		];

		?>
		<div class="alezux-register-form">
			<h3 class="alezux-register-title"><?php echo esc_html( $settings['title_text'] ); ?></h3>
			
			<form id="alezux-manual-register-form" data-alert-config='<?php echo json_encode( $alert_config ); ?>'>
				<div class="alezux-form-group">
					<label class="alezux-form-label"><?php echo esc_html( $settings['label_firstname'] ); ?></label>
					<input type="text" name="first_name" class="alezux-form-control" required placeholder="<?php esc_attr_e( 'Nombre del estudiante', 'alezux-members' ); ?>">
				</div>

				<div class="alezux-form-group">
					<label class="alezux-form-label"><?php echo esc_html( $settings['label_lastname'] ); ?></label>
					<input type="text" name="last_name" class="alezux-form-control" required placeholder="<?php esc_attr_e( 'Apellido del estudiante', 'alezux-members' ); ?>">
				</div>

				<div class="alezux-form-group">
					<label class="alezux-form-label"><?php echo esc_html( $settings['label_email'] ); ?></label>
					<input type="email" name="email" class="alezux-form-control" required placeholder="<?php esc_attr_e( 'correo@ejemplo.com', 'alezux-members' ); ?>">
				</div>

				<div class="alezux-form-group">
					<label class="alezux-form-label"><?php esc_html_e( 'Contraseña (Opcional)', 'alezux-members' ); ?></label>
					<input type="text" name="password" class="alezux-form-control" placeholder="<?php esc_attr_e( 'Dejar en blanco para generar automática', 'alezux-members' ); ?>">
				</div>

				<div class="alezux-form-group">
					<label class="alezux-form-label"><?php echo esc_html( $settings['label_plan'] ); ?></label>
						<select name="plan_id" id="alezux-student-plan" class="alezux-form-control" required>
							<option value=""><?php esc_html_e( '-- Seleccionar Plan --', 'alezux-members' ); ?></option>
							<?php 
							if ( $plans ) {
								foreach ( $plans as $plan ) : ?>
									<option value="<?php echo esc_attr( $plan->id ); ?>">
										<?php echo esc_html( $plan->name ); ?>
									</option>
								<?php endforeach; 
							} else {
								echo '<option value="">No hay planes configurados</option>';
							}
							?>
						</select>
					</div>

					<div class="alezux-form-row" style="display: flex; gap: 15px;">
						<div class="alezux-form-group" style="flex: 1;">
							<label class="alezux-form-label"><?php echo esc_html( $settings['label_payment_method'] ); ?></label>
							<select name="payment_method" class="alezux-form-control">
								<option value="manual_cash"><?php esc_html_e( 'Efectivo', 'alezux-members' ); ?></option>
								<option value="manual_transfer"><?php esc_html_e( 'Transferencia / Depósito', 'alezux-members' ); ?></option>
							</select>
						</div>

					<div class="alezux-form-group" style="flex: 1;">
						<label class="alezux-form-label"><?php echo esc_html( $settings['label_payment_ref'] ); ?></label>
						<input type="text" name="payment_reference" class="alezux-form-control" placeholder="<?php esc_attr_e( 'Ej: OP-123456', 'alezux-members' ); ?>">
					</div>
				</div>

				<div class="alezux-form-actions">
					<button type="submit" class="alezux-register-submit">
						<?php echo esc_html( $settings['text_button'] ); ?> <i class="fas fa-spinner fa-spin" style="display:none;"></i>
					</button>
				</div>
                <div class="alezux-form-message"></div>
			</form>
		</div>
		<?php
	}
}
