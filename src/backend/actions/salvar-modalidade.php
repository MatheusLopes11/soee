<?php

require_once __DIR__ . '/../includes/conexao.php';

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /soee/src/frontend/views/dashboards/adm-sala.php");
    exit;
}

// Inicia sessão apenas se ainda não estiver ativa (para flash msg)
if (session_status() === PHP_SESSION_NONE) session_start();

// ── Detecta a origem da requisição ────────────────────────────────
// O modal do adm-sala envia id_modalidade (mesmo que vazio); o form
// esporte.php nunca envia esse campo. Usamos isso para saber o destino.
$viaModal  = isset($_POST['id_modalidade']);
$idModal   = (int) ($_POST['id_modalidade'] ?? 0);

// Páginas de destino
$backSala   = '/soee/src/frontend/views/dashboards/adm-sala.php';
$backEsporte = '/soee/src/frontend/views/forms/esporte.php';

function redirSala(string $msg, string $tipo = 'sucesso'): never {
    global $backSala;
    $_SESSION['flash_msg']  = $msg;
    $_SESSION['flash_tipo'] = $tipo;
    header("Location: $backSala");
    exit;
}

function redirEsporte(string $erro): never {
    global $backEsporte;
    header("Location: $backEsporte?erro=$erro");
    exit;
}

// ── Coleta campos comuns ───────────────────────────────────────────
$nome         = trim($_POST['nome_modalidade']        ?? '');
$tipo         = $_POST['tipo_modalidade']             ?? null;
$formato      = $_POST['formato_modalidade']          ?? null;
$participacao = $_POST['tipo_participacao']           ?? null;
$min          = $_POST['qtd_min_jogadores']           ?? null;
$max          = $_POST['qtd_max_jogadores']           ?? null;
$descricao    = trim($_POST['descricao_modalidade']   ?? '');
$regulamento  = trim($_POST['regulamento_modalidade'] ?? '');
$tipoDuracao  = $_POST['tipo_duracao']                ?? null;
$ativo        = isset($_POST['ativo_modalidade']) ? 1 : 1; // padrão ativo

// Valida campos obrigatórios
if (!$nome || !$tipo || !$formato || !$participacao || !$min || !$max) {
    if ($viaModal) redirSala('Preencha todos os campos obrigatórios.', 'erro');
    else redirEsporte('dados_incompletos');
}

// ── Duração ────────────────────────────────────────────────────────
$duracaoMinutos = null;
$duracaoPontos  = null;

if ($tipoDuracao === 'minutos') {
    // Compatibilidade com esporte.php (select com opção "outro")
    $val = $_POST['duracao_minutos'] ?? '';
    $duracaoMinutos = ($val === 'outro')
        ? trim($_POST['outro_minutos'] ?? '')
        : $val;
    if (!$duracaoMinutos) $tipoDuracao = null;

} elseif ($tipoDuracao === 'pontos') {
    $val = $_POST['duracao_pontos'] ?? '';
    $duracaoPontos = ($val === 'outro')
        ? (int)($_POST['outro_pontos'] ?? 0)
        : (int)$val;
    if (!$duracaoPontos) $tipoDuracao = null;

} else {
    $tipoDuracao = null;
}

// ── Foto ───────────────────────────────────────────────────────────
$fotoFinal  = null;
$origemFoto = $_POST['origem_foto'] ?? 'upload'; // upload | url | nenhuma

if ($origemFoto === 'url') {
    $url = trim($_POST['foto_url'] ?? '');
    if ($url !== '') $fotoFinal = $url;

} elseif ($origemFoto === 'upload') {
    // Campo pode se chamar "foto_arquivo" (modal adm-sala) ou "foto_arquivo" (esporte.php)
    $fileKey = isset($_FILES['foto_arquivo']) ? 'foto_arquivo' : null;

    if ($fileKey && !empty($_FILES[$fileKey]['name'])) {
        $file      = $_FILES[$fileKey];
        $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $permitidos)) {
            if ($viaModal) redirSala('Formato de imagem não suportado.', 'erro');
            else redirEsporte('foto_invalida');
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            if ($viaModal) redirSala('A imagem deve ter menos de 5 MB.', 'erro');
            else redirEsporte('foto_grande');
        }

        $nomeArquivo = uniqid('modal_', true) . '.' . $ext;
        $destino     = $_SERVER['DOCUMENT_ROOT'] . '/soee/src/frontend/assets/images/modalidades/';

        if (!is_dir($destino)) mkdir($destino, 0755, true);

        if (move_uploaded_file($file['tmp_name'], $destino . $nomeArquivo)) {
            $fotoFinal = '/soee/src/frontend/assets/images/modalidades/' . $nomeArquivo;
        } else {
            if ($viaModal) redirSala('Falha ao salvar a imagem.', 'erro');
            else redirEsporte('erro_db');
        }
    }
}
// se nenhuma das condições acima: $fotoFinal permanece null

// ── Persistência ───────────────────────────────────────────────────
try {
    if ($idModal > 0) {
        // ── UPDATE ─────────────────────────────────────────────────
        $sql = "
            UPDATE modalidade SET
                nome_modalidade        = ?,
                descricao_modalidade   = ?,
                tipo_modalidade        = ?,
                formato_modalidade     = ?,
                tipo_participacao      = ?,
                qtd_min_jogadores      = ?,
                qtd_max_jogadores      = ?,
                ativo_modalidade       = ?,
                regulamento_modalidade = ?,
                tipo_duracao           = ?,
                duracao_minutos        = ?,
                duracao_pontos         = ?
        ";
        $params = [
            $nome, $descricao ?: null, $tipo,
            $formato, $participacao,
            (int)$min, (int)$max,
            $ativo,
            $regulamento ?: null,
            $tipoDuracao,
            $duracaoMinutos,
            $duracaoPontos,
        ];

        // Só atualiza foto se um novo arquivo foi enviado
        if ($fotoFinal !== null) {
            $sql .= ', foto_modalidade = ?';
            $params[] = $fotoFinal;
        }

        $sql .= ' WHERE id_modalidade = ?';
        $params[] = $idModal;

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        redirSala('Modalidade atualizada com sucesso!');

    } else {
        // ── INSERT ─────────────────────────────────────────────────
        $sql = "
            INSERT INTO modalidade
                (nome_modalidade, descricao_modalidade, tipo_modalidade,
                 formato_modalidade, tipo_participacao,
                 qtd_min_jogadores, qtd_max_jogadores,
                 ativo_modalidade, foto_modalidade, regulamento_modalidade,
                 tipo_duracao, duracao_minutos, duracao_pontos)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?)
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $nome,
            $descricao ?: null,
            $tipo,
            $formato,
            $participacao,
            (int)$min,
            (int)$max,
            $fotoFinal,
            $regulamento ?: null,
            $tipoDuracao,
            $duracaoMinutos,
            $duracaoPontos,
        ]);

        // Redireciona conforme a origem
        if ($viaModal) {
            redirSala('Modalidade criada com sucesso!');
        } else {
            // Origem: esporte.php (comportamento original)
            header("Location: /soee/src/frontend/views/dashboards/adm.php?cadastro=ok");
            exit;
        }
    }

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        // Nome duplicado (UNIQUE constraint)
        if ($viaModal) redirSala('Já existe uma modalidade com esse nome.', 'erro');
        else redirEsporte('modalidade_duplicada');
    }
    error_log('[salvar-modalidade] ' . $e->getMessage());
    if ($viaModal) redirSala('Erro interno ao salvar. Tente novamente.', 'erro');
    else redirEsporte('erro_db');
}