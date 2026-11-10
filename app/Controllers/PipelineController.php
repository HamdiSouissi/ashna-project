<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Prospect;

class PipelineController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $columns = [];
        foreach (Prospect::STATUTS as $s) {
            $columns[$s] = Prospect::search(['statut' => $s, 'sort' => 'updated_at', 'dir' => 'desc', 'limit' => 40]);
        }
        $this->render('pipeline/index', [
            'title' => t('pipeline'),
            'columns' => $columns,
            'statuts' => Prospect::STATUTS,
            'scripts' => ['assets/js/kanban.js'],
        ]);
    }
}
