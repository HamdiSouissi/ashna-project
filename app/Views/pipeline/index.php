<h1>📊 <?= e(t('pipeline')) ?></h1>
<p class="muted"><?= e(t('pipeline_hint')) ?></p>
<div class="kanban" id="kanban">
  <?php foreach ($statuts as $s): ?>
    <div class="kanban-col">
      <div class="kanban-col-head"><?= badge_statut($s) ?> <span class="muted"><?= count($columns[$s]) ?></span></div>
      <div class="kanban-col-body" data-status="<?= e($s) ?>">
        <?php foreach ($columns[$s] as $p): ?>
          <div class="kanban-card" draggable="true" data-id="<?= (int)$p['id'] ?>">
            <strong><?= e($p['nom']) ?></strong>
            <div class="muted small"><?= e($p['ville']) ?></div>
            <?php if ($p['telephone_normalise'] !== ''): ?>
              <a class="small" href="tel:<?= e($p['telephone_normalise']) ?>"><?= e($p['telephone_normalise']) ?></a>
            <?php endif; ?>
            <div><a class="small" href="<?= e(url('/prospect/' . $p['id'])) ?>">👁 <?= e(t('prospect')) ?></a></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
