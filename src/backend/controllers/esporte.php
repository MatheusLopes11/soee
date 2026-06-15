<?php
session_start();
include __DIR__ . '/../includes/conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome         = trim($_POST['nome_modalidade']        ?? '');
    $descricao    = trim($_POST['descricao_modalidade']   ?? '');
    $tipo         = trim($_POST['tipo_modalidade']        ?? '');
    $formato      = trim($_POST['formato_modalidade']     ?? '');
    $participacao = trim($_POST['tipo_participacao']      ?? '');
    $qtd_min      = isset($_POST['qtd_min_jogadores'])    ? intval($_POST['qtd_min_jogadores']) : 0;
    $qtd_max      = isset($_POST['qtd_max_jogadores'])    ? intval($_POST['qtd_max_jogadores']) : 0;
    $regulamento  = trim($_POST['regulamento_modalidade'] ?? '');

    $tipo_duracao    = trim($_POST['tipo_duracao']    ?? '');
    $duracao_minutos = trim($_POST['duracao_minutos'] ?? '');
    $duracao_pontos  = trim($_POST['duracao_pontos']  ?? '');
    $outro_minutos   = trim($_POST['outro_minutos']   ?? '');
    $outro_pontos    = trim($_POST['outro_pontos']    ?? '');
    
    if ($duracao_minutos === 'outro') $duracao_minutos = $outro_minutos;
    if ($duracao_pontos  === 'outro') $duracao_pontos  = $outro_pontos;

    $foto_path = null;
    $origem_foto = trim($_POST['origem_foto'] ?? 'upload');

    if ($origem_foto === 'upload' && !empty($_FILES['foto_arquivo']['name'])) {
        $extensoes_ok = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['foto_arquivo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $extensoes_ok)) {
            $erro = 'Formato de imagem inválido.';
        } elseif ($_FILES['foto_arquivo']['size'] > 5 * 1024 * 1024) {
            $erro = 'A imagem não pode ultrapassar 5 MB.';
        } else {
            $pasta = $_SERVER['DOCUMENT_ROOT'] . '/soee/src/frontend/assets/images/modalidades/';
            if (!is_dir($pasta)) mkdir($pasta, 0755, true);
            $nome_arquivo = 'modal_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['foto_arquivo']['tmp_name'], $pasta . $nome_arquivo)) {
                $foto_path = '/soee/src/frontend/assets/images/modalidades/' . $nome_arquivo;
            } else {
                $erro = 'Falha ao salvar a imagem.';
            }
        }
    } elseif ($origem_foto === 'url') {
        $url_externa = trim($_POST['foto_url'] ?? '');
        if (!empty($url_externa) && filter_var($url_externa, FILTER_VALIDATE_URL)) {
            $foto_path = $url_externa;
        } elseif (!empty($url_externa)) {
            $erro = 'A URL da imagem não é válida.';
        }
    }

    if (empty($erro)) {
        if ($nome === '' || $tipo === '' || $formato === '' || $participacao === '' || $regulamento === '' || $tipo_duracao === '') {
            $erro = 'dados_incompletos'; 
        } elseif ($qtd_min < 1) {
            $erro = 'A quantidade mínima deve ser pelo menos 1.';
        } elseif ($qtd_max < $qtd_min) {
            $erro = 'A quantidade máxima não pode ser menor que a mínima.';
        } elseif ($tipo_duracao === 'minutos' && $duracao_minutos === '') {
            $erro = 'dados_incompletos';
        } elseif ($tipo_duracao === 'pontos' && $duracao_pontos === '') {
            $erro = 'dados_incompletos';
        }
    }

    if (!empty($erro)) {
        
        $param_erro = ($erro === 'dados_incompletos') ? 'dados_incompletos' : urlencode($erro);
        header("Location: /soee/src/frontend/views/forms/criacao-esporte.php?erro=" . $param_erro);
        exit();
    }

    try {
        $id_modalidade = isset($_POST['id_modalidade']) && $_POST['id_modalidade'] !== '' ? intval($_POST['id_modalidade']) : null;

        if ($id_modalidade) {

            if ($origem_foto === 'upload' && empty($_FILES['foto_arquivo']['name'])) {
                $stmt_foto = $conn->prepare("SELECT foto_modalidade FROM modalidade WHERE id_modalidade = ?");
                $stmt_foto->execute([$id_modalidade]);
                $foto_path = $stmt_foto->fetchColumn();
            }

            $stmt = $conn->prepare("
                UPDATE modalidade SET
                    nome_modalidade = :nome, descricao_modalidade = :descricao, tipo_modalidade = :tipo,
                    formato_modalidade = :formato, tipo_participacao = :participacao,
                    qtd_min_jogadores = :qtd_min, qtd_max_jogadores = :qtd_max,
                    tipo_duracao = :tipo_duracao, duracao_minutos = :duracao_minutos, duracao_pontos = :duracao_pontos,
                    regulamento_modalidade = :regulamento, foto_modalidade = :foto
                WHERE id_modalidade = :id
            ");
            $stmt->execute([
                ':nome'            => $nome,
                ':descricao'       => $descricao,
                ':tipo'            => $tipo,
                ':formato'         => $formato,
                ':participacao'    => $participacao,
                ':qtd_min'         => $qtd_min,
                ':qtd_max'         => $qtd_max,
                ':tipo_duracao'    => $tipo_duracao,
                ':duracao_minutos' => $tipo_duracao === 'minutos' ? $duracao_minutos : null,
                ':duracao_pontos'  => $tipo_duracao === 'pontos'  ? $duracao_pontos  : null,
                ':regulamento'     => $regulamento,
                ':foto'            => $foto_path,
                ':id'              => $id_modalidade
            ]);
            header('Location: /soee/src/backend/php/pages/modalidades.php?alteracao=ok');
            exit();

        } else {

            $stmt = $conn->prepare("
                INSERT INTO modalidade
                    (nome_modalidade, descricao_modalidade, tipo_modalidade, formato_modalidade, tipo_participacao,
                     qtd_min_jogadores, qtd_max_jogadores, tipo_duracao, duracao_minutos, duracao_pontos,
                     regulamento_modalidade, foto_modalidade, ativo_modalidade)
                VALUES
                    (:nome, :descricao, :tipo, :formato, :participacao,
                     :qtd_min, :qtd_max, :tipo_duracao, :duracao_minutos, :duracao_pontos,
                     :regulamento, :foto, 1)
            ");
            $stmt->execute([
                ':nome'            => $nome,
                ':descricao'       => $descricao,
                ':tipo'            => $tipo,
                ':formato'         => $formato,
                ':participacao'    => $participacao,
                ':qtd_min'         => $qtd_min,
                ':qtd_max'         => $qtd_max,
                ':tipo_duracao'    => $tipo_duracao,
                ':duracao_minutos' => $tipo_duracao === 'minutos' ? $duracao_minutos : null,
                ':duracao_pontos'  => $tipo_duracao === 'pontos'  ? $duracao_pontos  : null,
                ':regulamento'     => $regulamento,
                ':foto'            => $foto_path,
            ]);
            header('Location: /soee/src/backend/php/pages/modalidades.php?cadastro=ok');
            exit();
        }

    } catch (PDOException $e) {
        $msg_erro = $e->getCode() === '23000' ? 'Já existe uma modalidade com esse nome.' : 'Erro: ' . $e->getMessage();
        header("Location: /soee/src/frontend/views/forms/criacao-esporte.php?erro=" . urlencode($msg_erro));
        exit();
    }
}