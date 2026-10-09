jQuery(document).ready(function ($) {
    const kanbanContainer = $('#alezux-kanban-app');
    if (!kanbanContainer.length) return;

    // init Sortable
    $('.kanban-column-body').sortable({
        connectWith: '.kanban-column-body',
        placeholder: "kanban-card-placeholder",
        start: function (event, ui) {
            ui.item.addClass('active');
        },
        stop: function (event, ui) {
            ui.item.removeClass('active');
        },
        receive: function (event, ui) {
            const projectId = ui.item.data('id');
            const newStatus = $(this).closest('.kanban-column').data('status');

            updateProjectStatus(projectId, newStatus);
        }
    }).disableSelection();

    // Load Projects
    loadProjects();

    function loadProjects() {
        $.ajax({
            url: alezux_agency_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'alezux_agency_get_projects',
                nonce: alezux_agency_vars.nonce
            },
            success: function (response) {
                if (response.success) {
                    renderProjects(response.data);
                } else {
                    console.error('Error loading projects:', response.data);
                }
            }
        });
    }

    function renderProjects(projects) {
        // Clear columns
        $('.kanban-column-body').empty();

        projects.forEach(project => {
            const card = createCard(project);
            $(`#col-${project.status}`).append(card);
        });
    }

    function createCard(project) {
        let stepLabel = '';
        switch (project.step) {
            case 'briefing': stepLabel = 'Briefing'; break;
            case 'identity': stepLabel = 'Identidad'; break;
            case 'web_design': stepLabel = 'Diseño Web'; break;
            case 'development': stepLabel = 'Desarrollo'; break;
            case 'delivery': stepLabel = 'Entrega'; break;
            default: stepLabel = project.step;
        }

        const avatarHtml = project.client_avatar ? `<img src="${project.client_avatar}" style="width:24px;height:24px;border-radius:50%;margin-right:5px;">` : '';

        const datesHtml = project.start_date && project.end_date ? `
            <div class="kanban-card-date">
                <i class="far fa-calendar-alt"></i> ${project.start_date} / ${project.end_date}
            </div>` : '';

        return $(`
            <div class="kanban-card" data-id="${project.id}">
                <div class="kanban-card-title">${project.title}</div>
                <div class="kanban-card-meta">
                    <div style="display:flex;align-items:center;">
                        ${avatarHtml} <span>${project.client_name}</span>
                    </div>
                </div>
                ${datesHtml}
                <div style="margin-top:8px;">
                    <span class="kanban-card-step status-${project.step}">${stepLabel}</span>
                </div>
            </div>
        `);
    }

    // Modals
    const detailsModal = $('#project-modal');
    const newProjectModal = $('#new-project-modal');

    // Step Definitions
    const PROJECT_STEPS = [
        { id: 'briefing', label: '1. Briefing' },
        { id: 'identity', label: '2. Identidad' },
        { id: 'web_design', label: '3. Diseño Web' },
        { id: 'development', label: '4. Desarrollo' },
        { id: 'delivery', label: '5. Entrega' }
    ];

    $('.close-details').on('click', function () { detailsModal.hide(); });
    $('.close-new').on('click', function () { newProjectModal.hide(); });

    // Open New Project Modal
    $('#add-project-btn').on('click', function () {
        newProjectModal.show();
        if ($.fn.datepicker) {
            $('.datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                beforeShowDay: $.datepicker.noWeekends
            });
        }

        // Load Users for Select
        const clientSelect = $('#new-project-client');
        if (clientSelect.children('option').length <= 1) {
            $.post(alezux_agency_vars.ajax_url, {
                action: 'alezux_agency_search_users',
                term: '',
                nonce: alezux_agency_vars.nonce
            }, function (response) {
                if (response.success) {
                    response.data.forEach(user => {
                        clientSelect.append(new Option(user.text, user.id));
                    });
                }
            });
        }
    });

    // Handle New Project Submit
    $('#new-project-form').on('submit', function (e) {
        e.preventDefault();

        const title = $('#new-project-name').val();
        const clientId = $('#new-project-client').val();
        const start = $('#new-project-start').val();
        const end = $('#new-project-end').val();

        if (!title || !clientId || !start || !end) {
            alert('Por favor completa todos los campos.');
            return;
        }

        $.post(alezux_agency_vars.ajax_url, {
            action: 'alezux_agency_create_project',
            title: title,
            client_id: clientId,
            start_date: start,
            end_date: end,
            nonce: alezux_agency_vars.nonce
        }, function (response) {
            if (response.success) {
                alert('Proyecto creado!');
                newProjectModal.hide();
                loadProjects();
                $('#new-project-form')[0].reset();
            } else {
                alert('Error: ' + response.data);
            }
        });
    });

    // Update Project Status Helper
    function updateProjectStatus(projectId, newStatus) {
        $.post(alezux_agency_vars.ajax_url, {
            action: 'alezux_agency_update_project_status',
            project_id: projectId,
            status: newStatus,
            nonce: alezux_agency_vars.nonce
        }, function (response) {
            if (!response.success) {
                alert('Error al actualizar estado');
                loadProjects();
            }
        });
    }

    let currentModalProject = null;
    let activeTabStep = null;

    // Open Details Modal on Card Click
    $(document).on('click', '.kanban-card', function () {
        const projectId = $(this).data('id');
        const modalBody = $('#modal-body-content');

        // Reset
        $('#project-edit-form').data('id', projectId);
        $('#save-project-btn').data('id', projectId);
        $('#modal-project-title').text('Cargando...');
        modalBody.html('<div class="kanban-loading">Obteniendo datos...</div>');
        detailsModal.show();

        $.post(alezux_agency_vars.ajax_url, {
            action: 'alezux_agency_get_project_details',
            project_id: projectId,
            nonce: alezux_agency_vars.nonce
        }, function (response) {
            if (response.success) {
                currentModalProject = response.data;
                activeTabStep = currentModalProject.current_step || 'briefing'; // Default to active step
                renderModalContent();
            } else {
                modalBody.html('<div class="alezux-alert-error">' + response.data + '</div>');
            }
        });
    });

    // Render Modal Content (Stepper UI)
    function renderModalContent() {
        const project = currentModalProject;
        $('#modal-project-title').text('Proyecto: ' + project.client_name);

        // Helper for safe access
        const getVal = (path, def = '') => {
            return path.split('.').reduce((acc, part) => acc && acc[part], project.data) || def;
        };

        // Stepper Nav
        let stepperHtml = '<div class="alezux-stepper">';
        PROJECT_STEPS.forEach((step, index) => {
            const isActive = step.id === activeTabStep ? 'active' : '';
            // Allow clicking any step to view
            stepperHtml += `<div class="step-item ${isActive}" data-step="${step.id}">${step.label}</div>`;
        });
        stepperHtml += '</div>';

        const datesHeader = getVal('start_date') && getVal('end_date') ? `
            <div class="modal-project-dates">
                <span><strong>Inicio:</strong> ${getVal('start_date')}</span>
                <span><strong>Fin estimado:</strong> ${getVal('end_date')}</span>
            </div>` : '';

        stepperHtml += datesHeader;

        // Sections (Only render active one visually, but input fields need to persist? 
        // Better to re-render inputs based on activeTabStep to avoid clutter)

        let contentHtml = '';

        // --- BRIEFING ---
        if (activeTabStep === 'briefing') {
            contentHtml = `
                <div class="step-content animate-fade-in">
                    <h4>Briefing & Datos</h4>
                    <label>Preferencias Web:</label>
                    <textarea class="alezux-input" readonly>${getVal('briefing.web_preferences')}</textarea>
                    <div style="display:flex; gap:10px; margin-top:10px;">
                        <div style="flex:1;">
                            <label>Tiene Logo:</label>
                            <input type="text" class="alezux-input" readonly value="${getVal('briefing.has_logo', 'No definido')}">
                        </div>
                        <div style="flex:1;">
                            <label>Logo URL:</label>
                            <input type="text" class="alezux-input" readonly value="${getVal('briefing.logo_url', '-')}">
                        </div>
                    </div>
                </div>`;
        }

        // --- IDENTITY ---
        else if (activeTabStep === 'identity') {
            const files = getVal('identity.proposal_files', []);
            const status = getVal('identity.status', '');
            const clientFeedback = getVal('identity.client_feedback', '');
            const history = getVal('identity.history', []);

            // Render History
            let historyHtml = '';
            if (history.length > 0) {
                historyHtml += '<div class="history-section" style="margin-bottom:20px; background:#f9f9f9; padding:10px; border-radius:5px;"><h5>Historial de Versiones</h5>';
                history.forEach((version, idx) => {
                    const date = version.timestamp || 'N/A';
                    const msg = version.message || `Versión ${idx + 1}`;
                    let vFilesHtml = '';
                    if (version.files && version.files.length) {
                        version.files.forEach((f, i) => {
                            vFilesHtml += `<a href="${f}" target="_blank" style="display:block; font-size:12px;"><i class="fas fa-paperclip"></i> Archivo ${i + 1}</a>`;
                        });
                    }
                    historyHtml += `
                        <div class="history-item" style="border-bottom:1px solid #ddd; padding:5px 0;">
                            <strong>${date}</strong> - ${msg}
                            <div style="margin-left:10px;">${vFilesHtml}</div>
                        </div>`;
                });
                historyHtml += '</div>';
            }

            let fileListHtml = '';
            files.forEach((url, i) => {
                fileListHtml += `
                    <div class="file-item-row" data-url="${url}">
                        <a href="${url}" target="_blank" class="file-link"><i class="fas fa-file"></i> Ver Archivo ${i + 1}</a>
                        <span class="remove-file" style="color:red;cursor:pointer;margin-left:10px;">&times;</span>
                    </div>`;
            });

            let statusAlert = '';
            if (status === 'changes_requested') {
                statusAlert = `<div class="alezux-alert-error"><strong>Cambios Solicitados:</strong> ${clientFeedback}</div>`;
            } else if (status === 'approved') {
                statusAlert = `<div class="alezux-alert-success"><strong>¡Aprobado por el Cliente!</strong> Puedes avanzar.</div>`;
            } else if (status === 'pending_review') {
                statusAlert = `<div class="alezux-alert-warning">Esperando revisión del cliente.</div>`;
            }

            contentHtml = `
                <div class="step-content animate-fade-in">
                    <h4>Identidad (Propuestas)</h4>
                    ${statusAlert}
                    
                    ${historyHtml}

                    <label>Archivos de Propuesta (Borrador Actual):</label>
                    <div id="identity-files-list" style="margin-bottom:10px;">${fileListHtml}</div>
                    
                    <button type="button" class="alezux-btn alezux-btn-secondary" id="btn-upload-identity">
                        <i class="fas fa-desktop"></i> Seleccionar Archivos (PC)
                    </button>
                    <input type="file" id="identity-native-upload" multiple style="display:none;" accept="image/*,application/pdf,.zip,.rar">
                    <input type="hidden" name="identity[proposal_files]" id="identity-files-input" value="${files.join(',')}">
                    
                    <div style="margin-top:20px; border-top:1px solid #eee; padding-top:15px;">
                        <button type="button" class="alezux-btn alezux-btn-info" id="btn-send-review">
                            <i class="fas fa-paper-plane"></i> Guardar y Enviar a Revisión
                        </button>
                        <p class="guidance-text">Al enviar, los archivos actuales se guardarán en el historial y se notificará al cliente.</p>
                    </div>
                </div>`;
        }

        // --- WEB DESIGN ---
        else if (activeTabStep === 'web_design') {
            const figmaUrl = getVal('web_design.figma_url');
            const status = getVal('web_design.status', '');
            const clientFeedback = getVal('web_design.client_feedback', '');
            const history = getVal('web_design.history', []);

            // Render History
            let historyHtml = '';
            if (history.length > 0) {
                historyHtml += '<div class="history-section" style="margin-bottom:20px; background:#f9f9f9; padding:10px; border-radius:5px;"><h5>Historial de Versiones</h5>';
                history.forEach((version, idx) => {
                    const date = version.timestamp || 'N/A';
                    const msg = version.message || `Versión ${idx + 1}`;
                    const vUrl = version.url || '#';
                    historyHtml += `
                        <div class="history-item" style="border-bottom:1px solid #ddd; padding:5px 0;">
                            <strong>${date}</strong> - ${msg}
                            <div style="margin-left:10px;">
                                <a href="${vUrl}" target="_blank" style="font-size:12px;"><i class="fas fa-external-link-alt"></i> Ver Prototipo</a>
                            </div>
                        </div>`;
                });
                historyHtml += '</div>';
            }

            let statusAlert = '';
            if (status === 'changes_requested') {
                statusAlert = `<div class="alezux-alert-error"><strong>Cambios Solicitados:</strong> ${clientFeedback}</div>`;
            } else if (status === 'approved') {
                statusAlert = `<div class="alezux-alert-success"><strong>¡Aprobado por el Cliente!</strong> Puedes avanzar.</div>`;
            } else if (status === 'pending_review') {
                statusAlert = `<div class="alezux-alert-warning">Esperando revisión del cliente.</div>`;
            }

            contentHtml = `
                <div class="step-content animate-fade-in">
                    <h4>Diseño Web (Figma)</h4>
                    ${statusAlert}
                    
                    ${historyHtml}

                    <label>URL Prototipo Figma (Actual):</label>
                    <input type="text" class="alezux-input" name="web_design[figma_url]" value="${figmaUrl}">
                    
                    <div style="margin-top:20px; border-top:1px solid #eee; padding-top:15px;">
                        <button type="button" class="alezux-btn alezux-btn-info" id="btn-send-review-web">
                            <i class="fas fa-paper-plane"></i> Guardar y Enviar a Revisión
                        </button>
                        <p class="guidance-text">Al enviar, la URL actual se guardará en el historial y se notificará al cliente.</p>
                    </div>
                </div>`;
        }

        // --- DEVELOPMENT ---
        else if (activeTabStep === 'development') {
            contentHtml = `
                <div class="step-content animate-fade-in">
                    <h4>Desarrollo (Staging)</h4>
                    <label>URL Sitio Staging:</label>
                    <input type="text" class="alezux-input" name="development[staging_url]" value="${getVal('development.staging_url')}">
                    <p class="guidance-text">El cliente revisará este enlace para dar aprobación final.</p>
                </div>`;
        }

        // --- DELIVERY ---
        else if (activeTabStep === 'delivery') {
            const logos = getVal('delivery.logos', []);
            const credentials = getVal('delivery.credentials', {});
            const videoLinks = getVal('delivery.video_links', []);

            let logosHtml = '';
            logos.forEach((url, i) => {
                logosHtml += `
                    <div class="file-item-row" data-url="${url}">
                        <a href="${url}" target="_blank" class="file-link"><i class="fas fa-image"></i> Logo ${i + 1}</a>
                        <span class="remove-logo" style="color:red;cursor:pointer;margin-left:10px;">&times;</span>
                    </div>`;
            });

            contentHtml = `
                <div class="step-content animate-fade-in">
                    <h4>Entrega Final</h4>
                    
                        <div id="logos-list" style="margin-bottom:10px;">${logosHtml}</div>
                        <button type="button" class="alezux-btn alezux-btn-secondary" id="btn-upload-logos">
                            <i class="fas fa-desktop"></i> Seleccionar Logos (PC)
                        </button>
                        <input type="file" id="logos-native-upload" multiple style="display:none;" accept="image/*">
                        <input type="hidden" id="logos-input" name="delivery[logos]" value="${logos.join(',')}">
                    </div>

                    <div class="delivery-section" style="margin-top:20px;">
                        <h5>2. Credenciales de Acceso</h5>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                             <div style="grid-column: span 2;">
                                <label>URL de Login:</label>
                                <input type="text" class="alezux-input" name="delivery[login_url]" value="${credentials.login_url || ''}">
                             </div>
                             <div>
                                <label>Usuario:</label>
                                <input type="text" class="alezux-input" name="delivery[user]" value="${credentials.user || ''}">
                             </div>
                             <div>
                                <label>Contraseña:</label>
                                <input type="text" class="alezux-input" name="delivery[password]" value="${credentials.password || ''}">
                             </div>
                        </div>
                    </div>

                    <div class="delivery-section" style="margin-top:20px;">
                        <h5>3. Links de Videos (Tutoriales)</h5>
                        <p class="guidance-text">Ingresa una URL por línea.</p>
                        <textarea class="alezux-input" name="delivery[video_links]" rows="4" placeholder="https://...">${videoLinks.join('\n')}</textarea>
                    </div>
                </div>`;
        }

        // Navigation Buttons
        const confirmBtn = `<button class="alezux-btn alezux-btn-primary" id="btn-advance-step">Completar y Avanzar</button>`;
        const saveBtnOnly = `<button class="alezux-btn alezux-btn-success" id="btn-save-step">Guardar Datos</button>`;
        const deleteBtn = `<button class="alezux-btn alezux-btn-danger" id="btn-delete-project">Eliminar Proyecto</button>`;

        const footerControls = `
            <div class="step-footer-controls">
                <div class="step-footer-left">
                    ${deleteBtn}
                </div>
                <div class="step-footer-actions">
                    ${saveBtnOnly}
                    ${activeTabStep !== 'delivery' ? confirmBtn : ''}
                </div>
            </div>
        `;

        $('#modal-body-content').html(stepperHtml + '<form id="project-edit-form">' + contentHtml + '</form>' + footerControls);

        // Bind Stepper Clicks
        $('.step-item').on('click', function () {
            activeTabStep = $(this).data('step');
            renderModalContent(); // Re-render
        });

        // Bind Action Buttons
        $('#btn-save-step').on('click', function () { saveCurrentStepData(false); });
        $('#btn-advance-step').on('click', function () { saveCurrentStepData(true); });

        // Bind Delete Project
        $('#btn-delete-project').on('click', function () {
            if (confirm('¿Estás seguro de que deseas ELIMINAR este proyecto? Esta acción no se puede deshacer.')) {
                $.post(alezux_agency_vars.ajax_url, {
                    action: 'alezux_agency_delete_project',
                    project_id: currentModalProject.id,
                    nonce: alezux_agency_vars.nonce
                }, function (response) {
                    if (response.success) {
                        alert('Proyecto eliminado.');
                        // Remove from DOM
                        $(`.kanban-card[data-id="${currentModalProject.id}"]`).remove();
                        // Close Modal
                        $('#project-modal').hide();
                    } else {
                        alert('Error: ' + response.data);
                    }
                });
            }
        });

        // Bind Identity Specific Actions
        if (activeTabStep === 'identity') {
            // Send Review
            $('#btn-send-review').off('click').on('click', function () {
                const currentVal = $('#identity-files-input').val();
                let currentFiles = currentVal ? currentVal.split(',') : [];

                if (currentFiles.length === 0) {
                    alert('Debes subir al menos un archivo antes de enviar a revisión.');
                    return;
                }

                if (!confirm('¿Estás seguro de enviar estos archivos a revisión? Se guardará una versión en el historial.')) return;

                // Add to History
                if (!currentModalProject.data.identity) currentModalProject.data.identity = {};
                if (!currentModalProject.data.identity.history) currentModalProject.data.identity.history = [];

                currentModalProject.data.identity.history.unshift({
                    timestamp: new Date().toLocaleString(),
                    files: currentFiles,
                    message: 'Enviado a revisión'
                });

                // Clear current files? -> User logic implies "sending new files", so maybe we keep them as "current draft" or clear them?
                // Usually in this workflow, the "Current Proposal" becomes the "History" and the input clears for next round, OR it stays. 
                // Based on "Se envia por primera vez... luego se le envia los nuevos", it suggests we might want to keep them visible but marked as sent?
                // The implementation plan says "Upload only saves draft". "Send moves to history". 
                // Let's Keep them in "proposal_files" so they are visible as "Latest status", but ALSO in history.
                // Actually, if we want to "upload new files with changes", we might want to clear the input for the next round?
                // Let's CLEAR the input to allow fresh upload for next round, as the "Current" is now in history.
                // BUT, the UI renders "Archivos de Propuesta (Borrador Actual)". If we clear it, they disappear.
                // Let's keep them. The user can remove them if they want to start fresh, or add to them.

                statusAlert = `<div class="alezux-alert-warning">Esperando revisión del cliente.</div>`; // Optimistic UI update? No, renderModalContent will handle it.

            });
        }

        // Bind Web Design Specific Actions
        if (activeTabStep === 'web_design') {
            $('#btn-send-review-web').off('click').on('click', function () {
                const currentUrl = $('input[name="web_design[figma_url]"]').val();

                if (!currentUrl) {
                    alert('Debes ingresar la URL de Figma antes de enviar a revisión.');
                    return;
                }

                if (!confirm('¿Estás seguro de enviar este diseño a revisión? Se guardará una versión en el historial.')) return;

                // Add to History
                if (!currentModalProject.data.web_design) currentModalProject.data.web_design = {};
                if (!currentModalProject.data.web_design.history) currentModalProject.data.web_design.history = [];

                currentModalProject.data.web_design.history.unshift({
                    timestamp: new Date().toLocaleString(),
                    url: currentUrl,
                    message: 'Enviado a revisión'
                });

                saveCurrentStepData(false, 'pending_review');
            });
        }

        // --- NATIVE UPLOAD LOGIC ---
        
        // Identity Upload
        $(document).off('click', '#btn-upload-identity').on('click', '#btn-upload-identity', function(e) {
            e.preventDefault();
            $('#identity-native-upload').click();
        });

        $(document).off('change', '#identity-native-upload').on('change', '#identity-native-upload', function() {
            handleNativeBatchUpload(this, 'identity');
        });

        // Delivery Logos Upload
        $(document).off('click', '#btn-upload-logos').on('click', '#btn-upload-logos', function(e) {
            e.preventDefault();
            $('#logos-native-upload').click();
        });

        $(document).off('change', '#logos-native-upload').on('change', '#logos-native-upload', function() {
            handleNativeBatchUpload(this, 'logos');
        });

        function handleNativeBatchUpload(input, target) {
            const files = input.files;
            if (!files.length) return;

            const listId = target === 'identity' ? '#identity-files-list' : '#logos-list';
            const inputId = target === 'identity' ? '#identity-files-input' : '#logos-input';
            const btnId = target === 'identity' ? '#btn-upload-identity' : '#btn-upload-logos';
            const btnIcon = target === 'identity' ? 'fa-desktop' : 'fa-desktop';
            const btnText = target === 'identity' ? 'Seleccionar Archivos (PC)' : 'Seleccionar Logos (PC)';

            // 1. Show Optimistic UI (Loading indicators)
            $(btnId).html('<i class="fas fa-spinner fa-spin"></i> Subiendo...').prop('disabled', true);
            
            for (let i = 0; i < files.length; i++) {
                $(listId).append(`
                    <div class="file-item-row uploading-ghost" style="opacity:0.6;">
                        <span><i class="fas fa-sync fa-spin"></i> ${files[i].name}...</span>
                    </div>
                `);
            }

            // 2. Prepare Batch
            let formData = new FormData();
            formData.append('action', 'alezux_agency_upload_file');
            Array.from(files).forEach(file => {
                formData.append('files[]', file);
            });

            // 3. AJAX Request
            $.ajax({
                url: alezux_agency_vars.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        const newUrls = response.data.files.map(f => f.url);
                        const currentVal = $(inputId).val();
                        let currentUrls = currentVal ? currentVal.split(',') : [];
                        currentUrls = [...currentUrls, ...newUrls].filter(s => s);

                        // Update Model
                        if (target === 'identity') {
                            if (!currentModalProject.data.identity) currentModalProject.data.identity = {};
                            currentModalProject.data.identity.proposal_files = currentUrls;
                        } else {
                            if (!currentModalProject.data.delivery) currentModalProject.data.delivery = {};
                            currentModalProject.data.delivery.logos = currentUrls;
                        }

                        $(inputId).val(currentUrls.join(','));
                        renderModalContent(); // Redraw with actual links
                        saveCurrentStepData(false, null, true); // Silent background save
                    } else {
                        alert('Error al subir: ' + (response.data || 'Error desconocido'));
                        renderModalContent(); // Cleanup ghosts
                    }
                },
                error: function () {
                    alert('Error de red al subir archivos.');
                    renderModalContent(); // Cleanup ghosts
                },
                complete: function() {
                    $(btnId).html(`<i class="fas ${btnIcon}"></i> ${btnText}`).prop('disabled', false);
                    input.value = ''; // Reset input to allow same file again
                }
            });
        }

        // Close on outside click is handled above
        $(document).off('click', '.remove-file').on('click', '.remove-file', function () {
            const urlToRemove = $(this).parent().data('url');
            const currentVal = $('#identity-files-input').val();
            let currentUrls = currentVal ? currentVal.split(',') : [];

            currentUrls = currentUrls.filter(u => u !== urlToRemove);
            $('#identity-files-input').val(currentUrls.join(','));
            
            if (currentModalProject.data.identity) {
                currentModalProject.data.identity.proposal_files = currentUrls;
            }
            
            renderModalContent();
            saveCurrentStepData(false, null, true);
        });

        $(document).off('click', '.remove-logo').on('click', '.remove-logo', function () {
            const urlToRemove = $(this).parent().data('url');
            const currentVal = $('#logos-input').val();
            let currentUrls = currentVal ? currentVal.split(',') : [];
            currentUrls = currentUrls.filter(u => u !== urlToRemove);
            $('#logos-input').val(currentUrls.join(','));
            
            if (currentModalProject.data.delivery) {
                currentModalProject.data.delivery.logos = currentUrls;
            }

            renderModalContent();
            saveCurrentStepData(false, null, true);
        });

        // Update main modal footer to be hidden or standard
        $('#save-project-btn').hide(); // Hide the old global save button
    }

    function saveCurrentStepData(advance = false, specificStatus = null, silent = false) {
        const projectId = currentModalProject.id;
        let formData = {};

        if (activeTabStep === 'identity') {
            const proposalFiles = $('input[name="identity[proposal_files]"]').val();
            // Retrieve existing history and feedback from model
            const currentHistory = (currentModalProject.data.identity && currentModalProject.data.identity.history) ? currentModalProject.data.identity.history : [];
            const currentFeedback = (currentModalProject.data.identity && currentModalProject.data.identity.client_feedback) ? currentModalProject.data.identity.client_feedback : '';

            formData.identity = {
                proposal_files: proposalFiles ? proposalFiles.split(',').filter(s => s) : [],
                history: currentHistory,
                client_feedback: currentFeedback
            };
            if (specificStatus) {
                formData.identity.status = specificStatus;
            } else {
                // Preserve existing status
                formData.identity.status = (currentModalProject.data.identity && currentModalProject.data.identity.status) ? currentModalProject.data.identity.status : '';
            }
        } else if (activeTabStep === 'web_design') {
            const figmaUrl = $('input[name="web_design[figma_url]"]').val();
            const currentHistory = (currentModalProject.data.web_design && currentModalProject.data.web_design.history) ? currentModalProject.data.web_design.history : [];
            const currentFeedback = (currentModalProject.data.web_design && currentModalProject.data.web_design.client_feedback) ? currentModalProject.data.web_design.client_feedback : '';

            formData.web_design = {
                figma_url: figmaUrl || '',
                history: currentHistory,
                client_feedback: currentFeedback
            };
            if (specificStatus) {
                formData.web_design.status = specificStatus;
            } else {
                formData.web_design.status = (currentModalProject.data.web_design && currentModalProject.data.web_design.status) ? currentModalProject.data.web_design.status : '';
            }
        } else if (activeTabStep === 'development') {
            const stagingUrl = $('input[name="development[staging_url]"]').val();
            formData.development = { staging_url: stagingUrl || '' };
        } else if (activeTabStep === 'delivery') {
            const logos = $('#logos-input').val();
            const videoLinks = $('textarea[name="delivery[video_links]"]').val();

            formData.delivery = {
                logos: logos ? logos.split(',').filter(s => s) : [],
                credentials: {
                    login_url: $('input[name="delivery[login_url]"]').val() || '',
                    user: $('input[name="delivery[user]"]').val() || '',
                    password: $('input[name="delivery[password]"]').val() || ''
                },
                video_links: videoLinks ? videoLinks.split('\n').filter(s => s) : []
            };
        }

        // 2. Save Data via AJAX
        $.post(alezux_agency_vars.ajax_url, {
            action: 'alezux_agency_update_project_data',
            project_id: projectId,
            project_data: JSON.stringify(formData),
            nonce: alezux_agency_vars.nonce
        }, function (response) {
            if (response.success) {
                // Update local model
                $.extend(true, currentModalProject.data, formData);

                if (advance) {
                    advanceToNextStep(projectId);
                } else if (!silent) {
                    alert('Datos guardados.');
                    if (specificStatus) renderModalContent(); // Refresh to show status update
                } else {
                    // Silent save: just update UI if needed (e.g. status)
                    if (specificStatus) renderModalContent();
                }
            } else {
                alert('Error al guardar datos: ' + response.data);
            }
        });
    }

    function advanceToNextStep(projectId) {
        // Find next step
        const currentIdx = PROJECT_STEPS.findIndex(s => s.id === activeTabStep);
        if (currentIdx < PROJECT_STEPS.length - 1) {
            const nextStepId = PROJECT_STEPS[currentIdx + 1].id;

            $.post(alezux_agency_vars.ajax_url, {
                action: 'alezux_agency_update_project_step',
                project_id: projectId,
                step: nextStepId,
                nonce: alezux_agency_vars.nonce
            }, function (response) {
                if (response.success) {
                    currentModalProject.current_step = nextStepId;
                    activeTabStep = nextStepId;
                    renderModalContent(); // Refresh UI to next step
                    updateProjectStatus(projectId, 'process'); // Optionally update Kanban column too?
                } else {
                    alert('Error al avanzar: ' + response.data);
                }
            });
        }
    }

    // Close on outside click is handled above

});
