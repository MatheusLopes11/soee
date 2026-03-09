<!DOCTYPE html>
<html lang="pt-br">
<head>
    <!-- (Meta Dados) -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- (TÍTULO GUIA) -->
    <title>Cadastrar</title>
    <!-- (LINKS) -->
    <link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">
    <link rel="stylesheet" href="/soee/src/frontend/css/cadastrar.css">
</head>
    <body>
        <div class="all_form">
                        <!-- (Formulario Cadastro) -->
                <form action="auth-cadastrar.php" method="POST">
                    <h1 class="cadastro">Cadastrar</h1>

                    <label class="label-form-cad">Nome:</label><br>
                        <input type="text" name="nome" required><br>

                    <label class="label-form-cad">Email:</label><br>
                        <input type="email" name="email" required><br>

                    <label class="label-form-cad">Senha:</label><br>
                        <input type="password" name="senha" required><br>

                    <label class="label-form-cad">RM:</label><br>
                        <input type="text" name="rm" required><br>

                    <label class="label-form-cad">RA:</label><br>
                        <input type="text" name="ra" required><br>

                    <label class="label-form-cad">CPF:</label><br>
                        <input type="text" name="cpf" required><br>

                    <label class="label-form-cad">Genero:</label><br>
                        <input type="text" name="genero" required><br>

                    <label class="label-form-cad">Data de Nascimento:</label><br>
                        <input type="text" name="data_nascimento" required><br>

                    <label class="label-form-cad">Tipo:</label><br>
                        <input type="text" name="tipo_usuario" required><br>

                    <label class="label-form-cad">Foto:</label><br>
                        <input type="text" name="foto" required><br><br>
            
                    <input type="submit" name="botao-cadastrar">Cadastrar<br>
                        <a href="/soee/index.php">Voltar - Entrar</a><br>
            </div>
        </form>
    </body>
</html>