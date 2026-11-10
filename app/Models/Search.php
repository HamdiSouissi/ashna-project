<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

class Search
{
    public static function create(?int $userId, string $ville, string $zoneLabel, string $categorie, string $bbox): int
    {
        Database::q(
            'INSERT INTO searches (user_id, ville, zone_label, categorie, bbox, statut) VALUES (?, ?, ?, ?, ?, "en_cours")',
            [$userId, mb_substr($ville, 0, 190), mb_substr($zoneLabel, 0, 190), $categorie, $bbox]
        );
        return (int)Database::pdo()->lastInsertId();
    }

    public static function finish(int $id, int $nbTrouves, int $nbSansSite, string $statut = 'terminee'): void
    {
        Database::q('UPDATE searches SET nb_trouves = ?, nb_sans_site = ?, statut = ? WHERE id = ?', [$nbTrouves, $nbSansSite, $statut, $id]);
    }

    public static function all(?int $userId = null, int $limit = 100): array
    {
        $sql = 'SELECT * FROM searches';
        $params = [];
        if ($userId !== null) {
            $sql .= ' WHERE user_id = ? OR user_id IS NULL';
            $params[] = $userId;
        }
        $sql .= ' ORDER BY created_at DESC LIMIT ' . (int)$limit;
        return Database::q($sql, $params)->fetchAll();
    }

    public static function recent(int $limit = 6): array
    {
        return Database::q('SELECT * FROM searches ORDER BY created_at DESC LIMIT ' . (int)$limit)->fetchAll();
    }

    public static function countAll(): int
    {
        return (int)Database::q('SELECT COUNT(*) FROM searches')->fetchColumn();
    }
}
