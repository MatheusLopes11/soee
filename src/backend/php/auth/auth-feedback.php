<?php
declare(strict_types=1);

/* ─────────────────────────────────────────
   CONFIGURAÇÃO DO BANCO DE DADOS
   Ajuste as credenciais conforme seu ambiente.
───────────────────────────────────────── */
define('DB_HOST', 'localhost');
define('DB_NAME', 'soee');
define('DB_USER', 'root');       // Altere para seu usuário
define('DB_PASS', '');           // Altere para sua senha
define('DB_CHARSET', 'utf8mb4');

/* ─────────────────────────────────────────
   HELPERS
───────────────────────────────────────── */

/**
 * Verifica se a requisição espera JSON como resposta.
 * Considera chamadas fetch() com Content-Type multipart/form-data
 * vindas do JavaScript do form-feedback.php.
 */
function isAjax(): bool
{
    return (
        (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        ||
        (isset($_SERVER['HTTP_ACCEPT']) &&
         strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    );
}

/**
 * Envia resposta JSON e encerra a execução.
 */
function jsonResponse(bool $sucesso, string $mensagem, array $extra = []): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['sucesso' => $sucesso, 'mensagem' => $mensagem], $extra));
    exit;
}

/**
 * Redireciona para o formulário com status e mensagem.
 */
function redirecionar(string $status, string $msg = ''): void
{
    $url = 'form-feedback.php?status=' . urlencode($status);
    if ($msg !== '') {
        $url .= '&msg=' . urlencode($msg);
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Sanitiza string simples.
 */
function limpar(string $valor): string
{
    return trim(htmlspecialchars(strip_tags($valor), ENT_QUOTES, 'UTF-8'));
}

/* ─────────────────────────────────────────
   SOMENTE POST
───────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: form-feedback.php');
    exit;
}

/* ─────────────────────────────────────────
   COLETA E SANITIZAÇÃO DOS DADOS
───────────────────────────────────────── */
$anonimo   = isset($_POST['anonimo']) && $_POST['anonimo'] === '1';

$nome      = $anonimo ? 'Anônimo' : limpar($_POST['nome_feedback']    ?? '');
$turma     = $anonimo ? ''        : limpar($_POST['turma_feedback']    ?? '');
$email     = $anonimo ? ''        : limpar($_POST['email_feedback']    ?? '');
$tipo      = limpar($_POST['tipo_feedback']     ?? '');
$mensagem  = limpar($_POST['mensagem_feedback'] ?? '');
$nota      = (int) ($_POST['nota_feedback']     ?? 0);

// Categorias: array de checkboxes
$categoriasRaw = $_POST['categorias'] ?? [];
$categorias    = [];
$cats_validas  = ['organizacao', 'sistema', 'comunicacao', 'arbitragem', 'inscricoes', 'outro'];
foreach ((array) $categoriasRaw as $cat) {
    $cat = limpar($cat);
    if (in_array($cat, $cats_validas, true)) {
        $categorias[] = $cat;
    }
}
$categorias_str = implode(',', $categorias); // armazena como CSV

/* ─────────────────────────────────────────
   VALIDAÇÃO SERVER-SIDE
───────────────────────────────────────── */
$erros = [];

// Nota: 1–5
if ($nota < 1 || $nota > 5) {
    $erros[] = 'Selecione uma avaliação de 1 a 5 estrelas.';
}

// Nome (apenas se não anônimo)
if (!$anonimo && mb_strlen($nome) < 3) {
    $erros[] = 'Informe um nome válido (mínimo 3 caracteres).';
}

// E-mail (apenas se não anônimo)
if (!$anonimo && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = 'Informe um e-mail válido.';
}

// Turma (apenas se não anônimo)
$turmas_validas = ['1 MTEC','2 MTEC','3 MTEC','1 EMIF','2 EMIF','3 EMIF','1 PI','2 PI','3 PI'];
if (!$anonimo && !in_array($turma, $turmas_validas, true)) {
    $erros[] = 'Selecione uma turma válida.';
}

// Categorias
if (count($categorias) === 0) {
    $erros[] = 'Selecione ao menos uma categoria.';
}

// Tipo de feedback
$tipos_validos = ['elogio', 'sugestao', 'critica', 'problema'];
if (!in_array($tipo, $tipos_validos, true)) {
    $erros[] = 'Selecione um tipo de feedback válido.';
}

// Mensagem
if (mb_strlen($mensagem) < 20) {
    $erros[] = 'A mensagem deve ter no mínimo 20 caracteres.';
}
if (mb_strlen($mensagem) > 2000) {
    $erros[] = 'A mensagem não pode ultrapassar 2000 caracteres.';
}

// Se houver erros, retorna
if (!empty($erros)) {
    $msgErro = implode(' | ', $erros);
    if (isAjax()) {
        jsonResponse(false, $msgErro);
    }
    redirecionar('erro', $msgErro);
}

/* ─────────────────────────────────────────
   CONEXÃO COM O BANCO (PDO)
───────────────────────────────────────── */
try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST, DB_NAME, DB_CHARSET
    );
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Não expõe detalhes do banco para o usuário
    error_log('[SOEE-Feedback] Erro de conexão: ' . $e->getMessage());
    $msg = 'Não foi possível conectar ao servidor. Tente novamente em instantes.';
    if (isAjax()) {
        jsonResponse(false, $msg);
    }
    redirecionar('erro', $msg);
}

