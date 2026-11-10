<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;
use App\Services\PhoneNormalizer;

class Prospect
{
    public const STATUTS = ['nouveau', 'a_contacter', 'contacte', 'interesse', 'rdv', 'client', 'refuse'];
    public const SITE    = ['aucun', 'reseaux_sociaux', 'verifie_aucun'];
    public const SCORES  = ['faible', 'moyen', 'eleve'];

    private static function tag(array $tags, string $key): string
    {
        $v = $tags[$key] ?? '';
        if (is_array($v)) $v = $v[0] ?? '';
        return trim((string)$v);
    }

    /**
     * Convertit un element Overpass en prospect candidat.
     * Retourne null si : sans nom, ou possedant un VRAI site web.
     */
    public static function mapElement(array $el): ?array
    {
        $tags = $el['tags'] ?? [];
        $name = self::tag($tags, 'name');
        if ($name === '') return null;

        $website = '';
        foreach (['website', 'contact:website', 'url'] as $k) {
            $v = self::tag($tags, $k);
            if ($v !== '') { $website = $v; break; }
        }
        $fb = self::tag($tags, 'contact:facebook') ?: self::tag($tags, 'facebook');
        $ig = self::tag($tags, 'contact:instagram') ?: self::tag($tags, 'instagram');

        if ($website !== '') {
            if (preg_match('~facebook\.com|fb\.me|fb\.com|instagram\.com~i', $website)) {
                $fb = $fb !== '' ? $fb : $website; // le "site" n'est qu'une page sociale
            } else {
                return null; // possede un vrai site web -> exclu
            }
        }

        $lat = isset($el['lat']) ? (float)$el['lat'] : (float)($el['center']['lat'] ?? 0);
        $lon = isset($el['lon']) ? (float)$el['lon'] : (float)($el['center']['lon'] ?? 0);

        return [
            'osm_type'         => (string)$el['type'],
            'osm_id'           => (int)$el['id'],
            'nom'              => $name,
            'adresse'          => trim(self::tag($tags, 'addr:housenumber') . ' ' . self::tag($tags, 'addr:street')),
            'ville'            => self::tag($tags, 'addr:city'),
            'code_postal'      => self::tag($tags, 'addr:postcode'),
            'telephone'        => self::tag($tags, 'phone') ?: self::tag($tags, 'contact:phone')
                                  ?: self::tag($tags, 'contact:mobile') ?: self::tag($tags, 'mobile'),
            'email'            => self::tag($tags, 'email') ?: self::tag($tags, 'contact:email'),
            'facebook'         => $fb,
            'instagram'        => $ig,
            'horaires'         => self::tag($tags, 'opening_hours'),
            'latitude'         => $lat,
            'longitude'        => $lon,
            'site_web_statut'  => ($fb !== '' || $ig !== '') ? 'reseaux_sociaux' : 'aucun',
        ];
    }

    /** INSERT ... ON DUPLICATE KEY UPDATE : ne touche NI statut_prospection NI notes NI assigned_to. */
    public static function upsert(array $c, int $searchId, string $categorie, string $villeFallback): array
    {
        if ($c['ville'] === '') $c['ville'] = $villeFallback;
        $normalized = PhoneNormalizer::normalize($c['telephone'], (string)setting('default_country', 'TN'));

        Database::q(
            'INSERT INTO prospects
             (osm_type, osm_id, nom, categorie, adresse, ville, code_postal, telephone, telephone_normalise,
              email, facebook, instagram, horaires, latitude, longitude, site_web_statut, score_confiance, search_id)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
               nom = VALUES(nom), categorie = VALUES(categorie), adresse = VALUES(adresse),
               ville = VALUES(ville), code_postal = VALUES(code_postal), telephone = VALUES(telephone),
               telephone_normalise = VALUES(telephone_normalise), email = VALUES(email),
               facebook = VALUES(facebook), instagram = VALUES(instagram), horaires = VALUES(horaires),
               latitude = VALUES(latitude), longitude = VALUES(longitude),
               site_web_statut = IF(prospects.site_web_statut = "verifie_aucun", "verifie_aucun", VALUES(site_web_statut)),
               score_confiance = VALUES(score_confiance), search_id = VALUES(search_id)',
            [
                $c['osm_type'], $c['osm_id'], $c['nom'], $categorie, $c['adresse'], $c['ville'],
                $c['code_postal'], $c['telephone'], $normalized, $c['email'], $c['facebook'],
                $c['instagram'], $c['horaires'], $c['latitude'], $c['longitude'], $c['site_web_statut'],
                'moyen', $searchId,
            ]
        );

        return self::find($c['osm_type'], $c['osm_id']);
    }

    public static function find(string $osmType, int $osmId): array
    {
        return Database::q('SELECT * FROM prospects WHERE osm_type = ? AND osm_id = ?', [$osmType, $osmId])->fetch() ?: [];
    }

