<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Prospect;
use App\Models\Search;

class SearchController extends Controller
{
    public function form(): void
    {
        Auth::requireLogin();
        $this->render('search/form', [
            'title'      => t('search'),
            'categories' => lf_categories(),
            'prefillVille' => trim((string)($_GET['ville'] ?? '')),
            'prefillCat'   => trim((string)($_GET['cat'] ?? '')),
            'scripts'    => ['assets/js/search.js'],
        ]);
    }

    public function results(): void
    {
        Auth::requireLogin();
        $filters = [
            'q'          => trim((string)($_GET['q'] ?? '')),
            'statut'     => (string)($_GET['statut'] ?? ''),
            'categorie'  => (string)($_GET['categorie'] ?? ''),
            'ville'      => trim((string)($_GET['ville'] ?? '')),
            'search_id'  => (int)($_GET['search_id'] ?? 0),
            'has_phone'  => !empty($_GET['has_phone']),
            'has_email'  => !empty($_GET['has_email']),
            'site'       => (string)($_GET['site'] ?? ''),
            'sort'       => (string)($_GET['sort'] ?? 'created_at'),
            'dir'        => strtolower((string)($_GET['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc',
        ];
        $rows = Prospect::search($filters);
        $this->render('search/results', [
            'title'      => t('prospects'),
            'rows'       => $rows,
            'filters'    => $filters,
            'categories' => lf_categories(),
            'statuts'    => Prospect::STATUTS,
        ]);
    }

    public function map(): void
    {
        Auth::requireLogin();
        $this->render('search/map', ['title' => t('map'), 'leaflet' => true]);
    }

    public function history(): void
    {
        Auth::requireLogin();
        $user = Auth::user();
        $rows = Search::all($user !== null && $user['role'] !== 'admin' ? (int)$user['id'] : null);
        $this->render('search/history', [
            'title'   => t('history'),
            'rows'    => $rows,
            'categories' => lf_categories(),
        ]);
    }
}
