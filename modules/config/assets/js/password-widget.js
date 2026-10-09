jQuery(document).ready(function ($) {
    const $form = $('#alezux-password-update-form');
    const $newPassword = $('#alezux-new-password');
    const $meterFill = $form.find('.meter-fill');
    const $submitBtn = $form.find('.alezux-submit-btn');

    // Toggle Password Visibility
    $form.on('click', '.alezux-toggle-password', function () {
        const $input = $(this).siblings('input');
        const $icon = $(this).find('i');

        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('eicon-preview-medium').addClass('eicon-visibility-light');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('eicon-visibility-light').addClass('eicon-preview-medium');
        }
    });

    // Real-time Validation
    $newPassword.on('input', function () {
        const val = $(this).val();
        let strength = 0;

        const requirements = {
            length: val.length >= 8,
            upper: /[A-Z]/.test(val),
            number: /[0-9]/.test(val),
            special: /[^A-Za-z0-9]/.test(val)
        };

        $.each(requirements, function (req, met) {
            const $li = $(`.password-requirements li[data-req="${req}"]`);
            if (met) {
                $li.addClass('met');
                strength++;
            } else {
                $li.removeClass('met');
            }
        });

        $meterFill.removeClass('strength-weak strength-fair strength-good strength-strong').css('width', '');

        if (val.length > 0) {
            if (strength <= 1) $meterFill.addClass('strength-weak').css('width', '25%');
            else if (strength === 2) $meterFill.addClass('strength-fair').css('width', '50%');
            else if (strength === 3) $meterFill.addClass('strength-good').css('width', '75%');
            else if (strength === 4) $meterFill.addClass('strength-strong').css('width', '100%');
        } else {
            $meterFill.css('width', '0');
        }
    });

    // Submit AJAX
    $form.on('submit', function (e) {
        e.preventDefault();

        const data = $(this).serialize();

        $submitBtn.find('.btn-text').hide();
        $submitBtn.find('.btn-loader').show();
        $submitBtn.prop('disabled', true);

        $.ajax({
            url: alezux_auth_obj.ajax_url,
            type: 'POST',
            data: data,
            success: function (response) {
                if (response.success) {
                    window.alezuxShowModal('¡Éxito!', response.data.message, 'success');
                    $form[0].reset();
                    $meterFill.css('width', '0').removeClass('strength-weak strength-fair strength-good strength-strong');
                    $('.password-requirements li').removeClass('met');
                } else {
                    window.alezuxShowModal('Error', response.data.message, 'error');
                }
            },
            error: function () {
                window.alezuxShowModal('Error', 'Hubo un problema en el servidor.', 'error');
            },
            complete: function () {
                $submitBtn.find('.btn-text').show();
                $submitBtn.find('.btn-loader').hide();
                $submitBtn.prop('disabled', false);
            }
        });
    });
});
