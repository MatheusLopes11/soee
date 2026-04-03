document.getElementById('dataAtual').textContent = new Date().toLocaleDateString('pt-BR', {
        weekday: 'short', day: '2-digit', month: 'short', year: 'numeric'
    });

    const sidebar = document.getElementById('sidebar');
    document.getElementById('toggleSidebar').addEventListener('click', () => {
        sidebar.classList.toggle('aberta');
    });

    const html      = document.documentElement;
    const btnTema   = document.getElementById('toggleTema');
    const iconeTema = document.getElementById('iconeTema');
    const temaSalvo = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-theme', temaSalvo);
    iconeTema.className = temaSalvo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';

    btnTema.addEventListener('click', () => {
        const atual = html.getAttribute('data-theme');
        const novo  = atual === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', novo);
        localStorage.setItem('theme', novo);
        iconeTema.className = novo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    });