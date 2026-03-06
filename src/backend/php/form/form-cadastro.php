<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Cadastrar</title>

    <!-- Meta Dados -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Links -->
    <link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">
    <!-- CSS -->
    <link rel="stylesheet" href="/soee/src/frontend/css/form-cadastro.css">
</head>
    <body>
        <div class="all_form">
            <div class="bg-blur"></div>
                <div class="card">
                    <div class="wrapper">
                        <!-- Formulario Cadastro -->
                        <form action="index.php" method="POST">
                    <h1 class="cadastro">Cadastrar</h1>

                    <label class="label-form-cad">Nome:</label><br>
                        <input type="text" name="nome"><br>

                    <label class="label-form-cad">Email:</label><br>
                        <input type="email" name="email"><br>

                    <label class="label-form-cad">Senha:</label><br>
                        <input type="password" name="senha"><br>

                    <label class="label-form-cad">RM:</label><br>
                        <input type="text" name="rm"><br>

                    <label class="label-form-cad">RA:</label><br>
                        <input type="text" name="ra"><br>

                    <label class="label-form-cad">CPF:</label><br>
                        <input type="text" name="cpf"><br>

                    <label class="label-form-cad">Genero:</label><br>
                        <input type="text" name="genero"><br>

                    <label class="label-form-cad">Data de Nascimento:</label><br>
                        <input type="text" name="data_nascimento"><br>

                        <label class="label-form-cad">Tipo:</label><br>
                            <input type="text" name="tipo_usuario"><br>

                        <label class="label-form-cad">Foto:</label><br>
                            <input type="text" name="foto"><br><br>
            
                    <button type="submit" name="cadastrar" class="botao-cadastrar">Cadastrar</button><br>
                        <a href="/soee/index.php">Entrar</a><br>
                    </div>
                </div>
            </div>
        </form>
    </body>
</html>