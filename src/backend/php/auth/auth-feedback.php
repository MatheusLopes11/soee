<?php
try{
include __DIR__ . '/../include/conexao.php';

// Verifica se os dados foram entregues.
if (!isset(
    $_POST['nome_feedback'],
    $_POST['turma_feedback'],
    $_POST['email_feedback'],
    $_POST['categorias[]'],
    $_POST['tipo_feedback'],
    $_POST['mensagem_feedback'])    
    ||
    empty($_POST['nota_feedback'])  ||
    empty($_POST['nome_feedback'])  ||
    empty($_POST['turma_feedback']) ||
    empty($_POST['email_feedback']) ||
    empty($_POST['categorias[]'])   ||
    empty($_POST['tipo_feedback'])  ||
    empty($_POST['mensagem_feedback'])) 
{
    header('Location: /soee/src/backend/php/form/form-feedback');
    die("Preencha os campos obrigatórios.");
}

// Atribuição do dados entregues do formulário.    
$nome        =  $_POST['nome_feedback'];     
$turma       =  $_POST['turma_feedback'];   
$email       =  $_POST['email_feedback'];    
$tipo        =  $_POST['tipo_feedback'];     
$categorias  =  $_POST['categorias[]'];      
$mensagem    =  $_POST['mensagem_feedback']; 

$comand = "insert into feedback (
                    nome_feedback, 
                    email_feedback, 
                    nota_feedback, 
                    turma_feedback, 
                    tipo_feedback,
                    categorias_feedback,
                    mensagem_feedback
                ) values (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ? 
                );";

    $stmt = $conn->prepare($comand);
        $stmt->binParam(1,$nota);
        $stmt->binParam(2,$nome);
        $stmt->binParam(3,$turma);
        $stmt->binParam(4,$email);
        $stmt->binParam(5,$categorias);
        $stmt->binParam(6,$tipo);
        $stmt->binParam(7,$mensagem);
    $stmt->execute();

} catch(PDOException $erro) {
    echo 'Erro ao processar o feedback' . $erro->getMessage();
}