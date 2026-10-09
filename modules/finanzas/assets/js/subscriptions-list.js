jQuery(window).on('elementor/frontend/init', function () {
    elementorFrontend.hooks.addAction('frontend/element_ready/alezux_subs_list.default', function ($scope, $) {

        const $wrapper = $scope.find('.alezux-subs-app');
        if ($wrapper.length === 0) return;

        const $tbody = $wrapper.find('.alezux-finanzas-table tbody');
        const $spinner = $wrapper.find('.alezux-loading');
        const $searchInput = $wrapper.find('#alezux-subs-search');
        const $limitSelect = $wrapper.find('#alezux-limit-select');
        const $pagination = $wrapper.find('.alezux-pagination');

        // Filter & Pagination State (Scoped to instance)
        let currentState = {
            paged: 1,
            limit: 20,
            search: ''
        };

        function fetchSubscriptions() {
            if (!alezux_finanzas_vars || !alezux_finanzas_vars.is_logged_in) {
                return;
            }

            $tbody.css('opacity', '0.5');

            currentState.search = $searchInput.val();
            currentState.limit = $limitSelect.val();

            const data = {
                action: 'alezux_get_subscriptions_list',
                nonce: alezux_finanzas_vars.nonce,
                search: currentState.search,
                paged: currentState.paged,
                limit: currentState.limit
            };

            $.post(alezux_finanzas_vars.ajax_url, data, function (response) {
                if (response.success) {
                    renderTable(response.data.rows);
                    renderPagination(response.data);
                } else {
                    $tbody.html('<tr><td colspan="7" style="text-align:center; padding: 20px; color: #ff6b6b;">Error: ' + response.data + '</td></tr>');
                }
            })
                .fail(function () {
                    $tbody.html('<tr><td colspan="7" style="text-align:center; padding: 20px; color: #ff6b6b;">Error de conexión con el servidor.</td></tr>');
                })
                .always(function () {
                    $spinner.hide();
                    $tbody.css('opacity', '1');
                });
        }

        function renderTable(rows) {
            $tbody.empty();

            if (!rows || rows.length === 0) {
                $tbody.html('<tr><td colspan="8" style="text-align:center;">No se encontraron suscripciones.</td></tr>');
                $pagination.empty();
                return;
            }

            rows.forEach(row => {
                let statusClass = 'status-pending';
                if (row.status === 'active') statusClass = 'status-active';
                if (row.status === 'completed') statusClass = 'status-completed';
                if (row.status === 'canceled') statusClass = 'status-canceled';
                if (row.status === 'past_due') statusClass = 'status-past_due';

                // Determinar si mostrar botón de acción
                let actionHtml = '';
                if (row.status === 'completed') {
                    actionHtml = '<span class="alezux-no-action">SIN ACCIONES</span>';
                } else {
                    actionHtml = `
                    <button class="alezux-btn-manual-pay" data-id="${row.id}" data-amount="${row.raw_amount}" title="Pago Manual">
                        <span class="dashicons dashicons-money-alt"></span> Pago manual
                    </button>`;
                }

                // Icono de estado
                const statusDot = `<span class="alezux-status-dot"></span>`;

                const html = `
                    <tr>
                        <td class="col-student">
                            <div class="alezux-student-info">
                                <img src="${row.student_avatar}" class="alezux-student-avatar" alt="Avatar">
                                <div class="alezux-student-text">
                                    <span class="student-name">${row.student}</span>
                                    <span class="student-email">${row.student_email}</span>
                                </div>
                            </div>
                        </td>
                        <td class="col-plan">
                            <div class="alezux-plan-info">
                                <span class="plan-name">${row.plan}</span>
                                <span class="plan-meta">${row.total_quotas} CUOTAS</span>
                            </div>
                        </td>
                        <td class="col-amount"><strong>${row.amount}</strong></td>
                        <td class="col-status">
                            <span class="alezux-status-badge ${statusClass}">
                                ${statusDot} ${row.status.toUpperCase()}
                            </span>
                        </td>
                        <td class="col-progress">
                            <div class="alezux-progress-wrapper">
                                <div class="progress-Label">
                                    <span>${row.quotas_paid}/${row.total_quotas} PAGADOS</span>
                                    <span>${row.percent}%</span>
                                </div>
                                <div class="alezux-progress-bar-bg">
                                    <div class="alezux-progress-bar-fill" style="width: ${row.percent}%;"></div>
                                </div>
                            </div>
                        </td>
                        <td class="col-next-payment">
                           <div class="alezux-date-info">
                               <span class="date-val">${row.next_payment_raw ? formatDate(row.next_payment_raw) : '-'}</span>
                               ${row.status === 'completed' ? '<span class="date-meta success-icon"><span class="dashicons dashicons-yes"></span> FINALIZADO</span>' : `<span class="date-meta"><span class="dashicons dashicons-calendar-alt"></span> CUOTA #${Number(row.quotas_paid) + 1}</span>`}
                           </div>
                        </td>
                        <td class="col-actions">
                            ${actionHtml}
                        </td>
                    </tr>
                `;
                $tbody.append(html);
            });
        }

        function renderPagination(data) {
            $pagination.empty();

            const totalPages = data.total_pages;
            const current = data.current_page;

            if (totalPages <= 1) return;

            let html = '';

            // Previous
            if (current > 1) {
                html += `<button class="page-btn" data-page="${current - 1}">&laquo;</button>`;
            }

            // Pages
            for (let i = 1; i <= totalPages; i++) {
                // Show first, last, current, and surrounding pages
                if (i === 1 || i === totalPages || (i >= current - 2 && i <= current + 2)) {
                    const activeClass = i === current ? 'active' : '';
                    html += `<button class="page-btn ${activeClass}" data-page="${i}">${i}</button>`;
                } else if (i === current - 3 || i === current + 3) {
                    html += `<span class="page-dots">...</span>`;
                }
            }

            // Next
            if (current < totalPages) {
                html += `<button class="page-btn" data-page="${current + 1}">&raquo;</button>`;
            }

            $pagination.html(html);
        }

        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' });
        }

        // Event Listeners - Scoped
        $pagination.on('click', '.page-btn', function () {
            if ($(this).hasClass('active') || $(this).attr('disabled')) return;
            const page = $(this).data('page');
            currentState.paged = page;
            fetchSubscriptions();
        });

        // Rows Limit Change
        $limitSelect.on('change', function () {
            currentState.paged = 1; // Reset to page 1
            fetchSubscriptions();
        });

        // Search Debounce
        let timeout = null;
        $searchInput.on('keyup', function () {
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                currentState.paged = 1;
                fetchSubscriptions();
            }, 500);
        });

        // Modal Logic (Scoped triggering)
        // Find by class within this widget instance (since ID is now dynamic)
        let $modal = $scope.find('.alezux-modal');

        // Safety check if modal is not found (shouldn't happen on fresh render)
        if ($modal.length === 0) {
            // Try to find by ID if class fails (fallback in case of weird scoping)
            const widgetId = $scope.data('id');
            if (widgetId) {
                $modal = $('#alezux-manual-pay-modal-' + widgetId);
            }
        }

        let currentSubId = 0;

        // Abrir Modal
        $tbody.on('click', '.alezux-btn-manual-pay', function () {
            currentSubId = $(this).data('id');
            const amount = $(this).data('amount');

            if ($modal.length === 0) {
                console.error("Manual Pay Modal Not Found");
                return;
            }

            // --- FIX START ---
            console.log('Opening Modal for Widget ID:', $scope.data('id'));

            // Check if already moved/wrapped and HAS the correct structure
            const $parent = $modal.parent();
            // We want the parent to be .alezux-finanzas-app (to inherit table styles)
            // And THAT parent to be .elementor-widget-container (to inherit Elementor text styles)
            // And THAT parent to be our wrapper (to provide the Elementor ID scope)

            const isCorrectlyWrapped = $parent.hasClass('alezux-finanzas-app') &&
                $parent.parent().hasClass('elementor-widget-container') &&
                $parent.parent().parent().hasClass('alezux-modal-body-wrapper');

            if (!isCorrectlyWrapped) {
                const widgetId = $scope.data('id');
                if (!widgetId) console.warn('Widget ID missing!');

                console.log('Wrapping modal with ID:', widgetId, 'and adding style containers');

                // 1. Top Level Wrapper (Elementor ID Scope)
                const $topWrapper = $('<div>', {
                    class: 'alezux-modal-body-wrapper elementor elementor-element elementor-element-' + widgetId + ' elementor-widget-alezux_subs_list'
                });

                // 2. Elementor Widget Container (Standard Elementor Structure)
                const $widgetContainer = $('<div>', {
                    class: 'elementor-widget-container'
                });

                // 3. App Container (Theme/Module Styles)
                const $appContainer = $('<div>', {
                    class: 'alezux-finanzas-app'
                });

                // Nest them
                $appContainer.append($modal);
                $widgetContainer.append($appContainer);
                $topWrapper.append($widgetContainer);

                // Append to Body
                $topWrapper.appendTo('body');

                // Update reference visibility
                $modal.css('display', 'flex');
            } else {
                console.log('Modal already correctly wrapped');
                $modal.css('display', 'flex');
            }
            // --- FIX END ---

            $modal.find('#modal-sub-id').text(currentSubId);
            $modal.find('#manual-pay-amount').val(amount).prop('readonly', true).css('background-color', '#eee');
            $modal.find('#manual-pay-note').val('');
            $modal.css('display', 'flex'); // Force flex for centering

            // Bind the confirm button's click event, ensuring it's unique per modal open
            const $btnConfirm = $modal.find('#btn-confirm-manual-pay');
            $btnConfirm.off('click'); // Remove previous handlers
            $btnConfirm.on('click', function () {
                handleManualPayment($(this));
            });
        });

        // Cerrar Modal (Scoped to this modal instance)
        $modal.on('click', '.alezux-close-modal', function () {
            $modal.hide();
        });

        // Close on overlay click
        $modal.on('click', function (e) {
            if ($(e.target).is($modal)) {
                $modal.hide();
            }
        });

        function handleManualPayment($btn) {
            const $modalContainer = $('#alezux-manual-pay-modal');
            const amount = $modalContainer.find('#manual-pay-amount').val();
            const note = $modalContainer.find('#manual-pay-note').val();

            if (!amount || !note) {
                alert('Por favor ingrese monto y motivo.');
                return;
            }

            $btn.text('Procesando...').prop('disabled', true);

            $.post(alezux_finanzas_vars.ajax_url, {
                action: 'alezux_manual_subs_payment',
                nonce: alezux_finanzas_vars.nonce,
                subscription_id: currentSubId,
                amount: amount,
                note: note
            }, function (response) {
                $btn.text('Registrar Pago').prop('disabled', false);
                if (response.success) {
                    alert('Pago registrado correctamente.');
                    $modalContainer.hide();
                    fetchSubscriptions(); // Refresh THIS instance
                } else {
                    alert('Error: ' + response.data);
                }
            });
        }

        // Init
        fetchSubscriptions();
    });
});
