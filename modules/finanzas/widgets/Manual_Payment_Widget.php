<?php
namespace Alezux_Members\Modules\Finanzas\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Manual_Payment_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux_manual_payment';
	}

	public function get_title() {
		return esc_html__( 'Registro Pago Manual (Alezux)', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	public function get_categories() {
		return [ 'alezux-finanzas' ];
	}

    public function get_style_depends() {
		return [ 'alezux-manual-payment-css' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Configuración', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);
        
        $this->add_control(
			'info_text',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => __( 'Este widget renderiza un formulario para registrar pagos manualmente. Asegúrate de colocarlo en una página protegida solo para administradores.', 'alezux-members' ),
			]
		);

		$this->end_controls_section();

        // Estilos Formulario
        $this->start_controls_section(
			'style_form_section',
			[
				'label' => esc_html__( 'Estilo Formulario', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        // Inputs
        $this->add_control(
			'input_bg_color',
			[
				'label' => esc_html__( 'Color Fondo Inputs', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-manual-pay-form input, {{WRAPPER}} .alezux-manual-pay-form select' => 'background-color: {{VALUE}}',
				],
			]
		);

        $this->add_control(
			'input_text_color',
			[
				'label' => esc_html__( 'Color Texto Inputs', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-manual-pay-form input, {{WRAPPER}} .alezux-manual-pay-form select' => 'color: {{VALUE}}',
				],
			]
		);

        $this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'input_border',
				'selector' => '{{WRAPPER}} .alezux-manual-pay-form input, {{WRAPPER}} .alezux-manual-pay-form select',
			]
		);

        // Botón
        $this->add_control(
			'button_bg_color',
			[
				'label' => esc_html__( 'Color Fondo Botón', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-manual-pay-btn' => 'background-color: {{VALUE}}',
				],
			]
		);

        $this->add_control(
			'button_text_color',
			[
				'label' => esc_html__( 'Color Texto Botón', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-manual-pay-btn' => 'color: {{VALUE}}',
				],
			]
		);

        $this->end_controls_section();
	}

	protected function render() {
        // Lógica de Procesamiento (Si queremos procesar en la misma carga, o usar admin-post.php)
        // Usaremos admin-post.php para consistencia con la implementación anterior
        
        global $wpdb;
        $plans = $wpdb->get_results( "SELECT id, name, quota_amount FROM {$wpdb->prefix}alezux_finanzas_plans" );

        if ( isset( $_GET['message'] ) && $_GET['message'] == 'success_manual_pay' ) {
            echo '<div class="alezux-success-msg">Pago registrado correctamente.</div>';
        }
        ?>
        <div class="alezux-manual-pay-wrapper">
             <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="alezux-manual-pay-form">
                <input type="hidden" name="action" value="alezux_manual_payment">
                <!-- Por seguridad, idealmente deberíamos generar nonce aquí si es posible en frontend context, 
                     o usar un ajax action. Para simplicidad de migración, usamos wp_nonce_field pero ojo con el caching -->
                <?php wp_nonce_field( 'alezux_manual_payment_action', 'alezux_nonce' ); ?>
                
                <div class="alezux-form-group">
                    <label for="user_email">Email del Usuario *</label>
                    <input type="email" name="user_email" id="user_email" required placeholder="email@usuario.com">
                </div>

                <div class="alezux-form-group">
                    <label for="plan_id">Plan *</label>
                    <select name="plan_id" id="plan_id" required>
                        <option value="">Selecciona un Plan...</option>
                        <?php foreach ( $plans as $plan ) : ?>
                            <option value="<?php echo esc_attr( $plan->id ); ?>" data-amount="<?php echo esc_attr( $plan->quota_amount ); ?>">
                                <?php echo esc_html( $plan->name ); ?> ($<?php echo esc_html( $plan->quota_amount ); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="alezux-form-group">
                    <label for="amount">Monto Pagado *</label>
                    <input type="number" step="0.01" name="amount" id="amount" required>
                </div>

                <div class="alezux-form-group">
                    <label for="payment_method">Método de Pago</label>
                    <select name="payment_method" id="payment_method">
                        <option value="transferencia">Transferencia Bancaria</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div class="alezux-form-group">
                    <label for="reference">Referencia / Notas</label>
                    <input type="text" name="reference" id="reference" placeholder="#Comprobante 1234">
                </div>
                
                <button type="submit" class="alezux-manual-pay-btn">Registrar Pago</button>
            </form>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var planSelect = document.getElementById('plan_id');
                    var amountInput = document.getElementById('amount');
                    if(planSelect && amountInput) {
                        planSelect.addEventListener('change', function() {
                            var selected = this.options[this.selectedIndex];
                            var amount = selected.getAttribute('data-amount');
                            if (amount) {
                                amountInput.value = amount;
                            }
                        });
                    }
                });
            </script>
        </div>
        <?php
	}
}
