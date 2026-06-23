<?php

// Caminho para o arquivo .env na raiz do projeto (soee/.env)
$envPath = __DIR__ . '/../../../.env';

if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
    $host = $env['DB_HOST'] ?? '';
    $port = $env['DB_PORT'] ?? '';
    $dbname = $env['DB_NAME'] ?? '';
    $user = $env['DB_USER'] ?? '';
    $password = $env['DB_PASS'] ?? '';
} else {
    // Fallback caso não encontre (em produção pode estar nas variáveis de ambiente do sistema)
    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT');
    $dbname = getenv('DB_NAME');
    $user = getenv('DB_USER');
    $password = getenv('DB_PASS');
}

try {
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Em produção, não exiba o erro detalhado para o usuário
    echo "Erro de conexão com o banco de dados.";
    error_log("Erro de conexão PDO: " . $e->getMessage());
}
?>