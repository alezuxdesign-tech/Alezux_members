

var AlezuxViewLogrosHandler = function ($scope, $) {
    var container = $scope.find('.alezux-view-logros-wrapper');

    // Check if scope itself is the wrapper (manual init case)
    if (!container.length && $scope.hasClass('alezux-view-logros-wrapper')) {
        container = $scope;
    }

    if (!container.length) return;

    // Removing strict initialization check to allow re-render update in Editor
    // OLD: if (container.data('alezux-initialized')) return; 

    console.log('Alezux Members: Initializing Logros Widget', container);

    // Reset global offset for this instance
    // Note: If multiple widgets exist on same page, global vars are bad. 
    // Ideally we attach these to the container.data, but refactoring that is bigger.
    // For now assuming single widget use-case or accepting last-one-wins for global vars.
    currentOffset = 0;

    // List Container referencing
    var listContainer = container.find('#alezux-logros-list-container');

    // --- EVENTS (Using off/on to prevent duplicates on re-init) ---

    // Search
    container.find('#alezux-logro-search').off('keyup').on('keyup', function () {
        delay(function () {
            currentOffset = 0;
            loadLogros(container, false);
        }, 500);
    });

    // Filter
    container.find('#alezux-logro-course-filter').off('change').on('change', function () {
        currentOffset = 0;
        loadLogros(container, false);
    });

    // Rows per page
    container.find('#alezux-logros-limit-select').off('change').on('change', function () {
        currentOffset = 0;
        loadLogros(container, false);
    });

    // Load More
    container.find('#alezux-load-more-logros').off('click').on('click', function (e) {
        e.preventDefault();
        loadLogros(container, true);
    });

    // Edit Button
    container.off('click', '.alezux-edit-logro').on('click', '.alezux-edit-logro', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation(); // Prevenir conflicto con estudiantes.js
        var id = $(this).data('id');
        openEditModal(id);
    });

    // Delete Button
    container.off('click', '.alezux-delete-logro').on('click', '.alezux-delete-logro', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation(); // Prevenir conflicto con estudiantes.js
        var id = $(this).data('id');
        openDeleteModal(id);
    });

    // Modal Close
    $('body').off('click', '.alezux-modal-close, .alezux-modal-close-btn').on('click', '.alezux-modal-close, .alezux-modal-close-btn', function (e) {
        e.preventDefault();
        $(this).closest('.alezux-modal').fadeOut();
    });

    // Delete Confirm (Bind globally or to unique ID if possible, avoiding closure issues)
    // Using body delegation for modals appended to footer/body
    $('body').off('click', '#alezux-confirm-delete-btn').on('click', '#alezux-confirm-delete-btn', function (e) {
        e.preventDefault();
        var id = $(this).data('id'); // Ensure the button gets the ID injected
        if (id) deleteLogro(id, container);
    });

    // Edit Form Submit
    $('body').off('submit', '#alezux-logro-edit-form').on('submit', '#alezux-logro-edit-form', function (e) {
        e.preventDefault();
        updateLogro(container);
    });

    // Media Uploader
    setupUploadHandler(container);

    // Initial Load calling
    loadLogros(container, false);

    // Mark as init (optional, mostly for debug now)
    container.data('alezux-initialized', true);
};

// Global pagination/state vars (Scoped strictly would be better but keeping structure)
var currentOffset = 0;
var itemsLimit = 20;
var isLoading = false;

// Initialize
jQuery(window).on('elementor/frontend/init', function () {
    elementorFrontend.hooks.addAction('frontend/element_ready/alezux_view_logros.default', AlezuxViewLogrosHandler);
});

// Fallback
jQuery(document).ready(function ($) {
    if (typeof elementorFrontend === 'undefined') {
        $('.alezux-view-logros-wrapper').each(function () {
            AlezuxViewLogrosHandler($(this), $);
        });
    }
});

var delay = (function () {
    var timer = 0;
    return function (callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
    };
})();

