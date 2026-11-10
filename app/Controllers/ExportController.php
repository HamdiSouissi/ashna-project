<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Prospect;
use App\Services\ExportService;

class ExportController extends Controller
{
    public function form(): void
    {
        Auth::requireLogin();
        $this->render('export/form', [
            'title'   => t('export_csv'),
            'columns' => ExportService::COLUMNS,
            'query'   => (string)($_SERVER['QUERY_STRING'] ?? ''),
        ]);
    }

    public function download(): void
    {
        Auth::requireLogin();
        Csrf::verify();
        $query = (string)($_POST['filters'] ?? '');
        $params = [];
        if ($query !== '') parse_str($query, $params);

        $rows = Prospect::search([
            'q'         => (string)($params['q'] ?? ''),
            'statut'    => (string)($params['statut'] ?? ''),
            'categorie' => (string)($params['categorie'] ?? ''),
            'ville'     => (string)($params['ville'] ?? ''),
            'search_id' => (int)($params['search_id'] ?? 0),
            'has_phone' => !empty($params['has_phone']),
            'has_email' => !empty($params['has_email']),
            'site'      => (string)($params['site'] ?? ''),
            'sort'      => (string)($params['sort'] ?? 'created_at'),
            'dir'       => (string)($params['dir'] ?? 'desc'),
            'limit'     => 1000,
        ]);

        $selected = array_intersect(array_keys($_POST['columns'] ?? []), array_keys(ExportService::COLUMNS));
        ExportService::csv($rows, $selected, 'leadfinder-export-' . date('Ymd-His') . '.csv');
    }
}
