<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

class Interaction
{
    public const TYPES = ['appel', 'whatsapp', 'email', 'visite'];

    public static function add(int $prospectId, ?int $userId, string $type, string $note): void
    {
        if (!in_array($type, self::TYPES, true)) throw new \InvalidArgumentException('Type invalide');
        Database::q(
            'INSERT INTO interactions (prospect_id, user_id, type, note) VALUES (?, ?, ?, ?)',
            [$prospectId, $userId, $type, $note !== '' ? $note : '-']
        );
    }

    public static function forProspect(int $prospectId): array
    {
        return Database::q(
            'SELECT i.*, u.nom AS user_nom FROM interactions i
             LEFT JOIN users u ON u.id = i.user_id
             WHERE i.prospect_id = ? ORDER BY i.date_interaction DESC LIMIT 100',
            [$prospectId]
        )->fetchAll();
    }

    public static function recent(int $limit = 8): array
    {
        return Database::q(
            'SELECT i.*, p.nom AS prospect_nom, p.id AS prospect_id, u.nom AS user_nom
             FROM interactions i
             JOIN prospects p ON p.id = i.prospect_id
             LEFT JOIN users u ON u.id = i.user_id
             ORDER BY i.date_interaction DESC LIMIT ' . (int)$limit
        )->fetchAll();
    }
}