function loadLogros(container, append) {
    if (isLoading) return;

    var $tableBody = container.find('#alezux-logros-list-container');
    var loadMoreBtnContainer = container.find('#alezux-logros-pagination-container');
    var search = container.find('#alezux-logro-search').val();
    var course_id = container.find('#alezux-logro-course-filter').val();
    var limit = container.find('#alezux-logros-limit-select').val() || container.data('limit') || 20;

    // Reset offset if not appending
    if (!append) {
        currentOffset = 0;
        $tableBody.css('opacity', '0.5');
        // If it's the first load, the <tbody> might have the loading <tr>
        if (!$tableBody.find('tr').length || $tableBody.find('.alezux-loading').length) {
            $tableBody.html('<tr><td colspan="5" style="text-align:center; padding: 40px;"><div class="alezux-loading">Cargando registros...</div></td></tr>');
        }
    } else {
        container.find('#alezux-load-more-logros').text('Cargando...');
    }

    isLoading = true;

    console.log('Alezux: loadLogros called. Append:', append, 'Offset:', currentOffset, 'Limit:', limit);

    jQuery.ajax({
        url: alezux_logros_vars.ajax_url,
        type: 'POST',
        data: {
            action: 'alezux_get_achievements',
            nonce: alezux_logros_vars.nonce,
            search: search,
            course_id: course_id,
            limit: limit,
            offset: currentOffset,
            image_size: $tableBody.data('image-size') || 'medium'
        },
        success: function (response) {
            console.log('Alezux: AJAX Response:', response);
            isLoading = false;
            $tableBody.css('opacity', '1');
            container.find('#alezux-load-more-logros').text('Cargar más');

            if (response.success) {
                var data = response.data;

                if (!append) {
                    $tableBody.empty();
                }

                if (data.length === 0) {
                    if (!append) {
                        $tableBody.html('<tr><td colspan="5" style="text-align:center; padding: 40px;"><div class="alezux-no-results">No se encontraron logros.</div></td></tr>');
                    } else {
                        loadMoreBtnContainer.hide();
                    }
                } else {
                    renderRows(data, $tableBody);
                    currentOffset += data.length;

                    // Pagination logic
                    if (data.length < limit) {
                        loadMoreBtnContainer.hide();
                    } else {
                        loadMoreBtnContainer.show();
                    }
                }

            } else {
                console.error('Alezux: Server Error:', response.data.message);
                if (!append) $tableBody.html('<tr><td colspan="5" style="text-align:center; color: #dc3545; padding: 20px;">' + response.data.message + '</td></tr>');
            }
        },
        error: function (xhr, status, error) {
            isLoading = false;
            $tableBody.css('opacity', '1');
            container.find('#alezux-load-more-logros').text('Cargar más');
            if (!append) $tableBody.html('<tr><td colspan="5" style="text-align:center; padding: 20px;">Error al cargar los registros.</td></tr>');
        }
    });
}

