jQuery(document).ready(function ($) {
    console.log('[Alezux] Estudiantes JS Inicializado (v1.0.7)');

    // Debug Global: Detectar clics en cualquier parte de la tabla
    $(document).on('click', '.alezux-estudiantes-table', function (e) {
        console.log('[Alezux] Clic detectado en tabla:', e.target);
    });

    // ==========================================================
    // ALERTAS MODALES PERSONALIZADAS
    // ==========================================================
    function showAlezuxAlert(title, message, type = 'info') {
        var $overlay = $('#alezux-alert-modal-overlay');
        var $icon = $('#alezux-alert-icon');
        var $title = $('#alezux-alert-title');
        var $msg = $('#alezux-alert-message');
        var $btnConfirm = $('#alezux-alert-confirm');
        var $btnCancel = $('#alezux-alert-cancel');

        $icon.removeClass('success error warning').addClass(type);
        if (type === 'success') $icon.html('<i class="fas fa-check-circle"></i>');
        if (type === 'error') $icon.html('<i class="fas fa-times-circle"></i>');
        if (type === 'warning') $icon.html('<i class="fas fa-exclamation-triangle"></i>');
        if (type === 'info') $icon.html('<i class="fas fa-info-circle"></i>');

        $title.text(title);
        $msg.html(message);
        $btnCancel.hide();
        $btnConfirm.off('click').on('click', function () {
            $overlay.fadeOut();
        });

        $overlay.fadeIn().css('display', 'flex');
    }

    function showAlezuxConfirm(title, message, onConfirm) {
        var $overlay = $('#alezux-alert-modal-overlay');
        var $icon = $('#alezux-alert-icon');
        var $title = $('#alezux-alert-title');
        var $msg = $('#alezux-alert-message');
        var $btnConfirm = $('#alezux-alert-confirm');
        var $btnCancel = $('#alezux-alert-cancel');

        $icon.removeClass('success error info').addClass('warning');
        $icon.html('<i class="fas fa-question-circle"></i>');

        $title.text(title);
        $msg.html(message);
        $btnCancel.show();

        $btnConfirm.off('click').on('click', function () {
            $overlay.fadeOut();
            if (typeof onConfirm === 'function') onConfirm();
        });

        $btnCancel.off('click').on('click', function () {
            $overlay.fadeOut();
        });

        $overlay.fadeIn().css('display', 'flex');
    }

    // ==========================================================
    // BÚSQUEDA Y PAGINACIÓN (AJAX)
    // ==========================================================
    var searchTimer;
    var currentSearch = '';

    $(document).on('input', '.alezux-table-search-input', function () {
        clearTimeout(searchTimer);
        currentSearch = $(this).val();
        var $clearIcon = $(this).parent().find('.alezux-clear-icon');

        if (currentSearch.length > 0) {
            $clearIcon.fadeIn(200);
        } else {
            $clearIcon.fadeOut(200);
        }

        searchTimer = setTimeout(function () {
            loadStudents(1, currentSearch);
        }, 500);
    });

    $(document).on('click', '.alezux-clear-icon', function () {
        var $input = $(this).parent().find('.alezux-table-search-input');
        $input.val('').trigger('input').focus();
    });

    // Eventos para Filtros
    $(document).on('change', '.alezux-filter-select', function () {
        loadStudents(1, currentSearch);
    });

    $(document).on('change', '.alezux-row-limit-select', function () {
        var newLimit = $(this).val();
        var $wrapper = $(this).closest('.alezux-estudiantes-wrapper');
        $wrapper.data('limit', newLimit);

        loadStudents(1, currentSearch);
    });

    function loadStudents(page, search) {
        var $tableBody = $('.alezux-estudiantes-table tbody');
        $tableBody.css('opacity', '0.5');

        // Obtener valores de filtros
        var courseId = $('#filter-course').val();
        var status = $('#filter-status').val();

        $.ajax({
            url: alezux_estudiantes_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'alezux_search_students',
                nonce: alezux_estudiantes_vars.nonce,
                page: page,
                search: search,
                limit: $('.alezux-estudiantes-wrapper').data('limit') || 10,
                course_id: courseId,
                status: status
            },
            success: function (response) {
                if (response.success) {
                    renderTable(response.data.students);
                    renderPagination(response.data.total_pages, response.data.current_page);
                } else {
                    $tableBody.html('<tr><td colspan="5" style="text-align:center; padding: 40px;">Error al cargar datos.</td></tr>');
                }
            },
            error: function () {
                $tableBody.html('<tr><td colspan="5" style="text-align:center; padding: 40px;">Error de conexión.</td></tr>');
            },
            complete: function () {
                $tableBody.css('opacity', '1');
            }
        });
    }

    function renderTable(students) {
        var $tableBody = $('.alezux-estudiantes-table tbody');
        $tableBody.empty();

        if (students.length === 0) {
            $tableBody.html('<tr><td colspan="6" style="text-align:center; padding: 40px;">No se encontraron estudiantes.</td></tr>');
            return;
        }

        students.forEach(function (student) {
            var row = `
                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="student-checkbox" value="${student.id}">
                    </td>
                    <td>
                        <div class="alezux-student-info">
                            <img src="${student.avatar_url}" alt="${student.name}" class="alezux-student-avatar">
                            <div class="alezux-student-text">
                                <span class="student-name">${student.name}</span>
                                <span class="student-email">@${student.username}</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="student-email">${student.email}</span></td>
                    <td>
                        <div class="alezux-progress-wrapper">
                            <div class="progress-Label">
                                <span>${student.progress}%</span>
                                <span>Completado</span>
                            </div>
                            <div class="alezux-progress-bar-bg">
                                <div class="alezux-progress-bar-fill" style="width: ${student.progress}%;"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="alezux-status-badge ${student.status_class}">
                            <span class="alezux-status-dot"></span>
                            ${student.status_label}
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <button class="alezux-action-btn" data-student-id="${student.id}">
                            <i class="fas fa-cog"></i> Gestionar
                        </button>
                    </td>
                </tr>
            `;
            $tableBody.append(row);
        });
    }

    function renderPagination(totalPages, currentPage) {
        var $container = $('.alezux-estudiantes-pagination');
        $container.empty();
        if (totalPages <= 1) return;

        var html = '';
        var prevPage = Math.max(1, currentPage - 1);
        var nextPage = Math.min(totalPages, currentPage + 1);

        html += `<button class="page-btn prev ${currentPage <= 1 ? 'disabled' : ''}" data-page="${prevPage}" ${currentPage <= 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;

        for (var i = 1; i <= totalPages; i++) {
            if (i == currentPage) {
                html += `<button class="page-btn active">${i}</button>`;
            } else if (i <= currentPage + 2 && i >= currentPage - 2) {
                html += `<button class="page-btn" data-page="${i}">${i}</button>`;
            } else if (i == currentPage + 3 || i == currentPage - 3) {
                html += `<span class="page-dots">...</span>`;
            }
        }

        html += `<button class="page-btn next ${currentPage >= totalPages ? 'disabled' : ''}" data-page="${nextPage}" ${currentPage >= totalPages ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
        $container.html(html);
    }

    // Corregido: Selector correcto para los nuevos botones de paginación
    $(document).on('click', '.page-btn', function (e) {
        e.preventDefault();
        if ($(this).hasClass('disabled') || $(this).hasClass('active')) return;
        var page = $(this).data('page');
        if (page) loadStudents(page, currentSearch);
    });

    // ==========================================================
    // GESTIÓN DE ESTUDIANTES (MODAL)
    // ==========================================================

    function loadStudentInfo(userId, iconUrl) {
        console.log('[Alezux] Cargando info para:', userId);
        $('#alezux-modal-loading').show();
        $('#alezux-modal-content').hide();

        $.ajax({
            url: alezux_estudiantes_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'alezux_get_student_details',
                nonce: alezux_estudiantes_vars.nonce,
                user_id: userId
            },
            success: function (response) {
                console.log('[Alezux] Detalle recibido:', response);
                if (response.success) {
                    var data = response.data;
                    $('#manage-first-name').val(data.first_name);
                    $('#manage-last-name').val(data.last_name);
                    $('#manage-email').val(data.email);
                    updateBlockButton(data.is_blocked);
                    renderCoursesLists(data.enrolled_courses, iconUrl);
                    renderPlansLists(data.enrolled_plans, iconUrl);
                    renderGrantForm(data.available_courses, data.available_plans);

                    $('#alezux-modal-loading').hide();
                    $('#alezux-modal-content').fadeIn();
                } else {
                    showAlezuxAlert('Error', response.data.message, 'error');
                    $('#alezux-management-modal-overlay').fadeOut();
                }
            }
        });
    }

    // Delegación fuerte para el botón gestionar - LIMITADO A LA TABLA DE ESTUDIANTES
    $(document).on('click', '.alezux-estudiantes-table .alezux-action-btn', function (e) {
        e.preventDefault();
        var userId = $(this).data('student-id');

        // Seguridad: Si no hay ID de estudiante, no es un clic destinado a este módulo.
        if (!userId) {
            console.log('[Alezux] Clic en botón sin ID de estudiante. Ignorando evento de Estudiantes.');
            return;
        }

        console.log('[Alezux] Clic en botón Gestionar. UserID:', userId);

        var iconUrl = $(this).closest('.alezux-estudiantes-wrapper').data('time-icon');

        $('#alezux-manage-user-id').val(userId);
        $('#alezux-management-modal-overlay').data('current-icon', iconUrl);
        $('#alezux-management-modal-overlay').fadeIn(200).css('display', 'flex');

        loadStudentInfo(userId, iconUrl);
    });

    $('#alezux-modal-close').on('click', function () {
        $('#alezux-management-modal-overlay').fadeOut();
    });

    $('#btn-save-student-data').on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $spinner = $btn.find('.alezux-spinner');
        $btn.prop('disabled', true);
        $spinner.show();

        $.ajax({
            url: alezux_estudiantes_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'alezux_update_student',
                nonce: alezux_estudiantes_vars.nonce,
                user_id: $('#alezux-manage-user-id').val(),
                first_name: $('#manage-first-name').val(),
                last_name: $('#manage-last-name').val(),
                email: $('#manage-email').val(),
                password: $('#manage-password').val().trim()
            },
            success: function (response) {
                showAlezuxAlert(response.success ? 'Éxito' : 'Error', response.data.message, response.success ? 'success' : 'error');
            },
            complete: function () {
                $btn.prop('disabled', false);
                $spinner.hide();
            }
        });
    });

    $('#btn-reset-password').on('click', function (e) {
        e.preventDefault();
        var userId = $('#alezux-manage-user-id').val();
        showAlezuxConfirm('Restablecer Contraseña', '¿Estás seguro?', function () {
            $.ajax({
                url: alezux_estudiantes_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'alezux_reset_password',
                    nonce: alezux_estudiantes_vars.nonce,
                    user_id: userId
                },
                success: function (response) {
                    showAlezuxAlert('Completado', response.data.message, response.success ? 'success' : 'error');
                }
            });
        });
    });

    $('#btn-block-user').on('click', function (e) {
        e.preventDefault();
        var isBlocked = $(this).data('is-blocked');
        var action = isBlocked ? 'unblock' : 'block';
        showAlezuxConfirm(isBlocked ? 'Desbloquear' : 'Bloquear', '¿Confirmas la acción?', function () {
            $.ajax({
                url: alezux_estudiantes_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'alezux_toggle_block_user',
                    nonce: alezux_estudiantes_vars.nonce,
                    user_id: $('#alezux-manage-user-id').val(),
                    block_action: action
                },
                success: function (response) {
                    if (response.success) {
                        showAlezuxAlert('Actualizado', response.data.message, 'success');
                        updateBlockButton(!isBlocked);
                        // Update table row logic here...
                    }
                }
            });
        });
    });

    $(document).on('click', '.btn-remove-access, .btn-grant-access', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var courseId = $btn.data('course-id');
        var isGranting = $btn.hasClass('btn-grant-access');
        var userId = $('#alezux-manage-user-id').val();

        showAlezuxConfirm(isGranting ? 'Conceder Acceso' : 'Quitar Acceso', '¿Confirmas?', function () {
            $.ajax({
                url: alezux_estudiantes_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'alezux_update_course_access',
                    nonce: alezux_estudiantes_vars.nonce,
                    user_id: userId,
                    course_id: courseId,
                    access_action: isGranting ? 'add' : 'remove'
                },
                success: function (response) {
                    if (response.success) {
                        var iconUrl = $('#alezux-management-modal-overlay').data('current-icon');
                        loadStudentInfo(userId, iconUrl);
                    }
                }
            });
        });
    });

    $(document).on('click', '.btn-remove-plan, .btn-grant-plan', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var planId = $btn.data('plan-id');
        var isGranting = $btn.hasClass('btn-grant-plan');
        var userId = $('#alezux-manage-user-id').val();

        showAlezuxConfirm(isGranting ? 'Conceder Plan' : 'Quitar Plan', '¿Confirmas esta acción?', function () {
            $.ajax({
                url: alezux_estudiantes_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'alezux_update_plan_access',
                    nonce: alezux_estudiantes_vars.nonce,
                    user_id: userId,
                    plan_id: planId,
                    access_action: isGranting ? 'add' : 'remove'
                },
                success: function (response) {
                    if (response.success) {
                        var iconUrl = $('#alezux-management-modal-overlay').data('current-icon');
                        loadStudentInfo(userId, iconUrl);
                    } else {
                        showAlezuxAlert('Error', response.data ? response.data.message : 'Error desconocido', 'error');
                    }
                }
            });
        });
    });

    $(document).on('click', '#btn-grant-course-plan', function (e) {
        e.preventDefault();
        var planId = $('#select-grant-plan').val();
        var userId = $('#alezux-manage-user-id').val();

        if (!planId) {
            showAlezuxAlert('Atención', 'Debes seleccionar un plan válido.', 'warning');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        $.ajax({
            url: alezux_estudiantes_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'alezux_update_plan_access',
                nonce: alezux_estudiantes_vars.nonce,
                user_id: userId,
                plan_id: planId,
                access_action: 'add'
            },
            success: function (response) {
                if (response.success) {
                    var iconUrl = $('#alezux-management-modal-overlay').data('current-icon');
                    loadStudentInfo(userId, iconUrl);
                    showAlezuxAlert('Éxito', 'Acceso concedido correctamente.', 'success');
                } else {
                    showAlezuxAlert('Error', response.data ? response.data.message : 'Error desconocido', 'error');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-plus-circle"></i> Conceder Acceso al Estudiante');
            }
        });
    });

    function updateBlockButton(isBlocked) {
        var $btn = $('#btn-block-user');
        $btn.data('is-blocked', isBlocked);
        if (isBlocked) {
            $btn.html('<i class="fas fa-unlock"></i> Desbloquear Acceso').css('background', '#10b981');
        } else {
            $btn.html('<i class="fas fa-ban"></i> Bloquear Acceso Academia').css('background', '');
        }
    }

    function renderCoursesLists(enrolled, iconUrl) {
        var $enrolledList = $('#list-enrolled-courses');
        $enrolledList.empty();

        if (enrolled.length === 0) { $('#no-enrolled-msg').show(); } else { $('#no-enrolled-msg').hide(); }

        enrolled.forEach(function (c) {
            var iconHtml = (iconUrl && iconUrl !== '') ? '<img src="' + iconUrl + '" alt="Icon">' : '<i class="far fa-clock"></i>';
            var item = `
                <li class="alezux-course-item">
                    <span>${c.title}</span>
                    <div class="alezux-course-actions">
                        <button class="btn-remove-access" data-course-id="${c.id}">Quitar</button>
                    </div>
                </li>`;
            $enrolledList.append(item);
        });
    }

    function renderPlansLists(enrolled, iconUrl) {
        var $enrolledList = $('#list-enrolled-plans');
        $enrolledList.empty();

        if (!enrolled || enrolled.length === 0) { $('#no-enrolled-plans-msg').show(); } else { $('#no-enrolled-plans-msg').hide(); }

        if (enrolled) {
            enrolled.forEach(function (p) {
                var item = `
                    <li class="alezux-course-item">
                        <span>${p.title}</span>
                        <div class="alezux-course-actions">
                            <button class="btn-remove-plan" data-plan-id="${p.id}">Quitar</button>
                        </div>
                    </li>`;
                $enrolledList.append(item);
            });
        }
    }

    function renderGrantForm(availableCourses, availablePlans) {
        var $selectCourse = $('#select-grant-course');
        var $selectPlan = $('#select-grant-plan');
        var $btnGrant = $('#btn-grant-course-plan');

        $selectCourse.empty().append('<option value="">Seleccione un curso...</option>');
        $selectPlan.empty().append('<option value="">Primero seleccione un curso...</option>').prop('disabled', true);
        $btnGrant.prop('disabled', true);

        if (availableCourses) {
            availableCourses.forEach(function (c) {
                $selectCourse.append(`<option value="${c.id}">${c.title}</option>`);
            });
        }

        // Handle course change
        $selectCourse.off('change').on('change', function () {
            var courseId = $(this).val();
            $selectPlan.empty().prop('disabled', true);
            $btnGrant.prop('disabled', true);

            if (!courseId) {
                $selectPlan.append('<option value="">Primero seleccione un curso...</option>');
                return;
            }

            var matchingPlans = [];
            if (availablePlans) {
                matchingPlans = availablePlans.filter(function(p) {
                    return p.course_id == courseId;
                });
            }

            if (matchingPlans.length > 0) {
                $selectPlan.append('<option value="">Seleccione un plan...</option>');
                matchingPlans.forEach(function(p) {
                    $selectPlan.append(`<option value="${p.id}">${p.title}</option>`);
                });
                $selectPlan.prop('disabled', false);
            } else {
                $selectPlan.append('<option value="">No hay planes configurados para este curso.</option>');
            }
        });

        // Handle plan change
        $selectPlan.off('change').on('change', function () {
            if ($(this).val()) {
                $btnGrant.prop('disabled', false);
            } else {
                $btnGrant.prop('disabled', true);
            }
        });
    }

    // ==========================================================
    // SELECCIÓN Y PROCESAMIENTO MASIVO
    // ==========================================================
    function updateBulkActionsBar() {
        var selectedCount = $('.student-checkbox:checked').length;
        $('#bulk-selected-count').text(selectedCount);
        
        if (selectedCount > 0) {
            $('.alezux-bulk-actions-bar').slideDown(200);
        } else {
            $('.alezux-bulk-actions-bar').slideUp(200);
            $('#selectAllStudents').prop('checked', false);
        }
    }

    $(document).on('change', '.student-checkbox', function() {
        var allChecked = $('.student-checkbox').length === $('.student-checkbox:checked').length;
        $('#selectAllStudents').prop('checked', allChecked);
        updateBulkActionsBar();
    });

    $(document).on('change', '#selectAllStudents', function() {
        var isChecked = $(this).prop('checked');
        $('.student-checkbox').prop('checked', isChecked);
        updateBulkActionsBar();
    });

    $('#btn-bulk-process').on('click', function(e) {
        e.preventDefault();
        
        var selectedIds = [];
        $('.student-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) return;

        var planId = $('#bulk-plan-select').val();

        showAlezuxConfirm('Procesar Masivamente', `Vas a enviar credenciales a ${selectedIds.length} estudiantes. Toma aproximadamente ${selectedIds.length * 3} segundos. ¿Continuar?`, function() {
            
            // UI State
            $('.alezux-bulk-left').hide();
            $('#bulk-progress-container').show();
            
            var total = selectedIds.length;
            var processed = 0;
            var errors = [];

            function processNextStudent() {
                if (selectedIds.length === 0) {
                    // Terminado
                    var msg = `Proceso completado. ${processed} enviados.`;
                    if (errors.length > 0) {
                        msg += `<br><br><b>Errores:</b><br><ul style="text-align:left; font-size:12px;"><li>${errors.join('</li><li>')}</li></ul>`;
                        showAlezuxAlert('Completado con errores', msg, 'warning');
                    } else {
                        showAlezuxAlert('¡Éxito!', msg, 'success');
                    }
                    
                    // Reset UI
                    $('.student-checkbox').prop('checked', false);
                    updateBulkActionsBar();
                    $('.alezux-bulk-left').show();
                    $('#bulk-progress-container').hide();
                    return;
                }

                var currentId = selectedIds.shift();
                
                $.ajax({
                    url: alezux_estudiantes_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'alezux_bulk_student_process',
                        nonce: alezux_estudiantes_vars.nonce,
                        user_id: currentId,
                        plan_id: planId
                    },
                    success: function(response) {
                        if (!response.success) {
                            errors.push(`Estudiante #${currentId}: ${response.data.message || 'Error desconocido'}`);
                        }
                    },
                    error: function() {
                        errors.push(`Estudiante #${currentId}: Error de red o servidor.`);
                    },
                    complete: function() {
                        processed++;
                        var percent = Math.round((processed / total) * 100);
                        $('#bulk-progress-percent').text(percent + '%');
                        $('#bulk-progress-fill').css('width', percent + '%');
                        $('#bulk-progress-text').text(`Enviados: ${processed} de ${total}`);

                        // Retraso de 3 segundos
                        setTimeout(processNextStudent, 3000);
                    }
                });
            }

            processNextStudent();
        });
    });

});
