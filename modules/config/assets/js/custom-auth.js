/**
 * ALEZUX MEMBERS - CUSTOM AUTHENTICATION ENGINE
 * Pure Vanilla JS, zero dependencies, lightning-fast execution.
 */
(function() {
  'use strict';

  document.addEventListener('DOMContentLoaded', initAuth);

  function initAuth() {
    const config = window.AlezuxAuthConfig || {
      ajaxUrl: '/wp-admin/admin-ajax.php',
      nonce: '',
      homeUrl: '/',
      defaultRedirect: '/'
    };

    // DOM Elements
    const views = {
      login: document.getElementById('alezux-view-login'),
      register: document.getElementById('alezux-view-register'),
      recover: document.getElementById('alezux-view-recover'),
      reset: document.getElementById('alezux-view-reset')
    };

    const heroSteps = document.querySelectorAll('.hero-step-card');

    // -------------------------------------------------------------
    // 1. Navigation / View Switching
    // -------------------------------------------------------------
    function switchView(target) {
      if (!views[target]) {
        target = 'login';
      }

      // Clear alerts in all views
      document.querySelectorAll('.auth-alert-container').forEach(function(container) {
        container.innerHTML = '';
      });

      // Toggle views
      Object.keys(views).forEach(function(key) {
        const el = views[key];
        if (el) {
          if (key === target) {
            el.classList.remove('is-hidden');
            const firstInput = el.querySelector('input:not([type="hidden"])');
            if (firstInput) {
              setTimeout(function() { firstInput.focus(); }, 100);
            }
          } else {
            el.classList.add('is-hidden');
          }
        }
      });

      // Update hero steps styling depending on active view
      if (heroSteps && heroSteps.length >= 3) {
        heroSteps.forEach(function(step) {
          step.classList.remove('active');
          step.classList.add('glass');
        });

        if (target === 'register') {
          heroSteps[0].classList.add('active');
          heroSteps[0].classList.remove('glass');
        } else if (target === 'reset') {
          heroSteps[1].classList.add('active');
          heroSteps[1].classList.remove('glass');
        } else {
          heroSteps[0].classList.add('active');
          heroSteps[0].classList.remove('glass');
        }
      }
    }

    // Attach click listeners to data-auth-target elements
    document.querySelectorAll('[data-auth-target]').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const target = this.getAttribute('data-auth-target');
        switchView(target);
      });
    });

    // -------------------------------------------------------------
    // 2. Password Visibility Toggles
    // -------------------------------------------------------------
    const eyeIconSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    const eyeOffIconSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

    document.querySelectorAll('.toggle-pwd-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const wrapper = this.closest('.input-wrapper');
        if (!wrapper) return;
        const input = wrapper.querySelector('input');
        if (!input) return;

        if (input.type === 'password') {
          input.type = 'text';
          this.innerHTML = eyeOffIconSvg;
        } else {
          input.type = 'password';
          this.innerHTML = eyeIconSvg;
        }
      });
    });

    // -------------------------------------------------------------
    // 3. Password Strength Meter
    // -------------------------------------------------------------
    function updatePasswordMeter(input, fillEl) {
      if (!input || !fillEl) return;
      const val = input.value || '';
      let score = 0;
      if (val.length >= 8) score++;
      if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;

      fillEl.className = 'password-meter-fill';
      if (val.length === 0) {
        fillEl.style.width = '0%';
      } else if (score <= 1) {
        fillEl.classList.add('meter-weak');
      } else if (score === 2 || score === 3) {
        fillEl.classList.add('meter-medium');
      } else {
        fillEl.classList.add('meter-strong');
      }
    }

    const regPwdInput = document.getElementById('alezux-register-password');
    const regMeterFill = document.getElementById('alezux-reg-meter-fill');
    if (regPwdInput && regMeterFill) {
      regPwdInput.addEventListener('input', function() {
        updatePasswordMeter(regPwdInput, regMeterFill);
      });
    }

    const resetPwdInput = document.getElementById('alezux-reset-password');
    const resetMeterFill = document.getElementById('alezux-reset-meter-fill');
    if (resetPwdInput && resetMeterFill) {
      resetPwdInput.addEventListener('input', function() {
        updatePasswordMeter(resetPwdInput, resetMeterFill);
      });
    }

    // -------------------------------------------------------------
    // 4. Alert Helper
    // -------------------------------------------------------------
    function showAlert(viewElement, type, message) {
      const container = viewElement.querySelector('.auth-alert-container');
      if (!container) return;

      const iconSvg = type === 'success'
        ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>'
        : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';

      container.innerHTML = '<div class="auth-alert ' + type + '">' + iconSvg + '<span>' + message + '</span></div>';
    }

    function setButtonLoading(btn, isLoading, originalText) {
      if (!btn) return;
      const textEl = btn.querySelector('.btn-text');
      const spinnerEl = btn.querySelector('.auth-spinner');

      if (isLoading) {
        btn.disabled = true;
        if (textEl) textEl.style.opacity = '0.5';
        if (spinnerEl) spinnerEl.style.display = 'inline-block';
      } else {
        btn.disabled = false;
        if (textEl) {
          textEl.style.opacity = '1';
          if (originalText) textEl.textContent = originalText;
        }
        if (spinnerEl) spinnerEl.style.display = 'none';
      }
    }

    // -------------------------------------------------------------
    // 5. AJAX Form Handlers
    // -------------------------------------------------------------

    // A. LOGIN
    const loginForm = document.getElementById('alezux-login-form');
    if (loginForm) {
      loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const submitBtn = loginForm.querySelector('.auth-submit-btn');
        const username = (document.getElementById('alezux-login-username') || {}).value || '';
        const password = (document.getElementById('alezux-login-password') || {}).value || '';
        const remember = (document.getElementById('alezux-login-remember') || {}).checked ? '1' : '0';
        const redirectTo = (document.getElementById('alezux-login-redirect') || {}).value || config.defaultRedirect;

        if (!username || !password) {
          showAlert(views.login, 'error', 'Por favor ingresa tu usuario/correo y contraseña.');
          return;
        }

        setButtonLoading(submitBtn, true);

        const formData = new FormData();
        formData.append('action', 'alezux_ajax_login');
        formData.append('nonce', config.nonce);
        formData.append('username', username.trim());
        formData.append('password', password);
        formData.append('remember', remember);
        formData.append('redirect_to', redirectTo);

        fetch(config.ajaxUrl, {
          method: 'POST',
          body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
          setButtonLoading(submitBtn, false);
          if (data && data.success) {
            showAlert(views.login, 'success', '¡Inicio de sesión exitoso! Redirigiendo...');
            const targetUrl = (data.data && data.data.redirect) ? data.data.redirect : redirectTo;
            setTimeout(function() {
              window.location.href = targetUrl;
            }, 600);
          } else {
            const msg = (data && data.data && data.data.message) ? data.data.message : 'Usuario o contraseña incorrectos.';
            showAlert(views.login, 'error', msg);
          }
        })
        .catch(function(err) {
          setButtonLoading(submitBtn, false);
          showAlert(views.login, 'error', 'Error al conectar con el servidor. Intenta de nuevo.');
        });
      });
    }

    // B. REGISTER
    const registerForm = document.getElementById('alezux-register-form');
    if (registerForm) {
      registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const submitBtn = registerForm.querySelector('.auth-submit-btn');
        const fullName = (document.getElementById('alezux-register-name') || {}).value || '';
        const email = (document.getElementById('alezux-register-email') || {}).value || '';
        const username = (document.getElementById('alezux-register-username') || {}).value || '';
        const password = (document.getElementById('alezux-register-password') || {}).value || '';
        const redirectTo = (document.getElementById('alezux-register-redirect') || {}).value || config.defaultRedirect;

        if (!email || !password) {
          showAlert(views.register, 'error', 'Por favor completa todos los campos requeridos.');
          return;
        }

        if (password.length < 8) {
          showAlert(views.register, 'error', 'La contraseña debe tener al menos 8 caracteres.');
          return;
        }

        setButtonLoading(submitBtn, true);

        const formData = new FormData();
        formData.append('action', 'alezux_ajax_register');
        formData.append('nonce', config.nonce);
        formData.append('full_name', fullName.trim());
        formData.append('email', email.trim());
        formData.append('username', username.trim());
        formData.append('password', password);
        formData.append('redirect_to', redirectTo);

        fetch(config.ajaxUrl, {
          method: 'POST',
          body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
          setButtonLoading(submitBtn, false);
          if (data && data.success) {
            showAlert(views.register, 'success', data.data.message || '¡Cuenta creada con éxito! Redirigiendo...');
            const targetUrl = (data.data && data.data.redirect) ? data.data.redirect : redirectTo;
            setTimeout(function() {
              window.location.href = targetUrl;
            }, 800);
          } else {
            const msg = (data && data.data && data.data.message) ? data.data.message : 'Error al registrar la cuenta.';
            showAlert(views.register, 'error', msg);
          }
        })
        .catch(function(err) {
          setButtonLoading(submitBtn, false);
          showAlert(views.register, 'error', 'Error de conexión. Inténtalo nuevamente.');
        });
      });
    }

    // C. RECOVER PASSWORD
    const recoverForm = document.getElementById('alezux-recover-form');
    if (recoverForm) {
      recoverForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const submitBtn = recoverForm.querySelector('.auth-submit-btn');
        const userLogin = (document.getElementById('alezux-recover-login') || {}).value || '';

        if (!userLogin) {
          showAlert(views.recover, 'error', 'Por favor ingresa tu correo o nombre de usuario.');
          return;
        }

        setButtonLoading(submitBtn, true);

        const formData = new FormData();
        formData.append('action', 'alezux_ajax_recover');
        formData.append('nonce', config.nonce);
        formData.append('user_login', userLogin.trim());

        fetch(config.ajaxUrl, {
          method: 'POST',
          body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
          setButtonLoading(submitBtn, false);
          if (data && data.success) {
            showAlert(views.recover, 'success', data.data.message || 'Se ha enviado un enlace seguro a tu correo electrónico.');
            const input = document.getElementById('alezux-recover-login');
            if (input) input.value = '';
          } else {
            const msg = (data && data.data && data.data.message) ? data.data.message : 'No existe ningún usuario con esos datos.';
            showAlert(views.recover, 'error', msg);
          }
        })
        .catch(function(err) {
          setButtonLoading(submitBtn, false);
          showAlert(views.recover, 'error', 'Error al procesar la solicitud.');
        });
      });
    }

    // D. RESET PASSWORD (CONFIRMATION)
    const resetForm = document.getElementById('alezux-reset-form');
    if (resetForm) {
      resetForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const submitBtn = resetForm.querySelector('.auth-submit-btn');
        const pass1 = (document.getElementById('alezux-reset-password') || {}).value || '';
        const pass2 = (document.getElementById('alezux-reset-confirm') || {}).value || '';
        const key = (document.getElementById('alezux-reset-key') || {}).value || '';
        const login = (document.getElementById('alezux-reset-user') || {}).value || '';

        if (!pass1 || !pass2) {
          showAlert(views.reset, 'error', 'Por favor completa ambas contraseñas.');
          return;
        }

        if (pass1 !== pass2) {
          showAlert(views.reset, 'error', 'Las contraseñas no coinciden.');
          return;
        }

        if (pass1.length < 8) {
          showAlert(views.reset, 'error', 'La contraseña debe tener mínimo 8 caracteres.');
          return;
        }

        setButtonLoading(submitBtn, true);

        const formData = new FormData();
        formData.append('action', 'alezux_reset_password');
        formData.append('nonce', config.nonce);
        formData.append('key', key);
        formData.append('login', login);
        formData.append('pass1', pass1);
        formData.append('pass2', pass2);

        fetch(config.ajaxUrl, {
          method: 'POST',
          body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
          setButtonLoading(submitBtn, false);
          if (data && data.success) {
            showAlert(views.reset, 'success', data.data.message || '¡Contraseña actualizada con éxito! Redirigiendo...');
            setTimeout(function() {
              switchView('login');
              showAlert(views.login, 'success', 'Contraseña restablecida. Ya puedes iniciar sesión.');
            }, 1200);
          } else {
            const msg = (data && data.data && data.data.message) ? data.data.message : 'El enlace de recuperación es inválido o ha expirado.';
            showAlert(views.reset, 'error', msg);
          }
        })
        .catch(function(err) {
          setButtonLoading(submitBtn, false);
          showAlert(views.reset, 'error', 'Error al actualizar la contraseña.');
        });
      });
    }

    // -------------------------------------------------------------
    // 6. Handle URL Parameters (Initial View)
    // -------------------------------------------------------------
    const urlParams = new URLSearchParams(window.location.search);
    const actionParam = urlParams.get('action');

    if (actionParam === 'rp' || actionParam === 'resetpass' || (urlParams.has('key') && urlParams.has('login'))) {
      switchView('reset');
      const keyVal = urlParams.get('key') || '';
      const loginVal = urlParams.get('login') || '';
      const keyInput = document.getElementById('alezux-reset-key');
      const loginInput = document.getElementById('alezux-reset-user');
      if (keyInput) keyInput.value = keyVal;
      if (loginInput) loginInput.value = loginVal;
    } else if (actionParam === 'register' || window.location.hash === '#register') {
      switchView('register');
    } else if (actionParam === 'lostpassword' || window.location.hash === '#recover') {
      switchView('recover');
    } else {
      switchView('login');
    }

    // Check query messages like loggedout=true
    if (urlParams.get('loggedout') === 'true') {
      showAlert(views.login, 'success', 'Has cerrado sesión correctamente.');
    }
  }
})();
