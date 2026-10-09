jQuery(document).ready(function ($) {
    const $form = $('#alezux-profile-update-form');
    // const $avatarInput = $('#alezux-avatar-input'); // Ya no vinculamos el input directamente al preview
    const $avatarInputRaw = document.getElementById('alezux-avatar-input');
    const $avatarPreview = $('#alezux-avatar-preview');
    const $submitBtn = $form.find('.alezux-submit-btn');

    const $btnUpload = $('#alezux-avatar-trigger');

    // Elementos del Modal de Recorte
    const $cropModal = $('#alezux-crop-modal');
    const $cropImage = document.getElementById('alezux-crop-image');
    const $btnCropConfirm = $('#alezux-crop-confirm');
    const $btnCropCancel = $('#alezux-crop-cancel');

    let cropper;
    let croppedBlob = null; // Aquí guardaremos la imagen recortada

    // 1. Abrir selector de archivos
    $btnUpload.on('click', function (e) {
        $avatarInputRaw.click();
    });

    // 2. Detectar cambio de archivo y abrir Modal Cropper
    $($avatarInputRaw).on('change', function (e) {
        const files = e.target.files;

        if (files && files.length > 0) {
            const file = files[0];
            const reader = new FileReader();

            reader.onload = function (e) {
                // Asignar imagen al elemento del modal
                $cropImage.src = e.target.result;

                // Mostrar Modal
                $cropModal.fadeIn(200, function () {
                    // Inicializar Cropper una vez visible
                    if (cropper) {
                        cropper.destroy();
                    }
                    cropper = new Cropper($cropImage, {
                        aspectRatio: 1, // Cuadrado para avatar
                        viewMode: 1,
                        background: false
                    });
                });
            };
            reader.readAsDataURL(file);
        }
        // Reset valor para permitir seleccionar la misma imagen de nuevo si cancela
        // $(this).val(''); // No resetear aquí, sino al cancelar o confirmar
    });

    // 3. Cancelar Recorte
    $btnCropCancel.on('click', function () {
        $cropModal.fadeOut();
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        $($avatarInputRaw).val(''); // Limpiar input
    });

    // 4. Confirmar Recorte
    $btnCropConfirm.on('click', function () {
        if (!cropper) return;

        // Obtener canvas recortado
        const canvas = cropper.getCroppedCanvas({
            width: 300,  // Tamaño razonable para avatar
            height: 300
        });

        // Convertir a Blob
        canvas.toBlob(function (blob) {
            croppedBlob = blob; // Guardar para el envío

            // Actualizar vista previa en el formulario
            $avatarPreview.attr('src', canvas.toDataURL());

            // Cerrar modal
            $cropModal.fadeOut();
            cropper.destroy();
            cropper = null;
        }, 'image/jpeg');
    });

    // 5. Envío del Formulario
    $form.on('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        // Si tenemos un blob recortado, lo agregamos al FormData
        // Reemplazando el archivo original 'alezux_avatar'
        if (croppedBlob) {
            formData.set('alezux_avatar', croppedBlob, 'avatar-bloque.jpg');
        }

        $submitBtn.find('.btn-text').hide();
        $submitBtn.find('.btn-loader').css('display', 'inline-flex'); // Flex para alinear spinner y texto
        $submitBtn.prop('disabled', true);

        $.ajax({
            url: alezux_auth_obj.ajax_url, // Usamos el objeto global ya definido en config.js
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    window.alezuxShowModal('¡Éxito!', response.data.message, 'success', 'reload');
                } else {
                    window.alezuxShowModal('Error', response.data.message, 'error');
                }
            },
            error: function () {
                window.alezuxShowModal('Error', 'Hubo un problema en el servidor.', 'error');
            },
            complete: function () {
                $submitBtn.find('.btn-text').show();
                $submitBtn.find('.btn-loader').hide();
                $submitBtn.prop('disabled', false);
            }
        });
    });
});
