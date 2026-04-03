<?php
/**
 * auth-home.php
 * Centraliza toda a lógica de autenticação e redirecionamento do SOEE.
 * Inclua este arquivo em qualquer página que precise verificar sessão.
 *
 * Uso no index.php (login):
 *   include __DIR__ . '/auth-home.php';
 *
 * Uso em páginas protegidas:
 *   require_once __DIR__ . '/../auth/auth-home.php';
 *   AuthHome::exigirLogin();
 *   AuthHome::exigirTipo(['adm_sala', 'adm_geral']);
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AuthHome
{
    private static array $rotas = [
        'adm_geral'  => '/soee/src/backend/php/dashboard/dash-adm.php',
        'adm_sala'   => '/soee/src/backend/php/dashboard/dash-adm-sala.php',
        'professor'  => '/soee/src/backend/php/dashboard/dash-prof.php',
        'aluno'      => '/soee/src/backend/php/dashboard/dash-user.php',
    ];

    private static string $paginaLogin  = '/soee/index.php';
    private static string $paginaInicio = '/soee/src/backend/php/pages/inicio.php';

    public static function getRota(string $tipo): string
    {
        return self::$rotas[$tipo] ?? self::$paginaInicio;
    }

    public static function estaLogado(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function getTipo(): string
    {
        return (string) ($_SESSION['user_tipo'] ?? '');
    }

    public static function getId(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    public static function getNome(): string
    {
        return (string) ($_SESSION['user_nome'] ?? '');
    }

    public static function exigirLogin(): void
    {
        if (!self::estaLogado()) {
            header('Location: ' . self::$paginaLogin);
            exit();
        }
    }

    public static function exigirTipo(array $tiposPermitidos): void
    {
        self::exigirLogin();
        if (!in_array(self::getTipo(), $tiposPermitidos, true)) {
            header('Location: ' . self::getRota(self::getTipo()));
            exit();
        }
    }

    public static function redirecionarPorTipo(): void
    {
        if (self::estaLogado()) {
            header('Location: ' . self::getRota(self::getTipo()));
            exit();
        }
    }

    public static function tentarLoginPorCookie(\PDO $conn): void
    {
        if (self::estaLogado()) {
            return;
        }

        if (empty($_COOKIE['remember_token'])) {
            return;
        }

        $token = trim($_COOKIE['remember_token']);

        if (strlen($token) !== 64) {
            self::limparCookieRemember();
            return;
        }

        $stmt = $conn->prepare("
            SELECT id_usuario, nome_usuario, tipo_usuario, ativo_usuario
            FROM usuario
            WHERE remember_token = :token
            LIMIT 1
        ");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || (int) $user['ativo_usuario'] === 0) {
            self::limparCookieRemember();
            return;
        }

        $_SESSION['user_id']   = (int) $user['id_usuario'];
        $_SESSION['user_nome'] = $user['nome_usuario'];
        $_SESSION['user_tipo'] = $user['tipo_usuario'];
    }

    public static function processarLogin(\PDO $conn, string $login, string $senha, bool $lembrar): array
    {
        if (empty($login) || empty($senha)) {
            return ['sucesso' => false, 'erro' => 'Preencha todos os campos.'];
        }

        $stmt = $conn->prepare("
            SELECT id_usuario, nome_usuario, email_usuario,
                   senha_usuario, tipo_usuario, ativo_usuario
            FROM usuario
            WHERE (nome_usuario = :login OR email_usuario = :login)
            LIMIT 1
        ");
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            return ['sucesso' => false, 'erro' => 'Usuário não encontrado.'];
        }

        if ($senha !== $user['senha_usuario']) {
            return ['sucesso' => false, 'erro' => 'Senha incorreta.'];
        }

        if ((int) $user['ativo_usuario'] === 0) {
            return ['sucesso' => false, 'erro' => 'Sua conta está desativada.'];
        }

        $_SESSION['user_id']   = (int) $user['id_usuario'];
        $_SESSION['user_nome'] = $user['nome_usuario'];
        $_SESSION['user_tipo'] = $user['tipo_usuario'];

        if ($lembrar) {
            $token = bin2hex(random_bytes(32));

            $upd = $conn->prepare("
                UPDATE usuario SET remember_token = :token
                WHERE id_usuario = :id
            ");
            $upd->execute([':token' => $token, ':id' => $user['id_usuario']]);

            setcookie(
                'remember_token',
                $token,
                time() + (86400 * 30),
                '/',
                '',
                false,
                true
            );
        }

        return [
            'sucesso'  => true,
            'tipo'     => $user['tipo_usuario'],
            'redirect' => self::getRota($user['tipo_usuario']),
        ];
    }

    public static function logout(\PDO $conn): void
    {
        if (self::estaLogado()) {
            $stmt = $conn->prepare("
                UPDATE usuario SET remember_token = NULL
                WHERE id_usuario = :id
            ");
            $stmt->execute([':id' => self::getId()]);
        }

        self::limparCookieRemember();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(), '',
                time() - 42000,
                $p['path'], $p['domain'],
                $p['secure'], $p['httponly']
            );
        }

        session_destroy();
        header('Location: ' . self::$paginaLogin);
        exit();
    }

    private static function limparCookieRemember(): void
    {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        unset($_COOKIE['remember_token']);
    }
}