    const sidebar     = document.getElementById('sidebar');
    const html        = document.documentElement;
    const btnTema     = document.getElementById('toggleTema');
    const iconeTema   = document.getElementById('iconeTema');
    const temaSalvo   = localStorage.getItem('theme') || 'light';

    html.setAttribute('data-theme', temaSalvo);
    iconeTema.className = temaSalvo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';

    document.getElementById('toggleSidebar').addEventListener('click', () => {
        sidebar.classList.toggle('aberta');
    });

    btnTema.addEventListener('click', () => {
        const atual = html.getAttribute('data-theme');
        const novo  = atual === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', novo);
        localStorage.setItem('theme', novo);
        iconeTema.className = novo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    });

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