/* ─────────────────────────────────────────
   INSERÇÃO NA TABELA feedback
   
   Estrutura esperada:
   CREATE TABLE IF NOT EXISTS feedback (
       id_feedback       INT AUTO_INCREMENT PRIMARY KEY,
       nome_feedback     VARCHAR(120),
       email_feedback    VARCHAR(120),
       turma_feedback    VARCHAR(20),
       nota_feedback     TINYINT UNSIGNED NOT NULL,
       tipo_feedback     ENUM('elogio','sugestao','critica','problema') NOT NULL,
       categorias        VARCHAR(120),
       mensagem_feedback TEXT NOT NULL,
       anonimo           TINYINT(1) DEFAULT 0,
       data_feedback     DATETIME DEFAULT CURRENT_TIMESTAMP,
       status_feedback   ENUM('pendente','lido','respondido') DEFAULT 'pendente'
   );
───────────────────────────────────────── */

// Cria a tabela automaticamente caso ainda não exista
$pdo->exec("
    CREATE TABLE IF NOT EXISTS feedback (
        id_feedback       INT AUTO_INCREMENT PRIMARY KEY,
        nome_feedback     VARCHAR(120),
        email_feedback    VARCHAR(120),
        turma_feedback    VARCHAR(20),
        nota_feedback     TINYINT UNSIGNED NOT NULL,
        tipo_feedback     ENUM('elogio','sugestao','critica','problema') NOT NULL,
        categorias        VARCHAR(120),
        mensagem_feedback TEXT NOT NULL,
        anonimo           TINYINT(1) DEFAULT 0,
        data_feedback     DATETIME DEFAULT CURRENT_TIMESTAMP,
        status_feedback   ENUM('pendente','lido','respondido') DEFAULT 'pendente'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

try {
    $sql = "
        INSERT INTO feedback
            (nome_feedback, email_feedback, turma_feedback,
             nota_feedback, tipo_feedback, categorias,
             mensagem_feedback, anonimo)
        VALUES
            (:nome, :email, :turma,
             :nota, :tipo, :categorias,
             :mensagem, :anonimo)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome'       => $nome,
        ':email'      => $email,
        ':turma'      => $turma,
        ':nota'       => $nota,
        ':tipo'       => $tipo,
        ':categorias' => $categorias_str,
        ':mensagem'   => $mensagem,
        ':anonimo'    => (int) $anonimo,
    ]);

    $idInserido = (int) $pdo->lastInsertId();

} catch (PDOException $e) {
    error_log('[SOEE-Feedback] Erro ao inserir: ' . $e->getMessage());
    $msg = 'Erro ao salvar o feedback. Tente novamente.';
    if (isAjax()) {
        jsonResponse(false, $msg);
    }
    redirecionar('erro', $msg);
}

/* ─────────────────────────────────────────
   SUCESSO
───────────────────────────────────────── */
if (isAjax()) {
    jsonResponse(true, 'Feedback enviado com sucesso!', ['id' => $idInserido]);
}

redirecionar('sucesso');