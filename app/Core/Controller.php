<?php
declare(strict_types=1);
namespace App\Core;

abstract class Controller
{
    protected function render(string $tpl, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $standalone = !empty($data['standalone']);
        require dirname(__DIR__) . '/Views/layout/header.php';
        require dirname(__DIR__) . '/Views/' . $tpl . '.php';
        require dirname(__DIR__) . '/Views/layout/footer.php';
    }

    protected function json(mixed $data, int $code = 200): never
    {
        lf_json($data, $code);
    }
}
