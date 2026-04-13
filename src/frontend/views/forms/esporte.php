<?php
session_start();
require_once __DIR__ . '/../../../backend/includes/conexao.php';
require_once __DIR__ . '/../../../backend/controllers/home.php';

AuthHome::exigirTipo(['adm_geral', 'professor']);

function sel($campo, $valor) {
    return isset($_POST[$campo]) && $_POST[$campo] === $valor ? 'selected' : '';
}

$erro = $_GET['erro'] ?? null;
$erroMsg = match($erro) {
    'dados_incompletos'    => 'Preencha todos os campos obrigatórios.',
    'modalidade_duplicada' => 'Já existe uma modalidade com esse nome.',
    'foto_invalida'        => 'Formato de foto inválido. Use JPG, PNG, GIF ou WEBP.',
    'foto_grande'          => 'A foto deve ter no máximo 5 MB.',
    'erro_db'              => 'Erro ao salvar. Tente novamente.',
    default                => null
};
?>
<?php include __DIR__ . '/../includes/doctype.php'; ?>
<head>
    <title>Cadastro de Modalidade — SOEE</title>
    <link rel="stylesheet" href="/soee/src/frontend/styles/inicio.css" />
    <link rel="stylesheet" href="/soee/src/frontend/styles/cad-esporte.css"/>
    <?php include __DIR__ . '/../includes/head.php'; ?>
</head>
<body>

<?php include __DIR__ . '/../includes/cursor.php'; ?>

