jQuery(document).ready(function ($) {
    const $widget = $('.alezux-notifications-widget');
    const $bell = $widget.find('.alezux-bell-icon');
    const $badge = $widget.find('.alezux-notification-badge');
    const $list = $widget.find('#alezux-notif-list-inbox');
    const $markAll = $widget.find('.alezux-mark-all-read');

    // Toggle Dropdown using delegation for robustness
    $(document).on('click', '.alezux-bell-icon', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $parent = $(this).closest('.alezux-notifications-widget');
        $parent.toggleClass('active');

        // Close other open widgets if any
        $('.alezux-notifications-widget').not($parent).removeClass('active');
    });

    // Close on click outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.alezux-notifications-widget').length) {
            $('.alezux-notifications-widget').removeClass('active');
        }
    });

    // Mark All Read
    $(document).on('click', '.alezux-mark-all-read', function (e) {
        e.stopPropagation();
        $.post(alezux_notifications_obj.ajaxurl, {
            action: 'alezux_mark_all_read',
            nonce: alezux_notifications_obj.nonce
        }, function (response) {
            if (response.success) {
                // Al marcar todo como leído, vaciamos la lista visualmente (Inbox Zero)
                $list.empty();
                $list.html(`
                    <div class="alezux-no-notifications">
                        <i class="eicon-bell-o alezux-no-notifications-icon"></i>
                        <div>No hay notificaciones nuevas</div>
                    </div>
                `);
                updateBadge(0);
            }
        });
    });

    // Click on Notification Item
    $(document).on('click', '.alezux-notification-item', function () {
        const $item = $(this);
        const id = $item.data('id');
        const link = $item.data('link');

        if ($item.hasClass('unread')) {
            $.post(alezux_notifications_obj.ajaxurl, {
                action: 'alezux_mark_read',
                id: id,
                nonce: alezux_notifications_obj.nonce
            });

            // Remover visualmente la notificación al hacer click (marcar como leída)
            // Si quieres que solo desaparezca al hacer click, descomenta la siguiente línea:
            $item.slideUp(200, function () {
                $(this).remove();
                // Check if list empty
                if ($list.children('.alezux-notification-item').length === 0) {
                    $list.html(`
                        <div class="alezux-no-notifications">
                            <i class="eicon-bell-o alezux-no-notifications-icon"></i>
                            <div>No hay notificaciones nuevas</div>
                        </div>
                    `);
                }
            });

            // Actualizar badge decrementando 1
            var currentCount = parseInt($badge.text()) || 0;
            updateBadge(Math.max(0, currentCount - 1));
        }

        if (link && link !== '#') {
            window.location.href = link;
        }
    });

    // Fetch Notifications
    function fetchNotifications() {
        if (!alezux_notifications_obj || !alezux_notifications_obj.is_logged_in) {
            return;
        }

        $.post(alezux_notifications_obj.ajaxurl, {
            action: 'alezux_get_notifications',
            nonce: alezux_notifications_obj.nonce
        }, function (response) {
            if (response.success) {
                renderNotifications(response.data.notifications);
                updateBadge(response.data.unread_count);
            }
        });
    }

    function renderNotifications(notifications) {
        $list.empty();
        if (notifications.length === 0) {
            $list.html(`
                <div class="alezux-no-notifications">
                    <i class="eicon-bell-o alezux-no-notifications-icon"></i>
                    <div>No hay notificaciones</div>
                </div>
            `);
            return;
        }

        notifications.forEach(notif => {
            const isUnread = notif.is_read == '0' ? 'unread' : '';
            // Mapa de Iconos
            const icons = {
                'bell': `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>`,
                'course': `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>`
            };

            let avatarHtml = '';

            if (notif.avatar_url && notif.avatar_url.startsWith('icon:')) {
                const iconName = notif.avatar_url.split(':')[1];
                const svg = icons[iconName] || icons['bell'];
                avatarHtml = `<div class="notif-avatar">${svg}</div>`;
            } else if (notif.avatar_url) {
                avatarHtml = `<img src="${notif.avatar_url}" class="notif-avatar">`;
            } else {
                avatarHtml = `<div class="notif-avatar">${icons['bell']}</div>`;
            }

            const html = `
                <div class="alezux-notification-item ${isUnread}" data-id="${notif.id}" data-link="${notif.link}">
                    ${avatarHtml}
                    <div class="notif-content">
                        <div class="notif-title">${notif.title}</div>
                        <div class="notif-message">${notif.message}</div>
                        <div class="notif-meta">${notif.time_ago}</div>
                    </div>
                </div>
            `;
            $list.append(html);
        });
    }

    function updateBadge(count) {
        if (count > 0) {
            $('.alezux-notification-badge').text(count).show();
        } else {
            $('.alezux-notification-badge').hide();
        }
    }

    // Initial Load
    fetchNotifications();

    // Poll every 60 seconds (optional)
    setInterval(fetchNotifications, 60000);
});
