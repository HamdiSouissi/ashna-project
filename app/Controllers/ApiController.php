<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Prospect;
use App\Models\Search;
use App\Services\AiService;
use App\Services\CacheService;
use App\Services\EnrichmentService;
use App\Services\NominatimService;
use App\Services\OverpassService;
use App\Services\PhoneNormalizer;

class ApiController extends Controller
{
    private function auth(): void
    {
        if (!Auth::check()) lf_json(['error' => 'auth'], 401);
    }

    private function jobStore(): CacheService
    {
        return new CacheService('jobs');
    }

    // ---------- Recherche : demarrage ----------
    public function searchStart(): void
    {
        $this->auth();
        Csrf::verify();
        $ville = trim((string)($_POST['ville'] ?? ''));
        $catKey = (string)($_POST['categorie'] ?? '');
        $includeSocial = !empty($_POST['include_social']);
        $categories = lf_categories();
        if ($ville === '' || $catKey === '' || !isset($categories[$catKey])) {
            $this->json(['error' => t('no_category')], 422);
        }

        try {
            $geo = (new NominatimService())->geocode($ville);
        } catch (\Throwable $e) {
            log_error($e->getMessage());
            $this->json(['error' => t('api_error') . $e->getMessage()], 502);
        }
        if ($geo === null) $this->json(['error' => t('zone_not_found')], 404);

        $cells = lf_grid($geo['bbox'], (int)LF_CONFIG['grid']['max_cells']);
        $searchId = Search::create(Auth::id(), $ville, $geo['label'], $catKey, sprintf('%.5f,%.5f,%.5f,%.5f', $geo['bbox']['s'], $geo['bbox']['w'], $geo['bbox']['n'], $geo['bbox']['e']));

        $token = bin2hex(random_bytes(16));
        $this->jobStore()->set('job_' . $token, [
            'search_id'      => $searchId,
            'cells'          => $cells,
            'index'          => 0,
            'found'          => 0,
            'kept'           => 0,
            'errors'         => 0,
            'include_social' => $includeSocial,
            'categorie'      => $catKey,
            'selectors'      => lf_selectors($categories[$catKey]),
            'ville'          => $ville,
        ], 0);

        $this->json([
            'token'     => $token,
            'total'     => count($cells),
            'search_id' => $searchId,
            'zone'      => $geo['label'],
        ]);
    }

    // ---------- Recherche : progression par sous-zone ----------
    public function searchStep(): void
    {
        $this->auth();
        Csrf::verify();
        $token = (string)($_POST['token'] ?? '');
        $job = $this->jobStore()->get('job_' . $token);
        if (!is_array($job) || $job === []) $this->json(['error' => 'job introuvable'], 404);

        $total = count($job['cells']);
        $prospects = [];
        $error = null;

        if ($job['index'] < $total) {
            $cell = $job['cells'][$job['index']];
            try {
                $elements = (new OverpassService())->query($job['selectors'], $cell);
                foreach ($elements as $el) {
                    $c = Prospect::mapElement($el);
                    if ($c === null) continue;
                    $job['found']++;
                    if (!$job['include_social'] && $c['site_web_statut'] === 'reseaux_sociaux') continue;
                    $row = Prospect::upsert($c, (int)$job['search_id'], $job['categorie'], $job['ville']);
                    if ($row !== []) { $prospects[] = $row; $job['kept']++; }
                }
            } catch (\Throwable $e) {
                $job['errors']++;
                $error = $e->getMessage();
                log_error('searchStep: ' . $error);
            }
            $job['index']++;
            $this->jobStore()->set('job_' . $token, $job, 0);

            $this->json([
                'done'      => false,
                'index'     => $job['index'],
                'total'     => $total,
                'prospects' => $prospects,
                'error'     => $error,
                'search_id' => $job['search_id'],
            ]);
        }

        // Termine
        Search::finish((int)$job['search_id'], (int)$job['found'], (int)$job['kept'], $job['errors'] >= $total ? 'echec' : 'terminee');
        $this->json(['done' => true, 'total' => $total, 'search_id' => $job['search_id'], 'kept' => $job['kept']]);
    }

    // ---------- Suggestions de villes ----------
    public function geoSuggest(): void
    {
        $this->auth();
        $q = trim((string)($_GET['q'] ?? ''));
        if (mb_strlen($q) < 3) $this->json([]);
        try {
            $this->json((new NominatimService())->suggest($q));
        } catch (\Throwable) {
            $this->json([]);
        }
    }

    // ---------- Donnees carte ----------
    public function prospectsMap(): void
    {
        $this->auth();
        $this->json(Prospect::mapData());
    }

