<!DOCTYPE html>
<html lang="pt-br">
<head>
<!-- (Meta Dados) -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- (Título Guia) -->
    <title>Cadastrar</title>
<!-- (Línks) -->
    <link rel="stylesheet" href="/soee/src/frontend/css/inicio.css">
    <link rel="stylesheet" href="/soee/src/frontend/css/cadastrar.css">
    <link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">
</head>
    <body>

    <section class="cadastro-page">

        <div class="cadastro-card">
            
            <h1>Criar Conta</h1>
                <p class="cadastro-sub">Preencha os dados para acessar o sistema</p>

                    <form action="auth-cadastrar.php" method="POST">

                        <div class="form-grupo">
                            <label>Nome</label>
                                <input type="text" name="nome" placeholder="" required>
                        </div>

                        <div class="form-grupo">
                            <label>Email</label>
                                <input type="email" name="email"  placeholder="Exemplo: email@gmail.com" required>
                        </div>

                        <div class="form-grupo">
                            <label>Senha</label>
                                <input type="password" name="senha" placeholder="" required>
                        </div>

                        <div class="form-grupo">
                        <label>Gênero</label>
                            <select name="genero" id="opcoes">
                                <option value="m">Masculino</option>
                                <option value="f">Feminino</option>
                                <option value="outro">Outro</option>
                            </select>
                        </div>

                        <div id="campoExtra" class="form-grupo campo-extra">
                            <label>Seu gênero</label>
                                <textarea name="genero_outro"></textarea>
                        </div>

                        <button class="botao-cadastrar" type="submit">
                            Cadastrar
                        </button>

                        <a class="link-login" href="/soee/index.php">
                            Já tenho conta
                        </a>

                    </form>
                    
                </div> <!-- (cadastro-card) -->

            </section> <!-- (cadastro-page) -->

        <script src="/soee/src/frontend/js/cadastrar.js"></script>

    </body>
</html>