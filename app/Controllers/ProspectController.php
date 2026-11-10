<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Interaction;
use App\Models\Prospect;
use App\Services\AiService;
use App\Services\PhoneNormalizer;

class ProspectController extends Controller
{
    public function show(string $id): void
    {
        Auth::requireLogin();
        $p = Prospect::findById((int)$id);
        if ($p === null) { flash('error', t('prospect_not_found')); redirect('/prospects'); }

        $this->render('prospect/show', [
            'title'   => $p['nom'],
            'p'       => $p,
            'statuts' => Prospect::STATUTS,
            'types'   => Interaction::TYPES,
            'interactions' => Interaction::forProspect((int)$p['id']),
            'aiEnabled' => (new AiService())->enabled(),
        ]);
    }

    public function update(string $id): void
    {
        Auth::requireLogin();
        Csrf::verify();
        $p = Prospect::findById((int)$id);
        if ($p === null) redirect('/prospects');

        $fields = [];
        if (array_key_exists('notes', $_POST))     $fields['notes'] = trim((string)$_POST['notes']);
        if (array_key_exists('telephone', $_POST)) $fields['telephone'] = trim((string)$_POST['telephone']);
        if (array_key_exists('email', $_POST))     $fields['email'] = trim((string)$_POST['email']);
        if (array_key_exists('adresse', $_POST))   $fields['adresse'] = trim((string)$_POST['adresse']);
        if (array_key_exists('telephone', $_POST)) {
            $fields['telephone_normalise'] = PhoneNormalizer::normalize($fields['telephone'], (string)setting('default_country', 'TN'));
        }
        if (!empty($_POST['statut_prospection']) && in_array($_POST['statut_prospection'], Prospect::STATUTS, true)) {
            $fields['statut_prospection'] = (string)$_POST['statut_prospection'];
        }
        Prospect::update((int)$id, $fields);
        flash('success', t('changed_ok'));
        redirect('/prospect/' . $id);
    }

    public function interaction(string $id): void
    {
        Auth::requireLogin();
        Csrf::verify();
        $p = Prospect::findById((int)$id);
        if ($p === null) redirect('/prospects');
        $type = (string)($_POST['type'] ?? '');
        $note = trim((string)($_POST['note'] ?? ''));
        try {
            Interaction::add((int)$id, Auth::id(), $type, $note);
            flash('success', t('changed_ok'));
        } catch (\Throwable $e) {
            flash('error', t('api_error') . $e->getMessage());
        }
        redirect('/prospect/' . $id);
    }
}
