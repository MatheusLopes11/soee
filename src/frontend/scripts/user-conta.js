/* ─────────────────────────────────────────
   user-conta.js — SOEE
───────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', function () {

    /* ── Cursor personalizado ── */
    var dot   = document.getElementById('cursorDot');
    var ring  = document.getElementById('cursorRing');
    var mouseX = 0, mouseY = 0;
    var ringX  = 0, ringY  = 0;

    if (dot && ring) {
        document.addEventListener('mousemove', function (e) {
            mouseX = e.clientX;
            mouseY = e.clientY;
            dot.style.left = mouseX + 'px';
            dot.style.top  = mouseY + 'px';
        });

        (function animateRing() {
            ringX += (mouseX - ringX) * 0.12;
            ringY += (mouseY - ringY) * 0.12;
            ring.style.left = ringX + 'px';
            ring.style.top  = ringY + 'px';
            requestAnimationFrame(animateRing);
        })();
    }

    /* ── Reveal on scroll ── */
    var reveals  = document.querySelectorAll('.reveal');
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.1 });
    reveals.forEach(function (el) { observer.observe(el); });

    /* ── Tema ── */
    var html      = document.documentElement;
    var btnTema   = document.getElementById('toggleTema');
    var iconeTema = document.getElementById('iconeTema');

    function setTema(t) {
        html.setAttribute('data-theme', t);
        localStorage.setItem('theme', t);
        if (iconeTema) iconeTema.className = t === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }

    setTema(localStorage.getItem('theme') || 'light');

    if (btnTema) {
        btnTema.addEventListener('click', function () {
            setTema(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
        });
    }

    /* ── Preview de foto antes de enviar ── */
    var inputFoto     = document.getElementById('inputFoto');
    var fotoPreview   = document.getElementById('fotoPreview');
    var heroAvatar    = document.getElementById('heroAvatar');
    var btnSalvarFoto = document.getElementById('btnSalvarFoto');
    var fotoNome      = document.getElementById('fotoNome');

    if (inputFoto) {
        inputFoto.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;

            var tipos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!tipos.includes(file.type)) {
                mostrarToast('Formato inválido. Use JPG, PNG, WEBP ou GIF.', 'erro');
                this.value = '';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                mostrarToast('Arquivo muito grande. Máximo 5MB.', 'erro');
                this.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                if (fotoPreview) {
                    fotoPreview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                    fotoPreview.classList.add('preview-ativa');
                }
                if (heroAvatar) {
                    heroAvatar.innerHTML = '<img src="' + e.target.result + '" alt="Avatar">';
                }
            };
            reader.readAsDataURL(file);

            if (fotoNome)       fotoNome.textContent = file.name;
            if (btnSalvarFoto)  btnSalvarFoto.style.display = 'inline-flex';
        });
    }

    /* ── Toast ── */
    function mostrarToast(msg, tipo) {
        tipo = tipo || 'sucesso';
        var iconMap = { sucesso: 'fa-check-circle', erro: 'fa-times-circle', aviso: 'fa-exclamation-triangle' };
        var el = document.createElement('div');
        el.className = 'toast-fixo ' + tipo;
        el.innerHTML = '<i class="fa-solid ' + iconMap[tipo] + '"></i> ' + msg;
        document.body.appendChild(el);
        setTimeout(function () {
            el.style.opacity = '0';
            el.style.transform = 'translateX(40px)';
            el.style.transition = '0.4s';
            setTimeout(function () { el.remove(); }, 400);
        }, 3500);
    }

    /* ── Auto-remove toast do PHP ── */
    var toastFoto = document.getElementById('toastFoto');
    if (toastFoto) {
        setTimeout(function () {
            toastFoto.style.opacity = '0';
            toastFoto.style.transform = 'translateX(40px)';
            toastFoto.style.transition = '0.5s';
            setTimeout(function () { toastFoto.remove(); }, 500);
        }, 4000);
    }

    /* ── Confirmação de logout ── */
    var btnLogout = document.getElementById('btnLogout');
    if (btnLogout) {
        btnLogout.addEventListener('click', function (e) {
            e.preventDefault();
            var href = this.href;
            if (confirm('Tem certeza que deseja sair da sua conta?')) {
                window.location.href = href;
            }
        });
    }

});