    // ---------- Statut (Kanban) ----------
    public function prospectStatus(): void
    {
        $this->auth();
        Csrf::verify();
        try {
            Prospect::setStatus((int)($_POST['id'] ?? 0), (string)($_POST['status'] ?? ''));
            $this->json(['ok' => true]);
        } catch (\Throwable $e) {
            $this->json(['error' => t('api_error') . $e->getMessage()], 422);
        }
    }

    // ---------- Actions groupees ----------
    public function prospectBulk(): void
    {
        $this->auth();
        Csrf::verify();
        $ids = array_map('intval', (array)($_POST['ids'] ?? []));
        $action = (string)($_POST['action'] ?? '');
        if ($ids === []) $this->json(['error' => t('none_selected')], 422);

        if ($action === 'delete') {
            Prospect::bulkDelete($ids);
            $this->json(['ok' => true, 'reload' => true]);
        }
        if (in_array($action, Prospect::STATUTS, true)) {
            Prospect::bulkSetStatus($ids, $action);
            $this->json(['ok' => true, 'reload' => true]);
        }
        $this->json(['error' => t('no_category')], 422);
    }

    // ---------- Verification web (DuckDuckGo) ----------
    public function verify(): void
    {
        $this->auth();
        Csrf::verify();
        $p = Prospect::findById((int)($_POST['id'] ?? 0));
        if ($p === null) $this->json(['error' => t('prospect_not_found')], 404);

        try {
            $res = (new EnrichmentService())->verify($p);
        } catch (\Throwable $e) {
            log_error('verify: ' . $e->getMessage());
            $this->json(['error' => t('api_error') . $e->getMessage()], 502);
        }

        $fields = ['score_confiance' => $res['score'], 'site_web_statut' => $res['status']];
        if ($p['telephone'] === '' && $res['found_phone'] !== '') {
            $fields['telephone'] = $res['found_phone'];
            $fields['telephone_normalise'] = PhoneNormalizer::normalize($res['found_phone'], (string)setting('default_country', 'TN'));
        }
        if ($res['found_social'] !== null && $p['facebook'] === '') $fields['facebook'] = $res['found_social'];
        Prospect::update((int)$p['id'], $fields);

        $message = $res['found_site'] !== null
            ? t('verify_site_found') . ' (' . $res['found_site'] . ')'
            : ($res['results'] > 0 ? t('verify_confirmed') : t('verify_no_results'));

        $this->json([
            'ok' => true, 'score' => $res['score'], 'site' => $res['status'],
            'results' => $res['results'], 'message' => $message,
        ]);
    }

    // ---------- IA : message d'approche ----------
    public function aiMessage(): void
    {
        $this->auth();
        Csrf::verify();
        $ai = new AiService();
        if (!$ai->enabled()) $this->json(['error' => t('ai_disabled')], 422);

        $p = Prospect::findById((int)($_POST['id'] ?? 0));
        if ($p === null) $this->json(['error' => t('prospect_not_found')], 404);
        $channel = in_array($_POST['channel'] ?? '', ['whatsapp', 'sms', 'email'], true) ? (string)$_POST['channel'] : 'whatsapp';

        try {
            $this->json(['ok' => true, 'message' => $ai->generateMessage($p, $channel, \App\Core\Lang::current())]);
        } catch (\Throwable $e) {
            log_error('aiMessage: ' . $e->getMessage());
            $this->json(['error' => t('api_error') . $e->getMessage()], 502);
        }
    }

    // ---------- IA : normalisation telephone / adresse ----------
    public function aiNormalize(): void
    {
        $this->auth();
        Csrf::verify();
        $ai = new AiService();
        if (!$ai->enabled()) $this->json(['error' => t('ai_disabled')], 422);

        $p = Prospect::findById((int)($_POST['id'] ?? 0));
        if ($p === null) $this->json(['error' => t('prospect_not_found')], 404);

        try {
            $fixed = $ai->normalizeContact($p);
        } catch (\Throwable $e) {
            log_error('aiNormalize: ' . $e->getMessage());
            $this->json(['error' => t('api_error') . $e->getMessage()], 502);
        }
        $fields = [];
        if (!empty($fixed['telephone'])) {
            $fields['telephone'] = (string)$fixed['telephone'];
            $fields['telephone_normalise'] = PhoneNormalizer::normalize($fields['telephone'], (string)setting('default_country', 'TN'));
        }
        if (!empty($fixed['adresse'])) $fields['adresse'] = (string)$fixed['adresse'];
        Prospect::update((int)$p['id'], $fields);
        $this->json(['ok' => true] + $fields);
    }
}
