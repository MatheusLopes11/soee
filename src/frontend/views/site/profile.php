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

// ─── Edição de informações pessoais ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'editar_perfil') {

    $novoNome    = trim($_POST['nome_usuario']   ?? '');
    $novoEmail   = trim($_POST['email_usuario']  ?? '');
    $novoGenero  = $_POST['genero_usuario']      ?? '';
    $novoTurmaId = (int)($_POST['turma_id']      ?? 0);
    $numCamisa   = trim($_POST['numero_camisa']  ?? '');
    $nomeCamisa  = trim($_POST['nome_camisa']    ?? '');

    $generosValidos = ['m', 'f', 'n'];
    $erros = [];

    if (empty($novoNome))  $erros[] = 'Nome é obrigatório.';
    if (empty($novoEmail)) $erros[] = 'E-mail é obrigatório.';
    elseif (!filter_var($novoEmail, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
    if (!in_array($novoGenero, $generosValidos)) $erros[] = 'Gênero inválido.';
    if ($numCamisa !== '' && (!ctype_digit($numCamisa) || (int)$numCamisa < 1 || (int)$numCamisa > 999))
        $erros[] = 'Número de camisa inválido (1 a 999).';

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
                genero_usuario = :genero,
                turma_id_turma = :turma
            WHERE id_usuario = :id
        ")->execute([
            ':nome'   => $novoNome,
            ':email'  => $novoEmail,
            ':genero' => $novoGenero,
            ':turma'  => $novoTurmaId > 0 ? $novoTurmaId : null,
            ':id'     => $userId,
        ]);

        // Atualiza camisa na inscrição ativa mais recente
        $conn->prepare("
            UPDATE inscricao
            SET numero_camisa_inscricao = :num,
                nome_camisa_inscricao   = :nome_camisa
            WHERE usuario_id_usuario = :uid
              AND status_inscricao   = 'ativa'
            ORDER BY data_inscricao DESC
            LIMIT 1
        ")->execute([
            ':num'         => $numCamisa !== '' ? (int)$numCamisa : null,
            ':nome_camisa' => $nomeCamisa !== '' ? strtoupper($nomeCamisa) : null,
            ':uid'         => $userId,
        ]);

        $msgEdit  = 'Perfil atualizado com sucesso!';
        $tipoEdit = 'sucesso';
    }
}

// ─── Busca dados do usuário ───────────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT u.id_usuario, u.nome_usuario, u.email_usuario,
           u.genero_usuario, u.tipo_usuario, u.ativo_usuario,
           u.foto_perfil_usuario, u.turma_id_turma,
           t.nome_turma, t.periodo_turma,
           c.nome_curso, c.sigla_curso
    FROM usuario u
    LEFT JOIN turma t ON t.id_turma = u.turma_id_turma
    LEFT JOIN curso c ON c.id_curso = t.curso_id_curso
    WHERE u.id_usuario = :id
