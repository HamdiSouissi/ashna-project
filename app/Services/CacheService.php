<?php
declare(strict_types=1);
namespace App\Services;

class CacheService
{
    private string $dir;

    public function __construct(string $subDir = '')
    {
        $this->dir = dirname(__DIR__, 2) . '/storage/cache' . ($subDir !== '' ? '/' . $subDir : '');
        if (!is_dir($this->dir)) @mkdir($this->dir, 0775, true);
    }

    public function get(string $key): mixed
    {
        $file = $this->dir . '/' . md5($key) . '.json';
        if (!is_file($file)) return null;
        $raw = json_decode((string)@file_get_contents($file), true);
        if (!is_array($raw) || !array_key_exists('data', $raw)) return null;
        if ((int)$raw['ttl'] > 0 && time() > (int)$raw['expires']) return null;
        return $raw['data'];
    }

    public function set(string $key, mixed $data, int $ttl): void
    {
        @file_put_contents(
            $this->dir . '/' . md5($key) . '.json',
            json_encode(['ttl' => $ttl, 'expires' => time() + $ttl, 'data' => $data], JSON_UNESCAPED_UNICODE)
        );
    }

    public function delete(string $key): void
    {
        @unlink($this->dir . '/' . md5($key) . '.json');
    }
}
