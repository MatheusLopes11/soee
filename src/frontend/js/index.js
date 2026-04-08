window.addEventListener('load', () => {
    setTimeout(() => document.getElementById('loader').classList.add('hide'), 1500);
});

const dot  = document.getElementById('cursorDot');
const ring = document.getElementById('cursorRing');
if (dot && ring) {
    document.addEventListener('mousemove', e => {
        dot.style.left  = ring.style.left  = e.clientX + 'px';
        dot.style.top   = ring.style.top   = e.clientY + 'px';
    });
}

const html      = document.documentElement;
const btnTema   = document.getElementById('toggleTema');
const iconeTema = document.getElementById('iconeTema');
const temaSalvo = localStorage.getItem('theme') || 'light';
html.setAttribute('data-theme', temaSalvo);

if (iconeTema) {
    iconeTema.className = temaSalvo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
}

if (btnTema) {
    btnTema.addEventListener('click', () => {
        const atual = html.getAttribute('data-theme');
        const novo  = atual === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', novo);
        localStorage.setItem('theme', novo);
        if (iconeTema) {
            iconeTema.className = novo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        }
    });
}

const toggleSenha = document.getElementById('toggleSenha');
const campoPwd    = document.getElementById('password');
const iconeSenha  = document.getElementById('iconeSenha');

if (toggleSenha && campoPwd && iconeSenha) {
    toggleSenha.addEventListener('click', () => {
        const visivel = campoPwd.type === 'text';
        campoPwd.type = visivel ? 'password' : 'text';
        iconeSenha.className = visivel ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
    });
}

const formLogin = document.getElementById('formLogin');
if (formLogin) {
    formLogin.addEventListener('submit', function (e) {
        const user = document.getElementById('username').value.trim();
        const pwd  = document.getElementById('password').value.trim();
        let valido = true;

        if (!user) {
            document.getElementById('username').classList.add('invalido');
            valido = false;
        } else {
            document.getElementById('username').classList.remove('invalido');
        }

        if (!pwd) {
            document.getElementById('password').classList.add('invalido');
            valido = false;
        } else {
            document.getElementById('password').classList.remove('invalido');
        }

        if (!valido) {
            e.preventDefault();
            return;
        }

        document.getElementById('btnEntrar').classList.add('loading');
    });
}