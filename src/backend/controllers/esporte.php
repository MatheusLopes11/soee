<?php
session_start();
include __DIR__ . '/../includes/conexao.php';

$erro;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ── 1. Coleta ── */
    $nome         = trim($_POST['nome_modalidade']        ?? '');
    $descricao    = trim($_POST['descricao_modalidade']   ?? '');
    $tipo         = trim($_POST['tipo_modalidade']        ?? '');
    $formato      = trim($_POST['formato_modalidade']     ?? '');
    $participacao = trim($_POST['tipo_participacao']      ?? '');
    $qtd_min      = intval($_POST['qtd_min_jogadores']    ?? 0);
    $qtd_max      = intval($_POST['qtd_max_jogadores']    ?? 0);
    $regulamento  = trim($_POST['regulamento_modalidade'] ?? '');

    /* ── Duração ── */
    $tipo_duracao    = trim($_POST['tipo_duracao']    ?? '');
    $duracao_minutos = trim($_POST['duracao_minutos'] ?? '');
    $duracao_pontos  = trim($_POST['duracao_pontos']  ?? '');
    $outro_minutos   = trim($_POST['outro_minutos']   ?? '');
    $outro_pontos    = trim($_POST['outro_pontos']    ?? '');
    // Se escolheu "outro", usa o valor digitado
    if ($duracao_minutos === 'outro') $duracao_minutos = $outro_minutos;
    if ($duracao_pontos  === 'outro') $duracao_pontos  = $outro_pontos;

    /* ── 2. Foto ── */
    $foto_path;
    $origem_foto = trim($_POST['origem_foto'] ?? 'upload'); // 'upload'|'url'|'nenhuma'

    if ($origem_foto === 'upload' && !empty($_FILES['foto_arquivo']['name'])) {
        $extensoes_ok = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['foto_arquivo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $extensoes_ok)) {
            $erro = 'Formato de imagem inválido. Use JPG, PNG, GIF ou WEBP.';
        } elseif ($_FILES['foto_arquivo']['size'] > 5 * 1024 * 1024) {
            $erro = 'A imagem não pode ultrapassar 5 MB.';
        } else {
            $pasta = $_SERVER['DOCUMENT_ROOT'] . '/soee/src/frontend/assets/images/modalidades/';
            if (!is_dir($pasta)) mkdir($pasta, 0755, true);
            $nome_arquivo = 'modal_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['foto_arquivo']['tmp_name'], $pasta . $nome_arquivo)) {
                $foto_path = '/soee/src/frontend/assets/images/modalidades/' . $nome_arquivo;
            } else {
                $erro = 'Falha ao salvar a imagem. Verifique as permissões da pasta.';
            }
        }
    } elseif ($origem_foto === 'url') {
        $url_externa = trim($_POST['foto_url'] ?? '');
        if (!empty($url_externa)) {
            if (filter_var($url_externa, FILTER_VALIDATE_URL)) {
                $foto_path = $url_externa;
            } else {
                $erro = 'A URL da imagem informada não é válida.';
            }
        }
    }
    // origem 'nenhuma' → $foto_path fica vazio, tudo certo

    /* ── 3. Validações ── */
    if (empty($erro)) {
        if (empty($nome))             $erro = 'O nome da modalidade é obrigatório.';
        elseif (empty($tipo))         $erro = 'Selecione o tipo da modalidade.';
        elseif (empty($formato))      $erro = 'Selecione o formato da competição.';
        elseif (empty($participacao)) $erro = 'Selecione o tipo de participação.';
        elseif ($qtd_min < 1)         $erro = 'A quantidade mínima de jogadores deve ser pelo menos 1.';
        elseif ($qtd_max < $qtd_min)  $erro = 'A quantidade máxima não pode ser menor que a mínima.';
        elseif (empty($regulamento))  $erro = 'O regulamento da modalidade é obrigatório.';
        elseif (empty($tipo_duracao))                               $erro = 'Selecione o tipo de duração da partida.';
        elseif ($tipo_duracao === 'minutos' && empty($duracao_minutos)) $erro = 'Selecione o formato de tempo da partida.';
        elseif ($tipo_duracao === 'pontos'  && empty($duracao_pontos))  $erro = 'Selecione a pontuação por partida.';
    }

    /* ── 4. Insere ── */
    if (empty($erro)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO modalidade
                    (nome_modalidade, descricao_modalidade, tipo_modalidade,
                     formato_modalidade, tipo_participacao,
                     qtd_min_jogadores, qtd_max_jogadores,
                     tipo_duracao, duracao_minutos, duracao_pontos,
                     regulamento_modalidade, foto_modalidade, ativo_modalidade)
                VALUES
                    (:nome, :descricao, :tipo, :formato, :participacao,
                     :qtd_min, :qtd_max,
                     :tipo_duracao, :duracao_minutos, :duracao_pontos,
                     :regulamento, :foto, 1)
            ");
            $stmt->execute([
                ':nome'           => $nome,
                ':descricao'      => $descricao,
                ':tipo'           => $tipo,
                ':formato'        => $formato,
                ':participacao'   => $participacao,
                ':qtd_min'        => $qtd_min,
                ':qtd_max'        => $qtd_max,
                ':tipo_duracao'    => $tipo_duracao,
                ':duracao_minutos' => $tipo_duracao === 'minutos' ? $duracao_minutos : null,
                ':duracao_pontos'  => $tipo_duracao === 'pontos'  ? $duracao_pontos  : null,
                ':regulamento'    => $regulamento,
                ':foto'           => $foto_path,
            ]);
            header('Location: /soee/src/backend/php/pages/modalidades.php?cadastro=ok');
            die();
        } catch (PDOException $e) {
            $erro = $e->getCode() === '23000'
                ? 'Já existe uma modalidade com esse nome.'
                : 'Erro ao cadastrar: ' . $e->getMessage();
        }
    }
}

/* helpers de re-exibição */
function sel(string $campo, string $val): string {
    return (($_POST[$campo] ?? '') === $val) ? 'selected' : '';
}
?>