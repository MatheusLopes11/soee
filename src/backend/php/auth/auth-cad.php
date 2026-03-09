<?php

if (!isset($_POST['nome'], $_POST['email'], $_POST['senha'])) {
    header('Location: form-cadastro.php');
    die();
}

?>
