<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $this->render('settings/index', ['title' => t('settings')]);
    }

    public function save(): void
    {
        Auth::requireAdmin();
        Csrf::verify();
        $keys = [
            'nominatim_user_agent', 'overpass_servers', 'overpass_timeout', 'api_delay',
            'cache_ttl_hours', 'enrichment_delay', 'default_country',
            'ai_enabled', 'ai_base_url', 'ai_api_key', 'ai_model', 'custom_categories',
        ];
        foreach ($keys as $key) {
            if (array_key_exists($key, $_POST)) Setting::set($key, trim((string)$_POST[$key]));
        }
        flash('success', t('settings_saved'));
        redirect('/settings');
    }
}
