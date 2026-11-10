<?php
declare(strict_types=1);
namespace App\Services;

class ExportService
{
    /** Colonnes exportables : colonne SQL => libelle. */
    public const COLUMNS = [
        'nom'                 => 'Nom',
        'categorie'           => 'Categorie',
        'adresse'             => 'Adresse',
        'ville'               => 'Ville',
        'code_postal'         => 'Code postal',
        'telephone'           => 'Telephone',
        'telephone_normalise' => 'Telephone normalise',
        'email'               => 'E-mail',
        'facebook'            => 'Facebook',
        'instagram'           => 'Instagram',
        'horaires'            => 'Horaires',
        'latitude'            => 'Latitude',
        'longitude'           => 'Longitude',
        'statut_prospection'  => 'Statut prospection',
        'site_web_statut'     => 'Site web',
        'score_confiance'     => 'Score confiance',
        'notes'               => 'Notes',
        'osm_type'            => 'Type OSM',
        'osm_id'              => 'ID OSM',
    ];

    /** CSV UTF-8 avec BOM (ouvrable directement dans Excel), separateur ";". */
    public static function csv(array $rows, array $columns, string $filename): never
    {
        $columns = array_values(array_intersect(array_keys(self::COLUMNS), $columns));
        if ($columns === []) $columns = array_keys(self::COLUMNS);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel
        fputcsv($out, array_map(static fn(string $c): string => self::COLUMNS[$c], $columns), ';');
        foreach ($rows as $row) {
            fputcsv($out, array_map(static fn(string $c): string => (string)($row[$c] ?? ''), $columns), ';');
        }
        fclose($out);
        exit;
    }
}
