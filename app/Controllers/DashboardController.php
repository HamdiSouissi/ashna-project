<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Interaction;
use App\Models\Prospect;
use App\Models\Search;

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $byStatus = Prospect::countByStatus();
        $total = array_sum($byStatus);
        $rdv = $byStatus['rdv'] ?? 0;
        $clients = $byStatus['client'] ?? 0;
        $conversion = $total > 0 ? round((($rdv + $clients) / $total) * 100, 1) : 0.0;

        $this->render('dashboard/index', [
            'title'     => t('dashboard'),
            'total'     => $total,
            'byStatus'  => $byStatus,
            'byCat'     => Prospect::topBy('categorie'),
            'byCity'    => Prospect::topBy('ville'),
            'conversion'=> $conversion,
            'searches'  => Search::recent(6),
            'searchCount' => Search::countAll(),
            'recent'    => Interaction::recent(8),
        ]);
    }
}
