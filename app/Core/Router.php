<?php
declare(strict_types=1);
namespace App\Core;

class Router
{
    /** @var array<string, array<string, array|callable>> */
    private array $routes = [];

    public function get(string $path, array|callable $handler): void  { $this->map('GET', $path, $handler); }
    public function post(string $path, array|callable $handler): void { $this->map('POST', $path, $handler); }

    private function map(string $method, string $path, array|callable $handler): void
    {
        $regex = preg_replace('#\{([a-z_]+)\}#', '(?P<$1>[^/]+)', rtrim($path, '/')) ?? $path;
        $this->routes[$method]['#^' . ($regex === '/' ? '/' : $regex) . '/?$#'] = $handler;
    }

    public function dispatch(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $base = base_path();
        if ($base !== '' && str_starts_with($uri, $base)) $uri = substr($uri, strlen($base));
        $route = '/' . trim($uri, '/');
        if ($route === '//') $route = '/';

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        foreach ($this->routes[$method] ?? [] as $pattern => $handler) {
            if (!preg_match($pattern, $route, $m)) continue;
            $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
            if (is_array($handler) && is_string($handler[0])) $handler = [new $handler[0](), $handler[1]];
            call_user_func_array($handler, array_values($params));
            return;
        }

        http_response_code(404);
        echo '<h1>404</h1><p>' . e(t('page_404')) . '</p><p><a href="' . e(url('/')) . '">' . e(t('back')) . '</a></p>';
    }
}
