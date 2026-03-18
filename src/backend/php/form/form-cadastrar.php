<!DOCTYPE html>
<html lang="pt-br" data-theme="light">
  <head>
<!-- (Título) -->
    <title>Criar Conta — SOEE</title>

<!-- (Meta Dados) -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- (Línks) -->
    <link rel="stylesheet" href="/soee/src/frontend/css/inicio.css">
    <link rel="stylesheet" href="/soee/src/frontend/css/cadastrar.css">
    <link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:wght@300;400;500;600;700&display=swap"   rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
    
  </head>
<body>

  <!-- Cursor personalizado -->
  <div class="cursor-dot" id="cursorDot"></div>
  <div class="cursor-ring" id="cursorRing"></div>

  <!-- Loader -->
  <div id="loader">
    <div class="loader-inner">
      <div class="loader-logo-text">SOEE</div>
      <div class="loader-logo-sub">Preparando cadastro</div>
      <div class="loader-bar">
        <div class="loader-bar-fill"></div>
      </div>
    </div>
  </div>

  <div class="cad-scene">

    <!-- Fundo animado (mesmo DNA do hero) -->
    <div class="cad-bg"></div>
    <div class="cad-grid"></div>
    <div class="cad-particles">
      <span></span><span></span><span></span><span></span><span></span>
    </div>

    <!-- Painel esquerdo — identidade visual -->
    <aside class="cad-lateral" aria-hidden="true">
      <div class="cad-lateral-inner">

        <a href="/soee/src/backend/php/pages/inicio.php" class="cad-back-link">
          <i class="fa-solid fa-arrow-left"></i>
          Voltar ao início
        </a>

        <div class="cad-brand">
          <div class="cad-brand-logo">
            <img src="/soee/src/images/logo-soee.png" alt="SOEE">
          </div>
          <h2 class="cad-brand-nome">SOEE</h2>
          <p class="cad-brand-tagline">Sistema de Organização de<br>Esportes Escolares</p>
        </div>

        <ul class="cad-beneficios">
          <li>
            <div class="cad-ben-icone"><i class="fa-solid fa-trophy"></i></div>
            <div>
              <strong>Participe dos interclasses</strong>
              <span>Inscreva seu time e acompanhe resultados ao vivo</span>
            </div>
          </li>
          <li>
            <div class="cad-ben-icone"><i class="fa-solid fa-calendar-check"></i></div>
            <div>
              <strong>Cronograma em tempo real</strong>
              <span>Veja horários, quadras e confrontos atualizados</span>
            </div>
          </li>
          <li>
            <div class="cad-ben-icone"><i class="fa-solid fa-medal"></i></div>
            <div>
              <strong>Placar e classificação</strong>
              <span>Tabelas e estatísticas de cada modalidade</span>
            </div>
          </li>
          <li>
            <div class="cad-ben-icone"><i class="fa-solid fa-bell"></i></div>
            <div>
              <strong>Notificações instantâneas</strong>
              <span>Avisos de partidas, mudanças e resultados</span>
            </div>
          </li>
        </ul>

        <div class="cad-etec-badge">
          <i class="fa-solid fa-school"></i>
          ETEC Juscelino Kubitschek de Oliveira
        </div>

      </div>
    </aside>

    <!-- Painel direito — formulário -->
    <main class="cad-main">
      <div class="cad-card" role="main">

        <!-- Cabeçalho do card -->
        <div class="cad-card-header">
          <div class="cad-step-track" aria-label="Progresso do formulário">
            <div class="cad-step active" data-step="1">
              <div class="cad-step-circle"><i class="fa-solid fa-user"></i></div>
              <span>Dados</span>
            </div>
            <div class="cad-step-line"></div>
            <div class="cad-step" data-step="2">
              <div class="cad-step-circle"><i class="fa-solid fa-lock"></i></div>
              <span>Segurança</span>
            </div>
            <div class="cad-step-line"></div>
            <div class="cad-step" data-step="3">
              <div class="cad-step-circle"><i class="fa-solid fa-check"></i></div>
              <span>Confirmar</span>
            </div>
          </div>
        </div>

        <!-- Formulário multi-step -->
        <form action="auth-cadastrar.php" method="POST" id="formCadastro" novalidate>

          <!-- ── STEP 1: Dados pessoais ── -->
          <div class="cad-passo" id="passo1">
            <div class="cad-passo-titulo">
              <div class="cad-passo-num">01</div>
              <div>
                <h1>Dados pessoais</h1>
                <p>Como devemos te chamar?</p>
              </div>
            </div>

            <div class="form-grupo" id="grupo-nome">
              <label for="nome">
                <i class="fa-solid fa-user-pen"></i>
                Nome completo
              </label>
              <div class="input-wrapper">
                <input
                  type="text"
                  id="nome"
                  name="nome"
                  placeholder="Ex.: João da Silva"
                  autocomplete="name"
                  required
                >
                <div class="input-status">
                  <i class="fa-solid fa-circle-check status-ok"></i>
                  <i class="fa-solid fa-circle-xmark status-erro"></i>
                </div>
              </div>
              <span class="campo-msg"></span>
            </div>

            <div class="form-grupo" id="grupo-email">
              <label for="email">
                <i class="fa-solid fa-envelope"></i>
                E-mail
              </label>
              <div class="input-wrapper">
                <input
                  type="email"
                  id="email"
                  name="email"
                  placeholder="Ex.: joao@email.com"
                  autocomplete="email"
                  required
                >
                <div class="input-status">
                  <i class="fa-solid fa-circle-check status-ok"></i>
                  <i class="fa-solid fa-circle-xmark status-erro"></i>
                </div>
              </div>
              <span class="campo-msg"></span>
            </div>

            <div class="form-grupo" id="grupo-genero">
              <label for="opcoes">
                <i class="fa-solid fa-venus-mars"></i>
                Gênero
              </label>
              <div class="input-wrapper select-wrapper">
                <select name="genero" id="opcoes" required>
                  <option value="" disabled selected>Selecione…</option>
                  <option value="m">Masculino</option>
                  <option value="f">Feminino</option>
                  <option value="outro">Prefiro não informar / Outro</option>
                </select>
                <i class="fa-solid fa-chevron-down select-arrow"></i>
              </div>
            </div>

            <div id="campoExtra" class="form-grupo campo-extra" style="display:none">
              <label for="genero_outro">
                <i class="fa-solid fa-pen"></i>
                Como você se identifica?
              </label>
              <div class="input-wrapper">
                <input
                  type="text"
                  id="genero_outro"
                  name="genero_outro"
                  placeholder="Escreva como preferir"
                  maxlength="80"
                >
              </div>
            </div>

            <button type="button" class="botao-proximo" id="btnP1">
              Continuar
              <i class="fa-solid fa-arrow-right"></i>
            </button>
          </div>

          <!-- ── STEP 2: Segurança ── -->
          <div class="cad-passo hidden" id="passo2">
            <div class="cad-passo-titulo">
              <div class="cad-passo-num">02</div>
              <div>
                <h1>Segurança</h1>
                <p>Crie uma senha forte para proteger sua conta</p>
              </div>
            </div>

            <div class="form-grupo" id="grupo-senha">
              <label for="senha">
                <i class="fa-solid fa-lock"></i>
                Senha
              </label>
              <div class="input-wrapper">
                <input
                  type="password"
                  id="senha"
                  name="senha"
                  placeholder="Mínimo 8 caracteres"
                  autocomplete="new-password"
                  required
                >
                <button type="button" class="toggle-senha" aria-label="Ver senha" tabindex="-1">
                  <i class="fa-solid fa-eye" id="icone-senha"></i>
                </button>
              </div>
              <span class="campo-msg"></span>

              <!-- Medidor de força -->
              <div class="forca-barra" aria-label="Força da senha">
                <div class="forca-fill" id="forcaFill"></div>
              </div>
              <div class="forca-label" id="forcaLabel">
                <span id="forcaTxt">—</span>
                <span id="forcaDica" class="forca-dica"></span>
              </div>
              <ul class="senha-requisitos" id="requisitos">
                <li id="req-len"><i class="fa-solid fa-circle"></i> Mínimo 8 caracteres</li>
                <li id="req-upper"><i class="fa-solid fa-circle"></i> Uma letra maiúscula</li>
                <li id="req-num"><i class="fa-solid fa-circle"></i> Um número</li>
                <li id="req-special"><i class="fa-solid fa-circle"></i> Um caractere especial</li>
              </ul>
            </div>

            <div class="form-grupo" id="grupo-confirma">
              <label for="confirma_senha">
                <i class="fa-solid fa-lock-open"></i>
                Confirmar senha
              </label>
              <div class="input-wrapper">
                <input
                  type="password"
                  id="confirma_senha"
                  name="confirma_senha"
                  placeholder="Repita a senha"
                  autocomplete="new-password"
                  required
                >
                <button type="button" class="toggle-senha" aria-label="Ver confirmação" tabindex="-1">
                  <i class="fa-solid fa-eye"></i>
                </button>
              </div>
              <span class="campo-msg"></span>
            </div>

            <div class="cad-passo-nav">
              <button type="button" class="botao-voltar" id="btnVoltarP2">
                <i class="fa-solid fa-arrow-left"></i>
                Voltar
              </button>
              <button type="button" class="botao-proximo" id="btnP2">
                Continuar
                <i class="fa-solid fa-arrow-right"></i>
              </button>
            </div>
          </div>

          <!-- ── STEP 3: Confirmação ── -->
          <div class="cad-passo hidden" id="passo3">
            <div class="cad-passo-titulo">
              <div class="cad-passo-num">03</div>
              <div>
                <h1>Confirmar dados</h1>
                <p>Revise antes de criar sua conta</p>
              </div>
            </div>

            <div class="cad-resumo" id="resumoBox">
              <div class="resumo-linha">
                <span><i class="fa-solid fa-user-pen"></i> Nome</span>
                <strong id="r-nome">—</strong>
              </div>
              <div class="resumo-linha">
                <span><i class="fa-solid fa-envelope"></i> E-mail</span>
                <strong id="r-email">—</strong>
              </div>
              <div class="resumo-linha">
                <span><i class="fa-solid fa-venus-mars"></i> Gênero</span>
                <strong id="r-genero">—</strong>
              </div>
              <div class="resumo-linha">
                <span><i class="fa-solid fa-lock"></i> Senha</span>
                <strong>••••••••</strong>
              </div>
            </div>

            <label class="cad-termo">
              <input type="checkbox" name="aceite_termos" id="aceite_termos" required>
              <span class="cad-termo-check"><i class="fa-solid fa-check"></i></span>
              <span>Concordo com os <a href="#">termos de uso</a> e <a href="#">política de privacidade</a> do SOEE</span>
            </label>

            <div class="cad-passo-nav">
              <button type="button" class="botao-voltar" id="btnVoltarP3">
                <i class="fa-solid fa-arrow-left"></i>
                Voltar
              </button>
              <button type="submit" class="botao-cadastrar" id="btnSubmit">
                <span class="btn-txt"><i class="fa-solid fa-user-plus"></i> Criar conta</span>
                <span class="btn-loading" style="display:none">
                  <span class="spinner"></span> Criando…
                </span>
              </button>
            </div>
          </div>

        </form>

        <!-- Rodapé do card -->
        <div class="cad-card-footer">
          <span>Já tem conta?</span>
          <a href="/soee/index.php">
            <i class="fa-solid fa-arrow-right-to-bracket"></i>
            Entrar agora
          </a>
        </div>

      </div>
    </main>

  </div><!-- /.cad-scene -->

        <script src="/soee/src/frontend/js/inicio.js"></script>
      <script src="/soee/src/frontend/js/cadastrar.js"></script>
  </body>
</html>