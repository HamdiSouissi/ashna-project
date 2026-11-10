<?php
$maxStatus = max(1, max($byStatus ?: [1]));
?>
<div class="stat-grid">
  <div class="card stat"><div class="stat-value"><?= (int)$total ?></div><div class="stat-label"><?= e(t('total_prospects')) ?></div></div>
  <div class="card stat"><div class="stat-value"><?= e(number_format($conversion, 1)) ?>%</div><div class="stat-label"><?= e(t('conversion_rate')) ?></div></div>
  <div class="card stat"><div class="stat-value"><?= (int)$searchCount ?></div><div class="stat-label"><?= e(t('searches_total')) ?></div></div>
  <div class="card stat"><div class="stat-value"><?= (int)($byStatus['a_contacter'] ?? 0) ?></div><div class="stat-label"><?= e(t('st_a_contacter')) ?></div></div>
</div>

<div class="grid-2">
  <div class="card">
    <h2><?= e(t('by_status')) ?></h2>
    <?php if ($byStatus === []): ?><p class="muted"><?= e(t('no_data')) ?></p><?php endif; ?>
    <?php foreach (\App\Models\Prospect::STATUTS as $s): ?>
      <?php if (empty($byStatus[$s])) continue; ?>
      <div class="bar-row">
        <span class="bar-label"><?= badge_statut($s) ?></span>
        <span class="bar-track"><span class="bar-fill st-<?= e($s) ?>" style="width: <?= round($byStatus[$s] / $maxStatus * 100) ?>%"></span></span>
        <span class="bar-value"><?= (int)$byStatus[$s] ?></span>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="card">
    <h2><?= e(t('by_category')) ?></h2>
    <?php if ($byCat === []): ?><p class="muted"><?= e(t('no_data')) ?></p><?php endif; ?>
    <?php foreach ($byCat as $r): ?>
      <div class="bar-row"><span class="bar-label"><?= e($r['k']) ?></span>
        <span class="bar-track"><span class="bar-fill cat" style="width: <?= round($r['c'] / max(1, $byCat[0]['c']) * 100) ?>%"></span></span>
        <span class="bar-value"><?= (int)$r['c'] ?></span></div>
    <?php endforeach; ?>
    <h2><?= e(t('by_city')) ?></h2>
    <?php foreach ($byCity as $r): ?>
      <div class="bar-row"><span class="bar-label"><?= e($r['k']) ?></span>
        <span class="bar-track"><span class="bar-fill city" style="width: <?= round($r['c'] / max(1, $byCity[0]['c']) * 100) ?>%"></span></span>
        <span class="bar-value"><?= (int)$r['c'] ?></span></div>
    <?php endforeach; ?>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <h2><?= e(t('recent_activity')) ?></h2>
    <?php if ($recent === []): ?><p class="muted"><?= e(t('no_data')) ?></p><?php endif; ?>
    <ul class="feed">
      <?php foreach ($recent as $i): ?>
        <li>
          <a href="<?= e(url('/prospect/' . $i['prospect_id'])) ?>"><strong><?= e($i['prospect_nom']) ?></strong></a>
          <?= lf_badge('it', t('type_' . $i['type'])) ?>
          <span class="muted"><?= e(mb_substr($i['note'], 0, 90)) ?></span>
          <div class="muted small"><?= e($i['date_interaction']) ?><?= $i['user_nom'] ? ' · ' . e($i['user_nom']) : '' ?></div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="card">
    <h2><?= e(t('recent_searches')) ?></h2>
    <?php if ($searches === []): ?><p class="muted"><?= e(t('no_data')) ?></p><?php endif; ?>
    <ul class="feed">
      <?php foreach ($searches as $s): ?>
        <li>
          <strong><?= e($s['ville']) ?></strong> · <?= e(lf_categories()[$s['categorie']]['label'] ?? $s['categorie']) ?>
          <?= lf_badge('sr-' . $s['statut'], t('search_status_' . $s['statut'])) ?>
          <div class="muted small"><?= e($s['created_at']) ?> · <?= (int)$s['nb_trouves'] ?> <?= e(t('nb_found')) ?> / <?= (int)$s['nb_sans_site'] ?> <?= e(t('nb_no_site')) ?></div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
