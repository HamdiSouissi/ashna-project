<?php
use App\Core\Csrf;
use App\Models\Prospect;

$keep = array_filter([
    'q' => $filters['q'], 'statut' => $filters['statut'], 'categorie' => $filters['categorie'],
    'ville' => $filters['ville'], 'search_id' => $filters['search_id'] ?: '',
    'has_phone' => $filters['has_phone'] ? '1' : '', 'has_email' => $filters['has_email'] ? '1' : '',
    'site' => $filters['site'],
], static fn($v) => $v !== '' && $v !== null);

$sortLink = static function (string $col) use ($keep, $filters): string {
    $dir = ($filters['sort'] === $col && $filters['dir'] === 'asc') ? 'desc' : 'asc';
    return e(url('/prospects')) . '?' . e(http_build_query(array_merge($keep, ['sort' => $col, 'dir' => $dir])));
};
$queryStr = http_build_query($keep);
?>
<h1>📋 <?= e(t('prospects')) ?> <span class="muted small">(<?= count($rows) ?> <?= e(t('results_count')) ?>)</span></h1>

<div class="card">
  <form method="get" action="<?= e(url('/prospects')) ?>" class="filters">
    <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="<?= e(t('name')) ?>…">
    <select name="statut">
      <option value=""><?= e(t('status')) ?> — <?= e(t('all')) ?></option>
      <?php foreach ($statuts as $s): ?>
        <option value="<?= e($s) ?>" <?= $filters['statut'] === $s ? 'selected' : '' ?>><?= e(t('st_' . $s)) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="categorie">
      <option value=""><?= e(t('category')) ?> — <?= e(t('all')) ?></option>
      <?php foreach ($categories as $key => $cat): ?>
        <option value="<?= e($key) ?>" <?= $filters['categorie'] === $key ? 'selected' : '' ?>><?= e($cat['icon'] . ' ' . $cat['label']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="site">
      <option value=""><?= e(t('score')) ?> — <?= e(t('all')) ?></option>
      <?php foreach (Prospect::SITE as $sw): ?>
        <option value="<?= e($sw) ?>" <?= $filters['site'] === $sw ? 'selected' : '' ?>><?= e(t('sw_' . $sw)) ?></option>
      <?php endforeach; ?>
    </select>
    <label class="check inline"><input type="checkbox" name="has_phone" <?= $filters['has_phone'] ? 'checked' : '' ?>> <?= e(t('filter_phone_only')) ?></label>
    <label class="check inline"><input type="checkbox" name="has_email" <?= $filters['has_email'] ? 'checked' : '' ?>> <?= e(t('filter_email_only')) ?></label>
    <?php if (!empty($filters['search_id'])): ?><input type="hidden" name="search_id" value="<?= (int)$filters['search_id'] ?>"><?php endif; ?>
    <button class="btn btn-primary" type="submit"><?= e(t('apply')) ?></button>
    <a class="btn" href="<?= e(url('/prospects')) ?>"><?= e(t('reset')) ?></a>
  </form>
</div>

<form id="bulk-form" class="card">
  <?= Csrf::field() ?>
  <div class="bulk-bar">
    <label class="check inline"><input type="checkbox" id="check-all"> <?= e(t('select_all')) ?></label>
    <span id="bulk-count" class="muted small"></span>
    <select id="bulk-status">
      <option value=""><?= e(t('bulk_change_status')) ?>…</option>
      <?php foreach ($statuts as $s): ?><option value="<?= e($s) ?>"><?= e(t('st_' . $s)) ?></option><?php endforeach; ?>
    </select>
    <button type="button" class="btn" id="bulk-status-btn"><?= e(t('bulk_change_status')) ?></button>
    <button type="button" class="btn btn-danger" id="bulk-delete-btn"><?= e(t('bulk_delete')) ?></button>
    <a class="btn" href="<?= e(url('/export')) ?>?<?= e($queryStr) ?>">⬇ <?= e(t('export')) ?></a>
  </div>

  <div class="table-wrap">
    <table class="table">
      <thead><tr>
        <th></th>
        <th><a href="<?= $sortLink('nom') ?>"><?= e(t('name')) ?> ⇅</a></th>
        <th><?= e(t('phone')) ?></th>
        <th><a href="<?= $sortLink('ville') ?>"><?= e(t('ville')) ?> ⇅</a></th>
        <th><?= e(t('email_col')) ?></th>
        <th><a href="<?= $sortLink('statut_prospection') ?>"><?= e(t('status')) ?> ⇅</a></th>
        <th><a href="<?= $sortLink('score_confiance') ?>"><?= e(t('score')) ?> ⇅</a></th>
        <th><?= e(t('actions')) ?></th>
      </tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><input type="checkbox" class="row-check" value="<?= (int)$r['id'] ?>"></td>
          <td><a href="<?= e(url('/prospect/' . $r['id'])) ?>"><strong><?= e($r['nom']) ?></strong></a>
              <div class="muted small"><?= e(mb_substr($r['adresse'], 0, 60)) ?></div></td>
          <td>
            <?php if ($r['telephone_normalise'] !== ''): ?>
              <a href="tel:<?= e($r['telephone_normalise']) ?>"><?= e($r['telephone_normalise']) ?></a>
              <a class="wa" href="<?= e(\App\Services\PhoneNormalizer::waLink($r['telephone_normalise'])) ?>" target="_blank" rel="noopener">💬</a>
            <?php else: ?><span class="muted"><?= e($r['telephone'] ?: '—') ?></span><?php endif; ?>
          </td>
          <td><?= e($r['ville']) ?></td>
          <td><?= $r['email'] !== '' ? '<a href="mailto:' . e($r['email']) . '">' . e($r['email']) . '</a>' : '—' ?></td>
          <td><?= badge_statut($r['statut_prospection']) ?></td>
          <td><?= badge_score($r['score_confiance']) ?> <?= badge_site($r['site_web_statut']) ?></td>
          <td><a class="btn btn-sm" href="<?= e(url('/prospect/' . $r['id'])) ?>">👁</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($rows === []): ?>
        <tr><td colspan="8" class="muted center"><?= e(t('no_data')) ?></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</form>
