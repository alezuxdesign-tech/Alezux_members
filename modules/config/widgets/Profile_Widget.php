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

class Profile_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux-profile-widget';
	}

	public function get_title() {
		return esc_html__( 'Alezux Perfil de Usuario', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-user-circle-o';
	}

	public function get_categories() {
		return [ 'alezux-perfil' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Configuración', 'alezux-members' ),
			]
		);

		$this->add_control(
			'instructions',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => esc_html__( 'Este widget permite al usuario editar su información básica y foto de perfil.', 'alezux-members' ),
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
					'{{WRAPPER}} .alezux-profile-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'form_bg',
				'selector' => '{{WRAPPER}} .alezux-profile-form',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'form_border',
				'selector' => '{{WRAPPER}} .alezux-profile-form',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'form_shadow',
				'selector' => '{{WRAPPER}} .alezux-profile-form',
			]
		);

		$this->end_controls_section();

		// --- STYLE SECTION: AVATAR ---
		$this->start_controls_section(
			'section_style_avatar',
			[
				'label' => esc_html__( 'Foto de Perfil', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'avatar_size',
			[
				'label' => esc_html__( 'Tamaño', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [ 'min' => 50, 'max' => 200 ],
				],
				'selectors' => [
					'{{WRAPPER}} .profile-avatar img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'avatar_border_radius',
			[
				'label' => esc_html__( 'Borde Redondeado', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .profile-avatar img' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'avatar_overlay_bg',
			[
				'label' => esc_html__( 'Color de Fondo Icono', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .avatar-overlay' => 'background-color: {{VALUE}};',
				],
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
					'{{WRAPPER}} .alezux-fields-grid' => 'gap: {{SIZE}}{{UNIT}};',
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

		// --- STYLE SECTION: BUTTON ---
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => esc_html__( 'Botón de Guardar', 'alezux-members' ),
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

		$this->end_controls_section();

		// --- STYLE SECTION: CROP MODAL BUTTONS ---
		$this->start_controls_section(
			'section_style_crop_buttons',
			[
				'label' => esc_html__( 'Botones de Recorte (Modal)', 'alezux-members' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

        // --- CANCEL BUTTON ---
        $this->add_control(
			'heading_cancel_btn',
			[
				'label' => esc_html__( 'Botón Cancelar', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        $this->start_controls_tabs( 'tabs_cancel_btn_style' );

		$this->start_controls_tab(
			'tab_cancel_btn_normal',
			[ 'label' => esc_html__( 'Normal', 'alezux-members' ) ]
		);

        $this->add_control(
			'cancel_btn_color',
			[
				'label' => esc_html__( 'Color Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .alezux-btn-cancel' => 'color: {{VALUE}};' ],
			]
		);

        $this->add_control(
			'cancel_btn_bg',
			[
				'label' => esc_html__( 'Color Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .alezux-btn-cancel' => 'background-color: {{VALUE}};' ],
			]
		);

        $this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'cancel_btn_border',
				'selector' => '{{WRAPPER}} .alezux-btn-cancel',
			]
		);

        $this->end_controls_tab();

        $this->start_controls_tab(
			'tab_cancel_btn_hover',
			[ 'label' => esc_html__( 'Hover', 'alezux-members' ) ]
		);

        $this->add_control(
			'cancel_btn_color_hover',
			[
				'label' => esc_html__( 'Color Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .alezux-btn-cancel:hover' => 'color: {{VALUE}};' ],
			]
		);

        $this->add_control(
			'cancel_btn_bg_hover',
			[
				'label' => esc_html__( 'Color Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .alezux-btn-cancel:hover' => 'background-color: {{VALUE}};' ],
			]
		);

        $this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'cancel_btn_border_hover',
				'selector' => '{{WRAPPER}} .alezux-btn-cancel:hover',
			]
		);

        $this->end_controls_tab();
        $this->end_controls_tabs();

        // --- CROP BUTTON ---
        $this->add_control(
			'heading_crop_btn',
			[
				'label' => esc_html__( 'Botón Recortar', 'alezux-members' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        $this->start_controls_tabs( 'tabs_crop_btn_style' );

		$this->start_controls_tab(
			'tab_crop_btn_normal',
			[ 'label' => esc_html__( 'Normal', 'alezux-members' ) ]
		);

        $this->add_control(
			'crop_btn_color',
			[
				'label' => esc_html__( 'Color Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .alezux-btn-crop' => 'color: {{VALUE}};' ],
			]
		);

        $this->add_control(
			'crop_btn_bg',
			[
				'label' => esc_html__( 'Color Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .alezux-btn-crop' => 'background-color: {{VALUE}};' ],
			]
		);

        $this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'crop_btn_border',
				'selector' => '{{WRAPPER}} .alezux-btn-crop',
			]
		);

        $this->end_controls_tab();

        $this->start_controls_tab(
			'tab_crop_btn_hover',
			[ 'label' => esc_html__( 'Hover', 'alezux-members' ) ]
		);

        $this->add_control(
			'crop_btn_color_hover',
			[
				'label' => esc_html__( 'Color Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .alezux-btn-crop:hover' => 'color: {{VALUE}};' ],
			]
		);

        $this->add_control(
			'crop_btn_bg_hover',
			[
				'label' => esc_html__( 'Color Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .alezux-btn-crop:hover' => 'background-color: {{VALUE}};' ],
			]
		);

        $this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'crop_btn_border_hover',
				'selector' => '{{WRAPPER}} .alezux-btn-crop:hover',
			]
		);

        $this->end_controls_tab();
        $this->end_controls_tabs();
        
        // Common typography
        $this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'crop_btns_typography',
				'label'    => esc_html__( 'Tipografía General', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-btn-cancel, {{WRAPPER}} .alezux-btn-crop',
                'separator' => 'before',
			]
		);

        $this->add_control(
			'crop_btns_border_radius',
			[
				'label' => esc_html__( 'Borde Redondeado', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-cancel, {{WRAPPER}} .alezux-btn-crop' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);


		$this->end_controls_section();
	}

	protected function render() {
		$current_user = wp_get_current_user();
		if ( ! $current_user->exists() ) {
			echo '<div class="alezux-profile-form"><p>' . esc_html__( 'Debes iniciar sesión para ver esta información.', 'alezux-members' ) . '</p></div>';
			return;
		}

		// Obtener foto personalizada si existe, si no Gravatar
		$custom_avatar = get_user_meta( $current_user->ID, 'alezux_user_avatar', true );
		$avatar_url = $custom_avatar ? $custom_avatar : get_avatar_url( $current_user->ID );
		?>
		<div class="alezux-profile-form">
			<form id="alezux-profile-update-form" enctype="multipart/form-data">
				<div class="profile-avatar-wrapper">
					<div class="profile-avatar">
						<img src="<?php echo esc_url( $avatar_url ); ?>" alt="Avatar" id="alezux-avatar-preview">
						<div class="avatar-overlay" id="alezux-avatar-trigger">
							<i class="fas fa-camera"></i>
						</div>
					</div>
					<input type="file" name="alezux_avatar" id="alezux-avatar-input" accept="image/*" style="display: none;">
					<p class="avatar-hint"><?php esc_html_e( 'Haz clic en el icono para cambiar tu foto', 'alezux-members' ); ?></p>
				</div>
				
				<div class="alezux-fields-grid">
					<div class="field-group">
						<label><?php esc_html_e( 'Nombre', 'alezux-members' ); ?></label>
						<input type="text" name="first_name" value="<?php echo esc_attr( $current_user->first_name ); ?>" placeholder="<?php esc_attr_e( 'Tu nombre', 'alezux-members' ); ?>">
					</div>

					<div class="field-group">
						<label><?php esc_html_e( 'Apellido', 'alezux-members' ); ?></label>
						<input type="text" name="last_name" value="<?php echo esc_attr( $current_user->last_name ); ?>" placeholder="<?php esc_attr_e( 'Tu apellido', 'alezux-members' ); ?>">
					</div>

					<div class="field-group full-width">
						<label><?php esc_html_e( 'Correo Electrónico', 'alezux-members' ); ?></label>
						<input type="email" name="user_email" value="<?php echo esc_attr( $current_user->user_email ); ?>" placeholder="<?php esc_attr_e( 'tu@correo.com', 'alezux-members' ); ?>">
					</div>
				</div>

				<input type="hidden" name="action" value="alezux_update_profile">
				<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'alezux-auth-nonce' ); ?>">
				
				<div class="button-wrapper">
					<button type="submit" class="alezux-submit-btn">
						<span class="btn-text"><?php esc_html_e( 'Guardar Cambios', 'alezux-members' ); ?></span>
						<span class="btn-loader" style="display: none;"><i class="eicon-spinner eicon-animation-spin"></i> <?php esc_html_e( 'Guardando...', 'alezux-members' ); ?></span>
					</button>
				</div>
			</form>

            <!-- Modal de Recorte (Oculto por defecto) -->
            <div id="alezux-crop-modal" class="alezux-crop-modal" style="display: none;">
                <div class="alezux-crop-container">
                    <div class="alezux-crop-body">
                        <div class="img-container">
                            <img id="alezux-crop-image" src="" alt="Imagen para recortar">
                        </div>
                    </div>
                    <div class="alezux-crop-footer">
                        <button type="button" class="alezux-btn-cancel" id="alezux-crop-cancel"><?php esc_html_e( 'Cancelar', 'alezux-members' ); ?></button>
                        <button type="button" class="alezux-btn-crop" id="alezux-crop-confirm"><?php esc_html_e( 'Recortar y Guardar', 'alezux-members' ); ?></button>
                    </div>
                </div>
            </div>
		</div>
		<?php
	}
}
