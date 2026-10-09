jQuery(document).ready(function ($) {
    const appContainer = $('#alezux-client-app');
    if (!appContainer.length) return;

    const projectId = appContainer.data('id');

    // Navigation logic for timeline steps (using delegation for robustness)
    appContainer.on('click', '.timeline-step', function () {
        const targetId = $(this).data('step-target');
        if (!targetId) return;

        // Visual feedback on timeline
        $('.timeline-step').removeClass('active');
        $(this).addClass('active');

        // Content switch
        $('.step-content').hide().removeClass('active');
        $('#' + targetId).fadeIn().addClass('active');
    });

    // Handle Briefing Form
    $('#briefing-form').on('submit', function (e) {
        e.preventDefault();

        // Use FormData for file uploads
        const formData = new FormData(this);
        formData.append('action', 'alezux_agency_client_save_briefing');
        formData.append('project_id', projectId);
        formData.append('nonce', alezux_agency_vars.nonce);

        $.ajax({
            url: alezux_agency_vars.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    alert('Información guardada correctamente.');
                    location.reload();
                } else {
                    alert('Error: ' + (response.data || 'Ocurrió un error inesperado.'));
                }
            },
            error: function () {
                alert('Error de conexión.');
            }
        });
    });

    // Handle Full Control Button
    $('#btn-full-control').on('click', function () {
        if (confirm('¿Estás seguro? Esto indicará que confías el diseño completamente a nosotros.')) {
            $.post(alezux_agency_vars.ajax_url, {
                action: 'alezux_agency_client_save_briefing',
                project_id: projectId,
                web_preferences: 'Todo en sus manos. Confío en su criterio.',
                has_logo: 'no', // Assumption
                legal_data: 'Pendiente',
                nonce: alezux_agency_vars.nonce
            }, function (response) {
                if (response.success) {
                    alert('¡Excelente! Nos encargaremos de todo.');
                    location.reload();
                }
            });
        }
    });

    // Handle Approvals and Feedback
    $('.alezux-btn[data-action]').on('click', function (e) {
        e.preventDefault();
        const action = $(this).data('action'); // approve or changes
        const step = $(this).data('step');

        let feedback = '';
        if (action === 'changes') {
            const feedbackInput = $(this).closest('div').find('textarea');
            // Better selector strategy needed if multiple textareas, but structure is simple now
            // Actually the textarea is in a hidden div shown by another button
            if (step === 'identity') feedback = $('#identity-feedback-text').val();
            if (step === 'web_design') feedback = $('#web-feedback-text').val();

            if (!feedback) {
                alert('Por favor describe los cambios solicitados.');
                return;
            }
        } else {
            if (!confirm('¿Confirmas que apruebas esta fase?')) return;
            feedback = 'Aprobado por el cliente';
        }

        $.post(alezux_agency_vars.ajax_url, {
            action: 'alezux_agency_client_send_feedback',
            project_id: projectId,
            step: step,
            action_type: action,
            feedback: feedback,
            nonce: alezux_agency_vars.nonce
        }, function (response) {
            if (response.success) {
                alert(response.data);
                location.reload();
            } else {
                alert('Error: ' + response.data);
            }
        });

    });

});
