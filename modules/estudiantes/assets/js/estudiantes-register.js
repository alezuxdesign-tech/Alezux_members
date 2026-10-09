jQuery(document).ready(function ($) {
    console.log('Alezux Register Widget JS v1.1.5 Loaded');
    var ajax_url = alezux_estudiantes_vars.ajax_url;
    var nonce = alezux_estudiantes_vars.nonce;

    /**
     * ================================
     * Lógica de Registro Manual
     * ================================
     */
    $('#alezux-manual-register-form').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $msg = $form.find('.alezux-form-message');
        var $spinner = $btn.find('i.fa-spinner');

        $btn.prop('disabled', true);
        $spinner.show();
        $msg.html('');

        var formData = {
            action: 'alezux_register_student',
            nonce: nonce,
            first_name: $form.find('input[name="first_name"]').val(),
            last_name: $form.find('input[name="last_name"]').val(),
            email: $form.find('input[name="email"]').val(),
            password: $form.find('input[name="password"]').val(),
            plan_id: $form.find('select[name="plan_id"]').val(),
            payment_method: $form.find('select[name="payment_method"]').val(),
            payment_reference: $form.find('input[name="payment_reference"]').val()
        };

        console.log('--------------------------------------------------');
        console.log('Alezux Debug: Data being sent to server:');
        console.log(formData);
        console.log('Plan ID raw value:', $form.find('select[name="plan_id"]').val());
        console.log('--------------------------------------------------');

        if (typeof alezuxShowToast === 'undefined') {
            // Define global helper if not exists (local scope wrapper)
            window.alezuxShowToast = function (message, type) {
                var $container = $('.alezux-toast-container');
                if ($container.length === 0) {
                    $('body').append('<div class="alezux-toast-container" style="display:none"></div>');
                    $container = $('.alezux-toast-container');
                }


                // Read Custom Config from Form Data
                var $form = $('#alezux-manual-register-form');
                // Use default empty object if undefined
                var config = $form.data('alert-config') || {};

                // Show container (backdrop)
                $container.fadeIn(200).css('display', 'flex');

                var typeClass = type === 'success' ? 'success' : 'error';
                var titleText = type === 'success' ? '¡Excelente!' : '¡Atención!';

                // Build inline styles from config
                var modalStyle = '';
                if (config.bgColor) {
                    modalStyle += 'background: ' + config.bgColor + ';';
                }

                var titleStyle = '';
                if (config.titleColor) titleStyle += 'color: ' + config.titleColor + ';';
                if (config.titleSize) titleStyle += 'font-size: ' + config.titleSize + ';';
                if (config.titleWeight) titleStyle += 'font-weight: ' + config.titleWeight + ';';

                var msgStyle = '';
                if (config.msgColor) msgStyle += 'color: ' + config.msgColor + ';';
                if (config.msgSize) msgStyle += 'font-size: ' + config.msgSize + ';';

                var btnStyle = '';
                if (config.btnBg) btnStyle += 'background-color: ' + config.btnBg + ';';
                if (config.btnColor) btnStyle += 'color: ' + config.btnColor + ';';
                if (config.btnSize) btnStyle += 'font-size: ' + config.btnSize + ';';

                // Limpiar alertas previas
                $container.empty();

                var $toast = $(
                    '<div class="alezux-toast ' + typeClass + '" style="' + modalStyle + '">' +
                    '<button class="alezux-toast-close">&times;</button>' +
                    '<h3 class="alezux-toast-title" style="' + titleStyle + '">' + titleText + '</h3>' +
                    '<div class="alezux-toast-message" style="' + msgStyle + '">' + message + '</div>' +
                    '<button class="alezux-toast-action-btn" style="' + btnStyle + '">Entendido</button>' +
                    '</div>'
                );

                $container.append($toast);

                // Interactions
                var closeAlert = function () {
                    $container.fadeOut(200, function () {
                        $container.empty();
                    });
                };

                $toast.find('.alezux-toast-close, .alezux-toast-action-btn').on('click', closeAlert);

                // Close on backdrop click
                $container.on('click', function (e) {
                    if (e.target === this) {
                        closeAlert();
                    }
                });
            };
        }

        $.ajax({
            url: ajax_url,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    window.alezuxShowToast(response.data.message, 'success');
                    $form[0].reset();
                } else {
                    window.alezuxShowToast(response.data.message || 'Error desconocido.', 'error');
                }
            },
            error: function () {
                window.alezuxShowToast('Error de conexión con el servidor.', 'error');
            },
            complete: function () {
                $btn.prop('disabled', false);
                $spinner.hide();
            }
        });
    });
});
