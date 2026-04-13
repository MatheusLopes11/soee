<?php
require_once __DIR__ . '/../includes/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /soee/src/frontend/views/dashboards/adm.php");
    exit;
}

try {
    $nome         = trim($_POST['nome_modalidade']        ?? '');
    $tipo         = $_POST['tipo_modalidade']             ?? null;
    $formato      = $_POST['formato_modalidade']          ?? null;
    $participacao = $_POST['tipo_participacao']           ?? null;
    $min          = $_POST['qtd_min_jogadores']           ?? null;
    $max          = $_POST['qtd_max_jogadores']           ?? null;
    $descricao    = trim($_POST['descricao_modalidade']   ?? '');
    $regulamento  = trim($_POST['regulamento_modalidade'] ?? '');
    $tipoDuracao  = $_POST['tipo_duracao']                ?? null;
    $origemFoto   = $_POST['origem_foto']                 ?? 'nenhuma';

    // ── Validação básica ───────────────────────────────────
    if (!$nome || !$tipo || !$formato || !$participacao || !$min || !$max) {
        header("Location: /soee/src/frontend/views/forms/esporte.php?erro=dados_incompletos");
        exit;
    }

    // ── Duração ────────────────────────────────────────────
    $duracaoMinutos = null;
    $duracaoPontos  = null;

    if ($tipoDuracao === 'minutos') {
        $val = $_POST['duracao_minutos'] ?? '';
        $duracaoMinutos = ($val === 'outro')
            ? trim($_POST['outro_minutos'] ?? '')
            : $val;
    } elseif ($tipoDuracao === 'pontos') {
        $val = $_POST['duracao_pontos'] ?? '';
        $duracaoPontos = ($val === 'outro')
            ? (int)($_POST['outro_pontos'] ?? 0)
            : (int)$val;
    }

    // ── Foto ───────────────────────────────────────────────
    $fotoFinal = null;

    if ($origemFoto === 'url') {
        $url = trim($_POST['foto_url'] ?? '');
        if ($url !== '') $fotoFinal = $url;

    } elseif ($origemFoto === 'upload') {
        if (!empty($_FILES['foto_arquivo']['name'])) {
            $ext        = strtolower(pathinfo($_FILES['foto_arquivo']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $permitidos)) {
                header("Location: /soee/src/frontend/views/forms/esporte.php?erro=foto_invalida");
                exit;
            }

            if ($_FILES['foto_arquivo']['size'] > 5 * 1024 * 1024) {
                header("Location: /soee/src/frontend/views/forms/esporte.php?erro=foto_grande");
                exit;
            }

            $nomeArquivo = uniqid('modal_') . '.' . $ext;
            $destino     = $_SERVER['DOCUMENT_ROOT'] . '/soee/src/frontend/assets/images/modalidades/';

            if (!is_dir($destino)) {
                mkdir($destino, 0755, true);
            }

            if (move_uploaded_file($_FILES['foto_arquivo']['tmp_name'], $destino . $nomeArquivo)) {
                $fotoFinal = '/soee/src/frontend/assets/images/modalidades/' . $nomeArquivo;
            }
        }
    }
    // se origemFoto === 'nenhuma', fotoFinal permanece null

    // ── INSERT ─────────────────────────────────────────────
    $sql = "INSERT INTO modalidade 
        (nome_modalidade, descricao_modalidade, tipo_modalidade,
         formato_modalidade, tipo_participacao,
         qtd_min_jogadores, qtd_max_jogadores,
         ativo_modalidade, foto_modalidade, regulamento_modalidade,
         tipo_duracao, duracao_minutos, duracao_pontos)
    VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $nome, $descricao, $tipo,
        $formato, $participacao,
        $min, $max,
        $fotoFinal, $regulamento,
        $tipoDuracao, $duracaoMinutos, $duracaoPontos
    ]);

    header("Location: /soee/src/frontend/views/dashboards/adm.php?cadastro=ok");
    exit;

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        header("Location: /soee/src/frontend/views/forms/esporte.php?erro=modalidade_duplicada");
    } else {
        header("Location: /soee/src/frontend/views/forms/esporte.php?erro=erro_db");
    }
    exit;
}
?>