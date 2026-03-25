<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Fale Conosco | SOEE</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="/soee/src/frontend/css/feedback.css">
    <link rel="icon" type="image/png" href="/soee/src/images/logo-soee.png">

</head>
    <body>

        <section class="all-info">
            <div class="title-subtitle">
                <h1>Fale Conosco</h1>
                    <h4>Coloque sua informação abaixo para entrar em contato com a gente</h4>
                    <p>Email: e166ti@cps.sp.gov.br</p>
                    <p>Telefone: (11) 4053-9400</p>
            </div>
        </section>

        <form action="auth-feedback.php">
            <section class="all-form">
                <div class="all-form">
                    
                    <div class="campo">

                        <div class="label">
                            <label class="label">Nome</label>
                        </div>

                        <div class="input">
                            <input class="input" type="text" name="nome" placeholder="Exemplo: João da Silva" required>
                        </div><br>

                    </div>
                    
                    <div class="campo">

                        <div class= "label">
                            <label class="label">Email</label>
                        </div>

                        <div class="input">
                            <input class="input" type="text" name="email" placeholder="Exemplo: João@gmail.com" required><br>
                        </div><br>

                    </div>

                    <div class="campo">

                        <div class="label">
                            <label class="label">Assunto (Opcional)</label>
                        </div>

                        <div class="input">
                            <select name="escolha" placeholder="-Escolha-" value="-Escolha-">
                                <option>-Escolha-</option>
                                <option value="value1" name="opcao1">Site fora do Ar</option>
                                <option value="value2" name="opcao2">Erros</option>
                                <option value="value3" name="opcao3">Lentidão</option>
                                <option value="value4" name="opcao4">Página não encontrada</option>
                            </select>
                        </div><br>

                    </div>

                    <div class="campo">

                        <div class="label">
                            <label class="label">Mensagem</label>
                        </div>

                        <div class="textarea">
                            <textarea name="mensagem"></textarea required>
                        </div><br>

                    </div>
                        
                    <div class="submit">
                        <input type="submit" name="botao" value="Enviar">
                    </div>

                </div>
            </section>
        </form>
    
        <script src="/soee/src/frontend/css/feedback.css"></script>

    </body>
</html>