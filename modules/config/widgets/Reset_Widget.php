<?php
namespace Alezux_Members\Modules\Config\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Reset_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux-reset-password-widget';
	}

	public function get_title() {
		return esc_html__( 'Alezux Reset Password', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-lock-user';
	}

	public function get_categories() {
		return [ 'alezux-auth' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Contenido', 'alezux-members' ),
			]
		);

		$this->add_control(
			'title',
			[
				'label' => esc_html__( 'Título', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Restablecer Contraseña', 'alezux-members' ),
			]
		);

		$this->add_control(
			'submit_text',
			[
				'label' => esc_html__( 'Texto Botón', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Guardar Nueva Contraseña', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

		// --- STYLE SECTION: CONTAINER ---
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Contenedor', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'form_padding',
			[
				'label' => esc_html__( 'Padding', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-password-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'form_bg',
				'selector' => '{{WRAPPER}} .alezux-password-form',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'form_border',
				'selector' => '{{WRAPPER}} .alezux-password-form',
			]
		);

		$this->add_control(
			'form_border_radius',
			[
				'label' => esc_html__( 'Borde Redondeado', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-password-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'form_shadow',
				'selector' => '{{WRAPPER}} .alezux-password-form',
			]
		);

		$this->end_controls_section();

		// --- STYLE SECTION: FIELDS ---
		$this->start_controls_section(
			'section_style_fields',
			[
				'label' => esc_html__( 'Campos de Texto', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'labels_typography',
				'label'    => esc_html__( 'Tipografía de Etiquetas', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .field-group label',
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => esc_html__( 'Color de Etiquetas', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .field-group label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'inputs_typography',
				'label'    => esc_html__( 'Tipografía de inputs', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .field-group input',
			]
		);

		$this->add_responsive_control(
			'field_spacing',
			[
				'label' => esc_html__( 'Rows Gap', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 100 ],
				],
				'selectors' => [
					'{{WRAPPER}} .field-group:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		// --- STYLE SECTION: BUTTON ---
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => esc_html__( 'Botón', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'button_align',
			[
				'label' => esc_html__( 'Alineación', 'alezux-members' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Izquierda', 'alezux-members' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centro', 'alezux-members' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Derecha', 'alezux-members' ),
						'icon' => 'eicon-text-align-right',
					],
					'stretch' => [
						'title' => esc_html__( 'Justificado', 'alezux-members' ),
						'icon' => 'eicon-h-align-stretch',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .button-wrapper' => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .button-wrapper .alezux-submit-btn' => 'width: {{VALUE}} == "stretch" ? 100% : auto;', 
					// Nota: Elementor no soporta lógica condicional neta en selectores, 
					// así que usaremos clases auxiliares o flexbox si es necesario.
					// Mejor enfoque para stretch:	
					'{{WRAPPER}} .button-wrapper' => 'display: flex; justify-content: {{VALUE}};',
				],
				'selectors_dictionary' => [
					'left' => 'flex-start',
					'center' => 'center',
					'right' => 'flex-end',
					'stretch' => 'stretch',
				],
				'default' => 'stretch',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .alezux-submit-btn',
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
				'label' => esc_html__( 'Color de Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-submit-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'button_bg',
				'selector' => '{{WRAPPER}} .alezux-submit-btn',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'button_border',
				'selector' => '{{WRAPPER}} .alezux-submit-btn',
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
				'label' => esc_html__( 'Color de Texto (Hover)', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-submit-btn:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'button_bg_hover',
				'selector' => '{{WRAPPER}} .alezux-submit-btn:hover',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'button_border_hover',
				'selector' => '{{WRAPPER}} .alezux-submit-btn:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label' => esc_html__( 'Borde Redondeado', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-submit-btn' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .alezux-submit-btn',
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__( 'Padding', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-submit-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		// Si estamos en el editor, mostramos el formulario
		$is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

		$key = isset( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : '';
		$login = isset( $_GET['login'] ) ? sanitize_user( $_GET['login'] ) : '';

		$error_message = false;
		$user = false;

		if ( ! $is_editor ) {
			if ( empty( $key ) || empty( $login ) ) {
				$error_message = 'Enlace de recuperación inválido o incompleto.';
			} else {
				// Verificar Key
				$user = check_password_reset_key( $key, $login );
				if ( is_wp_error( $user ) ) {
					$error_message = 'El enlace ha expirado o no es válido.';
				}
			}
		}

		?>
		<div class="alezux-password-form alezux-reset-form">
			<?php if ( ! empty( $settings['title'] ) ) : ?>
				<h2 class="alezux-auth-title" style="text-align: center; margin-bottom: 20px;"><?php echo esc_html( $settings['title'] ); ?></h2>
			<?php endif; ?>

			<?php if ( $error_message && ! $is_editor ) : ?>
				<div class="alezux-alert error">
					<p><?php echo esc_html( $error_message ); ?></p>
					<p><a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" style="text-decoration: underline;">Solicitar nuevo enlace</a></p>
				</div>
			<?php else : ?>
				
				<form id="alezux-reset-password-form">
					<div class="field-group password-strength-wrapper">
						<label><?php esc_html_e( 'Nueva Contraseña', 'alezux-members' ); ?></label>
						<div class="input-with-eye">
							<input type="password" name="pass1" id="pass1" required placeholder="••••••••">
							<span class="alezux-toggle-password"><i class="eicon-preview-medium"></i></span>
						</div>
						
						<!-- Reusing existing strength meter styles -->
						<div class="password-strength-meter">
							<div class="meter-fill"></div>
						</div>
						<ul class="password-requirements">
							<li data-req="length"><i class="eicon-check-circle"></i> <?php esc_html_e( 'Mínimo 8 caracteres', 'alezux-members' ); ?></li>
							<li data-req="upper"><i class="eicon-check-circle"></i> <?php esc_html_e( 'Al menos una mayúscula', 'alezux-members' ); ?></li>
							<li data-req="number"><i class="eicon-check-circle"></i> <?php esc_html_e( 'Al menos un número', 'alezux-members' ); ?></li>
							<li data-req="special"><i class="eicon-check-circle"></i> <?php esc_html_e( 'Al menos un signo', 'alezux-members' ); ?></li>
						</ul>
					</div>

					<div class="field-group">
						<label><?php esc_html_e( 'Confirmar Nueva Contraseña', 'alezux-members' ); ?></label>
						<div class="input-with-eye">
							<input type="password" name="pass2" id="pass2" required placeholder="••••••••">
							<span class="alezux-toggle-password"><i class="eicon-preview-medium"></i></span>
						</div>
					</div>

					<input type="hidden" name="action" value="alezux_reset_password">
					<input type="hidden" name="key" value="<?php echo esc_attr( $key ); ?>">
					<input type="hidden" name="login" value="<?php echo esc_attr( $login ); ?>">
					<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'alezux-auth-nonce' ); ?>">
					
					<div class="button-wrapper">
						<button type="submit" class="alezux-submit-btn">
							<span class="btn-text"><?php echo esc_html( $settings['submit_text'] ); ?></span>
							<span class="btn-loader" style="display: none;"><i class="eicon-spinner eicon-animation-spin"></i></span>
						</button>
					</div>
				</form>

			<?php endif; ?>
		</div>
		<?php
	}
}
