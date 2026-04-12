<?php
ob_start();
session_start();
require_once __DIR__ . '/../../../backend/includes/conexao.php';
require_once __DIR__ . '/../../../backend/controllers/home.php';

// exigirLogin() aceita qualquer tipo de usuário logado
AuthHome::exigirLogin();

$userId   = AuthHome::getId();
$userTipo = AuthHome::getTipo();
$userNome = AuthHome::getNome();

// ── Upload de foto ──────────────────────────────────────────
$msgFoto = '';
$tipoMsg = '';

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
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nomeFoto = 'usuario_' . $userId . '_' . time() . '.' . $ext;
        $destino  = $_SERVER['DOCUMENT_ROOT'] . '/soee/src/images/perfil/' . $nomeFoto;
        $caminhoWeb = '/soee/src/images/perfil/' . $nomeFoto;

        if (!is_dir(dirname($destino))) mkdir(dirname($destino), 0755, true);

        if (move_uploaded_file($file['tmp_name'], $destino)) {
            $conn->prepare("UPDATE foto_perfil SET atual_foto = 0 WHERE usuario_id_usuario = :id")
                 ->execute([':id' => $userId]);

            $ins = $conn->prepare("
                INSERT INTO foto_perfil (usuario_id_usuario, caminho_foto, nome_arquivo_foto, tipo_arquivo_foto)
                VALUES (:uid, :caminho, :nome, :tipo)
            ");
            $ins->execute([':uid' => $userId, ':caminho' => $caminhoWeb, ':nome' => $nomeFoto, ':tipo' => $ext]);

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

// ── Busca dados do usuário ──────────────────────────────────
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

// ── Inscrições ─────────────────────────────────────────────
$stmtCamisa = $conn->prepare("
    SELECT numero_camisa_inscricao, posicao_inscricao, capitao_inscricao,
           m.nome_modalidade, e.nome_edicao, i.status_inscricao
    FROM inscricao i
    INNER JOIN edicao_modalidade em ON em.id_edicao_modalidade = i.edicao_modalidade_id
    INNER JOIN modalidade m ON m.id_modalidade = em.modalidade_id_modalidade
    INNER JOIN edicao e ON e.id_edicao = em.edicao_id_edicao
    WHERE i.usuario_id_usuario = :id
    ORDER BY i.data_inscricao DESC
    LIMIT 5
");
$stmtCamisa->execute([':id' => $userId]);
$inscricoes = $stmtCamisa->fetchAll(PDO::FETCH_ASSOC);

// ── Rótulos ────────────────────────────────────────────────
$tipoLabel   = ['adm_geral' => 'Administrador Geral', 'adm_sala' => 'ADM de Sala', 'professor' => 'Professor', 'aluno' => 'Aluno'];
$generoLabel = ['m' => 'Masculino', 'f' => 'Feminino', 'n' => 'Não informado'];
$tipoIcone   = ['adm_geral' => 'crown', 'adm_sala' => 'user-shield', 'professor' => 'chalkboard-teacher', 'aluno' => 'graduation-cap'];

// getRota() retorna o dashboard correto para o tipo do usuário
$dashboardUrl = AuthHome::getRota($userTipo);
?>

<?php include __DIR__ . '/../includes/doctype.php';?>
    <head>
        <title>SOEE | Minha Conta</title>
        <link rel="stylesheet" href="/soee/src/frontend/styles/user-conta.css">
        <?php include __DIR__ . '/../includes/head.php';?>
    </head>
<body>

<!-- Cursor personalizado -->
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
    <div class="hero-particles">
        <span></span><span></span><span></span>
    </div>

    <div class="hero-conteudo">
        <div class="hero-avatar-wrap">
            <div class="hero-avatar" id="heroAvatar">
                <?php if (!empty($user['foto_perfil_usuario'])): ?>
                    <img src="<?= htmlspecialchars($user['foto_perfil_usuario']) ?>" alt="Foto de perfil">
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

<!-- FORM UPLOAD (oculto) -->
<form method="POST" enctype="multipart/form-data" id="formFoto" style="display:none">
    <input type="file" name="foto_perfil" id="inputFoto" accept="image/jpeg,image/png,image/webp,image/gif">
</form>

<!-- TOAST do PHP -->
<?php if ($msgFoto): ?>
<div class="toast-fixo <?= $tipoMsg ?>" id="toastFoto">
    <i class="fa-solid fa-<?= $tipoMsg === 'sucesso' ? 'check-circle' : 'times-circle' ?>"></i>
    <?= htmlspecialchars($msgFoto) ?>
</div>
<?php endif; ?>

<!-- MAIN -->
<main class="conta-main">

    <!-- Informações Pessoais -->
    <section class="conta-secao reveal reveal-delay-1">
        <div class="secao-header">
            <i class="fa-solid fa-user-circle"></i>
            <h2>Informações Pessoais</h2>
        </div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label"><i class="fa-solid fa-user"></i> Nome completo</span>
                <span class="info-valor"><?= htmlspecialchars($user['nome_usuario']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fa-solid fa-envelope"></i> E-mail</span>
                <span class="info-valor"><?= htmlspecialchars($user['email_usuario']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fa-solid fa-venus-mars"></i> Gênero</span>
                <span class="info-valor"><?= htmlspecialchars($generoLabel[$user['genero_usuario']] ?? '—') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fa-solid fa-id-badge"></i> Tipo de conta</span>
                <span class="info-valor">
                    <span class="badge-tipo <?= $userTipo ?>"><?= htmlspecialchars($tipoLabel[$userTipo] ?? $userTipo) ?></span>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fa-solid fa-circle-dot"></i> Status</span>
                <span class="info-valor">
                    <span class="badge-status <?= $user['ativo_usuario'] ? 'ativo' : 'inativo' ?>">
                        <?= $user['ativo_usuario'] ? 'Ativa' : 'Inativa' ?>
                    </span>
                </span>
            </div>
            <?php if (!empty($user['nome_turma'])): ?>
            <div class="info-item">
                <span class="info-label"><i class="fa-solid fa-door-open"></i> Turma</span>
                <span class="info-valor"><?= htmlspecialchars($user['nome_turma']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fa-solid fa-book"></i> Curso</span>
                <span class="info-valor"><?= htmlspecialchars($user['nome_curso'] ?? '—') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fa-solid fa-sun"></i> Período</span>
                <span class="info-valor"><?= ucfirst($user['periodo_turma'] ?? '—') ?></span>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Inscrições -->
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

    <!-- Foto de Perfil -->
    <section class="conta-secao foto-secao reveal reveal-delay-3">
        <div class="secao-header">
            <i class="fa-solid fa-image"></i>
            <h2>Foto de Perfil</h2>
        </div>
        <div class="foto-area">
            <div class="foto-preview-wrap">
                <div class="foto-preview" id="fotoPreview">
                    <?php if (!empty($user['foto_perfil_usuario'])): ?>
                        <img src="<?= htmlspecialchars($user['foto_perfil_usuario']) ?>" alt="Preview">
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

    <!-- Sessão / Logout -->
    <section class="conta-secao logout-secao reveal reveal-delay-4">
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
    const _t = localStorage.getItem('theme');
    if (_t) document.documentElement.setAttribute('data-theme', _t);
</script>

<?php include __DIR__ . '/../includes/end.php';?>