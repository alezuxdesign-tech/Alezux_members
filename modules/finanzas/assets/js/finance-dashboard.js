jQuery(document).ready(function ($) {

    // Globals
    var $kpiPeriod = $('#kpi-revenue-period');
    var $kpiProjected = $('#kpi-projected-period');
    // var $kpiStatic = $('.ax-fin-kpi-static'); // Estos no cambian con filtro rango

    var $dateInput = $('.alezux-date-filter-input');

    // Init Flatpickr
    if ($dateInput.length > 0) {

        var startDef = $dateInput.data('start');
        var endDef = $dateInput.data('end');
        var defaultDates = [];

        if (startDef && endDef) {
            defaultDates = [startDef, endDef];
        }

        var fp = flatpickr($dateInput[0], {
            mode: "range",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "F j, Y",
            defaultDate: defaultDates,
            locale: "es", // Asegurar que flatpickr-l10n-es está cargado
            onChange: function (selectedDates, dateStr, instance) {
                // Solo activar si hay 2 fechas (Rango completo) o si es modo 'single' pero aqui es 'range'
                if (selectedDates.length === 2) {
                    var start = instance.formatDate(selectedDates[0], "Y-m-d");
                    var end = instance.formatDate(selectedDates[1], "Y-m-d");

                    updateKPIs(start, end);
                }
            }
        });
    }

    function updateKPIs(start, end) {
        // Poner loading state
        $('.ax-fin-kpi').css('opacity', '0.5');

        $.ajax({
            url: alezux_finanzas_vars.ajax_url, // Debe estar localizable
            type: 'POST',
            data: {
                action: 'alezux_get_finance_kpis',
                nonce: alezux_finanzas_vars.nonce,
                start_date: start,
                end_date: end
            },
            success: function (response) {
                $('.ax-fin-kpi').css('opacity', '1');

                if (response.success) {
                    var data = response.data;

                    // Actualizar KPIs por ID o data-attribute
                    // Nota: Los IDs shortcode son unicos
                    if (data.revenue_period !== undefined) {
                        $('#kpi-revenue-period').text(formatMoney(data.revenue_period));
                    }
                    if (data.projected_period !== undefined) {
                        $('#kpi-projected-period').text(formatMoney(data.projected_period));
                    }

                    // Pending Total (Global) no debería cambiar por fecha, pero si la logica cambiara, aqui iria.

                } else {
                    console.error('Error KPI:', response.data);
                }
            },
            error: function (err) {
                console.error('Error Ajax KPI', err);
                $('.ax-fin-kpi').css('opacity', '1');
            }
        });
    }

    function formatMoney(amount) {
        return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

});
