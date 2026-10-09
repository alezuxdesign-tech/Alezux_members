
jQuery(document).ready(function ($) {

    // Main Init Function
    function initMarketingAdmin($scope) {
        var wrapper = $scope ? $scope.find('.alezux-marketing-app') : $('.alezux-marketing-app');

        if (wrapper.length === 0) return;

        // Check for localize vars before using
        if (typeof alezux_marketing_vars === 'undefined') {
            console.warn('Alezux Marketing: Variables not loaded. Likely in Editor mode without frontend assets.');
        }

        var tableBody = wrapper.find('.marketing-templates-table tbody');
        var modalTemplate = wrapper.find('#marketing-template-modal');
        var modalSettings = wrapper.find('#marketing-settings-modal');

        // Unbind previous events to avoid duplicates if re-initialized
        $(document).off('click', '.edit-template-btn');

        wrapper.find('#btn-marketing-settings').off('click');
        wrapper.find('#marketing-template-form').off('submit');
        wrapper.find('#marketing-settings-form').off('submit');
        wrapper.find('.alezux-close-modal').off('click');

        // 1. Load Templates
        function loadTemplates(force = false) {
            // Skip check for editor to allow preview
            // if (wrapper.data('is-editor') === 'yes' && !force) { return; }

            if (typeof alezux_marketing_vars === 'undefined') {
                console.error('Alezux Marketing: alezux_marketing_vars missing');

                // Fallback for Elementor Editor Preview
                if ($('body').hasClass('elementor-editor-active') || wrapper.parents('.elementor-editor-active').length) {
                    console.log('Alezux Marketing: Editor detected, rendering dummy data.');
                    var dummy = [
                        { title: 'Bienvenida', description: 'Correo de registro.', subject: '¡Bienvenido!', sent_count: 120, is_active: 1, type: 'welcome' },
                        { title: 'Pago Exitoso', description: 'Confirmación de compra.', subject: 'Tu pedido #123', sent_count: 85, is_active: 1, type: 'payment' }
                    ];
                    // Render dummy data manually to avoid recursion or complexity
                    tableBody.empty();
                    dummy.forEach(function (item) {
                        var statusBadge = '<span class="status-badge status-active">Activo</span>';
                        var row = `
                             <tr>
                                 <td><strong style="font-size:14px; color:#2271b1;">${item.title}</strong><div style="font-size:12px; color:#666;">${item.description}</div></td>
                                 <td>${item.subject}</td>
                                 <td style="text-align:center;"><span class="alezux-count-badge">${item.sent_count}</span></td>
                                 <td>${statusBadge}</td>
                                 <td><button class="alezux-marketing-btn"><i class="fa fa-pencil"></i> Editar</button></td>
                             </tr>`;
                        tableBody.append(row);
                    });
                    return;
                }

                if (tableBody.find('tr').length === 0) {
                    tableBody.html('<tr><td colspan="5">Error: Variables no cargadas.</td></tr>');
                }
                return;
            }

            tableBody.html('<tr><td colspan="5" style="text-align:center; padding: 20px;">Cargando datos...</td></tr>');

            $.ajax({
                url: alezux_marketing_vars.ajax_url,
                method: 'POST',
                data: {
                    action: 'alezux_marketing_get_templates',
                    nonce: alezux_marketing_vars.nonce
                },
                success: function (res) {
                    if (res.success) {
                        tableBody.empty();
                        if (res.data.length === 0) {
                            tableBody.html('<tr><td colspan="5" style="text-align:center;">No hay plantillas creadas.</td></tr>');
                            return;
                        }

                        res.data.forEach(function (item) {
                            var statusBadge = item.is_active
                                ? '<span class="status-badge status-active">Activo</span>'
                                : '<span class="status-badge status-inactive">Inactivo</span>';

                            // Build Row
                            var row = `
                                 <tr>
                                     <td>
                                        <strong style="font-size:14px; color:#2271b1;">${item.title}</strong>
                                        <div style="font-size:12px; color:#666; margin-top:2px; line-height:1.3;">${item.description}</div>
                                     </td>
                                     <td>${item.subject}</td>
                                     <td style="text-align:center;">
                                         <span class="alezux-count-badge">${item.sent_count}</span>
                                     </td>
                                     <td>${statusBadge}</td>
                                     <td>
                                         <div style="display:flex; gap:5px;">
                                             <button class="alezux-marketing-btn edit-template-btn" data-type="${item.type}" title="Editar Plantilla">
                                                 <i class="fa fa-pencil"></i> <span>Editar</span>
                                             </button>
                                             <button class="alezux-marketing-btn history-btn" data-type="${item.type}" data-title="${item.title}" title="Ver Historial">
                                                 <i class="fa fa-history"></i> <span>Historial</span>
                                             </button>
                                             <button class="alezux-marketing-btn send-test-email-btn" data-type="${item.type}" title="Enviar prueba">
                                                 <i class="fa fa-paper-plane"></i> <span>Prueba</span>
                                             </button>
                                         </div>
                                     </td>
                                 </tr>
                             `;
                            tableBody.append(row);
                        });

                    } else {
                        tableBody.html('<tr><td colspan="5">Error al cargar: ' + res.data + '</td></tr>');
                    }
                },
                error: function (err) {
                    console.error(err);
                    tableBody.html('<tr><td colspan="5">Error de conexión.</td></tr>');
                }
            });
        }

        loadTemplates();

        // 2. Open Edit Modal
        $(document).on('click', '.edit-template-btn', function (e) {
            e.preventDefault();
            var type = $(this).data('type');

            // Logic for Dummy/Editor buttons (no data-type)
            if (!type) {
                $('#tpl-type').val('dummy_type');
                $('#tpl-subject').val('Asunto de Prueba para Diseño');
                $('#tpl-content').val('<h1>Hola [Nombre]</h1><p>Este es un cotenido de prueba para visualizar estilos.</p>');
                $('#tpl-active').prop('checked', true);
                $('#vars-list').text('{{user.name}}, {{site_name}}');
                $('#modal-title').text('Editando: Plantilla de Prueba');
                modalTemplate.css('display', 'flex');
                $('#toggle-preview-mode').prop('checked', false).trigger('change');
                return;
            }

            // Real Data Load
            $.ajax({
                url: alezux_marketing_vars.ajax_url,
                method: 'POST',
                data: {
                    action: 'alezux_marketing_get_template',
                    type: type,
                    nonce: alezux_marketing_vars.nonce
                },
                success: function (res) {
                    if (res.success) {
                        var tpl = res.data;
                        $('#tpl-type').val(tpl.type);
                        $('#tpl-subject').val(tpl.subject);
                        $('#tpl-content').val(tpl.content);
                        $('#tpl-active').prop('checked', tpl.is_active == 1);

                        // Populate variables list
                        var varsHtml = 'Sin variables específicas.';
                        if (tpl.variables && Array.isArray(tpl.variables) && tpl.variables.length > 0) {
                            varsHtml = tpl.variables.map(v => `<code style="display:inline-block; background:#f0f0f1; color:#3c434a; padding:2px 5px; margin:2px; border-radius:3px; font-size:11px; border:1px solid #c3c4c7;">${v}</code>`).join(' ');
                        }
                        $('#vars-list').html(varsHtml);

                        $('#modal-title').text('Editando: ' + (tpl.title || type)); // Fallback if title not passed (should be passed conceptually but type lookup is ok)
                        // Actually row doesn't have title, but we can pass it if we want. For now, type is fine or we trust the user knows what they clicked. 
                        // To be perfect, we could store title in data attribute of button.

                        modalTemplate.css('display', 'flex');

                        // Reset mode to edit
                        $('#toggle-preview-mode').prop('checked', false).trigger('change');
                    }
                }
            });
        });

        // History Handler
        $(document).on('click', '.history-btn', function (e) {
            e.preventDefault();
            var type = $(this).data('type');
            var title = $(this).data('title');
            var historyModal = $('#marketing-history-modal');
            var tbody = historyModal.find('#history-table tbody');

            if (!type) return;

            historyModal.find('#history-modal-title').text('Historial: ' + title);
            tbody.html('<tr><td colspan="4" style="text-align:center;">Cargando historial...</td></tr>');
            historyModal.css('display', 'flex');

            $.ajax({
                url: alezux_marketing_vars.ajax_url,
                method: 'POST',
                data: {
                    action: 'alezux_marketing_get_logs',
                    type: type,
                    nonce: alezux_marketing_vars.nonce
                },
                success: function (res) {
                    tbody.empty();
                    if (res.success && res.data.length > 0) {
                        res.data.forEach(function (log) {
                            var statusBadged = log.status;
                            var s = log.raw_status;
                            var actionBtn = '-';

                            if (s === 'sent' || s === 'enviado') {
                                statusBadged = '<span style="color:green;">Enviado</span>';
                            } else if (s === 'leído' || s === 'leido') {
                                statusBadged = '<span style="color:#2271b1; font-weight:bold;">Leído</span>';
                            } else if (s.indexOf('fail') !== -1 || s.indexOf('error') !== -1) {
                                statusBadged = '<span style="color:#d63638; font-weight:bold;" title="' + s + '">Fallido</span>';
                                actionBtn = `<button class="alezux-marketing-btn btn-resend-log" data-log-id="${log.id}" style="padding: 2px 8px; font-size: 11px; background: #6c5ce7;">Reenviar</button>`;
                            }

                            tbody.append(`
                                <tr>
                                    <td>${log.date}</td>
                                    <td>${log.recipient}</td>
                                    <td>${statusBadged}</td>
                                    <td>${actionBtn}</td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.html('<tr><td colspan="4" style="text-align:center;">No hay envíos registrados aún.</td></tr>');
                    }
                },
                error: function () {
                    tbody.html('<tr><td colspan="4" style="text-align:center;">Error al cargar historial.</td></tr>');
                }
            });
        });

        // Resend Handler
        $(document).on('click', '.btn-resend-log', function(e) {
            e.preventDefault();
            var btn = $(this);
            var logId = btn.data('log-id');
            var tdAction = btn.closest('td');
            var tdStatus = btn.closest('tr').find('td:eq(2)');

            var originalText = btn.html();
            btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

            $.ajax({
                url: alezux_marketing_vars.ajax_url,
                method: 'POST',
                data: {
                    action: 'alezux_marketing_resend_log',
                    log_id: logId,
                    nonce: alezux_marketing_vars.nonce
                },
                success: function(res) {
                    if (res.success) {
                        tdStatus.html('<span style="color:green;">Enviado</span>');
                        tdAction.html('<i class="fa fa-check" style="color:green;"></i>');
                    } else {
                        btn.html(originalText).prop('disabled', false);
                        showModalMessage('Error de reenvío', res.data.message || 'Error desconocido', true);
                    }
                },
                error: function() {
                    btn.html(originalText).prop('disabled', false);
                    showModalMessage('Error de red', 'No se pudo comunicar con el servidor.', true);
                }
            });
        });

        // Test Email Handler
        $(document).on('click', '.send-test-email-btn', function (e) {
            e.preventDefault();
            var type = $(this).data('type');

            // Dummy logic
            if (!type) { // Editor mode
                alert('En modo editor no se envían correos reales.');
                return;
            }

            var defaultEmail = (typeof alezux_marketing_vars !== 'undefined' && alezux_marketing_vars.from_email)
                ? alezux_marketing_vars.from_email
                : '';

            var email = prompt("Enviar correo de prueba a:", defaultEmail);

            if (email) {
                var btn = $(this);
                var originalHtml = btn.html();
                btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

                $.ajax({
                    url: alezux_marketing_vars.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'alezux_marketing_send_test_email',
                        type: type,
                        email: email,
                        nonce: alezux_marketing_vars.nonce
                    },
                    success: function (res) {
                        btn.html(originalHtml).prop('disabled', false);
                        if (res.success) {
                            showModalMessage('Éxito', res.data.message, false);
                        } else {
                            showModalMessage('Error', 'Error: ' + (res.data || 'Desconocido'), true);
                        }
                    },
                    error: function (err) {
                        btn.html(originalHtml).prop('disabled', false);
                        console.error(err);
                        showModalMessage('Error', 'Error de conexión.', true);
                    }
                });
            }
        });

        // Tab Handler (Preview)
        // Toggle Edit/Preview Mode
        wrapper.find('#toggle-preview-mode').on('change', function () {
            var isPreview = $(this).is(':checked');

            if (isPreview) {
                // Switch to Preview
                wrapper.find('#mode-status-text').text('Vista Previa');
                $('#tab-content-edit').hide();
                $('#tab-content-preview').show();

                // Render Preview with Variable Replacement
                var content = wrapper.find('#tpl-content').val();

                // Replace common vars to avoid 404s and show better preview
                var logoUrl = (typeof alezux_marketing_vars !== 'undefined' && alezux_marketing_vars.logo_url)
                    ? alezux_marketing_vars.logo_url
                    : 'https://via.placeholder.com/150x50?text=LOGO';

                // Replace {{logo_url}} BEFORE inserting into DOM to prevent 404 request
                content = content.replace(/{{logo_url}}/g, logoUrl);
                content = content.replace(/{{site_name}}/g, 'Mi Escuela');
                content = content.replace(/{{user.name}}/g, 'Estudiante');
                content = content.replace(/{{home_url}}/g, '#');

                // iframe Implementation for Isolated Preview
                var frameContainer = wrapper.find('#email-preview-frame');
                frameContainer.empty();

                var iframe = document.createElement('iframe');
                iframe.style.width = '100%';
                iframe.style.height = '600px';
                iframe.style.border = '1px solid #e5e5e5';
                iframe.style.background = '#fff';
                frameContainer.append(iframe);

                var doc = iframe.contentWindow.document;
                doc.open();

                // Ensure charset
                var fullHtml = content;
                // If it doesn't have a doctype or html tag, we might want to wrap it, 
                // but let's trust the browser to handle partials in an iframe.

                doc.write(fullHtml);
                doc.close();

            } else {
                // Switch to Edit
                wrapper.find('#mode-status-text').text('Editor HTML');
                $('#tab-content-edit').show();
                $('#tab-content-preview').hide();
            }
        });

        // Helper: Show Message Modal
        function showModalMessage(title, message, isError) {
            var modal = $('#alezux-message-modal');
            modal.find('#msg-modal-title').text(title);
            modal.find('#msg-modal-content').html(message);

            var icon = modal.find('#msg-modal-icon');
            var btn = modal.find('#msg-modal-btn');

            // Customize based on type
            icon.removeClass('fa-info-circle fa-check-circle fa-times-circle fa-exclamation-triangle');

            if (isError) {
                icon.addClass('fa-times-circle').css('color', '#d63638');
                btn.css('background-color', '#d63638');
            } else {
                icon.addClass('fa-check-circle').css('color', '#2271b1');
                btn.css('background-color', '#2271b1');
            }

            modal.css('display', 'flex');
        }

        // Close Message Modal
        wrapper.find('#msg-modal-btn').on('click', function () {
            $('#alezux-message-modal').hide();
        });

        // SMTP Fields Toggle Logic
        wrapper.find('#set-smtp-enabled').off('change').on('change', function () {
            var isEnabled = $(this).is(':checked');
            if (isEnabled) {
                wrapper.find('#smtp-fields-wrapper').show();
            } else {
                wrapper.find('#smtp-fields-wrapper').hide();
            }
        });

        wrapper.find('#set-smtp-auth').off('change').on('change', function () {
            var isAuth = $(this).is(':checked');
            if (isAuth) {
                wrapper.find('#smtp-auth-fields').show();
            } else {
                wrapper.find('#smtp-auth-fields').hide();
            }
        });

        wrapper.find('#toggle-smtp-pass').off('click').on('click', function () {
            var passInput = wrapper.find('#set-smtp-password');
            var icon = $(this).find('i');
            if (passInput.attr('type') === 'password') {
                passInput.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passInput.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // 3. Open Settings Modal
        wrapper.find('#btn-marketing-settings').on('click', function (e) {
            e.preventDefault();

            // Check if backend vars exist
            if (typeof alezux_marketing_vars === 'undefined') {
                modalSettings.css('display', 'flex'); // Just open in dummy mode
                return;
            }

            // Cargar settings
            $.ajax({
                url: alezux_marketing_vars.ajax_url,
                method: 'POST',
                data: {
                    action: 'alezux_marketing_get_settings',
                    nonce: alezux_marketing_vars.nonce
                },
                success: function (res) {
                    if (res.success) {
                        var s = res.data;
                        wrapper.find('#set-from-name').val(s.from_name);
                        wrapper.find('#set-from-email').val(s.from_email);
                        wrapper.find('#set-logo-url').val(s.logo_url);

                        // SMTP options
                        wrapper.find('#set-smtp-enabled').prop('checked', s.smtp_enabled === '1').trigger('change');
                        wrapper.find('#set-smtp-host').val(s.smtp_host);
                        wrapper.find('#set-smtp-port').val(s.smtp_port);
                        wrapper.find('#set-smtp-secure').val(s.smtp_secure);
                        wrapper.find('#set-smtp-auth').prop('checked', s.smtp_auth === '1').trigger('change');
                        wrapper.find('#set-smtp-username').val(s.smtp_username);
                        wrapper.find('#set-smtp-password').val(s.smtp_password);
                        wrapper.find('#set-smtp-skip-ssl').prop('checked', s.smtp_skip_ssl === '1');

                        // Update Logo Preview
                        updateLogoPreview(s.logo_url);

                        modalSettings.css('display', 'flex');
                    }
                },
                error: function () {
                    // Fallback for editor usage if ajax fails
                    modalSettings.css('display', 'flex');
                }
            });
        });

        // --- MEDIA UPLOADER LOGIC ---

        function updateLogoPreview(url) {
            var previewArea = wrapper.find('#logo-preview-area');
            var uploadPlace = wrapper.find('#logo-upload-placeholder');
            var input = wrapper.find('#set-logo-url');
            var img = wrapper.find('#logo-preview-img');

            if (url) {
                input.val(url);
                img.attr('src', url);
                previewArea.show();
                uploadPlace.hide();
            } else {
                input.val('');
                img.attr('src', '');
                previewArea.hide();
                uploadPlace.show();
            }
        }

        // Click on Upload Box or Button -> Trigger Hidden Input
        wrapper.find('#logo-upload-trigger').on('click', function (e) {
            // STOP RECURSION: If the event came from the file input itself (bubbling), ignore it.
            if ($(e.target).is('#logo-file-input')) {
                return;
            }

            // Prevent if clicking remove link
            if ($(e.target).is('#remove-logo')) return;

            e.preventDefault();
            // Trigger native file input
            wrapper.find('#logo-file-input').trigger('click');
        });

        // Handle File Selection (Native Input)
        wrapper.find('#logo-file-input').on('change', function () {
            var fileInput = this;
            if (fileInput.files && fileInput.files[0]) {
                var file = fileInput.files[0];
                var formData = new FormData();
                formData.append('file', file);
                formData.append('action', 'alezux_marketing_upload_logo');
                formData.append('nonce', alezux_marketing_vars.nonce);

                // Show loading state
                var btn = wrapper.find('.alezux-upload-btn-styled');
                var originalText = btn.text();
                btn.text('Subiendo...').prop('disabled', true);

                $.ajax({
                    url: alezux_marketing_vars.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        btn.text(originalText).prop('disabled', false);
                        if (res.success) {
                            updateLogoPreview(res.data.url);
                        } else {
                            // Show error in modal
                            showModalMessage('Error', 'Error al subir la imagen: ' + (res.data || 'Desconocido'), true);
                        }
                        // Clear input to allow re-uploading same file if needed
                        $(fileInput).val('');
                    },
                    error: function (err) {
                        btn.text(originalText).prop('disabled', false);
                        console.error('Upload error', err);
                        showModalMessage('Error', 'Error de conexión al subir la imagen.', true);
                    }
                });
            }
        });

        // Remove Image
        wrapper.find('#remove-logo').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation(); // prevent bubbling to upload trigger
            updateLogoPreview('');
        });


        // 4. Save Template
        wrapper.find('#marketing-template-form').on('submit', function (e) {
            e.preventDefault();
            var formData = $(this).serialize();
            formData += '&action=alezux_marketing_save_template&nonce=' + alezux_marketing_vars.nonce;

            $.post(alezux_marketing_vars.ajax_url, formData, function (res) {
                if (res.success) {
                    showModalMessage('Éxito', res.data.message, false);
                    modalTemplate.hide();
                    loadTemplates(true); // Force reload even in editor
                } else {
                    showModalMessage('Error', 'No se pudo guardar: ' + res.data, true);
                }
            });
        });

        // 5. Save Settings
        wrapper.find('#marketing-settings-form').on('submit', function (e) {
            e.preventDefault();
            var formData = $(this).serialize();
            formData += '&action=alezux_marketing_save_settings&nonce=' + alezux_marketing_vars.nonce;

            $.post(alezux_marketing_vars.ajax_url, formData, function (res) {
                if (res.success) {
                    showModalMessage('Guardado', res.data.message, false);
                    modalSettings.hide();
                }
            });
        });

        // Close Modals
        wrapper.find('.alezux-close-modal').on('click', function () {
            $(this).closest('.alezux-modal').hide();
        });

        // Global modal click listener
        $(window).off('click.alezuxMarketingModal').on('click.alezuxMarketingModal', function (e) {
            if ($(e.target).hasClass('alezux-modal')) {
                $('.alezux-modal').hide();
            }
        });
    }

    // Initialize on Elementor Frontend Init
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/alezux_marketing_config.default', function ($scope) {
            initMarketingAdmin($scope);
        });
    });

    // Also fallback for direct load
    initMarketingAdmin(null);

});
