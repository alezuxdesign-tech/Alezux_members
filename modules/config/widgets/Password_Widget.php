<?php
namespace Alezux_Members\Modules\Config\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Password_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux-password-widget';
	}

	public function get_title() {
		return esc_html__( 'Alezux Cambio de Contraseña', 'alezux-members' );
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
				'label' => esc_html__( 'Seguridad', 'alezux-members' ),
			]
		);

		$this->add_control(
			'instructions',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => esc_html__( 'Este widget permite al usuario cambiar su contraseña de forma segura.', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

		// --- STYLE SECTION: CONTAINER ---
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Contenedor del Formulario', 'alezux-members' ),
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

		$this->add_control(
			'label_heading',
			[
				'label' => esc_html__( 'Label', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'label_spacing',
			[
				'label' => esc_html__( 'Spacing', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 50 ],
				],
				'selectors' => [
					'{{WRAPPER}} .field-group label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// --- STYLE SECTION: STRENGTH METER ---
		$this->start_controls_section(
			'section_style_meter',
			[
				'label' => esc_html__( 'Medidor de Fortaleza', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'meter_bg_color',
			[
				'label' => esc_html__( 'Color de Fondo de Barra', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .password-strength-meter' => 'background-color: {{VALUE}};',
				],
				'default' => '#f0f0f0',
			]
		);

		$this->add_control(
			'req_text_color',
			[
				'label' => esc_html__( 'Color de Requisitos (Pendiente)', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .password-requirements li' => 'color: {{VALUE}};',
				],
				'default' => '#888',
			]
		);

		$this->add_control(
			'req_text_color_active',
			[
				'label' => esc_html__( 'Color de Requisitos (Cumplido)', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .password-requirements li.met' => 'color: {{VALUE}};',
				],
				'default' => '#27ae60',
			]
		);

		$this->end_controls_section();

		// --- STYLE SECTION: BUTTON ---
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => esc_html__( 'Botón de Actualizar', 'alezux-members' ),
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
					'justify' => [
						'title' => esc_html__( 'Justificado', 'alezux-members' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'prefix_class' => 'alezux-btn-align-',
				'default' => 'center',
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
		if ( ! is_user_logged_in() ) {
			echo '<div class="alezux-password-form"><p>' . esc_html__( 'Debes iniciar sesión para realizar esta acción.', 'alezux-members' ) . '</p></div>';
			return;
		}
		?>
		<div class="alezux-password-form">
			<form id="alezux-password-update-form">
				<div class="field-group">
					<label><?php esc_html_e( 'Contraseña Actual', 'alezux-members' ); ?></label>
					<div class="input-with-eye">
						<input type="password" name="old_password" required placeholder="••••••••">
						<span class="alezux-toggle-password"><i class="eicon-preview-medium"></i></span>
					</div>
				</div>

				<div class="field-group password-strength-wrapper">
					<label><?php esc_html_e( 'Nueva Contraseña', 'alezux-members' ); ?></label>
					<div class="input-with-eye">
						<input type="password" name="new_password" id="alezux-new-password" required placeholder="••••••••">
						<span class="alezux-toggle-password"><i class="eicon-preview-medium"></i></span>
					</div>
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
						<input type="password" name="confirm_password" required placeholder="••••••••">
						<span class="alezux-toggle-password"><i class="eicon-preview-medium"></i></span>
					</div>
				</div>

				<input type="hidden" name="action" value="alezux_change_password">
				<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'alezux-auth-nonce' ); ?>">
				
				<div class="button-wrapper">
					<button type="submit" class="alezux-submit-btn">
						<span class="btn-text"><?php esc_html_e( 'Actualizar Contraseña', 'alezux-members' ); ?></span>
						<span class="btn-loader" style="display: none;"><i class="eicon-spinner eicon-animation-spin"></i></span>
					</button>
				</div>
			</form>
		</div>
		<?php
	}
}
