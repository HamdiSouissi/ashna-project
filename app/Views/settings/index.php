<?php use App\Core\Csrf; ?>
<div class="card">
  <h1>⚙️ <?= e(t('settings')) ?></h1>
  <form method="post" action="<?= e(url('/settings')) ?>">
    <?= Csrf::field() ?>

    <h2><?= e(t('section_api')) ?></h2>
    <label><?= e(t('settings_ua')) ?>
      <input type="text" name="nominatim_user_agent" value="<?= e(setting('nominatim_user_agent')) ?>">
    </label>
    <label><?= e(t('settings_overpass')) ?>
      <textarea name="overpass_servers" rows="3"><?= e(setting('overpass_servers')) ?></textarea>
    </label>
    <div class="form-row">
      <label><?= e(t('settings_timeout')) ?><input type="number" name="overpass_timeout" min="10" max="120" value="<?= e(setting('overpass_timeout')) ?>"></label>
      <label><?= e(t('settings_delay')) ?><input type="number" name="api_delay" step="0.1" min="0.5" value="<?= e(setting('api_delay')) ?>"></label>
      <label><?= e(t('settings_cache')) ?><input type="number" name="cache_ttl_hours" min="0" max="720" value="<?= e(setting('cache_ttl_hours')) ?>"></label>
      <label><?= e(t('settings_enrichment')) ?><input type="number" name="enrichment_delay" step="0.5" min="1" value="<?= e(setting('enrichment_delay')) ?>"></label>
      <label><?= e(t('settings_country')) ?>
        <select name="default_country">
          <?php foreach (['TN' => 'Tunisie +216', 'FR' => 'France +33', 'MA' => 'Maroc +212', 'DZ' => 'Algérie +213'] as $c => $l): ?>
            <option value="<?= e($c) ?>" <?= setting('default_country', 'TN') === $c ? 'selected' : '' ?>><?= e($l) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>

    <h2>🤖 <?= e(t('section_ai')) ?></h2>
    <label class="check">
      <input type="checkbox" name="ai_enabled" value="1" <?= setting('ai_enabled') === '1' ? 'checked' : '' ?>>
      <?= e(t('ai_enabled')) ?>
    </label>
    <label><?= e(t('ai_base_url')) ?>
      <input type="url" name="ai_base_url" placeholder="https://api.groq.com/openai/v1" value="<?= e(setting('ai_base_url')) ?>">
    </label>
    <div class="form-row">
      <label><?= e(t('ai_api_key')) ?><input type="password" name="ai_api_key" value="<?= e(setting('ai_api_key')) ?>"></label>
      <label><?= e(t('ai_model')) ?><input type="text" name="ai_model" value="<?= e(setting('ai_model')) ?>"></label>
    </div>

    <h2>📌 <?= e(t('section_categories')) ?></h2>
    <p class="muted small"><?= e(t('settings_categories_hint')) ?></p>
    <label><textarea name="custom_categories" rows="4"><?= e(setting('custom_categories')) ?></textarea></label>

    <button type="submit" class="btn btn-primary"><?= e(t('save')) ?></button>
  </form>
</div>
