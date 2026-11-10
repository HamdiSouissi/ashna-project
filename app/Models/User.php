<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

class User
{
    public static function findByEmail(string $email): ?array
    {
        return Database::one('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public static function create(string $nom, string $email, string $password, string $role = 'user'): int
    {
        Database::q(
            'INSERT INTO users (nom, email, mot_de_passe_hash, role) VALUES (?, ?, ?, ?)',
            [$nom, $email, password_hash($password, PASSWORD_DEFAULT), $role]
        );
        return (int)Database::pdo()->lastInsertId();
    }

    public static function count(): int
    {
        try {
            return (int)Database::q('SELECT COUNT(*) FROM users')->fetchColumn();
        } catch (\Throwable) {
            return 0;
        }
    }
}
