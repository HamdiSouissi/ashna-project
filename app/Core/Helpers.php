<?php
declare(strict_types=1);

use App\Core\Lang;
use App\Models\Setting;

function e(mixed $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function base_path(): string
{
    static $b = null;
    if ($b !== null) return $b;
    if (!empty(LF_CONFIG['base_url'])) {
        $p = parse_url(LF_CONFIG['base_url'], PHP_URL_PATH);
        return $b = rtrim((string)$p, '/');
    }
    $d = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    if (str_ends_with($d, '/public')) $d = substr($d, 0, -7);
    return $b = rtrim($d, '/');
}

function current_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $base = base_path();
    if ($base !== '' && str_starts_with($uri, $base)) $uri = substr($uri, strlen($base));
    return '/' . trim($uri, '/');
}

function url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}

function t(string $key, array $replace = []): string
{
    return Lang::t($key, $replace);
}

function flash(string $type, string $msg): void
{
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function take_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function setting(string $key, mixed $default = null): mixed
{
    return Setting::get($key, $default);
}

function lf_log(string $level, string $message): void
{
    @file_put_contents(
        dirname(__DIR__) . '/storage/logs/app-' . date('Y-m-d') . '.log',
        '[' . date('Y-m-d H:i:s') . "] $level $message\n",
        FILE_APPEND
    );
}

function log_info(string $m): void  { lf_log('INFO ', $m); }
function log_error(string $m): void { lf_log('ERROR', $m); }

function lf_json(mixed $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Categories = config + categories personnalisees des Parametres. */
function lf_categories(): array
{
    static $cats = null;
    if ($cats !== null) return $cats;
    $cats = require dirname(__DIR__) . '/config/categories.php';
    $custom = (string)setting('custom_categories', '');
    foreach (array_filter(array_map('trim', explode("\n", $custom))) as $line) {
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) < 2) continue;
        [$label, $tagsStr] = $parts;
        if ($label === '' || $tagsStr === '') continue;
        $tags = [];
        foreach (array_filter(array_map('trim', explode(';', $tagsStr))) as $kv) {
            $kv = array_map('trim', explode('=', $kv, 2));
            if (count($kv) === 2) $tags[] = ['k' => $kv[0], 'v' => $kv[1]];
        }
        if ($tags) {
            $key = 'custom_' . preg_replace('~[^a-z0-9]+~', '_', mb_strtolower($label));
            $cats[$key] = ['label' => $label, 'icon' => '📌', 'tags' => $tags];
        }
    }
    return $cats;
}

/** Selecteurs Overpass normalises [['k'=>, 'v'=>], ...] pour une categorie. */
function lf_selectors(array $category): array
{
    $out = [];
    foreach ($category['tags'] as $tag) {
        if (isset($tag['k'], $tag['v'])) {
            $out[] = ['k' => $tag['k'], 'v' => $tag['v']];
        } else {
            foreach ($tag as $k => $v) $out[] = ['k' => $k, 'v' => $v];
        }
    }
    return $out;
}

function lf_badge(string $class, string $label): string
{
    return '<span class="badge ' . e($class) . '">' . e($label) . '</span>';
}

function badge_statut(string $s): string { return lf_badge('st-' . $s, t('st_' . $s)); }
function badge_score(string $s): string  { return lf_badge('sc-' . $s, t('sc_' . $s)); }
function badge_site(string $s): string   { return lf_badge('sw-' . $s, t('sw_' . $s)); }

/** Decoupage d'une bounding box en sous-zones (grille) avec plafond. */
function lf_grid(array $bb, int $maxCells): array
{
    $cell = (float)(LF_CONFIG['grid']['cell_degrees'] ?? 0.12);
    $latSpan = $bb['n'] - $bb['s'];
    $lonSpan = $bb['e'] - $bb['w'];
    $rows = max(1, (int)ceil($latSpan / $cell));
    $cols = max(1, (int)ceil($lonSpan / $cell));
    while ($rows * $cols > $maxCells && ($rows > 1 || $cols > 1)) {
        if ($rows > 1) $rows = (int)ceil($rows / 2);
        if ($cols > 1) $cols = (int)ceil($cols / 2);
    }
    $cells = [];
    for ($r = 0; $r < $rows; $r++) {
        for ($c = 0; $c < $cols; $c++) {
            $cells[] = [
                's' => $bb['s'] + $latSpan * $r / $rows,
                'n' => $bb['s'] + $latSpan * ($r + 1) / $rows,
                'w' => $bb['w'] + $lonSpan * $c / $cols,
                'e' => $bb['w'] + $lonSpan * ($c + 1) / $cols,
            ];
        }
    }
    return $cells;
}
