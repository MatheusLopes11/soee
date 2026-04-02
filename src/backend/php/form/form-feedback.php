<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Feedback — SOEE</title>
  <link rel="stylesheet" href="/soee/src/frontend/css/form-feedback.css">
  <link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

</head>
<body>

<!-- CURSOR -->
<div class="cursor-dot" id="cursorDot"></div>
<div class="cursor-ring" id="cursorRing"></div>

<!-- LOADER -->
<div id="loader">
  <div class="loader-inner">
    <div class="loader-logo-text">SOEE</div>
    <div class="loader-logo-sub">Carregando...</div>
    <div class="loader-bar"><div class="loader-bar-fill"></div></div>
  </div>
</div>

<!-- HEADER -->
<header class="cabecalho">
  <div class="cabecalho-container">
    <div class="cabecalho-logos">
      <a href="index.php" class="logo-texto">S<span>O</span>EE</a>
    </div>
    <nav class="menu-principal">
      <ul class="menu-lista">
        <li><a href="/soee/src/backend/php/pages/inicio.php">Início</a></li>
        <li><a href="form-feedback.php" aria-current="page">Feedback</a></li>
      </ul>
    </nav>
    <div class="cabecalho-acoes">
      <button class="botao-icone" id="toggleTema" title="Alternar tema" aria-label="Alternar tema">
        <i class="fa-solid fa-moon"></i>
      </button>
    </div>
  </div>
</header>

<!-- HERO -->
<section class="hero-feedback">
  <div class="bg"></div>
  <div class="grid"></div>
  <div class="hero-feedback-inner">
    <span class="badge"><i class="fa-solid fa-circle fa-xs"></i> Sua opinião importa</span>
    <h1>Nos conte sua <em>experiência</em></h1>
    <p>Seu feedback nos ajuda a melhorar o SOEE e tornar os interclasses ainda mais organizados e incríveis.</p>
  </div>
</section>

