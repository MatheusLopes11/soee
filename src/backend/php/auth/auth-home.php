<?php

class AuthHome {

    /* ══════════════════════════════════════════
       GETTERS DE SESSÃO
    ══════════════════════════════════════════ */
    public static function getId(): ?int {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function getNome(): ?string {
        return $_SESSION['user_nome'] ?? null;
    }

    public static function getTipo(): ?string {
        return $_SESSION['user_tipo'] ?? null;
    }

    /* ══════════════════════════════════════════
       VERIFICA SE ESTÁ LOGADO
    ══════════════════════════════════════════ */
    public static function estaLogado(): bool {
        return !empty($_SESSION['user_id']);
    }

    /* ══════════════════════════════════════════
       EXIGE LOGIN (qualquer tipo)
       Usado em páginas como user-conta.php
    ══════════════════════════════════════════ */
    public static function exigirLogin(): void {
        if (!self::estaLogado()) {
            header('Location: /soee/index.php');
            exit();
        }
    }

    /* ══════════════════════════════════════════
       EXIGE TIPO(S) PERMITIDO(S)
       Redireciona para login se não estiver logado.
       Redireciona para o dashboard correto se o
       tipo não estiver na lista permitida.

       Uso:
         AuthHome::exigirTipo(['aluno']);
         AuthHome::exigirTipo(['adm_geral', 'adm_sala']);
    ══════════════════════════════════════════ */
    public static function exigirTipo(array $tiposPermitidos): void {
        if (!self::estaLogado()) {
            header('Location: /soee/index.php');
            exit();
        }

        if (!in_array(self::getTipo(), $tiposPermitidos, true)) {
            self::redirecionarPorTipo();
        }
    }

    /* ══════════════════════════════════════════
       RETORNA A ROTA DO DASHBOARD PELO TIPO
       Usado em user-conta.php para o botão "Voltar"
    ══════════════════════════════════════════ */
    public static function getRota(?string $tipo = null): string {
        $rotas = [
            'adm_geral' => '/soee/src/backend/php/dashboard/dash-adm.php',
            'adm_sala'  => '/soee/src/backend/php/dashboard/dash-adm-sala.php',
            'professor' => '/soee/src/backend/php/dashboard/dash-prof.php',
            'aluno'     => '/soee/src/backend/php/dashboard/dash-user.php',
        ];

        $t = $tipo ?? self::getTipo();
        return $rotas[$t] ?? '/soee/src/backend/php/pages/inicio.php';
    }

    /* ══════════════════════════════════════════
       REDIRECIONA PARA O DASHBOARD DO TIPO ATUAL
    ══════════════════════════════════════════ */
    public static function redirecionarPorTipo(): void {
        header('Location: ' . self::getRota());
        exit();
    }

    /* ══════════════════════════════════════════
       LOGIN AUTOMÁTICO POR COOKIE (remember me)
    ══════════════════════════════════════════ */
    public static function tentarLoginPorCookie($conn): void {
        if (self::estaLogado()) return;
        if (empty($_COOKIE['remember_token'])) return;

        $token = $_COOKIE['remember_token'];

        $stmt = $conn->prepare("SELECT * FROM usuario WHERE remember_token = :token LIMIT 1");
        $stmt->bindValue(':token', $token);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['ativo_usuario'] == 1) {
            $_SESSION['user_id']   = $user['id_usuario'];
            $_SESSION['user_nome'] = $user['nome_usuario'];
            $_SESSION['user_tipo'] = $user['tipo_usuario'];
            return;
        }

        self::apagarCookieLembrar();
    }

    /* ══════════════════════════════════════════
       PROCESSAR LOGIN (POST)

       Compatibilidade dupla de senha:
         - Contas do seed têm senha em texto puro
         - Contas novas têm bcrypt (password_hash)
       Detectamos pelo prefixo $2y$ do bcrypt.
    ══════════════════════════════════════════ */
    public static function processarLogin($conn, string $login, string $senha, bool $lembrar): array {

        if (empty($login) || empty($senha)) {
            return ['sucesso' => false, 'erro' => 'Preencha todos os campos.'];
        }

        $stmt = $conn->prepare(
            "SELECT * FROM usuario
             WHERE (nome_usuario = :login OR email_usuario = :login)
             LIMIT 1"
        );
        $stmt->bindValue(':login', $login);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['sucesso' => false, 'erro' => 'Usuário não cadastrado ou senha incorreta.'];
        }

        // Compatibilidade: bcrypt (novas contas) vs texto puro (contas do seed)
        $senhaCorreta = str_starts_with($user['senha_usuario'], '$2y$')
            ? password_verify($senha, $user['senha_usuario'])
            : ($senha === $user['senha_usuario']);

        if (!$senhaCorreta) {
            return ['sucesso' => false, 'erro' => 'Usuário não cadastrado ou senha incorreta.'];
        }

        if ($user['ativo_usuario'] == 0) {
            return ['sucesso' => false, 'erro' => 'Sua conta está desativada.'];
        }

        $_SESSION['user_id']   = $user['id_usuario'];
        $_SESSION['user_nome'] = $user['nome_usuario'];
        $_SESSION['user_tipo'] = $user['tipo_usuario'];

        if ($lembrar) {
            $token = bin2hex(random_bytes(32));
            $upd   = $conn->prepare("UPDATE usuario SET remember_token = :token WHERE id_usuario = :id");
            $upd->execute([':token' => $token, ':id' => $user['id_usuario']]);
            setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true);
        }

        return ['sucesso' => true, 'redirect' => self::getRota($user['tipo_usuario'])];
    }

    /* ══════════════════════════════════════════
       LOGOUT
    ══════════════════════════════════════════ */
    public static function logout($conn): void {
        if (!empty($_SESSION['user_id'])) {
            $stmt = $conn->prepare("UPDATE usuario SET remember_token = NULL WHERE id_usuario = :id");
            $stmt->execute([':id' => $_SESSION['user_id']]);
        }

        self::apagarCookieLembrar();

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();

        header('Location: /soee/index.php');
        exit();
    }

    /* ══════════════════════════════════════════
       HELPER — apaga cookie remember_token
    ══════════════════════════════════════════ */
    private static function apagarCookieLembrar(): void {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        unset($_COOKIE['remember_token']);
    }
}