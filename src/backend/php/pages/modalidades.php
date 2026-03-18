<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/soee/src/backend/php/include/conexao.php";

$stmt = $conn->query("SELECT * FROM modalidade WHERE ativo_modalidade = 1 ORDER BY nome_modalidade ASC");
$modalidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

function iconeModalidade(string $tipo): string {
    return match($tipo) {
        'quadra' => '🏀', 'mesa' => '🏓', 'campo' => '⚽', default => '🎯',
    };
}
function categoriaFiltro(string $p): string {
    return in_array($p, ['dupla','trio','time']) ? 'coletivo' : 'individual';
}
function labelCategoria(string $p): string {
    return in_array($p, ['dupla','trio','time']) ? 'Coletiva' : 'Individual';
}
function labelTipo(string $t): string {
    return match($t) { 'quadra'=>'Quadra','mesa'=>'Mesa','campo'=>'Campo',default=>'Outro' };
}
function numCard(int $n): string { return str_pad($n,2,'0',STR_PAD_LEFT); }
?>
<!DOCTYPE html>
<html lang="pt-br" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script>const _t=localStorage.getItem('theme');if(_t)document.documentElement.setAttribute('data-theme',_t);</script>
  <title>Modalidades</title>
  <link rel="stylesheet" href="/soee/src/frontend/css/modalidades.css">
  <link rel="stylesheet" href="/soee/src/frontend/css/inicio.css">
  <link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
</head>
<body>

  <div class="cursor-dot" id="cursorDot"></div>
  <div class="cursor-ring" id="cursorRing"></div>

  <?php if(isset($_GET['cadastro']) && $_GET['cadastro']==='ok'): ?>
  <div class="toast-sucesso">
    <i class="fa-solid fa-circle-check"></i>
    Modalidade cadastrada com sucesso!
  </div>
  <?php endif; ?>

  <header class="cabecalho">
    <div class="cabecalho-container">
      <div class="cabecalho-logos">
        <img src="/soee/src/images/logo-jk.png"  alt="ETEC Juscelino Kubitschek de Oliveira">
        <img src="/soee/src/images/logo-cps.png" alt="Centro Paula Souza">
      </div>
      <nav class="menu-principal" aria-label="Menu principal">
        <ul class="menu-lista">
          <li><a href="/soee/src/backend/php/pages/inicio.php">Início</a></li>
          <li><a href="/soee/src/backend/php/pages/quem-somos.php">Quem Somos</a></li>
          <li><a href="/soee/src/backend/php/pages/sobre-etec.php">Sobre a ETEC</a></li>
          <li><a href="/soee/src/backend/php/pages/contato-redes.php">Contato & Redes</a></li>
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
          <i class="fa-solid fa-medal"></i>
          Interclasses ETEC JK
        </div>
        <h1>Modalidades <em>Esportivas</em></h1>
        <p>Conheça todas as modalidades que fazem parte dos interclasses da ETEC Juscelino Kubitschek de Oliveira.</p>
      </div>
      <div class="pagina-onda">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z" fill="var(--fundo-pagina)"/>
        </svg>
      </div>
    </section>

    <section class="modalidades-filtro-section">
      <div class="modalidades-filtro">
        <button class="filtro-btn ativo" data-filtro="todos">
          <i class="fa-solid fa-border-all"></i> Todas
        </button>
        <button class="filtro-btn" data-filtro="coletivo">
          <i class="fa-solid fa-users"></i> Coletivas
        </button>
        <button class="filtro-btn" data-filtro="individual">
          <i class="fa-solid fa-user"></i> Individuais
        </button>
      </div>
    </section>

    <section class="modalidades-section">
      <div class="modalidades-grid">

        <!-- Card de adicionar — igual ao original -->
        <a href="/soee/src/backend/php/form/form-esporte.php" class="modalidade-card add-card">
          <div class="card-icone-wrap">
            <div class="card-icone">
              <i class="fa-solid fa-plus"></i>
            </div>
          </div>
          <div class="card-corpo">
            <div class="card-tag">Admin</div>
            <h2>Adicionar Modalidade</h2>
            <p>
              Crie uma nova modalidade esportiva para o interclasse.
              Defina tipo, formato da competição e número de jogadores.
            </p>
          </div>
        </a>

      </div>
    </section>

    <section class="chamada-sistema">
      <div class="chamada-sistema-inner">
        <h2>Pronto para competir?</h2>
        <p>Acesse o sistema, escolha sua modalidade e faça sua inscrição.</p>
        <a href="/soee/index.php" class="botao-primario">
          <i class="fa-solid fa-arrow-right"></i>
          Acessar o Sistema
        </a>
      </div>
    </section>

  </main>

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
  <script src="/soee/src/frontend/js/modalidades.js"></script>

</body>
</html>