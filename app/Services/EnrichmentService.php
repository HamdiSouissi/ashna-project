<?php
declare(strict_types=1);
namespace App\Services;

class EnrichmentService
{
    /**
     * Verification web gratuite (DuckDuckGo HTML) d'un prospect.
     * @return array{results:int,found_site:?string,found_social:?string,found_phone:string,score:string,status:string}
     */
    public function verify(array $prospect): array
    {
        $out = [
            'results' => 0, 'found_site' => null, 'found_social' => null,
            'found_phone' => '', 'score' => 'moyen', 'status' => 'verifie_aucun',
        ];

        $query = trim($prospect['nom'] . ' ' . $prospect['ville']);
        if ($query === '') return $out;

        Throttle::wait('ddg', (float)setting('enrichment_delay', LF_CONFIG['enrichment']['delay']));
        $res = HttpClient::post('https://html.duckduckgo.com/html/', [
            'body'       => http_build_query(['q' => $query . ' site web']),
            'user_agent' => (string)setting('nominatim_user_agent', LF_CONFIG['nominatim']['user_agent']),
            'timeout'    => (int)LF_CONFIG['enrichment']['timeout'],
        ]);
        if ($res['status'] !== 200) {
            throw new \RuntimeException('Recherche web indisponible (HTTP ' . $res['status'] . ')');
        }

        // Extraction des resultats (structure HTML DDG "result__a")
        preg_match_all('#<a[^>]+class="result__a"[^>]*href="([^"]+)"[^>]*>(.*?)</a>#si', $res['body'], $matches, PREG_SET_ORDER);
        $out['results'] = count($matches);

        $ignore = '~google\.|youtube\.|wikipedia\.|linkedin\.|twitter\.|x\.com|tripadvisor\.|yelp\.|pagesjaunes\.|openstreetmap\.|osm\.|gouv\.|facebook\.|instagram\.|fb\.~i';
        $social = '~facebook\.com|fb\.me|fb\.com|instagram\.com~i';

        foreach ($matches as $m) {
            $href = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            if (preg_match('~uddg=([^&]+)~', $href, $mm)) {
                $href = rawurldecode($mm[1]);
            }
            $host = strtolower((string)(parse_url($href, PHP_URL_HOST) ?? ''));
            if ($host === '') continue;
            if (preg_match($social, $host)) {
                $out['found_social'] = $out['found_social'] ?? 'https://' . $host;
                continue;
            }
            if (preg_match($ignore, $host)) continue;
            $out['found_site'] = 'https://' . $host;
            break;
        }

        // Heuristique telephone dans les extraits
        $text = trim(strip_tags(preg_replace('#<[^>]+>#', ' ', $res['body']) ?? ''));
        if (preg_match('~(?:\+?\d[\d .\-\(\)/]{7,15}\d)~u', $text, $pm)) {
            $out['found_phone'] = trim($pm[1]);
        }

        if ($out['found_site'] !== null) {
            $out['score'] = 'faible'; // un site semble exister
            $out['status'] = (string)$prospect['site_web_statut'];
        } elseif ($out['results'] > 0) {
            $out['score'] = 'eleve';
            $out['status'] = 'verifie_aucun';
        }
        return $out;
    }
}
