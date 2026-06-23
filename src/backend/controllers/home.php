<?php

class AuthHome
{
    /* ═══════════════════════════════
       SESSÃO
    ═══════════════════════════════ */

    public static function getId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public static function getNome(): ?string {
        return $_SESSION['user_nome'] ?? null;
    }

    public static function getTipo(): ?string {
        return $_SESSION['user_tipo'] ?? null;
    }

    public static function estaLogado(): bool {
        return !empty($_SESSION['user_id']);
    }

    /* ═══════════════════════════════
       LOGIN CHECKS
    ═══════════════════════════════ */

    public static function exigirLogin(): void {
        if (!self::estaLogado()) {
            header('Location: /soee/index.php');
            exit();
        }
    }

    public static function exigirTipo(array $tipos): void {
        if (!self::estaLogado()) {
            header('Location: /soee/index.php');
            exit();
        }

        if (!in_array(self::getTipo(), $tipos, true)) {
            self::redirecionarPorTipo();
        }
    }

    /* ═══════════════════════════════
       ROTAS
    ═══════════════════════════════ */

    public static function getRota(?string $tipo = null): string {
        $rotas = [
            'adm_geral' => '/soee/src/frontend/views/dashboards/adm.php',
            'adm_sala'  => '/soee/src/frontend/views/dashboards/adm-sala.php',
            'professor' => '/soee/src/frontend/views/dashboards/professor.php',
            'aluno'     => '/soee/src/frontend/views/dashboards/aluno.php',
        ];

        $tipo = $tipo ?? self::getTipo();

        return $rotas[$tipo] ?? '/soee/src/frontend/views/site/home.php';
    }

    public static function redirecionarPorTipo(): void {
        header('Location: ' . self::getRota());
        exit();
    }

    /* ═══════════════════════════════
       COOKIE LOGIN
    ═══════════════════════════════ */

    public static function tentarLoginPorCookie(PDO $conn): void {

        if (self::estaLogado()) return;
        if (empty($_COOKIE['remember_token'])) return;

        $token = $_COOKIE['remember_token'];

        $stmt = $conn->prepare("
            SELECT * FROM usuario 
            WHERE remember_token = :token 
            LIMIT 1
        ");

        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // PostgreSQL retorna ativo_usuario como boolean (true/false),
        // não como inteiro. Usamos comparação loose para suportar ambos.
        if ($user && filter_var($user['ativo_usuario'], FILTER_VALIDATE_BOOLEAN)) {

            session_regenerate_id(true);

            $_SESSION['user_id']   = $user['id_usuario'];
            $_SESSION['user_nome'] = $user['nome_usuario'];
            $_SESSION['user_tipo'] = $user['tipo_usuario'];

            return;
        }

        self::apagarCookieLembrar();
    }

    /* ═══════════════════════════════
       LOGIN
    ═══════════════════════════════ */

    public static function processarLogin(PDO $conn, string $login, string $senha, bool $lembrar): array
    {
        if ($login === '' || $senha === '') {
            return ['sucesso' => false, 'erro' => 'Preencha todos os campos.'];
        }

        $stmt = $conn->prepare("
            SELECT * FROM usuario
            WHERE nome_usuario = :login OR email_usuario = :login
            LIMIT 1
        ");

        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['sucesso' => false, 'erro' => 'Credenciais inválidas.'];
        }

        // Apenas validação bcrypt, texto plano foi migrado
        $senhaOk = password_verify($senha, $user['senha_usuario']);

        if (!$senhaOk) {
            return ['sucesso' => false, 'erro' => 'Credenciais inválidas.'];
        }

        // PostgreSQL retorna BOOLEAN como true/false (string 't'/'f' via PDO pgsql
        // ou bool nativo). filter_var lida com ambos os casos.
        if (!filter_var($user['ativo_usuario'], FILTER_VALIDATE_BOOLEAN)) {
            return ['sucesso' => false, 'erro' => 'Conta desativada.'];
        }

        session_regenerate_id(true);

        $_SESSION['user_id']   = $user['id_usuario'];
        $_SESSION['user_nome'] = $user['nome_usuario'];
        $_SESSION['user_tipo'] = $user['tipo_usuario'];

        if ($lembrar) {

            $token = bin2hex(random_bytes(32));

            $upd = $conn->prepare("
                UPDATE usuario 
                SET remember_token = :token 
                WHERE id_usuario = :id
            ");

            $upd->execute([
                ':token' => $token,
                ':id'    => $user['id_usuario']
            ]);

            setcookie('remember_token', $token, [
                'expires'  => time() + 86400 * 30,
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }

        return [
            'sucesso'  => true,
            'redirect' => self::getRota($user['tipo_usuario'])
        ];
    }

    /* ═══════════════════════════════
       LOGOUT
    ═══════════════════════════════ */

    public static function logout(PDO $conn): void {

        if (!empty($_SESSION['user_id'])) {
            $stmt = $conn->prepare("
                UPDATE usuario 
                SET remember_token = NULL 
                WHERE id_usuario = :id
            ");

            $stmt->execute([':id' => $_SESSION['user_id']]);
        }

        self::apagarCookieLembrar();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();

            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'],
                $p['secure'], $p['httponly']
            );
        }

        session_destroy();

        header('Location: /soee/index.php');
        exit();
    }

    /* ═══════════════════════════════
       COOKIE HELPER
    ═══════════════════════════════ */

    private static function apagarCookieLembrar(): void {
        setcookie('remember_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        unset($_COOKIE['remember_token']);
    }
}