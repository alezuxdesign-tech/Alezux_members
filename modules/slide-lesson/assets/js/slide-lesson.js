document.addEventListener('DOMContentLoaded', function () {
    initSlideLesson();
});

// También inicializar cuando Elementor renderiza el widget en el editor
window.addEventListener('elementor/frontend/init', () => {
    elementorFrontend.hooks.addAction('frontend/element_ready/alezux-slide-lesson.default', initSlideLesson);
});

function initSlideLesson($scope) {
    let container = document;
    if ($scope) {
        container = $scope[0];
    }

    const sliders = container.querySelectorAll('.alezux-slide-lesson-container');

    sliders.forEach(slider => {
        const wrapper = slider.querySelector('.alezux-slide-wrapper');
        const prevBtn = slider.querySelector('.alezux-slide-nav-prev');
        const nextBtn = slider.querySelector('.alezux-slide-nav-next');

        // Obtener el ancho de desplazamiento basado en el primer item + gap
        const firstItem = wrapper.querySelector('.alezux-slide-item');
        let scrollAmount = 320; // Default fallback

        if (firstItem) {
            const style = window.getComputedStyle(wrapper);
            const gap = parseFloat(style.gap) || 20;
            scrollAmount = firstItem.offsetWidth + gap;
        }

        if (wrapper && prevBtn && nextBtn) {
            // Remover listeners previos para evitar duplicados en el editor
            const newPrevBtn = prevBtn.cloneNode(true);
            const newNextBtn = nextBtn.cloneNode(true);
            prevBtn.parentNode.replaceChild(newPrevBtn, prevBtn);
            nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);

            // Función para actualizar estado de flechas
            const updateArrowState = () => {
                // Tolerancia pequeña para errores de redondeo
                const tolerance = 2;
                const isAtStart = wrapper.scrollLeft <= tolerance;
                const isAtEnd = wrapper.scrollLeft + wrapper.clientWidth >= wrapper.scrollWidth - tolerance;

                if (isAtStart) {
                    newPrevBtn.classList.add('alezux-nav-disabled');
                } else {
                    newPrevBtn.classList.remove('alezux-nav-disabled');
                }

                if (isAtEnd) {
                    newNextBtn.classList.add('alezux-nav-disabled');
                } else {
                    newNextBtn.classList.remove('alezux-nav-disabled');
                }
            };

            // Listeners de Click
            newPrevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (newPrevBtn.classList.contains('alezux-nav-disabled')) return;
                wrapper.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            });

            newNextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (newNextBtn.classList.contains('alezux-nav-disabled')) return;
                wrapper.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            });

            // Listeners de Scroll y Resize
            wrapper.addEventListener('scroll', updateArrowState);
            window.addEventListener('resize', updateArrowState);

            // Chequeo inicial
            // setTimeout para asegurar que el renderizado esté completo (especialmente imágenes)
            setTimeout(updateArrowState, 100);
            updateArrowState();
        }
    });
}
