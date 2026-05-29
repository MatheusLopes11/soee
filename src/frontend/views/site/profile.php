<?php
ob_start();
session_start();
require_once __DIR__ . '/../../../backend/includes/conexao.php';
require_once __DIR__ . '/../../../backend/controllers/home.php';

AuthHome::exigirLogin();

$userId   = AuthHome::getId();
$userTipo = AuthHome::getTipo();
$userNome = AuthHome::getNome();

$msgFoto  = '';
$tipoMsg  = '';
$msgEdit  = '';
$tipoEdit = '';

// ─── Upload de foto ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_perfil'])) {
    $file       = $_FILES['foto_perfil'];
    $permitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxSize    = 5 * 1024 * 1024;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $msgFoto = 'Erro ao enviar o arquivo.';
        $tipoMsg = 'erro';
    } elseif (!in_array($file['type'], $permitidos)) {
        $msgFoto = 'Formato inválido. Use JPG, PNG, WEBP ou GIF.';
        $tipoMsg = 'erro';
    } elseif ($file['size'] > $maxSize) {
        $msgFoto = 'Arquivo muito grande. Máximo 5MB.';
        $tipoMsg = 'erro';
    } else {
        $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nomeFoto   = 'usuario_' . $userId . '_' . time() . '.' . $ext;
        $destino    = $_SERVER['DOCUMENT_ROOT'] . '/soee/src/images/perfil/' . $nomeFoto;
        $caminhoWeb = '/soee/src/images/perfil/' . $nomeFoto;

        if (!is_dir(dirname($destino))) mkdir(dirname($destino), 0755, true);

        if (move_uploaded_file($file['tmp_name'], $destino)) {
            // PostgreSQL: atual_foto é BOOLEAN → FALSE (não 0)
            $conn->prepare("UPDATE foto_perfil SET atual_foto = FALSE WHERE usuario_id_usuario = :id")
                 ->execute([':id' => $userId]);

            $conn->prepare("
                INSERT INTO foto_perfil (usuario_id_usuario, caminho_foto, nome_arquivo_foto, tipo_arquivo_foto)
                VALUES (:uid, :caminho, :nome, :tipo)
            ")->execute([':uid' => $userId, ':caminho' => $caminhoWeb, ':nome' => $nomeFoto, ':tipo' => $ext]);

            $conn->prepare("UPDATE usuario SET foto_perfil_usuario = :foto WHERE id_usuario = :id")
                 ->execute([':foto' => $caminhoWeb, ':id' => $userId]);

            $msgFoto = 'Foto atualizada com sucesso!';
            $tipoMsg = 'sucesso';
        } else {
            $msgFoto = 'Não foi possível salvar o arquivo.';
            $tipoMsg = 'erro';
        }
    }
}

// ─── Edição de informações pessoais ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'editar_perfil') {

    $novoNome   = trim($_POST['nome_usuario']  ?? '');
    $novoEmail  = trim($_POST['email_usuario'] ?? '');
    $novoGenero = $_POST['genero_usuario']     ?? '';

    $generosValidos = ['m', 'f', 'n'];
    $erros = [];

    if (empty($novoNome))  $erros[] = 'Nome é obrigatório.';
    if (empty($novoEmail)) $erros[] = 'E-mail é obrigatório.';
    elseif (!filter_var($novoEmail, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
    if (!in_array($novoGenero, $generosValidos)) $erros[] = 'Gênero inválido.';

    if (empty($erros)) {
        $chk = $conn->prepare("SELECT id_usuario FROM usuario WHERE email_usuario = :email AND id_usuario != :id");
        $chk->execute([':email' => $novoEmail, ':id' => $userId]);
        if ($chk->fetch()) $erros[] = 'Este e-mail já está em uso por outro usuário.';
    }

    if (!empty($erros)) {
        $msgEdit  = implode(' ', $erros);
        $tipoEdit = 'erro';
    } else {
        $conn->prepare("
            UPDATE usuario
            SET nome_usuario   = :nome,
                email_usuario  = :email,
                genero_usuario = :genero
            WHERE id_usuario = :id
        ")->execute([
            ':nome'   => $novoNome,
            ':email'  => $novoEmail,
            ':genero' => $novoGenero,
            ':id'     => $userId,
        ]);

        $msgEdit  = 'Perfil atualizado com sucesso!';
        $tipoEdit = 'sucesso';
    }
}

