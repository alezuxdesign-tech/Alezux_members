jQuery(document).ready(function ($) {
    /**
     * Handle Topic Completion Toggle
     */
    $('body').on('click', '.alezux-btn-complete-topic', function (e) {
        e.preventDefault();

        var $button = $(this);
        // Ensure we are hitting the anchor if clicked on inner elements
        if (!$button.hasClass('alezux-btn-complete-topic')) {
            $button = $button.closest('.alezux-btn-complete-topic');
        }

        // --- LOGICA DE SOLO REDIRECCION SI ESTA COMPLETADO ---
        if ($button.hasClass('is-completed')) {
            var nextUrl = $button.data('next-url');
            if (nextUrl) {
                window.location.href = nextUrl;
            } else {
                // Si no hay siguiente URL, no hacemos nada (el usuario pidió no desmarcar)
                console.log('Topic completado. No hay siguiente paso definido.');
            }
            return; // EXIT - No AJAX
        }

        var postId = $button.data('post-id');
        var nonce = $button.data('nonce');
        var $loader = $button.find('.alezux-btn-loader');

        if ($button.hasClass('is-loading')) {
            return;
        }

        // Validación de seguridad extra
        if (!nonce) {
            console.warn('No nonce found for action');
            return;
        }

        // Add loading state
        $button.addClass('is-loading');
        $loader.css('display', 'flex');
        // NO OCULTAMOS EL CONTENIDO (.children().hide()) para evitar colapso visual, lo manejamos con CSS opacity

        // Ensure alezux_vars is defined
        var ajaxUrl = (typeof alezux_vars !== 'undefined') ? alezux_vars.ajax_url : '/wp-admin/admin-ajax.php';

        // Solo "mark" es posible ahora por la lógica anterior
        var method = 'mark';

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'alezux_toggle_topic_complete',
                post_id: postId,
                nonce: nonce,
                method: method
            },
            success: function (response) {
                // Manejo de Redirección Inteligente
                if (response.success) {
                    var nextUrl = $button.data('next-url');
                    if (nextUrl) {
                        window.location.href = nextUrl;
                    } else {
                        // Fallback reload
                        location.reload();
                    }
                    return;
                }

                $button.removeClass('is-loading');
                $loader.hide(); // Hide loader explicitly

                if (!response.success) {
                    console.warn('Alezux Toggle Error: ', response);
                    // UI bleibt visual (Error handling optional - alert?)
                }
            },
            error: function (xhr, status, error) {
                $button.removeClass('is-loading');
                $loader.hide();
                console.error('AJAX Error:', error);
            }
        });
    });

    /**
     * Time Tracking Logic for Study - UNIVERSAL VERSION
     * Supports HTML5, YouTube (API), Vimeo (API), and generic Iframe interaction.
     */
    /**
     * Study Time Tracker (Event Driven - SQL)
     */
    (function () {
        console.log('Alezux Tracker: Init');
        if (typeof alezux_vars === 'undefined' || !alezux_vars.ajax_url || !alezux_vars.post_id) {
            console.error('Alezux Tracker: Missing vars', typeof alezux_vars !== 'undefined' ? alezux_vars : 'undefined');
            return;
        }

        const ajaxUrl = alezux_vars.ajax_url;
        const postId = alezux_vars.post_id;

        // State
        let startTime = 0;
        let accumulatedTime = 0;
        let isTracking = false;
        let activeSources = new Set();

        console.log('Alezux Tracker: Ready. Post ID:', postId);

        function startTracking(source) {
            console.log('Alezux Tracker: Request Start from', source);
            if (!activeSources.has(source)) {
                activeSources.add(source);
                if (!isTracking) {
                    isTracking = true;
                    startTime = Date.now();
                    console.log('Alezux Tracker: STARTED TRACKING. StartTime:', new Date(startTime).toLocaleTimeString());
                }
            }
            console.log('Alezux Tracker: Active Sources:', Array.from(activeSources));
        }

        function stopTracking(source) {
            console.log('Alezux Tracker: Request Stop from', source);
            if (activeSources.has(source)) {
                activeSources.delete(source);
                if (activeSources.size === 0 && isTracking) {
                    isTracking = false;
                    const now = Date.now();
                    const sessionTime = Math.floor((now - startTime) / 1000);
                    if (sessionTime > 0) {
                        accumulatedTime += sessionTime;
                        console.log('Alezux Tracker: STOPPED TRACKING. Session Secs:', sessionTime, 'Total Accum:', accumulatedTime);
                        sendData();
                    } else {
                        console.log('Alezux Tracker: STOPPED ignored (0s duration)');
                    }
                }
            }
            console.log('Alezux Tracker: Active Sources:', Array.from(activeSources));
        }

        function sendData() {
            if (accumulatedTime > 0) {
                const timeToSend = accumulatedTime;
                accumulatedTime = 0;

                console.log('Alezux Tracker: SENDING DATA...', timeToSend, 'seconds');

                // Reliable send on unload
                if (navigator.sendBeacon) {
                    const formData = new FormData();
                    formData.append('action', 'alezux_track_study_time');
                    formData.append('seconds', timeToSend);
                    formData.append('post_id', postId);
                    const success = navigator.sendBeacon(ajaxUrl, formData); // Returns true if queued
                    console.log('Alezux Tracker: Beacon Queued?', success);
                } else {
                    $.post(ajaxUrl, {
                        action: 'alezux_track_study_time',
                        seconds: timeToSend,
                        post_id: postId
                    }).done(function (res) {
                        console.log('Alezux Tracker: AJAX Success', res);
                    }).fail(function (err) {
                        console.error('Alezux Tracker: AJAX Fail', err);
                    });
                }
            } else {
                console.log('Alezux Tracker: sendData called but nothing to send.');
            }
        }

        // Adapter for existing calls
        const logState = (source, active) => {
            if (active) startTracking(source);
            else stopTracking(source);
        };

        // --- HTML5 Events ---
        $('video, audio').on('play', function () {
            console.log('Alezux Tracker Event: HTML5 Play');
            startTracking('html5-' + (this.id || 'gen'));
        }).on('pause ended', function () {
            console.log('Alezux Tracker Event: HTML5 Pause/Ended');
            stopTracking('html5-' + (this.id || 'gen'));
        });

        // --- VdoCipher API Support ---
        function initVdoCipher() {
            const vdoIframes = $('iframe[src*="vdocipher.com"]');
            console.log('Alezux Tracker: Detected VdoCipher Iframes count:', vdoIframes.length);

            if (vdoIframes.length === 0) return;

            // Load VdoCipher API
            // Check if global VdoPlayer exists
            if (typeof VdoPlayer === 'undefined') {
                console.log('Alezux Tracker: Loading VdoCipher API...');
                $.getScript('https://player.vdocipher.com/v2/api.js')
                    .done(function () {
                        console.log('Alezux Tracker: VdoCipher API Loaded');
                        bindVdoPlayers();
                    })
                    .fail(function (jqxhr, settings, exception) {
                        console.error('Alezux Tracker: VdoCipher API Load Failed', exception);
                    });
            } else {
                console.log('Alezux Tracker: VdoCipher API already present');
                bindVdoPlayers();
            }

            function bindVdoPlayers() {
                vdoIframes.each(function () {
                    const iframe = this;
                    let player;

                    try {
                        // Try to get existing instance first
                        if (typeof VdoPlayer.getInstance === 'function') {
                            player = VdoPlayer.getInstance(iframe);
                        }
                    } catch (e) { console.warn('VdoCipher getInstance error', e); }

                    if (!player) {
                        console.log('Alezux Tracker: Creating NEW VdoCipher Player instance for', iframe.src);
                        try {
                            player = new VdoPlayer(iframe);
                        } catch (e) {
                            console.error('Alezux Tracker: Failed to create VdoPlayer', e);
                            // Fallback: maybe getInstance failed but it exists?
                            // Just stop here for this iframe to avoid crash
                            return;
                        }
                    } else {
                        console.log('Alezux Tracker: Using EXISTING VdoCipher Player instance for', iframe.src);
                    }

                    if (player && player.video) {
                        player.video.addEventListener('play', function () {
                            console.log('Alezux Tracker Event: VdoCipher Play');
                            startTracking('vdo-' + (iframe.id || iframe.src));
                        });

                        player.video.addEventListener('pause', function () {
                            console.log('Alezux Tracker Event: VdoCipher Pause');
                            stopTracking('vdo-' + (iframe.id || iframe.src));
                        });

                        player.video.addEventListener('ended', function () {
                            console.log('Alezux Tracker Event: VdoCipher Ended');
                            stopTracking('vdo-' + (iframe.id || iframe.src));
                        });
                    }
                });
            }
        }
        initVdoCipher();

        // --- YouTube API ---
        function onYouTubeIframeAPIReady() {
            $('iframe[src*="youtube.com"], iframe[src*="youtu.be"]').each(function (index) {
                if (!this.id) this.id = 'alezux-yt-' + index;
                try {
                    new YT.Player(this.id, {
                        events: {
                            'onStateChange': function (event) {
                                if (event.data === YT.PlayerState.PLAYING) {
                                    console.log('Alezux Tracker Event: YT Playing');
                                    startTracking('yt-' + event.target.getIframe().id);
                                } else if (event.data === YT.PlayerState.PAUSED || event.data === YT.PlayerState.ENDED) {
                                    console.log('Alezux Tracker Event: YT Paused/Ended');
                                    stopTracking('yt-' + event.target.getIframe().id);
                                }
                            }
                        }
                    });
                } catch (e) { }
            });
        }

        if (typeof YT === 'undefined' || typeof YT.Player === 'undefined') {
            var tag = document.createElement('script');
            tag.src = "https://www.youtube.com/iframe_api";
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
            window.onYouTubeIframeAPIReady = onYouTubeIframeAPIReady;
        } else {
            onYouTubeIframeAPIReady();
        }

        // --- Vimeo API ---
        const vimeoIframes = $('iframe[src*="vimeo.com"]');
        if (vimeoIframes.length > 0) {
            if (typeof Vimeo === 'undefined') {
                $.getScript('https://player.vimeo.com/api/player.js').done(bindVimeo);
            } else {
                bindVimeo();
            }
        }

        function bindVimeo() {
            vimeoIframes.each(function () {
                const player = new Vimeo.Player(this);
                player.on('play', () => { console.log('Alezux Tracker Event: Vimeo Play'); startTracking('vimeo'); });
                player.on('pause', () => { console.log('Alezux Tracker Event: Vimeo Pause'); stopTracking('vimeo'); });
                player.on('ended', () => { console.log('Alezux Tracker Event: Vimeo Ended'); stopTracking('vimeo'); });
            });
        }

        function sendData() {
            if (accumulatedTime > 0) {
                const timeToSend = accumulatedTime;
                accumulatedTime = 0;

                var ajaxUrl = (typeof alezux_vars !== 'undefined') ? alezux_vars.ajax_url : '/wp-admin/admin-ajax.php';
                var postId = (typeof alezux_vars !== 'undefined') ? alezux_vars.post_id : 0;
                var courseId = (typeof alezux_vars !== 'undefined' && alezux_vars.course_id) ? alezux_vars.course_id : 0;

                console.log('Alezux Tracker: Sending Data (Auto/Beacon)...', timeToSend, 'Course:', courseId, 'Topic:', postId);

                // Reliable send on unload
                if (navigator.sendBeacon) {
                    const formData = new FormData();
                    formData.append('action', 'alezux_track_study_time');
                    formData.append('seconds', timeToSend);
                    formData.append('post_id', postId); // Legacy/Fallback
                    formData.append('course_id', courseId);
                    formData.append('topic_id', postId);

                    const success = navigator.sendBeacon(ajaxUrl, formData); // Returns true if queued
                    console.log('Alezux Tracker: Beacon Queued?', success);
                } else {
                    $.post(ajaxUrl, {
                        action: 'alezux_track_study_time',
                        seconds: timeToSend,
                        post_id: postId, // Legacy
                        course_id: courseId,
                        topic_id: postId
                    });
                }
            } else {
                console.log('Alezux Tracker: sendData called but nothing to send.');
            }
        }



        $(window).on('beforeunload visibilitychange', function () {
            console.log('Alezux Tracker Event: Window/Vis Change', document.visibilityState);
            if (document.visibilityState === 'hidden') {
                if (isTracking) {
                    const now = Date.now();
                    const sessionTime = Math.floor((now - startTime) / 1000);
                    if (sessionTime > 0) {
                        accumulatedTime += sessionTime;
                        startTime = now;
                        console.log('Alezux Tracker: Accumulating before exit:', sessionTime);
                    }
                }
                sendData();
            }
        });

    })();

});
