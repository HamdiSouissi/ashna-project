<?php
declare(strict_types=1);
namespace App\Services;

class OverpassService
{
    /** @var array<int,string> */
    private array $servers;
    private int $timeout;
    private CacheService $cache;
    private int $cacheTtl;

    public function __construct()
    {
        $servers = (string)setting('overpass_servers', '');
        $list = array_values(array_filter(array_map('trim', explode("\n", $servers))));
        $this->servers = $list !== [] ? $list : LF_CONFIG['overpass']['servers'];
        $this->timeout = (int)setting('overpass_timeout', LF_CONFIG['overpass']['timeout']);
        $this->cache = new CacheService('overpass');
        $this->cacheTtl = 3600 * (int)setting('cache_ttl_hours', LF_CONFIG['cache_ttl_hours'] ?? 24);
    }

    /**
     * Interroge Overpass pour une sous-zone.
     * @param array<int,array{k:string,v:string}> $selectors
     * @param array{s:float,n:float,w:float,e:float} $bbox
     * @return array<int,array<string,mixed>>
     */
    public function query(array $selectors, array $bbox): array
    {
        $ql = $this->buildQl($selectors, $bbox);
        $key = 'ovp_' . md5($ql);
        if ($this->cacheTtl > 0) {
            $hit = $this->cache->get($key);
            if ($hit !== null) return $hit;
        }
        $elements = $this->request($ql);
        if ($this->cacheTtl > 0) $this->cache->set($key, $elements, $this->cacheTtl);
        return $elements;
    }

    private function buildQl(array $selectors, array $b): string
    {
        $bb = sprintf('%.6f,%.6f,%.6f,%.6f', $b['s'], $b['w'], $b['n'], $b['e']);
        $parts = '';
        foreach ($selectors as $sel) {
            $k = str_replace(['"', '\\'], '', $sel['k']);
            $v = str_replace(['"', '\\'], '', $sel['v']);
            $parts .= 'nwr["' . $k . '"="' . $v . '"](' . $bb . ');';
        }
        return '[out:json][timeout:' . $this->timeout . '];(' . $parts . ');out center tags;';
    }

    /** Bascule automatique entre serveurs miroirs en cas d'echec. */
    private function request(string $ql): array
    {
        $ua = (string)setting('nominatim_user_agent', LF_CONFIG['nominatim']['user_agent']);
        $delay = (float)setting('api_delay', LF_CONFIG['overpass']['delay']);
        $lastError = 'aucun serveur disponible';

        foreach ($this->servers as $server) {
            Throttle::wait('overpass', $delay);
            $res = HttpClient::post($server, [
                'body'       => 'data=' . rawurlencode($ql),
                'user_agent' => $ua,
                'timeout'    => $this->timeout + 10,
            ]);
            if ($res['status'] === 200) {
                $json = json_decode($res['body'], true);
                if (is_array($json) && array_key_exists('elements', $json)) return $json['elements'];
                $lastError = 'reponse Overpass invalide';
            } else {
                $lastError = 'HTTP ' . $res['status'] . ' ' . $res['error'];
            }
            log_error('Overpass echec (' . $server . ') : ' . $lastError);
        }
        throw new \RuntimeException('Tous les serveurs Overpass sont indisponibles (' . $lastError . ')');
    }
}
