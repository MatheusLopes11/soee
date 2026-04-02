<?php
try{
include __DIR__ . '/../../../../include/conexao.php';

# Verifica Se os Campos Foram Preenchidos
if(!isset($_POST['nome'],$_POST['email'],$_POST['mensagem'])) {
    header('Location: /soee/src/backend/php/form/form-feedback.php');
    die("Preenha os campos Obrigatórios.");
} 

# Recebe o valores entregues do Formulário
$nome = $_POST['nome'];
$email = $_POST['email'];
$mensagem = $_POST['mensagem'];

$sql = "insert into ";

} catch(PDOException $erro) {
    echo "Erro no processo de feedback: " . $this->erro->getMessage();
}
?>