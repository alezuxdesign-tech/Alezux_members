jQuery(document).ready(function ($) {

    // Helper functions for Notifications & Modals
    function showNotification(msg, type) {
        const id = 'notif-' + Date.now();
        const color = type === 'error' ? '#ff4757' : '#2ed573';
        const bg = type === 'error' ? 'rgba(255, 71, 87, 0.1)' : 'rgba(46, 213, 115, 0.1)';

        const markup = `
            <div id="${id}" style="position: fixed; bottom: 20px; right: 20px; z-index: 999999; background: ${bg}; border: 1px solid ${color}; color: ${color}; padding: 15px 25px; border-radius: 12px; font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,0.5); display: none; align-items: center; gap: 10px;">
                <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i> 
                ${msg}
            </div>
        `;
        $('body').append(markup);
        $(`#${id}`).fadeIn(300);
        setTimeout(() => {
            $(`#${id}`).fadeOut(300, function () { $(this).remove(); });
        }, 4000);
    }

    // Modal de Confirmación
    function customConfirm(message, callback) {
        // Eliminar confirmaciones pasadas si existen
        $('.confirm-modal-alezux').remove();

        const id = 'confirm-' + Date.now();
        const markup = `
            <div id="${id}" class="alezux-listing-modal-overlay confirm-modal-alezux" style="display: flex; z-index: 999999;">
                <div class="alezux-listing-modal-content" style="max-width: 400px; text-align: center;">
                    <div style="font-size: 40px; color: #ff4757; margin-bottom: 15px;"><i class="fas fa-exclamation-triangle"></i></div>
                    <h3 style="margin-bottom: 10px;">¿Estás seguro?</h3>
                    <p style="color: #a0a0a0; margin-bottom: 25px; line-height: 1.5;">${message}</p>
                    <div style="display: flex; gap: 10px; justify-content: center;">
                        <span class="alezux-btn btn-cancel-confirm" role="button" tabindex="0" style="background: transparent; border: 1px solid #333; color: #fff; padding: 10px 20px; border-radius: 8px; cursor: pointer;">Cancelar</span>
                        <span class="alezux-btn btn-accept-confirm" role="button" tabindex="0" style="background: #ff4757; color: #fff; padding: 10px 20px; border-radius: 8px; cursor: pointer;">Sí, Eliminar</span>
                    </div>
                </div>
            </div>
        `;
        $('body').append(markup);

        $(`#${id} .btn-cancel-confirm`).on('click', function (e) {
            e.preventDefault();
            $(`#${id}`).fadeOut(200, function () { $(this).remove(); });
        });

        $(`#${id} .btn-accept-confirm`).on('click', function (e) {
            e.preventDefault();
            $(`#${id}`).fadeOut(200, function () { $(this).remove(); });
            if (typeof callback === 'function') callback();
        });
    }

    // Load Tasks Logic (Independent from static variables to survive Elementor re-render)
    function loadTasksForWidget($widget) {
        const $tasksList = $widget.find('#alezux-admin-tasks-list');
        if ($tasksList.length === 0) return;

        // Evitar carga AJAX en el editor de Elementor si se están renderizando datos dummy por PHP
        if ($('body').hasClass('elementor-editor-active') && $tasksList.find('.alezux-task-item').length > 0) {
            return;
        }

        $tasksList.html('<div class="alezux-loading-tasks"><i class="fas fa-circle-notch fa-spin"></i> Cargando tareas...</div>');

        const iconEdit = $widget.attr('data-icon-edit') || '<i class="fas fa-edit"></i>';
        const iconDelete = $widget.attr('data-icon-delete') || '<i class="fas fa-trash"></i>';
        const iconHistory = $widget.attr('data-icon-history') || '<i class="fas fa-users"></i>';

        // Set Loading state
        $tasksList.html('<div class="alezux-loading-tasks" style="padding: 20px; text-align: center; color: #a0a0a0;"><i class="fas fa-circle-notch fa-spin"></i> Cargando tareas...</div>');

        $.ajax({
            url: alezux_listing_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'alezux_listing_get_tasks_admin',
                nonce: alezux_listing_vars.nonce
            },
            success: function (response) {
                if (response.success) {
                    renderTasks($tasksList, response.data, iconHistory, iconEdit, iconDelete);
                } else {
                    $tasksList.html('<div class="alezux-error-notice">Error al cargar tareas.</div>');
                }
            },
            error: function () {
                $tasksList.html('<div class="alezux-error-notice">Error de red al cargar tareas.</div>');
            }
        });
    }

    function renderTasks($listContainer, tasks, iconHistoryUrl, iconEditUrl, iconDeleteUrl) {
        $listContainer.empty();

        if (tasks.length === 0) {
            $listContainer.html('<div class="alezux-no-tasks" style="padding: 20px; text-align: center; color: #a0a0a0;">Aún no has creado ninguna tarea.</div>');
            return;
        }

        tasks.forEach(task => {
            const completedJson = encodeURIComponent(JSON.stringify(task.completed_by || []));
            const html = `
                <div class="alezux-task-item" data-id="${task.id}" data-completed="${completedJson}">
                    <div class="task-info">
                        <strong class="task-title">${task.title}</strong>
                        ${task.description ? `<p class="task-desc">${task.description}</p>` : ''}
                        <small class="task-meta"><i class="far fa-calendar-alt"></i> ${task.formatted_date}</small>
                    </div>
                    <div class="task-actions">
                        <span class="alezux-btn-icon btn-history-task" role="button" title="Historial">
                            <span style="pointer-events: none;">${iconHistoryUrl}</span>
                        </span>
                        <span class="alezux-btn-icon btn-edit-task" role="button" title="Editar Tarea">
                            <span style="pointer-events: none;">${iconEditUrl}</span>
                        </span>
                        <span class="alezux-btn-icon btn-delete-task" role="button" title="Eliminar Tarea">
                            <span style="pointer-events: none;">${iconDeleteUrl}</span>
                        </span>
                    </div>
                </div>
            `;
            $listContainer.append(html);
        });
    }

    // Helper para abrir modales clonándolos al body con los estilos de Elementor preservados
    function openAlezuxModal($originalModal, $widget, setupCallback) {
        if ($originalModal.length === 0) return;

        // Limpiar clones previos del mismo tipo
        const modalClass = $originalModal.attr('class').split(' ').filter(c => c.length > 0).join('.');
        $(`body > .elementor .${modalClass}.moved-to-body-modal`).closest('.elementor').remove();
        $(`body > .${modalClass}.moved-to-body-modal`).remove();

        // Clonar el modal
        const $modalClone = $originalModal.clone().addClass('moved-to-body-modal');
        
        // Extraer jerarquía de Elementor para el {{WRAPPER}}
        const $elementorElement = $widget.closest('.elementor-element');
        const elementorClasses = $elementorElement.attr('class') || '';
        const elementorId = $elementorElement.attr('data-id') || '';
        const $elementorRoot = $widget.closest('.elementor');
        const rootClasses = $elementorRoot.attr('class') || '';

        // Construir el envoltorio para preservar estilos
        let $finalElementToAppend = $modalClone;
        if (elementorClasses && elementorId) {
            const $innerWrapper = $('<div>', {
                'class': elementorClasses,
                'data-id': elementorId,
                'style': 'position: static; display: contents;'
            }).append($modalClone);

            if (rootClasses) {
                $finalElementToAppend = $('<div>', {
                    'class': rootClasses,
                    'style': 'position: static; display: contents;'
                }).append($innerWrapper);
            } else {
                $finalElementToAppend = $innerWrapper;
            }
        }

        $('body').append($finalElementToAppend);

        // Callback para popular datos
        if (typeof setupCallback === 'function') {
            setupCallback($modalClone);
        }

        // Mostrar con efecto
        $modalClone.removeClass('alezux-hidden').css({
            'display': 'flex',
            'opacity': '1',
            'visibility': 'visible',
            'z-index': '999999'
        }).hide().fadeIn(200);

        return $modalClone;
    }

    function setupHistoryModal($widget) {
        // En el editor no movemos nada para que los cambios de estilo sean live
        if ($('body').hasClass('elementor-editor-active')) return;

        const $originalHistoryModal = $widget.find('.alezux-history-task-modal');
        
        $widget.off('click', '.btn-history-task').on('click', '.btn-history-task', function (e) {
            e.preventDefault();
            const $item = $(this).closest('.alezux-task-item');
            const taskTitle = $item.find('.task-title').text();

            openAlezuxModal($originalHistoryModal, $widget, function($modal) {
                $modal.find('.history-task-name').text(taskTitle);
                const $historyTableBody = $modal.find('.history-table-body');
                $historyTableBody.empty();

                let completedUsers = [];
                try {
                    let dataAttr = $item.attr('data-completed');
                    if (dataAttr) {
                        completedUsers = JSON.parse(decodeURIComponent(dataAttr));
                    }
                } catch (err) {
                    console.error("Error parseando usuarios.", err);
                }

                if (completedUsers && completedUsers.length > 0) {
                    completedUsers.forEach(user => {
                        $historyTableBody.append(`
                            <tr>
                                <td><div class="student-name" style="font-weight: 600;">${user.display_name}</div></td>
                                <td style="color: var(--alezux-text-muted, #a0a0a0);">${user.user_email}</td>
                            </tr>
                        `);
                    });
                } else {
                    $historyTableBody.append(`<tr><td colspan="2" style="text-align: center; color: #a0a0a0; padding: 30px;">Esta tarea aún no ha sido completada por ningún usuario.</td></tr>`);
                }
            });
        });
    }

    // Inicializar todos los widgets
    function initAllWidgets() {
        $('.alezux-listing-admin').each(function () {
            loadTasksForWidget($(this));
            setupHistoryModal($(this));
        });
    }

    initAllWidgets();

    $(window).on('elementor/frontend/init', function () {
        if (elementorFrontend && elementorFrontend.hooks) {
            elementorFrontend.hooks.addAction('frontend/element_ready/alezux_listing_admin.default', function ($scope) {
                const $widget = $scope.find('.alezux-listing-admin');
                if ($widget.length > 0) {
                    loadTasksForWidget($widget);
                    setupHistoryModal($widget);
                }
            });
        }
    });

    // Eventos globales
    $(document.body).on('click', '.alezux-listing-modal-close', function () {
        const $overlay = $(this).closest('.alezux-listing-modal-overlay');
        $overlay.fadeOut(200, function () {
            // Si está dentro de un wrapper de Elementor (clon), borramos todo el wrapper
            const $wrapper = $overlay.closest('.elementor');
            if ($wrapper.length && $wrapper.parent().is('body')) {
                $wrapper.remove();
            } else if ($overlay.hasClass('moved-to-body-modal')) {
                $overlay.remove();
            }
        });
    });

    // SUBMIT ADD TASK
    $(document).on('submit', '#alezux-add-task-form', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $widget = $form.closest('.alezux-listing-admin');
        const $submitBtn = $form.find('#alezux-submit-task-btn');
        const $msgDiv = $form.find('#alezux-task-form-msg');
        const title = $form.find('#task_title').val();
        const description = $form.find('#task_description').val();

        if (!title) return;

        $submitBtn.prop('disabled', true).find('.btn-text').hide();
        $submitBtn.find('.btn-loading').show();
        $msgDiv.removeClass('success error').hide();

        $.ajax({
            url: alezux_listing_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'alezux_listing_add_task',
                nonce: alezux_listing_vars.nonce,
                title: title,
                description: description
            },
            success: function (response) {
                if (response.success) {
                    $msgDiv.addClass('success').text(response.data.message).fadeIn();
                    $form[0].reset();
                    loadTasksForWidget($widget);
                } else {
                    $msgDiv.addClass('error').text(response.data.message).fadeIn();
                }
            },
            error: function () {
                $msgDiv.addClass('error').text('Ocurrió un error en el servidor.').fadeIn();
            },
            complete: function () {
                $submitBtn.prop('disabled', false).find('.btn-text').show();
                $submitBtn.find('.btn-loading').hide();
                setTimeout(() => $msgDiv.fadeOut(), 4000);
            }
        });
    });

    // DELETE TASK
    $(document.body).on('click', '.btn-delete-task', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const $taskItem = $btn.closest('.alezux-task-item');
        const taskId = $taskItem.attr('data-id');
        const originalHtml = $btn.html();

        customConfirm("Esto no se puede deshacer y borrará el progreso de los usuarios.", function () {
            $btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
            $.ajax({
                url: alezux_listing_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'alezux_listing_delete_task',
                    nonce: alezux_listing_vars.nonce,
                    id: taskId
                },
                success: function (response) {
                    if (response.success) {
                        showNotification("Tarea eliminada correctamente.", "success");
                        $taskItem.fadeOut(300, function () { $(this).remove(); });
                    } else {
                        showNotification(response.data.message, "error");
                        $btn.html(originalHtml).prop('disabled', false);
                    }
                },
                error: function () {
                    showNotification('Error al intentar borrar la tarea.', 'error');
                    $btn.html(originalHtml).prop('disabled', false);
                }
            });
        });
    });

    // OPEN EDIT MODAL
    $(document.body).on('click', '.btn-edit-task', function (e) {
        e.preventDefault();
        const $widget = $(this).closest('.alezux-listing-admin');
        const $taskItem = $(this).closest('.alezux-task-item');
        const $originalModal = $widget.find('.alezux-edit-task-modal').not('.moved-to-body-modal');

        openAlezuxModal($originalModal, $widget, function($modal) {
            $modal.find('.edit_task_id').val($taskItem.attr('data-id'));
            $modal.find('.edit_task_title').val($taskItem.find('.task-title').text());
            $modal.find('.edit_task_description').val($taskItem.find('.task-desc').text());
            $modal.data('parent-widget', $widget);
        });
    });

    // SUBMIT EDIT FORM
    $(document.body).on('submit', '.alezux-edit-task-form', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $modal = $form.closest('.alezux-edit-task-modal');
        const $btn = $form.find('button[type="submit"]');
        const $parentWidget = $modal.data('parent-widget');
        const id = $form.find('.edit_task_id').val();
        const title = $form.find('.edit_task_title').val();
        const description = $form.find('.edit_task_description').val();

        if (!title) return;

        $btn.prop('disabled', true).find('.btn-text').hide();
        $btn.find('.btn-loading').show();

        $.ajax({
            url: alezux_listing_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'alezux_listing_edit_task',
                nonce: alezux_listing_vars.nonce,
                id: id,
                title: title,
                description: description
            },
            success: function (response) {
                if (response.success) {
                    showNotification(response.data.message, 'success');
                    $modal.fadeOut(200, function () {
                        const $wrapper = $modal.closest('.elementor');
                        if ($wrapper.length && $wrapper.parent().is('body')) {
                            $wrapper.remove();
                        } else {
                            $modal.remove();
                        }
                    });
                    if ($parentWidget) loadTasksForWidget($parentWidget);
                    else initAllWidgets();
                } else {
                    showNotification(response.data.message, 'error');
                }
            },
            error: function () {
                showNotification('Ocurrió un error en el servidor.', 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).find('.btn-text').show();
                $btn.find('.btn-loading').hide();
            }
        });
    });

});
