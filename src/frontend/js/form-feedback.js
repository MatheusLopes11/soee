/* ─── LOADER ─── */
  window.addEventListener('load', () => {
    setTimeout(() => document.getElementById('loader').classList.add('hide'), 1500);
  });

  /* ─── CURSOR ─── */
  const dot  = document.getElementById('cursorDot');
  const ring = document.getElementById('cursorRing');
  document.addEventListener('mousemove', e => {
    dot.style.left  = ring.style.left  = e.clientX + 'px';
    dot.style.top   = ring.style.top   = e.clientY + 'px';
  });

  /* ─── TEMA ─── */
  const toggleTema = document.getElementById('toggleTema');
  const html       = document.documentElement;
  const iconTema   = toggleTema.querySelector('i');
  const temaSalvo  = localStorage.getItem('soee-tema') || 'light';
  html.setAttribute('data-theme', temaSalvo);
  iconTema.className = temaSalvo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
  toggleTema.addEventListener('click', () => {
    const atual  = html.getAttribute('data-theme');
    const novo   = atual === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', novo);
    localStorage.setItem('soee-tema', novo);
    iconTema.className = novo === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
  });

  /* ─── TOAST ─── */
  function mostrarToast(mensagem, tipo = 'sucesso') {
    const t    = document.getElementById('toast');
    const msg  = document.getElementById('toastMsg');
    t.className = `toast ${tipo}`;
    t.querySelector('i').className = tipo === 'sucesso'
      ? 'fa-solid fa-circle-check'
      : 'fa-solid fa-circle-xmark';
    msg.textContent = mensagem;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 4000);
  }

  /* ─── VALIDAÇÃO ─── */
  const form = document.getElementById('feedbackForm');
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    let valido = true;

    // Nota
    const nota = document.querySelector('input[name="nota_feedback"]:checked');
    const erroNota = document.getElementById('erroNota');
    if (!nota) { erroNota.classList.add('visivel'); valido = false; }
    else        { erroNota.classList.remove('visivel'); }

    // Nome
    const nome = document.getElementById('nome_feedback');
    const erroNome = document.getElementById('erroNome');
    const anonimo = document.getElementById('anonimo').checked;
    if (!anonimo && nome.value.trim().length < 3) {
      nome.classList.add('erro'); erroNome.classList.add('visivel'); valido = false;
    } else {
      nome.classList.remove('erro'); erroNome.classList.remove('visivel');
    }

    // Turma
    const turma = document.getElementById('turma_feedback');
    const erroTurma = document.getElementById('erroTurma');
    if (!anonimo && !turma.value) {
      turma.classList.add('erro'); erroTurma.classList.add('visivel'); valido = false;
    } else {
      turma.classList.remove('erro'); erroTurma.classList.remove('visivel');
    }

    // Email
    const email = document.getElementById('email_feedback');
    const erroEmail = document.getElementById('erroEmail');
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!anonimo && !emailRe.test(email.value)) {
      email.classList.add('erro'); erroEmail.classList.add('visivel'); valido = false;
    } else {
      email.classList.remove('erro'); erroEmail.classList.remove('visivel');
    }

    // Categorias
    const cats = document.querySelectorAll('input[name="categorias[]"]:checked');
    const erroCats = document.getElementById('erroCategorias');
    if (cats.length === 0) { erroCats.classList.add('visivel'); valido = false; }
    else                   { erroCats.classList.remove('visivel'); }

    // Tipo
    const tipo = document.getElementById('tipo_feedback');
    const erroTipo = document.getElementById('erroTipo');
    if (!tipo.value) {
      tipo.classList.add('erro'); erroTipo.classList.add('visivel'); valido = false;
    } else {
      tipo.classList.remove('erro'); erroTipo.classList.remove('visivel');
    }

    // Mensagem
    const msg = document.getElementById('mensagem_feedback');
    const erroMsg = document.getElementById('erroMensagem');
    if (msg.value.trim().length < 20) {
      msg.classList.add('erro'); erroMsg.classList.add('visivel'); valido = false;
    } else {
      msg.classList.remove('erro'); erroMsg.classList.remove('visivel');
    }

    if (!valido) {
      mostrarToast('Corrija os campos em destaque.', 'falha');
      return;
    }

    // Loading e envio
    const btn = document.getElementById('btnSubmit');
    btn.classList.add('loading');

    const data = new FormData(form);
    fetch('auth-feedback.php', { method: 'POST', body: data })
      .then(r => r.json())
      .then(res => {
        btn.classList.remove('loading');
        if (res.sucesso) {
          mostrarToast('Feedback enviado! Obrigado 🎉', 'sucesso');
          form.reset();
        } else {
          mostrarToast(res.mensagem || 'Erro ao enviar. Tente novamente.', 'falha');
        }
      })
      .catch(() => {
        btn.classList.remove('loading');
        mostrarToast('Erro de conexão. Tente novamente.', 'falha');
      });
  });

  /* ─── ANÔNIMO: oculta nome/turma/email ─── */
  document.getElementById('anonimo').addEventListener('change', function() {
    const campos = ['nome_feedback', 'turma_feedback', 'email_feedback'];
    campos.forEach(id => {
      const el = document.getElementById(id);
      el.closest('.form-grupo').style.opacity = this.checked ? '0.4' : '1';
      el.disabled = this.checked;
    });
    // Também a .form-row que contém nome e turma
    const row = document.querySelector('.form-row');
    if (row) row.style.opacity = this.checked ? '0.4' : '1';
  });