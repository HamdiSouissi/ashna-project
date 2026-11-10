<h1>🕘 <?= e(t('history')) ?></h1>
<div class="card table-wrap">
  <table class="table">
    <thead><tr>
      <th><?= e(t('search_city')) ?></th><th><?= e(t('category_col')) ?></th>
      <th><?= e(t('nb_found')) ?></th><th><?= e(t('nb_no_site')) ?></th>
      <th><?= e(t('statut_col')) ?></th><th><?= e(t('date_col')) ?></th><th><?= e(t('actions')) ?></th>
    </tr></thead>
    <tbody>
    <?php foreach ($rows as $s): ?>
      <tr>
        <td><strong><?= e($s['ville']) ?></strong><div class="muted small"><?= e(mb_substr($s['zone_label'], 0, 70)) ?></div></td>
        <td><?= e(($categories[$s['categorie']]['icon'] ?? '📌') . ' ' . ($categories[$s['categorie']]['label'] ?? $s['categorie'])) ?></td>
        <td><?= (int)$s['nb_trouves'] ?></td>
        <td><?= (int)$s['nb_sans_site'] ?></td>
        <td><?= lf_badge('sr-' . $s['statut'], t('search_status_' . $s['statut'])) ?></td>
        <td class="muted small"><?= e($s['created_at']) ?></td>
        <td>
          <a class="btn btn-sm" href="<?= e(url('/prospects?search_id=' . $s['id'])) ?>">📋 <?= e(t('view_results')) ?></a>
          <a class="btn btn-sm" href="<?= e(url('/search?ville=' . rawurlencode($s['ville']) . '&cat=' . rawurlencode($s['categorie']))) ?>">↻ <?= e(t('relaunch')) ?></a>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if ($rows === []): ?><tr><td colspan="7" class="muted center"><?= e(t('history_empty')) ?></td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
