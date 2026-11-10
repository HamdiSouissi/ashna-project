<?php
declare(strict_types=1);
namespace App\Core;

class Lang
{
    private static array $dict = [];
    private static string $lang = 'fr';

    public static function init(string $lang): void
    {
        self::$lang = in_array($lang, ['fr', 'ar'], true) ? $lang : 'fr';
        $file = dirname(__DIR__, 2) . '/config/i18n/' . self::$lang . '.php';
        self::$dict = is_file($file) ? (array)require $file : [];
    }

    public static function t(string $key, array $replace = []): string
    {
        $s = self::$dict[$key] ?? $key;
        foreach ($replace as $k => $v) $s = str_replace(':' . $k, (string)$v, $s);
        return $s;
    }

    public static function current(): string { return self::$lang; }
    public static function dir(): string     { return self::$lang === 'ar' ? 'rtl' : 'ltr'; }
    public static function export(): array   { return self::$dict; }
}
