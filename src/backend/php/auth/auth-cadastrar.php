<?php
ob_start();
include __DIR__ . '/../include/conexao.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /soee/src/backend/php/form/form-cadastrar.php");
    exit();
}

if (
    empty($_POST['nome'])   ||
    empty($_POST['email'])  ||
    empty($_POST['senha'])  ||
    empty($_POST['genero'])
) {
    die("Preencha todos os campos obrigatórios.");
}

$nome   = trim($_POST['nome']);
$email  = trim($_POST['email']);
$senha  = $_POST['senha'];

// gênero
$generoRaw = $_POST['genero'];
$genero = ($generoRaw === 'm' || $generoRaw === 'f') ? $generoRaw : 'n';

// senha segura
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

try {
    // verifica email
    $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE email_usuario = :email LIMIT 1");
    $stmt->execute([':email' => $email]);

    if ($stmt->fetch()) {
        die("E-mail já cadastrado.");
    }

    // insert
    $stmt = $conn->prepare("
        INSERT INTO usuario
        (nome_usuario, email_usuario, senha_usuario, genero_usuario)
        VALUES
        (:nome, :email, :senha, :genero)
    ");

    $stmt->execute([
        ':nome'   => $nome,
        ':email'  => $email,
        ':senha'  => $senhaHash,
        ':genero' => $genero,
    ]);

    header("Location: /soee/index.php?cadastro=sucesso");
    exit();

} catch (PDOException $e) {
    echo "Erro no cadastro: " . $e->getMessage();
}