function renderRows(data, container) {
    var html = '';
    var showImage = container.data('show-image') !== 'no';

    jQuery.each(data, function (index, item) {
        var imgUrl = item.image_url || '';

        html += '<tr>';

        // Column: LOGRO (Image + Message)
        html += '<td>';
        html += '<div class="alezux-student-info">'; // Reusing class for layout
        if (showImage) {
            if (imgUrl) {
                html += '<img src="' + imgUrl + '" class="alezux-student-avatar" style="border-radius: 4px; width: 45px; height: 45px;" alt="Logro">';
            } else {
                html += '<div class="alezux-student-avatar" style="background: #1a202c; border-radius: 4px; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-award" style="color: #718096;"></i></div>';
            }
        }
        html += '<div class="alezux-student-text" style="width: 100%; overflow: hidden;">';
        html += '<span class="student-name" style="font-size: 13px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; white-space: normal; line-height: 1.4; word-break: break-all; overflow-wrap: anywhere;">' + item.message + '</span>';
        html += '</div>';
        html += '</div>';
        html += '</td>';

        // Column: ESTUDIANTE
        html += '<td>';
        html += '<div class="alezux-student-info">';
        if (item.student_avatar) {
            html += '<img src="' + item.student_avatar + '" class="alezux-student-avatar" alt="Avatar">';
        } else {
            html += '<div class="alezux-student-avatar" style="background: #2d3748; display: flex; align-items: center; justify-content: center;"><i class="fas fa-user" style="font-size: 14px; color: #a0aec0;"></i></div>';
        }
        html += '<div class="alezux-student-text">';
        html += '<span class="student-name">' + (item.student_name || 'Estudiante Desconocido') + '</span>';
        html += '<span class="student-id">ID: #' + (item.student_id || '---') + '</span>';
        html += '</div>';
        html += '</div>';
        html += '</td>';

        // Column: CURSO
        html += '<td>';
        html += '<span class="alezux-status-badge status-completed" style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block;">' + (item.course_title || 'Sin Curso') + '</span>';
        html += '</td>';

        // Column: FECHA
        html += '<td>';
        html += '<div class="alezux-date-info">';
        html += '<span class="date-val">' + (item.formatted_date || item.created_at || '') + '</span>';
        html += '</div>';
        html += '</td>';

        // Column: ACCIONES
        html += '<td style="text-align: right;">';
        html += '<div class="alezux-table-actions" style="display: flex; justify-content: flex-end; gap: 8px;">';
        html += '<button class="alezux-logro-action-btn alezux-edit-logro" data-id="' + item.id + '" title="Editar"><span class="dashicons dashicons-edit"></span></button>';
        html += '<button class="alezux-logro-action-btn alezux-delete-logro" style="color: #ff4d4d;" data-id="' + item.id + '" title="Eliminar"><span class="dashicons dashicons-trash"></span></button>';
        html += '</div>';
        html += '</td>';

        html += '</tr>';
    });

    container.append(html);
}

// ---------------------
// MODAL & ACTION LOGIC
// ---------------------

function openDeleteModal(id) {
    var modal = jQuery('#alezux-delete-modal');
    jQuery('#alezux-confirm-delete-btn').data('id', id);
    modal.fadeIn();
}

function deleteLogro(id, container) {
    var modal = jQuery('#alezux-delete-modal');
    var confirmBtn = jQuery('#alezux-confirm-delete-btn');
    var originalText = confirmBtn.text();
    confirmBtn.text('Eliminando...').prop('disabled', true);

    jQuery.ajax({
        url: alezux_logros_vars.ajax_url,
        type: 'POST',
        data: {
            action: 'alezux_delete_achievement',
            nonce: alezux_logros_vars.nonce,
            id: id
        },
        success: function (response) {
            confirmBtn.text(originalText).prop('disabled', false);
            modal.fadeOut();
            if (response.success) {
                // Remove item from DOM directly to avoid full reload flicker?
                // Or reload list. Reloading list is safer for offset consistency if we implemented smart index handling,
                // but simpler for now is to reload list (resetting offset to 0).
                loadLogros(container, false);
            } else {
                alert(response.data.message);
            }
        },
        error: function () {
            confirmBtn.text(originalText).prop('disabled', false);
            alert('Error de conexión');
        }
    });
}