    public static function findById(int $id): ?array
    {
        return Database::one('SELECT * FROM prospects WHERE id = ?', [$id]);
    }

    /** Recherche filtree / triee. */
    public static function search(array $f): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($f['q'])) {
            $where[] = '(nom LIKE :q OR adresse LIKE :q OR ville LIKE :q)';
            $params['q'] = '%' . $f['q'] . '%';
        }
        if (!empty($f['statut']) && in_array($f['statut'], self::STATUTS, true)) {
            $where[] = 'statut_prospection = :st';
            $params['st'] = $f['statut'];
        }
        if (!empty($f['categorie'])) {
            $where[] = 'categorie = :cat';
            $params['cat'] = $f['categorie'];
        }
        if (!empty($f['ville'])) {
            $where[] = 'ville = :vi';
            $params['vi'] = $f['ville'];
        }
        if (!empty($f['search_id'])) {
            $where[] = 'search_id = :sid';
            $params['sid'] = (int)$f['search_id'];
        }
        if (!empty($f['has_phone'])) $where[] = "telephone_normalise <> ''";
        if (!empty($f['has_email'])) $where[] = "email <> ''";
        if (!empty($f['site']) && in_array($f['site'], self::SITE, true)) {
            $where[] = 'site_web_statut = :sw';
            $params['sw'] = $f['site'];
        }

        $sortMap = [
            'nom' => 'nom', 'ville' => 'ville', 'categorie' => 'categorie',
            'created_at' => 'created_at', 'updated_at' => 'updated_at',
            'statut_prospection' => 'statut_prospection', 'score_confiance' => 'score_confiance',
        ];
        $sort = $sortMap[$f['sort'] ?? ''] ?? 'created_at';
        $dir = strtolower($f['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
        $limit = max(1, min(1000, (int)($f['limit'] ?? 500)));

        return Database::q(
            'SELECT * FROM prospects WHERE ' . implode(' AND ', $where) . " ORDER BY $sort $dir LIMIT $limit",
            $params
        )->fetchAll();
    }

    public static function setStatus(int $id, string $status): void
    {
        if (!in_array($status, self::STATUTS, true)) throw new \InvalidArgumentException('Statut invalide');
        Database::q('UPDATE prospects SET statut_prospection = ? WHERE id = ?', [$status, $id]);
    }

    public static function update(int $id, array $fields): void
    {
        $allowed = ['notes', 'statut_prospection', 'telephone', 'telephone_normalise', 'email', 'adresse', 'assigned_to', 'facebook', 'site_web_statut', 'score_confiance'];
        $sets = [];
        $params = [];
        foreach ($fields as $k => $v) {
            if (!in_array($k, $allowed, true)) continue;
            $sets[] = "$k = ?";
            $params[] = $v;
        }
        if ($sets === []) return;
        $params[] = $id;
        Database::q('UPDATE prospects SET ' . implode(', ', $sets) . ' WHERE id = ?', $params);
    }

    public static function bulkSetStatus(array $ids, string $status): int
    {
        if (!in_array($status, self::STATUTS, true) || $ids === []) return 0;
        $in = implode(',', array_fill(0, count($ids), '?'));
        return Database::q("UPDATE prospects SET statut_prospection = ? WHERE id IN ($in)", array_merge([$status], $ids))->rowCount();
    }

    public static function bulkDelete(array $ids): int
    {
        if ($ids === []) return 0;
        $in = implode(',', array_fill(0, count($ids), '?'));
        Database::q("DELETE FROM interactions WHERE prospect_id IN ($in)", $ids);
        return Database::q("DELETE FROM prospects WHERE id IN ($in)", $ids)->rowCount();
    }

    public static function mapData(): array
    {
        return Database::q(
            "SELECT id, nom, adresse, ville, telephone_normalise, statut_prospection, latitude, longitude
             FROM prospects WHERE latitude <> 0 AND longitude <> 0 LIMIT 2000"
        )->fetchAll();
    }

    public static function countAll(): int
    {
        return (int)Database::q('SELECT COUNT(*) FROM prospects')->fetchColumn();
    }

    public static function countByStatus(): array
    {
        $rows = Database::q('SELECT statut_prospection s, COUNT(*) c FROM prospects GROUP BY statut_prospection')->fetchAll();
        return array_combine(array_column($rows, 's'), array_map('intval', array_column($rows, 'c')));
    }

    public static function topBy(string $column, int $limit = 8): array
    {
        $allowed = ['categorie', 'ville'];
        if (!in_array($column, $allowed, true)) return [];
        return Database::q("SELECT $column k, COUNT(*) c FROM prospects WHERE $column <> '' GROUP BY $column ORDER BY c DESC LIMIT " . (int)$limit)->fetchAll();
    }
}
