<?php
declare(strict_types=1);

use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ExportController;
use App\Controllers\InstallController;
use App\Controllers\PipelineController;
use App\Controllers\ProspectController;
use App\Controllers\SearchController;
use App\Controllers\SettingsController;
use App\Core\Router;

return static function (Router $r): void {
    // Installation (uniquement si aucun utilisateur)
    $r->get('/install', [InstallController::class, 'form']);
    $r->post('/install', [InstallController::class, 'save']);

    // Authentification
    $r->get('/login', [AuthController::class, 'login']);
    $r->post('/login', [AuthController::class, 'doLogin']);
    $r->get('/logout', [AuthController::class, 'logout']);
    $r->get('/lang/{code}', [AuthController::class, 'lang']);

    // Application (protegee par Auth::requireLogin dans les controleurs)
    $r->get('/', [DashboardController::class, 'index']);
    $r->get('/search', [SearchController::class, 'form']);
    $r->get('/prospects', [SearchController::class, 'results']);
    $r->get('/map', [SearchController::class, 'map']);
    $r->get('/history', [SearchController::class, 'history']);
    $r->get('/prospect/{id}', [ProspectController::class, 'show']);
    $r->post('/prospect/{id}', [ProspectController::class, 'update']);
    $r->post('/prospect/{id}/interaction', [ProspectController::class, 'interaction']);
    $r->get('/pipeline', [PipelineController::class, 'index']);
    $r->get('/export', [ExportController::class, 'form']);
    $r->post('/export', [ExportController::class, 'download']);
    $r->get('/settings', [SettingsController::class, 'index']);
    $r->post('/settings', [SettingsController::class, 'save']);

    // API AJAX
    $r->post('/api/search/start', [ApiController::class, 'searchStart']);
    $r->post('/api/search/step', [ApiController::class, 'searchStep']);
    $r->get('/api/geo/suggest', [ApiController::class, 'geoSuggest']);
    $r->get('/api/prospects/map', [ApiController::class, 'prospectsMap']);
    $r->post('/api/prospect/status', [ApiController::class, 'prospectStatus']);
    $r->post('/api/prospect/bulk', [ApiController::class, 'prospectBulk']);
    $r->post('/api/prospect/verify', [ApiController::class, 'verify']);
    $r->post('/api/prospect/message', [ApiController::class, 'aiMessage']);
    $r->post('/api/prospect/ai-normalize', [ApiController::class, 'aiNormalize']);
};
