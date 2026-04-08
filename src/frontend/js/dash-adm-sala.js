document.addEventListener('DOMContentLoaded', function () {

    const sidebar   = document.getElementById('sidebar');
    const html      = document.documentElement;
    const btnTema   = document.getElementById('toggleTema');
    const iconeTema = document.getElementById('iconeTema');
    const btnSidebar = document.getElementById('toggleSidebar');

    const temaSalvo = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-theme', temaSalvo);
    if (iconeTema) {
        iconeTema.className = temaSalvo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }

    if (btnSidebar && sidebar) {
        btnSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('aberta');
        });
    }

    document.addEventListener('click', (e) => {
        if (!sidebar) return;
        if (window.innerWidth <= 768 &&
            sidebar.classList.contains('aberta') &&
            !sidebar.contains(e.target) &&
            e.target !== btnSidebar) {
            sidebar.classList.remove('aberta');
        }
    });

    if (btnTema && iconeTema) {
        btnTema.addEventListener('click', () => {
            const atual = html.getAttribute('data-theme');
            const novo  = atual === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', novo);
            localStorage.setItem('theme', novo);
            iconeTema.className = novo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        });
    }

    const buscaInput = document.getElementById('buscaAluno');
    const listaItens = document.querySelectorAll('#listaAlunos .aluno-item');
    if (buscaInput) {
        buscaInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            listaItens.forEach(item => {
                const nome = item.getAttribute('data-nome') || '';
                item.style.display = nome.includes(q) ? '' : 'none';
            });
        });
    }

});