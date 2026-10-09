jQuery(document).ready(function ($) {
    var ajax_url = alezux_estudiantes_vars.ajax_url;
    var nonce = alezux_estudiantes_vars.nonce;

    /**
     * ================================
     * Lógica de Registro Masivo (CSV)
     * ================================
     */
    var $dropzone = $('#alezux-csv-dropzone');
    var $fileInput = $('#alezux-csv-file-input');
    var $progressContainer = $('#alezux-csv-progress-container');
    var $progressBarFill = $progressContainer.find('.alezux-progress-fill');
    var $statusText = $progressContainer.find('.status-text');
    var $statusCount = $progressContainer.find('.status-count');
    var $report = $('#alezux-csv-report');

    if ($dropzone.length === 0) return; // Exit if widget not present

    // Drag & Drop UI
    $dropzone.on('dragover', function (e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });
    $dropzone.on('dragleave drop', function (e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });
    $dropzone.on('click', function () {
        $fileInput.click();
    });

    // File Selection
    $dropzone.on('drop', function (e) {
        var files = e.originalEvent.dataTransfer.files;
        if (files.length) handleCSVFile(files[0]);
    });
    $fileInput.on('change', function (e) {
        if (this.files.length) handleCSVFile(this.files[0]);
    });

    function handleCSVFile(file) {
        if (file.type !== "text/csv" && file.type !== "application/vnd.ms-excel" && !file.name.match(/\.csv$/i)) {
            alert("Por favor sube un archivo CSV válido.");
            return;
        }

        var reader = new FileReader();
        reader.onload = function (e) {
            var text = e.target.result;
            var students = parseCSV(text);

            if (students.length === 0) {
                alert("El archivo CSV parece estar vacío o mal formateado.");
                return;
            }

            if (confirm("Se encontraron " + students.length + " estudiantes. ¿Iniciar importación?")) {
                startBatchProcess(students);
            }
        };
        reader.readAsText(file);
    }

    // Simple CSV Parser (Nombre, Apellido, Email)
    function parseCSV(text) {
        var lines = text.split(/\r\n|\n/);
        var students = [];

        // Detectar si hay header (si la primera linea tiene 'email' o 'correo')
        var startIndex = 0;
        if (lines[0] && (lines[0].toLowerCase().includes('email') || lines[0].toLowerCase().includes('correo'))) {
            startIndex = 1;
        }

        for (var i = startIndex; i < lines.length; i++) {
            var line = lines[i].trim();
            if (!line) continue;

            // Separar por coma o punto y coma
            var parts = line.split(/,|;/);
            if (parts.length >= 3) {
                // Asumimos formato: Nombre, Apellido, Email
                var first = parts[0].trim().replace(/^"|"$/g, '');
                var last = parts[1].trim().replace(/^"|"$/g, '');
                var email = parts[2].trim().replace(/^"|"$/g, '');

                if (email && email.includes('@')) {
                    students.push({
                        first_name: first,
                        last_name: last,
                        email: email
                    });
                }
            }
        }
        return students;
    }

    // Batch Queue System
    function startBatchProcess(students) {
        var courseId = $('#alezux-csv-course-select').val();
        var planId = $('#alezux-csv-plan-select').val();
        var batchSize = 1; // 1 correo por lote para evitar bloqueos
        var total = students.length;
        var processed = 0;
        var successCount = 0;
        var errors = [];

        $progressContainer.show();
        $report.html('');
        $progressBarFill.css('width', '0%');
        updateStatus("Iniciando cola...", 0, total);

        // Recursive Batch Function
        function processNextBatch() {
            if (processed >= total) {
                finishProcess(successCount, total, errors);
                return;
            }

            var batch = students.slice(processed, processed + batchSize);

            updateStatus("Enviando lote...", processed, total);

            $.ajax({
                url: ajax_url,
                type: 'POST',
                data: {
                    action: 'alezux_register_batch_csv',
                    nonce: nonce,
                    students: batch,
                    course_id: courseId,
                    plan_id: planId
                },
                success: function (response) {
                    if (response.success) {
                        processed += parseInt(response.data.processed);
                        successCount += parseInt(response.data.success);
                        if (response.data.errors && response.data.errors.length) {
                            errors = errors.concat(response.data.errors);
                        }
                    } else {
                        // Si falla todo el lote, contamos como procesados pero con error
                        processed += batch.length;
                        errors.push("Error de lote: " + (response.data.message || "Desconocido"));
                    }

                    var percent = Math.round((processed / total) * 100);
                    $progressBarFill.css('width', percent + '%');
                    updateStatus("Procesando...", processed, total);

                    // Esperar 3000ms antes del siguiente lote para no saturar SMTP
                    setTimeout(processNextBatch, 3000);
                },
                error: function () {
                    processed += batch.length;
                    errors.push("Error de conexión en lote.");
                    setTimeout(processNextBatch, 3000);
                }
            });
        }

        processNextBatch();
    }

    function updateStatus(text, current, total) {
        $statusText.text(text);
        $statusCount.text(current + '/' + total);
    }

    function finishProcess(success, total, errors) {
        $progressBarFill.css('width', '100%');
        $progressBarFill.css('background-color', '#4CAF50'); // Green
        updateStatus("¡Proceso completado!", total, total);

        var reportHtml = "<div><strong>Resumen:</strong> " + success + " registrados exitosamente.</div>";
        if (errors.length > 0) {
            reportHtml += "<div style='color:#ff6b6b; margin-top:5px;'><strong>Errores (" + errors.length + "):</strong><br>";
            $.each(errors, function (i, err) {
                reportHtml += "- " + err + "<br>";
            });
            reportHtml += "</div>";
        }
        $report.html(reportHtml);

        alert("Proceso finalizado.\nEnviados: " + success + "\nErrores: " + errors.length);
        $fileInput.val(''); // Reset input
    }
});
