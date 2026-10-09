jQuery(document).ready(function ($) {
    // Alezux Sales Dashboard JS Loaded


    const ajaxUrl = alezux_dashboard_vars.ajax_url;
    const nonce = alezux_dashboard_vars.nonce;
    const is_logged_in = alezux_dashboard_vars.is_logged_in;

    // 1. Global Date Filter Logic
    const $dateInput = $('.alezux-date-global-input');

    if ($dateInput.length > 0) {
        $dateInput.flatpickr({
            mode: "range",
            dateFormat: "Y-m-d",
            maxDate: alezux_dashboard_vars.server_date || "today", // Use Server Date (Primary) or "today" (Fallback)
            disableMobile: true, // Force Flatpickr UI on mobile to respect styles/restrictions
            static: false,
            appendTo: document.body,
            locale: (typeof flatpickr !== 'undefined' && flatpickr.l10ns.es) ? "es" : "default",
            onChange: function (selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    const dateFrom = instance.formatDate(selectedDates[0], "Y-m-d");
                    const dateTo = instance.formatDate(selectedDates[1], "Y-m-d");

                    // Trigger Global Event
                    // Trigger Global Event
                    $(document).trigger('alezux_date_filter_change', { dateFrom: dateFrom, dateTo: dateTo });
                }
            }
        });

        // Clear button logic
        $('.alezux-clear-global-date').on('click', function () {
            const fp = $dateInput[0]._flatpickr;
            fp.clear();
            $(document).trigger('alezux_date_filter_change', { dateFrom: '', dateTo: '' });
        });
    }

    // 2. Stats (KPIs) Logic
    function updateStats(dateFrom, dateTo) {
        // console.log('updateStats called', { dateFrom, dateTo });

        // Find all elements that need updating: Cards AND Raw Spans
        const $elements = $('.alezux-kpi-card, .alezux-dynamic-stat-raw');

        if ($elements.length === 0) {
            console.warn('No stats elements found to update');
            return;
        }

        $elements.each(function () {
            const $el = $(this);
            const statType = $el.data('stat-type');

            // UI State
            let $valueTarget;
            let $loadingTarget;

            if ($el.hasClass('alezux-kpi-card')) {
                $valueTarget = $el.find('.alezux-kpi-number');
                $loadingTarget = $el.find('.alezux-kpi-loading');
            } else {
                // Raw span
                $valueTarget = $el;
                $loadingTarget = null;
                $el.css('opacity', '0.5'); // Visual feedback for raw elements
            }

            if ($loadingTarget) $loadingTarget.show();

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'alezux_get_sales_stats',
                    nonce: nonce,
                    stat_type: statType,
                    date_from: dateFrom,
                    date_to: dateTo
                },
                success: function (response) {
                    // console.log(`Stats Response [${statType}]:`, response);
                    if (response.success) {
                        const data = response.data;
                        let val = 0;
                        if (statType === 'month_revenue') val = data.month_revenue;
                        if (statType === 'today_revenue') val = data.today_revenue;

                        // Format Currency
                        const formatted = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val);

                        if ($valueTarget.is('input')) {
                            $valueTarget.val(formatted);
                        } else {
                            $valueTarget.text(formatted);
                        }
                    } else {
                        if (!$loadingTarget) $el.text('Err');
                        console.error('Alezux Stats Error:', response);
                    }
                },
                error: function (err) {
                    console.error('Alezux Stats AJAX Error:', err);
                },
                complete: function () {
                    if ($loadingTarget) $loadingTarget.hide();
                    if (!$loadingTarget) $el.css('opacity', '1');
                }
            });
        });
    }

    // 3. Chart Logic
    const charts = {}; // Store chart instances by ID

    function updateCharts(dateFrom, dateTo) {
        // console.log('updateCharts called', { dateFrom, dateTo });
        const $charts = $('.alezux-chart-card');

        if ($charts.length === 0) {
            console.warn('No chart elements found');
            return;
        }

        $charts.each(function () {
            const canvas = $(this)[0];
            const chartId = canvas.id;
            const chartType = $(this).data('chart-type') || 'doughnut';

            // Check if inside a container or standalone
            const $container = $(this).closest('.alezux-chart-wrapper, .alezux-chart-container');
            const $loading = $container.find('.alezux-chart-loading');

            // Colors
            const colorStripe = $(this).data('color-stripe') || '#6772e5';
            const colorManual = $(this).data('color-manual') || '#2ecc71';
            const colorPaypal = $(this).data('color-paypal') || '#003087';

            if ($loading.length) $loading.show();

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'alezux_get_sales_stats',
                    nonce: nonce,
                    stat_type: 'chart_data',
                    date_from: dateFrom,
                    date_to: dateTo
                },
                success: function (response) {
                    // console.log('Chart Response:', response);
                    if (response.success) {
                        const data = response.data.chart_data;
                        const chartData = {
                            labels: ['Stripe', 'Manual', 'PayPal'],
                            datasets: [{
                                label: 'Ingresos',
                                data: [data.stripe, data.manual, data.paypal],
                                backgroundColor: [colorStripe, colorManual, colorPaypal],
                                hoverOffset: 4
                            }]
                        };

                        if (charts[chartId]) {
                            charts[chartId].data = chartData;
                            charts[chartId].update();
                        } else {
                            // Init Chart
                            if (typeof Chart !== 'undefined') {
                                charts[chartId] = new Chart(canvas, {
                                    type: chartType,
                                    data: chartData,
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                position: 'bottom',
                                                labels: {
                                                    color: '#888',
                                                    font: { family: 'Inter, sans-serif' }
                                                }
                                            }
                                        }
                                    }
                                });
                            } else {
                                console.error('Chart.js library not loaded');
                            }
                        }
                    }
                },
                complete: function () {
                    if ($loading.length) $loading.hide();
                }
            });
        });
    }

    // Initial Load
    updateStats('', '');
    updateCharts('', '');

    // Listen to Global Event
    $(document).on('alezux_date_filter_change', function (e, data) {
        // console.log('Event alezux_date_filter_change caught', data);
        updateStats(data.dateFrom, data.dateTo);
        updateCharts(data.dateFrom, data.dateTo);
    });

});
