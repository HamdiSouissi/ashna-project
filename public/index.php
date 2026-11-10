<?php
declare(strict_types=1);
define('LF_START', microtime(true));

$root = dirname(__DIR__);
$configFile = $root . '/config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Configuration manquante : copiez /config/config.example.php en /config/config.php puis renseignez vos accès MySQL.');
}
define('LF_CONFIG', require $configFile);

require $root . '/app/bootstrap.php';

$router = new App\Core\Router();
(require $root . '/app/routes.php')($router);
$router->dispatch();
