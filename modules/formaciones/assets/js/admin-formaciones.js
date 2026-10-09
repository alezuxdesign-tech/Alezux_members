jQuery(document).ready(function ($) {
    // --- Lógica para subir Portada del Curso ---

    $('body').on('click', '.alezux-upload-course-cover', function (e) {
        e.preventDefault();

        var button = $(this);
        var wrapper = button.closest('.course-cover-wrapper');
        var input_url = wrapper.find('#alezux_course_cover');
        var preview = wrapper.find('.course-cover-preview');
        var remove_btn = wrapper.find('.alezux-remove-course-cover');

        var custom_uploader = wp.media({
            title: 'Seleccionar Portada del Curso',
            button: {
                text: 'Usar esta imagen'
            },
            multiple: false
        }).on('select', function () {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            input_url.val(attachment.url);
            preview.html('<img src="' + attachment.url + '" style="max-width: 300px; height: auto; border: 1px solid #ddd; padding: 4px; border-radius: 4px;" />');
            remove_btn.show();
        }).open();
    });

    $('body').on('click', '.alezux-remove-course-cover', function (e) {
        e.preventDefault();
        var wrapper = $(this).closest('.course-cover-wrapper');
        wrapper.find('#alezux_course_cover').val('');
        wrapper.find('.course-cover-preview').html('');
        $(this).hide();
    });

    // --- Lógica para subir imagen de Mentores ---

    // Delegación de eventos para el botón de subir imagen
    $('body').on('click', '.alezux-upload-mentor-image', function (e) {
        e.preventDefault();

        var button = $(this);
        var container = button.closest('.mentor-item');
        var input_url = container.find('.mentor-image-url');
        var preview = container.find('.mentor-image-preview');

        var custom_uploader = wp.media({
            title: 'Seleccionar Imagen del Mentor',
            button: {
                text: 'Usar esta imagen'
            },
            multiple: false
        }).on('select', function () {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            input_url.val(attachment.url);
            preview.html('<img src="' + attachment.url + '" style="max-width: 80px; height: auto; border-radius: 50%;" />');
        }).open();
    });

    // Delegación para eliminar imagen
    $('body').on('click', '.alezux-remove-mentor-image', function (e) {
        e.preventDefault();
        var container = $(this).closest('.mentor-item');
        container.find('.mentor-image-url').val('');
        container.find('.mentor-image-preview').html('');
    });

    // --- Lógica para Campos Repetibles (Añadir/Eliminar Mentor) ---

    $('#alezux-add-mentor').on('click', function (e) {
        e.preventDefault();

        var template = $('.mentor-item-template').html();
        // Generar un ID único temporal (o usar timestamp) para los inputs si fuera necesario, 
        // pero como es array name="...[]" no es estrictamente necesario para el PHP.

        $('#mentors-container').append('<div class="mentor-item" style="border:1px solid #ddd; padding:10px; margin-bottom:10px; background:#f9f9f9;">' + template + '</div>');
    });

    $('body').on('click', '.alezux-remove-mentor', function (e) {
        e.preventDefault();
        if (confirm('¿Estás seguro de eliminar este mentor?')) {
            $(this).closest('.mentor-item').remove();
        }
    });
});
