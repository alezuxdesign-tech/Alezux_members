jQuery(window).on('elementor/frontend/init', function () {
    elementorFrontend.hooks.addAction('frontend/element_ready/alezux_plans_manager.default', function ($scope, $) {

        const $wrapper = $scope.find('.alezux-plans-app');
        if ($wrapper.length === 0) return;

        const $tbody = $wrapper.find('.alezux-finanzas-table tbody');
        const $spinner = $wrapper.find('.alezux-loading');
        const $searchInput = $wrapper.find('#alezux-plans-search');
        const $courseFilter = $wrapper.find('#alezux-plans-course');
        const $limitSelect = $wrapper.find('#alezux-limit-select');
        const $pagination = $wrapper.find('.alezux-pagination');

        // Modal elements (GLOBAL or scoped if inside widget)
        // Since the modal is part of the widget HTML, it effectively belongs to the widget instance until moved.
        // We will try to find it within the scope first.
        let $editModal = $scope.find('#alezux-edit-plan-modal');
        let $editForm = $scope.find('#alezux-edit-plan-form');

        // If not found in scope (maybe moved to body by previous run or another instance?), try global ID
        if ($editModal.length === 0) {
            $editModal = $('#alezux-edit-plan-modal');
            $editForm = $('#alezux-edit-plan-form');
        }

        let currentPage = 1;

        function fetchPlans() {
            if (!alezux_finanzas_vars || !alezux_finanzas_vars.is_logged_in) {
                return;
            }

            $tbody.css('opacity', '0.5');

            const data = {
                action: 'alezux_get_plans_list',
                nonce: alezux_finanzas_vars.nonce,
                search: $searchInput.val(),
                course_id: $courseFilter.val(),
                limit: $limitSelect.val(),
                paged: currentPage
            };

            $.post(alezux_finanzas_vars.ajax_url, data, function (response) {
                $spinner.hide();
                $tbody.css('opacity', '1');

                if (response.success) {
                    renderTable(response.data.rows || response.data);
                    renderPagination(response.data.total_pages || 1);
                } else {
                    $tbody.html('<tr><td colspan="7">Error: ' + response.data + '</td></tr>');
                }
            });
        }

        function renderTable(rows) {
            $tbody.empty();

            if (rows.length === 0) {
                $tbody.html('<tr><td colspan="7" style="text-align:center;">No se encontraron planes.</td></tr>');
                return;
            }

            rows.forEach(row => {
                const html = `
                    <tr>
                        <td><strong>${row.name}</strong></td>
                        <td>${row.course}</td>
                        <td>${row.price}</td>
                        <td>${row.quotas}</td>
                        <td>${row.frequency}</td>
                        <td>
                            <button class="page-btn btn-copy-link" data-link="${row.buy_link}" title="Copiar Link de Pago Directo">
                                <i class="eicon-link"></i> Link
                            </button>
                            <button class="page-btn btn-edit-plan" data-id="${row.id}" title="Editar Plan">
                                <i class="eicon-pencil"></i>
                            </button>
                            <button class="page-btn btn-delete-plan" data-id="${row.id}" title="Eliminar Plan" style="color:#d9534f; border-color:#d9534f;">
                                <i class="eicon-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $tbody.append(html);
            });
        }

        // Event Listeners - Scoped
        let timeout = null;
        $searchInput.on('keyup', function () {
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                currentPage = 1;
                fetchPlans();
            }, 500);
        });

        $courseFilter.on('change', function () {
            currentPage = 1;
            fetchPlans();
        });

        $limitSelect.on('change', function () {
            currentPage = 1;
            fetchPlans();
        });

        $pagination.on('click', '.page-btn', function () {
            if ($(this).hasClass('active') || $(this).hasClass('disabled')) return;
            currentPage = $(this).data('page');
            fetchPlans();
        });

        function renderPagination(totalPages) {
            $pagination.empty();
            if (totalPages <= 1) return;

            let html = '';

            // Previous
            if (currentPage > 1) {
                html += `<button class="page-btn" data-page="${currentPage - 1}">«</button>`;
            }

            // Pages
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    const activeClass = i === currentPage ? 'active' : '';
                    html += `<button class="page-btn ${activeClass}" data-page="${i}">${i}</button>`;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    html += `<span class="page-dots">...</span>`;
                }
            }

            // Next
            if (currentPage < totalPages) {
                html += `<button class="page-btn" data-page="${currentPage + 1}">»</button>`;
            }

            $pagination.html(html);
        }

        // Helper for edit rules
        function renderEditRulesTable(modules, currentRules, totalQuotas) {
            let html = '<table class="alezux-rules-table">';
            html += '<thead><tr><th>Módulo / Lección</th><th>Se desbloquea al pagar:</th></tr></thead><tbody>';

            modules.forEach(function (mod) {
                const ruleValue = (currentRules && currentRules[mod.id]) ? currentRules[mod.id] : 1;

                html += '<tr>';
                html += '<td>' + mod.title + '</td>';
                html += '<td>';
                html += `<select name="rules[${mod.id}]" class="alezux-quota-select">`;
                html += '<option value="1">Cuota 1 (Inmediato)</option>';
                for (var i = 2; i <= totalQuotas; i++) {
                    const selected = (i == ruleValue) ? 'selected' : '';
                    html += `<option value="${i}" ${selected}>Cuota ${i}</option>`;
                }
                html += '</select>';
                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            $editModal.find('#edit-plan-rules-container').html(html);
        }

        function fetchCourseModulesForEdit(courseId, currentRules, totalQuotas) {
            $.post(alezux_finanzas_vars.ajax_url, {
                action: 'alezux_get_course_modules',
                nonce: alezux_finanzas_vars.nonce,
                course_id: courseId
            }, function (response) {
                if (response.success) {
                    renderEditRulesTable(response.data, currentRules, totalQuotas);
                } else {
                    $editModal.find('#edit-plan-rules-container').html('<p style="color:red">Error cargando módulos.</p>');
                }
            });
        }

        function openEditModal(planId) {
            // Move to body to avoid z-index/overflow issues
            if ($editModal.parent()[0].tagName !== 'BODY') {
                $editModal.appendTo('body');
            }

            $editModal.removeClass('alezux-hidden').css({
                'display': 'flex',
                'opacity': '1',
                'visibility': 'visible'
            }).hide().fadeIn();

            $editForm[0].reset();
            $editModal.find('#edit-plan-rules-container').html('<div class="alezux-spinner">Cargando datos...</div>');
            $editModal.find('#edit-plan-id').val(planId);

            $.post(alezux_finanzas_vars.ajax_url, {
                action: 'alezux_get_plan_details',
                nonce: alezux_finanzas_vars.nonce,
                plan_id: planId
            }, function (response) {
                if (response.success) {
                    const plan = response.data;
                    $editModal.find('#edit-plan-name').val(plan.name);
                    $editModal.find('#edit-plan-course').val(plan.course_name);
                    $editModal.find('#edit-plan-whatsapp').val(plan.whatsapp_number || '');
                    $editModal.find('#edit-plan-price').val(plan.quota_amount + ' USD');
                    $editModal.find('#edit-plan-quotas').val(plan.total_quotas);

                    fetchCourseModulesForEdit(plan.course_id, plan.access_rules, plan.total_quotas);
                } else {
                    alert('Error recuperando el plan: ' + response.data);
                    $editModal.fadeOut();
                }
            });
        }

        // Actions
        $tbody.on('click', '.btn-delete-plan', function () {
            const id = $(this).data('id');
            if (confirm('¿Estás seguro de eliminar este plan? Esto no eliminará las suscripciones activas en Stripe, pero lo quitará de la base de datos local.')) {
                $.post(alezux_finanzas_vars.ajax_url, {
                    action: 'alezux_delete_plan',
                    nonce: alezux_finanzas_vars.nonce,
                    id: id
                }, function (response) {
                    if (response.success) {
                        fetchPlans();
                    } else {
                        alert('Error al eliminar: ' + response.data);
                    }
                });
            }
        });

        $tbody.on('click', '.btn-copy-link', function () {
            const link = $(this).data('link');
            navigator.clipboard.writeText(link).then(function () {
                alert('Enlace copiado al portapapeles:\n' + link);
            }, function (err) {
                alert('No se pudo copiar el enlace. Cópielo manualmente:\n' + link);
            });
        });

        // Edit Button (Scoped to table)
        $tbody.on('click', '.btn-edit-plan', function (e) {
            e.preventDefault();
            const planId = $(this).data('id');
            openEditModal(planId);
        });

        // Save Changes (Scoped to form)
        // If form moves to body, we need to ensure this event listener is still valid or we delegate to document.
        // Since we query $editForm at start, and it's a jQuery object, events bound to it should persist even if moved in DOM?
        // Actually, removing and appending might lose events if not using delegation on a parent.
        // Safer: delegate to document or re-bind.
        // Let's use document delegation for the form submit since it might be in body.

        $(document).on('submit', '#alezux-edit-plan-form', function (e) {
            // We need to ensure we are submitting the form associated with THIS widget instance?
            // Since ID is unique (conceptually), it doesn't matter much.
            // But if multiple widgets, ID collision is an issue.
            // For now, assuming single instance logic for modal.

            // Check if this form is the one we care about?
            // If we use ID selector, it matches the first one.
            // If the modal was moved to body, it's there.

            // Logic:
            e.preventDefault();
            const $form = $(this);
            const btn = $form.find('.alezux-btn-save');
            const originalText = btn.text();
            btn.text('Guardando...').prop('disabled', true);

            const formData = $form.serialize();

            $.post(alezux_finanzas_vars.ajax_url,
                formData + '&action=alezux_update_plan&nonce=' + alezux_finanzas_vars.nonce,
                function (response) {
                    btn.text(originalText).prop('disabled', false);

                    if (response.success) {
                        alert('Plan actualizado correctamente.');

                        // Close modal
                        $form.closest('.alezux-modal-overlay').fadeOut();

                        // Refresh THIS widget's table
                        // But wait, this event is global! How do we know WHICH widget triggered it?
                        // We don't easily know which widget opened the modal if we use global delegation.
                        // However, since we are inside the hook, we can just call fetchPlans() which is scoped!
                        // BUT, does this event fire for ALL instances? Yes.
                        // So if I save on one instance, all instances on page will refresh. This is acceptable/desirable.
                        fetchPlans();
                    } else {
                        alert('Error: ' + response.data);
                    }
                }
            );
        });

        // Close Modal
        $(document).on('click', '.alezux-close-modal', function () {
            $('.alezux-modal-overlay').fadeOut();
        });

        // Initial Fetch
        fetchPlans();

    });
});
