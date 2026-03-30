<?php
include __DIR__ . '/../../../../pages/conexao.php';

# Verifica Se os Campos Foram Preenchidos
if(!isset($_POST['nome'],$_POST['email'],$_POST['mensagem'])) {
    header('Location: /soee/src/backend/php/form/form-feedback.php');
    die("Preenha os campos Obrigatórios.");
} else if((empty(['nome']) && empty(['email']) && empty(['mensagem']))) {
    header('Location: /soee/src/backend/php/form/form-feedback.php');
    die("Preencha os campos Obrigatórios.");
}

# Recebe o valores entregues do Formulário
$nome = $_POST['nome'];
$email = $_POST['email'];
$mensagem = $_POST['mensagem'];

try{
    $query_inserir = "insert into";

} catch(PDOException $erro) {
    echo "Erro: " . $this->erro->getMessage();
}
?>