// ─── Busca dados do usuário ───────────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT u.id_usuario, u.nome_usuario, u.email_usuario,
           u.genero_usuario, u.tipo_usuario, u.ativo_usuario,
           u.foto_perfil_usuario,
           t.nome_turma, t.periodo_turma,
           c.nome_curso, c.sigla_curso
    FROM usuario u
    LEFT JOIN turma t ON t.id_turma = u.turma_id_turma
    LEFT JOIN curso c ON c.id_curso = t.curso_id_curso
    WHERE u.id_usuario = :id
");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Inscrições ───────────────────────────────────────────────────────────────
$stmtIns = $conn->prepare("
    SELECT i.id_inscricao, i.numero_camisa_inscricao, i.nome_camisa_inscricao,
           i.posicao_inscricao, i.capitao_inscricao,
           m.nome_modalidade, e.nome_edicao, i.status_inscricao
    FROM inscricao i
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = i.edicao_modalidade_id
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    INNER JOIN edicao e ON e.id_edicao = em.edicao_id_edicao
    WHERE i.usuario_id_usuario = :id
    ORDER BY i.data_inscricao DESC
    LIMIT 5
");
$stmtIns->execute([':id' => $userId]);
$inscricoes = $stmtIns->fetchAll(PDO::FETCH_ASSOC);

$tipoLabel   = ['adm_geral' => 'Administrador Geral', 'adm_sala' => 'ADM de Sala', 'professor' => 'Professor', 'aluno' => 'Aluno'];
$generoLabel = ['m' => 'Masculino', 'f' => 'Feminino', 'n' => 'Não informado'];
$tipoIcone   = ['adm_geral' => 'crown', 'adm_sala' => 'user-shield', 'professor' => 'chalkboard-teacher', 'aluno' => 'graduation-cap'];

$dashboardUrl = AuthHome::getRota($userTipo);
?>
<?php include __DIR__ . '/../includes/doctype.php'; ?>
<head>
    <title>SOEE | Minha Conta</title>
    <link rel="stylesheet" href="/soee/src/frontend/styles/user-conta.css">
    <?php include __DIR__ . '/../includes/head.php'; ?>
</head>
<body>

<div class="cursor-dot" id="cursorDot"></div>
<div class="cursor-ring" id="cursorRing"></div>

<!-- TOPBAR -->
<header class="conta-topbar">
    <a href="<?= htmlspecialchars($dashboardUrl) ?>" class="topbar-back">
        <i class="fa-solid fa-arrow-left"></i> Voltar
    </a>
    <div class="topbar-logo">S<span>O</span>EE</div>
    <button class="btn-icone-topo" id="toggleTema" title="Alternar tema">
        <i class="fa-solid fa-moon" id="iconeTema"></i>
    </button>
</header>

<!-- HERO -->
<div class="conta-hero">
    <div class="hero-grid"></div>
    <div class="hero-particles"><span></span><span></span><span></span></div>
    <div class="hero-conteudo">
        <div class="hero-avatar-wrap">
            <div class="hero-avatar" id="heroAvatar">
                <?php if (!empty($user['foto_perfil_usuario'])): ?>
                    <img src="<?= htmlspecialchars($user['foto_perfil_usuario']) ?>">
                <?php else: ?>
                    <?= strtoupper(substr($user['nome_usuario'], 0, 2)) ?>
                <?php endif; ?>
            </div>
            <label class="avatar-edit-btn" for="inputFoto" title="Alterar foto">
                <i class="fa-solid fa-camera"></i>
            </label>
        </div>
        <div class="hero-info">
            <div class="hero-tipo-badge">
                <i class="fa-solid fa-<?= $tipoIcone[$userTipo] ?? 'user' ?>"></i>
                <?= htmlspecialchars($tipoLabel[$userTipo] ?? $userTipo) ?>
            </div>
            <h1 class="hero-nome"><?= htmlspecialchars($user['nome_usuario']) ?></h1>
            <p class="hero-email"><?= htmlspecialchars($user['email_usuario']) ?></p>
            <?php if (!empty($user['nome_turma'])): ?>
            <div class="hero-turma">
                <i class="fa-solid fa-door-open"></i>
                <?= htmlspecialchars($user['nome_turma']) ?>
                <?php if (!empty($user['sigla_curso'])): ?> · <?= htmlspecialchars($user['sigla_curso']) ?><?php endif; ?>
                <?php if (!empty($user['periodo_turma'])): ?> · <?= ucfirst($user['periodo_turma']) ?><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Form de upload (oculto) -->
<form method="POST" enctype="multipart/form-data" id="formFoto" style="display:none">
    <input type="file" name="foto_perfil" id="inputFoto" accept="image/jpeg,image/png,image/webp,image/gif">
</form>

<?php if ($msgFoto): ?>
<div class="toast-fixo <?= $tipoMsg ?>" id="toastFoto">
    <i class="fa-solid fa-<?= $tipoMsg === 'sucesso' ? 'check-circle' : 'times-circle' ?>"></i>
    <?= htmlspecialchars($msgFoto) ?>
</div>
<?php endif; ?>

<?php if ($msgEdit): ?>
<div class="toast-fixo <?= $tipoEdit ?> toast-edit" id="toastEdit">
    <i class="fa-solid fa-<?= $tipoEdit === 'sucesso' ? 'check-circle' : 'times-circle' ?>"></i>
    <?= htmlspecialchars($msgEdit) ?>
</div>
<?php endif; ?>

<main class="conta-main">

    <!-- ══════════════════════════════════════════════════════════
         Informações Pessoais
    ══════════════════════════════════════════════════════════ -->
    <section class="conta-secao reveal reveal-delay-1" id="secaoInfoPessoais">
        <div class="secao-header">
            <i class="fa-solid fa-user-circle"></i>
            <h2>Informações Pessoais <span class="badge-editando">Editando</span></h2>
        </div>

        <form method="POST" id="formEditPerfil">
            <input type="hidden" name="acao" value="editar_perfil">

            <div class="info-grid">

                <!-- Nome completo -->
                <div class="info-item campo-editavel">
                    <span class="info-label"><i class="fa-solid fa-user"></i> Nome completo</span>
                    <div class="info-valor">
                        <span class="val-texto"><?= htmlspecialchars($user['nome_usuario']) ?></span>
                        <input class="val-input" type="text" name="nome_usuario"
                               value="<?= htmlspecialchars($user['nome_usuario']) ?>"
                               placeholder="Nome completo" maxlength="100" required>
                    </div>
                </div>

                <!-- E-mail -->
                <div class="info-item campo-editavel">
                    <span class="info-label"><i class="fa-solid fa-envelope"></i> E-mail</span>
                    <div class="info-valor">
                        <span class="val-texto"><?= htmlspecialchars($user['email_usuario']) ?></span>
                        <input class="val-input" type="email" name="email_usuario"
                               value="<?= htmlspecialchars($user['email_usuario']) ?>"
                               placeholder="E-mail" maxlength="120" required>
                    </div>
                </div>

                <!-- Gênero -->
                <div class="info-item campo-editavel">
                    <span class="info-label"><i class="fa-solid fa-venus-mars"></i> Gênero</span>
                    <div class="info-valor">
                        <span class="val-texto"><?= htmlspecialchars($generoLabel[$user['genero_usuario']] ?? '—') ?></span>
                        <select class="val-input" name="genero_usuario">
                            <option value="m" <?= $user['genero_usuario'] === 'm' ? 'selected' : '' ?>>Masculino</option>
                            <option value="f" <?= $user['genero_usuario'] === 'f' ? 'selected' : '' ?>>Feminino</option>
                            <option value="n" <?= $user['genero_usuario'] === 'n' ? 'selected' : '' ?>>Não informado</option>
                        </select>
                    </div>
                </div>

                <!-- Tipo de conta (somente leitura) -->
                <div class="info-item">
                    <span class="info-label"><i class="fa-solid fa-id-badge"></i> Tipo de conta</span>
                    <div class="info-valor">
                        <span class="badge-tipo <?= $userTipo ?>"><?= htmlspecialchars($tipoLabel[$userTipo] ?? $userTipo) ?></span>
                    </div>
                </div>

                <!-- Status (somente leitura) -->
                <div class="info-item">
                    <span class="info-label"><i class="fa-solid fa-circle-dot"></i> Status</span>
                    <div class="info-valor">
                        <span class="badge-status <?= $user['ativo_usuario'] ? 'ativo' : 'inativo' ?>">
                            <?= $user['ativo_usuario'] ? 'Ativa' : 'Inativa' ?>
                        </span>
                    </div>
                </div>

                <!-- Turma (somente leitura) -->
                <?php if (!empty($user['nome_turma'])): ?>
                <div class="info-item">
                    <span class="info-label"><i class="fa-solid fa-door-open"></i> Turma</span>
                    <div class="info-valor"><?= htmlspecialchars($user['nome_turma']) ?></div>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fa-solid fa-book"></i> Curso</span>
                    <div class="info-valor"><?= htmlspecialchars($user['nome_curso'] ?? '—') ?></div>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fa-solid fa-sun"></i> Período</span>
                    <div class="info-valor"><?= ucfirst($user['periodo_turma'] ?? '—') ?></div>
                </div>
                <?php endif; ?>

            </div><!-- /info-grid -->

            <div class="secao-acoes">
                <button type="button" class="btn-editar" id="btnEditar">
                    <i class="fa-solid fa-pen"></i> Editar informações
                </button>
                <button type="submit" class="btn-salvar-edicao">
                    <i class="fa-solid fa-floppy-disk"></i> Salvar alterações
                </button>
                <button type="button" class="btn-cancelar-edicao" id="btnCancelar">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </button>
            </div>
        </form>
    </section>

    <!-- ══════════════════════════════════════════════════════════
         Minhas Inscrições
    ══════════════════════════════════════════════════════════ -->
    <?php if (!empty($inscricoes)): ?>
    <section class="conta-secao reveal reveal-delay-2">
        <div class="secao-header">
            <i class="fa-solid fa-shirt"></i>
            <h2>Minhas Inscrições</h2>
        </div>
        <div class="inscricoes-lista">
            <?php foreach ($inscricoes as $ins): ?>
            <div class="inscricao-card">
                <div class="inscricao-camisa">
                    <span class="camisa-num"><?= $ins['numero_camisa_inscricao'] ? '#' . $ins['numero_camisa_inscricao'] : '—' ?></span>
                    <span class="camisa-label">Camisa</span>
                    <?php if (!empty($ins['nome_camisa_inscricao'])): ?>
                    <span class="camisa-nome-card"><?= htmlspecialchars(strtoupper($ins['nome_camisa_inscricao'])) ?></span>
                    <?php endif; ?>
                </div>
                <div class="inscricao-info">
                    <strong><?= htmlspecialchars($ins['nome_modalidade']) ?></strong>
                    <span><?= htmlspecialchars($ins['nome_edicao']) ?></span>
                    <?php if ($ins['posicao_inscricao']): ?>
                    <span class="posicao"><i class="fa-solid fa-running"></i> <?= htmlspecialchars($ins['posicao_inscricao']) ?></span>
                    <?php endif; ?>
                    <?php if ($ins['capitao_inscricao']): ?>
                    <span class="capitao"><i class="fa-solid fa-star"></i> Capitão</span>
                    <?php endif; ?>
                </div>
                <span class="inscricao-status <?= $ins['status_inscricao'] ?>"><?= ucfirst($ins['status_inscricao']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════════
         Foto de Perfil
    ══════════════════════════════════════════════════════════ -->
    <section class="conta-secao reveal reveal-delay-3">
        <div class="secao-header">
            <i class="fa-solid fa-image"></i>
            <h2>Foto de Perfil</h2>
        </div>
        <div class="foto-area">
            <div class="foto-preview-wrap">
                <div class="foto-preview" id="fotoPreview">
                    <?php if (!empty($user['foto_perfil_usuario'])): ?>
                        <img src="<?= htmlspecialchars($user['foto_perfil_usuario']) ?>">
                    <?php else: ?>
                        <i class="fa-solid fa-user fa-2x"></i>
                    <?php endif; ?>
                </div>
            </div>
            <div class="foto-instrucoes">
                <p>Formatos aceitos: <strong>JPG, PNG, WEBP, GIF</strong></p>
                <p>Tamanho máximo: <strong>5 MB</strong></p>
                <p>Recomendado: imagem quadrada</p>
                <label for="inputFoto" class="btn-upload">
                    <i class="fa-solid fa-upload"></i> Escolher foto
                </label>
                <button class="btn-salvar-foto" id="btnSalvarFoto" style="display:none"
                        onclick="document.getElementById('formFoto').submit()">
                    <i class="fa-solid fa-save"></i> Salvar foto
                </button>
                <p class="foto-nome" id="fotoNome"></p>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════
         Sessão / Logout
    ══════════════════════════════════════════════════════════ -->
    <section class="conta-secao reveal reveal-delay-4">
        <div class="secao-header">
            <i class="fa-solid fa-right-from-bracket"></i>
            <h2>Sessão</h2>
        </div>
        <div class="logout-conteudo">
            <div class="logout-info">
                <h3>Encerrar acesso</h3>
                <p>Ao sair, você precisará fazer login novamente para acessar o sistema.</p>
                <p class="logout-sub">Cookies de "Lembrar de mim" também serão removidos.</p>
            </div>
            <a href="/soee/src/backend/includes/logout.php" class="btn-logout" id="btnLogout">
                <i class="fa-solid fa-right-from-bracket"></i>
                Sair da conta
            </a>
        </div>
    </section>

</main>

<script src="/soee/src/frontend/scripts/user-conta.js"></script>
<script>
/* ── Tema ─────────────────────────────────────────────────────────── */
const _t = localStorage.getItem('theme');
if (_t) document.documentElement.setAttribute('data-theme', _t);

/* ── Referências ──────────────────────────────────────────────────── */
const secao       = document.getElementById('secaoInfoPessoais');
const btnEditar   = document.getElementById('btnEditar');
const btnCancelar = document.getElementById('btnCancelar');

/* Captura snapshot dos valores atuais para o Cancelar */
const snapshot = {};
secao.querySelectorAll('input.val-input, select.val-input').forEach(el => {
    snapshot[el.name] = el.value;
});

/* ── Ativar modo edição ───────────────────────────────────────────── */
btnEditar.addEventListener('click', () => {
    secao.classList.add('secao-editando');
    const primeiroInput = secao.querySelector('input.val-input');
    if (primeiroInput) primeiroInput.focus();
});

/* ── Cancelar: restaura valores e sai do modo edição ─────────────── */
btnCancelar.addEventListener('click', () => {
    secao.querySelectorAll('input.val-input, select.val-input').forEach(el => {
        el.value = snapshot[el.name] ?? '';
    });
    secao.classList.remove('secao-editando');
});

/* ── Auto-dismiss dos toasts após 4 s ────────────────────────────── */
['toastFoto', 'toastEdit'].forEach(id => {
    const el = document.getElementById(id);
    if (el) setTimeout(() => { el.style.transition = 'opacity .5s'; el.style.opacity = '0'; }, 4000);
});

/* ── Se voltou com erro de validação, reabre o modo edição ──────── */
<?php if ($tipoEdit === 'erro'): ?>
secao.classList.add('secao-editando');
<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/end.php'; ?>