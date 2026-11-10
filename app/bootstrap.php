<?php
declare(strict_types=1);

// Autoloader PSR-4 minimal (App\ => app/)
spl_autoload_register(static function (string $class): void {
    if (strncmp($class, 'App\\', 4) !== 0) return;
    $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($file)) require $file;
});

require dirname(__DIR__) . '/app/Core/Helpers.php';

// Sessions securisees
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_name('LEADFINDER');
    session_start();
}

if (!defined('LF_CONFIG')) exit('LF_CONFIG manquant');

date_default_timezone_set(LF_CONFIG['app']['timezone'] ?? 'UTC');

// Logs applicatifs
ini_set('display_errors', '0');
ini_set('log_errors', '1');
foreach ([dirname(__DIR__) . '/storage/logs', dirname(__DIR__) . '/storage/cache'] as $dir) {
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
}
ini_set('error_log', dirname(__DIR__) . '/storage/logs/php-' . date('Y-m-d') . '.log');

// Langue (session), francais par defaut
$lang = $_SESSION['lang'] ?? (LF_CONFIG['app']['default_lang'] ?? 'fr');
if (!in_array($lang, ['fr', 'ar'], true)) $lang = 'fr';
App\Core\Lang::init($lang);
