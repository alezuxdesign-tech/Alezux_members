<?php
namespace Alezux_Members\Modules\Finanzas\Widgets;

use Alezux_Members\Core\Elementor_Widget_Base;
use Elementor\Controls_Manager;

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Create_Plan_Widget extends Elementor_Widget_Base {

	public function get_name() {
		return 'alezux_create_plan';
	}

	public function get_title() {
		return esc_html__( 'Creador de Planes (Finanzas)', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-price-table';
	}

	public function get_categories() {
		return [ 'alezux-finanzas' ];
	}

	protected function register_widget_controls() {
		
        $this->start_controls_section(
			'style_section',
			[
				'label' => esc_html__( 'Estilo del Formulario', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
			'primary_color',
			[
				'label' => esc_html__( 'Color Principal', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#6c5ce7',
				'selectors' => [
					'{{WRAPPER}} .alezux-btn-primary' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
                    '{{WRAPPER}} .alezux-plan-step-active' => 'border-color: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
        // Obtenemos todos los cursos de LearnDash para el select
        $courses = get_posts([
            'post_type' => 'sfwd-courses',
            'numberposts' => -1,
            'post_status' => 'publish'
        ]);
		?>
		<div class="alezux-create-plan-wrapper">
            
            <!-- Barra de Progreso -->
            <div class="alezux-plan-steps">
                <div class="alezux-plan-step alezux-plan-step-active" data-step="1">1. Definir Precio</div>
                <div class="alezux-plan-step" data-step="2">2. Reglas de Acceso</div>
            </div>

            <form id="alezux-create-plan-form">
                
                <!-- PASO 1: Configuración Financiera -->
                <div class="alezux-form-step alezux-step-1">
                    <h3>Configuración Financiera</h3>
                    
                    <div class="alezux-form-group">
                        <label>Nombre del Plan (Interno y Público)</label>
                        <input type="text" name="plan_name" placeholder="Ej: Bootcamp - Financiado 4 Cuotas" required>
                    </div>

                    <div class="alezux-form-group">
                        <label>Curso Asociado (LearnDash)</label>
                        <select name="course_id" id="alezux-plan-course" required>
                            <option value="">-- Selecciona un curso --</option>
                            <?php foreach($courses as $course): ?>
                                <option value="<?php echo esc_attr($course->ID); ?>"><?php echo esc_html($course->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="alezux-form-row">
                        <div class="alezux-form-group">
                            <label>Precio por Cuota (USD)</label>
                            <input type="number" name="quota_amount" placeholder="Ej: 1250" step="0.01" required>
                        </div>
                        <div class="alezux-form-group">
                            <label>Número de Cuotas</label>
                            <input type="number" name="total_quotas" placeholder="Ej: 4" min="1" max="24" required>
                            <small>Si es 1, se considera pago de contado.</small>
                        </div>
                    </div>

                    <div class="alezux-form-group">
                        <label>Frecuencia de Cobro</label>
                        <select name="frequency">
                            <option value="month">Mensual (Cada mes)</option>
                            <option value="week">Semanal (Cada semana)</option>
                            <option value="year">Anual (Cada año)</option>
                        </select>
                    </div>

                    <div class="alezux-form-group" style="background: rgba(108, 92, 231, 0.1); padding: 15px; border-radius: 8px; border: 1px solid #6c5ce7;">
                        <label style="display: flex; align-items: center; cursor: pointer; margin-bottom: 0;">
                            <input type="checkbox" name="is_internal" id="is_internal" value="1" style="width: auto; margin-right: 10px; cursor: pointer;">
                            <strong>Plan Interno (Sin Stripe)</strong>
                        </label>
                        <small style="display: block; margin-top: 5px; color: #aaa; margin-left: 25px;">Marca esta opción si gestionarás los pagos de forma manual. El plan se creará localmente sin conectarse a la API de Stripe.</small>
                    </div>

                    <div class="alezux-form-group" id="wa_number_group" style="display: none; background: rgba(37, 211, 102, 0.1); padding: 15px; border-radius: 8px; border: 1px solid #25D366; margin-top: 15px;">
                        <label>Enlace de Venta Manual / WhatsApp (Opcional)</label>
                        <input type="url" name="whatsapp_number" id="whatsapp_number" placeholder="Ej: https://wa.link/abcd o https://tu-sitio.com/pago">
                        <small style="color: #aaa;">Si el curso no tiene Stripe, el botón "Comprar Curso" redirigirá a este enlace.</small>
                    </div>

                    <button type="button" class="alezux-btn alezux-btn-primary" id="btn-goto-step-2" style="margin-top: 20px;">Siguiente: Configurar Reglas &rarr;</button>
                </div>

                <!-- PASO 2: Reglas de Desbloqueo -->
                <div class="alezux-form-step alezux-step-2" style="display:none;">
                    <h3>Reglas de Liberación de Contenido</h3>
                    <p class="description">Define con qué pago se desbloquea cada módulo del curso seleccionado.</p>
                    
                    <div id="alezux-course-modules-container">
                        <!-- Aquí se cargarán los módulos vía AJAX al seleccionar el curso -->
                        <div class="alezux-loading-spinner">Cargando módulos...</div>
                    </div>

                    <div class="alezux-form-actions">
                        <button type="button" class="alezux-btn alezux-btn-secondary" id="btn-back-step-1">&larr; Volver</button>
                        <button type="submit" class="alezux-btn alezux-btn-success">Guardar y Crear Plan en Stripe</button>
                    </div>
                </div>

            </form>

            <div id="alezux-plan-message"></div>

		</div>

        <style>
            .alezux-create-plan-wrapper {
                background: var(--alezux-bg-card, #1a1a1a);
                padding: 30px;
                border-radius: 15px;
                border: 1px solid var(--alezux-border-color, #333);
                color: #fff;
            }
            .alezux-plan-steps {
                display: flex;
                margin-bottom: 25px;
                border-bottom: 2px solid #333;
            }
            .alezux-plan-step {
                padding: 10px 20px;
                cursor: default;
                font-weight: bold;
                opacity: 0.5;
                border-bottom: 2px solid transparent;
                margin-bottom: -2px;
            }
            .alezux-plan-step-active {
                opacity: 1;
                border-bottom-color: var(--alezux-primary, #6c5ce7);
                color: var(--alezux-primary, #6c5ce7);
            }
            .alezux-form-group { margin-bottom: 20px; }
            .alezux-form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
            .alezux-form-group input, .alezux-form-group select {
                width: 100%;
                padding: 12px;
                border-radius: 8px;
                border: 1px solid #444;
                background: #222;
                color: #fff;
            }
            .alezux-form-row { display: flex; gap: 20px; }
            .alezux-form-row .alezux-form-group { flex: 1; }
            
            .alezux-btn {
                padding: 12px 25px;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                font-weight: bold;
                transition: all 0.3s;
            }
            .alezux-btn-primary {
                background: var(--alezux-primary, #6c5ce7);
                color: #fff;
            }
            .alezux-btn-secondary {
                background: #444;
                color: #fff;
            }
            .alezux-btn-success {
                background: #2ecc71;
                color: #fff;
            }

            /* Tabla de Reglas */
            .alezux-rules-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
            }
            .alezux-rules-table th, .alezux-rules-table td {
                padding: 10px;
                border-bottom: 1px solid #333;
                text-align: left;
            }
            .alezux-quota-select {
                padding: 5px;
                background: #222;
                color: #fff;
                border: 1px solid #555;
                border-radius: 4px;
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Navegación entre pasos
            $('#btn-goto-step-2').on('click', function() {
                var courseId = $('#alezux-plan-course').val();
                if(!courseId) {
                    alert('Por favor selecciona un curso primero');
                    return;
                }
                
                // Animación simple
                $('.alezux-step-1').hide();
                $('.alezux-step-2').fadeIn();
                $('.alezux-plan-step[data-step="2"]').addClass('alezux-plan-step-active');
                
                // Cargar Módulos del Curso vía AJAX
                loadCourseModules(courseId);
            });

            $('#btn-back-step-1').on('click', function() {
                $('.alezux-step-2').hide();
                $('.alezux-step-1').fadeIn();
                $('.alezux-plan-step[data-step="2"]').removeClass('alezux-plan-step-active');
            });

            function loadCourseModules(courseId) {
                $('#alezux-course-modules-container').html('<div class="alezux-loading-spinner">Cargando módulos...</div>');
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'alezux_get_course_modules', // Vamos a crear este handler
                        course_id: courseId,
                        nonce: '<?php echo wp_create_nonce("alezux_finanzas_nonce"); ?>'
                    },
                    success: function(response) {
                        if(response.success) {
                            renderModulesTable(response.data);
                        } else {
                            $('#alezux-course-modules-container').html('<p class="error">Error cargando módulos: ' + response.data + '</p>');
                        }
                    }
                });
            }

            function renderModulesTable(modules) {
                var totalQuotas = $('input[name="total_quotas"]').val();
                var html = '<table class="alezux-rules-table">';
                html += '<thead><tr><th>Módulo / Lección</th><th>Se desbloquea al pagar:</th></tr></thead><tbody>';
                
                modules.forEach(function(mod) {
                    html += '<tr>';
                    html += '<td>' + mod.title + '</td>';
                    html += '<td>';
                    html += '<select name="rules[' + mod.id + ']" class="alezux-quota-select">';
                    html += '<option value="1">Cuota 1 (Inmediato)</option>';
                    for(var i=2; i<=totalQuotas; i++) {
                        html += '<option value="' + i + '">Cuota ' + i + '</option>';
                    }
                    html += '</select>';
                    html += '</td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                $('#alezux-course-modules-container').html(html);
            }

            // Enviar Formulario Final
            $('#alezux-create-plan-form').on('submit', function(e) {
                e.preventDefault();
                var btn = $(this).find('button[type="submit"]');
                var isInternal = $('#is_internal').is(':checked');
                var btnText = isInternal ? 'Procesando Plan Interno...' : 'Procesando con Stripe...';
                var defaultBtnText = isInternal ? 'Guardar y Crear Plan Interno' : 'Guardar y Crear Plan en Stripe';
                
                btn.text(btnText).prop('disabled', true);
                
                var formData = $(this).serialize();

                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: formData + '&action=alezux_create_stripe_plan&nonce=<?php echo wp_create_nonce("alezux_finanzas_nonce"); ?>',
                    success: function(response) {
                        if(response.success) {
                            var msg = response.data.message ? response.data.message : 'Plan creado exitosamente.';
                            $('#alezux-plan-message').html('<div class="alezux-success-msg">✅ ' + msg + ' Actualizando página...</div>');
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            $('#alezux-plan-message').html('<div class="alezux-error-msg">❌ Error: ' + response.data + '</div>');
                            btn.text(defaultBtnText).prop('disabled', false);
                        }
                    }
                });
            });

            // Update button text when internal checkbox changes
            $('#is_internal').on('change', function() {
                var btn = $('#alezux-create-plan-form').find('button[type="submit"]');
                if($(this).is(':checked')) {
                    btn.text('Guardar y Crear Plan Interno');
                    $('#wa_number_group').slideDown();
                } else {
                    btn.text('Guardar y Crear Plan en Stripe');
                    $('#wa_number_group').slideUp();
                }
            });
        });
        </script>
		<?php
	}
}