<!-- CONTEÚDO PRINCIPAL -->
<main>
  <div class="feedback-layout">

    <!-- FORMULÁRIO -->
    <div class="form-card">

      <?php
      // Exibe mensagens vindas do auth-feedback.php via redirect
      if (!empty($_GET['status'])):
        $status = htmlspecialchars($_GET['status']);
        if ($status === 'sucesso'): ?>
          <div class="alerta-php sucesso">
            <i class="fa-solid fa-circle-check"></i>
            Feedback enviado com sucesso! Obrigado pela sua contribuição.
          </div>
        <?php elseif ($status === 'erro'): ?>
          <div class="alerta-php erro">
            <i class="fa-solid fa-circle-xmark"></i>
            <?= !empty($_GET['msg']) ? htmlspecialchars(urldecode($_GET['msg'])) : 'Ocorreu um erro ao enviar. Tente novamente.' ?>
          </div>
        <?php endif;
      endif; ?>

      <h2 class="form-titulo">Deixe seu Feedback</h2>
      <p class="form-subtitulo">Preencha o formulário abaixo. Campos com <span style="color:var(--laranja-destaque)">*</span> são obrigatórios.</p>

      <form id="feedbackForm" action="/soee/src/backend/php/auth/auth-feedback.php" method="POST" novalidate>

        <!-- AVALIAÇÃO GERAL -->
        <div class="rating-section">
          <span class="rating-label">Avaliação Geral <span style="color:var(--laranja-destaque)">*</span></span>
          <div class="stars-group" id="starsGroup">
            <input type="radio" name="nota_feedback" id="star5" value="5" required>
            <label for="star5" title="Excelente">★</label>
            <input type="radio" name="nota_feedback" id="star4" value="4">
            <label for="star4" title="Bom">★</label>
            <input type="radio" name="nota_feedback" id="star3" value="3">
            <label for="star3" title="Regular">★</label>
            <input type="radio" name="nota_feedback" id="star2" value="2">
            <label for="star2" title="Ruim">★</label>
            <input type="radio" name="nota_feedback" id="star1" value="1">
            <label for="star1" title="Péssimo">★</label>
          </div>
          <span class="msg-erro" id="erroNota">Por favor, selecione uma avaliação.</span>
        </div>

        <!-- NOME E TURMA -->
        <div class="form-row">
          <div class="form-grupo">
            <label class="form-label" for="nome_feedback">Nome <span>*</span></label>
            <input type="text" id="nome_feedback" name="nome_feedback" class="form-input"
              placeholder="Seu nome completo"
              value="<?= htmlspecialchars($_GET['nome'] ?? '') ?>" />
            <span class="msg-erro" id="erroNome">Informe seu nome.</span>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="turma_feedback">Turma <span>*</span></label>
            <select id="turma_feedback" name="turma_feedback" class="form-select">
              <option value="">Selecione sua turma</option>
              <option value="1 MTEC">1 MTEC</option>
              <option value="2 MTEC">2 MTEC</option>
              <option value="3 MTEC">3 MTEC</option>
              <option value="1 EMIF">1 EMIF</option>
              <option value="2 EMIF">2 EMIF</option>
              <option value="3 EMIF">3 EMIF</option>
              <option value="1 PI">1 PI</option>
              <option value="2 PI">2 PI</option>
              <option value="3 PI">3 PI</option>
            </select>
            <span class="msg-erro" id="erroTurma">Selecione sua turma.</span>
          </div>
        </div>

        <!-- EMAIL -->
        <div class="form-grupo">
          <label class="form-label" for="email_feedback">E-mail <span>*</span></label>
          <input type="email" id="email_feedback" name="email_feedback" class="form-input"
            placeholder="seuemail@exemplo.com"
            value="<?= htmlspecialchars($_GET['email'] ?? '') ?>" />
          <span class="msg-erro" id="erroEmail">Informe um e-mail válido.</span>
        </div>

        <!-- CATEGORIA -->
        <div class="form-grupo">
          <label class="form-label">Categorias <span>*</span></label>
          <div class="categorias-grid">
            <div class="categoria-item">
              <input type="checkbox" id="cat_organizacao" name="categorias[]" value="organizacao">
              <label for="cat_organizacao"><i class="fa-solid fa-list-check"></i> Organização</label>
            </div>
            <div class="categoria-item">
              <input type="checkbox" id="cat_sistema" name="categorias[]" value="sistema">
              <label for="cat_sistema"><i class="fa-solid fa-laptop"></i> Sistema (SOEE)</label>
            </div>
            <div class="categoria-item">
              <input type="checkbox" id="cat_comunicacao" name="categorias[]" value="comunicacao">
              <label for="cat_comunicacao"><i class="fa-solid fa-comments"></i> Comunicação</label>
            </div>
            <div class="categoria-item">
              <input type="checkbox" id="cat_arbitragem" name="categorias[]" value="arbitragem">
              <label for="cat_arbitragem"><i class="fa-solid fa-whistle"></i> Arbitragem</label>
            </div>
            <div class="categoria-item">
              <input type="checkbox" id="cat_inscricoes" name="categorias[]" value="inscricoes">
              <label for="cat_inscricoes"><i class="fa-solid fa-user-plus"></i> Inscrições</label>
            </div>
            <div class="categoria-item">
              <input type="checkbox" id="cat_outro" name="categorias[]" value="outro">
              <label for="cat_outro"><i class="fa-solid fa-ellipsis"></i> Outro</label>
            </div>
          </div>
          <span class="msg-erro" id="erroCategorias">Selecione ao menos uma categoria.</span>
        </div>

        <!-- TIPO -->
        <div class="form-grupo">
          <label class="form-label" for="tipo_feedback">Tipo de Feedback <span>*</span></label>
          <select id="tipo_feedback" name="tipo_feedback" class="form-select">
            <option value="">Selecione o tipo</option>
            <option value="elogio">😊 Elogio</option>
            <option value="sugestao">💡 Sugestão</option>
            <option value="critica">🔧 Crítica Construtiva</option>
            <option value="problema">⚠️ Relatar Problema</option>
          </select>
          <span class="msg-erro" id="erroTipo">Selecione o tipo de feedback.</span>
        </div>

        <!-- MENSAGEM -->
        <div class="form-grupo">
          <label class="form-label" for="mensagem_feedback">Mensagem <span>*</span></label>
          <textarea id="mensagem_feedback" name="mensagem_feedback" class="form-textarea"
            placeholder="Descreva sua experiência, sugestão ou problema com o máximo de detalhes possível..."></textarea>
          <span class="msg-erro" id="erroMensagem">Escreva sua mensagem (mínimo 20 caracteres).</span>
        </div>

        <!-- IDENTIFICAÇÃO ANÔNIMA -->
        <div class="form-grupo" style="display:flex; align-items:center; gap:10px; margin-bottom:0;">
          <input type="checkbox" id="anonimo" name="anonimo" value="1"
            style="width:18px;height:18px;accent-color:var(--laranja-destaque);cursor:pointer;">
          <label for="anonimo" style="font-size:0.9rem; color:var(--texto-secundario); cursor:pointer; user-select:none;">
            Enviar de forma <strong style="color:var(--texto-principal)">anônima</strong>
          </label>
        </div>

        <button type="submit" class="botao-submit" id="btnSubmit">
          <div class="btn-spinner"></div>
          <span class="btn-texto"><i class="fa-solid fa-paper-plane"></i> &nbsp;Enviar Feedback</span>
        </button>

      </form>
    </div><!-- /form-card -->

    <!-- SIDEBAR -->
    <aside class="sidebar">

      <div class="info-card">
        <h3 class="info-card-titulo"><i class="fa-solid fa-circle-info"></i> Por que dar feedback?</h3>
        <div class="info-item">
          <i class="fa-solid fa-bullseye"></i>
          <div>
            <strong>Melhora contínua</strong>
            <span>Sua opinião guia as atualizações do sistema SOEE.</span>
          </div>
        </div>
        <div class="info-item">
          <i class="fa-solid fa-users"></i>
          <div>
            <strong>Comunidade mais forte</strong>
            <span>Todos os alunos da ETEC JK se beneficiam das melhorias.</span>
          </div>
        </div>
        <div class="info-item">
          <i class="fa-solid fa-shield-halved"></i>
          <div>
            <strong>Privacidade garantida</strong>
            <span>Você pode enviar anonimamente, sem identificação.</span>
          </div>
        </div>
        <div class="info-item">
          <i class="fa-solid fa-clock"></i>
          <div>
            <strong>Rápido e simples</strong>
            <span>Leva menos de 2 minutos para preencher.</span>
          </div>
        </div>
      </div>

      <div class="destaque-card">
        <p>"A organização esportiva escolar precisa garantir clareza, ordem e participação equitativa para que cumpra seu papel educacional."</p>
        <cite>— Soares e Montagner, 2007</cite>
      </div>

      <div class="info-card">
        <h3 class="info-card-titulo"><i class="fa-solid fa-trophy"></i> Sobre o SOEE</h3>
        <p style="font-size:0.88rem; color:var(--texto-secundario); line-height:1.7;">
          O <strong style="color:var(--azul-principal)">Sistema de Organização Esportiva Escolar</strong> foi desenvolvido por alunos da ETEC Juscelino Kubitschek de Oliveira para automatizar inscrições, cronogramas e resultados dos interclasses.
        </p>
        <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--borda-sutil); font-size:0.8rem; color:var(--texto-secundario);">
          <i class="fa-solid fa-code" style="color:var(--laranja-destaque);"></i>&nbsp;
          HTML · CSS · JavaScript · PHP · MySQL
        </div>
      </div>

    </aside>
  </div>
</main>

<!-- RODAPÉ -->
<footer class="rodape">
  <div class="rodape-direitos">
    &copy; <?= date('Y') ?> <a href="#">SOEE</a> — ETEC Juscelino Kubitschek de Oliveira · Diadema/SP
  </div>
</footer>

<!-- TOAST -->
<div class="toast" id="toast">
  <i></i>
  <span id="toastMsg"></span>
</div>

<script>
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
</script>
</body>
</html>