<main>

    <section class="pagina-hero" aria-label="Cabeçalho da página">
        <div class="pagina-hero-bg"></div>
        <div class="pagina-hero-grid"></div>
        <div class="pagina-hero-conteudo">
            <div class="badge"><i class="fa-solid fa-shield-halved"></i> Painel Administrativo</div>
            <h1>Nova <em>Modalidade</em>
                <button id="toggle-theme" class="botao-icone" aria-label="Alternar tema">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </h1>
            <p>Cadastre um esporte, defina seu formato e inclua o regulamento oficial da competição.</p>
        </div>
        <div class="pagina-onda">
            <svg viewBox="0 0 1440 70" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,35 C360,70 1080,0 1440,35 L1440,70 L0,70 Z" fill="var(--fundo-pagina)" />
            </svg>
        </div>
    </section>

    <div class="form-wrapper">

        <?php if ($erroMsg): ?>
        <div class="alerta alerta-erro">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars($erroMsg) ?>
        </div>
        <?php endif; ?>

        <form action="/soee/src/backend/actions/salvar-modalidade.php"
              method="POST"
              id="formModalidade"
              enctype="multipart/form-data"
              novalidate>

            <div class="form-card">

                <!-- ══ SEÇÃO 1 — IDENTIFICAÇÃO ══ -->
                <section class="form-secao">
                    <div class="secao-titulo">
                        <span class="secao-numero">1</span>
                        <h2>Identificação</h2>
                        <span class="secao-desc">Informações básicas do esporte</span>
                    </div>
                    <div class="form-grid">
                        <div class="campo-grupo">
                            <label for="nome_modalidade">
                                <i class="fa-solid fa-tag label-icone"></i>
                                Nome da Modalidade <span class="obrigatorio">*</span>
                            </label>
                            <input type="text" id="nome_modalidade" name="nome_modalidade"
                                placeholder="Ex.: Futsal, Vôlei, Tênis de Mesa…"
                                value="<?= htmlspecialchars($_POST['nome_modalidade'] ?? '') ?>"
                                required maxlength="100" autocomplete="off" />
                        </div>
                        <div class="campo-grupo">
                            <label for="descricao_modalidade">
                                <i class="fa-solid fa-align-left label-icone"></i>
                                Descrição da Modalidade
                            </label>
                            <span class="campo-dica">Apresentação breve do esporte para os alunos.</span>
                            <textarea id="descricao_modalidade" name="descricao_modalidade"
                                placeholder="Descreva o objetivo do jogo, dinâmica geral…"
                                maxlength="1000"><?= htmlspecialchars($_POST['descricao_modalidade'] ?? '') ?></textarea>
                        </div>
                    </div>
                </section>

                <!-- ══ SEÇÃO 2 — FOTO ══ -->
                <section class="form-secao">
                    <div class="secao-titulo">
                        <span class="secao-numero">2</span>
                        <h2>Foto da Modalidade</h2>
                        <span class="secao-desc">Imagem exibida no card</span>
                    </div>

                    <input type="hidden" id="origem_foto" name="origem_foto" value="upload" />

                    <div class="foto-tabs">
                        <button type="button" class="foto-tab ativo" data-tab="upload">
                            <i class="fa-solid fa-upload"></i> Galeria
                        </button>
                        <button type="button" class="foto-tab" data-tab="url">
                            <i class="fa-solid fa-link"></i> Link externo
                        </button>
                        <button type="button" class="foto-tab" data-tab="nenhuma">
                            <i class="fa-solid fa-ban"></i> Sem foto
                        </button>
                    </div>

                    <!-- Painel upload -->
                    <div class="foto-painel" id="painel-upload" style="display:block;">
                        <label for="foto_arquivo" class="upload-area" id="uploadArea">
                            <div class="upload-preview" id="uploadPreview">
                                <i class="fa-solid fa-cloud-arrow-up upload-icone"></i>
                                <p class="upload-txt">Clique ou arraste uma imagem aqui</p>
                                <p class="upload-sub">JPG, PNG, GIF ou WEBP — máx. 5 MB</p>
                            </div>
                            <input type="file" id="foto_arquivo" name="foto_arquivo"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                                class="upload-input" />
                        </label>
                    </div>

                    <!-- Painel URL -->
                    <div class="foto-painel" id="painel-url" style="display:none;">
                        <div class="form-grid col-2" style="align-items:end;">
                            <div class="campo-grupo">
                                <label for="foto_url">
                                    <i class="fa-solid fa-link label-icone"></i> URL da Imagem
                                </label>
                                <span class="campo-dica">Cole o endereço direto da imagem</span>
                                <input type="url" id="foto_url" name="foto_url"
                                    placeholder="https://exemplo.com/imagem.jpg"
                                    value="<?= htmlspecialchars($_POST['foto_url'] ?? '') ?>" />
                            </div>
                            <div class="campo-grupo">
                                <span class="campo-dica">Pré-visualização</span>
                                <div class="url-preview" id="urlPreview">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Painel sem foto -->
                    <div class="foto-painel" id="painel-nenhuma" style="display:none;">
                        <div class="sem-foto-aviso">
                            <i class="fa-solid fa-image-slash"></i>
                            <p>O card da modalidade usará o emoji do esporte como ícone padrão.</p>
                        </div>
                    </div>
                </section>

                <!-- ══ SEÇÃO 3 — CONFIGURAÇÕES ══ -->
                <section class="form-secao">
                    <div class="secao-titulo">
                        <span class="secao-numero">3</span>
                        <h2>Configurações da Competição</h2>
                        <span class="secao-desc">Formato e estrutura do campeonato</span>
                    </div>
                    <div class="form-grid col-3">
                        <div class="campo-grupo">
                            <label for="tipo_modalidade">
                                <i class="fa-solid fa-layer-group label-icone"></i>
                                Tipo da Modalidade <span class="obrigatorio">*</span>
                            </label>
                            <select id="tipo_modalidade" name="tipo_modalidade" required>
                                <option value="" disabled <?= empty($_POST['tipo_modalidade']) ? 'selected' : '' ?>>Selecione…</option>
                                <option value="quadra" <?= sel('tipo_modalidade','quadra') ?>>🏀 Quadra</option>
                                <option value="mesa"   <?= sel('tipo_modalidade','mesa')   ?>>🏓 Mesa</option>
                                <option value="campo"  <?= sel('tipo_modalidade','campo')  ?>>⚽ Campo</option>
                                <option value="outro"  <?= sel('tipo_modalidade','outro')  ?>>🎯 Outro</option>
                            </select>
                        </div>
                        <div class="campo-grupo">
                            <label for="formato_modalidade">
                                <i class="fa-solid fa-sitemap label-icone"></i>
                                Formato da Competição <span class="obrigatorio">*</span>
                            </label>
                            <select id="formato_modalidade" name="formato_modalidade" required>
                                <option value="" disabled <?= empty($_POST['formato_modalidade']) ? 'selected' : '' ?>>Selecione…</option>
                                <option value="mata_mata"          <?= sel('formato_modalidade','mata_mata') ?>>⚔️ Mata-mata</option>
                                <option value="grupos"             <?= sel('formato_modalidade','grupos') ?>>📊 Fase de Grupos</option>
                                <option value="grupos_mata_mata"   <?= sel('formato_modalidade','grupos_mata_mata') ?>>🔀 Grupos + Mata-mata</option>
                                <option value="todos_contra_todos" <?= sel('formato_modalidade','todos_contra_todos') ?>>🔄 Todos contra Todos</option>
                            </select>
                        </div>
                        <div class="campo-grupo">
                            <label for="tipo_participacao">
                                <i class="fa-solid fa-users label-icone"></i>
                                Tipo de Participação <span class="obrigatorio">*</span>
                            </label>
                            <select id="tipo_participacao" name="tipo_participacao" required>
                                <option value="" disabled <?= empty($_POST['tipo_participacao']) ? 'selected' : '' ?>>Selecione…</option>
                                <option value="solo"  <?= sel('tipo_participacao','solo')  ?>>👤 Solo</option>
                                <option value="dupla" <?= sel('tipo_participacao','dupla') ?>>👥 Dupla</option>
                                <option value="trio"  <?= sel('tipo_participacao','trio')  ?>>👥 Trio</option>
                                <option value="time"  <?= sel('tipo_participacao','time')  ?>>🏅 Time</option>
                            </select>
                        </div>
                    </div>
                </section>

                <!-- ══ SEÇÃO 4 — JOGADORES ══ -->
                <section class="form-secao">
                    <div class="secao-titulo">
                        <span class="secao-numero">4</span>
                        <h2>Quantidade de Jogadores</h2>
                        <span class="secao-desc">Por equipe / por participante</span>
                    </div>
                    <div class="form-grid col-2">
                        <div class="campo-grupo">
                            <label for="qtd_min_jogadores">
                                <i class="fa-solid fa-arrow-down-1-9 label-icone"></i>
                                Mínimo de Jogadores <span class="obrigatorio">*</span>
                            </label>
                            <span class="campo-dica">Número mínimo para a partida ser realizada.</span>
                            <input type="number" id="qtd_min_jogadores" name="qtd_min_jogadores"
                                placeholder="Ex.: 5" min="1" max="99" required
                                value="<?= htmlspecialchars($_POST['qtd_min_jogadores'] ?? '') ?>" />
                        </div>
                        <div class="campo-grupo">
                            <label for="qtd_max_jogadores">
                                <i class="fa-solid fa-arrow-up-9-1 label-icone"></i>
                                Máximo de Jogadores <span class="obrigatorio">*</span>
                            </label>
                            <span class="campo-dica">Inclui titulares e reservas na lista.</span>
                            <input type="number" id="qtd_max_jogadores" name="qtd_max_jogadores"
                                placeholder="Ex.: 10" min="1" max="99" required
                                value="<?= htmlspecialchars($_POST['qtd_max_jogadores'] ?? '') ?>" />
                        </div>
                    </div>
                </section>

                <!-- ══ SEÇÃO 5 — DURAÇÃO ══ -->
                <section class="form-secao">
                    <div class="secao-titulo">
                        <span class="secao-numero">5</span>
                        <h2>Duração da Partida</h2>
                        <span class="secao-desc">Minutagem ou pontuação por partida</span>
                    </div>

                    <div class="form-grid col-2" style="margin-bottom:1.3rem;">
                        <div class="campo-grupo">
                            <label for="tipo_duracao">
                                <i class="fa-solid fa-stopwatch label-icone"></i>
                                Tipo de Duração <span class="obrigatorio">*</span>
                            </label>
                            <select id="tipo_duracao" name="tipo_duracao" required>
                                <option value="" disabled <?= empty($_POST['tipo_duracao']) ? 'selected' : '' ?>>Selecione…</option>
                                <option value="minutos" <?= sel('tipo_duracao','minutos') ?>>⏱️ Por Minutagem</option>
                                <option value="pontos"  <?= sel('tipo_duracao','pontos')  ?>>🏆 Por Pontuação</option>
                            </select>
                        </div>
                    </div>

                    <!-- Painel minutagem -->
                    <div id="painel-minutos" style="display:none;">
                        <div class="campo-grupo">
                            <label for="duracao_minutos">
                                <i class="fa-solid fa-clock label-icone"></i>
                                Formato de Tempo <span class="obrigatorio">*</span>
                            </label>
                            <span class="campo-dica">Escolha o formato de tempo da partida.</span>
                            <select id="duracao_minutos" name="duracao_minutos">
                                <option value="" disabled selected>Selecione…</option>
                                <option value="1x5"  <?= sel('duracao_minutos','1x5')  ?>>1 tempo de 5 min</option>
                                <option value="1x10" <?= sel('duracao_minutos','1x10') ?>>1 tempo de 10 min</option>
                                <option value="1x15" <?= sel('duracao_minutos','1x15') ?>>1 tempo de 15 min</option>
                                <option value="1x20" <?= sel('duracao_minutos','1x20') ?>>1 tempo de 20 min</option>
                                <option value="2x5"  <?= sel('duracao_minutos','2x5')  ?>>2 tempos de 5 min</option>
                                <option value="2x10" <?= sel('duracao_minutos','2x10') ?>>2 tempos de 10 min</option>
                                <option value="2x15" <?= sel('duracao_minutos','2x15') ?>>2 tempos de 15 min</option>
                                <option value="2x20" <?= sel('duracao_minutos','2x20') ?>>2 tempos de 20 min</option>
                                <option value="2x25" <?= sel('duracao_minutos','2x25') ?>>2 tempos de 25 min</option>
                                <option value="2x30" <?= sel('duracao_minutos','2x30') ?>>2 tempos de 30 min</option>
                                <option value="2x45" <?= sel('duracao_minutos','2x45') ?>>2 tempos de 45 min</option>
                                <option value="3x10" <?= sel('duracao_minutos','3x10') ?>>3 períodos de 10 min</option>
                                <option value="3x15" <?= sel('duracao_minutos','3x15') ?>>3 períodos de 15 min</option>
                                <option value="4x10" <?= sel('duracao_minutos','4x10') ?>>4 quartos de 10 min</option>
                                <option value="4x12" <?= sel('duracao_minutos','4x12') ?>>4 quartos de 12 min</option>
                                <option value="outro" <?= sel('duracao_minutos','outro') ?>>✏️ Outro…</option>
                            </select>
                            <input type="text" id="outro_minutos" name="outro_minutos"
                                placeholder="Ex.: 3 tempos de 8 min"
                                value="<?= htmlspecialchars($_POST['outro_minutos'] ?? '') ?>"
                                maxlength="50"
                                style="display:none; margin-top:0.6rem;" />
                        </div>
                    </div>

                    <!-- Painel pontuação -->
                    <div id="painel-pontos" style="display:none;">
                        <div class="campo-grupo">
                            <label for="duracao_pontos">
                                <i class="fa-solid fa-trophy label-icone"></i>
                                Pontos para Vencer <span class="obrigatorio">*</span>
                            </label>
                            <span class="campo-dica">O primeiro a atingir essa pontuação vence.</span>
                            <select id="duracao_pontos" name="duracao_pontos">
                                <option value="" disabled selected>Selecione…</option>
                                <option value="5"    <?= sel('duracao_pontos','5')    ?>>5 pontos</option>
                                <option value="7"    <?= sel('duracao_pontos','7')    ?>>7 pontos</option>
                                <option value="10"   <?= sel('duracao_pontos','10')   ?>>10 pontos</option>
                                <option value="11"   <?= sel('duracao_pontos','11')   ?>>11 pontos (Tênis de Mesa)</option>
                                <option value="15"   <?= sel('duracao_pontos','15')   ?>>15 pontos</option>
                                <option value="20"   <?= sel('duracao_pontos','20')   ?>>20 pontos</option>
                                <option value="21"   <?= sel('duracao_pontos','21')   ?>>21 pontos</option>
                                <option value="25"   <?= sel('duracao_pontos','25')   ?>>25 pontos (Vôlei)</option>
                                <option value="outro" <?= sel('duracao_pontos','outro') ?>>✏️ Outro…</option>
                            </select>
                            <input type="number" id="outro_pontos" name="outro_pontos"
                                placeholder="Ex.: 30"
                                value="<?= htmlspecialchars($_POST['outro_pontos'] ?? '') ?>"
                                min="1" max="999"
                                style="display:none; margin-top:0.6rem;" />
                        </div>
                    </div>
                </section>

                <!-- ══ SEÇÃO 6 — REGULAMENTO ══ -->
                <section class="form-secao">
                    <div class="secao-titulo">
                        <span class="secao-numero">6</span>
                        <h2>Regulamento Oficial</h2>
                        <span class="secao-desc">Regras detalhadas da modalidade</span>
                    </div>
                    <div class="form-grid">
                        <div class="regulamento-dica">
                            <strong><i class="fa-solid fa-circle-info"></i> &nbsp;Como preencher</strong>
                            Inclua todas as regras específicas do esporte para os interclasses:
                            <ul>
                                <li>Infrações e penalidades (ex.: recuo ao goleiro → tiro indireto livre)</li>
                                <li>Pontuação e critérios de desempate</li>
                                <li>Regras de tempo, substituições e cartões</li>
                                <li>Situações especiais e exceções da competição escolar</li>
                            </ul>
                        </div>
                        <div class="campo-grupo">
                            <label for="regulamento_modalidade">
                                <i class="fa-solid fa-scroll label-icone"></i>
                                Regulamento da Modalidade <span class="obrigatorio">*</span>
                            </label>
                            <span class="campo-dica">Detalhe regras, infrações, penalidades e casos especiais.</span>
                            <textarea id="regulamento_modalidade" name="regulamento_modalidade"
                                class="regulamento-area"
                                placeholder="Exemplo — Futsal:&#10;• Recuo ao goleiro → tiro indireto livre.&#10;• Cartão amarelo: 3 = 1 jogo de suspensão.&#10;• Tempo: 2 × 20 min, intervalo de 10 min."
                                required maxlength="5000"><?= htmlspecialchars($_POST['regulamento_modalidade'] ?? '') ?></textarea>
                        </div>
                    </div>
                </section>

                <!-- BARRA DE AÇÕES -->
                <div class="form-acoes">
                    <p class="form-acoes-nota">Campos marcados com <span>*</span> são obrigatórios</p>
                    <a href="/soee/src/frontend/views/dashboards/adm.php" class="btn btn-secundario">
                        <i class="fa-solid fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primario">
                        <i class="fa-solid fa-plus"></i> Cadastrar Modalidade
                    </button>
                </div>

            </div><!-- /.form-card -->
        </form>
    </div><!-- /.form-wrapper -->

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
    <div class="rodape-direitos">© 2026 — SOEE | Todos os direitos reservados</div>
