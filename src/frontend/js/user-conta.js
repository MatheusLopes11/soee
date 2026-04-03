/* ─────────────────────────────────────────
   user-conta.js — SOEE
───────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', function () {

    /* ── Tema ── */
    var html      = document.documentElement;
    var btnTema   = document.getElementById('toggleTema');
    var iconeTema = document.getElementById('iconeTema');
    var temaSalvo = localStorage.getItem('theme') || 'light';

    html.setAttribute('data-theme', temaSalvo);
    iconeTema.className = temaSalvo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';

    btnTema.addEventListener('click', function () {
        var atual = html.getAttribute('data-theme');
        var novo  = atual === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', novo);
        localStorage.setItem('theme', novo);
        iconeTema.className = novo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    });

    /* ── Preview de foto antes de enviar ── */
    var inputFoto      = document.getElementById('inputFoto');
    var fotoPreview    = document.getElementById('fotoPreview');
    var heroAvatar     = document.getElementById('heroAvatar');
    var btnSalvarFoto  = document.getElementById('btnSalvarFoto');
    var fotoNome       = document.getElementById('fotoNome');

    if (inputFoto) {
        inputFoto.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;

            /* validação client-side */
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
                /* Atualiza preview na seção foto */
                fotoPreview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                fotoPreview.classList.add('preview-ativa');

                /* Atualiza avatar no hero */
                if (heroAvatar) {
                    heroAvatar.innerHTML = '<img src="' + e.target.result + '" alt="Avatar">';
                }
            };
            reader.readAsDataURL(file);

            /* Mostra nome do arquivo e botão salvar */
            if (fotoNome) fotoNome.textContent = file.name;
            if (btnSalvarFoto) btnSalvarFoto.style.display = 'inline-flex';
        });
    }

    /* ── Toast para mensagens ── */
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

    /* ── Auto-remove toast fixo do PHP após 4s ── */
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