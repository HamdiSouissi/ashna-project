<?php
use App\Core\Csrf;
use App\Services\PhoneNormalizer;

$tel = $p['telephone_normalise'] ?: $p['telephone'];
$gmaps = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($p['latitude'] . ',' . $p['longitude']);
$osm = 'https://www.openstreetmap.org/?mlat=' . rawurlencode((string)$p['latitude']) . '&mlon=' . rawurlencode((string)$p['longitude']) . '#map=19/' . $p['latitude'] . '/' . $p['longitude'];
?>
<a class="btn" href="<?= e(url('/prospects')) ?>">← <?= e(t('back')) ?></a>
<div class="grid-2">
  <div class="card">
    <h1><?= e($p['nom']) ?></h1>
    <p><?= badge_statut($p['statut_prospection']) ?> <?= badge_score($p['score_confiance']) ?> <?= badge_site($p['site_web_statut']) ?></p>
    <dl class="info">
      <dt><?= e(t('category')) ?></dt><dd><?= e(lf_categories()[$p['categorie']]['label'] ?? $p['categorie']) ?></dd>
      <dt><?= e(t('address')) ?></dt><dd><?= e($p['adresse'] ?: t('not_provided')) ?></dd>
      <dt><?= e(t('ville')) ?></dt><dd><?= e($p['ville']) ?> <?= e($p['code_postal']) ?></dd>
      <dt><?= e(t('phone')) ?></dt>
      <dd>
        <?php if ($p['telephone_normalise'] !== ''): ?>
          <a href="tel:<?= e($p['telephone_normalise']) ?>"><?= e($p['telephone_normalise']) ?></a>
        <?php else: ?><?= e($p['telephone'] ?: t('not_provided')) ?><?php endif; ?>
        <?php if ($aiEnabled): ?>
          <button type="button" class="btn btn-sm" id="ai-normalize-btn" data-id="<?= (int)$p['id'] ?>">✨ <?= e(t('ai_normalize')) ?></button>
        <?php endif; ?>
      </dd>
      <dt><?= e(t('email_col')) ?></dt><dd><?= $p['email'] !== '' ? '<a href="mailto:' . e($p['email']) . '">' . e($p['email']) . '</a>' : e(t('not_provided')) ?></dd>
      <dt><?= e(t('social_links')) ?></dt>
      <dd>
        <?= $p['facebook'] !== '' ? '<a href="' . e($p['facebook']) . '" target="_blank" rel="noopener">Facebook</a>' : '' ?>
        <?= $p['instagram'] !== '' ? '<a href="' . e($p['instagram']) . '" target="_blank" rel="noopener">Instagram</a>' : '' ?>
        <?= ($p['facebook'] === '' && $p['instagram'] === '') ? e(t('not_provided')) : '' ?>
      </dd>
      <dt><?= e(t('opening_hours')) ?></dt><dd><?= e($p['horaires'] ?: t('not_provided')) ?></dd>
      <dt><?= e(t('coordinates')) ?></dt><dd><?= e($p['latitude']) ?>, <?= e($p['longitude']) ?></dd>
    </dl>

    <div class="btn-row">
      <?php if ($tel !== ''): ?>
        <a class="btn btn-primary" href="tel:<?= e($tel) ?>">📞 <?= e(t('call')) ?></a>
      <?php endif; ?>
      <?php if ($p['telephone_normalise'] !== ''): ?>
        <a class="btn btn-wa" target="_blank" rel="noopener" href="<?= e(PhoneNormalizer::waLink($p['telephone_normalise'])) ?>">💬 <?= e(t('whatsapp')) ?></a>
      <?php endif; ?>
      <?php if ($p['email'] !== ''): ?>
        <a class="btn" href="mailto:<?= e($p['email']) ?>">✉️ <?= e(t('send_email')) ?></a>
      <?php endif; ?>
      <a class="btn" target="_blank" rel="noopener" href="<?= e($gmaps) ?>">🧭 <?= e(t('itinerary')) ?></a>
      <a class="btn" target="_blank" rel="noopener" href="<?= e($osm) ?>">🗺️ <?= e(t('itinerary_osm')) ?></a>
      <button type="button" class="btn" id="verify-btn" data-id="<?= (int)$p['id'] ?>">🔍 <?= e(t('verify')) ?></button>
    </div>
    <p id="verify-result" class="muted small"></p>
  </div>

  <div class="card">
    <h2><?= e(t('notes')) ?> / <?= e(t('status')) ?></h2>
    <form method="post" action="<?= e(url('/prospect/' . $p['id'])) ?>">
      <?= Csrf::field() ?>
      <label><?= e(t('status')) ?>
        <select name="statut_prospection">
          <?php foreach ($statuts as $s): ?>
            <option value="<?= e($s) ?>" <?= $p['statut_prospection'] === $s ? 'selected' : '' ?>><?= e(t('st_' . $s)) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label><?= e(t('notes')) ?><textarea name="notes" rows="5"><?= e($p['notes'] ?? '') ?></textarea></label>
      <button class="btn btn-primary" type="submit"><?= e(t('save')) ?></button>
    </form>

    <?php if ($aiEnabled): ?>
      <h2>🤖 <?= e(t('ai_generate')) ?></h2>
      <div class="btn-row">
        <select id="ai-channel">
          <option value="whatsapp"><?= e(t('type_whatsapp')) ?></option>
          <option value="sms">SMS</option>
          <option value="email"><?= e(t('type_email')) ?></option>
        </select>
        <button type="button" class="btn" id="ai-message-btn" data-id="<?= (int)$p['id'] ?>">✨ <?= e(t('ai_generate')) ?></button>
      </div>
      <textarea id="ai-message-output" rows="6" placeholder="…"></textarea>
      <button type="button" class="btn btn-sm" id="copy-msg-btn"><?= e(t('copy')) ?></button>
    <?php endif; ?>
  </div>
</div>

<div class="card">
  <h2>💬 <?= e(t('interactions')) ?></h2>
  <form method="post" action="<?= e(url('/prospect/' . $p['id'] . '/interaction')) ?>" class="btn-row">
    <?= Csrf::field() ?>
    <select name="type" required>
      <?php foreach ($types as $type): ?>
        <option value="<?= e($type) ?>"><?= e(t('type_' . $type)) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="text" name="note" placeholder="<?= e(t('note_label')) ?>" required class="grow">
    <button class="btn btn-primary" type="submit">+ <?= e(t('add_interaction')) ?></button>
  </form>
  <?php if ($interactions === []): ?>
    <p class="muted"><?= e(t('no_interactions')) ?></p>
  <?php endif; ?>
  <ul class="feed">
    <?php foreach ($interactions as $i): ?>
      <li>
        <?= lf_badge('it', t('type_' . $i['type'])) ?>
        <span><?= e($i['note']) ?></span>
        <div class="muted small"><?= e($i['date_interaction']) ?><?= $i['user_nom'] ? ' · ' . e($i['user_nom']) : '' ?></div>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
<script src="<?= e(url('assets/js/prospect.js')) ?>"></script>
