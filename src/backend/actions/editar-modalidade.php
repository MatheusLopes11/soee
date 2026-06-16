<?php
header('Content-Type: application/json');

// Credenciais do pooler do seu Supabase obtidas do arquivo de configuração do projeto
$host = "aws-1-sa-east-1.pooler.supabase.com";
$port = "6543";
$dbname = "postgres";
$user = "postgres.xxejjfxpzucchmvvpicv";
$password = "65cjZSkbIzKLgSnF";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro na conexão com o banco: ' . $e->getMessage()]);
    exit;
}

// Verificação de segurança de inputs obrigatórios
if (empty($_POST['id_modalidade']) || empty($_POST['nome_modalidade'])) {
    echo json_encode(['success' => false, 'message' => 'Dados essenciais incompletos para a atualização.']);
    exit;
}

try {
    $id_modalidade      = (int)$_POST['id_modalidade'];
    $nome_modalidade    = trim($_POST['nome_modalidade']);
    $tipo_modalidade    = $_POST['tipo_modalidade']; // 'quadra' | 'mesa' | 'outro'
    $tipo_participacao  = $_POST['tipo_participacao']; // 'solo' | 'dupla' | 'trio' | 'time'
    $formato_modalidade = $_POST['formato_modalidade']; // 'mata_mata' | 'grupos' | etc
    $genero_modalidade  = $_POST['genero_modalidade']; // 'masculino' | 'feminino' | 'misto'
    
    $qtd_min_jogadores  = (int)$_POST['qtd_min_jogadores'];
    $qtd_max_jogadores  = (int)$_POST['qtd_max_jogadores'];
    
    $tipo_duracao       = !empty($_POST['tipo_duracao']) ? $_POST['tipo_duracao'] : null;
    $duracao_minutos    = !empty($_POST['duracao_minutos']) ? trim($_POST['duracao_minutos']) : null; // VARCHAR(10) no banco
    $duracao_pontos     = (!empty($_POST['duracao_pontos']) || $_POST['duracao_pontos'] === '0') ? (int)$_POST['duracao_pontos'] : null; // SMALLINT
    
    $descricao_modalidade = isset($_POST['descricao_modalidade']) ? trim($_POST['descricao_modalidade']) : null;
    $ativo_modalidade   = ($_POST['ativo_modalidade'] === '1'); // Booleano real para o Postgres

    // Query em total harmonia com o Schema da tabela modalidade
    $sql = "UPDATE modalidade SET 
                nome_modalidade = :nome,
                descricao_modalidade = :descricao,
                tipo_modalidade = :tipo_mod,
                formato_modalidade = :formato,
                tipo_participacao = :tipo_part,
                qtd_min_jogadores = :qtd_min,
                qtd_max_jogadores = :qtd_max,
                ativo_modalidade = :ativo,
                genero_modalidade = :genero,
                tipo_duracao = :tipo_dur,
                duracao_minutos = :dur_min,
                duracao_pontos = :dur_pt
            WHERE id_modalidade = :id";

    $stmt = $conn->prepare($sql);
    
    $stmt->bindParam(':nome', $nome_modalidade);
    $stmt->bindParam(':descricao', $descricao_modalidade);
    $stmt->bindParam(':tipo_mod', $tipo_modalidade);
    $stmt->bindParam(':formato', $formato_modalidade);
    $stmt->bindParam(':tipo_part', $tipo_participacao);
    $stmt->bindParam(':qtd_min', $qtd_min_jogadores, PDO::PARAM_INT);
    $stmt->bindParam(':qtd_max', $qtd_max_jogadores, PDO::PARAM_INT);
    $stmt->bindParam(':ativo', $ativo_modalidade, PDO::PARAM_BOOL);
    $stmt->bindParam(':genero', $genero_modalidade);
    $stmt->bindParam(':tipo_dur', $tipo_duracao);
    $stmt->bindParam(':dur_min', $duracao_minutos);
    $stmt->bindParam(':dur_pt', $duracao_pontos, $duracao_pontos === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindParam(':id', $id_modalidade, PDO::PARAM_INT);

    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Atualizado com sucesso!']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro de restrição no Supabase: ' . $e->getMessage()]);
}
?>