");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Lista todas as turmas para o select ─────────────────────────────────────
$turmas = $conn->query("
    SELECT t.id_turma, t.nome_turma, t.periodo_turma,
           c.nome_curso, c.sigla_curso
    FROM turma t
    INNER JOIN curso c ON c.id_curso = t.curso_id_curso
    ORDER BY c.sigla_curso, t.nome_turma
")->fetchAll(PDO::FETCH_ASSOC);

// ─── Inscrições ───────────────────────────────────────────────────────────────
$stmtIns = $conn->prepare("
    SELECT i.numero_camisa_inscricao, i.nome_camisa_inscricao,
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

$camisaAtual = null;
foreach ($inscricoes as $ins) {
    if ($ins['status_inscricao'] === 'ativa') { $camisaAtual = $ins; break; }
}

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
    <style>
        /* ── Campos editáveis: texto/input alternados pelo modo ──── */
        .campo-editavel .val-texto { display: block; }
        .campo-editavel .val-input { display: none;  }

        .secao-editando .campo-editavel .val-texto { display: none;  }
        .secao-editando .campo-editavel .val-input  { display: block; }

        /* ── Campos de camisa: sempre visíveis, input só ao editar ── */
        .campo-camisa .val-texto { display: block; }
        .campo-camisa .val-input { display: none;  }

        .secao-editando .campo-camisa .val-texto { display: none;  }
        .secao-editando .campo-camisa .val-input  { display: block; }

        /* ── Estilo dos inputs/selects no modo edição ─────────────── */
        .secao-editando .val-input {
            background  : var(--input-bg, rgba(255,255,255,.07));
            border      : 1.5px solid var(--accent, #6c63ff);
            border-radius: 8px;
            color       : inherit;
            font        : inherit;
            font-size   : .95rem;
            padding     : .38rem .7rem;
            width       : 100%;
            transition  : border-color .2s, box-shadow .2s;
        }
        .secao-editando .val-input:focus {
            outline    : none;
            box-shadow : 0 0 0 3px color-mix(in srgb, var(--accent,#6c63ff) 30%, transparent);
        }

        /* ── Valor vazio nos campos de camisa ─────────────────────── */
        .valor-vazio {
            opacity: .42;
            font-style: italic;
            font-size: .88rem;
        }

        /* ── Nome na camisa: destaque tipográfico ─────────────────── */
        .camisa-nome-destaque {
            font-family   : monospace;
            letter-spacing: .12em;
            font-weight   : 700;
        }

        /* ── Hint "opcional" ──────────────────────────────────────── */
        .hint-opt {
            font-size  : .72rem;
            opacity    : .48;
            font-weight: 400;
            margin-left: .25rem;
        }

        /* ── Badge "Editando" no cabeçalho da seção ───────────────── */
        .badge-editando {
            display      : none;
            font-size    : .7rem;
            font-weight  : 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            background   : var(--accent, #6c63ff);
            color        : #fff;
            padding      : .16rem .55rem;
            border-radius: 99px;
            margin-left  : .55rem;
            vertical-align: middle;
        }
        .secao-editando .badge-editando { display: inline; }

        /* ── Barra de ações ───────────────────────────────────────── */
        .secao-acoes {
            display    : flex;
            gap        : .75rem;
            margin-top : 1.4rem;
            flex-wrap  : wrap;
            align-items: center;
        }
        .btn-editar,
        .btn-salvar-edicao,
        .btn-cancelar-edicao {
            display    : inline-flex;
            align-items: center;
            gap        : .45rem;
            padding    : .52rem 1.15rem;
            border-radius: 8px;
            font-size  : .9rem;
            font-weight: 600;
            cursor     : pointer;
            border     : none;
            transition : opacity .2s, transform .15s;
        }
        .btn-editar          { background: var(--accent, #6c63ff); color: #fff; }
        .btn-salvar-edicao   { background: #22c55e; color: #fff; display: none; }
        .btn-cancelar-edicao {
            background: transparent;
            border    : 1.5px solid #ef4444;
            color     : #ef4444;
            display   : none;
        }
        .btn-editar:hover, .btn-salvar-edicao:hover { opacity: .88; transform: translateY(-1px); }
        .btn-cancelar-edicao:hover { background: rgba(239,68,68,.08); }

        /* Troca visibilidade na edição */
        .secao-editando .btn-editar          { display: none; }
        .secao-editando .btn-salvar-edicao,
        .secao-editando .btn-cancelar-edicao { display: inline-flex; }

        /* ── Toast de edição: posição inferior ────────────────────── */
        .toast-edit { top: auto; bottom: 1.5rem; }
    </style>
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
         Informações Pessoais — todos os campos editáveis
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
                    <span class="info-valor">
                        <span class="val-texto"><?= htmlspecialchars($user['nome_usuario']) ?></span>
                        <input class="val-input" type="text" name="nome_usuario"
                               value="<?= htmlspecialchars($user['nome_usuario']) ?>"
                               placeholder="Nome completo" maxlength="100" required>
                    </span>
                </div>

                <!-- E-mail -->
                <div class="info-item campo-editavel">
                    <span class="info-label"><i class="fa-solid fa-envelope"></i> E-mail</span>
                    <span class="info-valor">
                        <span class="val-texto"><?= htmlspecialchars($user['email_usuario']) ?></span>
                        <input class="val-input" type="email" name="email_usuario"
                               value="<?= htmlspecialchars($user['email_usuario']) ?>"
                               placeholder="E-mail" maxlength="120" required>
                    </span>
                </div>

                <!-- Gênero -->
                <div class="info-item campo-editavel">
                    <span class="info-label"><i class="fa-solid fa-venus-mars"></i> Gênero</span>
                    <span class="info-valor">
                        <span class="val-texto"><?= htmlspecialchars($generoLabel[$user['genero_usuario']] ?? '—') ?></span>
                        <select class="val-input" name="genero_usuario">
                            <option value="m" <?= $user['genero_usuario'] === 'm' ? 'selected' : '' ?>>Masculino</option>
                            <option value="f" <?= $user['genero_usuario'] === 'f' ? 'selected' : '' ?>>Feminino</option>
                            <option value="n" <?= $user['genero_usuario'] === 'n' ? 'selected' : '' ?>>Não informado</option>
                        </select>
                    </span>
                </div>

                <!-- Tipo de conta (somente leitura — controlado pelo sistema) -->
                <div class="info-item">
                    <span class="info-label"><i class="fa-solid fa-id-badge"></i> Tipo de conta</span>
                    <span class="info-valor">
                        <span class="badge-tipo <?= $userTipo ?>"><?= htmlspecialchars($tipoLabel[$userTipo] ?? $userTipo) ?></span>
                    </span>
                </div>

                <!-- Status (somente leitura) -->
                <div class="info-item">
                    <span class="info-label"><i class="fa-solid fa-circle-dot"></i> Status</span>
                    <span class="info-valor">
                        <span class="badge-status <?= $user['ativo_usuario'] ? 'ativo' : 'inativo' ?>">
                            <?= $user['ativo_usuario'] ? 'Ativa' : 'Inativa' ?>
                        </span>
                    </span>
                </div>

                <!-- Turma (select com todas as turmas) -->
                <div class="info-item campo-editavel">
                    <span class="info-label"><i class="fa-solid fa-door-open"></i> Turma</span>
                    <span class="info-valor">
                        <span class="val-texto"><?= htmlspecialchars($user['nome_turma'] ?? '—') ?></span>
                        <select class="val-input" name="turma_id" id="selectTurma">
                            <option value="0">— Sem turma —</option>
                            <?php foreach ($turmas as $t): ?>
                            <option value="<?= $t['id_turma'] ?>"
                                <?= (int)$user['turma_id_turma'] === (int)$t['id_turma'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['nome_turma']) ?> · <?= htmlspecialchars($t['sigla_curso']) ?> · <?= ucfirst($t['periodo_turma']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </span>
                </div>

                <!-- Curso (derivado da turma, somente leitura) -->
                <div class="info-item">
                    <span class="info-label"><i class="fa-solid fa-book"></i> Curso</span>
                    <span class="info-valor" id="campoCurso"><?= htmlspecialchars($user['nome_curso'] ?? '—') ?></span>
                </div>

                <!-- Período (derivado da turma, somente leitura) -->
                <div class="info-item">
                    <span class="info-label"><i class="fa-solid fa-sun"></i> Período</span>
                    <span class="info-valor" id="campoPeriodo"><?= ucfirst($user['periodo_turma'] ?? '—') ?></span>
                </div>

                <!-- ── Número da camisa (sempre visível) ─────────── -->
                <div class="info-item campo-camisa">
                    <span class="info-label">
                        <i class="fa-solid fa-shirt"></i> Número da camisa
                        <span class="hint-opt">(opcional)</span>
                    </span>
                    <span class="info-valor">
                        <span class="val-texto">
                            <?php if (!empty($camisaAtual['numero_camisa_inscricao'])): ?>
                                #<?= htmlspecialchars($camisaAtual['numero_camisa_inscricao']) ?>
                            <?php else: ?>
                                <span class="valor-vazio">Não informado</span>
                            <?php endif; ?>
                        </span>
                        <input class="val-input" type="number" name="numero_camisa"
                               min="1" max="999"
                               value="<?= htmlspecialchars($camisaAtual['numero_camisa_inscricao'] ?? '') ?>"
                               placeholder="Ex: 10">
                    </span>
                </div>

                <!-- ── Nome na camisa (sempre visível) ───────────── -->
                <div class="info-item campo-camisa">
                    <span class="info-label">
                        <i class="fa-solid fa-tag"></i> Nome na camisa
                        <span class="hint-opt">(opcional)</span>
                    </span>
                    <span class="info-valor">
                        <span class="val-texto">
                            <?php if (!empty($camisaAtual['nome_camisa_inscricao'])): ?>
                                <span class="camisa-nome-destaque"><?= htmlspecialchars(strtoupper($camisaAtual['nome_camisa_inscricao'])) ?></span>
                            <?php else: ?>
                                <span class="valor-vazio">Não informado</span>
                            <?php endif; ?>
                        </span>
                        <input class="val-input" type="text" name="nome_camisa"
                               maxlength="20"
                               value="<?= htmlspecialchars($camisaAtual['nome_camisa_inscricao'] ?? '') ?>"
                               placeholder="Ex: SILVA"
                               style="text-transform:uppercase; letter-spacing:.1em;">
                    </span>
                </div>

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
                    <span style="font-size:.68rem;letter-spacing:.1em;opacity:.7;font-weight:700;">
                        <?= htmlspecialchars(strtoupper($ins['nome_camisa_inscricao'])) ?>
                    </span>
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

/* ── Mapa de turmas para atualizar Curso/Período via JS ──────────── */
const turmasData = <?= json_encode(array_column($turmas, null, 'id_turma'), JSON_UNESCAPED_UNICODE) ?>;

/* ── Referências ──────────────────────────────────────────────────── */
const secao       = document.getElementById('secaoInfoPessoais');
const btnEditar   = document.getElementById('btnEditar');
const btnCancelar = document.getElementById('btnCancelar');
const selectTurma = document.getElementById('selectTurma');
const campoCurso  = document.getElementById('campoCurso');
const campoPeriodo= document.getElementById('campoPeriodo');

/* Captura snapshot dos valores atuais para o Cancelar */
const snapshot = {};
secao.querySelectorAll('input.val-input, select.val-input').forEach(el => {
    snapshot[el.name] = el.value;
});
/* Snapshot dos campos de texto derivados */
const snapCurso   = campoCurso   ? campoCurso.textContent   : '';
const snapPeriodo = campoPeriodo ? campoPeriodo.textContent : '';

/* ── Ativar modo edição ───────────────────────────────────────────── */
btnEditar.addEventListener('click', () => {
    secao.classList.add('secao-editando');
    const primeiroInput = secao.querySelector('input.val-input');
    if (primeiroInput) primeiroInput.focus();
});

/* ── Cancelar ────────────────────────────────────────────────────── */
btnCancelar.addEventListener('click', () => {
    secao.querySelectorAll('input.val-input, select.val-input').forEach(el => {
        el.value = snapshot[el.name] ?? '';
    });
    if (campoCurso)   campoCurso.textContent   = snapCurso;
    if (campoPeriodo) campoPeriodo.textContent = snapPeriodo;
    secao.classList.remove('secao-editando');
});

/* ── Atualiza Curso e Período ao trocar a turma ──────────────────── */
function syncTurma(idTurma) {
    const t = turmasData[idTurma];
    campoCurso.textContent   = t ? (t.nome_curso ?? t.sigla_curso ?? '—') : '—';
    campoPeriodo.textContent = t
        ? (t.periodo_turma.charAt(0).toUpperCase() + t.periodo_turma.slice(1))
        : '—';
}
if (selectTurma) {
    selectTurma.addEventListener('change', () => syncTurma(parseInt(selectTurma.value)));
}

/* ── Nome na camisa → força maiúsculas ao digitar ────────────────── */
const inputNomeCamisa = document.querySelector('input[name="nome_camisa"]');
if (inputNomeCamisa) {
    inputNomeCamisa.addEventListener('input', function () {
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });
}

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