</footer>

<script src="/soee/src/frontend/scripts/inicio.js"></script>
<script>
const _t = localStorage.getItem('theme');
if (_t) document.documentElement.setAttribute('data-theme', _t);

/* ── TABS DE FOTO ── */
document.querySelectorAll('.foto-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const tab = this.dataset.tab;
        document.querySelectorAll('.foto-tab').forEach(t => t.classList.remove('ativo'));
        this.classList.add('ativo');
        document.querySelectorAll('.foto-painel').forEach(p => p.style.display = 'none');
        document.getElementById('painel-' + tab).style.display = 'block';
        document.getElementById('origem_foto').value = tab;
        if (tab !== 'upload') document.getElementById('foto_arquivo').value = '';
        if (tab !== 'url') {
            document.getElementById('foto_url').value = '';
            document.getElementById('urlPreview').innerHTML = '<i class="fa-solid fa-image"></i>';
        }
    });
});

/* ── PREVIEW UPLOAD ── */
document.getElementById('foto_arquivo').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('uploadPreview').innerHTML =
            '<img src="' + e.target.result + '" style="max-height:160px;border-radius:8px;object-fit:cover;">';
    };
    reader.readAsDataURL(file);
});

/* ── PREVIEW URL ── */
document.getElementById('foto_url').addEventListener('input', function() {
    const url = this.value.trim();
    const preview = document.getElementById('urlPreview');
    if (url) {
        preview.innerHTML = '<img src="' + url + '" style="max-height:120px;border-radius:8px;object-fit:cover;" onerror="this.parentNode.innerHTML=\'<i class=\\\'fa-solid fa-image-slash\\\'></i>\'">';
    } else {
        preview.innerHTML = '<i class="fa-solid fa-image"></i>';
    }
});

/* ── DURAÇÃO — mostrar painel ── */
document.getElementById('tipo_duracao').addEventListener('change', function() {
    document.getElementById('painel-minutos').style.display = this.value === 'minutos' ? 'block' : 'none';
    document.getElementById('painel-pontos').style.display  = this.value === 'pontos'  ? 'block' : 'none';
});

/* ── DURAÇÃO — campo "outro" minutagem ── */
document.getElementById('duracao_minutos').addEventListener('change', function() {
    document.getElementById('outro_minutos').style.display = this.value === 'outro' ? 'block' : 'none';
});

/* ── DURAÇÃO — campo "outro" pontuação ── */
document.getElementById('duracao_pontos').addEventListener('change', function() {
    document.getElementById('outro_pontos').style.display = this.value === 'outro' ? 'block' : 'none';
});
</script>

<?php include __DIR__ . '/../includes/end.php'; ?>