<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

class Setting
{
    private static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$cache)) return self::$cache[$key];
        try {
            $v = Database::q('SELECT valeur FROM settings WHERE cle = ?', [$key])->fetchColumn();
        } catch (\Throwable) {
            return self::$cache[$key] = $default;
        }
        return self::$cache[$key] = ($v === false || $v === null) ? $default : $v;
    }

    public static function set(string $key, string $value): void
    {
        Database::q(
            'INSERT INTO settings (cle, valeur) VALUES (?, ?) ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)',
            [$key, $value]
        );
        self::$cache[$key] = $value;
    }
}
