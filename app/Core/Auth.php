<?php
declare(strict_types=1);
namespace App\Core;

use App\Models\User;

class Auth
{
    private static ?array $user = null;

    public static function check(): bool { return !empty($_SESSION['user_id']); }
    public static function id(): int   { return (int)($_SESSION['user_id'] ?? 0); }

    public static function user(): ?array
    {
        if (self::$user === null && self::check()) self::$user = User::find(self::id());
        return self::$user;
    }

    public static function isAdmin(): bool
    {
        $u = self::user();
        return $u !== null && $u['role'] === 'admin';
    }

    public static function attempt(string $email, string $password): bool
    {
        $u = User::findByEmail($email);
        if ($u && password_verify($password, $u['mot_de_passe_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$u['id'];
            self::$user = $u;
            return true;
        }
        return false;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) setcookie(session_name(), '', time() - 42000, '/');
        session_destroy();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) redirect('/login');
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) redirect('/');
    }
}
