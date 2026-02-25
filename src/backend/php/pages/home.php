<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>SOEE | Sistema de Organização de Esportes Escolares</title>

  <link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">

  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  >

  <link rel="stylesheet" href="/soee/src/frontend/css/home.css">
</head>

<body>

  <header class="cabecalho">
    <div class="cabecalho-container">

      <div class="cabecalho-logos">
        <img src="/soee/src/images/logo-jk.png" alt="ETEC Juscelino Kubitschek de Oliveira">
        <img src="/soee/src/images/logo-cps.png" alt="Centro Paula Souza">
      </div>

      <nav class="menu-principal" aria-label="Menu principal">
        <ul class="menu-lista">
          <li><a href="#" aria-current="page">Início</a></li>
          <li><a href="#">Modalidades</a></li>
          <li><a href="#">Quem Somos</a></li>
          <li><a href="#">Sobre a ETEC</a></li>
          <li><a href="#">Redes Sociais</a></li>
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

        <img
          src="/soee/src/images/logo-soee.png"
          alt="SOEE - Sistema de Organização de Esportes Escolares"
          class="logo-sistema"
        >
      </div>

    </div>
  </header>

  <main id="pagina-inicial">

    <section class="intro-soee" aria-labelledby="titulo-soee">
      <div class="intro-conteudo">
        <h1 id="titulo-soee">Sistema de Organização de Esportes Escolares</h1>

        <p>
          O <strong>SOEE</strong> é uma plataforma digital desenvolvida para
          organizar e modernizar os interclasses e eventos esportivos da
          ETEC Juscelino Kubitschek de Oliveira.
        </p>

        <div class="intro-acoes">
          <a href="#sobre-soee" class="botao-primario">Conheça o Projeto</a>
          <a href="#funcionalidades-soee" class="botao-secundario">Funcionalidades</a>
        </div>
      </div>
    </section>

    <section id="sobre-soee" class="secao-conteudo" aria-labelledby="titulo-sobre">
      <header class="secao-cabecalho">
        <h2 id="titulo-sobre">Sobre o SOEE</h2>
        <p>Organização, tecnologia e inovação no esporte escolar</p>
      </header>

      <div class="secao-texto">
        <p>
          O SOEE foi criado para resolver problemas recorrentes na organização
          manual dos interclasses, como falhas de comunicação, conflitos de
          horários e ausência de controle centralizado.
        </p>

        <p>
          A proposta é oferecer um sistema único, transparente e eficiente,
          facilitando a gestão do evento para alunos, professores e coordenação.
        </p>
      </div>
    </section>

    <section class="secao-problemas" aria-labelledby="titulo-problemas">
      <header class="secao-cabecalho">
        <h2 id="titulo-problemas">Problemática Identificada</h2>
        <p>Principais dificuldades enfrentadas atualmente</p>
      </header>

      <div class="problemas-lista">
        <article class="problema-item">
          <h3>Falta de Organização</h3>
          <p>Processos manuais causam erros, retrabalho e perda de informações.</p>
        </article>

        <article class="problema-item">
          <h3>Conflitos de Horários</h3>
          <p>Partidas marcadas simultaneamente e dificuldade de ajustes.</p>
        </article>

        <article class="problema-item">
          <h3>Comunicação Ineficiente</h3>
          <p>Ausência de um canal oficial para avisos e resultados.</p>
        </article>

        <article class="problema-item">
          <h3>Desmotivação dos Alunos</h3>
          <p>A desorganização compromete a experiência esportiva.</p>
        </article>
      </div>
    </section>

    <section id="funcionalidades-soee" class="secao-funcionalidades" aria-labelledby="titulo-funcionalidades">
      <header class="secao-cabecalho">
        <h2 id="titulo-funcionalidades">Funcionalidades do Sistema</h2>
        <p>Recursos desenvolvidos para facilitar a organização</p>
      </header>

      <div class="funcionalidades-lista">
        <article class="funcionalidade-item">
          <h3>Inscrição Online</h3>
          <p>Cadastro digital de participantes de forma rápida e segura.</p>
        </article>

        <article class="funcionalidade-item">
          <h3>Gestão de Times</h3>
          <p>Controle completo de equipes, atletas e modalidades.</p>
        </article>

        <article class="funcionalidade-item">
          <h3>Cronogramas Automatizados</h3>
          <p>Geração inteligente de tabelas e confrontos.</p>
        </article>

        <article class="funcionalidade-item">
          <h3>Resultados em Tempo Real</h3>
          <p>Acompanhamento atualizado das partidas e classificações.</p>
        </article>
      </div>
    </section>

    <section class="chamada-sistema">
      <h2>SOEE — Tecnologia a favor do esporte escolar</h2>
      <p>Mais organização, transparência e eficiência para toda a comunidade escolar.</p>

      <a href="/soee/index.php" class="botao-primario">
        Acessar o Sistema
      </a>
    </section>

  </main>

  <footer class="rodape">
    <div class="rodape-conteudo">

      <div class="rodape-institucional">
        <h3>Etec Juscelino Kubitschek de Oliveira</h3>
        <p>Conecte-se conosco</p>

        <a
          href="https://www.instagram.com/etecjko"
          class="rodape-rede-social"
          target="_blank"
          aria-label="Instagram da Etec JK"
        >
          <i class="fa-brands fa-instagram"></i>
        </a>
      </div>

      <ul class="rodape-lista">
        <li><strong>Comunicação</strong></li>
        <li>(11) 4053-9400</li>
        <li><a href="#">Contato</a></li>
      </ul>

      <ul class="rodape-lista">
        <li><strong>Institucional</strong></li>
        <li><a href="#">ETEC</a></li>
        <li><a href="#">Centro Paula Souza</a></li>
      </ul>

    </div>

    <div class="rodape-direitos">
      © 2026 — SOEE | Todos os direitos reservados
    </div>
  </footer>

  <script src="/soee/src/frontend/js/home.js"></script>

  <script>
    const toggleTheme = document.getElementById("toggle-theme");
    const icon = toggleTheme.querySelector("i");

    function setTheme(theme) {
      document.documentElement.setAttribute("data-theme", theme);
      localStorage.setItem("theme", theme);
      icon.className = theme === "dark"
        ? "fa-solid fa-sun"
        : "fa-solid fa-moon";
    }

    const savedTheme = localStorage.getItem("theme") || "light";
    setTheme(savedTheme);

    toggleTheme.addEventListener("click", () => {
      const currentTheme = document.documentElement.getAttribute("data-theme");
      setTheme(currentTheme === "dark" ? "light" : "dark");
    });
  </script>

</body>
</html>