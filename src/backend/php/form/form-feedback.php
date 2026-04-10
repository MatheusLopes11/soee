<?php include __DIR__ . '/../include/doctype.php';?>
  <head>
    <title>Feedback — SOEE</title>
    <link rel="stylesheet" href="/soee/src/frontend/css/form-feedback.css">
    <?php include __DIR__ . '/../include/head-data.php';?>
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
        <li><a href="/soee/src/backend/php/pages/modalidades.php">Modalidades</a></li>
        <li><a href="/soee/src/backend/php/pages/sobre-etec.php">Sobre a ETEC</a></li>
        <li><a href="/soee/src/backend/php/pages/quem-somos.php">Quem Somos</a></li>
        <li><a href="/soee/src/backend/php/pages/contato-redes.php">Contato & Redes</a></li>
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

      <h2 class="form-titulo">Deixe seu Feedback</h2>
      <p class="form-subtitulo">Preencha o formulário abaixo. Campos com <span style="color:var(--laranja-destaque)">*</span> são obrigatórios.</p>

      <form action="/soee/src/backend/php/auth/auth-feedback.php" method="POST" id="feedbackForm">

        <!-- NOME E TURMA -->
        <div class="form-row">
          <div class="form-grupo">

            <label class="form-label" for="nome_feedback">Nome <span>*</span></label>
            <input type="text" id="nome_feedback" name="nome_feedback" class="form-input" placeholder="Seu nome completo"
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
            <option value="elogio">Elogio</option>
            <option value="sugestao">Sugestão</option>
            <option value="critica">Crítica Construtiva</option>
            <option value="problema">Relatar Problema</option>
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

        <div class="submit">
          <input type="submit" id="btnSubmit" value="Enviar Feedback">
        </div>

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
          <i class="fa-solid fa-code" style="color:var(--laranja-destaque);"></i>
          HTML · CSS · JavaScript · PHP · SQL
        </div>
      </div>

    </aside>
  </div>
</main>

<script src="/soee/src/frontend/js/form-feedback.js"></script>

<?php include __DIR__ . '/../include/end.php';?>