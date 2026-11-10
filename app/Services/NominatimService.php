<?php
declare(strict_types=1);
namespace App\Services;

class NominatimService
{
    private CacheService $cache;
    private int $ttl;

    public function __construct()
    {
        $this->cache = new CacheService('nominatim');
        $this->ttl = 3600 * (int)setting('cache_ttl_hours', LF_CONFIG['cache_ttl_hours'] ?? 24);
    }

    private function userAgent(): string
    {
        return (string)setting('nominatim_user_agent', LF_CONFIG['nominatim']['user_agent']);
    }

    /** Geocodage d'une ville/zone : [lat, lon, label, bbox{ s,n,w,e }] ou null. */
    public function geocode(string $query): ?array
    {
        $key = 'geo_' . md5(mb_strtolower($query));
        $hit = $this->cache->get($key);
        if ($hit !== null) return $hit;

        Throttle::wait('nominatim', (float)LF_CONFIG['nominatim']['delay']);
        $url = LF_CONFIG['nominatim']['url'] . '?' . http_build_query([
            'q'               => $query,
            'format'          => 'json',
            'limit'           => 1,
            'addressdetails'  => 0,
            'accept-language' => \App\Core\Lang::current(),
        ]);
        $res = HttpClient::get($url, ['user_agent' => $this->userAgent(), 'timeout' => 20]);
        if ($res['status'] !== 200) {
            throw new \RuntimeException('Nominatim indisponible (HTTP ' . $res['status'] . ' ' . $res['error'] . ')');
        }
        $data = json_decode($res['body'], true);
        if (empty($data[0]['lat'])) return null;

        $bb = array_map('floatval', $data[0]['boundingbox'] ?? [0, 0, 0, 0]);
        $out = [
            'lat'   => (float)$data[0]['lat'],
            'lon'   => (float)$data[0]['lon'],
            'label' => (string)($data[0]['display_name'] ?? $query),
            'bbox'  => ['s' => $bb[0], 'n' => $bb[1], 'w' => $bb[2], 'e' => $bb[3]],
        ];
        $this->cache->set($key, $out, $this->ttl);
        return $out;
    }

    /** Suggestions de villes pour le champ de recherche. */
    public function suggest(string $query): array
    {
        Throttle::wait('nominatim', (float)LF_CONFIG['nominatim']['delay']);
        $url = LF_CONFIG['nominatim']['url'] . '?' . http_build_query([
            'q'              => $query,
            'format'         => 'json',
            'limit'          => 6,
            'addressdetails' => 0,
        ]);
        $res = HttpClient::get($url, ['user_agent' => $this->userAgent(), 'timeout' => 15]);
        if ($res['status'] !== 200) return [];
        $data = json_decode($res['body'], true) ?: [];
        return array_map(static fn(array $r): string => (string)($r['display_name'] ?? ''), $data);
    }
}