function openEditModal(id) {
    var modal = jQuery('#alezux-logro-edit-modal');

    // Cargar datos
    jQuery.ajax({
        url: alezux_logros_vars.ajax_url,
        type: 'POST',
        data: {
            action: 'alezux_get_achievement',
            nonce: alezux_logros_vars.nonce,
            id: id
        },
        success: function (response) {
            if (response.success) {
                var data = response.data;
                var form = jQuery('#alezux-logro-edit-form');

                form.find('#edit-logro-id').val(data.id);
                form.find('#edit-course-id').val(data.course_id);
                form.find('#edit-student-id').val(data.student_id);
                form.find('#edit-message').val(data.message);

                // Image handling
                var imgId = data.image_id;
                var imgContainer = form.find('.alezux-upload-box');

                // Reset UI first
                updateUploadUI(imgContainer, null);

                if (imgId) {
                    // We have ID, ideally we need URL for preview. 
                    // Does response have URL? If not, we can only set ID.
                    // The preview might be broken if we don't fetch URL.
                    // For now set ID. If data has `image_url` property use it.
                    form.find('#edit-image-id').val(imgId);

                    if (data.image_url) {
                        // Mock attachment object for UI update
                        var mockAttachment = { id: imgId, url: data.image_url };
                        updateUploadUI(imgContainer, mockAttachment);
                    }
                }

                modal.fadeIn();
            } else {
                alert(response.data.message);
            }
        }
    });
}

function updateLogro(container) {
    var form = jQuery('#alezux-logro-edit-form');
    var formData = form.serialize();

    // Add action and nonce
    formData += '&action=alezux_update_achievement&nonce=' + alezux_logros_vars.nonce;

    var btn = form.find('.alezux-logro-submit');
    btn.prop('disabled', true).css('opacity', 0.7).text('Guardando...');

    jQuery.ajax({
        url: alezux_logros_vars.ajax_url,
        type: 'POST',
        data: formData,
        success: function (response) {
            btn.prop('disabled', false).css('opacity', 1).text('Guardar Cambios');
            if (response.success) {
                jQuery('#alezux-logro-edit-modal').fadeOut();
                if (container) loadLogros(container, false); // Reload table from scratch
                else location.reload();
                // alert('Logro actualizado correctamente.'); // Removed alert as per cleaner UX requests usually, modal close is enough or toast? User requested personalized alerts, maybe toast later?
            } else {
                alert(response.data.message);
            }
        },
        error: function () {
            btn.prop('disabled', false).css('opacity', 1).text('Guardar Cambios');
            alert('Error al guardar.');
        }
    });
}


// MEDIA UPLOADER LOGIC (Shared)
var file_frame;

function setupUploadHandler(container) {
    var body = jQuery('body');

    // Prevent multiple bindings
    body.off('click', '.alezux-upload-box').on('click', '.alezux-upload-box', function (event) {
        if (jQuery(event.target).closest('.alezux-remove-img').length) return;

        event.preventDefault();

        // Check for wp.media
        if (typeof wp === 'undefined' || !wp.media) {
            console.error('WP Media not found. Ensure wp_enqueue_media() is called.');
            return;
        }

        var $box = jQuery(this);

        // Reopen frame if exists
        if (file_frame) {
            file_frame.open();
            return;
        }

        // Create frame
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Seleccionar Imagen',
            button: { text: 'Usar imagen' },
            multiple: false
        });

        // On Select
        file_frame.on('select', function () {
            var attachment = file_frame.state().get('selection').first().toJSON();
            updateUploadUI($box, attachment);
        });

        file_frame.open();
    });

    body.off('click', '.alezux-remove-img').on('click', '.alezux-remove-img', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $box = jQuery(this).closest('.alezux-upload-box');
        updateUploadUI($box, null);
    });
}

function updateUploadUI($box, attachment) {
    var $container = $box.closest('.alezux-logro-upload-container');
    var $input = $container.find('.alezux-logro-image-id');
    var $previewBox = $box.find('.alezux-upload-preview');
    var $placeholder = $box.find('.alezux-upload-placeholder');
    var $previewImg = $box.find('.alezux-preview-img');

    if (attachment) {
        $input.val(attachment.id);
        var url = attachment.url;
        if (attachment.sizes && attachment.sizes.medium) {
            url = attachment.sizes.medium.url;
        }

        $previewImg.attr('src', url);
        $placeholder.hide();
        $previewBox.css('display', 'flex').hide().fadeIn();
    } else {
        $input.val('');
        $previewImg.attr('src', '');
        $previewBox.hide();
        $placeholder.fadeIn();
    }
}
