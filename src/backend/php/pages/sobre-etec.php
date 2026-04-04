<?php include __DIR__ . '/../include/doctype.php';?>
<head>
  <title>Etec</title>
    <link rel="stylesheet" href="/soee/src/frontend/css/sobre-etec.css">
    <link rel="stylesheet" href="/soee/src/frontend/css/inicio.css">
  <?php include __DIR__ . '/../include/head-data.php';?>
</head>

<body>

  <div class="cursor-dot" id="cursorDot"></div>
  <div class="cursor-ring" id="cursorRing"></div>

  <!-- ─── HEADER ─── -->
  <header class="cabecalho">
    <div class="cabecalho-container">
      <div class="cabecalho-logos">
        <img src="/soee/src/images/logo-jk.png"  alt="ETEC Juscelino Kubitschek de Oliveira">
        <img src="/soee/src/images/logo-cps.png" alt="Centro Paula Souza">
      </div>

      <nav class="menu-principal" aria-label="Menu principal">
        <ul class="menu-lista">
          <li><a href="/soee/src/backend/php/pages/inicio.php">Início</a></li>
          <li><a href="/soee/src/backend/php/pages/modalidades.php">Modalidades</a></li>
          <li><a href="/soee/src/backend/php/pages/quem-somos.php">Quem Somos</a></li>
          <li><a href="/soee/src/backend/php/pages/contato-redes.php">Contato & Redes</a></li>
          <li><a href="/soee/src/backend/php/form/form-feedback.php">Feedback</a></li>
        </ul>
      </nav>

      <div class="cabecalho-acoes">
        <button id="toggle-theme" class="botao-icone" aria-label="Alternar tema">
          <i class="fa-solid fa-moon"></i>
        </button>
        <a href="/soee/index.php" class="botao-login">
          <i class="fa-solid fa-user"></i>
          Entrar
        </a>
        <img src="/soee/src/images/logo-soee.png" alt="SOEE" class="logo-sistema">
      </div>
    </div>
  </header>

  <main>

    <section class="pagina">
      <div class="pagina-bg"></div>
      <div class="pagina-grid"></div>
      <div class="pagina-conteudo">
        <div class="badge">
          <i class="fa-solid fa-school"></i>
          Centro Paula Souza
        </div>
        <h1>ETEC <em>Juscelino Kubitschek</em></h1>
        <p>Conheça a escola que inspira o SOEE — formando profissionais e cidadãos em Diadema desde 1993.</p>
      </div>
      <div class="pagina-onda">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z" fill="var(--fundo-pagina)"/>
        </svg>
      </div>
    </section>

    <!-- ─── SOBRE A ESCOLA ─── -->
    <section class="sobre-escola-section">
      <div class="sobre-escola-container">

        <div class="sobre-escola-grid reveal">
          <div class="sobre-escola-texto">
            
            <h2>Uma escola de <em>referência</em> em Diadema</h2>
            <p>
              A ETEC Juscelino Kubitschek de Oliveira é uma unidade de ensino do
              <strong>Centro Paula Souza</strong>, autarquia do Governo do Estado de São Paulo
              responsável pela gestão das Escolas Técnicas Estaduais (ETECs) e das Faculdades
              de Tecnologia (FATECs).
            </p>
            <p>
              Localizada no bairro Serraria, em Diadema/SP, a escola oferece cursos técnicos
              de qualidade reconhecida no mercado, formando profissionais prontos para os
              desafios das suas áreas de atuação. O compromisso da ETEC JK vai além da sala
              de aula — aqui, os alunos também vivenciam cultura, esporte e convivência.
            </p>
            <p>
              É nesse espírito de comunidade ativa que nasceu o <strong>SOEE</strong>:
              um projeto desenvolvido pelos próprios alunos para modernizar e celebrar
              o esporte dentro da escola.
            </p>
          </div>
          <div class="sobre-escola-destaque">
            <div class="destaque-card">
              <i class="fa-solid fa-graduation-cap"></i>
              <strong>6</strong>
              <span>Cursos oferecidos</span>
            </div>
            <div class="destaque-card">
              <i class="fa-solid fa-clock"></i>
              <strong>1200h</strong>
              <span>Carga horária técnica</span>
            </div>
            <div class="destaque-card">
              <i class="fa-solid fa-calendar-days"></i>
              <strong>3</strong>
              <span>Semestres por curso</span>
            </div>
            <div class="destaque-card destaque-card-full">
              <i class="fa-solid fa-star"></i>
              <strong>Centro Paula Souza</strong>
              <span>Autarquia do Governo do Estado de SP</span>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- ─── CURSOS ─── -->
    <section class="cursos-section">
      <div class="cursos-container">

        <header class="secao-cabecalho reveal">
          <div class="secao-tag">Formação profissional</div>
          <h2>Cursos Oferecidos</h2>
          <p>Formação técnica de qualidade para o mercado de trabalho</p>
        </header>

        <div class="cursos-grid">

          <article class="curso-card reveal reveal-delay-1">
            <div class="curso-icone">
              <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="curso-corpo">
              <div class="curso-tipo">Técnico</div>
              <h3>Administração</h3>
              <p>
                Prepara o aluno para atuar na organização e gestão de empresas,
                com conteúdos de finanças, marketing, recursos humanos, vendas
                e planejamento empresarial.
              </p>
              <div class="curso-detalhes">
                <span><i class="fa-solid fa-hourglass-half"></i> ~1200 horas</span>
                <span><i class="fa-solid fa-calendar"></i> 3 semestres</span>
              </div>
            </div>
          </article>

          <article class="curso-card reveal reveal-delay-2">
            <div class="curso-icone">
              <i class="fa-solid fa-code"></i>
            </div>
            <div class="curso-corpo">
              <div class="curso-tipo">Técnico</div>
              <h3>Desenvolvimento de Sistemas</h3>
              <p>
                Voltado para tecnologia, ensina programação, banco de dados,
                análise de sistemas, desenvolvimento de aplicativos e testes
                de software. O curso do time do SOEE!
              </p>
              <div class="curso-detalhes">
                <span><i class="fa-solid fa-hourglass-half"></i> ~1200 horas</span>
                <span><i class="fa-solid fa-calendar"></i> 3 semestres</span>
              </div>
            </div>
            <div class="curso-destaque-badge">
              <i class="fa-solid fa-star"></i> Nosso curso
            </div>
          </article>

          <article class="curso-card reveal reveal-delay-3">
            <div class="curso-icone">
              <i class="fa-solid fa-desktop"></i>
            </div>
            <div class="curso-corpo">
              <div class="curso-tipo">Técnico</div>
              <h3>Finanças</h3>
              <p>
              Capacita profissionais para realizar operações de tesouraria, contas a pagar/receber, análise de crédito, orçamentos e fluxo de caixa. 
              </p>
              <div class="curso-detalhes">
                <span><i class="fa-solid fa-hourglass-half"></i> ~1200 horas</span>
                <span><i class="fa-solid fa-calendar"></i> 3 semestres</span>
              </div>
            </div>
          </article>

          <article class="curso-card reveal reveal-delay-4">
            <div class="curso-icone">
              <i class="fa-solid fa-globe"></i>
            </div>
            <div class="curso-corpo">
              <div class="curso-tipo">Técnico</div>
              <h3>Recursos Humanos</h3>
              <p>
              Prepara profissionais para atuar na gestão de pessoas, abrangendo recrutamento e seleção, treinamento, departamento pessoal (folha de pagamento, benefícios, admissão/demissão) e avaliação de desempenho.
              </p>
              <div class="curso-detalhes">
                <span><i class="fa-solid fa-hourglass-half"></i> ~1200 horas</span>
                <span><i class="fa-solid fa-calendar"></i> 3 semestres</span>
              </div>
            </div>
          </article>

          <article class="curso-card reveal reveal-delay-1">
            <div class="curso-icone">
              <i class="fa-solid fa-truck"></i>
            </div>
            <div class="curso-corpo">
              <div class="curso-tipo">Técnico</div>
              <h3>Logística</h3>
              <p>
                Ensina a planejar e controlar transporte, armazenamento e
                distribuição de produtos, com gestão de estoques, cadeia de
                suprimentos e processos logísticos.
              </p>
              <div class="curso-detalhes">
                <span><i class="fa-solid fa-hourglass-half"></i> ~1200 horas</span>
                <span><i class="fa-solid fa-calendar"></i> 3 semestres</span>
              </div>
            </div>
          </article>

          <article class="curso-card curso-card-especial reveal reveal-delay-2">
            <div class="curso-icone">
              <i class="fa-solid fa-book-open"></i>
            </div>
            <div class="curso-corpo">
              <div class="curso-tipo curso-tipo-especial">Ensino Médio Integrado</div>
              <h3>Administração + Ensino Médio</h3>
              <p>
                O aluno cursa o ensino médio e o técnico simultaneamente,
                saindo com os dois diplomas ao final — uma formação completa
                em 3 anos.
              </p>
              <div class="curso-detalhes">
                <span><i class="fa-solid fa-hourglass-half"></i> ~3000–3600 horas</span>
                <span><i class="fa-solid fa-calendar"></i> 6 semestres</span>
              </div>
            </div>
          </article>

          <article class="curso-card curso-card-especial reveal reveal-delay-2">
            <div class="curso-icone">
              <i class="fa-solid fa-book-open"></i>
            </div>
            <div class="curso-corpo">
              <div class="curso-tipo curso-tipo-especial">Ensino Médio com Habilitação Profissional de Técnico em Período Integral</div>
              <h3>MTec-PI</h3>
              <p>
             Integra o Ensino Médio à formação técnica em uma jornada de até 8 aulas diárias, totalizando 3 anos. O aluno obtém o diploma do ensino médio e do técnico simultaneamente, com foco prático e inserção no mercado de trabalho.
              </p>
              <div class="curso-detalhes">
                <span><i class="fa-solid fa-hourglass-half"></i> ~3000–3600 horas</span>
                <span><i class="fa-solid fa-calendar"></i> 6 semestres</span>
              </div>
            </div>
          </article>

          <article class="curso-card curso-card-especial reveal reveal-delay-2">
            <div class="curso-icone">
              <i class="fa-solid fa-book-open"></i>
            </div>
            <div class="curso-corpo">
              <div class="curso-tipo curso-tipo-especial">Ensino Médio com Itinerário Formativo</div>
              <h3>EMIF</h3>
              <p>
                EMIF (Ensino Médio com Itinerário Formativo) nas Etecs é uma modalidade que une a Base Nacional Comum Curricular (BNCC) a um aprofundamento específico em Ciências da Natureza ou Humanas
              </p>
              <div class="curso-detalhes">
                <span><i class="fa-solid fa-hourglass-half"></i> ~3000–3600 horas</span>
                <span><i class="fa-solid fa-calendar"></i> 6 semestres</span>
              </div>
            </div>
          </article>

        </div>
      </div>
    </section>

    <!-- ─── INFORMAÇÕES / CONTATO ─── -->
    <section class="info-section">
      <div class="info-container">

        <header class="secao-cabecalho reveal">
          <div class="secao-tag">Visite-nos</div>
          <h2>Informações & Contato</h2>
          <p>Encontre a ETEC JK e entre em contato</p>
        </header>

        <div class="info-grid">

          <!-- Endereço -->
          <div class="info-card reveal reveal-delay-1">
            <div class="info-icone">
              <i class="fa-solid fa-location-dot"></i>
            </div>
            <h3>Endereço</h3>
            <p>Rua Guarani, 735</p>
            <p>Serraria — Diadema/SP</p>
            <p>CEP: 09991-060</p>
            <a
              href="https://maps.google.com/?q=Rua+Guarani,+735,+Serraria,+Diadema,+SP"
              target="_blank"
              class="info-link"
            >
              <i class="fa-solid fa-map"></i> Ver no Maps
            </a>
          </div>

          <!-- Telefone -->
          <div class="info-card reveal reveal-delay-2">
            <div class="info-icone">
              <i class="fa-solid fa-phone"></i>
            </div>
            <h3>Telefone</h3>
            <p>(11) 4053-9400</p>
            <a href="tel:+551140539400" class="info-link">
              <i class="fa-solid fa-phone-flip"></i> Ligar agora
            </a>
          </div>

          <!-- Horários -->
          <div class="info-card reveal reveal-delay-3">
            <div class="info-icone">
              <i class="fa-solid fa-clock"></i>
            </div>
            <h3>Horário de Funcionamento</h3>
            <div class="horario-lista">
              <div class="horario-item">
                <span class="horario-periodo">Manhã</span>
                <span class="horario-horas">09h às 11h</span>
              </div>
              <div class="horario-item">
                <span class="horario-periodo">Tarde</span>
                <span class="horario-horas">14h às 16h30</span>
              </div>
              <div class="horario-item">
                <span class="horario-periodo">Noite</span>
                <span class="horario-horas">18h30 às 20h</span>
              </div>
              <div class="horario-dias">
                <i class="fa-solid fa-calendar-week"></i> Segunda a Sexta
              </div>
            </div>
          </div>

          <!-- Mapa embed -->
          <div class="info-card info-card-mapa reveal reveal-delay-4">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3652.7!2d-46.6!3d-23.7!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2sRua+Guarani%2C+735%2C+Diadema!5e0!3m2!1spt-BR!2sbr!4v1"
              width="100%"
              height="100%"
              style="border:0; border-radius: 12px;"
              allowfullscreen=""
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              title="Localização da ETEC JK no Google Maps"
            ></iframe>
          </div>

        </div>
      </div>
    </section>

    <!-- ─── CTA ─── -->
    <section class="chamada-sistema">
      <div class="chamada-sistema-inner">
        <h2>Orgulho de ser ETEC JK.</h2>
        <p>Um projeto nascido aqui, para todos daqui.</p>
        <a href="/soee/index.php" class="botao-primario">
          <i class="fa-solid fa-arrow-right"></i>
          Acessar o Sistema
        </a>
      </div>
    </section>

  </main>

  <!-- ─── RODAPÉ ─── -->
  <footer class="rodape">
    <div class="rodape-conteudo">
      <div class="rodape-institucional">
        <h3>Etec Juscelino Kubitschek de Oliveira</h3>
        <p>Plataforma digital para organização dos interclasses e eventos esportivos da escola.</p>
        
      </div>
      <ul class="rodape-lista">
        <li><h3>Comunicação</h3></li>
        <li>(11) 4053-9400</li>
        <li><a href="#">Contato</a></li>
      </ul>
      <ul class="rodape-lista">
        <li><h3>Institucional</h3></li>
        <li><a href="#">ETEC</a></li>
        <li><a href="#">Centro Paula Souza</a></li>
      </ul>
    </div>
    <div class="rodape-direitos">
      © 2026 — SOEE | Todos os direitos reservados
    </div>
  </footer>

  <script src="/soee/src/frontend/js/inicio.js"></script>

<?php include __DIR__ . '/../include/end.php';?>